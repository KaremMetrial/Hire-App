<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TermsAndConditionsResource;
use App\Models\TermsAndConditions;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsAndConditionsController extends Controller
{
    use ApiResponse;

    /**
     * Get the current active terms and conditions
     */
    public function current(): JsonResponse
    {
        $terms = TermsAndConditions::getCurrent();

        if (!$terms) {
            return $this->errorResponse(__('message.terms_not_found'), 404);
        }

        return $this->successResponse([
            'terms_and_conditions' => new TermsAndConditionsResource($terms),
        ], __('message.success'));
    }

    /**
     * Get all terms and conditions versions
     */
    public function index(Request $request): JsonResponse
    {
        $query = TermsAndConditions::query()->with('translations');

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }

        $terms = $query->latestVersion()->get();

        return $this->successResponse([
            'terms_and_conditions' => TermsAndConditionsResource::collection($terms),
        ], __('message.success'));
    }

    /**
     * Get specific terms and conditions by version
     */
    public function show(string $version): JsonResponse
    {
        $terms = TermsAndConditions::getByVersion($version);

        if (!$terms) {
            return $this->errorResponse(__('message.terms_not_found'), 404);
        }

        return $this->successResponse([
            'terms_and_conditions' => new TermsAndConditionsResource($terms),
        ], __('message.success'));
    }

    /**
     * Accept terms and conditions for the authenticated user
     */
    public function accept(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
            'accepted_at' => 'nullable|date',
        ]);

        try {
            $user = $request->user();
            $terms = TermsAndConditions::getByVersion($request->version);

            if (!$terms) {
                return $this->errorResponse(__('message.terms_not_found'), 404);
            }

            // Check if user already accepted this version
            if ($user->acceptedTerms()->where('terms_and_conditions_id', $terms->id)->exists()) {
                return $this->errorResponse(__('message.terms_already_accepted'), 422);
            }

            // Create acceptance record
            $user->acceptedTerms()->attach($terms->id, [
                'accepted_at' => $request->accepted_at ?? now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->successResponse([
                'terms_and_conditions' => new TermsAndConditionsResource($terms),
                'accepted_at' => $request->accepted_at ?? now()->toISOString(),
            ], __('message.terms_accepted'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Check if user has accepted the current terms
     */
    public function checkAcceptance(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTerms = TermsAndConditions::getCurrent();

            if (!$currentTerms) {
                return $this->successResponse([
                    'has_accepted' => true, // No terms to accept
                    'current_version' => null,
                ], __('message.success'));
            }

            $hasAccepted = $user->acceptedTerms()
                ->where('terms_and_conditions_id', $currentTerms->id)
                ->exists();

            return $this->successResponse([
                'has_accepted' => $hasAccepted,
                'current_version' => $currentTerms->version,
                'terms_and_conditions' => $hasAccepted ? null : new TermsAndConditionsResource($currentTerms),
            ], __('message.success'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
