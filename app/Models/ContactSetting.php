<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Model para configurações da página de Contato.
 *
 * Armazena informações de contato, textos introdutórios,
 * configurações de envio de e-mail e SEO.
 *
 * Usa padrão Singleton - apenas um registro existe no banco.
 */
class ContactSetting extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'page_title',
        'intro_text',
        'whatsapp',
        'whatsapp_display',
        'email',
        'business_hours',
        'form_recipient_email',
        'form_subject',
        'banner_image',
        'meta_title',
        'meta_description',
        'active',
    ];

    /**
     * Casts de atributos.
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Chave de cache para as configurações.
     */
    protected const CACHE_KEY = 'contact_settings';

    /**
     * Tempo de cache em segundos (1 hora).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Obtém as configurações de contato (singleton com cache).
     *
     * Se não existir registro, cria um com valores padrão.
     *
     * @return self
     */
    public static function getSettings(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = self::first();

            // Se não existe, cria com valores padrão
            if (!$settings) {
                $settings = self::create([
                    'page_title' => 'Contato',
                    'intro_text' => 'Entre em contato via telefone ou envie-nos a sua mensagem, depoimento, ou sugestões abaixo. Em breve, entraremos em contato.',
                    'whatsapp' => '552134783000',
                    'whatsapp_display' => '(21) 3478-3000',
                    'email' => 'contato@deepfreeze.com.br',
                    'business_hours' => "Horário de atendimento de segunda à sexta das 7h às 16h\nTambém efetuamos plantão em 2 domingos do mês",
                    'form_recipient_email' => 'contato@deepfreeze.com.br',
                    'form_subject' => 'Nova mensagem de contato - Deep Freeze',
                    'active' => true,
                ]);
            }

            return $settings;
        });
    }

    /**
     * Limpa o cache das configurações.
     *
     * Deve ser chamado após salvar alterações.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Boot do model - limpa cache ao salvar.
     */
    protected static function boot()
    {
        parent::boot();

        // Limpa cache ao criar/atualizar
        static::saved(function () {
            self::clearCache();
        });

        // Limpa cache ao deletar
        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     * Retorna URL completa do WhatsApp para API.
     *
     * @param string|null $message Mensagem pré-definida (opcional)
     * @return string|null
     */
    public function getWhatsAppUrl(?string $message = 'Olá!'): ?string
    {
        if (empty($this->whatsapp)) {
            return null;
        }

        // Remove caracteres não numéricos do WhatsApp
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp);

        return "https://api.whatsapp.com/send?phone={$number}&text=" . urlencode($message);
    }

    /**
     * Retorna URL da imagem do banner.
     *
     * @return string|null
     */
    public function getBannerUrl(): ?string
    {
        if (empty($this->banner_image)) {
            // Retorna banner padrão local se não houver imagem configurada
            return asset('storefront/img/ban-interna-1.jpg');
        }

        return Storage::url($this->banner_image);
    }

    /**
     * Retorna título para SEO.
     *
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->page_title;
    }

    /**
     * Verifica se a página está ativa.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Retorna horário de atendimento como array de linhas.
     *
     * @return array
     */
    public function getBusinessHoursLines(): array
    {
        if (empty($this->business_hours)) {
            return [];
        }

        return array_filter(array_map('trim', explode("\n", $this->business_hours)));
    }

    /**
     * Relacionamento com mensagens recebidas.
     *
     * Nota: Não é um relacionamento direto, mas método de conveniência.
     */
    public function messages()
    {
        return ContactMessage::query();
    }
}
