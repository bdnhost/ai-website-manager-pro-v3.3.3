<?php
/**
 * Logs & History Settings Page
 *
 * @package AI_Manager_Pro
 * @subpackage Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include plugin header
include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-header.php';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="ai-manager-pro-admin-container">
        <div class="ai-manager-pro-main-content">
            <div class="postbox">
                <h2 class="hndle"><?php _e('Recent Changes', 'ai-website-manager-pro'); ?></h2>
                <div class="inside">
                    <div class="log-filters">
                        <select id="log-filter-action">
                            <option value=""><?php _e('All Actions', 'ai-website-manager-pro'); ?></option>
                            <option value="create"><?php _e('Create', 'ai-website-manager-pro'); ?></option>
                            <option value="update"><?php _e('Update', 'ai-website-manager-pro'); ?></option>
                            <option value="delete"><?php _e('Delete', 'ai-website-manager-pro'); ?></option>
                        </select>

                        <input type="text" id="log-search"
                            placeholder="<?php _e('Search settings...', 'ai-website-manager-pro'); ?>" />

                        <button type="button" class="button" id="export-logs-btn">
                            <?php _e('Export Logs', 'ai-website-manager-pro'); ?>
                        </button>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Setting', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Action', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('User', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Date', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Details', 'ai-website-manager-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_changes)): ?>
                                <?php foreach ($recent_changes as $change): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($change['setting_key']); ?></code></td>
                                        <td>
                                            <span class="action-<?php echo esc_attr($change['action']); ?>">
                                                <?php echo esc_html(ucfirst($change['action'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($change['user_name']); ?></td>
                                        <td><?php echo esc_html($change['formatted_timestamp']); ?></td>
                                        <td>
                                            <button type="button" class="button button-small view-details-btn"
                                                data-change-id="<?php echo esc_attr($change['id']); ?>">
                                                <?php _e('View', 'ai-website-manager-pro'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <p><?php _e('No changes found.', 'ai-website-manager-pro'); ?></p>
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
                <h3 class="hndle"><?php _e('Statistics', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <?php if (!empty($statistics)): ?>
                        <h4><?php _e('Total Changes', 'ai-website-manager-pro'); ?></h4>
                        <p class="stat-number"><?php echo esc_html($statistics['total_changes']); ?></p>

                        <h4><?php _e('Changes by Action', 'ai-website-manager-pro'); ?></h4>
                        <ul>
                            <?php foreach ($statistics['changes_by_action'] as $action_stat): ?>
                                <li>
                                    <strong><?php echo esc_html(ucfirst($action_stat['action'])); ?>:</strong>
                                    <?php echo esc_html($action_stat['count']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <h4><?php _e('Most Active Users', 'ai-website-manager-pro'); ?></h4>
                        <ul>
                            <?php foreach (array_slice($statistics['changes_by_user'], 0, 5) as $user_stat): ?>
                                <li>
                                    <strong><?php echo esc_html($user_stat['user_name']); ?>:</strong>
                                    <?php echo esc_html($user_stat['count']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php _e('No statistics available.', 'ai-website-manager-pro'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><?php _e('Log Management', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <p><?php _e('Manage your log files and cleanup old entries.', 'ai-website-manager-pro'); ?></p>

                    <button type="button" class="button button-secondary" id="cleanup-logs-btn">
                        <?php _e('Cleanup Old Logs', 'ai-website-manager-pro'); ?>
                    </button>

                    <p class="description">
                        <?php _e('This will remove log entries older than 90 days.', 'ai-website-manager-pro'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .log-filters {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .log-filters select,
    .log-filters input {
        margin-right: 10px;
    }

    .action-create {
        color: #46b450;
        font-weight: bold;
    }

    .action-update {
        color: #0073aa;
        font-weight: bold;
    }

    .action-delete {
        color: #d63638;
        font-weight: bold;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #0073aa;
        margin: 10px 0;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Export logs handler
    $('#export-logs-btn').on('click', function() {
        window.location.href = ajaxurl + '?action=ai_manager_pro_export_logs&nonce=' + '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>';
    });

    // Cleanup logs handler
    $('#cleanup-logs-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to delete logs older than 90 days?', 'ai-website-manager-pro'); ?>')) {
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Cleaning...', 'ai-website-manager-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'ai_manager_pro_cleanup_logs',
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('<?php _e('Error cleaning logs', 'ai-website-manager-pro'); ?>: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Network error', 'ai-website-manager-pro'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('Cleanup Old Logs', 'ai-website-manager-pro'); ?>');
            }
        });
    });

    // View details handler
    $('.view-details-btn').on('click', function() {
        const changeId = $(this).data('change-id');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'ai_manager_pro_get_log_details',
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>',
                change_id: changeId
            },
            success: function(response) {
                if (response.success) {
                    const change = response.data;
                    let html = '<div style="max-width: 600px;">';
                    html += '<h3><?php _e('Change Details', 'ai-website-manager-pro'); ?></h3>';
                    html += '<p><strong><?php _e('Setting', 'ai-website-manager-pro'); ?>:</strong> ' + change.setting_key + '</p>';
                    html += '<p><strong><?php _e('Action', 'ai-website-manager-pro'); ?>:</strong> ' + change.action + '</p>';
                    html += '<p><strong><?php _e('User', 'ai-website-manager-pro'); ?>:</strong> ' + change.user_name + '</p>';
                    html += '<p><strong><?php _e('Date', 'ai-website-manager-pro'); ?>:</strong> ' + change.formatted_timestamp + '</p>';
                    html += '<p><strong><?php _e('Old Value', 'ai-website-manager-pro'); ?>:</strong><br><pre>' + JSON.stringify(change.old_value, null, 2) + '</pre></p>';
                    html += '<p><strong><?php _e('New Value', 'ai-website-manager-pro'); ?>:</strong><br><pre>' + JSON.stringify(change.new_value, null, 2) + '</pre></p>';
                    html += '</div>';

                    // Show in modal or alert
                    if (typeof wp !== 'undefined' && wp.template) {
                        // Use WP modal if available
                        $(html).dialog({
                            modal: true,
                            width: 700,
                            close: function() { $(this).dialog('destroy').remove(); }
                        });
                    } else {
                        alert(html.replace(/<[^>]*>/g, '\n'));
                    }
                } else {
                    alert('<?php _e('Error loading details', 'ai-website-manager-pro'); ?>');
                }
            }
        });
    });

    // Search and filter functionality
    let searchTimeout;
    $('#log-search, #log-filter-action').on('change keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            const search = $('#log-search').val();
            const action = $('#log-filter-action').val();

            // Filter table rows
            $('tbody tr').each(function() {
                const $row = $(this);
                const settingKey = $row.find('td:first code').text().toLowerCase();
                const rowAction = $row.find('td:nth-child(2) span').text().toLowerCase();

                let showRow = true;

                // Apply search filter
                if (search && !settingKey.includes(search.toLowerCase())) {
                    showRow = false;
                }

                // Apply action filter
                if (action && !rowAction.includes(action.toLowerCase())) {
                    showRow = false;
                }

                $row.toggle(showRow);
            });
        }, 300);
    });
});
</script>

<?php
// Include plugin footer
include AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>