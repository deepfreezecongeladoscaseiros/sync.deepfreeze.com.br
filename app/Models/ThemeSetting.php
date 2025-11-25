<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model para gerenciar configurações de tema da loja
 *
 * Armazena cores, fontes e configurações de layout em formato JSON.
 * Permite múltiplos temas, mas apenas um pode estar ativo por vez.
 *
 * @property int $id
 * @property string $name - Nome do tema
 * @property bool $is_active - Se o tema está ativo
 * @property string|null $logo_path - Caminho da logomarca (altura min 120px)
 * @property string $logo_alt - Texto alternativo da logo (acessibilidade)
 * @property bool $top_bar_enabled - Se a barra de anúncios (top bar) está ativa
 * @property string|null $top_bar_text - Texto da top bar (aceita HTML)
 * @property string $top_bar_bg_color - Cor de fundo da top bar
 * @property string $top_bar_text_color - Cor do texto da top bar
 * @property array $colors - Configurações de cores (JSON)
 * @property array|null $fonts - Configurações de fontes (JSON)
 * @property array|null $layout - Configurações de layout (JSON)
 */
class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'logo_path',
        'logo_alt',
        'top_bar_enabled',
        'top_bar_text',
        'top_bar_bg_color',
        'top_bar_text_color',
        'colors',
        'fonts',
        'layout',
    ];

    /**
     * Cast automático de JSON para array e boolean
     */
    protected $casts = [
        'colors' => 'array',
        'fonts' => 'array',
        'layout' => 'array',
        'is_active' => 'boolean',
        'top_bar_enabled' => 'boolean',
    ];

    /**
     * Scope para buscar apenas o tema ativo
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ativa este tema e desativa todos os outros
     *
     * Garante que apenas um tema esteja ativo por vez.
     * Invalida o cache após a mudança.
     *
     * @return bool
     */
    public function activate(): bool
    {
        // Inicia transação para garantir consistência
        \DB::transaction(function () {
            // Desativa todos os temas
            self::query()->update(['is_active' => false]);

            // Ativa este tema
            $this->is_active = true;
            $this->save();
        });

        // Invalida o cache de cores
        Cache::forget('theme.active');
        Cache::forget('theme.colors');

        return true;
    }

    /**
     * Retorna uma cor específica do tema
     *
     * Usa notação dot para acessar valores aninhados.
     * Ex: getColor('brand.primary') retorna $colors['brand']['primary']
     *
     * @param string $key - Chave da cor (pode usar notação dot)
     * @param string $default - Valor padrão se a cor não existir
     * @return string
     */
    public function getColor(string $key, string $default = '#000000'): string
    {
        return data_get($this->colors, $key, $default);
    }

    /**
     * Atualiza uma cor específica do tema
     *
     * @param string $key - Chave da cor (pode usar notação dot)
     * @param string $value - Valor da cor (hex, rgb, rgba)
     * @return bool
     */
    public function setColor(string $key, string $value): bool
    {
        $colors = $this->colors;
        data_set($colors, $key, $value);
        $this->colors = $colors;

        $saved = $this->save();

        // Invalida cache se for o tema ativo
        if ($saved && $this->is_active) {
            Cache::forget('theme.colors');
        }

        return $saved;
    }
}
