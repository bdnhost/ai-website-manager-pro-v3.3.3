<?php
/**
 * Plugin Footer Component
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

    </div><!-- /.ai-plugin-content -->

<div class="ai-plugin-footer">
    <div class="footer-content">
        <div class="footer-left">
            <img src="https://bdnhost.net/wp-content/uploads/2024/04/cropped-bdnhost-1-1.png" alt="BDNHOST Logo"
                class="footer-logo">
            <div class="footer-text">
                <p>
                    <strong><?php _e('Developed by:', 'ai-website-manager-pro'); ?></strong>
                    יעקב בידני
                </p>
                <p>
                    <strong>BDNHOST</strong> - <?php _e('פתרונות אינטרנט וקוד פתוח', 'ai-website-manager-pro'); ?>
                </p>
            </div>
        </div>

        <div class="footer-right">
            <div class="footer-links">
                <a href="https://bdnhost.net" target="_blank" class="footer-link">
                    <span class="dashicons dashicons-admin-site"></span>
                    <?php _e('Website', 'ai-website-manager-pro'); ?>
                </a>
                <a href="mailto:info@bdnhost.net" class="footer-link">
                    <span class="dashicons dashicons-email"></span>
                    <?php _e('Support', 'ai-website-manager-pro'); ?>
                </a>
                <a href="https://bdnhost.net/ai-website-manager-pro" target="_blank" class="footer-link">
                    <span class="dashicons dashicons-book"></span>
                    <?php _e('Documentation', 'ai-website-manager-pro'); ?>
                </a>
            </div>

            <div class="footer-version">
                <small>
                    AI Website Manager Pro v<?php echo AI_MANAGER_PRO_VERSION; ?> |
                    <?php _e('Licensed under GPL v2+', 'ai-website-manager-pro'); ?>
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .ai-plugin-footer {
        margin-top: 40px;
        padding: 20px 0;
        border-top: 2px solid #0073aa;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .footer-logo {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .footer-text p {
        margin: 2px 0;
        font-size: 13px;
        color: #555;
        line-height: 1.4;
    }

    .footer-text strong {
        color: #0073aa;
    }

    .footer-right {
        text-align: right;
    }

    .footer-links {
        display: flex;
        gap: 15px;
        margin-bottom: 8px;
    }

    .footer-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #0073aa;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .footer-link:hover {
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 115, 170, 0.3);
    }

    .footer-link .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
    }

    .footer-version {
        color: #666;
        font-size: 11px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .footer-left {
            flex-direction: column;
            gap: 10px;
        }

        .footer-links {
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-link {
            font-size: 11px;
            padding: 4px 8px;
        }
    }

    /* Dark Theme Support */
    @media (prefers-color-scheme: dark) {
        .ai-plugin-footer {
            background: linear-gradient(135deg, #2c3338 0%, #1d2327 100%);
            border-top-color: #00a0d2;
        }

        .footer-text p {
            color: #c3c4c7;
        }

        .footer-text strong {
            color: #00a0d2;
        }

        .footer-link {
            background: #1d2327;
            border-color: #3c434a;
            color: #00a0d2;
        }

        .footer-link:hover {
            background: #00a0d2;
            color: #fff;
        }

        .footer-version {
            color: #8c8f94;
        }
    }

    /* Print Styles */
    @media print {
        .ai-plugin-footer {
            background: none !important;
            border-top: 1px solid #000;
        }

        .footer-links {
            display: none;
        }

        .footer-logo {
            width: 30px;
            height: 30px;
        }
    }

    /* Plugin Header Styles */
    .ai-plugin-wrap {
        direction: rtl;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        background: #f8fafc;
        min-height: 100vh;
    }

    .ai-plugin-header {
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

    .page-title-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-icon {
        font-size: 32px;
    }

    .page-subtitle {
        margin: 0;
        font-size: 16px;
        opacity: 0.9;
    }

    .header-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }

    .stat-badge:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .stat-badge.connected {
        background: rgba(16, 185, 129, 0.3);
        border-color: rgba(16, 185, 129, 0.5);
    }

    .stat-badge.disconnected {
        background: rgba(239, 68, 68, 0.3);
        border-color: rgba(239, 68, 68, 0.5);
    }

    .ai-plugin-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    /* Responsive Design for Header */
    @media (max-width: 768px) {
        .ai-plugin-wrap {
            padding: 10px;
        }

        .ai-plugin-header {
            padding: 20px;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .page-title {
            font-size: 22px;
        }

        .page-icon {
            font-size: 26px;
        }

        .page-subtitle {
            font-size: 14px;
        }

        .header-stats {
            width: 100%;
            justify-content: flex-start;
        }

        .ai-plugin-content {
            padding: 20px;
        }
    }
</style>

</div><!-- /.ai-plugin-wrap -->