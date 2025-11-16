<?php
/**
 * AI Topic Generator
 *
 * @package AI_Manager_Pro
 * @subpackage Brands
 */

namespace AI_Manager_Pro\Brands;

/**
 * AI Topic Generator Class
 *
 * Uses AI to generate new topics for brand topic pool
 */
class AI_Topic_Generator {

    /**
     * AI service instance
     *
     * @var mixed
     */
    private $ai_service;

    /**
     * Logger instance
     *
     * @var mixed
     */
    private $logger;

    /**
     * Constructor
     *
     * @param mixed $ai_service AI service instance
     * @param mixed $logger Logger instance
     */
    public function __construct($ai_service = null, $logger = null) {
        $this->ai_service = $ai_service;
        $this->logger = $logger;
    }

    /**
     * Generate topics for a brand using AI
     *
     * @param array $brand_data Brand data
     * @param array $options Generation options
     * @return array Generated topics or error
     */
    public function generate_topics($brand_data, $options = []) {
        $defaults = [
            'count' => 20,  // Number of topics to generate
            'category' => null,  // Generate for specific category or all
            'ai_provider' => null,  // openrouter|deepseek|openai
            'ai_model' => null,  // Specific model to use
            'content_types' => null,  // Array of preferred content types
            'difficulty_levels' => null,  // Array of difficulty levels
            'auto_add_to_pool' => false  // Automatically add to topic pool
        ];

        $options = array_merge($defaults, $options);

        // Build the AI prompt
        $prompt = $this->build_topic_generation_prompt($brand_data, $options);

        // Call AI service
        $ai_response = $this->call_ai_service($prompt, $options);

        if (!$ai_response || isset($ai_response['error'])) {
            return [
                'success' => false,
                'error' => $ai_response['error'] ?? 'AI service failed to generate topics'
            ];
        }

        // Parse AI response into structured topics
        $topics = $this->parse_ai_response($ai_response['content'], $options);

        if (empty($topics)) {
            return [
                'success' => false,
                'error' => 'Failed to parse topics from AI response'
            ];
        }

        // Auto-add to pool if requested
        if ($options['auto_add_to_pool']) {
            $brand_data = $this->add_topics_to_pool($brand_data, $topics, $options['category']);
        }

        return [
            'success' => true,
            'topics' => $topics,
            'count' => count($topics),
            'brand_data' => $brand_data  // Updated brand data if auto_add_to_pool
        ];
    }

    /**
     * Build AI prompt for topic generation
     *
     * @param array $brand_data Brand data
     * @param array $options Generation options
     * @return string AI prompt
     */
    private function build_topic_generation_prompt($brand_data, $options) {
        $brand_name = $brand_data['name'] ?? 'Brand';
        $industry = $brand_data['industry'] ?? 'general';
        $target_audience = $this->format_target_audience($brand_data['target_audience'] ?? []);
        $key_messages = implode(', ', $brand_data['key_messages'] ?? []);
        $seo_keywords = implode(', ', $brand_data['seo_keywords'] ?? []);
        $topics_to_cover = implode(', ', $brand_data['content_guidelines']['topics_to_cover'] ?? []);
        $topics_to_avoid = implode(', ', $brand_data['content_guidelines']['topics_to_avoid'] ?? []);

        $content_types_str = $options['content_types']
            ? implode(', ', $options['content_types'])
            : 'blog posts, articles, guides, reviews, case studies';

        $difficulty_str = $options['difficulty_levels']
            ? implode(', ', $options['difficulty_levels'])
            : 'beginner, intermediate, advanced';

        $category_context = '';
        if ($options['category']) {
            $category_context = "Focus on the '{$options['category']}' category. ";
        }

        $prompt = <<<PROMPT
You are an expert content strategist. Generate {$options['count']} highly relevant blog post/article topics for "{$brand_name}", a company in the {$industry} industry.

BRAND CONTEXT:
- Target Audience: {$target_audience}
- Key Messages: {$key_messages}
- SEO Keywords: {$seo_keywords}
- Topics to Cover: {$topics_to_cover}
- Topics to AVOID: {$topics_to_avoid}

REQUIREMENTS:
{$category_context}Generate topics suitable for these content types: {$content_types_str}
Include a mix of difficulty levels: {$difficulty_str}
Each topic should be specific, actionable, and valuable to the target audience.

OUTPUT FORMAT (JSON array):
Return ONLY a valid JSON array with this exact structure:
[
  {
    "title": "Complete Guide to Cloud Migration: Strategy, Planning, and Execution",
    "keywords": ["cloud migration", "strategy", "planning", "best practices"],
    "target_length": "long",
    "content_type": "guide",
    "difficulty": "intermediate",
    "priority": "high",
    "frequency": "yearly"
  },
  {
    "title": "Weekly Tech Roundup: Latest in Cloud and AI",
    "keywords": ["tech news", "cloud computing", "AI trends"],
    "target_length": "short",
    "content_type": "blog_post",
    "difficulty": "beginner",
    "priority": "medium",
    "frequency": "weekly"
  }
]

IMPORTANT:
- Return ONLY the JSON array, no additional text
- Each topic must have all 7 fields: title, keywords (array), target_length, content_type, difficulty, priority, frequency
- target_length: "short" | "medium" | "long"
- content_type: "blog_post" | "article" | "guide" | "review" | "product" | "social_media" | "newsletter"
- difficulty: "beginner" | "intermediate" | "advanced"
- priority: "low" | "medium" | "high"
- frequency: "once" | "weekly" | "monthly" | "quarterly" | "yearly"

Generate {$options['count']} topics now:
PROMPT;

        return $prompt;
    }

    /**
     * Format target audience for prompt
     *
     * @param array $target_audience Target audience data
     * @return string Formatted string
     */
    private function format_target_audience($target_audience) {
        if (empty($target_audience)) {
            return 'General audience';
        }

        $demographics = $target_audience['demographics'] ?? [];
        $psychographics = $target_audience['psychographics'] ?? [];

        $parts = [];

        if (!empty($demographics['age_range'])) {
            $parts[] = "Age {$demographics['age_range']}";
        }

        if (!empty($demographics['education'])) {
            $parts[] = "{$demographics['education']} education";
        }

        if (!empty($psychographics['interests'])) {
            $interests = is_array($psychographics['interests'])
                ? implode(', ', array_slice($psychographics['interests'], 0, 3))
                : $psychographics['interests'];
            $parts[] = "interested in {$interests}";
        }

        return implode(', ', $parts) ?: 'General audience';
    }

    /**
     * Call AI service to generate topics
     *
     * @param string $prompt AI prompt
     * @param array $options Generation options
     * @return array AI response
     */
    private function call_ai_service($prompt, $options) {
        // Try to use provided AI service or get from WordPress
        if (!$this->ai_service) {
            // Try to get AI service from global WordPress
            // This is a fallback - ideally AI service should be injected
            if (class_exists('AI_Real_Service')) {
                $this->ai_service = new \AI_Real_Service();
            } else {
                return ['error' => 'AI service not available'];
            }
        }

        // Prepare AI request
        $ai_params = [
            'prompt' => $prompt,
            'max_tokens' => 3000,
            'temperature' => 0.7,
            'provider' => $options['ai_provider'] ?? 'openrouter',
            'model' => $options['ai_model'] ?? 'openai/gpt-4o-mini'
        ];

        try {
            // Call AI service (implementation depends on the AI service class)
            if (method_exists($this->ai_service, 'generate_content')) {
                $response = $this->ai_service->generate_content($ai_params);
            } elseif (method_exists($this->ai_service, 'call_ai')) {
                $response = $this->ai_service->call_ai($prompt, $ai_params);
            } else {
                // Fallback: use WordPress HTTP API to call OpenRouter directly
                $response = $this->call_openrouter_directly($prompt, $ai_params);
            }

            return $response;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('AI Topic Generator error: ' . $e->getMessage());
            }
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Call OpenRouter API directly as fallback
     *
     * @param string $prompt AI prompt
     * @param array $params Parameters
     * @return array Response
     */
    private function call_openrouter_directly($prompt, $params) {
        $api_key = get_option('ai_manager_pro_openrouter_api_key');
        if (!$api_key) {
            return ['error' => 'OpenRouter API key not configured'];
        }

        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ],
            'body' => json_encode([
                'model' => $params['model'] ?? 'openai/gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $params['max_tokens'] ?? 3000,
                'temperature' => $params['temperature'] ?? 0.7
            ])
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['choices'][0]['message']['content'])) {
            return ['content' => $body['choices'][0]['message']['content']];
        }

        return ['error' => 'Invalid AI response'];
    }

    /**
     * Parse AI response into structured topics
     *
     * @param string $ai_content AI response content
     * @param array $options Options
     * @return array Parsed topics
     */
    private function parse_ai_response($ai_content, $options) {
        // Clean the response - remove markdown code blocks if present
        $ai_content = trim($ai_content);
        $ai_content = preg_replace('/^```json\s*/m', '', $ai_content);
        $ai_content = preg_replace('/^```\s*/m', '', $ai_content);
        $ai_content = trim($ai_content);

        // Try to decode JSON
        $topics = json_decode($ai_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->logger) {
                $this->logger->error('Failed to parse AI topics JSON: ' . json_last_error_msg());
                $this->logger->debug('AI Content: ' . $ai_content);
            }
            return [];
        }

        if (!is_array($topics)) {
            return [];
        }

        // Validate and enrich each topic
        $validated_topics = [];
        foreach ($topics as $topic) {
            if (!isset($topic['title']) || empty($topic['title'])) {
                continue;
            }

            $validated_topics[] = [
                'title' => sanitize_text_field($topic['title']),
                'keywords' => isset($topic['keywords']) && is_array($topic['keywords'])
                    ? array_map('sanitize_text_field', $topic['keywords'])
                    : [],
                'target_length' => $topic['target_length'] ?? 'medium',
                'content_type' => $topic['content_type'] ?? 'blog_post',
                'difficulty' => $topic['difficulty'] ?? 'intermediate',
                'priority' => $topic['priority'] ?? 'medium',
                'frequency' => $topic['frequency'] ?? 'once',
                'last_used' => null,
                'usage_count' => 0,
                'performance_score' => null
            ];
        }

        return $validated_topics;
    }

    /**
     * Add generated topics to brand's topic pool
     *
     * @param array $brand_data Brand data
     * @param array $topics Topics to add
     * @param string|null $category_name Category to add to (null = create new or use first)
     * @return array Updated brand data
     */
    private function add_topics_to_pool($brand_data, $topics, $category_name = null) {
        if (!isset($brand_data['topic_pool'])) {
            $brand_data['topic_pool'] = [
                'enabled' => true,
                'auto_refresh' => false,
                'categories' => [],
                'topic_selection_rules' => [
                    'avoid_repetition_days' => 30,
                    'prefer_unused' => true,
                    'rotate_categories' => true,
                    'respect_weights' => true,
                    'consider_performance' => false
                ]
            ];
        }

        // Find or create category
        $category_index = null;
        if ($category_name) {
            foreach ($brand_data['topic_pool']['categories'] as $index => $category) {
                if ($category['name'] === $category_name) {
                    $category_index = $index;
                    break;
                }
            }
        }

        // If category not found, use first category or create new
        if ($category_index === null) {
            if (empty($brand_data['topic_pool']['categories'])) {
                // Create new category
                $brand_data['topic_pool']['categories'][] = [
                    'name' => 'AI Generated Topics',
                    'weight' => 25,
                    'topics' => []
                ];
                $category_index = 0;
            } else {
                // Use first category
                $category_index = 0;
            }
        }

        // Add topics to category
        foreach ($topics as $topic) {
            $brand_data['topic_pool']['categories'][$category_index]['topics'][] = $topic;
        }

        return $brand_data;
    }

    /**
     * Refresh topics for a brand (monthly auto-refresh)
     *
     * @param int $brand_id Brand ID
     * @param array $options Refresh options
     * @return array Result
     */
    public function refresh_brand_topics($brand_id, $options = []) {
        global $wpdb;

        $defaults = [
            'count' => 10,  // Generate 10 new topics
            'remove_old' => false,  // Don't remove old topics by default
            'category' => null
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
        if (!$brand_data) {
            return [
                'success' => false,
                'error' => 'Invalid brand data'
            ];
        }

        // Check if auto_refresh is enabled
        if (empty($brand_data['topic_pool']['auto_refresh'])) {
            return [
                'success' => false,
                'error' => 'Auto-refresh is not enabled for this brand'
            ];
        }

        // Generate new topics
        $options['auto_add_to_pool'] = true;
        $result = $this->generate_topics($brand_data, $options);

        if (!$result['success']) {
            return $result;
        }

        // Update brand in database
        $wpdb->update(
            $table_name,
            ['brand_data' => json_encode($result['brand_data'], JSON_UNESCAPED_UNICODE)],
            ['id' => $brand_id]
        );

        return [
            'success' => true,
            'topics_generated' => $result['count'],
            'message' => "Successfully generated {$result['count']} new topics"
        ];
    }
}
