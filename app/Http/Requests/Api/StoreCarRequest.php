<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_of_manufacture' => ['required', 'string'],
            'color' => ['required', 'string'],
            'license_plate' => ['required', 'string'],
            'num_of_seat' => ['required', 'integer'],
            'kilometers' => ['required', 'integer'],
            'is_active' => ['boolean'],
            'model_id' => ['required', 'exists:models,id'],
            'fuel_id' => ['required', 'exists:fuels,id'],
            'transmission_id' => ['required', 'exists:transmissions,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'rental_shop_id' => ['required', 'exists:rental_shops,id'],
            'city_id' => ['required', 'exists:cities,id'],
        ];
    }
}
