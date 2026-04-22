<?php

namespace App\Services;

use App\Models\Legacy\Logradouro;
use App\Models\Legacy\Pedido;
use App\Models\Legacy\RegraEntrega;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service de Regras de Entrega (Logística).
 *
 * Replica a Fase 2 do sistema legado (horarios.php):
 * Filtra slots de entrega contra as regras da tabela regras_entregas.
 *
 * Para cada slot, aplica 5 verificações por regra:
 * 1. loja_id — regra pertence à loja do slot
 * 2. margens — margem_hora do slot está na lista da regra
 * 3. horarios_dias — hora do slot está permitida naquele dia
 * 4. regioes_ids — região do CEP está na lista da regra
 * 5. dias_da_semana — dia do slot está permitido
 *
 * Se todos passam → calcula distância → valida km_maxima e pedido_minimo.
 * Se quantidadeRegras == 0 (nenhuma regra cobriu a margem) → slot liberado.
 */
class DeliveryRuleService
{
    protected DistanceService $distanceService;

    public function __construct(DistanceService $distanceService)
    {
        $this->distanceService = $distanceService;
    }

    /**
     * Filtra slots de entrega aplicando as regras de logística.
     *
     * @param array $slots Slots gerados pelo ShippingService
     * @param int $lojaId ID da loja que atende a região
     * @param int $regiaoId ID da região de entrega
     * @param float $orderTotal Valor total do carrinho
     * @param Logradouro $logradouro Logradouro do CEP (com lat/lng)
     * @param int|null $pessoaId ID do cliente (para verificar se já comprou)
     * @return array ['slots' => array, 'avisos' => array, 'pedido_minimo' => float|null]
     */
    public function filterSlots(
        array $slots,
        int $lojaId,
        int $regiaoId,
        float $orderTotal,
        Logradouro $logradouro,
        ?int $pessoaId = null
    ): array {
        // Carrega todas as regras com eager load (cache 10 min — raramente mudam)
        $regras = Cache::remember("delivery_rules_all", 600, function () {
            return RegraEntrega::with(['loja', 'lojaMaisProxima', 'enderecoRegraEntrega'])->get();
        });

        if ($regras->isEmpty()) {
            return [
                'slots'         => $slots,
                'avisos'        => [],
                'pedido_minimo' => null,
            ];
        }

        // Verifica se o cliente já fez algum pedido finalizado
        $comprouAntes = $this->hasOrderedBefore($pessoaId);

        // Cache de distâncias em memória (evita chamadas repetidas no mesmo request)
        $distanciaCache = [];

        $slotsValidos = [];
        $minimo = null;

        foreach ($slots as $slot) {
            $valido = false;
            $quantidadeRegras = 0;

            foreach ($regras as $regra) {
                // Check 1: loja_id — a regra é para a loja do slot?
                if ((int) $regra->loja_id !== $lojaId) {
                    continue;
                }

                // Check 2: margem — o margem_hora do slot está na lista da regra?
                $margensRegra = $regra->margens_array;
                $margemSlot = (int) ($slot['margem_hora'] ?? 0);

                if (!in_array($margemSlot, $margensRegra, true)) {
                    continue;
                }

                // Margem bateu: esta regra "conta" para este slot
                $quantidadeRegras++;

                // Check 3: horários do dia — hora do slot é permitida neste dia?
                $diaSemana = (int) $slot['dia_semana'];
                $horaInicial = (int) substr($slot['time_start'], 0, 2);
                $horariosDia = $regra->horarios_dias_array;

                $passouHorario = false;
                if (isset($horariosDia[$diaSemana])) {
                    $passouHorario = in_array($horaInicial, $horariosDia[$diaSemana], true);
                }

                if (!$passouHorario) {
                    continue;
                }

                // Check 4: região — a região do CEP está na lista?
                $passouRegiao = in_array($regiaoId, $regra->regioes_array, true);

                if (!$passouRegiao) {
                    continue;
                }

                // Check 5: dia da semana — o dia do slot está permitido?
                $passouDia = in_array($diaSemana, $regra->dias_semana_array, true);

                if (!$passouDia) {
                    continue;
                }

                // Todos os 5 checks passaram — calcular distância
                $cacheKey = $this->buildDistanceCacheKey($regra);

                if (!isset($distanciaCache[$cacheKey])) {
                    $distanciaCache[$cacheKey] = $this->distanceService->getDistanceForRule(
                        $logradouro,
                        $regra
                    );
                }

                $distancia = $distanciaCache[$cacheKey];

                // Sem distância calculável → legado trata como 0 (float cast de null)
                // Isso faz o check de distância passar (0 <= km_maxima)
                if ($distancia === null) {
                    $distancia = 0;
                }

                // Validação final: distância dentro do raio?
                if ($distancia <= $regra->km_maxima) {
                    // Pedido mínimo para cliente novo: R$100 fixo (legado: horarios.php linha 157)
                    $pedidoMinimo = $regra->pedido_minimo;
                    if (!$comprouAntes) {
                        $pedidoMinimo = max($pedidoMinimo, 100);
                    }

                    if ($orderTotal >= $pedidoMinimo) {
                        $valido = true;
                    } else {
                        // Distância OK mas valor insuficiente — rastreia o menor mínimo
                        if ($minimo === null || $pedidoMinimo < $minimo) {
                            $minimo = $pedidoMinimo;
                        }
                    }
                }
            }

            // Slot é válido se passou em pelo menos uma regra,
            // OU se nenhuma regra cobriu a margem (comportamento default permissivo)
            if ($valido || $quantidadeRegras === 0) {
                $slotsValidos[] = $slot;
            }
        }

        // Monta avisos (replica legado: horarios.php linhas 279-298)
        $avisos = [];
        if (empty($slotsValidos)) {
            if ($regras->where('loja_id', $lojaId)->isEmpty()) {
                $avisos[] = 'Não há horários de entrega para o endereço selecionado. '
                    . 'Considere retirar seu pedido em uma de nossas lojas. '
                    . 'Em caso de dúvida, ligue para nosso atendimento (21) 3478-3000 (Rio)!';
            } else {
                if ($minimo !== null) {
                    $avisos[] = 'Para o endereço selecionado, o pedido mínimo para receber em domicílio é de R$ '
                        . number_format($minimo, 2, ',', '.') . '. '
                        . 'Considere retirar seu pedido em uma de nossas lojas. '
                        . 'Em caso de dúvida, ligue para nosso atendimento (21) 3478-3000 (Rio)!';
                } else {
                    $avisos[] = 'Não há horários de entrega para o endereço selecionado. '
                        . 'Considere retirar seu pedido em uma de nossas lojas. '
                        . 'Em caso de dúvida, ligue para nosso atendimento (21) 3478-3000 (Rio)!';
                }
            }
        }

        return [
            'slots'         => $slotsValidos,
            'avisos'        => $avisos,
            'pedido_minimo' => empty($slotsValidos) ? $minimo : null,
        ];
    }

    /**
     * Verifica se o cliente já fez algum pedido finalizado.
     * Legado: SELECT COUNT(*) FROM pedidos WHERE pessoa_id = ? AND finalizado = 1
     */
    protected function hasOrderedBefore(?int $pessoaId): bool
    {
        if (!$pessoaId) {
            return false;
        }

        return Cache::remember("customer_has_ordered_{$pessoaId}", 3600, function () use ($pessoaId) {
            return Pedido::where('pessoa_id', $pessoaId)
                ->where('finalizado', 1)
                ->exists();
        });
    }

    /**
     * Gera chave de cache para distância (evita recalcular no mesmo request).
     * Considera a origem: loja ou endereço alternativo.
     */
    protected function buildDistanceCacheKey(RegraEntrega $regra): string
    {
        if ($regra->endereco_regra_entrega_id) {
            return "end_{$regra->endereco_regra_entrega_id}";
        }

        $lojaId = $regra->loja_mais_proxima_id ?? $regra->loja_id;
        return "loja_{$lojaId}";
    }
}
