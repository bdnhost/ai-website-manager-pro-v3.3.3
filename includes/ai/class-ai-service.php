<?php
/**
 * AI Service
 * שירות AI מתקדם עם תמיכה במספר ספקים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_AI_Service
{

    private $providers = [];
    private $default_provider = 'openai';
    private $fallback_providers = ['openai', 'anthropic', 'openrouter', 'deepseek'];

    public function __construct()
    {
        $this->init_providers();
    }

    /**
     * אתחול ספקי AI
     */
    private function init_providers()
    {
        $this->providers = [
            'openai' => [
                'name' => 'OpenAI',
                'models' => ['gpt-4', 'gpt-3.5-turbo'],
                'api_key_option' => 'ai_manager_pro_openai_api_key',
                'endpoint' => 'https://api.openai.com/v1/chat/completions'
            ],
            'anthropic' => [
                'name' => 'Anthropic',
                'models' => ['claude-3-opus-20240229', 'claude-3-sonnet-20240229', 'claude-3-haiku-20240307'],
                'api_key_option' => 'ai_manager_pro_anthropic_api_key',
                'endpoint' => 'https://api.anthropic.com/v1/messages'
            ],
            'openrouter' => [
                'name' => 'OpenRouter',
                'models' => [
                    'openai/gpt-4',
                    'openai/gpt-3.5-turbo',
                    'anthropic/claude-3-opus',
                    'anthropic/claude-3-sonnet',
                    'meta-llama/llama-2-70b-chat',
                    'mistralai/mixtral-8x7b-instruct',
                    'google/gemini-pro'
                ],
                'api_key_option' => 'ai_manager_pro_openrouter_api_key',
                'endpoint' => 'https://openrouter.ai/api/v1/chat/completions'
            ],
            'deepseek' => [
                'name' => 'DeepSeek',
                'models' => ['deepseek-chat', 'deepseek-coder'],
                'api_key_option' => 'ai_manager_pro_deepseek_key',
                'endpoint' => 'https://api.deepseek.com/v1/chat/completions'
            ]
        ];
    }

    /**
     * יצירת תוכן באמצעות AI
     */
    public function generate_content($prompt, $options = [])
    {
        $provider = $options['provider'] ?? $this->get_default_provider();
        $model = $options['model'] ?? $this->get_default_model($provider);
        $max_tokens = $options['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? 0.7;

        // ניסיון עם הספק הראשי
        try {
            return $this->call_ai_provider($provider, $prompt, $model, $max_tokens, $temperature);
        } catch (Exception $e) {
            error_log("AI Service: Primary provider failed: " . $e->getMessage());

            // ניסיון עם ספקי fallback
            foreach ($this->fallback_providers as $fallback_provider) {
                if ($fallback_provider === $provider)
                    continue;

                try {
                    $fallback_model = $this->get_default_model($fallback_provider);
                    return $this->call_ai_provider($fallback_provider, $prompt, $fallback_model, $max_tokens, $temperature);
                } catch (Exception $fallback_e) {
                    error_log("AI Service: Fallback provider {$fallback_provider} failed: " . $fallback_e->getMessage());
                    continue;
                }
            }

            // אם כל הספקים נכשלו, החזר תוכן ברירת מחדל
            return $this->get_fallback_content($prompt);
        }
    }

    /**
     * קריאה לספק AI
     */
    private function call_ai_provider($provider, $prompt, $model, $max_tokens, $temperature)
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unknown AI provider: {$provider}");
        }

        $provider_config = $this->providers[$provider];
        $api_key = get_option($provider_config['api_key_option']);

        if (empty($api_key)) {
            throw new Exception("API key not configured for provider: {$provider}");
        }

        switch ($provider) {
            case 'openai':
                return $this->call_openai($api_key, $prompt, $model, $max_tokens, $temperature);
            case 'anthropic':
                return $this->call_anthropic($api_key, $prompt, $model, $max_tokens, $temperature);
            case 'openrouter':
                return $this->call_openrouter($api_key, $prompt, $model, $max_tokens, $temperature);
            case 'deepseek':
                return $this->call_deepseek($api_key, $prompt, $model, $max_tokens, $temperature);
            default:
                throw new Exception("Provider implementation not found: {$provider}");
        }
    }

    /**
     * קריאה ל-OpenAI
     */
    private function call_openai($api_key, $prompt, $model, $max_tokens, $temperature)
    {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $max_tokens,
                'temperature' => $temperature
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('OpenAI API request failed: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception('OpenAI API error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Invalid OpenAI API response format');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * קריאה ל-Anthropic
     */
    private function call_anthropic($api_key, $prompt, $model, $max_tokens, $temperature)
    {
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'timeout' => 60,
            'headers' => [
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ],
            'body' => json_encode([
                'model' => $model,
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Anthropic API request failed: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception('Anthropic API error: ' . $data['error']['message']);
        }

        if (!isset($data['content'][0]['text'])) {
            throw new Exception('Invalid Anthropic API response format');
        }

        return trim($data['content'][0]['text']);
    }

    /**
     * קריאה ל-OpenRouter
     */
    private function call_openrouter($api_key, $prompt, $model, $max_tokens, $temperature)
    {
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Website Manager Pro'
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $max_tokens,
                'temperature' => $temperature
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('OpenRouter API request failed: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception('OpenRouter API error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Invalid OpenRouter API response format');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * קריאה ל-DeepSeek
     */
    private function call_deepseek($api_key, $prompt, $model, $max_tokens, $temperature)
    {
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'אתה עוזר AI מקצועי של DeepSeek שיוצר תוכן איכותי בעברית.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $max_tokens,
                'temperature' => $temperature
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('DeepSeek API request failed: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception('DeepSeek API error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Invalid DeepSeek API response format');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * תוכן ברירת מחדל במקרה של כשל
     */
    private function get_fallback_content($prompt)
    {
        $fallback_templates = [
            'blog_post' => "
# כותרת מעניינת לפוסט הבלוג

## מבוא
זהו תוכן ברירת מחדל שנוצר כאשר שירותי ה-AI אינם זמינים. 

## תוכן עיקרי
הפוסט הזה מדבר על נושא חשוב ומעניין שיכול לעניין את הקוראים שלכם.

### נקודות מפתח:
- נקודה ראשונה חשובה
- נקודה שנייה מעניינת  
- נקודה שלישית רלוונטית

## סיכום
לסיכום, זהו תוכן בסיסי שיכול לשמש כנקודת התחלה לעריכה נוספת.
            ",
            'social_media' => "
🤔 שאלה מעניינת לקהילה שלנו!

מה דעתכם על הנושא הזה? אשמח לשמוע את המחשבות שלכם בתגובות! 💭

#טיפים #קהילה #שיתוף #דעות
            ",
            'product_description' => "
🌟 מוצר מדהים שכדאי להכיר!

✅ יתרון ראשון - איכות מעולה
✅ יתרון שני - מחיר הוגן  
✅ יתרון שלישי - שירות מקצועי

🔥 הזמינו עכשיו ותיהנו מהמוצר הטוב ביותר!
            ",
            'email_marketing' => "
שלום [שם],

אני רוצה לשתף אתכם במשהו מיוחד שיכול לעניין אתכם.

זהו תוכן ברירת מחדל לאימייל שיווקי שיכול לשמש כבסיס לתוכן מותאם אישית.

בברכה,
הצוות שלנו
            "
        ];

        // ניסיון לזהות סוג תוכן מהפרומפט
        $content_type = 'blog_post'; // ברירת מחדל

        if (stripos($prompt, 'social') !== false || stripos($prompt, 'facebook') !== false || stripos($prompt, 'instagram') !== false) {
            $content_type = 'social_media';
        } elseif (stripos($prompt, 'product') !== false || stripos($prompt, 'מוצר') !== false) {
            $content_type = 'product_description';
        } elseif (stripos($prompt, 'email') !== false || stripos($prompt, 'אימייל') !== false) {
            $content_type = 'email_marketing';
        }

        return $fallback_templates[$content_type];
    }

    /**
     * קבלת ספק ברירת מחדל
     */
    private function get_default_provider()
    {
        return get_option('ai_manager_pro_default_provider', $this->default_provider);
    }

    /**
     * קבלת מודל ברירת מחדל לספק
     */
    private function get_default_model($provider)
    {
        if (!isset($this->providers[$provider])) {
            return 'gpt-3.5-turbo';
        }

        $models = $this->providers[$provider]['models'];
        return $models[0] ?? 'gpt-3.5-turbo';
    }

    /**
     * בדיקת זמינות ספק
     */
    public function test_provider_connection($provider)
    {
        try {
            $test_prompt = "Say 'Hello' in Hebrew";
            $result = $this->call_ai_provider($provider, $test_prompt, $this->get_default_model($provider), 50, 0.1);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * קבלת רשימת ספקים זמינים
     */
    public function get_available_providers()
    {
        return $this->providers;
    }

    /**
     * קבלת מודלים לספק
     */
    public function get_provider_models($provider)
    {
        return $this->providers[$provider]['models'] ?? [];
    }
}