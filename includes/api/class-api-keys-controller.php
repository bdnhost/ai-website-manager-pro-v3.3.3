<?php
/**
 * API Keys Controller
 *
 * @package AI_Manager_Pro
 * @subpackage API
 */

namespace AI_Manager_Pro\API;

use AI_Manager_Pro\Settings\Settings_Manager;
use AI_Manager_Pro\Settings\Exceptions\Settings_Exception;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * API Keys Controller Class
 * 
 * Handles REST API endpoints for API keys management
 */
class API_Keys_Controller extends WP_REST_Controller
{

    /**
     * Settings manager
     *
     * @var Settings_Manager
     */
    private $settings_manager;

    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'ai-manager-pro/v1';

    /**
     * API base route
     *
     * @var string
     */
    protected $rest_base = 'api-keys';

    /**
     * Constructor
     *
     * @param Settings_Manager $settings_manager Settings manager instance
     */
    public function __construct(Settings_Manager $settings_manager)
    {
        $this->settings_manager = $settings_manager;
    }

    /**
     * Register API routes
     */
    public function register_routes()
    {
        // Get all API keys
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_api_keys'],
                'permission_callback' => [$this, 'get_permissions_check']
            ]
        ]);

        // Update all API keys
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_api_keys'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args' => [
                    'api_keys' => [
                        'description' => __('API keys data', 'ai-website-manager-pro'),
                        'type' => 'object',
                        'required' => true
                    ]
                ]
            ]
        ]);

        // Get provider API keys
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<provider>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_provider_api_keys'],
                'permission_callback' => [$this, 'get_permissions_check'],
                'args' => [
                    'provider' => [
                        'description' => __('AI provider name', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['openai', 'claude', 'openrouter'],
                        'sanitize_callback' => 'sanitize_key'
                    ]
                ]
            ]
        ]);

        // Update provider API keys
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<provider>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_provider_api_keys'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args' => [
                    'provider' => [
                        'description' => __('AI provider name', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['openai', 'claude', 'openrouter'],
                        'sanitize_callback' => 'sanitize_key'
                    ],
                    'settings' => [
                        'description' => __('Provider settings', 'ai-website-manager-pro'),
                        'type' => 'object',
                        'required' => true
                    ]
                ]
            ]
        ]);

        // Test provider connection
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<provider>[a-zA-Z0-9_-]+)/test', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'test_provider_connection'],
                'permission_callback' => [$this, 'get_permissions_check'],
                'args' => [
                    'provider' => [
                        'description' => __('AI provider name', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['openai', 'claude', 'openrouter'],
                        'sanitize_callback' => 'sanitize_key'
                    ]
                ]
            ]
        ]);

        // Get provider models
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<provider>[a-zA-Z0-9_-]+)/models', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_provider_models'],
                'permission_callback' => [$this, 'get_permissions_check'],
                'args' => [
                    'provider' => [
                        'description' => __('AI provider name', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['openai', 'claude', 'openrouter'],
                        'sanitize_callback' => 'sanitize_key'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get all API keys
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_api_keys($request)
    {
        try {
            $include_sensitive = $request->get_param('include_sensitive');
            $api_keys = $this->settings_manager->get_api_keys();

            // Filter sensitive data if not authorized
            if (!$include_sensitive || !current_user_can('ai_manager_manage_settings')) {
                $api_keys = $this->mask_sensitive_data($api_keys);
            }

            return rest_ensure_response([
                'api_keys' => $api_keys,
                'providers' => array_keys($api_keys),
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'api_keys_error',
                __('Failed to retrieve API keys', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update all API keys
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_api_keys($request)
    {
        try {
            $api_keys = $request->get_param('api_keys');
            $validate = $request->get_param('validate') !== false;

            if (!is_array($api_keys)) {
                return new WP_Error(
                    'invalid_api_keys',
                    __('API keys must be an object', 'ai-website-manager-pro'),
                    ['status' => 400]
                );
            }

            $result = $this->settings_manager->set_api_keys($api_keys);

            return rest_ensure_response([
                'success' => $result,
                'message' => __('API keys updated successfully', 'ai-website-manager-pro'),
                'providers' => array_keys($api_keys),
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'api_keys_error',
                __('Failed to update API keys', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get provider API keys
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_provider_api_keys($request)
    {
        try {
            $provider = $request->get_param('provider');
            $include_sensitive = $request->get_param('include_sensitive');

            $api_keys = $this->settings_manager->get_api_keys($provider);

            // Filter sensitive data if not authorized
            if (!$include_sensitive || !current_user_can('ai_manager_manage_settings')) {
                $api_keys = $this->mask_sensitive_data([$provider => $api_keys]);
                $api_keys = $api_keys[$provider] ?? [];
            }

            return rest_ensure_response([
                'provider' => $provider,
                'settings' => $api_keys,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'api_keys_error',
                __('Failed to retrieve provider API keys', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update provider API keys
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_provider_api_keys($request)
    {
        try {
            $provider = $request->get_param('provider');
            $settings = $request->get_param('settings');
            $validate = $request->get_param('validate') !== false;

            if (!is_array($settings)) {
                return new WP_Error(
                    'invalid_settings',
                    __('Settings must be an object', 'ai-website-manager-pro'),
                    ['status' => 400]
                );
            }

            $result = $this->settings_manager->set_api_keys($settings, $provider);

            return rest_ensure_response([
                'success' => $result,
                'message' => __('Provider API keys updated successfully', 'ai-website-manager-pro'),
                'provider' => $provider,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'api_keys_error',
                __('Failed to update provider API keys', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Test provider connection
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function test_provider_connection($request)
    {
        try {
            $provider = $request->get_param('provider');
            $result = $this->settings_manager->test_api_connection($provider);

            return rest_ensure_response([
                'provider' => $provider,
                'connected' => $result,
                'status' => $result ? 'success' : 'failed',
                'message' => $result ?
                    __('Connection successful', 'ai-website-manager-pro') :
                    __('Connection failed', 'ai-website-manager-pro'),
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (\Exception $e) {
            return new WP_Error(
                'connection_test_error',
                __('Failed to test provider connection', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get provider models
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_provider_models($request)
    {
        try {
            $provider = $request->get_param('provider');

            // Get available models based on provider
            $models = $this->get_available_models($provider);

            return rest_ensure_response([
                'provider' => $provider,
                'models' => $models,
                'count' => count($models),
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (\Exception $e) {
            return new WP_Error(
                'models_error',
                __('Failed to retrieve provider models', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get available models for provider
     *
     * @param string $provider Provider name
     * @return array Available models
     */
    private function get_available_models($provider)
    {
        $models = [
            'openai' => [
                ['id' => 'gpt-4', 'name' => 'GPT-4', 'description' => 'Most capable model'],
                ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo', 'description' => 'Faster GPT-4 variant'],
                ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo', 'description' => 'Fast and efficient'],
                ['id' => 'gpt-3.5-turbo-16k', 'name' => 'GPT-3.5 Turbo 16K', 'description' => 'Extended context length']
            ],
            'claude' => [
                ['id' => 'claude-3-haiku-20240307', 'name' => 'Claude 3 Haiku', 'description' => 'Fast and lightweight'],
                ['id' => 'claude-3-sonnet-20240229', 'name' => 'Claude 3 Sonnet', 'description' => 'Balanced performance'],
                ['id' => 'claude-3-opus-20240229', 'name' => 'Claude 3 Opus', 'description' => 'Most capable model']
            ],
            'openrouter' => [
                ['id' => 'openai/gpt-4', 'name' => 'OpenAI GPT-4', 'description' => 'GPT-4 via OpenRouter'],
                ['id' => 'openai/gpt-3.5-turbo', 'name' => 'OpenAI GPT-3.5 Turbo', 'description' => 'GPT-3.5 via OpenRouter'],
                ['id' => 'anthropic/claude-3-haiku', 'name' => 'Claude 3 Haiku', 'description' => 'Claude via OpenRouter'],
                ['id' => 'anthropic/claude-3-sonnet', 'name' => 'Claude 3 Sonnet', 'description' => 'Claude via OpenRouter'],
                ['id' => 'meta-llama/llama-2-70b-chat', 'name' => 'Llama 2 70B Chat', 'description' => 'Meta Llama model'],
                ['id' => 'google/gemini-pro', 'name' => 'Google Gemini Pro', 'description' => 'Google Gemini model'],
                ['id' => 'mistralai/mistral-7b-instruct', 'name' => 'Mistral 7B Instruct', 'description' => 'Mistral AI model'],
                ['id' => 'cohere/command-r-plus', 'name' => 'Cohere Command R+', 'description' => 'Cohere model']
            ]
        ];

        return $models[$provider] ?? [];
    }

    /**
     * Mask sensitive data
     *
     * @param array $api_keys API keys array
     * @return array Masked API keys
     */
    private function mask_sensitive_data($api_keys)
    {
        $masked = $api_keys;

        foreach ($masked as $provider => &$settings) {
            if (isset($settings['api_key']) && !empty($settings['api_key'])) {
                $key = $settings['api_key'];
                $settings['api_key'] = substr($key, 0, 8) . str_repeat('*', 20) . substr($key, -4);
                $settings['_masked'] = true;
            }
        }

        return $masked;
    }

    /**
     * Permission checks
     */

    /**
     * Check permissions for getting API keys
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error Permission result
     */
    public function get_permissions_check($request)
    {
        if (!current_user_can('ai_manager_manage_settings')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to view API keys.', 'ai-website-manager-pro'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Check permissions for updating API keys
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error Permission result
     */
    public function update_permissions_check($request)
    {
        if (!current_user_can('ai_manager_manage_settings')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to update API keys.', 'ai-website-manager-pro'),
                ['status' => 403]
            );
        }

        return true;
    }
}