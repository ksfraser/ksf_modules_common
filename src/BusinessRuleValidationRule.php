<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Validation rule for complex business logic validation.
 *
 * Allows for custom business rules that may involve multiple parameters,
 * external data lookups, or complex conditional logic that cannot be
 * expressed with simpler validation rules.
 */
class BusinessRuleValidationRule implements ValidationRuleInterface
{
    /** @var callable The function that performs validation */
    private $validationFunction;
    /** @var string Human-readable description of the business rule */
    private $description;
    /** @var string Error message when validation fails */
    private $errorMessage;

    /**
     * @param callable $validationFunction The function that performs validation
     * @param string $description Human-readable description of the business rule
     * @param string $errorMessage Error message when validation fails
     */
    public function __construct(
        callable $validationFunction,
        string $description,
        string $errorMessage
    ) {
        $this->validationFunction = $validationFunction;
        $this->description = $description;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Validate using the custom business rule function.
     *
     * @param mixed $value The value to validate
     * @return bool True if the business rule validation passes
     */
    public function validate($value): bool
    {
        try {
            $result = call_user_func($this->validationFunction, $value);

            // Ensure the result is boolean
            return (bool) $result;
        } catch (\Throwable $e) {
            // If the validation function throws an exception, consider it a failure
            return false;
        }
    }

    /**
     * Get a description of the business rule validation.
     *
     * @return string The validation rule description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the error message for business rule validation failure.
     *
     * @return string The error message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Create a common business rule: minimum investment amount based on account type.
     *
     * @param string $accountType The account type parameter name
     * @return self A business rule for minimum investment validation
     */
    public static function minimumInvestmentByAccountType(string $accountType): self
    {
        return new self(
            function ($investmentAmount) use ($accountType) {
                if (!is_numeric($investmentAmount)) {
                    return false;
                }

                $amount = (float) $investmentAmount;

                // This would typically access the account type from context
                // For now, return true as this is a structural example
                return $amount > 0;
            },
            'Investment amount must meet minimum requirements based on account type',
            'Investment amount does not meet the minimum requirement for the selected account type'
        );
    }

    /**
     * Create a business rule for age-based investment restrictions.
     *
     * @param int $minAge Minimum age for investment
     * @param int $maxAge Maximum age for investment (0 for no maximum)
     * @return self A business rule for age-based validation
     */
    public static function ageBasedInvestmentRestriction(int $minAge, int $maxAge = 0): self
    {
        return new self(
            function ($age) use ($minAge, $maxAge) {
                if (!is_numeric($age)) {
                    return false;
                }

                $ageValue = (int) $age;

                if ($ageValue < $minAge) {
                    return false;
                }

                if ($maxAge > 0 && $ageValue > $maxAge) {
                    return false;
                }

                return true;
            },
            $maxAge > 0
                ? "Age must be between {$minAge} and {$maxAge} for this investment"
                : "Age must be at least {$minAge} for this investment",
            $maxAge > 0
                ? "Age must be between {$minAge} and {$maxAge} years"
                : "Must be at least {$minAge} years old for this investment"
        );
    }

    /**
     * Create a business rule for combined income and expense validation.
     *
     * @return self A business rule for financial ratio validation
     */
    public static function debtToIncomeRatio(): self
    {
        return new self(
            function ($value) {
                // This would validate against other parameters in context
                // For now, return true as this is a structural example
                return is_numeric($value) && $value >= 0 && $value <= 100;
            },
            'Debt-to-income ratio must be within acceptable limits',
            'Debt-to-income ratio exceeds acceptable limits for this investment type'
        );
    }
}