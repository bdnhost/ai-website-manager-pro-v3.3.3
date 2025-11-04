<?php
/**
 * Knowledge Base Manager - מנהל מאגר הידע
 *
 * מנהל מאגר ידע מקיף לשימוש במערך האוטומציה
 * כולל כותרות, נושאים, מילות מפתח ורעיונות תוכן
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Automation
 */

namespace AI_Manager_Pro\Modules\Automation;

use Monolog\Logger;

/**
 * Knowledge Base Manager Class
 */
class Knowledge_Base_Manager {

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Knowledge base path
     *
     * @var string
     */
    private $kb_path;

    /**
     * Cached data
     *
     * @var array
     */
    private $cache = [];

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->kb_path = AI_MANAGER_PRO_PLUGIN_DIR . 'includes/knowledge-base/';
    }

    /**
     * Get headline template
     *
     * @param string $content_type Content type (article, guide, review, etc.)
     * @param string|null $category Optional category filter
     * @return array|false Headline template or false
     */
    public function get_headline_template($content_type = 'article', $category = null) {
        $headlines = $this->load_knowledge_file('headlines-templates.json');

        if (!$headlines || !isset($headlines[$content_type])) {
            $this->logger->warning('Headline templates not found for type', [
                'content_type' => $content_type
            ]);
            return false;
        }

        $templates = $headlines[$content_type]['templates'];

        // Filter by category if provided
        if ($category) {
            $templates = array_filter($templates, function($template) use ($category) {
                return isset($template['category']) && $template['category'] === $category;
            });
        }

        if (empty($templates)) {
            return false;
        }

        // Return random template
        $random_key = array_rand($templates);
        return $templates[$random_key];
    }

    /**
     * Get topics by industry
     *
     * @param string $industry Industry name
     * @return array|false Topics or false
     */
    public function get_topics_by_industry($industry) {
        $topics = $this->load_knowledge_file('topics-by-industry.json');

        if (!$topics || !isset($topics['industries'][$industry])) {
            $this->logger->warning('Topics not found for industry', [
                'industry' => $industry
            ]);
            return false;
        }

        return $topics['industries'][$industry];
    }

    /**
     * Get content ideas
     *
     * @param string $industry Industry name
     * @param string|null $category Optional category filter
     * @return array Content ideas
     */
    public function get_content_ideas($industry, $category = null) {
        $industry_data = $this->get_topics_by_industry($industry);

        if (!$industry_data) {
            return [];
        }

        $all_ideas = [];

        foreach ($industry_data['topics'] as $topic) {
            // Filter by category if provided
            if ($category && $topic['category'] !== $category) {
                continue;
            }

            $all_ideas = array_merge($all_ideas, $topic['content_ideas']);
        }

        return $all_ideas;
    }

    /**
     * Get SEO keywords
     *
     * @param string $type Type of keywords (power_words, long_tail_patterns, etc.)
     * @param string|null $subtype Optional subtype
     * @return array|false Keywords or false
     */
    public function get_seo_keywords($type, $subtype = null) {
        $keywords = $this->load_knowledge_file('seo-keywords-bank.json');

        if (!$keywords || !isset($keywords[$type])) {
            return false;
        }

        if ($subtype) {
            return $keywords[$type][$subtype] ?? false;
        }

        return $keywords[$type];
    }

    /**
     * Generate smart headline
     *
     * @param array $params Parameters for headline generation
     * @return string Generated headline
     */
    public function generate_smart_headline($params) {
        $content_type = $params['content_type'] ?? 'article';
        $topic = $params['topic'] ?? '';
        $industry = $params['industry'] ?? '';
        $year = date('Y');

        // Get template
        $template = $this->get_headline_template($content_type);

        if (!$template) {
            // Fallback to simple headline
            return $this->generate_fallback_headline($topic);
        }

        $pattern = $template['pattern'];

        // Replace placeholders
        $headline = str_replace('{topic}', $topic, $pattern);
        $headline = str_replace('{year}', $year, $headline);
        $headline = str_replace('{number}', rand(5, 15), $headline);

        // Add industry-specific keywords if available
        if ($industry) {
            $industry_data = $this->get_topics_by_industry($industry);
            if ($industry_data && !empty($industry_data['topics'])) {
                $random_topic = $industry_data['topics'][array_rand($industry_data['topics'])];
                if (!empty($random_topic['keywords'])) {
                    $keyword = $random_topic['keywords'][array_rand($random_topic['keywords'])];
                    $headline = str_replace('{keyword}', $keyword, $headline);
                }
            }
        }

        $this->logger->info('Generated smart headline', [
            'headline' => $headline,
            'content_type' => $content_type,
            'template' => $template['pattern']
        ]);

        return $headline;
    }

    /**
     * Generate fallback headline
     *
     * @param string $topic Topic
     * @return string Headline
     */
    private function generate_fallback_headline($topic) {
        $templates = [
            "המדריך המלא ל{topic}",
            "כל מה שצריך לדעת על {topic}",
            "{topic}: מדריך מקיף",
            "איך להצליח ב{topic}",
            "{topic} ב-%d - המדריך"
        ];

        $template = $templates[array_rand($templates)];
        $headline = str_replace('{topic}', $topic, $template);
        $headline = sprintf($headline, date('Y'));

        return $headline;
    }

    /**
     * Get smart keywords for content
     *
     * @param array $params Parameters
     * @return array Keywords array
     */
    public function get_smart_keywords($params) {
        $topic = $params['topic'] ?? '';
        $industry = $params['industry'] ?? '';
        $intent = $params['intent'] ?? 'informational';

        $keywords = [$topic];

        // Add power words
        $power_words = $this->get_seo_keywords('power_words', 'value');
        if ($power_words) {
            $keywords[] = $power_words[array_rand($power_words)];
        }

        // Add intent modifiers
        $intent_modifiers = $this->get_seo_keywords('intent_modifiers', $intent);
        if ($intent_modifiers) {
            $modifier = $intent_modifiers[array_rand($intent_modifiers)];
            $keywords[] = $modifier . ' ' . $topic;
        }

        // Add industry-specific keywords
        if ($industry) {
            $industry_data = $this->get_topics_by_industry($industry);
            if ($industry_data && !empty($industry_data['topics'])) {
                $random_topic = $industry_data['topics'][array_rand($industry_data['topics'])];
                if (!empty($random_topic['keywords'])) {
                    $keyword = $random_topic['keywords'][array_rand($random_topic['keywords'])];
                    $keywords[] = $keyword;
                }
            }
        }

        // Add semantic cluster keywords
        $semantic = $this->get_semantic_keywords($industry);
        if (!empty($semantic)) {
            $keywords = array_merge($keywords, array_slice($semantic, 0, 3));
        }

        // Remove duplicates and return
        $keywords = array_unique($keywords);

        $this->logger->info('Generated smart keywords', [
            'keywords' => $keywords,
            'topic' => $topic,
            'industry' => $industry
        ]);

        return $keywords;
    }

    /**
     * Get semantic keywords for industry
     *
     * @param string $industry Industry name
     * @return array Semantic keywords
     */
    private function get_semantic_keywords($industry) {
        $keywords_bank = $this->load_knowledge_file('seo-keywords-bank.json');

        if (!$keywords_bank || !isset($keywords_bank['semantic_clusters'])) {
            return [];
        }

        // Map industry to semantic cluster
        $cluster_map = [
            'marketing' => 'marketing',
            'technology' => 'web_development',
            'ecommerce' => 'ecommerce'
        ];

        $cluster_key = $cluster_map[$industry] ?? null;

        if (!$cluster_key || !isset($keywords_bank['semantic_clusters'][$cluster_key])) {
            return [];
        }

        $cluster = $keywords_bank['semantic_clusters'][$cluster_key];
        $keywords = [];

        // Get core keywords
        if (isset($cluster['core'])) {
            $keywords = array_merge($keywords, $cluster['core']);
        }

        // Get some related keywords
        if (isset($cluster['related'])) {
            $related = $cluster['related'];
            shuffle($related);
            $keywords = array_merge($keywords, array_slice($related, 0, 3));
        }

        return $keywords;
    }

    /**
     * Get all available industries
     *
     * @return array Industries list
     */
    public function get_all_industries() {
        $topics = $this->load_knowledge_file('topics-by-industry.json');

        if (!$topics || !isset($topics['industries'])) {
            return [];
        }

        $industries = [];

        foreach ($topics['industries'] as $key => $industry) {
            $industries[$key] = [
                'name' => $industry['name'],
                'icon' => $industry['icon'],
                'topics_count' => count($industry['topics'])
            ];
        }

        return $industries;
    }

    /**
     * Get trending topics
     *
     * @param int $limit Number of topics to return
     * @return array Trending topics
     */
    public function get_trending_topics($limit = 10) {
        $keywords_bank = $this->load_knowledge_file('seo-keywords-bank.json');

        if (!$keywords_bank || !isset($keywords_bank['trending_topics_2025'])) {
            return [];
        }

        $trending = $keywords_bank['trending_topics_2025'];
        shuffle($trending);

        return array_slice($trending, 0, $limit);
    }

    /**
     * Load knowledge file
     *
     * @param string $filename Filename
     * @return array|false File data or false
     */
    private function load_knowledge_file($filename) {
        // Check cache first
        if (isset($this->cache[$filename])) {
            return $this->cache[$filename];
        }

        $file_path = $this->kb_path . $filename;

        if (!file_exists($file_path)) {
            $this->logger->error('Knowledge file not found', [
                'file' => $filename,
                'path' => $file_path
            ]);
            return false;
        }

        $json_content = file_get_contents($file_path);
        $data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Failed to parse knowledge file', [
                'file' => $filename,
                'error' => json_last_error_msg()
            ]);
            return false;
        }

        // Cache the data
        $this->cache[$filename] = $data;

        return $data;
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        $this->cache = [];
        $this->logger->info('Knowledge base cache cleared');
    }

    /**
     * Get knowledge base stats
     *
     * @return array Statistics
     */
    public function get_stats() {
        $headlines = $this->load_knowledge_file('headlines-templates.json');
        $topics = $this->load_knowledge_file('topics-by-industry.json');
        $keywords = $this->load_knowledge_file('seo-keywords-bank.json');

        $stats = [
            'headlines_types' => $headlines ? count($headlines) : 0,
            'industries' => $topics && isset($topics['industries']) ? count($topics['industries']) : 0,
            'keyword_categories' => $keywords ? count($keywords) : 0,
            'cache_size' => count($this->cache),
            'files_loaded' => array_keys($this->cache)
        ];

        return $stats;
    }
}
