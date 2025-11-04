<?php
/**
 * Settings Exception Classes
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Exceptions
 */

namespace AI_Manager_Pro\Settings\Exceptions;

/**
 * Base Settings Exception
 */
class Settings_Exception extends \Exception
{

    /**
     * Error code
     *
     * @var string
     */
    private $error_code;

    /**
     * Error context
     *
     * @var array
     */
    private $context;

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param string $error_code Error code
     * @param array $context Error context
     * @param int $code Exception code
     * @param \Throwable $previous Previous exception
     */
    public function __construct($message, $error_code = '', $context = [], $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error_code = $error_code;
        $this->context = $context;
    }

    /**
     * Get error code
     *
     * @return string Error code
     */
    public function get_error_code()
    {
        return $this->error_code;
    }

    /**
     * Get error context
     *
     * @return array Error context
     */
    public function get_context()
    {
        return $this->context;
    }

    /**
     * Convert to array
     *
     * @return array Exception data
     */
    public function to_array()
    {
        return [
            'message' => $this->getMessage(),
            'error_code' => $this->error_code,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ];
    }
}

/**
 * Settings Validation Exception
 */
class Settings_Validation_Exception extends Settings_Exception
{

    /**
     * Validation errors
     *
     * @var array
     */
    private $validation_errors;

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param array $validation_errors Validation errors
     * @param array $context Error context
     */
    public function __construct($message, $validation_errors = [], $context = [])
    {
        parent::__construct($message, 'SETTINGS_002', $context);
        $this->validation_errors = $validation_errors;
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function get_validation_errors()
    {
        return $this->validation_errors;
    }

    /**
     * Convert to array
     *
     * @return array Exception data
     */
    public function to_array()
    {
        $data = parent::to_array();
        $data['validation_errors'] = $this->validation_errors;
        return $data;
    }
}

/**
 * Settings Permission Exception
 */
class Settings_Permission_Exception extends Settings_Exception
{

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param array $context Error context
     */
    public function __construct($message, $context = [])
    {
        parent::__construct($message, 'SETTINGS_003', $context);
    }
}

/**
 * Settings Encryption Exception
 */
class Settings_Encryption_Exception extends Settings_Exception
{

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param array $context Error context
     */
    public function __construct($message, $context = [])
    {
        parent::__construct($message, 'SETTINGS_004', $context);
    }
}

/**
 * Settings Database Exception
 */
class Settings_Database_Exception extends Settings_Exception
{

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param array $context Error context
     */
    public function __construct($message, $context = [])
    {
        parent::__construct($message, 'SETTINGS_005', $context);
    }
}

/**
 * Settings API Exception
 */
class Settings_API_Exception extends Settings_Exception
{

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param array $context Error context
     */
    public function __construct($message, $context = [])
    {
        parent::__construct($message, 'SETTINGS_006', $context);
    }
}