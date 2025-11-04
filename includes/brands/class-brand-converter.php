<?php
/**
 * Brand Converter Class
 * מחלקה להמרת דוגמאות למותגים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Brand_Converter
{

    private $brand_manager;
    private $samples_data;

    public function __construct()
    {
        $this->brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        $this->load_samples_data();
    }

    /**
     * טעינת נתוני הדוגמאות
     */
    private function load_samples_data()
    {
        // טעינת דוגמאות מהקובץ
        if (file_exists(AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php')) {
            ob_start();
            include AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php';
            ob_end_clean();

            if (function_exists('get_brand_samples')) {
                $this->samples_data = get_brand_samples();
            }
        }

        // אם לא נטענו דוגמאות, נשתמש בדוגמאות ברירת מחדל
        if (empty($this->samples_data)) {
            $this->samples_data = $this->get_default_samples();
        }
    }

    /**
     * המרת דוגמה למותג
     */
    public function convert_sample_to_brand($sample_type, $modifications = [])
    {
        try {
            if (!isset($this->samples_data[$sample_type])) {
                throw new Exception("Sample type '{$sample_type}' not found");
            }

            $sample_data = $this->samples_data[$sample_type];

            // הכנת נתוני המותג
            $brand_data = $this->prepare_brand_data($sample_data, $modifications);

            // ולידציה
            $validation_result = $this->validate_brand_data($brand_data);
            if ($validation_result !== true) {
                throw new Exception('Validation failed: ' . $validation_result);
            }

            // יצירת המותג
            $brand_id = $this->create_brand_from_template($brand_data);

            if ($brand_id) {
                // מעקב אחר ההמרה
                $this->track_conversion($sample_type, $brand_id);

                return [
                    'success' => true,
                    'brand_id' => $brand_id,
                    'message' => 'Brand created successfully from sample'
                ];
            } else {
                throw new Exception('Failed to create brand');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * הכנת נתוני מותג מדוגמה
     */
    private function prepare_brand_data($sample_data, $modifications = [])
    {
        // מיזוג השינויים עם נתוני הדוגמה
        $brand_data = array_merge($sample_data, $modifications);

        // הוספת מטא-דאטה
        $brand_data['created_from_sample'] = true;
        $brand_data['sample_source'] = $sample_data['name'] ?? 'Unknown';
        $brand_data['conversion_date'] = current_time('mysql');
        $brand_data['converted_by'] = get_current_user_id();

        // וידוא שדות חובה
        $required_fields = [
            'name' => $brand_data['name'] ?? 'New Brand',
            'industry' => $brand_data['industry'] ?? 'General',
            'description' => $brand_data['description'] ?? 'Brand description',
            'target_audience' => $brand_data['target_audience'] ?? 'General audience',
            'tone' => $brand_data['tone'] ?? 'Professional',
            'values' => $brand_data['values'] ?? ['Quality', 'Innovation'],
            'colors' => $brand_data['colors'] ?? ['#667eea', '#764ba2']
        ];

        foreach ($required_fields as $field => $default) {
            if (empty($brand_data[$field])) {
                $brand_data[$field] = $default;
            }
        }

        return $brand_data;
    }

    /**
     * הכנת טופס עריכת מותג מדוגמה
     */
    public function prepare_brand_form($sample_type)
    {
        try {
            if (!isset($this->samples_data[$sample_type])) {
                throw new Exception("Sample type '{$sample_type}' not found");
            }

            $sample_data = $this->samples_data[$sample_type];

            return [
                'success' => true,
                'form_data' => $sample_data,
                'sample_info' => [
                    'type' => $sample_type,
                    'name' => $sample_data['name'] ?? 'Unknown',
                    'industry' => $sample_data['industry'] ?? 'General'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ולידציה של נתוני מותג
     */
    public function validate_brand_data($brand_data)
    {
        $errors = [];

        // בדיקת שדות חובה
        $required_fields = ['name', 'industry', 'description'];
        foreach ($required_fields as $field) {
            if (empty($brand_data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        // בדיקת אורך שם המותג
        if (isset($brand_data['name']) && strlen($brand_data['name']) > 100) {
            $errors[] = "Brand name is too long (max 100 characters)";
        }

        // בדיקת ייחודיות השם
        if (isset($brand_data['name'])) {
            $existing_brand = $this->brand_manager->get_brand_by_name($brand_data['name']);
            if ($existing_brand) {
                $errors[] = "Brand name already exists";
            }
        }

        // בדיקת פורמט צבעים
        if (isset($brand_data['colors']) && is_array($brand_data['colors'])) {
            foreach ($brand_data['colors'] as $color) {
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                    $errors[] = "Invalid color format: {$color}";
                }
            }
        }

        // בדיקת URL אתר
        if (isset($brand_data['website']) && !empty($brand_data['website'])) {
            if (!filter_var($brand_data['website'], FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid website URL";
            }
        }

        return empty($errors) ? true : implode(', ', $errors);
    }

    /**
     * יצירת מותג מתבנית
     */
    public function create_brand_from_template($template_data)
    {
        try {
            // הכנת הנתונים לשמירה
            $brand_data = [
                'name' => $template_data['name'],
                'industry' => $template_data['industry'],
                'description' => $template_data['description'],
                'target_audience' => $template_data['target_audience'],
                'tone' => $template_data['tone'],
                'values' => $template_data['values'],
                'colors' => $template_data['colors'],
                'logo_url' => $template_data['logo_url'] ?? '',
                'website' => $template_data['website'] ?? '',
                'social_media' => $template_data['social_media'] ?? [],
                'additional_info' => [
                    'created_from_sample' => $template_data['created_from_sample'] ?? false,
                    'sample_source' => $template_data['sample_source'] ?? '',
                    'conversion_date' => $template_data['conversion_date'] ?? current_time('mysql'),
                    'converted_by' => $template_data['converted_by'] ?? get_current_user_id()
                ]
            ];

            // שמירת המותג
            $brand_id = $this->brand_manager->create_brand($brand_data);

            return $brand_id;

        } catch (Exception $e) {
            error_log('Brand creation from template failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * מעקב אחר המרות
     */
    public function track_conversion($sample_type, $brand_id)
    {
        $conversions = get_option('ai_manager_sample_conversions', []);

        $conversions[] = [
            'sample_type' => $sample_type,
            'brand_id' => $brand_id,
            'converted_at' => current_time('mysql'),
            'converted_by' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // שמירת רק 100 ההמרות האחרונות
        if (count($conversions) > 100) {
            $conversions = array_slice($conversions, -100);
        }

        update_option('ai_manager_sample_conversions', $conversions);

        // עדכון מונה המרות
        $total_conversions = get_option('ai_manager_total_conversions', 0);
        update_option('ai_manager_total_conversions', $total_conversions + 1);
    }

    /**
     * קבלת סטטיסטיקות המרות
     */
    public function get_conversion_stats()
    {
        $conversions = get_option('ai_manager_sample_conversions', []);
        $total_conversions = get_option('ai_manager_total_conversions', 0);

        // חישוב סטטיסטיקות
        $stats = [
            'total_conversions' => $total_conversions,
            'recent_conversions' => count($conversions),
            'conversions_this_month' => 0,
            'popular_samples' => [],
            'conversion_rate' => 0
        ];

        // המרות החודש
        $this_month = date('Y-m');
        foreach ($conversions as $conversion) {
            if (strpos($conversion['converted_at'], $this_month) === 0) {
                $stats['conversions_this_month']++;
            }

            // דוגמאות פופולריות
            $sample_type = $conversion['sample_type'];
            if (!isset($stats['popular_samples'][$sample_type])) {
                $stats['popular_samples'][$sample_type] = 0;
            }
            $stats['popular_samples'][$sample_type]++;
        }

        // מיון דוגמאות פופולריות
        arsort($stats['popular_samples']);

        return $stats;
    }

    /**
     * קבלת רשימת דוגמאות זמינות
     */
    public function get_available_samples()
    {
        $samples = [];

        foreach ($this->samples_data as $type => $data) {
            $samples[] = [
                'type' => $type,
                'name' => $data['name'] ?? 'Unknown',
                'industry' => $data['industry'] ?? 'General',
                'description' => $data['description'] ?? '',
                'icon' => $this->get_sample_icon($type),
                'preview_data' => [
                    'target_audience' => $data['target_audience'] ?? '',
                    'tone' => $data['tone'] ?? '',
                    'values' => $data['values'] ?? []
                ]
            ];
        }

        return $samples;
    }

    /**
     * קבלת אייקון לדוגמה
     */
    private function get_sample_icon($sample_type)
    {
        $icons = [
            'tech_startup' => '💻',
            'wellness' => '🏥',
            'education' => '🎓',
            'ecommerce' => '🛒',
            'consulting' => '💼',
            'creative' => '🎨',
            'restaurant' => '🍝',
            'fitness' => '💪',
            'real_estate' => '🏠',
            'finance' => '💰'
        ];

        return $icons[$sample_type] ?? '🏢';
    }

    /**
     * דוגמאות ברירת מחדל
     */
    private function get_default_samples()
    {
        return [
            'tech_startup' => [
                'name' => 'TechFlow Solutions',
                'industry' => 'טכנולוגיה',
                'description' => 'חברת סטארט-אפ מתקדמת בתחום הטכנולוגיה המתמחה בפתרונות חכמים',
                'target_audience' => 'מפתחים, מנהלי טכנולוגיה וחברות היי-טק',
                'tone' => 'מקצועי, חדשני ומעורר השראה',
                'values' => ['חדשנות', 'איכות', 'מהירות', 'שקיפות'],
                'colors' => ['#667eea', '#764ba2'],
                'logo_url' => '',
                'website' => 'https://techflow.example.com',
                'social_media' => [
                    'facebook' => 'techflow',
                    'twitter' => 'techflow',
                    'linkedin' => 'techflow-solutions'
                ]
            ],
            'wellness' => [
                'name' => 'HealthyLife Wellness',
                'industry' => 'בריאות ורווחה',
                'description' => 'מרכז בריאות ורווחה מקצועי המתמחה בטיפולים טבעיים ואורח חיים בריא',
                'target_audience' => 'אנשים המעוניינים בבריאות, איכות חיים ורווחה אישית',
                'tone' => 'חם, מעודד ומקצועי',
                'values' => ['בריאות', 'איזון', 'טבעיות', 'הוליסטיות'],
                'colors' => ['#4ecdc4', '#44a08d'],
                'logo_url' => '',
                'website' => 'https://healthylife.example.com',
                'social_media' => [
                    'facebook' => 'healthylife',
                    'instagram' => 'healthylife_wellness'
                ]
            ]
        ];
    }
}