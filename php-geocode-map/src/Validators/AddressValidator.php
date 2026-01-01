<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

class AddressValidator
{
    public function validateAddressData(array $addressData): void
    {
        if (!isset($addressData['id'])) {
            throw new ValidationException('Adres ID zorunludur', 'MISSING_ADDRESS_ID');
        }

        if (!is_int($addressData['id']) && !ctype_digit((string) $addressData['id'])) {
            throw new ValidationException('Adres ID bir tamsayı olmalıdır', 'INVALID_ADDRESS_ID');
        }

        if (!isset($addressData['title']) || trim($addressData['title']) === '') {
            throw new ValidationException('Adres başlığı zorunludur', 'MISSING_TITLE');
        }

        if (!isset($addressData['address']) || trim($addressData['address']) === '') {
            throw new ValidationException('Adres zorunludur', 'MISSING_ADDRESS');
        }

        if (strlen($addressData['address']) < 5) {
            throw new ValidationException('Adres çok kısa', 'ADDRESS_TOO_SHORT');
        }

        if (strlen($addressData['address']) > 500) {
            throw new ValidationException('Adres çok uzun (maksimum 500 karakter)', 'ADDRESS_TOO_LONG');
        }
    }

    public function validateAddressId(int $addressId): void
    {
        if ($addressId < 1) {
            throw new ValidationException('Adres ID pozitif bir tamsayı olmalıdır', 'INVALID_ADDRESS_ID');
        }
    }

    public function validateStatus(string $status): void
    {
        $allowedStatuses = ['success', 'failed'];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new ValidationException(
                'Geçersiz durum. Kabul edilen değerler: ' . implode(', ', $allowedStatuses),
                'INVALID_STATUS'
            );
        }
    }

    public function validateAddressList(array $addresses): void
    {
        if (empty($addresses)) {
            throw new ValidationException('Adres listesi boş olamaz', 'EMPTY_ADDRESS_LIST');
        }

        $ids = [];
        foreach ($addresses as $index => $address) {
            try {
                $this->validateAddressData($address);
            } catch (ValidationException $e) {
                throw new ValidationException(
                    "Geçersiz adres (index {$index}): " . $e->getMessage(),
                    $e->getErrorCode()
                );
            }

            if (in_array($address['id'], $ids, true)) {
                throw new ValidationException(
                    "Tekrar eden adres ID: {$address['id']}",
                    'DUPLICATE_ADDRESS_ID'
                );
            }

            $ids[] = $address['id'];
        }
    }
}
