<?php
/**
 * Settings Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Interfaces\Settings_Repository_Interface;
use AI_Manager_Pro\Settings\Exceptions\Settings_Exception;
use AI_Manager_Pro\Settings\Exceptions\Settings_Validation_Exception;
use AI_Manager_Pro\Settings\Schemas\General_Settings_Schema;
use AI_Manager_Pro\Settings\Schemas\API_Keys_Schema;
use Monolog\Logger;

/**
 * Settings Manager Class
 * 
 * Central manager for all plugin settings
 */
class Settings_Manager
{

    /**
     * Repository instance
     *
     * @var Settings_Repository_Interface
     */
    private $repository;

    /**
     * Encryption service
     *
     * @var Encryption_Service
     */
    private $encryption;

    /**
     * Schema validator
     *
     * @var Settings_Schema_Validator
     */
    private $validator;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Settings cache
     *
     * @var array
     */
    private $cache = [];

    /**
     * Cache expiry time in seconds
     *
     * @var int
     */
    private $cache_expiry = 3600; // 1 hour
    private $logger;

    /**
     * Settings cache
     *
     * @var array
     */
    private $cache = [];

    /**
     * Cache TTL in seconds
     *
     * @var int
     */
    private $cache_ttl = 300; // 5 minutes

    /**
     * Constructor
     *
     * @param Settings_Repository_Interface $repository Repository instance
     * @param Encryption_Service $encryption Encryption service
     * @param Settings_Schema_Validator $validator Schema validator
     * @param Logger $logger Logger instance
     */
    public function __construct(
        Settings_Repository_Interface $repository,
        Encryption_Service $encryption,
        Settings_Schema_Validator $validator,
        Logger $logger
    ) {
        $this->repository = $repository;
        $this->encryption = $encryption;
        $this->validator = $validator;
        $this->logger = $logger;

        $this->register_schemas();
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key (category.setting)
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function get($key, $default = null)
    {
        try {
            // Check cache first
            if ($this->is_cached($key)) {
                return $this->get_from_cache($key);
            }

            $value = $this->repository->get($key, $default);

            // Decrypt if needed
            if ($this->should_decrypt($key, $value)) {
                $value = $this->encryption->decrypt($value);
            }

            // Cache the value
            $this->set_cache($key, $value);

            return $value;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $default;
        }
    }

    /**
     * Set setting value
     *
     * @param string $key Setting key (category.setting)
     * @param mixed $value Setting value
     * @param bool $validate Whether to validate the value
     * @return bool Success status
     * @throws Settings_Exception
     */
    public function set($key, $value, $validate = true)
    {
        try {
            // Validate if requested
            if ($validate) {
                $this->validate_setting($key, $value);
            }

            // Encrypt if needed
            $stored_value = $this->should_encrypt($key) ? $this->encryption->encrypt($value) : $value;

            // Save to repository
            $result = $this->repository->set($key, $stored_value);

            if ($result) {
                // Update cache
                $this->set_cache($key, $value);

                // Log the change
                $this->log_setting_change($key, $value);

                // Trigger action hook
                do_action('ai_manager_pro_setting_updated', $key, $value, $this->get_previous_value($key));
            }

            return $result;

        } catch (Settings_Validation_Exception $e) {
            throw $e; // Re-throw validation exceptions
        } catch (\Exception $e) {
            $this->logger->error('Failed to set setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Settings_Exception(
                "Failed to save setting: {$key}",
                'SETTINGS_005',
                ['key' => $key, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get category settings
     *
     * @param string $category Category name
     * @return array Category settings
     */
    public function get_category($category)
    {
        try {
            $cache_key = "category_{$category}";

            // Check cache first
            if ($this->is_cached($cache_key)) {
                return $this->get_from_cache($cache_key);
            }

            $settings = $this->repository->get_category($category);

            // Decrypt encrypted fields
            $settings = $this->decrypt_category_settings($category, $settings);

            // Cache the settings
            $this->set_cache($cache_key, $settings);

            return $settings;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get category settings', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Set category settings
     *
     * @param string $category Category name
     * @param array $settings Settings array
     * @param bool $validate Whether to validate settings
     * @return bool Success status
     * @throws Settings_Exception
     */
    public function set_category($category, $settings, $validate = true)
    {
        try {
            // Validate if requested
            if ($validate) {
                $this->validate_category_settings($category, $settings);
            }

            // Encrypt sensitive fields
            $encrypted_settings = $this->encrypt_category_settings($category, $settings);

            // Save to repository
            $result = $this->repository->set_category($category, $encrypted_settings);

            if ($result) {
                // Update cache
                $cache_key = "category_{$category}";
                $this->set_cache($cache_key, $settings);

                // Log the change
                $this->log_category_change($category, $settings);

                // Trigger action hook
                do_action('ai_manager_pro_category_updated', $category, $settings);
            }

            return $result;

        } catch (Settings_Validation_Exception $e) {
            throw $e; // Re-throw validation exceptions
        } catch (\Exception $e) {
            $this->logger->error('Failed to set category settings', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            throw new Settings_Exception(
                "Failed to save category settings: {$category}",
                'SETTINGS_005',
                ['category' => $category, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get general settings
     *
     * @return array General settings
     */
    public function get_general_settings()
    {
        $settings = $this->get_category('general');
        $defaults = General_Settings_Schema::get_defaults();

        return array_merge($defaults, $settings);
    }

    /**
     * Set general settings
     *
     * @param array $settings General settings
     * @return bool Success status
     */
    public function set_general_settings($settings)
    {
        return $this->set_category('general', $settings);
    }

    /**
     * Get API keys settings
     *
     * @param string $provider Provider name (optional)
     * @return array API keys settings
     */
    public function get_api_keys($provider = null)
    {
        $settings = $this->get_category('api_keys');

        if ($provider) {
            return $settings[$provider] ?? [];
        }

        return $settings;
    }

    /**
     * Set API keys settings
     *
     * @param array $settings API keys settings
     * @param string $provider Provider name (optional)
     * @return bool Success status
     */
    public function set_api_keys($settings, $provider = null)
    {
        if ($provider) {
            $current_settings = $this->get_api_keys();
            $current_settings[$provider] = $settings;
            $settings = $current_settings;
        }

        return $this->set_category('api_keys', $settings);
    }

    /**
     * Test API connection
     *
     * @param string $provider Provider name
     * @return bool Connection status
     */
    public function test_api_connection($provider)
    {
        try {
            $api_keys = $this->get_api_keys($provider);

            if (empty($api_keys['api_key'])) {
                return false;
            }

            // This would typically make an actual API call
            // For now, we'll just validate the key format
            $validator_name = $provider . '_api_key';

            if (isset($this->validator->get_validators()[$validator_name])) {
                return $this->validator->validate_custom($api_keys['api_key'], $validator_name, '');
            }

            return !empty($api_keys['api_key']);

        } catch (\Exception $e) {
            $this->logger->error('API connection test failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Backup all settings
     *
     * @return array Backup data
     */
    public function backup()
    {
        try {
            $backup = $this->repository->backup();

            $this->logger->info('Settings backup created', [
                'settings_count' => count($backup['settings'] ?? []),
                'user_id' => get_current_user_id()
            ]);

            return $backup;

        } catch (\Exception $e) {
            $this->logger->error('Settings backup failed', [
                'error' => $e->getMessage()
            ]);

            throw new Settings_Exception(
                'Failed to create settings backup',
                'SETTINGS_005',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Restore settings from backup
     *
     * @param array $backup_data Backup data
     * @return bool Success status
     */
    public function restore($backup_data)
    {
        try {
            $result = $this->repository->restore($backup_data);

            if ($result) {
                // Clear cache
                $this->clear_cache();

                $this->logger->info('Settings restored from backup', [
                    'settings_count' => count($backup_data['settings'] ?? []),
                    'user_id' => get_current_user_id()
                ]);

                // Trigger action hook
                do_action('ai_manager_pro_settings_restored', $backup_data);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Settings restore failed', [
                'error' => $e->getMessage()
            ]);

            throw new Settings_Exception(
                'Failed to restore settings from backup',
                'SETTINGS_005',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Validate setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @throws Settings_Validation_Exception
     */
    private function validate_setting($key, $value)
    {
        $parts = explode('.', $key);
        $category = $parts[0];

        $validation_result = $this->validator->validate($category, [$parts[1] => $value]);

        if ($validation_result !== true) {
            throw new Settings_Validation_Exception(
                "Validation failed for setting: {$key}",
                $validation_result,
                ['key' => $key, 'value' => $value]
            );
        }
    }

    /**
     * Validate category settings
     *
     * @param string $category Category name
     * @param array $settings Settings array
     * @throws Settings_Validation_Exception
     */
    private function validate_category_settings($category, $settings)
    {
        $validation_result = $this->validator->validate($category, $settings);

        if ($validation_result !== true) {
            throw new Settings_Validation_Exception(
                "Validation failed for category: {$category}",
                $validation_result,
                ['category' => $category, 'settings' => $settings]
            );
        }
    }

    /**
     * Check if setting should be encrypted
     *
     * @param string $key Setting key
     * @return bool Whether to encrypt
     */
    private function should_encrypt($key)
    {
        // API keys should always be encrypted
        if (strpos($key, 'api_keys.') === 0 && strpos($key, '.api_key') !== false) {
            return true;
        }

        // Check schema for encryption flag
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $category = $parts[0];
            $field = $parts[1];

            $schemas = $this->validator->get_schemas();
            if (isset($schemas[$category]['properties'][$field]['encrypted'])) {
                return $schemas[$category]['properties'][$field]['encrypted'];
            }
        }

        return false;
    }

    /**
     * Check if setting should be decrypted
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Whether to decrypt
     */
    private function should_decrypt($key, $value)
    {
        return $this->should_encrypt($key) && $this->encryption->is_encrypted($value);
    }

    /**
     * Encrypt category settings
     *
     * @param string $category Category name
     * @param array $settings Settings array
     * @return array Encrypted settings
     */
    private function encrypt_category_settings($category, $settings)
    {
        $encrypted_settings = $settings;

        foreach ($settings as $key => $value) {
            $full_key = $category . '.' . $key;
            if ($this->should_encrypt($full_key)) {
                $encrypted_settings[$key] = $this->encryption->encrypt($value);
            }
        }

        return $encrypted_settings;
    }

    /**
     * Decrypt category settings
     *
     * @param string $category Category name
     * @param array $settings Settings array
     * @return array Decrypted settings
     */
    private function decrypt_category_settings($category, $settings)
    {
        $decrypted_settings = $settings;

        foreach ($settings as $key => $value) {
            $full_key = $category . '.' . $key;
            if ($this->should_decrypt($full_key, $value)) {
                $decrypted_settings[$key] = $this->encryption->decrypt($value);
            }
        }

        return $decrypted_settings;
    }

    /**
     * Register schemas
     */
    private function register_schemas()
    {
        $this->validator->register_schema('general', General_Settings_Schema::get_schema());
        $this->validator->register_schema('api_keys', API_Keys_Schema::get_schema());
    }

    /**
     * Cache management methods
     */
    private function is_cached($key)
    {
        return isset($this->cache[$key]) &&
            (time() - $this->cache[$key]['timestamp']) < $this->cache_ttl;
    }

    private function get_from_cache($key)
    {
        return $this->cache[$key]['value'];
    }

    private function set_cache($key, $value)
    {
        $this->cache[$key] = [
            'value' => $value,
            'timestamp' => time()
        ];
    }

    public function clear_cache()
    {
        $this->cache = [];
    }

    /**
     * Logging methods
     */
    private function log_setting_change($key, $value)
    {
        $this->logger->info('Setting updated', [
            'key' => $key,
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    private function log_category_change($category, $settings)
    {
        $this->logger->info('Category settings updated', [
            'category' => $category,
            'settings_count' => count($settings),
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        // Log to Settings Change Log
        try {
            if (class_exists('AI_Manager_Pro\\Core\\Plugin')) {
                $plugin = \AI_Manager_Pro\Core\Plugin::get_instance();
                $container = $plugin->get_container();
                $change_log = $container->get('settings_change_log');

                if ($change_log) {
                    // Get old settings for comparison
                    $old_settings = $this->get_category($category);

                    // Log each setting change individually for better tracking
                    foreach ($settings as $key => $new_value) {
                        $old_value = $old_settings[$key] ?? null;

                        // Only log if value actually changed
                        if ($old_value !== $new_value) {
                            $change_log->log_change(
                                "settings.{$category}.{$key}",
                                $old_value,
                                $new_value,
                                'update'
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to log settings change: ' . $e->getMessage());
            }
        }
    }

    private function get_previous_value($key)
    {
        // This would typically get the previous value from change log
        return null;
    }
}    

    /**
     * Constructor
     *
     * @param Settings_Repository_Interface $repository Repository instance
     * @param Encryption_Service $encryption Encryption service
     * @param Settings_Schema_Validator $validator Schema validator
     * @param Logger $logger Logger instance
     */
    public function __construct($repository, $encryption, $validator, $logger = null) {
        $this->repository = $repository;
        $this->encryption = $encryption;
        $this->validator = $validator;
        $this->logger = $logger;
        
        $this->init_cache();
    }
    
    /**
     * Initialize cache
     *
     * @return void
     */
    private function init_cache() {
        $cached_data = wp_cache_get('ai_manager_settings', 'ai_manager_pro');
        if ($cached_data && is_array($cached_data)) {
            $this->cache = $cached_data;
        }
    }
    
    /**
     * Get setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     * @throws Settings_Exception If setting retrieval fails
     */
    public function get($key, $default = null) {
        try {
            // Check cache first
            if (isset($this->cache[$key])) {
                return $this->cache[$key]['value'];
            }
            
            $value = $this->repository->get($key, $default);
            
            // Cache the value
            $this->cache[$key] = [
                'value' => $value,
                'timestamp' => time()
            ];
            
            $this->update_cache();
            
            return $value;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get setting', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
            throw new Settings_Exception("Failed to get setting: {$key}", 0, $e);
        }
    }
    
    /**
     * Set setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param bool $encrypt Whether to encrypt the value
     * @return bool Success status
     * @throws Settings_Validation_Exception If validation fails
     * @throws Settings_Exception If setting save fails
     */
    public function set($key, $value, $encrypt = false) {
        try {
            // Get old value for change tracking
            $old_value = $this->get($key);
            
            // Validate the value
            $this->validate_setting($key, $value);
            
            // Encrypt if needed
            if ($encrypt) {
                $value = $this->encryption->encrypt($value);
            }
            
            // Save to repository
            $success = $this->repository->set($key, $value);
            
            if ($success) {
                // Update cache
                $this->cache[$key] = [
                    'value' => $encrypt ? $value : $value, // Store encrypted value in cache
                    'timestamp' => time()
                ];
                
                $this->update_cache();
                
                // Log the change
                $this->log_change($key, $old_value, $value, $encrypt);
                
                // Fire action hook
                do_action('ai_manager_setting_updated', $key, $value, $old_value);
            }
            
            return $success;
            
        } catch (Settings_Validation_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to set setting', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
            throw new Settings_Exception("Failed to set setting: {$key}", 0, $e);
        }
    }
    
    /**
     * Get multiple settings
     *
     * @param array $keys Setting keys
     * @return array Settings values
     */
    public function get_multiple($keys) {
        $results = [];
        
        foreach ($keys as $key) {
            try {
                $results[$key] = $this->get($key);
            } catch (Settings_Exception $e) {
                $results[$key] = null;
            }
        }
        
        return $results;
    }
    
    /**
     * Set multiple settings
     *
     * @param array $settings Key-value pairs
     * @param array $encrypt_keys Keys to encrypt
     * @return bool Success status
     */
    public function set_multiple($settings, $encrypt_keys = []) {
        $success = true;
        
        foreach ($settings as $key => $value) {
            $encrypt = in_array($key, $encrypt_keys);
            try {
                if (!$this->set($key, $value, $encrypt)) {
                    $success = false;
                }
            } catch (Exception $e) {
                $success = false;
                if ($this->logger) {
                    $this->logger->error('Failed to set multiple settings', [
                        'key' => $key,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Delete setting
     *
     * @param string $key Setting key
     * @return bool Success status
     */
    public function delete($key) {
        try {
            $old_value = $this->get($key);
            $success = $this->repository->delete($key);
            
            if ($success) {
                // Remove from cache
                unset($this->cache[$key]);
                $this->update_cache();
                
                // Log the change
                $this->log_change($key, $old_value, null, false);
                
                // Fire action hook
                do_action('ai_manager_setting_deleted', $key, $old_value);
            }
            
            return $success;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to delete setting', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }
    
    /**
     * Get all settings
     *
     * @return array All settings
     */
    public function get_all() {
        try {
            return $this->repository->get_all();
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get all settings', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }
    
    /**
     * Clear all settings
     *
     * @return bool Success status
     */
    public function clear() {
        try {
            $success = $this->repository->clear();
            
            if ($success) {
                $this->cache = [];
                $this->update_cache();
                
                // Fire action hook
                do_action('ai_manager_settings_cleared');
            }
            
            return $success;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to clear settings', [
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }
    
    /**
     * Validate setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @throws Settings_Validation_Exception If validation fails
     */
    private function validate_setting($key, $value) {
        // Get schema based on key prefix
        $schema = $this->get_schema_for_key($key);
        
        if ($schema && $this->validator) {
            $result = $this->validator->validate($key, $value, $schema);
            
            if (!empty($result['errors'])) {
                throw new Settings_Validation_Exception(
                    "Validation failed for setting: {$key}",
                    $result['errors']
                );
            }
        }
    }
    
    /**
     * Get schema for setting key
     *
     * @param string $key Setting key
     * @return array|null Schema array or null
     */
    private function get_schema_for_key($key) {
        // Determine schema based on key prefix
        if (strpos($key, 'general_') === 0) {
            return General_Settings_Schema::get_schema();
        } elseif (strpos($key, 'api_') === 0) {
            return API_Keys_Schema::get_schema();
        }
        
        return null;
    }
    
    /**
     * Update cache in WordPress
     *
     * @return void
     */
    private function update_cache() {
        wp_cache_set('ai_manager_settings', $this->cache, 'ai_manager_pro', $this->cache_expiry);
    }
    
    /**
     * Log setting change
     *
     * @param string $key Setting key
     * @param mixed $old_value Old value
     * @param mixed $new_value New value
     * @param bool $encrypted Whether value is encrypted
     * @return void
     */
    private function log_change($key, $old_value, $new_value, $encrypted = false) {
        if ($this->logger) {
            $this->logger->info('Setting changed', [
                'key' => $key,
                'old_value' => $encrypted ? '[ENCRYPTED]' : $old_value,
                'new_value' => $encrypted ? '[ENCRYPTED]' : $new_value,
                'user_id' => get_current_user_id(),
                'timestamp' => current_time('mysql')
            ]);
        }
        
        // Also store in change log if available
        do_action('ai_manager_log_setting_change', $key, $old_value, $new_value, $encrypted);
    }
    
    /**
     * Get cached settings count
     *
     * @return int Number of cached settings
     */
    public function get_cache_count() {
        return count($this->cache);
    }
    
    /**
     * Clear cache
     *
     * @return void
     */
    public function clear_cache() {
        $this->cache = [];
        wp_cache_delete('ai_manager_settings', 'ai_manager_pro');
    }
    
    /**
     * Get settings by prefix
     *
     * @param string $prefix Key prefix
     * @return array Matching settings
     */
    public function get_by_prefix($prefix) {
        try {
            return $this->repository->get_by_prefix($prefix);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get settings by prefix', [
                    'prefix' => $prefix,
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }
}