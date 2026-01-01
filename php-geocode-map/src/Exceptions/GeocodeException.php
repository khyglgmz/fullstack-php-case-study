<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class GeocodeException extends Exception
{
    private string $errorCode;
    private int $httpStatusCode;

    public function __construct(string $message, string $errorCode = 'GEOCODE_ERROR', int $httpStatusCode = 500)
    {
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

    public static function apiError(string $message): self
    {
        return new self($message, 'GEOCODE_API_ERROR', 502);
    }

    public static function notFound(string $address): self
    {
        return new self("Adres için koordinat bulunamadı: {$address}", 'ADDRESS_NOT_FOUND', 404);
    }

    public static function rateLimited(): self
    {
        return new self('Geocoding servisi istek limiti aşıldı', 'RATE_LIMITED', 429);
    }

    public static function fileNotFound(string $path): self
    {
        return new self("Adres dosyası bulunamadı: {$path}", 'FILE_NOT_FOUND', 404);
    }

    public static function invalidJson(string $path): self
    {
        return new self("Adres dosyasında geçersiz JSON: {$path}", 'INVALID_JSON', 400);
    }
}
