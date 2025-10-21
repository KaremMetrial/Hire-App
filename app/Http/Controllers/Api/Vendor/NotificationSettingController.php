<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\NotificationSettingService;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function __construct(protected NotificationSettingService $service) {}

    public function show(Request $request)
    {
        $vendor = $request->user();
        $settings = $this->service->getSettings($vendor);

        return $this->successResponse([
            'settings' => new NotificationSettingResource($settings),
        ], __('Notification settings fetched successfully'));
    }

    public function update(NotificationSettingRequest $request)
    {
        $vendor = $request->user();
        $settings = $this->service->updateSettings($vendor, $request->validated());

        return $this->successResponse([
            'settings' => new NotificationSettingResource($settings),
        ], __('Notification settings updated successfully'));
    }
}
