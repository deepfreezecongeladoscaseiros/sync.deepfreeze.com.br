<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Estoque por Loja (lê da tabela 'otm_estoques_lojas' do banco legado)
 *
 * O estoque dos produtos é gerenciado no SIV e distribuído por loja.
 * Para a loja virtual, o estoque total é calculado como:
 *   SUM(estoque_atual_calculado - giro_balcao) por produto_id
 *
 * Colunas relevantes:
 * - produto_id: FK para produtos
 * - loja_id: FK para lojas
 * - estoque_atual: Estoque atual bruto
 * - estoque_reservado: Estoque já reservado
 * - estoque_atual_calculado: Estoque disponível calculado pelo sistema
 * - giro_balcao: Quantidade mínima para balcão/loja física
 *
 * Tabela: novo.otm_estoques_lojas
 */
class OtmEstoqueLoja extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'otm_estoques_lojas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // ==================== RELATIONSHIPS ====================

    /**
     * Produto deste registro de estoque
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }
}
