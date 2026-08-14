<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Factory for creating validation rules.
 *
 * Provides static methods to create common validation rules
 * and supports custom rule creation for financial calculations.
 *
 * TODO-003: Validation Framework Enhancement
 */
class ValidationRuleFactory
{
    /** Test hook: when true, taxRate() validation always fails. */
    public static bool $forceFail = false;

    /**
     * Create a range validation rule.
     *
     * @param float|null $min The minimum allowed value (inclusive)
     * @param float|null $max The maximum allowed value (inclusive)
     * @return RangeValidationRule
     */
    public static function range(?float $min = null, ?float $max = null): RangeValidationRule
    {
        return new RangeValidationRule($min, $max);
    }

    /**
     * Create a positive number validation rule.
     *
     * @return BusinessRuleValidationRule
     */
    public static function positive(): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $value): bool {
                return is_numeric($value) && (float) $value > 0;
            },
            'Value must be a positive number',
            'Value must be greater than zero'
        );
    }

    /**
     * Create a non-negative number validation rule.
     *
     * @return BusinessRuleValidationRule
     */
    public static function nonNegative(): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $value): bool {
                return is_numeric($value) && (float) $value >= 0;
            },
            'Value must be a non-negative number',
            'Value must be zero or greater'
        );
    }

    /**
     * Create a percentage validation rule (0-100).
     *
     * @return BusinessRuleValidationRule
     */
    public static function percentage(): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $value): bool {
                if (!is_numeric($value)) {
                    return false;
                }
                $numericValue = (float) $value;
                return $numericValue >= 0 && $numericValue <= 100;
            },
            'Value must be a percentage between 0 and 100',
            'Value must be between 0 and 100 percent'
        );
    }

    /**
     * Create a monetary amount validation rule.
     *
     * @param int $maxDecimals Maximum decimal places allowed
     * @return BusinessRuleValidationRule
     */
    public static function monetaryAmount(int $maxDecimals = 2): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $value) use ($maxDecimals): bool {
                if (!is_numeric($value)) {
                    return false;
                }

                $stringValue = (string) $value;

                // Check for valid decimal format
                if (!preg_match('/^-?\d+(\.\d{1,' . $maxDecimals . '})?$/', $stringValue)) {
                    return false;
                }

                return true;
            },
            "Value must be a valid monetary amount (max {$maxDecimals} decimal places)",
            "Value must be a valid monetary amount with no more than {$maxDecimals} decimal places"
        );
    }

    /**
     * Create an age validation rule for adults (18-100).
     *
     * @return RangeValidationRule
     */
    public static function adultAge(): RangeValidationRule
    {
        return new RangeValidationRule(18, 100);
    }

    /**
     * Create a dependency validation rule.
     *
     * @param string $dependentParam The parameter this rule depends on
     * @param mixed $requiredValue The value the dependent parameter must have
     * @param ValidationRuleInterface $rule The validation rule to apply when dependency is met
     * @param string $description Custom description for this dependency rule
     * @return DependencyValidationRule
     */
    public static function dependency(
        string $dependentParam,
        mixed $requiredValue,
        ValidationRuleInterface $rule,
        string $description = ''
    ): DependencyValidationRule {
        return new DependencyValidationRule($dependentParam, $requiredValue, $rule, $description);
    }

    /**
     * Create a custom business rule validation.
     *
     * @param callable $validationFunction The function that performs validation
     * @param string $description Human-readable description of the business rule
     * @param string $errorMessage Error message when validation fails
     * @return BusinessRuleValidationRule
     */
    public static function custom(
        callable $validationFunction,
        string $description,
        string $errorMessage
    ): BusinessRuleValidationRule {
        return new BusinessRuleValidationRule($validationFunction, $description, $errorMessage);
    }

    /**
     * Create a minimum investment validation rule based on account type.
     *
     * @param array<string, float> $minimums Minimum amounts by account type
     * @return BusinessRuleValidationRule
     */
    public static function minimumInvestmentByAccountType(array $minimums): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $investmentAmount) use ($minimums): bool {
                if (!is_numeric($investmentAmount)) {
                    return false;
                }

                $amount = (float) $investmentAmount;

                // This would typically access the account type from context
                // For now, validate that amount is positive
                return $amount > 0;
            },
            'Investment amount must meet minimum requirements based on account type',
            'Investment amount does not meet the minimum requirement for the selected account type'
        );
    }

    /**
     * Create an age-based investment restriction rule.
     *
     * @param int $minAge Minimum age for investment
     * @param string $restrictionType Type of restriction (e.g., 'retirement', 'high_risk')
     * @return BusinessRuleValidationRule
     */
    public static function ageBasedRestriction(int $minAge, string $restrictionType): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $age) use ($minAge): bool {
                if (!is_numeric($age)) {
                    return false;
                }

                return (int) $age >= $minAge;
            },
            "Age must be {$minAge}+ for {$restrictionType} investments",
            "You must be at least {$minAge} years old for {$restrictionType} investments"
        );
    }

    /**
     * Create a combined income validation rule.
     *
     * @param float $minIncome Minimum total income required
     * @return BusinessRuleValidationRule
     */
    public static function combinedIncome(float $minIncome): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $income) use ($minIncome): bool {
                if (!is_numeric($income)) {
                    return false;
                }

                return (float) $income >= $minIncome;
            },
            "Combined annual income must be at least $" . number_format($minIncome, 0),
            "Combined annual income must be at least $" . number_format($minIncome, 0)
        );
    }

    /**
     * Create a tax rate validation rule.
     *
     * @return BusinessRuleValidationRule
     */
    public static function taxRate(): BusinessRuleValidationRule
    {
        $message = self::$forceFail
            ? 'forced-rate-invalid'
            : 'Tax rate must be a decimal value between 0.00 and 1.00';

        return new BusinessRuleValidationRule(
            function (mixed $rate): bool {
                if (self::$forceFail) {
                    return false;
                }

                if (!is_numeric($rate)) {
                    return false;
                }

                $numericRate = (float) $rate;
                return $numericRate >= 0 && $numericRate <= 1; // 0% to 100% as decimal
            },
            'Tax rate validation',
            $message
        );
    }

    /**
     * Create a Canadian SIN (Social Insurance Number) validation rule.
     *
     * @return BusinessRuleValidationRule
     */
    public static function canadianSIN(): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $sin): bool {
                if (!is_string($sin) && !is_numeric($sin)) {
                    return false;
                }

                $sinString = preg_replace('/\D/', '', (string) $sin);

                if (strlen($sinString) !== 9) {
                    return false;
                }

                // Canadian SIN validation algorithm
                $digits = str_split($sinString);
                $checksum = 0;

                for ($i = 0; $i < 9; $i++) {
                    $digit = (int) $digits[$i];

                    if ($i % 2 === 1) {
                        $digit *= 2;
                        if ($digit > 9) {
                            $digit -= 9;
                        }
                    }

                    $checksum += $digit;
                }

                return $checksum % 10 === 0;
            },
            'Value must be a valid Canadian Social Insurance Number (SIN)',
            'Invalid Canadian Social Insurance Number format'
        );
    }

    /**
     * Create a Canadian postal code validation rule.
     *
     * @return BusinessRuleValidationRule
     */
    public static function canadianPostalCode(): BusinessRuleValidationRule
    {
        return new BusinessRuleValidationRule(
            function (mixed $postalCode): bool {
                if (!is_string($postalCode)) {
                    return false;
                }

                // Canadian postal code format: A1A 1A1
                return preg_match('/^[A-Z]\d[A-Z] ?\d[A-Z]\d$/i', $postalCode) === 1;
            },
            'Value must be a valid Canadian postal code (A1A 1A1 format)',
            'Invalid Canadian postal code format (should be A1A 1A1)'
        );
    }
}