<?php
/**
 * SEO Template Manager
 *
 * Manages SEO-optimized templates for different content types
 * Automatically structures content with proper headings, tables, and SEO elements
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Content_Generation
 */

namespace AI_Manager_Pro\Modules\Content_Generation;

/**
 * SEO Template Manager Class
 */
class SEO_Template_Manager {

    /**
     * SEO templates for different content types
     *
     * @var array
     */
    private $seo_templates;

    /**
     * Constructor
     */
    public function __construct() {
        $this->seo_templates = $this->init_seo_templates();
    }

    /**
     * Initialize SEO templates
     *
     * @return array SEO templates
     */
    private function init_seo_templates() {
        return [
            'article' => [
                'name' => 'Article',
                'description' => 'SEO-optimized article template',
                'structure' => [
                    'introduction' => true,
                    'main_sections' => 3,
                    'subsections_per_section' => 2,
                    'conclusion' => true,
                    'faq' => true,
                    'table_of_contents' => true
                ],
                'seo_prompt_additions' => '

IMPORTANT - SEO STRUCTURE REQUIREMENTS:
1. Start with a compelling H1 title (only one H1)
2. Include a 2-3 sentence introduction paragraph
3. Add Table of Contents after introduction (use HTML list with anchor links)
4. Create 3-5 main sections with H2 headings
5. Each main section should have 2-3 subsections with H3 headings
6. Use H4 for sub-subsections if needed
7. Include at least one comparison table with proper HTML table markup
8. Add a FAQ section with H2 "Frequently Asked Questions"
9. Each FAQ should use H3 for the question
10. End with a conclusion section with H2 "Conclusion"
11. Use bullet points (ul/li) and numbered lists (ol/li) where appropriate
12. Include internal linking opportunities (mention [link to: relevant topic])
13. Use bold (**) for important terms
14. Maintain proper heading hierarchy (H1 → H2 → H3 → H4)

EXAMPLE STRUCTURE:
<h1>Main Article Title</h1>
<p>Introduction paragraph...</p>

<div class="table-of-contents">
<h2>Table of Contents</h2>
<ul>
<li><a href="#section-1">Section 1 Title</a></li>
<li><a href="#section-2">Section 2 Title</a></li>
</ul>
</div>

<h2 id="section-1">First Main Section</h2>
<p>Content...</p>

<h3>Subsection 1.1</h3>
<p>Content...</p>

<h3>Subsection 1.2</h3>
<p>Content with a table:</p>
<table>
<thead>
<tr><th>Feature</th><th>Description</th><th>Benefit</th></tr>
</thead>
<tbody>
<tr><td>Feature 1</td><td>Description</td><td>Benefit</td></tr>
</tbody>
</table>

<h2 id="section-2">Second Main Section</h2>
<p>Content...</p>

<h2>Frequently Asked Questions</h2>
<h3>Question 1?</h3>
<p>Answer...</p>

<h2>Conclusion</h2>
<p>Summary and call to action...</p>',
                'heading_structure' => [
                    'h1' => 1,  // Only one H1
                    'h2' => '3-5',  // Main sections
                    'h3' => '6-15',  // Subsections
                    'h4' => '0-8'  // Optional sub-subsections
                ],
                'content_elements' => [
                    'tables' => true,
                    'lists' => true,
                    'faq' => true,
                    'toc' => true,
                    'images_placeholders' => true
                ]
            ],
            'guide' => [
                'name' => 'How-To Guide',
                'description' => 'Step-by-step guide template',
                'structure' => [
                    'introduction' => true,
                    'prerequisites' => true,
                    'step_by_step' => true,
                    'tips_section' => true,
                    'conclusion' => true,
                    'table_of_contents' => true
                ],
                'seo_prompt_additions' => '

IMPORTANT - SEO STRUCTURE REQUIREMENTS FOR GUIDE:
1. Start with H1 title in "How to [Action]" format
2. Brief introduction paragraph (what you\'ll learn)
3. Table of Contents
4. "What You\'ll Need" section with H2 (prerequisites)
5. Main "Step-by-Step Guide" section with H2
6. Each step should be an H3: "Step 1: [Action]", "Step 2: [Action]", etc.
7. Include numbered lists (ol/li) for sub-steps within each step
8. Add a tips table with HTML table markup
9. "Common Mistakes to Avoid" section with H2
10. "Pro Tips" section with H2 and bullet points
11. "Frequently Asked Questions" section with H2
12. Conclusion with H2
13. Use bold for important warnings or notes
14. Include time estimates for each major step

EXAMPLE STRUCTURE:
<h1>How to [Accomplish Task]: Complete Guide for [Year]</h1>
<p>Introduction explaining what you\'ll achieve...</p>

<div class="table-of-contents">
<h2>Table of Contents</h2>
<ol>
<li><a href="#what-youll-need">What You\'ll Need</a></li>
<li><a href="#step-by-step">Step-by-Step Guide</a></li>
<li><a href="#tips">Pro Tips</a></li>
</ol>
</div>

<h2 id="what-youll-need">What You\'ll Need</h2>
<ul>
<li>Item 1</li>
<li>Item 2</li>
</ul>

<h2 id="step-by-step">Step-by-Step Guide</h2>

<h3>Step 1: [First Action]</h3>
<p>Detailed explanation...</p>
<ol>
<li>Sub-step 1</li>
<li>Sub-step 2</li>
</ol>
<p><strong>Time required:</strong> 10 minutes</p>

<h3>Step 2: [Second Action]</h3>
<p>Detailed explanation...</p>

<h2 id="common-mistakes">Common Mistakes to Avoid</h2>
<ul>
<li><strong>Mistake 1:</strong> Description</li>
<li><strong>Mistake 2:</strong> Description</li>
</ul>

<h2 id="tips">Pro Tips</h2>
<table>
<thead>
<tr><th>Tip</th><th>Description</th><th>Impact</th></tr>
</thead>
<tbody>
<tr><td>Tip 1</td><td>Explanation</td><td>Benefit</td></tr>
</tbody>
</table>

<h2>Frequently Asked Questions</h2>
<h3>Question 1?</h3>
<p>Answer...</p>

<h2>Conclusion</h2>
<p>Summary and next steps...</p>',
                'heading_structure' => [
                    'h1' => 1,
                    'h2' => '4-6',
                    'h3' => '5-12',
                    'h4' => '0-5'
                ],
                'content_elements' => [
                    'tables' => true,
                    'lists' => true,
                    'numbered_steps' => true,
                    'faq' => true,
                    'toc' => true,
                    'time_estimates' => true
                ]
            ],
            'review' => [
                'name' => 'Product/Service Review',
                'description' => 'Comprehensive review template',
                'structure' => [
                    'introduction' => true,
                    'overview' => true,
                    'pros_cons' => true,
                    'detailed_analysis' => true,
                    'comparison_table' => true,
                    'rating' => true,
                    'conclusion' => true,
                    'faq' => true
                ],
                'seo_prompt_additions' => '

IMPORTANT - SEO STRUCTURE REQUIREMENTS FOR REVIEW:
1. H1 title: "[Product/Service Name] Review [Year]: Is It Worth It?"
2. Brief introduction with overall verdict
3. Table of Contents
4. "Quick Overview" section with H2 and specifications table
5. "Pros and Cons" section with H2 and two-column table
6. "Detailed Analysis" as H2 with multiple H3 subsections for features
7. "Comparison with Alternatives" section with H2 and comparison table
8. "Who Is It For?" section with H2
9. "Pricing and Plans" section with H2 and pricing table
10. "Our Verdict" section with H2 and rating
11. FAQ section with H2
12. Final recommendation with H2 "Final Thoughts"
13. Use star ratings (★) and scores where appropriate
14. Include bold for pros/cons and important points

EXAMPLE STRUCTURE:
<h1>[Product Name] Review 2025: Is It Worth the Investment?</h1>
<p>Introduction with quick verdict...</p>

<div class="table-of-contents">
<h2>Table of Contents</h2>
<ul>
<li><a href="#overview">Quick Overview</a></li>
<li><a href="#pros-cons">Pros and Cons</a></li>
<li><a href="#analysis">Detailed Analysis</a></li>
<li><a href="#comparison">Comparison</a></li>
<li><a href="#verdict">Our Verdict</a></li>
</ul>
</div>

<h2 id="overview">Quick Overview</h2>
<table>
<tbody>
<tr><th>Product Name</th><td>[Name]</td></tr>
<tr><th>Category</th><td>[Category]</td></tr>
<tr><th>Price</th><td>$XX/month</td></tr>
<tr><th>Rating</th><td>★★★★☆ (4/5)</td></tr>
<tr><th>Best For</th><td>[Target audience]</td></tr>
</tbody>
</table>

<h2 id="pros-cons">Pros and Cons</h2>
<table>
<thead>
<tr><th>Pros ✅</th><th>Cons ❌</th></tr>
</thead>
<tbody>
<tr><td><strong>Pro 1:</strong> Description</td><td><strong>Con 1:</strong> Description</td></tr>
<tr><td><strong>Pro 2:</strong> Description</td><td><strong>Con 2:</strong> Description</td></tr>
</tbody>
</table>

<h2 id="analysis">Detailed Analysis</h2>

<h3>Feature 1: [Feature Name]</h3>
<p>Analysis... <strong>Rating: 8/10</strong></p>

<h3>Feature 2: [Feature Name]</h3>
<p>Analysis... <strong>Rating: 7/10</strong></p>

<h2 id="comparison">Comparison with Alternatives</h2>
<table>
<thead>
<tr><th>Feature</th><th>[Product]</th><th>Alternative 1</th><th>Alternative 2</th></tr>
</thead>
<tbody>
<tr><td>Price</td><td>$XX</td><td>$YY</td><td>$ZZ</td></tr>
<tr><td>Feature A</td><td>✅</td><td>✅</td><td>❌</td></tr>
</tbody>
</table>

<h2 id="who-is-it-for">Who Is It For?</h2>
<ul>
<li><strong>Best for:</strong> Description</li>
<li><strong>Not ideal for:</strong> Description</li>
</ul>

<h2 id="pricing">Pricing and Plans</h2>
<table>
<thead>
<tr><th>Plan</th><th>Price</th><th>Features</th><th>Best For</th></tr>
</thead>
<tbody>
<tr><td>Basic</td><td>$X/mo</td><td>Features</td><td>Small teams</td></tr>
<tr><td>Pro</td><td>$XX/mo</td><td>Features</td><td>Growing businesses</td></tr>
</tbody>
</table>

<h2 id="verdict">Our Verdict</h2>
<p><strong>Overall Rating: ★★★★☆ (4.2/5)</strong></p>
<ul>
<li><strong>Features:</strong> 8.5/10</li>
<li><strong>Ease of Use:</strong> 9/10</li>
<li><strong>Value for Money:</strong> 7.5/10</li>
<li><strong>Support:</strong> 8/10</li>
</ul>

<h2>Frequently Asked Questions</h2>
<h3>Question 1?</h3>
<p>Answer...</p>

<h2>Final Thoughts</h2>
<p>Recommendation and call to action...</p>',
                'heading_structure' => [
                    'h1' => 1,
                    'h2' => '6-9',
                    'h3' => '8-15',
                    'h4' => '0-5'
                ],
                'content_elements' => [
                    'tables' => true,
                    'lists' => true,
                    'rating_system' => true,
                    'pros_cons_table' => true,
                    'comparison_table' => true,
                    'faq' => true,
                    'toc' => true
                ]
            ],
            'product' => [
                'name' => 'Product Description',
                'description' => 'E-commerce product template',
                'structure' => [
                    'headline' => true,
                    'brief_description' => true,
                    'key_features' => true,
                    'detailed_features' => true,
                    'specifications' => true,
                    'use_cases' => true,
                    'comparison' => true
                ],
                'seo_prompt_additions' => '

IMPORTANT - SEO STRUCTURE REQUIREMENTS FOR PRODUCT:
1. H1: "[Product Name] - [Main Benefit/USP]"
2. Brief compelling description (2-3 sentences)
3. "Key Features" section with H2 and bullet points
4. "What\'s Included" section with H2 and checklist
5. "Detailed Features" section with H2 and subsections (H3 for each feature)
6. "Technical Specifications" with H2 and specification table
7. "Use Cases" section with H2 and real-world examples
8. "What Makes It Different" with H2 and comparison table
9. "Who Should Buy This" with H2
10. "Frequently Asked Questions" with H2
11. Use bullet points extensively
12. Include tables for specifications and comparisons
13. Bold important features and benefits
14. Add trust elements (warranty, guarantee, certifications)

EXAMPLE STRUCTURE:
<h1>[Product Name] - [Main Benefit Statement]</h1>
<p><strong>Premium [category]</strong> that delivers [main benefit]. Perfect for [target audience].</p>

<h2>Key Features at a Glance</h2>
<ul>
<li>✅ <strong>Feature 1:</strong> Benefit</li>
<li>✅ <strong>Feature 2:</strong> Benefit</li>
<li>✅ <strong>Feature 3:</strong> Benefit</li>
<li>✅ <strong>Feature 4:</strong> Benefit</li>
</ul>

<h2>What\'s Included</h2>
<ul>
<li>Main product unit</li>
<li>Accessory 1</li>
<li>Accessory 2</li>
<li>User manual and quick start guide</li>
<li>1-year warranty</li>
</ul>

<h2>Detailed Features</h2>

<h3>Feature 1: [Feature Name]</h3>
<p><strong>What it does:</strong> Description</p>
<p><strong>Why it matters:</strong> Benefit explanation</p>

<h3>Feature 2: [Feature Name]</h3>
<p><strong>What it does:</strong> Description</p>
<p><strong>Why it matters:</strong> Benefit explanation</p>

<h2>Technical Specifications</h2>
<table>
<tbody>
<tr><th>Dimensions</th><td>XX x YY x ZZ cm</td></tr>
<tr><th>Weight</th><td>XX kg</td></tr>
<tr><th>Material</th><td>[Material]</td></tr>
<tr><th>Color Options</th><td>Option 1, Option 2, Option 3</td></tr>
<tr><th>Power</th><td>XX watts</td></tr>
<tr><th>Warranty</th><td>X years</td></tr>
</tbody>
</table>

<h2>Real-World Use Cases</h2>

<h3>Use Case 1: [Scenario]</h3>
<p>Description of how the product solves this specific problem...</p>

<h3>Use Case 2: [Scenario]</h3>
<p>Description...</p>

<h2>What Makes It Different</h2>
<table>
<thead>
<tr><th>Feature</th><th>Our Product</th><th>Competitor A</th><th>Competitor B</th></tr>
</thead>
<tbody>
<tr><td>Feature 1</td><td>✅ Yes</td><td>❌ No</td><td>✅ Yes</td></tr>
<tr><td>Feature 2</td><td>✅ Premium</td><td>⚠️ Basic</td><td>❌ No</td></tr>
<tr><td>Price</td><td>$XXX</td><td>$YYY</td><td>$ZZZ</td></tr>
<tr><td>Warranty</td><td>3 years</td><td>1 year</td><td>1 year</td></tr>
</tbody>
</table>

<h2>Who Should Buy This</h2>
<p><strong>Perfect for:</strong></p>
<ul>
<li>Customer type 1</li>
<li>Customer type 2</li>
<li>Customer type 3</li>
</ul>

<p><strong>Not recommended for:</strong></p>
<ul>
<li>Customer type who needs different features</li>
</ul>

<h2>Quality Guarantee</h2>
<ul>
<li>✅ <strong>X-Year Warranty:</strong> Full coverage</li>
<li>✅ <strong>30-Day Money Back:</strong> Risk-free trial</li>
<li>✅ <strong>Certified Quality:</strong> [Certification names]</li>
<li>✅ <strong>Customer Support:</strong> 24/7 assistance</li>
</ul>

<h2>Frequently Asked Questions</h2>

<h3>Is this product suitable for [use case]?</h3>
<p>Answer...</p>

<h3>What\'s the warranty coverage?</h3>
<p>Answer...</p>

<h3>How long does shipping take?</h3>
<p>Answer...</p>

<h2>Ready to Get Started?</h2>
<p>Order now and get [special offer]. Free shipping on orders over $XX.</p>',
                'heading_structure' => [
                    'h1' => 1,
                    'h2' => '7-10',
                    'h3' => '6-12',
                    'h4' => '0-3'
                ],
                'content_elements' => [
                    'tables' => true,
                    'lists' => true,
                    'specifications_table' => true,
                    'comparison_table' => true,
                    'checkmarks' => true,
                    'faq' => true,
                    'trust_elements' => true
                ]
            ],
            'blog_post' => [
                'name' => 'Blog Post',
                'description' => 'Standard blog post template',
                'structure' => [
                    'introduction' => true,
                    'main_content' => true,
                    'conclusion' => true,
                    'cta' => true
                ],
                'seo_prompt_additions' => '

IMPORTANT - SEO STRUCTURE REQUIREMENTS FOR BLOG POST:
1. H1 title (engaging and keyword-rich)
2. Introduction paragraph (2-3 sentences with hook)
3. 3-4 main sections with H2 headings
4. Each H2 section should have 2-3 H3 subsections
5. Include at least one table or comparison
6. Use bullet points for lists
7. Add a conclusion with H2
8. Bold important terms and key points
9. Include internal linking opportunities
10. Maintain conversational yet professional tone

EXAMPLE STRUCTURE:
<h1>Engaging Blog Post Title with Target Keyword</h1>
<p>Hook paragraph that captures attention and introduces the topic...</p>

<h2>First Main Section</h2>
<p>Content...</p>

<h3>Subsection 1.1</h3>
<p>Detailed content with <strong>important points</strong> highlighted...</p>

<h3>Subsection 1.2</h3>
<p>More content...</p>

<h2>Second Main Section</h2>
<p>Content...</p>

<h2>Conclusion</h2>
<p>Summary and call to action...</p>',
                'heading_structure' => [
                    'h1' => 1,
                    'h2' => '3-5',
                    'h3' => '6-12',
                    'h4' => '0-5'
                ],
                'content_elements' => [
                    'tables' => true,
                    'lists' => true,
                    'bold_text' => true,
                    'internal_links' => true
                ]
            ]
        ];
    }

    /**
     * Get SEO template for content type
     *
     * @param string $content_type Content type
     * @return array|null Template data or null if not found
     */
    public function get_template($content_type) {
        // Map existing content types to SEO templates
        $type_mapping = [
            'blog_post' => 'article',
            'article' => 'article',
            'guide' => 'guide',
            'how_to' => 'guide',
            'review' => 'review',
            'product_description' => 'product',
            'product' => 'product'
        ];

        $template_type = $type_mapping[$content_type] ?? 'blog_post';

        return $this->seo_templates[$template_type] ?? null;
    }

    /**
     * Apply SEO template to prompt
     *
     * @param string $original_prompt Original prompt
     * @param string $content_type Content type
     * @return string Enhanced prompt with SEO instructions
     */
    public function enhance_prompt_with_seo($original_prompt, $content_type) {
        $template = $this->get_template($content_type);

        if (!$template) {
            return $original_prompt;
        }

        // Add SEO structure requirements to the prompt
        return $original_prompt . $template['seo_prompt_additions'];
    }

    /**
     * Validate content structure against SEO template
     *
     * @param string $content Generated content
     * @param string $content_type Content type
     * @return array Validation result with score and suggestions
     */
    public function validate_seo_structure($content, $content_type) {
        $template = $this->get_template($content_type);

        if (!$template) {
            return [
                'score' => 50,
                'passed' => false,
                'issues' => ['Template not found for content type']
            ];
        }

        $issues = [];
        $score = 100;
        $required_structure = $template['heading_structure'];

        // Check H1 count
        $h1_count = preg_match_all('/<h1[^>]*>/', $content);
        if ($h1_count !== $required_structure['h1']) {
            $issues[] = "Should have exactly {$required_structure['h1']} H1 heading, found {$h1_count}";
            $score -= 20;
        }

        // Check H2 count
        $h2_count = preg_match_all('/<h2[^>]*>/', $content);
        $h2_expected = $required_structure['h2'];
        if (strpos($h2_expected, '-') !== false) {
            list($min, $max) = explode('-', $h2_expected);
            if ($h2_count < $min || $h2_count > $max) {
                $issues[] = "Should have {$h2_expected} H2 headings, found {$h2_count}";
                $score -= 15;
            }
        }

        // Check for tables if required
        if ($template['content_elements']['tables']) {
            $table_count = preg_match_all('/<table[^>]*>/', $content);
            if ($table_count === 0) {
                $issues[] = "Should include at least one table";
                $score -= 10;
            }
        }

        // Check for lists if required
        if ($template['content_elements']['lists']) {
            $list_count = preg_match_all('/<[uo]l[^>]*>/', $content);
            if ($list_count === 0) {
                $issues[] = "Should include bullet or numbered lists";
                $score -= 10;
            }
        }

        // Check for FAQ if required
        if (isset($template['content_elements']['faq']) && $template['content_elements']['faq']) {
            if (stripos($content, 'frequently asked questions') === false && stripos($content, 'faq') === false) {
                $issues[] = "Should include FAQ section";
                $score -= 10;
            }
        }

        // Check for Table of Contents if required
        if (isset($template['content_elements']['toc']) && $template['content_elements']['toc']) {
            if (stripos($content, 'table of contents') === false) {
                $issues[] = "Should include Table of Contents";
                $score -= 10;
            }
        }

        return [
            'score' => max(0, $score),
            'passed' => $score >= 70,
            'issues' => $issues,
            'template_used' => $template['name']
        ];
    }

    /**
     * Get all available templates
     *
     * @return array All SEO templates
     */
    public function get_all_templates() {
        return $this->seo_templates;
    }

    /**
     * Add custom SEO template
     *
     * @param string $template_name Template name
     * @param array $template_data Template data
     * @return bool Success status
     */
    public function add_custom_template($template_name, $template_data) {
        if (isset($this->seo_templates[$template_name])) {
            return false; // Template already exists
        }

        // Validate template structure
        $required_keys = ['name', 'description', 'structure', 'seo_prompt_additions', 'heading_structure', 'content_elements'];
        foreach ($required_keys as $key) {
            if (!isset($template_data[$key])) {
                return false; // Invalid template data
            }
        }

        $this->seo_templates[$template_name] = $template_data;
        return true;
    }

    /**
     * Format content with proper WordPress blocks (Gutenberg)
     *
     * @param string $content HTML content
     * @return string Content with WordPress blocks
     */
    public function format_as_gutenberg_blocks($content) {
        // This wraps HTML content in proper Gutenberg blocks
        // For now, we'll return the HTML as-is since WordPress handles HTML well
        // In future, could convert to actual Gutenberg block format
        return $content;
    }
}
