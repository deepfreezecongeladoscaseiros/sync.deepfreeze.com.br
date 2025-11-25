<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model para Páginas Internas (Institucionais)
 *
 * Gerencia páginas dinâmicas do site como:
 * - Quem Somos, Política de Privacidade, FAQ, Contato, etc.
 *
 * As páginas têm rotas dinâmicas criadas em runtime baseadas no slug.
 */
class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Lista de slugs reservados que não podem ser usados
     *
     * URLs reservadas pelo sistema (rotas existentes)
     */
    public static array $reservedSlugs = [
        'admin',
        'login',
        'logout',
        'register',
        'cadastro',
        'password',
        'email',
        'api',
        'docs',
        'home',
        'produtos',
        'produto',
        'kits',
        'kit',
        'carrinho',
        'cart',
        'checkout',
        'pedido',
        'order',
        'minha-conta',
        'my-account',
        'perfil',
        'profile',
        'contato',      // Página de contato (rota fixa)
        'css',
        'js',
        'images',
        'storage',
        'assets',
    ];

    /**
     * Scope: apenas páginas ativas
     *
     * Exemplo: Page::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: busca por slug
     *
     * Exemplo: Page::bySlug('quem-somos')->first()
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Retorna o título SEO (usa meta_title ou title como fallback)
     *
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * Retorna a URL pública da página
     *
     * @return string
     */
    public function getUrl(): string
    {
        return url($this->slug);
    }

    /**
     * Verifica se um slug é reservado (não pode ser usado)
     *
     * @param string $slug
     * @return bool
     */
    public static function isReservedSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::$reservedSlugs);
    }

    /**
     * Gera um slug único a partir do título
     *
     * @param string $title
     * @param int|null $excludeId ID da página a excluir da verificação (para updates)
     * @return string
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        // Verifica se o slug está disponível (não é reservado e não existe no banco)
        while (
            self::isReservedSlug($slug) ||
            self::when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot do model - auto-gera slug se não fornecido
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-gera slug ao criar se não fornecido
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = self::generateUniqueSlug($page->title);
            }
        });
    }
}
