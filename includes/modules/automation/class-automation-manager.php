<?php
/**
 * Automation Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Automation
 */

namespace AI_Manager_Pro\Modules\Automation;

use AI_Manager_Pro\Modules\Content_Generation\Content_Generator;
use Monolog\Logger;

/**
 * Automation Manager Class
 */
class Automation_Manager {
    
    /**
     * Content Generator instance
     *
     * @var Content_Generator
     */
    private $content_generator;
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Rule engine instance
     *
     * @var Rule_Engine
     */
    private $rule_engine;
    
    /**
     * Scheduler instance
     *
     * @var Smart_Scheduler
     */
    private $scheduler;
    
    /**
     * Constructor
     *
     * @param Content_Generator $content_generator Content Generator instance
     * @param Logger $logger Logger instance
     */
    public function __construct(Content_Generator $content_generator, Logger $logger) {
        $this->content_generator = $content_generator;
        $this->logger = $logger;
        $this->rule_engine = new Rule_Engine($logger);
        $this->scheduler = new Smart_Scheduler($logger);
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Cron hooks
        add_action('ai_manager_pro_daily_task', [$this, 'run_daily_automation']);
        add_action('ai_manager_pro_hourly_task', [$this, 'run_hourly_automation']);
        add_action('ai_manager_pro_custom_task', [$this, 'run_custom_automation'], 10, 1);
        
        // AJAX hooks
        add_action('wp_ajax_ai_manager_pro_create_automation', [$this, 'handle_create_automation']);
        add_action('wp_ajax_ai_manager_pro_update_automation', [$this, 'handle_update_automation']);
        add_action('wp_ajax_ai_manager_pro_delete_automation', [$this, 'handle_delete_automation']);
        add_action('wp_ajax_ai_manager_pro_run_automation', [$this, 'handle_run_automation']);
        
        // Admin hooks
        add_action('admin_init', [$this, 'maybe_run_pending_automations']);
    }
    
    /**
     * Create automation task
     *
     * @param array $task_data Task data
     * @return int|false Task ID on success, false on failure
     */
    public function create_automation_task($task_data) {
        global $wpdb;
        
        // Validate task data
        $validation_result = $this->validate_task_data($task_data);
        if ($validation_result !== true) {
            $this->logger->error('Automation task validation failed', [
                'errors' => $validation_result
            ]);
            return false;
        }
        
        // Prepare task rules
        $rules = $this->prepare_task_rules($task_data);
        
        // Calculate next run time
        $next_run = $this->scheduler->calculate_next_run($task_data['schedule']);
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        
        $result = $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field($task_data['name']),
                'description' => sanitize_textarea_field($task_data['description'] ?? ''),
                'rules' => json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                'status' => 'active',
                'next_run' => $next_run,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            $this->logger->error('Failed to create automation task', [
                'name' => $task_data['name'],
                'error' => $wpdb->last_error
            ]);
            return false;
        }
        
        $task_id = $wpdb->insert_id;
        
        // Schedule WordPress cron if needed
        $this->schedule_wordpress_cron($task_id, $rules);
        
        $this->logger->info('Automation task created', [
            'task_id' => $task_id,
            'name' => $task_data['name'],
            'next_run' => $next_run,
            'user_id' => get_current_user_id()
        ]);
        
        return $task_id;
    }
    
    /**
     * Validate task data
     *
     * @param array $task_data Task data
     * @return bool|array True if valid, array of errors if invalid
     */
    private function validate_task_data($task_data) {
        $errors = [];
        
        // Required fields
        if (empty($task_data['name'])) {
            $errors[] = 'Task name is required';
        }
        
        if (empty($task_data['content_type'])) {
            $errors[] = 'Content type is required';
        }
        
        if (empty($task_data['schedule']['type'])) {
            $errors[] = 'Schedule type is required';
        }
        
        // Validate schedule
        $schedule_validation = $this->scheduler->validate_schedule($task_data['schedule']);
        if ($schedule_validation !== true) {
            $errors = array_merge($errors, $schedule_validation);
        }
        
        // Validate content generation options
        if (empty($task_data['content_options']['topic']) && empty($task_data['content_options']['topics'])) {
            $errors[] = 'At least one topic is required';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Prepare task rules from task data
     *
     * @param array $task_data Task data
     * @return array Prepared rules
     */
    private function prepare_task_rules($task_data) {
        return [
            'content_generation' => [
                'content_type' => $task_data['content_type'],
                'topics' => $task_data['content_options']['topics'] ?? [$task_data['content_options']['topic']],
                'brand_id' => $task_data['content_options']['brand_id'] ?? null,
                'ai_provider' => $task_data['content_options']['ai_provider'] ?? null,
                'ai_model' => $task_data['content_options']['ai_model'] ?? null,
                'auto_publish' => $task_data['content_options']['auto_publish'] ?? false,
                'post_status' => $task_data['content_options']['post_status'] ?? 'draft',
                'post_category' => $task_data['content_options']['post_category'] ?? null,
                'post_tags' => $task_data['content_options']['post_tags'] ?? []
            ],
            'schedule' => $task_data['schedule'],
            'conditions' => $task_data['conditions'] ?? [],
            'actions' => $task_data['actions'] ?? [],
            'notifications' => $task_data['notifications'] ?? []
        ];
    }
    
    /**
     * Schedule WordPress cron for task
     *
     * @param int $task_id Task ID
     * @param array $rules Task rules
     */
    private function schedule_wordpress_cron($task_id, $rules) {
        $schedule = $rules['schedule'];
        
        switch ($schedule['type']) {
            case 'recurring':
                if ($schedule['frequency'] === 'custom') {
                    // Schedule custom cron event
                    $hook_name = "ai_manager_pro_task_{$task_id}";
                    
                    if (!wp_next_scheduled($hook_name)) {
                        $next_run = $this->scheduler->calculate_next_run($schedule);
                        wp_schedule_event(strtotime($next_run), $schedule['interval'], $hook_name);
                        
                        // Add action for this specific task
                        add_action($hook_name, function() use ($task_id) {
                            $this->run_automation_task($task_id);
                        });
                    }
                }
                break;
                
            case 'one_time':
                // Schedule single event
                $hook_name = "ai_manager_pro_single_task_{$task_id}";
                $run_time = strtotime($schedule['datetime']);
                
                if ($run_time > time()) {
                    wp_schedule_single_event($run_time, $hook_name);
                    
                    // Add action for this specific task
                    add_action($hook_name, function() use ($task_id) {
                        $this->run_automation_task($task_id);
                    });
                }
                break;
        }
    }
    
    /**
     * Run automation task
     *
     * @param int $task_id Task ID
     * @return bool Success status
     */
    public function run_automation_task($task_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        
        // Get task
        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d AND status = 'active'",
            $task_id
        ));
        
        if (!$task) {
            $this->logger->warning('Automation task not found or inactive', [
                'task_id' => $task_id
            ]);
            return false;
        }
        
        $rules = json_decode($task->rules, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid task rules JSON', [
                'task_id' => $task_id,
                'error' => json_last_error_msg()
            ]);
            return false;
        }
        
        try {
            $this->logger->info('Starting automation task execution', [
                'task_id' => $task_id,
                'task_name' => $task->name
            ]);
            
            // Check conditions
            if (!$this->rule_engine->check_conditions($rules['conditions'] ?? [])) {
                $this->logger->info('Automation task conditions not met', [
                    'task_id' => $task_id
                ]);
                
                // Update next run time
                $this->update_task_next_run($task_id, $rules['schedule']);
                return true; // Not an error, just conditions not met
            }
            
            // Execute content generation
            $results = $this->execute_content_generation($rules['content_generation']);
            
            // Execute additional actions
            $this->execute_actions($rules['actions'] ?? [], $results);
            
            // Send notifications
            $this->send_notifications($rules['notifications'] ?? [], $results);
            
            // Update task
            $wpdb->update(
                $table_name,
                [
                    'last_run' => current_time('mysql'),
                    'next_run' => $this->scheduler->calculate_next_run($rules['schedule']),
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $task_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
            
            $this->logger->info('Automation task completed successfully', [
                'task_id' => $task_id,
                'results_count' => count($results)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Automation task execution failed', [
                'task_id' => $task_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update next run time even on failure
            $this->update_task_next_run($task_id, $rules['schedule']);
            
            return false;
        }
    }
    
    /**
     * Execute content generation
     *
     * @param array $content_rules Content generation rules
     * @return array Generation results
     */
    private function execute_content_generation($content_rules) {
        $results = [];
        $topics = $content_rules['topics'] ?? [];
        
        foreach ($topics as $topic) {
            $options = array_merge($content_rules, ['topic' => $topic]);
            unset($options['topics']); // Remove topics array to avoid confusion
            
            $result = $this->content_generator->generate_content($options);
            
            if ($result) {
                $results[] = [
                    'topic' => $topic,
                    'success' => true,
                    'post_id' => $result['post_id'],
                    'content' => $result['content']
                ];
            } else {
                $results[] = [
                    'topic' => $topic,
                    'success' => false,
                    'error' => 'Content generation failed'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Execute additional actions
     *
     * @param array $actions Actions to execute
     * @param array $results Content generation results
     */
    private function execute_actions($actions, $results) {
        foreach ($actions as $action) {
            try {
                switch ($action['type']) {
                    case 'email_notification':
                        $this->send_email_notification($action, $results);
                        break;
                        
                    case 'social_media_post':
                        $this->create_social_media_post($action, $results);
                        break;
                        
                    case 'webhook':
                        $this->trigger_webhook($action, $results);
                        break;
                        
                    default:
                        $this->logger->warning('Unknown action type', [
                            'action_type' => $action['type']
                        ]);
                }
            } catch (\Exception $e) {
                $this->logger->error('Action execution failed', [
                    'action_type' => $action['type'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Send notifications
     *
     * @param array $notifications Notification settings
     * @param array $results Content generation results
     */
    private function send_notifications($notifications, $results) {
        if (empty($notifications)) {
            return;
        }
        
        $successful_count = count(array_filter($results, function($r) { return $r['success']; }));
        $failed_count = count($results) - $successful_count;
        
        $message = sprintf(
            'Automation task completed. %d content pieces generated successfully, %d failed.',
            $successful_count,
            $failed_count
        );
        
        foreach ($notifications as $notification) {
            switch ($notification['type']) {
                case 'email':
                    wp_mail(
                        $notification['email'],
                        'AI Manager Pro - Automation Task Completed',
                        $message
                    );
                    break;
                    
                case 'admin_notice':
                    set_transient('ai_manager_pro_admin_notice', $message, 3600);
                    break;
            }
        }
    }
    
    /**
     * Update task next run time
     *
     * @param int $task_id Task ID
     * @param array $schedule Schedule configuration
     */
    private function update_task_next_run($task_id, $schedule) {
        global $wpdb;
        
        $next_run = $this->scheduler->calculate_next_run($schedule);
        
        if ($next_run) {
            $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
            
            $wpdb->update(
                $table_name,
                ['next_run' => $next_run],
                ['id' => $task_id],
                ['%s'],
                ['%d']
            );
        }
    }
    
    /**
     * Run daily automation tasks
     */
    public function run_daily_automation() {
        $this->run_scheduled_tasks('daily');
    }
    
    /**
     * Run hourly automation tasks
     */
    public function run_hourly_automation() {
        $this->run_scheduled_tasks('hourly');
    }
    
    /**
     * Run scheduled tasks by frequency
     *
     * @param string $frequency Frequency (daily, hourly, etc.)
     */
    private function run_scheduled_tasks($frequency) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        
        // Get tasks that should run now
        $tasks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE status = 'active' 
             AND next_run <= %s 
             AND JSON_EXTRACT(rules, '$.schedule.frequency') = %s",
            current_time('mysql'),
            $frequency
        ));
        
        foreach ($tasks as $task) {
            $this->run_automation_task($task->id);
        }
    }
    
    /**
     * Maybe run pending automations (called on admin_init)
     */
    public function maybe_run_pending_automations() {
        // Only run in admin and not too frequently
        if (!is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Check if we should run (every 5 minutes)
        $last_check = get_transient('ai_manager_pro_last_automation_check');
        if ($last_check && (time() - $last_check) < 300) {
            return;
        }
        
        set_transient('ai_manager_pro_last_automation_check', time(), 300);
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        
        // Get overdue tasks
        $overdue_tasks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE status = 'active' 
             AND next_run <= %s 
             LIMIT 5", // Limit to prevent performance issues
            current_time('mysql')
        ));
        
        foreach ($overdue_tasks as $task) {
            // Run in background to avoid blocking admin
            wp_schedule_single_event(time() + 1, 'ai_manager_pro_custom_task', [$task->id]);
        }
    }
    
    /**
     * Get automation tasks
     *
     * @param array $args Query arguments
     * @return array Tasks
     */
    public function get_automation_tasks($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 20,
            'offset' => 0,
            'status' => null,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_automation_tasks';
        $where_clauses = [];
        $where_values = [];
        
        if ($args['status']) {
            $where_clauses[] = "status = %s";
            $where_values[] = $args['status'];
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        $order_sql = sprintf(
            'ORDER BY %s %s',
            sanitize_sql_orderby($args['orderby']),
            $args['order'] === 'ASC' ? 'ASC' : 'DESC'
        );
        
        $limit_sql = $wpdb->prepare('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        
        $sql = "SELECT * FROM {$table_name} {$where_sql} {$order_sql} {$limit_sql}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Handle create automation AJAX request
     */
    public function handle_create_automation() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('ai_manager_manage_automation')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $task_data = [
                'name' => sanitize_text_field($_POST['name'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'content_type' => sanitize_text_field($_POST['content_type'] ?? ''),
                'schedule' => json_decode(stripslashes($_POST['schedule'] ?? '{}'), true),
                'content_options' => json_decode(stripslashes($_POST['content_options'] ?? '{}'), true),
                'conditions' => json_decode(stripslashes($_POST['conditions'] ?? '[]'), true),
                'actions' => json_decode(stripslashes($_POST['actions'] ?? '[]'), true),
                'notifications' => json_decode(stripslashes($_POST['notifications'] ?? '[]'), true)
            ];
            
            $task_id = $this->create_automation_task($task_data);
            
            if ($task_id) {
                wp_send_json_success([
                    'task_id' => $task_id,
                    'message' => 'Automation task created successfully'
                ]);
            } else {
                wp_send_json_error('Failed to create automation task');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Create automation AJAX error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json_error('An error occurred');
        }
    }
    
    /**
     * Handle run automation AJAX request
     */
    public function handle_run_automation() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('ai_manager_manage_automation')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $task_id = intval($_POST['task_id'] ?? 0);
        
        if (!$task_id) {
            wp_send_json_error('Invalid task ID');
        }
        
        $result = $this->run_automation_task($task_id);
        
        if ($result) {
            wp_send_json_success(['message' => 'Automation task executed successfully']);
        } else {
            wp_send_json_error('Failed to execute automation task');
        }
    }
}

