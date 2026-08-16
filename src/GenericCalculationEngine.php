<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

use Psr\Log\LoggerInterface;

/**
 * Generic calculation engine providing common functionality for all financial calculations.
 *
 * This base class implements the CalculationEngineInterface and provides:
 * - Parameter validation
 * - Assumption management
 * - Result formatting
 * - Error handling
 * - Logging
 *
 * Specific calculation engines should extend this class and implement the
 * performCalculation method with their specific business logic.
 */
abstract class GenericCalculationEngine implements CalculationEngineInterface
{
    protected $logger;

    /**
     * @param LoggerInterface|null $logger Optional logger for calculation events
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Perform the calculation using the provided context.
     *
     * @param CalculationContext $context The calculation context containing all input data
     * @return CalculationResult The standardized calculation result
     * @throws CalculationException If calculation fails or input is invalid
     */
    final public function calculate(CalculationContext $context): CalculationResult
    {
        if ($this->logger !== null) {
            $this->logger->info('Starting calculation', [
                'type' => $context->calculationType,
                'client_id' => $context->clientId,
                'advisor_id' => $context->advisorId
            ]);
        }

        try {
            // Validate the context
            $validationResult = $this->validate($context);
            if (!$validationResult->isValid) {
                if ($this->logger !== null) {
                    $this->logger->warning('Validation failed', [
                        'type' => $context->calculationType,
                        'errors' => $validationResult->errors
                    ]);
                }

                return CalculationResult::failure(
                    $context->calculationType,
                    $validationResult->errors,
                    $validationResult->warnings
                );
            }

            // Perform the actual calculation
            $result = $this->performCalculation($context);

            if ($this->logger !== null) {
                $this->logger->info('Calculation completed successfully', [
                    'type' => $context->calculationType,
                    'result_type' => gettype($result)
                ]);
            }

            // Get assumptions used (merge defaults with overrides)
            $assumptionsUsed = $this->getAssumptionsUsed($context);

            return CalculationResult::success(
                $context->calculationType,
                $result,
                $this->getIntermediateResults(),
                $assumptionsUsed,
                [
                    'client_id' => $context->clientId,
                    'advisor_id' => $context->advisorId,
                    'effective_date' => $context->effectiveDate !== null ? $context->effectiveDate->format('c') : null,
                    'calculation_engine' => get_called_class()
                ]
            );

        } catch (CalculationException $e) {
            if ($this->logger !== null) {
                $this->logger->error('Calculation exception', [
                    'type' => $context->calculationType,
                    'message' => $e->getMessage(),
                    'context' => $e->context
                ]);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($this->logger !== null) {
                $this->logger->error('Unexpected calculation error', [
                    'type' => $context->calculationType,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e)
                ]);
            }

            throw CalculationException::calculationError(
                $context->calculationType,
                'An unexpected error occurred during calculation: ' . $e->getMessage(),
                ['original_exception' => get_class($e)]
            );
        }
    }

    /**
     * Validate the calculation context before performing calculations.
     *
     * @param CalculationContext $context The context to validate
     * @return ValidationResult The validation result
     */
    public function validate(CalculationContext $context): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Check calculation type matches
        if ($context->calculationType !== $this->getCalculationType()) {
            $errors[] = sprintf(
                'Calculation type mismatch. Expected %s, got %s',
                $this->getCalculationType(),
                $context->calculationType
            );
        }

        // Validate required parameters
        $requiredParams = $this->getRequiredParameters();
        $missingParams = [];
        foreach ($requiredParams as $paramName => $paramDef) {
            if (!$context->hasParameter($paramName)) {
                $missingParams[] = $paramName;
            } elseif (!$paramDef->validate($context->getParameter($paramName))) {
                $errors[] = sprintf(
                    'Parameter %s failed validation: %s',
                    $paramName,
                    $paramDef->validationRule !== null ? $paramDef->validationRule->getErrorMessage() : 'Invalid value'
                );
            }
        }

        if (!empty($missingParams)) {
            $errors[] = 'Missing required parameters: ' . implode(', ', $missingParams);
        }

        // Validate optional parameters
        $optionalParams = $this->getOptionalParameters();
        foreach ($optionalParams as $paramName => $paramDef) {
            if ($context->hasParameter($paramName) &&
                !$paramDef->validate($context->getParameter($paramName))) {
                $warnings[] = sprintf(
                    'Optional parameter %s failed validation: %s',
                    $paramName,
                    $paramDef->validationRule !== null ? $paramDef->validationRule->getErrorMessage() : 'Invalid value'
                );
            }
        }

        // Perform custom validation
        $customValidation = $this->performCustomValidation($context);
        $errors = array_merge($errors, $customValidation->errors);
        $warnings = array_merge($warnings, $customValidation->warnings);

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    /**
     * Get the calculation type identifier.
     *
     * @return string The unique identifier for this calculation type
     */
    abstract public function getCalculationType(): string;

    /**
     * Get the list of required input parameters for this calculation.
     *
     * @return array<string, ParameterDefinition> Map of parameter names to definitions
     */
    abstract public function getRequiredParameters(): array;

    /**
     * Get the list of optional input parameters for this calculation.
     *
     * @return array<string, ParameterDefinition> Map of parameter names to definitions
     */
    abstract public function getOptionalParameters(): array;

    /**
     * Perform the actual calculation logic.
     *
     * This method should be implemented by concrete calculation engines
     * to provide the specific business logic for their calculations.
     *
     * @param CalculationContext $context The validated calculation context
     * @return mixed The primary calculation result
     * @throws CalculationException If the calculation logic fails
     */
    abstract protected function performCalculation(CalculationContext $context);

    /**
     * Perform custom validation beyond parameter validation.
     *
     * Override this method in subclasses to add business-specific validation rules.
     *
     * @param CalculationContext $context The context to validate
     * @return ValidationResult Additional validation results
     */
    protected function performCustomValidation(CalculationContext $context): ValidationResult
    {
        return ValidationResult::success();
    }

    /**
     * Get the assumptions that were used in the calculation.
     *
     * This method combines default assumptions with context overrides.
     *
     * @param CalculationContext $context The calculation context
     * @return array<string, mixed> The assumptions used
     */
    protected function getAssumptionsUsed(CalculationContext $context): array
    {
        // Get default assumptions from subclass
        $defaults = $this->getDefaultAssumptions();

        // Apply overrides from context
        return array_merge($defaults, $context->assumptions);
    }

    /**
     * Get the default assumptions for this calculation type.
     *
     * Override this method in subclasses to provide default assumption values.
     *
     * @return array<string, mixed> Default assumptions
     */
    protected function getDefaultAssumptions(): array
    {
        return [];
    }

    /**
     * Get intermediate calculation results.
     *
     * Override this method in subclasses to return intermediate calculation steps.
     *
     * @return array<string, mixed> Intermediate results
     */
    protected function getIntermediateResults(): array
    {
        return [];
    }

    /**
     * Helper method to validate numeric parameters are positive.
     *
     * @param mixed $value The value to check
     * @param string $paramName The parameter name for error messages
     * @return ValidationResult Validation result
     */
    protected function validatePositiveNumber($value, string $paramName): ValidationResult
    {
        if (!is_numeric($value) || $value <= 0) {
            return ValidationResult::failure([
                sprintf('%s must be a positive number, got: %s', $paramName, $value)
            ]);
        }
        return ValidationResult::success();
    }

    /**
     * Helper method to validate percentage values (0-100).
     *
     * @param mixed $value The value to check
     * @param string $paramName The parameter name for error messages
     * @return ValidationResult Validation result
     */
    protected function validatePercentage($value, string $paramName): ValidationResult
    {
        if (!is_numeric($value) || $value < 0 || $value > 100) {
            return ValidationResult::failure([
                sprintf('%s must be a percentage between 0 and 100, got: %s', $paramName, $value)
            ]);
        }
        return ValidationResult::success();
    }
}
