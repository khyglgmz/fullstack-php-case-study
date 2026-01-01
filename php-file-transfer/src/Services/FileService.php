<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Logger;
use App\Exceptions\FileUploadException;
use App\Exceptions\NotFoundException;
use App\Helpers\FileHelper;
use App\Helpers\PathHelper;
use App\Models\File;
use App\Repositories\FileRepository;
use App\Validators\FileValidator;

class FileService
{
    private FileRepository $repository;
    private FileValidator $validator;
    private string $uploadPath;

    public function __construct()
    {
        $this->repository = new FileRepository();
        $this->validator = new FileValidator();
        $this->uploadPath = $this->getUploadPath();
    }

    private function getUploadPath(): string
    {
        $path = $_ENV['UPLOAD_PATH'] ?? 'storage/uploads';
        $fullPath = PathHelper::basePath($path);

        PathHelper::ensureDirectoryExists($fullPath);

        return $fullPath;
    }

    public function upload(?array $uploadedFile, ?string $description): File
    {
        if ($uploadedFile === null) {
            Logger::error('No file uploaded');
            throw new FileUploadException('No file was uploaded', 'NO_FILE', 400);
        }

        try {
            $this->validator->validate($uploadedFile);
        } catch (\Throwable $e) {
            Logger::error('File validation failed', [
                'filename' => $uploadedFile['name'] ?? 'unknown',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }

        $id = $this->generateUuid();
        $extension = FileHelper::getExtension($uploadedFile['name']);
        $storedName = $id . '.' . $extension;
        $storedPath = $this->uploadPath . '/' . $storedName;

        $checksum = hash_file('sha256', $uploadedFile['tmp_name']);

        if (!move_uploaded_file($uploadedFile['tmp_name'], $storedPath)) {
            Logger::error('Failed to save uploaded file', [
                'filename' => $uploadedFile['name'],
                'storedPath' => $storedPath
            ]);
            throw new FileUploadException(
                'Failed to save uploaded file',
                'STORAGE_ERROR',
                500
            );
        }

        $mimeType = FileHelper::getMimeType($storedPath);

        $file = new File(
            id: $id,
            originalName: $uploadedFile['name'],
            storedName: $storedName,
            description: $description,
            size: $uploadedFile['size'],
            mimeType: $mimeType,
            checksum: $checksum,
            createdAt: date('Y-m-d\TH:i:s\Z')
        );

        $this->repository->save($file);

        Logger::info('File uploaded', [
            'fileId' => $id,
            'originalName' => $uploadedFile['name'],
            'size' => $uploadedFile['size'],
            'mimeType' => $mimeType
        ]);

        return $file;
    }

    public function getMetadata(string $fileId): File
    {
        try {
            $this->validator->validateFileId($fileId);
        } catch (\Throwable $e) {
            Logger::error('Invalid file ID', [
                'fileId' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $file = $this->repository->findById($fileId);

        if (!$file) {
            Logger::info('File not found', ['fileId' => $fileId]);
            throw new NotFoundException('File not found', 'FILE_NOT_FOUND');
        }

        return $file;
    }

    public function download(string $fileId): array
    {
        $file = $this->getMetadata($fileId);
        $filePath = $this->uploadPath . '/' . $file->storedName;

        if (!file_exists($filePath)) {
            Logger::error('File not found on disk', [
                'fileId' => $fileId,
                'storedName' => $file->storedName,
                'expectedPath' => $filePath
            ]);
            throw new NotFoundException('File not found on disk', 'FILE_NOT_FOUND');
        }

        return [
            'path' => $filePath,
            'name' => $file->originalName,
            'mimeType' => $file->mimeType
        ];
    }

    public function list(int $page, int $pageSize): array
    {
        try {
            $this->validator->validatePagination($page, $pageSize);
        } catch (\Throwable $e) {
            Logger::error('Invalid pagination parameters', [
                'page' => $page,
                'pageSize' => $pageSize,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $files = $this->repository->findAll($page, $pageSize);
        $totalItems = $this->repository->count();
        $totalPages = (int) ceil($totalItems / $pageSize);

        return [
            'files' => array_map(fn($file) => $file->toPublicArray(), $files),
            'pagination' => [
                'currentPage' => $page,
                'pageSize' => $pageSize,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages
            ]
        ];
    }

    public function delete(string $fileId): void
    {
        try {
            $this->validator->validateFileId($fileId);
        } catch (\Throwable $e) {
            Logger::error('Invalid file ID for delete', [
                'fileId' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $deleted = $this->repository->softDelete($fileId);

        if (!$deleted) {
            Logger::info('File not found for delete', ['fileId' => $fileId]);
            throw new NotFoundException('File not found', 'FILE_NOT_FOUND');
        }

        Logger::info('File deleted', ['fileId' => $fileId]);
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
