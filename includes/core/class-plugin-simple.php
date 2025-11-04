<?php
/**
 * Simple Plugin Class for Initial Testing
 *
 * @package AI_Manager_Pro
 * @subpackage Core
 */

namespace AI_Manager_Pro\Core;

/**
 * Simple Plugin Class
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

        $this->init_hooks();
        $this->register_basic_services();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
    }

    /**
     * Register basic services
     */
    private function register_basic_services()
    {
        // Register logger
        $this->container->register('logger', function ($container) {
            if (class_exists('\Monolog\Logger')) {
                $logger = new \Monolog\Logger('ai-manager-pro');
                $handler = new \Monolog\Handler\StreamHandler(
                    WP_CONTENT_DIR . '/ai-manager-pro.log',
                    \Monolog\Logger::INFO
                );
                $logger->pushHandler($handler);
                return $logger;
            }
            return null;
        }, true);
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

        // Log plugin initialization
        if ($logger = $this->container->get('logger')) {
            $logger->info('AI Manager Pro initialized (simple mode)', [
                'version' => $this->version,
                'user_id' => get_current_user_id()
            ]);
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('AI Manager Pro', 'ai-website-manager-pro'),
            __('AI Manager Pro', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro',
            [$this, 'render_main_page'],
            'dashicons-robot',
            30
        );
    }

    /**
     * Render main page
     */
    public function render_main_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('AI Manager Pro', 'ai-website-manager-pro'); ?></h1>
            <div class="notice notice-success">
                <p><?php _e('Plugin activated successfully! This is a basic version for testing.', 'ai-website-manager-pro'); ?>
                </p>
            </div>
            <div class="card">
                <h2><?php _e('System Status', 'ai-website-manager-pro'); ?></h2>
                <ul>
                    <li><strong><?php _e('Plugin Version:', 'ai-website-manager-pro'); ?></strong>
                        <?php echo esc_html($this->version); ?></li>
                    <li><strong><?php _e('WordPress Version:', 'ai-website-manager-pro'); ?></strong>
                        <?php echo esc_html(get_bloginfo('version')); ?></li>
                    <li><strong><?php _e('PHP Version:', 'ai-website-manager-pro'); ?></strong>
                        <?php echo esc_html(PHP_VERSION); ?></li>
                    <li><strong><?php _e('Container Services:', 'ai-website-manager-pro'); ?></strong>
                        <?php echo count($this->container->get_services()); ?></li>
                </ul>
            </div>
            <div class="card">
                <h2><?php _e('Next Steps', 'ai-website-manager-pro'); ?></h2>
                <p><?php _e('The plugin is now active in basic mode. To enable full functionality, ensure all required files are present and properly configured.', 'ai-website-manager-pro'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices()
    {
        // Check for missing dependencies
        $missing_files = [];

        $required_files = [
            'includes/settings/class-settings-manager.php',
            'includes/admin/class-admin-manager.php',
            'includes/api/class-settings-api-controller.php'
        ];

        foreach ($required_files as $file) {
            if (!file_exists(AI_MANAGER_PRO_PLUGIN_DIR . $file)) {
                $missing_files[] = $file;
            }
        }

        if (!empty($missing_files)) {
            ?>
            <div class="notice notice-warning">
                <p><strong><?php _e('AI Manager Pro:', 'ai-website-manager-pro'); ?></strong>
                    <?php _e('Some files are missing. Plugin is running in basic mode.', 'ai-website-manager-pro'); ?></p>
                <ul>
                    <?php foreach ($missing_files as $file): ?>
                        <li><code><?php echo esc_html($file); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create basic options
        add_option('ai_manager_pro_version', $this->version);
        add_option('ai_manager_pro_activated', current_time('mysql'));

        // Log activation
        if ($logger = $this->container->get('logger')) {
            $logger->info('AI Manager Pro activated');
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Log deactivation
        if ($logger = $this->container->get('logger')) {
            $logger->info('AI Manager Pro deactivated');
        }
    }

    /**
     * Plugin uninstall
     */
    public function uninstall()
    {
        // Remove options
        delete_option('ai_manager_pro_version');
        delete_option('ai_manager_pro_activated');

        // Log uninstall
        if ($logger = $this->container->get('logger')) {
            $logger->info('AI Manager Pro uninstalled');
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