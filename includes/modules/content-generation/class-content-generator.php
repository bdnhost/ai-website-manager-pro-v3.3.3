<?php
/**
 * Content Generator with Integrated SEO Templates
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Content_Generation
 */

namespace AI_Manager_Pro\Modules\Content_Generation;

use AI_Manager_Pro\Modules\AI_Providers\AI_Provider_Manager;
use AI_Manager_Pro\Modules\Brand_Management\Brand_Manager;
use Monolog\Logger;

/**
 * Content Generator Class with SEO Templates
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
     * SEO templates for structured content
     *
     * @var array
     */
    private $seo_templates;

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
        $this->seo_templates = $this->init_seo_templates();

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_ai_manager_pro_generate_content', [$this, 'handle_generate_content_ajax']);
    }

    /**
     * Initialize SEO templates
     *
     * @return array SEO templates
     */
    private function init_seo_templates() {
        return [
            'article' => $this->get_article_seo_instructions(),
            'guide' => $this->get_guide_seo_instructions(),
            'review' => $this->get_review_seo_instructions(),
            'product' => $this->get_product_seo_instructions(),
            'blog_post' => $this->get_blog_post_seo_instructions()
        ];
    }

    /**
     * Get Article SEO instructions
     */
    private function get_article_seo_instructions() {
        return '

=== CRITICAL SEO STRUCTURE REQUIREMENTS ===

You MUST follow this exact structure:

1. START with ONE H1 title (the main article title)
2. Write a compelling 2-3 sentence introduction
3. Create a Table of Contents with links:
   <h2>תוכן עניינים</h2>
   <ul>
   <li><a href="#section-1">Section Title</a></li>
   </ul>

4. Create 3-5 main sections, each with:
   - H2 heading with id attribute (e.g., <h2 id="section-1">Title</h2>)
   - 2-3 paragraphs of content
   - 2-3 H3 subsections under each H2
   - At least ONE comparison table using proper HTML:
     <table>
     <thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead>
     <tbody><tr><td>Data 1</td><td>Data 2</td><td>Data 3</td></tr></tbody>
     </table>

5. Add a FAQ section:
   <h2>שאלות נפוצות</h2>
   <h3>שאלה 1?</h3>
   <p>תשובה מפורטת...</p>

6. End with a Conclusion:
   <h2>סיכום</h2>
   <p>Summary and call to action...</p>

FORMATTING RULES:
- Use <strong> for important terms
- Use <ul><li> for bullet points
- Use <ol><li> for numbered lists
- Include specific data and examples
- Bold key phrases with <strong>
';
    }

    /**
     * Get Guide SEO instructions
     */
    private function get_guide_seo_instructions() {
        return '

=== CRITICAL SEO STRUCTURE FOR GUIDES ===

You MUST follow this exact structure:

1. H1: "איך ל[Action]: מדריך מלא [Year]"
2. Brief introduction (what they will learn)
3. Table of Contents
4. <h2>מה תצטרכו</h2>
   <ul>
   <li>דרישה 1</li>
   <li>דרישה 2</li>
   </ul>

5. <h2>מדריך צעד אחר צעד</h2>
   <h3>שלב 1: [Action Name]</h3>
   <p>Detailed explanation...</p>
   <ol>
   <li>תת-שלב 1</li>
   <li>תת-שלב 2</li>
   </ol>
   <p><strong>זמן משוער:</strong> X דקות</p>

   Repeat for each step (5-8 steps total)

6. <h2>טעויות נפוצות שכדאי להימנע מהן</h2>
   <ul>
   <li><strong>טעות 1:</strong> הסבר</li>
   </ul>

7. <h2>טיפים מקצועיים</h2>
   <table>
   <thead><tr><th>טיפ</th><th>תיאור</th><th>השפעה</th></tr></thead>
   <tbody><tr><td>טיפ 1</td><td>הסבר</td><td>תועלת</td></tr></tbody>
   </table>

8. FAQ section with H2
9. <h2>סיכום</h2>

MUST INCLUDE: Time estimates, numbered steps, tips table, common mistakes
';
    }

    /**
     * Get Review SEO instructions
     */
    private function get_review_seo_instructions() {
        return '

=== CRITICAL SEO STRUCTURE FOR REVIEWS ===

You MUST follow this exact structure:

1. H1: "[Product Name] Review [Year]: Is It Worth It?"
2. Introduction with quick verdict
3. <h2>סקירה מהירה</h2>
   <table>
   <tbody>
   <tr><th>שם המוצר</th><td>[Name]</td></tr>
   <tr><th>קטגוריה</th><td>[Category]</td></tr>
   <tr><th>מחיר</th><td>₪XX/חודש</td></tr>
   <tr><th>דירוג</th><td>★★★★☆ (4/5)</td></tr>
   </tbody>
   </table>

4. <h2>יתרונות וחסרונות</h2>
   <table>
   <thead><tr><th>יתרונות ✅</th><th>חסרונות ❌</th></tr></thead>
   <tbody>
   <tr><td><strong>יתרון 1:</strong> הסבר</td><td><strong>חיסרון 1:</strong> הסבר</td></tr>
   </tbody>
   </table>

5. <h2>ניתוח מפורט</h2>
   <h3>תכונה 1: [Feature Name]</h3>
   <p>Analysis... <strong>דירוג: 8/10</strong></p>

6. <h2>השוואה עם מתחרים</h2>
   <table>
   <thead><tr><th>תכונה</th><th>[Product]</th><th>מתחרה 1</th><th>מתחרה 2</th></tr></thead>
   <tbody>
   <tr><td>מחיר</td><td>₪XX</td><td>₪YY</td><td>₪ZZ</td></tr>
   <tr><td>תכונה A</td><td>✅</td><td>❌</td><td>✅</td></tr>
   </tbody>
   </table>

7. <h2>פסק הדין שלנו</h2>
   <p><strong>דירוג כולל: ★★★★☆ (4.2/5)</strong></p>
   <ul>
   <li><strong>תכונות:</strong> 8.5/10</li>
   <li><strong>קלות שימוש:</strong> 9/10</li>
   <li><strong>תמורה למחיר:</strong> 7.5/10</li>
   </ul>

8. FAQ section
9. <h2>המלצה סופית</h2>

MUST INCLUDE: Ratings with stars, pros/cons table, comparison table, overall score
';
    }

    /**
     * Get Product SEO instructions
     */
    private function get_product_seo_instructions() {
        return '

=== CRITICAL SEO STRUCTURE FOR PRODUCTS ===

You MUST follow this exact structure:

1. H1: "[Product Name] - [Main Benefit/USP]"
2. Brief compelling description (2-3 sentences)
3. <h2>תכונות עיקריות במבט חטוף</h2>
   <ul>
   <li>✅ <strong>תכונה 1:</strong> תועלת</li>
   <li>✅ <strong>תכונה 2:</strong> תועלת</li>
   </ul>

4. <h2>מה כלול באריזה</h2>
   <ul><li>פריט 1</li><li>פריט 2</li></ul>

5. <h2>תכונות מפורטות</h2>
   <h3>תכונה 1: [Name]</h3>
   <p><strong>מה זה עושה:</strong> הסבר</p>
   <p><strong>למה זה חשוב:</strong> תועלת</p>

6. <h2>מפרטים טכניים</h2>
   <table>
   <tbody>
   <tr><th>מידות</th><td>XX x YY x ZZ ס"מ</td></tr>
   <tr><th>משקל</th><td>XX ק"ג</td></tr>
   <tr><th>חומר</th><td>[Material]</td></tr>
   <tr><th>אחריות</th><td>X שנים</td></tr>
   </tbody>
   </table>

7. <h2>תרחישי שימוש בחיים האמיתיים</h2>
   <h3>שימוש 1: [Scenario]</h3>
   <p>How the product solves this...</p>

8. <h2>מה מייחד אותו</h2>
   <table>
   <thead><tr><th>תכונה</th><th>המוצר שלנו</th><th>מתחרה A</th></tr></thead>
   <tbody><tr><td>תכונה 1</td><td>✅ כן</td><td>❌ לא</td></tr></tbody>
   </table>

9. <h2>ערבות לאיכות</h2>
   <ul>
   <li>✅ <strong>אחריות X שנים:</strong> כיסוי מלא</li>
   <li>✅ <strong>החזר כספי תוך 30 יום</strong></li>
   </ul>

10. FAQ section
11. <h2>מוכנים להזמין?</h2>

MUST INCLUDE: Features with checkmarks, specs table, comparison, trust elements
';
    }

    /**
     * Get Blog Post SEO instructions
     */
    private function get_blog_post_seo_instructions() {
        return '

=== CRITICAL SEO STRUCTURE FOR BLOG POSTS ===

You MUST follow this exact structure:

1. ONE H1 title (engaging and keyword-rich)
2. Introduction (2-3 sentences with a hook)
3. 3-4 main sections with H2 headings, each with:
   - 2-3 H3 subsections
   - Content with examples
   - At least one list (ul or ol)
4. Include at least ONE table for comparison or data
5. Use <strong> for important terms
6. Add a conclusion with H2
7. Include internal linking opportunities: [link to: related topic]

EXAMPLE:
<h1>Main Blog Post Title with Keywords</h1>
<p>Hook paragraph...</p>

<h2>First Main Point</h2>
<p>Content...</p>
<h3>Subsection 1.1</h3>
<p>Details with <strong>important points</strong>...</p>

<h2>Conclusion</h2>
<p>Summary...</p>
';
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
                'system_prompt' => 'You are a professional content writer creating engaging, SEO-optimized blog posts with proper HTML structure.',
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
- Use proper HTML structure with headings and tables

Please create an engaging, informative blog post that aligns with the brand voice and resonates with the target audience.',
                'default_options' => [
                    'max_tokens' => 2500,
                    'temperature' => 0.7
                ]
            ],
            'article' => [
                'name' => 'Article',
                'description' => 'Generate comprehensive SEO-optimized article',
                'system_prompt' => 'You are a professional content writer creating in-depth, SEO-optimized articles with perfect HTML structure including tables, lists, and proper headings.',
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
- Use proper HTML structure

Create an authoritative, well-researched article that provides value and ranks well in search results.',
                'default_options' => [
                    'max_tokens' => 3500,
                    'temperature' => 0.6
                ]
            ],
            'guide' => [
                'name' => 'How-To Guide',
                'description' => 'Generate step-by-step how-to guide',
                'system_prompt' => 'You are an expert at creating clear, actionable how-to guides with perfect HTML structure, numbered steps, tables, and detailed instructions.',
                'user_prompt_template' => 'Create a detailed how-to guide for "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Clear step-by-step instructions with HTML structure
- Practical and actionable
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include prerequisites, tools needed, and time estimates
- Add tips, warnings, and common mistakes

Create a comprehensive guide that helps users successfully complete the task.',
                'default_options' => [
                    'max_tokens' => 3500,
                    'temperature' => 0.5
                ]
            ],
            'review' => [
                'name' => 'Product/Service Review',
                'description' => 'Generate detailed product or service review',
                'system_prompt' => 'You are a professional reviewer creating honest, in-depth reviews with perfect HTML structure including comparison tables, pros/cons tables, and ratings.',
                'user_prompt_template' => 'Write a comprehensive review of "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Honest and balanced assessment with proper HTML structure
- Include pros/cons table and comparison tables
- Detailed feature analysis
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include ratings with stars (★) and scores (X/10)

Create a thorough review that helps readers make informed decisions.',
                'default_options' => [
                    'max_tokens' => 4000,
                    'temperature' => 0.6
                ]
            ],
            'product' => [
                'name' => 'Product Description',
                'description' => 'E-commerce product description',
                'system_prompt' => 'You are a copywriter creating compelling product descriptions with proper HTML structure including feature lists, specification tables, and comparison tables.',
                'user_prompt_template' => 'Write a product description for "{product_name}" by {brand_name}.

Product Details:
{product_details}

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}

Requirements:
- Highlight key features with checkmarks (✅)
- Include specifications table
- Address customer pain points
- Include compelling call-to-action
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Use proper HTML structure

Create a persuasive product description with tables and lists.',
                'default_options' => [
                    'max_tokens' => 2000,
                    'temperature' => 0.6
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

Create a compelling social media post that drives engagement.',
                'default_options' => [
                    'max_tokens' => 500,
                    'temperature' => 0.8
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

Requirements:
- Personal and conversational tone
- Include valuable insights
- Clear call-to-action
- Length: {content_length}

Create newsletter content that provides value.',
                'default_options' => [
                    'max_tokens' => 1200,
                    'temperature' => 0.7
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

            // Build prompt with SEO instructions
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
                'seo_score' => $processed_content['seo_score'] ?? 0,
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
     * Build prompt for content generation with SEO instructions
     *
     * @param array $options Generation options
     * @param array $brand_context Brand context
     * @return array|false Prompt array or false on failure
     */
    private function build_prompt($options, $brand_context) {
        // Get template
        $template = $this->content_templates[$options['content_type']] ?? null;
        if (!$template) {
            $this->logger->error('Content template not found', [
                'content_type' => $options['content_type']
            ]);
            return false;
        }

        // Build user prompt
        if (!empty($options['custom_prompt'])) {
            $user_prompt = $this->replace_placeholders($options['custom_prompt'], $options, $brand_context);
        } else {
            $user_prompt = $this->replace_placeholders(
                $template['user_prompt_template'],
                $options,
                $brand_context
            );
        }

        // Add SEO instructions based on content type
        $seo_instructions = $this->seo_templates[$options['content_type']] ?? $this->seo_templates['blog_post'];
        $user_prompt .= $seo_instructions;

        return [
            'system_prompt' => $template['system_prompt'],
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
        $placeholders['product_name'] = $options['topic'];
        $placeholders['product_details'] = $options['product_details'] ?? 'Standard product features';

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
            'product' => '400-800 words',
            'social_media' => '50-280 characters',
            'newsletter' => '300-600 words'
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
        $seo_score = $this->validate_seo_structure($content, $options['content_type']);

        return [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'meta_description' => $meta_description,
            'content_type' => $options['content_type'],
            'topic' => $options['topic'],
            'seo_score' => $seo_score
        ];
    }

    /**
     * Validate SEO structure and return score
     *
     * @param string $content Content to validate
     * @param string $content_type Content type
     * @return int SEO score (0-100)
     */
    private function validate_seo_structure($content, $content_type) {
        $score = 100;

        // Check H1 count (should be exactly 1)
        $h1_count = preg_match_all('/<h1[^>]*>/i', $content);
        if ($h1_count !== 1) {
            $score -= 20;
            $this->logger->warning("SEO: H1 count is {$h1_count}, should be 1");
        }

        // Check H2 count (should have at least 3)
        $h2_count = preg_match_all('/<h2[^>]*>/i', $content);
        if ($h2_count < 3) {
            $score -= 15;
            $this->logger->warning("SEO: H2 count is {$h2_count}, should be at least 3");
        }

        // Check for tables (except social media)
        if ($content_type !== 'social_media') {
            $table_count = preg_match_all('/<table[^>]*>/i', $content);
            if ($table_count === 0) {
                $score -= 10;
                $this->logger->warning("SEO: No tables found, should have at least 1");
            }
        }

        // Check for lists
        $list_count = preg_match_all('/<[uo]l[^>]*>/i', $content);
        if ($list_count === 0) {
            $score -= 10;
            $this->logger->warning("SEO: No lists found");
        }

        // Check for strong tags (important terms)
        $strong_count = preg_match_all('/<strong[^>]*>/i', $content);
        if ($strong_count < 3) {
            $score -= 5;
        }

        $this->logger->info("SEO validation score: {$score}/100 for content type: {$content_type}");

        return max(0, $score);
    }

    /**
     * Extract title from content
     *
     * @param string $content Content
     * @param array $options Generation options
     * @return string Extracted or generated title
     */
    private function extract_title($content, $options) {
        // Look for HTML heading
        if (preg_match('/<h1[^>]*>(.+?)<\/h1>/i', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        // Look for markdown-style heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Generate title from topic
        if (!empty($options['topic'])) {
            return ucwords($options['topic']);
        }

        // Extract first sentence as title
        $sentences = preg_split('/[.!?]+/', strip_tags($content), 2);
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
        // Headers with IDs
        $markdown = preg_replace_callback('/^### (.+)$/m', function($matches) {
            $id = sanitize_title($matches[1]);
            return '<h3 id="' . $id . '">' . $matches[1] . '</h3>';
        }, $markdown);

        $markdown = preg_replace_callback('/^## (.+)$/m', function($matches) {
            $id = sanitize_title($matches[1]);
            return '<h2 id="' . $id . '">' . $matches[1] . '</h2>';
        }, $markdown);

        $markdown = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $markdown);

        // Bold
        $markdown = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);

        // Italic
        $markdown = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $markdown);

        // Line breaks
        $markdown = nl2br($markdown);

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
                '_ai_seo_score' => $processed_content['seo_score'],
                '_ai_generation_date' => current_time('mysql')
            ]
        ];

        // Add category if provided
        if (!empty($options['post_category'])) {
            // Support both category ID and category name
            if (is_numeric($options['post_category'])) {
                $post_data['post_category'] = [$options['post_category']];
            } else {
                // Try to find category by name
                $category = get_category_by_slug($options['post_category']);
                if (!$category) {
                    $category = get_term_by('name', $options['post_category'], 'category');
                }
                if ($category) {
                    $post_data['post_category'] = [$category->term_id];
                }
            }
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

        // Add meta description for SEO plugins
        if (!empty($processed_content['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $processed_content['meta_description']);
            update_post_meta($post_id, '_rank_math_description', $processed_content['meta_description']);
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
                'post_status' => sanitize_text_field($_POST['post_status'] ?? 'draft'),
                'post_category' => sanitize_text_field($_POST['post_category'] ?? ''),
                'post_tags' => array_map('sanitize_text_field', $_POST['post_tags'] ?? [])
            ];

            $result = $this->generate_content($options);

            if ($result) {
                wp_send_json_success([
                    'content' => $result['content'],
                    'post_id' => $result['post_id'],
                    'seo_score' => $result['content']['seo_score'],
                    'message' => 'Content generated successfully with SEO score: ' . $result['content']['seo_score'] . '/100'
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
