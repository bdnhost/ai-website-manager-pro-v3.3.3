<?php
/**
 * RSS Trending Detector
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * RSS Trending Detector Class
 *
 * Detects trending topics from RSS feeds
 */
class RSS_Trending_Detector {

    /**
     * Logger instance
     *
     * @var mixed
     */
    private $logger;

    /**
     * Cache duration in seconds (1 hour)
     *
     * @var int
     */
    private $cache_duration = 3600;

    /**
     * Constructor
     *
     * @param mixed $logger Logger instance
     */
    public function __construct($logger = null) {
        $this->logger = $logger;
    }

    /**
     * Detect trending topics from brand's RSS feeds
     *
     * @param array $brand_data Brand data
     * @param array $options Detection options
     * @return array Trending topics
     */
    public function detect_trends($brand_data, $options = []) {
        $defaults = [
            'time_window' => '24h',  // 24h|7d|30d
            'minimum_mentions' => 3,  // Minimum times keyword must appear
            'max_results' => 10,  // Maximum trending topics to return
            'use_cache' => true  // Use cached RSS data
        ];

        $options = array_merge($defaults, $options);

        // Check if trending sources are enabled
        if (empty($brand_data['content_calendar']['trending_sources']['enabled'])) {
            return [
                'success' => false,
                'error' => 'Trending sources not enabled for this brand'
            ];
        }

        $trending_sources = $brand_data['content_calendar']['trending_sources'];
        $rss_feeds = $trending_sources['rss_feeds'] ?? [];
        $keywords_to_track = $trending_sources['keywords_to_track'] ?? [];

        if (empty($rss_feeds)) {
            return [
                'success' => false,
                'error' => 'No RSS feeds configured'
            ];
        }

        // Fetch RSS feeds
        $feed_items = $this->fetch_rss_feeds($rss_feeds, $options);

        if (empty($feed_items)) {
            return [
                'success' => false,
                'error' => 'No feed items retrieved'
            ];
        }

        // Analyze trends
        $trending_topics = $this->analyze_trends($feed_items, $keywords_to_track, $options);

        return [
            'success' => true,
            'trending_topics' => $trending_topics,
            'count' => count($trending_topics),
            'feed_items_analyzed' => count($feed_items)
        ];
    }

    /**
     * Fetch RSS feeds
     *
     * @param array $feed_urls RSS feed URLs
     * @param array $options Options
     * @return array Feed items
     */
    private function fetch_rss_feeds($feed_urls, $options) {
        $all_items = [];

        foreach ($feed_urls as $feed_url) {
            $items = $this->fetch_single_feed($feed_url, $options);
            $all_items = array_merge($all_items, $items);
        }

        // Sort by date (newest first)
        usort($all_items, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Filter by time window
        $time_window_seconds = $this->parse_time_window($options['time_window']);
        $cutoff_time = time() - $time_window_seconds;

        $filtered_items = array_filter($all_items, function($item) use ($cutoff_time) {
            return $item['timestamp'] >= $cutoff_time;
        });

        return array_values($filtered_items);
    }

    /**
     * Fetch single RSS feed
     *
     * @param string $feed_url Feed URL
     * @param array $options Options
     * @return array Feed items
     */
    private function fetch_single_feed($feed_url, $options) {
        // Check cache first
        if ($options['use_cache']) {
            $cache_key = 'ai_manager_rss_' . md5($feed_url);
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Fetch feed using WordPress SimplePie
        $rss = fetch_feed($feed_url);

        if (is_wp_error($rss)) {
            if ($this->logger) {
                $this->logger->warning('Failed to fetch RSS feed: ' . $feed_url . ' - ' . $rss->get_error_message());
            }
            return [];
        }

        $max_items = $rss->get_item_quantity(50);
        $rss_items = $rss->get_items(0, $max_items);

        $items = [];
        foreach ($rss_items as $item) {
            $items[] = [
                'title' => $item->get_title(),
                'content' => $item->get_content() ?? $item->get_description(),
                'link' => $item->get_permalink(),
                'timestamp' => $item->get_date('U'),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'source' => $feed_url
            ];
        }

        // Cache the results
        if ($options['use_cache']) {
            set_transient($cache_key, $items, $this->cache_duration);
        }

        return $items;
    }

    /**
     * Analyze trends from feed items
     *
     * @param array $feed_items Feed items
     * @param array $keywords_to_track Keywords to track
     * @param array $options Options
     * @return array Trending topics
     */
    private function analyze_trends($feed_items, $keywords_to_track, $options) {
        // Count keyword occurrences
        $keyword_counts = [];
        $keyword_articles = [];

        foreach ($feed_items as $item) {
            $text = strtolower($item['title'] . ' ' . $item['content']);

            foreach ($keywords_to_track as $keyword) {
                $keyword_lower = strtolower($keyword);

                // Count occurrences (whole word match)
                $pattern = '/\b' . preg_quote($keyword_lower, '/') . '\b/';
                $matches = preg_match_all($pattern, $text);

                if ($matches > 0) {
                    if (!isset($keyword_counts[$keyword])) {
                        $keyword_counts[$keyword] = 0;
                        $keyword_articles[$keyword] = [];
                    }

                    $keyword_counts[$keyword] += $matches;

                    // Store article reference
                    $keyword_articles[$keyword][] = [
                        'title' => $item['title'],
                        'link' => $item['link'],
                        'date' => $item['date']
                    ];
                }
            }
        }

        // Filter by minimum mentions
        $trending = [];
        foreach ($keyword_counts as $keyword => $count) {
            if ($count >= $options['minimum_mentions']) {
                $trending[] = [
                    'keyword' => $keyword,
                    'mention_count' => $count,
                    'articles' => array_slice($keyword_articles[$keyword], 0, 5),  // Top 5 articles
                    'trending_score' => $this->calculate_trending_score($count, $keyword_articles[$keyword])
                ];
            }
        }

        // Sort by trending score
        usort($trending, function($a, $b) {
            return $b['trending_score'] <=> $a['trending_score'];
        });

        // Limit results
        return array_slice($trending, 0, $options['max_results']);
    }

    /**
     * Calculate trending score
     *
     * @param int $mention_count Mention count
     * @param array $articles Articles mentioning the keyword
     * @return float Trending score
     */
    private function calculate_trending_score($mention_count, $articles) {
        // Score based on:
        // 1. Total mentions
        // 2. Recency (recent articles score higher)
        // 3. Number of unique articles

        $base_score = $mention_count * 10;
        $unique_articles = count($articles);
        $recency_bonus = 0;

        // Calculate recency bonus
        $now = time();
        foreach ($articles as $article) {
            $article_time = strtotime($article['date']);
            $hours_ago = ($now - $article_time) / 3600;

            // More recent = higher bonus
            if ($hours_ago < 6) {
                $recency_bonus += 20;
            } elseif ($hours_ago < 24) {
                $recency_bonus += 10;
            } elseif ($hours_ago < 72) {
                $recency_bonus += 5;
            }
        }

        return $base_score + ($unique_articles * 5) + $recency_bonus;
    }

    /**
     * Parse time window string to seconds
     *
     * @param string $time_window Time window (24h, 7d, 30d)
     * @return int Seconds
     */
    private function parse_time_window($time_window) {
        $unit = substr($time_window, -1);
        $value = (int) substr($time_window, 0, -1);

        switch ($unit) {
            case 'h':
                return $value * 3600;
            case 'd':
                return $value * 86400;
            case 'w':
                return $value * 604800;
            default:
                return 86400;  // Default 1 day
        }
    }

    /**
     * Generate topic suggestions from trending keywords
     *
     * @param array $trending_topics Trending topics from detect_trends()
     * @param array $brand_data Brand data for context
     * @return array Topic suggestions
     */
    public function generate_topic_suggestions($trending_topics, $brand_data) {
        $suggestions = [];

        foreach ($trending_topics as $trend) {
            $keyword = $trend['keyword'];
            $articles = $trend['articles'];

            // Create topic suggestion
            $suggestions[] = [
                'title' => $this->create_topic_title($keyword, $brand_data),
                'keywords' => [$keyword],
                'target_length' => 'medium',
                'content_type' => 'blog_post',
                'difficulty' => 'intermediate',
                'priority' => 'high',
                'frequency' => 'once',
                'trending' => true,
                'trending_score' => $trend['trending_score'],
                'mention_count' => $trend['mention_count'],
                'reference_articles' => array_slice($articles, 0, 3),
                'last_used' => null,
                'usage_count' => 0,
                'performance_score' => null
            ];
        }

        return $suggestions;
    }

    /**
     * Create topic title from keyword
     *
     * @param string $keyword Trending keyword
     * @param array $brand_data Brand data
     * @return string Topic title
     */
    private function create_topic_title($keyword, $brand_data) {
        $industry = $brand_data['industry'] ?? 'industry';

        // Template variations
        $templates = [
            "Breaking: {keyword} Trends in {industry}",
            "What {keyword} Means for {industry}",
            "The Rise of {keyword}: What You Need to Know",
            "{keyword}: A Comprehensive Analysis",
            "How {keyword} is Transforming {industry}",
            "Understanding {keyword} in Today's {industry}"
        ];

        $template = $templates[array_rand($templates)];

        return str_replace(
            ['{keyword}', '{industry}'],
            [ucfirst($keyword), ucfirst($industry)],
            $template
        );
    }

    /**
     * Clear RSS cache
     *
     * @param array $feed_urls Feed URLs to clear (empty = clear all)
     */
    public function clear_cache($feed_urls = []) {
        if (empty($feed_urls)) {
            // Clear all RSS caches
            global $wpdb;
            $wpdb->query(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE '_transient_ai_manager_rss_%'
                 OR option_name LIKE '_transient_timeout_ai_manager_rss_%'"
            );
        } else {
            // Clear specific feeds
            foreach ($feed_urls as $feed_url) {
                $cache_key = 'ai_manager_rss_' . md5($feed_url);
                delete_transient($cache_key);
            }
        }
    }

    /**
     * Get trending topics and auto-create content topics
     *
     * @param int $brand_id Brand ID
     * @param array $options Options
     * @return array Result
     */
    public function process_trending_for_brand($brand_id, $options = []) {
        global $wpdb;

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

        // Detect trends
        $result = $this->detect_trends($brand_data, $options);

        if (!$result['success']) {
            return $result;
        }

        // Generate topic suggestions
        $topic_suggestions = $this->generate_topic_suggestions(
            $result['trending_topics'],
            $brand_data
        );

        // Auto-add to topic pool if requested
        if (!empty($options['auto_add_to_pool'])) {
            if (!isset($brand_data['topic_pool'])) {
                $brand_data['topic_pool'] = [
                    'enabled' => true,
                    'categories' => []
                ];
            }

            // Add to "Trending Topics" category
            $category_index = null;
            foreach ($brand_data['topic_pool']['categories'] as $index => $category) {
                if ($category['name'] === 'Trending Topics') {
                    $category_index = $index;
                    break;
                }
            }

            if ($category_index === null) {
                $brand_data['topic_pool']['categories'][] = [
                    'name' => 'Trending Topics',
                    'weight' => 15,
                    'topics' => []
                ];
                $category_index = count($brand_data['topic_pool']['categories']) - 1;
            }

            // Add topics
            foreach ($topic_suggestions as $topic) {
                $brand_data['topic_pool']['categories'][$category_index]['topics'][] = $topic;
            }

            // Update brand
            $wpdb->update(
                $table_name,
                ['brand_data' => json_encode($brand_data, JSON_UNESCAPED_UNICODE)],
                ['id' => $brand_id]
            );
        }

        return [
            'success' => true,
            'trending_topics' => $result['trending_topics'],
            'topic_suggestions' => $topic_suggestions,
            'count' => count($topic_suggestions)
        ];
    }
}
