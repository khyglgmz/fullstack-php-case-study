<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Logger;
use App\Exceptions\GeocodeException;
use App\Exceptions\ValidationException;
use App\Helpers\PathHelper;
use App\Models\Location;
use App\Repositories\LocationRepository;
use App\Validators\AddressValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GeocodeService
{
    private LocationRepository $repository;
    private AddressValidator $validator;
    private Client $httpClient;
    private string $userAgent;

    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
    private const RATE_LIMIT_DELAY = 1;

    public function __construct()
    {
        $this->repository = new LocationRepository();
        $this->validator = new AddressValidator();
        $this->userAgent = $_ENV['NOMINATIM_USER_AGENT'] ?? 'php-geocode-app/1.0';
        $this->httpClient = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json'
            ]
        ]);
    }

    public function loadAddresses(): array
    {
        $path = PathHelper::basePath('data/addresses.json');

        if (!file_exists($path)) {
            throw GeocodeException::fileNotFound($path);
        }

        $content = file_get_contents($path);
        $addresses = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Invalid JSON in addresses file', [
                'path' => $path,
                'error' => json_last_error_msg()
            ]);
            throw GeocodeException::invalidJson($path);
        }

        try {
            $this->validator->validateAddressList($addresses);
        } catch (ValidationException $e) {
            Logger::error('Address list validation failed', [
                'path' => $path,
                'error' => $e->getMessage(),
                'code' => $e->getErrorCode()
            ]);
            throw $e;
        }

        return $addresses;
    }

    public function geocodeAll(bool $forceRetry = false): array
    {
        $addresses = $this->loadAddresses();
        $results = [];
        $processed = 0;

        foreach ($addresses as $addressData) {
            $existing = $this->repository->findByAddressId((int) $addressData['id']);

            if ($existing && $existing->isGeocoded() && !$forceRetry) {
                $results[] = $existing;
                continue;
            }

            if ($existing && $existing->isFailed() && !$forceRetry) {
                $results[] = $existing;
                continue;
            }

            if ($processed > 0) {
                sleep(self::RATE_LIMIT_DELAY);
            }

            $location = $existing ?? Location::fromAddressData($addressData);
            $geocoded = $this->geocodeSingle($location);
            $saved = $this->repository->upsert($geocoded);
            $results[] = $saved;
            $processed++;
        }

        return $results;
    }

    public function geocodeSingle(Location $location): Location
    {
        try {
            $response = $this->httpClient->get(self::NOMINATIM_URL, [
                'query' => [
                    'q' => $location->address,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                Logger::info("No results found for address: {$location->address}");
                return $location->withError('Bu adres için koordinat bulunamadı');
            }

            $result = $data[0];
            $latitude = (float) $result['lat'];
            $longitude = (float) $result['lon'];

            Logger::info("Geocoded address: {$location->address}", [
                'lat' => $latitude,
                'lon' => $longitude
            ]);

            return $location->withCoordinates($latitude, $longitude);

        } catch (GuzzleException $e) {
            Logger::error("Geocode API error for address: {$location->address}", [
                'error' => $e->getMessage()
            ]);

            if (str_contains($e->getMessage(), '429')) {
                return $location->withError('İstek limiti aşıldı - lütfen daha sonra tekrar deneyin');
            }

            return $location->withError('Geocoding servisi hatası: ' . $e->getMessage());
        }
    }

    public function retryAddress(int $addressId): Location
    {
        try {
            $this->validator->validateAddressId($addressId);
        } catch (ValidationException $e) {
            Logger::error('Address ID validation failed', [
                'addressId' => $addressId,
                'error' => $e->getMessage(),
                'code' => $e->getErrorCode()
            ]);
            throw $e;
        }

        $existing = $this->repository->findByAddressId($addressId);

        if (!$existing) {
            $addresses = $this->loadAddresses();
            $addressData = null;

            foreach ($addresses as $addr) {
                if ((int) $addr['id'] === $addressId) {
                    $addressData = $addr;
                    break;
                }
            }

            if (!$addressData) {
                Logger::error('Address not found', [
                    'addressId' => $addressId
                ]);
                throw new ValidationException("ID {$addressId} ile adres bulunamadı", 'ADDRESS_NOT_FOUND');
            }

            $existing = Location::fromAddressData($addressData);
        }

        $geocoded = $this->geocodeSingle($existing);
        return $this->repository->upsert($geocoded);
    }

    public function getLocations(?string $status = null): array
    {
        if ($status !== null) {
            try {
                $this->validator->validateStatus($status);
            } catch (ValidationException $e) {
                Logger::error('Status validation failed', [
                    'status' => $status,
                    'error' => $e->getMessage(),
                    'code' => $e->getErrorCode()
                ]);
                throw $e;
            }
            return $this->repository->findByStatus($status);
        }

        return $this->repository->findAll();
    }

    public function getLocationsSummary(): array
    {
        return $this->repository->countByStatus();
    }

    public function initializeFromFile(): array
    {
        $addresses = $this->loadAddresses();
        $results = [];

        foreach ($addresses as $addressData) {
            $existing = $this->repository->findByAddressId((int) $addressData['id']);

            if (!$existing) {
                $location = Location::fromAddressData($addressData);
                $saved = $this->repository->upsert($location);
                $results[] = $saved;
            } else {
                $results[] = $existing;
            }
        }

        return $results;
    }
}
