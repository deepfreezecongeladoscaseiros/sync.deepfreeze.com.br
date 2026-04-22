<?php

namespace App\Services;

use App\Models\Legacy\DistanciaCoordenada;
use App\Models\Legacy\EnderecoRegraEntrega;
use App\Models\Legacy\Loja;
use App\Models\Legacy\Logradouro;
use App\Models\Legacy\RegraEntrega;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de Cálculo de Distância.
 *
 * Calcula distância real de rota entre dois pontos, usando 3 camadas:
 * 1. Cache permanente (distancias_coordenadas) — compartilhado com o legado
 * 2. Google Directions API — distância real por estrada
 * 3. Haversine — fallback de emergência (linha reta)
 *
 * A API key do Google é lida do .env (GOOGLE_DIRECTIONS_API_KEY).
 */
class DistanceService
{
    /**
     * Calcula distância em km entre dois pontos.
     *
     * @return float|null Distância em km, ou null se impossível calcular
     */
    public function getDistance(float $lat1, float $lng1, float $lat2, float $lng2): ?float
    {
        // Coordenadas inválidas
        if ($lat1 == 0 || $lng1 == 0 || $lat2 == 0 || $lng2 == 0) {
            return null;
        }

        // 1. Cache do banco legado (distancias_coordenadas)
        $cached = DistanciaCoordenada::lookup($lat1, $lng1, $lat2, $lng2);
        if ($cached) {
            return (float) $cached->distancia;
        }

        // 2. Google Directions API
        $apiResult = $this->callGoogleDirections($lat1, $lng1, $lat2, $lng2);
        if ($apiResult !== null) {
            // Grava no cache para reutilização (inclusive pelo legado)
            DistanciaCoordenada::store(
                $lat1, $lng1, $lat2, $lng2,
                $apiResult['distancia'],
                $apiResult['tempo']
            );

            return $apiResult['distancia'];
        }

        // 3. Haversine (fallback de emergência)
        Log::warning('DistanceService: Google API falhou, usando Haversine', [
            'origin'      => [$lat1, $lng1],
            'destination' => [$lat2, $lng2],
        ]);

        return $this->haversine($lat1, $lng1, $lat2, $lng2);
    }

    /**
     * Calcula distância entre o logradouro do cliente e a origem da regra.
     *
     * Determina o ponto de origem conforme a regra:
     * - Se endereco_regra_entrega_id: usa coordenadas do endereço alternativo
     * - Se loja_mais_proxima_id: usa coordenadas dessa loja em vez da loja da regra
     * - Caso padrão: usa coordenadas da loja da regra
     *
     * @return float|null Distância em km
     */
    public function getDistanceForRule(Logradouro $logradouro, RegraEntrega $regra): ?float
    {
        $destLat = (float) $logradouro->latitude;
        $destLng = (float) $logradouro->longitude;

        if ($destLat == 0 || $destLng == 0) {
            return null;
        }

        // Caminho A: ponto de origem alternativo (ex: pedágio, CD)
        if ($regra->endereco_regra_entrega_id) {
            $enderecoRegra = EnderecoRegraEntrega::find($regra->endereco_regra_entrega_id);

            if ($enderecoRegra) {
                return $this->getDistance(
                    $enderecoRegra->latitude,
                    $enderecoRegra->longitude,
                    $destLat,
                    $destLng
                );
            }

            return null;
        }

        // Caminho B: loja como ponto de origem
        $lojaOrigemId = $regra->loja_mais_proxima_id ?? $regra->loja_id;
        $lojaOrigem = Loja::find($lojaOrigemId);

        if (!$lojaOrigem || !$lojaOrigem->latitude || !$lojaOrigem->longitude) {
            return null;
        }

        return $this->getDistance(
            (float) $lojaOrigem->latitude,
            (float) $lojaOrigem->longitude,
            $destLat,
            $destLng
        );
    }

    /**
     * Chama Google Directions API para obter distância real por estrada.
     *
     * @return array|null ['distancia' => float km, 'tempo' => int minutos]
     */
    protected function callGoogleDirections(float $lat1, float $lng1, float $lat2, float $lng2): ?array
    {
        $apiKey = config('services.google.directions_api_key');

        if (!$apiKey) {
            Log::warning('DistanceService: GOOGLE_DIRECTIONS_API_KEY não configurada');
            return null;
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/directions/json', [
                'key'         => $apiKey,
                'mode'        => 'driving',
                'origin'      => "{$lat1},{$lng1}",
                'destination' => "{$lat2},{$lng2}",
            ]);

            if (!$response->successful()) {
                Log::warning('DistanceService: Google API HTTP error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
                Log::warning('DistanceService: Google API sem rota', [
                    'status' => $data['status'] ?? 'unknown',
                ]);
                return null;
            }

            $leg = $data['routes'][0]['legs'][0];

            return [
                'distancia' => round($leg['distance']['value'] / 1000, 2),
                'tempo'     => (int) round($leg['duration']['value'] / 60),
            ];
        } catch (\Exception $e) {
            Log::error('DistanceService: Exceção na Google API', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Fórmula de Haversine — distância em linha reta (fallback).
     */
    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
