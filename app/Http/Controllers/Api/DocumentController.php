<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(protected DocumentService $documentService) {}

    public function index(Request $request): JsonResponse
    {
        $documents = $this->documentService->getAll($request->query('search'));

        return $this->successResponse([
            'documents' => DocumentResource::collection($documents),
        ], __('message.success'));
    }

    public function show(Document $document): JsonResponse
    {
        $document->load('translations');

        return $this->successResponse([
            'document' => new DocumentResource($document),
        ], __('message.success'));
    }

    public function addRequirement(Request $request): JsonResponse
    {
        $this->documentService->addRequirement($request->all());
    }
}
