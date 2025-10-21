<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateRentalShopRequest;
use App\Http\Resources\Vendor\RentalShopResourece;
use App\Services\Vendor\RentalShopService;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Auth;

class RentalShopController extends Controller
{
    use ApiResponse, FileUploadTrait;

    public function __construct(protected RentalShopService $rentalShopService) {}

    public function update(UpdateRentalShopRequest $request)
    {
        $rentalShop = Auth::user()->rentalShops->first();
        if ($request->hasFile('rental_shop.image')) {
            $validated['image'] = $this->upload($request, 'rental_shop.image', 'vendors/rental_shops');
        }
        if ($request->hasFile('rental_shop.transport_license_photo')) {
            $validated['transport_license_photo'] = $this->upload($request, 'rental_shop.transport_license_photo', 'vendors/transport_license_photos');
        }
        if ($request->hasFile('rental_shop.commerical_registration_photo')) {
            $validated['commerical_registration_photo'] = $this->upload($request, 'rental_shop.commerical_registration_photo', 'vendors/commerical_registration_photos');
        }
        $this->rentalShopService->update($rentalShop, $request->validated());

        $rentalShop->load(['address', 'workingDays']);

        return $this->successResponse([
            'rental_shop' => new RentalShopResourece($rentalShop),
        ], __('message.success'));
    }
}
