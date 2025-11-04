<?php
/**
 * Settings Model Base Class
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Models
 */

namespace AI_Manager_Pro\Settings\Models;

/**
 * Settings Model Base Class
 */
abstract class Settings_Model
{

    /**
     * Setting category
     *
     * @var string
     */
    protected $category;

    /**
     * Setting key
     *
     * @var string
     */
    protected $key;

    /**
     * Setting value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Whether setting is encrypted
     *
     * @var bool
     */
    protected $encrypted = false;

    /**
     * Setting schema
     *
     * @var array
     */
    protected $schema = [];

    /**
     * Last modified timestamp
     *
     * @var string
     */
    protected $last_modified;

    /**
     * User who last modified the setting
     *
     * @var int
     */
    protected $modified_by;

    /**
     * Setting metadata
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * Constructor
     *
     * @param string $category Setting category
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param array $options Additional options
     */
    public function __construct($category, $key, $value = null, $options = [])
    {
        $this->category = $category;
        $this->key = $key;
        $this->value = $value;

        if (isset($options['encrypted'])) {
            $this->encrypted = (bool) $options['encrypted'];
        }

        if (isset($options['schema'])) {
            $this->schema = $options['schema'];
        }

        if (isset($options['metadata'])) {
            $this->metadata = $options['metadata'];
        }

        $this->last_modified = current_time('mysql');
        $this->modified_by = get_current_user_id();
    }

    /**
     * Get setting category
     *
     * @return string Category
     */
    public function get_category()
    {
        return $this->category;
    }

    /**
     * Get setting key
     *
     * @return string Key
     */
    public function get_key()
    {
        return $this->key;
    }

    /**
     * Get full setting key (category.key)
     *
     * @return string Full key
     */
    public function get_full_key()
    {
        return $this->category . '.' . $this->key;
    }

    /**
     * Get setting value
     *
     * @return mixed Value
     */
    public function get_value()
    {
        return $this->value;
    }

    /**
     * Set setting value
     *
     * @param mixed $value New value
     */
    public function set_value($value)
    {
        $this->value = $value;
        $this->last_modified = current_time('mysql');
        $this->modified_by = get_current_user_id();
    }

    /**
     * Check if setting is encrypted
     *
     * @return bool Whether encrypted
     */
    public function is_encrypted()
    {
        return $this->encrypted;
    }

    /**
     * Set encryption status
     *
     * @param bool $encrypted Whether to encrypt
     */
    public function set_encrypted($encrypted)
    {
        $this->encrypted = (bool) $encrypted;
    }

    /**
     * Get setting schema
     *
     * @return array Schema
     */
    public function get_schema()
    {
        return $this->schema;
    }

    /**
     * Set setting schema
     *
     * @param array $schema Schema
     */
    public function set_schema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Get last modified timestamp
     *
     * @return string Timestamp
     */
    public function get_last_modified()
    {
        return $this->last_modified;
    }

    /**
     * Get user who last modified the setting
     *
     * @return int User ID
     */
    public function get_modified_by()
    {
        return $this->modified_by;
    }

    /**
     * Get metadata
     *
     * @param string $key Metadata key (optional)
     * @return mixed Metadata value or all metadata
     */
    public function get_metadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * Set metadata
     *
     * @param string|array $key Metadata key or array of metadata
     * @param mixed $value Metadata value (if key is string)
     */
    public function set_metadata($key, $value = null)
    {
        if (is_array($key)) {
            $this->metadata = array_merge($this->metadata, $key);
        } else {
            $this->metadata[$key] = $value;
        }
    }

    /**
     * Validate setting value against schema
     *
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate()
    {
        if (empty($this->schema)) {
            return true;
        }

        return $this->validate_value($this->value, $this->schema);
    }

    /**
     * Validate a value against schema
     *
     * @param mixed $value Value to validate
     * @param array $schema Schema to validate against
     * @return bool|array True if valid, array of errors if invalid
     */
    protected function validate_value($value, $schema)
    {
        $errors = [];

        // Check required
        if (isset($schema['required']) && $schema['required'] && ($value === null || $value === '')) {
            $errors[] = "Setting '{$this->key}' is required";
        }

        // Check type
        if (isset($schema['type']) && $value !== null) {
            $expected_type = $schema['type'];
            $actual_type = gettype($value);

            // Normalize type names
            if ($expected_type === 'integer') {
                $expected_type = 'int';
            } elseif ($expected_type === 'boolean') {
                $expected_type = 'bool';
            }

            if ($actual_type !== $expected_type) {
                $errors[] = "Setting '{$this->key}' should be of type {$expected_type}, got {$actual_type}";
            }
        }

        // Check enum values
        if (isset($schema['enum']) && $value !== null) {
            if (!in_array($value, $schema['enum'], true)) {
                $allowed = implode(', ', $schema['enum']);
                $errors[] = "Setting '{$this->key}' must be one of: {$allowed}";
            }
        }

        // Check min/max for numbers
        if (is_numeric($value)) {
            if (isset($schema['min']) && $value < $schema['min']) {
                $errors[] = "Setting '{$this->key}' must be at least {$schema['min']}";
            }

            if (isset($schema['max']) && $value > $schema['max']) {
                $errors[] = "Setting '{$this->key}' must be at most {$schema['max']}";
            }
        }

        // Check string length
        if (is_string($value)) {
            if (isset($schema['minLength']) && strlen($value) < $schema['minLength']) {
                $errors[] = "Setting '{$this->key}' must be at least {$schema['minLength']} characters";
            }

            if (isset($schema['maxLength']) && strlen($value) > $schema['maxLength']) {
                $errors[] = "Setting '{$this->key}' must be at most {$schema['maxLength']} characters";
            }
        }

        // Check format
        if (isset($schema['format']) && is_string($value) && !empty($value)) {
            switch ($schema['format']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Setting '{$this->key}' must be a valid email address";
                    }
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[] = "Setting '{$this->key}' must be a valid URL";
                    }
                    break;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Convert model to array
     *
     * @return array Model data
     */
    public function to_array()
    {
        return [
            'category' => $this->category,
            'key' => $this->key,
            'full_key' => $this->get_full_key(),
            'value' => $this->value,
            'encrypted' => $this->encrypted,
            'schema' => $this->schema,
            'last_modified' => $this->last_modified,
            'modified_by' => $this->modified_by,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Create model from array
     *
     * @param array $data Model data
     * @return static Model instance
     */
    public static function from_array($data)
    {
        $category = $data['category'] ?? '';
        $key = $data['key'] ?? '';
        $value = $data['value'] ?? null;

        $options = [
            'encrypted' => $data['encrypted'] ?? false,
            'schema' => $data['schema'] ?? [],
            'metadata' => $data['metadata'] ?? []
        ];

        $model = new static($category, $key, $value, $options);

        if (isset($data['last_modified'])) {
            $model->last_modified = $data['last_modified'];
        }

        if (isset($data['modified_by'])) {
            $model->modified_by = $data['modified_by'];
        }

        return $model;
    }
}