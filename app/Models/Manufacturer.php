<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Fabricante (lê da tabela 'fabricantes' do banco legado)
 *
 * Tabela: novo.fabricantes
 * Colunas: id, ativo, pessoa_id, nome_fantasia, razao_social, endereco,
 *          cnpj, inscricao_estadual, terceirizado, created, updated
 */
class Manufacturer extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'fabricantes';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * Mapeamento: inglês → coluna legado
     */
    protected $columnMap = [
        'trade_name'  => 'nome_fantasia',
        'legal_name'  => 'razao_social',
        'name'        => 'nome_fantasia',
        'address'     => 'endereco',
        'active'      => 'ativo',
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
     * Produtos deste fabricante
     * FK legado: fabricante_id
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'fabricante_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }
}
