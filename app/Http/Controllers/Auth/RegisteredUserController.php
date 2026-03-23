<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controller de registro de novos clientes.
 *
 * Exibe o formulário de cadastro no padrão visual da storefront
 * e processa a criação do usuário com suporte a Pessoa Física e Jurídica.
 */
class RegisteredUserController extends Controller
{
    /**
     * Exibe o formulário de registro.
     */
    public function create(): View
    {
        return view('storefront.auth.register');
    }

    /**
     * Processa o registro de um novo usuário.
     *
     * Validações condicionais:
     * - Pessoa Física: CPF obrigatório, CNPJ não enviado
     * - Pessoa Jurídica: CNPJ e nome da empresa obrigatórios, CPF não enviado
     *
     * Após criar o usuário, dispara o evento Registered (para verificação de email),
     * faz login automático e redireciona para a home.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Regras base de validação
        $rules = [
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'person_type' => ['required', 'in:fisica,juridica'],
            'gender' => ['required', 'in:m,f'],
            'phone' => ['required', 'string', 'max:20'],
            'zip_code' => ['required', 'string', 'max:9'],
            'address' => ['required', 'string', 'max:60'],
            'number' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:40'],
            'neighborhood' => ['required', 'string', 'max:60'],
            'city' => ['required', 'string', 'max:60'],
            'state' => ['required', 'string', 'size:2'],
            'birth_date' => ['nullable', 'string', 'max:10'],
            'state_registration' => ['nullable', 'string', 'max:15'],
            'newsletter' => ['nullable'],
        ];

        // Validação condicional por tipo de pessoa
        if ($request->input('person_type') === 'juridica') {
            // Pessoa Jurídica: CNPJ e razão social obrigatórios
            $rules['cnpj'] = ['required', 'string', 'max:18', 'unique:users,cnpj'];
            $rules['company_name'] = ['required', 'string', 'max:100'];
            $rules['cpf'] = ['nullable'];
        } else {
            // Pessoa Física: CPF obrigatório
            $rules['cpf'] = ['required', 'string', 'max:14', 'unique:users,cpf'];
            $rules['cnpj'] = ['nullable'];
            $rules['company_name'] = ['nullable'];
        }

        // Mensagens de erro personalizadas em português
        $messages = [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'name.required' => 'O nome é obrigatório.',
            'surname.required' => 'O sobrenome é obrigatório.',
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'cpf.required' => 'O CPF é obrigatório para Pessoa Física.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cnpj.required' => 'O CNPJ é obrigatório para Pessoa Jurídica.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'company_name.required' => 'O nome da empresa é obrigatório para Pessoa Jurídica.',
            'gender.required' => 'O gênero é obrigatório.',
            'phone.required' => 'O telefone é obrigatório.',
            'zip_code.required' => 'O CEP é obrigatório.',
            'address.required' => 'O endereço é obrigatório.',
            'number.required' => 'O número é obrigatório.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
            'state.required' => 'O estado é obrigatório.',
        ];

        $validated = $request->validate($rules, $messages);

        // Converte data de nascimento de dd/mm/yyyy para Y-m-d (formato do banco)
        $birthDate = null;
        if (!empty($validated['birth_date'])) {
            $parts = explode('/', $validated['birth_date']);
            if (count($parts) === 3) {
                $birthDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }

        // Cria o usuário no banco de dados
        $user = User::create([
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'person_type' => $validated['person_type'],
            'cpf' => $validated['cpf'] ?? null,
            'cnpj' => $validated['cnpj'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'state_registration' => $validated['state_registration'] ?? null,
            'gender' => $validated['gender'],
            'birth_date' => $birthDate,
            'phone' => $validated['phone'],
            'zip_code' => $validated['zip_code'],
            'address' => $validated['address'],
            'number' => $validated['number'],
            'complement' => $validated['complement'] ?? null,
            'neighborhood' => $validated['neighborhood'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'newsletter' => $request->has('newsletter'),
        ]);

        // Dispara evento Registered (usado para verificação de email, etc.)
        event(new Registered($user));

        // Login automático após cadastro
        Auth::login($user);

        // Redireciona para a home com mensagem de sucesso
        return redirect('/')->with('success', 'Cadastro realizado com sucesso! Bem-vindo(a)!');
    }
}
