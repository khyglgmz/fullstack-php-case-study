<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\FileUploadException;
use App\Exceptions\ValidationException;
use App\Helpers\FileHelper;

class FileValidator
{
    private const MAX_SIZE = 10 * 1024 * 1024;

    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'text/plain'
    ];

    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'txt'];

    private const EXTENSION_MIME_MAP = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'txt' => ['text/plain'],
    ];

    public function validate(array $uploadedFile): void
    {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw FileUploadException::fromUploadError($uploadedFile['error']);
        }

        $this->validateSize($uploadedFile['size']);

        $this->validateExtension($uploadedFile['name']);

        $this->validateMimeType($uploadedFile['tmp_name']);

        $this->validateExtensionMimeMatch($uploadedFile['name'], $uploadedFile['tmp_name']);
    }

    private function validateSize(int $size): void
    {
        $maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? self::MAX_SIZE);

        if ($size > $maxSize) {
            $maxSizeMb = round($maxSize / (1024 * 1024), 1);
            throw new FileUploadException(
                "File size exceeds maximum allowed ({$maxSizeMb}MB)",
                'FILE_TOO_LARGE',
                413
            );
        }

        if ($size === 0) {
            throw new ValidationException('Empty files are not allowed', 'EMPTY_FILE');
        }
    }

    private function validateExtension(string $fileName): void
    {
        $extension = FileHelper::getExtension($fileName);
        $allowedExtensions = $this->getAllowedExtensions();

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new FileUploadException(
                'File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions),
                'INVALID_FILE_TYPE',
                415
            );
        }
    }

    private function validateMimeType(string $tmpPath): void
    {
        $mimeType = FileHelper::getMimeType($tmpPath);

        if (!in_array($mimeType, self::ALLOWED_MIMES, true)) {
            throw new FileUploadException(
                'File content type not allowed',
                'INVALID_CONTENT_TYPE',
                415
            );
        }
    }

    private function validateExtensionMimeMatch(string $fileName, string $tmpPath): void
    {
        $extension = FileHelper::getExtension($fileName);
        $mimeType = FileHelper::getMimeType($tmpPath);

        $allowedMimesForExtension = self::EXTENSION_MIME_MAP[$extension] ?? [];

        if (!in_array($mimeType, $allowedMimesForExtension, true)) {
            throw new FileUploadException(
                'File content does not match its extension',
                'INVALID_CONTENT_TYPE',
                415
            );
        }
    }

    private function getAllowedExtensions(): array
    {
        $envExtensions = $_ENV['ALLOWED_EXTENSIONS'] ?? null;

        if ($envExtensions) {
            return array_map('trim', explode(',', $envExtensions));
        }

        return self::ALLOWED_EXTENSIONS;
    }

    public function validateFileId(string $fileId): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!preg_match($pattern, $fileId)) {
            throw new ValidationException('Invalid file ID format', 'INVALID_FILE_ID');
        }
    }

    public function validatePagination(int $page, int $pageSize): void
    {
        if ($page < 1) {
            throw new ValidationException('Page number must be at least 1', 'INVALID_PAGE');
        }

        if ($pageSize < 1 || $pageSize > 100) {
            throw new ValidationException(
                'Page size must be between 1 and 100',
                'INVALID_PAGE_SIZE'
            );
        }
    }
}
