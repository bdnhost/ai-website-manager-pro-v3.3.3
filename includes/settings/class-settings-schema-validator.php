<?php
/**
 * Settings Schema Validator
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Exceptions\Settings_Validation_Exception;

/**
 * Settings Schema Validator Class
 * 
 * Validates settings data against defined schemas
 */
class Settings_Schema_Validator {
    
    /**
     * Registered schemas
     *
     * @var array
     */
    private $schemas = [];
    
    /**
     * Custom validation callbacks
     *
     * @var array
     */
    private $custom_validators = [];
    
    /**
     * Validation errors
     *
     * @var array
     */
    private $errors = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->register_default_validators();
    }
    
    /**
     * Register a schema
     *
     * @param string $type Schema type
     * @param array $schema Schema definition
     */
    public function register_schema($type, $schema) {
        $this->schemas[$type] = $schema;
    }
    
    /**
     * Register custom validator
     *
     * @param string $name Validator name
     * @param callable $callback Validation callback
     */
    public function register_validator($name, $callback) {
        if (!is_callable($callback)) {
            throw new Settings_Validation_Exception(
                "Validator callback must be callable",
                [],
                ['validator' => $name]
            );
        }
        
        $this->custom_validators[$name] = $callback;
    }
    
    /**
     * Validate data against schema
     *
     * @param string $type Schema type
     * @param mixed $data Data to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate($type, $data) {
        $this->errors = [];
        
        if (!isset($this->schemas[$type])) {
            throw new Settings_Validation_Exception(
                "Schema type '{$type}' not found",
                [],
                ['type' => $type, 'available_types' => array_keys($this->schemas)]
            );
        }
        
        $schema = $this->schemas[$type];
        $this->validate_value($data, $schema, $type);
        
        return empty($this->errors) ? true : $this->errors;
    }
    
    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function get_validation_errors() {
        return $this->errors;
    }
    
    /**
     * Validate a value against schema
     *
     * @param mixed $value Value to validate
     * @param array $schema Schema definition
     * @param string $path Current validation path
     */
    private function validate_value($value, $schema, $path = '') {
        // Check required
        if (isset($schema['required']) && $schema['required'] && ($value === null || $value === '')) {
            $this->add_error($path, "Field is required");
            return;
        }
        
        // Skip further validation if value is null/empty and not required
        if ($value === null || $value === '') {
            return;
        }
        
        // Validate type
        if (isset($schema['type'])) {
            $this->validate_type($value, $schema['type'], $path);
        }
        
        // Validate enum
        if (isset($schema['enum'])) {
            $this->validate_enum($value, $schema['enum'], $path);
        }
        
        // Validate format
        if (isset($schema['format'])) {
            $this->validate_format($value, $schema['format'], $path);
        }
        
        // Validate string constraints
        if (is_string($value)) {
            $this->validate_string_constraints($value, $schema, $path);
        }
        
        // Validate numeric constraints
        if (is_numeric($value)) {
            $this->validate_numeric_constraints($value, $schema, $path);
        }
        
        // Validate array constraints
        if (is_array($value)) {
            $this->validate_array_constraints($value, $schema, $path);
        }
        
        // Validate object properties
        if (isset($schema['properties']) && is_array($value)) {
            $this->validate_object_properties($value, $schema, $path);
        }
        
        // Custom validation
        if (isset($schema['custom'])) {
            $this->validate_custom($value, $schema['custom'], $path);
        }
    }
    
    /**
     * Validate type
     *
     * @param mixed $value Value to validate
     * @param string $expected_type Expected type
     * @param string $path Validation path
     */
    private function validate_type($value, $expected_type, $path) {
        $actual_type = gettype($value);
        
        // Normalize type names
        $type_map = [
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float'
        ];
        
        $expected_type = $type_map[$expected_type] ?? $expected_type;
        $actual_type = $type_map[$actual_type] ?? $actual_type;
        
        // Special handling for numbers
        if ($expected_type === 'number' && (is_int($value) || is_float($value))) {
            return;
        }
        
        if ($actual_type !== $expected_type) {
            $this->add_error($path, "Expected {$expected_type}, got {$actual_type}");
        }
    }
    
    /**
     * Validate enum values
     *
     * @param mixed $value Value to validate
     * @param array $enum_values Allowed values
     * @param string $path Validation path
     */
    private function validate_enum($value, $enum_values, $path) {
        if (!in_array($value, $enum_values, true)) {
            $allowed = implode(', ', $enum_values);
            $this->add_error($path, "Value must be one of: {$allowed}");
        }
    }
    
    /**
     * Validate format
     *
     * @param mixed $value Value to validate
     * @param string $format Expected format
     * @param string $path Validation path
     */
    private function validate_format($value, $format, $path) {
        if (!is_string($value)) {
            return;
        }
        
        switch ($format) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->add_error($path, "Must be a valid email address");
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->add_error($path, "Must be a valid URL");
                }
                break;
                
            case 'date':
                if (!$this->validate_date($value)) {
                    $this->add_error($path, "Must be a valid date (YYYY-MM-DD)");
                }
                break;
                
            case 'datetime':
                if (!$this->validate_datetime($value)) {
                    $this->add_error($path, "Must be a valid datetime (YYYY-MM-DD HH:MM:SS)");
                }
                break;
                
            case 'regex':
                // Custom regex validation would need pattern in schema
                break;
        }
    }
    
    /**
     * Validate string constraints
     *
     * @param string $value String value
     * @param array $schema Schema definition
     * @param string $path Validation path
     */
    private function validate_string_constraints($value, $schema, $path) {
        $length = strlen($value);
        
        if (isset($schema['minLength']) && $length < $schema['minLength']) {
            $this->add_error($path, "Must be at least {$schema['minLength']} characters long");
        }
        
        if (isset($schema['maxLength']) && $length > $schema['maxLength']) {
            $this->add_error($path, "Must be at most {$schema['maxLength']} characters long");
        }
        
        if (isset($schema['pattern'])) {
            if (!preg_match($schema['pattern'], $value)) {
                $this->add_error($path, "Does not match required pattern");
            }
        }
    }
    
    /**
     * Validate numeric constraints
     *
     * @param numeric $value Numeric value
     * @param array $schema Schema definition
     * @param string $path Validation path
     */
    private function validate_numeric_constraints($value, $schema, $path) {
        if (isset($schema['min']) && $value < $schema['min']) {
            $this->add_error($path, "Must be at least {$schema['min']}");
        }
        
        if (isset($schema['max']) && $value > $schema['max']) {
            $this->add_error($path, "Must be at most {$schema['max']}");
        }
        
        if (isset($schema['multipleOf']) && fmod($value, $schema['multipleOf']) !== 0.0) {
            $this->add_error($path, "Must be a multiple of {$schema['multipleOf']}");
        }
    }
    
    /**
     * Validate array constraints
     *
     * @param array $value Array value
     * @param array $schema Schema definition
     * @param string $path Validation path
     */
    private function validate_array_constraints($value, $schema, $path) {
        $count = count($value);
        
        if (isset($schema['minItems']) && $count < $schema['minItems']) {
            $this->add_error($path, "Must have at least {$schema['minItems']} items");
        }
        
        if (isset($schema['maxItems']) && $count > $schema['maxItems']) {
            $this->add_error($path, "Must have at most {$schema['maxItems']} items");
        }
        
        if (isset($schema['uniqueItems']) && $schema['uniqueItems']) {
            if (count($value) !== count(array_unique($value, SORT_REGULAR))) {
                $this->add_error($path, "All items must be unique");
            }
        }
        
        // Validate array items
        if (isset($schema['items'])) {
            foreach ($value as $index => $item) {
                $item_path = $path ? "{$path}[{$index}]" : "[{$index}]";
                $this->validate_value($item, $schema['items'], $item_path);
            }
        }
    }
    
    /**
     * Validate object properties
     *
     * @param array $value Object value
     * @param array $schema Schema definition
     * @param string $path Validation path
     */
    private function validate_object_properties($value, $schema, $path) {
        $properties = $schema['properties'];
        $required = $schema['required'] ?? [];
        
        // Check required properties
        foreach ($required as $required_prop) {
            if (!array_key_exists($required_prop, $value)) {
                $prop_path = $path ? "{$path}.{$required_prop}" : $required_prop;
                $this->add_error($prop_path, "Required property is missing");
            }
        }
        
        // Validate each property
        foreach ($value as $prop_name => $prop_value) {
            if (isset($properties[$prop_name])) {
                $prop_path = $path ? "{$path}.{$prop_name}" : $prop_name;
                $this->validate_value($prop_value, $properties[$prop_name], $prop_path);
            } elseif (isset($schema['additionalProperties']) && !$schema['additionalProperties']) {
                $prop_path = $path ? "{$path}.{$prop_name}" : $prop_name;
                $this->add_error($prop_path, "Additional property not allowed");
            }
        }
    }
    
    /**
     * Validate custom constraints
     *
     * @param mixed $value Value to validate
     * @param string|array $custom_rules Custom validation rules
     * @param string $path Validation path
     */
    private function validate_custom($value, $custom_rules, $path) {
        $rules = is_array($custom_rules) ? $custom_rules : [$custom_rules];
        
        foreach ($rules as $rule) {
            if (isset($this->custom_validators[$rule])) {
                $callback = $this->custom_validators[$rule];
                $result = call_user_func($callback, $value, $path);
                
                if ($result !== true) {
                    $error_message = is_string($result) ? $result : "Custom validation failed";
                    $this->add_error($path, $error_message);
                }
            }
        }
    }
    
    /**
     * Add validation error
     *
     * @param string $path Field path
     * @param string $message Error message
     */
    private function add_error($path, $message) {
        $this->errors[] = [
            'path' => $path,
            'message' => $message
        ];
    }
    
    /**
     * Validate date format
     *
     * @param string $date Date string
     * @return bool Whether date is valid
     */
    private function validate_date($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validate datetime format
     *
     * @param string $datetime Datetime string
     * @return bool Whether datetime is valid
     */
    private function validate_datetime($datetime) {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }
    
    /**
     * Register default validators
     */
    private function register_default_validators() {
        // WordPress-specific validators
        $this->register_validator('wp_user_exists', function($value) {
            return get_user_by('ID', $value) !== false;
        });
        
        $this->register_validator('wp_post_exists', function($value) {
            return get_post($value) !== null;
        });
        
        $this->register_validator('wp_term_exists', function($value) {
            return term_exists($value) !== 0 && term_exists($value) !== null;
        });
        
        // API key validators
        $this->register_validator('openai_api_key', function($value) {
            return preg_match('/^sk-[a-zA-Z0-9]{48}$/', $value);
        });
        
        $this->register_validator('claude_api_key', function($value) {
            return preg_match('/^sk-ant-[a-zA-Z0-9\-_]{95}$/', $value);
        });
        
        $this->register_validator('openrouter_api_key', function($value) {
            return preg_match('/^sk-or-[a-zA-Z0-9\-_]+$/', $value);
        });
        
        // Security validators
        $this->register_validator('no_html', function($value) {
            return $value === strip_tags($value);
        });
        
        $this->register_validator('safe_filename', function($value) {
            return preg_match('/^[a-zA-Z0-9\-_\.]+$/', $value);
        });
    }
    
    /**
     * Get registered schemas
     *
     * @return array Registered schemas
     */
    public function get_schemas() {
        return $this->schemas;
    }
    
    /**
     * Get registered validators
     *
     * @return array Registered validators
     */
    public function get_validators() {
        return array_keys($this->custom_validators);
    }
}