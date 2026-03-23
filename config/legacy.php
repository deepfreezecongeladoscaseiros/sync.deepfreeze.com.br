<?php

/**
 * Configurações do sistema legado (siv_deepfreeze)
 *
 * O sistema legado é o ERP CakePHP 2.x onde os produtos, clientes,
 * pedidos e imagens são cadastrados. A nova loja (sync) lê desse banco.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | URL base para imagens de produtos
    |--------------------------------------------------------------------------
    |
    | Subdomínio que serve as imagens cadastradas no SIV.
    | As imagens ficam em /app/webroot/img/pratos/big/ no servidor legado.
    | O subdomínio img.deepfreeze.com.br aponta para esse diretório.
    |
    */
    'image_base_url' => env('LEGACY_IMAGE_BASE_URL', 'https://img.deepfreeze.com.br'),

    /*
    |--------------------------------------------------------------------------
    | Caminho das imagens de produtos
    |--------------------------------------------------------------------------
    |
    | Path relativo dentro do subdomínio de imagens.
    | Imagem final: {image_base_url}/{image_path}/{imagem_src}
    |
    */
    'image_path' => env('LEGACY_IMAGE_PATH', '/pratos/big'),

    /*
    |--------------------------------------------------------------------------
    | Caminho dos ícones de tags
    |--------------------------------------------------------------------------
    |
    | Tags de produtos (Sem Glúten, Sem Lactose, etc.) possuem ícones.
    | Esses ícones ficam no servidor legado.
    |
    */
    'tag_icon_path' => env('LEGACY_TAG_ICON_PATH', '/icons/tags'),

];
