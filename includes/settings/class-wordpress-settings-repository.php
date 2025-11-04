<?php
/**
 * WordPress Settings Repository
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Interfaces\Settings_Repository_Interface;
use AI_Manager_Pro\Settings\Exceptions\Settings_Database_Exception;

/**
 * WordPress Settings Repository Class
 * 
 * Implements settings storage using WordPress Options API
 */
class WordPress_Settings_Repository implements Settings_Repository_Interface
{

    /**
     * Option prefix
     *
     * @var string
     */
    private $prefix = 'ai_manager_pro_';

    /**
     * Cache for settings
     *
     * @var array
     */
    private $cache = [];

    /**
     * Constructor
     *
     * @param string $prefix Option prefix
     */
    public function __construct($prefix = 'ai_manager_pro_')
    {
        $this->prefix = $prefix;
    }

    /**
     * Get a setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed Setting value
     */
    public function get($key, $default = null)
    {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $option_name = $this->get_option_name($key);
        $value = get_option($option_name, $default);

        // Cache the value
        $this->cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success status
     */
    public function set($key, $value)
    {
        $option_name = $this->get_option_name($key);

        // Update cache
        $this->cache[$key] = $value;

        // Store metadata
        $this->set_metadata($key, [
            'last_modified' => current_time('mysql'),
            'modified_by' => get_current_user_id(),
            'ip_address' => $this->get_client_ip()
        ]);

        $result = update_option($option_name, $value);

        if (!$result && get_option($option_name) !== $value) {
            throw new Settings_Database_Exception(
                "Failed to save setting: {$key}",
                ['key' => $key, 'value' => $value]
            );
        }

        return true;
    }

    /**
     * Delete a setting
     *
     * @param string $key Setting key
     * @return bool Success status
     */
    public function delete($key)
    {
        $option_name = $this->get_option_name($key);

        // Remove from cache
        unset($this->cache[$key]);

        // Delete metadata
        $this->delete_metadata($key);

        return delete_option($option_name);
    }

    /**
     * Check if a setting exists
     *
     * @param string $key Setting key
     * @return bool Whether setting exists
     */
    public function exists($key)
    {
        $option_name = $this->get_option_name($key);
        return get_option($option_name, '__NOT_FOUND__') !== '__NOT_FOUND__';
    }

    /**
     * Get all settings for a category
     *
     * @param string $category Settings category
     * @return array Settings array
     */
    public function get_category($category)
    {
        $option_name = $this->get_option_name($category);
        return get_option($option_name, []);
    }

    /**
     * Set multiple settings for a category
     *
     * @param string $category Settings category
     * @param array $settings Settings array
     * @return bool Success status
     */
    public function set_category($category, $settings)
    {
        if (!is_array($settings)) {
            throw new Settings_Database_Exception(
                "Settings must be an array",
                ['category' => $category, 'settings' => $settings]
            );
        }

        $option_name = $this->get_option_name($category);

        // Update cache
        $this->cache[$category] = $settings;

        // Store metadata
        $this->set_metadata($category, [
            'last_modified' => current_time('mysql'),
            'modified_by' => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
            'settings_count' => count($settings)
        ]);

        $result = update_option($option_name, $settings);

        if (!$result && get_option($option_name) !== $settings) {
            throw new Settings_Database_Exception(
                "Failed to save category settings: {$category}",
                ['category' => $category, 'settings' => $settings]
            );
        }

        return true;
    }

    /**
     * Create a backup of all settings
     *
     * @return array Backup data
     */
    public function backup()
    {
        global $wpdb;

        // Get all options with our prefix
        $options = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
            $this->prefix . '%'
        ), ARRAY_A);

        $backup = [
            'timestamp' => current_time('mysql'),
            'version' => AI_MANAGER_PRO_VERSION,
            'user_id' => get_current_user_id(),
            'settings' => []
        ];

        foreach ($options as $option) {
            $key = str_replace($this->prefix, '', $option['option_name']);
            $backup['settings'][$key] = maybe_unserialize($option['option_value']);
        }

        return $backup;
    }

    /**
     * Restore settings from backup
     *
     * @param array $backup_data Backup data
     * @return bool Success status
     */
    public function restore($backup_data)
    {
        if (!is_array($backup_data) || !isset($backup_data['settings'])) {
            throw new Settings_Database_Exception(
                "Invalid backup data format",
                ['backup_data' => $backup_data]
            );
        }

        $restored_count = 0;
        $errors = [];

        foreach ($backup_data['settings'] as $key => $value) {
            try {
                $this->set($key, $value);
                $restored_count++;
            } catch (\Exception $e) {
                $errors[] = "Failed to restore {$key}: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new Settings_Database_Exception(
                "Partial restore completed with errors",
                [
                    'restored_count' => $restored_count,
                    'total_count' => count($backup_data['settings']),
                    'errors' => $errors
                ]
            );
        }

        return true;
    }

    /**
     * Get settings metadata
     *
     * @param string $key Setting key
     * @return array Metadata array
     */
    public function get_metadata($key)
    {
        $metadata_key = $this->get_metadata_key($key);
        return get_option($metadata_key, []);
    }

    /**
     * Set settings metadata
     *
     * @param string $key Setting key
     * @param array $metadata Metadata array
     * @return bool Success status
     */
    public function set_metadata($key, $metadata)
    {
        $metadata_key = $this->get_metadata_key($key);
        return update_option($metadata_key, $metadata);
    }

    /**
     * Delete settings metadata
     *
     * @param string $key Setting key
     * @return bool Success status
     */
    private function delete_metadata($key)
    {
        $metadata_key = $this->get_metadata_key($key);
        return delete_option($metadata_key);
    }

    /**
     * Get option name with prefix
     *
     * @param string $key Setting key
     * @return string Option name
     */
    private function get_option_name($key)
    {
        return $this->prefix . $key;
    }

    /**
     * Get metadata key
     *
     * @param string $key Setting key
     * @return string Metadata key
     */
    private function get_metadata_key($key)
    {
        return $this->prefix . 'meta_' . $key;
    }

    /**
     * Get client IP address
     *
     * @return string Client IP
     */
    private function get_client_ip()
    {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Clear cache
     */
    public function clear_cache()
    {
        $this->cache = [];
    }

    /**
     * Get cache statistics
     *
     * @return array Cache stats
     */
    public function get_cache_stats()
    {
        return [
            'cached_keys' => array_keys($this->cache),
            'cache_size' => count($this->cache),
            'memory_usage' => strlen(serialize($this->cache))
        ];
    }
}