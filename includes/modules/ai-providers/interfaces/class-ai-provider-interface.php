<?php
/**
 * AI Provider Interface
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\AI_Providers
 */

namespace AI_Manager_Pro\Modules\AI_Providers\Interfaces;

/**
 * Interface for AI providers
 */
interface AI_Provider_Interface {
    
    /**
     * Initialize the provider
     *
     * @param array $config Configuration array
     * @return bool Success status
     */
    public function initialize($config);
    
    /**
     * Test connection to the provider
     *
     * @return bool Connection status
     */
    public function test_connection();
    
    /**
     * Generate content using the provider
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options
     * @return array|false Generated content or false on failure
     */
    public function generate_content($prompt, $options = []);
    
    /**
     * Get available models for this provider
     *
     * @return array List of available models
     */
    public function get_available_models();
    
    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name();
    
    /**
     * Get provider configuration schema
     *
     * @return array Configuration schema
     */
    public function get_config_schema();
    
    /**
     * Validate configuration
     *
     * @param array $config Configuration to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_config($config);
}

