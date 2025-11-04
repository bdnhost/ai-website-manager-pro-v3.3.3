<?php
/**
 * Settings Repository Interface
 * 
 * @package AI_Website_Manager_Pro
 * @subpackage Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for settings repository implementations
 */
interface Settings_Repository_Interface
{

    /**
     * Get setting value by key
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public function get($key, $default = null);

    /**
     * Set setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success status
     */
    public function set($key, $value);

    /**
     * Delete setting by key
     * 
     * @param string $key Setting key
     * @return bool Success status
     */
    public function delete($key);

    /**
     * Get all settings
     * 
     * @return array All settings
     */
    public function get_all();

    /**
     * Set multiple settings at once
     * 
     * @param array $settings Key-value pairs
     * @return bool Success status
     */
    public function set_multiple($settings);

    /**
     * Check if setting exists
     * 
     * @param string $key Setting key
     * @return bool Whether setting exists
     */
    public function exists($key);

    /**
     * Clear all settings
     * 
     * @return bool Success status
     */
    public function clear();

    /**
     * Get settings by prefix
     * 
     * @param string $prefix Key prefix
     * @return array Matching settings
     */
    public function get_by_prefix($prefix);
}