<?php

namespace App\Services;

use App\Models\Legacy\EntregaDataBloqueada;
use App\Models\Legacy\EntregaPeriodo;
use App\Models\Legacy\EntregaRegiao;
use App\Models\Legacy\EntregaRegiaoLogradouro;
use App\Models\Legacy\Loja;
use App\Models\Legacy\LojaEntregaRegiao;
use App\Models\Legacy\Logradouro;
use App\Models\Legacy\NaoDisponivelData;
use App\Models\Legacy\PedidoInformacaoFrete;
use App\Models\Legacy\PrecoFrete;
use App\Models\Legacy\SoDisponivelData;
use App\Models\Legacy\VeiculoPeriodo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de Frete e Entregas.
 *
 * Replica a lógica de entrega do sistema legado:
 * CEP → Logradouro → Região → Loja → Períodos disponíveis → Valor do frete
 *
 * Implementação progressiva:
 * - V1 (atual): Frete fixo baseado na tabela precos_frete por distância estimada
 * - V2 (futura): Integração com LalaMove API + cascata de 14+ regras de desconto
 *
 * CEPs são armazenados SEM hífen no banco legado (ex: '20551030').
 */
class ShippingService
{
    /**
     * Busca dados de um CEP: logradouro, região e loja responsável.
     *
     * Fluxo: CEP → logradouros → entregas_regioes_logradouros → entregas_regioes → lojas_entregas_regioes
     *
     * @param string $cep CEP (com ou sem hífen)
     * @return array|null ['logradouro' => ..., 'regiao' => ..., 'loja' => ...] ou null se não atendido
     */
    public function lookupCep(string $cep): ?array
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        // Cache por 1 hora (evita queries repetidas no banco de 282k registros)
        $cacheKey = "shipping_cep_{$cepLimpo}";

        return Cache::remember($cacheKey, 3600, function () use ($cepLimpo) {
            // 1. Busca logradouro pelo CEP
            $logradouro = Logradouro::byCep($cepLimpo)->first();

            if (!$logradouro) {
                return null;
            }

            // 2. Busca a região desse logradouro
            $regiaoLink = EntregaRegiaoLogradouro::where('logradouros_id', $logradouro->id)->first();

            if (!$regiaoLink) {
                return null;
            }

            $regiao = EntregaRegiao::find($regiaoLink->entregas_regioes_id);

            if (!$regiao) {
                return null;
            }

            // 3. Busca a loja que atende esta região
            $lojaLink = LojaEntregaRegiao::where('entregas_regiao_id', $regiao->id)->first();
            $loja = $lojaLink ? Loja::find($lojaLink->loja_id) : null;

            return [
                'logradouro' => $logradouro,
                'regiao'     => $regiao,
                'loja'       => $loja,
            ];
        });
    }

    /**
     * Verifica se um CEP é atendido para entrega.
     *
     * @param string $cep CEP (com ou sem hífen)
     * @return bool
     */
    public function isCepServiced(string $cep): bool
    {
        $result = $this->lookupCep($cep);
        return $result !== null && $result['loja'] !== null;
    }

    /**
     * Retorna períodos de entrega disponíveis para um CEP.
     *
     * Calcula as próximas datas de entrega baseado nos períodos
     * cadastrados para a região, excluindo datas bloqueadas.
     * Aplica regras de logística (regras_entregas) quando existem para a loja.
     *
     * @param string $cep CEP do cliente
     * @param int $days Número de dias para frente para buscar (padrão 14)
     * @param float $orderTotal Valor total do carrinho (para regras de pedido mínimo)
     * @param int|null $pessoaId ID do cliente logado (para regra de cliente novo)
     * @return array ['slots' => array, 'avisos' => array, 'pedido_minimo' => float|null]
     */
    public function getDeliverySlots(string $cep, int $days = 14, float $orderTotal = 0, ?int $pessoaId = null): array
    {
        $emptyResult = ['slots' => [], 'avisos' => [], 'pedido_minimo' => null];

        $lookup = $this->lookupCep($cep);

        if (!$lookup || !$lookup['regiao'] || !$lookup['loja']) {
            return $emptyResult;
        }

        $regiao = $lookup['regiao'];
        $loja = $lookup['loja'];
        $logradouro = $lookup['logradouro'];

        // Busca períodos de entrega para esta região
        $periodos = EntregaPeriodo::where('entregas_regiao_id', $regiao->id)->get();

        if ($periodos->isEmpty()) {
            return $emptyResult;
        }

        // Busca datas bloqueadas para a loja
        $datasBloqueadas = EntregaDataBloqueada::forStore($loja->id)
            ->future()
            ->pluck('data')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        // Busca veiculos_periodos ativos para filtrar períodos válidos
        $veiculoPeriodoIds = VeiculoPeriodo::where('loja_id', $loja->id)
            ->where('ativo', 1)
            ->pluck('entregas_periodo_id')
            ->toArray();

        // Gera slots para os próximos N dias
        $slots = [];
        $hoje = Carbon::now();

        for ($i = 0; $i <= $days; $i++) {
            $data = $hoje->copy()->addDays($i);
            $dataStr = $data->format('Y-m-d');
            $diaSemana = (int) $data->dayOfWeek; // 0=Dom, 6=Sáb

            // Pula datas bloqueadas
            if (in_array($dataStr, $datasBloqueadas)) {
                continue;
            }

            // Filtra períodos para este dia da semana
            $periodosHoje = $periodos->filter(function ($p) use ($diaSemana, $veiculoPeriodoIds) {
                // Verifica se o dia bate
                if ((int) $p->dia !== $diaSemana) {
                    return false;
                }
                // Verifica se existe veículo/período ativo
                if (!empty($veiculoPeriodoIds) && !in_array($p->id, $veiculoPeriodoIds)) {
                    return false;
                }
                return true;
            });

            foreach ($periodosHoje as $periodo) {
                // Verifica margem de antecedência (em horas)
                $horaLimite = $data->copy()->setTimeFromTimeString($periodo->hora_inicial)
                    ->subHours($periodo->margem_hora ?? 0);

                // Se for hoje, verifica se ainda dá tempo
                if ($i === 0 && $hoje->gt($horaLimite)) {
                    continue;
                }

                // Busca veículo_período associado para obter o ID que o legado espera
                $vp = VeiculoPeriodo::where('entregas_periodo_id', $periodo->id)
                    ->where('loja_id', $loja->id)
                    ->where('ativo', 1)
                    ->first();

                $slots[] = [
                    'date'               => $dataStr,
                    'date_formatted'     => $data->format('d/m/Y') . ' (' . $periodo->dia_nome . ')',
                    'time_start'         => substr($periodo->hora_inicial, 0, 5),
                    'time_end'           => substr($periodo->hora_final, 0, 5),
                    'time_formatted'     => $periodo->horario_formatado,
                    'periodo_id'         => $periodo->id,
                    'veiculo_periodo_id' => $vp?->id,
                    'loja_id'            => $loja->id,
                    'regiao_id'          => $regiao->id,
                    'margem_hora'        => $periodo->margem_hora ?? 0,
                    'dia_semana'         => $diaSemana,
                ];
            }
        }

        // Aplica filtros de disponibilidade por veículo (#195)
        $slots = $this->filterSlotsByVehicleAvailability($slots);

        // Aplica regras de logística (regras_entregas)
        $deliveryRuleService = app(DeliveryRuleService::class);

        return $deliveryRuleService->filterSlots(
            $slots,
            $loja->id,
            $regiao->id,
            $orderTotal,
            $logradouro,
            $pessoaId
        );
    }

    /**
     * Retorna lojas disponíveis para retirada.
     *
     * @return Collection Lojas ativas com retirada habilitada
     */
    public function getPickupStores(): Collection
    {
        return Loja::active()->allowPickup()->get();
    }

    /**
     * Calcula o valor do frete para um pedido.
     *
     * V1 (simplificada): Usa tabela precos_frete com estimativa de distância.
     * Se não conseguir calcular, retorna valor fixo padrão.
     *
     * @param string $cep CEP de destino
     * @param float $orderTotal Valor total do pedido (para regras de desconto)
     * @param int|null $formaPagamentoId Forma de pagamento (PIX pode ter frete grátis)
     * @return array ['valor' => float, 'valor_original' => float, 'motivo' => string]
     */
    public function calculateShipping(string $cep, float $orderTotal, ?int $formaPagamentoId = null): array
    {
        $lookup = $this->lookupCep($cep);

        if (!$lookup) {
            // CEP não encontrado — frete a combinar
            return [
                'valor'          => 0,
                'valor_original' => 0,
                'motivo'         => 'cep_nao_encontrado',
                'distancia_km'   => 0,
            ];
        }

        $logradouro = $lookup['logradouro'];
        $loja = $lookup['loja'];

        // Tenta calcular distância por coordenadas GPS
        $distanciaKm = 0;
        if ($loja && $logradouro->latitude && $logradouro->longitude && $loja->latitude && $loja->longitude) {
            $distanciaKm = $this->calculateDistance(
                (float) $logradouro->latitude,
                (float) $logradouro->longitude,
                (float) $loja->latitude,
                (float) $loja->longitude
            );
        }

        // Busca tabela de preços (usa a primeira — "Lalago")
        $precoFrete = PrecoFrete::first();

        $valorOriginal = $precoFrete
            ? $precoFrete->calcularPorDistancia($distanciaKm)
            : 15.00; // Valor padrão se não houver tabela

        // Aplica regras de desconto simplificadas
        $valor = $valorOriginal;
        $motivo = 'tabela_preco';

        // Regra: Pedido >= R$ 300 com PIX = frete grátis
        if ($orderTotal >= 300 && $formaPagamentoId === PaymentService::FORMA_PIX) {
            $valor = 0;
            $motivo = 'pix >= 300';
        }
        // Regra: Pedido >= R$ 300 = 50% desconto no frete
        elseif ($orderTotal >= 300) {
            $valor = round($valorOriginal * 0.5, 2);
            $motivo = '>= 300';
        }

        return [
            'valor'          => round($valor, 2),
            'valor_original' => round($valorOriginal, 2),
            'motivo'         => $motivo,
            'distancia_km'   => round($distanciaKm, 2),
        ];
    }

    /**
     * Registra informações de frete no banco legado (tabela pedidos_informacoes_frete).
     *
     * @param int $pedidoId ID do pedido
     * @param int $pessoaId ID da pessoa
     * @param int $lojaId ID da loja
     * @param array $freteData Dados do frete calculado
     * @return PedidoInformacaoFrete Registro criado
     */
    public function saveShippingInfo(int $pedidoId, int $pessoaId, int $lojaId, array $freteData): PedidoInformacaoFrete
    {
        return PedidoInformacaoFrete::create([
            'pedido_id'              => $pedidoId,
            'pessoa_id'              => $pessoaId,
            'loja_id'                => $lojaId,
            'valor_frete_original'   => $freteData['valor_original'] ?? 0,
            'valor_frete'            => $freteData['valor'] ?? 0,
            'distancia_km'           => $freteData['distancia_km'] ?? 0,
            'distancia_ida_e_volta'  => ($freteData['distancia_km'] ?? 0) * 2,
            'falta_para_frete_gratis' => 0,
            'motivo'                 => $freteData['motivo'] ?? 'sync',
            'valor_total_sem_frete'  => $freteData['valor_pedido'] ?? 0,
        ]);
    }

    /**
     * Filtra slots de entrega por disponibilidade do veículo (#195).
     *
     * Replica lógica do legado (end_margem.php linhas 729-740):
     * - nao_disponiveis_datas: blacklist — remove slot em data específica
     * - so_disponiveis_datas: whitelist — veículo só opera nas datas listadas
     *
     * @param array $slots Slots gerados
     * @return array Slots filtrados
     */
    protected function filterSlotsByVehicleAvailability(array $slots): array
    {
        if (empty($slots)) {
            return $slots;
        }

        // Coleta veiculo_periodo_ids únicos dos slots
        $vpIds = array_unique(array_filter(array_column($slots, 'veiculo_periodo_id')));

        if (empty($vpIds)) {
            return $slots;
        }

        // Coleta range de datas dos slots
        $dates = array_column($slots, 'date');
        $minDate = min($dates);
        $maxDate = max($dates);

        // Carrega blacklist: nao_disponiveis_datas (batch)
        // Formato: [$vpId][$date] = true
        $blacklist = [];
        $naoDisp = NaoDisponivelData::whereIn('veiculo_periodo_id', $vpIds)
            ->whereBetween('data_nao_disponivel', [$minDate, $maxDate])
            ->get();

        foreach ($naoDisp as $nd) {
            $blacklist[$nd->veiculo_periodo_id][$nd->data_nao_disponivel->format('Y-m-d')] = true;
        }

        // Carrega whitelist: so_disponiveis_datas (batch)
        // Se um veiculo_periodo_id tem registros aqui, SÓ opera nas datas listadas
        // Formato: [$vpId][$date] = true
        $whitelist = [];
        $whitelistVpIds = [];
        $soDisp = SoDisponivelData::whereIn('veiculo_periodo_id', $vpIds)
            ->whereBetween('data_disponivel', [$minDate, $maxDate])
            ->get();

        foreach ($soDisp as $sd) {
            $whitelist[$sd->veiculo_periodo_id][$sd->data_disponivel->format('Y-m-d')] = true;
            $whitelistVpIds[$sd->veiculo_periodo_id] = true;
        }

        // Verifica se existem vpIds com registros em so_disponiveis (pode ter datas fora do range)
        if (empty($whitelistVpIds)) {
            $hasWhitelist = SoDisponivelData::whereIn('veiculo_periodo_id', $vpIds)->exists();
            if ($hasWhitelist) {
                // Há registros mas todos fora do range — precisa verificar quais vpIds têm whitelist
                $whitelistVpIds = SoDisponivelData::whereIn('veiculo_periodo_id', $vpIds)
                    ->distinct()
                    ->pluck('veiculo_periodo_id')
                    ->flip()
                    ->toArray();
            }
        }

        // Filtra slots
        return array_values(array_filter($slots, function ($slot) use ($blacklist, $whitelist, $whitelistVpIds) {
            $vpId = $slot['veiculo_periodo_id'];
            $date = $slot['date'];

            if (!$vpId) {
                return true;
            }

            // Blacklist: remove se (vpId, date) está em nao_disponiveis_datas
            if (isset($blacklist[$vpId][$date])) {
                return false;
            }

            // Whitelist: se este vpId tem registros em so_disponiveis_datas,
            // só manter se a data está na whitelist
            if (isset($whitelistVpIds[$vpId])) {
                return isset($whitelist[$vpId][$date]);
            }

            return true;
        }));
    }

    /**
     * Retorna datas de retirada disponíveis para uma loja (#199).
     *
     * Replica lógica do legado (end_margem.php):
     * - datasRetiradasLojas() — geração de datas (60 dias)
     * - bloquear_margens_retiradas() — filtragem por margem de produção
     *
     * @param int $lojaId ID da loja
     * @param array $productIds IDs dos produtos no carrinho (para calcular margem)
     * @param int $days Número de dias para gerar (padrão 60)
     * @param int $encaixe 0=normal (remove sem margem), 1=encaixe (remove COM margem)
     * @return array ['disponivel' => bool, 'datas' => array, 'loja' => array]
     */
    public function getPickupDates(int $lojaId, array $productIds = [], int $days = 60, int $encaixe = 0): array
    {
        $loja = Loja::active()->allowPickup()->find($lojaId);

        if (!$loja) {
            return ['disponivel' => false, 'datas' => [], 'loja' => null];
        }

        // Busca datas bloqueadas (por loja ou globais — loja_id = 0)
        $bloqueadas = $this->getBlockedDates();

        // Busca feriados: datas de so_disponiveis_datas mapeadas por loja
        // Em feriado, horário de funcionamento muda para o de sábado
        $feriados = $this->getFeriadosByLoja();

        // Calcula margem de produção com base nos produtos do carrinho
        $margemPedido = $this->getProductionMargin($productIds);

        // Gera datas candidatas
        $hoje = Carbon::now();
        $datas = [];

        for ($i = 0; $i <= $days; $i++) {
            $data = $hoje->copy()->addDays($i);
            $dataStr = $data->format('Y-m-d');
            $diaSemana = (int) $data->dayOfWeek; // 0=Dom, 6=Sáb

            // Pula domingos se a loja não funciona
            // Legado: strpos(horario_funcionamento_domingo, 's') === FALSE → pula
            if ($diaSemana === 0 && !$this->lojaAbertaDomingo($loja)) {
                continue;
            }

            // Pula datas bloqueadas (por loja ou global)
            if (isset($bloqueadas[$dataStr][$lojaId]) || isset($bloqueadas[$dataStr][0])) {
                continue;
            }

            // Determina horário de funcionamento
            $horario = $loja->horario_funcionamento_semana;

            // Em feriado, usa horário de sábado (legado: end_margem.php linha 388-391)
            if (isset($feriados[$lojaId][$dataStr])) {
                $horario = $loja->horario_funcionamento_sabado;
            }
            // Sábado: usa horário de sábado
            elseif ($diaSemana === 6) {
                $horario = $loja->horario_funcionamento_sabado;
            }
            // Domingo: usa horário de domingo
            elseif ($diaSemana === 0) {
                $horario = $loja->horario_funcionamento_domingo;
            }

            $datas[] = [
                'date'        => $dataStr,
                'dia_semana'  => $diaSemana,
                'horario'     => $horario,
                'loja_id'     => $lojaId,
            ];
        }

        // Aplica filtragem por margem de produção
        // Replica bloquear_margens_retiradas() do legado (end_margem.php linha 1119)
        $datas = $this->filterPickupDatesByMargin($datas, $margemPedido, $encaixe, $hoje->timestamp);

        return [
            'disponivel' => !empty($datas),
            'datas'      => $datas,
            'loja'       => [
                'id'       => $loja->id,
                'nome'     => $loja->nome,
                'endereco' => $loja->full_address,
            ],
        ];
    }

    /**
     * Filtra datas de retirada por margem de produção.
     *
     * Replica bloquear_margens_retiradas() do legado (end_margem.php linha 1119).
     *
     * Algoritmo:
     * - Para cada data candidata, calcula horas disponíveis de produção:
     *   - Corte fixo às 09:00 (mesma simplificação do legado)
     *   - Máximo 12h acumuláveis por dia
     *   - Sábado não conta se margem > 5h
     * - Acumula horas de dias anteriores da mesma loja
     * - Se total < margem → remove (modo normal) ou mantém (modo encaixe)
     *
     * @param array $datas Datas candidatas
     * @param int $margemPedido Margem de produção em horas
     * @param int $encaixe 0=normal, 1=encaixe
     * @param int $atual Timestamp atual (Unix)
     * @return array Datas filtradas
     */
    protected function filterPickupDatesByMargin(array $datas, int $margemPedido, int $encaixe, int $atual): array
    {
        if ($margemPedido <= 0) {
            return $datas;
        }

        $horaCorte = '09'; // Fixo às 09:00 (legado: $horafechamento = '09')
        $remover = [];

        foreach ($datas as $key => $data) {
            $tsCorte = strtotime($data['date'] . ' ' . $horaCorte . ':00:00');

            // Segundos disponíveis até o horário de corte desta data
            $segundos = $tsCorte - $atual;

            // Máximo 12h por dia
            if ($segundos > (60 * 60 * 12)) {
                $segundos = (60 * 60 * 12);
            }

            // Sábado não conta se margem > 5h (legado: T7286)
            // No legado a comparação $data['dia'] == 6 compara string com int,
            // mas a INTENÇÃO (documentada no ticket T7286) é excluir sábado
            if ($margemPedido > 5 && $data['dia_semana'] === 6) {
                $segundos = 0;
            }

            // Acumula horas de dias anteriores da mesma loja
            foreach ($datas as $d) {
                // Mesma loja
                if ($data['loja_id'] !== $d['loja_id']) {
                    continue;
                }

                // Sábado não conta na acumulação se margem > 5h
                if ($margemPedido > 5 && $d['dia_semana'] === 6) {
                    continue;
                }

                $tsCorte2 = strtotime($d['date'] . ' ' . $horaCorte . ':00:00');

                // Dia anterior deve estar no futuro (após $atual)
                if ($tsCorte2 <= $atual) {
                    continue;
                }

                // Apenas dias ANTES da data avaliada
                if ($tsCorte <= $tsCorte2) {
                    break;
                }

                $s = $tsCorte2 - $atual;
                if ($s > (60 * 60 * 12)) {
                    $s = (60 * 60 * 12);
                }

                $segundos += $s;
            }

            // Converte para horas
            $horas = $segundos / 3600;

            if ($horas < $margemPedido) {
                // Sem margem suficiente
                if ($encaixe === 0) {
                    $remover[$key] = true;
                }
            } else {
                // Com margem suficiente
                if ($encaixe === 1) {
                    $remover[$key] = true;
                }
            }
        }

        // Remove datas marcadas
        foreach ($remover as $key => $_) {
            unset($datas[$key]);
        }

        return array_values($datas);
    }

    /**
     * Calcula margem de produção com base nos produtos do carrinho.
     *
     * Replica get_margem_produtos() do legado (end_margem.php linha 831).
     *
     * Regras:
     * - Retorna MAX(margem_hora) dos produtos
     * - Inclui produtos dentro de pacotes (pacotes_produtos)
     * - Se algum produto tem cardapio = 0 e margem < 14 → mínimo 14h
     *
     * @param array $productIds IDs dos produtos no carrinho
     * @return int Margem em horas
     */
    public function getProductionMargin(array $productIds): int
    {
        if (empty($productIds)) {
            return 0;
        }

        $margem = 0;
        $placeholders = implode(',', array_map('intval', $productIds));

        // Query 1: Produtos diretos
        $result = DB::connection('mysql_legacy')->select("
            SELECT
                MAX(produtos.margem_hora) as m,
                MIN(produtos.cardapio) as min_cardapio,
                MAX(produtos.cardapio) as max_cardapio
            FROM produtos
            WHERE produtos.id IN ({$placeholders})
        ");

        if (!empty($result) && $result[0]->m !== null) {
            $row = $result[0];

            // Produto fora do cardápio: mínimo 14h
            if ($row->min_cardapio == 0 || $row->max_cardapio == 0) {
                if (14 > $row->m) {
                    $row->m = 14;
                }
            }

            if ($row->m > $margem) {
                $margem = (int) $row->m;
            }
        }

        // Query 2: Produtos dentro de pacotes
        $result2 = DB::connection('mysql_legacy')->select("
            SELECT
                MAX(produtos.margem_hora) as m,
                MIN(produtos.cardapio) as min_cardapio,
                MAX(produtos.cardapio) as max_cardapio
            FROM produtos
            INNER JOIN pacotes_produtos ON (pacotes_produtos.produto_id = produtos.id)
            INNER JOIN produtos as pacotes ON (pacotes.id = pacotes_produtos.pacote_id)
            WHERE pacotes.id IN ({$placeholders})
        ");

        if (!empty($result2) && $result2[0]->m !== null) {
            $row = $result2[0];

            if ($row->min_cardapio == 0 || $row->max_cardapio == 0) {
                if (14 > $row->m) {
                    $row->m = 14;
                }
            }

            if ($row->m > $margem) {
                $margem = (int) $row->m;
            }
        }

        return $margem;
    }

    /**
     * Retorna datas bloqueadas para entrega/retirada.
     *
     * Formato: [$date][$loja_id] = true (loja_id 0 = global)
     * Replica get_bloqueadas() do legado (end_margem.php linha 793).
     */
    protected function getBlockedDates(): array
    {
        $bloqueadas = [];

        $registros = EntregaDataBloqueada::future()->get();
        foreach ($registros as $r) {
            $date = $r->data->format('Y-m-d');
            $lojaId = (int) ($r->loja_id ?? 0);
            $bloqueadas[$date][$lojaId] = true;
        }

        return $bloqueadas;
    }

    /**
     * Retorna feriados (datas especiais) mapeados por loja.
     *
     * Datas em so_disponiveis_datas indicam feriados — na retirada,
     * a loja funciona com horário de sábado em vez de semana.
     *
     * Formato: [$loja_id][$date] = true
     */
    protected function getFeriadosByLoja(): array
    {
        $feriados = [];

        // so_disponiveis_datas tem veiculo_periodo_id → join com veiculos_periodos para obter loja_id
        $registros = DB::connection('mysql_legacy')->select("
            SELECT DISTINCT vp.loja_id, sd.data_disponivel
            FROM so_disponiveis_datas sd
            INNER JOIN veiculos_periodos vp ON vp.id = sd.veiculo_periodo_id
            WHERE sd.data_disponivel >= CURDATE()
        ");

        foreach ($registros as $r) {
            $date = $r->data_disponivel;
            if ($date instanceof \DateTime || $date instanceof Carbon) {
                $date = $date->format('Y-m-d');
            }
            $feriados[(int) $r->loja_id][$date] = true;
        }

        return $feriados;
    }

    /**
     * Verifica se a loja funciona aos domingos.
     *
     * Replica lógica do legado: strpos(horario_funcionamento_domingo, 's') !== FALSE
     * Se o campo contém 's' (parte de "sim" ou similar), a loja abre.
     */
    protected function lojaAbertaDomingo(Loja $loja): bool
    {
        $horDomingo = $loja->horario_funcionamento_domingo;
        return !empty($horDomingo) && str_contains(strtolower($horDomingo), 's');
    }

    /**
     * Calcula distância entre dois pontos usando fórmula de Haversine.
     *
     * @param float $lat1 Latitude ponto 1
     * @param float $lng1 Longitude ponto 1
     * @param float $lat2 Latitude ponto 2
     * @param float $lng2 Longitude ponto 2
     * @return float Distância em km
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
