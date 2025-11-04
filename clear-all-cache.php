<?php
/**
 * Clear All WordPress Cache Script
 *
 * Instructions:
 * 1. Upload this file to your WordPress root directory
 * 2. Visit: http://your-site.com/clear-all-cache.php
 * 3. Delete this file after use for security
 */

// Load WordPress
require_once('wp-load.php');

// Security check - only allow logged in admins
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('⛔ Access denied. You must be logged in as an administrator.');
}

echo '<html dir="rtl"><head><meta charset="UTF-8">';
echo '<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
.success { background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 8px; }
.info { background: #d1ecf1; border: 2px solid #17a2b8; padding: 15px; margin: 10px 0; border-radius: 8px; }
.warning { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 8px; }
h1 { color: #333; text-align: center; }
.action { font-size: 18px; font-weight: bold; }
</style></head><body>';

echo '<h1>🧹 ניקוי Cache מלא ל-WordPress</h1>';

$results = [];

// 1. WordPress Object Cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    $results[] = ['✅ WordPress Object Cache נוקה בהצלחה', 'success'];
} else {
    $results[] = ['⚠️ WordPress Object Cache לא זמין', 'warning'];
}

// 2. Transients Cache
global $wpdb;
$transients_deleted = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_%'");
$results[] = ["✅ נמחקו $transients_deleted Transients", 'success'];

// 3. AI Manager Pro specific cache
delete_transient('ai_manager_pro_version_updated');
update_option('ai_manager_pro_version', '0.0.0'); // Force version check
$results[] = ['✅ Cache של AI Manager Pro נוקה', 'success'];

// 4. Rewrite rules flush
flush_rewrite_rules();
$results[] = ['✅ Rewrite Rules רעננו', 'success'];

// 5. WP Super Cache (if active)
if (function_exists('wp_cache_clear_cache')) {
    wp_cache_clear_cache();
    $results[] = ['✅ WP Super Cache נוקה', 'success'];
}

// 6. W3 Total Cache (if active)
if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
    $results[] = ['✅ W3 Total Cache נוקה', 'success'];
}

// 7. WP Rocket (if active)
if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
    $results[] = ['✅ WP Rocket Cache נוקה', 'success'];
}

// 8. LiteSpeed Cache (if active)
if (class_exists('LiteSpeed\Purge')) {
    do_action('litespeed_purge_all');
    $results[] = ['✅ LiteSpeed Cache נוקה', 'success'];
}

// 9. OPcache (if available)
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = ['✅ PHP OPcache נוקה', 'success'];
} else {
    $results[] = ['⚠️ PHP OPcache לא זמין', 'warning'];
}

// Display results
foreach ($results as $result) {
    echo "<div class='{$result[1]}'>{$result[0]}</div>";
}

// Final instructions
echo '<div class="info">';
echo '<h3>📝 שלבים נוספים:</h3>';
echo '<ol style="text-align: right;">';
echo '<li><strong>נקה את Cache הדפדפן:</strong> לחץ Ctrl+Shift+Delete ונקה Cached Images</li>';
echo '<li><strong>עשה Hard Refresh:</strong> לחץ Ctrl+F5 בדף הדשבורד</li>';
echo '<li><strong>נסה במצב Incognito:</strong> פתח חלון פרטי ובדוק שם</li>';
echo '<li><strong>מחק קובץ זה לאחר השימוש!</strong> (לביטחון)</li>';
echo '</ol>';
echo '</div>';

echo '<div class="success" style="text-align: center;">';
echo '<h2>✅ ניקוי Cache הושלם בהצלחה!</h2>';
echo '<p class="action">עכשיו לך לדשבורד ועשה Ctrl+F5</p>';
echo '<p><a href="' . admin_url('admin.php?page=ai-manager-pro-general') . '" style="background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; font-size: 18px;">🚀 לך לדשבורד →</a></p>';
echo '</div>';

echo '<div class="warning" style="text-align: center; margin-top: 30px;">';
echo '<p><strong>⚠️ חשוב!</strong> מחק את הקובץ clear-all-cache.php מהשרת כעת!</p>';
echo '</div>';

echo '</body></html>';
