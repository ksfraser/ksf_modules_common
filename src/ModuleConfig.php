<?php
declare(strict_types=1);

namespace Ksfraser\ModulesCommon;

/**
 * Generic module configuration storage and form renderer.
 *
 * Provides a reusable key/value config system for any FA module.
 * Table name and field definitions are injected via the constructor.
 * Uses FA's db_query/db_fetch (session.inc must be loaded).
 *
 * Field definition keys:
 *   name    (string, required) — pref_name / DB column / form input name
 *   label   (string, required) — human-readable label
 *   type    (string, optional) — text|integer|boolean|customer_list|locations_list|dimensions_list (default: text)
 *   default (mixed,  optional) — default value
 *
 * @since 2.0.0
 */
class ModuleConfig
{
    /** @var string Fully-qualified table name including prefix (e.g. '0_ksf_isu_config') */
    private $tableName;

    /** @var string Module identifier for logging/debugging */
    private $moduleName;

    /** @var array[] Field definitions keyed by name */
    private $fields = [];

    /** @var array Current values keyed by name */
    private $values = [];

    /** @var bool Whether the table has been created/verified */
    private $initialized = false;

    /**
     * @param string $tableName  Table name including company prefix (e.g. TB_PREF . 'ksf_isu_config')
     * @param string $moduleName Module identifier (e.g. 'ksf_FA_ImportStagingProcessing_UI')
     * @param array  $fields     Array of field definition arrays
     */
    public function __construct(string $tableName, string $moduleName, array $fields = [])
    {
        $this->tableName  = $tableName;
        $this->moduleName = $moduleName;

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * Add a field definition.
     *
     * @param array $field Must contain 'name' and 'label'; may contain 'type' and 'default'
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
        ];

        if (!array_key_exists($name, $this->values)) {
            $this->values[$name] = $this->fields[$name]['default'];
        }

        return $this;
    }

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
        if ($type === 'integer') {
            $value = (int) $value;
        } elseif ($type === 'boolean') {
            $value = (bool) $value;
        } else {
            $value = (string) $value;
        }

        $this->values[$name] = $value;
        return $this;
    }

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
     * @param string $submitKey The POST key that triggers the save (e.g. 'update_config')
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

    /**
     * Render the config form using FA's built-in DDL functions.
     * Handles text, integer, boolean, customer_list, locations_list, dimensions_list.
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

    /**
     * Get the underlying field definitions.
     *
     * @return array[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

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
     * Render a single field row.
     *
     * @param string $name
     * @param array  $field
     */
    private function renderField(string $name, array $field): void
    {
        $label   = $field['label'];
        $value   = $this->values[$name] ?? $field['default'] ?? '';
        $type    = strtoupper($field['type']);

        switch ($type) {
            case 'BOOL':
            case 'BOOLEAN':
                checkbox($label, $name, $value, false, $label);
                break;

            case 'CUSTOMER_LIST':
                customer_list_row($label, $name, $value, false, false);
                break;

            case 'LOCATION':
            case 'LOCATIONS_LIST':
                locations_list_row($label, $name, $value, false, false);
                break;

            case 'DIMENSIONS_LIST':
                dimensions_list_row($label, $name, $value, false);
                break;

            case 'INTEGER':
                text_row($label, $name, $value, 10, 20);
                break;

            case 'TEXT':
            default:
                text_row($label, $name, $value, 40, 100);
                break;
        }
    }
}
