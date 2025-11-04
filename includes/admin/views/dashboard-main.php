<?php
/**
 * Main Dashboard View
 * דשבורד ראשי מקצועי עם סקירה כללית
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current statistics
$total_posts = wp_count_posts()->publish;
$brands_count = count(get_option('ai_manager_pro_brands_data', []));
$active_brand = get_option('ai_manager_pro_active_brand', '');
$default_provider = get_option('ai_manager_pro_default_provider', 'openai');

// Get usage stats (last 7 days)
$usage_stats = get_option('ai_manager_pro_usage_stats', []);
$recent_usage = 0;
$start_date = date('Y-m-d', strtotime('-7 days'));
foreach ($usage_stats as $date => $daily_data) {
    if ($date >= $start_date) {
        foreach ($daily_data as $model_data) {
            $recent_usage += $model_data['requests'] ?? 0;
        }
    }
}

// Check API keys status
$api_keys_status = [
    'openai' => !empty(get_option('ai_manager_pro_openai_api_key')),
    'anthropic' => !empty(get_option('ai_manager_pro_anthropic_api_key')),
    'openrouter' => !empty(get_option('ai_manager_pro_openrouter_api_key')),
    'deepseek' => !empty(get_option('ai_manager_pro_deepseek_key'))
];
$configured_providers = array_sum($api_keys_status);
?>

<div class="ai-dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-text">
                <h1>🤖 AI Website Manager Pro</h1>
                <p class="header-subtitle">מערכת ניהול תוכן מתקדמת עם בינה מלאכותית</p>
                <div class="version-badge">גרסה <?php echo AI_MANAGER_PRO_VERSION; ?></div>
            </div>
            <div class="header-logo">
                <div class="ai-logo">
                    <span class="logo-icon">🧠</span>
                    <span class="logo-text">AI Pro</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3><?php echo number_format($total_posts); ?></h3>
                <p>פוסטים פורסמו</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-content">
                <h3><?php echo $brands_count; ?></h3>
                <p>מותגים מוגדרים</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔑</div>
            <div class="stat-content">
                <h3><?php echo $configured_providers; ?>/4</h3>
                <p>ספקי AI מוגדרים</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-content">
                <h3><?php echo number_format($recent_usage); ?></h3>
                <p>בקשות השבוע</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Quick Actions -->
        <div class="dashboard-card quick-actions">
            <h2>🚀 פעולות מהירות</h2>
            <div class="actions-grid">
                <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-content-generator'); ?>"
                    class="action-button primary">
                    <span class="action-icon">✍️</span>
                    <span class="action-text">צור תוכן חדש</span>
                </a>

                <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-brands'); ?>"
                    class="action-button secondary">
                    <span class="action-icon">🏢</span>
                    <span class="action-text">נהל מותגים</span>
                </a>

                <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-api-keys'); ?>"
                    class="action-button secondary">
                    <span class="action-icon">🔑</span>
                    <span class="action-text">הגדר API</span>
                </a>

                <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-automation'); ?>"
                    class="action-button secondary">
                    <span class="action-icon">🔄</span>
                    <span class="action-text">אוטומציה</span>
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="dashboard-card system-status">
            <h2>🔧 מצב המערכת</h2>
            <div class="status-list">
                <div class="status-item <?php echo $configured_providers > 0 ? 'success' : 'warning'; ?>">
                    <span class="status-icon"><?php echo $configured_providers > 0 ? '✅' : '⚠️'; ?></span>
                    <span class="status-text">ספקי AI: <?php echo $configured_providers; ?> מתוך 4 מוגדרים</span>
                </div>

                <div class="status-item <?php echo !empty($active_brand) ? 'success' : 'info'; ?>">
                    <span class="status-icon"><?php echo !empty($active_brand) ? '✅' : 'ℹ️'; ?></span>
                    <span class="status-text">מותג פעיל:
                        <?php echo !empty($active_brand) ? 'מוגדר' : 'לא נבחר'; ?></span>
                </div>

                <div class="status-item success">
                    <span class="status-icon">✅</span>
                    <span class="status-text">ספק ברירת מחדל: <?php echo ucfirst($default_provider); ?></span>
                </div>

                <div class="status-item success">
                    <span class="status-icon">✅</span>
                    <span class="status-text">פלאגין פעיל ותקין</span>
                </div>
            </div>
        </div>

        <!-- Getting Started Guide -->
        <div class="dashboard-card getting-started">
            <h2>🎯 מדריך התחלה מהירה</h2>
            <div class="guide-steps">
                <div class="guide-step <?php echo $configured_providers > 0 ? 'completed' : 'active'; ?>">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>הגדר מפתחות API</h4>
                        <p>הוסף מפתחות עבור OpenAI, Anthropic, OpenRouter או DeepSeek</p>
                        <?php if ($configured_providers == 0): ?>
                            <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-api-keys'); ?>"
                                class="step-action">הגדר עכשיו</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div
                    class="guide-step <?php echo $brands_count > 0 ? 'completed' : ($configured_providers > 0 ? 'active' : ''); ?>">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>צור מותג ראשון</h4>
                        <p>הגדר את קול המותג, טון הכתיבה וההנחיות</p>
                        <?php if ($brands_count == 0 && $configured_providers > 0): ?>
                            <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-brands'); ?>"
                                class="step-action">צור מותג</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="guide-step <?php echo ($brands_count > 0 && $configured_providers > 0) ? 'active' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>התחל ליצור תוכן</h4>
                        <p>השתמש במחולל התוכן ליצירת פוסטים, מוצרים ועוד</p>
                        <?php if ($brands_count > 0 && $configured_providers > 0): ?>
                            <a href="<?php echo admin_url('admin.php?page=ai-manager-pro-content-generator'); ?>"
                                class="step-action">צור תוכן</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Providers Status -->
        <div class="dashboard-card providers-status">
            <h2>🤖 ספקי AI זמינים</h2>
            <div class="providers-grid">
                <div class="provider-item <?php echo $api_keys_status['openai'] ? 'configured' : 'not-configured'; ?>">
                    <div class="provider-logo">🤖</div>
                    <div class="provider-info">
                        <h4>OpenAI</h4>
                        <p>GPT-4, GPT-3.5</p>
                        <span
                            class="provider-status"><?php echo $api_keys_status['openai'] ? 'מוגדר' : 'לא מוגדר'; ?></span>
                    </div>
                </div>

                <div
                    class="provider-item <?php echo $api_keys_status['anthropic'] ? 'configured' : 'not-configured'; ?>">
                    <div class="provider-logo">🧠</div>
                    <div class="provider-info">
                        <h4>Anthropic</h4>
                        <p>Claude 3 Series</p>
                        <span
                            class="provider-status"><?php echo $api_keys_status['anthropic'] ? 'מוגדר' : 'לא מוגדר'; ?></span>
                    </div>
                </div>

                <div
                    class="provider-item <?php echo $api_keys_status['openrouter'] ? 'configured' : 'not-configured'; ?>">
                    <div class="provider-logo">🌐</div>
                    <div class="provider-info">
                        <h4>OpenRouter</h4>
                        <p>מודלים מרובים</p>
                        <span
                            class="provider-status"><?php echo $api_keys_status['openrouter'] ? 'מוגדר' : 'לא מוגדר'; ?></span>
                    </div>
                </div>

                <div
                    class="provider-item <?php echo $api_keys_status['deepseek'] ? 'configured' : 'not-configured'; ?>">
                    <div class="provider-logo">🔬</div>
                    <div class="provider-info">
                        <h4>DeepSeek</h4>
                        <p>Chat & Coder</p>
                        <span
                            class="provider-status"><?php echo $api_keys_status['deepseek'] ? 'מוגדר' : 'לא מוגדר'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card recent-activity">
            <h2>📈 פעילות אחרונה</h2>
            <div class="activity-list">
                <?php
                $recent_posts = get_posts([
                    'numberposts' => 5,
                    'meta_query' => [
                        [
                            'key' => '_ai_generated',
                            'value' => '1',
                            'compare' => '='
                        ]
                    ]
                ]);

                if (!empty($recent_posts)): ?>
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="activity-item">
                            <div class="activity-icon">📝</div>
                            <div class="activity-content">
                                <h4><?php echo esc_html($post->post_title); ?></h4>
                                <p>נוצר ב-<?php echo get_the_date('d/m/Y H:i', $post); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-activity">
                        <p>אין פעילות אחרונה. התחל ליצור תוכן!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-info">
                <p><strong>AI Website Manager Pro</strong> - פותח על ידי יעקב בידני, BDNHOST</p>
                <p>גרסה <?php echo AI_MANAGER_PRO_VERSION; ?> | <a href="https://bdnhost.net" target="_blank">אתר
                        המפתח</a> | <a href="mailto:info@bdnhost.net">תמיכה</a></p>
            </div>
        </div>
    </div>
</div>

<style>
    .ai-dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-text h1 {
        margin: 0 0 10px 0;
        font-size: 2.5em;
        font-weight: 700;
    }

    .header-subtitle {
        margin: 0 0 15px 0;
        font-size: 1.2em;
        opacity: 0.9;
    }

    .version-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9em;
        display: inline-block;
    }

    .ai-logo {
        display: flex;
        align-items: center;
        font-size: 2em;
        font-weight: bold;
    }

    .logo-icon {
        margin-left: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        font-size: 2.5em;
        margin-left: 20px;
    }

    .stat-content h3 {
        margin: 0;
        font-size: 2em;
        font-weight: 700;
        color: #333;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9em;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card h2 {
        margin: 0 0 20px 0;
        font-size: 1.3em;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .action-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        text-align: center;
    }

    .action-button.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .action-button.secondary {
        background: #f8f9fa;
        color: #333;
        border: 2px solid #e9ecef;
    }

    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .action-icon {
        font-size: 1.5em;
        margin-bottom: 8px;
    }

    .status-list,
    .activity-list {
        space-y: 10px;
    }

    .status-item,
    .activity-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .status-item.success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
    }

    .status-item.warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
    }

    .status-item.info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
    }

    .status-icon,
    .activity-icon {
        margin-left: 10px;
        font-size: 1.2em;
    }

    .guide-steps {
        space-y: 15px;
    }

    .guide-step {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }

    .guide-step.completed {
        background: #d4edda;
        border-color: #c3e6cb;
    }

    .guide-step.active {
        background: #fff3cd;
        border-color: #ffeaa7;
    }

    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #6c757d;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-left: 15px;
        flex-shrink: 0;
    }

    .guide-step.completed .step-number {
        background: #28a745;
    }

    .guide-step.active .step-number {
        background: #ffc107;
        color: #333;
    }

    .step-content h4 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .step-content p {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 0.9em;
    }

    .step-action {
        background: #007cba;
        color: white;
        padding: 5px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
    }

    .providers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .provider-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }

    .provider-item.configured {
        background: #d4edda;
        border-color: #c3e6cb;
    }

    .provider-item.not-configured {
        background: #f8f9fa;
        border-color: #e9ecef;
    }

    .provider-logo {
        font-size: 1.5em;
        margin-left: 15px;
    }

    .provider-info h4 {
        margin: 0 0 5px 0;
        font-size: 1em;
    }

    .provider-info p {
        margin: 0 0 5px 0;
        font-size: 0.8em;
        color: #666;
    }

    .provider-status {
        font-size: 0.8em;
        font-weight: bold;
    }

    .provider-item.configured .provider-status {
        color: #28a745;
    }

    .provider-item.not-configured .provider-status {
        color: #6c757d;
    }

    .no-activity {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    .dashboard-footer {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-top: 30px;
    }

    .footer-content p {
        margin: 5px 0;
        color: #666;
    }

    .footer-content a {
        color: #007cba;
        text-decoration: none;
    }

    .footer-content a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>