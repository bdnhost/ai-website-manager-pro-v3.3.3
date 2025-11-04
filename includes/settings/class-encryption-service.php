<?php
/**
 * Encryption Service
 *
 * @package AI_Manager_Pro
 * @subpackage Settings
 */

namespace AI_Manager_Pro\Settings;

use AI_Manager_Pro\Settings\Exceptions\Settings_Encryption_Exception;

/**
 * Encryption Service Class
 * 
 * Handles encryption and decryption of sensitive settings
 */
class Encryption_Service
{

    /**
     * Encryption method
     *
     * @var string
     */
    private $method = 'AES-256-CBC';

    /**
     * Encryption key
     *
     * @var string
     */
    private $key;

    /**
     * Constructor
     *
     * @param string $key Encryption key (optional)
     */
    public function __construct($key = null)
    {
        $this->key = $key ?: $this->get_encryption_key();
    }

    /**
     * Encrypt data
     *
     * @param mixed $data Data to encrypt
     * @param string $key Custom encryption key (optional)
     * @return string Encrypted data
     * @throws Settings_Encryption_Exception
     */
    public function encrypt($data, $key = null)
    {
        if ($data === null || $data === '') {
            return '';
        }

        $encryption_key = $key ?: $this->key;

        if (empty($encryption_key)) {
            throw new Settings_Encryption_Exception(
                'Encryption key is required',
                ['data_type' => gettype($data)]
            );
        }

        try {
            // Serialize data if it's not a string
            $serialized_data = is_string($data) ? $data : serialize($data);

            // Generate random IV
            $iv_length = openssl_cipher_iv_length($this->method);
            $iv = openssl_random_pseudo_bytes($iv_length);

            // Encrypt the data
            $encrypted = openssl_encrypt(
                $serialized_data,
                $this->method,
                base64_decode($encryption_key),
                0,
                $iv
            );

            if ($encrypted === false) {
                throw new Settings_Encryption_Exception(
                    'Failed to encrypt data: ' . openssl_error_string(),
                    ['method' => $this->method, 'data_length' => strlen($serialized_data)]
                );
            }

            // Combine IV and encrypted data
            $result = base64_encode($iv . $encrypted);

            // Add prefix to identify encrypted data
            return 'ENCRYPTED:' . $result;

        } catch (\Exception $e) {
            throw new Settings_Encryption_Exception(
                'Encryption failed: ' . $e->getMessage(),
                [
                    'data_type' => gettype($data),
                    'method' => $this->method,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted_data Encrypted data
     * @param string $key Custom encryption key (optional)
     * @return mixed Decrypted data
     * @throws Settings_Encryption_Exception
     */
    public function decrypt($encrypted_data, $key = null)
    {
        if (empty($encrypted_data)) {
            return '';
        }

        // Check if data is actually encrypted
        if (!$this->is_encrypted($encrypted_data)) {
            return $encrypted_data; // Return as-is for backward compatibility
        }

        $encryption_key = $key ?: $this->key;

        if (empty($encryption_key)) {
            throw new Settings_Encryption_Exception(
                'Encryption key is required for decryption',
                ['encrypted_data_length' => strlen($encrypted_data)]
            );
        }

        try {
            // Remove prefix
            $data = str_replace('ENCRYPTED:', '', $encrypted_data);
            $data = base64_decode($data);

            if ($data === false) {
                throw new Settings_Encryption_Exception(
                    'Invalid encrypted data format',
                    ['encrypted_data' => substr($encrypted_data, 0, 50) . '...']
                );
            }

            // Extract IV and encrypted content
            $iv_length = openssl_cipher_iv_length($this->method);

            if (strlen($data) < $iv_length) {
                throw new Settings_Encryption_Exception(
                    'Encrypted data is too short',
                    ['data_length' => strlen($data), 'required_iv_length' => $iv_length]
                );
            }

            $iv = substr($data, 0, $iv_length);
            $encrypted = substr($data, $iv_length);

            // Decrypt the data
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->method,
                base64_decode($encryption_key),
                0,
                $iv
            );

            if ($decrypted === false) {
                throw new Settings_Encryption_Exception(
                    'Failed to decrypt data: ' . openssl_error_string(),
                    ['method' => $this->method, 'iv_length' => strlen($iv)]
                );
            }

            // Try to unserialize if it's serialized data
            $unserialized = @unserialize($decrypted);
            return $unserialized !== false ? $unserialized : $decrypted;

        } catch (\Exception $e) {
            throw new Settings_Encryption_Exception(
                'Decryption failed: ' . $e->getMessage(),
                [
                    'method' => $this->method,
                    'encrypted_data_length' => strlen($encrypted_data),
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Generate a new encryption key
     *
     * @return string Base64 encoded encryption key
     */
    public function generate_key()
    {
        try {
            $key = openssl_random_pseudo_bytes(32); // 256 bits
            return base64_encode($key);
        } catch (\Exception $e) {
            throw new Settings_Encryption_Exception(
                'Failed to generate encryption key: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check if data is encrypted
     *
     * @param string $data Data to check
     * @return bool Whether data is encrypted
     */
    public function is_encrypted($data)
    {
        return is_string($data) && strpos($data, 'ENCRYPTED:') === 0;
    }

    /**
     * Get or create encryption key
     *
     * @return string Encryption key
     */
    private function get_encryption_key()
    {
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
            $key = $this->generate_key();
            update_option('ai_manager_pro_encryption_key', $key);

            // Log key generation
            if (function_exists('error_log')) {
                error_log('AI Manager Pro: Generated new encryption key');
            }
        }

        return $key;
    }

    /**
     * Rotate encryption key
     *
     * @param callable $callback Callback to re-encrypt data with new key
     * @return string New encryption key
     * @throws Settings_Encryption_Exception
     */
    public function rotate_key($callback = null)
    {
        $old_key = $this->key;
        $new_key = $this->generate_key();

        try {
            // If callback provided, use it to re-encrypt existing data
            if (is_callable($callback)) {
                $callback($old_key, $new_key);
            }

            // Update stored key
            update_option('ai_manager_pro_encryption_key', $new_key);
            $this->key = $new_key;

            return $new_key;

        } catch (\Exception $e) {
            throw new Settings_Encryption_Exception(
                'Key rotation failed: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Validate encryption key
     *
     * @param string $key Key to validate
     * @return bool Whether key is valid
     */
    public function validate_key($key)
    {
        if (empty($key)) {
            return false;
        }

        $decoded = base64_decode($key, true);
        if ($decoded === false) {
            return false;
        }

        // Check key length (should be 32 bytes for AES-256)
        return strlen($decoded) === 32;
    }

    /**
     * Test encryption/decryption with current key
     *
     * @return bool Whether encryption is working
     */
    public function test_encryption()
    {
        try {
            $test_data = 'test_encryption_' . time();
            $encrypted = $this->encrypt($test_data);
            $decrypted = $this->decrypt($encrypted);

            return $decrypted === $test_data;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get encryption info
     *
     * @return array Encryption information
     */
    public function get_info()
    {
        return [
            'method' => $this->method,
            'key_length' => strlen(base64_decode($this->key)),
            'key_valid' => $this->validate_key($this->key),
            'encryption_working' => $this->test_encryption(),
            'openssl_version' => OPENSSL_VERSION_TEXT
        ];
    }

    /**
     * Mask sensitive data for display
     *
     * @param string $data Data to mask
     * @param int $visible_chars Number of visible characters at start/end
     * @return string Masked data
     */
    public function mask_data($data, $visible_chars = 4)
    {
        if (empty($data) || strlen($data) <= $visible_chars * 2) {
            return str_repeat('*', strlen($data));
        }

        $start = substr($data, 0, $visible_chars);
        $end = substr($data, -$visible_chars);
        $middle_length = strlen($data) - ($visible_chars * 2);

        return $start . str_repeat('*', min($middle_length, 20)) . $end;
    }
}