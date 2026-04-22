<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Cache de Distâncias (tabela 'distancias_coordenadas' do banco legado)
 *
 * Cache permanente de distâncias reais de rota (Google Directions API)
 * entre pares de coordenadas. Chave primária composta (lat1, long1, lat2, long2).
 *
 * Compartilhada com o sistema legado — ambos leem e gravam.
 *
 * Tabela: novo.distancias_coordenadas
 * Engine: MyISAM
 */
class DistanciaCoordenada extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'distancias_coordenadas';

    public $incrementing = false;
    public $timestamps = false;

    /**
     * PK composta (lat1, long1, lat2, long2) — Eloquent não suporta PK composta nativamente.
     * Leitura: usar lookup() (WHERE explícito). Escrita: usar store() (updateOrInsert).
     * N��O usar find(), save() ou update() diretamente neste model.
     */
    protected $primaryKey = 'lat1';

    protected $fillable = [
        'lat1', 'long1', 'lat2', 'long2',
        'distancia', 'tempo', 'updated',
    ];

    protected $casts = [
        'lat1'      => 'double',
        'long1'     => 'double',
        'lat2'      => 'double',
        'long2'     => 'double',
        'distancia' => 'float',
        'tempo'     => 'integer',
    ];

    /**
     * Busca distância cacheada entre dois pontos.
     *
     * @return self|null
     */
    public static function lookup(float $lat1, float $long1, float $lat2, float $long2): ?self
    {
        return static::where('lat1', $lat1)
            ->where('long1', $long1)
            ->where('lat2', $lat2)
            ->where('long2', $long2)
            ->first();
    }

    /**
     * Grava distância no cache.
     */
    public static function store(
        float $lat1,
        float $long1,
        float $lat2,
        float $long2,
        float $distancia,
        int $tempo
    ): void {
        static::updateOrInsert(
            ['lat1' => $lat1, 'long1' => $long1, 'lat2' => $lat2, 'long2' => $long2],
            ['distancia' => $distancia, 'tempo' => $tempo, 'updated' => now()]
        );
    }
}
