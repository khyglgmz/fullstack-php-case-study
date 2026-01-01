<?php

namespace App\Services;

use App\Exceptions\ExportException;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductExportService
{
    private const EXPORT_PATH = 'exports';
    private const DISK = 'local';

    public function export(array $filters = []): array
    {
        try {
            $products = $this->getFilteredProducts($filters);

            if ($products->isEmpty()) {
                throw new ExportException('Export edilecek ürün bulunamadı', 404);
            }

            $fileName = $this->generateFileName();
            $filePath = $this->writeToFile($fileName, $products->toArray());

            return [
                'success' => true,
                'data' => [
                    'file_path' => $filePath,
                    'record_count' => $products->count(),
                    'created_at' => now()->toIso8601String(),
                    'file_size' => $this->formatFileSize(Storage::disk(self::DISK)->size($filePath)),
                ],
            ];
        } catch (ExportException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ExportException('Beklenmeyen hata: ' . $e->getMessage(), 500);
        }
    }

    private function getFilteredProducts(array $filters)
    {
        try {
            $query = Product::query();

            if (isset($filters['is_active'])) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            }

            if (isset($filters['min_price'])) {
                $query->where('price', '>=', (float) $filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('price', '<=', (float) $filters['max_price']);
            }

            return $query->get();
        } catch (\Illuminate\Database\QueryException $e) {
            throw new ExportException('Veritabanı bağlantı hatası: ' . $e->getMessage(), 503);
        }
    }

    private function generateFileName(): string
    {
        return 'products_' . now()->format('Ymd_His') . '_' . uniqid() . '.json';
    }

    private function writeToFile(string $fileName, array $data): string
    {
        try {
            $this->ensureExportDirectoryExists();

            $filePath = self::EXPORT_PATH . '/' . $fileName;

            $jsonContent = json_encode([
                'exported_at' => now()->toIso8601String(),
                'total_count' => \count($data),
                'products' => $data,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($jsonContent === false) {
                throw new ExportException('JSON dönüşümü başarısız oldu', 500);
            }

            $written = Storage::disk(self::DISK)->put($filePath, $jsonContent);

            if (!$written) {
                throw new ExportException('Dosya yazılamadı', 500);
            }

            return $filePath;
        } catch (ExportException $e) {
            throw $e;
        } catch (\League\Flysystem\UnableToWriteFile $e) {
            throw new ExportException('Dosya yazma hatası: Disk dolu veya erişim izni yok', 500);
        } catch (\Exception $e) {
            throw new ExportException('Dosya işlemi başarısız: ' . $e->getMessage(), 500);
        }
    }

    private function ensureExportDirectoryExists(): void
    {
        if (!Storage::disk(self::DISK)->exists(self::EXPORT_PATH)) {
            Storage::disk(self::DISK)->makeDirectory(self::EXPORT_PATH);
        }
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < \count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
