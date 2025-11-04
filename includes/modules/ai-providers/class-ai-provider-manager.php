<?php
/**
 * AI Provider Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\AI_Providers
 */

namespace AI_Manager_Pro\Modules\AI_Providers;

use AI_Manager_Pro\Modules\AI_Providers\Interfaces\AI_Provider_Interface;
use AI_Manager_Pro\Modules\AI_Providers\Providers\OpenAI_Provider;
use AI_Manager_Pro\Modules\AI_Providers\Providers\Claude_Provider;
use AI_Manager_Pro\Modules\AI_Providers\Providers\OpenRouter_Provider;
use AI_Manager_Pro\Modules\Security\Security_Manager;
use Monolog\Logger;

/**
 * AI Provider Manager Class
 */
class AI_Provider_Manager {
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Security manager instance
     *
     * @var Security_Manager
     */
    private $security;
    
    /**
     * Registered providers
     *
     * @var array
     */
    private $providers = [];
    
    /**
     * Active provider
     *
     * @var AI_Provider_Interface
     */
    private $active_provider;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     * @param Security_Manager $security Security manager instance
     */
    public function __construct(Logger $logger, Security_Manager $security) {
        $this->logger = $logger;
        $this->security = $security;
        
        $this->register_default_providers();
        $this->initialize_active_provider();
    }
    
    /**
     * Register default providers
     */
    private function register_default_providers() {
        $this->register_provider('openai', OpenAI_Provider::class);
        $this->register_provider('claude', Claude_Provider::class);
        $this->register_provider('openrouter', OpenRouter_Provider::class);
    }
    
    /**
     * Register a provider
     *
     * @param string $name Provider name
     * @param string $class Provider class
     */
    public function register_provider($name, $class) {
        if (!class_exists($class)) {
            $this->logger->error('Provider class not found', [
                'provider' => $name,
                'class' => $class
            ]);
            return;
        }
        
        if (!in_array(AI_Provider_Interface::class, class_implements($class))) {
            $this->logger->error('Provider class does not implement AI_Provider_Interface', [
                'provider' => $name,
                'class' => $class
            ]);
            return;
        }
        
        $this->providers[$name] = $class;
        
        $this->logger->info('Provider registered', [
            'provider' => $name,
            'class' => $class
        ]);
    }
    
    /**
     * Initialize active provider
     */
    private function initialize_active_provider() {
        $settings = get_option('ai_manager_pro_settings', []);
        $default_provider = $settings['default_provider'] ?? 'openai';
        
        $this->set_active_provider($default_provider);
    }
    
    /**
     * Set active provider
     *
     * @param string $provider_name Provider name
     * @return bool Success status
     */
    public function set_active_provider($provider_name) {
        if (!isset($this->providers[$provider_name])) {
            $this->logger->error('Provider not found', ['provider' => $provider_name]);
            return false;
        }
        
        try {
            $provider_class = $this->providers[$provider_name];
            $provider = new $provider_class($this->logger, $this->security);
            
            // Get API configuration
            $api_keys = get_option('ai_manager_pro_api_keys', []);
            $config = $api_keys[$provider_name] ?? [];
            
            if (empty($config)) {
                $this->logger->warning('No configuration found for provider', [
                    'provider' => $provider_name
                ]);
                return false;
            }
            
            // Initialize provider
            if (!$provider->initialize($config)) {
                $this->logger->error('Failed to initialize provider', [
                    'provider' => $provider_name
                ]);
                return false;
            }
            
            $this->active_provider = $provider;
            
            $this->logger->info('Active provider set', [
                'provider' => $provider_name
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Error setting active provider', [
                'provider' => $provider_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Get active provider
     *
     * @return AI_Provider_Interface|null
     */
    public function get_active_provider() {
        return $this->active_provider;
    }
    
    /**
     * Generate content using active provider
     *
     * @param string $prompt The prompt
     * @param array $options Additional options
     * @return array|false Generated content or false on failure
     */
    public function generate_content($prompt, $options = []) {
        if (!$this->active_provider) {
            $this->logger->error('No active provider set');
            return false;
        }
        
        try {
            $result = $this->active_provider->generate_content($prompt, $options);
            
            if ($result) {
                $this->logger->info('Content generated successfully', [
                    'provider' => $this->active_provider->get_name(),
                    'prompt_length' => strlen($prompt),
                    'content_length' => strlen($result['content'] ?? '')
                ]);
            } else {
                $this->logger->warning('Content generation failed', [
                    'provider' => $this->active_provider->get_name(),
                    'prompt_length' => strlen($prompt)
                ]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Error generating content', [
                'provider' => $this->active_provider->get_name(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Test connection for a specific provider
     *
     * @param string $provider_name Provider name
     * @return bool Connection status
     */
    public function test_connection($provider_name) {
        if (!isset($this->providers[$provider_name])) {
            return false;
        }
        
        try {
            $provider_class = $this->providers[$provider_name];
            $provider = new $provider_class($this->logger, $this->security);
            
            // Get API configuration
            $api_keys = get_option('ai_manager_pro_api_keys', []);
            $config = $api_keys[$provider_name] ?? [];
            
            if (empty($config)) {
                return false;
            }
            
            // Initialize and test
            if (!$provider->initialize($config)) {
                return false;
            }
            
            return $provider->test_connection();
            
        } catch (\Exception $e) {
            $this->logger->error('Error testing provider connection', [
                'provider' => $provider_name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get all registered providers
     *
     * @return array Provider names
     */
    public function get_providers() {
        return array_keys($this->providers);
    }
    
    /**
     * Get provider instance
     *
     * @param string $provider_name Provider name
     * @return AI_Provider_Interface|null
     */
    public function get_provider($provider_name) {
        if (!isset($this->providers[$provider_name])) {
            return null;
        }
        
        try {
            $provider_class = $this->providers[$provider_name];
            return new $provider_class($this->logger, $this->security);
        } catch (\Exception $e) {
            $this->logger->error('Error creating provider instance', [
                'provider' => $provider_name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get available models for a provider
     *
     * @param string $provider_name Provider name
     * @return array Available models
     */
    public function get_available_models($provider_name) {
        $provider = $this->get_provider($provider_name);
        if (!$provider) {
            return [];
        }
        
        try {
            // Get API configuration
            $api_keys = get_option('ai_manager_pro_api_keys', []);
            $config = $api_keys[$provider_name] ?? [];
            
            if (empty($config)) {
                return [];
            }
            
            // Initialize provider
            if (!$provider->initialize($config)) {
                return [];
            }
            
            return $provider->get_available_models();
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting available models', [
                'provider' => $provider_name,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get provider configuration schema
     *
     * @param string $provider_name Provider name
     * @return array Configuration schema
     */
    public function get_provider_config_schema($provider_name) {
        $provider = $this->get_provider($provider_name);
        if (!$provider) {
            return [];
        }
        
        return $provider->get_config_schema();
    }
    
    /**
     * Validate provider configuration
     *
     * @param string $provider_name Provider name
     * @param array $config Configuration to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_provider_config($provider_name, $config) {
        $provider = $this->get_provider($provider_name);
        if (!$provider) {
            return ['Provider not found'];
        }
        
        return $provider->validate_config($config);
    }
}

