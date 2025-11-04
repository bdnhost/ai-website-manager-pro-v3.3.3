<?php
/**
 * Plugin Header Component - Unified Design for All Pages
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current page info
$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

// Page titles and icons
$page_info = [
    'ai-manager-pro' => [
        'title' => 'דשבורד ראשי',
        'icon' => '🏠',
        'subtitle' => 'סקירה כללית של המערכת'
    ],
    'ai-manager-content-generator' => [
        'title' => 'מחולל תוכן',
        'icon' => '✍️',
        'subtitle' => 'צור תוכן איכותי עם בינה מלאכותית'
    ],
    'ai-manager-brands' => [
        'title' => 'ניהול מותגים',
        'icon' => '🏢',
        'subtitle' => 'נהל את המותגים והפרופילים שלך'
    ],
    'ai-manager-settings' => [
        'title' => 'הגדרות כלליות',
        'icon' => '⚙️',
        'subtitle' => 'הגדר את המערכת לפי הצרכים שלך'
    ],
    'ai-manager-api-keys' => [
        'title' => 'מפתחות API',
        'icon' => '🔑',
        'subtitle' => 'נהל את החיבורים לשירותי AI'
    ],
    'ai-manager-automation' => [
        'title' => 'אוטומציות',
        'icon' => '🤖',
        'subtitle' => 'הגדר משימות אוטומטיות'
    ],
    'ai-manager-logs' => [
        'title' => 'לוגים',
        'icon' => '📋',
        'subtitle' => 'צפה בפעילות המערכת'
    ],
];

$info = $page_info[$current_page] ?? [
    'title' => 'AI Website Manager Pro',
    'icon' => '🚀',
    'subtitle' => 'מערכת ניהול תוכן מתקדמת'
];

// Get quick stats for header
$api_key_configured = !empty(get_option('ai_manager_pro_openrouter_api_key', ''));
$brands_count = count(get_option('ai_manager_pro_brands_data', []));
?>

<div class="ai-plugin-wrap">
    <!-- Plugin Header -->
    <div class="ai-plugin-header">
        <div class="header-content">
            <div class="header-left">
                <div class="page-title-section">
                    <h1 class="page-title">
                        <span class="page-icon"><?php echo $info['icon']; ?></span>
                        <?php echo esc_html($info['title']); ?>
                    </h1>
                    <p class="page-subtitle"><?php echo esc_html($info['subtitle']); ?></p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-stats">
                    <span class="stat-badge <?php echo $api_key_configured ? 'connected' : 'disconnected'; ?>">
                        <?php echo $api_key_configured ? '🟢 מחובר' : '🔴 לא מחובר'; ?>
                    </span>
                    <span class="stat-badge">
                        🏢 <?php echo $brands_count; ?> מותגים
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="ai-plugin-content">
