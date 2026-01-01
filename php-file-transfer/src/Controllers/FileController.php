<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Logger;
use App\Exceptions\FileUploadException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Services\FileService;

class FileController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function upload(Request $request): void
    {
        try {
            $uploadedFile = $request->hasFile('file') ? $request->getFile('file') : null;
            $description = $request->getBodyParam('description');

            $file = $this->fileService->upload($uploadedFile, $description);

            Response::json($file->toPublicArray(), 201);
        } catch (FileUploadException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), $e->getHttpStatusCode());
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }

    public function getMetadata(Request $request, array $params): void
    {
        try {
            $file = $this->fileService->getMetadata($params['fileId']);
            Response::json($file->toPublicArray(), 200);
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        } catch (NotFoundException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 404);
        }
    }

    public function download(Request $request, array $params): void
    {
        try {
            $fileData = $this->fileService->download($params['fileId']);

            Response::download(
                $fileData['path'],
                $fileData['name'],
                $fileData['mimeType']
            );
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        } catch (NotFoundException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 404);
        }
    }

    public function list(Request $request): void
    {
        try {
            $page = (int) $request->getQueryParam('page', 1);
            $pageSize = (int) $request->getQueryParam('pageSize', 10);

            $result = $this->fileService->list($page, $pageSize);

            Response::json($result, 200);
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }

    public function delete(Request $request, array $params): void
    {
        try {
            $this->fileService->delete($params['fileId']);
            Response::noContent();
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        } catch (NotFoundException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 404);
        }
    }
}
