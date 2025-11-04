<?php
/**
 * Content Quality Analyzer
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\ContentQuality
 */

namespace AI_Manager_Pro\Modules\ContentQuality;

use Monolog\Logger;

/**
 * Content Quality Analyzer Class
 * 
 * Analyzes and scores content quality across multiple dimensions
 */
class Content_Quality_Analyzer
{
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        // Add to AJAX handlers for quality analysis
        add_action('wp_ajax_ai_manager_pro_analyze_quality', [$this, 'handle_quality_analysis']);
        add_action('wp_ajax_ai_manager_pro_get_quality_recommendations', [$this, 'handle_quality_recommendations']);
        add_action('wp_ajax_ai_manager_pro_improve_content', [$this, 'handle_content_improvement']);
    }

    /**
     * Analyze content quality across multiple dimensions
     *
     * @param string $content Content to analyze
     * @param array $options Analysis options
     * @return array Quality analysis results
     */
    public function analyze_content($content, $options = [])
    {
        $default_options = [
            'check_readability' => true,
            'check_seo' => true,
            'check_structure' => true,
            'check_keywords' => true,
            'check_length' => true,
            'check_engagement' => true,
            'check_originality' => false // Requires external API
        ];

        $options = array_merge($default_options, $options);

        $analysis = [];

        // 1. Content Structure Analysis
        if ($options['check_structure']) {
            $analysis['structure'] = $this->analyze_structure($content);
        }

        // 2. Readability Analysis
        if ($options['check_readability']) {
            $analysis['readability'] = $this->analyze_readability($content);
        }

        // 3. SEO Analysis
        if ($options['check_seo']) {
            $analysis['seo'] = $this->analyze_seo($content, $options);
        }

        // 4. Length Analysis
        if ($options['check_length']) {
            $analysis['length'] = $this->analyze_length($content);
        }

        // 5. Keyword Analysis
        if ($options['check_keywords']) {
            $analysis['keywords'] = $this->analyze_keywords($content, $options);
        }

        // 6. Engagement Analysis
        if ($options['check_engagement']) {
            $analysis['engagement'] = $this->analyze_engagement($content);
        }

        // Calculate overall score
        $overall_score = $this->calculate_overall_score($analysis);

        return [
            'overall_score' => $overall_score,
            'analysis' => $analysis,
            'grade' => $this->get_quality_grade($overall_score),
            'recommendations' => $this->generate_recommendations($analysis),
            'timestamp' => current_time('mysql')
        ];
    }

    /**
     * Analyze content structure
     *
     * @param string $content Content to analyze
     * @return array Structure analysis
     */
    private function analyze_structure($content)
    {
        $analysis = [
            'score' => 0,
            'has_title' => false,
            'has_subheadings' => false,
            'has_paragraphs' => false,
            'has_lists' => false,
            'has_images' => false,
            'word_count' => 0,
            'paragraph_count' => 0,
            'heading_count' => 0,
            'issues' => []
        ];

        // Check for title (H1)
        if (preg_match('/^#\s+.+/m', $content) || preg_match('/<h1[^>]*>.+<\/h1>/', $content)) {
            $analysis['has_title'] = true;
            $analysis['score'] += 15;
        } else {
            $analysis['issues'][] = 'Missing H1 title';
        }

        // Check for subheadings (H2, H3)
        $heading_count = preg_match_all('/^#{2,3}\s+.+/m', $content) +
            preg_match_all('/<h[2-3][^>]*>.+<\/h[2-3]>/', $content);
        if ($heading_count >= 2) {
            $analysis['has_subheadings'] = true;
            $analysis['heading_count'] = $heading_count;
            $analysis['score'] += 20;
        } elseif ($heading_count == 1) {
            $analysis['heading_count'] = 1;
            $analysis['score'] += 10;
            $analysis['issues'][] = 'Consider adding more subheadings';
        } else {
            $analysis['issues'][] = 'No subheadings found';
        }

        // Check for paragraphs
        $paragraph_count = preg_match_all('/^\s*[^\s\n]+\s*\n/m', $content) +
            preg_match_all('/<p[^>]*>.+<\/p>/', $content);
        $analysis['paragraph_count'] = $paragraph_count;
        if ($paragraph_count >= 3) {
            $analysis['has_paragraphs'] = true;
            $analysis['score'] += 15;
        } else {
            $analysis['issues'][] = 'Content may be too short or poorly structured';
        }

        // Check for lists
        $list_count = preg_match_all('/^\s*[*-]\s+.+/m', $content) +
            preg_match_all('/<ul[^>]*>.*<\/ul>/s', $content) +
            preg_match_all('/<ol[^>]*>.*<\/ol>/s', $content);
        if ($list_count > 0) {
            $analysis['has_lists'] = true;
            $analysis['score'] += 10;
        }

        // Word count analysis
        $word_count = str_word_count(strip_tags($content));
        $analysis['word_count'] = $word_count;

        if ($word_count >= 500 && $word_count <= 2000) {
            $analysis['score'] += 15;
        } elseif ($word_count >= 300) {
            $analysis['score'] += 10;
        } else {
            $analysis['issues'][] = 'Content may be too short for a quality article';
        }

        // Check for images
        if (preg_match('/<img[^>]+>/', $content)) {
            $analysis['has_images'] = true;
            $analysis['score'] += 10;
        }

        return $analysis;
    }

    /**
     * Analyze readability
     *
     * @param string $content Content to analyze
     * @return array Readability analysis
     */
    private function analyze_readability($content)
    {
        $analysis = [
            'score' => 0,
            'flesch_score' => 0,
            'grade_level' => 0,
            'avg_sentence_length' => 0,
            'avg_word_length' => 0,
            'complex_words' => 0,
            'simple_score' => 0,
            'issues' => []
        ];

        $text = strip_tags($content);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($sentences) || empty($words)) {
            $analysis['issues'][] = 'Unable to analyze readability';
            return $analysis;
        }

        // Calculate averages
        $sentence_count = count($sentences);
        $word_count = count($words);
        $syllable_count = $this->count_syllables($text);

        $analysis['avg_sentence_length'] = round($word_count / $sentence_count, 1);
        $analysis['avg_word_length'] = round(strlen($text) / $word_count, 1);
        $analysis['complex_words'] = $this->count_complex_words($words);

        // Flesch Reading Ease Score
        if ($sentence_count > 0 && $word_count > 0 && $syllable_count > 0) {
            $flesch = 206.835 - (1.015 * ($word_count / $sentence_count)) - (84.6 * ($syllable_count / $word_count));
            $analysis['flesch_score'] = round($flesch, 1);

            // Convert to grade level
            $analysis['grade_level'] = round((0.39 * ($word_count / $sentence_count)) + (11.8 * ($syllable_count / $word_count)) - 15.59, 1);

            // Score based on readability
            if ($flesch >= 60) {
                $analysis['score'] = 40; // Easy to read
            } elseif ($flesch >= 30) {
                $analysis['score'] = 30; // Moderately easy
            } elseif ($flesch >= 0) {
                $analysis['score'] = 20; // Difficult
            } else {
                $analysis['score'] = 10; // Very difficult
            }
        }

        // Additional readability checks
        if ($analysis['avg_sentence_length'] > 25) {
            $analysis['issues'][] = 'Sentences are too long on average';
            $analysis['score'] -= 5;
        }

        if ($analysis['complex_words'] > ($word_count * 0.3)) {
            $analysis['issues'][] = 'Too many complex words';
            $analysis['score'] -= 5;
        }

        return $analysis;
    }

    /**
     * Analyze SEO factors
     *
     * @param string $content Content to analyze
     * @param array $options Analysis options
     * @return array SEO analysis
     */
    private function analyze_seo($content, $options)
    {
        $analysis = [
            'score' => 0,
            'keyword_density' => 0,
            'title_length' => 0,
            'meta_description_length' => 0,
            'header_structure' => 0,
            'internal_links' => 0,
            'external_links' => 0,
            'issues' => []
        ];

        $text = strip_tags($content);
        $word_count = str_word_count($text);

        // Title analysis
        if (preg_match('/^#\s+(.+)/m', $content, $matches)) {
            $title = trim($matches[1]);
            $analysis['title_length'] = strlen($title);

            if ($analysis['title_length'] >= 50 && $analysis['title_length'] <= 60) {
                $analysis['score'] += 20;
            } elseif ($analysis['title_length'] >= 30 && $analysis['title_length'] <= 70) {
                $analysis['score'] += 15;
                if ($analysis['title_length'] > 60) {
                    $analysis['issues'][] = 'Title may be too long for search results';
                }
            } else {
                $analysis['issues'][] = 'Title length is not optimal for SEO';
            }
        } else {
            $analysis['issues'][] = 'No title found';
        }

        // Keyword analysis
        if (!empty($options['target_keyword'])) {
            $keyword = strtolower($options['target_keyword']);
            $keyword_count = substr_count(strtolower($text), $keyword);
            $density = ($keyword_count / $word_count) * 100;
            $analysis['keyword_density'] = round($density, 2);

            if ($density >= 1 && $density <= 2.5) {
                $analysis['score'] += 20;
            } elseif ($density >= 0.5 && $density <= 3) {
                $analysis['score'] += 15;
            } else {
                $analysis['issues'][] = 'Keyword density is not optimal';
            }
        }

        // Header structure
        $h2_count = preg_match_all('/^##\s+/m', $content);
        $h3_count = preg_match_all('/^###\s+/m', $content);
        $analysis['header_structure'] = $h2_count + $h3_count;

        if ($analysis['header_structure'] >= 2) {
            $analysis['score'] += 15;
        } else {
            $analysis['issues'][] = 'Content should have more subheadings';
        }

        // Link analysis
        $internal_links = preg_match_all('/<a[^>]+href=["\']\/(?!#|\w+:)[^"\']+["\']/', $content);
        $external_links = preg_match_all('/<a[^>]+href=["\']https?:\/\/[^"\']+["\']/', $content);

        $analysis['internal_links'] = $internal_links;
        $analysis['external_links'] = $external_links;

        if ($internal_links > 0) {
            $analysis['score'] += 10;
        } else {
            $analysis['issues'][] = 'Consider adding internal links';
        }

        if ($external_links > 0) {
            $analysis['score'] += 5;
        }

        return $analysis;
    }

    /**
     * Analyze content length
     *
     * @param string $content Content to analyze
     * @return array Length analysis
     */
    private function analyze_length($content)
    {
        $analysis = [
            'score' => 0,
            'word_count' => 0,
            'character_count' => 0,
            'paragraph_count' => 0,
            'ideal_range' => false,
            'issues' => []
        ];

        $text = strip_tags($content);
        $words = str_word_count($text);
        $characters = strlen($text);
        $paragraphs = preg_match_all('/\n\s*\n/', $content);

        $analysis['word_count'] = $words;
        $analysis['character_count'] = $characters;
        $analysis['paragraph_count'] = $paragraphs;

        // Word count scoring
        if ($words >= 800 && $words <= 1500) {
            $analysis['score'] = 40;
            $analysis['ideal_range'] = true;
        } elseif ($words >= 600) {
            $analysis['score'] = 30;
        } elseif ($words >= 400) {
            $analysis['score'] = 20;
        } else {
            $analysis['score'] = 10;
            $analysis['issues'][] = 'Content is too short for comprehensive coverage';
        }

        // Paragraph analysis
        if ($paragraphs > 0) {
            $avg_words_per_paragraph = $words / $paragraphs;
            if ($avg_words_per_paragraph <= 150) {
                $analysis['score'] += 10;
            } else {
                $analysis['score'] -= 5;
                $analysis['issues'][] = 'Paragraphs are too long';
            }
        }

        return $analysis;
    }

    /**
     * Analyze keyword usage
     *
     * @param string $content Content to analyze
     * @param array $options Analysis options
     * @return array Keyword analysis
     */
    private function analyze_keywords($content, $options)
    {
        $analysis = [
            'score' => 0,
            'primary_keywords' => [],
            'secondary_keywords' => [],
            'keyword_distribution' => 0,
            'long_tail_keywords' => 0,
            'issues' => []
        ];

        $text = strtolower(strip_tags($content));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Count word frequency
        $word_freq = array_count_values($words);
        arsort($word_freq);

        // Remove common words
        $stop_words = ['ו', 'ה', 'ב', 'ל', 'של', 'עם', 'את', 'או', 'כמו', 'על', 'מה', 'איך', 'מתי', 'איפה', 'למה'];
        foreach ($stop_words as $stop_word) {
            unset($word_freq[$stop_word]);
        }

        // Get top keywords
        $top_keywords = array_slice($word_freq, 0, 10, true);
        $analysis['primary_keywords'] = $top_keywords;

        // Check for keyword distribution
        $first_paragraph = substr(strip_tags($content), 0, 200);
        $last_paragraph = substr(strip_tags($content), -200);

        $first_100_words = implode(' ', array_slice($words, 0, 100));
        $last_100_words = implode(' ', array_slice($words, -100));

        $keyword_distribution_score = 0;
        foreach ($top_keywords as $keyword => $count) {
            if ($count > 1) {
                if (strpos($first_100_words, $keyword) !== false) {
                    $keyword_distribution_score += 5;
                }
                if (strpos($last_100_words, $keyword) !== false) {
                    $keyword_distribution_score += 5;
                }
            }
        }

        $analysis['keyword_distribution'] = min($keyword_distribution_score, 20);
        $analysis['score'] += $analysis['keyword_distribution'];

        return $analysis;
    }

    /**
     * Analyze engagement potential
     *
     * @param string $content Content to analyze
     * @return array Engagement analysis
     */
    private function analyze_engagement($content)
    {
        $analysis = [
            'score' => 0,
            'hook_strength' => 0,
            'question_count' => 0,
            'call_to_action' => false,
            'emotional_words' => 0,
            'story_elements' => 0,
            'issues' => []
        ];

        // Check for opening hook
        $text = strip_tags($content);
        $first_sentence = preg_match('/^([^.!?]+[.!?])/', $text, $matches) ? $matches[1] : '';

        if (strlen($first_sentence) > 50 && strlen($first_sentence) < 150) {
            $analysis['hook_strength'] = 20;
        } elseif (strlen($first_sentence) > 30) {
            $analysis['hook_strength'] = 10;
            $analysis['issues'][] = 'Opening sentence could be more engaging';
        } else {
            $analysis['hook_strength'] = 5;
            $analysis['issues'][] = 'Opening sentence is too short';
        }

        $analysis['score'] += $analysis['hook_strength'];

        // Check for questions (engagement boosters)
        $question_count = preg_match_all('/\?/', $content);
        $analysis['question_count'] = $question_count;

        if ($question_count > 0) {
            $analysis['score'] += min($question_count * 3, 15);
        } else {
            $analysis['issues'][] = 'Consider adding questions to increase engagement';
        }

        // Check for call-to-action
        $cta_patterns = ['קרא עוד', 'למד עוד', 'צפה עכשיו', 'נסה עכשיו', 'התחל עכשיו', 'פעל עכשיו'];
        foreach ($cta_patterns as $cta) {
            if (stripos($content, $cta) !== false) {
                $analysis['call_to_action'] = true;
                $analysis['score'] += 15;
                break;
            }
        }

        // Emotional words
        $emotional_words = ['מדהים', 'פנטסטי', 'חשוב', 'מרתק', 'יוצא דופן', 'בלתי נשכח', 'מרגש', 'מעצים'];
        foreach ($emotional_words as $emotion) {
            if (stripos($content, $emotion) !== false) {
                $analysis['emotional_words']++;
            }
        }

        if ($analysis['emotional_words'] > 0) {
            $analysis['score'] += min($analysis['emotional_words'] * 2, 10);
        }

        return $analysis;
    }

    /**
     * Calculate overall quality score
     *
     * @param array $analysis Individual analysis results
     * @return int Overall score (0-100)
     */
    private function calculate_overall_score($analysis)
    {
        $scores = [];
        $weights = [
            'structure' => 0.25,
            'readability' => 0.20,
            'seo' => 0.20,
            'length' => 0.15,
            'keywords' => 0.10,
            'engagement' => 0.10
        ];

        foreach ($weights as $dimension => $weight) {
            if (isset($analysis[$dimension])) {
                $scores[] = $analysis[$dimension]['score'] * $weight;
            }
        }

        return min(array_sum($scores), 100);
    }

    /**
     * Get quality grade based on score
     *
     * @param int $score Quality score
     * @return string Quality grade
     */
    private function get_quality_grade($score)
    {
        if ($score >= 90)
            return 'A+';
        if ($score >= 80)
            return 'A';
        if ($score >= 70)
            return 'B+';
        if ($score >= 60)
            return 'B';
        if ($score >= 50)
            return 'C+';
        if ($score >= 40)
            return 'C';
        return 'F';
    }

    /**
     * Generate quality improvement recommendations
     *
     * @param array $analysis Quality analysis
     * @return array Recommendations
     */
    private function generate_recommendations($analysis)
    {
        $recommendations = [];

        foreach ($analysis as $dimension => $data) {
            if (isset($data['issues'])) {
                foreach ($data['issues'] as $issue) {
                    $recommendations[] = [
                        'type' => $dimension,
                        'priority' => $this->get_priority($dimension, $issue),
                        'issue' => $issue,
                        'suggestion' => $this->get_suggestion($dimension, $issue)
                    ];
                }
            }
        }

        // Sort by priority
        usort($recommendations, function ($a, $b) {
            $priority_order = ['high' => 3, 'medium' => 2, 'low' => 1];
            return $priority_order[$b['priority']] - $priority_order[$a['priority']];
        });

        return array_slice($recommendations, 0, 10); // Return top 10 recommendations
    }

    /**
     * Count syllables in Hebrew/English text
     *
     * @param string $text Text to analyze
     * @return int Syllable count
     */
    private function count_syllables($text)
    {
        // Basic syllable counting for English
        $text = strtolower($text);
        $vowels = 'aeiouy';
        $syllables = 0;
        $previous_was_vowel = false;

        for ($i = 0; $i < strlen($text); $i++) {
            $is_vowel = strpos($vowels, $text[$i]) !== false;
            if ($is_vowel && !$previous_was_vowel) {
                $syllables++;
            }
            $previous_was_vowel = $is_vowel;
        }

        return max($syllables, 1);
    }

    /**
     * Count complex words (3+ syllables)
     *
     * @param array $words Words array
     * @return int Complex word count
     */
    private function count_complex_words($words)
    {
        $complex = 0;
        foreach ($words as $word) {
            if (strlen($word) > 6 && $this->count_syllables($word) >= 3) {
                $complex++;
            }
        }
        return $complex;
    }

    /**
     * Get recommendation priority
     *
     * @param string $dimension Analysis dimension
     * @param string $issue Issue description
     * @return string Priority level
     */
    private function get_priority($dimension, $issue)
    {
        $high_priority_issues = ['Missing', 'No', 'too short', 'too long'];
        $medium_priority_issues = ['Consider', 'Should', 'may be'];

        foreach ($high_priority_issues as $keyword) {
            if (stripos($issue, $keyword) !== false) {
                return 'high';
            }
        }

        foreach ($medium_priority_issues as $keyword) {
            if (stripos($issue, $keyword) !== false) {
                return 'medium';
            }
        }

        return 'low';
    }

    /**
     * Get improvement suggestion for an issue
     *
     * @param string $dimension Analysis dimension
     * @param string $issue Issue description
     * @return string Suggestion
     */
    private function get_suggestion($dimension, $issue)
    {
        $suggestions = [
            'structure' => [
                'Missing H1 title' => 'Add a clear, descriptive H1 title at the beginning of your content',
                'No subheadings' => 'Break your content into sections with H2 and H3 subheadings',
                'Content may be too short' => 'Expand your content to provide more comprehensive coverage'
            ],
            'readability' => [
                'Sentences are too long' => 'Break long sentences into shorter ones for better readability',
                'Too many complex words' => 'Use simpler words to make your content more accessible'
            ],
            'seo' => [
                'Title length is not optimal' => 'Keep your title between 50-60 characters for best search results',
                'Keyword density is not optimal' => 'Aim for 1-2.5% keyword density for better SEO',
                'Consider adding internal links' => 'Add links to related content on your website'
            ]
        ];

        if (isset($suggestions[$dimension]) && isset($suggestions[$dimension][$issue])) {
            return $suggestions[$dimension][$issue];
        }

        return "Consider improving this aspect of your content";
    }

    /**
     * AJAX handler for quality analysis
     */
    public function handle_quality_analysis()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_send_json_error('Security check failed');
        }

        // Check permissions
        if (!current_user_can('ai_manager_generate_content')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $content = wp_kses_post($_POST['content'] ?? '');
            $options = $_POST['options'] ?? [];

            if (empty($content)) {
                wp_send_json_error('Content is required');
            }

            $analysis = $this->analyze_content($content, $options);

            wp_send_json_success($analysis);

        } catch (\Exception $e) {
            wp_send_json_error('Quality analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for quality recommendations
     */
    public function handle_quality_recommendations()
    {
        // Implementation similar to quality analysis
        wp_send_json_success(['recommendations' => []]);
    }

    /**
     * AJAX handler for content improvement
     */
    public function handle_content_improvement()
    {
        // Implementation for AI-powered content improvement
        wp_send_json_success(['improved_content' => '']);
    }
}
