<?php
/**
 * OpenRouter AI Service
 *
 * @package AI_Manager_Pro
 * @subpackage AI
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenRouter AI Service Class
 */
class AI_Manager_Pro_OpenRouter_Service
{
    /**
     * API endpoint
     */
    private const API_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Models endpoint
     */
    private const MODELS_ENDPOINT = 'https://openrouter.ai/api/v1/models';

    /**
     * API key
     */
    private $api_key;

    /**
     * Available models
     */
    private $models = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api_key = get_option('ai_manager_pro_openrouter_api_key', '');
        $this->load_models();
    }

    /**
     * Load available models
     */
    private function load_models()
    {
        // Cache models for 1 hour
        $cached_models = get_transient('ai_manager_pro_openrouter_models');
        if ($cached_models !== false) {
            $this->models = $cached_models;
            return;
        }

        if (empty($this->api_key)) {
            return;
        }

        $response = wp_remote_get($this::MODELS_ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            error_log('OpenRouter models fetch error: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['data']) && is_array($data['data'])) {
            $this->models = $this->process_models($data['data']);
            set_transient('ai_manager_pro_openrouter_models', $this->models, HOUR_IN_SECONDS);
        }
    }

    /**
     * Process and categorize models
     */
    private function process_models($raw_models)
    {
        $processed = [
            'gpt' => [],
            'claude' => [],
            'gemini' => [],
            'llama' => [],
            'other' => []
        ];

        foreach ($raw_models as $model) {
            $model_data = [
                'id' => $model['id'],
                'name' => $model['name'] ?? $model['id'],
                'description' => $model['description'] ?? '',
                'context_length' => $model['context_length'] ?? 4096,
                'pricing' => [
                    'prompt' => $model['pricing']['prompt'] ?? '0',
                    'completion' => $model['pricing']['completion'] ?? '0'
                ],
                'top_provider' => $model['top_provider'] ?? []
            ];

            // Categorize models
            $model_id = strtolower($model['id']);
            if (strpos($model_id, 'gpt') !== false || strpos($model_id, 'openai') !== false) {
                $processed['gpt'][] = $model_data;
            } elseif (strpos($model_id, 'claude') !== false || strpos($model_id, 'anthropic') !== false) {
                $processed['claude'][] = $model_data;
            } elseif (strpos($model_id, 'gemini') !== false || strpos($model_id, 'google') !== false) {
                $processed['gemini'][] = $model_data;
            } elseif (strpos($model_id, 'llama') !== false || strpos($model_id, 'meta') !== false) {
                $processed['llama'][] = $model_data;
            } else {
                $processed['other'][] = $model_data;
            }
        }

        return $processed;
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
            'openai/gpt-4-turbo-preview' => [
                'name' => 'GPT-4 Turbo',
                'description' => 'המודל המתקדם ביותר של OpenAI',
                'icon' => '🚀',
                'category' => 'premium'
            ],
            'openai/gpt-3.5-turbo' => [
                'name' => 'GPT-3.5 Turbo',
                'description' => 'מהיר וחסכוני לרוב המשימות',
                'icon' => '⚡',
                'category' => 'standard'
            ],
            'anthropic/claude-3-opus' => [
                'name' => 'Claude 3 Opus',
                'description' => 'המודל החכם ביותר של Anthropic',
                'icon' => '🧠',
                'category' => 'premium'
            ],
            'anthropic/claude-3-sonnet' => [
                'name' => 'Claude 3 Sonnet',
                'description' => 'איזון מושלם בין ביצועים ומהירות',
                'icon' => '🎯',
                'category' => 'standard'
            ],
            'google/gemini-pro' => [
                'name' => 'Gemini Pro',
                'description' => 'המודל המתקדם של Google',
                'icon' => '💎',
                'category' => 'standard'
            ],
            'meta-llama/llama-2-70b-chat' => [
                'name' => 'Llama 2 70B',
                'description' => 'מודל קוד פתוח חזק ומהיר',
                'icon' => '🦙',
                'category' => 'open-source'
            ]
        ];
    }

    /**
     * Generate content using OpenRouter
     */
    public function generate_content($prompt, $model = null, $options = [])
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'מפתח OpenRouter API לא הוגדר');
        }

        $model = $model ?: get_option('ai_manager_pro_default_model', 'openai/gpt-3.5-turbo');

        $default_options = [
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];

        $options = wp_parse_args($options, $default_options);

        $messages = [
            [
                'role' => 'system',
                'content' => 'אתה עוזר AI מקצועי שיוצר תוכן איכותי בעברית. השתמש בשפה ברורה ומקצועית.'
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
            'presence_penalty' => $options['presence_penalty']
        ];

        $response = wp_remote_post($this::API_ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ],
            'body' => json_encode($body),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'שגיאה בחיבור ל-OpenRouter: ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'שגיאה לא ידועה';
            return new WP_Error('api_error', 'OpenRouter API Error: ' . $error_message);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'תגובה לא תקינה מ-OpenRouter');
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
     * Generate an image using a free model on OpenRouter.
     *
     * @param string $prompt The text prompt for image generation.
     * @return array|WP_Error The generated image data or an error.
     */
    public function generate_image($prompt)
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'OpenRouter API key is not configured.');
        }

        // Using a free model known to support image generation via OpenRouter.
        $model = 'google/gemini-pro-vision';

        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'modalities' => ['image', 'text'], // Custom OpenRouter parameter
        ];

        $response = wp_remote_post(self::API_ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ],
            'body' => json_encode($body),
            'timeout' => 120, // Image generation can be slow
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Error connecting to OpenRouter for image generation: ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API error';
            return new WP_Error('api_error', 'OpenRouter Image API Error: ' . $error_message);
        }

        // Extract base64 data from the data URL
        $image_url = $data['choices'][0]['message']['images'][0]['image_url']['url'] ?? null;

        if ($image_url && strpos($image_url, 'data:image/png;base64,') === 0) {
            $base64_data = str_replace('data:image/png;base64,', '', $image_url);
            return [
                'base64_image' => $base64_data
            ];
        }

        return new WP_Error('invalid_response', 'Could not find valid base64 image data in the OpenRouter API response.');
    }

    /**
     * Test API connection
     */
    public function test_connection()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'מפתח API לא הוגדר');
        }

        $test_prompt = 'שלום, זהו בדיקת חיבור. אנא השב "החיבור תקין" בעברית.';

        $result = $this->generate_content($test_prompt, 'openai/gpt-3.5-turbo', [
            'max_tokens' => 50,
            'temperature' => 0.1
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return [
            'success' => true,
            'message' => 'החיבור ל-OpenRouter תקין',
            'response' => $result['content']
        ];
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

        if (!isset($usage_data[$today][$model])) {
            $usage_data[$today][$model] = [
                'requests' => 0,
                'prompt_tokens' => 0,
                'completion_tokens' => 0
            ];
        }

        $usage_data[$today][$model]['requests']++;
        $usage_data[$today][$model]['prompt_tokens'] += $prompt_tokens;
        $usage_data[$today][$model]['completion_tokens'] += $completion_tokens;

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
                    $stats['total_requests'] += $model_data['requests'];
                    $stats['total_tokens'] += $model_data['prompt_tokens'] + $model_data['completion_tokens'];

                    if (!isset($stats['models_used'][$model])) {
                        $stats['models_used'][$model] = 0;
                    }
                    $stats['models_used'][$model] += $model_data['requests'];
                }
            }
        }

        return $stats;
    }
}