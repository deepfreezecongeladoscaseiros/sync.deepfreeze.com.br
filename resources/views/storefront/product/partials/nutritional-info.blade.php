{{--
    Partial: Informações Nutricionais do Produto

    Exibe tabela nutricional completa padrão ANVISA dentro de um card
    que segue o mesmo padrão visual dos demais blocos (group-box).

    Dados lidos diretamente do banco legado via NutritionalInfoService.

    Variáveis:
    - $nutritionalData: array|null do NutritionalInfoService::getForProduct()
      Se não null, contém: nutri, vdr, porcao, peso_liquido, unidade, medida_caseira, tipo_etiqueta
--}}

@if($nutritionalData)
    @php
        $nutri          = $nutritionalData['nutri'];
        $vdr            = $nutritionalData['vdr'];
        $porcao         = $nutritionalData['porcao'];
        $pesoLiquido    = $nutritionalData['peso_liquido'];
        $unidade        = $nutritionalData['unidade'];
        $medidaCaseira  = $nutritionalData['medida_caseira'];
        $tipoEtiqueta   = $nutritionalData['tipo_etiqueta'];
        $ocultarPorcao  = ($porcao == 100);

        // Funções de arredondamento ANVISA (Anexo III)
        // Replica: get_arredondado_info_nutri() do legado
        $arredondar = function($valor, $medida = 'g') {
            if ($valor === null) return '-';
            $valorInt = floor($valor);

            if ($valorInt == $valor) return (string) $valorInt;

            if ($valor >= 10) {
                $dif = ($valor - $valorInt) * 10;
                if (substr((string) $dif, 0, 1) >= 5) $valorInt++;
                return (string) $valorInt;
            }

            if (($valor >= 1 && $valor < 10) || ($valor < 1 && $medida == 'g')) {
                $v = number_format($valor, 3, '.', '');
                $pos = strpos($v, '.');
                if (substr($v, $pos + 2, 1) >= 5) $valor += 0.1;
                $v = substr(number_format($valor, 3, '.', ''), 0, $pos + 2);
                $v = number_format((float)$v, 1, ',', '');
                return str_replace(',0', '', $v);
            }

            if ($valor < 1 && $medida == 'mg') {
                $v = number_format($valor, 3, '.', '');
                $pos = strpos($v, '.');
                if (substr($v, $pos + 3, 1) >= 5) $valor += 0.01;
                $v = substr(number_format($valor, 3, '.', ''), 0, $pos + 3);
                $v = number_format((float)$v, 2, ',', '');
                return str_replace(',00', ',0', $v);
            }

            return number_format($valor, 1, ',', '');
        };

        // Valor por porção: (valor_100g * porcao) / 100
        $valorPorcao = function($id, $medida = 'g') use ($nutri, $porcao, $arredondar) {
            if (!isset($nutri[$id])) return '-';
            $valor = $porcao * $nutri[$id] / 100;
            return $arredondar($valor, $medida);
        };

        // %VD por porção
        $percentVD = function($id) use ($nutri, $vdr, $porcao) {
            if (!isset($nutri[$id]) || !isset($vdr[$id]) || $vdr[$id] <= 0) return '';
            $valorPorcao = $porcao * $nutri[$id] / 100;
            return round($valorPorcao * 100 / $vdr[$id]);
        };

        // Porções por embalagem (Anexo VI ANVISA)
        $porcoesEmbalagem = function() use ($pesoLiquido, $porcao) {
            if ($pesoLiquido <= 0 || $porcao <= 0) return '';
            $qtd = $pesoLiquido / $porcao;
            $qtdInt = floor($qtd);
            $dif = ($qtd - $qtdInt) * 10;
            if (substr((string) $dif, 0, 1) >= 5) $qtdInt++;
            return "Cerca de {$qtdInt}";
        };

        // Medida caseira
        $descricaoMedida = function() use ($pesoLiquido, $porcao, $medidaCaseira) {
            if (!empty($medidaCaseira)) return $medidaCaseira;
            if ($pesoLiquido == $porcao) return '1 Unidade';
            if ($pesoLiquido > $porcao) {
                $qtd = (int) round($pesoLiquido / $porcao);
                return "1/{$qtd} Unidade";
            }
            $qtd = (int) round($porcao / $pesoLiquido);
            return "{$qtd} Unidades";
        };

        $unidadeLabel = ($unidade == 'ml') ? 'ml' : 'g';
    @endphp

    {{-- Card no padrão dos demais blocos (group-box) --}}
    <div class="group-box box-nutricional">
        <h2 class="titulo-desc">
            <i class="bi bi-clipboard2-pulse"></i> Informação Nutricional
        </h2>

        {{-- Info da porção acima da tabela --}}
        <div class="nutri-porcao-info">
            <span class="nutri-porcao-embalagem">Porções por embalagem: {{ $porcoesEmbalagem() }}</span>
            <span class="nutri-porcao-detalhe">Porção: {{ (int) $porcao }}{{ $unidadeLabel }} ({{ $descricaoMedida() }})</span>
        </div>

        {{-- Oculta coluna de porção se porcao == 100 (seria redundante com coluna 100g) --}}
        @if($ocultarPorcao)
            <style>.ocultar-porcao { display: none; }</style>
        @endif

        <table class="tabela-nutri">
            <thead>
                <tr>
                    <th class="nutri-col-nome">&nbsp;</th>
                    <th class="nutri-col-valor">100{{ $unidadeLabel }}</th>
                    <th class="nutri-col-valor ocultar-porcao">{{ (int) $porcao }}{{ $unidadeLabel }}</th>
                    <th class="nutri-col-vd">%VD*</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Valor energético (kcal)</td>
                    <td>{{ $arredondar($nutri[1] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(1) }}</td>
                    <td>{{ $percentVD(1) }}</td>
                </tr>
                <tr>
                    <td>Carboidratos totais (g)</td>
                    <td>{{ $arredondar($nutri[4] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(4) }}</td>
                    <td>{{ $percentVD(4) }}</td>
                </tr>
                @if($tipoEtiqueta >= 4)
                    <tr class="nutri-sub">
                        <td>Açúcares totais (g)</td>
                        <td>{{ $arredondar($nutri[187] ?? null) }}</td>
                        <td class="ocultar-porcao">{{ $valorPorcao(187) }}</td>
                        <td>{{ $percentVD(187) }}</td>
                    </tr>
                    <tr class="nutri-sub nutri-sub-2">
                        <td>Açúcares adicionados (g)</td>
                        <td>{{ $arredondar($nutri[188] ?? null) }}</td>
                        <td class="ocultar-porcao">{{ $valorPorcao(188) }}</td>
                        <td>{{ $percentVD(188) }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Proteínas (g)</td>
                    <td>{{ $arredondar($nutri[2] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(2) }}</td>
                    <td>{{ $percentVD(2) }}</td>
                </tr>
                <tr>
                    <td>Gorduras totais (g)</td>
                    <td>{{ $arredondar($nutri[3] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(3) }}</td>
                    <td>{{ $percentVD(3) }}</td>
                </tr>
                <tr class="nutri-sub">
                    <td>Gorduras saturadas (g)</td>
                    <td>{{ $arredondar($nutri[185] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(185) }}</td>
                    <td>{{ $percentVD(185) }}</td>
                </tr>
                <tr class="nutri-sub">
                    <td>Gorduras trans (g)</td>
                    <td>{{ $arredondar($nutri[186] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(186) }}</td>
                    <td>{{ $percentVD(186) }}</td>
                </tr>
                <tr>
                    <td>Fibras alimentares (g)</td>
                    <td>{{ $arredondar($nutri[69] ?? null) }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(69) }}</td>
                    <td>{{ $percentVD(69) }}</td>
                </tr>
                <tr>
                    <td>Sódio (mg)</td>
                    <td>{{ $arredondar($nutri[58] ?? null, 'mg') }}</td>
                    <td class="ocultar-porcao">{{ $valorPorcao(58, 'mg') }}</td>
                    <td>{{ $percentVD(58) }}</td>
                </tr>
            </tbody>
        </table>

        <p class="nutri-caption">
            *Percentual de valores diários fornecidos pela porção, com base em uma dieta de 2.000 kcal.
        </p>

        {{-- Selos "Alto em" (RDC 429 ANVISA) --}}
        @php
            $altoEm = [];
            if (($nutri[188] ?? 0) >= 15) $altoEm[] = 'acucar';
            if (($nutri[185] ?? 0) >= 6) $altoEm[] = 'gordura';
            if (($nutri[58] ?? 0) >= 600) $altoEm[] = 'sodio';
        @endphp

        @if(count($altoEm) > 0)
            <div class="selos-alto-em">
                <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em.jpg" alt="Alto em" class="selo-alto">
                @foreach($altoEm as $selo)
                    <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em_{{ $selo }}.jpg" alt="Alto em {{ $selo }}" class="selo-alto">
                @endforeach
                <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em_fim.jpg" alt="" class="selo-alto-fim">
            </div>
        @endif
    </div>

@else
    {{-- Placeholder: Dados nutricionais não disponíveis --}}
    <div class="group-box box-nutricional">
        <h2 class="titulo-desc">
            <i class="bi bi-clipboard2-pulse"></i> Informação Nutricional
        </h2>
        <div class="nutritional-coming-soon">
            <p>Em breve, as informações nutricionais completas deste produto estarão disponíveis.</p>
        </div>
    </div>
@endif
