{{--
    Partial: Informações Nutricionais do Produto

    Exibe tabela nutricional completa padrão ANVISA.
    Dados lidos diretamente do banco legado via NutritionalInfoService.

    Variáveis:
    - $nutritionalData: array|null do NutritionalInfoService::getForProduct()
      Se não null, contém: nutri, vdr, porcao, peso_liquido, unidade, medida_caseira, tipo_etiqueta
--}}

<div class="box-tabela-nutricional mod-2">

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

        {{-- Oculta coluna de porção se porcao == 100 (seria redundante com coluna 100g) --}}
        @if($ocultarPorcao)
            <style>.ocultar-porcao { display: none; }</style>
        @endif

        <table class="tabela-padrao">
            <thead>
                <tr class="tb-title">
                    <th colspan="4">Informação Nutricional</th>
                </tr>
                <tr class="tb-subtitle">
                    <th colspan="4">
                        <span class="tb-porcao-embalagem">
                            Porções por embalagem: {{ $porcoesEmbalagem() }}
                        </span>
                        <span class="tb-porcao">
                            Porção: {{ (int) $porcao }}{{ $unidadeLabel }} ({{ $descricaoMedida() }})
                        </span>
                    </th>
                </tr>
                <tr class="tb-legend">
                    <th>&nbsp;</th>
                    <th class="text-center">100{{ $unidadeLabel }}</th>
                    <th class="text-center ocultar-porcao">{{ (int) $porcao }}{{ $unidadeLabel }}</th>
                    <th class="text-center">%VD*</th>
                </tr>
            </thead>
            <tbody>
                {{-- Valor Energético --}}
                <tr>
                    <td>Valor energético (kcal)</td>
                    <td class="text-center">{{ $arredondar($nutri[1] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(1) }}</td>
                    <td class="text-center">{{ $percentVD(1) }}</td>
                </tr>

                {{-- Carboidratos --}}
                <tr>
                    <td>Carboidratos totais (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[4] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(4) }}</td>
                    <td class="text-center">{{ $percentVD(4) }}</td>
                </tr>

                {{-- Açúcares totais e adicionados (somente se tipo_etiqueta >= 4) --}}
                @if($tipoEtiqueta >= 4)
                    <tr>
                        <td class="pl-2">Açúcares totais (g)</td>
                        <td class="text-center">{{ $arredondar($nutri[187] ?? null) }}</td>
                        <td class="text-center ocultar-porcao">{{ $valorPorcao(187) }}</td>
                        <td class="text-center">{{ $percentVD(187) }}</td>
                    </tr>
                    <tr>
                        <td class="pl-3">Açúcares adicionados (g)</td>
                        <td class="text-center">{{ $arredondar($nutri[188] ?? null) }}</td>
                        <td class="text-center ocultar-porcao">{{ $valorPorcao(188) }}</td>
                        <td class="text-center">{{ $percentVD(188) }}</td>
                    </tr>
                @endif

                {{-- Proteínas --}}
                <tr>
                    <td>Proteínas (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[2] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(2) }}</td>
                    <td class="text-center">{{ $percentVD(2) }}</td>
                </tr>

                {{-- Gorduras Totais --}}
                <tr>
                    <td>Gorduras totais (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[3] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(3) }}</td>
                    <td class="text-center">{{ $percentVD(3) }}</td>
                </tr>

                {{-- Gorduras Saturadas --}}
                <tr>
                    <td class="pl-2">Gorduras saturadas (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[185] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(185) }}</td>
                    <td class="text-center">{{ $percentVD(185) }}</td>
                </tr>

                {{-- Gorduras Trans --}}
                <tr>
                    <td class="pl-2">Gorduras trans (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[186] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(186) }}</td>
                    <td class="text-center">{{ $percentVD(186) }}</td>
                </tr>

                {{-- Fibras --}}
                <tr>
                    <td>Fibras alimentares (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[69] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(69) }}</td>
                    <td class="text-center">{{ $percentVD(69) }}</td>
                </tr>

                {{-- Sódio --}}
                <tr>
                    <td>Sódio (mg)</td>
                    <td class="text-center">{{ $arredondar($nutri[58] ?? null, 'mg') }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(58, 'mg') }}</td>
                    <td class="text-center">{{ $percentVD(58) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="caption">
            *Percentual de valores diários fornecidos pela porção, com base em uma dieta de 2.000 kcal.
        </div>

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

    @else
        {{-- Placeholder: Dados nutricionais não disponíveis --}}
        <div class="nutritional-coming-soon">
            <div class="icon">
                <i class="fa fa-leaf"></i>
            </div>
            <h4>Informações Nutricionais</h4>
            <p>Em breve, as informações nutricionais completas deste produto estarão disponíveis.</p>
        </div>
    @endif

</div>
