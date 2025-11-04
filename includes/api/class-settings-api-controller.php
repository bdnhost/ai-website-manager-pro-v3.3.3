<?php
/**
 * Settings API Controller
 *
 * @package AI_Manager_Pro
 * @subpackage API
 */

namespace AI_Manager_Pro\API;

use AI_Manager_Pro\Settings\Settings_Manager;
use AI_Manager_Pro\Settings\Exceptions\Settings_Exception;
use AI_Manager_Pro\Settings\Exceptions\Settings_Validation_Exception;
use AI_Manager_Pro\Settings\Exceptions\Settings_Permission_Exception;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Settings API Controller Class
 * 
 * Handles REST API endpoints for settings management
 */
class Settings_API_Controller extends WP_REST_Controller
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
    protected $rest_base = 'settings';

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
        // Get all settings
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permissions_check'],
                'args' => $this->get_collection_params()
            ],
            'schema' => [$this, 'get_public_item_schema']
        ]);

        // Update all settings
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'update_settings_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE)
            ]
        ]);

        // Get category settings
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<category>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_category_settings'],
                'permission_callback' => [$this, 'get_settings_permissions_check'],
                'args' => [
                    'category' => [
                        'description' => __('Settings category', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ]
                ]
            ]
        ]);

        // Update category settings
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<category>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_category_settings'],
                'permission_callback' => [$this, 'update_settings_permissions_check'],
                'args' => [
                    'category' => [
                        'description' => __('Settings category', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ],
                    'settings' => [
                        'description' => __('Settings data', 'ai-website-manager-pro'),
                        'type' => 'object',
                        'required' => true
                    ]
                ]
            ]
        ]);

        // Get specific setting
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<category>[a-zA-Z0-9_-]+)/(?P<key>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_setting'],
                'permission_callback' => [$this, 'get_settings_permissions_check'],
                'args' => [
                    'category' => [
                        'description' => __('Settings category', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ],
                    'key' => [
                        'description' => __('Setting key', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ]
                ]
            ]
        ]);

        // Update specific setting
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<category>[a-zA-Z0-9_-]+)/(?P<key>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_setting'],
                'permission_callback' => [$this, 'update_settings_permissions_check'],
                'args' => [
                    'category' => [
                        'description' => __('Settings category', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ],
                    'key' => [
                        'description' => __('Setting key', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_key'
                    ],
                    'value' => [
                        'description' => __('Setting value', 'ai-website-manager-pro'),
                        'required' => true
                    ]
                ]
            ]
        ]);

        // Backup settings
        register_rest_route($this->namespace, '/' . $this->rest_base . '/backup', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_backup'],
                'permission_callback' => [$this, 'manage_settings_permissions_check'],
                'args' => [
                    'name' => [
                        'description' => __('Backup name', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ]
            ]
        ]);

        // Restore settings
        register_rest_route($this->namespace, '/' . $this->rest_base . '/restore', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'restore_backup'],
                'permission_callback' => [$this, 'manage_settings_permissions_check'],
                'args' => [
                    'backup_id' => [
                        'description' => __('Backup ID', 'ai-website-manager-pro'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ]
            ]
        ]);

        // Test API connection
        register_rest_route($this->namespace, '/' . $this->rest_base . '/test-connection', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'test_api_connection'],
                'permission_callback' => [$this, 'get_settings_permissions_check'],
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
     * Get all settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_settings($request)
    {
        try {
            $categories = $request->get_param('categories');
            $include_sensitive = $request->get_param('include_sensitive');

            $settings = [];

            if ($categories) {
                $category_list = explode(',', $categories);
                foreach ($category_list as $category) {
                    $category = trim($category);
                    $settings[$category] = $this->settings_manager->get_category($category);
                }
            } else {
                // Get all categories
                $settings['general'] = $this->settings_manager->get_general_settings();

                if ($include_sensitive && current_user_can('ai_manager_manage_settings')) {
                    $settings['api_keys'] = $this->settings_manager->get_api_keys();
                }
            }

            // Filter sensitive data if not authorized
            if (!$include_sensitive || !current_user_can('ai_manager_manage_settings')) {
                $settings = $this->filter_sensitive_data($settings);
            }

            return rest_ensure_response([
                'settings' => $settings,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'version' => AI_MANAGER_PRO_VERSION
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
                'settings_error',
                __('Failed to retrieve settings', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update all settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_settings($request)
    {
        try {
            $settings = $request->get_param('settings');
            $validate = $request->get_param('validate') !== false;

            if (!is_array($settings)) {
                return new WP_Error(
                    'invalid_settings',
                    __('Settings must be an object', 'ai-website-manager-pro'),
                    ['status' => 400]
                );
            }

            $updated_categories = [];

            foreach ($settings as $category => $category_settings) {
                $result = $this->settings_manager->set_category($category, $category_settings, $validate);

                if ($result) {
                    $updated_categories[] = $category;
                }
            }

            return rest_ensure_response([
                'success' => true,
                'message' => __('Settings updated successfully', 'ai-website-manager-pro'),
                'updated_categories' => $updated_categories,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Validation_Exception $e) {
            return new WP_Error(
                'validation_failed',
                $e->getMessage(),
                [
                    'status' => 400,
                    'validation_errors' => $e->get_validation_errors()
                ]
            );
        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'settings_error',
                __('Failed to update settings', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get category settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_category_settings($request)
    {
        try {
            $category = $request->get_param('category');
            $include_sensitive = $request->get_param('include_sensitive');

            $settings = $this->settings_manager->get_category($category);

            // Filter sensitive data if not authorized
            if (!$include_sensitive || !current_user_can('ai_manager_manage_settings')) {
                $settings = $this->filter_sensitive_data([$category => $settings]);
                $settings = $settings[$category] ?? [];
            }

            return rest_ensure_response([
                'category' => $category,
                'settings' => $settings,
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
                'settings_error',
                __('Failed to retrieve category settings', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update category settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_category_settings($request)
    {
        try {
            $category = $request->get_param('category');
            $settings = $request->get_param('settings');
            $validate = $request->get_param('validate') !== false;

            $result = $this->settings_manager->set_category($category, $settings, $validate);

            return rest_ensure_response([
                'success' => $result,
                'message' => __('Category settings updated successfully', 'ai-website-manager-pro'),
                'category' => $category,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Validation_Exception $e) {
            return new WP_Error(
                'validation_failed',
                $e->getMessage(),
                [
                    'status' => 400,
                    'validation_errors' => $e->get_validation_errors()
                ]
            );
        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'settings_error',
                __('Failed to update category settings', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get specific setting
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_setting($request)
    {
        try {
            $category = $request->get_param('category');
            $key = $request->get_param('key');
            $full_key = $category . '.' . $key;

            $value = $this->settings_manager->get($full_key);

            return rest_ensure_response([
                'category' => $category,
                'key' => $key,
                'value' => $value,
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
                'settings_error',
                __('Failed to retrieve setting', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update specific setting
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_setting($request)
    {
        try {
            $category = $request->get_param('category');
            $key = $request->get_param('key');
            $value = $request->get_param('value');
            $validate = $request->get_param('validate') !== false;
            $full_key = $category . '.' . $key;

            $result = $this->settings_manager->set($full_key, $value, $validate);

            return rest_ensure_response([
                'success' => $result,
                'message' => __('Setting updated successfully', 'ai-website-manager-pro'),
                'category' => $category,
                'key' => $key,
                'value' => $value,
                'meta' => [
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ]
            ]);

        } catch (Settings_Validation_Exception $e) {
            return new WP_Error(
                'validation_failed',
                $e->getMessage(),
                [
                    'status' => 400,
                    'validation_errors' => $e->get_validation_errors()
                ]
            );
        } catch (Settings_Exception $e) {
            return new WP_Error(
                $e->get_error_code(),
                $e->getMessage(),
                ['status' => 400, 'context' => $e->get_context()]
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'settings_error',
                __('Failed to update setting', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Create backup
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function create_backup($request)
    {
        try {
            $name = $request->get_param('name');
            $backup_data = $this->settings_manager->backup();

            return rest_ensure_response([
                'success' => true,
                'message' => __('Backup created successfully', 'ai-website-manager-pro'),
                'backup' => $backup_data,
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
                'backup_error',
                __('Failed to create backup', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Restore backup
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function restore_backup($request)
    {
        try {
            $backup_id = $request->get_param('backup_id');

            // This would typically get backup data from backup service
            // For now, we'll expect the backup data in the request
            $backup_data = $request->get_param('backup_data');

            if (!$backup_data) {
                return new WP_Error(
                    'missing_backup_data',
                    __('Backup data is required', 'ai-website-manager-pro'),
                    ['status' => 400]
                );
            }

            $result = $this->settings_manager->restore($backup_data);

            return rest_ensure_response([
                'success' => $result,
                'message' => __('Settings restored successfully', 'ai-website-manager-pro'),
                'backup_id' => $backup_id,
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
                'restore_error',
                __('Failed to restore backup', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Test API connection
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function test_api_connection($request)
    {
        try {
            $provider = $request->get_param('provider');
            $result = $this->settings_manager->test_api_connection($provider);

            return rest_ensure_response([
                'provider' => $provider,
                'connected' => $result,
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
                __('Failed to test connection', 'ai-website-manager-pro'),
                ['status' => 500, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Permission checks
     */

    /**
     * Check permissions for getting settings
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error Permission result
     */
    public function get_settings_permissions_check($request)
    {
        if (!current_user_can('ai_manager_manage_settings')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to view settings.', 'ai-website-manager-pro'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Check permissions for updating settings
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error Permission result
     */
    public function update_settings_permissions_check($request)
    {
        if (!current_user_can('ai_manager_manage_settings')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to update settings.', 'ai-website-manager-pro'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Check permissions for managing settings (backup/restore)
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error Permission result
     */
    public function manage_settings_permissions_check($request)
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to manage settings.', 'ai-website-manager-pro'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Get collection parameters
     *
     * @return array Collection parameters
     */
    public function get_collection_params()
    {
        return [
            'categories' => [
                'description' => __('Comma-separated list of categories to retrieve', 'ai-website-manager-pro'),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'include_sensitive' => [
                'description' => __('Whether to include sensitive data like API keys', 'ai-website-manager-pro'),
                'type' => 'boolean',
                'default' => false
            ]
        ];
    }

    /**
     * Filter sensitive data from settings
     *
     * @param array $settings Settings array
     * @return array Filtered settings
     */
    private function filter_sensitive_data($settings)
    {
        $filtered = $settings;

        // Remove API keys if present
        if (isset($filtered['api_keys'])) {
            foreach ($filtered['api_keys'] as $provider => &$provider_settings) {
                if (isset($provider_settings['api_key'])) {
                    $provider_settings['api_key'] = '[HIDDEN]';
                }
            }
        }

        return $filtered;
    }

    /**
     * Get the schema for settings
     *
     * @return array Schema array
     */
    public function get_public_item_schema()
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'settings',
            'type' => 'object',
            'properties' => [
                'settings' => [
                    'description' => __('Plugin settings', 'ai-website-manager-pro'),
                    'type' => 'object'
                ],
                'meta' => [
                    'description' => __('Metadata about the response', 'ai-website-manager-pro'),
                    'type' => 'object',
                    'properties' => [
                        'timestamp' => [
                            'description' => __('Response timestamp', 'ai-website-manager-pro'),
                            'type' => 'string',
                            'format' => 'date-time'
                        ],
                        'user_id' => [
                            'description' => __('Current user ID', 'ai-website-manager-pro'),
                            'type' => 'integer'
                        ],
                        'version' => [
                            'description' => __('Plugin version', 'ai-website-manager-pro'),
                            'type' => 'string'
                        ]
                    ]
                ]
            ]
        ];

        return $schema;
    }
}