<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

use DateTimeImmutable;

/**
 * Context container for calculation inputs and metadata.
 *
 * Provides a standardized way to pass input data to calculation engines,
 * along with metadata about the calculation request.
 */
class CalculationContext
{
    public $calculationType;

    public $parameters;

    public $assumptions;

    public $clientId;

    public $advisorId;

    public $effectiveDate;

    public $metadata;

    /**
     * @param string $calculationType The type of calculation being performed
     * @param array<string, mixed> $parameters The calculation parameters
     * @param array<string, mixed> $assumptions Override assumptions for this calculation
     * @param string|null $clientId The client identifier
     * @param string|null $advisorId The advisor identifier
     * @param DateTimeImmutable|null $effectiveDate The date the calculation is effective
     * @param array<string, mixed> $metadata Additional metadata
     */
    public function __construct(
        string $calculationType,
        array $parameters,
        array $assumptions = [],
        ?string $clientId = null,
        ?string $advisorId = null,
        ?DateTimeImmutable $effectiveDate = null,
        array $metadata = []
    ) {
        $this->calculationType = $calculationType;
        $this->parameters = $parameters;
        $this->assumptions = $assumptions;
        $this->clientId = $clientId;
        $this->advisorId = $advisorId;
        $this->effectiveDate = $effectiveDate;
        $this->metadata = $metadata;
    }

    /**
     * Get a parameter value by name.
     *
     * @param string $name The parameter name
     * @param mixed $default The default value if parameter not found
     * @return mixed The parameter value or default
     */
    public function getParameter(string $name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Check if a parameter exists.
     *
     * @param string $name The parameter name
     * @return bool True if the parameter exists
     */
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Get an assumption value by name.
     *
     * @param string $name The assumption name
     * @param mixed $default The default value if assumption not found
     * @return mixed The assumption value or default
     */
    public function getAssumption(string $name, $default = null)
    {
        return $this->assumptions[$name] ?? $default;
    }

    /**
     * Get metadata value by name.
     *
     * @param string $name The metadata name
     * @param mixed $default The default value if metadata not found
     * @return mixed The metadata value or default
     */
    public function getMetadata(string $name, $default = null)
    {
        return $this->metadata[$name] ?? $default;
    }

    /**
     * Create a new context with additional parameters.
     *
     * @param array<string, mixed> $additionalParameters Parameters to add
     * @return self New context instance
     */
    public function withParameters(array $additionalParameters): self
    {
        return new self(
            $this->calculationType,
            array_merge($this->parameters, $additionalParameters),
            $this->assumptions,
            $this->clientId,
            $this->advisorId,
            $this->effectiveDate,
            $this->metadata
        );
    }

    /**
     * Create a new context with additional assumptions.
     *
     * @param array<string, mixed> $additionalAssumptions Assumptions to add
     * @return self New context instance
     */
    public function withAssumptions(array $additionalAssumptions): self
    {
        return new self(
            $this->calculationType,
            $this->parameters,
            array_merge($this->assumptions, $additionalAssumptions),
            $this->clientId,
            $this->advisorId,
            $this->effectiveDate,
            $this->metadata
        );
    }

    /**
     * Create a new context with updated metadata.
     *
     * @param array<string, mixed> $additionalMetadata Metadata to add
     * @return self New context instance
     */
    public function withMetadata(array $additionalMetadata): self
    {
        return new self(
            $this->calculationType,
            $this->parameters,
            $this->assumptions,
            $this->clientId,
            $this->advisorId,
            $this->effectiveDate,
            array_merge($this->metadata, $additionalMetadata)
        );
    }
}
