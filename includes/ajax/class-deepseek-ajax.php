<?php
/**
 * DeepSeek AJAX Handler
 *
 * @package AI_Manager_Pro
 * @subpackage AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DeepSeek AJAX Handler Class
 */
class AI_Manager_Pro_DeepSeek_Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_ajax_ai_manager_pro_test_deepseek_connection', [$this, 'test_connection']);
        add_action('wp_ajax_ai_manager_pro_generate_deepseek_content', [$this, 'generate_content']);
        add_action('wp_ajax_ai_manager_pro_get_deepseek_models', [$this, 'get_models']);
        add_action('wp_ajax_ai_manager_pro_deepseek_usage_stats', [$this, 'get_usage_stats']);
    }

    /**
     * Test DeepSeek API connection
     */
    public function test_connection()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');

        if (empty($api_key)) {
            wp_send_json_error('מפתח API נדרש');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();

            // Temporarily set the API key for testing
            update_option('ai_manager_pro_deepseek_key', $api_key);

            $result = $deepseek_service->test_connection();

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success([
                'message' => 'החיבור ל-DeepSeek הצליח!',
                'response' => $result['response'] ?? '',
                'models' => $deepseek_service->get_models()
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בבדיקת החיבור: ' . $e->getMessage());
        }
    }

    /**
     * Generate content using DeepSeek
     */
    public function generate_content()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }

        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? 'deepseek-chat');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'blog_post');
        $brand_id = sanitize_text_field($_POST['brand_id'] ?? '');

        if (empty($prompt)) {
            wp_send_json_error('פרומפט נדרש');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();

            // Get brand data if specified
            $brand_data = null;
            if (!empty($brand_id)) {
                $brands_data = get_option('ai_manager_pro_brands_data', []);
                $brand_data = $brands_data[$brand_id] ?? null;
            }

            $options = [
                'max_tokens' => intval($_POST['max_tokens'] ?? 2000),
                'temperature' => floatval($_POST['temperature'] ?? 0.7)
            ];

            // Use specialized content generation if available
            if (method_exists($deepseek_service, 'generate_specialized_content')) {
                $result = $deepseek_service->generate_specialized_content($content_type, $prompt, $brand_data, $options);
            } else {
                $result = $deepseek_service->generate_content($prompt, $model, $options);
            }

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success([
                'content' => $result['content'],
                'model' => $result['model'],
                'usage' => $result['usage'] ?? [],
                'message' => 'התוכן נוצר בהצלחה עם DeepSeek!'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה ביצירת התוכן: ' . $e->getMessage());
        }
    }

    /**
     * Get available DeepSeek models
     */
    public function get_models()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();
            $models = $deepseek_service->get_models();
            $popular_models = $deepseek_service->get_popular_models();

            wp_send_json_success([
                'models' => $models,
                'popular_models' => $popular_models,
                'pricing' => $deepseek_service->get_model_pricing()
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בקבלת רשימת המודלים: ' . $e->getMessage());
        }
    }

    /**
     * Get DeepSeek usage statistics
     */
    public function get_usage_stats()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $days = intval($_POST['days'] ?? 7);

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();
            $stats = $deepseek_service->get_usage_stats($days);

            wp_send_json_success([
                'stats' => $stats,
                'period' => $days . ' ימים אחרונים'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בקבלת סטטיסטיקות: ' . $e->getMessage());
        }
    }

    /**
     * Estimate content generation cost
     */
    public function estimate_cost()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }

        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? 'deepseek-chat');
        $expected_length = intval($_POST['expected_length'] ?? 1000);

        if (empty($prompt)) {
            wp_send_json_error('פרומפט נדרש לחישוב עלות');
        }

        try {
            $deepseek_service = new AI_Manager_Pro_DeepSeek_Service();
            $cost_estimate = $deepseek_service->estimate_cost(strlen($prompt), $expected_length, $model);

            if ($cost_estimate) {
                wp_send_json_success([
                    'cost_estimate' => $cost_estimate,
                    'message' => 'חישוב עלות הושלם'
                ]);
            } else {
                wp_send_json_error('לא ניתן לחשב עלות עבור מודל זה');
            }

        } catch (Exception $e) {
            wp_send_json_error('שגיאה בחישוב העלות: ' . $e->getMessage());
        }
    }
}

// Initialize the AJAX handler
new AI_Manager_Pro_DeepSeek_Ajax();