<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Tag de Produto (lê da tabela 'tags' do banco legado)
 *
 * Tags são selos visuais exibidos nos cards de produtos na loja.
 * Exemplos: "Sem Glúten", "Sem Lactose", "Novidade", "Vegano"
 *
 * Cada tag tem um tipo que define como é renderizada:
 * - IMGSP: Ícone especial (exibido sobre a imagem do produto)
 * - IMG: Ícone normal
 * - TXT: Apenas texto
 *
 * As tags são associadas aos produtos via tabela produtos_tags,
 * que permite tags temporárias (data_inicial/data_final).
 *
 * Tabela: novo.tags
 * Colunas: id, nome, icone, nordem, classe, cor_fundo, tipo_tag, cor_texto, ativa
 */
class Tag extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'tags';

    /**
     * Tabela tags não possui timestamps
     */
    public $timestamps = false;

    // ==================== RELATIONSHIPS ====================

    /**
     * Produtos que possuem esta tag
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'produtos_tags', 'tag_id', 'produto_id')
            ->withPivot('data_inicial', 'data_final');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Apenas tags ativas
     */
    public function scopeActive($query)
    {
        return $query->where('ativa', 1);
    }

    // ==================== HELPERS ====================

    /**
     * URL do ícone da tag
     *
     * Ícones ficam no servidor legado em /app/webroot/img/icons/tags/
     * Servidos via: https://img.deepfreeze.com.br/icons/tags/{icone}
     */
    public function getIconUrl(): ?string
    {
        if (empty($this->icone)) {
            return null;
        }

        $baseUrl = rtrim(config('legacy.image_base_url'), '/');
        $iconPath = rtrim(config('legacy.tag_icon_path'), '/');

        return $baseUrl . $iconPath . '/' . $this->icone;
    }

    /**
     * Verifica se é uma tag do tipo ícone especial (exibida sobre a imagem)
     * Este é o tipo usado nos cards de produto no site antigo
     */
    public function isSpecialIcon(): bool
    {
        return $this->tipo_tag === 'IMGSP';
    }
}
