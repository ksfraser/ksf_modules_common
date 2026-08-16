<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Exception thrown when calculation operations fail.
 *
 * Provides detailed error information for calculation failures,
 * including the calculation type and context information.
 */
class CalculationException extends \Exception
{
    public $calculationType;

    public $context;

    /**
     * @param string $message The error message
     * @param string $calculationType The type of calculation that failed
     * @param array<string, mixed> $context Additional context information
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message,
        string $calculationType,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->calculationType = $calculationType;
        $this->context = $context;
    }

    /**
     * Create an exception for invalid input parameters.
     *
     * @param string $calculationType The calculation type
     * @param array<string> $missingParameters The missing required parameters
     * @return self
     */
    public static function invalidParameters(
        string $calculationType,
        array $missingParameters
    ): self {
        $message = sprintf(
            'Missing required parameters for %s calculation: %s',
            $calculationType,
            implode(', ', $missingParameters)
        );

        return new self($message, $calculationType, [
            'missing_parameters' => $missingParameters
        ]);
    }

    /**
     * Create an exception for calculation logic errors.
     *
     * @param string $calculationType The calculation type
     * @param string $reason The reason for the failure
     * @param array<string, mixed> $context Additional context
     * @return self
     */
    public static function calculationError(
        string $calculationType,
        string $reason,
        array $context = []
    ): self {
        return new self($reason, $calculationType, $context);
    }
}
