<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Regra de Entrega (tabela 'regras_entregas' do banco legado)
 *
 * Define regras de logística por loja: pedido mínimo, raio máximo,
 * dias da semana, horários e margens de antecedência cobertas.
 *
 * Campos CSV/JSON:
 * - regioes_ids: JSON array de IDs de entregas_regioes (ex: ["41","13","7"])
 * - margens: CSV de margem_hora cobertos (ex: "14,28,5,35,30,44")
 * - dias_da_semana: CSV de dias 0-6 (ex: "0,1,2,3,4,5,6")
 * - horarios_dias: JSON array indexado por dia, cada elemento é CSV de horas (ex: ["0,7,8,...,23", ...])
 *
 * Tabela: novo.regras_entregas
 * Engine: MyISAM | ~30 regras ativas (3 faixas por loja)
 */
class RegraEntrega extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'regras_entregas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $casts = [
        'pedido_minimo' => 'float',
        'km_maxima'     => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function lojaMaisProxima()
    {
        return $this->belongsTo(Loja::class, 'loja_mais_proxima_id');
    }

    public function enderecoRegraEntrega()
    {
        return $this->belongsTo(EnderecoRegraEntrega::class, 'endereco_regra_entrega_id');
    }

    // ==================== ACCESSORS ====================

    /**
     * Retorna regioes_ids como array de inteiros.
     */
    public function getRegioesArrayAttribute(): array
    {
        $json = json_decode($this->regioes_ids, true);

        return $json ? array_map('intval', $json) : [];
    }

    /**
     * Retorna margens como array de inteiros.
     */
    public function getMargensArrayAttribute(): array
    {
        if (empty($this->margens)) {
            return [];
        }

        return array_map('intval', explode(',', $this->margens));
    }

    /**
     * Retorna dias_da_semana como array de inteiros.
     */
    public function getDiasSemanaArrayAttribute(): array
    {
        if (empty($this->dias_da_semana)) {
            return [];
        }

        return array_map('intval', explode(',', $this->dias_da_semana));
    }

    /**
     * Retorna horarios_dias como array associativo: dia => array de horas permitidas.
     * Índice: 0=Dom, 1=Seg, ..., 6=Sáb
     */
    public function getHorariosDiasArrayAttribute(): array
    {
        $json = json_decode($this->horarios_dias, true);

        if (!$json || !is_array($json)) {
            return [];
        }

        $result = [];
        foreach ($json as $dia => $horasCsv) {
            $result[(int) $dia] = ($horasCsv !== '' && $horasCsv !== null)
                ? array_map('intval', explode(',', $horasCsv))
                : [];
        }

        return $result;
    }

    // ==================== SCOPES ====================

    /**
     * Regras de uma loja específica.
     */
    public function scopeForStore($query, int $lojaId)
    {
        return $query->where('loja_id', $lojaId);
    }
}
