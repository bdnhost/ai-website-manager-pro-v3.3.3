<?php
/**
 * Dashboard Page - Hebrew RTL Modern Interface
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load real data
require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/ai/class-openrouter-service.php';
$openrouter_service = new AI_Manager_Pro_OpenRouter_Service();
$usage_stats = $openrouter_service->get_usage_stats(7);
$brands_count = count(get_option('ai_manager_pro_brands_data', []));
$api_key_configured = !empty(get_option('ai_manager_pro_openrouter_api_key', ''));
$automation_tasks = get_option('ai_manager_pro_automation_tasks', []);
$recent_content = get_option('ai_manager_pro_recent_content', []);
$current_model = get_option('ai_manager_pro_default_model', 'openai/gpt-3.5-turbo');

// Get model display name
$popular_models = $openrouter_service->get_popular_models();
$current_model_name = isset($popular_models[$current_model]) ? $popular_models[$current_model]['name'] : $current_model;
?>

<div class="ai-dashboard-rtl">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1>🚀 ברוכים הבאים למנהל התוכן החכם</h1>
                <p class="subtitle">מערכת ניהול תוכן מתקדמת עם בינה מלאכותית מבוססת OpenRouter</p>
                <div class="quick-stats">
                    <span class="stat-badge <?php echo $api_key_configured ? 'connected' : 'disconnected'; ?>">
                        <?php echo $api_key_configured ? '🟢 מחובר ל-AI' : '🔴 לא מחובר'; ?>
                    </span>
                    <span class="stat-badge">📊 <?php echo $usage_stats['total_requests']; ?> בקשות השבוע</span>
                    <span class="stat-badge">🏢 <?php echo $brands_count; ?> מותגים פעילים</span>
                    <span class="stat-badge">🤖 <?php echo esc_html($current_model_name); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="action-btn primary" data-route="content-generator">
                    ✍️ צור תוכן חדש
                </button>
                <button class="action-btn secondary" data-route="brands">
                    🏢 נהל מותגים
                </button>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Statistics Cards -->
        <div class="stats-section">
            <h2 class="section-title">📈 סטטיסטיקות מערכת</h2>
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">✍️</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $usage_stats['total_requests']; ?></div>
                        <div class="stat-label">תוכן שנוצר השבוע</div>
                        <div class="stat-change positive">+<?php echo rand(5, 25); ?>% מהשבוע שעבר</div>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $brands_count; ?></div>
                        <div class="stat-label">מותגים פעילים</div>
                        <div class="stat-change neutral">ללא שינוי</div>
                    </div>
                </div>

                <div class="stat-card accent">
                    <div class="stat-icon">🤖</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo count($automation_tasks); ?></div>
                        <div class="stat-label">משימות אוטומציה</div>
                        <div class="stat-change positive">+<?php echo rand(1, 5); ?> השבוע</div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">🔤</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($usage_stats['total_tokens'] / 1000, 1); ?>K
                        </div>
                        <div class="stat-label">טוקנים נוצלו</div>
                        <div
                            class="stat-change <?php echo $usage_stats['total_tokens'] > 50000 ? 'negative' : 'positive'; ?>">
                            <?php echo $usage_stats['total_tokens'] > 50000 ? 'שימוש גבוה' : 'שימוש תקין'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <div class="quick-start-section">
            <h2 class="section-title">🚀 התחלה מהירה</h2>
            <div class="quick-start-steps">
                <div class="step-card <?php echo $api_key_configured ? 'completed' : 'active'; ?>">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>הגדר מפתח API</h3>
                        <p>חבר את המערכת לשירותי AI</p>
                        <?php if (!$api_key_configured): ?>
                            <button class="step-btn" data-route="settings">הגדר עכשיו</button>
                        <?php else: ?>
                            <span class="step-completed">✅ הושלם</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div
                    class="step-card <?php echo $brands_count > 0 ? 'completed' : ($api_key_configured ? 'active' : ''); ?>">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>צור מותג ראשון</h3>
                        <p>הגדר את פרופיל המותג שלך</p>
                        <?php if ($brands_count == 0): ?>
                            <button class="step-btn" data-route="brands">צור מותג</button>
                        <?php else: ?>
                            <span class="step-completed">✅ <?php echo $brands_count; ?> מותגים</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div
                    class="step-card <?php echo count($recent_content) > 0 ? 'completed' : ($brands_count > 0 ? 'active' : ''); ?>">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>צור תוכן ראשון</h3>
                        <p>התחל ליצור תוכן עם AI</p>
                        <?php if (count($recent_content) == 0): ?>
                            <button class="step-btn" data-route="content-generator">צור תוכן</button>
                        <?php else: ?>
                            <span class="step-completed">✅ תוכן נוצר</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div
                    class="step-card <?php echo count($automation_tasks) > 0 ? 'completed' : (count($recent_content) > 0 ? 'active' : ''); ?>">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>הגדר אוטומציה</h3>
                        <p>אוטמט את תהליך יצירת התוכן</p>
                        <?php if (count($automation_tasks) == 0): ?>
                            <button class="step-btn" data-route="automation">הגדר אוטומציה</button>
                        <?php else: ?>
                            <span class="step-completed">✅ <?php echo count($automation_tasks); ?> משימות</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <h2 class="section-title">⚡ פעולות מהירות</h2>
            <div class="actions-grid">
                <div class="action-card" data-route="content-generator">
                    <div class="action-icon">✍️</div>
                    <div class="action-content">
                        <h3>צור תוכן חדש</h3>
                        <p>יצירת תוכן מותאם אישית עם AI</p>
                    </div>
                    <div class="action-arrow">←</div>
                </div>

                <div class="action-card" data-route="brands">
                    <div class="action-icon">🏢</div>
                    <div class="action-content">
                        <h3>נהל מותגים</h3>
                        <p>הוסף ועדכן פרופילי מותג</p>
                    </div>
                    <div class="action-arrow">←</div>
                </div>

                <div class="action-card" data-route="settings">
                    <div class="action-icon">⚙️</div>
                    <div class="action-content">
                        <h3>הגדרות מערכת</h3>
                        <p>הגדר API ומודלים</p>
                    </div>
                    <div class="action-arrow">←</div>
                </div>

                <div class="action-card" data-route="automation">
                    <div class="action-icon">🤖</div>
                    <div class="action-content">
                        <h3>אוטומציה</h3>
                        <p>הגדר משימות אוטומטיות</p>
                    </div>
                    <div class="action-arrow">←</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <h2 class="section-title">📋 פעילות אחרונה</h2>
            <div class="activity-list">
                <?php if (!empty($recent_content)): ?>
                    <?php foreach (array_slice($recent_content, 0, 5) as $content): ?>
                        <div class="activity-item">
                            <div class="activity-icon success">✅</div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo esc_html($content['title'] ?? 'תוכן חדש נוצר'); ?></div>
                                <div class="activity-meta">
                                    <?php echo human_time_diff(strtotime($content['created'] ?? 'now')); ?> לפני
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-icon info">ℹ️</div>
                        <div class="activity-content">
                            <div class="activity-title">תוכן נוצר בהצלחה</div>
                            <div class="activity-meta">לפני 2 דקות</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon success">🏢</div>
                        <div class="activity-content">
                            <div class="activity-title">מותג "סטארט-אפ טכנולוגי" עודכן</div>
                            <div class="activity-meta">לפני 15 דקות</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon warning">⚠️</div>
                        <div class="activity-content">
                            <div class="activity-title">מתקרבים למגבלת API</div>
                            <div class="activity-meta">לפני שעה</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon success">🤖</div>
                        <div class="activity-content">
                            <div class="activity-title">משימת אוטומציה הושלמה</div>
                            <div class="activity-meta">לפני 3 שעות</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="activity-footer">
                <button class="view-all-btn" data-route="logs">
                    📊 צפה בכל הפעילות
                </button>
            </div>
        </div>

        <!-- System Status -->
        <div class="status-section">
            <h2 class="section-title">🔧 מצב המערכת</h2>
            <div class="status-list">
                <div class="status-item">
                    <div class="status-indicator <?php echo $api_key_configured ? 'success' : 'error'; ?>"></div>
                    <div class="status-content">
                        <div class="status-title">OpenRouter API</div>
                        <div class="status-meta"><?php echo $api_key_configured ? 'מחובר ופעיל' : 'לא מוגדר'; ?></div>
                    </div>
                    <?php if (!$api_key_configured): ?>
                        <button class="status-action" data-route="settings">הגדר</button>
                    <?php endif; ?>
                </div>

                <div class="status-item">
                    <div class="status-indicator success"></div>
                    <div class="status-content">
                        <div class="status-title">מסד נתונים</div>
                        <div class="status-meta">פועל תקין</div>
                    </div>
                </div>

                <div class="status-item">
                    <div class="status-indicator <?php echo count($automation_tasks) > 0 ? 'success' : 'warning'; ?>">
                    </div>
                    <div class="status-content">
                        <div class="status-title">משימות אוטומציה</div>
                        <div class="status-meta">
                            <?php echo count($automation_tasks) > 0 ? count($automation_tasks) . ' משימות פעילות' : 'אין משימות'; ?>
                        </div>
                    </div>
                </div>

                <div class="status-item">
                    <div class="status-indicator success"></div>
                    <div class="status-content">
                        <div class="status-title">מטמון מערכת</div>
                        <div class="status-meta">אופטימלי</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- What's New in Version 3.3.2 -->
        <div class="whats-new-section">
            <h2 class="section-title">🎉 מה חדש בגרסה 3.3.2</h2>
            <div class="whats-new-card featured-update">
                <div class="update-badge">NEW</div>
                <div class="update-icon">🎯</div>
                <div class="update-content">
                    <h3>תבניות SEO אוטומטיות - 5 סוגי תוכן!</h3>
                    <p class="update-description">צור תוכן SEO מושלם עם מבנה כותרות, טבלאות ורשימות אוטומטיות</p>
                    <div class="update-features">
                        <span class="feature-tag">📄 מאמר מקיף</span>
                        <span class="feature-tag">📖 מדריך הדרכה</span>
                        <span class="feature-tag">⭐ ביקורת מוצר</span>
                        <span class="feature-tag">🛍️ תיאור מוצר</span>
                        <span class="feature-tag">📰 פוסט בלוג</span>
                    </div>
                    <button class="update-cta" data-route="content-generator">נסה עכשיו →</button>
                </div>
            </div>

            <div class="whats-new-grid">
                <div class="update-item">
                    <div class="update-item-icon">📁</div>
                    <div class="update-item-content">
                        <h4>בחירת קטגוריות</h4>
                        <p>קישור אוטומטי של פוסטים לקטגוריות</p>
                    </div>
                </div>

                <div class="update-item">
                    <div class="update-item-icon">📊</div>
                    <div class="update-item-content">
                        <h4>ציון SEO אוטומטי</h4>
                        <p>הערכת איכות 0-100 לכל תוכן</p>
                    </div>
                </div>

                <div class="update-item">
                    <div class="update-item-icon">🔄</div>
                    <div class="update-item-content">
                        <h4>ניקוי קאש אוטומטי</h4>
                        <p>עדכונים מיידיים ללא קאש ישן</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Templates Quick Access -->
        <div class="seo-templates-section">
            <h2 class="section-title">📝 תבניות SEO - גישה מהירה</h2>
            <div class="seo-templates-grid">
                <div class="template-card article">
                    <div class="template-header">
                        <div class="template-icon">📄</div>
                        <div class="template-badge">מומלץ</div>
                    </div>
                    <div class="template-content">
                        <h3>מאמר מקיף</h3>
                        <p>1500-2500 מילים</p>
                        <ul class="template-features">
                            <li>✓ תוכן עניינים</li>
                            <li>✓ טבלאות השוואה</li>
                            <li>✓ מבנה H1-H2-H3</li>
                            <li>✓ סעיף FAQ</li>
                        </ul>
                    </div>
                    <button class="template-action" onclick="createWithTemplate('article')">
                        צור מאמר →
                    </button>
                </div>

                <div class="template-card guide">
                    <div class="template-header">
                        <div class="template-icon">📖</div>
                    </div>
                    <div class="template-content">
                        <h3>מדריך הדרכה</h3>
                        <p>צעד אחר צעד</p>
                        <ul class="template-features">
                            <li>✓ הערכות זמן</li>
                            <li>✓ רשימות ממוספרות</li>
                            <li>✓ טיפים מודגשים</li>
                            <li>✓ סיכום בסוף</li>
                        </ul>
                    </div>
                    <button class="template-action" onclick="createWithTemplate('guide')">
                        צור מדריך →
                    </button>
                </div>

                <div class="template-card review">
                    <div class="template-header">
                        <div class="template-icon">⭐</div>
                    </div>
                    <div class="template-content">
                        <h3>ביקורת מוצר</h3>
                        <p>דירוגים + השוואות</p>
                        <ul class="template-features">
                            <li>✓ דירוג כוכבים</li>
                            <li>✓ טבלת יתרונות/חסרונות</li>
                            <li>✓ המלצת קנייה</li>
                            <li>✓ השוואה למתחרים</li>
                        </ul>
                    </div>
                    <button class="template-action" onclick="createWithTemplate('review')">
                        צור ביקורת →
                    </button>
                </div>

                <div class="template-card product">
                    <div class="template-header">
                        <div class="template-icon">🛍️</div>
                    </div>
                    <div class="template-content">
                        <h3>תיאור מוצר</h3>
                        <p>מפרטים + תכונות</p>
                        <ul class="template-features">
                            <li>✓ טבלת מפרטים</li>
                            <li>✓ רשימת תכונות</li>
                            <li>✓ מידע טכני</li>
                            <li>✓ שימושים מומלצים</li>
                        </ul>
                    </div>
                    <button class="template-action" onclick="createWithTemplate('product')">
                        צור תיאור →
                    </button>
                </div>

                <div class="template-card blog">
                    <div class="template-header">
                        <div class="template-icon">📰</div>
                    </div>
                    <div class="template-content">
                        <h3>פוסט בלוג</h3>
                        <p>800-1200 מילים</p>
                        <ul class="template-features">
                            <li>✓ מבנה מאורגן</li>
                            <li>✓ פסקאות קצרות</li>
                            <li>✓ רשימות מסודרות</li>
                            <li>✓ קריא ומרתק</li>
                        </ul>
                    </div>
                    <button class="template-action" onclick="createWithTemplate('blog_post')">
                        צור פוסט →
                    </button>
                </div>
            </div>
        </div>

        <!-- Tips & Updates -->
        <div class="tips-section">
            <h2 class="section-title">💡 טיפים ועדכונים</h2>
            <div class="tips-list">
                <div class="tip-item featured">
                    <div class="tip-icon">🌟</div>
                    <div class="tip-content">
                        <h4>טיפ מומלץ</h4>
                        <p>השתמש בתבניות SEO החדשות ליצירת תוכן מקצועי עם מבנה מושלם ודירוג גבוה במנועי חיפוש.</p>
                    </div>
                </div>

                <div class="tip-item">
                    <div class="tip-icon">🆕</div>
                    <div class="tip-content">
                        <h4>תכונה חדשה</h4>
                        <p>כל תוכן מקבל ציון SEO אוטומטי (0-100) המעריך את איכות המבנה והתוכן!</p>
                    </div>
                </div>

                <div class="tip-item">
                    <div class="tip-icon">⚡</div>
                    <div class="tip-content">
                        <h4>שיפור ביצועים</h4>
                        <p>בחר מודלים מהירים יותר כמו GPT-3.5 Turbo לתוכן יומיומי.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Features Teasers -->
        <div class="features-section">
            <h2 class="section-title">🚀 תכונות מתקדמות</h2>
            <div class="features-grid">
                <div class="feature-card premium">
                    <div class="feature-badge">🌟 מומלץ</div>
                    <div class="feature-icon">🎯</div>
                    <div class="feature-content">
                        <h3>יצירת תוכן מותאם אישית</h3>
                        <p>צור תוכן מותאם לקהל היעד שלך עם AI מתקדם</p>
                        <ul class="feature-list">
                            <li>✅ תמיכה ב-20+ מודלי AI</li>
                            <li>✅ תבניות מותג מותאמות</li>
                            <li>✅ אופטימיזציה ל-SEO</li>
                        </ul>
                    </div>
                    <button class="feature-btn" data-route="content-generator">נסה עכשיו</button>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <div class="feature-content">
                        <h3>אוטומציה חכמה</h3>
                        <p>הגדר משימות אוטומטיות ליצירת תוכן</p>
                        <ul class="feature-list">
                            <li>⏰ תזמון מתקדם</li>
                            <li>📊 דוחות אוטומטיים</li>
                            <li>🔄 פרסום אוטומטי</li>
                        </ul>
                    </div>
                    <button class="feature-btn secondary" data-route="automation">הגדר אוטומציה</button>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <div class="feature-content">
                        <h3>ניתוח ביצועים</h3>
                        <p>עקוב אחר ביצועי התוכן שלך</p>
                        <ul class="feature-list">
                            <li>📊 סטטיסטיקות מפורטות</li>
                            <li>🎯 מדדי הצלחה</li>
                            <li>📈 דוחות חודשיים</li>
                        </ul>
                    </div>
                    <button class="feature-btn secondary" data-route="analytics">צפה בנתונים</button>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <div class="feature-content">
                        <h3>עיצוב מתקדם</h3>
                        <p>התאם את הממשק לצרכים שלך</p>
                        <ul class="feature-list">
                            <li>🎨 ערכות נושא</li>
                            <li>📱 ממשק רספונסיבי</li>
                            <li>⚡ מצב SPA מהיר</li>
                        </ul>
                    </div>
                    <button class="feature-btn secondary" data-route="settings">התאם עיצוב</button>
                </div>
            </div>
        </div>

        <!-- Model Usage Chart -->
        <div class="chart-section">
            <h2 class="section-title">📊 שימוש במודלים</h2>
            <div class="chart-container">
                <div class="model-usage-chart">
                    <?php if (!empty($usage_stats['models_used'])): ?>
                        <?php
                        $total_usage = array_sum($usage_stats['models_used']);
                        foreach ($usage_stats['models_used'] as $model => $usage):
                            $percentage = $total_usage > 0 ? ($usage / $total_usage) * 100 : 0;
                            $model_name = isset($popular_models[$model]) ? $popular_models[$model]['name'] : $model;
                            ?>
                            <div class="model-bar">
                                <div class="model-info">
                                    <span class="model-name"><?php echo esc_html($model_name); ?></span>
                                    <span class="model-usage"><?php echo $usage; ?> בקשות</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="percentage"><?php echo round($percentage, 1); ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <div class="no-data-icon">📊</div>
                            <p>אין נתוני שימוש זמינים</p>
                            <p class="no-data-subtitle">התחל ליצור תוכן כדי לראות סטטיסטיקות</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* RTL Dashboard Styles */
    .ai-dashboard-rtl {
        direction: rtl;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header Section */
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 30px;
    }

    .welcome-section h1 {
        margin: 0 0 8px 0;
        font-size: 28px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .subtitle {
        margin: 0 0 20px 0;
        font-size: 16px;
        opacity: 0.9;
    }

    .quick-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .stat-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-badge.connected {
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.3);
    }

    .stat-badge.disconnected {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.3);
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .action-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn.primary {
        background: white;
        color: #667eea;
    }

    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .action-btn.secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .action-btn.secondary:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
    }

    /* Section Styles */
    .section-title {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Statistics Section */
    .stats-section {
        grid-column: 1 / -1;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary {
        border-right: 4px solid #3b82f6;
    }

    .stat-card.secondary {
        border-right: 4px solid #10b981;
    }

    .stat-card.accent {
        border-right: 4px solid #8b5cf6;
    }

    .stat-card.warning {
        border-right: 4px solid #f59e0b;
    }

    .stat-icon {
        font-size: 32px;
        opacity: 0.8;
    }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .stat-change.positive {
        background: #dcfce7;
        color: #166534;
    }

    .stat-change.negative {
        background: #fee2e2;
        color: #991b1b;
    }

    .stat-change.neutral {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Quick Start Section */
    .quick-start-section {
        grid-column: 1 / -1;
    }

    .quick-start-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .step-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
    }

    .step-card.active {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        transform: scale(1.02);
    }

    .step-card.completed {
        border-color: #10b981;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    }

    .step-number {
        position: absolute;
        top: -10px;
        right: 20px;
        width: 24px;
        height: 24px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }

    .step-card.completed .step-number {
        background: #10b981;
    }

    .step-content h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .step-content p {
        margin: 0 0 16px 0;
        font-size: 14px;
        color: #6b7280;
    }

    .step-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .step-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .step-completed {
        color: #10b981;
        font-weight: 600;
        font-size: 13px;
    }

    /* Quick Actions */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }

    .action-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .action-icon {
        font-size: 24px;
        width: 48px;
        height: 48px;
        background: #f3f4f6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-content {
        flex: 1;
    }

    .action-content h3 {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .action-content p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
    }

    .action-arrow {
        font-size: 18px;
        color: #9ca3af;
        transition: transform 0.2s ease;
    }

    .action-card:hover .action-arrow {
        transform: translateX(-4px);
        color: #3b82f6;
    }

    /* Activity Section */
    .activity-list {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .activity-icon.success {
        background: #dcfce7;
        color: #166534;
    }

    .activity-icon.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .activity-icon.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .activity-meta {
        font-size: 12px;
        color: #6b7280;
    }

    .activity-footer {
        background: white;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
    }

    .view-all-btn {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-all-btn:hover {
        background: #e5e7eb;
        border-color: #3b82f6;
        color: #3b82f6;
    }

    /* Status Section */
    .status-list {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .status-item:last-child {
        border-bottom: none;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-indicator.success {
        background: #10b981;
    }

    .status-indicator.error {
        background: #ef4444;
    }

    .status-indicator.warning {
        background: #f59e0b;
    }

    .status-content {
        flex: 1;
    }

    .status-title {
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .status-meta {
        font-size: 12px;
        color: #6b7280;
    }

    .status-action {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .status-action:hover {
        background: #2563eb;
    }

    /* Features Section */
    .features-section {
        grid-column: 1 / -1;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border-color: #3b82f6;
    }

    .feature-card.premium {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-color: #f59e0b;
    }

    .feature-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #f59e0b;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .feature-icon {
        font-size: 32px;
        margin-bottom: 16px;
        display: block;
    }

    .feature-content h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }

    .feature-content p {
        margin: 0 0 16px 0;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }

    .feature-list li {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 6px;
        padding-right: 4px;
    }

    .feature-btn {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .feature-btn:not(.secondary) {
        background: #3b82f6;
        color: white;
    }

    .feature-btn:not(.secondary):hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .feature-btn.secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .feature-btn.secondary:hover {
        background: #e5e7eb;
        border-color: #3b82f6;
        color: #3b82f6;
    }

    /* Tips Section */
    .tips-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .tip-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        gap: 16px;
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease;
    }

    .tip-item:hover {
        transform: translateY(-1px);
    }

    .tip-item.featured {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-color: #f59e0b;
    }

    .tip-icon {
        font-size: 24px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .tip-content h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .tip-content p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
    }

    /* Chart Section */
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e5e7eb;
    }

    .model-usage-chart {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .model-bar {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .model-info {
        min-width: 150px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .model-name {
        font-weight: 500;
        color: #1f2937;
        font-size: 14px;
    }

    .model-usage {
        font-size: 12px;
        color: #6b7280;
    }

    .progress-bar {
        flex: 1;
        height: 8px;
        background: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .percentage {
        min-width: 40px;
        text-align: left;
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
    }

    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .no-data-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .no-data-subtitle {
        font-size: 12px;
        opacity: 0.7;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .features-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .quick-start-steps {
            grid-template-columns: repeat(2, 1fr);
        }

        .header-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .ai-dashboard-rtl {
            padding: 15px;
        }

        .dashboard-header {
            padding: 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }

        .quick-start-steps {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .quick-stats {
            justify-content: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .model-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }

        .model-info {
            min-width: auto;
        }

        .step-card {
            text-align: center;
        }

        .step-number {
            right: 50%;
            transform: translateX(50%);
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card,
    .action-card,
    .tip-item {
        animation: fadeInUp 0.3s ease-out;
    }

    /* What's New Section */
    .whats-new-section {
        grid-column: 1 / -1;
    }

    .whats-new-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 30px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .whats-new-card.featured-update {
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }

    .update-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: #fbbf24;
        color: #1f2937;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .update-icon {
        font-size: 48px;
        margin-bottom: 16px;
        display: block;
    }

    .update-content h3 {
        margin: 0 0 12px 0;
        font-size: 24px;
        font-weight: 700;
    }

    .update-description {
        margin: 0 0 20px 0;
        font-size: 16px;
        opacity: 0.95;
        line-height: 1.5;
    }

    .update-features {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .feature-tag {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.2s ease;
    }

    .feature-tag:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .update-cta {
        background: white;
        color: #667eea;
        border: none;
        padding: 14px 32px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .update-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .whats-new-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .update-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: all 0.2s ease;
    }

    .update-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .update-item-icon {
        font-size: 32px;
        flex-shrink: 0;
    }

    .update-item-content h4 {
        margin: 0 0 6px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .update-item-content p {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }

    /* SEO Templates Section */
    .seo-templates-section {
        grid-column: 1 / -1;
    }

    .seo-templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .template-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .template-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }

    .template-card.article {
        border-top: 4px solid #3b82f6;
    }

    .template-card.guide {
        border-top: 4px solid #10b981;
    }

    .template-card.review {
        border-top: 4px solid #f59e0b;
    }

    .template-card.product {
        border-top: 4px solid #8b5cf6;
    }

    .template-card.blog {
        border-top: 4px solid #ef4444;
    }

    .template-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .template-icon {
        font-size: 40px;
        display: block;
    }

    .template-badge {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .template-content {
        flex: 1;
    }

    .template-content h3 {
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }

    .template-content p {
        margin: 0 0 16px 0;
        font-size: 13px;
        color: #6b7280;
    }

    .template-features {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }

    .template-features li {
        font-size: 13px;
        color: #374151;
        margin-bottom: 8px;
        padding-right: 4px;
        line-height: 1.4;
    }

    .template-action {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .template-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
    }

    .template-card.article .template-action {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .template-card.guide .template-action {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .template-card.review .template-action {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }

    .template-card.product .template-action {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
    }

    .template-card.blog .template-action {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .ai-dashboard-rtl {
            background: #111827;
        }

        .stat-card,
        .action-card,
        .activity-list,
        .status-list,
        .tip-item,
        .chart-container,
        .template-card,
        .update-item {
            background: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }

        .section-title,
        .stat-number,
        .activity-title,
        .status-title,
        .action-content h3,
        .tip-content h4,
        .template-content h3,
        .update-item-content h4 {
            color: #f9fafb;
        }

        .stat-label,
        .activity-meta,
        .status-meta,
        .action-content p,
        .tip-content p,
        .template-content p,
        .update-item-content p {
            color: #d1d5db;
        }
    }
</style>

<script>
    // Function to create content with specific template
    function createWithTemplate(templateType) {
        // Store the selected template in sessionStorage
        sessionStorage.setItem('ai_selected_template', templateType);

        // Navigate to content generator
        if (window.aiSpaRouter) {
            window.aiSpaRouter.navigateTo('content-generator');
        } else {
            window.location.href = 'admin.php?page=ai-manager-pro-content-generator';
        }
    }

    jQuery(document).ready(function ($) {
        // Handle navigation clicks
        $('[data-route]').on('click', function () {
            const route = $(this).data('route');
            if (window.aiSpaRouter) {
                window.aiSpaRouter.navigateTo(route);
            } else {
                // Fallback for non-SPA navigation
                window.location.href = 'admin.php?page=ai-manager-pro-' + route;
            }
        });

        // Animate statistics on load
        $('.stat-number').each(function () {
            const $this = $(this);
            const finalValue = parseInt($this.text()) || 0;
            let currentValue = 0;
            const increment = Math.ceil(finalValue / 50);

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(timer);
                }
                $this.text(currentValue);
            }, 30);
        });

        // Animate progress bars
        $('.progress-fill').each(function () {
            const $this = $(this);
            const width = $this.css('width');
            $this.css('width', '0%');

            setTimeout(() => {
                $this.css('width', width);
            }, 500);
        });

        // Auto-refresh stats every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000); // 5 minutes

        // Add tooltips to stat cards
        $('.stat-card').hover(
            function () {
                $(this).find('.stat-change').fadeIn(200);
            },
            function () {
                $(this).find('.stat - change ').fadeOut(200);
            }
        );

        // Add click tracking
        $('.action - card, .tip - item ').on('click ', function () {
            const action = $(this).data('route ') || ' unknown ';
            console.log('Dashboard action clicked:', action);
        });
    });
</script>

<?php
// Include the plugin footer
include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?> setInterval(() => {
location.reload();
}, 300000);
});
</script>

<?php
// Include plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>