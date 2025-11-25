<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model para mensagens de contato recebidas.
 *
 * Armazena mensagens enviadas pelo formulário de contato da loja.
 * Permite visualização e gerenciamento pelo admin.
 */
class ContactMessage extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'ip_address',
        'user_agent',
        'read',
        'read_at',
    ];

    /**
     * Casts de atributos.
     */
    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Scope para mensagens não lidas.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('read', false);
    }

    /**
     * Scope para mensagens lidas.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('read', true);
    }

    /**
     * Scope para ordenar por mais recentes primeiro.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Marca a mensagem como lida.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->read) {
            return true;
        }

        return $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Marca a mensagem como não lida.
     *
     * @return bool
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Retorna telefone formatado.
     *
     * @return string|null
     */
    public function getFormattedPhone(): ?string
    {
        if (empty($this->phone)) {
            return null;
        }

        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $this->phone);

        // Formata conforme quantidade de dígitos
        if (strlen($phone) === 11) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 5),
                substr($phone, 7)
            );
        } elseif (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 4),
                substr($phone, 6)
            );
        }

        return $this->phone;
    }

    /**
     * Retorna preview da mensagem (primeiros 100 caracteres).
     *
     * @param int $length
     * @return string
     */
    public function getMessagePreview(int $length = 100): string
    {
        if (strlen($this->message) <= $length) {
            return $this->message;
        }

        return substr($this->message, 0, $length) . '...';
    }

    /**
     * Verifica se é uma mensagem recente (últimas 24h).
     *
     * @return bool
     */
    public function isRecent(): bool
    {
        return $this->created_at->isAfter(now()->subDay());
    }

    /**
     * Retorna contagem de mensagens não lidas.
     *
     * @return int
     */
    public static function unreadCount(): int
    {
        return self::unread()->count();
    }
}
