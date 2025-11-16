<?php
/**
 * Enhanced Brand Schema
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * Enhanced Brand Schema Class
 *
 * Extends the base brand schema with topic_pool and content_calendar support
 */
class Enhanced_Brand_Schema {

    /**
     * Get enhanced brand schema properties
     * These are additional properties beyond the base brand schema
     *
     * @return array Enhanced schema properties
     */
    public static function get_enhanced_properties() {
        return [
            'topic_pool' => [
                'type' => 'object',
                'description' => 'Dynamic topic pool for automated content generation',
                'properties' => [
                    'enabled' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable topic pool functionality'
                    ],
                    'auto_refresh' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Auto-refresh topics using AI monthly'
                    ],
                    'categories' => [
                        'type' => 'array',
                        'description' => 'Topic categories with weighted distribution',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => [
                                    'type' => 'string',
                                    'description' => 'Category name (e.g., "Core Expertise")'
                                ],
                                'weight' => [
                                    'type' => 'integer',
                                    'min' => 0,
                                    'max' => 100,
                                    'description' => 'Percentage weight (total should be 100)'
                                ],
                                'topics' => [
                                    'type' => 'array',
                                    'description' => 'Topics in this category',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => [
                                                'type' => 'string',
                                                'description' => 'Topic title'
                                            ],
                                            'keywords' => [
                                                'type' => 'array',
                                                'items' => ['type' => 'string'],
                                                'description' => 'Keywords for this topic'
                                            ],
                                            'target_length' => [
                                                'type' => 'string',
                                                'enum' => ['short', 'medium', 'long'],
                                                'description' => 'Target content length'
                                            ],
                                            'content_type' => [
                                                'type' => 'string',
                                                'enum' => ['blog_post', 'article', 'guide', 'review', 'product', 'social_media', 'newsletter'],
                                                'description' => 'Preferred content type'
                                            ],
                                            'difficulty' => [
                                                'type' => 'string',
                                                'enum' => ['beginner', 'intermediate', 'advanced'],
                                                'default' => 'intermediate',
                                                'description' => 'Content difficulty level'
                                            ],
                                            'frequency' => [
                                                'type' => 'string',
                                                'enum' => ['once', 'monthly', 'quarterly', 'yearly'],
                                                'default' => 'once',
                                                'description' => 'How often to reuse this topic'
                                            ],
                                            'priority' => [
                                                'type' => 'string',
                                                'enum' => ['low', 'medium', 'high'],
                                                'default' => 'medium',
                                                'description' => 'Topic priority'
                                            ],
                                            'last_used' => [
                                                'type' => ['string', 'null'],
                                                'format' => 'date-time',
                                                'description' => 'Last time this topic was used'
                                            ],
                                            'usage_count' => [
                                                'type' => 'integer',
                                                'default' => 0,
                                                'description' => 'Number of times topic was used'
                                            ],
                                            'performance_score' => [
                                                'type' => ['number', 'null'],
                                                'min' => 0,
                                                'max' => 10,
                                                'description' => 'Performance score from analytics'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'topic_selection_rules' => [
                        'type' => 'object',
                        'description' => 'Rules for selecting topics from pool',
                        'properties' => [
                            'avoid_repetition_days' => [
                                'type' => 'integer',
                                'default' => 30,
                                'description' => 'Days before reusing a topic'
                            ],
                            'prefer_unused' => [
                                'type' => 'boolean',
                                'default' => true,
                                'description' => 'Prefer topics never used before'
                            ],
                            'rotate_categories' => [
                                'type' => 'boolean',
                                'default' => true,
                                'description' => 'Rotate between categories'
                            ],
                            'respect_weights' => [
                                'type' => 'boolean',
                                'default' => true,
                                'description' => 'Follow category weight percentages'
                            ],
                            'consider_performance' => [
                                'type' => 'boolean',
                                'default' => false,
                                'description' => 'Favor high-performing topics'
                            ]
                        ]
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
                        'description' => 'Monthly seasonal content themes',
                        'properties' => [
                            'january' => self::get_month_schema(),
                            'february' => self::get_month_schema(),
                            'march' => self::get_month_schema(),
                            'april' => self::get_month_schema(),
                            'may' => self::get_month_schema(),
                            'june' => self::get_month_schema(),
                            'july' => self::get_month_schema(),
                            'august' => self::get_month_schema(),
                            'september' => self::get_month_schema(),
                            'october' => self::get_month_schema(),
                            'november' => self::get_month_schema(),
                            'december' => self::get_month_schema()
                        ]
                    ],
                    'evergreen_topics' => [
                        'type' => 'array',
                        'description' => 'Evergreen topics that are always relevant',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'Evergreen category name'
                                ],
                                'topics' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'priority' => [
                                                'type' => 'string',
                                                'enum' => ['low', 'medium', 'high']
                                            ],
                                            'frequency' => [
                                                'type' => 'string',
                                                'enum' => ['once', 'monthly', 'quarterly', 'yearly']
                                            ],
                                            'type' => [
                                                'type' => 'string',
                                                'enum' => ['blog_post', 'article', 'guide', 'review']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'trending_sources' => [
                        'type' => 'object',
                        'description' => 'Sources for trending topics',
                        'properties' => [
                            'enabled' => [
                                'type' => 'boolean',
                                'default' => false,
                                'description' => 'Enable trending topic detection'
                            ],
                            'rss_feeds' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                    'format' => 'uri'
                                ],
                                'description' => 'RSS feeds to monitor for trends'
                            ],
                            'keywords_to_track' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Keywords to track for trends'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get month schema for seasonal content
     *
     * @return array Month schema
     */
    private static function get_month_schema() {
        return [
            'type' => 'object',
            'properties' => [
                'themes' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Monthly themes'
                ],
                'topics' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'priority' => [
                                'type' => 'string',
                                'enum' => ['low', 'medium', 'high']
                            ],
                            'type' => [
                                'type' => 'string',
                                'enum' => ['blog_post', 'article', 'guide', 'review']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get default topic pool structure
     *
     * @return array Default topic pool
     */
    public static function get_default_topic_pool() {
        return [
            'enabled' => true,
            'auto_refresh' => false,
            'categories' => [
                [
                    'name' => 'Core Expertise',
                    'weight' => 40,
                    'topics' => []
                ],
                [
                    'name' => 'Industry News',
                    'weight' => 30,
                    'topics' => []
                ],
                [
                    'name' => 'Customer Success',
                    'weight' => 20,
                    'topics' => []
                ],
                [
                    'name' => 'Thought Leadership',
                    'weight' => 10,
                    'topics' => []
                ]
            ],
            'topic_selection_rules' => [
                'avoid_repetition_days' => 30,
                'prefer_unused' => true,
                'rotate_categories' => true,
                'respect_weights' => true,
                'consider_performance' => false
            ]
        ];
    }

    /**
     * Get default content calendar structure
     *
     * @return array Default content calendar
     */
    public static function get_default_content_calendar() {
        return [
            'enabled' => true,
            'strategy' => 'mixed',
            'seasonal_content' => [],
            'evergreen_topics' => [],
            'trending_sources' => [
                'enabled' => false,
                'rss_feeds' => [],
                'keywords_to_track' => []
            ]
        ];
    }
}
