<?php
/**
 * Security Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Security
 */

namespace AI_Manager_Pro\Modules\Security;

use Monolog\Logger;

/**
 * Security Manager Class
 */
class Security_Manager {
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Encryption key
     *
     * @var string
     */
    private $encryption_key;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->encryption_key = $this->get_encryption_key();
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Log security events
        add_action('wp_login', [$this, 'log_user_login'], 10, 2);
        add_action('wp_login_failed', [$this, 'log_failed_login']);
    }
    
    /**
     * Get or create encryption key
     *
     * @return string Encryption key
     */
    private function get_encryption_key() {
        // Try to get from wp-config.php constant
        if (defined('AI_MANAGER_PRO_ENCRYPTION_KEY')) {
            return AI_MANAGER_PRO_ENCRYPTION_KEY;
        }
        
        // Try to get from environment variable
        $env_key = getenv('AI_MANAGER_PRO_ENCRYPTION_KEY');
        if ($env_key) {
            return $env_key;
        }
        
        // Get or create from database
        $key = get_option('ai_manager_pro_encryption_key');
        if (!$key) {
            $key = $this->generate_encryption_key();
            update_option('ai_manager_pro_encryption_key', $key);
            
            $this->logger->info('Generated new encryption key');
        }
        
        return $key;
    }
    
    /**
     * Generate a new encryption key
     *
     * @return string Generated key
     */
    private function generate_encryption_key() {
        return base64_encode(random_bytes(32));
    }
    
    /**
     * Encrypt API key
     *
     * @param string $api_key API key to encrypt
     * @return string Encrypted API key
     */
    public function encrypt_api_key($api_key) {
        if (empty($api_key)) {
            return '';
        }
        
        try {
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($api_key, 'AES-256-CBC', base64_decode($this->encryption_key), 0, $iv);
            
            if ($encrypted === false) {
                $this->logger->error('Failed to encrypt API key');
                return $api_key; // Return original if encryption fails
            }
            
            return base64_encode($iv . $encrypted);
            
        } catch (\Exception $e) {
            $this->logger->error('Error encrypting API key', [
                'error' => $e->getMessage()
            ]);
            return $api_key; // Return original if encryption fails
        }
    }
    
    /**
     * Decrypt API key
     *
     * @param string $encrypted_key Encrypted API key
     * @return string Decrypted API key
     */
    public function decrypt_api_key($encrypted_key) {
        if (empty($encrypted_key)) {
            return '';
        }
        
        try {
            $data = base64_decode($encrypted_key);
            
            if ($data === false || strlen($data) < 16) {
                // Assume it's not encrypted (backward compatibility)
                return $encrypted_key;
            }
            
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', base64_decode($this->encryption_key), 0, $iv);
            
            if ($decrypted === false) {
                $this->logger->warning('Failed to decrypt API key, assuming not encrypted');
                return $encrypted_key; // Return original if decryption fails
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            $this->logger->error('Error decrypting API key', [
                'error' => $e->getMessage()
            ]);
            return $encrypted_key; // Return original if decryption fails
        }
    }
    
    /**
     * Sanitize and validate input
     *
     * @param mixed $input Input to sanitize
     * @param string $type Input type (text, email, url, etc.)
     * @return mixed Sanitized input
     */
    public function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url_raw($input);
            case 'textarea':
                return sanitize_textarea_field($input);
            case 'key':
                return sanitize_key($input);
            case 'slug':
                return sanitize_title($input);
            case 'html':
                return wp_kses_post($input);
            case 'int':
                return intval($input);
            case 'float':
                return floatval($input);
            case 'bool':
                return (bool) $input;
            case 'json':
                return $this->sanitize_json($input);
            case 'text':
            default:
                return sanitize_text_field($input);
        }
    }
    
    /**
     * Sanitize JSON input
     *
     * @param string $json JSON string
     * @return string|false Sanitized JSON or false on error
     */
    private function sanitize_json($json) {
        if (empty($json)) {
            return '';
        }
        
        // Decode JSON to validate structure
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Invalid JSON input', [
                'error' => json_last_error_msg()
            ]);
            return false;
        }
        
        // Re-encode to ensure proper formatting
        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * Validate JSON against schema
     *
     * @param string $json JSON string
     * @param array $schema JSON schema
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_json_schema($json, $schema) {
        if (empty($json)) {
            return ['JSON is required'];
        }
        
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['Invalid JSON: ' . json_last_error_msg()];
        }
        
        // Use JsonSchema library if available
        if (class_exists('JsonSchema\\Validator')) {
            try {
                $validator = new \JsonSchema\Validator();
                $validator->validate($decoded, $schema);
                
                if ($validator->isValid()) {
                    return true;
                } else {
                    $errors = [];
                    foreach ($validator->getErrors() as $error) {
                        $errors[] = sprintf('[%s] %s', $error['property'], $error['message']);
                    }
                    return $errors;
                }
            } catch (\Exception $e) {
                $this->logger->error('JSON schema validation error', [
                    'error' => $e->getMessage()
                ]);
                return ['Schema validation failed'];
            }
        }
        
        // Basic validation if JsonSchema library not available
        return $this->basic_schema_validation($decoded, $schema);
    }
    
    /**
     * Basic schema validation
     *
     * @param array $data Data to validate
     * @param array $schema Schema definition
     * @return bool|array True if valid, array of errors if invalid
     */
    private function basic_schema_validation($data, $schema) {
        $errors = [];
        
        // Check required properties
        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $required_field) {
                if (!isset($data[$required_field])) {
                    $errors[] = "Required field '{$required_field}' is missing";
                }
            }
        }
        
        // Check property types if defined
        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $property => $property_schema) {
                if (isset($data[$property]) && isset($property_schema['type'])) {
                    $value = $data[$property];
                    $expected_type = $property_schema['type'];
                    
                    $actual_type = gettype($value);
                    if ($expected_type === 'integer') {
                        $expected_type = 'int';
                    } elseif ($expected_type === 'boolean') {
                        $expected_type = 'bool';
                    }
                    
                    if ($actual_type !== $expected_type) {
                        $errors[] = "Field '{$property}' should be of type {$expected_type}, got {$actual_type}";
                    }
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Check if user has capability for plugin action
     *
     * @param string $capability Capability to check
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool Whether user has capability
     */
    public function user_can($capability, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $has_capability = user_can($user_id, $capability);
        
        if (!$has_capability) {
            $this->logger->warning('User capability check failed', [
                'user_id' => $user_id,
                'capability' => $capability,
                'ip' => $this->get_client_ip()
            ]);
        }
        
        return $has_capability;
    }
    
    /**
     * Log security event
     *
     * @param string $event Event name
     * @param array $context Event context
     */
    public function log_security_event($event, $context = []) {
        $context['ip'] = $this->get_client_ip();
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $context['timestamp'] = current_time('mysql');
        
        $this->logger->warning("Security event: {$event}", $context);
        
        // Store in database for security monitoring
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_logs';
        
        $wpdb->insert(
            $table_name,
            [
                'level' => 'WARNING',
                'message' => "Security event: {$event}",
                'context' => json_encode($context),
                'module' => 'security',
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );
    }
    
    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs
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
     * Log user login
     *
     * @param string $user_login User login
     * @param WP_User $user User object
     */
    public function log_user_login($user_login, $user) {
        $this->log_security_event('user_login', [
            'user_id' => $user->ID,
            'user_login' => $user_login,
            'user_email' => $user->user_email
        ]);
    }
    
    /**
     * Log failed login attempt
     *
     * @param string $username Username used in failed login
     */
    public function log_failed_login($username) {
        $this->log_security_event('failed_login', [
            'username' => $username
        ]);
    }
    
    /**
     * Rate limit check
     *
     * @param string $action Action being rate limited
     * @param int $limit Number of allowed actions
     * @param int $window Time window in seconds
     * @param string $identifier Identifier for rate limiting (IP, user ID, etc.)
     * @return bool Whether action is allowed
     */
    public function rate_limit_check($action, $limit, $window, $identifier = null) {
        if ($identifier === null) {
            $identifier = $this->get_client_ip();
        }
        
        $cache_key = "ai_manager_pro_rate_limit_{$action}_{$identifier}";
        $current_count = wp_cache_get($cache_key);
        
        if ($current_count === false) {
            $current_count = 0;
        }
        
        if ($current_count >= $limit) {
            $this->log_security_event('rate_limit_exceeded', [
                'action' => $action,
                'identifier' => $identifier,
                'limit' => $limit,
                'window' => $window
            ]);
            return false;
        }
        
        wp_cache_set($cache_key, $current_count + 1, '', $window);
        return true;
    }
}

