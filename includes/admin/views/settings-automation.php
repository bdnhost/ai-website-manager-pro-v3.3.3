<?php
/**
 * Automation Settings Page
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
                <h2 class="hndle"><?php _e('Automation Tasks', 'ai-website-manager-pro'); ?></h2>
                <div class="inside">
                    <p><?php _e('Manage automated content generation and publishing tasks.', 'ai-website-manager-pro'); ?>
                    </p>

                    <div class="automation-actions">
                        <button type="button" class="button button-primary" id="create-automation-btn">
                            <?php _e('Create New Task', 'ai-website-manager-pro'); ?>
                        </button>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Schedule', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Status', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Last Run', 'ai-website-manager-pro'); ?></th>
                                <th><?php _e('Actions', 'ai-website-manager-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $automation_tasks = get_option('ai_manager_pro_automation_tasks', []);
                            if (!empty($automation_tasks)):
                                foreach ($automation_tasks as $task_id => $task):
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($task['name']); ?></strong>
                                            <div class="task-description">
                                                <?php echo esc_html($task['description'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $schedule_text = '';
                                            switch ($task['schedule']) {
                                                case 'hourly':
                                                    $schedule_text = __('Every hour', 'ai-website-manager-pro');
                                                    break;
                                                case 'daily':
                                                    $schedule_text = __('Daily', 'ai-website-manager-pro');
                                                    break;
                                                case 'weekly':
                                                    $schedule_text = __('Weekly', 'ai-website-manager-pro');
                                                    break;
                                                default:
                                                    $schedule_text = esc_html($task['schedule']);
                                            }
                                            echo $schedule_text;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($task['enabled']): ?>
                                                <span
                                                    class="status-badge active"><?php _e('Active', 'ai-website-manager-pro'); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="status-badge inactive"><?php _e('Inactive', 'ai-website-manager-pro'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (!empty($task['last_run'])) {
                                                echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($task['last_run'])));
                                            } else {
                                                _e('Never', 'ai-website-manager-pro');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small edit-task-btn"
                                                data-task-id="<?php echo esc_attr($task_id); ?>">
                                                <?php _e('Edit', 'ai-website-manager-pro'); ?>
                                            </button>
                                            <button type="button" class="button button-small toggle-task-btn"
                                                data-task-id="<?php echo esc_attr($task_id); ?>"
                                                data-enabled="<?php echo $task['enabled'] ? '1' : '0'; ?>">
                                                <?php echo $task['enabled'] ? __('Disable', 'ai-website-manager-pro') : __('Enable', 'ai-website-manager-pro'); ?>
                                            </button>
                                            <button type="button" class="button button-small run-now-btn"
                                                data-task-id="<?php echo esc_attr($task_id); ?>">
                                                <?php _e('Run Now', 'ai-website-manager-pro'); ?>
                                            </button>
                                            <button type="button" class="button button-small button-link-delete delete-task-btn"
                                                data-task-id="<?php echo esc_attr($task_id); ?>">
                                                <?php _e('Delete', 'ai-website-manager-pro'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                endforeach;
                            else:
                                ?>
                                <tr>
                                    <td colspan="5">
                                        <p><?php _e('No automation tasks found. Create your first task to get started.', 'ai-website-manager-pro'); ?>
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
                <h3 class="hndle"><?php _e('Automation Settings', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <form method="post" id="automation-settings-form">
                        <?php wp_nonce_field('ai_manager_pro_automation_settings', 'automation_nonce'); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enable_automation">
                                        <?php _e('Enable Automation', 'ai-website-manager-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enable_automation" name="enable_automation" value="1"
                                        checked />
                                    <p class="description">
                                        <?php _e('Enable automated content generation', 'ai-website-manager-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="max_concurrent_tasks">
                                        <?php _e('Max Concurrent Tasks', 'ai-website-manager-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" id="max_concurrent_tasks" name="max_concurrent_tasks" value="3"
                                        min="1" max="10" class="small-text" />
                                    <p class="description">
                                        <?php _e('Maximum number of tasks to run simultaneously', 'ai-website-manager-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save Settings', 'ai-website-manager-pro')); ?>
                    </form>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><?php _e('Quick Stats', 'ai-website-manager-pro'); ?></h3>
                <div class="inside">
                    <ul>
                        <li><strong><?php _e('Total Tasks:', 'ai-website-manager-pro'); ?></strong> 0</li>
                        <li><strong><?php _e('Active Tasks:', 'ai-website-manager-pro'); ?></strong> 0</li>
                        <li><strong><?php _e('Completed Today:', 'ai-website-manager-pro'); ?></strong> 0</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.task-description {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-badge.active {
    background: #d1e7dd;
    color: #0f5132;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.automation-actions {
    margin-bottom: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle task status
    $('.toggle-task-btn').on('click', function() {
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        const enabled = $btn.data('enabled') === '1';
        const newStatus = enabled ? '0' : '1';
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_toggle_automation',
                task_id: taskId,
                enabled: newStatus,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Failed to toggle task status', 'ai-website-manager-pro'); ?>: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Network error occurred', 'ai-website-manager-pro'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Run task now
    $('.run-now-btn').on('click', function() {
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        
        $btn.prop('disabled', true).text('<?php _e('Running...', 'ai-website-manager-pro'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_run_task_now',
                task_id: taskId,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Task executed successfully!', 'ai-website-manager-pro'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Task execution failed', 'ai-website-manager-pro'); ?>: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Network error occurred', 'ai-website-manager-pro'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('Run Now', 'ai-website-manager-pro'); ?>');
            }
        });
    });
    
    // Delete task
    $('.delete-task-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to delete this task?', 'ai-website-manager-pro'); ?>')) {
            return;
        }
        
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_delete_automation',
                task_id: taskId,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').fadeOut();
                } else {
                    alert('<?php _e('Failed to delete task', 'ai-website-manager-pro'); ?>: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Network error occurred', 'ai-website-manager-pro'); ?>');
            }
        });
    });
});
</script>

<?php
// Include plugin footer
include_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/admin/views/plugin-footer.php';
?>

<style>
    .automation-actions {
        margin-bottom: 20px;
    }
</style>