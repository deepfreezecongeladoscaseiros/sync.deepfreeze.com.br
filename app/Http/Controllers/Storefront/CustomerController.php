<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Endereco;
use App\Models\Legacy\Pedido;
use App\Models\Legacy\Pessoa;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Dashboard da área do cliente.
     * GET /minha-conta
     */
    public function dashboard(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Conta endereços ativos do cliente
        $addressCount = Endereco::where('pessoa_id', $customer->id)->where('ativo', 1)->count();

        // Conta pedidos finalizados (exclui carrinhos abandonados)
        $orderCount = Pedido::where('pessoa_id', $customer->id)->where('finalizado', '>', 0)->count();

        $activeMenu = 'dashboard';

        return view('storefront.customer.dashboard', compact('customer', 'addressCount', 'orderCount', 'activeMenu'));
    }

    /**
     * Exibe formulário de edição de perfil.
     * GET /minha-conta/dados
     */
    public function profile(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $activeMenu = 'profile';

        return view('storefront.customer.profile', compact('customer', 'activeMenu'));
    }

    /**
     * Atualiza dados do perfil no banco legado.
     * PUT /minha-conta/dados
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'nome'                => 'required|string|max:255',
            'email_primario'      => 'required|email|max:255',
            'cpf'                 => 'nullable|string|max:50',
            'telefone_celular'    => 'nullable|string|max:15',
            'nascimento'          => 'nullable|string|max:10',
            'sexo'                => 'nullable|in:M,F,O',
        ], [
            'nome.required'           => 'O nome é obrigatório.',
            'email_primario.required' => 'O e-mail é obrigatório.',
            'email_primario.email'    => 'Informe um e-mail válido.',
        ]);

        // Converte data de DD/MM/YYYY para YYYY-MM-DD
        $nascimento = null;
        if (!empty($validated['nascimento'])) {
            $parts = explode('/', $validated['nascimento']);
            if (count($parts) === 3) {
                $nascimento = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }

        // Atualiza no banco legado (mesma operação que o perfil.ctp do legado faz)
        $customer->nome              = $validated['nome'];
        $customer->email_primario    = $validated['email_primario'];
        $customer->cpf               = $validated['cpf'] ?? $customer->cpf;
        $customer->telefone_celular  = $validated['telefone_celular'] ?? $customer->telefone_celular;
        $customer->nascimento        = $nascimento;
        $customer->sexo              = $validated['sexo'] ?? $customer->sexo;
        $customer->autoriza_newsletter = $request->has('autoriza_newsletter') ? 1 : 0;
        $customer->aceita_whats_app    = $request->has('aceita_whats_app') ? 1 : 0;
        $customer->aceita_sms          = $request->has('aceita_sms') ? 1 : 0;
        $customer->aceita_ligacao      = $request->has('aceita_ligacao') ? 1 : 0;
        $customer->save();

        return redirect()->route('customer.profile')->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Exibe formulário de alteração de senha.
     * GET /minha-conta/senha
     */
    public function password(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $activeMenu = 'password';

        return view('storefront.customer.password', compact('customer', 'activeMenu'));
    }

    /**
     * Atualiza senha do cliente no banco legado.
     * Senha armazenada em MD5 para compatibilidade com o SIV.
     * PUT /minha-conta/senha
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password'     => 'required|string',
            'new_password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required'     => 'Informe sua senha atual.',
            'new_password.required'         => 'Informe a nova senha.',
            'new_password.min'              => 'A nova senha deve ter no mínimo 6 caracteres.',
            'new_password.confirmed'        => 'A confirmação da senha não confere.',
        ]);

        // Verifica senha atual (MD5 — compatibilidade com legado)
        if (md5($request->input('current_password')) !== $customer->senha) {
            return redirect()->back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        // Grava nova senha em MD5 (mesma operação que senha.ctp do legado faz)
        $customer->senha = md5($request->input('new_password'));
        $customer->save();

        return redirect()->route('customer.password')->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Lista endereços do cliente.
     * GET /minha-conta/enderecos
     */
    public function addresses(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Busca endereços ativos, priorizando o principal no topo
        $addresses = Endereco::where('pessoa_id', $customer->id)
            ->where('ativo', 1)
            ->orderByDesc('end_principal')
            ->get();

        $activeMenu = 'addresses';

        return view('storefront.customer.addresses', compact('customer', 'addresses', 'activeMenu'));
    }

    /**
     * Remove endereço (soft delete — marca ativo=0).
     * DELETE /minha-conta/enderecos/{id}
     */
    public function deleteAddress(int $id): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Garante que o endereço pertence ao cliente logado
        $address = Endereco::where('id', $id)
            ->where('pessoa_id', $customer->id)
            ->first();

        if (!$address) {
            return redirect()->route('customer.addresses')->with('error', 'Endereço não encontrado.');
        }

        // Soft delete — mesma operação que EnderecosController::remover() do legado
        $address->ativo = 0;
        $address->save();

        return redirect()->route('customer.addresses')->with('success', 'Endereço removido com sucesso.');
    }

    /**
     * Cadastra novo endereço.
     * POST /minha-conta/enderecos
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'zip_code'     => 'required|string|max:10',
            'street'       => 'required|string|max:190',
            'number'       => 'required|string|max:40',
            'complement'   => 'nullable|string|max:80',
            'neighborhood' => 'required|string|max:90',
            'city'         => 'required|string|max:90',
            'state'        => 'required|string|size:2',
        ]);

        // Cria endereço no banco legado — mesma estrutura que o cadastro original
        Endereco::create([
            'pessoa_id'                     => $customer->id,
            'cep'                           => $validated['zip_code'],
            'logradouro'                    => $validated['street'],
            'logradouro_complemento_numero' => $validated['number'],
            'logradouro_complemento'        => $validated['complement'] ?? null,
            'bairro'                        => $validated['neighborhood'],
            'cidade'                        => $validated['city'],
            'uf'                            => $validated['state'],
            'ativo'                         => 1,
        ]);

        return redirect()->route('customer.addresses')->with('success', 'Endereço cadastrado com sucesso!');
    }

    /**
     * Define um endereço como principal.
     * Replica lógica do legado: desmarca todos, marca o selecionado.
     * PUT /minha-conta/enderecos/{id}/principal
     */
    public function setPrimaryAddress(int $id): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $address = Endereco::where('id', $id)->where('pessoa_id', $customer->id)->first();

        if (!$address) {
            return redirect()->route('customer.addresses')->with('error', 'Endereço não encontrado.');
        }

        // Mesma lógica do legado (app/Model/Endereco.php:103-104):
        // Desmarca todos os endereços do cliente
        Endereco::where('pessoa_id', $customer->id)
            ->update(['ultimo_endereco_usado' => null, 'end_principal' => null]);

        // Marca o selecionado como principal
        $address->end_principal = 1;
        $address->ultimo_endereco_usado = 1;
        $address->save();

        return redirect()->route('customer.addresses')->with('success', 'Endereço definido como principal.');
    }

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

        $activeMenu = 'orders';

        return view('storefront.customer.orders', compact('pedidos', 'customer', 'activeMenu'));
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

        $activeMenu = 'orders';

        return view('storefront.customer.order-detail', compact('pedido', 'customer', 'activeMenu'));
    }

    /**
     * Repetir pedido: adiciona os itens de um pedido anterior ao carrinho.
     * POST /minha-conta/pedidos/{id}/repetir
     *
     * Replica o comportamento do legado (PedidosController::repetir):
     * - Usa preço atual do produto (não o preço do pedido original)
     * - Faz merge com carrinho existente (soma quantidade)
     * - Pula itens com desconto especial (log_desconto)
     * - Pula produtos inativos ou sem estoque
     */
    public function repeatOrder(int $id): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Busca pedido garantindo que pertence ao cliente logado
        $pedido = Pedido::with('items.product')
            ->where('id', $id)
            ->where('pessoa_id', $customer->id)
            ->first();

        if (!$pedido) {
            abort(404);
        }

        $cartService = app(CartService::class);
        $added = 0;
        $skipped = [];

        foreach ($pedido->items as $item) {
            // Pula itens sem produto_id (pedidos muito antigos)
            if (!$item->produto_id) {
                $skipped[] = $item->product_name ?: 'Produto desconhecido';
                continue;
            }

            // Pula itens com desconto especial (campanhas/cupons do legado)
            if ($item->log_desconto) {
                continue;
            }

            // Pula brindes (gift = 1)
            if ($item->gift) {
                continue;
            }

            try {
                $cartService->add($item->produto_id, $item->quantidade);
                $added++;
            } catch (\Exception $e) {
                // Produto inativo, sem estoque ou removido
                $skipped[] = $item->product_name;
            }
        }

        // Monta mensagens de feedback
        if ($added > 0 && empty($skipped)) {
            return redirect()->route('cart.index')
                ->with('success', $added . ($added === 1 ? ' produto adicionado' : ' produtos adicionados') . ' ao carrinho.');
        }

        if ($added > 0 && !empty($skipped)) {
            return redirect()->route('cart.index')
                ->with('success', $added . ($added === 1 ? ' produto adicionado' : ' produtos adicionados') . ' ao carrinho.')
                ->with('warning', count($skipped) . (count($skipped) === 1 ? ' produto não estava disponível e foi ignorado' : ' produtos não estavam disponíveis e foram ignorados') . ': ' . implode(', ', $skipped) . '.');
        }

        // Nenhum item adicionado
        return redirect()->route('customer.order.detail', $id)
            ->with('error', 'Nenhum produto deste pedido está disponível no momento.');
    }
}
