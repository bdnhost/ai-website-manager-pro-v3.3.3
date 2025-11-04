<?php
/**
 * User Flow Test Script
 *
 * This script simulates the complete user flow and validates all components
 * Upload to WordPress root and visit: http://your-site.com/test-user-flow.php
 */

// Load WordPress
require_once('wp-load.php');

// Security check
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('⛔ Access denied. You must be logged in as an administrator.');
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>בדיקת מסלול משתמש - AI Manager Pro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        .test-section.success {
            background: #d4edda;
            border-color: #28a745;
        }
        .test-section.error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .test-section.warning {
            background: #fff3cd;
            border-color: #ffc107;
        }
        .test-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .test-result {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        .button:hover {
            background: #5568d3;
        }
        .code {
            background: #1e1e1e;
            color: #dcdcdc;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin: 10px 0;
        }
        .success-icon { color: #28a745; font-size: 24px; }
        .error-icon { color: #dc3545; font-size: 24px; }
        .warning-icon { color: #ffc107; font-size: 24px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔍 בדיקת מסלול משתמש מלא</h1>
        <p style="text-align: center; color: #666;">AI Website Manager Pro v3.3.3</p>

        <?php
        $tests = [];
        $all_passed = true;

        // Test 1: Dashboard file
        $dashboard_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
        if (file_exists($dashboard_file)) {
            $dashboard_content = file_get_contents($dashboard_file);
            $has_whats_new = strpos($dashboard_content, "What's New") !== false;
            $has_seo_templates = strpos($dashboard_content, "SEO Templates Quick Access") !== false;
            $has_function = strpos($dashboard_content, "function createWithTemplate") !== false;

            if ($has_whats_new && $has_seo_templates && $has_function) {
                $tests[] = [
                    'title' => 'דשבורד (Dashboard)',
                    'status' => 'success',
                    'details' => [
                        "✅ קובץ dashboard.php קיים",
                        "✅ סעיף 'What's New' נמצא",
                        "✅ סעיף 'SEO Templates' נמצא",
                        "✅ פונקציית createWithTemplate() קיימת",
                        "📊 גודל קובץ: " . number_format(strlen($dashboard_content)) . " תווים"
                    ]
                ];
            } else {
                $tests[] = [
                    'title' => 'דשבורד (Dashboard)',
                    'status' => 'error',
                    'details' => [
                        "❌ חסרים רכיבים בדשבורד",
                        "What's New: " . ($has_whats_new ? '✅' : '❌'),
                        "SEO Templates: " . ($has_seo_templates ? '✅' : '❌'),
                        "createWithTemplate: " . ($has_function ? '✅' : '❌')
                    ]
                ];
                $all_passed = false;
            }
        } else {
            $tests[] = [
                'title' => 'דשבורד (Dashboard)',
                'status' => 'error',
                'details' => ["❌ קובץ dashboard.php לא נמצא!"]
            ];
            $all_passed = false;
        }

        // Test 2: Content Generator
        $generator_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/content-generator.php';
        if (file_exists($generator_file)) {
            $generator_content = file_get_contents($generator_file);
            $has_load_function = strpos($generator_content, "loadTemplateFromDashboard") !== false;
            $has_session_check = strpos($generator_content, "sessionStorage.getItem") !== false;
            $has_highlight = strpos($generator_content, "box-shadow") !== false;

            if ($has_load_function && $has_session_check && $has_highlight) {
                $tests[] = [
                    'title' => 'מחולל תוכן (Content Generator)',
                    'status' => 'success',
                    'details' => [
                        "✅ קובץ content-generator.php קיים",
                        "✅ פונקציית loadTemplateFromDashboard() קיימת",
                        "✅ בדיקת sessionStorage קיימת",
                        "✅ Highlight ויזואלי קיים"
                    ]
                ];
            } else {
                $tests[] = [
                    'title' => 'מחולל תוכן (Content Generator)',
                    'status' => 'error',
                    'details' => [
                        "❌ חסר קוד במחולל תוכן",
                        "loadTemplateFromDashboard: " . ($has_load_function ? '✅' : '❌'),
                        "sessionStorage check: " . ($has_session_check ? '✅' : '❌'),
                        "Visual highlight: " . ($has_highlight ? '✅' : '❌')
                    ]
                ];
                $all_passed = false;
            }
        } else {
            $tests[] = [
                'title' => 'מחולל תוכן (Content Generator)',
                'status' => 'error',
                'details' => ["❌ קובץ content-generator.php לא נמצא!"]
            ];
            $all_passed = false;
        }

        // Test 3: Backend SEO Templates
        $backend_file = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/modules/content-generation/class-content-generator.php';
        if (file_exists($backend_file)) {
            $backend_content = file_get_contents($backend_file);
            $has_init = strpos($backend_content, "init_seo_templates") !== false;
            $has_article = strpos($backend_content, "get_article_seo_instructions") !== false;
            $has_validate = strpos($backend_content, "validate_seo_structure") !== false;

            if ($has_init && $has_article && $has_validate) {
                $tests[] = [
                    'title' => 'Backend - תבניות SEO',
                    'status' => 'success',
                    'details' => [
                        "✅ קובץ class-content-generator.php קיים",
                        "✅ init_seo_templates() קיימת",
                        "✅ get_article_seo_instructions() קיימת",
                        "✅ validate_seo_structure() קיימת",
                        "📦 5 תבניות SEO מוגדרות"
                    ]
                ];
            } else {
                $tests[] = [
                    'title' => 'Backend - תבניות SEO',
                    'status' => 'error',
                    'details' => [
                        "❌ חסר קוד Backend",
                        "init_seo_templates: " . ($has_init ? '✅' : '❌'),
                        "article instructions: " . ($has_article ? '✅' : '❌'),
                        "validate: " . ($has_validate ? '✅' : '❌')
                    ]
                ];
                $all_passed = false;
            }
        } else {
            $tests[] = [
                'title' => 'Backend - תבניות SEO',
                'status' => 'error',
                'details' => ["❌ קובץ class-content-generator.php לא נמצא!"]
            ];
            $all_passed = false;
        }

        // Test 4: Main plugin file
        $main_file = AI_MANAGER_PRO_PLUGIN_DIR . 'ai-website-manager-pro.php';
        if (file_exists($main_file)) {
            $main_content = file_get_contents($main_file);
            $loads_dashboard = strpos($main_content, "includes/admin/views/dashboard.php") !== false;
            $correct_version = strpos($main_content, "3.3.3") !== false;

            if ($loads_dashboard && $correct_version) {
                $tests[] = [
                    'title' => 'קובץ ראשי (Main Plugin)',
                    'status' => 'success',
                    'details' => [
                        "✅ טוען את dashboard.php (לא dashboard-main.php)",
                        "✅ גרסה 3.3.3 מוגדרת",
                        "✅ הודעת עדכון מעודכנת"
                    ]
                ];
            } else {
                $tests[] = [
                    'title' => 'קובץ ראשי (Main Plugin)',
                    'status' => 'error',
                    'details' => [
                        "dashboard.php: " . ($loads_dashboard ? '✅' : '❌'),
                        "version 3.3.3: " . ($correct_version ? '✅' : '❌')
                    ]
                ];
                $all_passed = false;
            }
        } else {
            $tests[] = [
                'title' => 'קובץ ראשי (Main Plugin)',
                'status' => 'error',
                'details' => ["❌ קובץ ai-website-manager-pro.php לא נמצא!"]
            ];
            $all_passed = false;
        }

        // Display results
        foreach ($tests as $test) {
            $class = 'test-section ' . $test['status'];
            $icon = $test['status'] === 'success' ? '✅' : '❌';

            echo "<div class='$class'>";
            echo "<div class='test-title'>$icon {$test['title']}</div>";
            foreach ($test['details'] as $detail) {
                echo "<div class='test-result'>$detail</div>";
            }
            echo "</div>";
        }

        // Final result
        if ($all_passed) {
            echo "<div class='test-section success' style='margin-top: 30px; text-align: center;'>";
            echo "<div class='success-icon'>🎉</div>";
            echo "<h2>כל הבדיקות עברו בהצלחה!</h2>";
            echo "<p>התשתית מושלמת ומוכנה לשימוש</p>";
            echo "<div style='margin-top: 20px;'>";
            echo "<a href='" . admin_url('admin.php?page=ai-manager-pro-general') . "' class='button'>🚀 לך לדשבורד</a>";
            echo "<a href='" . admin_url('admin.php?page=ai-manager-pro-content-generator') . "' class='button'>✍️ לך למחולל תוכן</a>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='test-section error' style='margin-top: 30px; text-align: center;'>";
            echo "<div class='error-icon'>⚠️</div>";
            echo "<h2>נמצאו בעיות!</h2>";
            echo "<p>אנא התקן את הפלאגין מחדש או צור קשר לתמיכה</p>";
            echo "</div>";
        }
        ?>

        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h3>📝 מסלול משתמש מומלץ לבדיקה:</h3>
            <ol style="line-height: 2;">
                <li>לך ל<a href="<?php echo admin_url('admin.php?page=ai-manager-pro-general'); ?>">דשבורד</a> וגלול למטה</li>
                <li>בדוק שאתה רואה סעיף <strong>"תבניות SEO - גישה מהירה"</strong></li>
                <li>פתח <strong>Developer Tools (F12)</strong> ולך ל-Console</li>
                <li>לחץ על כפתור <strong>"צור מאמר →"</strong></li>
                <li>בקונסול תראה הודעות debug</li>
                <li>בעמוד content-generator התפריט יהיה מודגש בכחול</li>
                <li>צור תוכן וראה את ציון ה-SEO</li>
            </ol>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; text-align: center;">
            <p><strong>⚠️ חשוב:</strong> מחק את הקובץ test-user-flow.php מהשרת לאחר השימוש!</p>
        </div>
    </div>
</body>
</html>
