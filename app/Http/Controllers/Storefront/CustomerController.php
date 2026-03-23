<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Pedido;
use App\Models\Legacy\Pessoa;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller da área do cliente na storefront.
 *
 * Exibe histórico de pedidos e detalhes, lendo diretamente
 * da tabela 'pedidos' do banco legado.
 * Requer autenticação via guard 'customer' (tabela pessoas).
 */
class CustomerController extends Controller
{
    /**
     * Lista de pedidos do cliente logado.
     * GET /minha-conta/pedidos
     */
    public function orders(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Busca pedidos do cliente, mais recentes primeiro
        // Exclui pedidos com finalizado=0 que não têm status (carrinhos abandonados)
        $pedidos = Pedido::where('pessoa_id', $customer->id)
            ->where(function ($q) {
                // Pedidos finalizados OU pendentes que já passaram pelo checkout (têm status)
                $q->where('finalizado', '>', 0)
                  ->orWhereHas('statuses');
            })
            ->with('items')
            ->orderByDesc('id')
            ->paginate(10);

        return view('storefront.customer.orders', compact('pedidos', 'customer'));
    }

    /**
     * Detalhe de um pedido específico.
     * GET /minha-conta/pedidos/{id}
     */
    public function orderDetail(int $id): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Busca pedido garantindo que pertence ao cliente logado
        $pedido = Pedido::with(['items.product', 'statuses.status', 'formaPagamento', 'lojaRetirada'])
            ->where('id', $id)
            ->where('pessoa_id', $customer->id)
            ->first();

        if (!$pedido) {
            abort(404);
        }

        return view('storefront.customer.order-detail', compact('pedido', 'customer'));
    }
}
