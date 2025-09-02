<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\GovernorateResource;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Services\CountryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ApiResponse;

    public function __construct(protected CountryService $countryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $countries = $this->countryService->getAll($request->query('search'));

        return $this->successResponse([
            'countries' => CountryResource::collection($countries)
        ]);
    }

    public function show(Country $country): JsonResponse
    {
        // Eager load relationships and their translations for an efficient response.
        $country->load(['translations', 'governorates.translations']);

        return $this->successResponse([
            'country' => new CountryResource($country),
            'governorates' => GovernorateResource::collection($country->governorates),
        ]);
    }

    public function cities(Country $country): JsonResponse
    {
        $cities = $this->countryService->getCities($country);

        return $this->successResponse([
            'cities' => CityResource::collection($cities)
        ]);
    }
}
