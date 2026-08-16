<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Result of a validation operation.
 *
 * Contains the validation outcome and any error messages or warnings.
 */
class ValidationResult
{
    public $isValid;

    public $errors;

    public $warnings;

    /**
     * @param bool $isValid Whether the validation passed
     * @param array<string> $errors List of error messages
     * @param array<string> $warnings List of warning messages
     */
    public function __construct(
        bool $isValid,
        array $errors = [],
        array $warnings = []
    ) {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    /**
     * Create a successful validation result.
     *
     * @return self
     */
    public static function success(): self
    {
        return new self(true, [], []);
    }

    /**
     * Create a failed validation result with errors.
     *
     * @param array<string> $errors The error messages
     * @param array<string> $warnings The warning messages
     * @return self
     */
    public static function failure(array $errors, array $warnings = []): self
    {
        return new self(false, $errors, $warnings);
    }

    /**
     * Add an error to the result.
     *
     * @param string $error The error message
     * @return self New instance with the error added
     */
    public function withError(string $error): self
    {
        return new self(false, array_merge($this->errors, [$error]), $this->warnings);
    }

    /**
     * Add a warning to the result.
     *
     * @param string $warning The warning message
     * @return self New instance with the warning added
     */
    public function withWarning(string $warning): self
    {
        return new self($this->isValid, $this->errors, array_merge($this->warnings, [$warning]));
    }

    /**
     * Check if there are any errors.
     *
     * @return bool True if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if there are any warnings.
     *
     * @return bool True if there are warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get all messages (errors and warnings combined).
     *
     * @return array<string> All messages
     */
    public function getAllMessages(): array
    {
        return array_merge($this->errors, $this->warnings);
    }
}
