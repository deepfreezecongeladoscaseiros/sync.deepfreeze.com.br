<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Imagem de Produto (lê da tabela 'produtos_imagens' do banco legado)
 *
 * As imagens são cadastradas e gerenciadas no SIV (sistema legado).
 * O upload é feito no SIV e os arquivos ficam em:
 *   /app/webroot/img/pratos/big/{imagem_src}
 *
 * Na nova loja, as imagens são servidas via subdomínio:
 *   https://img.deepfreeze.com.br/pratos/big/{imagem_src}
 *
 * Tabela: novo.produtos_imagens
 * Colunas: id, produto_id, imagem_src, esta_na_embalagem, posicao, ativa, created, updated
 */
class ProductImage extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'produtos_imagens';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * Produto dono desta imagem
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }

    /**
     * URL completa da imagem via subdomínio img.deepfreeze.com.br
     *
     * @param string $size Tamanho (futuro: thumb, medium, large)
     * @return string
     */
    public function getUrl(string $size = 'medium'): string
    {
        if (empty($this->imagem_src)) {
            return asset('storefront/img/no-image.jpg');
        }

        $baseUrl = rtrim(config('legacy.image_base_url'), '/');
        $imagePath = rtrim(config('legacy.image_path'), '/');

        return $baseUrl . $imagePath . '/' . $this->imagem_src;
    }
}
