<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRequirementDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('rental_shop_id')) {
            $this->merge(['rental_shop_id' => $this->user()->rentalShops()->first()->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rental_shop_id' => ['required', 'exists:rental_shops,id'],
            'customer_type_id' => ['required', 'exists:customer_types,id'],
            'document_id' => ['required', 'exists:documents,id'],
        ];
    }
}
