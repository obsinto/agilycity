<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpendingCapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Aqui estamos permitindo apenas que o prefeito (role: mayor) possa enviar essa request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('mayor');
    }

    /**
     * Prepare the data for validation.
     *
     * Esse método pré-processa o campo 'cap_value', removendo símbolos e convertendo
     * o valor para o formato numérico padrão (ex.: "R$ 1.234,56" para "1234.56").
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('cap_value')) {
            $value = $this->input('cap_value');

            // Remove o símbolo "R$" e espaços
            $value = str_replace(['R$', ' '], '', $value);
            // Remove o ponto dos milhares
            $value = str_replace('.', '', $value);
            // Converte a vírgula em ponto para separar os decimais
            $value = str_replace(',', '.', $value);

            $this->merge([
                'cap_value' => $value,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'secretary_id' => 'required|exists:secretaries,id',
            'expense_type_id' => 'nullable|exists:expense_types,id',
            'cap_value' => 'required|numeric|min:0',
        ];
    }
}
