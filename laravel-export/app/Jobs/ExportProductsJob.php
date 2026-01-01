<?php

namespace App\Jobs;

use App\Services\ProductExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExportProductsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public array $filters = []
    ) {}

    public function handle(ProductExportService $exportService): void
    {
        Log::info('Export job başlatıldı', ['filters' => $this->filters]);

        $result = $exportService->export($this->filters);

        Log::info('Export job tamamlandı', [
            'file_path' => $result['data']['file_path'],
            'record_count' => $result['data']['record_count'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Export job başarısız oldu', [
            'filters' => $this->filters,
            'error' => $exception->getMessage(),
        ]);
    }
}
