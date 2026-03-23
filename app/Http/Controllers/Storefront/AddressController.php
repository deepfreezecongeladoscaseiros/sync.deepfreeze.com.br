<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Endereco;
use App\Models\Legacy\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de endereços do cliente (AJAX).
 *
 * Gerencia endereços salvos na tabela 'enderecos' do banco legado.
 * Usado no checkout para:
 * - Listar endereços salvos do cliente logado
 * - Criar novo endereço
 * - Marcar endereço como último usado
 */
class AddressController extends Controller
{
    /**
     * Lista endereços do cliente logado.
     * Retorna JSON para popular dropdown no checkout.
     */
    public function list(): JsonResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return response()->json(['addresses' => []], 401);
        }

        $enderecos = Endereco::where('pessoa_id', $customer->id)
            ->where('ativo', 1)
            ->orderByDesc('end_principal')
            ->orderByDesc('ultimo_endereco_usado')
            ->get()
            ->map(function ($endereco) {
                return [
                    'id'           => $endereco->id,
                    'zip_code'     => $endereco->cep,
                    'street'       => $endereco->logradouro,
                    'number'       => $endereco->logradouro_complemento_numero,
                    'complement'   => $endereco->logradouro_complemento,
                    'neighborhood' => $endereco->bairro,
                    'city'         => $endereco->cidade,
                    'state'        => $endereco->uf,
                    'notes'        => $endereco->observacao,
                    'is_primary'   => (bool) $endereco->end_principal,
                    'is_last_used' => (bool) $endereco->ultimo_endereco_usado,
                    'full_address' => $endereco->full_address,
                ];
            });

        return response()->json(['addresses' => $enderecos]);
    }

    /**
     * Cria novo endereço para o cliente logado.
     */
    public function store(Request $request): JsonResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return response()->json(['error' => 'Não autenticado.'], 401);
        }

        $validated = $request->validate([
            'zip_code'     => ['required', 'string', 'max:10'],
            'street'       => ['required', 'string', 'max:190'],
            'number'       => ['required', 'string', 'max:40'],
            'complement'   => ['nullable', 'string', 'max:80'],
            'neighborhood' => ['required', 'string', 'max:90'],
            'city'         => ['required', 'string', 'max:90'],
            'state'        => ['required', 'string', 'size:2'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        // Desmarca "último usado" dos endereços anteriores
        Endereco::where('pessoa_id', $customer->id)
            ->update(['ultimo_endereco_usado' => 0]);

        $endereco = Endereco::create([
            'pessoa_id'                    => $customer->id,
            'cep'                          => $validated['zip_code'],
            'logradouro'                   => $validated['street'],
            'logradouro_complemento_numero' => $validated['number'],
            'logradouro_complemento'       => $validated['complement'] ?? null,
            'bairro'                       => $validated['neighborhood'],
            'cidade'                       => $validated['city'],
            'uf'                           => strtoupper($validated['state']),
            'observacao'                   => $validated['notes'] ?? null,
            'end_principal'                => 0,
            'ativo'                        => 1,
            'ultimo_endereco_usado'        => 1,
        ]);

        return response()->json([
            'success' => true,
            'address' => [
                'id'           => $endereco->id,
                'zip_code'     => $endereco->cep,
                'street'       => $endereco->logradouro,
                'number'       => $endereco->logradouro_complemento_numero,
                'complement'   => $endereco->logradouro_complemento,
                'neighborhood' => $endereco->bairro,
                'city'         => $endereco->cidade,
                'state'        => $endereco->uf,
                'full_address' => $endereco->full_address,
            ],
        ]);
    }
}
