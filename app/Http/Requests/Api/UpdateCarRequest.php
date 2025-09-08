<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_of_manufacture' => ['sometimes', 'string'],
            'color' => ['sometimes', 'string'],
            'license_plate' => ['sometimes', 'string'],
            'num_of_seat' => ['sometimes', 'integer'],
            'kilometers' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'model_id' => ['sometimes', 'exists:models,id'],
            'fuel_id' => ['sometimes', 'exists:fuels,id'],
            'transmission_id' => ['sometimes', 'exists:transmissions,id'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'rental_shop_id' => ['sometimes', 'exists:rental_shops,id'],
            'city_id' => ['sometimes', 'exists:cities,id'],
        ];
    }
}
