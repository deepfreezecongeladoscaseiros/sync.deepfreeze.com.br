<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsent extends Model
{
    /**
     * Nome da tabela (singular)
     */
    protected $table = 'cookie_consent';

    /**
     * Campos permitidos para mass assignment
     */
    protected $fillable = [
        'active',
        'message_text',
        'button_label',
        'button_bg_color',
        'button_text_color',
        'button_hover_bg_color',
    ];

    /**
     * Casting de tipos
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Scope: retorna apenas se ativo
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Retorna o registro único de configuração
     * Cria um se não existir
     */
    public static function getConfig()
    {
        $config = self::first();

        // Se não existe, cria com valores padrão
        if (!$config) {
            $config = self::create([
                'active' => true,
                'message_text' => 'Nós usamos cookies e outras tecnologias semelhantes para melhorar a sua experiência em nossos serviços. Ao utilizar nossos serviços, você concorda com nossas <a href="/politica-de-privacidade">Políticas de Privacidade</a> e <a href="/politica-de-cookies">Cookies.</a>',
                'button_label' => 'Aceito',
                'button_bg_color' => '#FFA733',
                'button_text_color' => '#FFFFFF',
                'button_hover_bg_color' => '#013E3B',
            ]);
        }

        return $config;
    }
}
