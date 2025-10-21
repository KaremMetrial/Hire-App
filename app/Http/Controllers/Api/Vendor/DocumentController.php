<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddRequirementDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(protected DocumentService $documentService) {}

    public function index(Request $request)
    {
        $documents = $this->documentService->getAll($request->query('search'));

        return $this->successResponse([
            'documents' => DocumentResource::collection($documents),
        ], __('message.success'));
    }

    public function addRequirement(AddRequirementDocumentRequest $request)
    {
        $document = $this->documentService->addRequirementDocument($request->validated());

        return $this->successResponse([
            'document' => new DocumentResource($document),
        ], __('Document added successfully'));
    }
}
