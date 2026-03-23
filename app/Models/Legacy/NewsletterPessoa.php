<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Fila de E-mails (tabela 'newsletters_pessoas' do banco legado)
 *
 * Fila de e-mails transacionais e marketing processada por CRON.
 * O CRON do SIV lê registros com enviado=0 e envia via ElasticEmail.
 *
 * Tabela: novo.newsletters_pessoas
 * Engine: MyISAM
 * Charset: utf8mb4
 *
 * Uso no sync:
 * - E-mails de acompanhamento do pedido (separação, entrega, etc.)
 *   são inseridos automaticamente pelo SIV quando o status muda.
 * - Este Model permite inserir na fila manualmente se necessário.
 *
 * Campos importantes:
 * - enviado: 0=pendente, 1=enviado, NULL=não enviar
 * - newsletter_id: FK para tabela newsletters (template)
 * - texto_do_email: conteúdo HTML renderizado (pronto para envio)
 */
class NewsletterPessoa extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'newsletters_pessoas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // Status de envio
    const PENDENTE = 0;
    const ENVIADO = 1;

    protected $fillable = [
        'pessoa_id',
        'newsletter_id',
        'texto_do_email',
        'enviado',
        'momento_envio',
        'abriu',
        'momento_retorno',
    ];

    protected $casts = [
        'momento_envio' => 'datetime',
        'momento_retorno' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    // ==================== HELPERS ====================

    /**
     * Enfileira um e-mail transacional para envio pelo CRON do SIV.
     *
     * @param int $pessoaId ID da pessoa destinatária
     * @param int $newsletterId ID do template de newsletter
     * @param string|null $htmlContent Conteúdo HTML renderizado (opcional — CRON pode gerar)
     * @return static Registro criado
     */
    public static function enqueue(int $pessoaId, int $newsletterId, ?string $htmlContent = null): static
    {
        return static::create([
            'pessoa_id' => $pessoaId,
            'newsletter_id' => $newsletterId,
            'texto_do_email' => $htmlContent,
            'enviado' => self::PENDENTE,
        ]);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: E-mails pendentes de envio
     */
    public function scopePending($query)
    {
        return $query->where('enviado', self::PENDENTE);
    }

    /**
     * Scope: E-mails enviados
     */
    public function scopeSent($query)
    {
        return $query->where('enviado', self::ENVIADO);
    }
}
