<?php
/**
 * API Keys Settings Page - OpenRouter Exclusive
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load OpenRouter service
require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
$openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
$popular_models = $openrouter_service->get_popular_models();
$current_api_key = get_option('ai_manager_pro_openrouter_api_key', '');
$current_model = get_option('ai_manager_pro_default_model', 'openai/gpt-3.5-turbo');
$connection_status = !empty($current_api_key) ? 'configured' : 'not-configured';
?>

<div class="ai-openrouter-settings">
    <div class="settings-header">
        <div class="header-content">
            <div class="header-icon">🚀</div>
            <div class="header-text">
                <h1>הגדרות OpenRouter AI</h1>
                <p class="header-subtitle">גישה לכל המודלים המתקדמים ביותר במקום אחד</p>
            </div>
        </div>
        <div class="connection-status status-<?php echo $connection_status; ?>">
            <span class="status-indicator"></span>
            <span class="status-text">
                <?php echo $connection_status === 'configured' ? 'מחובר ✅' : 'לא מחובר ❌'; ?>
            </span>
        </div>
    </div>

    <div class="settings-grid">
        <!-- API Key Configuration -->
        <div class="settings-card primary-card">
            <div class="card-header">
                <h2>🔑 הגדרת מפתח API</h2>
                <p>הזן את מפתח ה-API שלך מ-OpenRouter</p>
            </div>

            <form method="post" id="openrouter-api-form" class="api-form">
                <?php wp_nonce_field('ai_manager_pro_api_keys', 'ai_manager_pro_nonce'); ?>

                <div class="form-group">
                    <label for="openrouter_api_key">
                        <strong>מפתח OpenRouter API</strong>
                        <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" id="openrouter_api_key" name="ai_manager_pro_openrouter_api_key"
                            value="<?php echo esc_attr($current_api_key); ?>"
                            placeholder="sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="api-key-input" required>
                        <button type="button" class="toggle-visibility" title="הצג/הסתר מפתח">
                            👁️
                        </button>
                    </div>
                    <div class="field-help">
                        <p>📝 <strong>איך להשיג מפתח API:</strong></p>
                        <ol>
                            <li>היכנס ל-<a href="https://openrouter.ai" target="_blank">OpenRouter.ai</a></li>
                            <li>צור חשבון או התחבר</li>
                            <li>עבור ל-"API Keys" בתפריט</li>
                            <li>צור מפתח חדש והעתק אותו לכאן</li>
                        </ol>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 שמור הגדרות
                    </button>
                    <button type="button" id="test-connection" class="btn btn-secondary">
                        🔍 בדוק חיבור
                    </button>
                </div>
            </form>
        </div>

        <!-- Model Selection -->
        <div class="settings-card">
            <div class="card-header">
                <h2>🤖 בחירת מודל ברירת מחדל</h2>
                <p>בחר את המודל שישמש כברירת מחדל ליצירת תוכן</p>
            </div>

            <div class="models-grid">
                <?php foreach ($popular_models as $model_id => $model_info): ?>
                    <div class="model-card <?php echo $current_model === $model_id ? 'selected' : ''; ?>"
                        data-model="<?php echo esc_attr($model_id); ?>">
                        <div class="model-icon"><?php echo $model_info['icon']; ?></div>
                        <div class="model-info">
                            <h3><?php echo esc_html($model_info['name']); ?></h3>
                            <p><?php echo esc_html($model_info['description']); ?></p>
                            <span class="model-category category-<?php echo $model_info['category']; ?>">
                                <?php
                                $categories = [
                                    'premium' => '⭐ פרימיום',
                                    'standard' => '⚡ סטנדרטי',
                                    'open-source' => '🔓 קוד פתוח'
                                ];
                                echo $categories[$model_info['category']] ?? $model_info['category'];
                                ?>
                            </span>
                        </div>
                        <div class="model-select">
                            <input type="radio" name="ai_manager_pro_default_model"
                                value="<?php echo esc_attr($model_id); ?>" <?php checked($current_model, $model_id); ?>>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="model-actions">
                <button type="button" id="save-model-selection" class="btn btn-primary">
                    ✅ שמור בחירת מודל
                </button>
                <button type="button" id="refresh-models" class="btn btn-secondary">
                    🔄 רענן רשימת מודלים
                </button>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="settings-card">
            <div class="card-header">
                <h2>📊 סטטיסטיקות שימוש</h2>
                <p>מעקב אחר השימוש שלך ב-API</p>
            </div>

            <div class="usage-stats" id="usage-stats">
                <div class="loading-stats">
                    <div class="spinner"></div>
                    <p>טוען נתונים...</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="settings-card">
            <div class="card-header">
                <h2>⚡ פעולות מהירות</h2>
                <p>כלים שימושיים לניהול ה-API</p>
            </div>

            <div class="quick-actions">
                <button type="button" class="action-btn" id="test-all-models">
                    🧪 בדוק כל המודלים
                </button>
                <button type="button" class="action-btn" id="clear-cache">
                    🗑️ נקה מטמון
                </button>
                <button type="button" class="action-btn" id="export-settings">
                    📤 ייצא הגדרות
                </button>
                <button type="button" class="action-btn" id="view-documentation">
                    📚 תיעוד API
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div id="test-results-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔍 תוצאות בדיקת חיבור</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="test-results-content">
                <!-- Results will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">סגור</button>
        </div>
    </div>
</div>

<style>
    /* OpenRouter Settings Styles */
    .ai-openrouter-settings {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        direction: rtl;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        font-size: 48px;
        opacity: 0.9;
    }

    .header-text h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }

    .header-subtitle {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 16px;
    }

    .connection-status {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .status-configured .status-indicator {
        background: #4ade80;
    }

    .status-not-configured .status-indicator {
        background: #f87171;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .settings-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .settings-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .primary-card {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .card-header h2 {
        margin: 0 0 8px 0;
        color: #1f2937;
        font-size: 20px;
        font-weight: 600;
    }

    .card-header p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #ef4444;
    }

    .input-group {
        position: relative;
        display: flex;
    }

    .api-key-input {
        flex: 1;
        padding: 12px 50px 12px 16px;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Monaco', 'Menlo', monospace;
        transition: border-color 0.2s ease;
    }

    .api-key-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .toggle-visibility {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        opacity: 0.6;
        transition: opacity 0.2s ease;
    }

    .toggle-visibility:hover {
        opacity: 1;
    }

    .field-help {
        margin-top: 12px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
        border-right: 4px solid #3b82f6;
    }

    .field-help p {
        margin: 0 0 8px 0;
        font-weight: 600;
        color: #374151;
    }

    .field-help ol {
        margin: 0;
        padding-right: 20px;
    }

    .field-help li {
        margin-bottom: 4px;
        color: #6b7280;
    }

    .field-help a {
        color: #3b82f6;
        text-decoration: none;
    }

    .field-help a:hover {
        text-decoration: underline;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .models-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin: 20px 0;
    }

    .model-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .model-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .model-card.selected {
        border-color: #10b981;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    }

    .model-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .model-info h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .model-info p {
        margin: 0 0 12px 0;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.4;
    }

    .model-category {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .category-premium {
        background: #fef3c7;
        color: #92400e;
    }

    .category-standard {
        background: #dbeafe;
        color: #1e40af;
    }

    .category-open-source {
        background: #d1fae5;
        color: #065f46;
    }

    .model-select {
        position: absolute;
        top: 16px;
        left: 16px;
    }

    .model-select input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #10b981;
    }

    .model-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .usage-stats {
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loading-stats {
        text-align: center;
        color: #6b7280;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 12px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .action-btn {
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        text-align: center;
    }

    .action-btn:hover {
        background: #f1f5f9;
        border-color: #3b82f6;
        color: #3b82f6;
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        color: #374151;
    }

    .modal-body {
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
    }

    /* Animations */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .settings-header {
            flex-direction: column;
            gap: 20px;
        }

        .models-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Toggle API key visibility
        $('.toggle-visibility').on('click', function () {
            const input = $(this).siblings('.api-key-input');
            const type = input.attr('type');

            if (type === 'password') {
                input.attr('type', 'text');
                $(this).text('🙈');
            } else {
                input.attr('type', 'password');
                $(this).text('👁️');
            }
        });

        // Model selection
        $('.model-card').on('click', function () {
            $('.model-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Test connection
        $('#test-connection').on('click', function () {
            const $btn = $(this);
            const originalText = $btn.text();

            $btn.prop('disabled', true).html('🔄 בודק...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_test_openrouter_connection',
                    api_key: $('#openrouter_api_key').val(),
                    nonce: $('#ai_manager_pro_nonce').val()
                },
                success: function (response) {
                    if (response.success) {
                        showTestResults('✅ החיבור תקין!', response.data.response, 'success');
                    } else {
                        showTestResults('❌ החיבור נכשל', response.data, 'error');
                    }
                },
                error: function () {
                    showTestResults('❌ שגיאה בבדיקת החיבור', 'אנא נסה שוב מאוחר יותר', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Save model selection
        $('#save-model-selection').on('click', function () {
            const selectedModel = $('input[name="ai_manager_pro_default_model"]:checked').val();

            if (!selectedModel) {
                alert('אנא בחר מודל');
                return;
            }

            const $btn = $(this);
            const originalText = $btn.text();

            $btn.prop('disabled', true).html('💾 שומר...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_save_default_model',
                    model: selectedModel,
                    nonce: $('#ai_manager_pro_nonce').val()
                },
                success: function (response) {
                    if (response.success) {
                        showNotification('✅ המודל נשמר בהצלחה!', 'success');
                    } else {
                        showNotification('❌ שגיאה בשמירת המודל', 'error');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Load usage statistics
        loadUsageStats();

        // Modal functionality
        $('.modal-close').on('click', function () {
            $(this).closest('.modal').hide();
        });

        // Quick actions
        $('#refresh-models').on('click', function () {
            location.reload();
        });

        $('#clear-cache').on('click', function () {
            if (confirm('האם אתה בטוח שברצונך לנקות את המטמון?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ai_manager_pro_clear_cache',
                        nonce: $('#ai_manager_pro_nonce').val()
                    },
                    success: function (response) {
                        showNotification('🗑️ המטמון נוקה בהצלחה!', 'success');
                    }
                });
            }
        });

        $('#view-documentation').on('click', function () {
            window.open('https://openrouter.ai/docs', '_blank');
        });

        function showTestResults(title, content, type) {
            const modal = $('#test-results-modal');
            const resultsContent = $('#test-results-content');

            resultsContent.html(`
            <div class="test-result test-result-${type}">
                <h4>${title}</h4>
                <p>${content}</p>
            </div>
        `);

            modal.show();
        }

        function loadUsageStats() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_manager_pro_get_usage_stats',
                    nonce: $('#ai_manager_pro_nonce').val()
                },
                success: function (response) {
                    if (response.success) {
                        displayUsageStats(response.data);
                    } else {
                        $('#usage-stats').html('<p>לא ניתן לטעון נתוני שימוש</p>');
                    }
                }
            });
        }

        function displayUsageStats(stats) {
            const html = `
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">${stats.total_requests}</div>
                    <div class="stat-label">📊 סה"כ בקשות</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${Math.round(stats.total_tokens / 1000)}K</div>
                    <div class="stat-label">🔤 טוקנים</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${Object.keys(stats.models_used).length}</div>
                    <div class="stat-label">🤖 מודלים בשימוש</div>
                </div>
            </div>
        `;

            $('#usage-stats').html(html);
        }

        function showNotification(message, type) {
            const notification = $(`
            <div class="notification notification-${type}">
                ${message}
            </div>
        `);

            $('body').append(notification);

            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 3000);
        }
    });
</script><?
php
// Include plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>