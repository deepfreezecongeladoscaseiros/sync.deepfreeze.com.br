<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model: Depoimento/Avaliação (tabela 'depoimentos' do banco legado)
 *
 * Armazena avaliações de clientes sobre produtos (1-5 estrelas + texto).
 * Apenas depoimentos aprovados (situacao_depoimento=1) são exibidos no site.
 *
 * Tabela: novo.depoimentos
 * Engine: MyISAM
 *
 * Situação:
 *   0 = Pendente de aprovação
 *   1 = Aprovado (exibido no site)
 */
class Depoimento extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'depoimentos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // Status de aprovação
    const SITUACAO_PENDENTE = 0;
    const SITUACAO_APROVADO = 1;

    protected $fillable = [
        'pessoa_id',               // FK para pessoas (cliente)
        'produto_id',              // FK para produtos
        'servico_id',              // FK para servicos (opcional)
        'depoimento',              // Texto da avaliação (max 200 chars)
        'avaliacao',               // Nota de 1 a 5 (estrelas)
        'situacao_depoimento',     // 0=pendente, 1=aprovado
    ];

    protected $casts = [
        'avaliacao'            => 'integer',
        'situacao_depoimento'  => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function produto()
    {
        return $this->belongsTo(Pedido::class, 'produto_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Apenas depoimentos aprovados
     */
    public function scopeApproved($query)
    {
        return $query->where('situacao_depoimento', self::SITUACAO_APROVADO);
    }

    /**
     * Scope: Apenas depoimentos de produtos (não serviços)
     */
    public function scopeForProducts($query)
    {
        return $query->whereNotNull('produto_id');
    }

    // ==================== MÉTODOS ESTÁTICOS ====================

    /**
     * Retorna mapa de estrelas por produto: [produto_id => ['estrelas' => X, 'total' => Y]]
     *
     * Replica ExibirDepoimento::get_media_avaliacoes_produto_id() do legado.
     * Cache de 30 minutos para evitar queries pesadas em cada request.
     *
     * @return array Mapa indexado por produto_id
     */
    public static function getStarsByProduct(): array
    {
        return Cache::remember('product_stars', 1800, function () {
            $results = self::approved()
                ->forProducts()
                ->selectRaw('produto_id, ROUND(AVG(avaliacao)) as estrelas, COUNT(*) as total')
                ->groupBy('produto_id')
                ->get();

            $map = [];
            foreach ($results as $row) {
                $map[$row->produto_id] = [
                    'estrelas' => (int) $row->estrelas,
                    'total'    => (int) $row->total,
                ];
            }

            return $map;
        });
    }

    /**
     * Retorna estrelas e total de avaliações de um produto específico.
     *
     * @return array ['estrelas' => int (1-5), 'total' => int] ou ['estrelas' => 0, 'total' => 0]
     */
    public static function getStarsForProduct(int $produtoId): array
    {
        $map = self::getStarsByProduct();

        return $map[$produtoId] ?? ['estrelas' => 0, 'total' => 0];
    }
}
