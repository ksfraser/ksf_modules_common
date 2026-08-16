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
    public $name;

    public $type;

    public $description;

    public $required;

    public $defaultValue;

    public $validationRule;

    public $allowedValues;

    public function __construct(
        string $name,
        string $type,
        string $description,
        bool $required = true,
        $defaultValue = null,
        ?ValidationRuleInterface $validationRule = null,
        ?array $allowedValues = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->validationRule = $validationRule;
        $this->allowedValues = $allowedValues;
    }

    /**
     * Validate a value against this parameter definition.
     *
     * @param mixed $value The value to validate
     * @return bool True if valid
     */
    public function validate($value): bool
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
    private function validateType($value): bool
    {
        switch ($this->type) {
            case 'int':
            case 'integer':
                return is_int($value);
            case 'float':
            case 'double':
                return is_float($value) || is_int($value);
            case 'string':
                return is_string($value);
            case 'bool':
            case 'boolean':
                return is_bool($value);
            case 'array':
                return is_array($value);
            case 'number':
                return is_numeric($value);
            default:
                return true; // Allow any type for custom types
        }
    }
}
