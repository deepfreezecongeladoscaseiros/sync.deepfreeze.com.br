<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Categoria de Produto (lê da tabela 'categorias' do banco legado)
 *
 * As categorias são gerenciadas no SIV (sistema legado).
 *
 * Tabela: novo.categorias
 * Colunas relevantes: id, nome, slug, slug_underscore, descricao, cor, cor_fundo,
 *                     cor_texto, nordem, site, imagem_menu, imagem_topo_deepfreeze
 *
 * A coluna 'site' indica se a categoria aparece na loja (1=sim, 0=não).
 * A coluna 'slug' é usada para URLs amigáveis na nova loja.
 */
class Category extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'categorias';

    /**
     * Legado usa 'created' e 'modified' (não created_at/updated_at)
     */
    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    /**
     * Mapeamento: nome inglês (Blade) → coluna real (banco legado)
     */
    protected $columnMap = [
        'name'        => 'nome',
        'description' => 'descricao',
        'color'       => 'cor',
        'order'       => 'nordem',
    ];

    /**
     * Resolve atributos mapeados
     */
    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Produtos desta categoria
     * FK legado: categoria_id
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'categoria_id');
    }

    /**
     * Metatags (polimórfico - tabela do banco sync, não do legado)
     */
    public function metatags()
    {
        return $this->morphMany(Metatag::class, 'metatagable');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Categorias visíveis no site
     * Coluna legado: site (int, 1=visível)
     */
    public function scopeVisibleOnSite($query)
    {
        return $query->where('site', 1);
    }

    /**
     * Scope: Ordenar por ordem de exibição
     * Coluna legado: nordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('nordem', 'asc');
    }
}
