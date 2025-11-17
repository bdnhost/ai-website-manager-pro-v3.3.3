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

<!-- Automation Task Modal -->
<div id="automation-task-modal" style="display: none;">
    <div class="automation-modal-content">
        <h2 id="modal-title"><?php _e('Create New Automation Task', 'ai-website-manager-pro'); ?></h2>
        <form id="automation-task-form">
            <input type="hidden" id="task_id" name="task_id" value="">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="task_name"><?php _e('Task Name', 'ai-website-manager-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="task_name" name="task_name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="task_description"><?php _e('Description', 'ai-website-manager-pro'); ?></label>
                    </th>
                    <td>
                        <textarea id="task_description" name="task_description" class="large-text" rows="3"></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="task_schedule"><?php _e('Schedule', 'ai-website-manager-pro'); ?></label>
                    </th>
                    <td>
                        <select id="task_schedule" name="task_schedule" class="regular-text">
                            <option value="hourly"><?php _e('Every Hour', 'ai-website-manager-pro'); ?></option>
                            <option value="daily"><?php _e('Daily', 'ai-website-manager-pro'); ?></option>
                            <option value="weekly"><?php _e('Weekly', 'ai-website-manager-pro'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="task_enabled"><?php _e('Status', 'ai-website-manager-pro'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="task_enabled" name="task_enabled" value="1" checked>
                            <?php _e('Enable this task', 'ai-website-manager-pro'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <div class="modal-actions">
                <button type="button" class="button button-primary" onclick="saveAutomationTask()">
                    <?php _e('Save Task', 'ai-website-manager-pro'); ?>
                </button>
                <button type="button" class="button" onclick="closeAutomationModal()">
                    <?php _e('Cancel', 'ai-website-manager-pro'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#automation-task-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.automation-modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 4px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.automation-modal-content h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

.modal-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.modal-actions .button {
    margin-left: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Create new task button
    $('#create-automation-btn').on('click', function() {
        openAutomationModal();
    });

    // Edit task button
    $('.edit-task-btn').on('click', function() {
        const taskId = $(this).data('task-id');
        openAutomationModal(taskId);
    });

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

// Open automation modal
function openAutomationModal(taskId) {
    const modal = document.getElementById('automation-task-modal');
    const form = document.getElementById('automation-task-form');

    // Reset form
    form.reset();

    if (taskId) {
        // Edit mode - load task data
        document.getElementById('modal-title').textContent = '<?php _e('Edit Automation Task', 'ai-website-manager-pro'); ?>';
        document.getElementById('task_id').value = taskId;

        // Load task data via AJAX
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_manager_pro_get_automation_task',
                task_id: taskId,
                nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const task = response.data;
                    document.getElementById('task_name').value = task.name || '';
                    document.getElementById('task_description').value = task.description || '';
                    document.getElementById('task_schedule').value = task.schedule || 'daily';
                    document.getElementById('task_enabled').checked = task.enabled || false;
                }
            }
        });
    } else {
        // Create mode
        document.getElementById('modal-title').textContent = '<?php _e('Create New Automation Task', 'ai-website-manager-pro'); ?>';
        document.getElementById('task_id').value = '';
    }

    modal.style.display = 'flex';
}

// Close automation modal
function closeAutomationModal() {
    const modal = document.getElementById('automation-task-modal');
    modal.style.display = 'none';
}

// Save automation task
function saveAutomationTask() {
    const taskId = document.getElementById('task_id').value;
    const taskData = {
        name: document.getElementById('task_name').value,
        description: document.getElementById('task_description').value,
        schedule: document.getElementById('task_schedule').value,
        enabled: document.getElementById('task_enabled').checked
    };

    if (!taskData.name) {
        alert('<?php _e('Task name is required', 'ai-website-manager-pro'); ?>');
        return;
    }

    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.textContent;
    saveBtn.textContent = '<?php _e('Saving...', 'ai-website-manager-pro'); ?>';
    saveBtn.disabled = true;

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'ai_manager_pro_save_automation',
            task_id: taskId,
            task_data: taskData,
            nonce: '<?php echo wp_create_nonce('ai_manager_pro_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                alert('<?php _e('Task saved successfully!', 'ai-website-manager-pro'); ?>');
                closeAutomationModal();
                location.reload();
            } else {
                alert('<?php _e('Error', 'ai-website-manager-pro'); ?>: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            alert('<?php _e('Network error', 'ai-website-manager-pro'); ?>: ' + error);
        },
        complete: function() {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    });
}

// Close modal when clicking outside
jQuery(document).on('click', '#automation-task-modal', function(e) {
    if (e.target.id === 'automation-task-modal') {
        closeAutomationModal();
    }
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