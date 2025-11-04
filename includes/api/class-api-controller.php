<?php
/**
 * Base API Controller
 *
 * @package AI_Manager_Pro
 * @subpackage API
 */

namespace AI_Manager_Pro\API;

/**
 * Base API Controller Class
 * 
 * Provides common functionality for all API controllers
 */
abstract class API_Controller
{

    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'ai-manager-pro/v1';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     * 
     * Must be implemented by child classes
     */
    abstract public function register_routes();

    /**
     * Check if user has permission
     *
     * @param string $capability Required capability
     * @return bool
     */
    protected function check_permission($capability = 'manage_options')
    {
        return current_user_can($capability);
    }

    /**
     * Validate nonce
     *
     * @param \WP_REST_Request $request Request object
     * @param string $action Nonce action
     * @return bool
     */
    protected function validate_nonce($request, $action = 'ai_manager_pro_nonce')
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce) {
            $nonce = $request->get_param('_wpnonce');
        }

        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Sanitize request data
     *
     * @param array $data Raw data
     * @param array $schema Data schema
     * @return array Sanitized data
     */
    protected function sanitize_data($data, $schema = [])
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (isset($schema[$key])) {
                $sanitized[$key] = $this->sanitize_field($value, $schema[$key]);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize individual field
     *
     * @param mixed $value Field value
     * @param array $field_schema Field schema
     * @return mixed Sanitized value
     */
    private function sanitize_field($value, $field_schema)
    {
        $type = $field_schema['type'] ?? 'string';

        switch ($type) {
            case 'string':
                return sanitize_text_field($value);
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'array':
                return is_array($value) ? array_map('sanitize_text_field', $value) : [];
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Return success response
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $status HTTP status code
     * @return \WP_REST_Response
     */
    protected function success_response($data = null, $message = 'Success', $status = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return new \WP_REST_Response($response, $status);
    }

    /**
     * Return error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param mixed $data Additional error data
     * @return \WP_REST_Response
     */
    protected function error_response($message = 'Error', $status = 400, $data = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return new \WP_REST_Response($response, $status);
    }

    /**
     * Validate required fields
     *
     * @param array $data Request data
     * @param array $required_fields Required field names
     * @return bool|\WP_REST_Response True if valid, error response if not
     */
    protected function validate_required_fields($data, $required_fields)
    {
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            return $this->error_response(
                sprintf(
                    __('Missing required fields: %s', 'ai-website-manager-pro'),
                    implode(', ', $missing_fields)
                ),
                400,
                ['missing_fields' => $missing_fields]
            );
        }

        return true;
    }

    /**
     * Log API request
     *
     * @param string $endpoint Endpoint name
     * @param string $method HTTP method
     * @param array $data Request data
     * @param mixed $response Response data
     */
    protected function log_request($endpoint, $method, $data = [], $response = null)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'AI Manager Pro API: %s %s - Data: %s - Response: %s',
                $method,
                $endpoint,
                json_encode($data),
                json_encode($response)
            ));
        }
    }

    /**
     * Get current user ID
     *
     * @return int
     */
    protected function get_current_user_id()
    {
        return get_current_user_id();
    }

    /**
     * Check if request is from admin
     *
     * @return bool
     */
    protected function is_admin_request()
    {
        return is_admin() || (defined('DOING_AJAX') && DOING_AJAX);
    }
}