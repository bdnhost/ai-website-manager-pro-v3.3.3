<?php
/**
 * Settings Migration
 *
 * @package AI_Manager_Pro
 * @subpackage Database
 */

namespace AI_Manager_Pro\Database;

use AI_Manager_Pro\Settings\Settings_Manager;
use AI_Manager_Pro\Settings\Encryption_Service;
use Monolog\Logger;

/**
 * Settings Migration Class
 * 
 * Handles migration of settings between versions
 */
class Settings_Migration
{

    /**
     * Settings manager
     *
     * @var Settings_Manager
     */
    private $settings_manager;

    /**
     * Encryption service
     *
     * @var Encryption_Service
     */
    private $encryption;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Current database version
     *
     * @var string
     */
    private $db_version;

    /**
     * Migration steps
     *
     * @var array
     */
    private $migrations = [
        '1.0.0' => 'migrate_from_1_0_0',
        '1.5.0' => 'migrate_from_1_5_0',
        '2.0.0' => 'migrate_from_2_0_0'
    ];

    /**
     * Constructor
     *
     * @param Settings_Manager $settings_manager Settings manager
     * @param Encryption_Service $encryption Encryption service
     * @param Logger $logger Logger instance
     */
    public function __construct(Settings_Manager $settings_manager, Encryption_Service $encryption, Logger $logger)
    {
        $this->settings_manager = $settings_manager;
        $this->encryption = $encryption;
        $this->logger = $logger;

        $this->db_version = get_option('ai_manager_pro_db_version', '0.0.0');
    }

    /**
     * Run migrations
     *
     * @return bool Success status
     */
    public function run_migrations()
    {
        try {
            $current_version = AI_MANAGER_PRO_VERSION;

            // Check if migration is needed
            if (version_compare($this->db_version, $current_version, '>=')) {
                $this->logger->info('No migration needed', [
                    'db_version' => $this->db_version,
                    'plugin_version' => $current_version
                ]);
                return true;
            }

            $this->logger->info('Starting migration', [
                'from_version' => $this->db_version,
                'to_version' => $current_version
            ]);

            // Create backup before migration
            $backup_created = $this->create_pre_migration_backup();

            if (!$backup_created) {
                $this->logger->warning('Failed to create pre-migration backup, continuing anyway');
            }

            // Run migration steps
            $migration_success = true;

            foreach ($this->migrations as $version => $method) {
                if (
                    version_compare($this->db_version, $version, '<') &&
                    version_compare($current_version, $version, '>=')
                ) {

                    $this->logger->info('Running migration step', ['version' => $version]);

                    if (method_exists($this, $method)) {
                        $result = $this->$method();

                        if (!$result) {
                            $migration_success = false;
                            $this->logger->error('Migration step failed', ['version' => $version]);
                            break;
                        }
                    }
                }
            }

            if ($migration_success) {
                // Update database version
                update_option('ai_manager_pro_db_version', $current_version);

                // Create database tables if needed
                $this->create_database_tables();

                $this->logger->info('Migration completed successfully', [
                    'from_version' => $this->db_version,
                    'to_version' => $current_version
                ]);

                return true;
            } else {
                $this->logger->error('Migration failed, attempting rollback');
                $this->rollback_migration();
                return false;
            }

        } catch (\Exception $e) {
            $this->logger->error('Migration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->rollback_migration();
            return false;
        }
    }

    /**
     * Migrate from version 1.0.0
     *
     * @return bool Success status
     */
    private function migrate_from_1_0_0()
    {
        try {
            // Migrate old settings format to new structure
            $old_settings = get_option('ai_website_manager_settings', []);

            if (!empty($old_settings)) {
                // Map old settings to new structure
                $new_general_settings = [
                    'default_provider' => $old_settings['ai_provider'] ?? 'openai',
                    'auto_publish' => $old_settings['auto_publish'] ?? false,
                    'default_post_status' => $old_settings['post_status'] ?? 'draft',
                    'enable_logging' => true,
                    'log_level' => 'info'
                ];

                // Save new general settings
                $this->settings_manager->set_general_settings($new_general_settings);

                // Migrate API keys
                $old_api_keys = get_option('ai_website_manager_api_keys', []);

                if (!empty($old_api_keys)) {
                    $new_api_keys = [];

                    // Map old API key structure
                    if (isset($old_api_keys['openai_key'])) {
                        $new_api_keys['openai'] = [
                            'api_key' => $old_api_keys['openai_key'],
                            'model' => $old_api_keys['openai_model'] ?? 'gpt-3.5-turbo',
                            'max_tokens' => $old_api_keys['openai_max_tokens'] ?? 2000
                        ];
                    }

                    if (isset($old_api_keys['claude_key'])) {
                        $new_api_keys['claude'] = [
                            'api_key' => $old_api_keys['claude_key'],
                            'model' => $old_api_keys['claude_model'] ?? 'claude-3-haiku-20240307',
                            'max_tokens' => $old_api_keys['claude_max_tokens'] ?? 2000
                        ];
                    }

                    // Save new API keys
                    $this->settings_manager->set_api_keys($new_api_keys);
                }

                // Remove old options
                delete_option('ai_website_manager_settings');
                delete_option('ai_website_manager_api_keys');

                $this->logger->info('Successfully migrated from version 1.0.0');
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate from version 1.0.0', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Migrate from version 1.5.0
     *
     * @return bool Success status
     */
    private function migrate_from_1_5_0()
    {
        try {
            // Add new settings introduced in 2.0.0
            $general_settings = $this->settings_manager->get_general_settings();

            // Add new fields with defaults
            $new_fields = [
                'content_language' => 'en',
                'enable_seo_optimization' => true,
                'content_tone' => 'professional',
                'backup_frequency' => 'weekly',
                'cleanup_logs_after' => 30
            ];

            foreach ($new_fields as $field => $default_value) {
                if (!isset($general_settings[$field])) {
                    $general_settings[$field] = $default_value;
                }
            }

            $this->settings_manager->set_general_settings($general_settings);

            // Encrypt existing API keys if not already encrypted
            $api_keys = $this->settings_manager->get_api_keys();
            $updated = false;

            foreach ($api_keys as $provider => $settings) {
                if (isset($settings['api_key']) && !$this->encryption->is_encrypted($settings['api_key'])) {
                    $api_keys[$provider]['api_key'] = $this->encryption->encrypt($settings['api_key']);
                    $updated = true;
                }
            }

            if ($updated) {
                $this->settings_manager->set_api_keys($api_keys);
                $this->logger->info('Encrypted existing API keys during migration');
            }

            $this->logger->info('Successfully migrated from version 1.5.0');
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate from version 1.5.0', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Migrate from version 2.0.0
     *
     * @return bool Success status
     */
    private function migrate_from_2_0_0()
    {
        try {
            // Future migration logic for version 2.0.0+
            $this->logger->info('Successfully migrated from version 2.0.0');
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate from version 2.0.0', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create database tables
     */
    private function create_database_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Settings table
        $settings_table = $wpdb->prefix . 'ai_manager_pro_settings';
        $settings_sql = "CREATE TABLE IF NOT EXISTS {$settings_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            modified_by bigint(20),
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY last_modified (last_modified),
            KEY modified_by (modified_by)
        ) $charset_collate;";

        // Settings log table
        $log_table = $wpdb->prefix . 'ai_manager_pro_settings_log';
        $log_sql = "CREATE TABLE IF NOT EXISTS {$log_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            old_value longtext,
            new_value longtext,
            action varchar(20) NOT NULL,
            user_id bigint(20),
            user_ip varchar(45),
            user_agent text,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            context longtext,
            PRIMARY KEY (id),
            KEY setting_key (setting_key),
            KEY user_id (user_id),
            KEY timestamp (timestamp),
            KEY action (action)
        ) $charset_collate;";

        // Backups table
        $backups_table = $wpdb->prefix . 'ai_manager_pro_backups';
        $backups_sql = "CREATE TABLE IF NOT EXISTS {$backups_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            backup_id varchar(36) NOT NULL,
            name varchar(255) NOT NULL,
            filename varchar(255) NOT NULL,
            filepath text NOT NULL,
            size bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20),
            settings_count int(11),
            last_restored_at datetime,
            last_restored_by bigint(20),
            restore_count int(11) DEFAULT 0,
            imported boolean DEFAULT FALSE,
            PRIMARY KEY (id),
            UNIQUE KEY backup_id (backup_id),
            KEY created_at (created_at),
            KEY created_by (created_by)
        ) $charset_collate;";

        // Automation tasks table
        $automation_table = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        $automation_sql = "CREATE TABLE IF NOT EXISTS {$automation_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            rules longtext NOT NULL,
            schedule varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'active',
            last_run datetime,
            next_run datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20),
            PRIMARY KEY (id),
            KEY status (status),
            KEY next_run (next_run),
            KEY created_by (created_by)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($settings_sql);
        dbDelta($log_sql);
        dbDelta($backups_sql);
        dbDelta($automation_sql);

        $this->logger->info('Database tables created/updated');
    }

    /**
     * Create pre-migration backup
     *
     * @return bool Success status
     */
    private function create_pre_migration_backup()
    {
        try {
            $backup_name = 'pre_migration_' . $this->db_version . '_to_' . AI_MANAGER_PRO_VERSION;
            $backup_data = $this->settings_manager->backup();

            // Save backup to file
            $backup_dir = WP_CONTENT_DIR . '/ai-manager-pro-backups/';

            if (!file_exists($backup_dir)) {
                wp_mkdir_p($backup_dir);
            }

            $backup_file = $backup_dir . $backup_name . '.json';
            $result = file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));

            if ($result !== false) {
                $this->logger->info('Pre-migration backup created', [
                    'backup_file' => $backup_file,
                    'size' => $result
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->error('Failed to create pre-migration backup', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Rollback migration
     */
    private function rollback_migration()
    {
        try {
            // Attempt to restore from pre-migration backup
            $backup_name = 'pre_migration_' . $this->db_version . '_to_' . AI_MANAGER_PRO_VERSION;
            $backup_file = WP_CONTENT_DIR . '/ai-manager-pro-backups/' . $backup_name . '.json';

            if (file_exists($backup_file)) {
                $backup_data = json_decode(file_get_contents($backup_file), true);

                if ($backup_data) {
                    $this->settings_manager->restore($backup_data);
                    $this->logger->info('Migration rollback completed');
                    return true;
                }
            }

            $this->logger->warning('Could not rollback migration - backup not found');
            return false;

        } catch (\Exception $e) {
            $this->logger->error('Migration rollback failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if migration is needed
     *
     * @return bool Whether migration is needed
     */
    public function is_migration_needed()
    {
        return version_compare($this->db_version, AI_MANAGER_PRO_VERSION, '<');
    }

    /**
     * Get current database version
     *
     * @return string Database version
     */
    public function get_db_version()
    {
        return $this->db_version;
    }

    /**
     * Get plugin version
     *
     * @return string Plugin version
     */
    public function get_plugin_version()
    {
        return AI_MANAGER_PRO_VERSION;
    }

    /**
     * Get migration status
     *
     * @return array Migration status
     */
    public function get_migration_status()
    {
        return [
            'db_version' => $this->db_version,
            'plugin_version' => AI_MANAGER_PRO_VERSION,
            'migration_needed' => $this->is_migration_needed(),
            'available_migrations' => array_keys($this->migrations)
        ];
    }

    /**
     * Force migration (for testing)
     *
     * @param string $from_version Force migration from this version
     * @return bool Success status
     */
    public function force_migration($from_version)
    {
        $this->db_version = $from_version;
        update_option('ai_manager_pro_db_version', $from_version);

        return $this->run_migrations();
    }

    /**
     * Clean up migration files
     *
     * @param int $days_old Delete files older than this many days
     * @return int Number of files deleted
     */
    public function cleanup_migration_files($days_old = 30)
    {
        $backup_dir = WP_CONTENT_DIR . '/ai-manager-pro-backups/';
        $deleted_count = 0;

        if (!is_dir($backup_dir)) {
            return 0;
        }

        $files = glob($backup_dir . 'pre_migration_*.json');
        $cutoff_time = time() - ($days_old * 24 * 60 * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                if (unlink($file)) {
                    $deleted_count++;
                    $this->logger->info('Deleted old migration backup', ['file' => basename($file)]);
                }
            }
        }

        return $deleted_count;
    }
}