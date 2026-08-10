<?php

declare(strict_types=1);

namespace Ksfraser\ModulesCommon\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test suite for ValidationRuleFactory.
 *
 * TODO-003: Validation Framework Enhancement
 */
class ValidationRuleFactoryTest extends TestCase
{
    public function testRangeCreatesRangeValidationRule(): void
    {
        $rule = ValidationRuleFactory::range(10, 100);

        $this->assertInstanceOf(RangeValidationRule::class, $rule);
        $this->assertTrue($rule->validate(50));
        $this->assertFalse($rule->validate(150));
        $this->assertStringContainsString('between 10 and 100', $rule->getDescription());
    }

    public function testPositiveCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::positive();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(10));
        $this->assertFalse($rule->validate(-5));
        $this->assertFalse($rule->validate(0));
        $this->assertStringContainsString('positive number', $rule->getDescription());
    }

    public function testNonNegativeCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::nonNegative();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(10));
        $this->assertTrue($rule->validate(0));
        $this->assertFalse($rule->validate(-5));
        $this->assertStringContainsString('non-negative number', $rule->getDescription());
    }

    public function testPercentageCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::percentage();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(50));
        $this->assertTrue($rule->validate(0));
        $this->assertTrue($rule->validate(100));
        $this->assertFalse($rule->validate(150));
        $this->assertFalse($rule->validate(-5));
        $this->assertStringContainsString('percentage between 0 and 100', $rule->getDescription());
    }

    public function testMonetaryAmountCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::monetaryAmount(2);

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(100.50));
        $this->assertTrue($rule->validate(100));
        $this->assertFalse($rule->validate(100.123)); // Too many decimals
        $this->assertFalse($rule->validate('not-a-number'));
        $this->assertStringContainsString('monetary amount', $rule->getDescription());
    }

    public function testAdultAgeCreatesRangeValidationRule(): void
    {
        $rule = ValidationRuleFactory::adultAge();

        $this->assertInstanceOf(RangeValidationRule::class, $rule);
        $this->assertTrue($rule->validate(25));
        $this->assertTrue($rule->validate(18));
        $this->assertTrue($rule->validate(100));
        $this->assertFalse($rule->validate(17));
        $this->assertFalse($rule->validate(101));
        $this->assertStringContainsString('between 18 and 100', $rule->getDescription());
    }

    public function testDependencyCreatesDependencyValidationRule(): void
    {
        $baseRule = ValidationRuleFactory::positive();
        $rule = ValidationRuleFactory::dependency('accountType', 'retirement', $baseRule);

        $this->assertInstanceOf(DependencyValidationRule::class, $rule);
        $this->assertStringContainsString('accountType equals retirement', $rule->getDescription());
    }

    public function testCustomCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::custom(
            function (mixed $value): bool {
                return is_string($value) && strlen($value) > 3;
            },
            'String must be longer than 3 characters',
            'String is too short'
        );

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate('hello'));
        $this->assertFalse($rule->validate('hi'));
        $this->assertFalse($rule->validate(123));
        $this->assertEquals('String must be longer than 3 characters', $rule->getDescription());
        $this->assertEquals('String is too short', $rule->getErrorMessage());
    }

    public function testMinimumInvestmentByAccountTypeCreatesBusinessRuleValidationRule(): void
    {
        $minimums = ['RRSP' => 1000, 'TFSA' => 500];
        $rule = ValidationRuleFactory::minimumInvestmentByAccountType($minimums);

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(1500)); // Positive amount
        $this->assertFalse($rule->validate(-100)); // Negative amount
        $this->assertStringContainsString('minimum requirements based on account type', $rule->getDescription());
    }

    public function testAgeBasedRestrictionCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::ageBasedRestriction(65, 'retirement');

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(70));
        $this->assertFalse($rule->validate(60));
        $this->assertStringContainsString('65+ for retirement investments', $rule->getDescription());
        $this->assertStringContainsString('at least 65 years old', $rule->getErrorMessage());
    }

    public function testCombinedIncomeCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::combinedIncome(50000);

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(75000));
        $this->assertFalse($rule->validate(30000));
        $this->assertStringContainsString('at least $50,000', $rule->getDescription());
    }

    public function testTaxRateCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::taxRate();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);
        $this->assertTrue($rule->validate(0.25)); // 25%
        $this->assertTrue($rule->validate(0.0)); // 0%
        $this->assertTrue($rule->validate(1.0)); // 100%
        $this->assertFalse($rule->validate(1.5)); // Over 100%
        $this->assertFalse($rule->validate(-0.1)); // Negative
        $this->assertStringContainsString('between 0.00 and 1.00', $rule->getDescription());
    }

    public function testCanadianSINCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::canadianSIN();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);

        // Valid SIN: 046 454 286 (checksum algorithm test)
        $this->assertTrue($rule->validate('046454286'));
        $this->assertTrue($rule->validate('046 454 286'));

        // Invalid SINs
        $this->assertFalse($rule->validate('123456789')); // Invalid checksum
        $this->assertFalse($rule->validate('12345678')); // Too short
        $this->assertFalse($rule->validate('1234567890')); // Too long
        $this->assertFalse($rule->validate('abc123def')); // Non-numeric

        $this->assertStringContainsString('Canadian Social Insurance Number', $rule->getDescription());
    }

    public function testCanadianPostalCodeCreatesBusinessRuleValidationRule(): void
    {
        $rule = ValidationRuleFactory::canadianPostalCode();

        $this->assertInstanceOf(BusinessRuleValidationRule::class, $rule);

        // Valid postal codes
        $this->assertTrue($rule->validate('T2A 1A1'));
        $this->assertTrue($rule->validate('t2a1a1'));
        $this->assertTrue($rule->validate('K1A0A1'));

        // Invalid postal codes
        $this->assertFalse($rule->validate('123456')); // Wrong format
        $this->assertFalse($rule->validate('T2A 1A')); // Too short
        $this->assertFalse($rule->validate('T2A 1A1 2B2')); // Too long
        $this->assertFalse($rule->validate('12A 1A1')); // Starts with number

        $this->assertStringContainsString('Canadian postal code', $rule->getDescription());
    }
}