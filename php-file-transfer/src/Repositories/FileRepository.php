<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\File;
use Medoo\Medoo;

class FileRepository
{
    private Medoo $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function save(File $file): void
    {
        $data = $file->toArray();

        $this->db->insert('files', [
            'id' => $data['id'],
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'description' => $data['description'],
            'size' => $data['size'],
            'mime_type' => $data['mime_type'],
            'checksum' => $data['checksum'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        ]);
    }

    public function findById(string $id): ?File
    {
        $row = $this->db->get('files', '*', [
            'id' => $id,
            'deleted_at' => null
        ]);

        if (!$row) {
            return null;
        }

        return File::fromArray($row);
    }

    public function findAll(int $page, int $pageSize): array
    {
        $offset = ($page - 1) * $pageSize;

        $rows = $this->db->select('files', '*', [
            'deleted_at' => null,
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => [$offset, $pageSize]
        ]);

        return array_map(fn($row) => File::fromArray($row), $rows);
    }

    public function count(): int
    {
        return $this->db->count('files', [
            'deleted_at' => null
        ]);
    }

    public function softDelete(string $id): bool
    {
        $now = date('Y-m-d\TH:i:s\Z');

        $result = $this->db->update('files', [
            'deleted_at' => $now,
            'updated_at' => $now
        ], [
            'id' => $id,
            'deleted_at' => null
        ]);

        return $result->rowCount() > 0;
    }
}
