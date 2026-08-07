<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

use DateTimeImmutable;

/**
 * Standardized result container for calculation outcomes.
 *
 * Provides a consistent format for all calculation results, including
 * the main result, intermediate calculations, assumptions used, and metadata.
 */
class CalculationResult
{
    /**
     * @param string $calculationType The type of calculation performed
     * @param mixed $primaryResult The main calculation result
     * @param array<string, mixed> $intermediateResults Breakdown of intermediate calculations
     * @param array<string, mixed> $assumptionsUsed The assumptions that were used
     * @param ValidationResult $validationResult The validation result
     * @param array<string, mixed> $metadata Additional metadata about the calculation
     * @param DateTimeImmutable $calculatedAt When the calculation was performed
     * @param string|null $calculationId Unique identifier for this calculation
     */
    public function __construct(
        public readonly string $calculationType,
        public readonly mixed $primaryResult,
        public readonly ValidationResult $validationResult,
        public readonly array $intermediateResults = [],
        public readonly array $assumptionsUsed = [],
        public readonly array $metadata = [],
        public readonly DateTimeImmutable $calculatedAt = new DateTimeImmutable(),
        public readonly ?string $calculationId = null
    ) {}

    /**
     * Create a successful calculation result.
     *
     * @param string $calculationType The calculation type
     * @param mixed $primaryResult The main result
     * @param array<string, mixed> $intermediateResults Intermediate results
     * @param array<string, mixed> $assumptionsUsed Assumptions used
     * @param array<string, mixed> $metadata Additional metadata
     * @return self
     */
    public static function success(
        string $calculationType,
        mixed $primaryResult,
        array $intermediateResults = [],
        array $assumptionsUsed = [],
        array $metadata = []
    ): self {
        return new self(
            $calculationType,
            $primaryResult,
            ValidationResult::success(),
            $intermediateResults,
            $assumptionsUsed,
            $metadata
        );
    }

    /**
     * Create a failed calculation result.
     *
     * @param string $calculationType The calculation type
     * @param array<string> $errors The error messages
     * @param array<string> $warnings The warning messages
     * @param array<string, mixed> $metadata Additional metadata
     * @return self
     */
    public static function failure(
        string $calculationType,
        array $errors,
        array $warnings = [],
        array $metadata = []
    ): self {
        return new self(
            $calculationType,
            null,
            ValidationResult::failure($errors, $warnings),
            [],
            [],
            $metadata
        );
    }

    /**
     * Check if the calculation was successful.
     *
     * @return bool True if successful
     */
    public function isSuccessful(): bool
    {
        return $this->validationResult->isValid;
    }

    /**
     * Get an intermediate result by name.
     *
     * @param string $name The intermediate result name
     * @param mixed $default The default value if not found
     * @return mixed The intermediate result or default
     */
    public function getIntermediateResult(string $name, mixed $default = null): mixed
    {
        return $this->intermediateResults[$name] ?? $default;
    }

    /**
     * Get an assumption that was used.
     *
     * @param string $name The assumption name
     * @param mixed $default The default value if not found
     * @return mixed The assumption value or default
     */
    public function getAssumptionUsed(string $name, mixed $default = null): mixed
    {
        return $this->assumptionsUsed[$name] ?? $default;
    }

    /**
     * Get metadata value.
     *
     * @param string $name The metadata name
     * @param mixed $default The default value if not found
     * @return mixed The metadata value or default
     */
    public function getMetadata(string $name, mixed $default = null): mixed
    {
        return $this->metadata[$name] ?? $default;
    }

    /**
     * Get the primary results array.
     *
     * @return mixed The primary calculation results
     */
    public function getResults(): mixed
    {
        return $this->primaryResult;
    }

    /**
     * Convert the result to an array for serialization.
     *
     * @return array<string, mixed> The result as an array
     */
    public function toArray(): array
    {
        return [
            'calculation_id' => $this->calculationId,
            'calculation_type' => $this->calculationType,
            'primary_result' => $this->primaryResult,
            'intermediate_results' => $this->intermediateResults,
            'assumptions_used' => $this->assumptionsUsed,
            'validation_result' => [
                'is_valid' => $this->validationResult->isValid,
                'errors' => $this->validationResult->errors,
                'warnings' => $this->validationResult->warnings,
            ],
            'metadata' => $this->metadata,
            'calculated_at' => $this->calculatedAt->format('c'),
        ];
    }

    /**
     * Create a result from an array.
     *
     * @param array<string, mixed> $data The array data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $validationResult = new ValidationResult(
            $data['validation_result']['is_valid'] ?? false,
            $data['validation_result']['errors'] ?? [],
            $data['validation_result']['warnings'] ?? []
        );

        return new self(
            $data['calculation_type'] ?? '',
            $data['primary_result'] ?? null,
            $validationResult,
            $data['intermediate_results'] ?? [],
            $data['assumptions_used'] ?? [],
            $data['metadata'] ?? [],
            isset($data['calculated_at']) ? new DateTimeImmutable($data['calculated_at']) : new DateTimeImmutable(),
            $data['calculation_id'] ?? null
        );
    }
}