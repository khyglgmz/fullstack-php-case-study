<?php

declare(strict_types=1);

namespace App\Models;

class File
{
    public function __construct(
        public readonly string $id,
        public readonly string $originalName,
        public readonly string $storedName,
        public readonly ?string $description,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly string $checksum,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null,
        public readonly ?string $deletedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            originalName: $data['original_name'],
            storedName: $data['stored_name'],
            description: $data['description'] ?? null,
            size: (int) $data['size'],
            mimeType: $data['mime_type'],
            checksum: $data['checksum'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'] ?? null,
            deletedAt: $data['deleted_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->originalName,
            'stored_name' => $this->storedName,
            'description' => $this->description,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'checksum' => $this->checksum,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt
        ];
    }

    public function toPublicArray(): array
    {
        return [
            'fileId' => $this->id,
            'originalName' => $this->originalName,
            'description' => $this->description,
            'size' => $this->size,
            'mimeType' => $this->mimeType,
            'checksum' => $this->checksum,
            'createdAt' => $this->createdAt
        ];
    }
}
