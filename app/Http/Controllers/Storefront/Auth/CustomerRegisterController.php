<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Endereco;
use App\Models\Legacy\Pessoa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller de registro de novos clientes.
 *
 * Grava na tabela 'pessoas' do banco legado (MD5) para compatibilidade com SIV.
 * Também cria o endereço na tabela 'enderecos'.
 *
 * Campos obrigatórios (compatíveis com legado):
 * - nome, email_primario, cpf, nascimento, sexo, telefone_celular, senha (MD5)
 */
class CustomerRegisterController extends Controller
{
    /**
     * Exibe o formulário de cadastro.
     */
    public function create(): View
    {
        return view('storefront.auth.register');
    }

    /**
     * Processa o registro de um novo cliente.
     * Grava em 'pessoas' (banco legado) com senha MD5.
     */
    public function store(Request $request): RedirectResponse
    {
        // Regras base de validação
        $rules = [
            'email' => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'person_type' => ['required', 'in:fisica,juridica'],
            'gender' => ['required', 'in:M,F,m,f'],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'string', 'min:10'],
            'zip_code' => ['required', 'string', 'max:9'],
            'address' => ['required', 'string', 'max:190'],
            'number' => ['required', 'string', 'max:40'],
            'complement' => ['nullable', 'string', 'max:80'],
            'neighborhood' => ['required', 'string', 'max:90'],
            'city' => ['required', 'string', 'max:90'],
            'state' => ['required', 'string', 'size:2'],
            'newsletter' => ['nullable'],
        ];

        // Validação condicional por tipo de pessoa
        if ($request->input('person_type') === 'juridica') {
            $rules['cnpj'] = ['required', 'string', 'max:18'];
            $rules['company_name'] = ['required', 'string', 'max:100'];
            $rules['cpf'] = ['nullable'];
            $rules['state_registration'] = ['nullable', 'string', 'max:15'];
        } else {
            $rules['cpf'] = ['required', 'string', 'max:14'];
            $rules['cnpj'] = ['nullable'];
            $rules['company_name'] = ['nullable'];
        }

        $messages = [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'name.required' => 'O nome é obrigatório.',
            'surname.required' => 'O sobrenome é obrigatório.',
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'cpf.required' => 'O CPF é obrigatório para Pessoa Física.',
            'cnpj.required' => 'O CNPJ é obrigatório para Pessoa Jurídica.',
            'company_name.required' => 'O nome da empresa é obrigatório para Pessoa Jurídica.',
            'gender.required' => 'O gênero é obrigatório.',
            'phone.required' => 'O telefone é obrigatório.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'zip_code.required' => 'O CEP é obrigatório.',
            'address.required' => 'O endereço é obrigatório.',
            'number.required' => 'O número é obrigatório.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
            'state.required' => 'O estado é obrigatório.',
        ];

        $validated = $request->validate($rules, $messages);

        // Limpa CPF/CNPJ — apenas dígitos
        $cpf = isset($validated['cpf']) ? preg_replace('/\D/', '', $validated['cpf']) : null;
        $cnpj = isset($validated['cnpj']) ? preg_replace('/\D/', '', $validated['cnpj']) : null;

        // Verifica duplicidade de email no banco legado
        $emailExists = Pessoa::where('email_primario', strtolower(trim($validated['email'])))->exists();
        if ($emailExists) {
            return back()->withInput()->withErrors(['email' => 'Este e-mail já está cadastrado.']);
        }

        // Verifica duplicidade de CPF no banco legado
        if ($cpf && strlen($cpf) === 11) {
            $cpfExists = Pessoa::where('cpf', $cpf)->exists();
            if ($cpfExists) {
                return back()->withInput()->withErrors(['cpf' => 'Este CPF já está cadastrado.']);
            }
        }

        // Verifica duplicidade de CNPJ no banco legado
        if ($cnpj && strlen($cnpj) === 14) {
            $cnpjExists = Pessoa::where('cnpj', $cnpj)->exists();
            if ($cnpjExists) {
                return back()->withInput()->withErrors(['cnpj' => 'Este CNPJ já está cadastrado.']);
            }
        }

        // Converte data de nascimento de DD/MM/YYYY para YYYY-MM-DD
        $birthDate = null;
        if (!empty($validated['birth_date'])) {
            $parts = explode('/', $validated['birth_date']);
            if (count($parts) === 3) {
                $birthDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }

        // Limpa telefone — apenas dígitos
        $phone = preg_replace('/\D/', '', $validated['phone']);

        // Nome completo = nome + sobrenome
        $nomeCompleto = trim($validated['name'] . ' ' . $validated['surname']);

        $connection = DB::connection('mysql_legacy');

        try {
            // Cria a pessoa no banco legado
            $pessoa = new Pessoa();
            $pessoa->nome = $nomeCompleto;
            $pessoa->email_primario = strtolower(trim($validated['email']));
            $pessoa->senha = md5($validated['password']); // MD5 — compatibilidade SIV
            $pessoa->cpf = $cpf;
            $pessoa->cnpj = $cnpj;
            $pessoa->razao_social = $validated['company_name'] ?? null;
            $pessoa->inscricao_estadual = $validated['state_registration'] ?? null;
            $pessoa->nascimento = $birthDate;
            $pessoa->sexo = strtoupper($validated['gender']);
            $pessoa->telefone_celular = $phone;
            $pessoa->ativo = 1;
            $pessoa->autoriza_newsletter = $request->has('newsletter') ? 1 : 0;
            $pessoa->data_cadastro = now();
            $pessoa->save();

            // Cria o endereço principal no banco legado
            $endereco = new Endereco();
            $endereco->pessoa_id = $pessoa->id;
            $endereco->cep = $validated['zip_code'];
            $endereco->logradouro = $validated['address'];
            $endereco->logradouro_complemento_numero = $validated['number'];
            $endereco->logradouro_complemento = $validated['complement'] ?? null;
            $endereco->bairro = $validated['neighborhood'];
            $endereco->cidade = $validated['city'];
            $endereco->uf = strtoupper($validated['state']);
            $endereco->end_principal = 1; // Endereço principal
            $endereco->ativo = 1;
            $endereco->ultimo_endereco_usado = 1;
            $endereco->save();

            // Login automático via guard 'customer'
            Auth::guard('customer')->login($pessoa);

            $request->session()->regenerate();

            return redirect('/')->with('success', 'Cadastro realizado com sucesso! Bem-vindo(a)!');

        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->withErrors([
                'email' => 'Erro ao realizar cadastro. Tente novamente.',
            ]);
        }
    }
}
