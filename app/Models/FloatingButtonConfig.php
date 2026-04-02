<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model: Configuração dos Ícones Flutuantes (WhatsApp + Instagram)
 *
 * Padrão singleton com cache (mesmo padrão de ContactSetting).
 * Armazena configurações dos botões flutuantes exibidos no storefront.
 * Banco: sync (tabela floating_button_config).
 */
class FloatingButtonConfig extends Model
{
    protected $table = 'floating_button_config';

    protected $fillable = [
        'position',
        'whatsapp_number',
        'whatsapp_message',
        'instagram_url',
    ];

    protected const CACHE_KEY = 'floating_button_config';
    protected const CACHE_TTL = 3600; // 1 hora

    /**
     * Retorna o registro único de configuração (singleton com cache).
     * Cria com valores padrão se não existir.
     */
    public static function getConfig(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $config = self::first();

            if (!$config) {
                $config = self::create([
                    'position'         => 'right',
                    'whatsapp_number'  => null,
                    'whatsapp_message' => 'Olá! Gostaria de saber mais sobre os produtos da Deep Freeze.',
                    'instagram_url'    => null,
                ]);
            }

            return $config;
        });
    }

    /**
     * Limpa o cache ao salvar.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Retorna a URL completa do WhatsApp API com número e mensagem.
     * Retorna null se o número não estiver configurado.
     */
    public function getWhatsappUrl(): ?string
    {
        if (empty($this->whatsapp_number)) {
            return null;
        }

        // Remove caracteres não numéricos
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);
        $message = $this->whatsapp_message ?? '';

        return "https://api.whatsapp.com/send?phone={$number}&text=" . urlencode($message);
    }

    /**
     * Verifica se o ícone do WhatsApp deve ser exibido.
     */
    public function showWhatsapp(): bool
    {
        return !empty($this->whatsapp_number);
    }

    /**
     * Verifica se o ícone do Instagram deve ser exibido.
     */
    public function showInstagram(): bool
    {
        return !empty($this->instagram_url);
    }

    /**
     * Verifica se pelo menos um ícone deve ser exibido.
     */
    public function hasAnyButton(): bool
    {
        return $this->showWhatsapp() || $this->showInstagram();
    }
}
