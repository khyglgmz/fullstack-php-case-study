<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    private string $errorCode;
    private int $httpStatusCode;

    public function __construct(
        string $message,
        string $errorCode = 'FILE_UPLOAD_ERROR',
        int $httpStatusCode = 400
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public static function fromUploadError(int $errorCode): self
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => new self(
                'File exceeds server maximum upload size',
                'FILE_TOO_LARGE',
                413
            ),
            UPLOAD_ERR_FORM_SIZE => new self(
                'File exceeds form maximum upload size',
                'FILE_TOO_LARGE',
                413
            ),
            UPLOAD_ERR_PARTIAL => new self(
                'File was only partially uploaded',
                'UPLOAD_INCOMPLETE',
                400
            ),
            UPLOAD_ERR_NO_FILE => new self(
                'No file was uploaded',
                'NO_FILE',
                400
            ),
            UPLOAD_ERR_NO_TMP_DIR => new self(
                'Server configuration error: missing temp folder',
                'SERVER_ERROR',
                500
            ),
            UPLOAD_ERR_CANT_WRITE => new self(
                'Server configuration error: failed to write file',
                'SERVER_ERROR',
                500
            ),
            UPLOAD_ERR_EXTENSION => new self(
                'File upload stopped by extension',
                'UPLOAD_BLOCKED',
                400
            ),
            default => new self(
                'Unknown upload error',
                'UNKNOWN_ERROR',
                500
            )
        };
    }
}
