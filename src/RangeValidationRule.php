<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Validation rule for numeric ranges.
 *
 * Validates that a numeric value falls within a specified range,
 * with optional minimum and maximum bounds.
 */
class RangeValidationRule implements ValidationRuleInterface
{
    /**
     * @param float|null $min The minimum allowed value (inclusive)
     * @param float|null $max The maximum allowed value (inclusive)
     */
    public function __construct(
        private readonly ?float $min = null,
        private readonly ?float $max = null
    ) {
        if ($this->min === null && $this->max === null) {
            throw new \InvalidArgumentException('At least one of min or max must be specified');
        }

        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new \InvalidArgumentException('Minimum value cannot be greater than maximum value');
        }
    }

    /**
     * Validate that the value is within the specified range.
     *
     * @param mixed $value The value to validate
     * @return bool True if the value is within range
     */
    public function validate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $numericValue = (float) $value;

        if ($this->min !== null && $numericValue < $this->min) {
            return false;
        }

        if ($this->max !== null && $numericValue > $this->max) {
            return false;
        }

        return true;
    }

    /**
     * Get a description of the range validation rule.
     *
     * @return string The validation rule description
     */
    public function getDescription(): string
    {
        if ($this->min !== null && $this->max !== null) {
            return sprintf('Value must be between %s and %s (inclusive)', $this->min, $this->max);
        } elseif ($this->min !== null) {
            return sprintf('Value must be greater than or equal to %s', $this->min);
        } else {
            return sprintf('Value must be less than or equal to %s', $this->max);
        }
    }

    /**
     * Get the error message for range validation failure.
     *
     * @return string The error message
     */
    public function getErrorMessage(): string
    {
        if ($this->min !== null && $this->max !== null) {
            return sprintf('Value must be between %s and %s', $this->min, $this->max);
        } elseif ($this->min !== null) {
            return sprintf('Value must be at least %s', $this->min);
        } else {
            return sprintf('Value must be at most %s', $this->max);
        }
    }
}