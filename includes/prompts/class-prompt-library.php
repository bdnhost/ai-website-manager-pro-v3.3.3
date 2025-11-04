<?php
/**
 * Prompt Library Class
 * מחלקה לניהול ספריית הפרומפטים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Prompt_Library
{

    private $prompts_data = [];
    private $user_prompts = [];
    private $prompts_file;

    public function __construct()
    {
        $this->prompts_file = AI_WEBSITE_MANAGER_PATH . 'includes/prompts/data/default-prompts.json';
        $this->load_prompts();
        $this->load_user_prompts();
    }

    /**
     * טעינת פרומפטים מהקובץ
     */
    public function load_prompts()
    {
        if (file_exists($this->prompts_file)) {
            $json_content = file_get_contents($this->prompts_file);
            $this->prompts_data = json_decode($json_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('AI Manager: Failed to parse prompts JSON - ' . json_last_error_msg());
                $this->prompts_data = $this->get_fallback_prompts();
            }
        } else {
            $this->prompts_data = $this->get_fallback_prompts();
        }
    }

    /**
     * טעינת פרומפטים של המשתמש
     */
    public function load_user_prompts()
    {
        $user_id = get_current_user_id();
        $this->user_prompts = get_user_meta($user_id, 'ai_manager_user_prompts', true) ?: [];
    }

    /**
     * קבלת כל הקטגוריות
     */
    public function get_categories()
    {
        $categories = [];

        if (isset($this->prompts_data['categories'])) {
            foreach ($this->prompts_data['categories'] as $key => $category) {
                $categories[$key] = [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'prompts_count' => count($category['prompts'])
                ];
            }
        }

        // הוספת קטגוריית הפרומפטים האישיים
        if (!empty($this->user_prompts)) {
            $categories['user_prompts'] = [
                'name' => 'הפרומפטים שלי',
                'icon' => '👤',
                'description' => 'פרומפטים אישיים שיצרת',
                'prompts_count' => count($this->user_prompts)
            ];
        }

        return $categories;
    }

    /**
     * קבלת פרומפטים לפי קטגוריה
     */
    public function get_prompts_by_category($category)
    {
        if ($category === 'user_prompts') {
            return $this->user_prompts;
        }

        if (isset($this->prompts_data['categories'][$category]['prompts'])) {
            return $this->prompts_data['categories'][$category]['prompts'];
        }

        return [];
    }

    /**
     * חיפוש פרומפטים
     */
    public function search_prompts($query, $category = null)
    {
        $results = [];
        $query = strtolower(trim($query));

        if (empty($query)) {
            return $results;
        }

        // חיפוש בפרומפטים ברירת מחדל
        if (isset($this->prompts_data['categories'])) {
            foreach ($this->prompts_data['categories'] as $cat_key => $category_data) {
                // אם צוינה קטגוריה ספציפית, חפש רק בה
                if ($category && $category !== $cat_key) {
                    continue;
                }

                foreach ($category_data['prompts'] as $prompt) {
                    if ($this->prompt_matches_query($prompt, $query)) {
                        $prompt['category'] = $cat_key;
                        $prompt['category_name'] = $category_data['name'];
                        $prompt['is_user_prompt'] = false;
                        $results[] = $prompt;
                    }
                }
            }
        }

        // חיפוש בפרומפטים של המשתמש
        if (!$category || $category === 'user_prompts') {
            foreach ($this->user_prompts as $prompt) {
                if ($this->prompt_matches_query($prompt, $query)) {
                    $prompt['category'] = 'user_prompts';
                    $prompt['category_name'] = 'הפרומפטים שלי';
                    $prompt['is_user_prompt'] = true;
                    $results[] = $prompt;
                }
            }
        }

        // מיון לפי רלוונטיות
        usort($results, function ($a, $b) use ($query) {
            $score_a = $this->calculate_relevance_score($a, $query);
            $score_b = $this->calculate_relevance_score($b, $query);
            return $score_b - $score_a;
        });

        return $results;
    }

    /**
     * בדיקה אם פרומפט תואם לחיפוש
     */
    private function prompt_matches_query($prompt, $query)
    {
        $searchable_text = strtolower(
            ($prompt['title'] ?? '') . ' ' .
            ($prompt['description'] ?? '') . ' ' .
            ($prompt['prompt'] ?? '') . ' ' .
            implode(' ', $prompt['tags'] ?? [])
        );

        return strpos($searchable_text, $query) !== false;
    }

    /**
     * חישוב ציון רלוונטיות
     */
    private function calculate_relevance_score($prompt, $query)
    {
        $score = 0;

        // ציון גבוה יותר לכותרת
        if (stripos($prompt['title'] ?? '', $query) !== false) {
            $score += 10;
        }

        // ציון בינוני לתיאור
        if (stripos($prompt['description'] ?? '', $query) !== false) {
            $score += 5;
        }

        // ציון נמוך לתגיות
        foreach ($prompt['tags'] ?? [] as $tag) {
            if (stripos($tag, $query) !== false) {
                $score += 2;
            }
        }

        // ציון לפי שימוש
        $score += ($prompt['usage_count'] ?? 0) * 0.1;

        return $score;
    }

    /**
     * הוספת פרומפט אישי
     */
    public function add_custom_prompt($prompt_data)
    {
        try {
            // ולידציה
            $validation_result = $this->validate_prompt_data($prompt_data);
            if ($validation_result !== true) {
                throw new Exception($validation_result);
            }

            $user_id = get_current_user_id();

            // הכנת נתוני הפרומפט
            $new_prompt = [
                'id' => 'user_' . uniqid(),
                'title' => sanitize_text_field($prompt_data['title']),
                'description' => sanitize_textarea_field($prompt_data['description']),
                'prompt' => wp_kses_post($prompt_data['prompt']),
                'variables' => array_map('sanitize_text_field', $prompt_data['variables'] ?? []),
                'content_types' => array_map('sanitize_text_field', $prompt_data['content_types'] ?? []),
                'tags' => array_map('sanitize_text_field', $prompt_data['tags'] ?? []),
                'usage_count' => 0,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];

            // הוספה לרשימה
            $this->user_prompts[] = $new_prompt;

            // שמירה
            update_user_meta($user_id, 'ai_manager_user_prompts', $this->user_prompts);

            return [
                'success' => true,
                'prompt_id' => $new_prompt['id'],
                'message' => 'Prompt added successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * עדכון פרומפט אישי
     */
    public function update_prompt($prompt_id, $data)
    {
        try {
            $user_id = get_current_user_id();
            $prompt_index = $this->find_user_prompt_index($prompt_id);

            if ($prompt_index === false) {
                throw new Exception('Prompt not found');
            }

            // ולידציה
            $validation_result = $this->validate_prompt_data($data);
            if ($validation_result !== true) {
                throw new Exception($validation_result);
            }

            // עדכון הנתונים
            $this->user_prompts[$prompt_index]['title'] = sanitize_text_field($data['title']);
            $this->user_prompts[$prompt_index]['description'] = sanitize_textarea_field($data['description']);
            $this->user_prompts[$prompt_index]['prompt'] = wp_kses_post($data['prompt']);
            $this->user_prompts[$prompt_index]['variables'] = array_map('sanitize_text_field', $data['variables'] ?? []);
            $this->user_prompts[$prompt_index]['content_types'] = array_map('sanitize_text_field', $data['content_types'] ?? []);
            $this->user_prompts[$prompt_index]['tags'] = array_map('sanitize_text_field', $data['tags'] ?? []);
            $this->user_prompts[$prompt_index]['updated_at'] = current_time('mysql');

            // שמירה
            update_user_meta($user_id, 'ai_manager_user_prompts', $this->user_prompts);

            return [
                'success' => true,
                'message' => 'Prompt updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * מחיקת פרומפט אישי
     */
    public function delete_prompt($prompt_id)
    {
        try {
            $user_id = get_current_user_id();
            $prompt_index = $this->find_user_prompt_index($prompt_id);

            if ($prompt_index === false) {
                throw new Exception('Prompt not found');
            }

            // הסרה מהרשימה
            array_splice($this->user_prompts, $prompt_index, 1);

            // שמירה
            update_user_meta($user_id, 'ai_manager_user_prompts', $this->user_prompts);

            return [
                'success' => true,
                'message' => 'Prompt deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * קבלת פרומפט לפי ID
     */
    public function get_prompt_by_id($prompt_id)
    {
        // חיפוש בפרומפטים של המשתמש
        foreach ($this->user_prompts as $prompt) {
            if ($prompt['id'] === $prompt_id) {
                $prompt['is_user_prompt'] = true;
                return $prompt;
            }
        }

        // חיפוש בפרומפטים ברירת מחדל
        if (isset($this->prompts_data['categories'])) {
            foreach ($this->prompts_data['categories'] as $category_data) {
                foreach ($category_data['prompts'] as $prompt) {
                    if ($prompt['id'] === $prompt_id) {
                        $prompt['is_user_prompt'] = false;
                        return $prompt;
                    }
                }
            }
        }

        return null;
    }

    /**
     * עדכון מונה שימוש
     */
    public function increment_usage_count($prompt_id)
    {
        // עדכון בפרומפטים של המשתמש
        $prompt_index = $this->find_user_prompt_index($prompt_id);
        if ($prompt_index !== false) {
            $this->user_prompts[$prompt_index]['usage_count']++;
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'ai_manager_user_prompts', $this->user_prompts);
            return true;
        }

        // עדכון בפרומפטים ברירת מחדל (שמירה בנפרד)
        $usage_stats = get_option('ai_manager_prompt_usage_stats', []);
        if (!isset($usage_stats[$prompt_id])) {
            $usage_stats[$prompt_id] = 0;
        }
        $usage_stats[$prompt_id]++;
        update_option('ai_manager_prompt_usage_stats', $usage_stats);

        return true;
    }

    /**
     * קבלת סטטיסטיקות שימוש
     */
    public function get_usage_stats()
    {
        $stats = [
            'total_prompts' => 0,
            'user_prompts' => count($this->user_prompts),
            'default_prompts' => 0,
            'most_used' => [],
            'recent_prompts' => []
        ];

        // ספירת פרומפטים ברירת מחדל
        if (isset($this->prompts_data['categories'])) {
            foreach ($this->prompts_data['categories'] as $category_data) {
                $stats['default_prompts'] += count($category_data['prompts']);
            }
        }

        $stats['total_prompts'] = $stats['user_prompts'] + $stats['default_prompts'];

        // פרומפטים הכי נפוצים
        $usage_stats = get_option('ai_manager_prompt_usage_stats', []);
        arsort($usage_stats);
        $stats['most_used'] = array_slice($usage_stats, 0, 5, true);

        // פרומפטים אחרונים של המשתמש
        $recent_user_prompts = array_slice($this->user_prompts, -5);
        $stats['recent_prompts'] = array_reverse($recent_user_prompts);

        return $stats;
    }

    /**
     * ולידציה של נתוני פרומפט
     */
    private function validate_prompt_data($data)
    {
        if (empty($data['title'])) {
            return 'Title is required';
        }

        if (empty($data['prompt'])) {
            return 'Prompt content is required';
        }

        if (strlen($data['title']) > 200) {
            return 'Title is too long (max 200 characters)';
        }

        if (strlen($data['prompt']) > 5000) {
            return 'Prompt is too long (max 5000 characters)';
        }

        return true;
    }

    /**
     * מציאת אינדקס פרומפט משתמש
     */
    private function find_user_prompt_index($prompt_id)
    {
        foreach ($this->user_prompts as $index => $prompt) {
            if ($prompt['id'] === $prompt_id) {
                return $index;
            }
        }
        return false;
    }

    /**
     * פרומפטים ברירת מחדל במקרה של כשל
     */
    private function get_fallback_prompts()
    {
        return [
            'categories' => [
                'general' => [
                    'name' => 'כללי',
                    'icon' => '📝',
                    'description' => 'פרומפטים כלליים לשימוש יומיומי',
                    'prompts' => [
                        [
                            'id' => 'general_001',
                            'title' => 'יצירת תוכן כללי',
                            'description' => 'פרומפט בסיסי ליצירת תוכן',
                            'prompt' => 'כתוב תוכן מעניין ואיכותי על [נושא] עבור [קהל יעד]. השתמש בטון [טון] והוסף דוגמאות רלוונטיות.',
                            'variables' => ['נושא', 'קהל יעד', 'טון'],
                            'content_types' => ['blog_post', 'social_media'],
                            'tags' => ['כללי', 'תוכן'],
                            'usage_count' => 0
                        ]
                    ]
                ]
            ]
        ];
    }
}