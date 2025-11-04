<?php
/**
 * Content Generator Page
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="content-generator-container">
    <div class="generator-header">
        <h1>🤖 מחולל תוכן AI</h1>
        <p class="generator-subtitle">
            צור תוכן איכותי באמצעות AI עם קול המותג שלך
        </p>
    </div>

    <div class="generator-form">
        <div class="form-section">
            <h2>⚙️ הגדרות יצירת תוכן</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="content-type">📝 סוג התוכן (עם תבניות SEO אוטומטיות)</label>
                    <select id="content-type" class="form-control">
                        <option value="blog_post">📰 פוסט בלוג (800-1200 מילים)</option>
                        <option value="article">📄 מאמר מקיף (1500-2500 מילים + תוכן עניינים + טבלאות)</option>
                        <option value="guide">📖 מדריך הדרכה (צעד אחר צעד + הערכות זמן)</option>
                        <option value="review">⭐ ביקורת מוצר (דירוגים + טבלת השוואה)</option>
                        <option value="product">🛍️ תיאור מוצר (מפרטים + תכונות)</option>
                        <option value="social_media">📱 פוסט לרשתות חברתיות</option>
                        <option value="newsletter">📧 ניוזלטר אימייל</option>
                    </select>
                    <small class="form-help" style="color: #0073aa; font-weight: 600;">
                        ✨ תבניות SEO חדשות! כל תוכן נוצר עם מבנה מושלם של כותרות, טבלאות ורשימות
                    </small>
                </div>

                <div class="form-group">
                    <label for="content-length">📏 אורך התוכן</label>
                    <select id="content-length" class="form-control">
                        <option value="short">קצר (100-300 מילים)</option>
                        <option value="medium" selected>בינוני (300-800 מילים)</option>
                        <option value="long">ארוך (800-1500 מילים)</option>
                        <option value="very-long">ארוך מאוד (1500+ מילים)</option>
                    </select>
                    <small class="form-help">
                        בחר את אורך התוכן בהתאם לסוג והמטרה
                    </small>
                </div>
            </div>

            <div class="form-row">
                            <div class="form-group">
                                <label for="brand-select">🎤 קול המותג</label>
                                <select id="brand-select" class="form-control">
                                    <option value="">בחר מותג...</option>
                                    <option value="tech-startup">סטארטאפ טכנולוגי</option>
                                    <option value="professional-services">שירותים מקצועיים</option>
                                    <option value="e-commerce">מסחר אלקטרוני</option>
                    </select>
                    <small class="form-help">
                        המותג יקבע את הטון והסגנון של התוכן
                    </small>
                </div>

                <div class="form-group">
                    <label for="ai-provider">🔌 ספק AI</label>
                    <select id="ai-provider" class="form-control">
                        <?php
                        $default_provider = get_option('ai_manager_pro_default_provider', 'openai');

                        // Check which providers have API keys configured
                        $providers = [
                            'openai' => [
                                'name' => 'OpenAI',
                                'icon' => '🤖',
                                'has_key' => !empty(get_option('ai_manager_pro_openai_api_key'))
                            ],
                            'anthropic' => [
                                'name' => 'Anthropic (Claude)',
                                'icon' => '🧠',
                                'has_key' => !empty(get_option('ai_manager_pro_anthropic_api_key'))
                            ],
                            'openrouter' => [
                                'name' => 'OpenRouter',
                                'icon' => '🌐',
                                'has_key' => !empty(get_option('ai_manager_pro_openrouter_api_key'))
                            ],
                            'deepseek' => [
                                'name' => 'DeepSeek',
                                'icon' => '🔬',
                                'has_key' => !empty(get_option('ai_manager_pro_deepseek_key'))
                            ]
                        ];

                        foreach ($providers as $provider_id => $provider_info):
                            if ($provider_info['has_key']):
                        ?>
                            <option value="<?php echo esc_attr($provider_id); ?>"
                                    <?php selected($default_provider, $provider_id); ?>>
                                <?php echo $provider_info['icon'] . ' ' . esc_html($provider_info['name']); ?>
                            </option>
                        <?php
                            endif;
                        endforeach;

                        // If no providers configured, show message
                        if (!array_filter($providers, function($p) { return $p['has_key']; })):
                        ?>
                            <option value="">אין ספקי AI מוגדרים - נא להגדיר מפתח API</option>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">
                        ספק ה-AI שישמש ליצירת התוכן
                    </small>
                </div>

                <div class="form-group">
                    <label for="ai-model">🤖 מודל AI</label>
                    <select id="ai-model" class="form-control">
                        <?php
                        $current_model = get_option('ai_manager_pro_default_model', '');

                        // OpenAI models
                        if (!empty(get_option('ai_manager_pro_openai_api_key'))):
                        ?>
                            <optgroup label="🤖 OpenAI">
                                <option value="gpt-4" <?php selected($current_model, 'gpt-4'); ?>>GPT-4 - חכם ומדויק ביותר</option>
                                <option value="gpt-4-turbo" <?php selected($current_model, 'gpt-4-turbo'); ?>>GPT-4 Turbo - מהיר ועדכני</option>
                                <option value="gpt-3.5-turbo" <?php selected($current_model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo - מהיר וחסכוני</option>
                            </optgroup>
                        <?php endif; ?>

                        <?php
                        // Anthropic models
                        if (!empty(get_option('ai_manager_pro_anthropic_api_key'))):
                        ?>
                            <optgroup label="🧠 Anthropic Claude">
                                <option value="claude-3-opus" <?php selected($current_model, 'claude-3-opus'); ?>>Claude 3 Opus - הכי מתקדם</option>
                                <option value="claude-3-sonnet" <?php selected($current_model, 'claude-3-sonnet'); ?>>Claude 3 Sonnet - איזון מושלם</option>
                                <option value="claude-3-haiku" <?php selected($current_model, 'claude-3-haiku'); ?>>Claude 3 Haiku - מהיר וזול</option>
                            </optgroup>
                        <?php endif; ?>

                        <?php
                        // DeepSeek models
                        if (!empty(get_option('ai_manager_pro_deepseek_key'))):
                        ?>
                            <optgroup label="🔬 DeepSeek">
                                <option value="deepseek-chat" <?php selected($current_model, 'deepseek-chat'); ?>>DeepSeek Chat - שיחה כללית</option>
                                <option value="deepseek-coder" <?php selected($current_model, 'deepseek-coder'); ?>>DeepSeek Coder - כתיבת קוד</option>
                            </optgroup>
                        <?php endif; ?>

                        <?php
                        // OpenRouter models
                        if (!empty(get_option('ai_manager_pro_openrouter_api_key'))):
                            require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
                            $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
                            $popular_models = $openrouter_service->get_popular_models();
                        ?>
                            <optgroup label="🌐 OpenRouter">
                                <?php foreach ($popular_models as $model_id => $model_info): ?>
                                    <option value="<?php echo esc_attr($model_id); ?>"
                                            <?php selected($current_model, $model_id); ?>>
                                        <?php echo $model_info['icon'] . ' ' . esc_html($model_info['name']); ?>
                                        - <?php echo esc_html($model_info['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">
                        בחר מודל לפי הצורך: GPT-4 לאיכות, GPT-3.5 למהירות, Claude ליצירתיות
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label for="content-topic">📝 נושא / כותרת התוכן</label>
                                        <input type="text" id="content-topic" class="form-control"
                                            placeholder="הזן את הנושא או הכותרת הראשית לתוכן שלך... (למשל: 'כיצד לבחור מחשב נייד')">
                                        <small class="form-help">
                        הנושא הראשי שעליו ייכתב התוכן - היה ספציפי וברור
                    </small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="post-category">📁 קטגוריה לפרסום</label>
                                    <select id="post-category" class="form-control">
                                        <option value="">ללא קטגוריה (Uncategorized)</option>
                                        <?php
                                        $categories = get_categories(['hide_empty' => false]);
                                        foreach ($categories as $category) {
                                            echo '<option value="' . esc_attr($category->term_id) . '">' .
                                                 esc_html($category->name) . ' (' . $category->count . ' פוסטים)</option>';
                                        }
                                        ?>
                                    </select>
                                    <small class="form-help">
                                        הפוסט שייווצר יקושר אוטומטית לקטגוריה זו
                                    </small>
                                </div>

                            <div class="form-group">
                                <label for="content-keywords">🔑 מילות מפתח (אופציונלי)</label>
                <input type="text" id="content-keywords" class="form-control"
                                    placeholder="מילה1, מילה2, מילה3... (למשל: 'שיווק דיגיטלי, קידום אתרים, SEO')">
                                    <small class="form-help">
                                        הפרד מילות מפתח בפסיקים - ישפרו את ה-SEO של התוכן
                                    </small>
                            </div>
                            </div>

                            <div class="form-group">
                <label for="additional-instructions">📋 הוראות נוספות (אופציונלי)</label>
                                <textarea id="additional-instructions" class="form-control" rows="4"
                                    placeholder="דרישות ספציפיות, התאמות טון, או הוראות מיוחדות... (למשל: 'השתמש בסגנון פשוט ונגיש', 'הוסף דוגמאות מעשיות')"></textarea>
                                <small class="form-help">
                        הוסף הנחיות מיוחדות שיעזרו ל-AI ליצור את התוכן המדויק שאתה צריך
                    </small>
                            </div>

                            <div class="form-actions">
                                <button type="button" id="generate-content-btn" class="button button-primary button-large">
                    <span class="dashicons dashicons-edit"></span>
                                    ✨ צור תוכן
                                </button>

                                <button type="button" id="use-prompt-library-btn" class="button button-secondary">
                                    <span class="dashicons dashicons-book"></span>
                                    📚 השתמש בספריית פרומפטים
                                </button>

                                <button type="button" id="save-as-template-btn" class="button button-secondary"
                                    disabled>
                                    <span class="dashicons dashicons-saved"></span>
                                    💾 שמור כתבנית
                                </button>
                            </div>
                </div>

                <div class="form-section">
                    <h2>📄 התוכן שנוצר</h2>

                    <div class="content-output">
                        <div class="output-toolbar">
                                <div class="toolbar-left">
                            <span class="content-stats" id="content-stats">
                                ⚡ מוכן ליצירת תוכן
                            </span>
                        </div>
                        <div class="toolbar-right">
                            <button type="button" id="copy-content-btn" class="button button-small" disabled>
                                <span class="dashicons dashicons-admin-page"></span>
                            📋 העתק
                        </button>
                        <button type="button" id="export-content-btn" class="button button-small" disabled>
                                    <span class="dashicons dashicons-download"></span>
                                    💾 ייצא
                            </button>
                            <button type="button" id="regenerate-btn" class="button button-small" disabled>
                                <span class="dashicons dashicons-update"></span>
                                🔄 צור מחדש
                            </button>
                        </div>
                    </div>

                    <div class="content-editor-wrapper">
                        <textarea id="generated-content" class="content-editor"
                            placeholder="התוכן שנוצר יופיע כאן... לחץ על 'צור תוכן' כדי להתחיל"></textarea>
                    </div>

                    <div class="generation-status" id="generation-status" style="display: none;">
                        <div class="status-indicator">
                        <div class="loading-spinner"></div>
                        <span class="status-text">
                            ⏳ יוצר תוכן מושלם... אנא המתן
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="generator-sidebar">
        <div class="sidebar-section">
            <h3>🕒 יצירות אחרונות</h3>
            <div class="recent-list">
                <div class="recent-item">
                    <div class="recent-title">
                        📰 פוסט בלוג: AI בשיווק
                    </div>
                        <div class="recent-meta">לפני שעתיים
                </div>
            </div>
            <div class="recent-item">
                <div class="recent-title">🛍️ תיאור מוצר: שעון חכם
                </div>
                <div class="recent-meta">
                    אתמול
                </div>
            </div>
            <div class="recent-item">
                <div class="recent-title">
                    📱 רשתות חברתיות: הודעת השקה
                </div>
                <div class="recent-meta">
                    לפני יומיים
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-section">
        <h3>
            ✨ תבניות SEO חדשות בגרסה 3.3.1!
        </h3>
        <div class="tips-list">
            <div class="tip-item">
                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <span><strong>מאמר:</strong> תוכן עניינים + טבלאות + FAQ</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <span><strong>מדריך:</strong> צעדים ממוספרים + הערכות זמן</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <span><strong>ביקורת:</strong> דירוגים ★ + השוואות</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <span><strong>מוצר:</strong> מפרטים + תכונות עם ✅</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-chart-line" style="color: #0073aa;"></span>
                <span><strong>ציון SEO אוטומטי</strong> לכל תוכן (0-100)</span>
            </div>
        </div>
    </div>

    <div class="sidebar-section">
        <h3>
            💡 טיפים מהירים
        </h3>
        <div class="tips-list">
            <div class="tip-item">
                <span class="dashicons dashicons-lightbulb"></span>
                    <span>בחר קטגוריה לפני יצירת התוכן</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-star-filled"></span>
                <span>השתמש במותג לטון עקבי</span>
            </div>
            <div class="tip-item">
                <span class="dashicons dashicons-admin-tools"></span>
                <span>בחר את סוג התוכן המתאים למטרה</span>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    /* Content Generator Styles */
    .content-generator-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .generator-header {
        grid-column: 1 / -1;
        text-align: center;
        margin-bottom: 20px;
    }

    .generator-header h1 {
        font-size: 28px;
        margin-bottom: 10px;
        color: #1d2327;
    }

    .generator-subtitle {
        font-size: 16px;
        color: #646970;
        margin: 0;
    }

    .generator-form {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .form-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-section h2 {
        margin: 0 0 20px 0;
        font-size: 18px;
        color: #1d2327;
        border-bottom: 2px solid #0073aa;
        padding-bottom: 10px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #1d2327;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #0073aa;
        box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2);
    }

    .form-help {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #646970;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .button-large {
        padding: 12px 24px;
        font-size: 16px;
    }

    .content-output {
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
    }

    .output-toolbar {
        background: #f9f9f9;
        border-bottom: 1px solid #ddd;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .toolbar-left .content-stats {
        font-size: 13px;
        color: #646970;
        font-weight: 500;
    }

    .toolbar-right {
        display: flex;
        gap: 8px;
    }

    .content-editor-wrapper {
        position: relative;
    }

    .content-editor {
        width: 100%;
        min-height: 400px;
        padding: 20px;
        border: none;
        resize: vertical;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 14px;
        line-height: 1.6;
        background: #fff;
    }

    .content-editor:focus {
        outline: none;
    }

    .generation-status {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #0073aa;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .status-text {
        font-weight: 500;
        color: #1d2327;
    }

    .generator-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar-section h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #1d2327;
    }

    .recent-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .recent-item {
        padding: 12px;
        background: #f9f9f9;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .recent-item:hover {
        background: #f0f0f1;
    }

    .recent-title {
        font-weight: 500;
        color: #1d2327;
        margin-bottom: 4px;
        font-size: 13px;
    }

    .recent-meta {
        font-size: 12px;
        color: #646970;
    }

    .tips-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tip-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 13px;
        line-height: 1.4;
    }

    .tip-item .dashicons {
        color: #0073aa;
        margin-top: 2px;
        flex-shrink: 0;
    }

    @media (max-width: 1024px) {
        .content-generator-container {
            grid-template-columns: 1fr;
        }

        .generator-sidebar {
            order: -1;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content-generator-container {
            padding: 15px;
        }

        .form-section,
        .sidebar-section {
            padding: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .output-toolbar {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .toolbar-right {
            justify-content: center;
        }
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        let isGenerating = false;
        
        // Load brands into select dropdown
        loadBrands();

        // Generate content button
        $('#generate-content-btn').on('click', function () {
            if (isGenerating) return;

            const topic = $('#content-topic').val().trim();
            if (!topic) {
                alert('<?php _e('Please enter a topic for your content.', 'ai-website-manager-pro'); ?>');
        return;
    }

            generateContent();
        });

    // Use prompt library button
    $('#use-prompt-library-btn').on('click', function () {
        // Check if prompt library is available
        if (typeof AIManagerProPromptLibrary !== 'undefined') {
            AIManagerProPromptLibrary.openModal();
        } else {
            // Fallback - open a simple prompt selection dialog
            const prompts = [
                'Write a comprehensive blog post about',
                'Create an engaging social media post about',
                'Develop a professional product description for',
                'Compose an informative email newsletter about',
                'Generate a compelling landing page for'
            ];
            
            const selectedPrompt = prompt('Choose a prompt template:\n\n' + 
                prompts.map((p, i) => `${i + 1}. ${p}`).join('\n') + 
                '\n\nEnter number (1-5):');
            
            if (selectedPrompt && prompts[selectedPrompt - 1]) {
                const currentTopic = $('#content-topic').val();
                $('#additional-instructions').val(prompts[selectedPrompt - 1] + ' ' + currentTopic);
            }
        }
    });

    // Copy content button
    $('#copy-content-btn').on('click', function () {
            const content = $('#generated-content').val();
    if (content) {
        navigator.clipboard.writeText(content).then(function () {
            showNotification('<?php _e('Content copied to clipboard!', 'ai-website-manager-pro'); ?>', 'success');
        });
    }
        });

    // Regenerate button
    $('#regenerate-btn').on('click', function () {
        if (!isGenerating) {
            generateContent();
        }
    });

    // Generate content function
    function generateContent() {
        const topic = $('#content-topic').val().trim();
        if (!topic) {
            alert('<?php _e('Please enter a topic for your content.', 'ai-website-manager-pro'); ?>');
            return;
        }

        isGenerating = true;

        // Show loading state
        $('#generation-status').show();
        $('#generate-content-btn').prop('disabled', true);
        $('#regenerate-btn').prop('disabled', true);

        // Collect form data
        const formData = {
            action: 'ai_manager_pro_generate_content',
            topic: topic,
            content_type: $('#content-type').val(),
            content_length: $('#content-length').val(),
            brand_id: $('#brand-select').val(),
            post_category: $('#post-category').val(),
            ai_provider: $('#ai-provider').val(),
            ai_model: $('#ai-model').val(),
            keywords: $('#content-keywords').val(),
            additional_instructions: $('#additional-instructions').val(),
            auto_publish: false,
            post_status: 'draft',
            nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
        };

        // Make AJAX call to generate content
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Handle content structure
                    let content = response.data.content;
                    if (typeof content === 'object' && content.content) {
                        $('#generated-content').val(content.content);

                        // Show SEO score if available
                        if (content.seo_score) {
                            let scoreColor = content.seo_score >= 80 ? 'green' : (content.seo_score >= 60 ? 'orange' : 'red');
                            let scoreMessage = `<span style="color: ${scoreColor}; font-weight: bold;">✓ ציון SEO: ${content.seo_score}/100</span>`;
                            $('#content-stats').html(scoreMessage + ' | ' + $('#content-stats').text());
                        }

                        updateContentStats(content.content);
                    } else {
                        $('#generated-content').val(content);
                        updateContentStats(content);
                    }

                    let message = '<?php _e('Content generated successfully!', 'ai-website-manager-pro'); ?>';
                    if (response.data.seo_score) {
                        message += ' ציון SEO: ' + response.data.seo_score + '/100';
                    }
                    if (response.data.fallback) {
                        message = '<?php _e('Content generated using fallback template', 'ai-website-manager-pro'); ?>';
                    }

                    showNotification(message, response.data.fallback ? 'warning' : 'success');

                    // Enable action buttons
                    $('#copy-content-btn').prop('disabled', false);
                    $('#export-content-btn').prop('disabled', false);
                    $('#save-as-template-btn').prop('disabled', false);
                } else {
                    showNotification('<?php _e('Content generation failed: ', 'ai-website-manager-pro'); ?>' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Content generation error:', error);
                showNotification('<?php _e('Network error occurred', 'ai-website-manager-pro'); ?>', 'error');
            },
            complete: function() {
                // Hide loading state
                $('#generation-status').hide();
                $('#generate-content-btn').prop('disabled', false);
                $('#regenerate-btn').prop('disabled', false);
                isGenerating = false;
            }
        });
    }

    // Export content button
    $('#export-content-btn').on('click', function() {
        const content = $('#generated-content').val();
        const topic = $('#content-topic').val() || 'generated-content';
        
        if (content) {
            // Create and download file
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${topic.replace(/[^a-z0-9]/gi, '-').toLowerCase()}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showNotification('<?php _e('Content exported successfully!', 'ai-website-manager-pro'); ?>', 'success');
        }
    });

    // Save as template button
    $('#save-as-template-btn').on('click', function() {
        const content = $('#generated-content').val();
        const topic = $('#content-topic').val();
        
        if (content && topic) {
            // Save to localStorage as a simple template system
            const templates = JSON.parse(localStorage.getItem('ai_content_templates') || '[]');
            const template = {
                id: Date.now(),
                title: topic,
                content: content,
                created: new Date().toISOString()
            };
            
            templates.push(template);
            localStorage.setItem('ai_content_templates', JSON.stringify(templates));
            
            showNotification('<?php _e('Content saved as template!', 'ai-website-manager-pro'); ?>', 'success');
        }
    });

    // Update content statistics
    function updateContentStats(content) {
        const wordCount = content.split(/\s+/).length;
        const charCount = content.length;
        $('#content-stats').text(`${wordCount} words, ${charCount} characters`);
    }
    
    // Load brands function
    function loadBrands() {
        // Get brands from WordPress options
        const brands = <?php 
            $brands = get_option('ai_manager_pro_brands_data', []);
            echo json_encode($brands);
        ?>;
        
        const $brandSelect = $('#brand-select');
        $brandSelect.empty().append('<option value=""><?php _e('Select Brand...', 'ai-website-manager-pro'); ?></option>');
        
        Object.keys(brands).forEach(function(brandId) {
            const brand = brands[brandId];
            $brandSelect.append(`<option value="${brandId}">${brand.name || brandId}</option>`);
        });
        
        // Set active brand as selected
        const activeBrand = '<?php echo get_option('ai_manager_pro_active_brand', ''); ?>';
        if (activeBrand) {
            $brandSelect.val(activeBrand);
        }
    }

    // Show notification
    function showNotification(message, type) {
        // Simple notification system
        const notification = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
        $('.content-generator-container').prepend(notification);

        setTimeout(function () {
            notification.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Make ajaxurl available
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Update content stats on input
    $('#generated-content').on('input', function () {
        const content = $(this).val();
        if (content) {
            updateContentStats(content);
        } else {
            $('#content-stats').text('<?php _e('Ready to generate', 'ai-website-manager-pro'); ?>');
        }
    });

    // Function to load template from sessionStorage
    function loadTemplateFromDashboard() {
        console.log('🔍 Checking for selected template...');
        const selectedTemplate = sessionStorage.getItem('ai_selected_template');
        console.log('📦 SessionStorage value:', selectedTemplate);

        if (selectedTemplate) {
            console.log('✅ Template found:', selectedTemplate);

            // Set the content type to the selected template
            $('#content-type').val(selectedTemplate);
            console.log('✅ Content type dropdown set to:', selectedTemplate);

            // Trigger change event to update UI if needed
            $('#content-type').trigger('change');

            // Highlight the dropdown to show it changed
            $('#content-type').css({
                'border': '3px solid #667eea',
                'box-shadow': '0 0 10px rgba(102, 126, 234, 0.5)',
                'background': 'linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%)'
            });

            // Remove highlight after 3 seconds
            setTimeout(function() {
                $('#content-type').css({
                    'border': '',
                    'box-shadow': '',
                    'background': ''
                });
            }, 3000);

            // Clear the sessionStorage so it doesn't keep loading on refresh
            sessionStorage.removeItem('ai_selected_template');

            // Show a notification
            const templateNames = {
                'article': 'מאמר מקיף',
                'guide': 'מדריך הדרכה',
                'review': 'ביקורת מוצר',
                'product': 'תיאור מוצר',
                'blog_post': 'פוסט בלוג'
            };

            const templateName = templateNames[selectedTemplate] || selectedTemplate;
            showNotification(`✨ תבנית "${templateName}" נבחרה! מוכן ליצור תוכן מקצועי עם SEO מושלם.`, 'success');

            // Scroll to the topic input to encourage user to start
            setTimeout(function() {
                const topicInput = $('#content-topic');
                if (topicInput.length) {
                    $('html, body').animate({
                        scrollTop: topicInput.offset().top - 100
                    }, 500);

                    // Focus on the topic input
                    topicInput.focus();
                }
            }, 100);
        } else {
            console.log('ℹ️ No template selected from dashboard');
        }
    }

    // Load template immediately
    loadTemplateFromDashboard();

    // Also load template if page loaded via SPA
    $(document).on('ai-page-loaded', function() {
        console.log('🔄 Page loaded via SPA, checking template again...');
        loadTemplateFromDashboard();
    });
    });
</script>

<?php
// Include plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>