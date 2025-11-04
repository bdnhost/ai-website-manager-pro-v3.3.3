<?php
/**
 * Admin Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Admin
 */

namespace AI_Manager_Pro\Admin;

use AI_Manager_Pro\Core\Container;
use AI_Manager_Pro\Core\SPA_Router;
use AI_Manager_Pro\Settings\Settings_Manager;

/**
 * Admin Manager Class
 * 
 * Manages WordPress admin interface for the plugin
 */
class Admin_Manager
{

    /**
     * Container instance
     *
     * @var Container
     */
    private $container;

    /**
     * Settings manager
     *
     * @var Settings_Manager
     */
    private $settings_manager;

    /**
     * Admin pages
     *
     * @var array
     */
    private $pages = [];

    /**
     * SPA Router instance
     *
     * @var SPA_Router
     */
    private $spa_router;

    /**
     * Constructor
     *
     * @param Container $container Container instance
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->settings_manager = $container->get('settings_manager');
        $this->spa_router = new SPA_Router();

        $this->init_hooks();
        $this->register_pages();
        $this->register_spa_routes();

        // Log successful initialization
        error_log('AI Manager Pro: Admin Manager initialized successfully');
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_notices', [$this, 'show_admin_notices']);

        // AJAX hooks
        add_action('wp_ajax_ai_manager_pro_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_ai_manager_pro_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_ai_manager_pro_backup_settings', [$this, 'handle_backup_settings']);
        add_action('wp_ajax_ai_manager_pro_restore_settings', [$this, 'handle_restore_settings']);
    }

    /**
     * Register admin pages
     */
    private function register_pages()
    {
        $this->pages = [
            'general' => [
                'title' => __('General Settings', 'ai-website-manager-pro'),
                'capability' => 'ai_manager_manage_settings',
                'callback' => [$this, 'render_general_settings_page']
            ],
            'api-keys' => [
                'title' => __('API Keys', 'ai-website-manager-pro'),
                'capability' => 'ai_manager_manage_settings',
                'callback' => [$this, 'render_api_keys_page']
            ],
            'brands' => [
                'title' => __('Brand Management', 'ai-website-manager-pro'),
                'capability' => 'ai_manager_manage_brands',
                'callback' => [$this, 'render_brands_page']
            ],
            'automation' => [
                'title' => __('Automation', 'ai-website-manager-pro'),
                'capability' => 'ai_manager_manage_automation',
                'callback' => [$this, 'render_automation_page']
            ],
            'logs' => [
                'title' => __('Logs & History', 'ai-website-manager-pro'),
                'capability' => 'ai_manager_view_logs',
                'callback' => [$this, 'render_logs_page']
            ]
        ];
    }

    /**
     * Register SPA routes
     */
    private function register_spa_routes()
    {
        // Register routes with SPA Router
        foreach ($this->pages as $route => $config) {
            $this->spa_router->register_route($route, [
                'title' => $config['title'],
                'callback' => $config['callback'],
                'capability' => $config['capability']
            ]);
        }

        // Add dashboard route
        $this->spa_router->register_route('dashboard', [
            'title' => __('Dashboard', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_dashboard_page'],
            'capability' => 'ai_manager_view_dashboard'
        ]);

        // Add content generator route
        $this->spa_router->register_route('content-generator', [
            'title' => __('Content Generator', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_content_generator_page'],
            'capability' => 'ai_manager_generate_content'
        ]);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        // Use manage_options as fallback capability
        $main_capability = current_user_can('ai_manager_manage_settings') ? 'ai_manager_manage_settings' : 'manage_options';

        // Main menu page
        add_menu_page(
            __('AI Manager Pro', 'ai-website-manager-pro'),
            __('AI Manager Pro', 'ai-website-manager-pro'),
            $main_capability,
            'ai-manager-pro',
            [$this, 'render_main_page'],
            'dashicons-robot',
            30
        );

        // Submenu pages
        foreach ($this->pages as $slug => $page) {
            // Use manage_options as fallback if custom capability doesn't exist
            $capability = current_user_can($page['capability']) ? $page['capability'] : 'manage_options';

            add_submenu_page(
                'ai-manager-pro',
                $page['title'],
                $page['title'],
                $capability,
                'ai-manager-pro-' . $slug,
                $page['callback']
            );
        }

        // Remove duplicate main page from submenu
        remove_submenu_page('ai-manager-pro', 'ai-manager-pro');

        // Log menu creation
        error_log('AI Manager Pro: Admin menu created with ' . count($this->pages) . ' subpages');
    }

    /**
     * Admin initialization
     */
    public function admin_init()
    {
        // Register settings
        $this->register_settings();

        // Add settings sections and fields
        $this->add_settings_sections();
    }

    /**
     * Register settings
     */
    private function register_settings()
    {
        register_setting('ai_manager_pro_general', 'ai_manager_pro_general_settings', [
            'sanitize_callback' => [$this, 'sanitize_general_settings']
        ]);

        register_setting('ai_manager_pro_api_keys', 'ai_manager_pro_api_keys_settings', [
            'sanitize_callback' => [$this, 'sanitize_api_keys_settings']
        ]);
    }

    /**
     * Add settings sections
     */
    private function add_settings_sections()
    {
        // General settings section
        add_settings_section(
            'ai_manager_pro_general_section',
            __('General Configuration', 'ai-website-manager-pro'),
            [$this, 'render_general_section_description'],
            'ai_manager_pro_general'
        );

        // API keys section
        add_settings_section(
            'ai_manager_pro_api_keys_section',
            __('API Configuration', 'ai-website-manager-pro'),
            [$this, 'render_api_keys_section_description'],
            'ai_manager_pro_api_keys'
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only load on plugin pages
        if (strpos($hook, 'ai-manager-pro') === false) {
            return;
        }

        // Enqueue scripts
        wp_enqueue_script(
            'ai-manager-pro-admin',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-util'],
            AI_MANAGER_PRO_VERSION,
            true
        );

        // Enqueue advanced JSON editor
        wp_enqueue_script(
            'ai-manager-pro-json-editor',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/json-editor-advanced.js',
            ['jquery'],
            AI_MANAGER_PRO_VERSION,
            true
        );

        // Enqueue SPA Router
        wp_enqueue_script(
            'ai-manager-pro-spa-router',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/spa-router.js',
            ['jquery'],
            AI_MANAGER_PRO_VERSION,
            true
        );

        // Enqueue styles
        wp_enqueue_style(
            'ai-manager-pro-admin',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/admin.css',
            [],
            AI_MANAGER_PRO_VERSION
        );

        // Enqueue JSON editor styles
        wp_enqueue_style(
            'ai-manager-pro-json-editor',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/json-editor-advanced.css',
            [],
            AI_MANAGER_PRO_VERSION
        );

        // Enqueue SPA Router styles
        wp_enqueue_style(
            'ai-manager-pro-spa-router',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/spa-router.css',
            [],
            AI_MANAGER_PRO_VERSION
        );

        // Localize script
        wp_localize_script('ai-manager-pro-admin', 'aiManagerProAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_manager_pro_admin_nonce'),
            'strings' => [
                'saving' => __('Saving...', 'ai-website-manager-pro'),
                'saved' => __('Settings saved successfully!', 'ai-website-manager-pro'),
                'error' => __('An error occurred. Please try again.', 'ai-website-manager-pro'),
                'testing' => __('Testing connection...', 'ai-website-manager-pro'),
                'connected' => __('Connection successful!', 'ai-website-manager-pro'),
                'connection_failed' => __('Connection failed!', 'ai-website-manager-pro'),
                'confirm_restore' => __('Are you sure you want to restore these settings? This will overwrite your current configuration.', 'ai-website-manager-pro')
            ]
        ]);

        // Enqueue CodeMirror for JSON editing
        wp_enqueue_code_editor(['type' => 'application/json']);
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices()
    {
        // Check if we're on a plugin page
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'ai-manager-pro') === false) {
            return;
        }

        // Show success message after settings save
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e('Settings saved successfully!', 'ai-website-manager-pro'); ?>
                </p>
            </div>
            <?php
        }

        // Show API key warnings
        $this->show_api_key_warnings();
    }

    /**
     * Show API key warnings
     */
    private function show_api_key_warnings()
    {
        $api_keys = $this->settings_manager->get_api_keys();
        $missing_keys = [];

        $providers = ['openai', 'claude', 'openrouter'];
        foreach ($providers as $provider) {
            if (empty($api_keys[$provider]['api_key'])) {
                $missing_keys[] = ucfirst($provider);
            }
        }

        if (!empty($missing_keys)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(
                        __('Missing API keys for: %s. <a href="%s">Configure them now</a> to enable AI content generation.', 'ai-website-manager-pro'),
                        implode(', ', $missing_keys),
                        admin_url('admin.php?page=ai-manager-pro-api-keys')
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Render main page
     */
    public function render_main_page()
    {
        // Redirect to general settings
        wp_redirect(admin_url('admin.php?page=ai-manager-pro-general'));
        exit;
    }

    /**
     * Render general settings page
     */
    public function render_general_settings_page()
    {
        $settings = [];
        if ($this->settings_manager) {
            $settings = $this->settings_manager->get_general_settings();
        }

        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings-general.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>General Settings</h1><p>Settings view not found.</p></div>';
        }
    }

    /**
     * Render API keys page
     */
    public function render_api_keys_page()
    {
        $api_keys = [];
        if ($this->settings_manager) {
            $api_keys = $this->settings_manager->get_api_keys();
        }

        // Mock providers data if needed
        $providers = [
            ['id' => 'openai', 'name' => 'OpenAI', 'description' => 'GPT models'],
            ['id' => 'anthropic', 'name' => 'Anthropic', 'description' => 'Claude models'],
            ['id' => 'openrouter', 'name' => 'OpenRouter', 'description' => 'Multiple models']
        ];

        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings-api-keys.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>API Keys</h1><p>API Keys view not found.</p></div>';
        }
    }

    /**
     * Render brands page
     */
    public function render_brands_page()
    {
        $brands = [];
        $active_brand = null;

        $brand_manager = $this->container->get('brand_manager');
        if ($brand_manager) {
            $brands = $brand_manager->get_brands();
            $active_brand = $brand_manager->get_active_brand();
        }

        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings-brands.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Brand Management</h1><p>Brands view not found.</p></div>';
        }
    }

    /**
     * Render automation page
     */
    public function render_automation_page()
    {
        $automation_manager = $this->container->get('automation');

        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings-automation.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Automation</h1><p>Automation view not found.</p></div>';
        }
    }

    /**
     * Render logs page
     */
    public function render_logs_page()
    {
        $recent_changes = [];
        $statistics = [];

        $change_log = $this->container->get('settings_change_log');
        if ($change_log) {
            $recent_changes = $change_log->get_recent_changes(50);
            $statistics = $change_log->get_statistics();
        }

        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings-logs.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Logs & History</h1><p>Logs view not found.</p></div>';
        }
    }

    /**
     * Render section descriptions
     */
    public function render_general_section_description()
    {
        echo '<p>' . __('Configure general plugin settings and behavior.', 'ai-website-manager-pro') . '</p>';
    }

    public function render_api_keys_section_description()
    {
        echo '<p>' . __('Configure API keys for AI providers. All keys are encrypted before storage.', 'ai-website-manager-pro') . '</p>';
    }

    /**
     * AJAX Handlers
     */

    /**
     * Handle save settings AJAX request
     */
    public function handle_save_settings()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('ai_manager_manage_settings')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $category = sanitize_text_field($_POST['category'] ?? '');
            $settings = $_POST['settings'] ?? [];

            if (empty($category)) {
                wp_send_json_error('Category is required');
            }

            // Sanitize settings based on category
            $sanitized_settings = $this->sanitize_settings($category, $settings);

            // Save settings
            $result = $this->settings_manager->set_category($category, $sanitized_settings);

            if ($result) {
                wp_send_json_success([
                    'message' => __('Settings saved successfully!', 'ai-website-manager-pro')
                ]);
            } else {
                wp_send_json_error('Failed to save settings');
            }

        } catch (\Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('ai_manager_manage_settings')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $provider = sanitize_text_field($_POST['provider'] ?? '');

            if (empty($provider)) {
                wp_send_json_error('Provider is required');
            }

            $result = $this->settings_manager->test_api_connection($provider);

            wp_send_json_success([
                'connected' => $result,
                'message' => $result ?
                    __('Connection successful!', 'ai-website-manager-pro') :
                    __('Connection failed!', 'ai-website-manager-pro')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle backup settings AJAX request
     */
    public function handle_backup_settings()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('ai_manager_manage_settings')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $backup_service = $this->container->get('settings_backup');
            $backup_name = sanitize_text_field($_POST['name'] ?? '');

            $backup_info = $backup_service->create_backup($backup_name);

            wp_send_json_success([
                'backup_info' => $backup_info,
                'message' => __('Backup created successfully!', 'ai-website-manager-pro')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle restore settings AJAX request
     */
    public function handle_restore_settings()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('ai_manager_manage_settings')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $backup_service = $this->container->get('settings_backup');
            $backup_id = sanitize_text_field($_POST['backup_id'] ?? '');

            if (empty($backup_id)) {
                wp_send_json_error('Backup ID is required');
            }

            $result = $backup_service->restore_backup($backup_id);

            if ($result) {
                wp_send_json_success([
                    'message' => __('Settings restored successfully!', 'ai-website-manager-pro')
                ]);
            } else {
                wp_send_json_error('Failed to restore settings');
            }

        } catch (\Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize settings based on category
     *
     * @param string $category Settings category
     * @param array $settings Settings to sanitize
     * @return array Sanitized settings
     */
    private function sanitize_settings($category, $settings)
    {
        // This would use the schema validator to sanitize settings
        // For now, basic sanitization
        $sanitized = [];

        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_bool($value)) {
                $sanitized[$key] = (bool) $value;
            } elseif (is_numeric($value)) {
                $sanitized[$key] = is_float($value) ? (float) $value : (int) $value;
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize general settings
     *
     * @param array $settings Settings to sanitize
     * @return array Sanitized settings
     */
    public function sanitize_general_settings($settings)
    {
        return $this->sanitize_settings('general', $settings);
    }

    /**
     * Sanitize API keys settings
     *
     * @param array $settings Settings to sanitize
     * @return array Sanitized settings
     */
    public function sanitize_api_keys_settings($settings)
    {
        return $this->sanitize_settings('api_keys', $settings);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page()
    {
        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Dashboard</h1><p>Dashboard view not found.</p></div>';
        }
    }

    /**
     * Render content generator page
     */
    public function render_content_generator_page()
    {
        $view_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/content-generator.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Content Generator</h1><p>Content generator view not found.</p></div>';
        }
    }

    /**
     * Get SPA Router instance
     */
    public function get_spa_router()
    {
        return $this->spa_router;
    }

    /**
     * Render main SPA page
     */
    public function render_spa_main_page()
    {
        // Check if we should use SPA mode
        $use_spa = get_option('ai_manager_pro_use_spa', true);

        if ($use_spa) {
            $this->spa_router->render_spa_container();
        } else {
            // Fallback to traditional page rendering
            $current_page = $_GET['page'] ?? 'ai-manager-pro';
            $route = str_replace('ai-manager-pro-', '', $current_page);

            if (isset($this->pages[$route])) {
                call_user_func($this->pages[$route]['callback']);
            } else {
                $this->render_dashboard_page();
            }
        }
    }
}