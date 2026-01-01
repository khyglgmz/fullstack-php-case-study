<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.boolean' => 'is_active parametresi boolean (true/false) olmalıdır',
            'min_price.numeric' => 'min_price parametresi sayısal bir değer olmalıdır',
            'min_price.min' => 'min_price parametresi 0 veya daha büyük olmalıdır',
            'max_price.numeric' => 'max_price parametresi sayısal bir değer olmalıdır',
            'max_price.min' => 'max_price parametresi 0 veya daha büyük olmalıdır',
            'max_price.gte' => 'max_price parametresi min_price değerinden büyük veya eşit olmalıdır',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && $this->is_active !== null && $this->is_active !== '') {
            $value = $this->is_active;

            $validValues = ['true', 'false', '1', '0', true, false, 1, 0];

            if (is_string($value)) {
                $value = strtolower($value);
            }

            if (!in_array($value, $validValues, true)) {
                return;
            }

            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Validasyon hatası',
                    'details' => $validator->errors(),
                ],
            ], 422)
        );
    }
}
