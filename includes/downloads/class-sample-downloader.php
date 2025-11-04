<?php
/**
 * Sample Downloader Class
 * מחלקה לטיפול בהורדת קבצים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Sample_Downloader
{

    private $json_exporter;

    public function __construct()
    {
        $this->json_exporter = new AI_Website_Manager_JSON_Exporter();
        $this->init_hooks();
    }

    /**
     * אתחול hooks
     */
    private function init_hooks()
    {
        // AJAX endpoints
        add_action('wp_ajax_ai_download_sample', [$this, 'handle_download_sample']);
        add_action('wp_ajax_ai_download_all_samples', [$this, 'handle_download_all_samples']);
        add_action('wp_ajax_ai_export_brand', [$this, 'handle_export_brand']);

        // Cleanup cron
        add_action('ai_manager_cleanup_temp_files', [$this, 'cleanup_temp_files']);

        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('ai_manager_cleanup_temp_files')) {
            wp_schedule_event(time(), 'daily', 'ai_manager_cleanup_temp_files');
        }
    }

    /**
     * טיפול בהורדת דוגמה בודדת
     */
    public function handle_download_sample()
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
            $result = $this->json_exporter->export_sample($sample_type);

            if ($result['success']) {
                // יצירת קובץ זמני
                $temp_file = $this->create_temp_file($result['data'], $result['filename']);

                if ($temp_file) {
                    $this->json_exporter->update_export_stats();

                    wp_send_json_success([
                        'download_url' => $this->get_download_url($temp_file),
                        'filename' => $result['filename'],
                        'message' => 'Sample exported successfully'
                    ]);
                } else {
                    wp_send_json_error('Failed to create temporary file');
                }
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Export failed: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בהורדת כל הדוגמאות
     */
    public function handle_download_all_samples()
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
            $result = $this->json_exporter->export_all_samples();

            if ($result['success']) {
                $this->json_exporter->update_export_stats();

                wp_send_json_success([
                    'download_url' => $this->get_download_url($result['zip_path']),
                    'filename' => $result['zip_filename'],
                    'files_count' => $result['files_count'],
                    'message' => 'All samples exported successfully'
                ]);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Export failed: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בייצוא מותג
     */
    public function handle_export_brand()
    {
        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // בדיקת nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $brand_id = intval($_POST['brand_id'] ?? 0);

        if (empty($brand_id)) {
            wp_send_json_error('Brand ID is required');
        }

        try {
            $result = $this->json_exporter->export_brand($brand_id);

            if ($result['success']) {
                // יצירת קובץ זמני
                $temp_file = $this->create_temp_file($result['data'], $result['filename']);

                if ($temp_file) {
                    $this->json_exporter->update_export_stats();

                    wp_send_json_success([
                        'download_url' => $this->get_download_url($temp_file),
                        'filename' => $result['filename'],
                        'message' => 'Brand exported successfully'
                    ]);
                } else {
                    wp_send_json_error('Failed to create temporary file');
                }
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Export failed: ' . $e->getMessage());
        }
    }

    /**
     * יצירת קובץ זמני
     */
    private function create_temp_file($content, $filename)
    {
        $upload_dir = wp_upload_dir();

        if (!$upload_dir['error']) {
            $file_path = $upload_dir['path'] . '/' . $filename;

            if (file_put_contents($file_path, $content) !== false) {
                return $file_path;
            }
        }

        return false;
    }

    /**
     * קבלת URL להורדה
     */
    public function get_download_url($file_path, $format = 'json')
    {
        $filename = basename($file_path);

        return add_query_arg([
            'action' => 'ai_serve_download',
            'file' => urlencode($filename),
            'nonce' => wp_create_nonce('ai_download_' . $filename)
        ], admin_url('admin-ajax.php'));
    }

    /**
     * הגשת קובץ להורדה
     */
    public function serve_download($file_path, $filename)
    {
        if (!file_exists($file_path)) {
            wp_die('File not found');
        }

        // בדיקת הרשאות
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // קביעת סוג התוכן
        $content_type = $this->get_content_type($file_path);

        // הגדרת headers
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        // ניקוי output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // הגשת הקובץ
        readfile($file_path);

        // מחיקת הקובץ הזמני אחרי ההורדה
        unlink($file_path);

        exit;
    }

    /**
     * קביעת סוג תוכן
     */
    private function get_content_type($file_path)
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'json':
                return 'application/json';
            case 'zip':
                return 'application/zip';
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * ניקוי קבצים זמניים
     */
    public function cleanup_temp_files()
    {
        $cleaned = $this->json_exporter->cleanup_temp_files();

        // לוג הניקוי
        error_log("AI Manager: Cleaned {$cleaned} temporary files");

        return $cleaned;
    }

    /**
     * רישום endpoint להגשת קבצים
     */
    public function register_download_endpoint()
    {
        add_action('wp_ajax_ai_serve_download', [$this, 'handle_serve_download']);
    }

    /**
     * טיפול בהגשת קובץ
     */
    public function handle_serve_download()
    {
        $filename = sanitize_file_name($_GET['file'] ?? '');
        $nonce = $_GET['nonce'] ?? '';

        if (empty($filename) || !wp_verify_nonce($nonce, 'ai_download_' . $filename)) {
            wp_die('Invalid download request');
        }

        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;

        $this->serve_download($file_path, $filename);
    }

    /**
     * קבלת רשימת קבצים זמינים להורדה
     */
    public function get_available_downloads()
    {
        $upload_dir = wp_upload_dir()['path'];
        $files = [];

        // חיפוש קבצי JSON
        $json_files = glob($upload_dir . '/brand_sample_*.json');
        foreach ($json_files as $file) {
            $files[] = [
                'type' => 'json',
                'filename' => basename($file),
                'size' => filesize($file),
                'created' => filemtime($file),
                'download_url' => $this->get_download_url($file)
            ];
        }

        // חיפוש קבצי ZIP
        $zip_files = glob($upload_dir . '/ai_brand_samples_*.zip');
        foreach ($zip_files as $file) {
            $files[] = [
                'type' => 'zip',
                'filename' => basename($file),
                'size' => filesize($file),
                'created' => filemtime($file),
                'download_url' => $this->get_download_url($file)
            ];
        }

        // מיון לפי תאריך יצירה (החדשים ראשונים)
        usort($files, function ($a, $b) {
            return $b['created'] - $a['created'];
        });

        return $files;
    }
}

// אתחול המחלקה
new AI_Website_Manager_Sample_Downloader();