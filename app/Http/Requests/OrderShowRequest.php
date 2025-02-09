<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderShowRequest extends FormRequest
{

    /**
     * Add the id to the validation data 
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['id' => $this->route('id')]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $order = Order::where("id", $this->route('id'))->firstOrFail();

        if ($this->user()->role === "USER" && $order->user_id === $this->user()->id) {
            return true;
        };

        if ($this->user()->role === "ADMIN") {
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
            'id' => 'required|integer|exists:orders,id',
        ];
    }
}
