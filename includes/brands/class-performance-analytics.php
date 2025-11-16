<?php
/**
 * Performance Analytics
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * Performance Analytics Class
 *
 * Tracks and analyzes content performance for topic optimization
 */
class Performance_Analytics {

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
     * Calculate performance score for a post
     *
     * @param int $post_id Post ID
     * @return float Performance score (0-10)
     */
    public function calculate_post_performance($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return 0;
        }

        $metrics = [
            'views' => $this->get_post_views($post_id),
            'comments' => get_comments_number($post_id),
            'shares' => $this->get_post_shares($post_id),
            'time_on_page' => $this->get_avg_time_on_page($post_id),
            'bounce_rate' => $this->get_bounce_rate($post_id)
        ];

        // Weighted scoring
        $score = 0;

        // Views (30% weight) - normalized to 0-3
        $score += min(($metrics['views'] / 1000) * 3, 3);

        // Comments (20% weight) - normalized to 0-2
        $score += min(($metrics['comments'] / 20) * 2, 2);

        // Shares (20% weight) - normalized to 0-2
        $score += min(($metrics['shares'] / 50) * 2, 2);

        // Time on page (15% weight) - normalized to 0-1.5
        // Good engagement = 2+ minutes
        $score += min(($metrics['time_on_page'] / 120) * 1.5, 1.5);

        // Bounce rate (15% weight) - inverted, normalized to 0-1.5
        // Lower bounce rate = better score
        $bounce_penalty = ($metrics['bounce_rate'] / 100) * 1.5;
        $score += (1.5 - $bounce_penalty);

        return round($score, 2);
    }

    /**
     * Get post views count
     *
     * @param int $post_id Post ID
     * @return int Views count
     */
    private function get_post_views($post_id) {
        // Check for popular plugins
        // 1. WP Statistics
        if (function_exists('wp_statistics_pages')) {
            $stats = wp_statistics_pages('total', null, $post_id);
            if ($stats) {
                return (int) $stats;
            }
        }

        // 2. Post Views Counter
        if (function_exists('pvc_get_post_views')) {
            return (int) pvc_get_post_views($post_id);
        }

        // 3. Simple custom meta
        $views = get_post_meta($post_id, '_ai_manager_views', true);
        if ($views) {
            return (int) $views;
        }

        // 4. Google Analytics (if integrated)
        $ga_views = $this->get_ga_views($post_id);
        if ($ga_views !== false) {
            return $ga_views;
        }

        // Fallback: estimate based on age and comments
        $post = get_post($post_id);
        $days_old = (time() - strtotime($post->post_date)) / 86400;
        $comments = get_comments_number($post_id);

        // Rough estimation
        return max((int) ($days_old * 10 + $comments * 5), 0);
    }

    /**
     * Get post social shares count
     *
     * @param int $post_id Post ID
     * @return int Shares count
     */
    private function get_post_shares($post_id) {
        // Check for sharing plugins
        // 1. Social Warfare
        if (function_exists('get_social_warfare_shares')) {
            return (int) get_social_warfare_shares($post_id);
        }

        // 2. Shared Counts
        if (class_exists('Shared_Counts')) {
            $shares = get_post_meta($post_id, 'shared_counts_total', true);
            if ($shares) {
                return (int) $shares;
            }
        }

        // 3. Custom meta
        $shares = get_post_meta($post_id, '_ai_manager_shares', true);
        if ($shares) {
            return (int) $shares;
        }

        // Fallback
        return 0;
    }

    /**
     * Get average time on page
     *
     * @param int $post_id Post ID
     * @return int Seconds
     */
    private function get_avg_time_on_page($post_id) {
        // Check for analytics plugins
        $time = get_post_meta($post_id, '_ai_manager_avg_time', true);
        if ($time) {
            return (int) $time;
        }

        // Fallback: estimate based on content length
        $post = get_post($post_id);
        $word_count = str_word_count(strip_tags($post->post_content));

        // Average reading speed: 200-250 words/minute
        $estimated_seconds = ($word_count / 225) * 60;

        return (int) $estimated_seconds;
    }

    /**
     * Get bounce rate
     *
     * @param int $post_id Post ID
     * @return float Bounce rate percentage
     */
    private function get_bounce_rate($post_id) {
        // Check for analytics
        $bounce_rate = get_post_meta($post_id, '_ai_manager_bounce_rate', true);
        if ($bounce_rate) {
            return (float) $bounce_rate;
        }

        // Fallback: estimate based on engagement
        $comments = get_comments_number($post_id);

        // More comments = lower bounce rate (rough estimation)
        if ($comments > 20) {
            return 30.0;
        } elseif ($comments > 10) {
            return 45.0;
        } elseif ($comments > 5) {
            return 60.0;
        } else {
            return 70.0;
        }
    }

    /**
     * Get Google Analytics views (placeholder)
     *
     * @param int $post_id Post ID
     * @return int|false Views or false if not available
     */
    private function get_ga_views($post_id) {
        // This would require Google Analytics API integration
        // Placeholder for future implementation
        return false;
    }

    /**
     * Update topic performance scores in brand data
     *
     * @param int $brand_id Brand ID
     * @param array $options Options
     * @return array Result
     */
    public function update_topic_performance_scores($brand_id, $options = []) {
        global $wpdb;

        $defaults = [
            'days_back' => 90,  // Analyze posts from last 90 days
            'min_posts_per_topic' => 1  // Minimum posts to calculate score
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

        if (empty($brand_data['topic_pool']) || empty($brand_data['topic_pool']['categories'])) {
            return [
                'success' => false,
                'error' => 'No topic pool found'
            ];
        }

        // Get posts from last X days
        $cutoff_date = date('Y-m-d', strtotime("-{$options['days_back']} days"));
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 500,
            'date_query' => [
                [
                    'after' => $cutoff_date
                ]
            ]
        ]);

        // Map topics to post performance
        $topic_performance = [];

        foreach ($brand_data['topic_pool']['categories'] as &$category) {
            foreach ($category['topics'] as &$topic) {
                $topic_title = $topic['title'];

                // Find posts matching this topic
                $matching_posts = $this->find_posts_by_topic($posts, $topic_title, $topic['keywords'] ?? []);

                if (count($matching_posts) >= $options['min_posts_per_topic']) {
                    // Calculate average performance
                    $scores = [];
                    foreach ($matching_posts as $post_id) {
                        $scores[] = $this->calculate_post_performance($post_id);
                    }

                    $avg_score = array_sum($scores) / count($scores);
                    $topic['performance_score'] = round($avg_score, 2);

                    $topic_performance[$topic_title] = [
                        'score' => $avg_score,
                        'post_count' => count($matching_posts)
                    ];
                }
            }
        }

        // Update brand data
        $wpdb->update(
            $table_name,
            ['brand_data' => json_encode($brand_data, JSON_UNESCAPED_UNICODE)],
            ['id' => $brand_id]
        );

        return [
            'success' => true,
            'topics_updated' => count($topic_performance),
            'topic_performance' => $topic_performance
        ];
    }

    /**
     * Find posts matching a topic
     *
     * @param array $posts Posts to search
     * @param string $topic_title Topic title
     * @param array $keywords Topic keywords
     * @return array Matching post IDs
     */
    private function find_posts_by_topic($posts, $topic_title, $keywords) {
        $matching = [];

        foreach ($posts as $post) {
            $post_title = strtolower($post->post_title);
            $post_content = strtolower($post->post_content);
            $topic_lower = strtolower($topic_title);

            // Check if topic title appears in post title/content
            if (strpos($post_title, $topic_lower) !== false ||
                strpos($post_content, $topic_lower) !== false) {
                $matching[] = $post->ID;
                continue;
            }

            // Check if any keywords match
            foreach ($keywords as $keyword) {
                $keyword_lower = strtolower($keyword);
                if (strpos($post_title, $keyword_lower) !== false ||
                    strpos($post_content, $keyword_lower) !== false) {
                    $matching[] = $post->ID;
                    break;
                }
            }
        }

        return $matching;
    }

    /**
     * Get performance report for brand topics
     *
     * @param int $brand_id Brand ID
     * @return array Performance report
     */
    public function get_performance_report($brand_id) {
        global $wpdb;

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

        if (empty($brand_data['topic_pool'])) {
            return [
                'success' => false,
                'error' => 'No topic pool found'
            ];
        }

        $report = [
            'top_performers' => [],
            'low_performers' => [],
            'unused_topics' => [],
            'average_score' => 0
        ];

        $all_topics = [];

        foreach ($brand_data['topic_pool']['categories'] as $category) {
            foreach ($category['topics'] as $topic) {
                $topic['category'] = $category['name'];
                $all_topics[] = $topic;
            }
        }

        // Sort by performance score
        usort($all_topics, function($a, $b) {
            $score_a = $a['performance_score'] ?? 0;
            $score_b = $b['performance_score'] ?? 0;
            return $score_b <=> $score_a;
        });

        // Top 5 performers
        $report['top_performers'] = array_slice($all_topics, 0, 5);

        // Bottom 5 performers (with scores)
        $with_scores = array_filter($all_topics, function($topic) {
            return isset($topic['performance_score']) && $topic['performance_score'] > 0;
        });
        $report['low_performers'] = array_slice(array_reverse($with_scores), 0, 5);

        // Unused topics
        $report['unused_topics'] = array_filter($all_topics, function($topic) {
            return ($topic['usage_count'] ?? 0) === 0;
        });

        // Average score
        $scores = array_map(function($topic) {
            return $topic['performance_score'] ?? 0;
        }, $all_topics);
        $scores = array_filter($scores);

        $report['average_score'] = !empty($scores)
            ? round(array_sum($scores) / count($scores), 2)
            : 0;

        $report['total_topics'] = count($all_topics);
        $report['topics_with_scores'] = count($with_scores);

        return [
            'success' => true,
            'report' => $report
        ];
    }

    /**
     * Track post view (for custom tracking)
     *
     * @param int $post_id Post ID
     */
    public function track_view($post_id) {
        $views = (int) get_post_meta($post_id, '_ai_manager_views', true);
        update_post_meta($post_id, '_ai_manager_views', $views + 1);
    }

    /**
     * Track post share (for custom tracking)
     *
     * @param int $post_id Post ID
     */
    public function track_share($post_id) {
        $shares = (int) get_post_meta($post_id, '_ai_manager_shares', true);
        update_post_meta($post_id, '_ai_manager_shares', $shares + 1);
    }
}
