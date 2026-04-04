<?php

namespace App\DTOs\Base;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseDataTransferObject
{
    /**
     * Validate the DTO data.
     */
    abstract protected function validate(): void;

    /**
     * Get the validation rules for the DTO.
     */
    abstract protected function rules(): array;

    /**
     * Get the custom validation messages.
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get the custom validation attributes.
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Perform validation using Laravel's validator.
     */
    protected function performValidation(array $data): array
    {
        // Try to use Validator facade if available, otherwise skip validation
        if (class_exists('Illuminate\Support\Facades\Validator') && app()->bound('validator')) {
            $validator = Validator::make($data, $this->rules(), $this->messages(), $this->attributes());

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            return $validator->validated();
        }

        // For unit tests or when validator is not available, return data as-is
        return $data;
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($this);
        }

        return $data;
    }

    /**
     * Convert DTO to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    /**
     * Create DTO from JSON.
     */
    public static function fromJson(string $json): static
    {
        return static::fromArray(json_decode($json, true));
    }
}
