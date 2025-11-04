<?php
/**
 * Brand Settings Schema
 *
 * @package AI_Manager_Pro
 * @subpackage Settings\Schemas
 */

namespace AI_Manager_Pro\Settings\Schemas;

/**
 * Brand Settings Schema Class
 */
class Brand_Settings_Schema
{

    /**
     * Get brand settings schema
     *
     * @return array Schema definition
     */
    public static function get_schema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'active_brand_id' => [
                    'type' => 'integer',
                    'min' => 0,
                    'default' => 0,
                    'title' => __('Active Brand ID', 'ai-website-manager-pro'),
                    'description' => __('ID of the currently active brand', 'ai-website-manager-pro')
                ],
                'default_tone' => [
                    'type' => 'string',
                    'enum' => ['professional', 'casual', 'friendly', 'authoritative', 'conversational', 'technical'],
                    'default' => 'professional',
                    'title' => __('Default Tone', 'ai-website-manager-pro'),
                    'description' => __('Default tone for brand content', 'ai-website-manager-pro')
                ],
                'auto_apply_brand' => [
                    'type' => 'boolean',
                    'default' => true,
                    'title' => __('Auto Apply Brand', 'ai-website-manager-pro'),
                    'description' => __('Automatically apply brand guidelines to generated content', 'ai-website-manager-pro')
                ],
                'brand_consistency_level' => [
                    'type' => 'string',
                    'enum' => ['strict', 'moderate', 'flexible'],
                    'default' => 'moderate',
                    'title' => __('Brand Consistency Level', 'ai-website-manager-pro'),
                    'description' => __('How strictly to enforce brand guidelines', 'ai-website-manager-pro')
                ]
            ],
            'required' => []
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

            if (isset($property['enum'])) {
                $field['options'] = array_combine($property['enum'], $property['enum']);
            }

            if (isset($property['default'])) {
                $field['default'] = $property['default'];
            }

            $fields[$key] = $field;
        }

        return $fields;
    }
}