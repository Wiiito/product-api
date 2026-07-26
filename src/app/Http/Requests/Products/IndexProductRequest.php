<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'min_quantity' => ['sometimes', 'integer', 'min:0'],
            'max_quantity' => ['sometimes', 'integer', 'min:0'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'min_price' => 'preço mínimo',
            'max_price' => 'preço máximo',
            'min_quantity' => 'quantidade mínima em estoque',
            'max_quantity' => 'quantidade máxima em estoque',
            'per_page' => 'itens por página',
            'page' => 'página',
        ];
    }
}
