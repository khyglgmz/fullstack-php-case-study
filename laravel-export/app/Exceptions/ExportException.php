<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ExportException extends Exception
{
    public function __construct(
        string $message = 'Export işlemi başarısız oldu',
        int $code = 500,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ],
        ], $this->getCode());
    }
}
