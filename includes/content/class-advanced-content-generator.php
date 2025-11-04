<?php
/**
 * Advanced Content Generator
 * מחולל תוכן מתקדם עם פרומפטים חכמים ואיכות גבוהה
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Advanced_Content_Generator
{

    private $template_engine;
    private $brand_manager;
    private $ai_service;

    public function __construct()
    {
        $this->template_engine = new AI_Website_Manager_Content_Template_Engine();
        $this->brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
        
        // טעינת שירות AI אם קיים
        if (class_exists('AI_Website_Manager_AI_Service')) {
            $this->ai_service = new AI_Website_Manager_AI_Service();
        } else {
            $this->ai_service = $this->create_mock_ai_service();
        }
    }
    
    /**
     * יצירת שירות AI מדומה לבדיקות
     */
    private function create_mock_ai_service() {
        return new class {
            public function generate_content($prompt, $options = []) {
                // תוכן מדומה לבדיקות
                return "זהו תוכן מדומה שנוצר עבור הפרומפט: " . substr($prompt, 0, 100) . "...";
    }

    /**
     * יצירת תוכן מתקדם
     */
    public function generate_advanced_content($content_type, $topic, $additional_params = [])
    {
        try {
            // קבלת מידע המותג הפעיל
            $brand_info = $this->brand_manager->get_active_brand();

            // בניית פרומפט מתקדם
            $advanced_prompt = $this->build_advanced_prompt($content_type, $topic, $brand_info, $additional_params);

            // יצירת התוכן באמצעות AI
            $raw_content = $this->ai_service->generate_content($advanced_prompt);

            // עיבוד התוכן והחלת תבניות
            $processed_content = $this->process_generated_content($raw_content, $content_type, $brand_info);

            // החלת עיצוב מתקדם
            $styled_content = $this->template_engine->generate_styled_content(
                $content_type,
                $processed_content,
                $brand_info
            );

            return [
                'success' => true,
                'content' => $styled_content,
                'raw_content' => $raw_content,
                'metadata' => $this->generate_content_metadata($processed_content, $brand_info)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * בניית פרומפט מתקדם
     */
    private function build_advanced_prompt($content_type, $topic, $brand_info, $additional_params)
    {
        $base_prompt = $this->get_base_prompt_template($content_type);
        $brand_context = $this->build_brand_context($brand_info);
        $seo_guidelines = $this->build_seo_guidelines($brand_info, $topic);
        $quality_guidelines = $this->get_quality_guidelines($content_type);
        $current_trends = $this->get_current_trends($topic);

        $advanced_prompt = sprintf(
            "%s\n\n%s\n\n%s\n\n%s\n\n%s\n\nנושא: %s\n\n%s",
            $base_prompt,
            $brand_context,
            $seo_guidelines,
            $quality_guidelines,
            $current_trends,
            $topic,
            $this->build_additional_instructions($additional_params)
        );

        return $advanced_prompt;
    }

    /**
     * תבניות פרומפט בסיסיות
     */
    private function get_base_prompt_template($content_type)
    {
        $templates = [
            'blog_post' => "
אתה כותב תוכן מקצועי ומומחה בתחום. צור פוסט בלוג מקיף ואיכותי שכולל:

📝 מבנה הפוסט:
1. כותרת מושכת עם מילות מפתח
2. מבוא עם hook חזק שמושך את הקורא
3. תוכן מפורט עם תת-כותרות ברורות
4. דוגמאות מעשיות ומקרי בוחן
5. רשימות מסודרות עם נקודות פעולה
6. סיכום עם קריאה לפעולה

💡 דרישות איכות:
- אורך: 800-1200 מילים
- טון מקצועי אך נגיש
- שימוש בסטטיסטיקות ונתונים
- כלול לפחות 3 תת-כותרות
- הוסף tips מעשיים
- צור תוכן ייחודי ומקורי
            ",

            'social_media' => "
צור פוסט לרשתות חברתיות ויראלי ומעניין:

📱 מבנה הפוסט:
1. שאלה מעוררת או hook מושך
2. תוכן קצר וחד
3. hashtags רלוונטיים (5-8)
4. קריאה לפעולה (לייק, שיתוף, תגובה)

🎯 דרישות:
- אורך: 100-200 מילים
- טון חברותי ומעניין
- שימוש באמוג'י אסטרטגי
- תוכן שמעודד אינטראקציה
- מסר ברור וחד
            ",

            'product_description' => "
כתב תיאור מוצר משכנע ומקצועי:

🛍️ מבנה התיאור:
1. כותרת מושכת עם יתרון עיקרי
2. בעיה שהמוצר פותר
3. רשימת יתרונות (לא תכונות!)
4. social proof או המלצות
5. יצירת דחיפות (מלאי מוגבל, מבצע)
6. קריאה לפעולה ברורה

💎 דרישות:
- אורך: 200-400 מילים
- מיקוד ביתרונות ללקוח
- שפה משכנעת
- פתרון לכאב של הלקוח
- יצירת רצון לקנייה
            ",

            'email_marketing' => "
צור אימייל שיווקי אפקטיבי:

📧 מבנה האימייל:
1. שורת נושא מושכת
2. פתיחה אישית
3. הצעת ערך ברורה
4. תוכן מעניין ורלוונטי
5. CTA בולט ויחיד
6. חתימה מקצועית

✨ דרישות:
- אורך: 150-300 מילים
- טון אישי ויידידותי
- מסר ממוקד
- CTA אחד וברור
- ערך ללקוח
            "
        ];

        return $templates[$content_type] ?? $templates['blog_post'];
    }

    /**
     * בניית הקשר המותג
     */
    private function build_brand_context($brand_info)
    {
        if (!$brand_info) {
            return "צור תוכן כללי ומקצועי.";
        }

        $context = "🏢 הקשר המותג:\n";
        $context .= "שם המותג: " . ($brand_info['name'] ?? 'לא צוין') . "\n";
        $context .= "תיאור: " . ($brand_info['description'] ?? 'לא צוין') . "\n";
        $context .= "תעשייה: " . ($brand_info['industry'] ?? 'לא צוין') . "\n";
        $context .= "קהל יעד: " . ($brand_info['target_audience'] ?? 'לא צוין') . "\n";
        $context .= "טון דיבור: " . ($brand_info['brand_voice'] ?? 'מקצועי') . "\n";
        $context .= "סגנון תוכן: " . ($brand_info['tone_of_voice'] ?? 'אינפורמטיבי') . "\n";

        if (isset($brand_info['keywords']) && is_array($brand_info['keywords'])) {
            $context .= "מילות מפתח: " . implode(', ', $brand_info['keywords']) . "\n";
        }

        if (isset($brand_info['values']) && is_array($brand_info['values'])) {
            $context .= "ערכי המותג: " . implode(', ', $brand_info['values']) . "\n";
        }

        if (!empty($brand_info['unique_selling_proposition'])) {
            $context .= "הצעת ערך ייחודית: " . $brand_info['unique_selling_proposition'] . "\n";
        }

        $context .= "\n🎯 הנחיות יצירה:\n";
        $context .= "- שמור על עקביות עם זהות המותג\n";
        $context .= "- השתמש במילות המפתח באופן טבעי\n";
        $context .= "- התאם את הטון לקהל היעד\n";
        $context .= "- שלב את ערכי המותג בתוכן\n";

        return $context;
    }

    /**
     * בניית הנחיות SEO
     */
    private function build_seo_guidelines($brand_info, $topic)
    {
        $guidelines = "🔍 הנחיות SEO:\n";
        $guidelines .= "- השתמש במילת המפתח הראשית בכותרת\n";
        $guidelines .= "- כלול מילות מפתח משניות בתת-כותרות\n";
        $guidelines .= "- צור meta description אטרקטיבי (150-160 תווים)\n";
        $guidelines .= "- השתמש במילות מפתח LSI (קשורות לנושא)\n";
        $guidelines .= "- כתב בצורה טבעית ונגישה\n";
        $guidelines .= "- הוסף קישורים פנימיים רלוונטיים\n";
        $guidelines .= "- צור תוכן באורך מתאים לנושא\n";

        // הוספת מילות מפתח ספציפיות למותג
        if ($brand_info && isset($brand_info['seo_settings']['focus_keywords'])) {
            $focus_keywords = $brand_info['seo_settings']['focus_keywords'];
            if (is_array($focus_keywords) && !empty($focus_keywords)) {
                $guidelines .= "\n🎯 מילות מפתח למיקוד: " . implode(', ', $focus_keywords) . "\n";
            }
        }

        return $guidelines;
    }

    /**
     * הנחיות איכות
     */
    private function get_quality_guidelines($content_type)
    {
        $base_guidelines = "
📊 הנחיות איכות:
- כתב בעברית תקנית וברורה
- השתמש במשפטים קצרים ובהירים
- הוסף דוגמאות מעשיות
- כלול סטטיסטיקות ונתונים (אם רלוונטי)
- צור תוכן ייחודי ומקורי
- הימנע מקלישאות ומשפטים גנריים
- הוסף ערך אמיתי לקורא
- בדוק עובדות ומידע
        ";

        $specific_guidelines = [
            'blog_post' => "
- פתח עם סטטיסטיקה מעניינת או שאלה
- חלק לפסקאות קצרות (2-3 משפטים)
- השתמש ברשימות ונקודות
- הוסף קריאות לפעולה לאורך הטקסט
- סיים עם סיכום וצעדים הבאים
            ",
            'social_media' => "
- התחל עם hook חזק
- השתמש באמוג'י בחכמה
- צור תחושת דחיפות או FOMO
- עודד אינטראקציה
- הוסף hashtags רלוונטיים
            ",
            'product_description' => "
- מקד ביתרונות, לא בתכונות
- פתור בעיה ספציפית
- השתמש בשפה רגשית
- הוסף social proof
- צור דחיפות לקנייה
            "
        ];

        return $base_guidelines . ($specific_guidelines[$content_type] ?? '');
    }

    /**
     * קבלת טרנדים נוכחיים
     */
    private function get_current_trends($topic)
    {
        $current_year = date('Y');
        $current_month = date('F');

        return "
📈 הקשר זמני ורלוונטיות:
- השנה היא {$current_year}, החודש הוא {$current_month}
- התייחס לטרנדים עדכניים בתחום
- כלול מידע רלוונטי לתקופה הנוכחית
- הזכר אירועים או שינויים עדכניים בתחום
- השתמש בנתונים עדכניים (2023-2024)
        ";
    }

    /**
     * בניית הוראות נוספות
     */
    private function build_additional_instructions($additional_params)
    {
        $instructions = "";

        if (isset($additional_params['target_length'])) {
            $instructions .= "אורך מטרה: " . $additional_params['target_length'] . " מילים\n";
        }

        if (isset($additional_params['specific_keywords'])) {
            $instructions .= "מילות מפתח ספציפיות: " . implode(', ', $additional_params['specific_keywords']) . "\n";
        }

        if (isset($additional_params['call_to_action'])) {
            $instructions .= "קריאה לפעולה: " . $additional_params['call_to_action'] . "\n";
        }

        if (isset($additional_params['tone_adjustment'])) {
            $instructions .= "התאמת טון: " . $additional_params['tone_adjustment'] . "\n";
        }

        return $instructions;
    }

    /**
     * עיבוד התוכן שנוצר
     */
    private function process_generated_content($raw_content, $content_type, $brand_info)
    {
        // חילוץ כותרת
        $title = $this->extract_title($raw_content);

        // ניקוי התוכן
        $cleaned_content = $this->clean_content($raw_content);

        // הוספת מטא-דאטה
        $metadata = $this->generate_content_metadata($cleaned_content, $brand_info);

        return [
            'title' => $title,
            'content' => $cleaned_content,
            'metadata' => $metadata,
            'word_count' => str_word_count(strip_tags($cleaned_content)),
            'reading_time' => $this->calculate_reading_time($cleaned_content)
        ];
    }

    /**
     * חילוץ כותרת מהתוכן
     */
    private function extract_title($content)
    {
        // חיפוש כותרת בפורמטים שונים
        $patterns = [
            '/^#\s*(.+)$/m',           // Markdown H1
            '/^(.+)\n=+$/m',           // Underlined title
            '/^כותרת:\s*(.+)$/m',      // Hebrew "Title:"
            '/^(.+)$/m'                // First line
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        // אם לא נמצאה כותרת, צור אחת
        return 'תוכן חדש - ' . date('d/m/Y');
    }

    /**
     * ניקוי התוכן
     */
    private function clean_content($content)
    {
        // הסרת כותרת מהתוכן
        $content = preg_replace('/^#\s*.+\n/', '', $content);
        $content = preg_replace('/^.+\n=+\n/', '', $content);
        $content = preg_replace('/^כותרת:\s*.+\n/', '', $content);

        // ניקוי רווחים מיותרים
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        return $content;
    }

    /**
     * חישוב זמן קריאה
     */
    private function calculate_reading_time($content)
    {
        $word_count = str_word_count(strip_tags($content));
        $reading_speed = 200; // מילים לדקה בעברית

        return max(1, ceil($word_count / $reading_speed));
    }

    /**
     * יצירת מטא-דאטה לתוכן
     */
    private function generate_content_metadata($content_data, $brand_info)
    {
        $metadata = [
            'generated_at' => current_time('mysql'),
            'word_count' => $content_data['word_count'] ?? 0,
            'reading_time' => $content_data['reading_time'] ?? 1,
            'content_type' => 'generated',
            'brand_id' => $brand_info['id'] ?? null,
            'brand_name' => $brand_info['name'] ?? null
        ];

        // הוספת מילות מפתח שזוהו
        $detected_keywords = $this->extract_keywords($content_data['content'] ?? '');
        if (!empty($detected_keywords)) {
            $metadata['detected_keywords'] = $detected_keywords;
        }

        // הוספת ציון SEO בסיסי
        $metadata['seo_score'] = $this->calculate_basic_seo_score($content_data, $brand_info);

        return $metadata;
    }

    /**
     * חילוץ מילות מפתח מהתוכן
     */
    private function extract_keywords($content)
    {
        // הסרת HTML ופיסוק
        $clean_content = strip_tags($content);
        $clean_content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean_content);

        // פיצול למילים
        $words = preg_split('/\s+/', $clean_content);
        $words = array_filter($words, function ($word) {
            return mb_strlen($word) > 3; // מילים באורך 4+ תווים
        });

        // ספירת תדירות
        $word_count = array_count_values($words);
        arsort($word_count);

        // החזרת 10 המילים הנפוצות ביותר
        return array_slice(array_keys($word_count), 0, 10);
    }

    /**
     * חישוב ציון SEO בסיסי
     */
    private function calculate_basic_seo_score($content_data, $brand_info)
    {
        $score = 0; // Max score: 100
        $content = $content_data['content'] ?? '';
        $title = $content_data['title'] ?? '';
        $word_count = $content_data['word_count'] ?? 0;
        $brand_keywords = ($brand_info && isset($brand_info['keywords']) && is_array($brand_info['keywords'])) ? $brand_info['keywords'] : [];
        $main_keyword = $brand_keywords[0] ?? '';

        // 1. Content Length (15 points)
        if ($word_count >= 800) $score += 15;
        elseif ($word_count >= 500) $score += 10;
        elseif ($word_count >= 300) $score += 5;

        // 2. Title SEO (20 points)
        $title_len = mb_strlen($title);
        if ($title_len >= 30 && $title_len <= 60) $score += 10; // Optimal length
        if (!empty($main_keyword) && stripos($title, $main_keyword) !== false) $score += 10; // Main keyword in title

        // 3. Keyword Density (20 points)
        if (!empty($main_keyword)) {
            $keyword_count = substr_count(strtolower($content), strtolower($main_keyword));
            $density = ($word_count > 0) ? ($keyword_count / $word_count) * 100 : 0;
            if ($density >= 0.5 && $density <= 2.0) $score += 20; // Optimal density
            elseif ($density > 0.2) $score += 10;
        }

        // 4. Secondary Keywords (15 points)
        if (count($brand_keywords) > 1) {
            $secondary_keywords = array_slice($brand_keywords, 1, 4); // Check up to 4 secondary keywords
            $found_count = 0;
            foreach ($secondary_keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $found_count++;
                }
            }
            $score += min(15, $found_count * 5); // 5 points per found keyword, max 15
        }

        // 5. Readability & Structure (30 points)
        // Subheadings (H2, H3)
        if (preg_match_all('/^##|###/m', $content, $matches)) {
            $subheading_count = count($matches[0]);
            if ($subheading_count >= 5) $score += 10;
            elseif ($subheading_count >= 2) $score += 5;
        }

        // Lists (bullet points)
        if (preg_match('/^\s*[-*•]/m', $content)) {
            $score += 5;
        }

        // Paragraph length
        $paragraphs = explode("\n\n", $content);
        $long_paragraphs = 0;
        foreach ($paragraphs as $p) {
            if (str_word_count($p) > 150) {
                $long_paragraphs++;
            }
        }
        if ($long_paragraphs < 3) $score += 5; // Penalize for too many long paragraphs

        // Sentence length (basic check)
        $sentences = preg_split('/[.!?]+/', $content);
        $avg_sentence_length = $word_count / (count($sentences) ?: 1);
        if ($avg_sentence_length > 15 && $avg_sentence_length < 25) {
            $score += 5; // Good average sentence length
        }

        // Meta Description (Implicitly checked by asking AI to generate it)
        // For now, we assume it's generated. A future step would be to parse it.
        // Let's add a placeholder score for it.
        $score += 5;

        return min(100, $score);
    }

    /**
     * שמירת התוכן כפוסט טיוטה
     */
    private function save_as_draft_post($content_result, $brand_info)
    {
        $post_data = [
            'post_title' => $content_result['title'] ?? 'תוכן חדש',
            'post_content' => $content_result['content'],
            'post_status' => 'draft',
            'post_type' => 'post',
            'meta_input' => [
                '_ai_generated' => true,
                '_ai_brand_id' => $brand_info['id'] ?? null,
                '_ai_seo_score' => $content_result['metadata']['seo_score'] ?? 0,
                '_ai_word_count' => $content_result['word_count'] ?? 0,
                '_ai_raw_metadata' => json_encode($content_result['metadata']) // Keep all data in one field as well
            ]
        ];

        return wp_insert_post($post_data);
    }

    /**
     * יצירת תוכן אוטומטי לפי לוח זמנים
     */
    public function generate_scheduled_content($content_type, $brand_id = null)
    {
        try {
            // קבלת מידע המותג
            $brand_info = $brand_id ?
                $this->brand_manager->get_brand($brand_id) :
                $this->brand_manager->get_active_brand();

            if (!$brand_info) {
                throw new Exception('לא נמצא מותג פעיל');
            }

            // בחירת נושא אוטומטית
            $topic = $this->select_automatic_topic($brand_info, $content_type);

            // יצירת התוכן
            $result = $this->generate_advanced_content($content_type, $topic);

            if ($result['success']) {
                // שמירת התוכן כפוסט טיוטה
                $post_id = $this->save_as_draft_post($result['processed_content'], $brand_info);
                $result['post_id'] = $post_id;
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * בחירת נושא אוטומטית
     */
    private function select_automatic_topic($brand_info, $content_type)
    {
        $keywords = $brand_info['keywords'] ?? [];

        if (empty($keywords)) {
            // נושאים כלליים לפי סוג התוכן
            $default_topics = [
                'blog_post' => ['טיפים מקצועיים', 'מגמות בתחום', 'מדריך מעשי'],
                'social_media' => ['שאלה לקהילה', 'טיפ יומי', 'השראה'],
                'product_description' => ['מוצר חדש', 'יתרונות המוצר', 'המלצות לקוחות'],
                'email_marketing' => ['עדכון חשוב', 'הצעה מיוחדת', 'תוכן בלעדי']
            ];

            $topics = $default_topics[$content_type] ?? $default_topics['blog_post'];
            return $topics[array_rand($topics)];
        }

        // בחירת מילת מפתח רנדומלית
        $selected_keyword = $keywords[array_rand($keywords)];

        // יצירת נושא מבוסס על מילת המפתח
        $topic_templates = [
            'כל מה שצריך לדעת על %s',
            'המדריך המלא ל%s',
            '5 טיפים חשובים על %s',
            'איך %s משפיע על העסק שלך',
            'המגמות החדשות ב%s'
        ];

        $template = $topic_templates[array_rand($topic_templates)];
        return sprintf($template, $selected_keyword);
    }

    /**
     * שמירת התוכן כפוסט טיוטה
     */
    private function save_as_draft_post_old($content_result, $brand_info)
    {
        $post_data = [
            'post_title' => $content_result['metadata']['title'] ?? 'תוכן חדש',
            'post_content' => $content_result['content'],
            'post_status' => 'draft',
            'post_type' => 'post',
            'meta_input' => [
                'ai_generated' => true,
                'ai_brand_id' => $brand_info['id'] ?? null,
                'ai_metadata' => json_encode($content_result['metadata'])
            ]
        ];

        return wp_insert_post($post_data);
    }
}
            $score += 20;
        elseif ($word_count >= 150)
            $score += 10;

        // בדיקת כותרת (20 נקודות)
        if (mb_strlen($title) >= 30 && mb_strlen($title) <= 60)
            $score += 20;
        elseif (mb_strlen($title) >= 20)
            $score += 10;

        // בדיקת מילות מפתח במותג (30 נקודות)
        if ($brand_info && isset($brand_info['keywords'])) {
            $brand_keywords = is_array($brand_info['keywords']) ? $brand_info['keywords'] : [];
            $found_keywords = 0;

            foreach ($brand_keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $found_keywords++;
                }
            }

            if ($found_keywords >= 3)
                $score += 30;
            elseif ($found_keywords >= 1)
                $score += 15;
        }

        // בדיקת מבנה (30 נקודות)
        $has_subheadings = preg_match('/^##|^###/m', $content);
        $has_lists = preg_match('/^\s*[-*•]/m', $content);
        $has_paragraphs = substr_count($content, "\n\n") >= 2;

        if ($has_subheadings)
            $score += 10;
        if ($has_lists)
            $score += 10;
        if ($has_paragraphs)
            $score += 10;

        return min(100, $score);
    }

    /**
     * יצירת תוכן אוטומטי לפי לוח זמנים
     */
    public function generate_scheduled_content($content_type, $brand_id = null)
    {
        try {
            // קבלת מידע המותג
            $brand_info = $brand_id ?
                $this->brand_manager->get_brand($brand_id) :
                $this->brand_manager->get_active_brand();

            if (!$brand_info) {
                throw new Exception('לא נמצא מותג פעיל');
            }

            // בחירת נושא אוטומטית
            $topic = $this->select_automatic_topic($brand_info, $content_type);

            // יצירת התוכן
            $result = $this->generate_advanced_content($content_type, $topic);

            if ($result['success']) {
                // שמירת התוכן כפוסט טיוטה
                $post_id = $this->save_as_draft_post($result, $brand_info);
                $result['post_id'] = $post_id;
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * בחירת נושא אוטומטית
     */
    private function select_automatic_topic($brand_info, $content_type)
    {
        $keywords = $brand_info['keywords'] ?? [];

        if (empty($keywords)) {
            // נושאים כלליים לפי סוג התוכן
            $default_topics = [
                'blog_post' => ['טיפים מקצועיים', 'מגמות בתחום', 'מדריך מעשי'],
                'social_media' => ['שאלה לקהילה', 'טיפ יומי', 'השראה'],
                'product_description' => ['מוצר חדש', 'יתרונות המוצר', 'המלצות לקוחות'],
                'email_marketing' => ['עדכון חשוב', 'הצעה מיוחדת', 'תוכן בלעדי']
            ];

            $topics = $default_topics[$content_type] ?? $default_topics['blog_post'];
            return $topics[array_rand($topics)];
        }

        // בחירת מילת מפתח רנדומלית
        $selected_keyword = $keywords[array_rand($keywords)];

        // יצירת נושא מבוסס על מילת המפתח
        $topic_templates = [
            'כל מה שצריך לדעת על %s',
            'המדריך המלא ל%s',
            '5 טיפים חשובים על %s',
            'איך %s משפיע על העסק שלך',
            'המגמות החדשות ב%s'
        ];

        $template = $topic_templates[array_rand($topic_templates)];
        return sprintf($template, $selected_keyword);
    }

    /**
     * שמירת התוכן כפוסט טיוטה
     */
    private function save_as_draft_post($content_result, $brand_info)
    {
        $post_data = [
            'post_title' => $content_result['metadata']['title'] ?? 'תוכן חדש',
            'post_content' => $content_result['content'],
            'post_status' => 'draft',
            'post_type' => 'post',
            'meta_input' => [
                'ai_generated' => true,
                'ai_brand_id' => $brand_info['id'] ?? null,
                'ai_metadata' => json_encode($content_result['metadata'])
            ]
        ];

        return wp_insert_post($post_data);
    }
}