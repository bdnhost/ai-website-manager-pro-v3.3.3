<?php
/**
 * AI Enhanced Editor
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\ContentEditor
 */

namespace AI_Manager_Pro\Modules\ContentEditor;

use AI_Manager_Pro\Modules\ContentQuality\Content_Quality_Analyzer;
use AI_Manager_Pro\AI\AI_Service;
use Monolog\Logger;

/**
 * AI Enhanced Editor Class
 * 
 * Provides real-time AI-powered editing capabilities
 */
class AI_Enhanced_Editor
{
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Content Quality Analyzer
     *
     * @var Content_Quality_Analyzer
     */
    private $quality_analyzer;
    
    /**
     * AI Service
     *
     * @var AI_Service
     */
    private $ai_service;
    
    /**
     * Editor settings
     *
     * @var array
     */
    private $settings = [
        'auto_analyze' => true,
        'real_time_feedback' => true,
        'ai_suggestions' => true,
        'quality_threshold' => 70
    ];
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     * @param Content_Quality_Analyzer $quality_analyzer Quality analyzer
     * @param AI_Service $ai_service AI service
     */
    public function __construct(Logger $logger, Content_Quality_Analyzer $quality_analyzer, AI_Service $ai_service)
    {
        $this->logger = $logger;
        $this->quality_analyzer = $quality_analyzer;
        $this->ai_service = $ai_service;
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        add_action('wp_ajax_ai_manager_pro_get_ai_suggestions', [$this, 'handle_ai_suggestions']);
        add_action('wp_ajax_ai_manager_pro_improve_text', [$this, 'handle_text_improvement']);
        add_action('wp_ajax_ai_manager_pro_analyze_structure', [$this, 'handle_structure_analysis']);
        add_action('wp_ajax_ai_manager_pro_generate_headings', [$this, 'handle_heading_generation']);
        add_action('wp_ajax_ai_manager_pro_suggest_keywords', [$this, 'handle_keyword_suggestions']);
        add_action('wp_ajax_ai_manager_pro_get_readability_improvements', [$this, 'handle_readability_improvements']);
    }
    
    /**
     * Render AI enhanced editor
     *
     * @param array $args Editor arguments
     * @return string Editor HTML
     */
    public function render_editor($args = [])
    {
        $defaults = [
            'id' => 'ai-enhanced-editor',
            'name' => 'ai_content',
            'content' => '',
            'placeholder' => 'התחל לכתוב כאן...',
            'rows' => 20,
            'show_quality_panel' => true,
            'show_suggestions_panel' => true,
            'show_structure_panel' => true,
            'auto_analyze' => true
        ];
        
        $args = array_merge($defaults, $args);
        
        ob_start();
        ?>
        <div class="ai-enhanced-editor-container" data-editor-id="<?php echo esc_attr($args['id']); ?>">
            
            <!-- Editor Header -->
            <div class="ai-editor-header">
                <div class="ai-editor-title">
                    <span class="ai-editor-icon">🤖</span>
                    <span>עורך AI מתקדם</span>
                </div>
                
                <!-- Quick Actions -->
                <div class="ai-editor-actions">
                    <button type="button" class="ai-btn ai-btn-primary" onclick="aiEditor.analyzeContent('<?php echo esc_attr($args['id']); ?>')">
                        <span>🔍</span> נתח איכות
                    </button>
                    <button type="button" class="ai-btn ai-btn-secondary" onclick="aiEditor.getSuggestions('<?php echo esc_attr($args['id']); ?>')">
                        <span>💡</span> הצעות AI
                    </button>
                    <button type="button" class="ai-btn ai-btn-secondary" onclick="aiEditor.improveStructure('<?php echo esc_attr($args['id']); ?>')">
                        <span>📋</span> שפר מבנה
                    </button>
                </div>
                
                <!-- Quality Indicator -->
                <div class="ai-quality-indicator" id="quality-<?php echo esc_attr($args['id']); ?>">
                    <div class="quality-score">
                        <span class="score-label">ציון:</span>
                        <span class="score-value">--</span>
                        <span class="score-grade">--</span>
                    </div>
                </div>
            </div>
            
            <!-- Main Editor Grid -->
            <div class="ai-editor-grid">
                
                <!-- Content Editor -->
                <div class="ai-editor-main">
                    <div class="ai-editor-wrapper">
                        <textarea 
                            id="<?php echo esc_attr($args['id']); ?>" 
                            name="<?php echo esc_attr($args['name']); ?>" 
                            class="ai-content-editor"
                            rows="<?php echo esc_attr($args['rows']); ?>"
                            placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                            data-auto-analyze="<?php echo $args['auto_analyze'] ? 'true' : 'false'; ?>"
                        ><?php echo esc_textarea($args['content']); ?></textarea>
                        
                        <!-- Real-time Feedback Overlay -->
                        <div class="ai-feedback-overlay" id="feedback-<?php echo esc_attr($args['id']); ?>">
                            <div class="feedback-content"></div>
                        </div>
                    </div>
                    
                    <!-- Editor Footer -->
                    <div class="ai-editor-footer">
                        <div class="ai-stats">
                            <span class="stat-item">
                                <span class="stat-icon">📝</span>
                                <span class="stat-value" id="word-count-<?php echo esc_attr($args['id']); ?>">0</span>
                                <span class="stat-label">מילים</span>
                            </span>
                            <span class="stat-item">
                                <span class="stat-icon">📊</span>
                                <span class="stat-value" id="paragraph-count-<?php echo esc_attr($args['id']); ?>">0</span>
                                <span class="stat-label">פסקאות</span>
                            </span>
                            <span class="stat-item">
                                <span class="stat-icon">🎯</span>
                                <span class="stat-value" id="readability-<?php echo esc_attr($args['id']); ?>">--</span>
                                <span class="stat-label">קריאות</span>
                            </span>
                        </div>
                        
                        <!-- Auto-save indicator -->
                        <div class="ai-autosave">
                            <span class="autosave-status" id="autosave-status-<?php echo esc_attr($args['id']); ?>">
                                שמור אוטומטי פעיל
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Side Panels -->
                <div class="ai-editor-sidebar">
                    
                    <?php if ($args['show_quality_panel']): ?>
                    <!-- Quality Analysis Panel -->
                    <div class="ai-side-panel" id="quality-panel-<?php echo esc_attr($args['id']); ?>">
                        <div class="panel-header">
                            <span class="panel-icon">📊</span>
                            <span class="panel-title">ניתוח איכות</span>
                            <button type="button" class="panel-toggle" onclick="togglePanel('quality-panel-<?php echo esc_attr($args['id']); ?>')">−</button>
                        </div>
                        <div class="panel-content">
                            <div class="quality-overview">
                                <div class="quality-score-circle">
                                    <canvas id="quality-chart-<?php echo esc_attr($args['id']); ?>" width="120" height="120"></canvas>
                                    <div class="score-text">
                                        <span class="score-number">--</span>
                                        <span class="score-letter">--</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quality-details">
                                <div class="quality-metric">
                                    <span class="metric-label">מבנה</span>
                                    <div class="metric-bar">
                                        <div class="metric-fill" data-metric="structure"></div>
                                    </div>
                                    <span class="metric-score">0</span>
                                </div>
                                
                                <div class="quality-metric">
                                    <span class="metric-label">קריאות</span>
                                    <div class="metric-bar">
                                        <div class="metric-fill" data-metric="readability"></div>
                                    </div>
                                    <span class="metric-score">0</span>
                                </div>
                                
                                <div class="quality-metric">
                                    <span class="metric-label">SEO</span>
                                    <div class="metric-bar">
                                        <div class="metric-fill" data-metric="seo"></div>
                                    </div>
                                    <span class="metric-score">0</span>
                                </div>
                                
                                <div class="quality-metric">
                                    <span class="metric-label">אורך</span>
                                    <div class="metric-bar">
                                        <div class="metric-fill" data-metric="length"></div>
                                    </div>
                                    <span class="metric-score">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_suggestions_panel']): ?>
                    <!-- AI Suggestions Panel -->
                    <div class="ai-side-panel" id="suggestions-panel-<?php echo esc_attr($args['id']); ?>">
                        <div class="panel-header">
                            <span class="panel-icon">💡</span>
                            <span class="panel-title">הצעות AI</span>
                            <button type="button" class="panel-toggle" onclick="togglePanel('suggestions-panel-<?php echo esc_attr($args['id']); ?>')">−</button>
                        </div>
                        <div class="panel-content">
                            <div class="suggestions-container" id="suggestions-container-<?php echo esc_attr($args['id']); ?>">
                                <div class="no-suggestions">
                                    <span class="suggestion-icon">🤖</span>
                                    <p>התחל לכתוב כדי לקבל הצעות AI</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_structure_panel']): ?>
                    <!-- Structure Analysis Panel -->
                    <div class="ai-side-panel" id="structure-panel-<?php echo esc_attr($args['id']); ?>">
                        <div class="panel-header">
                            <span class="panel-icon">📋</span>
                            <span class="panel-title">מבנה התוכן</span>
                            <button type="button" class="panel-toggle" onclick="togglePanel('structure-panel-<?php echo esc_attr($args['id']); ?>')">−</button>
                        </div>
                        <div class="panel-content">
                            <div class="structure-preview" id="structure-preview-<?php echo esc_attr($args['id']); ?>">
                                <div class="structure-placeholder">
                                    <span class="structure-icon">📄</span>
                                    <p>מבנה התוכן יופיע כאן</p>
                                </div>
                            </div>
                            
                            <div class="structure-actions">
                                <button type="button" class="ai-btn ai-btn-small" onclick="aiEditor.generateHeadings('<?php echo esc_attr($args['id']); ?>')">
                                    צור כותרות
                                </button>
                                <button type="button" class="ai-btn ai-btn-small" onclick="aiEditor.analyzeStructure('<?php echo esc_attr($args['id']); ?>')">
                                    נתח מבנה
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
        
        <!-- Styles -->
        <style>
        .ai-enhanced-editor-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .ai-editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .ai-editor-title {
            display: flex;
            align-items: center;
            font-size: 16px;
            font-weight: 600;
        }
        
        .ai-editor-title .ai-editor-icon {
            margin-left: 8px;
            font-size: 18px;
        }
        
        .ai-editor-actions {
            display: flex;
            gap: 10px;
        }
        
        .ai-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .ai-btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .ai-btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .ai-btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .ai-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .ai-btn-small {
            padding: 6px 10px;
            font-size: 11px;
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }
        
        .ai-quality-indicator {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 6px;
        }
        
        .quality-score {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .quality-score .score-label {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .quality-score .score-value {
            font-weight: 600;
            font-size: 16px;
        }
        
        .quality-score .score-grade {
            font-weight: 600;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .ai-editor-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 0;
            min-height: 600px;
        }
        
        .ai-editor-main {
            display: flex;
            flex-direction: column;
        }
        
        .ai-editor-wrapper {
            position: relative;
            flex: 1;
        }
        
        .ai-content-editor {
            width: 100%;
            min-height: 400px;
            padding: 20px;
            border: none;
            outline: none;
            resize: vertical;
            font-size: 14px;
            line-height: 1.6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            direction: rtl;
            text-align: right;
        }
        
        .ai-feedback-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .ai-feedback-overlay.active {
            display: flex;
        }
        
        .feedback-content {
            text-align: center;
            padding: 20px;
        }
        
        .ai-editor-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }
        
        .ai-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .stat-item .stat-icon {
            font-size: 14px;
        }
        
        .stat-item .stat-value {
            font-weight: 600;
            color: #333;
        }
        
        .ai-sidebar {
            background: #fafafa;
            border-left: 1px solid #e0e0e0;
            overflow-y: auto;
        }
        
        .ai-side-panel {
            border-bottom: 1px solid #e0e0e0;
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .panel-header .panel-icon {
            margin-left: 8px;
        }
        
        .panel-title {
            font-weight: 600;
            font-size: 13px;
        }
        
        .panel-toggle {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .panel-content {
            padding: 15px;
        }
        
        .quality-overview {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .quality-score-circle {
            position: relative;
            display: inline-block;
        }
        
        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        .score-number {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .score-letter {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #666;
        }
        
        .quality-details {
            space-y: 12px;
        }
        
        .quality-metric {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .metric-label {
            width: 50px;
            font-size: 12px;
            color: #666;
        }
        
        .metric-bar {
            flex: 1;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .metric-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff6b6b, #ffd93d, #6bcf7f);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .metric-score {
            width: 30px;
            font-size: 12px;
            font-weight: 600;
            text-align: right;
            color: #333;
        }
        
        .suggestions-container {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .no-suggestions {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .suggestion-icon {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
        
        .structure-preview {
            min-height: 200px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .structure-placeholder {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .structure-icon {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
        
        .structure-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 15px;
        }
        
        .autosave-status {
            font-size: 11px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .autosave-status::before {
            content: '💾';
        }
        
        @media (max-width: 768px) {
            .ai-editor-grid {
                grid-template-columns: 1fr;
            }
            
            .ai-sidebar {
                border-left: none;
                border-top: 1px solid #e0e0e0;
            }
            
            .ai-editor-header {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            
            .ai-editor-actions {
                justify-content: center;
            }
        }
        </style>
        
        <!-- JavaScript -->
        <script>
        // Initialize AI Editor functionality
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof aiEditor === 'undefined') {
                window.aiEditor = new AIEnhancedEditor();
            }
            
            // Initialize editor
            aiEditor.initEditor('<?php echo esc_js($args['id']); ?>');
        });
        
        // Toggle panel visibility
        function togglePanel(panelId) {
            const panel = document.getElementById(panelId);
            const content = panel.querySelector('.panel-content');
            const toggle = panel.querySelector('.panel-toggle');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                toggle.textContent = '−';
            } else {
                content.style.display = 'none';
                toggle.textContent = '+';
            }
        }
        </script>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get AI-powered writing suggestions
     *
     * @param string $content Current content
     * @param array $context Context information
     * @return array Suggestions
     */
    public function get_ai_suggestions($content, $context = [])
    {
        $suggestions = [];
        
        // Analyze current content
        $quality_analysis = $this->quality_analyzer->analyze_content($content);
        
        // Get content-specific suggestions
        $structure_suggestions = $this->get_structure_suggestions($content, $quality_analysis);
        $readability_suggestions = $this->get_readability_suggestions($content, $quality_analysis);
        $engagement_suggestions = $this->get_engagement_suggestions($content, $quality_analysis);
        $seo_suggestions = $this->get_seo_suggestions($content, $quality_analysis);
        
        // Combine all suggestions
        $suggestions = array_merge($structure_suggestions, $readability_suggestions, $engagement_suggestions, $seo_suggestions);
        
        // Sort by priority
        usort($suggestions, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return array_slice($suggestions, 0, 10); // Return top 10 suggestions
    }
    
    /**
     * Get structure improvement suggestions
     */
    private function get_structure_suggestions($content, $analysis)
    {
        $suggestions = [];
        
        if (!$analysis['analysis']['structure']['has_title']) {
            $suggestions[] = [
                'type' => 'structure',
                'priority' => 9,
                'title' => 'הוסף כותרת ראשית',
                'description' => 'הוסף כותרת H1 ברורה למאמר שלך',
                'action' => 'add_heading',
                'position' => 'start'
            ];
        }
        
        if ($analysis['analysis']['structure']['heading_count'] < 2) {
            $suggestions[] = [
                'type' => 'structure',
                'priority' => 7,
                'title' => 'הוסף תת-כותרות',
                'description' => 'חלק את התוכן לקטעים עם כותרות H2 ו-H3',
                'action' => 'suggest_headings',
                'position' => 'auto'
            ];
        }
        
        if ($analysis['analysis']['structure']['word_count'] < 300) {
            $suggestions[] = [
                'type' => 'structure',
                'priority' => 6,
                'title' => 'הרחב את התוכן',
                'description' => 'התוכן קצר מדי, שקול להרחיבו לפחות ל-500 מילים',
                'action' => 'expand_content',
                'position' => 'end'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get readability improvement suggestions
     */
    private function get_readability_suggestions($content, $analysis)
    {
        $suggestions = [];
        
        $readability = $analysis['analysis']['readability'];
        
        if ($readability['avg_sentence_length'] > 25) {
            $suggestions[] = [
                'type' => 'readability',
                'priority' => 8,
                'title' => 'קצר משפטים ארוכים',
                'description' => 'חלק משפטים ארוכים למשפטים קצרים יותר',
                'action' => 'split_sentences',
                'position' => 'auto'
            ];
        }
        
        if ($readability['complex_words'] > (str_word_count(strip_tags($content)) * 0.3)) {
            $suggestions[] = [
                'type' => 'readability',
                'priority' => 7,
                'title' => 'השתמש במילים פשוטות',
                'description' => 'החלף מילים מורכבות במילים פשוטות יותר',
                'action' => 'simplify_language',
                'position' => 'auto'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get engagement improvement suggestions
     */
    private function get_engagement_suggestions($content, $analysis)
    {
        $suggestions = [];
        
        $engagement = $analysis['analysis']['engagement'];
        
        if (!$engagement['call_to_action']) {
            $suggestions[] = [
                'type' => 'engagement',
                'priority' => 8,
                'title' => 'הוסף קריאה לפעולה',
                'description' => 'הוסף הזמנה ברורה לפעולה בסוף המאמר',
                'action' => 'add_cta',
                'position' => 'end'
            ];
        }
        
        if ($engagement['question_count'] === 0) {
            $suggestions[] = [
                'type' => 'engagement',
                'priority' => 6,
                'title' => 'הוסף שאלות',
                'description' => 'שאלות מגדילות את העניין ומעודדות אינטראקציה',
                'action' => 'add_questions',
                'position' => 'auto'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get SEO improvement suggestions
     */
    private function get_seo_suggestions($content, $analysis)
    {
        $suggestions = [];
        
        $seo = $analysis['analysis']['seo'];
        
        if ($seo['internal_links'] === 0) {
            $suggestions[] = [
                'type' => 'seo',
                'priority' => 7,
                'title' => 'הוסף קישורים פנימיים',
                'description' => 'הוסף קישורים לתוכן קשור באתר שלך',
                'action' => 'suggest_internal_links',
                'position' => 'auto'
            ];
        }
        
        if ($seo['keyword_density'] === 0 && !empty($seo['target_keyword'])) {
            $suggestions[] = [
                'type' => 'seo',
                'priority' => 6,
                'title' => 'שפר צפיפות מילות מפתח',
                'description' => 'השתמש יותר במילת המפתח באופן טבעי',
                'action' => 'optimize_keywords',
                'position' => 'auto'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Improve text using AI
     *
     * @param string $content Content to improve
     * @param string $improvement_type Type of improvement
     * @param array $options Improvement options
     * @return array Improved content and suggestions
     */
    public function improve_text($content, $improvement_type = 'general', $options = [])
    {
        try {
            // Build improvement prompt
            $prompt = $this->build_improvement_prompt($content, $improvement_type, $options);
            
            // Get AI provider settings
            $provider = get_option('ai_manager_pro_default_provider', 'openai');
            $model = $this->get_improvement_model($provider);
            
            // Generate improvement using AI service
            $result = $this->ai_service->generate_content($prompt, $provider, [
                'model' => $model,
                'max_tokens' => 2000,
                'temperature' => 0.7
            ]);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'improved_content' => $result['content'],
                    'improvements' => $this->analyze_improvements($content, $result['content']),
                    'provider' => $provider,
                    'model' => $model
                ];
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'improved_content' => $content
            ];
        }
    }
    
    /**
     * Generate heading structure using AI
     *
     * @param string $content Content to analyze
     * @param string $topic Topic of the content
     * @return array Generated headings
     */
    public function generate_headings($content, $topic = '')
    {
        try {
            // Extract topic from content if not provided
            if (empty($topic)) {
                $topic = $this->extract_topic($content);
            }
            
            $prompt = "צור מבנה כותרות מקצועי בעברית למאמר על הנושא: {$topic}\n\n";
            $prompt .= "התוכן הקיים:\n" . substr(strip_tags($content), 0, 500) . "...\n\n";
            $prompt .= "דרישות:\n";
            $prompt .= "- 1 כותרת ראשית (H1)\n";
            $prompt .= "4-6 תת-כותרות (H2)\n";
            $prompt .= "כותרות מעניינות וקליטות\n";
            $prompt .= "מותאמות לקהל היעד\n";
            $prompt .= "כלול מספרים ורשימות\n\n";
            $prompt .= "החזר רק את הכותרות בפורמט JSON:\n";
            $prompt .= '{"title": "כותרת ראשית", "headings": ["תת-כותרת 1", "תת-כותרת 2", ...]}';
            
            $provider = get_option('ai_manager_pro_default_provider', 'openai');
            $result = $this->ai_service->generate_content($prompt, $provider);
            
            if ($result['success']) {
                $headings = json_decode($result['content'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'headings' => $headings,
                        'provider' => $provider
                    ];
                }
            }
            
            throw new Exception('Failed to parse AI response');
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'headings' => $this->generate_fallback_headings($topic)
            ];
        }
    }
    
    /**
     * Suggest keywords based on content
     *
     * @param string $content Content to analyze
     * @param int $limit Number of keywords to suggest
     * @return array Suggested keywords
     */
    public function suggest_keywords($content, $limit = 10)
    {
        // Extract keywords using existing quality analyzer
        $analysis = $this->quality_analyzer->analyze_content($content);
        $extracted_keywords = array_keys($analysis['analysis']['keywords']['primary_keywords']);
        
        // Get AI-powered keyword suggestions
        try {
            $prompt = "נתח את התוכן הבא והצע 15 מילות מפתח רלוונטיות בעברית:\n\n";
            $prompt .= substr(strip_tags($content), 0, 1000) . "\n\n";
            $prompt .= "החזר רק רשימה של מילות מפתח מופרדות בפסיקים:";
            
            $provider = get_option('ai_manager_pro_default_provider', 'openai');
            $result = $this->ai_service->generate_content($prompt, $provider);
            
            if ($result['success']) {
                $ai_keywords = array_map('trim', explode(',', $result['content']));
                $all_keywords = array_merge($extracted_keywords, $ai_keywords);
                
                return array_unique(array_filter($all_keywords))[:$limit];
            }
        } catch (\Exception $e) {
            // Fallback to extracted keywords only
        }
        
        return array_slice($extracted_keywords, 0, $limit);
    }
    
    /**
     * AJAX handler for AI suggestions
     */
    public function handle_ai_suggestions()
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
            $context = $_POST['context'] ?? [];
            
            $suggestions = $this->get_ai_suggestions($content, $context);
            
            wp_send_json_success(['suggestions' => $suggestions]);
            
        } catch (\Exception $e) {
            wp_send_json_error('Failed to get AI suggestions: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for text improvement
     */
    public function handle_text_improvement()
    {
        // Implementation for text improvement via AJAX
        wp_send_json_success(['improved_content' => '']);
    }
    
    /**
     * AJAX handler for structure analysis
     */
    public function handle_structure_analysis()
    {
        // Implementation for structure analysis via AJAX
        wp_send_json_success(['structure' => []]);
    }
    
    /**
     * AJAX handler for heading generation
     */
    public function handle_heading_generation()
    {
        // Implementation for heading generation via AJAX
        wp_send_json_success(['headings' => []]);
    }
    
    /**
     * AJAX handler for keyword suggestions
     */
    public function handle_keyword_suggestions()
    {
        // Implementation for keyword suggestions via AJAX
        wp_send_json_success(['keywords' => []]);
    }
    
    /**
     * AJAX handler for readability improvements
     */
    public function handle_readability_improvements()
    {
        // Implementation for readability improvements via AJAX
        wp_send_json_success(['improvements' => []]);
    }
    
    /**
     * Build improvement prompt
     */
    private function build_improvement_prompt($content, $type, $options)
    {
        $prompt = "שפר את התוכן הבא בעברית:\n\n";
        $prompt .= $content . "\n\n";
        
        switch ($type) {
            case 'readability':
                $prompt .= "שפר את הקריאות והבהירות של הטקסט. השתמש במשפטים קצרים יותר ומילים פשוטות.";
                break;
            case 'engagement':
                $prompt .= "הפוך את התוכן למעניין ומעורר יותר. הוסף שאלות, סיפורים וקריאות לפעולה.";
                break;
            case 'seo':
                $prompt .= "שפר את התוכן לצרכי SEO. השתמש במילות מפתח רלוונטיות ומבנה ברור.";
                break;
            default:
                $prompt .= "שפר את האיכות הכללית של התוכן. הפוך אותו למקצועי, ברור ומעניין יותר.";
        }
        
        return $prompt;
    }
    
    /**
     * Get improvement model for provider
     */
    private function get_improvement_model($provider)
    {
        $models = [
            'openai' => 'gpt-3.5-turbo',
            'anthropic' => 'claude-3-haiku-20240307',
            'openrouter' => 'openai/gpt-3.5-turbo',
            'deepseek' => 'deepseek-chat'
        ];
        
        return $models[$provider] ?? 'gpt-3.5-turbo';
    }
    
    /**
     * Analyze improvements made
     */
    private function analyze_improvements($original, $improved)
    {
        return [
            'word_count_change' => str_word_count(strip_tags($improved)) - str_word_count(strip_tags($original)),
            'structure_improved' => true, // Placeholder
            'readability_improved' => true, // Placeholder
            'seo_improved' => true // Placeholder
        ];
    }
    
    /**
     * Extract topic from content
     */
    private function extract_topic($content)
    {
        $text = strip_tags($content);
        
        // Try to find first heading
        if (preg_match('/^#\s+(.+)/m', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Use first sentence as topic
        $sentences = preg_split('/[.!?]+/', $text, 2, PREG_SPLIT_NO_EMPTY);
        return trim($sentences[0] ?? 'נושא כללי');
    }
    
    /**
     * Generate fallback headings
     */
    private function generate_fallback_headings($topic)
    {
        return [
            'title' => $topic,
            'headings' => [
                "מבוא ל{$topic}",
                "היתרונות העיקריים",
                "איך ליישם את זה",
                "טיפים וטריקים",
                "סיכום ומסקנות"
            ]
        ];
    }
}
