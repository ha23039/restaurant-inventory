<?php

namespace App\Http\Requests;

use App\Models\SaleReturn;
use Illuminate\Foundation\Http\FormRequest;

class ProcessReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('processReturn', SaleReturn::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'required|in:defective,wrong_order,customer_request,error,other',
            'notes' => 'nullable|string|max:1000',
            'refund_method' => 'required|in:efectivo,tarjeta,transferencia,credito',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1'
        ];
    }
}
