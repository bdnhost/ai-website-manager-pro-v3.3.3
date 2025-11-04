<?php
/**
 * Multi-Channel Content AJAX Handlers
 * מטפלי AJAX לתוכן רב-ערוצי
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Multi_Channel_Content_Ajax
{

    private $multi_channel_manager;

    public function __construct()
    {
        $this->multi_channel_manager = new AI_Website_Manager_Multi_Channel_Content_Manager();
        $this->init_hooks();
    }

    /**
     * אתחול hooks
     */
    private function init_hooks()
    {
        add_action('wp_ajax_generate_multi_channel_content', [$this, 'generate_multi_channel_content']);
        add_action('wp_ajax_get_content_generation_stats', [$this, 'get_content_generation_stats']);
        add_action('wp_ajax_save_content_plan', [$this, 'save_content_plan']);
        add_action('wp_ajax_load_content_plan', [$this, 'load_content_plan']);
        add_action('wp_ajax_export_content_results', [$this, 'export_content_results']);
        add_action('wp_ajax_create_wordpress_posts', [$this, 'create_wordpress_posts']);

        // Cron hook לעיבוד אסינכרוני
        add_action('ai_manager_process_content_generation', [$this, 'process_async_content_generation']);
    }

    /**
     * יצירת תוכן רב-ערוצי
     */
    public function generate_multi_channel_content()
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
            $content_plan = json_decode(stripslashes($_POST['content_plan']), true);
            $async_generation = isset($_POST['async_generation']) && $_POST['async_generation'] === 'true';

            if ($brand_id <= 0) {
                throw new Exception('מזהה מותג לא תקין');
            }

            if (empty($content_plan)) {
                throw new Exception('תוכנית תוכן חסרה');
            }

            // אם נבחרה יצירה אסינכרונית
            if ($async_generation) {
                $task_id = $this->multi_channel_manager->generate_content_async($brand_id, $content_plan);

                wp_send_json_success([
                    'async' => true,
                    'task_id' => $task_id,
                    'message' => 'המשימה נוספה לתור. תקבל התראה כשתסתיים.'
                ]);
                return;
            }

            // יצירה סינכרונית
            $result = $this->multi_channel_manager->generate_multi_channel_content($brand_id, $content_plan);

            if ($result['success']) {
                // רישום לוג
                $this->log_content_generation($brand_id, $result['total_content_pieces']);

                wp_send_json_success($result);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * קבלת סטטיסטיקות יצירת תוכן
     */
    public function get_content_generation_stats()
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

            $brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : null;
            $days = isset($_POST['days']) ? intval($_POST['days']) : 30;

            $stats = $this->multi_channel_manager->get_content_generation_stats($brand_id, $days);

            wp_send_json_success($stats);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * שמירת תוכנית תוכן
     */
    public function save_content_plan()
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

            $plan_name = sanitize_text_field($_POST['plan_name']);
            $brand_id = intval($_POST['brand_id']);
            $content_plan = json_decode(stripslashes($_POST['content_plan']), true);

            if (empty($plan_name)) {
                throw new Exception('שם התוכנית נדרש');
            }

            // שמירה במסד הנתונים
            global $wpdb;

            $table_name = $wpdb->prefix . 'ai_content_plans';
            $this->create_content_plans_table();

            $result = $wpdb->insert(
                $table_name,
                [
                    'plan_name' => $plan_name,
                    'brand_id' => $brand_id,
                    'content_plan' => json_encode($content_plan, JSON_UNESCAPED_UNICODE),
                    'created_by' => get_current_user_id(),
                    'created_at' => current_time('mysql')
                ],
                ['%s', '%d', '%s', '%d', '%s']
            );

            if ($result === false) {
                throw new Exception('שגיאה בשמירת התוכנית');
            }

            wp_send_json_success([
                'plan_id' => $wpdb->insert_id,
                'message' => 'התוכנית נשמרה בהצלחה'
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * טעינת תוכנית תוכן
     */
    public function load_content_plan()
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

            $plan_id = intval($_POST['plan_id']);

            if ($plan_id <= 0) {
                // החזרת רשימת תוכניות זמינות
                $plans = $this->get_available_content_plans();
                wp_send_json_success(['plans' => $plans]);
                return;
            }

            // טעינת תוכנית ספציפית
            global $wpdb;

            $table_name = $wpdb->prefix . 'ai_content_plans';
            $plan = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $plan_id),
                ARRAY_A
            );

            if (!$plan) {
                throw new Exception('תוכנית לא נמצאה');
            }

            $plan['content_plan'] = json_decode($plan['content_plan'], true);

            wp_send_json_success($plan);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * ייצוא תוצאות תוכן
     */
    public function export_content_results()
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

            $results_data = json_decode(stripslashes($_POST['results_data']), true);
            $export_format = sanitize_text_field($_POST['export_format'] ?? 'json');

            if (empty($results_data)) {
                throw new Exception('אין נתונים לייצוא');
            }

            $exported_data = $this->format_export_data($results_data, $export_format);
            $filename = 'content_results_' . date('Y-m-d_H-i-s') . '.' . $export_format;

            wp_send_json_success([
                'data' => $exported_data,
                'filename' => $filename,
                'format' => $export_format
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * יצירת פוסטי WordPress מהתוכן
     */
    public function create_wordpress_posts()
    {
        try {
            // בדיקת הרשאות
            if (!current_user_can('edit_posts')) {
                throw new Exception('אין הרשאה ליצירת פוסטים');
            }

            // בדיקת nonce
            if (!wp_verify_nonce($_POST['nonce'], 'ai_website_manager_nonce')) {
                throw new Exception('בדיקת אבטחה נכשלה');
            }

            $content_items = json_decode(stripslashes($_POST['content_items']), true);
            $post_status = sanitize_text_field($_POST['post_status'] ?? 'draft');

            if (empty($content_items)) {
                throw new Exception('אין תוכן ליצירת פוסטים');
            }

            $created_posts = [];
            $errors = [];

            foreach ($content_items as $item) {
                try {
                    $post_data = [
                        'post_title' => $item['title'] ?? 'תוכן חדש',
                        'post_content' => $item['content'] ?? '',
                        'post_status' => $post_status,
                        'post_type' => 'post',
                        'meta_input' => [
                            'ai_generated' => true,
                            'ai_channel' => $item['channel'] ?? '',
                            'ai_content_type' => $item['content_type'] ?? '',
                            'ai_brand_id' => $item['brand_id'] ?? null,
                            'ai_generation_date' => current_time('mysql')
                        ]
                    ];

                    $post_id = wp_insert_post($post_data);

                    if (is_wp_error($post_id)) {
                        $errors[] = 'שגיאה ביצירת פוסט: ' . $post_id->get_error_message();
                    } else {
                        $created_posts[] = [
                            'post_id' => $post_id,
                            'title' => $item['title'] ?? 'תוכן חדש',
                            'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit')
                        ];
                    }

                } catch (Exception $e) {
                    $errors[] = 'שגיאה ביצירת פוסט: ' . $e->getMessage();
                }
            }

            wp_send_json_success([
                'created_posts' => $created_posts,
                'errors' => $errors,
                'total_created' => count($created_posts),
                'total_errors' => count($errors)
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * עיבוד יצירת תוכן אסינכרונית
     */
    public function process_async_content_generation($task_id)
    {
        global $wpdb;

        try {
            // קבלת פרטי המשימה
            $task_table = $wpdb->prefix . 'ai_content_generation_tasks';
            $task = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$task_table} WHERE id = %d", $task_id),
                ARRAY_A
            );

            if (!$task) {
                throw new Exception('משימה לא נמצאה');
            }

            // עדכון סטטוס למעבד
            $wpdb->update(
                $task_table,
                ['status' => 'processing', 'started_at' => current_time('mysql')],
                ['id' => $task_id],
                ['%s', '%s'],
                ['%d']
            );

            // יצירת התוכן
            $content_plan = json_decode($task['content_plan'], true);
            $result = $this->multi_channel_manager->generate_multi_channel_content(
                $task['brand_id'],
                $content_plan
            );

            if ($result['success']) {
                // עדכון סטטוס להושלם
                $wpdb->update(
                    $task_table,
                    [
                        'status' => 'completed',
                        'completed_at' => current_time('mysql'),
                        'result_data' => json_encode($result, JSON_UNESCAPED_UNICODE)
                    ],
                    ['id' => $task_id],
                    ['%s', '%s', '%s'],
                    ['%d']
                );

                // שליחת התראה למשתמש
                $this->send_completion_notification($task, $result);

            } else {
                // עדכון סטטוס לכשל
                $wpdb->update(
                    $task_table,
                    [
                        'status' => 'failed',
                        'completed_at' => current_time('mysql'),
                        'error_message' => $result['error']
                    ],
                    ['id' => $task_id],
                    ['%s', '%s', '%s'],
                    ['%d']
                );
            }

        } catch (Exception $e) {
            // עדכון סטטוס לכשל
            $wpdb->update(
                $task_table,
                [
                    'status' => 'failed',
                    'completed_at' => current_time('mysql'),
                    'error_message' => $e->getMessage()
                ],
                ['id' => $task_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        }
    }

    /**
     * פונקציות עזר
     */

    /**
     * יצירת טבלת תוכניות תוכן
     */
    private function create_content_plans_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_plans';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            plan_name varchar(255) NOT NULL,
            brand_id int(11) NOT NULL,
            content_plan longtext NOT NULL,
            created_by int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_brand_id (brand_id),
            KEY idx_created_by (created_by)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * יצירת טבלת משימות אסינכרוניות
     */
    private function create_async_tasks_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_generation_tasks';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            brand_id int(11) NOT NULL,
            content_plan longtext NOT NULL,
            status enum('pending','processing','completed','failed') DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            started_at datetime NULL,
            completed_at datetime NULL,
            result_data longtext NULL,
            error_message text NULL,
            PRIMARY KEY (id),
            KEY idx_brand_id (brand_id),
            KEY idx_status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * קבלת תוכניות תוכן זמינות
     */
    private function get_available_content_plans()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_plans';

        return $wpdb->get_results(
            "SELECT id, plan_name, brand_id, created_at 
             FROM {$table_name} 
             ORDER BY created_at DESC 
             LIMIT 20",
            ARRAY_A
        );
    }

    /**
     * עיצוב נתוני ייצוא
     */
    private function format_export_data($results_data, $format)
    {
        switch ($format) {
            case 'csv':
                return $this->format_as_csv($results_data);
            case 'txt':
                return $this->format_as_text($results_data);
            case 'json':
            default:
                return json_encode($results_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * עיצוב כ-CSV
     */
    private function format_as_csv($results_data)
    {
        $csv_data = "Channel,Content Type,Title,Content,Generated At\n";

        foreach ($results_data['channels'] as $channel_id => $channel_data) {
            foreach ($channel_data['results'] as $result) {
                if ($result['success']) {
                    $csv_data .= sprintf(
                        '"%s","%s","%s","%s","%s"' . "\n",
                        $channel_data['channel_name'],
                        $result['content_type'] ?? '',
                        $result['metadata']['title'] ?? '',
                        str_replace('"', '""', strip_tags($result['content'])),
                        $results_data['generated_at']
                    );
                }
            }
        }

        return $csv_data;
    }

    /**
     * עיצוב כטקסט
     */
    private function format_as_text($results_data)
    {
        $text_data = "תוצאות יצירת תוכן רב-ערוצי\n";
        $text_data .= "מותג: " . $results_data['brand_name'] . "\n";
        $text_data .= "נוצר ב: " . $results_data['generated_at'] . "\n";
        $text_data .= "סך התכנים: " . $results_data['total_content_pieces'] . "\n\n";

        foreach ($results_data['channels'] as $channel_id => $channel_data) {
            $text_data .= "=== " . $channel_data['channel_name'] . " ===\n\n";

            foreach ($channel_data['results'] as $index => $result) {
                if ($result['success']) {
                    $text_data .= "תוכן " . ($index + 1) . ":\n";
                    $text_data .= "כותרת: " . ($result['metadata']['title'] ?? 'ללא כותרת') . "\n";
                    $text_data .= "תוכן:\n" . strip_tags($result['content']) . "\n\n";
                    $text_data .= "---\n\n";
                }
            }
        }

        return $text_data;
    }

    /**
     * שליחת התראת השלמה
     */
    private function send_completion_notification($task, $result)
    {
        // כאן ניתן להוסיף שליחת אימייל או התראה אחרת
        // לעת עתה נשמור הודעה ב-transient

        $notification_data = [
            'type' => 'content_generation_completed',
            'message' => sprintf(
                'יצירת התוכן הושלמה! נוצרו %d תכנים עבור %s',
                $result['total_content_pieces'],
                $result['brand_name']
            ),
            'task_id' => $task['id'],
            'result' => $result
        ];

        set_transient(
            'ai_content_notification_' . $task['created_by'],
            $notification_data,
            DAY_IN_SECONDS
        );
    }

    /**
     * רישום לוג יצירת תוכן
     */
    private function log_content_generation($brand_id, $content_count)
    {
        if (class_exists('AI_Website_Manager_Logger')) {
            $logger = new AI_Website_Manager_Logger();
            $logger->log(
                'info',
                'content_generation',
                "נוצרו {$content_count} תכנים עבור מותג {$brand_id}",
                [
                    'brand_id' => $brand_id,
                    'content_count' => $content_count,
                    'user_id' => get_current_user_id()
                ]
            );
        }
    }
}

// אתחול המחלקה
new AI_Website_Manager_Multi_Channel_Content_Ajax();