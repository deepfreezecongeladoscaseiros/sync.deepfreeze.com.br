{{--
    Partial: Informações Nutricionais do Produto

    Exibe a tabela nutricional completa seguindo padrão ANVISA.
    Se não houver dados nutricionais, exibe mensagem "Em breve".

    Variáveis:
    - $product: Product model com relacionamento nutritionalInfo carregado
--}}

<div class="box-tabela-nutricional mod-2">

    @if($product->nutritionalInfo && $product->nutritionalInfo->hasData())
        {{-- Tabela Nutricional Completa --}}
        @php
            $nutri = $product->nutritionalInfo;
        @endphp

        <table class="tabela-padrao">
            <thead>
                <tr class="tb-title">
                    <th colspan="3">Informação Nutricional</th>
                </tr>
                <tr class="tb-subtitle">
                    <th colspan="3">
                        @if($nutri->servings_per_container)
                            <span class="tb-porcao-embalagem">
                                Porções por embalagem: {{ $nutri->servings_per_container }}
                            </span>
                        @endif
                        <span class="tb-porcao">
                            Porção: {{ $nutri->portion_label }}
                        </span>
                    </th>
                </tr>
                <tr class="tb-legend">
                    <th>&nbsp;</th>
                    <th class="text-center">{{ $nutri->portion_size ?? 100 }}{{ $nutri->portion_unit }}</th>
                    <th class="text-center">%VD*</th>
                </tr>
            </thead>
            <tbody>
                {{-- Valor Energético --}}
                @if($nutri->energy_kcal)
                    <tr>
                        <td>Valor energético (kcal)</td>
                        <td class="text-center" data-th="{{ $nutri->portion_size ?? 100 }}{{ $nutri->portion_unit }}">
                            {{ $nutri->formatValue($nutri->energy_kcal, '', 0) }}
                        </td>
                        <td class="text-center" data-th="%VD*">
                            {{ $nutri->formatDV($nutri->dv_energy) }}
                        </td>
                    </tr>
                @endif

                {{-- Carboidratos --}}
                @if($nutri->carbohydrates !== null)
                    <tr>
                        <td>Carboidratos totais (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->carbohydrates) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_carbohydrates) }}</td>
                    </tr>
                @endif

                {{-- Açúcares Totais --}}
                @if($nutri->total_sugars !== null)
                    <tr>
                        <td class="pl-2">Açúcares totais (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->total_sugars) }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif

                {{-- Açúcares Adicionados --}}
                @if($nutri->added_sugars !== null)
                    <tr>
                        <td class="pl-2">Açúcares adicionados (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->added_sugars) }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif

                {{-- Proteínas --}}
                @if($nutri->proteins !== null)
                    <tr>
                        <td>Proteínas (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->proteins) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_proteins) }}</td>
                    </tr>
                @endif

                {{-- Gorduras Totais --}}
                @if($nutri->total_fat !== null)
                    <tr>
                        <td>Gorduras totais (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->total_fat) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_total_fat) }}</td>
                    </tr>
                @endif

                {{-- Gorduras Saturadas --}}
                @if($nutri->saturated_fat !== null)
                    <tr>
                        <td class="pl-2">Gorduras saturadas (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->saturated_fat) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_saturated_fat) }}</td>
                    </tr>
                @endif

                {{-- Gorduras Trans --}}
                @if($nutri->trans_fat !== null)
                    <tr>
                        <td class="pl-2">Gorduras trans (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->trans_fat) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_trans_fat) }}</td>
                    </tr>
                @endif

                {{-- Colesterol --}}
                @if($nutri->cholesterol !== null)
                    <tr>
                        <td>Colesterol (mg)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->cholesterol, 'mg') }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif

                {{-- Fibra Alimentar --}}
                @if($nutri->dietary_fiber !== null)
                    <tr>
                        <td>Fibra alimentar (g)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->dietary_fiber) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_dietary_fiber) }}</td>
                    </tr>
                @endif

                {{-- Sódio --}}
                @if($nutri->sodium !== null)
                    <tr>
                        <td>Sódio (mg)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->sodium, 'mg', 0) }}</td>
                        <td class="text-center">{{ $nutri->formatDV($nutri->dv_sodium) }}</td>
                    </tr>
                @endif

                {{-- Cálcio --}}
                @if($nutri->calcium !== null)
                    <tr>
                        <td>Cálcio (mg)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->calcium, 'mg', 0) }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif

                {{-- Ferro --}}
                @if($nutri->iron !== null)
                    <tr>
                        <td>Ferro (mg)</td>
                        <td class="text-center">{{ $nutri->formatValue($nutri->iron, 'mg') }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif

            </tbody>
        </table>

        <div class="caption">
            *Percentual de valores diários fornecidos pela porção, com base em uma dieta de 2.000 kcal.
        </div>

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
