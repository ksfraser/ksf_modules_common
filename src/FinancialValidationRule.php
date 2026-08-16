<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Base class for financial-specific validation rules.
 *
 * Provides common functionality for financial validation rules,
 * including currency formatting and financial data validation.
 */
abstract class FinancialValidationRule implements ValidationRuleInterface
{
    /**
     * Validate that a value is a valid monetary amount.
     *
     * @param mixed $value The value to validate
     * @param int $decimals Maximum number of decimal places allowed
     * @return bool True if the value is a valid monetary amount
     */
    protected function validateMonetaryAmount($value, int $decimals = 2): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $stringValue = (string) $value;

        // Check for valid decimal format
        if (!preg_match('/^-?\d+(\.\d{1,' . $decimals . '})?$/', $stringValue)) {
            return false;
        }

        return true;
    }

    /**
     * Validate that a value is a valid percentage (0-100).
     *
     * @param mixed $value The value to validate
     * @return bool True if the value is a valid percentage
     */
    protected function validatePercentage($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $numericValue = (float) $value;
        return $numericValue >= 0 && $numericValue <= 100;
    }

    /**
     * Validate that a value is a positive number.
     *
     * @param mixed $value The value to validate
     * @return bool True if the value is positive
     */
    protected function validatePositive($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value > 0;
    }

    /**
     * Validate that a value is non-negative (zero or positive).
     *
     * @param mixed $value The value to validate
     * @return bool True if the value is non-negative
     */
    protected function validateNonNegative($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value >= 0;
    }

    /**
     * Format a monetary value for display.
     *
     * @param mixed $value The value to format
     * @return string The formatted monetary value
     */
    protected function formatMonetary($value): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        return '$' . number_format((float) $value, 2);
    }

    /**
     * Format a percentage value for display.
     *
     * @param mixed $value The value to format
     * @return string The formatted percentage value
     */
    protected function formatPercentage($value): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        return number_format((float) $value, 2) . '%';
    }
}
