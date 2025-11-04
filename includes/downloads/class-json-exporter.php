<?php
/**
 * JSON Exporter Class
 * מחלקה לייצוא דוגמאות ומותגים לקבצי JSON
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_JSON_Exporter
{

    private $samples_manager;
    private $brand_manager;

    public function __construct()
    {
        // טעינת מנהל הדוגמאות והמותגים
        if (file_exists(AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php')) {
            require_once AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php';
        }

        if (class_exists('AI_Website_Manager_Advanced_Brand_Manager')) {
            $this->brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        }
    }

    /**
     * ייצוא דוגמה בודדת
     */
    public function export_sample($sample_type)
    {
        try {
            $samples = $this->get_all_samples();

            if (!isset($samples[$sample_type])) {
                throw new Exception("Sample type '{$sample_type}' not found");
            }

            $sample_data = $samples[$sample_type];

            // הכנת נתוני JSON
            $json_data = $this->prepare_json_data($sample_data);

            // יצירת שם קובץ
            $filename = $this->generate_filename($sample_type, 'json');

            return [
                'success' => true,
                'data' => $json_data,
                'filename' => $filename,
                'content_type' => 'application/json'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ייצוא כל הדוגמאות
     */
    public function export_all_samples()
    {
        try {
            $samples = $this->get_all_samples();
            $exported_files = [];

            foreach ($samples as $type => $data) {
                $json_data = $this->prepare_json_data($data);
                $filename = $this->generate_filename($type, 'json');

                $exported_files[] = [
                    'filename' => $filename,
                    'content' => $json_data,
                    'type' => $type
                ];
            }

            // יצירת קובץ ZIP
            $zip_result = $this->create_zip_archive($exported_files);

            return $zip_result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ייצוא מותג קיים
     */
    public function export_brand($brand_id)
    {
        try {
            if (!$this->brand_manager) {
                throw new Exception('Brand manager not available');
            }

            $brand_data = $this->brand_manager->get_brand($brand_id);

            if (!$brand_data) {
                throw new Exception("Brand with ID {$brand_id} not found");
            }

            // הכנת נתוני JSON
            $json_data = $this->prepare_json_data($brand_data);

            // יצירת שם קובץ
            $brand_name = sanitize_file_name($brand_data['name'] ?? 'brand');
            $filename = $brand_name . '_export_' . date('Y-m-d_H-i-s') . '.json';

            return [
                'success' => true,
                'data' => $json_data,
                'filename' => $filename,
                'content_type' => 'application/json'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * יצירת ארכיון ZIP
     */
    public function create_zip_archive($files)
    {
        try {
            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive class not available');
            }

            $zip = new ZipArchive();
            $zip_filename = 'ai_brand_samples_' . date('Y-m-d_H-i-s') . '.zip';
            $zip_path = wp_upload_dir()['path'] . '/' . $zip_filename;

            if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
                throw new Exception('Cannot create ZIP file');
            }

            foreach ($files as $file) {
                $zip->addFromString($file['filename'], $file['content']);
            }

            $zip->close();

            return [
                'success' => true,
                'zip_path' => $zip_path,
                'zip_filename' => $zip_filename,
                'files_count' => count($files),
                'content_type' => 'application/zip'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * הכנת נתוני JSON
     */
    private function prepare_json_data($data)
    {
        // הוספת מטא-דאטה
        $prepared_data = [
            'export_info' => [
                'exported_at' => current_time('mysql'),
                'exported_by' => get_current_user_id(),
                'plugin_version' => AI_WEBSITE_MANAGER_VERSION,
                'export_type' => 'ai_website_manager_brand'
            ],
            'brand_data' => $data
        ];

        return wp_json_encode($prepared_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * יצירת שם קובץ
     */
    private function generate_filename($type, $extension)
    {
        $safe_type = sanitize_file_name($type);
        $timestamp = date('Y-m-d_H-i-s');
        return "brand_sample_{$safe_type}_{$timestamp}.{$extension}";
    }

    /**
     * קבלת כל הדוגמאות
     */
    private function get_all_samples()
    {
        // אם קיים קובץ הדוגמאות, נטען אותו
        if (file_exists(AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php')) {
            ob_start();
            include AI_WEBSITE_MANAGER_PATH . 'includes/samples/brand-samples.php';
            ob_end_clean();

            // נניח שהקובץ מחזיר מערך של דוגמאות
            if (function_exists('get_brand_samples')) {
                return get_brand_samples();
            }
        }

        // דוגמאות ברירת מחדל אם הקובץ לא קיים
        return $this->get_default_samples();
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
                'description' => 'חברת סטארט-אפ מתקדמת בתחום הטכנולוגיה',
                'target_audience' => 'מפתחים ומנהלי טכנולוגיה',
                'tone' => 'מקצועי וחדשני',
                'values' => ['חדשנות', 'איכות', 'מהירות'],
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
                'description' => 'מרכז בריאות ורווחה מקצועי',
                'target_audience' => 'אנשים המעוניינים בבריאות ואיכות חיים',
                'tone' => 'חם ומעודד',
                'values' => ['בריאות', 'איזון', 'טבעיות'],
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

    /**
     * ניקוי קבצים זמניים
     */
    public function cleanup_temp_files($max_age_hours = 24)
    {
        $upload_dir = wp_upload_dir()['path'];
        $files = glob($upload_dir . '/ai_brand_samples_*.zip');

        $cleaned = 0;
        foreach ($files as $file) {
            if (filemtime($file) < (time() - ($max_age_hours * 3600))) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    /**
     * קבלת סטטיסטיקות ייצוא
     */
    public function get_export_stats()
    {
        $samples = $this->get_all_samples();

        return [
            'total_samples' => count($samples),
            'sample_types' => array_keys($samples),
            'last_export' => get_option('ai_manager_last_export_time', 'Never'),
            'total_exports' => get_option('ai_manager_total_exports', 0)
        ];
    }

    /**
     * עדכון סטטיסטיקות ייצוא
     */
    public function update_export_stats()
    {
        update_option('ai_manager_last_export_time', current_time('mysql'));
        $total = get_option('ai_manager_total_exports', 0);
        update_option('ai_manager_total_exports', $total + 1);
    }
}