<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'sku' => 'required|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'sku.required' => 'El sku es requerido',
            'price.required' => 'El precio es requerido',
            'stock.required' => 'El stock es requerido',
            'sku.unique' => 'El sku ya existe',
            'price.numeric' => 'El precio debe ser un número',
            'stock.integer' => 'El stock debe ser un número entero',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'stock.min' => 'El stock debe ser mayor o igual a 0',
        ];
    }
}
