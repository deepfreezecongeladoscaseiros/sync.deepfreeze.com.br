<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Service: Informações Nutricionais do Produto
 *
 * Replica a lógica do sistema legado (html/mobile/app/Model/InformacaoNutricional.php)
 * para buscar dados nutricionais diretamente do banco legado.
 *
 * Arquitetura do legado:
 * - Tabela informacoes_nutricionais: append-only, ~175K registros
 * - Valor atual = MAX(id) agrupado por (produto_id, dietpro_nutriente, origem)
 * - Origem: 'S' (SIV/calculado) ou 'J' (JOB/manual), controlado por produtos.informacao_nutricional_siv
 * - Cache de 1 hora (mesmo padrão do legado mobile)
 */
class NutritionalInfoService
{
    // IDs dos nutrientes obrigatórios (tabela dietpro_nutrientes)
    private const NUTRIENT_IDS = [1, 2, 3, 4, 58, 69, 185, 186, 187, 188];

    // Valores Diários de Referência (%VD) — ANVISA, dieta de 2000 kcal
    private const VD_REFERENCES = [
        1   => 2000,  // Energia (kcal)
        2   => 50,    // Proteínas (g)
        3   => 65,    // Gorduras totais (g)
        4   => 300,   // Carboidratos (g)
        58  => 2000,  // Sódio (mg)
        69  => 25,    // Fibras (g)
        185 => 20,    // Gorduras saturadas (g)
        186 => 2,     // Gorduras trans (g)
        187 => 0,     // Açúcares totais (sem VD)
        188 => 50,    // Açúcares adicionados (g)
    ];

    /**
     * Busca dados nutricionais completos de um produto.
     *
     * Retorna null se o produto não tem dados nutricionais suficientes (< 10 nutrientes).
     *
     * @param object $product Produto do legado (precisa de: id, informacao_nutricional_siv, porcao,
     *                        peso_liquido, unidade_medida_peso, medida_caseira, tipo_de_etiqueta)
     * @return array|null Array com chaves: 'nutri', 'vdr', 'porcao', 'peso_liquido',
     *                    'unidade', 'medida_caseira', 'tipo_etiqueta'
     */
    public function getForProduct(object $product): ?array
    {
        // Determina origem dos dados conforme flag do produto
        $origem = $product->informacao_nutricional_siv ? 'S' : 'J';

        // Busca valores nutricionais com cache de 1 hora (mesmo padrão do legado)
        $nutri = $this->getNutrientValues($product->id, $origem);

        // Guard: precisa ter exatamente 10 nutrientes (mesma regra do legado)
        if (count($nutri) !== 10) {
            return null;
        }

        // IMPORTANTE: O legado (nutri.ctp linha 169) sobrescreve porcao com peso_liquido
        // antes de renderizar: $v['porcao'] = $v['peso_liquido']
        // Ou seja, a "porção" exibida é o peso total da embalagem, não o campo porcao.
        // Devemos replicar essa mesma lógica para manter os dados idênticos ao legado.
        $pesoLiquido = (float) ($product->peso_liquido ?: 0);
        $porcao = $pesoLiquido > 0 ? (int) $pesoLiquido : (int) ($product->porcao ?: 100);

        return [
            'nutri'           => $nutri,                                    // [nutriente_id => valor_por_100g]
            'vdr'             => self::VD_REFERENCES,                       // [nutriente_id => valor_diario]
            'porcao'          => $porcao,                                   // = peso_liquido (lógica do legado)
            'peso_liquido'    => $pesoLiquido,                              // Peso total embalagem
            'unidade'         => $product->unidade_medida_peso ?: 'g',      // 'g' ou 'ml'
            'medida_caseira'  => $product->medida_caseira ?: '',            // Ex: "1 fatia"
            'tipo_etiqueta'   => (int) ($product->tipo_de_etiqueta ?: 0),   // >= 4 inclui açúcares
        ];
    }

    /**
     * Busca valores dos nutrientes para um produto.
     * Replica: InformacaoNutricional::get_valores_nutricientes_criterio()
     *
     * @return array [dietpro_nutriente_id => valor_por_100g]
     */
    private function getNutrientValues(int $productId, string $origem): array
    {
        $cacheKey = "nutri_product_{$productId}_{$origem}";

        return Cache::remember($cacheKey, 3600, function () use ($productId, $origem) {
            // Query 1: MAX(id) por nutriente — busca o registro mais recente de cada nutriente
            $maxIds = DB::connection('mysql_legacy')
                ->table('informacoes_nutricionais')
                ->selectRaw('MAX(id) as max_id')
                ->where('produto_id', $productId)
                ->where('origem', $origem)
                ->whereIn('dietpro_nutriente', self::NUTRIENT_IDS)
                ->groupBy('dietpro_nutriente')
                ->pluck('max_id')
                ->toArray();

            if (empty($maxIds)) {
                return [];
            }

            // Query 2: Busca os valores dos registros encontrados
            return DB::connection('mysql_legacy')
                ->table('informacoes_nutricionais')
                ->whereIn('id', $maxIds)
                ->pluck('valor', 'dietpro_nutriente')
                ->toArray();
        });
    }
}
