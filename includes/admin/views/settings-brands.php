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
                                            <button type="button" class="button button-small view-brand-btn"
                                                data-brand-id="<?php echo esc_attr($brand->id); ?>">
                                                <span class="dashicons dashicons-visibility"></span>
                                                <?php _e('View', 'ai-website-manager-pro'); ?>
                                            </button>
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
        // View brand button
        $('.view-brand-btn').on('click', function() {
            const brandId = $(this).data('brand-id');
            viewBrandDetails(brandId);
        });

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

    // View brand details
    function viewBrandDetails(brandId) {
        jQuery('#brand-view-modal').show();
        jQuery('#brand-view-content').html('<div class="loading-spinner"><span class="dashicons dashicons-update spin"></span> Loading brand details...</div>');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_get_brand_details',
                brand_id: brandId,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayBrandDetails(response.data);
                    jQuery('#edit-from-view-btn').data('brand-id', brandId);
                } else {
                    jQuery('#brand-view-content').html('<div class="error"><p>Failed to load brand details: ' + (response.data || 'Unknown error') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                jQuery('#brand-view-content').html('<div class="error"><p>Network error: ' + error + '</p></div>');
            }
        });
    }

    // Display brand details
    function displayBrandDetails(brand) {
        let html = '';

        // Basic Information
        html += '<div class="brand-section">';
        html += '<h3>📋 ' + '<?php _e('Basic Information', 'ai-website-manager-pro'); ?>' + '</h3>';
        html += '<div class="brand-info-grid">';
        if (brand.name) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Brand Name', 'ai-website-manager-pro'); ?></div><div class="brand-info-value">' + brand.name + '</div></div>';
        if (brand.industry) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Industry', 'ai-website-manager-pro'); ?></div><div class="brand-info-value">' + brand.industry + '</div></div>';
        if (brand.brand_voice) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Brand Voice', 'ai-website-manager-pro'); ?></div><div class="brand-info-value">' + brand.brand_voice + '</div></div>';
        if (brand.tone_of_voice) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Tone of Voice', 'ai-website-manager-pro'); ?></div><div class="brand-info-value">' + brand.tone_of_voice + '</div></div>';
        html += '</div>';
        if (brand.description) html += '<div class="brand-text-content" style="margin-top: 15px;"><strong><?php _e('Description:', 'ai-website-manager-pro'); ?></strong><br>' + brand.description + '</div>';
        html += '</div>';

        // Keywords & Values
        if (brand.keywords || brand.values) {
            html += '<div class="brand-section">';
            html += '<h3>🔑 ' + '<?php _e('Keywords & Values', 'ai-website-manager-pro'); ?>' + '</h3>';
            if (brand.keywords) {
                html += '<div><strong><?php _e('Keywords:', 'ai-website-manager-pro'); ?></strong><div class="brand-keywords-list">';
                const keywords = Array.isArray(brand.keywords) ? brand.keywords : brand.keywords.split(',');
                keywords.forEach(keyword => {
                    html += '<span class="brand-keyword-tag">' + keyword.trim() + '</span>';
                });
                html += '</div></div>';
            }
            if (brand.values) {
                html += '<div style="margin-top: 15px;"><strong><?php _e('Values:', 'ai-website-manager-pro'); ?></strong><div class="brand-keywords-list">';
                const values = Array.isArray(brand.values) ? brand.values : brand.values.split(',');
                values.forEach(value => {
                    html += '<span class="brand-keyword-tag" style="background: #28a745;">' + value.trim() + '</span>';
                });
                html += '</div></div>';
            }
            html += '</div>';
        }

        // Target Audience
        if (brand.target_audience) {
            html += '<div class="brand-section">';
            html += '<h3>🎯 ' + '<?php _e('Target Audience', 'ai-website-manager-pro'); ?>' + '</h3>';
            html += '<div class="brand-text-content">' + brand.target_audience + '</div>';
            html += '</div>';
        }

        // Mission, Vision, USP
        if (brand.mission || brand.vision || brand.unique_selling_proposition) {
            html += '<div class="brand-section">';
            html += '<h3>🚀 ' + '<?php _e('Mission & Vision', 'ai-website-manager-pro'); ?>' + '</h3>';
            if (brand.mission) html += '<div class="brand-text-content" style="margin-bottom: 10px;"><strong><?php _e('Mission:', 'ai-website-manager-pro'); ?></strong><br>' + brand.mission + '</div>';
            if (brand.vision) html += '<div class="brand-text-content" style="margin-bottom: 10px;"><strong><?php _e('Vision:', 'ai-website-manager-pro'); ?></strong><br>' + brand.vision + '</div>';
            if (brand.unique_selling_proposition) html += '<div class="brand-text-content"><strong><?php _e('Unique Selling Proposition:', 'ai-website-manager-pro'); ?></strong><br>' + brand.unique_selling_proposition + '</div>';
            html += '</div>';
        }

        // Website & Contact
        if (brand.website_url || brand.logo_url) {
            html += '<div class="brand-section">';
            html += '<h3>🌐 ' + '<?php _e('Online Presence', 'ai-website-manager-pro'); ?>' + '</h3>';
            html += '<div class="brand-info-grid">';
            if (brand.website_url) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Website', 'ai-website-manager-pro'); ?></div><div class="brand-info-value"><a href="' + brand.website_url + '" target="_blank">' + brand.website_url + '</a></div></div>';
            if (brand.logo_url) html += '<div class="brand-info-item"><div class="brand-info-label"><?php _e('Logo URL', 'ai-website-manager-pro'); ?></div><div class="brand-info-value"><a href="' + brand.logo_url + '" target="_blank"><?php _e('View Logo', 'ai-website-manager-pro'); ?></a></div></div>';
            html += '</div>';
            html += '</div>';
        }

        jQuery('#brand-view-content').html(html);
    }

    // Close brand view modal
    function closeBrandViewModal() {
        jQuery('#brand-view-modal').hide();
    }
</script>

<!-- Brand View Modal -->
<div id="brand-view-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content brand-view-content">
        <div class="ai-modal-header">
            <h2 id="brand-view-title">🏢 <?php _e('Brand Details', 'ai-website-manager-pro'); ?></h2>
            <button type="button" class="ai-modal-close" onclick="closeBrandViewModal()">&times;</button>
        </div>
        <div class="ai-modal-body brand-view-body" id="brand-view-content">
            <!-- Content loaded via AJAX -->
            <div class="loading-spinner">
                <span class="dashicons dashicons-update spin"></span>
                <?php _e('Loading brand details...', 'ai-website-manager-pro'); ?>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="button button-secondary" onclick="closeBrandViewModal()">
                <?php _e('Close', 'ai-website-manager-pro'); ?>
            </button>
            <button type="button" class="button button-primary" id="edit-from-view-btn">
                <span class="dashicons dashicons-edit"></span>
                <?php _e('Edit Brand', 'ai-website-manager-pro'); ?>
            </button>
        </div>
    </div>
</div>

<style>
    .brand-view-content {
        max-width: 900px !important;
        width: 90%;
    }

    .brand-view-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .brand-section {
        margin-bottom: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .brand-section h3 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 1.1em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .brand-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .brand-info-item {
        background: white;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }

    .brand-info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9em;
    }

    .brand-info-value {
        color: #6c757d;
        font-size: 0.95em;
    }

    .brand-keywords-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .brand-keyword-tag {
        background: #667eea;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .brand-text-content {
        background: white;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        line-height: 1.6;
        color: #495057;
    }

    .loading-spinner {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

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