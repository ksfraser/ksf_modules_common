<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ksfraser\ModulesCommon\ModuleConfig;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ModuleConfig — pure logic only (no DB).
 *
 * @BABOK Related: UT-ISU-004-001
 */
class ModuleConfigTest extends TestCase
{
    // ------------------------------------------------------------------
    // Constructor / field registration
    // ------------------------------------------------------------------

    /** @BABOK Related: UT-ISU-004-001-001 */
    public function testConstructorWithEmptyFields(): void
    {
        $config = new ModuleConfig('test_table', 'test_module');
        $this->assertSame([], $config->getFields());
        $this->assertSame([], $config->getAll());
    }

    /** @BABOK Related: UT-ISU-004-001-002 */
    public function testConstructorWithFields(): void
    {
        $fields = [
            ['name' => 'foo', 'label' => 'Foo', 'type' => 'text', 'default' => 'bar'],
        ];
        $config = new ModuleConfig('test_table', 'test_module', $fields);

        $this->assertArrayHasKey('foo', $config->getFields());
        $this->assertSame('bar', $config->get('foo'));
    }

    /** @BABOK Related: UT-ISU-004-001-003 */
    public function testAddFieldChaining(): void
    {
        $config = new ModuleConfig('test_table', 'test_module');
        $result = $config->addField(['name' => 'x', 'label' => 'X']);

        $this->assertSame($config, $result);
        $this->assertArrayHasKey('x', $config->getFields());
    }

    /** @BABOK Related: UT-ISU-004-001-004 */
    public function testAddFieldMissingNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new ModuleConfig('test_table', 'test_module');
        $config->addField(['label' => 'No Name']);
    }

    /** @BABOK Related: UT-ISU-004-001-005 */
    public function testAddFieldMissingLabelThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new ModuleConfig('test_table', 'test_module');
        $config->addField(['name' => 'no_label']);
    }

    // ------------------------------------------------------------------
    // get / set
    // ------------------------------------------------------------------

    /** @BABOK Related: UT-ISU-004-001-006 */
    public function testGetDefault(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X', 'default' => 42],
        ]);
        $this->assertSame(42, $config->get('x'));
    }

    /** @BABOK Related: UT-ISU-004-001-007 */
    public function testGetUnknownFieldReturnsFallback(): void
    {
        $config = new ModuleConfig('t', 'm');
        $this->assertSame('fallback', $config->get('nonexistent', 'fallback'));
        $this->assertNull($config->get('nonexistent'));
    }

    /** @BABOK Related: UT-ISU-004-001-008 */
    public function testSetTextValue(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X', 'type' => 'text'],
        ]);
        $result = $config->set('x', 'hello');
        $this->assertSame($config, $result);
        $this->assertSame('hello', $config->get('x'));
    }

    /** @BABOK Related: UT-ISU-004-001-009 */
    public function testSetIntegerValue(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X', 'type' => 'integer'],
        ]);
        $config->set('x', '42');
        $this->assertSame(42, $config->get('x'));
        $this->assertIsInt($config->get('x'));
    }

    /** @BABOK Related: UT-ISU-004-001-010 */
    public function testSetBooleanValue(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X', 'type' => 'boolean'],
        ]);
        $config->set('x', '1');
        $this->assertTrue($config->get('x'));
        $this->assertIsBool($config->get('x'));

        $config->set('x', 0);
        $this->assertFalse($config->get('x'));
    }

    /** @BABOK Related: UT-ISU-004-001-011 */
    public function testSetUnknownFieldThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new ModuleConfig('t', 'm');
        $config->set('unknown', 'value');
    }

    /** @BABOK Related: UT-ISU-004-001-012 */
    public function testGetAll(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'a', 'label' => 'A', 'default' => 'alpha'],
            ['name' => 'b', 'label' => 'B', 'default' => 'beta'],
        ]);
        $all = $config->getAll();
        $this->assertSame('alpha', $all['a']);
        $this->assertSame('beta', $all['b']);
    }

    // ------------------------------------------------------------------
    // getTableName
    // ------------------------------------------------------------------

    /** @BABOK Related: UT-ISU-004-001-013 */
    public function testGetTableName(): void
    {
        $config = new ModuleConfig('0_ksf_isu_config', 'ISU');
        $this->assertSame('0_ksf_isu_config', $config->getTableName());
    }

    // ------------------------------------------------------------------
    // Defaults
    // ------------------------------------------------------------------

    /** @BABOK Related: UT-ISU-004-001-014 */
    public function testFieldDefaultsAreApplied(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'a', 'label' => 'A', 'default' => 10],
            ['name' => 'b', 'label' => 'B', 'default' => 'hello'],
            ['name' => 'c', 'label' => 'C'],
        ]);
        $this->assertSame(10, $config->get('a'));
        $this->assertSame('hello', $config->get('b'));
        $this->assertNull($config->get('c'));
    }

    /** @BABOK Related: UT-ISU-004-001-015 */
    public function testFieldTypes(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'text_field',    'label' => 'Text',    'type' => 'text'],
            ['name' => 'int_field',     'label' => 'Int',     'type' => 'integer'],
            ['name' => 'bool_field',    'label' => 'Bool',    'type' => 'boolean'],
            ['name' => 'customer_field','label' => 'Customer','type' => 'customer_list'],
            ['name' => 'location_field','label' => 'Location','type' => 'locations_list'],
            ['name' => 'dim_field',     'label' => 'Dim',     'type' => 'dimensions_list'],
        ]);

        $fields = $config->getFields();
        $this->assertSame('text', $fields['text_field']['type']);
        $this->assertSame('integer', $fields['int_field']['type']);
        $this->assertSame('boolean', $fields['bool_field']['type']);
        $this->assertSame('customer_list', $fields['customer_field']['type']);
        $this->assertSame('locations_list', $fields['location_field']['type']);
        $this->assertSame('dimensions_list', $fields['dim_field']['type']);
    }

    /** @BABOK Related: UT-ISU-004-001-016 */
    public function testFieldOptionsStored(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X', 'type' => 'text', 'options' => ['size' => 20, 'max' => 50]],
        ]);
        $fields = $config->getFields();
        $this->assertSame(['size' => 20, 'max' => 50], $fields['x']['options']);
    }

    /** @BABOK Related: UT-ISU-004-001-017 */
    public function testFieldOptionsDefaultsToEmpty(): void
    {
        $config = new ModuleConfig('t', 'm', [
            ['name' => 'x', 'label' => 'X'],
        ]);
        $fields = $config->getFields();
        $this->assertSame([], $fields['x']['options']);
    }

    // ------------------------------------------------------------------
    // Renderer registry
    // ------------------------------------------------------------------

    /** @BABOK Related: UT-ISU-004-001-020 */
    public function testBuiltInRenderersRegistered(): void
    {
        $config = new ModuleConfig('t', 'm');
        $this->assertNotNull($config->getRenderer('text'));
        $this->assertNotNull($config->getRenderer('integer'));
        $this->assertNotNull($config->getRenderer('boolean'));
        $this->assertIsCallable($config->getRenderer('text'));
    }

    /** @BABOK Related: UT-ISU-004-001-021 */
    public function testAddRenderer(): void
    {
        $config = new ModuleConfig('t', 'm');
        $fn = function () {};
        $result = $config->addRenderer('custom_type', $fn);

        $this->assertSame($config, $result);
        $this->assertSame($fn, $config->getRenderer('custom_type'));
    }

    /** @BABOK Related: UT-ISU-004-001-022 */
    public function testAddRendererCaseInsensitive(): void
    {
        $config = new ModuleConfig('t', 'm');
        $fn = function () {};
        $config->addRenderer('MyType', $fn);

        $this->assertSame($fn, $config->getRenderer('mytype'));
        $this->assertSame($fn, $config->getRenderer('MYTYPE'));
    }

    /** @BABOK Related: UT-ISU-004-001-023 */
    public function testAddRenderersBulk(): void
    {
        $config = new ModuleConfig('t', 'm');
        $fn1 = function () {};
        $fn2 = function () {};
        $config->addRenderers(['a' => $fn1, 'b' => $fn2]);

        $this->assertSame($fn1, $config->getRenderer('a'));
        $this->assertSame($fn2, $config->getRenderer('b'));
    }

    /** @BABOK Related: UT-ISU-004-001-024 */
    public function testConstructorWithRenderers(): void
    {
        $fn = function () {};
        $config = new ModuleConfig('t', 'm', [], ['my_type' => $fn]);

        $this->assertSame($fn, $config->getRenderer('my_type'));
    }

    /** @BABOK Related: UT-ISU-004-001-025 */
    public function testGetRendererUnknownReturnsNull(): void
    {
        $config = new ModuleConfig('t', 'm');
        $this->assertNull($config->getRenderer('nonexistent'));
    }

    /** @BABOK Related: UT-ISU-004-001-026 */
    public function testGetRendererTypes(): void
    {
        $config = new ModuleConfig('t', 'm');
        $types = $config->getRendererTypes();
        $this->assertContains('text', $types);
        $this->assertContains('integer', $types);
        $this->assertContains('boolean', $types);
    }

    /** @BABOK Related: UT-ISU-004-001-027 */
    public function testAddDDLRendererRegistersCallable(): void
    {
        $config = new ModuleConfig('t', 'm');
        $result = $config->addDDLRenderer('customer_list', 'customer_list_row');

        $this->assertSame($result, $config);
        $this->assertNotNull($config->getRenderer('customer_list'));
        $this->assertIsCallable($config->getRenderer('customer_list'));
    }

    /** @BABOK Related: UT-ISU-004-001-028 */
    public function testAddRadioRendererRegistersCallable(): void
    {
        $config = new ModuleConfig('t', 'm');
        $result = $config->addRadioRenderer();

        $this->assertSame($result, $config);
        $this->assertNotNull($config->getRenderer('radio'));
        $this->assertIsCallable($config->getRenderer('radio'));
    }

    /** @BABOK Related: UT-ISU-004-001-029 */
    public function testAddSelectRendererRegistersCallable(): void
    {
        $config = new ModuleConfig('t', 'm');
        $result = $config->addSelectRenderer();

        $this->assertSame($result, $config);
        $this->assertNotNull($config->getRenderer('select'));
    }

    /** @BABOK Related: UT-ISU-004-001-030 */
    public function testAddCheckboxRendererRegistersCallable(): void
    {
        $config = new ModuleConfig('t', 'm');
        $result = $config->addCheckboxRenderer('check');

        $this->assertSame($result, $config);
        $this->assertNotNull($config->getRenderer('check'));
    }

    /** @BABOK Related: UT-ISU-004-001-031 */
    public function testRendererOverridesBuiltIn(): void
    {
        $config = new ModuleConfig('t', 'm');
        $original = $config->getRenderer('text');
        $custom = function () {};
        $config->addRenderer('text', $custom);

        $this->assertSame($custom, $config->getRenderer('text'));
        $this->assertNotSame($original, $config->getRenderer('text'));
    }

    /** @BABOK Related: UT-ISU-004-001-032 */
    public function testConstructorMergesFieldAndRendererArrays(): void
    {
        $fn = function () {};
        $config = new ModuleConfig(
            't',
            'm',
            [['name' => 'x', 'label' => 'X', 'type' => 'custom']],
            ['custom' => $fn]
        );

        $this->assertSame('custom', $config->getFields()['x']['type']);
        $this->assertSame($fn, $config->getRenderer('custom'));
    }
}
