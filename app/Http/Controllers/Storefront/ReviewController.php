<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Depoimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller: Avaliações de Produtos (Storefront)
 *
 * Permite que clientes logados enviem avaliações (1-5 estrelas + texto)
 * para produtos. As avaliações ficam pendentes de aprovação (situacao_depoimento=0)
 * até que um administrador aprove no sistema legado.
 */
class ReviewController extends Controller
{
    /**
     * Grava uma nova avaliação de produto (AJAX)
     *
     * Requer autenticação via guard 'customer' (tabela pessoas).
     * A avaliação é salva com situacao_depoimento=0 (pendente de aprovação).
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Requer login de cliente (guard customer)
        $customer = auth()->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Faça login para avaliar.'], 401);
        }

        // Valida os campos obrigatórios
        $validated = $request->validate([
            'produto_id' => 'required|integer',
            'avaliacao'   => 'required|integer|min:1|max:5',
            'depoimento'  => 'required|string|max:200',
        ]);

        // Cria o depoimento no banco legado com status pendente
        Depoimento::create([
            'pessoa_id'            => $customer->id,
            'produto_id'           => $validated['produto_id'],
            'avaliacao'            => $validated['avaliacao'],
            'depoimento'           => $validated['depoimento'],
            'situacao_depoimento'  => Depoimento::SITUACAO_PENDENTE, // Pendente de aprovação
        ]);

        // Limpa cache de estrelas para atualizar quando aprovado
        Cache::forget('product_stars');

        return response()->json([
            'success' => true,
            'message' => 'Obrigado! Sua avaliação será publicada após aprovação.',
        ]);
    }
}
