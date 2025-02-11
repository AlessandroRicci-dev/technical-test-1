<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()->role === "USER") {
            return true;
        };
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:DRAFT,PAID,SHIPPED,COMPLETED',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.qty' => 'required|integer'
        ];
    }
}
