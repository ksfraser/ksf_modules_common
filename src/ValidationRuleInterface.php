<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Contract for validation rules used in parameter validation.
 *
 * Defines the interface that all validation rules must implement,
 * allowing for custom validation logic beyond basic type checking.
 */
interface ValidationRuleInterface
{
    /**
     * Validate a value against this rule.
     *
     * @param mixed $value The value to validate
     * @return bool True if the value passes validation
     */
    public function validate(mixed $value): bool;

    /**
     * Get a human-readable description of what this rule validates.
     *
     * @return string The validation rule description
     */
    public function getDescription(): string;

    /**
     * Get the error message to display when validation fails.
     *
     * @return string The error message
     */
    public function getErrorMessage(): string;
}