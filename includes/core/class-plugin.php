<?php
/**
 * Main Plugin Class
 *
 * @package AI_Manager_Pro
 * @subpackage Core
 */

namespace AI_Manager_Pro\Core;

use AI_Manager_Pro\Core\Container;
use AI_Manager_Pro\Modules\Security\Security_Manager;
use AI_Manager_Pro\Modules\AI_Providers\AI_Provider_Manager;
use AI_Manager_Pro\Modules\Content_Generation\Content_Generator;
use AI_Manager_Pro\Modules\Brand_Management\Brand_Manager;
use AI_Manager_Pro\Modules\Automation\Automation_Manager;
use AI_Manager_Pro\Modules\Analytics\Analytics_Manager;
use AI_Manager_Pro\Admin\Admin_Manager;
use AI_Manager_Pro\Admin\Permissions_Manager;
use AI_Manager_Pro\Settings\Settings_Manager;
use AI_Manager_Pro\Settings\Settings_Repository_Factory;
use AI_Manager_Pro\Settings\Encryption_Service;
use AI_Manager_Pro\Settings\Settings_Schema_Validator;
use AI_Manager_Pro\Settings\Settings_Backup;
use AI_Manager_Pro\Settings\Settings_Change_Log;
use AI_Manager_Pro\API\Settings_API_Controller;
use AI_Manager_Pro\API\API_Keys_Controller;
use AI_Manager_Pro\Database\Settings_Migration;

/**
 * Main Plugin Class
 */
class Plugin
{

    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Container instance
     *
     * @var Container
     */
    private $container;

    /**
     * Plugin version
     *
     * @var string
     */
    private $version;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->version = AI_MANAGER_PRO_VERSION;
        $this->container = Container::get_instance();

        $this->register_services();
        $this->init_hooks();
    }

    /**
     * Register services in the container
     */
    private function register_services()
    {
        // Register simple logger (fallback if Monolog not available)
        $this->container->register('logger', function ($container) {
            return new class {
                public function info($message, $context = [])
                {
                    error_log("AI Manager Pro: $message " . json_encode($context));
                }
                public function error($message, $context = [])
                {
                    error_log("AI Manager Pro ERROR: $message " . json_encode($context));
                }
            };
        }, true);

        // Register settings services first (they're needed by others)
        $this->container->register('settings_manager', function ($container) {
            if (class_exists('AI_Manager_Pro\\Settings\\Settings_Manager')) {
                return new Settings_Manager();
            }
            return null;
        }, true);

        $this->container->register('permissions', function ($container) {
            if (class_exists('AI_Manager_Pro\\Admin\\Permissions_Manager')) {
                return new Permissions_Manager();
            }
            return null;
        }, true);

        $this->container->register('settings_migration', function ($container) {
            if (class_exists('AI_Manager_Pro\\Database\\Settings_Migration')) {
                return new Settings_Migration();
            }
            return null;
        }, true);

        $this->container->register('settings_api', function ($container) {
            if (class_exists('AI_Manager_Pro\\API\\Settings_API_Controller')) {
                return new Settings_API_Controller();
            }
            return null;
        }, true);

        $this->container->register('api_keys_api', function ($container) {
            if (class_exists('AI_Manager_Pro\\API\\API_Keys_Controller')) {
                return new API_Keys_Controller();
            }
            return null;
        }, true);

        $this->container->register('settings_change_log', function ($container) {
            if (class_exists('AI_Manager_Pro\\Settings\\Settings_Change_Log')) {
                return new Settings_Change_Log($container->get('logger'));
            }
            return null;
        }, true);

        // Register module managers (with fallbacks)
        $this->container->register('security', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\Security\\Security_Manager')) {
                return new \AI_Manager_Pro\Modules\Security\Security_Manager($container->get('logger'));
            }
            return null;
        }, true);

        $this->container->register('ai_providers', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\AI_Providers\\AI_Provider_Manager')) {
                return new \AI_Manager_Pro\Modules\AI_Providers\AI_Provider_Manager($container->get('logger'), $container->get('security'));
            }
            return null;
        }, true);

        $this->container->register('brand_manager', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\Brand_Management\\Brand_Manager')) {
                return new \AI_Manager_Pro\Modules\Brand_Management\Brand_Manager($container->get('logger'), $container->get('security'));
            }
            return null;
        }, true);

        $this->container->register('content_generator', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\Content_Generation\\Content_Generator')) {
                return new \AI_Manager_Pro\Modules\Content_Generation\Content_Generator(
                    $container->get('ai_providers'),
                    $container->get('brand_manager'),
                    $container->get('logger')
                );
            }
            return null;
        }, true);

        $this->container->register('automation', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\Automation\\Automation_Manager')) {
                return new \AI_Manager_Pro\Modules\Automation\Automation_Manager(
                    $container->get('content_generator'),
                    $container->get('logger')
                );
            }
            return null;
        }, true);

        $this->container->register('analytics', function ($container) {
            if (class_exists('AI_Manager_Pro\\Modules\\Analytics\\Analytics_Manager')) {
                return new \AI_Manager_Pro\Modules\Analytics\Analytics_Manager($container->get('logger'));
            }
            return null;
        }, true);

        // Register admin manager (most important for menu)
        $this->container->register('admin', function ($container) {
            if (class_exists('AI_Manager_Pro\\Admin\\Admin_Manager')) {
                return new Admin_Manager($container);
            }
            return null;
        }, true);
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // AJAX hooks
        add_action('wp_ajax_ai_manager_pro_action', [$this, 'handle_ajax']);
        add_action('wp_ajax_nopriv_ai_manager_pro_action', [$this, 'handle_ajax']);

        // REST API hooks
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        // Load text domain
        load_plugin_textdomain(
            'ai-website-manager-pro',
            false,
            dirname(AI_MANAGER_PRO_PLUGIN_BASENAME) . '/languages'
        );

        // Run migrations if needed (safely)
        try {
            $migration = $this->container->get('settings_migration');
            if ($migration && method_exists($migration, 'is_migration_needed') && $migration->is_migration_needed()) {
                $migration->run_migrations();
            }
        } catch (Exception $e) {
            $this->container->get('logger')->error('Migration failed', ['error' => $e->getMessage()]);
        }

        // Initialize modules (safely)
        $modules = ['permissions', 'security', 'settings_manager', 'ai_providers', 'brand_manager', 'automation', 'analytics'];
        foreach ($modules as $module) {
            try {
                $this->container->get($module);
            } catch (Exception $e) {
                $this->container->get('logger')->error("Failed to initialize module: $module", ['error' => $e->getMessage()]);
            }
        }

        // Log plugin initialization
        $this->container->get('logger')->info('AI Manager Pro initialized', [
            'version' => $this->version,
            'user_id' => get_current_user_id()
        ]);
    }

    /**
     * Initialize admin
     */
    public function admin_init()
    {
        if (is_admin()) {
            try {
                $admin = $this->container->get('admin');
                if ($admin) {
                    $this->container->get('logger')->info('Admin manager initialized successfully');
                } else {
                    $this->container->get('logger')->error('Admin manager failed to initialize');
                }
            } catch (Exception $e) {
                $this->container->get('logger')->error('Admin initialization failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'ai-manager-pro-frontend',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'ai-manager-pro-frontend',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            $this->version
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook)
    {
        // Only load on plugin pages
        if (strpos($hook, 'ai-manager-pro') === false) {
            return;
        }

        wp_enqueue_script(
            'ai-manager-pro-admin',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-util'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'ai-manager-pro-admin',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        // Localize script
        wp_localize_script('ai-manager-pro-admin', 'aiManagerPro', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ai-manager-pro/v1/'),
            'nonce' => wp_create_nonce('ai_manager_pro_nonce'),
            'version' => $this->version
        ]);
    }

    /**
     * Handle AJAX requests
     */
    public function handle_ajax()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        $action = sanitize_text_field($_POST['action_type'] ?? '');

        try {
            switch ($action) {
                case 'generate_content':
                    $this->handle_generate_content();
                    break;
                case 'test_api_connection':
                    $this->handle_test_api_connection();
                    break;
                default:
                    wp_send_json_error('Invalid action');
            }
        } catch (\Exception $e) {
            $this->container->get('logger')->error('AJAX error', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json_error('An error occurred');
        }
    }

    /**
     * Handle content generation AJAX
     */
    private function handle_generate_content()
    {
        if (!current_user_can('ai_manager_generate_content')) {
            wp_send_json_error('Insufficient permissions');
        }

        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $audience = sanitize_text_field($_POST['audience'] ?? '');

        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }

        $content_generator = $this->container->get('content_generator');
        $result = $content_generator->generate_content([
            'topic' => $topic,
            'audience' => $audience
        ]);

        if ($result) {
            wp_send_json_success([
                'content' => $result,
                'message' => 'Content generated successfully'
            ]);
        } else {
            wp_send_json_error('Failed to generate content');
        }
    }

    /**
     * Handle API connection test
     */
    private function handle_test_api_connection()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $provider = sanitize_text_field($_POST['provider'] ?? '');

        if (empty($provider)) {
            wp_send_json_error('Provider is required');
        }

        $ai_providers = $this->container->get('ai_providers');
        $result = $ai_providers->test_connection($provider);

        wp_send_json_success([
            'connected' => $result,
            'message' => $result ? 'Connection successful' : 'Connection failed'
        ]);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes()
    {
        register_rest_route('ai-manager-pro/v1', '/status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_status'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);
    }

    /**
     * Get plugin status via REST API
     */
    public function get_status()
    {
        return rest_ensure_response([
            'version' => $this->version,
            'status' => 'active',
            'modules' => $this->container->get_services()
        ]);
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create database tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Create custom capabilities
        $this->create_capabilities();

        // Schedule cron jobs
        $this->schedule_cron_jobs();

        // Log activation
        $this->container->get('logger')->info('AI Manager Pro activated');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('ai_manager_pro_daily_task');
        wp_clear_scheduled_hook('ai_manager_pro_hourly_task');

        // Log deactivation
        $this->container->get('logger')->info('AI Manager Pro deactivated');
    }

    /**
     * Plugin uninstall
     */
    public function uninstall()
    {
        // Remove database tables
        $this->remove_tables();

        // Remove options
        $this->remove_options();

        // Remove capabilities
        $this->remove_capabilities();

        // Log uninstall
        $this->container->get('logger')->info('AI Manager Pro uninstalled');
    }

    /**
     * Create database tables
     */
    private function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Brands table
        $brands_table = $wpdb->prefix . 'ai_manager_pro_brands';
        $brands_sql = "CREATE TABLE IF NOT EXISTS {$brands_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            brand_data longtext NOT NULL,
            is_active boolean DEFAULT FALSE,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name),
            KEY is_active (is_active),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Logs table
        $logs_table = $wpdb->prefix . 'ai_manager_pro_logs';
        $logs_sql = "CREATE TABLE IF NOT EXISTS {$logs_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            module varchar(50),
            user_id bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY module (module),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Automation tasks table
        $tasks_table = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        $tasks_sql = "CREATE TABLE IF NOT EXISTS {$tasks_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            rules longtext NOT NULL,
            status varchar(20) DEFAULT 'active',
            last_run datetime,
            next_run datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY next_run (next_run),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($brands_sql);
        dbDelta($logs_sql);
        dbDelta($tasks_sql);
    }

    /**
     * Remove database tables
     */
    private function remove_tables()
    {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'ai_manager_pro_brands',
            $wpdb->prefix . 'ai_manager_pro_logs',
            $wpdb->prefix . 'ai_manager_pro_automation_tasks'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    /**
     * Set default options
     */
    private function set_default_options()
    {
        $default_options = [
            'ai_manager_pro_version' => $this->version,
            'ai_manager_pro_settings' => [
                'default_provider' => 'openai',
                'auto_publish' => false,
                'default_post_status' => 'draft',
                'enable_logging' => true,
                'log_level' => 'info'
            ]
        ];

        foreach ($default_options as $option_name => $option_value) {
            add_option($option_name, $option_value);
        }
    }

    /**
     * Remove options
     */
    private function remove_options()
    {
        $options = [
            'ai_manager_pro_version',
            'ai_manager_pro_settings',
            'ai_manager_pro_api_keys'
        ];

        foreach ($options as $option) {
            delete_option($option);
        }
    }

    /**
     * Create custom capabilities
     */
    private function create_capabilities()
    {
        $capabilities = [
            'ai_manager_manage_settings',
            'ai_manager_generate_content',
            'ai_manager_manage_brands',
            'ai_manager_view_logs',
            'ai_manager_manage_automation'
        ];

        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
    }

    /**
     * Remove custom capabilities
     */
    private function remove_capabilities()
    {
        $capabilities = [
            'ai_manager_manage_settings',
            'ai_manager_generate_content',
            'ai_manager_manage_brands',
            'ai_manager_view_logs',
            'ai_manager_manage_automation'
        ];

        $roles = ['administrator', 'editor', 'author'];
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * Schedule cron jobs
     */
    private function schedule_cron_jobs()
    {
        if (!wp_next_scheduled('ai_manager_pro_daily_task')) {
            wp_schedule_event(time(), 'daily', 'ai_manager_pro_daily_task');
        }

        if (!wp_next_scheduled('ai_manager_pro_hourly_task')) {
            wp_schedule_event(time(), 'hourly', 'ai_manager_pro_hourly_task');
        }
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function get_container()
    {
        return $this->container;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function get_version()
    {
        return $this->version;
    }
}

