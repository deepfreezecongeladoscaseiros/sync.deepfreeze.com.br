<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Model: Sessão de Pedido (tabela 'sessoes_pedidos' do banco legado)
 *
 * Tabela auxiliar para geração de IDs únicos de sessão.
 * O ID é um inteiro aleatório (pode ser negativo!) inserido manualmente.
 * Não usa AUTO_INCREMENT — o valor é gerado por mt_rand().
 *
 * Tabela: novo.sessoes_pedidos
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Replica a lógica de Pedido::getSessao() do CakePHP legado.
 */
class SessaoPedido extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'sessoes_pedidos';

    // Tabela não tem auto_increment — o ID é o valor da sessão
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'created'];

    /**
     * Gera uma sessão única e insere na tabela.
     *
     * Replica o comportamento do legado:
     * 1. Gera inteiro aleatório entre -999999999 e 999999999
     * 2. Verifica se já existe em sessoes_pedidos ou pedidos
     * 3. Se único, insere e retorna
     * 4. Se duplicado, tenta novamente (loop)
     *
     * @return int ID da sessão gerada
     */
    public static function generate(): int
    {
        $connection = DB::connection('mysql_legacy');
        $maxAttempts = 100; // Proteção contra loop infinito

        for ($i = 0; $i < $maxAttempts; $i++) {
            $sessaoId = mt_rand(-999999999, 999999999);

            // Verifica se já existe em pedidos
            $existsInPedidos = $connection
                ->table('pedidos')
                ->where('sessao', $sessaoId)
                ->exists();

            if ($existsInPedidos) {
                continue;
            }

            // Verifica se já existe em sessoes_pedidos
            $existsInSessoes = $connection
                ->table('sessoes_pedidos')
                ->where('id', $sessaoId)
                ->exists();

            if ($existsInSessoes) {
                continue;
            }

            // Insere a sessão
            $inserted = $connection->table('sessoes_pedidos')->insert([
                'id' => $sessaoId,
                'created' => now(),
            ]);

            if ($inserted) {
                return $sessaoId;
            }
        }

        // Fallback: se após 100 tentativas não conseguiu, usa timestamp como base
        $sessaoId = (int) -(time() . mt_rand(100, 999));
        $connection->table('sessoes_pedidos')->insert([
            'id' => $sessaoId,
            'created' => now(),
        ]);

        return $sessaoId;
    }
}
