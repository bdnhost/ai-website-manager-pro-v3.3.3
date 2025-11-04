<?php
/**
 * Multi-Channel Content Management Page
 * דף ניהול תוכן רב-ערוצי
 */

if (!defined('ABSPATH')) {
    exit;
}

// טעינת מנהל התוכן הרב-ערוצי
$multi_channel_manager = new AI_Website_Manager_Multi_Channel_Content_Manager();
$brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();

$brands = $brand_manager->get_all_brands();
$active_brand = $brand_manager->get_active_brand();
$available_channels = $multi_channel_manager->get_available_channels();
$content_stats = $multi_channel_manager->get_content_generation_stats();
?>

<div class="wrap ai-multi-channel-content">
    <div class="content-header">
        <h1>🚀 יצירת תוכן רב-ערוצי</h1>
        <p class="description">צור תוכן מקצועי לכל הערוצים שלך בלחיצה אחת</p>
    </div>

    <!-- סטטיסטיקות מהירות -->
    <div class="stats-overview">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo array_sum(array_column($content_stats, 'total_pieces')); ?></h3>
                    <p>תכנים נוצרו השבוע</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <h3><?php echo count($brands); ?></h3>
                    <p>מותגים פעילים</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📱</div>
                <div class="stat-content">
                    <h3><?php echo count($available_channels); ?></h3>
                    <p>ערוצי תוכן</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚡</div>
                <div class="stat-content">
                    <h3><?php echo count($content_stats); ?></h3>
                    <p>סשני יצירה</p>
                </div>
            </div>
        </div>
    </div>

    <!-- בחירת מותג -->
    <div class="brand-selection-section">
        <div class="section-header">
            <h2>🎯 בחירת מותג</h2>
            <p>בחר את המותג שעבורו תרצה ליצור תוכן</p>
        </div>

        <div class="brand-selector">
            <select id="selected-brand" onchange="updateBrandInfo()">
                <option value="">בחר מותג...</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['id']; ?>" <?php selected($active_brand && $active_brand['id'] == $brand['id']); ?> data-industry="<?php echo esc_attr($brand['industry']); ?>"
                        data-keywords="<?php echo esc_attr(json_encode($brand['keywords'] ?? [])); ?>">
                        <?php echo esc_html($brand['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div id="brand-info" class="brand-info" style="display: none;">
                <div class="brand-details">
                    <div class="brand-meta">
                        <span class="brand-industry"></span>
                        <span class="brand-keywords"></span>
                    </div>
                    <div class="brand-description"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- בחירת ערוצים -->
    <div class="channels-selection-section">
        <div class="section-header">
            <h2>📱 בחירת ערוצי תוכן</h2>
            <p>בחר את הערוצים שעבורם תרצה ליצור תוכן</p>
        </div>

        <div class="channels-grid">
            <?php foreach ($available_channels as $channel_id => $channel): ?>
                <div class="channel-card">
                    <div class="channel-header">
                        <input type="checkbox" id="channel-<?php echo $channel_id; ?>" value="<?php echo $channel_id; ?>"
                            class="channel-checkbox">
                        <label for="channel-<?php echo $channel_id; ?>">
                            <span class="channel-icon"><?php echo $channel['icon']; ?></span>
                            <span class="channel-name"><?php echo $channel['name']; ?></span>
                        </label>
                    </div>

                    <div class="channel-details">
                        <div class="channel-types">
                            <strong>סוגי תוכן:</strong>
                            <div class="types-list">
                                <?php foreach (array_slice($channel['types'], 0, 3) as $type): ?>
                                    <span class="type-tag"><?php echo $type; ?></span>
                                <?php endforeach; ?>
                                <?php if (count($channel['types']) > 3): ?>
                                    <span class="type-more">+<?php echo count($channel['types']) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="channel-frequency">
                            <strong>תדירות:</strong> <?php echo $channel['frequency']; ?>
                        </div>

                        <div class="channel-options" style="display: none;">
                            <div class="option-group">
                                <label>כמות תכנים:</label>
                                <select class="content-count" data-channel="<?php echo $channel_id; ?>">
                                    <option value="3">3 תכנים</option>
                                    <option value="5" selected>5 תכנים</option>
                                    <option value="10">10 תכנים</option>
                                    <option value="15">15 תכנים</option>
                                </select>
                            </div>

                            <div class="option-group">
                                <label>
                                    <input type="checkbox" class="create-variations"
                                        data-channel="<?php echo $channel_id; ?>">
                                    צור וריאציות
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="channels-actions">
            <button type="button" class="ai-btn ai-btn-secondary" onclick="selectAllChannels()">
                ✅ בחר הכל
            </button>
            <button type="button" class="ai-btn ai-btn-secondary" onclick="clearChannelSelection()">
                ❌ נקה בחירה
            </button>
            <button type="button" class="ai-btn ai-btn-info" onclick="showChannelPresets()">
                📋 תבניות מוכנות
            </button>
        </div>
    </div>

    <!-- הגדרות מתקדמות -->
    <div class="advanced-settings-section">
        <div class="section-header">
            <h2>⚙️ הגדרות מתקדמות</h2>
            <button type="button" class="toggle-advanced" onclick="toggleAdvancedSettings()">
                <span class="toggle-text">הצג הגדרות מתקדמות</span>
                <span class="toggle-icon">▼</span>
            </button>
        </div>

        <div class="advanced-options" style="display: none;">
            <div class="options-grid">
                <div class="option-group">
                    <label for="content-tone">טון כתיבה כללי:</label>
                    <select id="content-tone">
                        <option value="">השתמש בטון המותג</option>
                        <option value="professional">מקצועי</option>
                        <option value="friendly">ידידותי</option>
                        <option value="casual">רגיל</option>
                        <option value="authoritative">סמכותי</option>
                        <option value="inspiring">מעורר השראה</option>
                    </select>
                </div>

                <div class="option-group">
                    <label for="target-audience">קהל יעד ספציפי:</label>
                    <input type="text" id="target-audience" placeholder="השאר ריק לשימוש בקהל המותג">
                </div>

                <div class="option-group">
                    <label for="additional-keywords">מילות מפתח נוספות:</label>
                    <input type="text" id="additional-keywords" placeholder="הפרד במקף">
                </div>

                <div class="option-group">
                    <label for="content-focus">מיקוד תוכן:</label>
                    <select id="content-focus">
                        <option value="general">כללי</option>
                        <option value="educational">חינוכי</option>
                        <option value="promotional">שיווקי</option>
                        <option value="entertaining">בידורי</option>
                        <option value="informational">אינפורמטיבי</option>
                    </select>
                </div>

                <div class="option-group">
                    <label>
                        <input type="checkbox" id="include-cta" checked>
                        כלול קריאה לפעולה
                    </label>
                </div>

                <div class="option-group">
                    <label>
                        <input type="checkbox" id="seo-optimize" checked>
                        אופטימיזציה ל-SEO
                    </label>
                </div>

                <div class="option-group">
                    <label>
                        <input type="checkbox" id="create-schedule">
                        צור לוח זמנים לפרסום
                    </label>
                </div>

                <div class="option-group">
                    <label>
                        <input type="checkbox" id="async-generation">
                        יצירה אסינכרונית (לכמויות גדולות)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- תצוגה מקדימה של התוכנית -->
    <div class="content-plan-preview" id="content-plan-preview" style="display: none;">
        <div class="section-header">
            <h2>👁️ תצוגה מקדימה של התוכנית</h2>
            <p>סקירה של התוכן שיווצר</p>
        </div>

        <div class="plan-summary">
            <div class="summary-stats">
                <div class="summary-item">
                    <span class="summary-label">סה"כ תכנים:</span>
                    <span class="summary-value" id="total-content-count">0</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">ערוצים נבחרים:</span>
                    <span class="summary-value" id="selected-channels-count">0</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">זמן יצירה משוער:</span>
                    <span class="summary-value" id="estimated-time">0 דקות</span>
                </div>
            </div>

            <div class="plan-details" id="plan-details">
                <!-- יוצג דינמית -->
            </div>
        </div>
    </div>

    <!-- כפתורי פעולה -->
    <div class="action-buttons">
        <button type="button" class="ai-btn ai-btn-primary ai-btn-lg" onclick="generateMultiChannelContent()"
            id="generate-btn">
            🚀 צור תוכן רב-ערוצי
        </button>
        <button type="button" class="ai-btn ai-btn-secondary" onclick="previewContentPlan()">
            👁️ תצוגה מקדימה
        </button>
        <button type="button" class="ai-btn ai-btn-info" onclick="saveContentPlan()">
            💾 שמור תוכנית
        </button>
        <button type="button" class="ai-btn ai-btn-outline" onclick="loadContentPlan()">
            📂 טען תוכנית
        </button>
    </div>

    <!-- תוצאות יצירת התוכן -->
    <div class="content-results" id="content-results" style="display: none;">
        <div class="section-header">
            <h2>📝 תוצאות יצירת התוכן</h2>
            <div class="results-actions">
                <button type="button" class="ai-btn ai-btn-sm ai-btn-success" onclick="downloadAllContent()">
                    💾 הורד הכל
                </button>
                <button type="button" class="ai-btn ai-btn-sm ai-btn-info" onclick="exportToWordPress()">
                    📄 יצא ל-WordPress
                </button>
                <button type="button" class="ai-btn ai-btn-sm ai-btn-secondary" onclick="shareResults()">
                    🔗 שתף תוצאות
                </button>
            </div>
        </div>

        <div class="results-container" id="results-container">
            <!-- תוצאות יוצגו כאן דינמית -->
        </div>
    </div>
</div>

<!-- Modal תבניות ערוצים -->
<div id="channel-presets-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h2>📋 תבניות ערוצים מוכנות</h2>
            <span class="ai-modal-close" onclick="closeChannelPresetsModal()">&times;</span>
        </div>
        <div class="ai-modal-body">
            <div class="presets-grid">
                <div class="preset-card" onclick="applyChannelPreset('startup')">
                    <div class="preset-icon">🚀</div>
                    <h3>סטארט-אפ</h3>
                    <p>בלוג + רשתות חברתיות + אימייל</p>
                    <div class="preset-channels">
                        <span class="channel-tag">📝 בלוג</span>
                        <span class="channel-tag">📱 רשתות</span>
                        <span class="channel-tag">📧 אימייל</span>
                    </div>
                </div>

                <div class="preset-card" onclick="applyChannelPreset('ecommerce')">
                    <div class="preset-icon">🛍️</div>
                    <h3>מסחר אלקטרוני</h3>
                    <p>מוצרים + פרסומות + רשתות</p>
                    <div class="preset-channels">
                        <span class="channel-tag">🛍️ מוצרים</span>
                        <span class="channel-tag">📢 פרסומות</span>
                        <span class="channel-tag">📱 רשתות</span>
                    </div>
                </div>

                <div class="preset-card" onclick="applyChannelPreset('corporate')">
                    <div class="preset-icon">🏢</div>
                    <h3>תאגידי</h3>
                    <p>בלוג + הודעות לעיתונות + לינקדאין</p>
                    <div class="preset-channels">
                        <span class="channel-tag">📝 בלוג</span>
                        <span class="channel-tag">📰 עיתונות</span>
                        <span class="channel-tag">💼 לינקדאין</span>
                    </div>
                </div>

                <div class="preset-card" onclick="applyChannelPreset('content_creator')">
                    <div class="preset-icon">🎨</div>
                    <h3>יוצר תוכן</h3>
                    <p>רשתות + וידאו + בלוג</p>
                    <div class="preset-channels">
                        <span class="channel-tag">📱 רשתות</span>
                        <span class="channel-tag">🎬 וידאו</span>
                        <span class="channel-tag">📝 בלוג</span>
                    </div>
                </div>

                <div class="preset-card" onclick="applyChannelPreset('all_channels')">
                    <div class="preset-icon">🌟</div>
                    <h3>כל הערוצים</h3>
                    <p>מקסימום חשיפה</p>
                    <div class="preset-channels">
                        <span class="channel-tag">🌐 הכל</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="ai-btn ai-btn-secondary" onclick="closeChannelPresetsModal()">ביטול</button>
        </div>
    </div>
</div>

<style>
    .ai-multi-channel-content {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .content-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
    }

    .content-header h1 {
        margin: 0 0 15px 0;
        font-size: 2.5em;
        font-weight: 700;
    }

    .stats-overview {
        margin-bottom: 40px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 2.5em;
        opacity: 0.8;
    }

    .stat-content h3 {
        margin: 0;
        font-size: 2.2em;
        font-weight: 700;
        color: #333;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9em;
    }

    .brand-selection-section,
    .channels-selection-section,
    .advanced-settings-section,
    .content-plan-preview {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .section-header h2 {
        margin: 0;
        color: #333;
        font-size: 1.5em;
    }

    .section-header p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9em;
    }

    .brand-selector select {
        width: 100%;
        max-width: 400px;
        padding: 15px 20px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 16px;
        background: white;
    }

    .brand-info {
        margin-top: 20px;
        padding: 20px;
        background: #f8f9ff;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .brand-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
    }

    .brand-industry,
    .brand-keywords {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .brand-industry {
        background: #667eea;
        color: white;
    }

    .brand-keywords {
        background: #e9ecef;
        color: #495057;
    }

    .channels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .channel-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        background: white;
    }

    .channel-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    }

    .channel-card.selected {
        border-color: #667eea;
        background: #f8f9ff;
    }

    .channel-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .channel-checkbox {
        width: 20px;
        height: 20px;
        accent-color: #667eea;
    }

    .channel-header label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 600;
        color: #333;
    }

    .channel-icon {
        font-size: 1.5em;
    }

    .channel-details {
        font-size: 0.9em;
        color: #666;
    }

    .channel-types {
        margin-bottom: 10px;
    }

    .types-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 5px;
    }

    .type-tag {
        background: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8em;
    }

    .type-more {
        background: #6c757d;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8em;
    }

    .channel-options {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .option-group {
        margin-bottom: 15px;
    }

    .option-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .option-group select,
    .option-group input[type="text"] {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }

    .channels-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .toggle-advanced {
        background: none;
        border: none;
        color: #667eea;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }

    .toggle-advanced.expanded .toggle-icon {
        transform: rotate(180deg);
    }

    .advanced-options {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .plan-summary {
        background: #f8f9ff;
        padding: 25px;
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }

    .summary-stats {
        display: flex;
        justify-content: space-around;
        margin-bottom: 20px;
        text-align: center;
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .summary-label {
        font-size: 0.9em;
        color: #666;
    }

    .summary-value {
        font-size: 1.5em;
        font-weight: 700;
        color: #667eea;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 40px 0;
        flex-wrap: wrap;
    }

    .content-results {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .results-actions {
        display: flex;
        gap: 10px;
    }

    .presets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .preset-card {
        padding: 20px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .preset-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
        transform: translateY(-3px);
    }

    .preset-icon {
        font-size: 2.5em;
        margin-bottom: 15px;
    }

    .preset-card h3 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .preset-card p {
        margin: 0 0 15px 0;
        color: #666;
        font-size: 0.9em;
    }

    .preset-channels {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
    }

    .channel-tag {
        background: #667eea;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7em;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .channels-grid {
            grid-template-columns: 1fr;
        }

        .options-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
            align-items: center;
        }

        .summary-stats {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>

<script>
    // JavaScript functions will be added in the next file
</script>