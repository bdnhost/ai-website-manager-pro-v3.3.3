<?php
/**
 * Brand Intelligence Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * Brand Intelligence Manager Class
 *
 * Orchestrates all advanced brand intelligence features
 */
class Brand_Intelligence_Manager {

    /**
     * AI Topic Generator instance
     *
     * @var AI_Topic_Generator
     */
    private $topic_generator;

    /**
     * RSS Trending Detector instance
     *
     * @var RSS_Trending_Detector
     */
    private $trending_detector;

    /**
     * Performance Analytics instance
     *
     * @var Performance_Analytics
     */
    private $performance_analytics;

    /**
     * Content Gap Analyzer instance
     *
     * @var Content_Gap_Analyzer
     */
    private $gap_analyzer;

    /**
     * Logger instance
     *
     * @var mixed
     */
    private $logger;

    /**
     * Constructor
     *
     * @param mixed $logger Logger instance
     */
    public function __construct($logger = null) {
        $this->logger = $logger;

        // Initialize components
        $this->init_components();

        // Register hooks
        $this->init_hooks();
    }

    /**
     * Initialize components
     */
    private function init_components() {
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-ai-topic-generator.php';
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-rss-trending-detector.php';
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-performance-analytics.php';
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-content-gap-analyzer.php';

        $this->topic_generator = new AI_Topic_Generator(null, $this->logger);
        $this->trending_detector = new RSS_Trending_Detector($this->logger);
        $this->performance_analytics = new Performance_Analytics($this->logger);
        $this->gap_analyzer = new Content_Gap_Analyzer($this->logger);
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX endpoints
        add_action('wp_ajax_ai_manager_pro_generate_topics', [$this, 'handle_generate_topics']);
        add_action('wp_ajax_ai_manager_pro_detect_trends', [$this, 'handle_detect_trends']);
        add_action('wp_ajax_ai_manager_pro_update_performance', [$this, 'handle_update_performance']);
        add_action('wp_ajax_ai_manager_pro_analyze_gaps', [$this, 'handle_analyze_gaps']);
        add_action('wp_ajax_ai_manager_pro_get_performance_report', [$this, 'handle_get_performance_report']);

        // Scheduled tasks
        add_action('ai_manager_pro_monthly_topic_refresh', [$this, 'run_monthly_refresh']);
        add_action('ai_manager_pro_daily_trending_check', [$this, 'run_trending_check']);
        add_action('ai_manager_pro_weekly_performance_update', [$this, 'run_performance_update']);

        // Schedule events if not already scheduled
        if (!wp_next_scheduled('ai_manager_pro_monthly_topic_refresh')) {
            wp_schedule_event(time(), 'monthly', 'ai_manager_pro_monthly_topic_refresh');
        }

        if (!wp_next_scheduled('ai_manager_pro_daily_trending_check')) {
            wp_schedule_event(time(), 'daily', 'ai_manager_pro_daily_trending_check');
        }

        if (!wp_next_scheduled('ai_manager_pro_weekly_performance_update')) {
            wp_schedule_event(time(), 'weekly', 'ai_manager_pro_weekly_performance_update');
        }
    }

    /**
     * Handle AJAX: Generate topics with AI
     */
    public function handle_generate_topics() {
        check_ajax_referer('ai_manager_pro_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0;
        $count = isset($_POST['count']) ? (int) $_POST['count'] : 20;
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : null;
        $auto_add = isset($_POST['auto_add_to_pool']) ? (bool) $_POST['auto_add_to_pool'] : true;

        if (!$brand_id) {
            wp_send_json_error('Brand ID required');
        }

        // Get brand data
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brand = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $brand_id
        ));

        if (!$brand) {
            wp_send_json_error('Brand not found');
        }

        $brand_data = json_decode($brand->brand_data, true);

        // Generate topics
        $result = $this->topic_generator->generate_topics($brand_data, [
            'count' => $count,
            'category' => $category,
            'auto_add_to_pool' => $auto_add
        ]);

        if (!$result['success']) {
            wp_send_json_error($result['error']);
        }

        // Update brand if topics were added
        if ($auto_add) {
            $wpdb->update(
                $table_name,
                ['brand_data' => json_encode($result['brand_data'], JSON_UNESCAPED_UNICODE)],
                ['id' => $brand_id]
            );
        }

        wp_send_json_success([
            'topics' => $result['topics'],
            'count' => $result['count'],
            'message' => "Successfully generated {$result['count']} topics"
        ]);
    }

    /**
     * Handle AJAX: Detect trending topics
     */
    public function handle_detect_trends() {
        check_ajax_referer('ai_manager_pro_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0;
        $auto_add = isset($_POST['auto_add_to_pool']) ? (bool) $_POST['auto_add_to_pool'] : true;

        if (!$brand_id) {
            wp_send_json_error('Brand ID required');
        }

        $result = $this->trending_detector->process_trending_for_brand($brand_id, [
            'time_window' => '24h',
            'minimum_mentions' => 3,
            'auto_add_to_pool' => $auto_add
        ]);

        if (!$result['success']) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success([
            'trending_topics' => $result['trending_topics'],
            'topic_suggestions' => $result['topic_suggestions'],
            'count' => $result['count'],
            'message' => "Found {$result['count']} trending topics"
        ]);
    }

    /**
     * Handle AJAX: Update performance scores
     */
    public function handle_update_performance() {
        check_ajax_referer('ai_manager_pro_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0;

        if (!$brand_id) {
            wp_send_json_error('Brand ID required');
        }

        $result = $this->performance_analytics->update_topic_performance_scores($brand_id);

        if (!$result['success']) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success([
            'topics_updated' => $result['topics_updated'],
            'message' => "Updated performance scores for {$result['topics_updated']} topics"
        ]);
    }

    /**
     * Handle AJAX: Analyze content gaps
     */
    public function handle_analyze_gaps() {
        check_ajax_referer('ai_manager_pro_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0;

        if (!$brand_id) {
            wp_send_json_error('Brand ID required');
        }

        $result = $this->gap_analyzer->analyze_gaps($brand_id);

        if (!$result['success']) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success([
            'gaps' => $result['gaps'],
            'recommendations' => $result['recommendations'],
            'gap_count' => $result['gap_count'],
            'message' => "Found {$result['gap_count']} content gaps"
        ]);
    }

    /**
     * Handle AJAX: Get performance report
     */
    public function handle_get_performance_report() {
        check_ajax_referer('ai_manager_pro_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $brand_id = isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0;

        if (!$brand_id) {
            wp_send_json_error('Brand ID required');
        }

        $result = $this->performance_analytics->get_performance_report($brand_id);

        if (!$result['success']) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success([
            'report' => $result['report']
        ]);
    }

    /**
     * Run monthly topic refresh for all brands with auto_refresh enabled
     */
    public function run_monthly_refresh() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brands = $wpdb->get_results("SELECT * FROM {$table_name}");

        foreach ($brands as $brand) {
            $brand_data = json_decode($brand->brand_data, true);

            if (!empty($brand_data['topic_pool']['auto_refresh'])) {
                $this->topic_generator->refresh_brand_topics($brand->id, [
                    'count' => 10
                ]);

                if ($this->logger) {
                    $this->logger->info("Monthly topic refresh for brand: {$brand->name}");
                }
            }
        }
    }

    /**
     * Run daily trending check for all brands with trending enabled
     */
    public function run_trending_check() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brands = $wpdb->get_results("SELECT * FROM {$table_name}");

        foreach ($brands as $brand) {
            $brand_data = json_decode($brand->brand_data, true);

            if (!empty($brand_data['content_calendar']['trending_sources']['enabled'])) {
                $this->trending_detector->process_trending_for_brand($brand->id, [
                    'auto_add_to_pool' => true
                ]);

                if ($this->logger) {
                    $this->logger->info("Daily trending check for brand: {$brand->name}");
                }
            }
        }
    }

    /**
     * Run weekly performance update for all brands
     */
    public function run_performance_update() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brands = $wpdb->get_results("SELECT * FROM {$table_name}");

        foreach ($brands as $brand) {
            $this->performance_analytics->update_topic_performance_scores($brand->id);

            if ($this->logger) {
                $this->logger->info("Weekly performance update for brand: {$brand->name}");
            }
        }
    }

    /**
     * Get all AI features status for a brand
     *
     * @param int $brand_id Brand ID
     * @return array Features status
     */
    public function get_features_status($brand_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brand = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $brand_id
        ));

        if (!$brand) {
            return [
                'success' => false,
                'error' => 'Brand not found'
            ];
        }

        $brand_data = json_decode($brand->brand_data, true);

        return [
            'success' => true,
            'features' => [
                'topic_pool' => [
                    'enabled' => !empty($brand_data['topic_pool']['enabled']),
                    'auto_refresh' => !empty($brand_data['topic_pool']['auto_refresh']),
                    'total_topics' => $this->count_total_topics($brand_data)
                ],
                'content_calendar' => [
                    'enabled' => !empty($brand_data['content_calendar']['enabled']),
                    'strategy' => $brand_data['content_calendar']['strategy'] ?? 'mixed'
                ],
                'trending' => [
                    'enabled' => !empty($brand_data['content_calendar']['trending_sources']['enabled']),
                    'rss_feeds_count' => count($brand_data['content_calendar']['trending_sources']['rss_feeds'] ?? [])
                ],
                'performance_tracking' => [
                    'enabled' => true,  // Always available
                    'topics_with_scores' => $this->count_topics_with_scores($brand_data)
                ]
            ]
        ];
    }

    /**
     * Count total topics in brand
     *
     * @param array $brand_data Brand data
     * @return int Topic count
     */
    private function count_total_topics($brand_data) {
        $count = 0;

        if (!empty($brand_data['topic_pool']) && !empty($brand_data['topic_pool']['categories'])) {
            foreach ($brand_data['topic_pool']['categories'] as $category) {
                $count += count($category['topics']);
            }
        }

        return $count;
    }

    /**
     * Count topics with performance scores
     *
     * @param array $brand_data Brand data
     * @return int Count
     */
    private function count_topics_with_scores($brand_data) {
        $count = 0;

        if (!empty($brand_data['topic_pool']) && !empty($brand_data['topic_pool']['categories'])) {
            foreach ($brand_data['topic_pool']['categories'] as $category) {
                foreach ($category['topics'] as $topic) {
                    if (isset($topic['performance_score']) && $topic['performance_score'] > 0) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
