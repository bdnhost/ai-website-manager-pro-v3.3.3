<?php
/**
 * OpenRouter AI Provider
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\AI_Providers\Providers
 */

namespace AI_Manager_Pro\Modules\AI_Providers\Providers;

use AI_Manager_Pro\Modules\AI_Providers\Interfaces\AI_Provider_Interface;
use AI_Manager_Pro\Modules\Security\Security_Manager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;

/**
 * OpenRouter Provider Class
 */
class OpenRouter_Provider implements AI_Provider_Interface {
    
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
     * HTTP client
     *
     * @var Client
     */
    private $client;
    
    /**
     * API configuration
     *
     * @var array
     */
    private $config;
    
    /**
     * Available models cache
     *
     * @var array
     */
    private $models_cache;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     * @param Security_Manager $security Security manager instance
     */
    public function __construct(Logger $logger, Security_Manager $security) {
        $this->logger = $logger;
        $this->security = $security;
    }
    
    /**
     * Initialize the provider
     *
     * @param array $config Configuration array
     * @return bool Success status
     */
    public function initialize($config) {
        $this->config = $config;
        
        // Validate required configuration
        if (empty($config['api_key'])) {
            $this->logger->error('OpenRouter API key is required');
            return false;
        }
        
        // Initialize HTTP client
        $this->client = new Client([
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->security->decrypt_api_key($config['api_key']),
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ]
        ]);
        
        $this->logger->info('OpenRouter provider initialized');
        return true;
    }
    
    /**
     * Test connection to the provider
     *
     * @return bool Connection status
     */
    public function test_connection() {
        try {
            $response = $this->client->get('models');
            $status_code = $response->getStatusCode();
            
            if ($status_code === 200) {
                $this->logger->info('OpenRouter connection test successful');
                return true;
            } else {
                $this->logger->warning('OpenRouter connection test failed', [
                    'status_code' => $status_code
                ]);
                return false;
            }
            
        } catch (RequestException $e) {
            $this->logger->error('OpenRouter connection test failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return false;
        }
    }
    
    /**
     * Generate content using the provider
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options
     * @return array|false Generated content or false on failure
     */
    public function generate_content($prompt, $options = []) {
        try {
            // Get selected model or use default
            $model = $options['model'] ?? $this->config['default_model'] ?? 'openai/gpt-3.5-turbo';
            
            // Prepare request data
            $request_data = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $options['max_tokens'] ?? 2000,
                'temperature' => $options['temperature'] ?? 0.7,
                'top_p' => $options['top_p'] ?? 1.0,
                'frequency_penalty' => $options['frequency_penalty'] ?? 0,
                'presence_penalty' => $options['presence_penalty'] ?? 0
            ];
            
            // Add system message if provided
            if (!empty($options['system_message'])) {
                array_unshift($request_data['messages'], [
                    'role' => 'system',
                    'content' => $options['system_message']
                ]);
            }
            
            $this->logger->info('Sending request to OpenRouter', [
                'model' => $model,
                'prompt_length' => strlen($prompt)
            ]);
            
            $response = $this->client->post('chat/completions', [
                'json' => $request_data
            ]);
            
            $body = json_decode($response->getBody()->getContents(), true);
            
            if (isset($body['choices'][0]['message']['content'])) {
                $content = trim($body['choices'][0]['message']['content']);
                
                $result = [
                    'content' => $content,
                    'model' => $model,
                    'usage' => $body['usage'] ?? [],
                    'provider' => 'openrouter'
                ];
                
                $this->logger->info('Content generated successfully via OpenRouter', [
                    'model' => $model,
                    'content_length' => strlen($content),
                    'tokens_used' => $body['usage']['total_tokens'] ?? 0
                ]);
                
                return $result;
            } else {
                $this->logger->error('Invalid response from OpenRouter', [
                    'response' => $body
                ]);
                return false;
            }
            
        } catch (RequestException $e) {
            $error_message = $e->getMessage();
            $status_code = $e->getCode();
            
            // Try to get more specific error from response
            if ($e->hasResponse()) {
                $error_body = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (isset($error_body['error']['message'])) {
                    $error_message = $error_body['error']['message'];
                }
            }
            
            $this->logger->error('OpenRouter API request failed', [
                'error' => $error_message,
                'status_code' => $status_code,
                'model' => $options['model'] ?? 'default'
            ]);
            
            return false;
            
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in OpenRouter content generation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Get available models for this provider
     *
     * @return array List of available models
     */
    public function get_available_models() {
        // Return cached models if available
        if ($this->models_cache) {
            return $this->models_cache;
        }
        
        try {
            $response = $this->client->get('models');
            $body = json_decode($response->getBody()->getContents(), true);
            
            if (isset($body['data']) && is_array($body['data'])) {
                $models = [];
                
                foreach ($body['data'] as $model) {
                    $models[] = [
                        'id' => $model['id'],
                        'name' => $model['name'] ?? $model['id'],
                        'description' => $model['description'] ?? '',
                        'context_length' => $model['context_length'] ?? 0,
                        'pricing' => [
                            'prompt' => $model['pricing']['prompt'] ?? 0,
                            'completion' => $model['pricing']['completion'] ?? 0
                        ],
                        'top_provider' => $model['top_provider'] ?? []
                    ];
                }
                
                // Sort models by name
                usort($models, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                $this->models_cache = $models;
                
                $this->logger->info('Retrieved OpenRouter models', [
                    'count' => count($models)
                ]);
                
                return $models;
            } else {
                $this->logger->error('Invalid models response from OpenRouter', [
                    'response' => $body
                ]);
                return [];
            }
            
        } catch (RequestException $e) {
            $this->logger->error('Failed to retrieve OpenRouter models', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return [];
        }
    }
    
    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name() {
        return 'OpenRouter';
    }
    
    /**
     * Get provider configuration schema
     *
     * @return array Configuration schema
     */
    public function get_config_schema() {
        return [
            'api_key' => [
                'type' => 'string',
                'required' => true,
                'label' => 'API Key',
                'description' => 'Your OpenRouter API key'
            ],
            'default_model' => [
                'type' => 'select',
                'required' => false,
                'label' => 'Default Model',
                'description' => 'Default model to use for content generation',
                'options' => 'dynamic', // Will be populated from get_available_models()
                'default' => 'openai/gpt-3.5-turbo'
            ],
            'max_tokens' => [
                'type' => 'number',
                'required' => false,
                'label' => 'Max Tokens',
                'description' => 'Maximum number of tokens to generate',
                'default' => 2000,
                'min' => 1,
                'max' => 8000
            ],
            'temperature' => [
                'type' => 'number',
                'required' => false,
                'label' => 'Temperature',
                'description' => 'Controls randomness (0.0 to 2.0)',
                'default' => 0.7,
                'min' => 0.0,
                'max' => 2.0,
                'step' => 0.1
            ]
        ];
    }
    
    /**
     * Validate configuration
     *
     * @param array $config Configuration to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_config($config) {
        $errors = [];
        
        // Validate API key
        if (empty($config['api_key'])) {
            $errors[] = 'API key is required';
        } elseif (!is_string($config['api_key'])) {
            $errors[] = 'API key must be a string';
        }
        
        // Validate default model if provided
        if (!empty($config['default_model'])) {
            if (!is_string($config['default_model'])) {
                $errors[] = 'Default model must be a string';
            }
        }
        
        // Validate max tokens if provided
        if (isset($config['max_tokens'])) {
            if (!is_numeric($config['max_tokens']) || $config['max_tokens'] < 1 || $config['max_tokens'] > 8000) {
                $errors[] = 'Max tokens must be a number between 1 and 8000';
            }
        }
        
        // Validate temperature if provided
        if (isset($config['temperature'])) {
            if (!is_numeric($config['temperature']) || $config['temperature'] < 0 || $config['temperature'] > 2) {
                $errors[] = 'Temperature must be a number between 0.0 and 2.0';
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get model information by ID
     *
     * @param string $model_id Model ID
     * @return array|null Model information
     */
    public function get_model_info($model_id) {
        $models = $this->get_available_models();
        
        foreach ($models as $model) {
            if ($model['id'] === $model_id) {
                return $model;
            }
        }
        
        return null;
    }
    
    /**
     * Get popular models
     *
     * @return array Popular models
     */
    public function get_popular_models() {
        $popular_model_ids = [
            'openai/gpt-4',
            'openai/gpt-3.5-turbo',
            'anthropic/claude-3-haiku',
            'anthropic/claude-3-sonnet',
            'meta-llama/llama-2-70b-chat',
            'google/gemini-pro'
        ];
        
        $models = $this->get_available_models();
        $popular_models = [];
        
        foreach ($popular_model_ids as $model_id) {
            foreach ($models as $model) {
                if ($model['id'] === $model_id) {
                    $popular_models[] = $model;
                    break;
                }
            }
        }
        
        return $popular_models;
    }
}

