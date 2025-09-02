<?php

    namespace App\Http\Controllers\Api\Vendor;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Vendor\StoreWorkingDayRequest;
    use App\Http\Requests\Vendor\UpdateWorkingDayRequest;
    use App\Http\Resources\Vendor\WorkingDayResource;
    use App\Services\Vendor\WorkingDayService;
    use App\Traits\ApiResponse;
    use Illuminate\Http\JsonResponse;

    class WorkingDayController extends Controller
    {
        use ApiResponse;

        public function __construct(protected WorkingDayService $workingDayService)
        {
        }

        public function store(StoreWorkingDayRequest $request): JsonResponse
        {
            $workingDay = $this->workingDayService->create($request->validated());

            return $this->successResponse(
                [ 'working_day' => new WorkingDayResource($workingDay)],
                __('message.working_day.created')
            );
        }

        public function update(UpdateWorkingDayRequest $request, int $id): JsonResponse
        {
            $workingDay = $this->workingDayService->update($id, $request->validated());

            return $this->successResponse(
                [ 'working_day' => new WorkingDayResource($workingDay)],
                __('message.working_day.updated')
            );
        }

        public function index(int $id): JsonResponse
        {
            $workingDays = $this->workingDayService->getByRentalShop($id);

            return $this->successResponse([
                'working_days' => WorkingDayResource::collection($workingDays),
            ], __('message.working_day.index'));
        }
    }
