<?php
/**
 * Multi-Channel Content Manager
 * מנהל תוכן רב-ערוצי למקסום יצירת תוכן למותג
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Multi_Channel_Content_Manager
{

    private $brand_manager;
    private $content_generator;
    private $template_engine;

    // ערוצי תוכן זמינים
    private $content_channels = [
        'blog_posts' => [
            'name' => 'פוסטי בלוג',
            'icon' => '📝',
            'types' => ['how_to', 'listicle', 'case_study', 'news', 'opinion', 'tutorial'],
            'frequency' => 'weekly'
        ],
        'social_media' => [
            'name' => 'רשתות חברתיות',
            'icon' => '📱',
            'types' => ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok'],
            'frequency' => 'daily'
        ],
        'email_marketing' => [
            'name' => 'שיווק באימייל',
            'icon' => '📧',
            'types' => ['newsletter', 'promotional', 'welcome', 'follow_up', 'announcement'],
            'frequency' => 'weekly'
        ],
        'product_content' => [
            'name' => 'תוכן מוצרים',
            'icon' => '🛍️',
            'types' => ['description', 'features', 'benefits', 'comparison', 'review'],
            'frequency' => 'as_needed'
        ],
        'video_scripts' => [
            'name' => 'תסריטי וידאו',
            'icon' => '🎬',
            'types' => ['explainer', 'testimonial', 'demo', 'behind_scenes', 'tutorial'],
            'frequency' => 'bi_weekly'
        ],
        'press_releases' => [
            'name' => 'הודעות לעיתונות',
            'icon' => '📰',
            'types' => ['announcement', 'launch', 'partnership', 'achievement', 'event'],
            'frequency' => 'monthly'
        ],
        'landing_pages' => [
            'name' => 'דפי נחיתה',
            'icon' => '🎯',
            'types' => ['sales', 'lead_gen', 'event', 'product', 'service'],
            'frequency' => 'as_needed'
        ],
        'ad_copy' => [
            'name' => 'קופי לפרסומות',
            'icon' => '📢',
            'types' => ['google_ads', 'facebook_ads', 'display', 'native', 'video_ads'],
            'frequency' => 'weekly'
        ]
    ];

    public function __construct()
    {
        $this->brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        $this->content_generator = new AI_Website_Manager_Advanced_Content_Generator();
        $this->template_engine = new AI_Website_Manager_Content_Template_Engine();
    }

    /**
     * יצירת תוכן רב-ערוצי למותג
     */
    public function generate_multi_channel_content($brand_id, $content_plan = [])
    {
        try {
            $brand = $this->brand_manager->get_brand($brand_id);
            if (!$brand) {
                throw new Exception('מותג לא נמצא');
            }

            $results = [];
            $total_content_pieces = 0;

            // אם לא סופק תוכנית תוכן, צור תוכנית ברירת מחדל
            if (empty($content_plan)) {
                $content_plan = $this->generate_default_content_plan($brand);
            }

            foreach ($content_plan as $channel => $channel_plan) {
                if (!isset($this->content_channels[$channel])) {
                    continue;
                }

                $channel_results = [];

                foreach ($channel_plan['topics'] as $topic_data) {
                    $topic = is_array($topic_data) ? $topic_data['topic'] : $topic_data;
                    $content_type = is_array($topic_data) ? $topic_data['type'] : $channel_plan['default_type'];

                    // יצירת תוכן לערוץ ספציפי
                    $content_result = $this->generate_channel_content(
                        $brand,
                        $channel,
                        $content_type,
                        $topic,
                        $channel_plan['options'] ?? []
                    );

                    if ($content_result['success']) {
                        $channel_results[] = $content_result;
                        $total_content_pieces++;
                    }
                }

                $results[$channel] = [
                    'channel_name' => $this->content_channels[$channel]['name'],
                    'icon' => $this->content_channels[$channel]['icon'],
                    'content_pieces' => count($channel_results),
                    'results' => $channel_results
                ];
            }

            // שמירת סטטיסטיקות
            $this->save_content_generation_stats($brand_id, $total_content_pieces, $results);

            return [
                'success' => true,
                'brand_name' => $brand['name'],
                'total_content_pieces' => $total_content_pieces,
                'channels' => $results,
                'generated_at' => current_time('mysql')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * יצירת תוכן לערוץ ספציפי
     */
    private function generate_channel_content($brand, $channel, $content_type, $topic, $options = [])
    {
        try {
            // בניית פרמטרים מותאמים לערוץ
            $channel_params = $this->build_channel_params($channel, $content_type, $options);

            // יצירת התוכן
            $content_result = $this->content_generator->generate_advanced_content(
                $channel,
                $topic,
                array_merge($channel_params, [
                    'brand_context' => $brand,
                    'content_type' => $content_type
                ])
            );

            if ($content_result['success']) {
                // הוספת מטא-דאטה ספציפית לערוץ
                $content_result['channel'] = $channel;
                $content_result['content_type'] = $content_type;
                $content_result['topic'] = $topic;
                $content_result['channel_name'] = $this->content_channels[$channel]['name'];

                // יצירת גרסאות נוספות אם נדרש
                if ($options['create_variations'] ?? false) {
                    $content_result['variations'] = $this->create_content_variations(
                        $content_result['content'],
                        $channel,
                        $content_type
                    );
                }
            }

            return $content_result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'channel' => $channel,
                'topic' => $topic
            ];
        }
    }

    /**
     * בניית פרמטרים מותאמים לערוץ
     */
    private function build_channel_params($channel, $content_type, $options)
    {
        $base_params = [
            'target_length' => $options['target_length'] ?? null,
            'tone_adjustment' => $options['tone_adjustment'] ?? null,
            'specific_keywords' => $options['keywords'] ?? [],
            'call_to_action' => $options['cta'] ?? null
        ];

        // פרמטרים ספציפיים לכל ערוץ
        switch ($channel) {
            case 'blog_posts':
                $base_params['target_length'] = $base_params['target_length'] ?? 800;
                $base_params['include_seo'] = true;
                $base_params['include_meta_description'] = true;
                break;

            case 'social_media':
                $base_params['target_length'] = $base_params['target_length'] ?? 150;
                $base_params['include_hashtags'] = true;
                $base_params['include_emojis'] = true;
                $base_params['engagement_focused'] = true;
                break;

            case 'email_marketing':
                $base_params['target_length'] = $base_params['target_length'] ?? 300;
                $base_params['include_subject_line'] = true;
                $base_params['personalization'] = true;
                break;

            case 'product_content':
                $base_params['target_length'] = $base_params['target_length'] ?? 200;
                $base_params['benefits_focused'] = true;
                $base_params['include_features'] = true;
                break;

            case 'video_scripts':
                $base_params['target_length'] = $base_params['target_length'] ?? 500;
                $base_params['include_timing'] = true;
                $base_params['visual_cues'] = true;
                break;

            case 'ad_copy':
                $base_params['target_length'] = $base_params['target_length'] ?? 100;
                $base_params['conversion_focused'] = true;
                $base_params['urgency'] = true;
                break;
        }

        return $base_params;
    }

    /**
     * יצירת תוכנית תוכן ברירת מחדל
     */
    private function generate_default_content_plan($brand)
    {
        $keywords = $brand['keywords'] ?? ['עסק', 'שירות', 'איכות'];
        $industry = $brand['industry'] ?? 'כללי';

        // יצירת נושאים בהתבסס על מילות המפתח והתעשייה
        $base_topics = $this->generate_topics_from_brand($brand);

        return [
            'blog_posts' => [
                'default_type' => 'how_to',
                'topics' => array_slice($base_topics, 0, 4),
                'options' => ['target_length' => 800, 'create_variations' => false]
            ],
            'social_media' => [
                'default_type' => 'facebook',
                'topics' => array_merge(
                    array_slice($base_topics, 0, 3),
                    ['שאלה לקהילה', 'טיפ יומי', 'השראה']
                ),
                'options' => ['target_length' => 150, 'create_variations' => true]
            ],
            'email_marketing' => [
                'default_type' => 'newsletter',
                'topics' => array_slice($base_topics, 0, 2),
                'options' => ['target_length' => 300, 'create_variations' => false]
            ],
            'product_content' => [
                'default_type' => 'description',
                'topics' => ['המוצר הראשי', 'השירות המוביל'],
                'options' => ['target_length' => 200, 'create_variations' => true]
            ]
        ];
    }

    /**
     * יצירת נושאים מהמותג
     */
    private function generate_topics_from_brand($brand)
    {
        $topics = [];
        $keywords = $brand['keywords'] ?? [];
        $industry = $brand['industry'] ?? '';
        $values = $brand['values'] ?? [];

        // נושאים מבוססי מילות מפתח
        foreach ($keywords as $keyword) {
            $topics[] = "המדריך המלא ל{$keyword}";
            $topics[] = "5 טיפים חשובים על {$keyword}";
            $topics[] = "איך {$keyword} משפיע על העסק שלך";
        }

        // נושאים מבוססי תעשייה
        if ($industry) {
            $topics[] = "מגמות חדשות בתחום {$industry}";
            $topics[] = "אתגרים נפוצים ב{$industry}";
            $topics[] = "עתיד ה{$industry}";
        }

        // נושאים מבוססי ערכים
        foreach ($values as $value) {
            $topics[] = "למה {$value} חשוב בעסק";
            $topics[] = "איך להטמיע {$value} בארגון";
        }

        // נושאים כלליים
        $general_topics = [
            'טיפים למתחילים',
            'שגיאות נפוצות להימנע מהן',
            'מה חדש בתחום',
            'סיפורי הצלחה',
            'מדריך צעד אחר צעד',
            'השוואה בין אפשרויות',
            'עתיד התחום',
            'טרנדים חמים'
        ];

        $topics = array_merge($topics, $general_topics);

        return array_unique($topics);
    }

    /**
     * יצירת וריאציות תוכן
     */
    private function create_content_variations($original_content, $channel, $content_type)
    {
        $variations = [];

        // וריאציות לפי אורך
        if ($channel === 'social_media') {
            $variations['short'] = $this->create_short_version($original_content);
            $variations['long'] = $this->create_long_version($original_content);
        }

        // וריאציות לפי טון
        $variations['formal'] = $this->adjust_tone($original_content, 'formal');
        $variations['casual'] = $this->adjust_tone($original_content, 'casual');

        // וריאציות לפי פלטפורמה (לרשתות חברתיות)
        if ($channel === 'social_media') {
            $variations['facebook'] = $this->adapt_for_platform($original_content, 'facebook');
            $variations['instagram'] = $this->adapt_for_platform($original_content, 'instagram');
            $variations['linkedin'] = $this->adapt_for_platform($original_content, 'linkedin');
        }

        return $variations;
    }

    /**
     * יצירת גרסה קצרה
     */
    private function create_short_version($content)
    {
        $sentences = explode('.', $content);
        $short_sentences = array_slice($sentences, 0, 2);
        return implode('.', $short_sentences) . '.';
    }

    /**
     * יצירת גרסה ארוכה
     */
    private function create_long_version($content)
    {
        return $content . "\n\nמה דעתכם? שתפו את המחשבות שלכם בתגובות! 💭";
    }

    /**
     * התאמת טון
     */
    private function adjust_tone($content, $tone)
    {
        // זוהי פונקציה בסיסית - ניתן לשפר עם AI
        switch ($tone) {
            case 'formal':
                return str_replace(['אתם', 'שלכם'], ['הנכם', 'שלכם'], $content);
            case 'casual':
                return str_replace(['הנכם', 'אנו'], ['אתם', 'אנחנו'], $content);
            default:
                return $content;
        }
    }

    /**
     * התאמה לפלטפורמה
     */
    private function adapt_for_platform($content, $platform)
    {
        switch ($platform) {
            case 'facebook':
                return $content . "\n\n#פייסבוק #תוכן #שיתוף";
            case 'instagram':
                return $content . "\n\n📸 #אינסטגרם #תמונה #סטורי";
            case 'linkedin':
                return $content . "\n\n🔗 #לינקדאין #מקצועי #רשת";
            default:
                return $content;
        }
    }

    /**
     * שמירת סטטיסטיקות יצירת תוכן
     */
    private function save_content_generation_stats($brand_id, $total_pieces, $results)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_generation_stats';

        // יצירת הטבלה אם לא קיימת
        $this->create_stats_table();

        $wpdb->insert(
            $table_name,
            [
                'brand_id' => $brand_id,
                'total_content_pieces' => $total_pieces,
                'channels_data' => json_encode($results, JSON_UNESCAPED_UNICODE),
                'generated_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
    }

    /**
     * יצירת טבלת סטטיסטיקות
     */
    private function create_stats_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_generation_stats';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            brand_id int(11) NOT NULL,
            total_content_pieces int(11) NOT NULL,
            channels_data longtext,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_brand_id (brand_id),
            KEY idx_generated_at (generated_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * קבלת סטטיסטיקות יצירת תוכן
     */
    public function get_content_generation_stats($brand_id = null, $days = 30)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ai_content_generation_stats';

        $where_clause = "WHERE generated_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)";
        if ($brand_id) {
            $where_clause .= $wpdb->prepare(" AND brand_id = %d", $brand_id);
        }

        $results = $wpdb->get_results("
            SELECT 
                brand_id,
                SUM(total_content_pieces) as total_pieces,
                COUNT(*) as generation_sessions,
                MAX(generated_at) as last_generation
            FROM {$table_name}
            {$where_clause}
            GROUP BY brand_id
            ORDER BY total_pieces DESC
        ");

        return $results;
    }

    /**
     * קבלת ערוצי תוכן זמינים
     */
    public function get_available_channels()
    {
        return $this->content_channels;
    }

    /**
     * יצירת תוכנית תוכן מותאמת אישית
     */
    public function create_custom_content_plan($brand_id, $channels, $topics_per_channel = 3)
    {
        $brand = $this->brand_manager->get_brand($brand_id);
        if (!$brand) {
            return false;
        }

        $base_topics = $this->generate_topics_from_brand($brand);
        $content_plan = [];

        foreach ($channels as $channel) {
            if (!isset($this->content_channels[$channel])) {
                continue;
            }

            $channel_topics = array_slice($base_topics, 0, $topics_per_channel);
            shuffle($channel_topics); // ערבוב לגיוון

            $content_plan[$channel] = [
                'default_type' => $this->content_channels[$channel]['types'][0],
                'topics' => $channel_topics,
                'options' => [
                    'create_variations' => true,
                    'target_length' => $this->get_default_length_for_channel($channel)
                ]
            ];
        }

        return $content_plan;
    }

    /**
     * קבלת אורך ברירת מחדל לערוץ
     */
    private function get_default_length_for_channel($channel)
    {
        $default_lengths = [
            'blog_posts' => 800,
            'social_media' => 150,
            'email_marketing' => 300,
            'product_content' => 200,
            'video_scripts' => 500,
            'press_releases' => 400,
            'landing_pages' => 600,
            'ad_copy' => 100
        ];

        return $default_lengths[$channel] ?? 300;
    }

    /**
     * יצירת תוכן בצורה אסינכרונית (לכמויות גדולות)
     */
    public function generate_content_async($brand_id, $content_plan)
    {
        // שמירת המשימה במסד הנתונים
        global $wpdb;

        $task_data = [
            'brand_id' => $brand_id,
            'content_plan' => json_encode($content_plan, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ];

        $wpdb->insert(
            $wpdb->prefix . 'ai_content_generation_tasks',
            $task_data,
            ['%d', '%s', '%s', '%s']
        );

        $task_id = $wpdb->insert_id;

        // תזמון המשימה עם WordPress Cron
        wp_schedule_single_event(time() + 60, 'ai_manager_process_content_generation', [$task_id]);

        return $task_id;
    }
}