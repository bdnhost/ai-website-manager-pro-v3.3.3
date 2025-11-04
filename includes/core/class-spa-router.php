<?php
/**
 * SPA Router System
 *
 * @package AI_Manager_Pro
 * @subpackage Core
 */

namespace AI_Manager_Pro\Core;

/**
 * SPA Router Class
 * 
 * Handles single-page application routing for admin interface
 */
class SPA_Router
{
    /**
     * Registered routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Current route
     *
     * @var string
     */
    private $current_route = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
        $this->register_default_routes();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('wp_ajax_ai_manager_load_page', [$this, 'handle_page_load']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_router_scripts']);
    }

    /**
     * Register default routes
     */
    private function register_default_routes()
    {
        $this->register_route('dashboard', [
            'title' => __('Dashboard', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_dashboard'],
            'capability' => 'ai_manager_view_dashboard'
        ]);

        $this->register_route('content-generator', [
            'title' => __('Content Generator', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_content_generator'],
            'capability' => 'ai_manager_generate_content'
        ]);

        $this->register_route('brands', [
            'title' => __('Brand Management', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_brands'],
            'capability' => 'ai_manager_manage_brands'
        ]);

        $this->register_route('settings', [
            'title' => __('Settings', 'ai-website-manager-pro'),
            'callback' => [$this, 'render_settings'],
            'capability' => 'ai_manager_manage_settings'
        ]);
    }

    /**
     * Register a new route
     *
     * @param string $route Route name
     * @param array $config Route configuration
     */
    public function register_route($route, $config)
    {
        $this->routes[$route] = wp_parse_args($config, [
            'title' => '',
            'callback' => null,
            'capability' => 'manage_options',
            'cache' => true,
            'preload' => false
        ]);
    }

    /**
     * Get registered routes
     *
     * @return array
     */
    public function get_routes()
    {
        return $this->routes;
    }

    /**
     * Handle AJAX page load
     */
    public function handle_page_load()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_spa_nonce')) {
            wp_die(__('Security check failed', 'ai-website-manager-pro'));
        }

        $route = sanitize_text_field($_POST['route'] ?? '');

        if (!isset($this->routes[$route])) {
            wp_send_json_error(__('Route not found', 'ai-website-manager-pro'));
        }

        $route_config = $this->routes[$route];

        // Check capabilities
        if (!current_user_can($route_config['capability'])) {
            wp_send_json_error(__('Insufficient permissions', 'ai-website-manager-pro'));
        }

        // Set current route
        $this->current_route = $route;

        // Start output buffering
        ob_start();

        try {
            // Call route callback
            if (is_callable($route_config['callback'])) {
                call_user_func($route_config['callback']);
            } else {
                throw new \Exception(__('Invalid route callback', 'ai-website-manager-pro'));
            }

            $content = ob_get_clean();

            wp_send_json_success([
                'content' => $content,
                'title' => $route_config['title'],
                'route' => $route
            ]);

        } catch (\Exception $e) {
            ob_end_clean();
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Enqueue router scripts
     */
    public function enqueue_router_scripts($hook)
    {
        // Only load on plugin pages
        if (strpos($hook, 'ai-manager-pro') === false) {
            return;
        }

        wp_enqueue_script(
            'ai-manager-pro-spa-router',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/spa-router.js',
            ['jquery'],
            AI_MANAGER_PRO_VERSION,
            true
        );

        wp_localize_script('ai-manager-pro-spa-router', 'aiManagerProSPA', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_manager_pro_spa_nonce'),
            'routes' => array_keys($this->routes),
            'currentRoute' => $this->get_current_route(),
            'strings' => [
                'loading' => __('Loading...', 'ai-website-manager-pro'),
                'error' => __('Failed to load page', 'ai-website-manager-pro'),
                'retry' => __('Retry', 'ai-website-manager-pro')
            ]
        ]);
    }

    /**
     * Get current route
     *
     * @return string
     */
    public function get_current_route()
    {
        if (empty($this->current_route)) {
            $this->current_route = $_GET['route'] ?? 'dashboard';
        }
        return $this->current_route;
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard()
    {
        include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
    }

    /**
     * Render content generator page
     */
    public function render_content_generator()
    {
        include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/content-generator.php';
    }

    /**
     * Render brands page
     */
    public function render_brands()
    {
        include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/brands.php';
    }

    /**
     * Render settings page
     */
    public function render_settings()
    {
        include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/settings.php';
    }

    /**
     * Generate navigation menu HTML
     *
     * @return string
     */
    public function generate_navigation()
    {
        $current_route = $this->get_current_route();
        $nav_html = '<nav class="ai-spa-navigation">';

        foreach ($this->routes as $route => $config) {
            if (!current_user_can($config['capability'])) {
                continue;
            }

            $active_class = ($route === $current_route) ? ' active' : '';
            $nav_html .= sprintf(
                '<a href="#" class="nav-item%s" data-route="%s">%s</a>',
                $active_class,
                esc_attr($route),
                esc_html($config['title'])
            );
        }

        $nav_html .= '</nav>';
        return $nav_html;
    }

    /**
     * Render SPA container
     */
    public function render_spa_container()
    {
        ?>
        <div class="wrap ai-spa-container">
            <h1 class="wp-heading-inline"><?php _e('AI Website Manager Pro', 'ai-website-manager-pro'); ?></h1>

            <?php echo $this->generate_navigation(); ?>

            <div class="ai-spa-content" id="ai-spa-content">
                <div class="ai-spa-loading" id="ai-spa-loading" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p><?php _e('Loading...', 'ai-website-manager-pro'); ?></p>
                </div>

                <div class="ai-spa-error" id="ai-spa-error" style="display: none;">
                    <div class="notice notice-error">
                        <p class="error-message"></p>
                        <button type="button" class="button retry-btn"><?php _e('Retry', 'ai-website-manager-pro'); ?></button>
                    </div>
                </div>

                <div class="ai-spa-page-content" id="ai-spa-page-content">
                    <!-- Page content will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
}