<?php
/**
 * Real AI Service Implementation
 *
 * @package AI_Manager_Pro
 * @subpackage AI
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Real AI Service Class
 * 
 * Handles actual API calls to AI providers
 */
class AI_Manager_Pro_Real_AI_Service
{
    /**
     * OpenAI API endpoint
     */
    const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Anthropic API endpoint
     */
    const ANTHROPIC_API_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * OpenRouter API endpoint
     */
    const OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * DeepSeek API endpoint
     */
    const DEEPSEEK_API_URL = 'https://api.deepseek.com/v1/chat/completions';

    /**
     * Test API connection
     *
     * @param string $provider AI provider (openai, anthropic, openrouter)
     * @param string $api_key API key to test
     * @return array Test result with success status and message
     */
    public function test_connection($provider, $api_key)
    {
        try {
            switch ($provider) {
                case 'openai':
                    return $this->test_openai_connection($api_key);
                case 'anthropic':
                    return $this->test_anthropic_connection($api_key);
                case 'openrouter':
                    return $this->test_openrouter_connection($api_key);
                case 'deepseek':
                    return $this->test_deepseek_connection($api_key);
                default:
                    return [
                        'success' => false,
                        'message' => 'Unknown provider: ' . $provider
                    ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test OpenAI connection
     */
    private function test_openai_connection($api_key)
    {
        $response = wp_remote_post(self::OPENAI_API_URL, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Test connection - respond with "OK"'
                    ]
                ],
                'max_tokens' => 5,
                'temperature' => 0
            ])
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Network error: ' . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'])) {
            return [
                'success' => true,
                'message' => 'OpenAI connection successful',
                'model' => $data['model'] ?? 'gpt-3.5-turbo'
            ];
        } elseif ($status_code === 401) {
            return [
                'success' => false,
                'message' => 'Invalid API key'
            ];
        } elseif ($status_code === 429) {
            return [
                'success' => false,
                'message' => 'Rate limit exceeded'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API error: ' . ($data['error']['message'] ?? 'Unknown error')
            ];
        }
    }

    /**
     * Test Anthropic connection
     */
    private function test_anthropic_connection($api_key)
    {
        $response = wp_remote_post(self::ANTHROPIC_API_URL, [
            'timeout' => 30,
            'headers' => [
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ],
            'body' => json_encode([
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 5,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Test connection - respond with "OK"'
                    ]
                ]
            ])
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Network error: ' . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['content'])) {
            return [
                'success' => true,
                'message' => 'Anthropic connection successful',
                'model' => $data['model'] ?? 'claude-3-haiku-20240307'
            ];
        } elseif ($status_code === 401) {
            return [
                'success' => false,
                'message' => 'Invalid API key'
            ];
        } elseif ($status_code === 429) {
            return [
                'success' => false,
                'message' => 'Rate limit exceeded'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API error: ' . ($data['error']['message'] ?? 'Unknown error')
            ];
        }
    }

    /**
     * Test OpenRouter connection
     */
    private function test_openrouter_connection($api_key)
    {
        $response = wp_remote_post(self::OPENROUTER_API_URL, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Website Manager Pro'
            ],
            'body' => json_encode([
                'model' => 'openai/gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Test connection - respond with "OK"'
                    ]
                ],
                'max_tokens' => 5,
                'temperature' => 0
            ])
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Network error: ' . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'])) {
            return [
                'success' => true,
                'message' => 'OpenRouter connection successful',
                'model' => $data['model'] ?? 'openai/gpt-3.5-turbo'
            ];
        } elseif ($status_code === 401) {
            return [
                'success' => false,
                'message' => 'Invalid API key'
            ];
        } elseif ($status_code === 402) {
            return [
                'success' => false,
                'message' => 'Insufficient credits'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API error: ' . ($data['error']['message'] ?? 'Unknown error')
            ];
        }
    }

    /**
     * Test DeepSeek connection
     */
    private function test_deepseek_connection($api_key)
    {
        $request_body = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Test connection - respond with "OK" in Hebrew'
                ]
            ],
            'max_tokens' => 10,
            'temperature' => 0
        ];

        // Try with SSL verification first
        $response = wp_remote_post(self::DEEPSEEK_API_URL, [
            'timeout' => 30,
            'sslverify' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
            ],
            'body' => json_encode($request_body)
        ]);

        // SSL fallback mechanism
        if (is_wp_error($response) && strpos($response->get_error_message(), 'SSL') !== false) {
            error_log('DeepSeek test SSL error, retrying without SSL verification');
            $response = wp_remote_post(self::DEEPSEEK_API_URL, [
                'timeout' => 30,
                'sslverify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
                ],
                'body' => json_encode($request_body)
            ]);
        }

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Network error: ' . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'])) {
            return [
                'success' => true,
                'message' => 'DeepSeek connection successful',
                'model' => $data['model'] ?? 'deepseek-chat'
            ];
        } elseif ($status_code === 401) {
            return [
                'success' => false,
                'message' => 'מפתח API לא תקין'
            ];
        } elseif ($status_code === 429) {
            return [
                'success' => false,
                'message' => 'חריגה ממגבלת הקצב'
            ];
        } else {
            $error_message = $data['error']['message'] ?? 'Unknown error';

            // Handle specific error types with Hebrew messages
            if (strpos($error_message, 'Insufficient Balance') !== false || strpos($error_message, 'insufficient_quota') !== false) {
                return [
                    'success' => false,
                    'message' => 'יתרת החשבון ב-DeepSeek אזלה. אנא טען יתרה נוספת בחשבון DeepSeek.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'שגיאת API: ' . $error_message
                ];
            }
        }
    }

    /**
     * Generate content using AI
     *
     * @param string $prompt Content prompt
     * @param string $provider AI provider
     * @param array $options Generation options
     * @return array Generation result
     */
    public function generate_content($prompt, $provider = 'openai', $options = [])
    {
        $api_key = $this->get_api_key($provider);
        if (!$api_key) {
            throw new Exception("API key not found for provider: {$provider}");
        }

        $default_options = [
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'model' => $this->get_default_model($provider)
        ];

        $options = array_merge($default_options, $options);

        // Test basic connectivity before making content requests
        error_log("AI Manager Pro - Testing connectivity for provider: {$provider}");

        $connectivity_urls = [
            'openai' => 'https://api.openai.com',
            'anthropic' => 'https://api.anthropic.com',
            'openrouter' => 'https://openrouter.ai',
            'deepseek' => 'https://api.deepseek.com'
        ];

        if (isset($connectivity_urls[$provider])) {
            $test_url = $connectivity_urls[$provider];
            error_log("AI Manager Pro - Testing connectivity to: {$test_url}");

            $test_response = wp_remote_get($test_url, [
                'timeout' => 10,
                'sslverify' => true
            ]);

            if (is_wp_error($test_response)) {
                $error_msg = $test_response->get_error_message();
                error_log("AI Manager Pro - Connectivity test failed for {$provider}: {$error_msg}");

                // Try without SSL verification as fallback
                if (strpos($error_msg, 'SSL') !== false) {
                    error_log("AI Manager Pro - Retrying connectivity test without SSL verification");
                    $test_response = wp_remote_get($test_url, [
                        'timeout' => 10,
                        'sslverify' => false
                    ]);

                    if (is_wp_error($test_response)) {
                        error_log("AI Manager Pro - Connectivity test failed even without SSL: " . $test_response->get_error_message());
                        throw new Exception("Cannot connect to {$provider} API: " . $test_response->get_error_message());
                    } else {
                        error_log("AI Manager Pro - Connectivity test succeeded without SSL verification");
                    }
                } else {
                    throw new Exception("Cannot connect to {$provider} API: " . $error_msg);
                }
            } else {
                $status_code = wp_remote_retrieve_response_code($test_response);
                error_log("AI Manager Pro - Connectivity test successful for {$provider} (Status: {$status_code})");
            }
        }

        try {
            switch ($provider) {
                case 'openai':
                    return $this->generate_openai_content($prompt, $api_key, $options);
                case 'anthropic':
                    return $this->generate_anthropic_content($prompt, $api_key, $options);
                case 'openrouter':
                    return $this->generate_openrouter_content($prompt, $api_key, $options);
                case 'deepseek':
                    return $this->generate_deepseek_content($prompt, $api_key, $options);
                default:
                    throw new Exception("Unknown provider: {$provider}");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Content generation failed: ' . $e->getMessage(),
                'content' => null
            ];
        }
    }

    /**
     * Generate content using OpenAI
     */
    private function generate_openai_content($prompt, $api_key, $options)
    {
        $response = wp_remote_post(self::OPENAI_API_URL, [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => $options['model'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature']
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Network error: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'content' => trim($data['choices'][0]['message']['content']),
                'model' => $data['model'],
                'usage' => $data['usage'] ?? null
            ];
        } else {
            throw new Exception('API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }
    }

    /**
     * Generate content using Anthropic
     */
    private function generate_anthropic_content($prompt, $api_key, $options)
    {
        $response = wp_remote_post(self::ANTHROPIC_API_URL, [
            'timeout' => 60,
            'headers' => [
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ],
            'body' => json_encode([
                'model' => $options['model'],
                'max_tokens' => $options['max_tokens'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Network error: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['content'][0]['text'])) {
            return [
                'success' => true,
                'content' => trim($data['content'][0]['text']),
                'model' => $data['model'],
                'usage' => $data['usage'] ?? null
            ];
        } else {
            throw new Exception('API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }
    }

    /**
     * Generate content using OpenRouter
     */
    private function generate_openrouter_content($prompt, $api_key, $options)
    {
        $response = wp_remote_post(self::OPENROUTER_API_URL, [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Website Manager Pro'
            ],
            'body' => json_encode([
                'model' => $options['model'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature']
            ])
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Network error: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'content' => trim($data['choices'][0]['message']['content']),
                'model' => $data['model'],
                'usage' => $data['usage'] ?? null
            ];
        } else {
            throw new Exception('API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }
    }

    /**
     * Generate content using DeepSeek
     */
    private function generate_deepseek_content($prompt, $api_key, $options)
    {
        // Debug logging
        error_log('DeepSeek API Call - URL: ' . self::DEEPSEEK_API_URL);
        error_log('DeepSeek API Call - API Key Length: ' . strlen($api_key));
        error_log('DeepSeek API Call - Model: ' . $options['model']);

        $request_body = [
            'model' => $options['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'אתה עוזר AI מקצועי של DeepSeek שיוצר תוכן איכותי בעברית. השתמש בשפה ברורה ומקצועית.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $options['max_tokens'],
            'temperature' => $options['temperature']
        ];

        error_log('DeepSeek API Call - Request Body: ' . json_encode($request_body, JSON_UNESCAPED_UNICODE));

        $response = wp_remote_post(self::DEEPSEEK_API_URL, [
            'timeout' => 60,
            'sslverify' => true, // Try with SSL verification first
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
            ],
            'body' => json_encode($request_body)
        ]);

        // If SSL verification fails, try without it
        if (is_wp_error($response) && strpos($response->get_error_message(), 'SSL') !== false) {
            error_log('DeepSeek SSL error, retrying without SSL verification');
            $response = wp_remote_post(self::DEEPSEEK_API_URL, [
                'timeout' => 60,
                'sslverify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'AI-Website-Manager-Pro/3.2.1'
                ],
                'body' => json_encode($request_body)
            ]);
        }

        if (is_wp_error($response)) {
            $error_msg = 'Network error: ' . $response->get_error_message();
            error_log('DeepSeek API Error: ' . $error_msg);
            throw new Exception($error_msg);
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        error_log('DeepSeek API Response - Status: ' . $status_code);
        error_log('DeepSeek API Response - Body: ' . $body);

        $data = json_decode($body, true);

        if ($status_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'content' => trim($data['choices'][0]['message']['content']),
                'model' => $data['model'],
                'usage' => $data['usage'] ?? null
            ];
        } else {
            $error_message = $data['error']['message'] ?? $body ?? 'Unknown error';

            // Handle specific error types with Hebrew messages
            if (strpos($error_message, 'Insufficient Balance') !== false || strpos($error_message, 'insufficient_quota') !== false) {
                $error_msg = 'יתרת החשבון ב-DeepSeek אזלה. אנא טען יתרה נוספת בחשבון DeepSeek שלך.';
            } elseif (strpos($error_message, 'Invalid API key') !== false || $status_code === 401) {
                $error_msg = 'מפתח API לא תקין. אנא בדוק את מפתח ה-API של DeepSeek בהגדרות.';
            } elseif (strpos($error_message, 'rate_limit') !== false || $status_code === 429) {
                $error_msg = 'חריגה ממגבלת הקצב. אנא נסה שוב בעוד כמה דקות.';
            } else {
                $error_msg = 'שגיאת DeepSeek API (Status: ' . $status_code . '): ' . $error_message;
            }

            error_log('DeepSeek API Error: ' . $error_msg . ' | Original: ' . $error_message);
            throw new Exception($error_msg);
        }
    }

    /**
     * Get API key for provider
     */
    private function get_api_key($provider)
    {
        switch ($provider) {
            case 'openai':
                return get_option('ai_manager_pro_openai_api_key');
            case 'anthropic':
                return get_option('ai_manager_pro_anthropic_api_key');
            case 'openrouter':
                return get_option('ai_manager_pro_openrouter_api_key');
            case 'deepseek':
                return get_option('ai_manager_pro_deepseek_key');
            default:
                return null;
        }
    }

    /**
     * Get default model for provider
     */
    private function get_default_model($provider)
    {
        switch ($provider) {
            case 'openai':
                return 'gpt-3.5-turbo';
            case 'anthropic':
                return 'claude-3-haiku-20240307';
            case 'openrouter':
                return get_option('ai_manager_pro_openrouter_model', 'openai/gpt-3.5-turbo');
            case 'deepseek':
                return get_option('ai_manager_pro_deepseek_model', 'deepseek-chat');
            default:
                return 'gpt-3.5-turbo';
        }
    }

    /**
     * Build content prompt with brand context
     */
    public function build_prompt($topic, $content_type, $brand_data = null, $additional_instructions = '')
    {
        $prompt = "Create {$content_type} content about: {$topic}\n\n";

        if ($brand_data) {
            $prompt .= "Brand Guidelines:\n";
            if (isset($brand_data['voice'])) {
                $prompt .= "- Tone: " . ($brand_data['voice']['tone'] ?? 'professional') . "\n";
                $prompt .= "- Style: " . ($brand_data['voice']['style'] ?? 'informative') . "\n";
                $prompt .= "- Personality: " . ($brand_data['voice']['personality'] ?? 'friendly') . "\n";
            }

            if (isset($brand_data['target_audience'])) {
                $prompt .= "- Target Audience: " . $brand_data['target_audience'] . "\n";
            }

            if (isset($brand_data['keywords']) && is_array($brand_data['keywords'])) {
                $prompt .= "- Keywords to include: " . implode(', ', $brand_data['keywords']) . "\n";
            }

            if (isset($brand_data['avoid_words']) && is_array($brand_data['avoid_words'])) {
                $prompt .= "- Words to avoid: " . implode(', ', $brand_data['avoid_words']) . "\n";
            }
        }

        if (!empty($additional_instructions)) {
            $prompt .= "\nAdditional Instructions:\n" . $additional_instructions . "\n";
        }

        $prompt .= "\nPlease create engaging, high-quality content that follows these guidelines.";

        return $prompt;
    }
}