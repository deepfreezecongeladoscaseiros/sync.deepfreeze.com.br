<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Marca (lê da tabela 'marcas' do banco legado)
 *
 * Tabela: novo.marcas
 * Colunas: id, marca_ativa, nome_marca, descricao_marca, imagem_marca, created, updated
 */
class Brand extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'marcas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * Mapeamento: inglês → coluna legado
     */
    protected $columnMap = [
        'name'        => 'nome_marca',
        'brand'       => 'nome_marca',
        'description' => 'descricao_marca',
        'image'       => 'imagem_marca',
        'active'      => 'marca_ativa',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Produtos desta marca
     * FK legado: marca_id
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'marca_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('marca_ativa', 1);
    }
}
