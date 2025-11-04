<?php
/**
 * General Settings Page
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use AI_Manager_Pro\Settings\Schemas\General_Settings_Schema;

$form_fields = General_Settings_Schema::get_form_fields();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="ai-manager-pro-admin-container">
        <div class="ai-manager-pro-main-content">
            <form method="post" action="options.php" id="ai-manager-pro-general-form">
                <?php
                settings_fields('ai_manager_pro_general');
                do_settings_sections('ai_manager_pro_general');
                ?>

                <table class="form-table" role="presentation">
                    <?php foreach ($form_fields as $field_name => $field): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($field_name); ?>">
                                    <?php echo esc_html($field['title']); ?>
                                    <?php if ($field['required']): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                $field_id = $field_name;
                                $field_name_attr = "ai_manager_pro_general_settings[{$field_name}]";
                                $field_value = $settings[$field_name] ?? $field['default'] ?? '';

                                switch ($field['type']):
                                    case 'checkbox':
                                        ?>
                                        <input type="checkbox" id="<?php echo esc_attr($field_id); ?>"
                                            name="<?php echo esc_attr($field_name_attr); ?>" value="1" <?php checked($field_value, 1); ?> />
                                        <label for="<?php echo esc_attr($field_id); ?>">
                                            <?php echo esc_html($field['description']); ?>
                                        </label>
                                        <?php
                                        break;

                                    case 'select':
                                        ?>
                                        <select id="<?php echo esc_attr($field_id); ?>"
                                            name="<?php echo esc_attr($field_name_attr); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                                            <?php foreach ($field['options'] as $option_value => $option_label): ?>
                                                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($field_value, $option_value); ?>>
                                                    <?php echo esc_html($option_label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (!empty($field['description'])): ?>
                                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                                        <?php endif; ?>
                                        <?php
                                        break;

                                    case 'integer':
                                        ?>
                                        <input type="number" id="<?php echo esc_attr($field_id); ?>"
                                            name="<?php echo esc_attr($field_name_attr); ?>"
                                            value="<?php echo esc_attr($field_value); ?>" <?php echo isset($field['min']) ? 'min="' . esc_attr($field['min']) . '"' : ''; ?>             <?php echo isset($field['max']) ? 'max="' . esc_attr($field['max']) . '"' : ''; ?>             <?php echo $field['required'] ? 'required' : ''; ?> class="regular-text" />
                                        <?php if (!empty($field['description'])): ?>
                                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                                        <?php endif; ?>
                                        <?php
                                        break;

                                    case 'string':
                                    default:
                                        if (isset($field['options'])):
                                            // Render as select
                                            ?>
                                            <select id="<?php echo esc_attr($field_id); ?>"
                                                name="<?php echo esc_attr($field_name_attr); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                                                <?php foreach ($field['options'] as $option_value => $option_label): ?>
                                                    <option value="<?php echo esc_attr($option_value); ?>" <?php selected($field_value, $option_value); ?>>
                                                        <?php echo esc_html($option_label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php
                                        else:
                                            // Render as text input
                                            ?>
                                            <input type="text" id="<?php echo esc_attr($field_id); ?>"
                                                name="<?php echo esc_attr($field_name_attr); ?>"
                                                value="<?php echo esc_attr($field_value); ?>" <?php echo isset($field['minlength']) ? 'minlength="' . esc_attr($field['minlength']) . '"' : ''; ?>                 <?php echo isset($field['maxlength']) ? 'maxlength="' . esc_attr($field['maxlength']) . '"' : ''; ?>                 <?php echo $field['required'] ? 'required' : ''; ?> class="regular-text" />
                                            <?php
                                        endif;

                                        if (!empty($field['description'])): ?>
                                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                                        <?php endif; ?>
                                        <?php
                                        break;
                                endswitch;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button(__('Save Settings', 'ai-website-manager-pro')); ?>
            </form>
        </div>

        <div class="ai-manager-pro-sidebar">
            <div class="postbox">
                <h3 class="hndle"><?php _e('Quick Actions', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <p>
                        <button type="button" class="button button-secondary" id="backup-settings-btn">
                            <?php _e('Create Backup', 'ai-website-manager-pro'); ?>
                        </button>
                    </p>
                    <p>
                        <button type="button" class="button button-secondary" id="export-settings-btn">
                            <?php _e('Export Settings', 'ai-website-manager-pro'); ?>
                        </button>
                    </p>
                    <p>
                        <label
                            for="import-settings-file"><?php _e('Import Settings:', 'ai-website-manager-pro'); ?></label>
                        <input type="file" id="import-settings-file" accept=".json,.backup" />
                        <button type="button" class="button button-secondary" id="import-settings-btn">
                            <?php _e('Import', 'ai-website-manager-pro'); ?>
                        </button>
                    </p>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><?php _e('System Status', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <ul>
                        <li>
                            <strong><?php _e('Plugin Version:', 'ai-website-manager-pro'); ?></strong>
                            <?php echo esc_html(AI_MANAGER_PRO_VERSION); ?>
                        </li>
                        <li>
                            <strong><?php _e('WordPress Version:', 'ai-website-manager-pro'); ?></strong>
                            <?php echo esc_html(get_bloginfo('version')); ?>
                        </li>
                        <li>
                            <strong><?php _e('PHP Version:', 'ai-website-manager-pro'); ?></strong>
                            <?php echo esc_html(PHP_VERSION); ?>
                        </li>
                        <li>
                            <strong><?php _e('Database Version:', 'ai-website-manager-pro'); ?></strong>
                            <?php
                            global $wpdb;
                            echo esc_html($wpdb->db_version());
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ai-manager-pro-admin-container {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    .ai-manager-pro-main-content {
        flex: 1;
    }

    .ai-manager-pro-sidebar {
        width: 300px;
    }

    .ai-manager-pro-sidebar .postbox {
        margin-bottom: 20px;
    }

    .required {
        color: #d63638;
    }

    .form-table th {
        width: 200px;
    }

    #import-settings-file {
        width: 100%;
        margin: 5px 0;
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Backup settings
        $('#backup-settings-btn').on('click', function () {
            const $btn = $(this);
            const originalText = $btn.text();

            $btn.prop('disabled', true).text('<?php _e('Creating...', 'ai-website-manager-pro'); ?>');

            $.post(aiManagerProAdmin.ajaxUrl, {
                action: 'ai_manager_pro_backup_settings',
                nonce: aiManagerProAdmin.nonce
            })
                .done(function (response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                })
                .fail(function () {
                    alert('Network error occurred');
                })
                .always(function () {
                    $btn.prop('disabled', false).text(originalText);
                });
        });

        // Export settings
        $('#export-settings-btn').on('click', function () {
            window.location.href = aiManagerProAdmin.ajaxUrl +
                '?action=ai_manager_pro_export_settings&nonce=' + aiManagerProAdmin.nonce;
        });

        // Import settings
        $('#import-settings-btn').on('click', function () {
            const fileInput = document.getElementById('import-settings-file');
            const file = fileInput.files[0];

            if (!file) {
                alert('<?php _e('Please select a file to import.', 'ai-website-manager-pro'); ?>');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'ai_manager_pro_import_settings');
            formData.append('nonce', aiManagerProAdmin.nonce);
            formData.append('settings_file', file);

            const $btn = $(this);
            const originalText = $btn.text();

            $btn.prop('disabled', true).text('<?php _e('Importing...', 'ai-website-manager-pro'); ?>');

            $.ajax({
                url: aiManagerProAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            })
                .done(function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                })
                .fail(function () {
                    alert('Network error occurred');
                })
                .always(function () {
                    $btn.prop('disabled', false).text(originalText);
                });
        });
    });
</script><?php
// I
nclude plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>