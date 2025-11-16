<?php
/**
 * Brand Manager
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Brand_Management
 */

namespace AI_Manager_Pro\Modules\Brand_Management;

use AI_Manager_Pro\Modules\Security\Security_Manager;
use Monolog\Logger;

/**
 * Brand Manager Class
 */
class Brand_Manager {
    
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
     * Brand JSON schema
     *
     * @var array
     */
    private $brand_schema;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     * @param Security_Manager $security Security manager instance
     */
    public function __construct(Logger $logger, Security_Manager $security) {
        $this->logger = $logger;
        $this->security = $security;
        $this->brand_schema = $this->get_brand_schema();
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_ai_manager_pro_import_brand', [$this, 'handle_brand_import']);
        add_action('wp_ajax_ai_manager_pro_export_brand', [$this, 'handle_brand_export']);
        add_action('wp_ajax_ai_manager_pro_get_brand_template', [$this, 'handle_get_brand_template']);
    }
    
    /**
     * Get brand JSON schema
     *
     * @return array Brand schema
     */
    private function get_brand_schema() {
        return [
            'type' => 'object',
            'required' => ['name', 'description', 'industry'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 255,
                    'description' => 'Brand name'
                ],
                'description' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 1000,
                    'description' => 'Brand description'
                ],
                'industry' => [
                    'type' => 'string',
                    'enum' => [
                        'technology',
                        'healthcare',
                        'finance',
                        'education',
                        'retail',
                        'manufacturing',
                        'hospitality',
                        'real-estate',
                        'automotive',
                        'food-beverage',
                        'fashion',
                        'entertainment',
                        'consulting',
                        'non-profit',
                        'other'
                    ],
                    'description' => 'Industry sector'
                ],
                'target_audience' => [
                    'type' => 'object',
                    'properties' => [
                        'demographics' => [
                            'type' => 'object',
                            'properties' => [
                                'age_range' => [
                                    'type' => 'string',
                                    'description' => 'Target age range (e.g., "25-45")'
                                ],
                                'gender' => [
                                    'type' => 'string',
                                    'enum' => ['male', 'female', 'all'],
                                    'description' => 'Target gender'
                                ],
                                'income_level' => [
                                    'type' => 'string',
                                    'enum' => ['low', 'middle', 'high', 'all'],
                                    'description' => 'Target income level'
                                ],
                                'education' => [
                                    'type' => 'string',
                                    'enum' => ['high-school', 'college', 'graduate', 'all'],
                                    'description' => 'Target education level'
                                ]
                            ]
                        ],
                        'psychographics' => [
                            'type' => 'object',
                            'properties' => [
                                'interests' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Target audience interests'
                                ],
                                'values' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Target audience values'
                                ],
                                'lifestyle' => [
                                    'type' => 'string',
                                    'description' => 'Target lifestyle description'
                                ]
                            ]
                        ]
                    ]
                ],
                'brand_voice' => [
                    'type' => 'object',
                    'properties' => [
                        'tone' => [
                            'type' => 'string',
                            'enum' => [
                                'professional',
                                'friendly',
                                'casual',
                                'authoritative',
                                'playful',
                                'inspirational',
                                'educational',
                                'conversational'
                            ],
                            'description' => 'Brand tone of voice'
                        ],
                        'personality_traits' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Brand personality traits'
                        ],
                        'communication_style' => [
                            'type' => 'string',
                            'description' => 'How the brand communicates'
                        ],
                        'language_preferences' => [
                            'type' => 'object',
                            'properties' => [
                                'formality' => [
                                    'type' => 'string',
                                    'enum' => ['formal', 'informal', 'mixed'],
                                    'description' => 'Language formality level'
                                ],
                                'technical_level' => [
                                    'type' => 'string',
                                    'enum' => ['basic', 'intermediate', 'advanced'],
                                    'description' => 'Technical language level'
                                ],
                                'avoid_words' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Words to avoid in content'
                                ],
                                'preferred_words' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Preferred words to use'
                                ]
                            ]
                        ]
                    ]
                ],
                'key_messages' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Key brand messages'
                ],
                'unique_selling_points' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Unique selling propositions'
                ],
                'competitors' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Main competitors'
                ],
                'content_guidelines' => [
                    'type' => 'object',
                    'properties' => [
                        'topics_to_cover' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Topics the brand should cover'
                        ],
                        'topics_to_avoid' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Topics the brand should avoid'
                        ],
                        'content_types' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'enum' => [
                                    'blog-posts',
                                    'social-media',
                                    'newsletters',
                                    'case-studies',
                                    'tutorials',
                                    'news',
                                    'reviews',
                                    'interviews'
                                ]
                            ],
                            'description' => 'Preferred content types'
                        ],
                        'content_length' => [
                            'type' => 'object',
                            'properties' => [
                                'short' => [
                                    'type' => 'string',
                                    'description' => 'Short content length range'
                                ],
                                'medium' => [
                                    'type' => 'string',
                                    'description' => 'Medium content length range'
                                ],
                                'long' => [
                                    'type' => 'string',
                                    'description' => 'Long content length range'
                                ]
                            ]
                        ]
                    ]
                ],
                'seo_keywords' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Primary SEO keywords'
                ],
                'social_media' => [
                    'type' => 'object',
                    'properties' => [
                        'platforms' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'enum' => [
                                    'facebook',
                                    'twitter',
                                    'instagram',
                                    'linkedin',
                                    'youtube',
                                    'tiktok',
                                    'pinterest'
                                ]
                            ],
                            'description' => 'Active social media platforms'
                        ],
                        'hashtags' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Brand hashtags'
                        ]
                    ]
                ],
                'contact_info' => [
                    'type' => 'object',
                    'properties' => [
                        'website' => [
                            'type' => 'string',
                            'format' => 'uri',
                            'description' => 'Brand website URL'
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'Contact email'
                        ],
                        'phone' => [
                            'type' => 'string',
                            'description' => 'Contact phone number'
                        ],
                        'address' => [
                            'type' => 'string',
                            'description' => 'Business address'
                        ]
                    ]
                ],
                'topic_pool' => [
                    'type' => 'object',
                    'description' => 'Dynamic topic pool for automated content generation',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean',
                            'default' => true,
                            'description' => 'Enable topic pool functionality'
                        ],
                        'categories' => [
                            'type' => 'array',
                            'description' => 'Topic categories with weighted distribution',
                            'items' => ['type' => 'object']
                        ],
                        'topic_selection_rules' => [
                            'type' => 'object',
                            'description' => 'Rules for selecting topics from pool'
                        ]
                    ]
                ],
                'content_calendar' => [
                    'type' => 'object',
                    'description' => 'Content calendar for strategic planning',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean',
                            'default' => true,
                            'description' => 'Enable content calendar functionality'
                        ],
                        'strategy' => [
                            'type' => 'string',
                            'enum' => ['seasonal', 'evergreen', 'mixed', 'trending'],
                            'default' => 'mixed',
                            'description' => 'Content calendar strategy'
                        ],
                        'seasonal_content' => [
                            'type' => 'object',
                            'description' => 'Monthly seasonal content themes'
                        ],
                        'evergreen_topics' => [
                            'type' => 'array',
                            'description' => 'Evergreen topics that are always relevant'
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get brand template
     *
     * @return array Brand template
     */
    public function get_brand_template() {
        return [
            'name' => 'Example Brand',
            'description' => 'A comprehensive description of the brand, its mission, vision, and what it stands for.',
            'industry' => 'technology',
            'target_audience' => [
                'demographics' => [
                    'age_range' => '25-45',
                    'gender' => 'all',
                    'income_level' => 'middle',
                    'education' => 'college'
                ],
                'psychographics' => [
                    'interests' => ['technology', 'innovation', 'productivity'],
                    'values' => ['quality', 'reliability', 'innovation'],
                    'lifestyle' => 'Tech-savvy professionals who value efficiency and quality'
                ]
            ],
            'brand_voice' => [
                'tone' => 'professional',
                'personality_traits' => ['knowledgeable', 'helpful', 'innovative', 'trustworthy'],
                'communication_style' => 'Clear, informative, and solution-oriented',
                'language_preferences' => [
                    'formality' => 'mixed',
                    'technical_level' => 'intermediate',
                    'avoid_words' => ['cheap', 'basic', 'simple'],
                    'preferred_words' => ['premium', 'advanced', 'innovative', 'reliable']
                ]
            ],
            'key_messages' => [
                'We deliver innovative solutions that drive business growth',
                'Quality and reliability are at the core of everything we do',
                'Empowering businesses through cutting-edge technology'
            ],
            'unique_selling_points' => [
                '24/7 customer support',
                'Industry-leading security standards',
                'Scalable solutions for businesses of all sizes'
            ],
            'competitors' => [
                'Competitor A',
                'Competitor B',
                'Competitor C'
            ],
            'content_guidelines' => [
                'topics_to_cover' => [
                    'Industry trends',
                    'Product updates',
                    'Customer success stories',
                    'Best practices',
                    'Technology insights'
                ],
                'topics_to_avoid' => [
                    'Political content',
                    'Controversial subjects',
                    'Competitor criticism'
                ],
                'content_types' => [
                    'blog-posts',
                    'case-studies',
                    'tutorials',
                    'news'
                ],
                'content_length' => [
                    'short' => '300-500 words',
                    'medium' => '800-1200 words',
                    'long' => '1500-2500 words'
                ]
            ],
            'seo_keywords' => [
                'innovative solutions',
                'business technology',
                'enterprise software',
                'digital transformation'
            ],
            'social_media' => [
                'platforms' => ['linkedin', 'twitter', 'facebook'],
                'hashtags' => ['#innovation', '#technology', '#business', '#solutions']
            ],
            'contact_info' => [
                'website' => 'https://example.com',
                'email' => 'contact@example.com',
                'phone' => '+1-555-0123',
                'address' => '123 Business St, City, State 12345'
            ]
        ];
    }
    
    /**
     * Create a new brand
     *
     * @param array $brand_data Brand data
     * @return int|false Brand ID on success, false on failure
     */
    public function create_brand($brand_data) {
        global $wpdb;
        
        // Validate brand data
        $validation_result = $this->validate_brand_data($brand_data);
        if ($validation_result !== true) {
            $this->logger->error('Brand validation failed', [
                'errors' => $validation_result
            ]);
            return false;
        }
        
        // Sanitize brand name
        $brand_name = $this->security->sanitize_input($brand_data['name'], 'text');
        $brand_description = $this->security->sanitize_input($brand_data['description'] ?? '', 'textarea');
        
        // Check if brand name already exists
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE name = %s",
            $brand_name
        ));
        
        if ($existing) {
            $this->logger->warning('Brand name already exists', [
                'name' => $brand_name
            ]);
            return false;
        }
        
        // Insert brand
        $result = $wpdb->insert(
            $table_name,
            [
                'name' => $brand_name,
                'description' => $brand_description,
                'brand_data' => json_encode($brand_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                'is_active' => false,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s']
        );
        
        if ($result === false) {
            $this->logger->error('Failed to create brand', [
                'name' => $brand_name,
                'error' => $wpdb->last_error
            ]);
            return false;
        }
        
        $brand_id = $wpdb->insert_id;
        
        $this->logger->info('Brand created successfully', [
            'brand_id' => $brand_id,
            'name' => $brand_name,
            'user_id' => get_current_user_id()
        ]);
        
        return $brand_id;
    }
    
    /**
     * Import brand from JSON
     *
     * @param string $json_data JSON data
     * @return int|false Brand ID on success, false on failure
     */
    public function import_brand_from_json($json_data) {
        // Sanitize and validate JSON
        $sanitized_json = $this->security->sanitize_input($json_data, 'json');
        if ($sanitized_json === false) {
            return false;
        }
        
        $brand_data = json_decode($sanitized_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON in brand import', [
                'error' => json_last_error_msg()
            ]);
            return false;
        }
        
        return $this->create_brand($brand_data);
    }
    
    /**
     * Import brand from uploaded file
     *
     * @param array $file_data Uploaded file data
     * @return int|false Brand ID on success, false on failure
     */
    public function import_brand_from_file($file_data) {
        // Validate file
        if (!isset($file_data['tmp_name']) || !is_uploaded_file($file_data['tmp_name'])) {
            $this->logger->error('Invalid uploaded file');
            return false;
        }
        
        // Check file type
        $file_type = $file_data['type'] ?? '';
        if ($file_type !== 'application/json' && !str_ends_with($file_data['name'], '.json')) {
            $this->logger->error('Invalid file type for brand import', [
                'type' => $file_type,
                'name' => $file_data['name']
            ]);
            return false;
        }
        
        // Check file size (max 1MB)
        if ($file_data['size'] > 1024 * 1024) {
            $this->logger->error('Brand file too large', [
                'size' => $file_data['size']
            ]);
            return false;
        }
        
        // Read file content
        $json_content = file_get_contents($file_data['tmp_name']);
        if ($json_content === false) {
            $this->logger->error('Failed to read brand file');
            return false;
        }
        
        return $this->import_brand_from_json($json_content);
    }
    
    /**
     * Export brand as JSON
     *
     * @param int $brand_id Brand ID
     * @return string|false JSON data on success, false on failure
     */
    public function export_brand_as_json($brand_id) {
        $brand = $this->get_brand($brand_id);
        if (!$brand) {
            return false;
        }
        
        $brand_data = json_decode($brand->brand_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid brand data JSON', [
                'brand_id' => $brand_id,
                'error' => json_last_error_msg()
            ]);
            return false;
        }
        
        return json_encode($brand_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * Validate brand data against schema
     *
     * @param array $brand_data Brand data to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_brand_data($brand_data) {
        return $this->security->validate_json_schema(
            json_encode($brand_data),
            $this->brand_schema
        );
    }
    
    /**
     * Get brand by ID
     *
     * @param int $brand_id Brand ID
     * @return object|null Brand object or null if not found
     */
    public function get_brand($brand_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $brand_id
        ));
    }
    
    /**
     * Get all brands
     *
     * @param array $args Query arguments
     * @return array Brands array
     */
    public function get_brands($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'search' => '',
            'active_only' => false
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $where_clauses = [];
        $where_values = [];
        
        // Search filter
        if (!empty($args['search'])) {
            $where_clauses[] = "(name LIKE %s OR description LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Active only filter
        if ($args['active_only']) {
            $where_clauses[] = "is_active = 1";
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        $order_sql = sprintf(
            'ORDER BY %s %s',
            sanitize_sql_orderby($args['orderby']),
            $args['order'] === 'DESC' ? 'DESC' : 'ASC'
        );
        
        $limit_sql = $wpdb->prepare('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        
        $sql = "SELECT * FROM {$table_name} {$where_sql} {$order_sql} {$limit_sql}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get active brand
     *
     * @return object|null Active brand or null if none active
     */
    public function get_active_brand() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        
        return $wpdb->get_row(
            "SELECT * FROM {$table_name} WHERE is_active = 1 LIMIT 1"
        );
    }
    
    /**
     * Set active brand
     *
     * @param int $brand_id Brand ID
     * @return bool Success status
     */
    public function set_active_brand($brand_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        
        // Deactivate all brands
        $wpdb->update(
            $table_name,
            ['is_active' => 0],
            ['is_active' => 1],
            ['%d'],
            ['%d']
        );
        
        // Activate selected brand
        $result = $wpdb->update(
            $table_name,
            [
                'is_active' => 1,
                'updated_at' => current_time('mysql')
            ],
            ['id' => $brand_id],
            ['%d', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            $this->logger->info('Brand activated', [
                'brand_id' => $brand_id,
                'user_id' => get_current_user_id()
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Update brand
     *
     * @param int $brand_id Brand ID
     * @param array $brand_data Updated brand data
     * @return bool Success status
     */
    public function update_brand($brand_id, $brand_data) {
        global $wpdb;
        
        // Validate brand data
        $validation_result = $this->validate_brand_data($brand_data);
        if ($validation_result !== true) {
            $this->logger->error('Brand validation failed during update', [
                'brand_id' => $brand_id,
                'errors' => $validation_result
            ]);
            return false;
        }
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        
        $result = $wpdb->update(
            $table_name,
            [
                'name' => $this->security->sanitize_input($brand_data['name'], 'text'),
                'description' => $this->security->sanitize_input($brand_data['description'] ?? '', 'textarea'),
                'brand_data' => json_encode($brand_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $brand_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            $this->logger->info('Brand updated', [
                'brand_id' => $brand_id,
                'user_id' => get_current_user_id()
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete brand
     *
     * @param int $brand_id Brand ID
     * @return bool Success status
     */
    public function delete_brand($brand_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        
        $result = $wpdb->delete(
            $table_name,
            ['id' => $brand_id],
            ['%d']
        );
        
        if ($result !== false) {
            $this->logger->info('Brand deleted', [
                'brand_id' => $brand_id,
                'user_id' => get_current_user_id()
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle brand import AJAX request
     */
    public function handle_brand_import() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!$this->security->user_can('ai_manager_manage_brands')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $brand_id = false;
            
            // Check if JSON data provided
            if (!empty($_POST['json_data'])) {
                $brand_id = $this->import_brand_from_json($_POST['json_data']);
            }
            // Check if file uploaded
            elseif (!empty($_FILES['brand_file'])) {
                $brand_id = $this->import_brand_from_file($_FILES['brand_file']);
            }
            else {
                wp_send_json_error('No brand data provided');
            }
            
            if ($brand_id) {
                wp_send_json_success([
                    'brand_id' => $brand_id,
                    'message' => 'Brand imported successfully'
                ]);
            } else {
                wp_send_json_error('Failed to import brand');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Brand import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json_error('An error occurred during import');
        }
    }
    
    /**
     * Handle brand export AJAX request
     */
    public function handle_brand_export() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!$this->security->user_can('ai_manager_manage_brands')) {
            wp_die('Insufficient permissions');
        }
        
        $brand_id = intval($_GET['brand_id'] ?? 0);
        if (!$brand_id) {
            wp_die('Invalid brand ID');
        }
        
        $json_data = $this->export_brand_as_json($brand_id);
        if ($json_data === false) {
            wp_die('Failed to export brand');
        }
        
        $brand = $this->get_brand($brand_id);
        $filename = sanitize_file_name($brand->name . '-brand-profile.json');
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json_data));
        
        echo $json_data;
        exit;
    }
    
    /**
     * Handle get brand template AJAX request
     */
    public function handle_get_brand_template() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!$this->security->user_can('ai_manager_manage_brands')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $template = $this->get_brand_template();
        $json_template = json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        wp_send_json_success([
            'template' => $json_template,
            'schema' => $this->brand_schema
        ]);
    }

    /**
     * Select topics from brand's topic pool
     *
     * @param int $brand_id Brand ID
     * @param array $options Selection options
     * @return array Selected topics
     */
    public function select_topics_from_pool($brand_id, $options = []) {
        // Load Smart Topic Selector
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-smart-topic-selector.php';
        $selector = new \AI_Manager_Pro\Brands\Smart_Topic_Selector();

        // Get brand data
        $brand = $this->get_brand($brand_id);
        if (!$brand) {
            return [];
        }

        $brand_data = json_decode($brand->brand_data, true);
        if (!$brand_data) {
            return [];
        }

        // Select topics
        $selected_topics = $selector->select_topics($brand_data, $options);

        // Mark topics as used if requested
        if (!empty($selected_topics) && !empty($options['mark_as_used'])) {
            $brand_data = $selector->mark_topics_as_used($brand_data, $selected_topics);
            $this->update_brand($brand_id, ['brand_data' => json_encode($brand_data)]);
        }

        return $selected_topics;
    }

    /**
     * Get topic statistics for a brand
     *
     * @param int $brand_id Brand ID
     * @return array Topic statistics
     */
    public function get_topic_statistics($brand_id) {
        // Load Smart Topic Selector
        require_once AI_MANAGER_PRO_PLUGIN_DIR . 'includes/brands/class-smart-topic-selector.php';
        $selector = new \AI_Manager_Pro\Brands\Smart_Topic_Selector();

        // Get brand data
        $brand = $this->get_brand($brand_id);
        if (!$brand) {
            return [
                'total_topics' => 0,
                'total_categories' => 0,
                'unused_topics' => 0
            ];
        }

        $brand_data = json_decode($brand->brand_data, true);
        if (!$brand_data) {
            return [
                'total_topics' => 0,
                'total_categories' => 0,
                'unused_topics' => 0
            ];
        }

        return $selector->get_topic_statistics($brand_data);
    }

    /**
     * Get topics for automation
     * This is the main method automation will call
     *
     * @param int|null $brand_id Brand ID (null = active brand)
     * @param int $count Number of topics needed
     * @param array $filters Additional filters
     * @return array Topics with titles
     */
    public function get_topics_for_automation($brand_id = null, $count = 1, $filters = []) {
        // If no brand_id provided, get active brand
        if ($brand_id === null) {
            $brand = $this->get_active_brand();
            if ($brand) {
                $brand_id = $brand->id;
            }
        }

        if (!$brand_id) {
            return [];
        }

        // Selection options
        $options = array_merge([
            'count' => $count,
            'mark_as_used' => true,  // Mark topics as used
            'exclude_recent' => true,  // Avoid recent topics
            'selection_method' => 'weighted_random'  // Default method
        ], $filters);

        // Get topics
        $topics = $this->select_topics_from_pool($brand_id, $options);

        // Convert to simple array of titles for automation
        $topic_titles = [];
        foreach ($topics as $topic) {
            $topic_titles[] = $topic['title'] ?? 'Untitled Topic';
        }

        return $topic_titles;
    }
}

