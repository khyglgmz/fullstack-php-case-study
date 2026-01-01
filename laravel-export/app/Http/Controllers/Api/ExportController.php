<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportProductsRequest;
use App\Jobs\ExportProductsJob;
use App\Services\ProductExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function __construct(
        private ProductExportService $exportService
    ) {}

    public function products(ExportProductsRequest $request): JsonResponse
    {
        $result = $this->exportService->export($request->validated());

        return response()->json($result);
    }

    public function productsAsync(ExportProductsRequest $request): JsonResponse
    {
        $filters = $request->validated();

        ExportProductsJob::dispatch($filters);

        return response()->json([
            'success' => true,
            'message' => 'Export işlemi kuyruğa eklendi',
            'data' => [
                'status' => 'queued',
                'filters' => $filters,
            ],
        ], 202);
    }

    public function showForm(): View
    {
        return view('export.products');
    }
}
