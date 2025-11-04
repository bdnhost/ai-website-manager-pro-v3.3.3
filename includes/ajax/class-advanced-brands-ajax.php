<?php
/**
 * Advanced Brands AJAX Handlers
 * מטפלי AJAX לניהול מותגים מתקדם
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Advanced_Brands_Ajax
{

    private $brand_manager;

    public function __construct()
    {
        $this->brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        $this->init_hooks();
    }

    /**
     * אתחול hooks
     */
    private function init_hooks()
    {
        // AJAX actions for logged in users
        add_action('wp_ajax_get_brand_data', [$this, 'get_brand_data']);
        add_action('wp_ajax_save_advanced_brand', [$this, 'save_advanced_brand']);
        add_action('wp_ajax_activate_brand', [$this, 'activate_brand']);
        add_action('wp_ajax_delete_brand', [$this, 'delete_brand']);
        add_action('wp_ajax_export_brand_json', [$this, 'export_brand_json']);
        add_action('wp_ajax_import_brand_json', [$this, 'import_brand_json']);
        add_action('wp_ajax_export_all_brands', [$this, 'export_all_brands']);
        add_action('wp_ajax_duplicate_brand', [$this, 'duplicate_brand']);
        add_action('wp_ajax_search_brands', [$this, 'search_brands']);
        add_action('wp_ajax_get_brand_sample', [$this, 'get_brand_sample']);
        add_action('wp_ajax_download_all_brand_samples', [$this, 'download_all_brand_samples']);
    }

    /**
     * קבלת נתוני מותג
     */
    public function get_brand_data()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $brand_id = intval($_POST['brand_id']);
            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            $brand = $this->brand_manager->get_brand($brand_id);
            if (!$brand) {
                throw new Exception('מותג לא נמצא');
            }

            wp_send_json_success($brand);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * שמירת מותג מתקדם
     */
    public function save_advanced_brand()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            // איסוף נתונים מהטופס
            $brand_data = [
                'id' => intval($_POST['brand_id'] ?? 0),
                'name' => sanitize_text_field($_POST['name'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'industry' => sanitize_text_field($_POST['industry'] ?? ''),
                'target_audience' => sanitize_textarea_field($_POST['target_audience'] ?? ''),
                'brand_voice' => sanitize_text_field($_POST['brand_voice'] ?? 'professional'),
                'tone_of_voice' => sanitize_text_field($_POST['tone_of_voice'] ?? 'informative'),
                'mission' => sanitize_textarea_field($_POST['mission'] ?? ''),
                'vision' => sanitize_textarea_field($_POST['vision'] ?? ''),
                'unique_selling_proposition' => sanitize_textarea_field($_POST['unique_selling_proposition'] ?? ''),
                'website_url' => esc_url_raw($_POST['website_url'] ?? ''),
                'logo_url' => esc_url_raw($_POST['logo_url'] ?? '')
            ];

            // עיבוד מילות מפתח וערכים
            if (isset($_POST['keywords_array'])) {
                $keywords = json_decode(stripslashes($_POST['keywords_array']), true);
                $brand_data['keywords'] = is_array($keywords) ? $keywords : [];
            }

            if (isset($_POST['values_array'])) {
                $values = json_decode(stripslashes($_POST['values_array']), true);
                $brand_data['values'] = is_array($values) ? $values : [];
            }

            // שמירת המותג
            $brand_id = $this->brand_manager->save_advanced_brand($brand_data);

            // רישום לוג
            $this->log_brand_action('save', $brand_id, $brand_data['name']);

            wp_send_json_success([
                'brand_id' => $brand_id,
                'message' => 'המותג נשמר בהצלחה'
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * הפעלת מותג
     */
    public function activate_brand()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $brand_id = intval($_POST['brand_id']);
            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            $result = $this->brand_manager->activate_brand($brand_id);
            if (!$result) {
                throw new Exception('שגיאה בהפעלת המותג');
            }

            // רישום לוג
            $brand = $this->brand_manager->get_brand($brand_id);
            $this->log_brand_action('activate', $brand_id, $brand['name'] ?? 'לא ידוע');

            wp_send_json_success(['message' => 'המותג הופעל בהצלחה']);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * מחיקת מותג
     */
    public function delete_brand()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $brand_id = intval($_POST['brand_id']);
            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            // שמירת שם המותג לפני המחיקה
            $brand = $this->brand_manager->get_brand($brand_id);
            $brand_name = $brand['name'] ?? 'לא ידוע';

            $result = $this->brand_manager->delete_brand($brand_id);
            if (!$result) {
                throw new Exception('שגיאה במחיקת המותג');
            }

            // רישום לוג
            $this->log_brand_action('delete', $brand_id, $brand_name);

            wp_send_json_success(['message' => 'המותג נמחק בהצלחה']);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * ייצוא מותג ל-JSON
     */
    public function export_brand_json()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $brand_id = intval($_POST['brand_id']);
            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            $json_data = $this->brand_manager->export_brand_to_json($brand_id);
            $brand = $this->brand_manager->get_brand($brand_id);
            $filename = 'brand_' . sanitize_file_name($brand['name']) . '_' . date('Y-m-d') . '.json';

            // רישום לוג
            $this->log_brand_action('export', $brand_id, $brand['name'] ?? 'לא ידוע');

            wp_send_json_success([
                'json' => $json_data,
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * ייבוא מותג מ-JSON
     */
    public function import_brand_json()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $json_data = stripslashes($_POST['json_data'] ?? '');
            $brand_name = sanitize_text_field($_POST['brand_name'] ?? '');

            if (empty($json_data)) {
                throw new Exception('נתוני JSON חסרים');
            }

            $brand_id = $this->brand_manager->import_brand_from_json($json_data, $brand_name);

            // רישום לוג
            $imported_brand = $this->brand_manager->get_brand($brand_id);
            $this->log_brand_action('import', $brand_id, $imported_brand['name'] ?? 'לא ידוע');

            wp_send_json_success([
                'brand_id' => $brand_id,
                'message' => 'המותג יובא בהצלחה'
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * ייצוא כל המותגים
     */
    public function export_all_brands()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $all_brands = $this->brand_manager->get_all_brands();

            // הסרת שדות מערכת מכל המותגים
            $export_brands = [];
            foreach ($all_brands as $brand) {
                unset($brand['id'], $brand['created_at'], $brand['updated_at'], $brand['is_active']);
                $export_brands[] = $brand;
            }

            $json_data = json_encode($export_brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename = 'all_brands_' . date('Y-m-d_H-i-s') . '.json';

            // רישום לוג
            $this->log_brand_action('export_all', 0, count($all_brands) . ' מותגים');

            wp_send_json_success([
                'json' => $json_data,
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * שכפול מותג
     */
    public function duplicate_brand()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $brand_id = intval($_POST['brand_id']);
            $new_name = sanitize_text_field($_POST['new_name'] ?? '');

            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            if (empty($new_name)) {
                throw new Exception('שם המותג החדש חסר');
            }

            // קבלת נתוני המותג המקורי
            $original_brand = $this->brand_manager->get_brand($brand_id);
            if (!$original_brand) {
                throw new Exception('מותג מקורי לא נמצא');
            }

            // יצירת עותק עם שם חדש
            $duplicate_data = $original_brand;
            unset($duplicate_data['id'], $duplicate_data['created_at'], $duplicate_data['updated_at'], $duplicate_data['is_active']);
            $duplicate_data['name'] = $new_name;

            $new_brand_id = $this->brand_manager->save_advanced_brand($duplicate_data);

            // רישום לוג
            $this->log_brand_action('duplicate', $new_brand_id, $new_name);

            wp_send_json_success([
                'brand_id' => $new_brand_id,
                'message' => 'המותג שוכפל בהצלחה'
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * חיפוש מותגים
     */
    public function search_brands()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $search_term = sanitize_text_field($_POST['search_term'] ?? '');

            if (empty($search_term)) {
                $brands = $this->brand_manager->get_all_brands();
            } else {
                $brands = $this->brand_manager->search_brands($search_term);
            }

            wp_send_json_success($brands);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * רישום פעולת מותג ללוג
     */
    private function log_brand_action($action, $brand_id, $brand_name)
    {
        $actions_map = [
            'save' => 'שמירת מותג',
            'activate' => 'הפעלת מותג',
            'delete' => 'מחיקת מותג',
            'export' => 'ייצוא מותג',
            'import' => 'ייבוא מותג',
            'export_all' => 'ייצוא כל המותגים',
            'duplicate' => 'שכפול מותג'
        ];

        $action_name = $actions_map[$action] ?? $action;
        $message = sprintf('%s: %s (ID: %d)', $action_name, $brand_name, $brand_id);

        // רישום ללוג המערכת
        if (class_exists('AI_Website_Manager_Logger')) {
            $logger = new AI_Website_Manager_Logger();
            $logger->log('info', 'brands', $message, [
                'action' => $action,
                'brand_id' => $brand_id,
                'brand_name' => $brand_name,
                'user_id' => get_current_user_id()
            ]);
        }
    }

    /**
     * ולידציה של נתוני JSON
     */
    private function validate_json_brand_data($data)
    {
        $required_fields = ['name'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("שדה חובה חסר: {$field}");
            }
        }

        // בדיקת תקינות URL-ים
        if (isset($data['website_url']) && !empty($data['website_url'])) {
            if (!filter_var($data['website_url'], FILTER_VALIDATE_URL)) {
                throw new Exception('כתובת אתר לא תקינה');
            }
        }

        if (isset($data['logo_url']) && !empty($data['logo_url'])) {
            if (!filter_var($data['logo_url'], FILTER_VALIDATE_URL)) {
                throw new Exception('כתובת לוגו לא תקינה');
            }
        }

        return true;
    }

    /**
     * ניקוי נתוני JSON
     */
    private function sanitize_json_brand_data($data)
    {
        $sanitized = [];

        // שדות טקסט
        $text_fields = ['name', 'industry', 'brand_voice', 'tone_of_voice'];
        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = sanitize_text_field($data[$field]);
            }
        }

        // שדות textarea
        $textarea_fields = ['description', 'target_audience', 'mission', 'vision', 'unique_selling_proposition', 'brand_guidelines'];
        foreach ($textarea_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = sanitize_textarea_field($data[$field]);
            }
        }

        // שדות URL
        $url_fields = ['website_url', 'logo_url'];
        foreach ($url_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = esc_url_raw($data[$field]);
            }
        }

        // שדות מערך
        $array_fields = ['keywords', 'values'];
        foreach ($array_fields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    $sanitized[$field] = array_map('sanitize_text_field', $data[$field]);
                } else {
                    $sanitized[$field] = [];
                }
            }
        }

        // שדות JSON מורכבים
        $json_fields = ['competitor_analysis', 'content_pillars', 'brand_colors', 'typography', 'social_media_links', 'contact_info', 'content_templates', 'seo_settings', 'analytics_goals'];
        foreach ($json_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $data[$field]; // יישמר כמו שהוא - יעבור ולידציה נוספת במנהל המותגים
            }
        }

        return $sanitized;
    }
}

// אתחול המחלקה
new AI_Website_Manager_Advanced_Brands_Ajax();    
    
/**
     * קבלת דוגמת מותג
     */
    public function get_brand_sample() {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }
            
            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }
            
            $sample_type = sanitize_text_field($_POST['sample_type']);
            
            if (empty($sample_type)) {
                throw new Exception('סוג דוגמה לא צוין');
            }
            
            // טעינת מחלקת הדוגמאות
            require_once AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php';
            
            $sample_data = AI_Website_Manager_Brand_Samples::get_sample_by_type($sample_type);
            
            if (!$sample_data) {
                throw new Exception('דוגמה לא נמצאה');
            }
            
            wp_send_json_success($sample_data);
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * הורדת כל דוגמאות המותגים
     */
    public function download_all_brand_samples() {
        try {
            // בדיקת הרשאות
            if (!current_user_can('manage_options')) {
                throw new Exception('אין הרשאה לביצוע פעולה זו');
            }
            
            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }
            
            // טעינת מחלקת הדוגמאות
            require_once AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php';
            
            $all_samples = AI_Website_Manager_Brand_Samples::get_all_samples();
            $sample_types = AI_Website_Manager_Brand_Samples::get_sample_types();
            
            // יצירת מבנה מסודר לייצוא
            $export_data = [
                'title' => 'דוגמאות מותגים - AI Website Manager Pro',
                'description' => 'אוסף דוגמאות מותגים מקצועיות לתחומים שונים',
                'created_at' => current_time('mysql'),
                'total_samples' => count($all_samples),
                'sample_types' => $sample_types,
                'samples' => $all_samples
            ];
            
            $json_data = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename = 'brand_samples_collection_' . date('Y-m-d') . '.json';
            
            wp_send_json_success([
                'json' => $json_data,
                'filename' => $filename
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }