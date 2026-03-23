<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Período de Entrega (tabela 'entregas_periodos' do banco legado)
 *
 * Define horários de entrega por região e dia da semana.
 * Campo 'dia': 0=Dom, 1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex, 6=Sáb
 * Campo 'margem_hora': antecedência mínima em horas para aceitar pedido
 *
 * Tabela: novo.entregas_periodos
 * Engine: MyISAM | ~128k registros
 */
class EntregaPeriodo extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'entregas_periodos';

    public $timestamps = false;

    const DIAS_SEMANA = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

    // ==================== RELATIONSHIPS ====================

    public function regiao()
    {
        return $this->belongsTo(EntregaRegiao::class, 'entregas_regiao_id');
    }

    // ==================== HELPERS ====================

    /**
     * Nome do dia da semana
     */
    public function getDiaNomeAttribute(): string
    {
        return self::DIAS_SEMANA[$this->dia] ?? '?';
    }

    /**
     * Horário formatado (ex: "09:00 - 12:00")
     */
    public function getHorarioFormatadoAttribute(): string
    {
        $inicio = substr($this->hora_inicial, 0, 5);
        $fim = substr($this->hora_final, 0, 5);
        return "{$inicio} - {$fim}";
    }
}
