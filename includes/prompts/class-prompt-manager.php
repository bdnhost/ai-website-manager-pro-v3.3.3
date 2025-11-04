<?php
/**
 * Prompt Manager Class
 * מחלקה לניהול ממשק הפרומפטים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Prompt_Manager
{

    private $prompt_library;

    public function __construct()
    {
        require_once AI_WEBSITE_MANAGER_PATH . 'includes/prompts/class-prompt-library.php';
        $this->prompt_library = new AI_Website_Manager_Prompt_Library();
        $this->init_hooks();
    }

    /**
     * אתחול hooks
     */
    private function init_hooks()
    {
        // AJAX endpoints
        add_action('wp_ajax_ai_get_prompt_categories', [$this, 'handle_get_categories']);
        add_action('wp_ajax_ai_get_prompts_by_category', [$this, 'handle_get_prompts_by_category']);
        add_action('wp_ajax_ai_search_prompts', [$this, 'handle_search_prompts']);
        add_action('wp_ajax_ai_get_prompt_by_id', [$this, 'handle_get_prompt_by_id']);
        add_action('wp_ajax_ai_save_user_prompt', [$this, 'handle_save_user_prompt']);
        add_action('wp_ajax_ai_update_user_prompt', [$this, 'handle_update_user_prompt']);
        add_action('wp_ajax_ai_delete_user_prompt', [$this, 'handle_delete_user_prompt']);
        add_action('wp_ajax_ai_use_prompt', [$this, 'handle_use_prompt']);
        add_action('wp_ajax_ai_get_prompt_stats', [$this, 'handle_get_prompt_stats']);
    }

    /**
     * רינדור מודל ספריית הפרומפטים
     */
    public function render_prompt_library_modal()
    {
        $categories = $this->prompt_library->get_categories();
        $stats = $this->prompt_library->get_usage_stats();

        ob_start();
        ?>
        <div id="prompt-library-modal" class="ai-modal" style="display: none;">
            <div class="modal-overlay"></div>
            <div class="modal-content extra-large-modal">
                <div class="modal-header">
                    <h2>📚 ספריית הפרומפטים</h2>
                    <div class="header-stats">
                        <span class="stat-item">
                            <strong><?php echo $stats['total_prompts']; ?></strong> פרומפטים
                        </span>
                        <span class="stat-item">
                            <strong><?php echo $stats['user_prompts']; ?></strong> אישיים
                        </span>
                    </div>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="prompt-library-layout">
                        <!-- סרגל חיפוש -->
                        <div class="search-section">
                            <div class="search-bar">
                                <input type="text" id="prompt-search" placeholder="🔍 חפש פרומפטים..." />
                                <button id="clear-search" class="clear-btn" style="display: none;">&times;</button>
                            </div>
                            <div class="search-filters">
                                <select id="category-filter">
                                    <option value="">כל הקטגוריות</option>
                                    <?php foreach ($categories as $key => $category): ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($category['name']); ?> (<?php echo $category['prompts_count']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button id="add-new-prompt" class="button button-primary">
                                    ➕ פרומפט חדש
                                </button>
                            </div>
                        </div>

                        <div class="library-content">
                            <!-- סרגל צד עם קטגוריות -->
                            <div class="categories-sidebar">
                                <h3>📂 קטגוריות</h3>
                                <div class="categories-list">
                                    <div class="category-item active" data-category="">
                                        <span class="category-icon">🌟</span>
                                        <span class="category-name">הכל</span>
                                        <span class="category-count"><?php echo $stats['total_prompts']; ?></span>
                                    </div>
                                    <?php foreach ($categories as $key => $category): ?>
                                        <div class="category-item" data-category="<?php echo esc_attr($key); ?>">
                                            <span class="category-icon"><?php echo $category['icon']; ?></span>
                                            <span class="category-name"><?php echo esc_html($category['name']); ?></span>
                                            <span class="category-count"><?php echo $category['prompts_count']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- תוכן ראשי -->
                            <div class="prompts-main">
                                <div class="prompts-header">
                                    <h3 id="current-category-title">כל הפרומפטים</h3>
                                    <div class="view-controls">
                                        <button class="view-btn active" data-view="grid">⊞</button>
                                        <button class="view-btn" data-view="list">☰</button>
                                    </div>
                                </div>

                                <div id="prompts-container" class="prompts-grid">
                                    <div class="loading-prompts">טוען פרומפטים...</div>
                                </div>

                                <div id="no-prompts" class="no-results" style="display: none;">
                                    <div class="no-results-icon">🔍</div>
                                    <h4>לא נמצאו פרומפטים</h4>
                                    <p>נסה לשנות את מילות החיפוש או הקטגוריה</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- מודל יצירת/עריכת פרומפט -->
        <div id="prompt-editor-modal" class="ai-modal" style="display: none;">
            <div class="modal-overlay"></div>
            <div class="modal-content large-modal">
                <div class="modal-header">
                    <h2 id="editor-title">✏️ פרומפט חדש</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="prompt-editor-form">
                        <input type="hidden" id="prompt-id" value="">

                        <div class="form-section">
                            <div class="form-group">
                                <label for="prompt-title">כותרת הפרומפט *</label>
                                <input type="text" id="prompt-title" name="title" required maxlength="200">
                            </div>

                            <div class="form-group">
                                <label for="prompt-description">תיאור קצר</label>
                                <textarea id="prompt-description" name="description" rows="2" maxlength="500"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-group">
                                <label for="prompt-content">תוכן הפרומפט *</label>
                                <textarea id="prompt-content" name="prompt" rows="6" required maxlength="5000"
                                    placeholder="כתוב כאן את הפרומפט שלך. השתמש ב-[משתנה] לחלקים שישתנו..."></textarea>
                                <small class="form-help">השתמש ב-[שם המשתנה] כדי לסמן חלקים שישתנו בכל שימוש</small>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="prompt-variables">משתנים</label>
                                    <div class="variables-input">
                                        <input type="text" id="variable-input" placeholder="הכנס שם משתנה...">
                                        <button type="button" id="add-variable-btn">הוסף</button>
                                    </div>
                                    <div id="variables-list" class="tags-list"></div>
                                </div>

                                <div class="form-group">
                                    <label for="prompt-content-types">סוגי תוכן</label>
                                    <select id="prompt-content-types" multiple>
                                        <option value="blog_post">פוסט בלוג</option>
                                        <option value="social_media">רשתות חברתיות</option>
                                        <option value="email_marketing">אימייל שיווקי</option>
                                        <option value="product_description">תיאור מוצר</option>
                                        <option value="video_script">תסריט וידאו</option>
                                        <option value="press_release">הודעה לעיתונות</option>
                                        <option value="landing_page">דף נחיתה</option>
                                        <option value="ad_copy">קופי פרסומת</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-group">
                                <label for="prompt-tags">תגיות</label>
                                <div class="tags-input">
                                    <input type="text" id="tag-input" placeholder="הכנס תגית...">
                                    <button type="button" id="add-tag-btn">הוסף</button>
                                </div>
                                <div id="tags-list" class="tags-list"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button-secondary" onclick="promptManager.closeEditor()">
                        ביטול
                    </button>
                    <button type="button" id="save-prompt-btn" class="button button-primary">
                        💾 שמור פרומפט
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * טיפול בקבלת קטגוריות
     */
    public function handle_get_categories()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        try {
            $categories = $this->prompt_library->get_categories();
            wp_send_json_success($categories);
        } catch (Exception $e) {
            wp_send_json_error('Failed to get categories: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בקבלת פרומפטים לפי קטגוריה
     */
    public function handle_get_prompts_by_category()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $category = sanitize_text_field($_POST['category'] ?? '');

        try {
            if (empty($category)) {
                // החזרת כל הפרומפטים
                $all_prompts = [];
                $categories = $this->prompt_library->get_categories();

                foreach (array_keys($categories) as $cat_key) {
                    $prompts = $this->prompt_library->get_prompts_by_category($cat_key);
                    foreach ($prompts as $prompt) {
                        $prompt['category'] = $cat_key;
                        $prompt['category_name'] = $categories[$cat_key]['name'];
                        $all_prompts[] = $prompt;
                    }
                }

                wp_send_json_success($all_prompts);
            } else {
                $prompts = $this->prompt_library->get_prompts_by_category($category);
                wp_send_json_success($prompts);
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to get prompts: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בחיפוש פרומפטים
     */
    public function handle_search_prompts()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $query = sanitize_text_field($_POST['query'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');

        try {
            $results = $this->prompt_library->search_prompts($query, $category ?: null);
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error('Search failed: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בקבלת פרומפט לפי ID
     */
    public function handle_get_prompt_by_id()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $prompt_id = sanitize_text_field($_POST['prompt_id'] ?? '');

        try {
            $prompt = $this->prompt_library->get_prompt_by_id($prompt_id);

            if ($prompt) {
                wp_send_json_success($prompt);
            } else {
                wp_send_json_error('Prompt not found');
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to get prompt: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בשמירת פרומפט משתמש
     */
    public function handle_save_user_prompt()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        try {
            $prompt_data = [
                'title' => sanitize_text_field($_POST['title'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'prompt' => wp_kses_post($_POST['prompt'] ?? ''),
                'variables' => array_filter(array_map('sanitize_text_field', $_POST['variables'] ?? [])),
                'content_types' => array_filter(array_map('sanitize_text_field', $_POST['content_types'] ?? [])),
                'tags' => array_filter(array_map('sanitize_text_field', $_POST['tags'] ?? []))
            ];

            $result = $this->prompt_library->add_custom_prompt($prompt_data);

            if ($result['success']) {
                wp_send_json_success([
                    'prompt_id' => $result['prompt_id'],
                    'message' => 'Prompt saved successfully'
                ]);
            } else {
                wp_send_json_error($result['error']);
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to save prompt: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בעדכון פרומפט משתמש
     */
    public function handle_update_user_prompt()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $prompt_id = sanitize_text_field($_POST['prompt_id'] ?? '');

        try {
            $prompt_data = [
                'title' => sanitize_text_field($_POST['title'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'prompt' => wp_kses_post($_POST['prompt'] ?? ''),
                'variables' => array_filter(array_map('sanitize_text_field', $_POST['variables'] ?? [])),
                'content_types' => array_filter(array_map('sanitize_text_field', $_POST['content_types'] ?? [])),
                'tags' => array_filter(array_map('sanitize_text_field', $_POST['tags'] ?? []))
            ];

            $result = $this->prompt_library->update_prompt($prompt_id, $prompt_data);

            if ($result['success']) {
                wp_send_json_success(['message' => 'Prompt updated successfully']);
            } else {
                wp_send_json_error($result['error']);
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to update prompt: ' . $e->getMessage());
        }
    }

    /**
     * טיפול במחיקת פרומפט משתמש
     */
    public function handle_delete_user_prompt()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $prompt_id = sanitize_text_field($_POST['prompt_id'] ?? '');

        try {
            $result = $this->prompt_library->delete_prompt($prompt_id);

            if ($result['success']) {
                wp_send_json_success(['message' => 'Prompt deleted successfully']);
            } else {
                wp_send_json_error($result['error']);
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to delete prompt: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בשימוש בפרומפט
     */
    public function handle_use_prompt()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        $prompt_id = sanitize_text_field($_POST['prompt_id'] ?? '');

        try {
            $this->prompt_library->increment_usage_count($prompt_id);
            wp_send_json_success(['message' => 'Usage recorded']);
        } catch (Exception $e) {
            wp_send_json_error('Failed to record usage: ' . $e->getMessage());
        }
    }

    /**
     * טיפול בקבלת סטטיסטיקות
     */
    public function handle_get_prompt_stats()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_website_manager_nonce')) {
            wp_die('Security check failed');
        }

        try {
            $stats = $this->prompt_library->get_usage_stats();
            wp_send_json_success($stats);
        } catch (Exception $e) {
            wp_send_json_error('Failed to get stats: ' . $e->getMessage());
        }
    }
}

// אתחול המחלקה
new AI_Website_Manager_Prompt_Manager();