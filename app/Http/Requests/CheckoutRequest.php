<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validação do checkout.
 *
 * Validação condicional:
 * - Logado: dados pessoais vêm do auth()->user(), só endereço é validado no form
 * - Convidado: valida dados pessoais (nome, email, telefone, PF/PJ) + endereço
 *
 * Endereço de entrega é sempre validado (pode ser diferente do cadastro).
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Regras de endereço (sempre obrigatórias)
        $rules = [
            'shipping_zip_code'     => ['required', 'string', 'max:9'],
            'shipping_address'      => ['required', 'string', 'max:255'],
            'shipping_number'       => ['required', 'string', 'max:20'],
            'shipping_complement'   => ['nullable', 'string', 'max:100'],
            'shipping_neighborhood' => ['required', 'string', 'max:100'],
            'shipping_city'         => ['required', 'string', 'max:100'],
            'shipping_state'        => ['required', 'string', 'size:2'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ];

        // Se não está logado, valida também os dados pessoais
        if (!auth()->check()) {
            $rules['name']        = ['required', 'string', 'max:100'];
            $rules['surname']     = ['required', 'string', 'max:100'];
            $rules['email']       = ['required', 'string', 'email', 'max:255'];
            $rules['phone']       = ['required', 'string', 'max:20'];
            $rules['person_type'] = ['required', 'in:fisica,juridica'];

            // Validação condicional por tipo de pessoa
            if ($this->input('person_type') === 'juridica') {
                $rules['cnpj']         = ['required', 'string', 'max:18'];
                $rules['company_name'] = ['required', 'string', 'max:100'];
                $rules['cpf']          = ['nullable'];
            } else {
                $rules['cpf']          = ['required', 'string', 'max:14'];
                $rules['cnpj']         = ['nullable'];
                $rules['company_name'] = ['nullable'];
            }
        }

        return $rules;
    }

    /**
     * Mensagens de erro personalizadas em português.
     */
    public function messages(): array
    {
        return [
            // Dados pessoais (convidado)
            'name.required'        => 'O nome é obrigatório.',
            'surname.required'     => 'O sobrenome é obrigatório.',
            'email.required'       => 'O e-mail é obrigatório.',
            'email.email'          => 'Informe um e-mail válido.',
            'phone.required'       => 'O telefone é obrigatório.',
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'cpf.required'         => 'O CPF é obrigatório para Pessoa Física.',
            'cnpj.required'        => 'O CNPJ é obrigatório para Pessoa Jurídica.',
            'company_name.required' => 'O nome da empresa é obrigatório para Pessoa Jurídica.',

            // Endereço
            'shipping_zip_code.required'     => 'O CEP é obrigatório.',
            'shipping_address.required'      => 'O endereço é obrigatório.',
            'shipping_number.required'       => 'O número é obrigatório.',
            'shipping_neighborhood.required' => 'O bairro é obrigatório.',
            'shipping_city.required'         => 'A cidade é obrigatória.',
            'shipping_state.required'        => 'O estado é obrigatório.',

            // Observações
            'notes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }
}
