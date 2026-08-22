<?php
declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Generic module configuration storage and form renderer.
 *
 * Provides a reusable key/value config system for any FA module.
 * Table name, field definitions, and renderers are injected via the constructor.
 * Uses FA's db_query/db_fetch (session.inc must be loaded).
 *
 * Field definition keys:
 *   name    (string, required) - pref_name / DB column / form input name
 *   label   (string, required) - human-readable label
 *   type    (string, optional) - maps to a registered renderer (default: 'text')
 *   default (mixed,  optional) - default value
 *   options (array,  optional) - extra data passed to the renderer
 *
 * Built-in renderers: 'text', 'integer', 'boolean'
 * Register DDL/radio/select renderers via addRenderer() or convenience methods.
 *
 * @since 2.0.0
 * @since 2.1.0 Refactored: renderers are now DI'd via registry instead of hardcoded switch.
 */
class ModuleConfig
{
    /** @var string Fully-qualified table name including prefix */
    private $tableName;

    /** @var string Module identifier for logging/debugging */
    private $moduleName;

    /** @var array[] Field definitions keyed by name */
    private $fields = [];

    /** @var array Current values keyed by name */
    private $values = [];

    /**
     * Renderer registry: type => callable.
     * Each callable signature: function(string $label, string $name, $value, array $field): void
     *
     * @var callable[]
     */
    private $renderers = [];

    /** @var bool Whether the table has been created/verified */
    private $initialized = false;

    /**
     * @param string     $tableName  Table name including company prefix
     * @param string     $moduleName Module identifier
     * @param array      $fields     Array of field definition arrays
     * @param callable[] $renderers  Optional renderers to register (type => callable)
     */
    public function __construct(
        string $tableName,
        string $moduleName,
        array $fields = [],
        array $renderers = []
    ) {
        $this->tableName  = $tableName;
        $this->moduleName = $moduleName;

        $this->registerBuiltInRenderers();

        foreach ($renderers as $type => $callable) {
            $this->addRenderer($type, $callable);
        }

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    // ------------------------------------------------------------------
    // Renderer registration
    // ------------------------------------------------------------------

    /**
     * Register a renderer for a field type.
     *
     * The callable receives: ($label, $name, $value, $field) and must output
     * the form element directly (echo/print).
     *
     * @param string   $type     Field type key
     * @param callable $callable function(string $label, string $name, $value, array $field): void
     * @return self
     */
    public function addRenderer(string $type, callable $callable): self
    {
        $this->renderers[strtolower($type)] = $callable;
        return $this;
    }

    /**
     * Register multiple renderers at once.
     *
     * @param callable[] $renderers type => callable
     * @return self
     */
    public function addRenderers(array $renderers): self
    {
        foreach ($renderers as $type => $callable) {
            $this->addRenderer($type, $callable);
        }
        return $this;
    }

    /**
     * Convenience: register a DDL renderer that wraps a FA *_list_row() function.
     *
     * @param string $type         Field type key (e.g. 'customer_list')
     * @param string $functionName FA function name (e.g. 'customer_list_row')
     * @return self
     */
    public function addDDLRenderer(string $type, string $functionName): self
    {
        $this->addRenderer($type, function (string $label, string $name, $value, array $field) use ($functionName) {
            $functionName($label, $name, $value, false, false);
        });
        return $this;
    }

    /**
     * Register a radio button renderer.
     *
     * @param string $type Field type key (default: 'radio')
     * @return self
     */
    public function addRadioRenderer(string $type = 'radio'): self
    {
        $this->addRenderer($type, function (string $label, string $name, $value, array $field) {
            $choices = $field['options']['choices'] ?? [];
            radio($label, $name, $value, false, $choices);
        });
        return $this;
    }

    /**
     * Register a select/dropdown renderer.
     *
     * @param string $type Field type key (default: 'select')
     * @return self
     */
    public function addSelectRenderer(string $type = 'select'): self
    {
        $this->addRenderer($type, function (string $label, string $name, $value, array $field) {
            $choices = $field['options']['choices'] ?? [];
            array_selector_row($label, $name, $value, $choices);
        });
        return $this;
    }

    /**
     * Register a checkbox renderer.
     *
     * @param string $type Field type key (default: 'boolean')
     * @return self
     */
    public function addCheckboxRenderer(string $type = 'boolean'): self
    {
        $this->addRenderer($type, function (string $label, string $name, $value, array $field) {
            checkbox($label, $name, $value, false, $label);
        });
        return $this;
    }

    /**
     * Get a registered renderer, or null if none.
     *
     * @param string $type
     * @return callable|null
     */
    public function getRenderer(string $type): ?callable
    {
        return $this->renderers[strtolower($type)] ?? null;
    }

    /**
     * Get all registered renderer types.
     *
     * @return string[]
     */
    public function getRendererTypes(): array
    {
        return array_keys($this->renderers);
    }

    // ------------------------------------------------------------------
    // Field management
    // ------------------------------------------------------------------

    /**
     * Add a field definition.
     *
     * @param array $field Must contain 'name' and 'label'; may contain 'type', 'default', 'options'
     * @return self
     */
    public function addField(array $field): self
    {
        if (empty($field['name']) || empty($field['label'])) {
            throw new \InvalidArgumentException('Field definition must include "name" and "label"');
        }

        $name = $field['name'];
        $this->fields[$name] = [
            'name'    => $name,
            'label'   => $field['label'],
            'type'    => $field['type'] ?? 'text',
            'default' => $field['default'] ?? null,
            'options' => $field['options'] ?? [],
        ];

        if (!array_key_exists($name, $this->values)) {
            $this->values[$name] = $this->fields[$name]['default'];
        }

        return $this;
    }

    /**
     * Get the underlying field definitions.
     *
     * @return array[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    // ------------------------------------------------------------------
    // Value access
    // ------------------------------------------------------------------

    /**
     * Get a single config value.
     *
     * @param string $name    Field name
     * @param mixed  $default Fallback if not set
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->values[$name] ?? $default;
    }

    /**
     * Get all config values.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->values;
    }

    /**
     * Set a value in memory (call save() to persist).
     *
     * @param string $name  Field name
     * @param mixed  $value Value to store
     * @return self
     */
    public function set(string $name, $value): self
    {
        if (!isset($this->fields[$name])) {
            throw new \InvalidArgumentException("Unknown field: $name");
        }

        $type = $this->fields[$name]['type'];
        $this->values[$name] = $this->castValue((string) $value, $type);
        return $this;
    }

    // ------------------------------------------------------------------
    // Persistence
    // ------------------------------------------------------------------

    /**
     * Load all values from the database.
     *
     * @return self
     */
    public function load(): self
    {
        $this->ensureTable();

        $sql = "SELECT name, value FROM " . $this->tableName;
        $result = db_query($sql, $this->moduleName . ': failed to load config');

        while ($row = db_fetch($result)) {
            $name = $row['name'];
            if (isset($this->fields[$name])) {
                $this->values[$name] = $this->castValue($row['value'], $this->fields[$name]['type']);
            }
        }

        return $this;
    }

    /**
     * Save all current values to the database (upsert per field).
     *
     * @return self
     */
    public function save(): self
    {
        $this->ensureTable();

        foreach ($this->fields as $name => $field) {
            $value = $this->values[$name] ?? $field['default'];
            if ($value === null) {
                $value = '';
            }

            $sql = "REPLACE INTO " . $this->tableName
                 . " (name, value) VALUES (" . db_escape($name) . ", " . db_escape((string) $value) . ")";
            db_query($sql, $this->moduleName . ": failed to save config field '$name'");
        }

        return $this;
    }

    /**
     * Load values from $_POST and persist to DB.
     *
     * @return self
     */
    public function updateFromPost(): self
    {
        foreach ($this->fields as $name => $field) {
            if (isset($_POST[$name])) {
                $this->set($name, $_POST[$name]);
            }
        }
        return $this->save();
    }

    /**
     * Check if POST contains an update for this config and handle it.
     * Returns true if an update was processed.
     *
     * @param string $submitKey The POST key that triggers the save
     * @return bool
     */
    public function handlePost(string $submitKey = 'update_config'): bool
    {
        if (!isset($_POST[$submitKey])) {
            return false;
        }

        $this->updateFromPost();
        display_notification($this->moduleName . ': configuration updated');
        return true;
    }

    // ------------------------------------------------------------------
    // Rendering
    // ------------------------------------------------------------------

    /**
     * Render the config form using registered renderers.
     *
     * @param string $submitKey POST key for the submit button
     */
    public function renderForm(string $submitKey = 'update_config'): void
    {
        start_form(true);
        start_table(TABLESTYLE2, "width='50%'");
        table_header([$this->moduleName . ' Configuration', '']);

        $k = 0;
        foreach ($this->fields as $name => $field) {
            alt_table_row_color($k);
            $this->renderField($name, $field);
        }

        end_table(1);
        hidden('action', 'update');
        submit_center($submitKey, _('Update Configuration'));
        end_form();
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Get the module name.
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Register the built-in renderers (text, integer, boolean/checkbox).
     */
    private function registerBuiltInRenderers(): void
    {
        $this->renderers['text'] = function (string $label, string $name, $value, array $field) {
            $size = $field['options']['size'] ?? 40;
            $max  = $field['options']['max']  ?? 100;
            text_row($label, $name, $value, $size, $max);
        };

        $this->renderers['integer'] = function (string $label, string $name, $value, array $field) {
            $size = $field['options']['size'] ?? 10;
            $max  = $field['options']['max']  ?? 20;
            text_row($label, $name, $value, $size, $max);
        };

        $this->renderers['boolean'] = function (string $label, string $name, $value, array $field) {
            checkbox($label, $name, $value, false, $label);
        };
    }

    /**
     * Ensure the config table exists, creating it if necessary.
     */
    private function ensureTable(): void
    {
        if ($this->initialized) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->tableName . " (
            `name`  VARCHAR(60) NOT NULL,
            `value` VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        db_query($sql, $this->moduleName . ': failed to create config table');

        $this->initialized = true;
    }

    /**
     * Cast a raw DB string value to the appropriate PHP type.
     *
     * @param string $rawValue
     * @param string $type
     * @return mixed
     */
    private function castValue(string $rawValue, string $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $rawValue;
            case 'boolean':
                return (bool) (int) $rawValue;
            default:
                return $rawValue;
        }
    }

    /**
     * Render a single field row using the registered renderer.
     *
     * @param string $name
     * @param array  $field
     * @throws \RuntimeException if no renderer registered for the field type
     */
    private function renderField(string $name, array $field): void
    {
        $type = strtolower($field['type']);
        $renderer = $this->renderers[$type] ?? null;

        if ($renderer === null) {
            throw new \RuntimeException(
                "No renderer registered for field type '{$field['type']}' "
                . "on field '$name'. Register one via addRenderer() or addDDLRenderer()."
            );
        }

        $value = $this->values[$name] ?? $field['default'] ?? '';
        $renderer($field['label'], $name, $value, $field);
    }
}
