<?php
/**
 * Settings Validator Interface
 * 
 * @package AI_Website_Manager_Pro
 * @subpackage Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for settings validation implementations
 */
interface Settings_Validator_Interface
{

    /**
     * Validate setting value against schema
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param array $schema Validation schema
     * @return array Validation result with errors
     */
    public function validate($key, $value, $schema);

    /**
     * Validate multiple settings
     * 
     * @param array $settings Key-value pairs
     * @param array $schema Validation schema
     * @return array Validation results
     */
    public function validate_multiple($settings, $schema);

    /**
     * Sanitize setting value
     * 
     * @param mixed $value Raw value
     * @param array $rules Sanitization rules
     * @return mixed Sanitized value
     */
    public function sanitize($value, $rules);

    /**
     * Add custom validation rule
     * 
     * @param string $name Rule name
     * @param callable $callback Validation callback
     * @return void
     */
    public function add_rule($name, $callback);

    /**
     * Get validation errors
     * 
     * @return array Current validation errors
     */
    public function get_errors();

    /**
     * Clear validation errors
     * 
     * @return void
     */
    public function clear_errors();
}