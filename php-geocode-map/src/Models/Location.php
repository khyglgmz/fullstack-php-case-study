<?php

declare(strict_types=1);

namespace App\Models;

class Location
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $addressId,
        public readonly string $title,
        public readonly string $address,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly string $status,
        public readonly ?string $errorMessage,
        public readonly ?string $geocodedAt,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            addressId: (int) $data['address_id'],
            title: $data['title'],
            address: $data['address'],
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            status: $data['status'] ?? 'pending',
            errorMessage: $data['error_message'] ?? null,
            geocodedAt: $data['geocoded_at'] ?? null,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public static function fromAddressData(array $addressData): self
    {
        return new self(
            id: null,
            addressId: (int) $addressData['id'],
            title: $addressData['title'],
            address: $addressData['address'],
            latitude: null,
            longitude: null,
            status: 'pending',
            errorMessage: null,
            geocodedAt: null,
            createdAt: date('Y-m-d\TH:i:s\Z')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'address_id' => $this->addressId,
            'title' => $this->title,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
            'geocoded_at' => $this->geocodedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'addressId' => $this->addressId,
            'title' => $this->title,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'errorMessage' => $this->errorMessage,
            'geocodedAt' => $this->geocodedAt
        ];
    }

    public function withCoordinates(float $latitude, float $longitude): self
    {
        return new self(
            id: $this->id,
            addressId: $this->addressId,
            title: $this->title,
            address: $this->address,
            latitude: $latitude,
            longitude: $longitude,
            status: 'success',
            errorMessage: null,
            geocodedAt: date('Y-m-d\TH:i:s\Z'),
            createdAt: $this->createdAt,
            updatedAt: date('Y-m-d\TH:i:s\Z')
        );
    }

    public function withError(string $errorMessage): self
    {
        return new self(
            id: $this->id,
            addressId: $this->addressId,
            title: $this->title,
            address: $this->address,
            latitude: null,
            longitude: null,
            status: 'failed',
            errorMessage: $errorMessage,
            geocodedAt: date('Y-m-d\TH:i:s\Z'),
            createdAt: $this->createdAt,
            updatedAt: date('Y-m-d\TH:i:s\Z')
        );
    }

    public function isGeocoded(): bool
    {
        return $this->status === 'success' && $this->latitude !== null && $this->longitude !== null;
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
