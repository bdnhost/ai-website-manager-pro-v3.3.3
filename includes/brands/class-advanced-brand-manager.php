<?php
/**
 * Advanced Brand Manager
 * מנהל מותגים מתקדם עם ייבוא/ייצוא JSON ותכונות נוספות
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Advanced_Brand_Manager
{

    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ai_website_manager_brands';
    }

    /**
     * יצירת טבלת מותגים מתקדמת
     */
    public function create_brands_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            industry varchar(100),
            target_audience text,
            brand_voice varchar(50),
            tone_of_voice varchar(50),
            keywords text,
            values text,
            mission text,
            vision text,
            unique_selling_proposition text,
            competitor_analysis text,
            content_pillars text,
            brand_colors text,
            typography text,
            logo_url varchar(500),
            website_url varchar(500),
            social_media_links text,
            contact_info text,
            brand_guidelines text,
            content_templates text,
            seo_settings text,
            analytics_goals text,
            is_active tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_name (name),
            KEY idx_industry (industry),
            KEY idx_is_active (is_active)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * שמירת מותג מתקדם
     */
    public function save_advanced_brand($brand_data)
    {
        global $wpdb;

        // ולידציה של נתונים
        $validated_data = $this->validate_brand_data($brand_data);

        if (isset($brand_data['id']) && $brand_data['id'] > 0) {
            // עדכון מותג קיים
            $result = $wpdb->update(
                $this->table_name,
                $validated_data,
                ['id' => $brand_data['id']],
                $this->get_data_format(),
                ['%d']
            );
        } else {
            // יצירת מותג חדש
            $result = $wpdb->insert(
                $this->table_name,
                $validated_data,
                $this->get_data_format()
            );
        }

        if ($result === false) {
            throw new Exception('שגיאה בשמירת המותג: ' . $wpdb->last_error);
        }

        return $wpdb->insert_id ?: $brand_data['id'];
    }

    /**
     * ולידציה של נתוני מותג
     */
    private function validate_brand_data($data)
    {
        $validated = [];

        // שדות חובה
        $validated['name'] = sanitize_text_field($data['name'] ?? '');
        if (empty($validated['name'])) {
            throw new Exception('שם המותג הוא שדה חובה');
        }

        // שדות טקסט
        $text_fields = [
            'description',
            'industry',
            'target_audience',
            'brand_voice',
            'tone_of_voice',
            'mission',
            'vision',
            'unique_selling_proposition',
            'brand_guidelines',
            'logo_url',
            'website_url'
        ];

        foreach ($text_fields as $field) {
            $validated[$field] = sanitize_textarea_field($data[$field] ?? '');
        }

        // שדות JSON
        $json_fields = [
            'keywords',
            'values',
            'competitor_analysis',
            'content_pillars',
            'brand_colors',
            'typography',
            'social_media_links',
            'contact_info',
            'content_templates',
            'seo_settings',
            'analytics_goals'
        ];

        foreach ($json_fields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    $validated[$field] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
                } else {
                    $validated[$field] = $data[$field];
                }
            }
        }

        // שדה פעיל
        $validated['is_active'] = isset($data['is_active']) ? (int) $data['is_active'] : 0;

        return $validated;
    }

    /**
     * פורמט נתונים לשמירה
     */
    private function get_data_format()
    {
        return [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        ];
    }

    /**
     * קבלת מותג לפי ID
     */
    public function get_brand($brand_id)
    {
        global $wpdb;

        $brand = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $brand_id),
            ARRAY_A
        );

        if ($brand) {
            return $this->parse_brand_data($brand);
        }

        return null;
    }

    /**
     * קבלת כל המותגים
     */
    public function get_all_brands()
    {
        global $wpdb;

        $brands = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY name ASC", ARRAY_A);

        $parsed_brands = [];
        foreach ($brands as $brand) {
            $parsed_brands[] = $this->parse_brand_data($brand);
        }

        return $parsed_brands;
    }

    /**
     * קבלת המותג הפעיל
     */
    public function get_active_brand()
    {
        global $wpdb;

        $brand = $wpdb->get_row(
            "SELECT * FROM {$this->table_name} WHERE is_active = 1 LIMIT 1",
            ARRAY_A
        );

        if ($brand) {
            return $this->parse_brand_data($brand);
        }

        return null;
    }

    /**
     * הפעלת מותג
     */
    public function activate_brand($brand_id)
    {
        global $wpdb;

        // ביטול הפעלה של כל המותגים
        $wpdb->update(
            $this->table_name,
            ['is_active' => 0],
            [],
            ['%d']
        );

        // הפעלת המותג הנבחר
        $result = $wpdb->update(
            $this->table_name,
            ['is_active' => 1],
            ['id' => $brand_id],
            ['%d'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * מחיקת מותג
     */
    public function delete_brand($brand_id)
    {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            ['id' => $brand_id],
            ['%d']
        ) !== false;
    }

    /**
     * ייצוא מותג ל-JSON
     */
    public function export_brand_to_json($brand_id)
    {
        $brand = $this->get_brand($brand_id);

        if (!$brand) {
            throw new Exception('מותג לא נמצא');
        }

        // הסרת שדות מערכת
        unset($brand['id'], $brand['created_at'], $brand['updated_at'], $brand['is_active']);

        return json_encode($brand, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * ייבוא מותג מ-JSON
     */
    public function import_brand_from_json($json_data, $brand_name = null)
    {
        $brand_data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('קובץ JSON לא תקין: ' . json_last_error_msg());
        }

        // אם סופק שם חדש למותג
        if ($brand_name) {
            $brand_data['name'] = $brand_name;
        }

        // בדיקה אם מותג עם השם הזה כבר קיים
        if ($this->brand_exists($brand_data['name'])) {
            throw new Exception('מותג עם השם "' . $brand_data['name'] . '" כבר קיים');
        }

        return $this->save_advanced_brand($brand_data);
    }

    /**
     * בדיקה אם מותג קיים
     */
    private function brand_exists($brand_name)
    {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE name = %s",
                $brand_name
            )
        );

        return $count > 0;
    }

    /**
     * פרסור נתוני מותג
     */
    private function parse_brand_data($brand)
    {
        $json_fields = [
            'keywords',
            'values',
            'competitor_analysis',
            'content_pillars',
            'brand_colors',
            'typography',
            'social_media_links',
            'contact_info',
            'content_templates',
            'seo_settings',
            'analytics_goals'
        ];

        foreach ($json_fields as $field) {
            if (isset($brand[$field]) && !empty($brand[$field])) {
                $decoded = json_decode($brand[$field], true);
                $brand[$field] = $decoded ?: $brand[$field];
            }
        }

        return $brand;
    }

    /**
     * יצירת תבנית מותג ברירת מחדל
     */
    public function create_default_brand_template()
    {
        return [
            'name' => '',
            'description' => '',
            'industry' => '',
            'target_audience' => [
                'demographics' => '',
                'psychographics' => '',
                'pain_points' => [],
                'goals' => []
            ],
            'brand_voice' => 'professional', // professional, friendly, authoritative, casual
            'tone_of_voice' => 'informative', // informative, inspiring, conversational, urgent
            'keywords' => [],
            'values' => [],
            'mission' => '',
            'vision' => '',
            'unique_selling_proposition' => '',
            'competitor_analysis' => [
                'main_competitors' => [],
                'competitive_advantages' => [],
                'market_positioning' => ''
            ],
            'content_pillars' => [
                'pillar_1' => '',
                'pillar_2' => '',
                'pillar_3' => '',
                'pillar_4' => ''
            ],
            'brand_colors' => [
                'primary' => '#000000',
                'secondary' => '#ffffff',
                'accent' => '#ff0000'
            ],
            'typography' => [
                'primary_font' => 'Arial',
                'secondary_font' => 'Helvetica'
            ],
            'logo_url' => '',
            'website_url' => '',
            'social_media_links' => [
                'facebook' => '',
                'instagram' => '',
                'twitter' => '',
                'linkedin' => '',
                'youtube' => ''
            ],
            'contact_info' => [
                'email' => '',
                'phone' => '',
                'address' => ''
            ],
            'brand_guidelines' => '',
            'content_templates' => [
                'blog_post_template' => '',
                'social_media_template' => '',
                'email_template' => ''
            ],
            'seo_settings' => [
                'focus_keywords' => [],
                'meta_description_template' => '',
                'title_template' => ''
            ],
            'analytics_goals' => [
                'primary_goal' => '',
                'secondary_goals' => [],
                'kpis' => []
            ]
        ];
    }

    /**
     * קבלת סטטיסטיקות מותגים
     */
    public function get_brands_statistics()
    {
        global $wpdb;

        $stats = [];

        // סך הכל מותגים
        $stats['total_brands'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        // מותגים פעילים
        $stats['active_brands'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE is_active = 1");

        // מותגים לפי תעשייה
        $stats['by_industry'] = $wpdb->get_results(
            "SELECT industry, COUNT(*) as count FROM {$this->table_name} 
             WHERE industry != '' GROUP BY industry ORDER BY count DESC",
            ARRAY_A
        );

        // מותגים שנוצרו השבוע
        $stats['created_this_week'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );

        return $stats;
    }

    /**
     * חיפוש מותגים
     */
    public function search_brands($search_term)
    {
        global $wpdb;

        $search_term = '%' . $wpdb->esc_like($search_term) . '%';

        $brands = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                 WHERE name LIKE %s OR description LIKE %s OR industry LIKE %s
                 ORDER BY name ASC",
                $search_term,
                $search_term,
                $search_term
            ),
            ARRAY_A
        );

        $parsed_brands = [];
        foreach ($brands as $brand) {
            $parsed_brands[] = $this->parse_brand_data($brand);
        }

        return $parsed_brands;
    }
}