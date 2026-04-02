<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Service: Consulta de CEP via API ViaCEP
 *
 * API pública, gratuita, sem autenticação.
 * Retorna estado, cidade e bairro para um CEP.
 * Cache de 24h — mesmo CEP = mesma localidade.
 */
class ViaCepService
{
    /**
     * Consulta dados de localização de um CEP via ViaCEP.
     *
     * @param string $cep CEP (com ou sem hífen)
     * @return array|null ['uf', 'localidade', 'bairro', 'logradouro'] ou null
     */
    public function lookup(string $cep): ?array
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        $cacheKey = "viacep_{$cepLimpo}";

        return Cache::remember($cacheKey, 86400, function () use ($cepLimpo) {
            try {
                $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cepLimpo}/json/");

                if ($response->failed()) {
                    return null;
                }

                $data = $response->json();

                // ViaCEP retorna {"erro": true} quando CEP não existe
                if (isset($data['erro']) && $data['erro'] === true) {
                    return null;
                }

                return [
                    'uf'         => $data['uf'] ?? null,
                    'localidade' => $data['localidade'] ?? null,
                    'bairro'     => $data['bairro'] ?? null,
                    'logradouro' => $data['logradouro'] ?? null,
                ];
            } catch (\Exception $e) {
                // Se ViaCEP estiver fora, não bloqueia — retorna null
                return null;
            }
        });
    }
}
