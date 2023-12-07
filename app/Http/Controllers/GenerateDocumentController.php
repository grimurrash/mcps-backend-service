<?php

namespace App\Http\Controllers;

use App\Contracts\RzdDocumentServiceInterface;
use App\Http\Requests\GenerateActsRequest;
use App\Http\Resources\GenerateActsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenerateDocumentController extends Controller
{
    public function __construct(
        private readonly RzdDocumentServiceInterface $documentService
    )
    {
    }

    public function generateActs(GenerateActsRequest $request): JsonResponse
    {
        $file = $request->file('table_file');

        $downloadUrl = $this->documentService->generateActs($file);

        return response()->json(GenerateActsResource::make($downloadUrl));
    }
}
