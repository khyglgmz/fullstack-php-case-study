<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Location;
use Medoo\Medoo;

class LocationRepository
{
    private Medoo $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function save(Location $location): int
    {
        $data = $location->toArray();
        unset($data['id']);

        $this->db->insert('locations', $data);

        return (int) $this->db->id();
    }

    public function update(Location $location): bool
    {
        if ($location->id === null) {
            return false;
        }

        $data = $location->toArray();
        unset($data['id']);
        $data['updated_at'] = date('Y-m-d\TH:i:s\Z');

        $result = $this->db->update('locations', $data, ['id' => $location->id]);

        return $result->rowCount() > 0;
    }

    public function findById(int $id): ?Location
    {
        $row = $this->db->get('locations', '*', ['id' => $id]);

        if (!$row) {
            return null;
        }

        return Location::fromArray($row);
    }

    public function findByAddressId(int $addressId): ?Location
    {
        $row = $this->db->get('locations', '*', ['address_id' => $addressId]);

        if (!$row) {
            return null;
        }

        return Location::fromArray($row);
    }

    public function findAll(): array
    {
        $rows = $this->db->select('locations', '*', [
            'ORDER' => ['address_id' => 'ASC']
        ]);

        return array_map(fn($row) => Location::fromArray($row), $rows);
    }

    public function findByStatus(string $status): array
    {
        $rows = $this->db->select('locations', '*', [
            'status' => $status,
            'ORDER' => ['address_id' => 'ASC']
        ]);

        return array_map(fn($row) => Location::fromArray($row), $rows);
    }

    public function findPendingOrFailed(): array
    {
        $rows = $this->db->select('locations', '*', [
            'status' => ['pending', 'failed'],
            'ORDER' => ['address_id' => 'ASC']
        ]);

        return array_map(fn($row) => Location::fromArray($row), $rows);
    }

    public function countByStatus(): array
    {
        $total = $this->db->count('locations');
        $success = $this->db->count('locations', ['status' => 'success']);
        $failed = $this->db->count('locations', ['status' => 'failed']);
        $pending = $this->db->count('locations', ['status' => 'pending']);

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'pending' => $pending
        ];
    }

    public function deleteAll(): bool
    {
        $result = $this->db->delete('locations', ['id[>]' => 0]);
        return true;
    }

    public function upsert(Location $location): Location
    {
        $existing = $this->findByAddressId($location->addressId);

        if ($existing) {
            $updated = new Location(
                id: $existing->id,
                addressId: $location->addressId,
                title: $location->title,
                address: $location->address,
                latitude: $location->latitude,
                longitude: $location->longitude,
                status: $location->status,
                errorMessage: $location->errorMessage,
                geocodedAt: $location->geocodedAt,
                createdAt: $existing->createdAt,
                updatedAt: date('Y-m-d\TH:i:s\Z')
            );
            $this->update($updated);
            return $updated;
        }

        $id = $this->save($location);
        return new Location(
            id: $id,
            addressId: $location->addressId,
            title: $location->title,
            address: $location->address,
            latitude: $location->latitude,
            longitude: $location->longitude,
            status: $location->status,
            errorMessage: $location->errorMessage,
            geocodedAt: $location->geocodedAt,
            createdAt: $location->createdAt,
            updatedAt: $location->updatedAt
        );
    }
}
