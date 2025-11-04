<?php
/**
 * Settings Backup Service
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Exceptions\Settings_Exception;
use Monolog\Logger;

/**
 * Settings Backup Class
 * 
 * Handles backup and restore operations for settings
 */
class Settings_Backup
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
     * Backup directory
     *
     * @var string
     */
    private $backup_dir;

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

        $this->backup_dir = WP_CONTENT_DIR . '/ai-manager-pro-backups/';
        $this->ensure_backup_directory();
    }

    /**
     * Create backup
     *
     * @param string $name Backup name (optional)
     * @return array Backup info
     * @throws Settings_Exception
     */
    public function create_backup($name = null)
    {
        try {
            $backup_data = $this->settings_manager->backup();

            // Generate backup name if not provided
            if (!$name) {
                $name = 'backup_' . date('Y-m-d_H-i-s');
            }

            // Add backup metadata
            $backup_data['backup_name'] = $name;
            $backup_data['backup_id'] = wp_generate_uuid4();
            $backup_data['created_by'] = get_current_user_id();
            $backup_data['site_url'] = get_site_url();
            $backup_data['wp_version'] = get_bloginfo('version');
            $backup_data['plugin_version'] = AI_MANAGER_PRO_VERSION;

            // Encrypt backup data
            $encrypted_backup = $this->encryption->encrypt(json_encode($backup_data));

            // Save to file
            $filename = $this->sanitize_filename($name) . '.backup';
            $filepath = $this->backup_dir . $filename;

            if (file_put_contents($filepath, $encrypted_backup) === false) {
                throw new Settings_Exception(
                    'Failed to write backup file',
                    'SETTINGS_005',
                    ['filepath' => $filepath]
                );
            }

            // Save backup info to database
            $backup_info = [
                'backup_id' => $backup_data['backup_id'],
                'name' => $name,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id(),
                'settings_count' => count($backup_data['settings'] ?? [])
            ];

            $this->save_backup_info($backup_info);

            $this->logger->info('Settings backup created', $backup_info);

            return $backup_info;

        } catch (\Exception $e) {
            $this->logger->error('Backup creation failed', [
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Settings_Exception(
                'Failed to create backup: ' . $e->getMessage(),
                'SETTINGS_005',
                ['name' => $name, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Restore from backup
     *
     * @param string $backup_id Backup ID or filename
     * @return bool Success status
     * @throws Settings_Exception
     */
    public function restore_backup($backup_id)
    {
        try {
            // Get backup info
            $backup_info = $this->get_backup_info($backup_id);

            if (!$backup_info) {
                throw new Settings_Exception(
                    'Backup not found',
                    'SETTINGS_001',
                    ['backup_id' => $backup_id]
                );
            }

            // Read backup file
            $filepath = $backup_info['filepath'];

            if (!file_exists($filepath)) {
                throw new Settings_Exception(
                    'Backup file not found',
                    'SETTINGS_001',
                    ['filepath' => $filepath]
                );
            }

            $encrypted_data = file_get_contents($filepath);

            if ($encrypted_data === false) {
                throw new Settings_Exception(
                    'Failed to read backup file',
                    'SETTINGS_005',
                    ['filepath' => $filepath]
                );
            }

            // Decrypt backup data
            $backup_json = $this->encryption->decrypt($encrypted_data);
            $backup_data = json_decode($backup_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Settings_Exception(
                    'Invalid backup data format: ' . json_last_error_msg(),
                    'SETTINGS_001',
                    ['filepath' => $filepath]
                );
            }

            // Validate backup data
            $this->validate_backup_data($backup_data);

            // Create pre-restore backup
            $pre_restore_backup = $this->create_backup('pre_restore_' . date('Y-m-d_H-i-s'));

            try {
                // Restore settings
                $result = $this->settings_manager->restore($backup_data);

                if ($result) {
                    $this->logger->info('Settings restored from backup', [
                        'backup_id' => $backup_id,
                        'backup_name' => $backup_info['name'],
                        'settings_count' => count($backup_data['settings'] ?? []),
                        'user_id' => get_current_user_id()
                    ]);

                    // Update restore info
                    $this->update_backup_restore_info($backup_info['id'], [
                        'last_restored_at' => current_time('mysql'),
                        'last_restored_by' => get_current_user_id(),
                        'restore_count' => ($backup_info['restore_count'] ?? 0) + 1
                    ]);
                }

                return $result;

            } catch (\Exception $e) {
                // If restore fails, try to restore the pre-restore backup
                $this->logger->warning('Restore failed, attempting rollback', [
                    'backup_id' => $backup_id,
                    'error' => $e->getMessage()
                ]);

                try {
                    $this->restore_backup($pre_restore_backup['backup_id']);
                } catch (\Exception $rollback_error) {
                    $this->logger->error('Rollback also failed', [
                        'rollback_error' => $rollback_error->getMessage()
                    ]);
                }

                throw $e;
            }

        } catch (\Exception $e) {
            $this->logger->error('Backup restore failed', [
                'backup_id' => $backup_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Settings_Exception(
                'Failed to restore backup: ' . $e->getMessage(),
                'SETTINGS_005',
                ['backup_id' => $backup_id, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get list of backups
     *
     * @param int $limit Number of backups to return
     * @param int $offset Offset for pagination
     * @return array List of backups
     */
    public function get_backups($limit = 20, $offset = 0)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ), ARRAY_A);

        return $results ?: [];
    }

    /**
     * Delete backup
     *
     * @param string $backup_id Backup ID
     * @return bool Success status
     */
    public function delete_backup($backup_id)
    {
        try {
            $backup_info = $this->get_backup_info($backup_id);

            if (!$backup_info) {
                return false;
            }

            // Delete file
            if (file_exists($backup_info['filepath'])) {
                unlink($backup_info['filepath']);
            }

            // Delete from database
            global $wpdb;
            $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

            $result = $wpdb->delete(
                $table_name,
                ['backup_id' => $backup_id],
                ['%s']
            );

            if ($result) {
                $this->logger->info('Backup deleted', [
                    'backup_id' => $backup_id,
                    'name' => $backup_info['name']
                ]);
            }

            return $result !== false;

        } catch (\Exception $e) {
            $this->logger->error('Backup deletion failed', [
                'backup_id' => $backup_id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Export backup to file
     *
     * @param string $backup_id Backup ID
     * @return string File path
     * @throws Settings_Exception
     */
    public function export_backup($backup_id)
    {
        $backup_info = $this->get_backup_info($backup_id);

        if (!$backup_info) {
            throw new Settings_Exception(
                'Backup not found',
                'SETTINGS_001',
                ['backup_id' => $backup_id]
            );
        }

        return $backup_info['filepath'];
    }

    /**
     * Import backup from file
     *
     * @param string $filepath File path
     * @param string $name Backup name
     * @return array Backup info
     * @throws Settings_Exception
     */
    public function import_backup($filepath, $name = null)
    {
        if (!file_exists($filepath)) {
            throw new Settings_Exception(
                'Backup file not found',
                'SETTINGS_001',
                ['filepath' => $filepath]
            );
        }

        try {
            // Read and decrypt file
            $encrypted_data = file_get_contents($filepath);
            $backup_json = $this->encryption->decrypt($encrypted_data);
            $backup_data = json_decode($backup_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Settings_Exception(
                    'Invalid backup file format',
                    'SETTINGS_001',
                    ['filepath' => $filepath]
                );
            }

            // Validate backup data
            $this->validate_backup_data($backup_data);

            // Generate new backup name
            if (!$name) {
                $name = 'imported_' . date('Y-m-d_H-i-s');
            }

            // Copy file to backup directory
            $filename = $this->sanitize_filename($name) . '.backup';
            $new_filepath = $this->backup_dir . $filename;

            if (!copy($filepath, $new_filepath)) {
                throw new Settings_Exception(
                    'Failed to copy backup file',
                    'SETTINGS_005',
                    ['source' => $filepath, 'destination' => $new_filepath]
                );
            }

            // Save backup info
            $backup_info = [
                'backup_id' => wp_generate_uuid4(),
                'name' => $name,
                'filename' => $filename,
                'filepath' => $new_filepath,
                'size' => filesize($new_filepath),
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id(),
                'settings_count' => count($backup_data['settings'] ?? []),
                'imported' => true
            ];

            $this->save_backup_info($backup_info);

            $this->logger->info('Backup imported', $backup_info);

            return $backup_info;

        } catch (\Exception $e) {
            throw new Settings_Exception(
                'Failed to import backup: ' . $e->getMessage(),
                'SETTINGS_005',
                ['filepath' => $filepath, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Cleanup old backups
     *
     * @param int $keep_count Number of backups to keep
     * @return int Number of deleted backups
     */
    public function cleanup_old_backups($keep_count = 10)
    {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

            // Get old backups
            $old_backups = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d, 999999",
                $keep_count
            ), ARRAY_A);

            $deleted_count = 0;

            foreach ($old_backups as $backup) {
                if ($this->delete_backup($backup['backup_id'])) {
                    $deleted_count++;
                }
            }

            $this->logger->info('Old backups cleaned up', [
                'deleted_count' => $deleted_count,
                'kept_count' => $keep_count
            ]);

            return $deleted_count;

        } catch (\Exception $e) {
            $this->logger->error('Backup cleanup failed', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Get backup info
     *
     * @param string $backup_id Backup ID
     * @return array|null Backup info
     */
    private function get_backup_info($backup_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE backup_id = %s OR filename = %s",
            $backup_id,
            $backup_id
        ), ARRAY_A);
    }

    /**
     * Save backup info to database
     *
     * @param array $backup_info Backup information
     */
    private function save_backup_info($backup_info)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

        $wpdb->insert($table_name, $backup_info);
    }

    /**
     * Update backup restore info
     *
     * @param int $backup_id Backup ID
     * @param array $update_data Update data
     */
    private function update_backup_restore_info($backup_id, $update_data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_backups';

        $wpdb->update(
            $table_name,
            $update_data,
            ['backup_id' => $backup_id],
            null,
            ['%s']
        );
    }

    /**
     * Validate backup data
     *
     * @param array $backup_data Backup data
     * @throws Settings_Exception
     */
    private function validate_backup_data($backup_data)
    {
        if (!is_array($backup_data)) {
            throw new Settings_Exception(
                'Backup data must be an array',
                'SETTINGS_001'
            );
        }

        if (!isset($backup_data['settings'])) {
            throw new Settings_Exception(
                'Backup data missing settings',
                'SETTINGS_001'
            );
        }

        if (!isset($backup_data['timestamp'])) {
            throw new Settings_Exception(
                'Backup data missing timestamp',
                'SETTINGS_001'
            );
        }
    }

    /**
     * Ensure backup directory exists
     */
    private function ensure_backup_directory()
    {
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }

        // Create .htaccess to prevent direct access
        $htaccess_file = $this->backup_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Deny from all\n");
        }

        // Create index.php to prevent directory listing
        $index_file = $this->backup_dir . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, "<?php\n// Silence is golden.\n");
        }
    }

    /**
     * Sanitize filename
     *
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    private function sanitize_filename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);
    }
}