<?php
/**
 * OpenRouter AJAX Handler
 *
 * @package AI_Manager_Pro
 * @subpackage AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenRouter AJAX Handler Class
 */
class AI_Manager_Pro_OpenRouter_AJAX
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_ajax_ai_manager_pro_test_openrouter_connection', [$this, 'test_connection']);
        add_action('wp_ajax_ai_manager_pro_save_default_model', [$this, 'save_default_model']);
        add_action('wp_ajax_ai_manager_pro_get_usage_stats', [$this, 'get_usage_stats']);
        add_action('wp_ajax_ai_manager_pro_clear_cache', [$this, 'clear_cache']);
        add_action('wp_ajax_ai_manager_pro_generate_content', [$this, 'generate_content']);
        add_action('wp_ajax_ai_manager_pro_refresh_models', [$this, 'refresh_models']);
    }

    /**
     * Test OpenRouter connection
     */
    public function test_connection()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');

        if (empty($api_key)) {
            wp_send_json_error('מפתח API לא הוזן');
        }

        // Temporarily save the API key for testing
        $old_key = get_option('ai_manager_pro_openrouter_api_key', '');
        update_option('ai_manager_pro_openrouter_api_key', $api_key);

        // Load OpenRouter service
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();

        $result = $openrouter_service->test_connection();

        // Restore old key if test failed
        if (is_wp_error($result)) {
            update_option('ai_manager_pro_openrouter_api_key', $old_key);
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success([
            'message' => $result['message'],
            'response' => $result['response']
        ]);
    }

    /**
     * Save default model
     */
    public function save_default_model()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        $model = sanitize_text_field($_POST['model'] ?? '');

        if (empty($model)) {
            wp_send_json_error('מודל לא נבחר');
        }

        // Validate model exists
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
        $popular_models = $openrouter_service->get_popular_models();

        if (!isset($popular_models[$model])) {
            wp_send_json_error('מודל לא תקין');
        }

        update_option('ai_manager_pro_default_model', $model);

        wp_send_json_success([
            'message' => 'המודל נשמר בהצלחה',
            'model' => $model,
            'model_name' => $popular_models[$model]['name']
        ]);
    }

    /**
     * Get usage statistics
     */
    public function get_usage_stats()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();

        $days = intval($_POST['days'] ?? 7);
        $stats = $openrouter_service->get_usage_stats($days);

        wp_send_json_success($stats);
    }

    /**
     * Clear cache
     */
    public function clear_cache()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        // Clear OpenRouter models cache
        delete_transient('ai_manager_pro_openrouter_models');

        // Clear other caches
        wp_cache_flush();

        wp_send_json_success([
            'message' => 'המטמון נוקה בהצלחה'
        ]);
    }

    /**
     * Generate content
     */
    public function generate_content()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? '');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'blog-post');
        $content_length = sanitize_text_field($_POST['content_length'] ?? 'medium');
        $brand_id = sanitize_text_field($_POST['brand_id'] ?? '');
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $additional_instructions = sanitize_textarea_field($_POST['additional_instructions'] ?? '');

        if (empty($prompt)) {
            wp_send_json_error('נושא התוכן לא הוזן');
        }

        // Build enhanced prompt
        $enhanced_prompt = $this->build_enhanced_prompt(
            $prompt,
            $content_type,
            $content_length,
            $brand_id,
            $keywords,
            $additional_instructions
        );

        // Load OpenRouter service
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();

        // Set model options based on content length
        $options = $this->get_model_options($content_length);

        $result = $openrouter_service->generate_content($enhanced_prompt, $model, $options);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Save to recent content
        $this->save_recent_content([
            'title' => $prompt,
            'content' => $result['content'],
            'model' => $result['model'],
            'content_type' => $content_type,
            'created' => current_time('mysql')
        ]);

        wp_send_json_success([
            'content' => $result['content'],
            'model' => $result['model'],
            'usage' => $result['usage'],
            'word_count' => str_word_count(strip_tags($result['content'])),
            'char_count' => strlen($result['content'])
        ]);
    }

    /**
     * Refresh models list
     */
    public function refresh_models()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('אימות אבטחה נכשל');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('אין הרשאות מתאימות');
        }

        // Clear models cache
        delete_transient('ai_manager_pro_openrouter_models');

        // Load fresh models
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
        $models = $openrouter_service->get_models();

        wp_send_json_success([
            'message' => 'רשימת המודלים עודכנה',
            'models_count' => count($models)
        ]);
    }

    /**
     * Build enhanced prompt with context
     */
    private function build_enhanced_prompt($prompt, $content_type, $content_length, $brand_id, $keywords, $additional_instructions)
    {
        $enhanced_prompt = '';

        // Add content type context
        $content_types = [
            'blog-post' => 'כתוב פוסט בלוג מקצועי ומעניין',
            'product-description' => 'כתוב תיאור מוצר משכנע ומפורט',
            'social-media' => 'כתוב פוסט לרשתות חברתיות קצר ומעניין',
            'email-newsletter' => 'כתוב ניוזלטר אימייל מקצועי',
            'landing-page' => 'כתוב תוכן לעמוד נחיתה משכנע',
            'custom' => 'כתוב תוכן מותאם אישית'
        ];

        $enhanced_prompt .= $content_types[$content_type] ?? $content_types['custom'];

        // Add length context
        $length_instructions = [
            'short' => ' באורך קצר (100-300 מילים)',
            'medium' => ' באורך בינוני (300-800 מילים)',
            'long' => ' באורך ארוך (800-1500 מילים)',
            'very-long' => ' באורך מאוד ארוך (1500+ מילים)'
        ];

        $enhanced_prompt .= $length_instructions[$content_length] ?? $length_instructions['medium'];

        // Add brand context
        if (!empty($brand_id)) {
            $brands_data = get_option('ai_manager_pro_brands_data', []);
            if (isset($brands_data[$brand_id])) {
                $brand = $brands_data[$brand_id];
                $enhanced_prompt .= "\n\nהקשר מותג:\n";
                $enhanced_prompt .= "שם המותג: " . $brand['name'] . "\n";
                if (!empty($brand['voice']['tone'])) {
                    $enhanced_prompt .= "טון: " . $brand['voice']['tone'] . "\n";
                }
                if (!empty($brand['voice']['style'])) {
                    $enhanced_prompt .= "סגנון: " . $brand['voice']['style'] . "\n";
                }
                if (!empty($brand['target_audience']['description'])) {
                    $enhanced_prompt .= "קהל יעד: " . $brand['target_audience']['description'] . "\n";
                }
            }
        }

        // Add keywords
        if (!empty($keywords)) {
            $keywords_array = array_map('trim', explode(',', $keywords));
            $enhanced_prompt .= "\n\nמילות מפתח לשילוב: " . implode(', ', $keywords_array);
        }

        // Add main topic
        $enhanced_prompt .= "\n\nנושא התוכן: " . $prompt;

        // Add additional instructions
        if (!empty($additional_instructions)) {
            $enhanced_prompt .= "\n\nהוראות נוספות: " . $additional_instructions;
        }

        // Add general instructions
        $enhanced_prompt .= "\n\nהוראות כלליות:";
        $enhanced_prompt .= "\n- כתוב בעברית ברמה גבוהה";
        $enhanced_prompt .= "\n- השתמש בכותרות ופסקאות מובנות";
        $enhanced_prompt .= "\n- הקפד על דקדוק ואיות נכונים";
        $enhanced_prompt .= "\n- צור תוכן מעניין ורלוונטי";
        $enhanced_prompt .= "\n- הימנע מחזרות מיותרות";

        return $enhanced_prompt;
    }

    /**
     * Get model options based on content length
     */
    private function get_model_options($content_length)
    {
        $options = [
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];

        switch ($content_length) {
            case 'short':
                $options['max_tokens'] = 500;
                break;
            case 'medium':
                $options['max_tokens'] = 1500;
                break;
            case 'long':
                $options['max_tokens'] = 3000;
                break;
            case 'very-long':
                $options['max_tokens'] = 4000;
                break;
            default:
                $options['max_tokens'] = 1500;
        }

        return $options;
    }

    /**
     * Save recent content for dashboard
     */
    private function save_recent_content($content_data)
    {
        $recent_content = get_option('ai_manager_pro_recent_content', []);

        // Add new content to the beginning
        array_unshift($recent_content, $content_data);

        // Keep only last 20 items
        $recent_content = array_slice($recent_content, 0, 20);

        update_option('ai_manager_pro_recent_content', $recent_content);
    }
}

// Initialize AJAX handler
new AI_Manager_Pro_OpenRouter_AJAX();