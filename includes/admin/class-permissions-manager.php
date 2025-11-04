<?php
/**
 * Permissions Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Admin
 */

namespace AI_Manager_Pro\Admin;

use Monolog\Logger;

/**
 * Permissions Manager Class
 * 
 * Manages user permissions and capabilities for the plugin
 */
class Permissions_Manager
{

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Plugin capabilities
     *
     * @var array
     */
    private $capabilities = [
        'ai_manager_manage_settings' => [
            'label' => 'Manage AI Manager Settings',
            'description' => 'Allow user to view and modify plugin settings',
            'default_roles' => ['administrator']
        ],
        'ai_manager_generate_content' => [
            'label' => 'Generate AI Content',
            'description' => 'Allow user to generate content using AI',
            'default_roles' => ['administrator', 'editor']
        ],
        'ai_manager_manage_brands' => [
            'label' => 'Manage Brands',
            'description' => 'Allow user to create and manage brand profiles',
            'default_roles' => ['administrator', 'editor']
        ],
        'ai_manager_view_logs' => [
            'label' => 'View Logs',
            'description' => 'Allow user to view plugin logs and history',
            'default_roles' => ['administrator']
        ],
        'ai_manager_manage_automation' => [
            'label' => 'Manage Automation',
            'description' => 'Allow user to create and manage automation tasks',
            'default_roles' => ['administrator']
        ],
        'ai_manager_backup_restore' => [
            'label' => 'Backup & Restore',
            'description' => 'Allow user to backup and restore settings',
            'default_roles' => ['administrator']
        ],
        'ai_manager_api_access' => [
            'label' => 'API Access',
            'description' => 'Allow user to access plugin REST API',
            'default_roles' => ['administrator']
        ]
    ];

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'register_capabilities']);
        add_action('admin_init', [$this, 'maybe_update_capabilities']);
        add_filter('user_has_cap', [$this, 'filter_user_capabilities'], 10, 4);
        add_action('wp_ajax_ai_manager_pro_update_permissions', [$this, 'handle_update_permissions']);
    }

    /**
     * Register plugin capabilities
     */
    public function register_capabilities()
    {
        // Only run on plugin activation or version update
        $current_version = get_option('ai_manager_pro_capabilities_version');

        if ($current_version !== AI_MANAGER_PRO_VERSION) {
            $this->add_capabilities_to_roles();
            update_option('ai_manager_pro_capabilities_version', AI_MANAGER_PRO_VERSION);

            $this->logger->info('Plugin capabilities registered', [
                'version' => AI_MANAGER_PRO_VERSION,
                'capabilities' => array_keys($this->capabilities)
            ]);
        }
    }

    /**
     * Add capabilities to default roles
     */
    private function add_capabilities_to_roles()
    {
        foreach ($this->capabilities as $capability => $config) {
            foreach ($config['default_roles'] as $role_name) {
                $role = get_role($role_name);

                if ($role && !$role->has_cap($capability)) {
                    $role->add_cap($capability);

                    $this->logger->debug('Added capability to role', [
                        'capability' => $capability,
                        'role' => $role_name
                    ]);
                }
            }
        }
    }

    /**
     * Maybe update capabilities on plugin update
     */
    public function maybe_update_capabilities()
    {
        $stored_capabilities = get_option('ai_manager_pro_custom_capabilities', []);

        // Apply custom capability assignments
        foreach ($stored_capabilities as $user_id => $user_capabilities) {
            $user = get_user_by('ID', $user_id);

            if ($user) {
                foreach ($user_capabilities as $capability => $granted) {
                    if ($granted && !user_can($user_id, $capability)) {
                        $user->add_cap($capability);
                    } elseif (!$granted && user_can($user_id, $capability)) {
                        $user->remove_cap($capability);
                    }
                }
            }
        }
    }

    /**
     * Filter user capabilities
     *
     * @param array $allcaps All capabilities
     * @param array $caps Required capabilities
     * @param array $args Arguments
     * @param WP_User $user User object
     * @return array Filtered capabilities
     */
    public function filter_user_capabilities($allcaps, $caps, $args, $user)
    {
        // Log capability checks for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            foreach ($caps as $cap) {
                if (strpos($cap, 'ai_manager_') === 0) {
                    $this->logger->debug('Capability check', [
                        'user_id' => $user->ID,
                        'capability' => $cap,
                        'granted' => isset($allcaps[$cap]) && $allcaps[$cap]
                    ]);
                }
            }
        }

        return $allcaps;
    }

    /**
     * Check if user has plugin capability
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool Whether user has capability
     */
    public function user_can($user_id, $capability)
    {
        $can = user_can($user_id, $capability);

        // Log security-sensitive capability checks
        if (in_array($capability, ['ai_manager_manage_settings', 'ai_manager_backup_restore'])) {
            $this->logger->info('Security capability check', [
                'user_id' => $user_id,
                'capability' => $capability,
                'granted' => $can,
                'ip' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }

        return $can;
    }

    /**
     * Get all plugin capabilities
     *
     * @return array Plugin capabilities
     */
    public function get_capabilities()
    {
        return $this->capabilities;
    }

    /**
     * Get capability configuration
     *
     * @param string $capability Capability name
     * @return array|null Capability configuration
     */
    public function get_capability_config($capability)
    {
        return $this->capabilities[$capability] ?? null;
    }

    /**
     * Get users with specific capability
     *
     * @param string $capability Capability name
     * @return array Users with capability
     */
    public function get_users_with_capability($capability)
    {
        $users = get_users([
            'capability' => $capability,
            'fields' => ['ID', 'display_name', 'user_email']
        ]);

        return $users;
    }

    /**
     * Grant capability to user
     *
     * @param int $user_id User ID
     * @param string $capability Capability name
     * @return bool Success status
     */
    public function grant_capability($user_id, $capability)
    {
        if (!isset($this->capabilities[$capability])) {
            return false;
        }

        $user = get_user_by('ID', $user_id);

        if (!$user) {
            return false;
        }

        $user->add_cap($capability);

        // Store custom capability assignment
        $custom_capabilities = get_option('ai_manager_pro_custom_capabilities', []);
        $custom_capabilities[$user_id][$capability] = true;
        update_option('ai_manager_pro_custom_capabilities', $custom_capabilities);

        $this->logger->info('Capability granted to user', [
            'user_id' => $user_id,
            'capability' => $capability,
            'granted_by' => get_current_user_id()
        ]);

        return true;
    }

    /**
     * Revoke capability from user
     *
     * @param int $user_id User ID
     * @param string $capability Capability name
     * @return bool Success status
     */
    public function revoke_capability($user_id, $capability)
    {
        if (!isset($this->capabilities[$capability])) {
            return false;
        }

        $user = get_user_by('ID', $user_id);

        if (!$user) {
            return false;
        }

        $user->remove_cap($capability);

        // Store custom capability assignment
        $custom_capabilities = get_option('ai_manager_pro_custom_capabilities', []);
        $custom_capabilities[$user_id][$capability] = false;
        update_option('ai_manager_pro_custom_capabilities', $custom_capabilities);

        $this->logger->info('Capability revoked from user', [
            'user_id' => $user_id,
            'capability' => $capability,
            'revoked_by' => get_current_user_id()
        ]);

        return true;
    }

    /**
     * Get user capabilities matrix
     *
     * @return array User capabilities matrix
     */
    public function get_user_capabilities_matrix()
    {
        $users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
        $matrix = [];

        foreach ($users as $user) {
            $user_caps = [];

            foreach ($this->capabilities as $capability => $config) {
                $user_caps[$capability] = user_can($user->ID, $capability);
            }

            $matrix[$user->ID] = [
                'user' => $user,
                'capabilities' => $user_caps
            ];
        }

        return $matrix;
    }

    /**
     * Handle update permissions AJAX request
     */
    public function handle_update_permissions()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $user_id = intval($_POST['user_id'] ?? 0);
            $capability = sanitize_text_field($_POST['capability'] ?? '');
            $grant = (bool) ($_POST['grant'] ?? false);

            if (!$user_id || !$capability) {
                wp_send_json_error('Invalid parameters');
            }

            if ($grant) {
                $result = $this->grant_capability($user_id, $capability);
            } else {
                $result = $this->revoke_capability($user_id, $capability);
            }

            if ($result) {
                wp_send_json_success([
                    'message' => $grant ?
                        __('Capability granted successfully', 'ai-website-manager-pro') :
                        __('Capability revoked successfully', 'ai-website-manager-pro')
                ]);
            } else {
                wp_send_json_error('Failed to update capability');
            }

        } catch (\Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove all plugin capabilities
     */
    public function remove_capabilities()
    {
        $roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];

        foreach ($roles as $role_name) {
            $role = get_role($role_name);

            if ($role) {
                foreach ($this->capabilities as $capability => $config) {
                    $role->remove_cap($capability);
                }
            }
        }

        // Remove custom capability assignments
        delete_option('ai_manager_pro_custom_capabilities');
        delete_option('ai_manager_pro_capabilities_version');

        $this->logger->info('All plugin capabilities removed');
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
     * Export capabilities configuration
     *
     * @return array Capabilities configuration
     */
    public function export_capabilities()
    {
        return [
            'capabilities' => $this->capabilities,
            'custom_assignments' => get_option('ai_manager_pro_custom_capabilities', []),
            'version' => AI_MANAGER_PRO_VERSION,
            'exported_at' => current_time('mysql'),
            'exported_by' => get_current_user_id()
        ];
    }

    /**
     * Import capabilities configuration
     *
     * @param array $config Capabilities configuration
     * @return bool Success status
     */
    public function import_capabilities($config)
    {
        if (!is_array($config) || !isset($config['capabilities'])) {
            return false;
        }

        try {
            // Apply custom assignments
            if (isset($config['custom_assignments'])) {
                update_option('ai_manager_pro_custom_capabilities', $config['custom_assignments']);
                $this->maybe_update_capabilities();
            }

            $this->logger->info('Capabilities configuration imported', [
                'version' => $config['version'] ?? 'unknown',
                'imported_by' => get_current_user_id()
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to import capabilities configuration', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}