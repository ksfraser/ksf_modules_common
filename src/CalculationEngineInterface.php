<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Contract for all calculation engines in the Canada Life system.
 *
 * Defines the standard interface that all financial calculation engines must implement,
 * ensuring consistency and interchangeability across different calculation types.
 */
interface CalculationEngineInterface
{
    /**
     * Perform the calculation using the provided context.
     *
     * @param CalculationContext $context The calculation context containing all input data
     * @return CalculationResult The standardized calculation result
     * @throws CalculationException If calculation fails or input is invalid
     */
    public function calculate(CalculationContext $context): CalculationResult;

    /**
     * Validate the calculation context before performing calculations.
     *
     * @param CalculationContext $context The context to validate
     * @return ValidationResult The validation result
     */
    public function validate(CalculationContext $context): ValidationResult;

    /**
     * Get the calculation type identifier.
     *
     * @return string The unique identifier for this calculation type
     */
    public function getCalculationType(): string;

    /**
     * Get the list of required input parameters for this calculation.
     *
     * @return array<string, ParameterDefinition> Map of parameter names to definitions
     */
    public function getRequiredParameters(): array;

    /**
     * Get the list of optional input parameters for this calculation.
     *
     * @return array<string, ParameterDefinition> Map of parameter names to definitions
     */
    public function getOptionalParameters(): array;
}