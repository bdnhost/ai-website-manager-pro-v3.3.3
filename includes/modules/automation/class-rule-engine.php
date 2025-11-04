<?php
/**
 * Rule Engine
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Automation
 */

namespace AI_Manager_Pro\Modules\Automation;

use Monolog\Logger;

/**
 * Rule Engine Class
 */
class Rule_Engine {
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }
    
    /**
     * Check conditions
     *
     * @param array $conditions Conditions to check
     * @return bool Whether all conditions are met
     */
    public function check_conditions($conditions) {
        if (empty($conditions)) {
            return true; // No conditions means always run
        }
        
        foreach ($conditions as $condition) {
            if (!$this->check_single_condition($condition)) {
                $this->logger->debug('Condition not met', [
                    'condition' => $condition
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check single condition
     *
     * @param array $condition Condition to check
     * @return bool Whether condition is met
     */
    private function check_single_condition($condition) {
        $type = $condition['type'] ?? '';
        
        switch ($type) {
            case 'time_range':
                return $this->check_time_range_condition($condition);
                
            case 'day_of_week':
                return $this->check_day_of_week_condition($condition);
                
            case 'post_count':
                return $this->check_post_count_condition($condition);
                
            case 'last_post_age':
                return $this->check_last_post_age_condition($condition);
                
            case 'traffic_threshold':
                return $this->check_traffic_threshold_condition($condition);
                
            case 'custom_field':
                return $this->check_custom_field_condition($condition);
                
            case 'user_role':
                return $this->check_user_role_condition($condition);
                
            case 'site_option':
                return $this->check_site_option_condition($condition);
                
            default:
                $this->logger->warning('Unknown condition type', [
                    'type' => $type
                ]);
                return false;
        }
    }
    
    /**
     * Check time range condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_time_range_condition($condition) {
        $start_time = $condition['start_time'] ?? '00:00';
        $end_time = $condition['end_time'] ?? '23:59';
        $timezone = $condition['timezone'] ?? wp_timezone_string();
        
        try {
            $tz = new \DateTimeZone($timezone);
            $now = new \DateTime('now', $tz);
            $current_time = $now->format('H:i');
            
            // Handle overnight ranges (e.g., 22:00 to 06:00)
            if ($start_time > $end_time) {
                return $current_time >= $start_time || $current_time <= $end_time;
            } else {
                return $current_time >= $start_time && $current_time <= $end_time;
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Time range condition error', [
                'error' => $e->getMessage(),
                'condition' => $condition
            ]);
            return false;
        }
    }
    
    /**
     * Check day of week condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_day_of_week_condition($condition) {
        $allowed_days = $condition['days'] ?? [];
        $timezone = $condition['timezone'] ?? wp_timezone_string();
        
        if (empty($allowed_days)) {
            return true;
        }
        
        try {
            $tz = new \DateTimeZone($timezone);
            $now = new \DateTime('now', $tz);
            $current_day = strtolower($now->format('l')); // Full day name in lowercase
            
            return in_array($current_day, array_map('strtolower', $allowed_days));
            
        } catch (\Exception $e) {
            $this->logger->error('Day of week condition error', [
                'error' => $e->getMessage(),
                'condition' => $condition
            ]);
            return false;
        }
    }
    
    /**
     * Check post count condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_post_count_condition($condition) {
        $operator = $condition['operator'] ?? '>=';
        $threshold = intval($condition['threshold'] ?? 0);
        $post_type = $condition['post_type'] ?? 'post';
        $period = $condition['period'] ?? 'today'; // today, week, month
        
        // Calculate date range
        $date_query = $this->get_date_query_for_period($period);
        
        // Count posts
        $query_args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => $date_query
        ];
        
        $query = new \WP_Query($query_args);
        $post_count = $query->found_posts;
        
        return $this->compare_values($post_count, $operator, $threshold);
    }
    
    /**
     * Check last post age condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_last_post_age_condition($condition) {
        $operator = $condition['operator'] ?? '>=';
        $threshold = intval($condition['threshold'] ?? 24); // hours
        $post_type = $condition['post_type'] ?? 'post';
        
        // Get latest post
        $latest_post = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($latest_post)) {
            // No posts exist, condition is met if we're checking for old posts
            return in_array($operator, ['>', '>=']);
        }
        
        $post_date = strtotime($latest_post[0]->post_date);
        $hours_since = (time() - $post_date) / 3600;
        
        return $this->compare_values($hours_since, $operator, $threshold);
    }
    
    /**
     * Check traffic threshold condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_traffic_threshold_condition($condition) {
        $operator = $condition['operator'] ?? '>=';
        $threshold = intval($condition['threshold'] ?? 100);
        $period = $condition['period'] ?? 'today';
        
        // This would integrate with analytics plugins or services
        // For now, we'll use a simple page view counter if available
        $traffic_count = $this->get_traffic_count($period);
        
        return $this->compare_values($traffic_count, $operator, $threshold);
    }
    
    /**
     * Check custom field condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_custom_field_condition($condition) {
        $field_name = $condition['field_name'] ?? '';
        $operator = $condition['operator'] ?? '==';
        $expected_value = $condition['value'] ?? '';
        $scope = $condition['scope'] ?? 'option'; // option, user_meta, post_meta
        
        if (empty($field_name)) {
            return false;
        }
        
        switch ($scope) {
            case 'option':
                $actual_value = get_option($field_name);
                break;
                
            case 'user_meta':
                $user_id = $condition['user_id'] ?? get_current_user_id();
                $actual_value = get_user_meta($user_id, $field_name, true);
                break;
                
            case 'post_meta':
                $post_id = $condition['post_id'] ?? get_the_ID();
                $actual_value = get_post_meta($post_id, $field_name, true);
                break;
                
            default:
                return false;
        }
        
        return $this->compare_values($actual_value, $operator, $expected_value);
    }
    
    /**
     * Check user role condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_user_role_condition($condition) {
        $required_roles = $condition['roles'] ?? [];
        $user_id = $condition['user_id'] ?? get_current_user_id();
        
        if (empty($required_roles) || !$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user_roles = $user->roles;
        
        // Check if user has any of the required roles
        return !empty(array_intersect($user_roles, $required_roles));
    }
    
    /**
     * Check site option condition
     *
     * @param array $condition Condition data
     * @return bool Whether condition is met
     */
    private function check_site_option_condition($condition) {
        $option_name = $condition['option_name'] ?? '';
        $operator = $condition['operator'] ?? '==';
        $expected_value = $condition['value'] ?? '';
        
        if (empty($option_name)) {
            return false;
        }
        
        $actual_value = get_option($option_name);
        
        return $this->compare_values($actual_value, $operator, $expected_value);
    }
    
    /**
     * Compare values using operator
     *
     * @param mixed $actual_value Actual value
     * @param string $operator Comparison operator
     * @param mixed $expected_value Expected value
     * @return bool Comparison result
     */
    private function compare_values($actual_value, $operator, $expected_value) {
        switch ($operator) {
            case '==':
            case 'equals':
                return $actual_value == $expected_value;
                
            case '!=':
            case 'not_equals':
                return $actual_value != $expected_value;
                
            case '>':
            case 'greater_than':
                return $actual_value > $expected_value;
                
            case '>=':
            case 'greater_than_or_equal':
                return $actual_value >= $expected_value;
                
            case '<':
            case 'less_than':
                return $actual_value < $expected_value;
                
            case '<=':
            case 'less_than_or_equal':
                return $actual_value <= $expected_value;
                
            case 'contains':
                return strpos($actual_value, $expected_value) !== false;
                
            case 'not_contains':
                return strpos($actual_value, $expected_value) === false;
                
            case 'starts_with':
                return str_starts_with($actual_value, $expected_value);
                
            case 'ends_with':
                return str_ends_with($actual_value, $expected_value);
                
            case 'in':
                return is_array($expected_value) && in_array($actual_value, $expected_value);
                
            case 'not_in':
                return is_array($expected_value) && !in_array($actual_value, $expected_value);
                
            default:
                $this->logger->warning('Unknown comparison operator', [
                    'operator' => $operator
                ]);
                return false;
        }
    }
    
    /**
     * Get date query for period
     *
     * @param string $period Period (today, week, month)
     * @return array Date query
     */
    private function get_date_query_for_period($period) {
        switch ($period) {
            case 'today':
                return [
                    [
                        'year' => date('Y'),
                        'month' => date('n'),
                        'day' => date('j')
                    ]
                ];
                
            case 'week':
                return [
                    [
                        'after' => '1 week ago'
                    ]
                ];
                
            case 'month':
                return [
                    [
                        'after' => '1 month ago'
                    ]
                ];
                
            case 'year':
                return [
                    [
                        'after' => '1 year ago'
                    ]
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Get traffic count for period
     *
     * @param string $period Period
     * @return int Traffic count
     */
    private function get_traffic_count($period) {
        // This is a placeholder implementation
        // In a real implementation, this would integrate with:
        // - Google Analytics
        // - WordPress analytics plugins
        // - Custom tracking systems
        
        $transient_key = "ai_manager_pro_traffic_{$period}";
        $traffic_count = get_transient($transient_key);
        
        if ($traffic_count === false) {
            // Simulate traffic data for demo purposes
            switch ($period) {
                case 'today':
                    $traffic_count = rand(50, 500);
                    break;
                case 'week':
                    $traffic_count = rand(300, 3000);
                    break;
                case 'month':
                    $traffic_count = rand(1000, 10000);
                    break;
                default:
                    $traffic_count = 0;
            }
            
            // Cache for 1 hour
            set_transient($transient_key, $traffic_count, 3600);
        }
        
        return intval($traffic_count);
    }
    
    /**
     * Validate condition
     *
     * @param array $condition Condition to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_condition($condition) {
        $errors = [];
        
        if (empty($condition['type'])) {
            $errors[] = 'Condition type is required';
            return $errors;
        }
        
        $type = $condition['type'];
        
        switch ($type) {
            case 'time_range':
                if (empty($condition['start_time']) || empty($condition['end_time'])) {
                    $errors[] = 'Start time and end time are required for time range condition';
                }
                break;
                
            case 'day_of_week':
                if (empty($condition['days']) || !is_array($condition['days'])) {
                    $errors[] = 'Days array is required for day of week condition';
                }
                break;
                
            case 'post_count':
            case 'last_post_age':
            case 'traffic_threshold':
                if (!isset($condition['threshold']) || !is_numeric($condition['threshold'])) {
                    $errors[] = 'Numeric threshold is required';
                }
                if (empty($condition['operator'])) {
                    $errors[] = 'Operator is required';
                }
                break;
                
            case 'custom_field':
                if (empty($condition['field_name'])) {
                    $errors[] = 'Field name is required for custom field condition';
                }
                break;
                
            case 'user_role':
                if (empty($condition['roles']) || !is_array($condition['roles'])) {
                    $errors[] = 'Roles array is required for user role condition';
                }
                break;
                
            case 'site_option':
                if (empty($condition['option_name'])) {
                    $errors[] = 'Option name is required for site option condition';
                }
                break;
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get available condition types
     *
     * @return array Available condition types
     */
    public function get_available_condition_types() {
        return [
            'time_range' => [
                'name' => 'Time Range',
                'description' => 'Check if current time is within specified range',
                'fields' => ['start_time', 'end_time', 'timezone']
            ],
            'day_of_week' => [
                'name' => 'Day of Week',
                'description' => 'Check if current day is in allowed days',
                'fields' => ['days', 'timezone']
            ],
            'post_count' => [
                'name' => 'Post Count',
                'description' => 'Check number of posts in specified period',
                'fields' => ['operator', 'threshold', 'post_type', 'period']
            ],
            'last_post_age' => [
                'name' => 'Last Post Age',
                'description' => 'Check age of most recent post',
                'fields' => ['operator', 'threshold', 'post_type']
            ],
            'traffic_threshold' => [
                'name' => 'Traffic Threshold',
                'description' => 'Check if traffic meets threshold',
                'fields' => ['operator', 'threshold', 'period']
            ],
            'custom_field' => [
                'name' => 'Custom Field',
                'description' => 'Check custom field value',
                'fields' => ['field_name', 'operator', 'value', 'scope']
            ],
            'user_role' => [
                'name' => 'User Role',
                'description' => 'Check if user has required role',
                'fields' => ['roles', 'user_id']
            ],
            'site_option' => [
                'name' => 'Site Option',
                'description' => 'Check WordPress option value',
                'fields' => ['option_name', 'operator', 'value']
            ]
        ];
    }
}

