<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Defines a parameter required or optional for a calculation.
 *
 * Provides metadata about calculation parameters including type, validation rules,
 * and default values.
 */
class ParameterDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $description,
        public readonly bool $required = true,
        public readonly mixed $defaultValue = null,
        public readonly ?ValidationRuleInterface $validationRule = null,
        public readonly ?array $allowedValues = null
    ) {}

    /**
     * Validate a value against this parameter definition.
     *
     * @param mixed $value The value to validate
     * @return bool True if valid
     */
    public function validate(mixed $value): bool
    {
        // Type validation
        if (!$this->validateType($value)) {
            return false;
        }

        // Allowed values validation
        if ($this->allowedValues !== null && !in_array($value, $this->allowedValues, true)) {
            return false;
        }

        // Custom validation rule
        if ($this->validationRule !== null) {
            return $this->validationRule->validate($value);
        }

        return true;
    }

    /**
     * Validate the type of a value.
     *
     * @param mixed $value The value to validate
     * @return bool True if type is valid
     */
    private function validateType(mixed $value): bool
    {
        return match ($this->type) {
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value) || is_int($value),
            'string' => is_string($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'number' => is_numeric($value),
            default => true // Allow any type for custom types
        };
    }
}