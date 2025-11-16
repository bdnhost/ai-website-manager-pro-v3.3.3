<?php
/**
 * Content Gap Analyzer
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * Content Gap Analyzer Class
 *
 * Analyzes content gaps and suggests topics to fill them
 */
class Content_Gap_Analyzer {

    /**
     * Logger instance
     *
     * @var mixed
     */
    private $logger;

    /**
     * Constructor
     *
     * @param mixed $logger Logger instance
     */
    public function __construct($logger = null) {
        $this->logger = $logger;
    }

    /**
     * Analyze content gaps for a brand
     *
     * @param int $brand_id Brand ID
     * @param array $options Analysis options
     * @return array Analysis results
     */
    public function analyze_gaps($brand_id, $options = []) {
        global $wpdb;

        $defaults = [
            'days_back' => 90,  // Analyze last 90 days
            'include_competitors' => false  // Future: competitor analysis
        ];

        $options = array_merge($defaults, $options);

        // Get brand
        $table_name = $wpdb->prefix . 'ai_manager_pro_brands';
        $brand = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $brand_id
        ));

        if (!$brand) {
            return [
                'success' => false,
                'error' => 'Brand not found'
            ];
        }

        $brand_data = json_decode($brand->brand_data, true);

        // Analyze different gap types
        $gaps = [];

        // 1. Category coverage gaps
        $gaps['category_gaps'] = $this->analyze_category_gaps($brand_data, $options);

        // 2. Temporal gaps (publishing frequency)
        $gaps['temporal_gaps'] = $this->analyze_temporal_gaps($options);

        // 3. Topic coverage gaps
        $gaps['topic_gaps'] = $this->analyze_topic_gaps($brand_data, $options);

        // 4. Content type gaps
        $gaps['content_type_gaps'] = $this->analyze_content_type_gaps($brand_data, $options);

        // 5. Difficulty level gaps
        $gaps['difficulty_gaps'] = $this->analyze_difficulty_gaps($brand_data, $options);

        // Generate recommendations
        $recommendations = $this->generate_recommendations($gaps, $brand_data);

        return [
            'success' => true,
            'gaps' => $gaps,
            'recommendations' => $recommendations,
            'gap_count' => $this->count_gaps($gaps)
        ];
    }

    /**
     * Analyze category coverage gaps
     *
     * @param array $brand_data Brand data
     * @param array $options Options
     * @return array Category gaps
     */
    private function analyze_category_gaps($brand_data, $options) {
        if (empty($brand_data['topic_pool']) || empty($brand_data['topic_pool']['categories'])) {
            return [
                'has_gaps' => false,
                'message' => 'No topic pool configured'
            ];
        }

        $categories = $brand_data['topic_pool']['categories'];
        $category_usage = [];

        foreach ($categories as $category) {
            $total_topics = count($category['topics']);
            $used_topics = 0;
            $total_usage = 0;

            foreach ($category['topics'] as $topic) {
                $usage_count = $topic['usage_count'] ?? 0;
                if ($usage_count > 0) {
                    $used_topics++;
                }
                $total_usage += $usage_count;
            }

            $target_weight = $category['weight'] ?? 25;

            $category_usage[] = [
                'name' => $category['name'],
                'weight' => $target_weight,
                'total_topics' => $total_topics,
                'used_topics' => $used_topics,
                'unused_topics' => $total_topics - $used_topics,
                'total_usage' => $total_usage,
                'usage_percentage' => $total_usage > 0 ? ($total_usage / array_sum(array_column($category_usage, 'total_usage') ?: [1])) * 100 : 0,
                'gap' => 0  // Will calculate after
            ];
        }

        // Calculate gap (difference between target weight and actual usage)
        $total_usage = array_sum(array_column($category_usage, 'total_usage')) ?: 1;

        foreach ($category_usage as &$usage) {
            $actual_percentage = ($usage['total_usage'] / $total_usage) * 100;
            $usage['usage_percentage'] = round($actual_percentage, 2);
            $usage['gap'] = abs($usage['weight'] - $actual_percentage);
        }

        // Sort by gap (highest first)
        usort($category_usage, function($a, $b) {
            return $b['gap'] <=> $a['gap'];
        });

        return [
            'has_gaps' => true,
            'categories' => $category_usage,
            'largest_gap' => $category_usage[0]['name'] ?? null
        ];
    }

    /**
     * Analyze temporal gaps (days without posts)
     *
     * @param array $options Options
     * @return array Temporal gaps
     */
    private function analyze_temporal_gaps($options) {
        global $wpdb;

        $cutoff_date = date('Y-m-d', strtotime("-{$options['days_back']} days"));

        // Get all post dates in period
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT post_date
             FROM {$wpdb->posts}
             WHERE post_type = 'post'
             AND post_status = 'publish'
             AND post_date >= %s
             ORDER BY post_date ASC",
            $cutoff_date
        ));

        if (empty($posts)) {
            return [
                'has_gaps' => true,
                'message' => 'No posts in analyzed period',
                'gap_days' => $options['days_back']
            ];
        }

        // Find gaps (consecutive days without posts)
        $gaps = [];
        $previous_date = null;

        foreach ($posts as $post) {
            $current_date = strtotime($post->post_date);

            if ($previous_date) {
                $days_between = ($current_date - $previous_date) / 86400;

                if ($days_between > 7) {  // Gap of more than 7 days
                    $gaps[] = [
                        'start_date' => date('Y-m-d', $previous_date),
                        'end_date' => date('Y-m-d', $current_date),
                        'days' => (int) $days_between
                    ];
                }
            }

            $previous_date = $current_date;
        }

        // Check for recent gap (last post to now)
        $last_post_date = strtotime($posts[count($posts) - 1]->post_date);
        $days_since_last = (time() - $last_post_date) / 86400;

        if ($days_since_last > 7) {
            $gaps[] = [
                'start_date' => date('Y-m-d', $last_post_date),
                'end_date' => date('Y-m-d'),
                'days' => (int) $days_since_last,
                'is_current' => true
            ];
        }

        return [
            'has_gaps' => !empty($gaps),
            'gaps' => $gaps,
            'gap_count' => count($gaps),
            'longest_gap' => !empty($gaps) ? max(array_column($gaps, 'days')) : 0
        ];
    }

    /**
     * Analyze topic coverage gaps
     *
     * @param array $brand_data Brand data
     * @param array $options Options
     * @return array Topic gaps
     */
    private function analyze_topic_gaps($brand_data, $options) {
        $topics_to_cover = $brand_data['content_guidelines']['topics_to_cover'] ?? [];

        if (empty($topics_to_cover)) {
            return [
                'has_gaps' => false,
                'message' => 'No topics_to_cover configured'
            ];
        }

        // Get recent posts
        $cutoff_date = date('Y-m-d', strtotime("-{$options['days_back']} days"));
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 500,
            'date_query' => [
                ['after' => $cutoff_date]
            ]
        ]);

        // Check coverage for each topic
        $coverage = [];

        foreach ($topics_to_cover as $topic) {
            $covered = false;
            $mention_count = 0;

            foreach ($posts as $post) {
                $content = strtolower($post->post_title . ' ' . $post->post_content);
                $topic_lower = strtolower($topic);

                if (strpos($content, $topic_lower) !== false) {
                    $covered = true;
                    $mention_count++;
                }
            }

            $coverage[] = [
                'topic' => $topic,
                'covered' => $covered,
                'mention_count' => $mention_count
            ];
        }

        $uncovered = array_filter($coverage, function($item) {
            return !$item['covered'];
        });

        return [
            'has_gaps' => !empty($uncovered),
            'total_topics' => count($topics_to_cover),
            'covered_topics' => count($coverage) - count($uncovered),
            'uncovered_topics' => array_column($uncovered, 'topic'),
            'coverage' => $coverage
        ];
    }

    /**
     * Analyze content type gaps
     *
     * @param array $brand_data Brand data
     * @param array $options Options
     * @return array Content type gaps
     */
    private function analyze_content_type_gaps($brand_data, $options) {
        $preferred_types = $brand_data['content_guidelines']['content_types'] ?? [];

        if (empty($preferred_types)) {
            return [
                'has_gaps' => false,
                'message' => 'No preferred content types configured'
            ];
        }

        // Map content type names to actual types
        $type_mapping = [
            'blog-posts' => 'blog_post',
            'social-media' => 'social_media',
            'case-studies' => 'review',
            'tutorials' => 'guide',
            'news' => 'blog_post',
            'reviews' => 'review',
            'interviews' => 'article'
        ];

        // Count usage in topic pool
        $type_usage = [];

        if (!empty($brand_data['topic_pool']) && !empty($brand_data['topic_pool']['categories'])) {
            foreach ($brand_data['topic_pool']['categories'] as $category) {
                foreach ($category['topics'] as $topic) {
                    $content_type = $topic['content_type'] ?? 'blog_post';
                    $usage_count = $topic['usage_count'] ?? 0;

                    if (!isset($type_usage[$content_type])) {
                        $type_usage[$content_type] = 0;
                    }

                    $type_usage[$content_type] += $usage_count;
                }
            }
        }

        // Check which types are underutilized
        $gaps = [];
        foreach ($preferred_types as $preferred) {
            $actual_type = $type_mapping[$preferred] ?? $preferred;
            $usage = $type_usage[$actual_type] ?? 0;

            if ($usage === 0) {
                $gaps[] = [
                    'type' => $preferred,
                    'usage' => 0,
                    'status' => 'unused'
                ];
            }
        }

        return [
            'has_gaps' => !empty($gaps),
            'type_usage' => $type_usage,
            'unused_types' => $gaps
        ];
    }

    /**
     * Analyze difficulty level gaps
     *
     * @param array $brand_data Brand data
     * @param array $options Options
     * @return array Difficulty gaps
     */
    private function analyze_difficulty_gaps($brand_data, $options) {
        if (empty($brand_data['topic_pool']) || empty($brand_data['topic_pool']['categories'])) {
            return [
                'has_gaps' => false,
                'message' => 'No topic pool configured'
            ];
        }

        // Target distribution: 30% beginner, 50% intermediate, 20% advanced
        $target_distribution = [
            'beginner' => 30,
            'intermediate' => 50,
            'advanced' => 20
        ];

        $difficulty_usage = [
            'beginner' => 0,
            'intermediate' => 0,
            'advanced' => 0
        ];

        $total_usage = 0;

        foreach ($brand_data['topic_pool']['categories'] as $category) {
            foreach ($category['topics'] as $topic) {
                $difficulty = $topic['difficulty'] ?? 'intermediate';
                $usage = $topic['usage_count'] ?? 0;

                $difficulty_usage[$difficulty] += $usage;
                $total_usage += $usage;
            }
        }

        if ($total_usage === 0) {
            return [
                'has_gaps' => false,
                'message' => 'No topics used yet'
            ];
        }

        // Calculate actual percentages and gaps
        $analysis = [];
        foreach ($target_distribution as $level => $target) {
            $actual = ($difficulty_usage[$level] / $total_usage) * 100;
            $gap = abs($target - $actual);

            $analysis[$level] = [
                'target' => $target,
                'actual' => round($actual, 2),
                'usage' => $difficulty_usage[$level],
                'gap' => round($gap, 2)
            ];
        }

        $max_gap = max(array_column($analysis, 'gap'));

        return [
            'has_gaps' => $max_gap > 10,  // Gap > 10% is significant
            'distribution' => $analysis,
            'largest_gap_level' => array_search($max_gap, array_column($analysis, 'gap'))
        ];
    }

    /**
     * Generate recommendations based on gaps
     *
     * @param array $gaps All gaps
     * @param array $brand_data Brand data
     * @return array Recommendations
     */
    private function generate_recommendations($gaps, $brand_data) {
        $recommendations = [];

        // Category gaps
        if (!empty($gaps['category_gaps']['has_gaps'])) {
            $largest_gap_category = $gaps['category_gaps']['largest_gap'];
            if ($largest_gap_category) {
                $recommendations[] = [
                    'type' => 'category',
                    'priority' => 'high',
                    'message' => "Focus on creating more content for '{$largest_gap_category}' category",
                    'action' => 'generate_topics',
                    'params' => ['category' => $largest_gap_category]
                ];
            }
        }

        // Temporal gaps
        if (!empty($gaps['temporal_gaps']['has_gaps'])) {
            $gap_count = $gaps['temporal_gaps']['gap_count'];
            if ($gap_count > 0) {
                $recommendations[] = [
                    'type' => 'frequency',
                    'priority' => 'medium',
                    'message' => "Detected {$gap_count} publishing gaps. Consider increasing posting frequency",
                    'action' => 'increase_frequency'
                ];
            }
        }

        // Topic coverage gaps
        if (!empty($gaps['topic_gaps']['has_gaps'])) {
            $uncovered = $gaps['topic_gaps']['uncovered_topics'];
            if (!empty($uncovered)) {
                $recommendations[] = [
                    'type' => 'topic',
                    'priority' => 'high',
                    'message' => "Topics not covered: " . implode(', ', array_slice($uncovered, 0, 3)),
                    'action' => 'create_content',
                    'params' => ['topics' => $uncovered]
                ];
            }
        }

        // Content type gaps
        if (!empty($gaps['content_type_gaps']['has_gaps'])) {
            $unused = $gaps['content_type_gaps']['unused_types'];
            if (!empty($unused)) {
                $types = array_column($unused, 'type');
                $recommendations[] = [
                    'type' => 'content_type',
                    'priority' => 'medium',
                    'message' => "Unused content types: " . implode(', ', $types),
                    'action' => 'diversify_content'
                ];
            }
        }

        // Difficulty gaps
        if (!empty($gaps['difficulty_gaps']['has_gaps'])) {
            $largest_gap = $gaps['difficulty_gaps']['largest_gap_level'];
            if ($largest_gap) {
                $recommendations[] = [
                    'type' => 'difficulty',
                    'priority' => 'low',
                    'message' => "Need more '{$largest_gap}' level content",
                    'action' => 'balance_difficulty'
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Count total number of gaps
     *
     * @param array $gaps All gaps
     * @return int Gap count
     */
    private function count_gaps($gaps) {
        $count = 0;

        foreach ($gaps as $gap_type) {
            if (isset($gap_type['has_gaps']) && $gap_type['has_gaps']) {
                $count++;
            }
        }

        return $count;
    }
}
