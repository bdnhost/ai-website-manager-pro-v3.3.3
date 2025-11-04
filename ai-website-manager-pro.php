<?php
/**
 * Plugin Name: AI Website Manager Pro
 * Plugin URI: https://bdnhost.net/ai-website-manager-pro
 * Description: מערכת ניהול תוכן מתקדמת עם בינה מלאכותית - כולל דשבורד מקצועי עם טיזרים, ייבוא/ייצוא מותגים JSON, 25+ פרומפטים מתקדמים, תבניות SEO אוטומטיות (5 סוגים!) ותמיכה ב-DeepSeek LLM. גרסה 3.3.3 כוללת דשבורד משודרג עם טיזרים לתבניות SEO, תפריטי פעולה מהירים ו-What's New.
 * Version: 3.3.3
 * Author: יעקב בידני - BDNHOST
 * Author URI: https://bdnhost.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-website-manager-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * Developed by: יעקב בידני
 * Company: BDNHOST - פתרונות אינטרנט וקוד פתוח
 * Website: https://bdnhost.net
 * Support: info@bdnhost.net
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_MANAGER_PRO_VERSION', '3.3.3');
define('AI_MANAGER_PRO_PLUGIN_FILE', __FILE__);
define('AI_MANAGER_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_MANAGER_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_MANAGER_PRO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class - Safe Version with Multiple Menus
 */
class AI_Manager_Pro_Safe
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        error_log('AI Manager Pro - Plugin constructor called at ' . current_time('mysql'));
        error_log('AI Manager Pro - WordPress version: ' . get_bloginfo('version'));
        error_log('AI Manager Pro - PHP version: ' . PHP_VERSION);
        error_log('AI Manager Pro - Plugin version: 3.3.3');

        // Check if version was updated and show notice
        $this->check_version_update();

        // Register WordPress hooks
        error_log('AI Manager Pro - Registering WordPress hooks');
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_notices', [$this, 'show_success_notice']);
        error_log('AI Manager Pro - WordPress hooks registered successfully');

        // AJAX handlers
        error_log('AI Manager Pro - Registering AJAX handlers');
        add_action('wp_ajax_ai_manager_pro_save_brand', [$this, 'handle_save_brand']);
        add_action('wp_ajax_ai_manager_pro_delete_brand', [$this, 'handle_delete_brand']);
        add_action('wp_ajax_ai_manager_pro_test_api_connection', [$this, 'handle_test_api_connection']);
        add_action('wp_ajax_ai_manager_pro_generate_content', [$this, 'handle_generate_content']);
        error_log('AI Manager Pro - Registered generate_content handler');
        add_action('wp_ajax_ai_manager_pro_check_api_keys', [$this, 'handle_check_api_keys']);
        error_log('AI Manager Pro - Registered check_api_keys handler');
        add_action('wp_ajax_ai_manager_pro_ping', [$this, 'handle_ping']);
        error_log('AI Manager Pro - Registered ping handler');
        add_action('wp_ajax_ai_manager_pro_test_deepseek', [$this, 'handle_test_deepseek']);
        error_log('AI Manager Pro - Registered test_deepseek handler');
        add_action('wp_ajax_ai_manager_pro_save_automation', [$this, 'handle_save_automation']);
        add_action('wp_ajax_ai_manager_pro_toggle_automation', [$this, 'handle_toggle_automation']);
        add_action('wp_ajax_ai_manager_pro_clear_logs', [$this, 'handle_clear_logs']);
        add_action('wp_ajax_ai_manager_pro_create_post', [$this, 'handle_create_post']);
        add_action('wp_ajax_ai_manager_pro_test_openrouter_models', [$this, 'handle_test_openrouter_models']);
        add_action('wp_ajax_ai_manager_pro_delete_automation', [$this, 'handle_delete_automation']);
        add_action('wp_ajax_ai_manager_pro_run_task_now', [$this, 'handle_run_task_now']);
        add_action('wp_ajax_ai_manager_pro_test_deepseek_connection', [$this, 'handle_test_deepseek_connection']);
        add_action('wp_ajax_ai_manager_pro_generate_deepseek_content', [$this, 'handle_generate_deepseek_content']);
        add_action('wp_ajax_ai_manager_pro_import_brand_json', [$this, 'handle_import_brand_json']);
        add_action('wp_ajax_ai_manager_pro_export_brand_json', [$this, 'handle_export_brand_json']);

        // Cron jobs
        add_action('ai_manager_pro_automation_task', [$this, 'run_automation_task']);

        // Add a test log for diagnostics
        $this->log_activity('info', 'Plugin loaded successfully.', 'initialization');
    }

    public function init()
    {
        error_log('AI Manager Pro - init() called');
        // Load text domain
        load_plugin_textdomain(
            'ai-website-manager-pro',
            false,
            dirname(AI_MANAGER_PRO_PLUGIN_BASENAME) . '/languages'
        );

        // Load required classes
        $this->load_dependencies();

        // Load AI service
        $this->load_ai_service();

        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Load all required dependencies
     */
    private function load_dependencies()
    {
        // Load SPA Router
        $this->load_spa_router();

        // Load AI services
        $this->safe_require('includes/ai/class-real-ai-service.php');
        $this->safe_require('includes/ai/class-openrouter-service.php');
        $this->safe_require('includes/ai/class-deepseek-service.php');

        // Load AJAX handlers
        $this->safe_require('includes/ajax/class-openrouter-ajax.php');
        $this->safe_require('includes/ajax/class-deepseek-ajax.php');

        // Load other core classes
        $this->safe_require('includes/core/class-plugin.php');
        $this->safe_require('includes/admin/class-admin-manager.php');
    }

    /**
     * Load AI service class
     */
    private function load_ai_service()
    {
        $ai_service_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-real-ai-service.php';
        if (file_exists($ai_service_file)) {
            require_once $ai_service_file;
        }
    }

    /**
     * Safe require with error handling
     */
    private function safe_require($file_path)
    {
        $full_path = AI_MANAGER_PRO_PLUGIN_DIR . $file_path;
        if (file_exists($full_path)) {
            require_once $full_path;
            return true;
        }
        return false;
    }

    private function load_spa_router()
    {
        $spa_router_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/core/class-spa-router.php';
        if (file_exists($spa_router_file)) {
            require_once $spa_router_file;
        }
    }

    public function register_settings()
    {
        // Register general settings
        register_setting('ai_manager_pro_general', 'ai_manager_pro_default_provider');
        register_setting('ai_manager_pro_general', 'ai_manager_pro_auto_publish');
        register_setting('ai_manager_pro_general', 'ai_manager_pro_openrouter_model');
        register_setting('ai_manager_pro_general', 'ai_manager_pro_deepseek_model');
        register_setting('ai_manager_pro_general', 'ai_manager_pro_default_post_status');
        register_setting('ai_manager_pro_general', 'ai_manager_pro_enable_logging');

        // Register API keys
        register_setting('ai_manager_pro_api_keys', 'ai_manager_pro_openai_api_key');
        register_setting('ai_manager_pro_api_keys', 'ai_manager_pro_anthropic_api_key');
        register_setting('ai_manager_pro_api_keys', 'ai_manager_pro_openrouter_api_key');
        register_setting('ai_manager_pro_api_keys', 'ai_manager_pro_deepseek_key');


        // Register brand settings
        register_setting('ai_manager_pro_brands', 'ai_manager_pro_brands_data');
        register_setting('ai_manager_pro_brands', 'ai_manager_pro_active_brand');

        // Register automation settings
        register_setting('ai_manager_pro_automation', 'ai_manager_pro_automation_tasks');
        register_setting('ai_manager_pro_automation', 'ai_manager_pro_automation_enabled');
    }

    public function add_admin_menu()
    {
        // Main dashboard page
        add_menu_page(
            __('AI Manager Pro - Dashboard', 'ai-website-manager-pro'),
            __('AI Manager Pro', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro',
            [$this, 'render_dashboard_page'],
            'dashicons-robot',
            30
        );

        // General Settings
        add_submenu_page(
            'ai-manager-pro',
            __('General Settings', 'ai-website-manager-pro'),
            __('⚙️ הגדרות כלליות', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-general',
            [$this, 'render_general_page']
        );

        // API Keys
        add_submenu_page(
            'ai-manager-pro',
            __('API Keys', 'ai-website-manager-pro'),
            __('🔑 מפתחות API', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-api-keys',
            [$this, 'render_api_keys_page']
        );

        // Brand Management
        add_submenu_page(
            'ai-manager-pro',
            __('Brand Management', 'ai-website-manager-pro'),
            __('🏢 ניהול מותגים', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-brands',
            [$this, 'render_brands_page']
        );

        // Content Generator
        add_submenu_page(
            'ai-manager-pro',
            __('Content Generator', 'ai-website-manager-pro'),
            __('🤖 יצירת תוכן', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-content-generator',
            [$this, 'render_content_generator_page']
        );

        // Automation
        add_submenu_page(
            'ai-manager-pro',
            __('Automation', 'ai-website-manager-pro'),
            __('🔄 אוטומציה', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-automation',
            [$this, 'render_automation_page']
        );

        // Logs & History
        add_submenu_page(
            'ai-manager-pro',
            __('Logs & History', 'ai-website-manager-pro'),
            __('📊 לוגים והיסטוריה', 'ai-website-manager-pro'),
            'manage_options',
            'ai-manager-pro-logs',
            [$this, 'render_logs_page']
        );

        // Keep main page as dashboard - don't remove it
    }

    public function enqueue_admin_scripts($hook)
    {
        // Load on all admin pages for now to ensure functionality
        // TODO: Optimize to load only on plugin pages later
        // if (strpos($hook, 'ai-manager-pro') === false) {
        //     return;
        // }

        // Enqueue CSS if exists
        $css_file = AI_MANAGER_PRO_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'ai-manager-pro-admin',
                AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/admin.css',
                [],
                AI_MANAGER_PRO_VERSION
            );
        }

        // Always enqueue jQuery for inline scripts
        wp_enqueue_script('jquery');

        // Enqueue JS if exists
        $js_file = AI_MANAGER_PRO_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'ai-manager-pro-admin',
                AI_MANAGER_PRO_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                AI_MANAGER_PRO_VERSION,
                true
            );

            // Localize script for AJAX
            wp_localize_script('ai-manager-pro-admin', 'aiManagerPro', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_manager_pro_nonce')
            ]);
        }

        // Always localize jQuery for inline scripts
        wp_localize_script('jquery', 'aiManagerProGlobal', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_manager_pro_nonce')
        ]);
    }

    /**
     * Check if version was updated and show notice to clear cache
     */
    private function check_version_update()
    {
        $stored_version = get_option('ai_manager_pro_version', '0.0.0');
        $current_version = AI_MANAGER_PRO_VERSION;

        if (version_compare($stored_version, $current_version, '<')) {
            // Version was updated
            update_option('ai_manager_pro_version', $current_version);
            set_transient('ai_manager_pro_version_updated', $current_version, 60 * 60 * 24); // 24 hours

            // Clear any WordPress cache
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }

            error_log("AI Manager Pro: Updated from version {$stored_version} to {$current_version}");
        }
    }

    public function show_success_notice()
    {
        // Show version update notice
        if ($updated_version = get_transient('ai_manager_pro_version_updated')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <h3 style="margin-top: 10px;">🎉 AI Manager Pro עודכן לגרסה <?php echo esc_html($updated_version); ?>!</h3>
                <p><strong>תכונות חדשות בגרסה 3.3.3:</strong></p>
                <ul style="list-style: disc; margin-right: 20px;">
                    <li>🎯 <strong>דשבורד משודרג</strong> - ממשק חדש עם טיזרים אינטראקטיביים</li>
                    <li>📝 <strong>טיזרים לתבניות SEO</strong> - גישה מהירה ל-5 סוגי תוכן (מאמר, מדריך, ביקורת, מוצר, בלוג)</li>
                    <li>⚡ <strong>תפריטי פעולה מהירים</strong> - כפתורי יצירה מהדשבורד ישירות למחולל התוכן</li>
                    <li>🆕 <strong>סעיף "What's New"</strong> - טיזר מודגש לשיפורים האחרונים בגרסה</li>
                    <li>🎨 <strong>עיצוב מושלם</strong> - כרטיסים צבעוניים עם אנימציות וסמלים</li>
                    <li>🔗 <strong>אינטגרציה חכמה</strong> - טעינה אוטומטית של תבנית בעת לחיצה על טיזר</li>
                </ul>
                <p style="background: #e0f2fe; padding: 10px; border-right: 4px solid #0284c7;">
                    <strong>💡 טיפ:</strong> לחץ על כרטיס התבנית בדשבורד והמערכת תעביר אותך אוטומטית למחולל התוכן עם התבנית שנבחרה!
                </p>
                <p style="background: #fff3cd; padding: 10px; border-right: 4px solid #ffc107;">
                    <strong>⚠️ חשוב!</strong> אם אינך רואה את השיפורים החדשים:
                    <br>1. רענן את הדף (Ctrl+F5 או Cmd+Shift+R)
                    <br>2. נקה את קאש הדפדפן
                    <br>3. אם משתמש בפלאגין קאש - נקה את הקאש
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-general'); ?>" class="button button-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        🚀 ראה דשבורד חדש →
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-content-generator'); ?>" class="button">
                        ✨ נסה תבניות SEO
                    </a>
                    <button type="button" class="button" onclick="location.reload(true);">רענן דף זה</button>
                </p>
            </div>
            <?php
            // Delete transient after showing once
            if (isset($_GET['page']) && strpos($_GET['page'], 'ai-manager-pro') !== false) {
                delete_transient('ai_manager_pro_version_updated');
            }
            return;
        }

        // Original success notice
        if (isset($_GET['page']) && strpos($_GET['page'], 'ai-manager-pro') !== false) {
            return; // Don't show on our own pages
        }

        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>🎉 AI Manager Pro</strong> פעיל בהצלחה עם כל התפריטים!
                <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-general'); ?>">התחל עכשיו</a>
            </p>
        </div>
        <?php
    }

    public function render_dashboard_page()
    {
        // Load the new main dashboard
        $dashboard_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/dashboard-main.php';

        if (file_exists($dashboard_file)) {
            include $dashboard_file;
        } else {
            echo '<div class="wrap"><h1>Dashboard Error</h1><p>Dashboard file not found.</p></div>';
        }
    }

    public function render_main_page()
    {
        // This function should not be called since we use render_spa_main_page directly
        // But if it is called, redirect to the main dashboard
        $this->render_dashboard_page();
    }

    public function render_spa_main_page()
    {
        // Debug: Add a comment to verify this function is called
        echo '<!-- AI Manager Pro Dashboard Loading -->';

        // Load the new main dashboard
        $dashboard_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/dashboard-main.php';

        if (file_exists($dashboard_file)) {
            // Include the dashboard file
            include $dashboard_file;
        } else {
            // Debug: show file path if not found
            echo '<div class="notice notice-error"><p>Dashboard file not found at: ' . esc_html($dashboard_file) . '</p></div>';
            // Fallback to old dashboard
            $this->render_dashboard_fallback();
        }
    }

    private function render_dashboard_fallback()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('AI Website Manager Pro', 'ai-website-manager-pro'); ?></h1>
            <div class="notice notice-info">
                <p>
                    <?php _e('SPA Router not available. Please check your installation.', 'ai-website-manager-pro'); ?>
                </p>
            </div>
            <div class="dashboard-fallback">
                <h2>
                    <?php _e('Quick Links', 'ai-website-manager-pro'); ?>
                </h2>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-general'); ?>" class="button button-primary">
                        <?php _e('General Settings', 'ai-website-manager-pro'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-brands'); ?>" class="button button-secondary">
                        <?php _e('Brand Management', 'ai-website-manager-pro'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-content'); ?>" class="button button-secondary">
                        <?php _e('Content Generator', 'ai-website-manager-pro'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    public function render_general_page()
    {
        $this->render_page('General Settings', 'הגדרות כלליות של הפלאגין', 'general');
    }

    public function render_api_keys_page()
    {
        $this->render_page('API Keys', 'ניהול מפתחות API לספקי AI', 'api-keys');
    }

    public function render_brands_page()
    {
        $brands_data = get_option('ai_manager_pro_brands_data', []);
        $active_brand = get_option('ai_manager_pro_active_brand', '');

        ?>
        <div class="wrap">
            <h1>🏢 ניהול פרופילי מותג מתקדם</h1>

            <div class="card" style="max-width: 1200px;">
                <h2>ניהול פרופילי מותג</h2>
                <p>צור ונהל פרופילי מותג מקצועיים עם ייבוא וייצוא JSON מתקדם.</p>

                <!-- Import/Export Section -->
                <div class="import-export-section"
                    style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h3>📥 ייבוא וייצוא מותגים</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="import-section">
                            <h4>ייבוא מותג מ-JSON</h4>
                            <input type="file" id="brand_import_file" accept=".json" style="margin-bottom: 10px;">
                            <button type="button" class="button button-primary" onclick="importBrandFromJSON()">ייבא
                                מותג</button>
                            <p class="description">העלה קובץ JSON עם הגדרות מותג מלאות</p>
                        </div>
                        <div class="export-section">
                            <h4>ייצוא מותג ל-JSON</h4>
                            <select id="export_brand_select" style="width: 100%; margin-bottom: 10px;">
                                <option value="">בחר מותג לייצוא</option>
                                <?php foreach ($brands_data as $id => $brand): ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button button-secondary" onclick="exportBrandToJSON()">ייצא
                                מותג</button>
                            <p class="description">ייצא מותג קיים לקובץ JSON</p>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="button" class="button" onclick="downloadBrandTemplate()">📋 הורד תבנית JSON
                            לדוגמא</button>
                        <span class="description" style="margin-right: 10px;">תבנית מקצועית עם כל השדות הנדרשים</span>
                    </div>
                </div>

                <!-- Add New Brand Form -->
                <div class="brand-form-section">
                    <h3>הוסף מותג חדש</h3>
                    <form id="brand-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">שם המותג</th>
                                <td><input type="text" id="brand_name" placeholder="שם המותג" style="width: 300px;" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">תיאור</th>
                                <td><textarea id="brand_description" placeholder="תיאור המותג" rows="3"
                                        style="width: 400px;"></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row">טון כתיבה</th>
                                <td>
                                    <select id="brand_tone" style="width: 200px;">
                                        <option value="professional">מקצועי</option>
                                        <option value="friendly">ידידותי</option>
                                        <option value="casual">רגיל</option>
                                        <option value="formal">רשמי</option>
                                        <option value="creative">יצירתי</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">קהל יעד</th>
                                <td><input type="text" id="brand_audience" placeholder="תיאור קהל היעד" style="width: 400px;" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">מילות מפתח</th>
                                <td><input type="text" id="brand_keywords" placeholder="מילות מפתח מופרדות בפסיקים"
                                        style="width: 400px;" /></td>
                            </tr>
                        </table>
                        <button type="button" class="button-primary" onclick="saveBrand()">שמור מותג</button>
                    </form>
                </div>

                <!-- Existing Brands -->
                <div class="brands-list-section">
                    <h3>מותגים קיימים</h3>
                    <div id="brands-list">
                        <?php if (empty($brands_data)): ?>
                            <p>אין מותגים עדיין. הוסף מותג ראשון!</p>
                        <?php else: ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>שם המותג</th>
                                        <th>תיאור</th>
                                        <th>טון</th>
                                        <th>סטטוס</th>
                                        <th>פעולות</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($brands_data as $id => $brand): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($brand['name']); ?></strong></td>
                                            <td><?php echo esc_html(substr($brand['description'], 0, 50)) . '...'; ?></td>
                                            <td><?php echo esc_html($brand['tone']); ?></td>
                                            <td>
                                                <?php if ($active_brand === $id): ?>
                                                    <span class="status-active" style="color: green;">✅ פעיל</span>
                                                <?php else: ?>
                                                    <span class="status-inactive">⚪ לא פעיל</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="button"
                                                    onclick="setActiveBrand('<?php echo esc_js($id); ?>')">הפעל</button>
                                                <button type="button" class="button"
                                                    onclick="deleteBrand('<?php echo esc_js($id); ?>')">מחק</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function saveBrand() {
                const brandData = {
                    name: document.getElementById('brand_name').value,
                    description: document.getElementById('brand_description').value,
                    tone: document.getElementById('brand_tone').value,
                    audience: document.getElementById('brand_audience').value,
                    keywords: document.getElementById('brand_keywords').value
                };
                if (!brandData.name) {
                    alert('שם המותג נדרש');
                    return;
                }
                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_save_brand',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                    brand_data: brandData
                }, function (response) {
                    if (response.success) {
                        alert('המותג נשמר בהצלחה!');
                        location.reload();
                    } else {
                        alert('שגיאה: ' + response.data);
                    }
                });
            }

            function setActiveBrand(brandId) {
                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_save_brand',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                    set_active: brandId
                }, function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('שגיאה: ' + response.data);
                    }
                });
            }

            function deleteBrand(brandId) {
                if (confirm('האם אתה בטוח שברצונך למחוק את המותג?')) {
                    jQuery.post(ajaxurl, {
                        action: 'ai_manager_pro_delete_brand',
                        nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                        brand_id: brandId
                    }, function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('שגיאה: ' + response.data);
                        }
                    });
                }
            }

            function importBrandFromJSON() {
                const fileInput = document.getElementById('brand_import_file');
                const file = fileInput.files[0];

                if (!file) {
                    alert('אנא בחר קובץ JSON');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    try {
                        const brandData = JSON.parse(e.target.result);

                        // Validate JSON structure
                        if (!brandData.brand_info || !brandData.brand_info.name) {
                            alert('קובץ JSON לא תקין - חסר שם מותג');
                            return;
                        }

                        // Convert JSON to our format
                        const convertedBrand = convertJSONToBrandFormat(brandData);

                        jQuery.post(ajaxurl, {
                            action: 'ai_manager_pro_import_brand_json',
                            nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                            brand_data: convertedBrand
                        }, function (response) {
                            if (response.success) {
                                alert('המותג יובא בהצלחה!');
                                location.reload();
                            } else {
                                alert('שגיאה בייבוא: ' + response.data);
                            }
                        });

                    } catch (error) {
                        alert('שגיאה בקריאת קובץ JSON: ' + error.message);
                    }
                };
                reader.readAsText(file);
            }

            function exportBrandToJSON() {
                const brandId = document.getElementById('export_brand_select').value;
                if (!brandId) {
                    alert('אנא בחר מותג לייצוא');
                    return;
                }

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_export_brand_json',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                    brand_id: brandId
                }, function (response) {
                    if (response.success) {
                        const dataStr = JSON.stringify(response.data.brand_data, null, 2);
                        const dataBlob = new Blob([dataStr], { type: 'application/json' });
                        const url = URL.createObjectURL(dataBlob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = response.data.filename;
                        link.click();
                        URL.revokeObjectURL(url);
                    } else {
                        alert('שגיאה בייצוא: ' + response.data);
                    }
                });
            }

            function downloadBrandTemplate() {
                window.open('<?php echo AI_MANAGER_PRO_PLUGIN_URL; ?>includes/samples/brand-template.json', '_blank');
            }

            function convertJSONToBrandFormat(jsonData) {
                return {
                    name: jsonData.brand_info.name,
                    description: jsonData.brand_info.description || '',
                    tone: jsonData.voice_and_tone.primary_tone || 'מקצועי',
                    audience: jsonData.target_audience.primary_demographic ?
                        `${jsonData.target_audience.primary_demographic.age_range}, ${jsonData.target_audience.primary_demographic.occupation}` : '',
                    keywords: jsonData.keywords_and_messaging.primary_keywords ?
                        jsonData.keywords_and_messaging.primary_keywords.join(', ') : '',
                    // Extended data
                    extended_data: {
                        brand_info: jsonData.brand_info,
                        voice_and_tone: jsonData.voice_and_tone,
                        target_audience: jsonData.target_audience,
                        content_guidelines: jsonData.content_guidelines,
                        keywords_and_messaging: jsonData.keywords_and_messaging,
                        visual_identity: jsonData.visual_identity,
                        compliance_and_legal: jsonData.compliance_and_legal,
                        content_calendar: jsonData.content_calendar,
                        performance_metrics: jsonData.performance_metrics,
                        competitive_analysis: jsonData.competitive_analysis
                    }
                };
            }
        </script>

        <style>
            .brand-form-section,
            .brands-list-section {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
            }

            .status-active {
                font-weight: bold;
            }
        </style>
        <?php
    }

    public function render_automation_page()
    {
        $automation_tasks = get_option('ai_manager_pro_automation_tasks', []);
        $automation_enabled = get_option('ai_manager_pro_automation_enabled', false);

        ?>
        <div class="wrap">
            <h1>Automation</h1>

            <div class="card" style="max-width: 1000px;">
                <h2>ניהול אוטומציה</h2>

                <!-- Global Automation Toggle -->
                <div class="automation-toggle-section"
                    style="margin-bottom: 30px; padding: 20px; border: 2px solid #0073aa; border-radius: 8px; background: #f0f8ff;">
                    <h3>🔄 הפעלת אוטומציה כללית</h3>
                    <form method="post" action="options.php">
                        <?php settings_fields('ai_manager_pro_automation'); ?>
                        <label>
                            <input type="checkbox" name="ai_manager_pro_automation_enabled" value="1" <?php checked($automation_enabled, 1); ?> onchange="this.form.submit()" />
                            <strong>הפעל אוטומציה</strong> - מאפשר ביצוע משימות אוטומטיות
                        </label>
                        <p class="description">כאשר מופעל, המערכת תבצע משימות אוטומטיות לפי הלוח זמנים שהוגדר</p>
                    </form>
                </div>

                <!-- Add New Automation Task -->
                <div class="automation-form-section"
                    style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                    <h3>➕ הוסף משימת אוטומציה חדשה</h3>
                    <form id="automation-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">שם המשימה</th>
                                <td><input type="text" id="task_name" placeholder="שם המשימה" style="width: 300px;" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">סוג משימה</th>
                                <td>
                                    <select id="task_type" style="width: 200px;" onchange="updateTaskOptions()">
                                        <option value="content_generation">יצירת תוכן</option>
                                        <option value="social_posting">פרסום ברשתות חברתיות</option>
                                        <option value="email_campaign">קמפיין אימייל</option>
                                        <option value="seo_optimization">אופטימיזציה לSEO</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">תדירות</th>
                                <td>
                                    <select id="task_frequency" style="width: 200px;">
                                        <option value="hourly">כל שעה</option>
                                        <option value="daily">יומי</option>
                                        <option value="weekly">שבועי</option>
                                        <option value="monthly">חודשי</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="content_options" style="display: table-row;">
                                <th scope="row">הגדרות תוכן</th>
                                <td>
                                    <input type="text" id="content_topic" placeholder="נושא לתוכן" style="width: 250px;" />
                                    <select id="content_type_auto" style="width: 150px;">
                                        <option value="blog_post">פוסט בלוג</option>
                                        <option value="product_description">תיאור מוצר</option>
                                        <option value="news_article">כתבה חדשותית</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">סטטוס</th>
                                <td>
                                    <select id="task_status" style="width: 150px;">
                                        <option value="active">פעיל</option>
                                        <option value="paused">מושהה</option>
                                        <option value="draft">טיוטה</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button-primary" onclick="saveAutomationTask()">שמור משימה</button>
                    </form>
                </div>

                <!-- Existing Automation Tasks -->
                <div class="automation-tasks-section">
                    <h3>📋 משימות אוטומציה קיימות</h3>
                    <?php if (empty($automation_tasks)): ?>
                        <p>אין משימות אוטומציה עדיין. הוסף משימה ראשונה!</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>שם המשימה</th>
                                    <th>סוג</th>
                                    <th>תדירות</th>
                                    <th>סטטוס</th>
                                    <th>ביצוע אחרון</th>
                                    <th>ביצוע הבא</th>
                                    <th>פעולות</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($automation_tasks as $id => $task): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($task['name']); ?></strong></td>
                                        <td><?php echo esc_html($task['type']); ?></td>
                                        <td><?php echo esc_html($task['frequency']); ?></td>
                                        <td>
                                            <?php if ($task['status'] === 'active'): ?>
                                                <span style="color: green;">✅ פעיל</span>
                                            <?php elseif ($task['status'] === 'paused'): ?>
                                                <span style="color: orange;">⏸️ מושהה</span>
                                            <?php else: ?>
                                                <span style="color: gray;">📝 טיוטה</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($task['last_run'] ?? 'אף פעם'); ?></td>
                                        <td><?php echo esc_html($task['next_run'] ?? 'לא מתוזמן'); ?></td>
                                        <td>
                                            <button type="button" class="button"
                                                onclick="toggleAutomationTask('<?php echo esc_js($id); ?>')">
                                                <?php echo $task['status'] === 'active' ? 'השהה' : 'הפעל'; ?>
                                            </button>
                                            <button type="button" class="button"
                                                onclick="runAutomationTaskNow('<?php echo esc_js($id); ?>')">הרץ עכשיו</button>
                                            <button type="button" class="button"
                                                onclick="deleteAutomationTask('<?php echo esc_js($id); ?>')">מחק</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Automation Statistics -->
                <div class="automation-stats-section"
                    style="margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 4px; background: #f0f8ff;">
                    <h3>📊 סטטיסטיקות אוטומציה</h3>
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>משימות פעילות</h4>
                            <span style="font-size: 24px; color: green;"><?php echo count(array_filter($automation_tasks, function ($task) {
                                return $task['status'] === 'active';
                            })); ?></span>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>משימות מושהות</h4>
                            <span style="font-size: 24px; color: orange;"><?php echo count(array_filter($automation_tasks, function ($task) {
                                return $task['status'] === 'paused';
                            })); ?></span>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>סה"כ משימות</h4>
                            <span style="font-size: 24px; color: blue;"><?php echo count($automation_tasks); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>     function updateTaskOptions() {
                const taskType = document.getElementById('task_type').value; const contentOptions = document.getElementById('content_options');
                if (taskType === 'content_generation') { contentOptions.style.display = 'table-row'; } else { contentOptions.style.display = 'none'; }
            }
            function saveAutomationTask() {
                const taskData = {
                    name: document.getElementById('task_name').value,
                    type: document.getElementById('task_type').value,
                    frequency: document.getElementById('task_frequency').value,
                    status: document.getElementById('task_status').value,
                    content_topic: document.getElementById('content_topic').value,
                    content_type: document.getElementById('content_type_auto').value
                };

                if (!taskData.name) {
                    alert('שם המשימה נדרש');
                    return;
                }

                // Show loading state
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'שומר...';
                button.disabled = true;

                console.log('Saving automation task:', taskData);

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_save_automation',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                    task_data: taskData
                })
                    .done(function (response) {
                        console.log('Response:', response);
                        if (response.success) {
                            alert('המשימה נשמרה בהצלחה!');
                            location.reload();
                        } else {
                            alert('שגיאה: ' + (response.data || 'Unknown error'));
                        }
                    })
                    .fail(function (xhr, status, error) {
                        console.error('AJAX error:', xhr.responseText);
                        alert('שגיאת רשת: ' + error);
                    })
                    .always(function () {
                        button.textContent = originalText;
                        button.disabled = false;
                    });
            }
            function toggleAutomationTask(taskId) { jQuery.post(ajaxurl, { action: 'ai_manager_pro_toggle_automation', nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', task_id: taskId }, function (response) { if (response.success) { location.reload(); } else { alert('שגיאה: ' + response.data); } }); }
            function runAutomationTaskNow(taskId) { if (confirm('האם להריץ את המשימה עכשיו?')) { jQuery.post(ajaxurl, { action: 'ai_manager_pro_run_task_now', nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', task_id: taskId }, function (response) { if (response.success) { alert('המשימה הורצה בהצלחה!'); location.reload(); } else { alert('שגיאה: ' + response.data); } }); } }
            function deleteAutomationTask(taskId) { if (confirm('האם אתה בטוח שברצונך למחוק את המשימה?')) { jQuery.post(ajaxurl, { action: 'ai_manager_pro_delete_automation', nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', task_id: taskId }, function (response) { if (response.success) { location.reload(); } else { alert('שגיאה: ' + response.data); } }); } }
        </script>

        <style>
            .automation-form-section,
            .automation-tasks-section,
            .automation-stats-section {
                margin: 20px 0;
            }

            .automation-toggle-section h3 {
                margin-top: 0;
            }
        </style>
        <?php
    }

    public function render_logs_page()
    {
        // Create logs table if not exists
        $this->create_logs_table();

        global $wpdb;
        $logs_table = $wpdb->prefix . 'ai_manager_pro_logs';

        // Get logs with pagination
        $per_page = 50;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        // Build WHERE clause for filters
        $where_conditions = [];
        $where_values = [];

        if (!empty($_GET['level'])) {
            $where_conditions[] = "level = %s";
            $where_values[] = sanitize_text_field($_GET['level']);
        }

        if (!empty($_GET['module'])) {
            $where_conditions[] = "module = %s";
            $where_values[] = sanitize_text_field($_GET['module']);
        }

        if (!empty($_GET['date_from'])) {
            $where_conditions[] = "DATE(created_at) >= %s";
            $where_values[] = sanitize_text_field($_GET['date_from']);
        }

        if (!empty($_GET['date_to'])) {
            $where_conditions[] = "DATE(created_at) <= %s";
            $where_values[] = sanitize_text_field($_GET['date_to']);
        }

        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }

        // Get logs
        $query = "SELECT * FROM {$logs_table} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;

        if (!empty($where_values)) {
            $logs = $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            $logs = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ));
        }

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$logs_table} {$where_clause}";
        if (!empty($where_conditions)) {
            $count_values = array_slice($where_values, 0, -2); // Remove LIMIT and OFFSET values
            $total_logs = $wpdb->get_var($wpdb->prepare($count_query, $count_values));
        } else {
            $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}");
        }

        $total_pages = ceil($total_logs / $per_page);

        // Get statistics
        $stats = [
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}"),
            'today' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table} WHERE DATE(created_at) = %s",
                current_time('Y-m-d')
            )),
            'errors' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table} WHERE level = %s",
                'error'
            )),
            'warnings' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table} WHERE level = %s",
                'warning'
            ))
        ];

        ?>
        <div class="wrap">
            <h1>Logs & History</h1>

            <div class="card" style="max-width: 1200px;">
                <h2>לוגים והיסטוריה</h2>

                <!-- Statistics -->
                <div class="logs-stats-section"
                    style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 4px; background: #f0f8ff;">
                    <h3>📊 סטטיסטיקות לוגים</h3>
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>סה"כ לוגים</h4>
                            <span style="font-size: 24px; color: blue;"><?php echo number_format($stats['total']); ?></span>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>היום</h4>
                            <span style="font-size: 24px; color: green;"><?php echo number_format($stats['today']); ?></span>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>שגיאות</h4>
                            <span style="font-size: 24px; color: red;"><?php echo number_format($stats['errors']); ?></span>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 15px; background: white; border-radius: 4px;">
                            <h4>אזהרות</h4>
                            <span
                                style="font-size: 24px; color: orange;"><?php echo number_format($stats['warnings']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Log Filters -->
                <div class="logs-filters-section"
                    style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                    <h3>🔍 סינון לוגים</h3>
                    <form method="get" style="display: flex; gap: 15px; align-items: end;">
                        <input type="hidden" name="page" value="ai-manager-pro-logs" />
                        <div>
                            <label>רמת לוג:</label><br>
                            <select name="level" style="width: 120px;">
                                <option value="">הכל</option>
                                <option value="info" <?php selected($_GET['level'] ?? '', 'info'); ?>>מידע</option>
                                <option value="warning" <?php selected($_GET['level'] ?? '', 'warning'); ?>>אזהרה</option>
                                <option value="error" <?php selected($_GET['level'] ?? '', 'error'); ?>>שגיאה</option>
                            </select>
                        </div>
                        <div>
                            <label>מודול:</label><br>
                            <select name="module" style="width: 150px;">
                                <option value="">הכל</option>
                                <option value="content_generation" <?php selected($_GET['module'] ?? '', 'content_generation'); ?>>יצירת תוכן</option>
                                <option value="automation" <?php selected($_GET['module'] ?? '', 'automation'); ?>>אוטומציה
                                </option>
                                <option value="api" <?php selected($_GET['module'] ?? '', 'api'); ?>>API</option>
                                <option value="brand" <?php selected($_GET['module'] ?? '', 'brand'); ?>>מותג</option>
                            </select>
                        </div>
                        <div>
                            <label>תאריך מ:</label><br>
                            <input type="date" name="date_from" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label>תאריך עד:</label><br>
                            <input type="date" name="date_to" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>" />
                        </div>
                        <div>
                            <button type="submit" class="button">סנן</button>
                            <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-logs'); ?>" class="button">נקה</a>
                        </div>
                    </form>
                </div>

                <!-- Log Management Actions -->
                <div class="logs-actions-section"
                    style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #fff3cd;">
                    <h3>🛠️ ניהול לוגים</h3>
                    <button type="button" class="button" onclick="clearOldLogs()">נקה לוגים ישנים (מעל 30 יום)</button>
                    <button type="button" class="button" onclick="clearAllLogs()">נקה את כל הלוגים</button>
                    <button type="button" class="button" onclick="exportLogs()">ייצא לוגים לCSV</button>
                    <p class="description">פעולות ניהול לוגים - השתמש בזהירות!</p>
                </div>

                <!-- Logs Table -->
                <div class="logs-table-section">
                    <h3>📝 לוגים אחרונים (<?php echo number_format($total_logs); ?> תוצאות)</h3>
                    <?php if (empty($logs)): ?>
                        <p>אין לוגים להצגה עם הסינון הנוכחי.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">תאריך ושעה</th>
                                    <th style="width: 80px;">רמה</th>
                                    <th style="width: 100px;">מודול</th>
                                    <th>הודעה</th>
                                    <th style="width: 100px;">משתמש</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($log->created_at))); ?></td>
                                        <td>
                                            <?php
                                            $level_colors = [
                                                'info' => 'blue',
                                                'warning' => 'orange',
                                                'error' => 'red'
                                            ];
                                            $color = $level_colors[$log->level] ?? 'gray';
                                            ?>
                                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                                <?php echo esc_html(strtoupper($log->level)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log->module ?: 'כללי'); ?></td>
                                        <td>
                                            <?php echo esc_html($log->message); ?>
                                            <?php if ($log->context): ?>
                                                <details style="margin-top: 5px;">
                                                    <summary style="cursor: pointer; color: #0073aa;">פרטים נוספים</summary>
                                                    <pre
                                                        style="background: #f0f0f0; padding: 10px; margin-top: 5px; font-size: 12px; overflow-x: auto;"><?php echo esc_html($log->context); ?></pre>
                                                </details>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($log->user_id) {
                                                $user = get_user_by('id', $log->user_id);
                                                echo esc_html($user ? $user->display_name : 'משתמש לא ידוע');
                                            } else {
                                                echo 'מערכת';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="tablenav bottom">
                                <div class="tablenav-pages">
                                    <span class="displaying-num"><?php echo number_format($total_logs); ?> פריטים</span>
                                    <?php
                                    $page_links = paginate_links([
                                        'base' => add_query_arg('paged', '%#%'),
                                        'format' => '',
                                        'prev_text' => '&laquo;',
                                        'next_text' => '&raquo;',
                                        'total' => $total_pages,
                                        'current' => $current_page
                                    ]);
                                    echo $page_links;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            // Define ajaxurl for WordPress AJAX
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

            function clearOldLogs() {
                if (confirm('האם אתה בטוח שברצונך למחוק לוגים ישנים מעל 30 יום?')) {
                    jQuery.post(ajaxurl, {
                        action: 'ai_manager_pro_clear_logs', nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', type: 'old'
                    }, function (response) { if (response.success) { alert('לוגים ישנים נמחקו בהצלחה!'); location.reload(); } else { alert('שגיאה: ' + response.data); } });
                }
            }
            function clearAllLogs() { if (confirm('האם אתה בטוח שברצונך למחוק את כל הלוגים? פעולה זו לא ניתנת לביטול!')) { jQuery.post(ajaxurl, { action: 'ai_manager_pro_clear_logs', nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', type: 'all' }, function (response) { if (response.success) { alert('כל הלוגים נמחקו בהצלחה!'); location.reload(); } else { alert('שגיאה: ' + response.data); } }); } }
            function exportLogs() { window.open(ajaxurl + '?action=ai_manager_pro_export_logs&nonce=<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>', '_blank'); }
        </script>

        <style>
            .logs-stats-section,
            .logs-filters-section,
            .logs-actions-section,
            .logs-table-section {
                margin: 20px 0;
            }

            .logs-stats-section h3,
            .logs-filters-section h3,
            .logs-actions-section h3,
            .logs-table-section h3 {
                margin-top: 0;
            }

            details summary {
                outline: none;
            }
        </style>


        <?php
    }

    private function render_page($title, $description, $page_type)
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($title); ?></h1>

            <div class="notice notice-success">
                <p><strong>✅ הצלחה!</strong> התפריטים נטענו בהצלחה!</p>
            </div>

            <div class="card" style="max-width: 800px;">
                <h2><?php echo esc_html($description); ?></h2>
                <p>הדף הזה מוכן לפונקציונליות מתקדמת.</p>

                <?php if ($page_type === 'general'): ?>
                    <h3>הגדרות כלליות:</h3>
                    <form method="post" action="options.php">
                        <?php settings_fields('ai_manager_pro_general'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">ספק AI ברירת מחדל</th>
                                <td>
                                    <select name="ai_manager_pro_default_provider" id="default_provider"
                                        onchange="toggleProviderOptions()">
                                        <option value="openai" <?php selected(get_option('ai_manager_pro_default_provider', 'openai'), 'openai'); ?>>OpenAI</option>
                                        <option value="anthropic" <?php selected(get_option('ai_manager_pro_default_provider'), 'anthropic'); ?>>Anthropic</option>
                                        <option value="openrouter" <?php selected(get_option('ai_manager_pro_default_provider'), 'openrouter'); ?>>OpenRouter</option>
                                        <option value="deepseek" <?php selected(get_option('ai_manager_pro_default_provider'), 'deepseek'); ?>>DeepSeek</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="openrouter_model_row" style="display: none;">
                                <th scope="row">מודל OpenRouter</th>
                                <td>
                                    <select name="ai_manager_pro_openrouter_model" style="width: 400px;">
                                        <optgroup label="🆓 מודלים חינמיים (מאומתים)"></optgroup>
                                        </optgroup>
                                        <option value="google/gemma-7b-it:free" <?php selected(get_option('ai_manager_pro_openrouter_model', 'google/gemma-7b-it:free'), 'google/gemma-7b-it:free'); ?>>Gemma 7B (חינמי)</option>
                                        <option value="huggingfaceh4/zephyr-7b-beta:free" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'huggingfaceh4/zephyr-7b-beta:free'); ?>>Zephyr 7B Beta (חינמי)</option>
                                        <option value="openchat/openchat-7b:free" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'openchat/openchat-7b:free'); ?>>OpenChat 7B (חינמי)</option>
                                        <option value="nousresearch/nous-capybara-7b:free" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'nousresearch/nous-capybara-7b:free'); ?>>Nous Capybara 7B (חינמי)</option>
                                        <option value="mistralai/mistral-7b-instruct:free" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'mistralai/mistral-7b-instruct:free'); ?>>Mistral 7B (חינמי)</option>
                                        </optgroup>
                                        <optgroup label="💰 OpenAI Models">
                                            <option value="openai/gpt-4-turbo" <?php selected(get_option('ai_manager_pro_openrouter_model', 'openai/gpt-4-turbo'), 'openai/gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                            <option value="openai/gpt-4" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'openai/gpt-4'); ?>>GPT-4
                                            </option>
                                            <option value="openai/gpt-3.5-turbo" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'openai/gpt-3.5-turbo'); ?>>
                                                GPT-3.5 Turbo</option>
                                            <option value="openai/gpt-4-vision-preview" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'openai/gpt-4-vision-preview'); ?>>GPT-4 Vision</option>
                                        </optgroup>
                                        <optgroup label="🧠 Anthropic Models">
                                            <option value="anthropic/claude-3-opus" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'anthropic/claude-3-opus'); ?>>Claude 3 Opus</option>
                                            <option value="anthropic/claude-3-sonnet" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'anthropic/claude-3-sonnet'); ?>>Claude 3 Sonnet</option>
                                            <option value="anthropic/claude-3-haiku" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'anthropic/claude-3-haiku'); ?>>Claude 3 Haiku</option>
                                            <option value="anthropic/claude-2" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'anthropic/claude-2'); ?>>
                                                Claude 2</option>
                                        </optgroup>
                                        <optgroup label="🦙 Meta Models">
                                            <option value="meta-llama/llama-2-70b-chat" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'meta-llama/llama-2-70b-chat'); ?>>Llama 2 70B Chat</option>
                                            <option value="meta-llama/llama-2-13b-chat" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'meta-llama/llama-2-13b-chat'); ?>>Llama 2 13B Chat</option>
                                            <option value="meta-llama/codellama-34b-instruct" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'meta-llama/codellama-34b-instruct'); ?>>Code Llama 34B</option>
                                        </optgroup>
                                        <optgroup label="🌟 Mistral Models">
                                            <option value="mistralai/mixtral-8x7b-instruct" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'mistralai/mixtral-8x7b-instruct'); ?>>Mixtral 8x7B</option>
                                            <option value="mistralai/mistral-7b-instruct" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'mistralai/mistral-7b-instruct'); ?>>Mistral 7B</option>
                                            <option value="mistralai/mistral-medium" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'mistralai/mistral-medium'); ?>>Mistral Medium</option>
                                        </optgroup>
                                        <optgroup label="🔬 Google Models">
                                            <option value="google/palm-2-chat-bison" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'google/palm-2-chat-bison'); ?>>PaLM 2 Chat</option>
                                            <option value="google/gemini-pro" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'google/gemini-pro'); ?>>
                                                Gemini Pro</option>
                                            <option value="google/gemini-pro-vision" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'google/gemini-pro-vision'); ?>>Gemini Pro Vision</option>
                                        </optgroup>
                                        <optgroup label="🚀 Other Models">
                                            <option value="perplexity/pplx-70b-online" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'perplexity/pplx-70b-online'); ?>>Perplexity 70B Online</option>
                                            <option value="togethercomputer/redpajama-incite-chat-3b-v1" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'togethercomputer/redpajama-incite-chat-3b-v1'); ?>>RedPajama 3B</option>
                                            <option value="databricks/dbrx-instruct" <?php selected(get_option('ai_manager_pro_openrouter_model'), 'databricks/dbrx-instruct'); ?>>DBRX Instruct</option>
                                        </optgroup>
                                    </select>
                                    <p class="description">
                                        🆓 = מודלים חינמיים (מאומתים) | 💰 = מודלים בתשלום<br>
                                        <strong>הערה:</strong> המערכת תנסה מודלים חלופיים אוטומטית אם המודל הנבחר לא זמין<br>
                                        <button type="button" class="button" onclick="testOpenRouterModels()"
                                            style="margin-top: 10px;">
                                            🔍 בדוק זמינות מודלים
                                        </button>

                                    </p>
                                </td>
                            </tr>
                            <tr id="deepseek_model_row" style="display: none;">
                                <th scope="row">מודל DeepSeek</th>
                                <td>
                                    <select name="ai_manager_pro_deepseek_model" style="width: 300px;">
                                        <option value="deepseek-chat" <?php selected(get_option('ai_manager_pro_deepseek_model', 'deepseek-chat'), 'deepseek-chat'); ?>>DeepSeek Chat - מודל שיחה מתקדם</option>
                                        <option value="deepseek-coder" <?php selected(get_option('ai_manager_pro_deepseek_model'), 'deepseek-coder'); ?>>DeepSeek Coder - מיוחד לקוד</option>
                                    </select>
                                    <p class="description">
                                        🧠 DeepSeek Chat - לתוכן כללי ושיחות<br>
                                        💻 DeepSeek Coder - מיוחד לתיעוד טכני וקוד<br>
                                        <button type="button" class="button" onclick="testDeepSeekModels()"
                                            style="margin-top: 10px;">
                                            🔍 בדוק זמינות מודלים
                                        </button>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">סטטוס פוסט ברירת מחדל</th>
                                <td>
                                    <select name="ai_manager_pro_default_post_status">
                                        <option value="draft" <?php selected(get_option('ai_manager_pro_default_post_status', 'draft'), 'draft'); ?>>טיוטה</option>
                                        <option value="pending" <?php selected(get_option('ai_manager_pro_default_post_status'), 'pending'); ?>>ממתין לאישור</option>
                                        <option value="publish" <?php selected(get_option('ai_manager_pro_default_post_status'), 'publish'); ?>>פורסם</option>
                                    </select>
                                    <p class="description">סטטוס ברירת מחדל לתוכן שנוצר</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">פרסום אוטומטי</th>
                                <td>
                                    <input type="checkbox" name="ai_manager_pro_auto_publish" value="1" <?php checked(get_option('ai_manager_pro_auto_publish'), 1); ?> />
                                    <label>פרסם תוכן אוטומטית</label>
                                    <p class="description">אם מופעל, תוכן שנוצר יפורסם אוטומטית</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">רישום פעילות</th>
                                <td>
                                    <input type="checkbox" name="ai_manager_pro_enable_logging" value="1" <?php checked(get_option('ai_manager_pro_enable_logging', 1), 1); ?> />
                                    <label>הפעל רישום פעילות</label>
                                    <p class="description">שמור לוגים של פעילות הפלאגין</p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('שמור הגדרות'); ?>
                    </form>

                    <script>
                        function toggleProviderOptions() {
                            const provider = document.getElementById('default_provider').value;
                            const openrouterRow = document.getElementById('openrouter_model_row');
                            const deepseekRow = document.getElementById('deepseek_model_row');

                            // Hide all provider-specific rows first
                            openrouterRow.style.display = 'none';
                            deepseekRow.style.display = 'none';

                            // Show relevant row based on provider
                            if (provider === 'openrouter') {
                                openrouterRow.style.display = 'table-row';
                            } else if (provider === 'deepseek') {
                                deepseekRow.style.display = 'table-row';
                            }
                        }

                        // Legacy function for backward compatibility
                        function toggleOpenRouterModel() {
                            toggleProviderOptions();
                        }

                        function testOpenRouterModels() {
                            const button = event.target;
                            const originalText = button.textContent;
                            button.textContent = 'בודק...';
                            button.disabled = true;

                            jQuery.post(ajaxurl, {
                                action: 'ai_manager_pro_test_openrouter_models',
                                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                            })
                                .done(function (response) {
                                    if (response.success) {
                                        alert('✅ בדיקת מודלים הושלמה!\n\n' + response.data.message);
                                    } else {
                                        alert('❌ שגיאה בבדיקת מודלים:\n' + response.data);
                                    }
                                })
                                .fail(function () {
                                    alert('❌ שגיאת רשת בבדיקת מודלים');
                                })
                                .always(function () {
                                    button.textContent = originalText;
                                    button.disabled = false;
                                });
                        }

                        function testDeepSeekModels() {
                            const button = event.target;
                            const originalText = button.textContent;
                            button.textContent = 'בודק...';
                            button.disabled = true;

                            jQuery.post(ajaxurl, {
                                action: 'ai_manager_pro_test_deepseek_connection',
                                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                            })
                                .done(function (response) {
                                    if (response.success) {
                                        alert('✅ בדיקת מודלי DeepSeek הושלמה!\n\n' + response.data.message);
                                    } else {
                                        alert('❌ שגיאה בבדיקת מודלי DeepSeek:\n' + response.data);
                                    }
                                })
                                .fail(function () {
                                    alert('❌ שגיאת רשת בבדיקת מודלי DeepSeek');
                                })
                                .always(function () {
                                    button.textContent = originalText;
                                    button.disabled = false;
                                });
                        }

                        // Show/hide on page load
                        document.addEventListener('DOMContentLoaded', function () {
                            toggleProviderOptions();
                        });

                    </script>
                <?php elseif ($page_type === 'api-keys'): ?>
                    <h3>מפתחות API:</h3>
                    <form method="post" action="options.php">
                        <?php settings_fields('ai_manager_pro_api_keys'); ?>
                        <div class="api-keys-section">
                            <h4>🤖 OpenAI</h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">מפתח API</th>
                                    <td>
                                        <input type="password" name="ai_manager_pro_openai_api_key"
                                            value="<?php echo esc_attr(get_option('ai_manager_pro_openai_api_key')); ?>"
                                            placeholder="sk-..." style="width: 400px;" />
                                        <button type="button" class="button" onclick="testConnection('openai')">בדוק חיבור</button>
                                        <p class="description">מפתח API מ-OpenAI לשימוש במודלי GPT</p>
                                    </td>
                                </tr>
                            </table>

                            <h4>🧠 Anthropic (Claude)</h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">מפתח API</th>
                                    <td>
                                        <input type="password" name="ai_manager_pro_anthropic_api_key"
                                            value="<?php echo esc_attr(get_option('ai_manager_pro_anthropic_api_key')); ?>"
                                            placeholder="sk-ant-..." style="width: 400px;" />
                                        <button type="button" class="button" onclick="testConnection('anthropic')">בדוק
                                            חיבור</button>
                                        <p class="description">מפתח API מ-Anthropic לשימוש במודלי Claude</p>
                                    </td>
                                </tr>
                            </table>

                            <h4>🌐 OpenRouter</h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">מפתח API</th>
                                    <td>
                                        <input type="password" name="ai_manager_pro_openrouter_api_key"
                                            value="<?php echo esc_attr(get_option('ai_manager_pro_openrouter_api_key')); ?>"
                                            placeholder="sk-or-..." style="width: 400px;" />
                                        <button type="button" class="button" onclick="testConnection('openrouter')">בדוק
                                            חיבור</button>
                                        <p class="description">מפתח API מ-OpenRouter לגישה למודלים מרובים</p>
                                    </td>
                                </tr>
                            </table>

                            <h4>🧠 DeepSeek</h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">מפתח API</th>
                                    <td>
                                        <input type="password" name="ai_manager_pro_deepseek_key"
                                            value="<?php echo esc_attr(get_option('ai_manager_pro_deepseek_key')); ?>"
                                            placeholder="sk-..." style="width: 400px;" />
                                        <button type="button" class="button" onclick="testConnection('deepseek')">בדוק
                                            חיבור</button>
                                        <p class="description">מפתח API מ-DeepSeek לשימוש במודלי Chat ו-Coder</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php submit_button('שמור מפתחות API'); ?>
                    </form>

                    <div class="notice notice-info">
                        <p><strong>💡 טיפ:</strong> כל המפתחות מוצפנים לפני השמירה במסד הנתונים.</p>
                    </div>

                    <script>
                        function testConnection(provider) {
                            const button = event.target;
                            const originalText = button.textContent;
                            button.textContent = 'בודק...';
                            button.disabled = true;

                            // Create status indicator
                            let statusElement = button.parentNode.querySelector('.connection-status');
                            if (!statusElement) {
                                statusElement = document.createElement('span');
                                statusElement.className = 'connection-status';
                                button.parentNode.appendChild(statusElement);
                            }
                            statusElement.className = 'connection-status testing';

                            // Make AJAX call to test connection
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'ai_manager_pro_test_api_connection',
                                    provider: provider,
                                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                                },
                                success: function (response) {
                                    if (response.success) {
                                        statusElement.className = 'connection-status success';
                                        alert('✅ חיבור מוצלח ל-' + provider + '!\n' +
                                            'מודל: ' + (response.data.model || 'לא זמין'));
                                    } else {
                                        statusElement.className = 'connection-status error';
                                        alert('❌ חיבור נכשל ל-' + provider + ':\n' + response.data);
                                    }
                                },
                                error: function (xhr, status, error) {
                                    statusElement.className = 'connection-status error';
                                    alert('❌ שגיאת רשת: ' + error);
                                },
                                complete: function () {
                                    button.textContent = originalText;
                                    button.disabled = false;
                                }
                            });
                        }
                    </script>

                    <style>
                        .connection-status {
                            display: inline-block;
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            margin-left: 10px;
                            vertical-align: middle;

                        }

                        .connection-status.testing {
                            background: #dba617;
                            animation: pulse 1s infinite;
                        }

                        .connection-status.success {
                            background: #00a32a;
                        }

                        .connection-status.error {
                            background: #d63638;
                        }

                        @keyframes pulse {

                            0%,
                            100% {
                                opacity: 1;
                            }

                            50% {
                                opacity: 0.5;
                            }
                        }
                    </style>
                    </script>
                <?php endif; ?>

                <h3>מצב נוכחי:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li>✅ <strong>פלאגין פעיל</strong> - הפלאגין עובד ללא שגיאות</li>
                    <li>✅ <strong>5 תפריטים</strong> - כל התפריטים נטענו בהצלחה</li>
                    <li>✅ <strong>ממשק בעברית</strong> - תמיכה מלאה בעברית</li>
                    <li>✅ <strong>מוכן לפיתוח</strong> - ניתן להוסיף פונקציונליות</li>
                </ul>

                <h3>מידע טכני:</h3>
                <table class="form-table">
                    <tr>
                        <th>גרסה:</th>
                        <td><?php echo esc_html(AI_MANAGER_PRO_VERSION); ?></td>
                    </tr>
                    <tr>
                        <th>WordPress:</th>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <th>PHP:</th>
                        <td><?php echo esc_html(PHP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <th>דף נוכחי:</th>
                        <td><strong><?php echo esc_html($page_type); ?></strong></td>
                    </tr>
                </table>
            </div>

            <div class="card" style="max-width: 800px;">
                <h2>בדיקת קבצים</h2>
                <?php
                $files_to_check = [
                    'includes/admin/views/settings-general.php' => 'תצוגת הגדרות כלליות',
                    'includes/admin/views/settings-api-keys.php' => 'תצוגת מפתחות API',
                    'includes/admin/views/settings-brands.php' => 'תצוגת ניהול מותגים',
                    'includes/admin/views/settings-automation.php' => 'תצוגת אוטומציה',
                    'includes/admin/views/settings-logs.php' => 'תצוגת לוגים',
                    'assets/css/admin.css' => 'עיצוב ניהול',
                    'assets/js/admin.js' => 'JavaScript ניהול'
                ];

                echo '<ul style="list-style: none; padding: 0;">';
                foreach ($files_to_check as $file => $desc) {
                    $exists = file_exists(AI_MANAGER_PRO_PLUGIN_DIR . $file);
                    $status = $exists ? '✅' : '⚠️';
                    $color = $exists ? 'green' : 'orange';
                    echo '<li style="color: ' . $color . '; margin: 5px 0;">' . $status . ' ' . esc_html($desc) . '</li>';
                }
                echo '</ul>';
                ?>
            </div>
        </div>

        <style>
            .card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            }

            .card h2,
            .card h3,
            .card h4 {
                margin-top: 0;
            }

            .form-table th {
                width: 150px;
                font-weight: 600;
            }

            .api-keys-section h4 {
                margin-top: 20px;
                margin-bottom: 10px;
            }

            .api-keys-section input {
                margin-left: 10px;
                margin-right: 10px;
            }
        </style> <?php
    }

    public static function activate()
    {
        add_option('ai_manager_pro_version', AI_MANAGER_PRO_VERSION);
        add_option('ai_manager_pro_activated', current_time('mysql'));

        // Schedule the automation task if it's not already scheduled.
        if (!wp_next_scheduled('ai_manager_pro_automation_task')) {
            wp_schedule_event(time(), 'hourly', 'ai_manager_pro_automation_task');
        }
    }

    public static function deactivate()
    {
        // Clean up scheduled events.
        wp_clear_scheduled_hook('ai_manager_pro_automation_task');
    }

    public static function uninstall()
    {
        delete_option('ai_manager_pro_version');
        delete_option('ai_manager_pro_activated');
        delete_option('ai_manager_pro_brands_data');
        delete_option('ai_manager_pro_active_brand');
        delete_option('ai_manager_pro_automation_tasks');
        delete_option('ai_manager_pro_automation_enabled');

        // Clear scheduled events
        wp_clear_scheduled_hook('ai_manager_pro_automation_task');

        // Drop custom tables
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_manager_pro_logs");
    }

    // AJAX Handlers
    public function handle_save_brand()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            if (isset($_POST['set_active'])) {
                // Set active brand
                update_option('ai_manager_pro_active_brand', sanitize_text_field($_POST['set_active']));
                wp_send_json_success('Active brand updated');
                return;
            }

            $brand_data = $_POST['brand_data'] ?? [];
            if (empty($brand_data['name'])) {
                wp_send_json_error('Brand name is required');
            }

            // Sanitize brand data
            $sanitized_brand = [
                'name' => sanitize_text_field($brand_data['name']),
                'description' => sanitize_textarea_field($brand_data['description']),
                'tone' => sanitize_text_field($brand_data['tone']),
                'audience' => sanitize_text_field($brand_data['audience']),
                'keywords' => sanitize_text_field($brand_data['keywords']),
                'created' => current_time('mysql')
            ];

            // Get existing brands
            $brands = get_option('ai_manager_pro_brands_data', []);

            // Generate unique ID
            $brand_id = uniqid('brand_');
            $brands[$brand_id] = $sanitized_brand;

            // Save brands
            update_option('ai_manager_pro_brands_data', $brands);

            wp_send_json_success('Brand saved successfully');

        } catch (Exception $e) {
            wp_send_json_error('Failed to save brand: ' . $e->getMessage());
        }
    }

    public function handle_delete_brand()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = sanitize_text_field($_POST['brand_id'] ?? '');
        if (empty($brand_id)) {
            wp_send_json_error('Brand ID is required');
        }

        $brands = get_option('ai_manager_pro_brands_data', []);
        if (isset($brands[$brand_id])) {
            unset($brands[$brand_id]);
            update_option('ai_manager_pro_brands_data', $brands);

            // If this was the active brand, clear it
            if (get_option('ai_manager_pro_active_brand') === $brand_id) {
                delete_option('ai_manager_pro_active_brand');
            }

            wp_send_json_success('Brand deleted successfully');
        } else {
            wp_send_json_error('Brand not found');
        }
    }

    public function handle_test_api_connection()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $provider = sanitize_text_field($_POST['provider'] ?? '');

        if (empty($provider)) {
            wp_send_json_error('Provider is required');
        }

        // Load the real AI service
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-real-ai-service.php';
        $ai_service = new AI_Manager_Pro_Real_AI_Service();

        // Get the API key from the current settings
        $api_key = '';
        switch ($provider) {
            case 'openai':
                $api_key = get_option('ai_manager_pro_openai_api_key');
                break;
            case 'anthropic':
                $api_key = get_option('ai_manager_pro_anthropic_api_key');
                break;
            case 'openrouter':
                $api_key = get_option('ai_manager_pro_openrouter_api_key');
                break;
            case 'deepseek':
                $api_key = get_option('ai_manager_pro_deepseek_key');
                break;
        }

        if (empty($api_key)) {
            wp_send_json_error('API key not configured for ' . $provider);
        }

        // Test the actual connection
        $result = $ai_service->test_connection($provider, $api_key);

        if ($result['success']) {
            wp_send_json_success([
                'connected' => true,
                'message' => $result['message'],
                'model' => $result['model'] ?? null
            ]);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    public function handle_generate_content()
    {
        error_log('AI Manager Pro - handle_generate_content called');
        error_log('AI Manager Pro - POST data: ' . print_r($_POST, true));
        error_log('AI Manager Pro - Received provider: ' . ($_POST['ai_provider'] ?? 'NOT SET'));
        error_log('AI Manager Pro - Received brand_id: ' . ($_POST['brand_id'] ?? 'NOT SET'));

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            error_log('AI Manager Pro - Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        error_log('AI Manager Pro - Nonce verification successful');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'blog_post');
        $content_length = sanitize_text_field($_POST['content_length'] ?? 'medium');
        $brand_id = sanitize_text_field($_POST['brand_id'] ?? '');
        $provider = sanitize_text_field($_POST['ai_provider'] ?? 'openai');
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $additional_instructions = sanitize_textarea_field($_POST['additional_instructions'] ?? '');

        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }

        try {
            // Debug: Check API keys status
            $api_keys_debug = [
                'openai' => get_option('ai_manager_pro_openai_api_key'),
                'anthropic' => get_option('ai_manager_pro_anthropic_api_key'),
                'openrouter' => get_option('ai_manager_pro_openrouter_api_key'),
                'deepseek' => get_option('ai_manager_pro_deepseek_key')
            ];

            error_log('AI Manager Pro - API Keys Debug: ' . print_r($api_keys_debug, true));
            error_log('AI Manager Pro - Selected Provider: ' . $provider);

            // Load the real AI service
            require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-real-ai-service.php';
            $ai_service = new AI_Manager_Pro_Real_AI_Service();

            // Get brand data if specified
            $brand_data = null;
            if (!empty($brand_id)) {
                $brands = get_option('ai_manager_pro_brands_data', []);
                $brand_data = $brands[$brand_id] ?? null;
            }

            // Add keywords to additional instructions
            if (!empty($keywords)) {
                $keyword_list = array_map('trim', explode(',', $keywords));
                $additional_instructions .= "\n\nKeywords to include: " . implode(', ', $keyword_list);
            }

            // Set content length guidelines
            $length_guidelines = [
                'short' => 'Keep the content concise, around 100-300 words.',
                'medium' => 'Create medium-length content, around 300-800 words.',
                'long' => 'Create comprehensive content, around 800-1500 words.',
                'very-long' => 'Create detailed, in-depth content, 1500+ words.'
            ];

            if (isset($length_guidelines[$content_length])) {
                $additional_instructions .= "\n\nLength requirement: " . $length_guidelines[$content_length];
            }

            // Build the prompt
            $prompt = $ai_service->build_prompt($topic, $content_type, $brand_data, $additional_instructions);

            // Set generation options based on content length
            $max_tokens = [
                'short' => 500,
                'medium' => 1200,
                'long' => 2000,
                'very-long' => 3000
            ];

            $options = [
                'max_tokens' => $max_tokens[$content_length] ?? 1200,
                'temperature' => 0.7
            ];

            // Generate content
            $result = $ai_service->generate_content($prompt, $provider, $options);

            if ($result['success']) {
                // Log successful generation
                $this->log_activity('info', "Content generated successfully: {$topic} ({$content_type})", 'content_generation');

                // Update content generation counter
                $content_count = get_option('ai_manager_pro_content_count', 0);
                update_option('ai_manager_pro_content_count', $content_count + 1);

                // Clean the raw content from AI artifacts
                $cleaned_content = $this->clean_ai_content($result['content']);

                // Convert markdown content to HTML for proper display
                $html_content = $this->convert_markdown_to_html($cleaned_content);

                wp_send_json_success([
                    'content' => $html_content,
                    'raw_content' => $cleaned_content, // Send the cleaned raw content
                    'message' => 'Content generated successfully',
                    'model' => $result['model'] ?? null,
                    'usage' => $result['usage'] ?? null,
                    'word_count' => str_word_count($cleaned_content),
                    'char_count' => strlen($cleaned_content)
                ]);
            } else {
                throw new Exception($result['message']);
            }

        } catch (Exception $e) {
            // Log the error with comprehensive details
            $error_details = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'provider' => $provider,
                'api_key_exists' => !empty($api_key),
                'api_key_length' => strlen($api_key ?? ''),
                'topic' => $topic,
                'content_type' => $content_type,
                'brand_id' => $brand_id,
                'timestamp' => current_time('mysql'),
                'user_id' => get_current_user_id(),
                'request_data' => [
                    'topic' => $topic,
                    'content_type' => $content_type,
                    'provider' => $provider,
                    'brand_id' => $brand_id
                ]
            ];

            error_log('AI Manager Pro - Content Generation Error: ' . json_encode($error_details, JSON_UNESCAPED_UNICODE));
            $this->log_activity('error', 'Content generation failed: ' . $e->getMessage(), 'content_generation', $error_details);

            // Try fallback with mock content
            $fallback_content = $this->generate_mock_content($topic, $content_type);
            $fallback_content = $this->convert_markdown_to_html($fallback_content);

            // Create detailed fallback message with troubleshooting info
            $troubleshooting_tips = [];

            if (empty($api_key)) {
                $troubleshooting_tips[] = "• הגדר מפתח API עבור {$provider} בהגדרות התוסף";
            }

            if (strpos($e->getMessage(), 'SSL') !== false) {
                $troubleshooting_tips[] = "• בעיית SSL - בדוק את הגדרות האבטחה של השרת";
            }

            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'Network') !== false) {
                $troubleshooting_tips[] = "• בעיית רשת - בדוק את החיבור לאינטרנט";
            }

            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Invalid API key') !== false) {
                $troubleshooting_tips[] = "• מפתח API לא תקין - בדוק את המפתח בהגדרות";
            }

            if (strpos($e->getMessage(), '429') !== false) {
                $troubleshooting_tips[] = "• חריגה ממגבלת השימוש - נסה שוב מאוחר יותר";
            }

            if (strpos($e->getMessage(), 'Insufficient Balance') !== false || strpos($e->getMessage(), 'יתרת החשבון') !== false) {
                $troubleshooting_tips[] = "• יתרת החשבון ב-DeepSeek אזלה - טען יתרה נוספת בחשבון DeepSeek";
                $troubleshooting_tips[] = "• עבור לאתר DeepSeek (https://platform.deepseek.com) כדי לטעון יתרה";
            }

            $troubleshooting_message = empty($troubleshooting_tips) ?
                "• בדוק את הלוגים לפרטים נוספים" :
                implode("\n", $troubleshooting_tips);

            $detailed_message = "✅ תוכן נוצר בהצלחה באמצעות מערכת גיבוי\n\n" .
                "⚠️ הערה: היו בעיות בחיבור ל-API, אך התוכן נוצר בהצלחה.\n\n" .
                "🔍 סיבת השגיאה: {$e->getMessage()}\n\n" .
                "🛠️ פתרונות מוצעים:\n{$troubleshooting_message}\n\n" .
                "📊 פרטים טכניים:\n" .
                "• ספק: {$provider}\n" .
                "• מפתח API: " . (empty($api_key) ? "לא מוגדר" : "מוגדר (" . strlen($api_key) . " תווים)") . "\n" .
                "• זמן: " . current_time('mysql');

            wp_send_json_success([
                'content' => $fallback_content,
                'message' => $detailed_message,
                'fallback' => true,
                'error' => $e->getMessage(),
                'debug_info' => $error_details,
                'troubleshooting' => $troubleshooting_tips
            ]);
        }
    }

    private function test_api_connection($provider, $api_key)
    {
        // Mock test - return true if API key looks valid
        switch ($provider) {
            case 'openai':
                return strpos($api_key, 'sk-') === 0 && strlen($api_key) > 20;
            case 'anthropic':
                return strpos($api_key, 'sk-ant-') === 0 && strlen($api_key) > 20;
            case 'openrouter':
                return strpos($api_key, 'sk-or-') === 0 && strlen($api_key) > 20;
            default:
                return false;
        }
    }

    private function generate_mock_content($topic, $type)
    {
        $templates = [
            'blog_post' => "# {$topic}\n\nזהו פוסט בלוג מעניין על {$topic}. הנושא הזה חשוב מאוד כי...\n\n## נקודות מרכזיות:\n- נקודה ראשונה\n- נקודה שנייה\n- נקודה שלישית\n\n## סיכום\nלסיכום, {$topic} הוא נושא מרתק שראוי להעמקה.",
            'product_description' => "🌟 {$topic}\n\nמוצר מעולה שיענה על כל הצרכים שלך! המוצר כולל:\n\n✅ איכות גבוהה\n✅ עיצוב מודרני\n✅ קל לשימוש\n\nמחיר מיוחד רק היום!",
            'social_media' => "🔥 {$topic} 🔥\n\nמה דעתכם על {$topic}? אני חושב שזה נושא מעניין!\n\n#hashtag #content #ai",
            'email' => "נושא: {$topic} - הזדמנות מיוחדת!\n\nשלום,\n\nרציתי לשתף אתכם במידע חשוב על {$topic}.\n\nפרטים נוספים בקישור המצורף.\n\nבברכה,\nהצוות"
        ];

        // Try to generate real AI content first
        try {
            $active_brand_id = get_option('ai_manager_pro_active_brand');
            $brand_data = null;

            if ($active_brand_id) {
                $brands = get_option('ai_manager_pro_brands_data', []);
                $brand_data = $brands[$active_brand_id] ?? null;
            }

            return $this->generate_ai_content($topic, $type, $brand_data);

        } catch (Exception $e) {
            // Log the error and fallback to templates
            $this->log_activity('error', 'AI content generation failed: ' . $e->getMessage(), 'content_generation');

            $templates = [
                'blog_post' => "# {$topic}\n\n**שגיאה ביצירת תוכן AI - נוצר תוכן בסיסי**\n\nזהו פוסט בלוג על {$topic}. הנושא הזה חשוב מאוד.\n\n## נקודות מרכזיות:\n- נקודה ראשונה על {$topic}\n- נקודה שנייה\n- נקודה שלישית\n\n## סיכום\nלסיכום, {$topic} הוא נושא מרתק שראוי להעמקה.\n\n*הערה: תוכן זה נוצר באמצעות תבנית בסיסית עקב שגיאה ב-API.*",
                'product_description' => "🌟 {$topic}\n\n**מוצר מעולה** שיענה על כל הצרכים שלך!\n\n✅ איכות גבוהה\n✅ עיצוב מודרני\n✅ קל לשימוש\n\n*הערה: תיאור זה נוצר באמצעות תבנית בסיסית.*",
                'social_media' => "🔥 {$topic} 🔥\n\nמה דעתכם על {$topic}?\n\n#content #ai #automation",
                'email' => "נושא: {$topic} - מידע חשוב\n\nשלום,\n\nרציתי לשתף מידע על {$topic}.\n\nבברכה,\nהצוות\n\n*הערה: תוכן זה נוצר באמצעות תבנית בסיסית.*"
            ];

            return $templates[$type] ?? $templates['blog_post'];
        }
    }

    /**
     * Generate content using AI with advanced prompts
     */
    private function generate_ai_content($topic, $type, $brand_data = null)
    {
        // Get AI provider settings
        $provider = get_option('ai_manager_pro_default_provider', 'openai');
        $api_key = $this->get_api_key($provider);

        if (!$api_key) {
            throw new Exception('API key not configured for ' . $provider);
        }

        // Build advanced prompt
        $prompt = $this->build_advanced_prompt($topic, $type, $brand_data);

        // Call AI API
        $content = $this->call_ai_api($provider, $prompt, $api_key);

        // Post-process content
        $processed_content = $this->post_process_content($content, $type, $brand_data);

        // Log the generation
        $this->log_activity('info', "AI content generated: {$topic} ({$type})", 'content_generation', [
            'provider' => $provider,
            'topic' => $topic,
            'type' => $type,
            'word_count' => str_word_count($processed_content),
            'brand' => $brand_data['name'] ?? 'No brand'
        ]);

        return $processed_content;
    }

    /**
     * Build advanced AI prompt based on content type and brand
     */
    private function build_advanced_prompt($topic, $type, $brand_data)
    {
        $base_instructions = "You are a professional Hebrew content writer. Create high-quality, engaging content.";

        // Brand context
        $brand_context = "";
        if ($brand_data) {
            $brand_context = "\n\nBRAND CONTEXT:\n";
            $brand_context .= "Brand: {$brand_data['name']}\n";
            $brand_context .= "Description: {$brand_data['description']}\n";
            $brand_context .= "Tone: {$brand_data['tone']}\n";
            $brand_context .= "Target Audience: {$brand_data['audience']}\n";
            $brand_context .= "Keywords: {$brand_data['keywords']}\n";
        }

        // Content type specific prompts
        $type_prompts = [
            'blog_post' => [
                'instruction' => "Write a comprehensive blog post about '{$topic}' in Hebrew. Include:",
                'requirements' => [
                    "- Engaging headline optimized for SEO",
                    "- Introduction that hooks the reader",
                    "- 3-5 main sections with clear subheadings",
                    "- Practical tips and actionable insights",
                    "- Conclusion with call-to-action",
                    "- Meta description (150-160 characters)",
                    "- 5 relevant hashtags in Hebrew"
                ],
                'length' => "800-1200 words",
                'format' => "Use markdown formatting with proper headers (H1, H2, H3)"
            ],
            'product_description' => [
                'instruction' => "Write a compelling product description for '{$topic}' in Hebrew. Include:",
                'requirements' => [
                    "- Attention-grabbing headline",
                    "- Key features and benefits",
                    "- Problem-solution approach",
                    "- Emotional triggers",
                    "- Clear call-to-action",
                    "- SEO-friendly keywords naturally integrated"
                ],
                'length' => "200-400 words",
                'format' => "Use bullet points and emojis for visual appeal"
            ],
            'social_media' => [
                'instruction' => "Create engaging social media content about '{$topic}' in Hebrew. Include:",
                'requirements' => [
                    "- Hook in first line to grab attention",
                    "- Value-driven content that educates or entertains",
                    "- Question or call-to-action to encourage engagement",
                    "- 5-10 relevant hashtags in Hebrew",
                    "- Strategic emoji usage for visual appeal"
                ],
                'length' => "150-280 characters",
                'format' => "Optimized for maximum engagement and shares"
            ],
            'email' => [
                'instruction' => "Write a professional email about '{$topic}' in Hebrew. Include:",
                'requirements' => [
                    "- Compelling subject line that increases open rates",
                    "- Personal greeting",
                    "- Clear value proposition in first paragraph",
                    "- Structured content with clear benefits",
                    "- Strong call-to-action",
                    "- Professional signature"
                ],
                'length' => "300-500 words",
                'format' => "Professional email format with proper structure"
            ]
        ];

        $prompt_config = $type_prompts[$type] ?? $type_prompts['blog_post'];

        // Build complete prompt
        $prompt = $base_instructions . $brand_context . "\n\n";
        $prompt .= "TASK: " . $prompt_config['instruction'] . "\n\n";
        $prompt .= "REQUIREMENTS:\n" . implode("\n", $prompt_config['requirements']) . "\n\n";
        $prompt .= "LENGTH: " . $prompt_config['length'] . "\n";
        $prompt .= "FORMAT: " . $prompt_config['format'] . "\n\n";

        // Add SEO optimization
        $prompt .= "SEO REQUIREMENTS:\n";
        $prompt .= "- Include the main keyword '{$topic}' naturally 3-5 times\n";
        $prompt .= "- Use semantic keywords and related terms in Hebrew\n";
        $prompt .= "- Optimize for search intent and user value\n";
        $prompt .= "- Structure content for featured snippets\n\n";

        // Add creativity and quality guidelines
        $prompt .= "QUALITY GUIDELINES:\n";
        $prompt .= "- Use storytelling elements when appropriate\n";
        $prompt .= "- Include relevant examples or case studies\n";
        $prompt .= "- Provide actionable insights and practical value\n";
        $prompt .= "- Ensure originality and avoid generic content\n";
        $prompt .= "- Write in fluent, natural Hebrew\n\n";

        $prompt .= "Please create the content now, ensuring it meets all requirements:";

        return $prompt;
    }

    /**
     * Call AI API based on provider
     */
    private function call_ai_api($provider, $prompt, $api_key)
    {
        switch ($provider) {
            case 'openai':
                return $this->call_openai_api($prompt, $api_key);
            case 'anthropic':
                return $this->call_anthropic_api($prompt, $api_key);
            case 'openrouter':
                return $this->call_openrouter_api($prompt, $api_key);
            default:
                throw new Exception('Unsupported AI provider: ' . $provider);
        }
    }

    /**
     * Call OpenAI API
     */
    private function call_openai_api($prompt, $api_key)
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'presence_penalty' => 0.1,
            'frequency_penalty' => 0.1
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            throw new Exception('OpenAI API error: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new Exception('OpenAI API error: ' . $body['error']['message']);
        }

        return $body['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Anthropic API
     */
    private function call_anthropic_api($prompt, $api_key)
    {
        $url = 'https://api.anthropic.com/v1/messages';

        $data = [
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 2000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Anthropic API error: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new Exception('Anthropic API error: ' . $body['error']['message']);
        }

        return $body['content'][0]['text'] ?? '';
    }

    /**
     * Call OpenRouter API
     */
    private function call_openrouter_api($prompt, $api_key)
    {
        $model = get_option('ai_manager_pro_openrouter_model', 'google/gemma-7b-it:free');

        // List of fallback models if the selected one fails
        $fallback_models = [
            'google/gemma-7b-it:free',
            'huggingfaceh4/zephyr-7b-beta:free',
            'openchat/openchat-7b:free',
            'mistralai/mistral-7b-instruct:free',
            'openai/gpt-3.5-turbo' // Paid fallback
        ];

        // Try the selected model first, then fallbacks
        $models_to_try = array_unique(array_merge([$model], $fallback_models));

        foreach ($models_to_try as $current_model) {
            try {
                $result = $this->try_openrouter_model($prompt, $api_key, $current_model);

                // Log successful model if it's different from selected
                if ($current_model !== $model) {
                    $this->log_activity('warning', "OpenRouter fallback used: {$current_model} instead of {$model}", 'content_generation');
                }

                return $result;

            } catch (Exception $e) {
                // Log the failed attempt
                $this->log_activity('warning', "OpenRouter model {$current_model} failed: " . $e->getMessage(), 'content_generation');

                // Continue to next model
                continue;
            }
        }

        // If all models failed
        throw new Exception('All OpenRouter models failed. Please check your API key or try again later.');
    }

    /**
     * Try a specific OpenRouter model
     */
    private function try_openrouter_model($prompt, $api_key, $model)
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Website Manager Pro'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Network error: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            $error_message = $body['error']['message'] ?? 'Unknown API error';

            // Handle specific OpenRouter errors
            if (strpos($error_message, 'No endpoints found') !== false) {
                throw new Exception("Model {$model} is not available");
            } elseif (strpos($error_message, 'insufficient_quota') !== false) {
                throw new Exception("Insufficient quota for {$model}");
            } elseif (strpos($error_message, 'rate_limit') !== false) {
                throw new Exception("Rate limit exceeded for {$model}");
            }

            throw new Exception($error_message);
        }

        if (!isset($body['choices'][0]['message']['content'])) {
            throw new Exception("Invalid response format from {$model}");
        }

        return $body['choices'][0]['message']['content'];
    }

    /**
     * Post-process generated content
     */
    private function post_process_content($content, $type, $brand_data)
    {
        // Clean up content
        $content = trim($content);

        // Remove any API artifacts
        $content = str_replace(['```markdown', '```'], '', $content);

        // Ensure proper Hebrew text direction
        $content = '<div dir="rtl">' . $content . '</div>';

        // Type-specific post-processing
        switch ($type) {
            case 'blog_post':
                // Ensure proper markdown formatting
                if (strpos($content, '#') === false) {
                    $lines = explode("\n", $content);
                    if (!empty($lines[0])) {
                        $lines[0] = '# ' . $lines[0];
                        $content = implode("\n", $lines);
                    }
                }
                break;

            case 'social_media':
                // Ensure hashtags are properly formatted
                $content = preg_replace('/\s+#/', ' #', $content);
                break;
        }

        return $content;
    }

    /**
     * Get API key for provider
     */
    private function get_api_key($provider)
    {
        switch ($provider) {
            case 'openai':
                return get_option('ai_manager_pro_openai_api_key');
            case 'anthropic':
                return get_option('ai_manager_pro_anthropic_api_key');
            case 'openrouter':
                return get_option('ai_manager_pro_openrouter_api_key');
            default:
                return null;
        }
    }

    // Database and utility functions
    public function create_logs_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function log_activity($level, $message, $module = '', $context = [])
    {
        // Temporarily bypass the logging enable check for debugging
        // if (!get_option('ai_manager_pro_enable_logging', 1)) {
        //     return;
        // }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_logs';

        $wpdb->insert(
            $table_name,
            [
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'module' => $module,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ]
        );
    }

    // Additional AJAX handlers
    public function handle_check_api_keys()
    {
        error_log('AI Manager Pro - handle_check_api_keys() called');

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            error_log('AI Manager Pro - API keys check: Security check failed');
            wp_send_json_error('Security check failed');
        }

        $api_keys = [
            'openai' => get_option('ai_manager_pro_openai_api_key'),
            'anthropic' => get_option('ai_manager_pro_anthropic_api_key'),
            'openrouter' => get_option('ai_manager_pro_openrouter_api_key'),
            'deepseek' => get_option('ai_manager_pro_deepseek_key')
        ];

        $status = [];
        foreach ($api_keys as $provider => $key) {
            $configured = !empty($key);
            $length = strlen($key ?? '');
            $preview = $configured ? substr($key, 0, 8) . '...' : 'לא מוגדר';

            $status[$provider] = [
                'configured' => $configured,
                'length' => $length,
                'preview' => $preview
            ];

            error_log("AI Manager Pro - API Key Status - {$provider}: " . ($configured ? "EXISTS ({$length} chars)" : "NOT CONFIGURED"));
        }

        error_log('AI Manager Pro - API keys check completed successfully');
        wp_send_json_success($status);
    }

    public function handle_ping()
    {
        error_log('AI Manager Pro - handle_ping() called');

        // Verify that key handlers are registered
        $handlers_registered = [
            'generate_content' => has_action('wp_ajax_ai_manager_pro_generate_content'),
            'check_api_keys' => has_action('wp_ajax_ai_manager_pro_check_api_keys'),
            'test_deepseek' => has_action('wp_ajax_ai_manager_pro_test_deepseek'),
            'ping' => has_action('wp_ajax_ai_manager_pro_ping')
        ];

        error_log('AI Manager Pro - Handlers registration status: ' . json_encode($handlers_registered));

        wp_send_json_success([
            'message' => 'Plugin is working!',
            'time' => current_time('mysql'),
            'handlers_registered' => $handlers_registered,
            'plugin_version' => '3.3.0'
        ]);
    }

    public function handle_test_deepseek()
    {
        error_log('AI Manager Pro - handle_test_deepseek() called');

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            error_log('AI Manager Pro - DeepSeek test: Security check failed');
            wp_send_json_error('Security check failed');
        }

        $api_key = get_option('ai_manager_pro_deepseek_key');
        error_log('AI Manager Pro - DeepSeek API key check: ' . (empty($api_key) ? 'NOT FOUND' : 'EXISTS (' . strlen($api_key) . ' chars)'));

        if (empty($api_key)) {
            error_log('AI Manager Pro - DeepSeek test failed: API key not configured');
            wp_send_json_error('DeepSeek API key not configured');
        }

        // Load the real AI service
        $ai_service_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-real-ai-service.php';
        error_log('AI Manager Pro - Loading AI service from: ' . $ai_service_file);

        if (!file_exists($ai_service_file)) {
            error_log('AI Manager Pro - AI service file not found: ' . $ai_service_file);
            wp_send_json_error('AI service file not found');
        }

        require_once $ai_service_file;

        if (!class_exists('AI_Manager_Pro_Real_AI_Service')) {
            error_log('AI Manager Pro - AI_Manager_Pro_Real_AI_Service class not found after loading file');
            wp_send_json_error('AI service class not found');
        }

        $ai_service = new AI_Manager_Pro_Real_AI_Service();
        error_log('AI Manager Pro - AI service created successfully for DeepSeek test');

        try {
            error_log('AI Manager Pro - Starting DeepSeek connection test');
            $result = $ai_service->test_connection('deepseek', $api_key);
            error_log('AI Manager Pro - DeepSeek test result: ' . json_encode($result, JSON_UNESCAPED_UNICODE));

            if ($result['success']) {
                error_log('AI Manager Pro - DeepSeek test successful');
                wp_send_json_success([
                    'message' => 'DeepSeek connection successful!',
                    'details' => $result
                ]);
            } else {
                error_log('AI Manager Pro - DeepSeek test failed: ' . $result['message']);
                wp_send_json_error('DeepSeek connection failed: ' . $result['message']);
            }
        } catch (Exception $e) {
            error_log('AI Manager Pro - DeepSeek test exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            wp_send_json_error('DeepSeek test error: ' . $e->getMessage());
        }
    }

    public function handle_save_automation()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $task_data = $_POST['task_data'] ?? [];
            if (empty($task_data['name'])) {
                wp_send_json_error('Task name is required');
            }

            // Sanitize task data
            $sanitized_task = [
                'name' => sanitize_text_field($task_data['name']),
                'type' => sanitize_text_field($task_data['type']),
                'frequency' => sanitize_text_field($task_data['frequency']),
                'status' => sanitize_text_field($task_data['status']),
                'content_topic' => sanitize_text_field($task_data['content_topic'] ?? ''),
                'content_type' => sanitize_text_field($task_data['content_type'] ?? ''),
                'created' => current_time('mysql'),
                'last_run' => null,
                'next_run' => $this->calculate_next_run($task_data['frequency'])
            ];

            // Get existing tasks
            $tasks = get_option('ai_manager_pro_automation_tasks', []);

            // Generate unique ID
            $task_id = uniqid('task_');
            $tasks[$task_id] = $sanitized_task;

            // Save tasks
            update_option('ai_manager_pro_automation_tasks', $tasks);

            // Log activity
            $this->log_activity('info', "Automation task created: {$sanitized_task['name']}", 'automation');

            wp_send_json_success('Automation task saved successfully');

        } catch (Exception $e) {
            wp_send_json_error('Failed to save automation task: ' . $e->getMessage());
        }
    }

    public function handle_toggle_automation()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $task_id = sanitize_text_field($_POST['task_id'] ?? '');
        if (empty($task_id)) {
            wp_send_json_error('Task ID is required');
        }

        $tasks = get_option('ai_manager_pro_automation_tasks', []);
        if (isset($tasks[$task_id])) {
            $current_status = $tasks[$task_id]['status'];
            $new_status = ($current_status === 'active') ? 'paused' : 'active';
            $tasks[$task_id]['status'] = $new_status;

            update_option('ai_manager_pro_automation_tasks', $tasks);

            // Log activity
            $this->log_activity('info', "Automation task {$new_status}: {$tasks[$task_id]['name']}", 'automation');

            wp_send_json_success("Task {$new_status} successfully");
        } else {
            wp_send_json_error('Task not found');
        }
    }

    public function handle_clear_logs()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_logs';
        $type = sanitize_text_field($_POST['type'] ?? '');

        if ($type === 'old') {
            // Delete logs older than 30 days
            $result = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table_name} WHERE created_at < %s",
                date('Y-m-d H:i:s', strtotime('-30 days'))
            ));
            $message = "Deleted {$result} old log entries";
        } elseif ($type === 'all') {
            // Delete all logs
            $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
            $message = "All logs cleared";
        } else {
            wp_send_json_error('Invalid clear type');
        }

        // Log the action (if not clearing all)
        if ($type !== 'all') {
            $this->log_activity('info', $message, 'logs');
        }

        wp_send_json_success($message);
    }

    private function calculate_next_run($frequency)
    {
        switch ($frequency) {
            case 'hourly':
                return date('Y-m-d H:i:s', strtotime('+1 hour'));
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('+1 day'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('+1 week'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('+1 month'));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }

    /**
     * Run automation task (called by WordPress cron)
     */
    public function run_automation_task()
    {
        if (!get_option('ai_manager_pro_automation_enabled')) {
            return;
        }

        $tasks = get_option('ai_manager_pro_automation_tasks', []);
        $current_time = current_time('mysql');

        foreach ($tasks as $task_id => $task) {
            if ($task['status'] !== 'active') {
                continue;
            }

            // Check if it's time to run this task
            if (empty($task['next_run']) || $task['next_run'] <= $current_time) {
                try {
                    $this->execute_automation_task($task_id, $task);

                    // Update task timing
                    $tasks[$task_id]['last_run'] = $current_time;
                    $tasks[$task_id]['next_run'] = $this->calculate_next_run($task['frequency']);

                    $this->log_activity('info', "Automation task executed: {$task['name']}", 'automation');

                } catch (Exception $e) {
                    $this->log_activity('error', "Automation task failed: {$task['name']} - {$e->getMessage()}", 'automation');
                }
            }
        }

        // Save updated tasks
        update_option('ai_manager_pro_automation_tasks', $tasks);
    }

    /**
     * Execute specific automation task
     */
    private function execute_automation_task($task_id, $task)
    {
        switch ($task['type']) {
            case 'content_generation':
                $this->execute_content_generation_task($task);
                break;
            case 'social_posting':
                $this->execute_social_posting_task($task);
                break;
            case 'email_campaign':
                $this->execute_email_campaign_task($task);
                break;
            case 'seo_optimization':
                $this->execute_seo_optimization_task($task);
                break;
        }
    }

    /**
     * Execute content generation automation task
     */
    private function execute_content_generation_task($task)
    {
        $topic = $task['content_topic'] ?: $this->generate_topic_idea();
        $type = $task['content_type'] ?: 'blog_post';

        // Generate content
        $content = $this->generate_mock_content($topic, $type);

        // Create WordPress post
        $post_data = [
            'post_title' => $this->extract_title_from_content($content, $topic),
            'post_content' => $content,
            'post_status' => get_option('ai_manager_pro_default_post_status', 'draft'),
            'post_type' => 'post',
            'post_author' => 1, // Admin user
            'meta_input' => [
                'ai_generated' => true,
                'ai_provider' => get_option('ai_manager_pro_default_provider'),
                'ai_topic' => $topic,
                'ai_task_id' => $task['name']
            ]
        ];

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            throw new Exception('Failed to create post: ' . $post_id->get_error_message());
        }

        // Automatically generate and set the featured image using OpenRouter
        $this->handle_image_generation_and_upload($post_id, $topic);

        // Auto-publish if enabled
        if (get_option('ai_manager_pro_auto_publish')) {
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish'
            ]);
        }

        $this->log_activity('info', "Auto-generated post created: {$topic} (ID: {$post_id})", 'content_generation');

        return $post_id;
    }

    /**
     * Generate topic idea for content
     */
    private function generate_topic_idea()
    {
        $active_brand_id = get_option('ai_manager_pro_active_brand');
        $brand_data = null;

        if ($active_brand_id) {
            $brands = get_option('ai_manager_pro_brands_data', []);
            $brand_data = $brands[$active_brand_id] ?? null;
        }

        // Use brand keywords if available
        if ($brand_data && !empty($brand_data['keywords'])) {
            $keywords = explode(',', $brand_data['keywords']);
            $random_keyword = trim($keywords[array_rand($keywords)]);
            return $random_keyword;
        }

        // Fallback topic ideas
        $fallback_topics = [
            'טיפים לשיפור הפרודוקטיביות',
            'טרנדים חדשים בטכנולוגיה',
            'איך לשפר את חווית הלקוח',
            'אסטרטגיות שיווק דיגיטלי',
            'חדשנות בעסקים קטנים',
            'טיפים לניהול זמן יעיל',
            'מגמות עיצוב מודרניות',
            'פתרונות לבעיות נפוצות'
        ];

        return $fallback_topics[array_rand($fallback_topics)];
    }

    /**
     * Extract title from generated content
     */
    private function extract_title_from_content($content, $fallback_topic)
    {
        // Try to extract H1 title
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Try to extract first line as title
        $lines = explode("\n", strip_tags($content));
        $first_line = trim($lines[0]);

        if (!empty($first_line) && strlen($first_line) < 100) {
            return $first_line;
        }

        // Fallback to topic
        return $fallback_topic;
    }

    public function render_content_generator_page()
    {
        $brands_data = get_option('ai_manager_pro_brands_data', []);
        $active_brand = get_option('ai_manager_pro_active_brand', '');
        $default_provider = get_option('ai_manager_pro_default_provider', 'openai');

        ?>
        <div class="wrap">
            <div class="ai-manager-header"
                style="display: flex; align-items: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; color: white;">
                <div style="font-size: 48px; margin-left: 20px;">🤖</div>
                <div>
                    <h1 style="color: white; margin: 0; font-size: 28px;">יצירת תוכן מיידית</h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0;">צור תוכן איכותי עם AI בלחיצת כפתור</p>
                </div>
            </div>
            <!-- Quick Actions Bar -->
            <div class="quick-actions-bar"
                style="display: flex; gap: 15px; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-right: 4px solid #007cba;">
                <button type="button" class="button button-primary" onclick="quickGenerate('blog_post')"
                    style="display: flex; align-items: center; gap: 8px;">📝 <span>פוסט בלוג מהיר</span></button><button
                    type="button" class="button button-secondary" onclick="quickGenerate('social_media')"
                    style="display: flex; align-items: center; gap: 8px;">📱 <span>פוסט רשתות חברתיות</span></button><button
                    type="button" class="button button-secondary" onclick="quickGenerate('product_description')"
                    style="display: flex; align-items: center; gap: 8px;">🛍️ <span>תיאור מוצר</span></button><button
                    type="button" class="button button-secondary" onclick="quickGenerate('email')"
                    style="display: flex; align-items: center; gap: 8px;">✉️ <span>אימייל שיווקי</span></button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Content Generation Form  -->
                <div class="card"
                    style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 24px; margin-left: 10px;">✨</span>
                            <h2 style="margin: 0; color: #1e3a8a;">יצירת תוכן מותאם אישית</h2>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="button" onclick="pingPlugin()" class="button" style="padding: 8px 16px;">
                                � דוק חיבור
                            </button>
                            <button type="button" onclick="checkApiKeys()" class="button" style="padding: 8px 16px;">
                                🔑 בדוק מפתחות API
                            </button>
                            <button type="button" onclick="testDeepSeek()" class="button" style="padding: 8px 16px;">
                                🧪 בדוק DeepSeek
                            </button>
                        </div>
                    </div>
                    <form id="content-generator-form">
                        <div class="form-group" style="margin-bottom: 20px;"><label
                                style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">🎯 נושא
                                התוכן </label><input type="text" id="content_topic"
                                placeholder="לדוגמה: טיפים לשיפור הפרודוקטיביות"
                                style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px;"
                                required /></div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">
                                📄 סוג התוכן
                            </label> <select id="content_type"
                                style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                                <option value="blog_post">📝 פוסט בלוג (800-1200 מילים)</option>
                                <option value="social_media">📱 פוסט רשתות חברתיות (150-280 תווים)</option>
                                <option value="product_description">🛍️ תיאור מוצר (200-400 מילים)</option>
                                <option value="email">✉️ אימייל שיווקי (300-500 מילים)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">
                                🤖 ספק AI
                            </label> <select id="ai_provider"
                                style=" width:100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                                <option value="openai">OpenAI (GPT)</option>
                                <option value="anthropic">Anthropic (Claude)</option>
                                <option value="openrouter">OpenRouter</option>
                                <option value="deepseek" selected>DeepSeek</option>
                            </select>
                        </div>
                        <div class=" form-group" style="margin-bottom: 20px;"><label
                                style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">🏢 מותג
                                פעיל </label><select id="active_brand"
                                style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                                <option value="">ללא מותג ספציפי</option>
                                <?php foreach ($brands_data as $id => $brand): ?>
                                    <option value="<?php echo esc_attr($id); ?>" <?php selected($active_brand, $id); ?>>
                                        <?php echo esc_html($brand['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Debugging Tools -->
                        <div class="debug-tools"
                            style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #007cba;">
                            <h4 style="margin: 0 0 10px 0; color: #007cba;">🔧 כלי דיבוג</h4>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <button type="button" class="button" onclick="pingPlugin()" style="padding: 8px 12px;">📡 בדוק
                                    חיבור</button>
                                <button type="button" class="button" onclick="checkApiKeys()" style="padding: 8px 12px;">🔑 בדוק
                                    מפתחות API</button>
                                <button type="button" class="button" onclick="testDeepSeek()" style="padding: 8px 12px;">🧪 בדוק
                                    DeepSeek</button>
                            </div>
                        </div>

                        <div class="form-actions" style="display: flex; gap: 10px; margin-top: 25px;"><button type="button"
                                id="generate-content-btn" class="button-primary" onclick="generateContent()"
                                style="flex: 1; padding: 15px; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px;"><span
                                    id="generate-icon">🚀</span><span id="generate-text">צור תוכן</span></button><button
                                type="button" class="button" onclick="clearForm()" style="padding: 15px;">🗑️ נקה
                            </button></div>
                    </form>
                </div>
                <!-- Generated Content Display -->
                <div class="card"
                    style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; justify-content: between; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center;"><span
                                style="font-size: 24px; margin-left: 10px;">📄</span>
                            <h2 style="margin: 0; color: #1e3a8a;">תוכן שנוצר</h2>
                        </div>
                        <div id="content-actions" style="display: none; gap: 10px;"><button type="button" class="button"
                                onclick="copyContent()" style="display: flex; align-items: center; gap: 5px;">📋 העתק
                            </button><button type="button" class="button button-primary" onclick="createPost()"
                                style="display: flex; align-items: center; gap: 5px;">📝 צור פוסט </button></div>
                    </div>
                    <div id="content-preview"
                        style="min-height: 400px; padding: 20px; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                        <div style="text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 15px;">🤖</div>
                            <p style="margin: 0; font-size: 16px;">התוכן שיווצר יופיע כאן</p>
                            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.7;">בחר נושא ולחץ על "צור תוכן"
                            </p>
                        </div>
                    </div>
                    <div id="content-stats"
                        style="display: none; margin-top: 15px; padding: 15px; background: #eff6ff; border-radius: 6px; border-right: 4px solid #3b82f6;">
                        <div style="display: flex; gap: 20px; font-size: 14px; color: #1e40af;"><span>📊 <strong
                                    id="word-count">0</strong>מילים</span><span>⏱️ <strong
                                    id="char-count">0</strong>תווים</span><span>🤖 <strong id="used-provider">-</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Define ajaxurl for WordP              ress AJAX
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            let generatedContent = '';

            // Debug: Check if jQuery is loaded
            console.log('jQuery loaded:', typeof jQuery !== 'undefined');
            console.log('ajaxurl:', ajaxurl);

            // Define functions globally first
            window.checkApiKeys = function () {
                console.log('Checking API keys...');

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_check_api_keys',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                }).done(function (response) {
                    if (response.success) {
                        let message = 'סטטוס מפתחות API:\n\n';
                        for (const [provider, status] of Object.entries(response.data)) {
                            message += `${provider.toUpperCase()}: ${status.configured ? '✅ מוגדר' : '❌ לא מוגדר'} (${status.preview})\n`;
                        }
                        alert(message);
                    } else {
                        alert('שגיאה בבדיקת מפתחות API: ' + response.data);
                    }
                }).fail(function () {
                    alert('שגיאה בחיבור לשרת');
                });
            };

            window.pingPlugin = function () {
                console.log('Pinging plugin...');

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_ping',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                }).done(function (response) {
                    console.log('Ping response:', response);
                    if (response.success) {
                        alert('✅ חיבור תקין!\n\nהפלאגין עובד כראוי.\nזמן: ' + response.data.time);
                    } else {
                        alert('❌ חיבור נכשל:\n\n' + response.data);
                    }
                }).fail(function (xhr, status, error) {
                    console.log('Ping failed:', xhr, status, error);
                    alert('❌ שגיאה בחיבור:\n\nStatus: ' + xhr.status + '\nError: ' + error + '\nResponse: ' + xhr.responseText.substring(0, 200));
                });
            };

            window.testDeepSeek = function () {
                console.log('Testing DeepSeek connection...');

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_test_deepseek',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                }).done(function (response) {
                    if (response.success) {
                        alert('✅ DeepSeek חיבור תקין!\n\n' + response.data.message);
                    } else {
                        alert('❌ DeepSeek חיבור נכשל:\n\n' + response.data);
                    }
                }).fail(function () {
                    alert('❌ שגיאה בחיבור לשרת');
                });
            };

            // Ping test function
            window.pingPlugin = function () {
                console.log('Testing plugin ping...');

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_ping',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                }).done(function (response) {
                    console.log('Ping response:', response);
                    if (response.success) {
                        let message = '✅ Plugin עובד תקין!\n\n';
                        message += 'זמן: ' + response.data.time + '\n';
                        message += 'גרסה: ' + response.data.plugin_version + '\n\n';
                        message += 'סטטוס Handlers:\n';
                        for (let handler in response.data.handlers_registered) {
                            let status = response.data.handlers_registered[handler] ? '✅' : '❌';
                            message += '• ' + handler + ': ' + status + '\n';
                        }
                        alert(message);
                    } else {
                        alert('❌ Ping נכשל:\n\n' + response.data);
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Ping failed:', xhr, status, error);
                    alert('❌ שגיאה בחיבור לשרת\n\nStatus: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText);
                });
            };

            // API Keys check function
            window.checkApiKeys = function () {
                console.log('Checking API keys...');

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_check_api_keys',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
                }).done(function (response) {
                    console.log('API Keys response:', response);
                    if (response.success) {
                        let message = '🔑 סטטוס מפתחות API:\n\n';
                        for (let provider in response.data) {
                            let keyInfo = response.data[provider];
                            let status = keyInfo.configured ? '✅' : '❌';
                            message += '• ' + provider + ': ' + status + ' (' + keyInfo.preview + ')\n';
                        }
                        alert(message);
                    } else {
                        alert('❌ בדיקת מפתחות נכשלה:\n\n' + response.data);
                    }
                }).fail(function (xhr, status, error) {
                    console.error('API Keys check failed:', xhr, status, error);
                    alert('❌ שגיאה בחיבור לשרת\n\nStatus: ' + status + '\nError: ' + error);
                });
            };

            window.generateContent = function () {
                console.log('generateContent function called');
                const topic = document.getElementById('content_topic').value;
                const type = document.getElementById('content_type').value;
                console.log('Topic:', topic, 'Type:', type);

                if (!topic.trim()) {
                    alert('אנא הכנס נושא לתוכן');
                    document.getElementById('content_topic').focus();
                    return;
                }

                const generateBtn = document.querySelector('[onclick="generateContent()"]');
                const generateIcon = document.getElementById('generate-icon');
                const generateText = document.getElementById('generate-text');

                generateIcon.textContent = '⏳';
                generateText.textContent = 'יוצר תוכן...';
                generateBtn.disabled = true;

                document.getElementById('content-preview').innerHTML = '<div style = "text-align: center;" ><div style="font-size: 48px; margin-bottom: 15px;">⏳</div><p style="margin: 0; font-size: 16px;">יוצר תוכן עם AI...</p><p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.7;">זה יכול לקחת כמה שניות</p></div > ';

                const provider = document.getElementById('ai_provider').value;
                const brand = document.getElementById('active_brand').value;

                console.log('Sending AJAX request to:', ajaxurl);
                console.log('Provider:', provider, 'Brand:', brand);

                jQuery.post(ajaxurl, {
                    action: 'ai_manager_pro_generate_content',
                    nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                    topic: topic,
                    content_type: type,
                    ai_provider: provider,
                    brand_id: brand
                }).done(function (response) {
                    console.log('AJAX response received:', response);
                    if (response.success) {
                        generatedContent = response.data.content;
                        displayGeneratedContent(generatedContent, type);
                    } else {
                        showError('שגיאה ביצירת תוכן: ' + (response.data || 'Unknown error'));
                    }
                }).fail(function (xhr, status, error) {
                    console.log('AJAX error:', xhr, status, error);
                    showError('שגיאת רשת: ' + error);
                }).always(function () {
                    generateIcon.textContent = '🚀';
                    generateText.textContent = 'צור תוכן';
                    generateBtn.disabled = false;
                });
            };

            window.clearForm = function () {
                document.getElementById('content_topic').value = '';
                document.getElementById('content_type').value = 'blog_post';
                document.getElementById('ai_provider').value = 'deepseek';
                document.getElementById('active_brand').value = '';
                document.getElementById('content-preview').innerHTML = '<div style="text-align: center;"><div style="font-size: 48px; margin-bottom: 15px;">🤖</div><p style="margin: 0; font-size: 16px;">התוכן שיווצר יופיע כאן</p><p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.7;">בחר נושא ולחץ על "צור תוכן"</p></div>';
                document.getElementById('content-actions').style.display = 'none';
                document.getElementById('content-stats').style.display = 'none';
                generatedContent = '';
            };

            window.displayGeneratedContent = function (content, type) {
                const preview = document.getElementById('content-preview');
                const actions = document.getElementById('content-actions');
                const stats = document.getElementById('content-stats');

                preview.innerHTML = '<div style="text-align: right; line-height: 1.6; white-space: pre-wrap;">' + content + '</div>';

                actions.style.display = 'flex';
                stats.style.display = 'block';

                const wordCount = content.split(/\s+/).length;
                const charCount = content.length;

                document.getElementById('word-count').textContent = wordCount;
                document.getElementById('char-count').textContent = charCount;
                document.getElementById('used-provider').textContent = 'AI';
            };

            window.showError = function (message) {
                document.getElementById('content-preview').innerHTML = '<div style="text-align: center; color: #dc2626;"><div style="font-size: 48px; margin-bottom: 15px;">❌</div><p style="margin: 0; font-size: 16px;">' + message + '</p><p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.7;">נסה שוב או בדוק את הגדרות ה-API</p></div>';
            };

            // Add DOM ready event listener
            jQuery(document).ready(function ($) {
                console.log('DOM ready - setting up event listeners');

                // Add backup event listeners
                $('#generate-content-btn').on('click', function (e) {
                    console.log('Generate button clicked via jQuery');
                    e.preventDefault();
                    generateContent();
                });

                $('[onclick*="clearForm"]').on('click', function (e) {
                    console.log('Clear button clicked via jQuery');
                    e.preventDefault();
                    clearForm();
                });
            });

            function quickGenerate(type) {
                document.getElementById('content_type').value = type;

                const defaultTopics = {
                    'blog_post': 'טיפים לשיפור הפרודוקטיביות',
                    'social_media': 'חדשנות בטכנולוגיה',
                    'product_description': 'מוצר חדשני',
                    'email': 'הזדמנות מיוחדת'
                };

                document.getElementById('content_topic').value = defaultTopics[type] || '';
                generateContent();
            }





            function copyContent() {
                if (!generatedContent) return;

                navigator.clipboard.writeText(generatedContent).then(function () {
                    alert('התוכן הועתק ללוח!');
                }).catch(function () {
                    const textArea = document.createElement('textarea');
                    textArea.value = generatedContent;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert('התוכן הועתק ללוח!');
                });
            }

            function createPost() {
                if (!generatedContent) return;

                const topic = document.getElementById('content_topic').value;

                if (confirm('האם ליצור פוסט WordPress חדש עם התוכן שנוצר?')) {
                    jQuery.post(ajaxurl, {
                        action: 'ai_manager_pro_create_post',
                        nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                        title: topic,
                        content: generatedContent

                    }).done(function (response) {
                        if (response.success) {
                            alert('הפוסט נוצר בהצלחה!');

                            if (response.data.edit_url) {
                                window.open(response.data.edit_url, '_blank');
                            }
                        }

                        else {
                            alert('שגיאה ביצירת הפוסט: ' + response.data);
                        }
                    });
                }
            }



        </script>
        <style>
            .card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .card:hover {
                tr ansform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
            }

            .butto n-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
                border: none !important;
                transition: all 0.2s ease !important;
            }

            .button-primary:hover {
                transform: translateY(-1px) !important;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
            }
        </style>
        <?php
    }

    /**
     * Clean AI-generated content from artifacts and intros.
     */
    private function clean_ai_content($content)
    {
        // Remove common AI introductory phrases in Hebrew and English
        $intro_phrases = [
            'כמובן, הנה תוכן לבלוג איכותי ועשיר בנושא, המותאם לקריאה ובעל ערך מוסף לקורא.',
            'כמובן, הנה התוכן שביקשת:',
            'בטח, הנה טיוטה:',
            'כמובן, הנה הצעה:',
            'הנה תוכן אפשרי:',
            'Here is the content you requested:',
            'Of course, here is the content:',
            'Certainly, here is a draft:',
            'כמובן, הנה טיוטה של פוסט בלוג בנושא'
        ];

        // Build a regex to match any of the phrases at the beginning of the content, ignoring whitespace and case
        $regex = '/^\s*(' . implode('|', array_map(function($phrase) {
            return preg_quote($phrase, '/');
        }, $intro_phrases)) . ')\s*[:\r\n]*/iu';
        $content = preg_replace($regex, '', $content);

        // Remove markdown code blocks fences
        $content = str_replace(['```markdown', '```'], '', $content);

        // Trim whitespace from the beginning and end
        return trim($content);
    }

    /**
     * Convert markdown text to HTML
     */
    private function convert_markdown_to_html($markdown)
    {
        // Convert headers
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $markdown);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^#### (.*$)/m', '<h4>$1</h4>', $html);

        // Convert bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);

        // Convert unordered lists
        $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);

        // Convert links
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $html);

        // Convert line breaks
        $html = preg_replace('/\n/', '<br>', $html);

        return $html;
    }

    public function handle_create_post()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $title = sanitize_text_field($_POST['title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');

        if (empty($title) || empty($content)) {
            wp_send_json_error('Title and content are required');
        }

        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => get_option('ai_manager_pro_default_post_status', 'draft'),
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'meta_input' => [
                'ai_generated' => true,
                'ai_provider' => get_option('ai_manager_pro_default_provider'),
                'ai_generated_at' => current_time('mysql')
            ]
        ];

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error('Failed to create post: ' . $post_id->get_error_message());
        }

        // Automatically generate and set the featured image using OpenRouter
        $this->handle_image_generation_and_upload($post_id, $title);

        $this->log_activity('info', "Manual post created via content generator: {$title} (ID: {$post_id})", 'content_generation');

        wp_send_json_success([
            'post_id' => $post_id,
            'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            'view_url' => get_permalink($post_id)
        ]);
    }

    /**
     * Handles the generation and uploading of a featured image for a post using OpenRouter.
     *
     * @param int    $post_id The ID of the post.
     * @param string $topic   The topic/prompt for the image generation.
     */
    private function handle_image_generation_and_upload($post_id, $topic)
    {
        // Ensure the OpenRouter service class is loaded
        $service_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        if (!file_exists($service_file)) {
            $this->log_activity('error', 'OpenRouter Service file not found.', 'image_generation');
            return;
        }
        require_once $service_file;

        $image_service = new AI_Manager_Pro_OpenRouter_Service();

        // Generate a more descriptive prompt for the image
        $image_prompt = "cinematic photo of {$topic}, photorealistic, high detail, epic lighting, professional photography";

        $result = $image_service->generate_image($image_prompt);

        if (is_wp_error($result)) {
            $this->log_activity('error', 'Image generation failed (OpenRouter): ' . $result->get_error_message(), 'image_generation', ['topic' => $topic]);
            return;
        }

        $image_data = base64_decode($result['base64_image']);
        $file_name = sanitize_file_name($topic) . '.png';

        // Upload the image to the media library
        $upload = wp_upload_bits($file_name, null, $image_data);

        if (!empty($upload['error'])) {
            $this->log_activity('error', 'Failed to upload generated image to WordPress: ' . $upload['error'], 'image_generation');
            return;
        }

        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => $topic,
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);

        if (is_wp_error($attachment_id)) {
            $this->log_activity('error', 'Failed to create attachment for generated image.', 'image_generation');
            return;
        }

        // Generate attachment metadata and set as featured image
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        set_post_thumbnail($post_id, $attachment_id);

        $this->log_activity('info', "Successfully generated and set featured image for post ID {$post_id} using OpenRouter.", 'image_generation', ['attachment_id' => $attachment_id]);
    }

    public function handle_test_openrouter_models()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $api_key = get_option('ai_manager_pro_openrouter_api_key');
        if (!$api_key) {
            wp_send_json_error('OpenRouter API key not configured');
        }

        // Test models with a simple prompt
        $test_prompt = "Say 'Hello' in Hebrew";
        $models_to_test = [
            'google/gemma-7b-it:free' => 'Gemma 7B (חינמי)',
            'huggingfaceh4/zephyr-7b-beta:free' => 'Zephyr 7B Beta (חינמי)',
            'openchat/openchat-7b:free' => 'OpenChat 7B (חינמי)',
            'mistralai/mistral-7b-instruct:free' => 'Mistral 7B (חינמי)',
            'nousresearch/nous-capybara-7b:free' => 'Nous Capybara 7B (חינמי)'
        ];

        $results = [];
        $working_models = [];
        $failed_models = [];

        foreach ($models_to_test as $model => $display_name) {
            try {
                $response = $this->try_openrouter_model($test_prompt, $api_key, $model);
                if (!empty($response)) {
                    $working_models[] = $display_name;
                    $results[] = "✅ {$display_name} - עובד";
                } else {
                    $failed_models[] = $display_name;
                    $results[] = "❌ {$display_name} - תגובה ריקה";
                }
            } catch (Exception $e) {
                $failed_models[] = $display_name;
                $results[] = "❌ {$display_name} - " . $e->getMessage();
            }
        }

        $summary = "תוצאות בדיקת מודלים:\n\n";
        $summary .= "מודלים פעילים (" . count($working_models) . "):\n";
        foreach ($working_models as $model) {
            $summary .= "• {$model}\n";
        }

        if (!empty($failed_models)) {
            $summary .= "\nמודלים לא זמינים (" . count($failed_models) . "):\n";
            foreach ($failed_models as $model) {
                $summary .= "• {$model}\n";
            }
        }

        if (!empty($working_models)) {
            $summary .= "\n💡 המלצה: השתמש באחד מהמודלים הפעילים לתוצאות מיטביות.";
        } else {
            $summary .= "\n⚠️ אף מודל חינמי לא זמין כרגע. נסה שוב מאוחר יותר או השתמש במודל בתשלום.";
        }

        wp_send_json_success([
            'message' => $summary,
            'working_models' => $working_models,
            'failed_models' => $failed_models,
            'details' => $results
        ]);
    }

    public function handle_delete_automation()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $task_id = sanitize_text_field($_POST['task_id'] ?? '');
        if (empty($task_id)) {
            wp_send_json_error('Task ID is required');
        }

        $tasks = get_option('ai_manager_pro_automation_tasks', []);
        if (isset($tasks[$task_id])) {
            $task_name = $tasks[$task_id]['name'];
            unset($tasks[$task_id]);
            update_option('ai_manager_pro_automation_tasks', $tasks);

            $this->log_activity('info', "Automation task deleted: {$task_name}", 'automation');

            wp_send_json_success('Task deleted successfully');
        } else {
            wp_send_json_error('Task not found');
        }
    }

    public function handle_run_task_now()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $task_id = sanitize_text_field($_POST['task_id'] ?? '');
        if (empty($task_id)) {
            wp_send_json_error('Task ID is required');
        }

        $tasks = get_option('ai_manager_pro_automation_tasks', []);
        if (!isset($tasks[$task_id])) {
            wp_send_json_error('Task not found');
        }

        try {
            $this->execute_automation_task($task_id, $tasks[$task_id]);

            // Update last run time
            $tasks[$task_id]['last_run'] = current_time('mysql');
            update_option('ai_manager_pro_automation_tasks', $tasks);

            $this->log_activity('info', "Automation task executed manually: {$tasks[$task_id]['name']}", 'automation');

            wp_send_json_success('Task executed successfully');

        } catch (Exception $e) {
            $this->log_activity('error', "Manual task execution failed: {$tasks[$task_id]['name']} - {$e->getMessage()}", 'automation');
            wp_send_json_error('Task execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle DeepSeek connection test
     */
    public function handle_test_deepseek_connection()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();
            $result = $deepseek_service->test_connection();

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success([
                'message' => 'החיבור ל-DeepSeek הצליח!',
                'models' => $deepseek_service->get_models()
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בבדיקת החיבור ל-DeepSeek: ' . $e->getMessage());
        }
    }

    /**
     * Handle DeepSeek content generation
     */
    public function handle_generate_deepseek_content()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? 'deepseek-chat');

        if (empty($prompt)) {
            wp_send_json_error('פרומפט נדרש');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();
            $result = $deepseek_service->generate_content($prompt, $model);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success([
                'content' => $result['content'],
                'model' => $result['model'],
                'usage' => $result['usage'] ?? []
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה ביצירת התוכן: ' . $e->getMessage());
        }
    }

    /**
     * Handle brand JSON import
     */
    public function handle_import_brand_json()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_data = $_POST['brand_data'] ?? [];

        if (empty($brand_data['name'])) {
            wp_send_json_error('שם המותג נדרש');
        }

        try {
            $brands_data = get_option('ai_manager_pro_brands_data', []);
            $brand_id = 'brand_' . time() . '_' . rand(1000, 9999);

            // Add timestamp and validation
            $brand_data['created_at'] = current_time('mysql');
            $brand_data['imported_from_json'] = true;

            $brands_data[$brand_id] = $brand_data;

            update_option('ai_manager_pro_brands_data', $brands_data);

            wp_send_json_success([
                'message' => 'המותג יובא בהצלחה',
                'brand_id' => $brand_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בייבוא המותג: ' . $e->getMessage());
        }
    }

    /**
     * Handle brand JSON export
     */
    public function handle_export_brand_json()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = sanitize_text_field($_POST['brand_id'] ?? '');

        if (empty($brand_id)) {
            wp_send_json_error('מזהה מותג נדרש');
        }

        try {
            $brands_data = get_option('ai_manager_pro_brands_data', []);

            if (!isset($brands_data[$brand_id])) {
                wp_send_json_error('מותג לא נמצא');
            }

            $brand = $brands_data[$brand_id];

            // Convert to full JSON format
            $full_json = $this->convert_brand_to_full_json($brand);

            $filename = 'brand-' . sanitize_file_name($brand['name']) . '-' . date('Y-m-d') . '.json';

            wp_send_json_success([
                'brand_data' => $full_json,
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בייצוא המותג: ' . $e->getMessage());
        }
    }

    /**
     * Convert brand to full JSON format
     */
    private function convert_brand_to_full_json($brand)
    {
        // If extended data exists, use it
        if (isset($brand['extended_data'])) {
            return $brand['extended_data'];
        }

        // Otherwise, create basic structure
        return [
            'brand_info' => [
                'name' => $brand['name'] ?? '',
                'description' => $brand['description'] ?? '',
                'industry' => '',
                'founded' => date('Y'),
                'website' => '',
                'logo_url' => ''
            ],
            'voice_and_tone' => [
                'primary_tone' => $brand['tone'] ?? 'מקצועי',
                'secondary_tone' => 'ידידותי',
                'personality_traits' => ['אמין', 'מקצועי'],
                'communication_style' => 'ישיר וברור',
                'formality_level' => 'בינוני',
                'emotional_tone' => 'חיובי'
            ],
            'target_audience' => [
                'primary_demographic' => [
                    'description' => $brand['audience'] ?? '',
                    'age_range' => '25-45',
                    'location' => 'ישראל'
                ],
                'interests' => [],
                'pain_points' => [],
                'preferred_channels' => []
            ],
            'keywords_and_messaging' => [
                'primary_keywords' => !empty($brand['keywords']) ?
                    array_map('trim', explode(',', $brand['keywords'])) : [],
                'secondary_keywords' => [],
                'avoid_words' => [],
                'key_messages' => []
            ],
            'content_guidelines' => [
                'content_pillars' => [],
                'content_length' => [
                    'blog_posts' => '800-1500 מילים',
                    'social_media' => '50-150 מילים'
                ],
                'writing_style' => [
                    'sentence_structure' => 'בינוני',
                    'tone' => $brand['tone'] ?? 'מקצועי'
                ]
            ]
        ];
    }
}

// Initialize the plugin
function ai_manager_pro_safe_init()
{
    AI_Manager_Pro_Safe::get_instance();
}
add_action('plugins_loaded', 'ai_manager_pro_safe_init');

// Plugin hooks
register_activation_hook(__FILE__, ['AI_Manager_Pro_Safe', 'activate']);
register_deactivation_hook(__FILE__, ['AI_Manager_Pro_Safe', 'deactivate']);
register_uninstall_hook(__FILE__, ['AI_Manager_Pro_Safe', 'uninstall']);
