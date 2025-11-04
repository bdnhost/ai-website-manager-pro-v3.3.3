<?php
/**
 * Content Generator
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Content_Generation
 */

namespace AI_Manager_Pro\Modules\Content_Generation;

use AI_Manager_Pro\Modules\AI_Providers\AI_Provider_Manager;
use AI_Manager_Pro\Modules\Brand_Management\Brand_Manager;
use Monolog\Logger;

/**
 * Content Generator Class
 */
class Content_Generator {
    
    /**
     * AI Provider Manager instance
     *
     * @var AI_Provider_Manager
     */
    private $ai_providers;
    
    /**
     * Brand Manager instance
     *
     * @var Brand_Manager
     */
    private $brand_manager;
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Content templates
     *
     * @var array
     */
    private $content_templates;

    /**
     * SEO Template Manager instance
     *
     * @var SEO_Template_Manager
     */
    private $seo_template_manager;
    
    /**
     * Constructor
     *
     * @param AI_Provider_Manager $ai_providers AI Provider Manager instance
     * @param Brand_Manager $brand_manager Brand Manager instance
     * @param Logger $logger Logger instance
     */
    public function __construct(AI_Provider_Manager $ai_providers, Brand_Manager $brand_manager, Logger $logger) {
        $this->ai_providers = $ai_providers;
        $this->brand_manager = $brand_manager;
        $this->logger = $logger;
        $this->content_templates = $this->get_content_templates();
        $this->seo_template_manager = new SEO_Template_Manager();

        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_ai_manager_pro_generate_content', [$this, 'handle_generate_content_ajax']);
    }
    
    /**
     * Get content templates
     *
     * @return array Content templates
     */
    private function get_content_templates() {
        return [
            'blog_post' => [
                'name' => 'Blog Post',
                'description' => 'Generate a comprehensive blog post',
                'system_prompt' => 'You are a professional content writer creating engaging blog posts.',
                'user_prompt_template' => 'Write a comprehensive blog post about "{topic}" for {brand_name}. 

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}
- Key Messages: {key_messages}

Requirements:
- Length: {content_length}
- Tone: {tone}
- Include relevant examples and actionable insights
- Optimize for SEO with keywords: {seo_keywords}
- Structure with clear headings and subheadings

Please create an engaging, informative blog post that aligns with the brand voice and resonates with the target audience.',
                'default_options' => [
                    'max_tokens' => 2000,
                    'temperature' => 0.7
                ]
            ],
            'social_media' => [
                'name' => 'Social Media Post',
                'description' => 'Generate social media content',
                'system_prompt' => 'You are a social media expert creating engaging posts for various platforms.',
                'user_prompt_template' => 'Create a {platform} post about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}
- Hashtags: {hashtags}

Requirements:
- Platform: {platform}
- Tone: {tone}
- Include relevant hashtags
- Engaging and shareable
- Call-to-action if appropriate

Create a compelling social media post that drives engagement.',
                'default_options' => [
                    'max_tokens' => 500,
                    'temperature' => 0.8
                ]
            ],
            'product_description' => [
                'name' => 'Product Description',
                'description' => 'Generate product descriptions',
                'system_prompt' => 'You are a copywriter specializing in compelling product descriptions.',
                'user_prompt_template' => 'Write a product description for "{product_name}" by {brand_name}.

Product Details:
{product_details}

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}
- Unique Selling Points: {unique_selling_points}

Requirements:
- Highlight key features and benefits
- Address customer pain points
- Include compelling call-to-action
- Tone: {tone}
- SEO keywords: {seo_keywords}

Create a persuasive product description that converts browsers into buyers.',
                'default_options' => [
                    'max_tokens' => 800,
                    'temperature' => 0.6
                ]
            ],
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Generate newsletter content',
                'system_prompt' => 'You are an email marketing specialist creating engaging newsletter content.',
                'user_prompt_template' => 'Create a newsletter section about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}
- Key Messages: {key_messages}

Requirements:
- Engaging subject line
- Personal and conversational tone
- Include valuable insights or tips
- Clear call-to-action
- Length: {content_length}

Create newsletter content that provides value and builds relationships with subscribers.',
                'default_options' => [
                    'max_tokens' => 1200,
                    'temperature' => 0.7
                ]
            ],
            'case_study' => [
                'name' => 'Case Study',
                'description' => 'Generate case study content',
                'system_prompt' => 'You are a business writer creating compelling case studies.',
                'user_prompt_template' => 'Write a case study about "{topic}" for {brand_name}.

Case Study Details:
{case_details}

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Unique Selling Points: {unique_selling_points}

Requirements:
- Clear problem-solution-results structure
- Include specific metrics and outcomes
- Professional and authoritative tone
- Length: {content_length}
- SEO keywords: {seo_keywords}

Create a compelling case study that demonstrates value and builds credibility.',
                'default_options' => [
                    'max_tokens' => 2500,
                    'temperature' => 0.5
                ]
            ],
            'article' => [
                'name' => 'Article',
                'description' => 'Generate comprehensive SEO-optimized article',
                'system_prompt' => 'You are a professional content writer creating in-depth, SEO-optimized articles.',
                'user_prompt_template' => 'Write a comprehensive article about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}
- Key Messages: {key_messages}

Requirements:
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include data, statistics, and expert insights
- Create comprehensive coverage of the topic
- Optimize for search engines and readers

Create an authoritative, well-researched article that provides value and ranks well in search results.',
                'default_options' => [
                    'max_tokens' => 3000,
                    'temperature' => 0.6
                ]
            ],
            'guide' => [
                'name' => 'How-To Guide',
                'description' => 'Generate step-by-step how-to guide',
                'system_prompt' => 'You are an expert at creating clear, actionable how-to guides and tutorials.',
                'user_prompt_template' => 'Create a detailed how-to guide for "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Clear step-by-step instructions
- Practical and actionable
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include prerequisites, tools needed, and time estimates
- Add tips, warnings, and common mistakes to avoid

Create a comprehensive guide that helps users successfully complete the task.',
                'default_options' => [
                    'max_tokens' => 3000,
                    'temperature' => 0.5
                ]
            ],
            'review' => [
                'name' => 'Product/Service Review',
                'description' => 'Generate detailed product or service review',
                'system_prompt' => 'You are a professional reviewer creating honest, in-depth product and service reviews.',
                'user_prompt_template' => 'Write a comprehensive review of "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Honest and balanced assessment
- Include pros and cons
- Detailed feature analysis
- Comparison with alternatives
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include ratings and scores
- Provide clear recommendation

Create a thorough review that helps readers make informed decisions.',
                'default_options' => [
                    'max_tokens' => 3500,
                    'temperature' => 0.6
                ]
            ]
        ];
    }
    
    /**
     * Generate content
     *
     * @param array $options Content generation options
     * @return array|false Generated content or false on failure
     */
    public function generate_content($options = []) {
        $defaults = [
            'topic' => '',
            'content_type' => 'blog_post',
            'brand_id' => null,
            'custom_prompt' => '',
            'ai_provider' => null,
            'ai_model' => null,
            'auto_publish' => false,
            'post_status' => 'draft',
            'post_category' => null,
            'post_tags' => []
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        // Validate required options
        if (empty($options['topic']) && empty($options['custom_prompt'])) {
            $this->logger->error('Topic or custom prompt is required for content generation');
            return false;
        }
        
        try {
            // Get brand context
            $brand_context = $this->get_brand_context($options['brand_id']);
            
            // Build prompt
            $prompt = $this->build_prompt($options, $brand_context);
            if (!$prompt) {
                return false;
            }
            
            // Generate content using AI
            $ai_options = [
                'model' => $options['ai_model'],
                'system_message' => $prompt['system_prompt']
            ];
            
            // Merge template options
            if (isset($this->content_templates[$options['content_type']])) {
                $template_options = $this->content_templates[$options['content_type']]['default_options'];
                $ai_options = array_merge($template_options, $ai_options);
            }
            
            $ai_result = $this->ai_providers->generate_content($prompt['user_prompt'], $ai_options);
            
            if (!$ai_result) {
                $this->logger->error('AI content generation failed');
                return false;
            }
            
            // Process generated content
            $processed_content = $this->process_generated_content($ai_result['content'], $options);
            
            // Create WordPress post if requested
            $post_id = null;
            if ($options['auto_publish']) {
                $post_id = $this->create_wordpress_post($processed_content, $options);
            }
            
            $result = [
                'content' => $processed_content,
                'ai_result' => $ai_result,
                'post_id' => $post_id,
                'brand_context' => $brand_context,
                'options' => $options
            ];
            
            $this->logger->info('Content generated successfully', [
                'content_type' => $options['content_type'],
                'topic' => $options['topic'],
                'content_length' => strlen($processed_content['content']),
                'ai_provider' => $ai_result['provider'] ?? 'unknown',
                'ai_model' => $ai_result['model'] ?? 'unknown',
                'post_id' => $post_id
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Content generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'options' => $options
            ]);
            return false;
        }
    }
    
    /**
     * Get brand context
     *
     * @param int|null $brand_id Brand ID
     * @return array Brand context
     */
    private function get_brand_context($brand_id = null) {
        if ($brand_id) {
            $brand = $this->brand_manager->get_brand($brand_id);
        } else {
            $brand = $this->brand_manager->get_active_brand();
        }
        
        if (!$brand) {
            return $this->get_default_brand_context();
        }
        
        $brand_data = json_decode($brand->brand_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Invalid brand data JSON', [
                'brand_id' => $brand->id,
                'error' => json_last_error_msg()
            ]);
            return $this->get_default_brand_context();
        }
        
        return [
            'brand_name' => $brand_data['name'] ?? $brand->name,
            'industry' => $brand_data['industry'] ?? 'general',
            'target_audience' => $this->format_target_audience($brand_data['target_audience'] ?? []),
            'brand_voice' => $this->format_brand_voice($brand_data['brand_voice'] ?? []),
            'tone' => $brand_data['brand_voice']['tone'] ?? 'professional',
            'key_messages' => implode(', ', $brand_data['key_messages'] ?? []),
            'unique_selling_points' => implode(', ', $brand_data['unique_selling_points'] ?? []),
            'seo_keywords' => implode(', ', $brand_data['seo_keywords'] ?? []),
            'hashtags' => implode(' ', array_map(function($tag) {
                return str_starts_with($tag, '#') ? $tag : '#' . $tag;
            }, $brand_data['social_media']['hashtags'] ?? [])),
            'content_guidelines' => $brand_data['content_guidelines'] ?? []
        ];
    }
    
    /**
     * Get default brand context
     *
     * @return array Default brand context
     */
    private function get_default_brand_context() {
        return [
            'brand_name' => get_bloginfo('name'),
            'industry' => 'general',
            'target_audience' => 'general audience',
            'brand_voice' => 'professional and informative',
            'tone' => 'professional',
            'key_messages' => 'providing value to our audience',
            'unique_selling_points' => 'quality content and expertise',
            'seo_keywords' => '',
            'hashtags' => '',
            'content_guidelines' => []
        ];
    }
    
    /**
     * Format target audience for prompt
     *
     * @param array $target_audience Target audience data
     * @return string Formatted target audience
     */
    private function format_target_audience($target_audience) {
        $parts = [];
        
        if (isset($target_audience['demographics'])) {
            $demo = $target_audience['demographics'];
            if (!empty($demo['age_range'])) {
                $parts[] = "ages {$demo['age_range']}";
            }
            if (!empty($demo['gender']) && $demo['gender'] !== 'all') {
                $parts[] = $demo['gender'];
            }
            if (!empty($demo['income_level']) && $demo['income_level'] !== 'all') {
                $parts[] = "{$demo['income_level']} income";
            }
            if (!empty($demo['education']) && $demo['education'] !== 'all') {
                $parts[] = "{$demo['education']} education";
            }
        }
        
        if (isset($target_audience['psychographics']['lifestyle'])) {
            $parts[] = $target_audience['psychographics']['lifestyle'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'general audience';
    }
    
    /**
     * Format brand voice for prompt
     *
     * @param array $brand_voice Brand voice data
     * @return string Formatted brand voice
     */
    private function format_brand_voice($brand_voice) {
        $parts = [];
        
        if (!empty($brand_voice['tone'])) {
            $parts[] = $brand_voice['tone'];
        }
        
        if (!empty($brand_voice['personality_traits'])) {
            $parts[] = implode(', ', $brand_voice['personality_traits']);
        }
        
        if (!empty($brand_voice['communication_style'])) {
            $parts[] = $brand_voice['communication_style'];
        }
        
        return !empty($parts) ? implode('; ', $parts) : 'professional and informative';
    }
    
    /**
     * Build prompt for content generation
     *
     * @param array $options Generation options
     * @param array $brand_context Brand context
     * @return array|false Prompt array or false on failure
     */
    private function build_prompt($options, $brand_context) {
        // Use custom prompt if provided
        if (!empty($options['custom_prompt'])) {
            $user_prompt = $this->replace_placeholders($options['custom_prompt'], $options, $brand_context);

            // Enhance custom prompt with SEO template instructions
            $user_prompt = $this->seo_template_manager->enhance_prompt_with_seo(
                $user_prompt,
                $options['content_type']
            );

            return [
                'system_prompt' => 'You are a professional content writer with expertise in SEO optimization.',
                'user_prompt' => $user_prompt
            ];
        }

        // Use template
        $template = $this->content_templates[$options['content_type']] ?? null;
        if (!$template) {
            $this->logger->error('Content template not found', [
                'content_type' => $options['content_type']
            ]);
            return false;
        }

        $user_prompt = $this->replace_placeholders(
            $template['user_prompt_template'],
            $options,
            $brand_context
        );

        // Enhance prompt with SEO template instructions
        $user_prompt = $this->seo_template_manager->enhance_prompt_with_seo(
            $user_prompt,
            $options['content_type']
        );

        return [
            'system_prompt' => $template['system_prompt'] . ' You are an expert in creating SEO-optimized content with proper structure and formatting.',
            'user_prompt' => $user_prompt
        ];
    }
    
    /**
     * Replace placeholders in prompt template
     *
     * @param string $template Template string
     * @param array $options Generation options
     * @param array $brand_context Brand context
     * @return string Processed template
     */
    private function replace_placeholders($template, $options, $brand_context) {
        $placeholders = array_merge($options, $brand_context);
        
        // Add additional placeholders
        $placeholders['content_length'] = $this->get_content_length($options['content_type']);
        $placeholders['platform'] = $options['platform'] ?? 'general';
        
        foreach ($placeholders as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace('{' . $key . '}', $value, $template);
            }
        }
        
        return $template;
    }
    
    /**
     * Get content length for content type
     *
     * @param string $content_type Content type
     * @return string Content length description
     */
    private function get_content_length($content_type) {
        $length_map = [
            'blog_post' => '800-1200 words',
            'article' => '1500-2500 words',
            'guide' => '1500-3000 words',
            'review' => '1200-2000 words',
            'social_media' => '50-280 characters',
            'product_description' => '150-300 words',
            'product' => '400-800 words',
            'newsletter' => '300-600 words',
            'case_study' => '1000-2000 words'
        ];

        return $length_map[$content_type] ?? '500-800 words';
    }
    
    /**
     * Process generated content
     *
     * @param string $raw_content Raw AI-generated content
     * @param array $options Generation options
     * @return array Processed content
     */
    private function process_generated_content($raw_content, $options) {
        // Extract title if it exists
        $title = $this->extract_title($raw_content, $options);

        // Clean and format content
        $content = $this->clean_content($raw_content);

        // Generate excerpt
        $excerpt = $this->generate_excerpt($content);

        // Generate meta description for SEO
        $meta_description = $this->generate_meta_description($content);

        // Validate SEO structure
        $seo_validation = $this->seo_template_manager->validate_seo_structure(
            $content,
            $options['content_type']
        );

        // Log SEO validation results
        if (!$seo_validation['passed']) {
            $this->logger->warning('Generated content did not meet all SEO requirements', [
                'content_type' => $options['content_type'],
                'seo_score' => $seo_validation['score'],
                'issues' => $seo_validation['issues']
            ]);
        } else {
            $this->logger->info('Generated content meets SEO requirements', [
                'content_type' => $options['content_type'],
                'seo_score' => $seo_validation['score']
            ]);
        }

        return [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'meta_description' => $meta_description,
            'content_type' => $options['content_type'],
            'topic' => $options['topic'],
            'seo_validation' => $seo_validation
        ];
    }
    
    /**
     * Extract title from content
     *
     * @param string $content Content
     * @param array $options Generation options
     * @return string Extracted or generated title
     */
    private function extract_title($content, $options) {
        // Look for markdown-style heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Look for HTML heading
        if (preg_match('/<h[1-6][^>]*>(.+?)<\/h[1-6]>/i', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }
        
        // Generate title from topic
        if (!empty($options['topic'])) {
            return ucwords($options['topic']);
        }
        
        // Extract first sentence as title
        $sentences = preg_split('/[.!?]+/', $content, 2);
        if (!empty($sentences[0])) {
            $title = trim($sentences[0]);
            if (strlen($title) > 60) {
                $title = substr($title, 0, 57) . '...';
            }
            return $title;
        }
        
        return 'Generated Content';
    }
    
    /**
     * Clean content
     *
     * @param string $content Raw content
     * @return string Cleaned content
     */
    private function clean_content($content) {
        // Remove title if it's at the beginning
        $content = preg_replace('/^#\s+.+\n\n?/m', '', $content);
        
        // Clean up extra whitespace
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);
        
        // Convert markdown to HTML if needed
        if (strpos($content, '##') !== false || strpos($content, '**') !== false) {
            $content = $this->markdown_to_html($content);
        }
        
        return $content;
    }
    
    /**
     * Simple markdown to HTML conversion
     *
     * @param string $markdown Markdown content
     * @return string HTML content
     */
    private function markdown_to_html($markdown) {
        // Headers
        $markdown = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $markdown);
        
        // Bold
        $markdown = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);
        
        // Italic
        $markdown = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $markdown);
        
        // Line breaks
        $markdown = str_replace("\n", "<br>\n", $markdown);
        
        return $markdown;
    }
    
    /**
     * Generate excerpt
     *
     * @param string $content Content
     * @return string Excerpt
     */
    private function generate_excerpt($content) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        if (strlen($text) <= 155) {
            return $text;
        }
        
        return substr($text, 0, 152) . '...';
    }
    
    /**
     * Generate meta description
     *
     * @param string $content Content
     * @return string Meta description
     */
    private function generate_meta_description($content) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        if (strlen($text) <= 160) {
            return $text;
        }
        
        return substr($text, 0, 157) . '...';
    }
    
    /**
     * Create WordPress post
     *
     * @param array $processed_content Processed content
     * @param array $options Generation options
     * @return int|false Post ID on success, false on failure
     */
    private function create_wordpress_post($processed_content, $options) {
        $post_data = [
            'post_title' => $processed_content['title'],
            'post_content' => $processed_content['content'],
            'post_excerpt' => $processed_content['excerpt'],
            'post_status' => $options['post_status'],
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'meta_input' => [
                '_ai_generated' => true,
                '_ai_content_type' => $options['content_type'],
                '_ai_topic' => $options['topic'],
                '_ai_generation_date' => current_time('mysql')
            ]
        ];
        
        // Add category
        if (!empty($options['post_category'])) {
            $post_data['post_category'] = [$options['post_category']];
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            $this->logger->error('Failed to create WordPress post', [
                'error' => $post_id->get_error_message()
            ]);
            return false;
        }
        
        // Add tags
        if (!empty($options['post_tags'])) {
            wp_set_post_tags($post_id, $options['post_tags']);
        }
        
        // Add meta description
        if (!empty($processed_content['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $processed_content['meta_description']);
        }
        
        return $post_id;
    }
    
    /**
     * Get content templates
     *
     * @return array Content templates
     */
    public function get_templates() {
        return $this->content_templates;
    }
    
    /**
     * Handle generate content AJAX request
     */
    public function handle_generate_content_ajax() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('ai_manager_generate_content')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            $options = [
                'topic' => sanitize_text_field($_POST['topic'] ?? ''),
                'content_type' => sanitize_text_field($_POST['content_type'] ?? 'blog_post'),
                'brand_id' => intval($_POST['brand_id'] ?? 0) ?: null,
                'custom_prompt' => sanitize_textarea_field($_POST['custom_prompt'] ?? ''),
                'ai_model' => sanitize_text_field($_POST['ai_model'] ?? ''),
                'auto_publish' => (bool) ($_POST['auto_publish'] ?? false),
                'post_status' => sanitize_text_field($_POST['post_status'] ?? 'draft')
            ];
            
            $result = $this->generate_content($options);
            
            if ($result) {
                wp_send_json_success([
                    'content' => $result['content'],
                    'post_id' => $result['post_id'],
                    'message' => 'Content generated successfully'
                ]);
            } else {
                wp_send_json_error('Failed to generate content');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Content generation AJAX error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json_error('An error occurred during content generation');
        }
    }
}

