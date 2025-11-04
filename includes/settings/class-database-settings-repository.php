<?php
/**
 * Database Settings Repository
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Interfaces\Settings_Repository_Interface;
use AI_Manager_Pro\Settings\Exceptions\Settings_Database_Exception;

/**
 * Database Settings Repository Class
 * 
 * Implements settings storage using custom database table
 */
class Database_Settings_Repository implements Settings_Repository_Interface
{

    /**
     * Table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Cache for settings
     *
     * @var array
     */
    private $cache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ai_manager_pro_settings';
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

        global $wpdb;

        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_key = %s",
            $key
        ));

        if ($result === null) {
            return $default;
        }

        $value = maybe_unserialize($result);

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
        global $wpdb;

        $serialized_value = maybe_serialize($value);
        $current_time = current_time('mysql');
        $user_id = get_current_user_id();

        // Update cache
        $this->cache[$key] = $value;

        // Check if setting exists
        $exists = $this->exists($key);

        if ($exists) {
            // Update existing setting
            $result = $wpdb->update(
                $this->table_name,
                [
                    'setting_value' => $serialized_value,
                    'last_modified' => $current_time,
                    'modified_by' => $user_id
                ],
                ['setting_key' => $key],
                ['%s', '%s', '%d'],
                ['%s']
            );
        } else {
            // Insert new setting
            $result = $wpdb->insert(
                $this->table_name,
                [
                    'setting_key' => $key,
                    'setting_value' => $serialized_value,
                    'created_at' => $current_time,
                    'last_modified' => $current_time,
                    'modified_by' => $user_id
                ],
                ['%s', '%s', '%s', '%s', '%d']
            );
        }

        if ($result === false) {
            throw new Settings_Database_Exception(
                "Failed to save setting: {$key}",
                [
                    'key' => $key,
                    'value' => $value,
                    'db_error' => $wpdb->last_error
                ]
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
        global $wpdb;

        // Remove from cache
        unset($this->cache[$key]);

        $result = $wpdb->delete(
            $this->table_name,
            ['setting_key' => $key],
            ['%s']
        );

        return $result !== false;
    }

    /**
     * Check if a setting exists
     *
     * @param string $key Setting key
     * @return bool Whether setting exists
     */
    public function exists($key)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE setting_key = %s",
            $key
        ));

        return $count > 0;
    }

    /**
     * Get all settings for a category
     *
     * @param string $category Settings category
     * @return array Settings array
     */
    public function get_category($category)
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT setting_key, setting_value FROM {$this->table_name} WHERE setting_key LIKE %s",
            $category . '.%'
        ), ARRAY_A);

        $settings = [];
        foreach ($results as $result) {
            $key = str_replace($category . '.', '', $result['setting_key']);
            $settings[$key] = maybe_unserialize($result['setting_value']);
        }

        return $settings;
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

        global $wpdb;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete existing category settings
            $wpdb->delete(
                $this->table_name,
                ['setting_key' => ['LIKE' => $category . '.%']],
                ['%s']
            );

            // Insert new settings
            foreach ($settings as $key => $value) {
                $full_key = $category . '.' . $key;
                $this->set($full_key, $value);
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            throw new Settings_Database_Exception(
                "Failed to save category settings: {$category}",
                [
                    'category' => $category,
                    'settings' => $settings,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Create a backup of all settings
     *
     * @return array Backup data
     */
    public function backup()
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY setting_key",
            ARRAY_A
        );

        $backup = [
            'timestamp' => current_time('mysql'),
            'version' => AI_MANAGER_PRO_VERSION,
            'user_id' => get_current_user_id(),
            'settings' => []
        ];

        foreach ($results as $result) {
            $backup['settings'][$result['setting_key']] = [
                'value' => maybe_unserialize($result['setting_value']),
                'created_at' => $result['created_at'],
                'last_modified' => $result['last_modified'],
                'modified_by' => $result['modified_by']
            ];
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

        global $wpdb;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            $restored_count = 0;

            foreach ($backup_data['settings'] as $key => $setting_data) {
                $value = is_array($setting_data) ? $setting_data['value'] : $setting_data;
                $this->set($key, $value);
                $restored_count++;
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            throw new Settings_Database_Exception(
                "Failed to restore settings from backup",
                [
                    'error' => $e->getMessage(),
                    'restored_count' => $restored_count ?? 0
                ]
            );
        }
    }

    /**
     * Get settings metadata
     *
     * @param string $key Setting key
     * @return array Metadata array
     */
    public function get_metadata($key)
    {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT created_at, last_modified, modified_by FROM {$this->table_name} WHERE setting_key = %s",
            $key
        ), ARRAY_A);

        return $result ?: [];
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
        // Metadata is automatically handled in set() method
        return true;
    }

    /**
     * Get all settings with pagination
     *
     * @param int $limit Number of settings to retrieve
     * @param int $offset Offset for pagination
     * @return array Settings array
     */
    public function get_paginated($limit = 50, $offset = 0)
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY setting_key LIMIT %d OFFSET %d",
            $limit,
            $offset
        ), ARRAY_A);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = [
                'value' => maybe_unserialize($result['setting_value']),
                'created_at' => $result['created_at'],
                'last_modified' => $result['last_modified'],
                'modified_by' => $result['modified_by']
            ];
        }

        return $settings;
    }

    /**
     * Get total settings count
     *
     * @return int Total count
     */
    public function get_total_count()
    {
        global $wpdb;

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * Search settings by key pattern
     *
     * @param string $pattern Search pattern
     * @return array Matching settings
     */
    public function search($pattern)
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE setting_key LIKE %s ORDER BY setting_key",
            '%' . $wpdb->esc_like($pattern) . '%'
        ), ARRAY_A);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = [
                'value' => maybe_unserialize($result['setting_value']),
                'created_at' => $result['created_at'],
                'last_modified' => $result['last_modified'],
                'modified_by' => $result['modified_by']
            ];
        }

        return $settings;
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