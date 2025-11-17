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
        <h1><?php _e('AI Content Generator', 'ai-website-manager-pro'); ?></h1>
        <p class="generator-subtitle">
            <?php _e('Create high-quality content using AI with your brand voice', 'ai-website-manager-pro'); ?>
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
                    <label for="content-length">
                        <?php _e('Content Length', 'ai-website-manager-pro'); ?>
                    </label>
                    <select id="content-length" class="form-control">
                        <option value="short">
                            <?php _e('Short (100-300 words)', 'ai-website-manager-pro'); ?>
                        </option>
                        <option value="medium" selected>
                            <?php _e('Medium (300-800 words)', 'ai-website-manager-pro'); ?>
                        </option>
                        <option value="long">
                            <?php _e('Long (800-1500 words)', 'ai-website-manager-pro'); ?>
                        </option>
                        <option value="very-long"><?php _e('Very Long (1500+ words)', 'ai-website-manager-pro'); ?>
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                            <div class="form-group">
                                <label for="brand-select">
                                    🏢 <?php _e('Brand Voice', 'ai-website-manager-pro'); ?>
                                    <button type="button" id="refresh-brand-suggestions" class="button button-small" style="margin-left: 10px;">
                                        <span class="dashicons dashicons-update"></span>
                                        <?php _e('Get Suggestions', 'ai-website-manager-pro'); ?>
                                    </button>
                                </label>
                                <select id="brand-select" class="form-control">
                                    <option value="">
                                        <?php _e('Select Brand...', 'ai-website-manager-pro'); ?>
                                    </option>
                                    <?php
                                    // Load brands from database
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'ai_website_manager_brands';
                                    $brands = $wpdb->get_results("SELECT id, name, is_active FROM {$table_name} ORDER BY is_active DESC, name ASC");

                                    foreach ($brands as $brand):
                                        $is_active = $brand->is_active ? ' 🟢' : '';
                                    ?>
                                        <option value="<?php echo esc_attr($brand->id); ?>" <?php selected($brand->is_active, 1); ?>>
                                            <?php echo esc_html($brand->name) . $is_active; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-help" id="brand-sync-status"></small>
                    </div>

                <div class="form-group">
                    <label for="ai-model">🤖 מודל AI</label>
                    <select id="ai-model" class="form-control">
                        <?php
                        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
                        $openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
                        $popular_models = $openrouter_service->get_popular_models();
                        $current_model = get_option('ai_manager_pro_default_model', 'openai/gpt-3.5-turbo');

                        foreach ($popular_models as $model_id => $model_info):
                            ?>
                                <option value="<?php echo esc_attr($model_id); ?>" <?php selected($current_model, $model_id); ?>>
                                    <?php echo $model_info['icon'] . ' ' . esc_html($model_info['name']); ?>
                                    - <?php echo esc_html($model_info['description']); ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="content-topic"><?php _e('Topic / Subject', 'ai-website-manager-pro'); ?></label>
                                        <input type="text" id="content-topic" class="form-control"
                                            placeholder="<?php _e('Enter the main topic or subject for your content...', 'ai-website-manager-pro'); ?>">
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
                                <label for="content-keywords"><?php _e('Keywords (Optional)', 'ai-website-manager-pro'); ?></label>
                <input type="text" id="content-keywords" class="form-control"
                                    placeholder="<?php _e('keyword1, keyword2, keyword3...', 'ai-website-manager-pro'); ?>">
                                    <small class="form-help">
                                        <?php _e('Separate keywords with commas', 'ai-website-manager-pro'); ?>
                                    </small>
                            </div>
                            </div>

                            <div class="form-group">
                <label
                    for=" additional-instructions">
                                <?php _e('Additional Instructions', 'ai-website-manager-pro'); ?></label>
                                <textarea id="additional-instructions" class="form-control" rows="4"
                                    placeholder="<?php _e('Any specific requirements, tone adjustments, or special instructions...', 'ai-website-manager-pro'); ?>"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" id="generate-content-btn" class="button button-primary button-large">
                    <span class=" dashicons dashicons-edit"></span>
                                    <?php _e('Generate Content', 'ai-website-manager-pro'); ?>
                                </button>

                                <button type="button" id="use-prompt-library-btn" class="button button-secondary">
                                    <span class="dashicons dashicons-book"></span>
                                    <?php _e('Use Prompt Library', 'ai-website-manager-pro'); ?>
                                </button>

                                <button type="button" id="save-as-template-btn" class="button button-secondary"
                                    disabled>
                                    <span class="dashicons dashicons-saved"></span>
                                    <?php _e('Save as Template', 'ai-website-manager-pro'); ?>
                                </button>
                            </div>
                </div>

                <div class="form-section">
                    <h2>
                        <?php _e('Generated Content', 'ai-website-manager-pro'); ?>
                    </h2>

                    <div class="content-output">
                        <div class="output-toolbar">
                                <div class="toolbar-left">
                            <span class="content-stats" id="content-stats">
                                <?php _e('Ready to generate', 'ai-website-manager-pro'); ?>
                            </span>
                        </div>
                        <div class="toolbar-right">
                            <button type="button" id="copy-content-btn" class="button button-small" disabled>
                                <span class="dashicons dashicons-admin-page"></span>
                            <?php _e('Copy', 'ai-website-manager-pro'); ?>
                        </button>
                        <button type=" button" id="export-content-btn" class="button button-small" disabled>
                                    <span class="dashicons dashicons-download"></span>
                                    <?php _e('Export', 'ai-website-manager-pro'); ?>
                            </button>
                            <button type="button" id="regenerate-btn" class="button button-small" disabled>
                                <span class="dashicons dashicons-update"></span>
                                <?php _e('Regenerate', 'ai-website-manager-pro'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="content-editor-wrapper">
                        <textarea id="generated-content" class="content-editor"
                            placeholder="<?php _e('Generated content will appear here...', 'ai-website-manager-pro'); ?>"></textarea>
                    </div>

                    <div class="generation-status" id="generation-status" style="display: none;">
                        <div class="status-indicator">
                        <div class=" loading-spinner"></div>
                        <span class="status-text">
                            <?php _e('Generating content...', 'ai-website-manager-pro'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="generator-sidebar">
        <!-- Brand Suggestions Panel -->
        <div class="sidebar-section" id="brand-suggestions-panel" style="display: none;">
            <h3>
                💡 <?php _e('Content Suggestions from Your Brand', 'ai-website-manager-pro'); ?>
            </h3>
            <div id="brand-info" class="brand-info-box" style="margin-bottom: 15px; padding: 10px; background: #f0f8ff; border-radius: 6px; border-left: 4px solid #667eea;">
                <!-- Brand info loaded via AJAX -->
            </div>
            <div id="suggestions-list" class="suggestions-list">
                <!-- Suggestions loaded via AJAX -->
            </div>
        </div>

        <div class="sidebar-section">
            <h3>
                <?php _e('Recent Generations', 'ai-website-manager-pro'); ?>
            </h3>
            <div class="recent-list">
                <div class="recent-item">
                    <div class="recent-title">
                        <?php _e('Blog Post: AI in Marketing', 'ai-website-manager-pro'); ?>
                    </div>
                        <div class="recent-meta"><?php _e('2 hours ago', 'ai-website-manager-pro'); ?>
                </div>
            </div>
            <div class="recent-item">
                <div class="recent-title"><?php _e('Product Description: Smart Watch', 'ai-website-manager-pro'); ?>
                </div>
                <div class="recent-meta">
                    <?php _e('Yesterday', 'ai-website-manager-pro'); ?>
                </div>
            </div>
            <div class="recent-item">
                <div class="recent-title">
                    <?php _e('Social Media: Launch Announcement', 'ai-website-manager-pro'); ?>
                </div>
                <div class="recent-meta">
                    <?php _e('2 days ago', 'ai-website-manager-pro'); ?>
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

        // Check for active brand and load suggestions automatically
        const selectedBrand = $('#brand-select').val();
        if (selectedBrand) {
            loadBrandSuggestions(selectedBrand);
        }

        // Brand select change event
        $('#brand-select').on('change', function() {
            const brandId = $(this).val();
            if (brandId) {
                loadBrandSuggestions(brandId);
                $('#brand-sync-status').html('✅ <?php _e('Synced with brand', 'ai-website-manager-pro'); ?>').css('color', '#00a32a');
            } else {
                $('#brand-suggestions-panel').hide();
                $('#brand-sync-status').html('');
            }
        });

        // Refresh suggestions button
        $('#refresh-brand-suggestions').on('click', function() {
            const brandId = $('#brand-select').val();
            if (brandId) {
                loadBrandSuggestions(brandId);
            } else {
                alert('<?php _e('Please select a brand first', 'ai-website-manager-pro'); ?>');
            }
        });

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
    // Load brand suggestions
    function loadBrandSuggestions(brandId) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_get_brand_suggestions',
                brand_id: brandId,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            beforeSend: function() {
                jQuery('#brand-info').html('<div style="text-align: center;"><span class="dashicons dashicons-update spin"></span> <?php _e('Loading...', 'ai-website-manager-pro'); ?></div>');
                jQuery('#suggestions-list').html('');
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayBrandSuggestions(response.data);
                    jQuery('#brand-suggestions-panel').slideDown();
                } else {
                    jQuery('#brand-info').html('<p style="color: #dc3232;"><?php _e('Failed to load suggestions', 'ai-website-manager-pro'); ?>: ' + (response.data || 'Unknown error') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                jQuery('#brand-info').html('<p style="color: #dc3232;"><?php _e('Network error', 'ai-website-manager-pro'); ?>: ' + error + '</p>');
            }
        });
    }

    // Display brand suggestions
    function displayBrandSuggestions(data) {
        const brand = data.brand;
        const suggestions = data.suggestions;

        // Display brand info
        let brandHtml = '<strong style="color: #667eea; font-size: 1.1em;">' + brand.name + '</strong><br>';
        if (brand.voice) brandHtml += '<small><strong><?php _e('Voice:', 'ai-website-manager-pro'); ?></strong> ' + brand.voice + '</small><br>';
        if (brand.tone) brandHtml += '<small><strong><?php _e('Tone:', 'ai-website-manager-pro'); ?></strong> ' + brand.tone + '</small>';
        if (brand.keywords && brand.keywords.length > 0) {
            brandHtml += '<div style="margin-top: 8px;">';
            brand.keywords.slice(0, 5).forEach(keyword => {
                brandHtml += '<span style="background: #667eea; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.85em; margin: 2px; display: inline-block;">' + keyword.trim() + '</span>';
            });
            brandHtml += '</div>';
        }
        jQuery('#brand-info').html(brandHtml);

        // Display suggestions
        if (suggestions && suggestions.length > 0) {
            let suggestionsHtml = '';
            suggestions.forEach((suggestion, index) => {
                const typeIcon = getContentTypeIcon(suggestion.type);
                suggestionsHtml += '<div class="suggestion-item" style="background: white; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #e9ecef; cursor: pointer; transition: all 0.2s;" data-topic="' + suggestion.topic + '" data-type="' + suggestion.type + '">';
                suggestionsHtml += '<div style="display: flex; align-items: start; gap: 8px;">';
                suggestionsHtml += '<span style="font-size: 1.2em;">' + typeIcon + '</span>';
                suggestionsHtml += '<div style="flex: 1;">';
                suggestionsHtml += '<strong style="color: #333; font-size: 0.95em;">' + suggestion.title + '</strong><br>';
                suggestionsHtml += '<small style="color: #666;">' + suggestion.reason + '</small>';
                suggestionsHtml += '</div>';
                suggestionsHtml += '</div>';
                suggestionsHtml += '</div>';
            });
            jQuery('#suggestions-list').html(suggestionsHtml);

            // Add click handlers to suggestions
            jQuery('.suggestion-item').on('click', function() {
                const topic = jQuery(this).data('topic');
                const type = jQuery(this).data('type');
                jQuery('#content-topic').val(topic);
                jQuery('#content-type').val(type);
                jQuery(this).css('border-color', '#667eea').css('background', '#f8f9ff');
                showNotification('<?php _e('Suggestion applied!', 'ai-website-manager-pro'); ?>', 'success');
            }).on('mouseenter', function() {
                jQuery(this).css('box-shadow', '0 2px 8px rgba(0,0,0,0.1)').css('transform', 'translateY(-2px)');
            }).on('mouseleave', function() {
                jQuery(this).css('box-shadow', 'none').css('transform', 'none');
            });
        } else {
            jQuery('#suggestions-list').html('<p style="text-align: center; color: #666; padding: 20px;"><?php _e('No suggestions available', 'ai-website-manager-pro'); ?></p>');
        }
    }

    // Get content type icon
    function getContentTypeIcon(type) {
        const icons = {
            'blog_post': '📰',
            'article': '📄',
            'guide': '📖',
            'review': '⭐',
            'product': '🛍️',
            'social_media': '📱',
            'newsletter': '📧'
        };
        return icons[type] || '📝';
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

    // Check if a template was selected from dashboard
    const selectedTemplate = sessionStorage.getItem('ai_selected_template');
    if (selectedTemplate) {
        // Set the content type to the selected template
        $('#content-type').val(selectedTemplate);

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
        $('html, body').animate({
            scrollTop: $('#content-topic').offset().top - 100
        }, 500);

        // Focus on the topic input
        $('#content-topic').focus();
    }
    });
</script>

<?php
// Include plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>