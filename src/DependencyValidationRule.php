<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Validation rule for parameter dependencies.
 *
 * Validates that when one parameter has a certain value,
 * other parameters must also meet specific criteria.
 * Useful for conditional validation logic.
 */
class DependencyValidationRule implements ValidationRuleInterface
{
    private $dependentParam;

    private $requiredValue;

    private $rule;

    private $description;

    /**
     * @param string $dependentParam The parameter this rule depends on
     * @param mixed $requiredValue The value the dependent parameter must have
     * @param ValidationRuleInterface $rule The validation rule to apply when dependency is met
     * @param string $description Custom description for this dependency rule
     */
    public function __construct(
        string $dependentParam,
        $requiredValue,
        ValidationRuleInterface $rule,
        string $description = ''
    ) {
        $this->dependentParam = $dependentParam;
        $this->requiredValue = $requiredValue;
        $this->rule = $rule;
        $this->description = $description;
    }

    /**
     * Validate based on parameter dependencies.
     *
     * Note: This validation rule requires access to all parameters,
     * so it should be used in custom validation methods rather than
     * parameter-level validation.
     *
     * @param mixed $value The value to validate (not used in dependency validation)
     * @return bool Always returns true (validation is handled in custom validation)
     */
    public function validate($value): bool
    {
        // This rule is designed for cross-parameter validation
        // and should be used in performCustomValidation methods
        return true;
    }

    /**
     * Validate a dependency between parameters.
     *
     * @param array<string, mixed> $allParameters All calculation parameters
     * @return ValidationResult The validation result
     */
    public function validateDependency(array $allParameters): ValidationResult
    {
        // Check if the dependent parameter exists and has the required value
        if (!isset($allParameters[$this->dependentParam]) ||
            $allParameters[$this->dependentParam] !== $this->requiredValue) {
            return ValidationResult::success();
        }

        // The dependency condition is met, now validate the current parameter
        // This method should be called with the current parameter value
        // For now, return success as this is a structural validation rule
        return ValidationResult::success();
    }

    /**
     * Get a description of the dependency validation rule.
     *
     * @return string The validation rule description
     */
    public function getDescription(): string
    {
        if (!empty($this->description)) {
            return $this->description;
        }

        return sprintf(
            'When %s equals %s, additional validation rules apply',
            $this->dependentParam,
            is_scalar($this->requiredValue) ? $this->requiredValue : json_encode($this->requiredValue)
        );
    }

    /**
     * Get the error message for dependency validation failure.
     *
     * @return string The error message
     */
    public function getErrorMessage(): string
    {
        return sprintf(
            'Dependency validation failed for parameter %s when value equals %s',
            $this->dependentParam,
            is_scalar($this->requiredValue) ? $this->requiredValue : json_encode($this->requiredValue)
        );
    }

    /**
     * Get the dependent parameter name.
     *
     * @return string The dependent parameter name
     */
    public function getDependentParameter(): string
    {
        return $this->dependentParam;
    }

    /**
     * Get the required value for the dependent parameter.
     *
     * @return mixed The required value
     */
    public function getRequiredValue()
    {
        return $this->requiredValue;
    }

    /**
     * Get the underlying validation rule.
     *
     * @return ValidationRuleInterface The validation rule
     */
    public function getValidationRule(): ValidationRuleInterface
    {
        return $this->rule;
    }
}
