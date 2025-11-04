<?php
/**
 * DeepSeek AI Service
 *
 * @package AI_Manager_Pro
 * @subpackage AI
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DeepSeek AI Service Class
 */
class AI_Manager_Pro_DeepSeek_Service
{
    /**
     * API endpoint
     */
    private const API_ENDPOINT = 'https://api.deepseek.com/v1/chat/completions';

    /**
     * Models endpoint
     */
    private const MODELS_ENDPOINT = 'https://api.deepseek.com/v1/models';

    /**
     * API key
     */
    private $api_key;

    /**
     * Available models
     */
    private $models = [
        'deepseek-chat' => [
            'name' => 'DeepSeek Chat',
            'description' => 'מודל שיחה מתקדם של DeepSeek',
            'context_length' => 32768,
            'icon' => '🧠',
            'category' => 'chat'
        ],
        'deepseek-coder' => [
            'name' => 'DeepSeek Coder',
            'description' => 'מודל מיוחד לכתיבת קוד ופיתוח',
            'context_length' => 16384,
            'icon' => '💻',
            'category' => 'code'
        ]
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api_key = get_option('ai_manager_pro_deepseek_key', '');
    }

    /**
     * Get available models
     */
    public function get_models()
    {
        return $this->models;
    }

    /**
     * Get popular models for quick selection
     */
    public function get_popular_models()
    {
        return [
            'deepseek-chat' => [
                'name' => 'DeepSeek Chat',
                'description' => 'מודל שיחה מתקדם עם הבנה עמוקה',
                'icon' => '🧠',
                'category' => 'standard'
            ],
            'deepseek-coder' => [
                'name' => 'DeepSeek Coder',
                'description' => 'מיוחד לכתיבת קוד ופתרון בעיות טכניות',
                'icon' => '💻',
                'category' => 'specialized'
            ]
        ];
    }

    /**
     * Generate content using DeepSeek
     */
    public function generate_content($prompt, $model = null, $options = [])
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'מפתח DeepSeek API לא הוגדר');
        }

        $model = $model ?: get_option('ai_manager_pro_deepseek_default_model', 'deepseek-chat');

        $default_options = [
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => false
        ];

        $options = wp_parse_args($options, $default_options);

        $messages = [
            [
                'role' => 'system',
                'content' => 'אתה עוזר AI מקצועי של DeepSeek שיוצר תוכן איכותי בעברית. השתמש בשפה ברורה ומקצועית והתמחה בפתרונות יצירתיים ומעמיקים.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $body = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'],
            'temperature' => $options['temperature'],
            'top_p' => $options['top_p'],
            'frequency_penalty' => $options['frequency_penalty'],
            'presence_penalty' => $options['presence_penalty'],
            'stream' => $options['stream']
        ];

        $response = wp_remote_post($this::API_ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
            ],
            'body' => json_encode($body),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'שגיאה בחיבור ל-DeepSeek: ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'שגיאה לא ידועה';
            return new WP_Error('api_error', 'DeepSeek API Error: ' . $error_message);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'תגובה לא תקינה מ-DeepSeek');
        }

        $content = trim($data['choices'][0]['message']['content']);

        // Log usage for analytics
        $this->log_usage($model, strlen($prompt), strlen($content));

        return [
            'content' => $content,
            'model' => $model,
            'usage' => $data['usage'] ?? [],
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? 'stop'
        ];
    }

    /**
     * Test API connection
     */
    public function test_connection()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'מפתח API לא הוגדר');
        }

        $test_prompt = 'שלום, זהו בדיקת חיבור ל-DeepSeek. אנא השב "החיבור תקין" בעברית.';

        $result = $this->generate_content($test_prompt, 'deepseek-chat', [
            'max_tokens' => 50,
            'temperature' => 0.1
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return [
            'success' => true,
            'message' => 'החיבור ל-DeepSeek תקין',
            'response' => $result['content']
        ];
    }

    /**
     * Generate specialized content based on type
     */
    public function generate_specialized_content($content_type, $topic, $brand_data = null, $options = [])
    {
        $model = $this->select_model_for_content_type($content_type);
        $prompt = $this->build_specialized_prompt($content_type, $topic, $brand_data);

        return $this->generate_content($prompt, $model, $options);
    }

    /**
     * Select appropriate model based on content type
     */
    private function select_model_for_content_type($content_type)
    {
        $code_related_types = ['technical_documentation', 'api_documentation', 'code_tutorial', 'developer_guide'];

        if (in_array($content_type, $code_related_types)) {
            return 'deepseek-coder';
        }

        return 'deepseek-chat';
    }

    /**
     * Build specialized prompt based on content type and brand
     */
    private function build_specialized_prompt($content_type, $topic, $brand_data = null)
    {
        $prompts = [
            'blog_post' => "צור פוסט בלוג מקצועי ומעניין על הנושא: {$topic}",
            'social_media' => "צור פוסט לרשתות חברתיות קצר ומושך על: {$topic}",
            'product_description' => "כתב תיאור מוצר משכנע ומפורט עבור: {$topic}",
            'email_marketing' => "צור אימייל שיווקי אפקטיבי בנושא: {$topic}",
            'technical_documentation' => "כתב תיעוד טכני מפורט ומדויק עבור: {$topic}",
            'code_tutorial' => "צור מדריך קוד שלב אחר שלב עבור: {$topic}",
            'seo_content' => "צור תוכן מאופטמז למנועי חיפוש בנושא: {$topic}",
            'landing_page' => "כתב תוכן לדף נחיתה משכנע עבור: {$topic}"
        ];

        $base_prompt = $prompts[$content_type] ?? $prompts['blog_post'];

        if ($brand_data) {
            $base_prompt .= "\n\nהנחיות מותג:";

            if (isset($brand_data['voice'])) {
                $base_prompt .= "\n- טון: " . ($brand_data['voice']['tone'] ?? 'מקצועי');
                $base_prompt .= "\n- סגנון: " . ($brand_data['voice']['style'] ?? 'אינפורמטיבי');
                $base_prompt .= "\n- אישיות: " . ($brand_data['voice']['personality'] ?? 'ידידותי');
            }

            if (isset($brand_data['target_audience'])) {
                $base_prompt .= "\n- קהל יעד: " . $brand_data['target_audience'];
            }

            if (isset($brand_data['keywords']) && is_array($brand_data['keywords'])) {
                $base_prompt .= "\n- מילות מפתח לשילוב: " . implode(', ', $brand_data['keywords']);
            }

            if (isset($brand_data['guidelines']['content_length'])) {
                $base_prompt .= "\n- אורך תוכן: " . $brand_data['guidelines']['content_length'];
            }
        }

        $base_prompt .= "\n\nצור תוכן איכותי, מקורי ומעניין שעונה על הדרישות.";

        return $base_prompt;
    }

    /**
     * Log usage for analytics
     */
    private function log_usage($model, $prompt_tokens, $completion_tokens)
    {
        $usage_data = get_option('ai_manager_pro_usage_stats', []);
        $today = date('Y-m-d');

        if (!isset($usage_data[$today])) {
            $usage_data[$today] = [];
        }

        $provider_model = "deepseek/{$model}";
        if (!isset($usage_data[$today][$provider_model])) {
            $usage_data[$today][$provider_model] = [
                'requests' => 0,
                'prompt_tokens' => 0,
                'completion_tokens' => 0
            ];
        }

        $usage_data[$today][$provider_model]['requests']++;
        $usage_data[$today][$provider_model]['prompt_tokens'] += $prompt_tokens;
        $usage_data[$today][$provider_model]['completion_tokens'] += $completion_tokens;

        // Keep only last 30 days
        $cutoff_date = date('Y-m-d', strtotime('-30 days'));
        foreach ($usage_data as $date => $data) {
            if ($date < $cutoff_date) {
                unset($usage_data[$date]);
            }
        }

        update_option('ai_manager_pro_usage_stats', $usage_data);
    }

    /**
     * Get usage statistics
     */
    public function get_usage_stats($days = 7)
    {
        $usage_data = get_option('ai_manager_pro_usage_stats', []);
        $stats = [
            'total_requests' => 0,
            'total_tokens' => 0,
            'models_used' => [],
            'daily_usage' => []
        ];

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        foreach ($usage_data as $date => $daily_data) {
            if ($date >= $start_date) {
                $stats['daily_usage'][$date] = $daily_data;

                foreach ($daily_data as $model => $model_data) {
                    if (strpos($model, 'deepseek/') === 0) {
                        $stats['total_requests'] += $model_data['requests'];
                        $stats['total_tokens'] += $model_data['prompt_tokens'] + $model_data['completion_tokens'];

                        if (!isset($stats['models_used'][$model])) {
                            $stats['models_used'][$model] = 0;
                        }
                        $stats['models_used'][$model] += $model_data['requests'];
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Get model pricing information
     */
    public function get_model_pricing()
    {
        return [
            'deepseek-chat' => [
                'input' => 0.14,  // per 1M tokens
                'output' => 0.28, // per 1M tokens
                'currency' => 'USD'
            ],
            'deepseek-coder' => [
                'input' => 0.14,  // per 1M tokens
                'output' => 0.28, // per 1M tokens
                'currency' => 'USD'
            ]
        ];
    }

    /**
     * Estimate cost for a request
     */
    public function estimate_cost($prompt_length, $expected_response_length, $model = 'deepseek-chat')
    {
        $pricing = $this->get_model_pricing();

        if (!isset($pricing[$model])) {
            return null;
        }

        // Rough estimation: 1 token ≈ 4 characters
        $input_tokens = ceil($prompt_length / 4);
        $output_tokens = ceil($expected_response_length / 4);

        $input_cost = ($input_tokens / 1000000) * $pricing[$model]['input'];
        $output_cost = ($output_tokens / 1000000) * $pricing[$model]['output'];

        return [
            'input_cost' => $input_cost,
            'output_cost' => $output_cost,
            'total_cost' => $input_cost + $output_cost,
            'currency' => $pricing[$model]['currency']
        ];
    }
}