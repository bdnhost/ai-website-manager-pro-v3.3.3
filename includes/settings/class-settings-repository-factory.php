<?php
/**
 * Settings Repository Factory
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Interfaces\Settings_Repository_Interface;

/**
 * Settings Repository Factory Class
 * 
 * Creates appropriate repository instances based on configuration
 */
class Settings_Repository_Factory
{

    /**
     * Repository instances cache
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Create repository instance
     *
     * @param string $type Repository type ('wordpress' or 'database')
     * @param array $config Configuration options
     * @return Settings_Repository_Interface Repository instance
     */
    public static function create($type = 'wordpress', $config = [])
    {
        $cache_key = $type . '_' . md5(serialize($config));

        if (isset(self::$instances[$cache_key])) {
            return self::$instances[$cache_key];
        }

        switch ($type) {
            case 'database':
                $repository = new Database_Settings_Repository();
                break;

            case 'wordpress':
            default:
                $prefix = $config['prefix'] ?? 'ai_manager_pro_';
                $repository = new WordPress_Settings_Repository($prefix);
                break;
        }

        self::$instances[$cache_key] = $repository;
        return $repository;
    }

    /**
     * Get default repository
     *
     * @return Settings_Repository_Interface Default repository
     */
    public static function get_default()
    {
        // Use database repository if table exists, otherwise use WordPress options
        if (self::database_table_exists()) {
            return self::create('database');
        }

        return self::create('wordpress');
    }

    /**
     * Check if database table exists
     *
     * @return bool Whether table exists
     */
    private static function database_table_exists()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_settings';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));

        return $table_exists === $table_name;
    }

    /**
     * Clear instances cache
     */
    public static function clear_cache()
    {
        self::$instances = [];
    }

    /**
     * Get available repository types
     *
     * @return array Available types
     */
    public static function get_available_types()
    {
        $types = ['wordpress'];

        if (self::database_table_exists()) {
            $types[] = 'database';
        }

        return $types;
    }
}