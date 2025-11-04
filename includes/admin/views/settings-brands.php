<?php
/**
 * Brand Management Settings Page
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="ai-manager-pro-admin-container">
        <div class="ai-manager-pro-main-content">
            <div class="postbox">
                <h2 class="hndle"><?php _e('Brand Management', 'ai-website-manager-pro'); ?></h2>
                <div class="inside">
                    <p><?php _e('Manage your brand profiles for consistent AI-generated content.', 'ai-website-manager-pro'); ?>
                    </p>

                    <div class="brand-actions">
                        <button type="button" class="button button-primary" id="create-brand-btn">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Create New Brand', 'ai-website-manager-pro'); ?>
                        </button>
                        <button type="button" class="button button-secondary" id="import-brand-btn">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Import Brand', 'ai-website-manager-pro'); ?>
                        </button>
                        <button type="button" class="button button-secondary" id="sample-to-brand-btn">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('Create from Sample', 'ai-website-manager-pro'); ?>
                        </button>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Description', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Status', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Actions', 'ai-website-manager-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($brands)): ?>
                                <?php foreach ($brands as $brand): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($brand->name); ?></strong>
                                        </td>
                                        <td><?php echo esc_html($brand->description); ?></td>
                                        <td>
                                            <?php if ($brand->is_active): ?>
                                                <span class="status-active"><?php _e('Active', 'ai-website-manager-pro'); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="status-inactive"><?php _e('Inactive', 'ai-website-manager-pro'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small edit-brand-btn"
                                                data-brand-id="<?php echo esc_attr($brand->id); ?>">
                                                <?php _e('Edit', 'ai-website-manager-pro'); ?>
                                            </button>
                                            <?php if (!$brand->is_active): ?>
                                                <button type="button" class="button button-small activate-brand-btn"
                                                    data-brand-id="<?php echo esc_attr($brand->id); ?>">
                                                    <?php _e('Activate', 'ai-website-manager-pro'); ?>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="button button-small export-brand-btn"
                                                data-brand-id="<?php echo esc_attr($brand->id); ?>">
                                                <?php _e('Export', 'ai-website-manager-pro'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                        <p><?php _e('No brands found. Create your first brand to get started.', 'ai-website-manager-pro'); ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ai-manager-pro-sidebar">
            <div class="postbox">
                <h3 class="hndle"><?php _e('Active Brand', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <?php if ($active_brand): ?>
                        <h4><?php echo esc_html($active_brand->name); ?></h4>
                        <p><?php echo esc_html($active_brand->description); ?></p>
                        <p><small><?php printf(__('Last updated: %s', 'ai-website-manager-pro'), esc_html($active_brand->updated_at)); ?></small>
                        </p>
                    <?php else: ?>
                        <p><?php _e('No active brand selected.', 'ai-website-manager-pro'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><?php _e('Brand Template', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <p><?php _e('Download a brand template to get started quickly.', 'ai-website-manager-pro'); ?></p>
                    <button type="button" class="button button-secondary" id="download-template-btn">
                        <?php _e('Download Template', 'ai-website-manager-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .brand-actions {
        margin-bottom: 20px;
    }

    .brand-actions .button {
        margin-right: 10px;
    }

    .status-active {
        color: #46b450;
        font-weight: bold;
    }

    .status-inactive {
        color: #999;
    }
</style>
<!-- JSO
N Editor Modal -->
<div id="json-editor-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content large">
        <div class="ai-modal-header">
            <h2><?php _e('Advanced JSON Editor', 'ai-website-manager-pro'); ?></h2>
            <button type="button" class="ai-modal-close">&times;</button>
        </div>
        <div class="ai-modal-body">
            <div class="json-editor-wrapper">
                <label
                    for="brand-json-editor"><?php _e('Brand Configuration (JSON)', 'ai-website-manager-pro'); ?></label>
                <textarea id="brand-json-editor" class="json-editor-textarea" rows="20" cols="80">{
  "name": "Example Brand",
  "description": "A sample brand configuration",
  "voice": {
    "tone": "professional",
    "style": "informative",
    "personality": "friendly"
  },
  "guidelines": {
    "content_length": "medium",
    "keywords": ["innovation", "quality", "service"],
    "avoid_words": ["cheap", "basic"]
  },
  "target_audience": {
    "age_range": "25-45",
    "interests": ["technology", "business"],
    "demographics": "professionals"
  },
  "brand_colors": {
    "primary": "#0073aa",
    "secondary": "#005177",
    "accent": "#00a0d2"
  },
  "social_media": {
    "facebook": "@examplebrand",
    "twitter": "@examplebrand",
    "linkedin": "company/example-brand"
  }
}</textarea>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="button button-primary" id="save-json-btn">
                <?php _e('Save Configuration', 'ai-website-manager-pro'); ?>
            </button>
            <button type="button" class="button button-secondary ai-modal-close">
                <?php _e('Cancel', 'ai-website-manager-pro'); ?>
            </button>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function ($) {
        // Test JSON Editor button
        $('#json-editor-test-btn').on('click', function () {
            $('#json-editor-modal').show();
        });

        // Modal close functionality
        $('.ai-modal-close').on('click', function () {
            $(this).closest('.ai-modal').hide();
        });

        // Close modal on outside click
        $('.ai-modal').on('click', function (e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Save JSON configuration
        $('#save-json-btn').on('click', function () {
            const editor = AIManagerProJSONEditor.editors['brand-json-editor'];
            if (editor) {
                try {
                    const jsonData = JSON.parse(editor.getValue());
                    console.log('Valid JSON saved:', jsonData);

                    // Show success message
                    AIManagerProJSONEditor.showStatus('brand-json-editor', 'success', 'Configuration saved successfully');

                    // Here you would typically send the data to the server
                    // via AJAX to save the brand configuration

                    setTimeout(() => {
                        $('#json-editor-modal').hide();
                    }, 1500);

                } catch (e) {
                    AIManagerProJSONEditor.showStatus('brand-json-editor', 'error', 'Invalid JSON: ' + e.message);
                }
            }
        });
    });
</script>

<style>
    /* Modal Styles */
    .ai-modal {
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

    .ai-modal-content {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        max-width: 90%;
        max-height: 90%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .ai-modal-content.large {
        width: 1200px;
        height: 800px;
    }

    .ai-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9f9f9;
    }

    .ai-modal-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .ai-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ai-modal-close:hover {
        color: #000;
    }

    .ai-modal-body {
        padding: 20px;
        flex: 1;
        overflow: auto;
    }

    .ai-modal-footer {
        padding: 20px;
        border-top: 1px solid #ddd;
        background: #f9f9f9;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .json-editor-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .json-editor-wrapper label {
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
    }

    .json-editor-wrapper .json-editor-container {
        flex: 1;
        margin: 0;
    }

    @media (max-width: 768px) {
        .ai-modal-content.large {
            width: 95%;
            height: 95%;
        }

        .ai-modal-header,
        .ai-modal-body,
        .ai-modal-footer {
            padding: 15px;
        }
    }
</style>
< ?php // Include plugin footer include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php' ; ?>