<?php
/**
 * API Keys Settings Schema
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Schemas
 */

namespace AI_Manager_Pro\Settings\Schemas;

/**
 * API Keys Settings Schema Class
 */
class API_Keys_Schema
{

    /**
     * Get API keys settings schema
     *
     * @return array Schema definition
     */
    public static function get_schema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'openai' => [
                    'type' => 'object',
                    'title' => __('OpenAI Configuration', 'ai-website-manager-pro'),
                    'properties' => [
                        'api_key' => [
                            'type' => 'string',
                            'encrypted' => true,
                            'minLength' => 20,
                            'title' => __('API Key', 'ai-website-manager-pro'),
                            'description' => __('Your OpenAI API key', 'ai-website-manager-pro'),
                            'placeholder' => 'sk-...'
                        ],
                        'model' => [
                            'type' => 'string',
                            'enum' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo', 'gpt-3.5-turbo-16k'],
                            'default' => 'gpt-3.5-turbo',
                            'title' => __('Model', 'ai-website-manager-pro'),
                            'description' => __('OpenAI model to use', 'ai-website-manager-pro')
                        ],
                        'max_tokens' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 4000,
                            'default' => 2000,
                            'title' => __('Max Tokens', 'ai-website-manager-pro'),
                            'description' => __('Maximum number of tokens to generate', 'ai-website-manager-pro')
                        ],
                        'temperature' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 2,
                            'default' => 0.7,
                            'title' => __('Temperature', 'ai-website-manager-pro'),
                            'description' => __('Controls randomness in output (0-2)', 'ai-website-manager-pro')
                        ],
                        'top_p' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 1,
                            'default' => 1,
                            'title' => __('Top P', 'ai-website-manager-pro'),
                            'description' => __('Controls diversity via nucleus sampling', 'ai-website-manager-pro')
                        ],
                        'frequency_penalty' => [
                            'type' => 'number',
                            'min' => -2,
                            'max' => 2,
                            'default' => 0,
                            'title' => __('Frequency Penalty', 'ai-website-manager-pro'),
                            'description' => __('Penalizes repeated tokens', 'ai-website-manager-pro')
                        ],
                        'presence_penalty' => [
                            'type' => 'number',
                            'min' => -2,
                            'max' => 2,
                            'default' => 0,
                            'title' => __('Presence Penalty', 'ai-website-manager-pro'),
                            'description' => __('Penalizes new tokens based on presence', 'ai-website-manager-pro')
                        ]
                    ],
                    'required' => ['api_key', 'model']
                ],
                'claude' => [
                    'type' => 'object',
                    'title' => __('Claude Configuration', 'ai-website-manager-pro'),
                    'properties' => [
                        'api_key' => [
                            'type' => 'string',
                            'encrypted' => true,
                            'minLength' => 20,
                            'title' => __('API Key', 'ai-website-manager-pro'),
                            'description' => __('Your Anthropic API key', 'ai-website-manager-pro'),
                            'placeholder' => 'sk-ant-...'
                        ],
                        'model' => [
                            'type' => 'string',
                            'enum' => ['claude-3-haiku-20240307', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229'],
                            'default' => 'claude-3-haiku-20240307',
                            'title' => __('Model', 'ai-website-manager-pro'),
                            'description' => __('Claude model to use', 'ai-website-manager-pro')
                        ],
                        'max_tokens' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 4000,
                            'default' => 2000,
                            'title' => __('Max Tokens', 'ai-website-manager-pro'),
                            'description' => __('Maximum number of tokens to generate', 'ai-website-manager-pro')
                        ],
                        'temperature' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 1,
                            'default' => 0.7,
                            'title' => __('Temperature', 'ai-website-manager-pro'),
                            'description' => __('Controls randomness in output (0-1)', 'ai-website-manager-pro')
                        ],
                        'top_p' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 1,
                            'default' => 1,
                            'title' => __('Top P', 'ai-website-manager-pro'),
                            'description' => __('Controls diversity via nucleus sampling', 'ai-website-manager-pro')
                        ],
                        'top_k' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 100,
                            'default' => 40,
                            'title' => __('Top K', 'ai-website-manager-pro'),
                            'description' => __('Limits token selection to top K choices', 'ai-website-manager-pro')
                        ]
                    ],
                    'required' => ['api_key', 'model']
                ],
                'openrouter' => [
                    'type' => 'object',
                    'title' => __('OpenRouter Configuration', 'ai-website-manager-pro'),
                    'properties' => [
                        'api_key' => [
                            'type' => 'string',
                            'encrypted' => true,
                            'minLength' => 20,
                            'title' => __('API Key', 'ai-website-manager-pro'),
                            'description' => __('Your OpenRouter API key', 'ai-website-manager-pro'),
                            'placeholder' => 'sk-or-...'
                        ],
                        'model' => [
                            'type' => 'string',
                            'enum' => [
                                'openai/gpt-4',
                                'openai/gpt-3.5-turbo',
                                'anthropic/claude-3-haiku',
                                'anthropic/claude-3-sonnet',
                                'anthropic/claude-3-opus',
                                'meta-llama/llama-2-70b-chat',
                                'google/gemini-pro',
                                'mistralai/mistral-7b-instruct',
                                'cohere/command-r-plus'
                            ],
                            'default' => 'openai/gpt-3.5-turbo',
                            'title' => __('Model', 'ai-website-manager-pro'),
                            'description' => __('OpenRouter model to use', 'ai-website-manager-pro')
                        ],
                        'site_url' => [
                            'type' => 'string',
                            'format' => 'url',
                            'title' => __('Site URL', 'ai-website-manager-pro'),
                            'description' => __('Your website URL for OpenRouter tracking', 'ai-website-manager-pro'),
                            'default' => get_site_url()
                        ],
                        'app_name' => [
                            'type' => 'string',
                            'maxLength' => 100,
                            'title' => __('App Name', 'ai-website-manager-pro'),
                            'description' => __('Application name for OpenRouter tracking', 'ai-website-manager-pro'),
                            'default' => get_bloginfo('name')
                        ],
                        'max_tokens' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 4000,
                            'default' => 2000,
                            'title' => __('Max Tokens', 'ai-website-manager-pro'),
                            'description' => __('Maximum number of tokens to generate', 'ai-website-manager-pro')
                        ],
                        'temperature' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 2,
                            'default' => 0.7,
                            'title' => __('Temperature', 'ai-website-manager-pro'),
                            'description' => __('Controls randomness in output', 'ai-website-manager-pro')
                        ],
                        'top_p' => [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 1,
                            'default' => 1,
                            'title' => __('Top P', 'ai-website-manager-pro'),
                            'description' => __('Controls diversity via nucleus sampling', 'ai-website-manager-pro')
                        ]
                    ],
                    'required' => ['api_key', 'model']
                ]
            ]
        ];
    }

    /**
     * Get default values for a specific provider
     *
     * @param string $provider Provider name
     * @return array Default values
     */
    public static function get_provider_defaults($provider)
    {
        $schema = self::get_schema();

        if (!isset($schema['properties'][$provider])) {
            return [];
        }

        $provider_schema = $schema['properties'][$provider];
        $defaults = [];

        foreach ($provider_schema['properties'] as $key => $property) {
            if (isset($property['default'])) {
                $defaults[$key] = $property['default'];
            }
        }

        return $defaults;
    }

    /**
     * Get all default values
     *
     * @return array Default values for all providers
     */
    public static function get_defaults()
    {
        $schema = self::get_schema();
        $defaults = [];

        foreach ($schema['properties'] as $provider => $provider_schema) {
            $defaults[$provider] = self::get_provider_defaults($provider);
        }

        return $defaults;
    }

    /**
     * Get form fields for a specific provider
     *
     * @param string $provider Provider name
     * @return array Form fields
     */
    public static function get_provider_form_fields($provider)
    {
        $schema = self::get_schema();

        if (!isset($schema['properties'][$provider])) {
            return [];
        }

        $provider_schema = $schema['properties'][$provider];
        $fields = [];

        foreach ($provider_schema['properties'] as $key => $property) {
            $field = [
                'name' => $key,
                'type' => $property['type'],
                'title' => $property['title'] ?? ucwords(str_replace('_', ' ', $key)),
                'description' => $property['description'] ?? '',
                'required' => in_array($key, $provider_schema['required'] ?? []),
                'encrypted' => $property['encrypted'] ?? false
            ];

            // Add type-specific properties
            switch ($property['type']) {
                case 'string':
                    if (isset($property['enum'])) {
                        $field['options'] = array_combine($property['enum'], $property['enum']);
                    }
                    if (isset($property['minLength'])) {
                        $field['minlength'] = $property['minLength'];
                    }
                    if (isset($property['maxLength'])) {
                        $field['maxlength'] = $property['maxLength'];
                    }
                    if (isset($property['placeholder'])) {
                        $field['placeholder'] = $property['placeholder'];
                    }
                    break;

                case 'integer':
                case 'number':
                    if (isset($property['min'])) {
                        $field['min'] = $property['min'];
                    }
                    if (isset($property['max'])) {
                        $field['max'] = $property['max'];
                    }
                    if ($property['type'] === 'number') {
                        $field['step'] = 0.1;
                    }
                    break;
            }

            if (isset($property['default'])) {
                $field['default'] = $property['default'];
            }

            $fields[$key] = $field;
        }

        return $fields;
    }

    /**
     * Get supported providers
     *
     * @return array Provider names
     */
    public static function get_providers()
    {
        $schema = self::get_schema();
        return array_keys($schema['properties']);
    }
}