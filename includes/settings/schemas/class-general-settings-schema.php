<?php
/**
 * General Settings Schema
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Schemas
 */

namespace AI_Manager_Pro\Settings\Schemas;

/**
 * General Settings Schema Class
 */
class General_Settings_Schema
{

    /**
     * Get general settings schema
     *
     * @return array Schema definition
     */
    public static function get_schema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'default_provider' => [
                    'type' => 'string',
                    'enum' => ['openai', 'claude', 'openrouter'],
                    'default' => 'openai',
                    'title' => __('Default AI Provider', 'ai-website-manager-pro'),
                    'description' => __('The default AI provider to use for content generation', 'ai-website-manager-pro')
                ],
                'auto_publish' => [
                    'type' => 'boolean',
                    'default' => false,
                    'title' => __('Auto Publish', 'ai-website-manager-pro'),
                    'description' => __('Automatically publish generated content', 'ai-website-manager-pro')
                ],
                'default_post_status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'publish', 'private', 'pending'],
                    'default' => 'draft',
                    'title' => __('Default Post Status', 'ai-website-manager-pro'),
                    'description' => __('Default status for generated posts', 'ai-website-manager-pro')
                ],
                'enable_logging' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Enable Logging', 'ai-website-manager-pro'),
                    'description' => __('Enable system logging for debugging and monitoring', 'ai-website-manager-pro')
                ],
                'log_level' => [
                    'type' => 'string',
                    'enum' => ['debug', 'info', 'warning', 'error'],
                    'default' => 'info',
                    'title' => __('Log Level', 'ai-website-manager-pro'),
                    'description' => __('Minimum log level to record', 'ai-website-manager-pro')
                ],
                'max_content_length' => [
                    'type' => 'integer',
                    'min' => 100,
                    'max' => 10000,
                    'default' => 2000,
                    'title' => __('Max Content Length', 'ai-website-manager-pro'),
                    'description' => __('Maximum length for generated content (in words)', 'ai-website-manager-pro')
                ],
                'content_language' => [
                    'type' => 'string',
                    'enum' => ['en', 'he', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'],
                    'default' => 'en',
                    'title' => __('Content Language', 'ai-website-manager-pro'),
                    'description' => __('Default language for generated content', 'ai-website-manager-pro')
                ],
                'enable_seo_optimization' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Enable SEO Optimization', 'ai-website-manager-pro'),
                    'description' => __('Automatically optimize content for SEO', 'ai-website-manager-pro')
                ],
                'default_post_category' => [
                    'type' => 'integer',
                    'min' => 0,
                    'default' => 0,
                    'title' => __('Default Post Category', 'ai-website-manager-pro'),
                    'description' => __('Default category ID for generated posts (0 for uncategorized)', 'ai-website-manager-pro')
                ],
                'enable_featured_images' => [
                    'type' => 'boolean',
                    'default' => false,
                    'title' => __('Enable Featured Images', 'ai-website-manager-pro'),
                    'description' => __('Automatically generate or assign featured images', 'ai-website-manager-pro')
                ],
                'content_tone' => [
                    'type' => 'string',
                    'enum' => ['professional', 'casual', 'friendly', 'authoritative', 'conversational', 'technical'],
                    'default' => 'professional',
                    'title' => __('Content Tone', 'ai-website-manager-pro'),
                    'description' => __('Default tone for generated content', 'ai-website-manager-pro')
                ],
                'enable_plagiarism_check' => [
                    'type' => 'boolean',
                    'default' => false,
                    'title' => __('Enable Plagiarism Check', 'ai-website-manager-pro'),
                    'description' => __('Check generated content for plagiarism', 'ai-website-manager-pro')
                ],
                'backup_frequency' => [
                    'type' => 'string',
                    'enum' => ['daily', 'weekly', 'monthly', 'never'],
                    'default' => 'weekly',
                    'title' => __('Backup Frequency', 'ai-website-manager-pro'),
                    'description' => __('How often to automatically backup settings', 'ai-website-manager-pro')
                ],
                'cleanup_logs_after' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 365,
                    'default' => 30,
                    'title' => __('Cleanup Logs After (Days)', 'ai-website-manager-pro'),
                    'description' => __('Number of days to keep log entries', 'ai-website-manager-pro')
                ]
            ],
            'required' => ['default_provider', 'default_post_status', 'log_level']
        ];
    }

    /**
     * Get default values
     *
     * @return array Default values
     */
    public static function get_defaults()
    {
        $schema = self::get_schema();
        $defaults = [];

        foreach ($schema['properties'] as $key => $property) {
            if (isset($property['default'])) {
                $defaults[$key] = $property['default'];
            }
        }

        return $defaults;
    }

    /**
     * Get field configuration for admin forms
     *
     * @return array Field configuration
     */
    public static function get_form_fields()
    {
        $schema = self::get_schema();
        $fields = [];

        foreach ($schema['properties'] as $key => $property) {
            $field = [
                'name' => $key,
                'type' => $property['type'],
                'title' => $property['title'] ?? ucwords(str_replace('_', ' ', $key)),
                'description' => $property['description'] ?? '',
                'required' => in_array($key, $schema['required'] ?? [])
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
                    break;

                case 'integer':
                    if (isset($property['min'])) {
                        $field['min'] = $property['min'];
                    }
                    if (isset($property['max'])) {
                        $field['max'] = $property['max'];
                    }
                    break;

                case 'boolean':
                    $field['type'] = 'checkbox';
                    break;
            }

            if (isset($property['default'])) {
                $field['default'] = $property['default'];
            }

            $fields[$key] = $field;
        }

        return $fields;
    }
}