<?php
/**
 * Content Template Engine
 * מנוע תבניות מתקדם ליצירת תוכן מעוצב
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Content_Template_Engine
{

    private $templates = [];

    public function __construct()
    {
        $this->init_templates();
        // Register styles to be enqueued on the front-end and in the editor
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    /**
     * אתחול תבניות התוכן
     */
    private function init_templates()
    {
        $this->templates = [
            'blog_post' => [
                'structure' => [
                    'title_with_icon',
                    'intro_hook',
                    'reading_time',
                    'content_stats',
                    'main_content',
                    'key_points',
                    'call_to_action'
                ],
                'icons' => ['📝', '✍️', '📖', '💡', '🚀', '⭐'],
                'css_class' => 'ai-blog-post'
            ],
            'social_media' => [
                'structure' => [
                    'engaging_question',
                    'main_message',
                    'hashtags',
                    'call_to_action'
                ],
                'icons' => ['📱', '💬', '🔥', '✨', '👥', '🎯'],
                'css_class' => 'ai-social-post'
            ],
            'product_description' => [
                'structure' => [
                    'catchy_title',
                    'problem_solution',
                    'benefits_list',
                    'social_proof',
                    'urgency_scarcity',
                    'purchase_cta'
                ],
                'icons' => ['🛍️', '⭐', '✅', '🎁', '🔥', '💎'],
                'css_class' => 'ai-product-desc'
            ],
            'email_marketing' => [
                'structure' => [
                    'personal_greeting',
                    'value_proposition',
                    'main_content',
                    'clear_cta',
                    'signature'
                ],
                'icons' => ['📧', '💌', '🎯', '✨', '👋', '💝'],
                'css_class' => 'ai-email-content'
            ]
        ];
    }

    /**
     * טעינת קבצי העיצוב של התבניות
     */
    public function enqueue_styles()
    {
        // Enqueue a base stylesheet for all templates
        wp_enqueue_style(
            'ai-content-template-base',
            AI_MANAGER_PRO_PLUGIN_URL . 'assets/css/templates/base.css',
            [],
            AI_MANAGER_PRO_VERSION
        );

        // Enqueue specific stylesheets for each template type
        foreach (array_keys($this->templates) as $template_name) {
            $css_file_path = 'assets/css/templates/' . str_replace('_', '-', $template_name) . '.css';
            if (file_exists(AI_MANAGER_PRO_PLUGIN_DIR . $css_file_path)) {
                wp_enqueue_style(
                    'ai-content-template-' . $template_name,
                    AI_MANAGER_PRO_PLUGIN_URL . $css_file_path,
                    ['ai-content-template-base'],
                    AI_MANAGER_PRO_VERSION
                );
            }
        }
    }

    /**
     * יצירת תוכן מעוצב לפי תבנית
     */
    public function generate_styled_content($content_type, $content_data, $brand_info = null)
    {
        if (!isset($this->templates[$content_type])) {
            return $content_data['content'] ?? '';
        }

        $template = $this->templates[$content_type];
        $styled_content = $this->build_content_structure($template, $content_data, $brand_info);

        return $this->wrap_with_container($styled_content, $template['css_class']);
    }

    /**
     * בניית מבנה התוכן
     */
    private function build_content_structure($template, $content_data, $brand_info)
    {
        $content = '';
        $icons = $template['icons'];

        foreach ($template['structure'] as $section) {
            $content .= $this->build_section($section, $content_data, $brand_info, $icons);
        }

        return $content;
    }

    /**
     * בניית קטע בתוכן
     */
    private function build_section($section_type, $content_data, $brand_info, $icons)
    {
        $random_icon = $icons[array_rand($icons)];

        switch ($section_type) {
            case 'title_with_icon':
                return sprintf(
                    '<h1 class="content-title">%s %s</h1>',
                    $random_icon,
                    $content_data['title'] ?? 'כותרת'
                );

            case 'intro_hook':
                return sprintf(
                    '<div class="content-intro"><p class="hook">%s</p></div>',
                    $this->generate_hook($content_data, $brand_info)
                );

            case 'reading_time':
                $word_count = str_word_count(strip_tags($content_data['content'] ?? ''));
                $reading_time = max(1, ceil($word_count / 200));
                return sprintf(
                    '<div class="reading-time">⏱️ זמן קריאה: %d דקות</div>',
                    $reading_time
                );

            case 'content_stats':
                return sprintf(
                    '<div class="content-stats">📊 <strong>%s</strong></div>',
                    $this->generate_stat($content_data, $brand_info)
                );

            case 'main_content':
                return sprintf(
                    '<div class="main-content">%s</div>',
                    $this->format_main_content($content_data['content'] ?? '')
                );

            case 'key_points':
                return $this->generate_key_points($content_data, $icons);

            case 'call_to_action':
                return $this->generate_cta($content_data, $brand_info);

            case 'engaging_question':
                return sprintf(
                    '<div class="social-question">%s %s</div>',
                    $random_icon,
                    $this->generate_engaging_question($content_data)
                );

            case 'hashtags':
                return $this->generate_hashtags($content_data, $brand_info);

            default:
                return '';
        }
    }

    /**
     * יצירת hook מעניין
     */
    private function generate_hook($content_data, $brand_info)
    {
        $hooks = [
            'האם ידעת ש-%s?',
            'מחקר חדש מגלה: %s',
            'הסוד שאף אחד לא מספר לך על %s',
            'למה %s זה הדבר הבא שכולם מדברים עליו?',
            'התגלית המדהימה על %s שתשנה הכל'
        ];

        $hook_template = $hooks[array_rand($hooks)];
        $topic = $brand_info['keywords'][0] ?? 'הנושא הזה';

        return sprintf($hook_template, $topic);
    }

    /**
     * יצירת סטטיסטיקה מעניינת
     */
    private function generate_stat($content_data, $brand_info)
    {
        $stats = [
            'מחקר מ-2024 מראה שיפור של %d%% בתחום זה',
            '%d%% מהמומחים ממליצים על הגישה הזו',
            'יותר מ-%d אנשים כבר השתמשו בטכניקה הזו בהצלחה',
            'חוסך עד %d שעות בשבוע לפי המחקרים'
        ];

        $stat_template = $stats[array_rand($stats)];
        $number = rand(25, 85);

        return sprintf($stat_template, $number);
    }

    /**
     * עיצוב התוכן הראשי
     */
    private function format_main_content($content)
    {
        // המרת פסקאות לפורמט מעוצב
        $paragraphs = explode("\n\n", $content);
        $formatted = '';

        foreach ($paragraphs as $paragraph) {
            if (empty(trim($paragraph)))
                continue;

            // זיהוי רשימות
            if (strpos($paragraph, '•') !== false || strpos($paragraph, '-') !== false) {
                $formatted .= $this->format_list($paragraph);
            } else {
                $formatted .= sprintf('<p class="content-paragraph">%s</p>', trim($paragraph));
            }
        }

        return $formatted;
    }

    /**
     * עיצוב רשימות
     */
    private function format_list($list_content)
    {
        $items = preg_split('/[•\-]\s*/', $list_content);
        $formatted_items = '';

        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item))
                continue;

            $formatted_items .= sprintf(
                '<li><span class="list-icon">✅</span> %s</li>',
                $item
            );
        }

        return sprintf('<ul class="styled-list">%s</ul>', $formatted_items);
    }

    /**
     * יצירת נקודות מפתח
     */
    private function generate_key_points($content_data, $icons)
    {
        $points = [
            'הדבר החשוב ביותר לזכור',
            'הטעות הנפוצה שכולם עושים',
            'הטיפ שיחסוך לך הכי הרבה זמן',
            'מה שהמומחים לא אוהבים לספר'
        ];

        $key_points_html = '<div class="key-points">';
        $key_points_html .= sprintf('<h3>%s נקודות מפתח</h3>', $icons[0]);
        $key_points_html .= '<ul>';

        foreach (array_slice($points, 0, 3) as $point) {
            $icon = $icons[array_rand($icons)];
            $key_points_html .= sprintf('<li><span>%s</span> %s</li>', $icon, $point);
        }

        $key_points_html .= '</ul></div>';

        return $key_points_html;
    }

    /**
     * יצירת קריאה לפעולה
     */
    private function generate_cta($content_data, $brand_info)
    {
        $cta_texts = [
            'מוכן להתחיל? בוא נעשה את זה ביחד!',
            'הגיע הזמן לקחת את הצעד הבא',
            'אל תחכה יותר - התחל עכשיו!',
            'הצטרף לאלפים שכבר השיגו הצלחה'
        ];

        $cta_text = $cta_texts[array_rand($cta_texts)];
        $brand_name = $brand_info['name'] ?? 'המותג שלנו';

        return sprintf(
            '<div class="cta-box">
                <h3>🚀 %s</h3>
                <p>%s מזמין אותך להצטרף למהפכה</p>
                <a href="#" class="cta-button">בוא נתחיל!</a>
            </div>',
            $cta_text,
            $brand_name
        );
    }

    /**
     * יצירת שאלה מעוררת לרשתות חברתיות
     */
    private function generate_engaging_question($content_data)
    {
        $questions = [
            'מה הדבר הכי מעניין שלמדת השבוע?',
            'איך אתה מתמודד עם האתגר הזה?',
            'מה הטיפ הכי טוב שקיבלת אי פעם?',
            'איזה שינוי קטן עשה לך את ההבדל הכי גדול?'
        ];

        return $questions[array_rand($questions)];
    }

    /**
     * יצירת hashtags
     */
    private function generate_hashtags($content_data, $brand_info)
    {
        $base_tags = ['#טיפים', '#הצלחה', '#מוטיבציה', '#למידה'];
        $brand_tags = [];

        if ($brand_info && isset($brand_info['keywords'])) {
            foreach ($brand_info['keywords'] as $keyword) {
                $brand_tags[] = '#' . str_replace(' ', '', $keyword);
            }
        }

        $all_tags = array_merge($base_tags, array_slice($brand_tags, 0, 3));

        return sprintf(
            '<div class="social-hashtags">%s</div>',
            implode(' ', $all_tags)
        );
    }

    /**
     * עטיפת התוכן בקונטיינר
     */
    private function wrap_with_container($content, $css_class)
    {
        return sprintf(
            '<div class="ai-content-wrapper %s">%s</div>',
            $css_class,
            $content
        );
    }

    /**
     * קבלת תבניות זמינות
     */
    public function get_available_templates()
    {
        return array_keys($this->templates);
    }

    /**
     * קבלת מידע על תבנית
     */
    public function get_template_info($template_name)
    {
        return $this->templates[$template_name] ?? null;
    }
}