<?php
/**
 * Smart Topic Selector
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * Smart Topic Selector Class
 *
 * Intelligently selects topics from brand topic pool based on various criteria
 */
class Smart_Topic_Selector {

    /**
     * Select topics from brand profile
     *
     * @param array $brand_data Complete brand data including topic_pool
     * @param array $options Selection options
     * @return array Selected topics
     */
    public function select_topics($brand_data, $options = []) {
        $defaults = [
            'count' => 1,
            'category' => null,  // null = any category, or specific category name
            'selection_method' => 'weighted_random',  // weighted_random|round_robin|performance_based|priority_based
            'content_type' => null,  // filter by content type
            'difficulty' => null,  // filter by difficulty
            'exclude_recent' => true  // exclude topics used recently
        ];

        $options = array_merge($defaults, $options);

        // Check if topic_pool exists and is enabled
        if (empty($brand_data['topic_pool']) ||
            !isset($brand_data['topic_pool']['enabled']) ||
            $brand_data['topic_pool']['enabled'] !== true) {
            return [];
        }

        $topic_pool = $brand_data['topic_pool'];
        $selection_rules = $topic_pool['topic_selection_rules'] ?? [];

        // Get available topics based on filters
        $available_topics = $this->get_available_topics(
            $topic_pool['categories'] ?? [],
            $options,
            $selection_rules
        );

        if (empty($available_topics)) {
            return [];
        }

        // Select topics based on method
        switch ($options['selection_method']) {
            case 'weighted_random':
                return $this->select_weighted_random($available_topics, $options['count'], $topic_pool['categories'] ?? []);

            case 'round_robin':
                return $this->select_round_robin($available_topics, $options['count']);

            case 'performance_based':
                return $this->select_performance_based($available_topics, $options['count']);

            case 'priority_based':
                return $this->select_priority_based($available_topics, $options['count']);

            default:
                return $this->select_random($available_topics, $options['count']);
        }
    }

    /**
     * Get available topics based on filters and rules
     *
     * @param array $categories Topic categories
     * @param array $options Filter options
     * @param array $selection_rules Selection rules
     * @return array Available topics with metadata
     */
    private function get_available_topics($categories, $options, $selection_rules) {
        $available = [];

        foreach ($categories as $category) {
            // Skip if specific category requested and this isn't it
            if ($options['category'] && $options['category'] !== '*' && $category['name'] !== $options['category']) {
                continue;
            }

            $topics = $category['topics'] ?? [];

            foreach ($topics as $topic_index => $topic) {
                // Apply filters
                if ($options['content_type'] && isset($topic['content_type']) && $topic['content_type'] !== $options['content_type']) {
                    continue;
                }

                if ($options['difficulty'] && isset($topic['difficulty']) && $topic['difficulty'] !== $options['difficulty']) {
                    continue;
                }

                // Check if topic was used recently
                if ($options['exclude_recent'] && isset($topic['last_used'])) {
                    $avoid_days = $selection_rules['avoid_repetition_days'] ?? 30;
                    $last_used_time = strtotime($topic['last_used']);
                    $days_since_used = (time() - $last_used_time) / (60 * 60 * 24);

                    if ($days_since_used < $avoid_days) {
                        continue;
                    }
                }

                // Add topic with metadata
                $available[] = [
                    'category' => $category['name'],
                    'category_weight' => $category['weight'] ?? 25,
                    'topic' => $topic,
                    'topic_index' => $topic_index
                ];
            }
        }

        return $available;
    }

    /**
     * Select topics using weighted random based on category weights
     *
     * @param array $available_topics Available topics
     * @param int $count Number of topics to select
     * @param array $categories Category definitions
     * @return array Selected topics
     */
    private function select_weighted_random($available_topics, $count, $categories) {
        if (empty($available_topics)) {
            return [];
        }

        $selected = [];

        for ($i = 0; $i < $count; $i++) {
            if (empty($available_topics)) {
                break;
            }

            // Calculate total weight
            $total_weight = 0;
            $weighted_topics = [];

            foreach ($available_topics as $index => $topic_data) {
                $weight = $topic_data['category_weight'];
                $total_weight += $weight;
                $weighted_topics[$index] = $weight;
            }

            // Random selection based on weight
            $random = mt_rand(1, $total_weight);
            $current_weight = 0;

            foreach ($weighted_topics as $index => $weight) {
                $current_weight += $weight;
                if ($random <= $current_weight) {
                    $selected[] = $available_topics[$index]['topic'];
                    unset($available_topics[$index]);
                    $available_topics = array_values($available_topics); // Re-index
                    break;
                }
            }
        }

        return $selected;
    }

    /**
     * Select topics using round-robin (evenly distributed)
     *
     * @param array $available_topics Available topics
     * @param int $count Number of topics to select
     * @return array Selected topics
     */
    private function select_round_robin($available_topics, $count) {
        if (empty($available_topics)) {
            return [];
        }

        // Group by category
        $by_category = [];
        foreach ($available_topics as $topic_data) {
            $category = $topic_data['category'];
            if (!isset($by_category[$category])) {
                $by_category[$category] = [];
            }
            $by_category[$category][] = $topic_data['topic'];
        }

        $selected = [];
        $categories = array_keys($by_category);
        $category_index = 0;

        for ($i = 0; $i < $count; $i++) {
            // Round-robin through categories
            if (empty($by_category)) {
                break;
            }

            $attempts = 0;
            while ($attempts < count($categories)) {
                $category = $categories[$category_index % count($categories)];

                if (!empty($by_category[$category])) {
                    $selected[] = array_shift($by_category[$category]);

                    if (empty($by_category[$category])) {
                        unset($by_category[$category]);
                        $categories = array_keys($by_category);
                    }
                    break;
                }

                $category_index++;
                $attempts++;
            }

            $category_index++;
        }

        return $selected;
    }

    /**
     * Select topics based on performance scores
     *
     * @param array $available_topics Available topics
     * @param int $count Number of topics to select
     * @return array Selected topics
     */
    private function select_performance_based($available_topics, $count) {
        if (empty($available_topics)) {
            return [];
        }

        // Sort by performance score (highest first)
        usort($available_topics, function($a, $b) {
            $score_a = $a['topic']['performance_score'] ?? 0;
            $score_b = $b['topic']['performance_score'] ?? 0;
            return $score_b <=> $score_a;
        });

        $selected = [];
        for ($i = 0; $i < min($count, count($available_topics)); $i++) {
            $selected[] = $available_topics[$i]['topic'];
        }

        return $selected;
    }

    /**
     * Select topics based on priority
     *
     * @param array $available_topics Available topics
     * @param int $count Number of topics to select
     * @return array Selected topics
     */
    private function select_priority_based($available_topics, $count) {
        if (empty($available_topics)) {
            return [];
        }

        // Priority weights
        $priority_weights = [
            'high' => 3,
            'medium' => 2,
            'low' => 1
        ];

        // Sort by priority
        usort($available_topics, function($a, $b) use ($priority_weights) {
            $priority_a = $a['topic']['priority'] ?? 'medium';
            $priority_b = $b['topic']['priority'] ?? 'medium';
            $weight_a = $priority_weights[$priority_a] ?? 2;
            $weight_b = $priority_weights[$priority_b] ?? 2;
            return $weight_b <=> $weight_a;
        });

        $selected = [];
        for ($i = 0; $i < min($count, count($available_topics)); $i++) {
            $selected[] = $available_topics[$i]['topic'];
        }

        return $selected;
    }

    /**
     * Simple random selection
     *
     * @param array $available_topics Available topics
     * @param int $count Number of topics to select
     * @return array Selected topics
     */
    private function select_random($available_topics, $count) {
        if (empty($available_topics)) {
            return [];
        }

        shuffle($available_topics);

        $selected = [];
        for ($i = 0; $i < min($count, count($available_topics)); $i++) {
            $selected[] = $available_topics[$i]['topic'];
        }

        return $selected;
    }

    /**
     * Mark topics as used (update last_used and usage_count)
     *
     * @param array $brand_data Brand data array (passed by reference)
     * @param array $used_topics Topics that were used
     * @return array Updated brand data
     */
    public function mark_topics_as_used(&$brand_data, $used_topics) {
        if (empty($brand_data['topic_pool']) || empty($brand_data['topic_pool']['categories'])) {
            return $brand_data;
        }

        $current_time = current_time('mysql');

        foreach ($brand_data['topic_pool']['categories'] as &$category) {
            foreach ($category['topics'] as &$topic) {
                foreach ($used_topics as $used_topic) {
                    // Match by title
                    if ($topic['title'] === $used_topic['title']) {
                        $topic['last_used'] = $current_time;
                        $topic['usage_count'] = ($topic['usage_count'] ?? 0) + 1;
                        break;
                    }
                }
            }
        }

        return $brand_data;
    }

    /**
     * Get topic statistics
     *
     * @param array $brand_data Brand data
     * @return array Statistics
     */
    public function get_topic_statistics($brand_data) {
        if (empty($brand_data['topic_pool']) || empty($brand_data['topic_pool']['categories'])) {
            return [
                'total_topics' => 0,
                'total_categories' => 0,
                'unused_topics' => 0,
                'most_used_topic' => null,
                'average_usage' => 0
            ];
        }

        $total_topics = 0;
        $unused_topics = 0;
        $usage_counts = [];
        $most_used = ['title' => '', 'count' => 0];

        foreach ($brand_data['topic_pool']['categories'] as $category) {
            foreach ($category['topics'] as $topic) {
                $total_topics++;
                $usage_count = $topic['usage_count'] ?? 0;

                if ($usage_count === 0) {
                    $unused_topics++;
                }

                $usage_counts[] = $usage_count;

                if ($usage_count > $most_used['count']) {
                    $most_used = [
                        'title' => $topic['title'],
                        'count' => $usage_count
                    ];
                }
            }
        }

        return [
            'total_topics' => $total_topics,
            'total_categories' => count($brand_data['topic_pool']['categories']),
            'unused_topics' => $unused_topics,
            'most_used_topic' => $most_used['count'] > 0 ? $most_used : null,
            'average_usage' => $total_topics > 0 ? array_sum($usage_counts) / $total_topics : 0
        ];
    }
}
