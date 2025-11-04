<?php
/**
 * Brand Converter AJAX Handler
 * טיפול בבקשות AJAX למערכת המרת דוגמאות
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Brand_Converter_Ajax
{

    private $brand_converter;

    public function __construct()
    {
        require_once AI_WEBSITE_MANAGER_PATH . 'includes/brands/class-brand-converter.php';
        $this->brand_converter = new AI_Website_Manager_Brand_Converter();
        $this->init_hooks();
    }

    /**
     * אתחול hooks
     */
    private function init_hooks()
    {
        // AJAX endpoints
        add_action('wp_ajax_ai_prepare_brand_form', [$this, 'handle_prepare_brand_form']);
        add_action('wp_ajax_ai_convert_sample_to_brand', [$this, 'handle_convert_sample_to_brand']);
        add_action('wp_ajax_ai_quick_convert_sample', [$this, 'handle_quick_convert_sample']);
        add_action('wp_ajax_ai_get_available_samples', [$this, 'handle_get_available_samples']);
        add_action('wp_ajax_ai_get_conversion_stats', [$this, 'handle_get_conversion_stats']);
    }

    /**
     * הכנת טופס מותג מדוגמה
     */
    public function handle_prepare_brand_form()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $sample_type = sanitize_text_field($_POST['sample_type'] ?? '');

        if (empty($sample_type)) {
            wp_send_json_error('Sample type is required');
        }

        try {
            $result = $this->brand_converter->prepare_brand_form($sample_type);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Failed to prepare form: ' . $e->getMessage());
        }
    }

    /**
     * המרת דוגמה למותג
     */
    public function handle_convert_sample_to_brand()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $sample_type = sanitize_text_field($_POST['sample_type'] ?? '');
        $brand_data_json = $_POST['brand_data'] ?? '';

        if (empty($sample_type) || empty($brand_data_json)) {
            wp_send_json_error('Sample type and brand data are required');
        }

        try {
            $brand_data = json_decode(stripslashes($brand_data_json), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Invalid JSON data');
            }

            // סניטיזציה של הנתונים
            $brand_data = $this->sanitize_brand_data($brand_data);

            $result = $this->brand_converter->convert_sample_to_brand($sample_type, $brand_data);

            if ($result['success']) {
                wp_send_json_success([
                    'brand_id' => $result['brand_id'],
                    'message' => 'Brand created successfully from sample'
                ]);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * המרה מהירה
     */
    public function handle_quick_convert_sample()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $sample_type = sanitize_text_field($_POST['sample_type'] ?? '');

        if (empty($sample_type)) {
            wp_send_json_error('Sample type is required');
        }

        try {
            // המרה ללא שינויים (רק עם שם ייחודי)
            $modifications = [
                'name' => $this->generate_unique_brand_name($sample_type)
            ];

            $result = $this->brand_converter->convert_sample_to_brand($sample_type, $modifications);

            if ($result['success']) {
                wp_send_json_success([
                    'brand_id' => $result['brand_id'],
                    'message' => 'Brand created successfully with quick conversion'
                ]);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Quick conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * קבלת דוגמאות זמינות
     */
    public function handle_get_available_samples()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        try {
            $samples = $this->brand_converter->get_available_samples();
            wp_send_json_success($samples);

        } catch (Exception $e) {
            wp_send_json_error('Failed to get samples: ' . $e->getMessage());
        }
    }

    /**
     * קבלת סטטיסטיקות המרות
     */
    public function handle_get_conversion_stats()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        try {
            $stats = $this->brand_converter->get_conversion_stats();
            wp_send_json_success($stats);

        } catch (Exception $e) {
            wp_send_json_error('Failed to get stats: ' . $e->getMessage());
        }
    }

    /**
     * סניטיזציה של נתוני מותג
     */
    private function sanitize_brand_data($brand_data)
    {
        $sanitized = [];

        // שדות טקסט
        $text_fields = ['name', 'industry', 'description', 'target_audience', 'tone', 'website'];
        foreach ($text_fields as $field) {
            if (isset($brand_data[$field])) {
                $sanitized[$field] = sanitize_text_field($brand_data[$field]);
            }
        }

        // תיאור (יכול להכיל HTML בסיסי)
        if (isset($brand_data['description'])) {
            $sanitized['description'] = wp_kses_post($brand_data['description']);
        }

        // URL אתר
        if (isset($brand_data['website'])) {
            $sanitized['website'] = esc_url_raw($brand_data['website']);
        }

        // צבעים
        if (isset($brand_data['colors']) && is_array($brand_data['colors'])) {
            $sanitized['colors'] = [];
            foreach ($brand_data['colors'] as $color) {
                if (preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                    $sanitized['colors'][] = $color;
                }
            }
        }

        // ערכים
        if (isset($brand_data['values']) && is_array($brand_data['values'])) {
            $sanitized['values'] = [];
            foreach ($brand_data['values'] as $value) {
                $clean_value = sanitize_text_field($value);
                if (!empty($clean_value)) {
                    $sanitized['values'][] = $clean_value;
                }
            }
        }

        // רשתות חברתיות
        if (isset($brand_data['social_media']) && is_array($brand_data['social_media'])) {
            $sanitized['social_media'] = [];
            $social_fields = ['facebook', 'instagram', 'twitter', 'linkedin'];

            foreach ($social_fields as $field) {
                if (isset($brand_data['social_media'][$field])) {
                    $sanitized['social_media'][$field] = sanitize_text_field($brand_data['social_media'][$field]);
                }
            }
        }

        return $sanitized;
    }

    /**
     * יצירת שם מותג ייחודי
     */
    private function generate_unique_brand_name($sample_type)
    {
        $base_names = [
            'tech_startup' => 'TechFlow Solutions',
            'wellness' => 'HealthyLife Wellness',
            'education' => 'EduTech Academy',
            'ecommerce' => 'StyleHub Store',
            'consulting' => 'Business Growth Partners',
            'creative' => 'Creative Studio Pro',
            'restaurant' => 'Taste of Italy',
            'fitness' => 'FitZone Gym',
            'real_estate' => 'Prime Properties',
            'finance' => 'WealthWise Financial'
        ];

        $base_name = $base_names[$sample_type] ?? 'New Brand';

        // בדיקה אם השם קיים
        $brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        $counter = 1;
        $unique_name = $base_name;

        while ($brand_manager->get_brand_by_name($unique_name)) {
            $unique_name = $base_name . ' ' . $counter;
            $counter++;

            // הגבלה למניעת לולאה אינסופית
            if ($counter > 100) {
                $unique_name = $base_name . ' ' . time();
                break;
            }
        }

        return $unique_name;
    }
}

// אתחול המחלקה
new AI_Website_Manager_Brand_Converter_Ajax();