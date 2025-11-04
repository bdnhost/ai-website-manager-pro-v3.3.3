<?php
/**
 * Settings Change Log
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use Monolog\Logger;

/**
 * Settings Change Log Class
 * 
 * Tracks and manages changes to settings
 */
class Settings_Change_Log
{

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ai_manager_pro_settings_log';
    }

    /**
     * Log setting change
     *
     * @param string $key Setting key
     * @param mixed $old_value Old value
     * @param mixed $new_value New value
     * @param string $action Action type (create, update, delete)
     * @return bool Success status
     */
    public function log_change($key, $old_value, $new_value, $action = 'update')
    {
        try {
            global $wpdb;

            $log_data = [
                'setting_key' => $key,
                'old_value' => $this->serialize_value($old_value),
                'new_value' => $this->serialize_value($new_value),
                'action' => $action,
                'user_id' => get_current_user_id(),
                'user_ip' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => current_time('mysql'),
                'context' => $this->get_context()
            ];

            $result = $wpdb->insert($this->table_name, $log_data);

            if ($result === false) {
                $this->logger->error('Failed to log setting change', [
                    'key' => $key,
                    'action' => $action,
                    'db_error' => $wpdb->last_error
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Error logging setting change', [
                'key' => $key,
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get change history for a setting
     *
     * @param string $key Setting key
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Change history
     */
    public function get_setting_history($key, $limit = 50, $offset = 0)
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE setting_key = %s 
             ORDER BY timestamp DESC 
             LIMIT %d OFFSET %d",
            $key,
            $limit,
            $offset
        ), ARRAY_A);

        return $this->process_log_results($results);
    }

    /**
     * Get recent changes
     *
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @param array $filters Filters to apply
     * @return array Recent changes
     */
    public function get_recent_changes($limit = 50, $offset = 0, $filters = [])
    {
        global $wpdb;

        $where_clauses = [];
        $where_values = [];

        // Apply filters
        if (!empty($filters['user_id'])) {
            $where_clauses[] = 'user_id = %d';
            $where_values[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where_clauses[] = 'action = %s';
            $where_values[] = $filters['action'];
        }

        if (!empty($filters['setting_key'])) {
            $where_clauses[] = 'setting_key LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($filters['setting_key']) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'timestamp >= %s';
            $where_values[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'timestamp <= %s';
            $where_values[] = $filters['date_to'];
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($sql, $where_values), ARRAY_A);

        return $this->process_log_results($results);
    }

    /**
     * Get change statistics
     *
     * @param array $filters Filters to apply
     * @return array Statistics
     */
    public function get_statistics($filters = [])
    {
        global $wpdb;

        $where_clauses = [];
        $where_values = [];

        // Apply date filter
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'timestamp >= %s';
            $where_values[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'timestamp <= %s';
            $where_values[] = $filters['date_to'];
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // Total changes
        $total_changes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}",
            $where_values
        ));

        // Changes by action
        $changes_by_action = $wpdb->get_results($wpdb->prepare(
            "SELECT action, COUNT(*) as count FROM {$this->table_name} {$where_sql} GROUP BY action",
            $where_values
        ), ARRAY_A);

        // Changes by user
        $changes_by_user = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, COUNT(*) as count FROM {$this->table_name} {$where_sql} GROUP BY user_id ORDER BY count DESC LIMIT 10",
            $where_values
        ), ARRAY_A);

        // Most changed settings
        $most_changed_settings = $wpdb->get_results($wpdb->prepare(
            "SELECT setting_key, COUNT(*) as count FROM {$this->table_name} {$where_sql} GROUP BY setting_key ORDER BY count DESC LIMIT 10",
            $where_values
        ), ARRAY_A);

        // Changes by day (last 30 days)
        $changes_by_day = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(timestamp) as date, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
             GROUP BY DATE(timestamp) 
             ORDER BY date DESC",
            []
        ), ARRAY_A);

        return [
            'total_changes' => (int) $total_changes,
            'changes_by_action' => $changes_by_action,
            'changes_by_user' => $this->enrich_user_data($changes_by_user),
            'most_changed_settings' => $most_changed_settings,
            'changes_by_day' => $changes_by_day
        ];
    }

    /**
     * Search change log
     *
     * @param string $search_term Search term
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Search results
     */
    public function search($search_term, $limit = 50, $offset = 0)
    {
        global $wpdb;

        $search_term = '%' . $wpdb->esc_like($search_term) . '%';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE setting_key LIKE %s 
                OR old_value LIKE %s 
                OR new_value LIKE %s 
             ORDER BY timestamp DESC 
             LIMIT %d OFFSET %d",
            $search_term,
            $search_term,
            $search_term,
            $limit,
            $offset
        ), ARRAY_A);

        return $this->process_log_results($results);
    }

    /**
     * Export change log
     *
     * @param array $filters Filters to apply
     * @param string $format Export format (csv, json)
     * @return string Export data
     */
    public function export($filters = [], $format = 'csv')
    {
        $changes = $this->get_recent_changes(10000, 0, $filters); // Large limit for export

        switch ($format) {
            case 'json':
                return json_encode($changes, JSON_PRETTY_PRINT);

            case 'csv':
            default:
                return $this->export_to_csv($changes);
        }
    }

    /**
     * Cleanup old log entries
     *
     * @param int $days_to_keep Number of days to keep
     * @return int Number of deleted entries
     */
    public function cleanup_old_entries($days_to_keep = 90)
    {
        global $wpdb;

        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));

        $deleted_count = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE timestamp < %s",
            $cutoff_date
        ));

        if ($deleted_count > 0) {
            $this->logger->info('Cleaned up old change log entries', [
                'deleted_count' => $deleted_count,
                'cutoff_date' => $cutoff_date
            ]);
        }

        return $deleted_count;
    }

    /**
     * Get total log entries count
     *
     * @param array $filters Filters to apply
     * @return int Total count
     */
    public function get_total_count($filters = [])
    {
        global $wpdb;

        $where_clauses = [];
        $where_values = [];

        if (!empty($filters['setting_key'])) {
            $where_clauses[] = 'setting_key LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($filters['setting_key']) . '%';
        }

        if (!empty($filters['user_id'])) {
            $where_clauses[] = 'user_id = %d';
            $where_values[] = $filters['user_id'];
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}",
            $where_values
        ));
    }

    /**
     * Process log results
     *
     * @param array $results Raw results from database
     * @return array Processed results
     */
    private function process_log_results($results)
    {
        $processed = [];

        foreach ($results as $result) {
            $processed[] = [
                'id' => $result['id'],
                'setting_key' => $result['setting_key'],
                'old_value' => $this->unserialize_value($result['old_value']),
                'new_value' => $this->unserialize_value($result['new_value']),
                'action' => $result['action'],
                'user_id' => $result['user_id'],
                'user_name' => $this->get_user_name($result['user_id']),
                'user_ip' => $result['user_ip'],
                'user_agent' => $result['user_agent'],
                'timestamp' => $result['timestamp'],
                'context' => json_decode($result['context'] ?? '{}', true),
                'formatted_timestamp' => $this->format_timestamp($result['timestamp'])
            ];
        }

        return $processed;
    }

    /**
     * Serialize value for storage
     *
     * @param mixed $value Value to serialize
     * @return string Serialized value
     */
    private function serialize_value($value)
    {
        if ($value === null) {
            return '';
        }

        return is_string($value) ? $value : json_encode($value);
    }

    /**
     * Unserialize value from storage
     *
     * @param string $value Serialized value
     * @return mixed Unserialized value
     */
    private function unserialize_value($value)
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Get client IP address
     *
     * @return string Client IP
     */
    private function get_client_ip()
    {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get context information
     *
     * @return string Context JSON
     */
    private function get_context()
    {
        $context = [
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'http_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'is_admin' => is_admin(),
            'is_ajax' => wp_doing_ajax(),
            'is_rest' => defined('REST_REQUEST') && REST_REQUEST
        ];

        return json_encode($context);
    }

    /**
     * Get user name by ID
     *
     * @param int $user_id User ID
     * @return string User name
     */
    private function get_user_name($user_id)
    {
        if (!$user_id) {
            return 'System';
        }

        $user = get_user_by('ID', $user_id);
        return $user ? $user->display_name : "User #{$user_id}";
    }

    /**
     * Enrich user data with names
     *
     * @param array $user_data User data array
     * @return array Enriched user data
     */
    private function enrich_user_data($user_data)
    {
        foreach ($user_data as &$data) {
            $data['user_name'] = $this->get_user_name($data['user_id']);
        }

        return $user_data;
    }

    /**
     * Format timestamp for display
     *
     * @param string $timestamp Timestamp
     * @return string Formatted timestamp
     */
    private function format_timestamp($timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($timestamp));
    }

    /**
     * Export to CSV format
     *
     * @param array $changes Changes data
     * @return string CSV data
     */
    private function export_to_csv($changes)
    {
        $csv = "ID,Setting Key,Old Value,New Value,Action,User,IP Address,Timestamp\n";

        foreach ($changes as $change) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $change['id'],
                str_replace('"', '""', $change['setting_key']),
                str_replace('"', '""', json_encode($change['old_value'])),
                str_replace('"', '""', json_encode($change['new_value'])),
                $change['action'],
                str_replace('"', '""', $change['user_name']),
                $change['user_ip'],
                $change['timestamp']
            );
        }

        return $csv;
    }
}