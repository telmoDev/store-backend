<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'customer_name' => 'required',
            'customer_email' => 'required|email',
            'customer_phone' => 'required',
            'customer_address' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'El nombre del cliente es requerido',
            'customer_email.required' => 'El email del cliente es requerido',
            'customer_phone.required' => 'El teléfono del cliente es requerido',
            'customer_address.required' => 'La dirección del cliente es requerida',
            'products.required' => 'Debe incluir al menos un producto',
            'products.*.product_id.required' => 'El producto es requerido',
            'products.*.quantity.required' => 'La cantidad es requerida',
            'products.*.quantity.integer' => 'La cantidad debe ser un número entero',
            'products.*.quantity.min' => 'La cantidad debe ser mayor o igual a 1',
        ];
    }
}
