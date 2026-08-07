<?php

declare(strict_types=1);

namespace Tests\Unit\Calculations;

use Ksfraser\ModulesCommon\CalculationContext;
use Ksfraser\ModulesCommon\CalculationException;
use Ksfraser\ModulesCommon\CalculationResult;
use Ksfraser\ModulesCommon\GenericCalculationEngine;
use Ksfraser\ModulesCommon\ParameterDefinition;
use Ksfraser\ModulesCommon\ValidationResult;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for the GenericCalculationEngine base class.
 *
 * Tests the common functionality provided by the generic engine,
 * including validation, error handling, and result formatting.
 */
class GenericCalculationEngineTest extends TestCase
{
    private $logger;
    private TestCalculationEngine $engine;

    protected function setUp(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $this->logger = $logger;
        $this->engine = new TestCalculationEngine($this->logger);
    }

    public function testCalculateSuccess(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 100]
        );

        $result = $this->engine->calculate($context);

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('test_calculation', $result->calculationType);
        $this->assertEquals(200, $result->primaryResult); // Test engine doubles the value
        $this->assertArrayHasKey('client_id', $result->metadata);
        $this->assertArrayHasKey('calculation_engine', $result->metadata);
    }

    public function testCalculateWithValidationFailure(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            [] // Missing required parameter
        );

        $result = $this->engine->calculate($context);

        $this->assertFalse($result->isSuccessful());
        $this->assertContains('Missing required parameters: value', $result->validationResult->errors);
    }

    public function testCalculateWithCalculationException(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => -100] // Negative value causes exception in test engine
        );

        $this->expectException(CalculationException::class);
        $this->expectExceptionMessage('Test calculation error');

        $this->engine->calculate($context);
    }

    public function testValidateSuccess(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 100]
        );

        $validationResult = $this->engine->validate($context);

        $this->assertTrue($validationResult->isValid);
        $this->assertEmpty($validationResult->errors);
        $this->assertEmpty($validationResult->warnings);
    }

    public function testValidateMissingRequiredParameter(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            []
        );

        $validationResult = $this->engine->validate($context);

        $this->assertFalse($validationResult->isValid);
        $this->assertContains('Missing required parameters: value', $validationResult->errors);
    }

    public function testValidateInvalidParameterType(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 'not_a_number']
        );

        $validationResult = $this->engine->validate($context);

        $this->assertFalse($validationResult->isValid);
        $this->assertContains('Parameter value failed validation: Value must be numeric', $validationResult->errors);
    }

    public function testValidateCalculationTypeMismatch(): void
    {
        $context = new CalculationContext(
            'wrong_type',
            ['value' => 100]
        );

        $validationResult = $this->engine->validate($context);

        $this->assertFalse($validationResult->isValid);
        $this->assertContains('Calculation type mismatch. Expected test_calculation, got wrong_type', $validationResult->errors);
    }

    public function testGetCalculationType(): void
    {
        $this->assertEquals('test_calculation', $this->engine->getCalculationType());
    }

    public function testGetRequiredParameters(): void
    {
        $params = $this->engine->getRequiredParameters();

        $this->assertArrayHasKey('value', $params);
        $this->assertInstanceOf(ParameterDefinition::class, $params['value']);
        $this->assertEquals('value', $params['value']->name);
        $this->assertEquals('number', $params['value']->type);
        $this->assertTrue($params['value']->required);
    }

    public function testGetOptionalParameters(): void
    {
        $params = $this->engine->getOptionalParameters();

        $this->assertArrayHasKey('multiplier', $params);
        $this->assertInstanceOf(ParameterDefinition::class, $params['multiplier']);
        $this->assertEquals('multiplier', $params['multiplier']->name);
        $this->assertFalse($params['multiplier']->required);
    }

    public function testAssumptionsUsed(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 100],
            ['inflation_rate' => 0.03] // Override default
        );

        $result = $this->engine->calculate($context);

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(0.03, $result->getAssumptionUsed('inflation_rate'));
        $this->assertEquals(0.05, $result->getAssumptionUsed('default_rate')); // Default value
    }

    public function testIntermediateResults(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 100]
        );

        $result = $this->engine->calculate($context);

        $this->assertEquals(50, $result->getIntermediateResult('half_value'));
        $this->assertEquals(150, $result->getIntermediateResult('value_plus_50'));
    }

    public function testLogging(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => 100]
        );

        // Just verify the calculation works with logging enabled
        $result = $this->engine->calculate($context);
        $this->assertTrue($result->isSuccessful());
    }

    public function testLoggingWithValidationFailure(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            []
        );

        // Just verify validation failure works with logging
        $result = $this->engine->calculate($context);
        $this->assertFalse($result->isSuccessful());
    }

    public function testLoggingWithException(): void
    {
        $context = new CalculationContext(
            'test_calculation',
            ['value' => -100]
        );

        $this->expectException(CalculationException::class);
        $this->engine->calculate($context);
    }
}

/**
 * Concrete implementation of GenericCalculationEngine for testing.
 */
class TestCalculationEngine extends GenericCalculationEngine
{
    public function getCalculationType(): string
    {
        return 'test_calculation';
    }

    public function getRequiredParameters(): array
    {
        return [
            'value' => new ParameterDefinition(
                'value',
                'number',
                'A numeric value to process',
                true,
                null,
                new class implements \Ksfraser\ModulesCommon\ValidationRuleInterface {
                    public function validate(mixed $value): bool {
                        return is_numeric($value);
                    }
                    public function getDescription(): string {
                        return 'Must be numeric';
                    }
                    public function getErrorMessage(): string {
                        return 'Value must be numeric';
                    }
                }
            )
        ];
    }

    public function getOptionalParameters(): array
    {
        return [
            'multiplier' => new ParameterDefinition(
                'multiplier',
                'number',
                'Optional multiplier',
                false,
                2.0
            )
        ];
    }

    protected function performCalculation(CalculationContext $context): mixed
    {
        $value = $context->getParameter('value');

        if ($value < 0) {
            throw CalculationException::calculationError(
                $this->getCalculationType(),
                'Test calculation error',
                ['value' => $value]
            );
        }

        return $value * 2; // Simple doubling for testing
    }

    protected function getDefaultAssumptions(): array
    {
        return [
            'inflation_rate' => 0.02,
            'default_rate' => 0.05,
        ];
    }

    protected function getIntermediateResults(): array
    {
        return [
            'half_value' => 50, // Would be calculated in real implementation
            'value_plus_50' => 150,
        ];
    }
}