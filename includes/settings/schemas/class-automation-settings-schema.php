<?php
/**
 * Automation Settings Schema
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Schemas
 */

namespace AI_Manager_Pro\Settings\Schemas;

/**
 * Automation Settings Schema Class
 */
class Automation_Settings_Schema
{

    /**
     * Get automation settings schema
     *
     * @return array Schema definition
     */
    public static function get_schema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'enable_automation' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Enable Automation', 'ai-website-manager-pro'),
                    'description' => __('Enable automated content generation and publishing', 'ai-website-manager-pro')
                ],
                'max_concurrent_tasks' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 10,
                    'default' => 3,
                    'title' => __('Max Concurrent Tasks', 'ai-website-manager-pro'),
                    'description' => __('Maximum number of automation tasks to run simultaneously', 'ai-website-manager-pro')
                ],
                'default_schedule_timezone' => [
                    'type' => 'string',
                    'default' => 'UTC',
                    'title' => __('Default Timezone', 'ai-website-manager-pro'),
                    'description' => __('Default timezone for scheduled tasks', 'ai-website-manager-pro')
                ],
                'notification_email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'title' => __('Notification Email', 'ai-website-manager-pro'),
                    'description' => __('Email address for automation notifications', 'ai-website-manager-pro')
                ],
                'retry_failed_tasks' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Retry Failed Tasks', 'ai-website-manager-pro'),
                    'description' => __('Automatically retry failed automation tasks', 'ai-website-manager-pro')
                ],
                'max_retry_attempts' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 5,
                    'default' => 3,
                    'title' => __('Max Retry Attempts', 'ai-website-manager-pro'),
                    'description' => __('Maximum number of retry attempts for failed tasks', 'ai-website-manager-pro')
                ],
                'task_timeout' => [
                    'type' => 'integer',
                    'min' => 30,
                    'max' => 600,
                    'default' => 120,
                    'title' => __('Task Timeout (seconds)', 'ai-website-manager-pro'),
                    'description' => __('Maximum time to wait for a task to complete', 'ai-website-manager-pro')
                ],
                'enable_smart_scheduling' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Enable Smart Scheduling', 'ai-website-manager-pro'),
                    'description' => __('Use AI to optimize task scheduling based on performance', 'ai-website-manager-pro')
                ],
                'content_quality_threshold' => [
                    'type' => 'number',
                    'min' => 0,
                    'max' => 1,
                    'default' => 0.7,
                    'title' => __('Content Quality Threshold', 'ai-website-manager-pro'),
                    'description' => __('Minimum quality score for auto-published content (0-1)', 'ai-website-manager-pro')
                ],
                'enable_content_review' => [
                    'type' => 'boolean',
                    'default' => false,
                    'title' => __('Enable Content Review', 'ai-website-manager-pro'),
                    'description' => __('Require manual review before publishing automated content', 'ai-website-manager-pro')
                ]
            ],
            'required' => ['enable_automation']
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
     * Get form fields
     *
     * @return array Form fields
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

                case 'string':
                    if (isset($property['format'])) {
                        $field['format'] = $property['format'];
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

    /**
     * Get timezone options
     *
     * @return array Timezone options
     */
    public static function get_timezone_options()
    {
        $timezones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney'
        ];

        return $timezones;
    }
}