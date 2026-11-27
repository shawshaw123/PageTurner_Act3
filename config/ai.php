<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    | Order of providers to try when the primary fails.
    */
    'fallback_chain' => ['openai', 'gemini', 'ollama', 'mock'],
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'max_tokens' => 1024,
            'temperature' => 0.7,
            'daily_token_limit' => 200000,
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'max_tokens' => 1024,
            'daily_token_limit' => 1500,
        ],

        'ollama' => [
            'enabled' => env('OLLAMA_ENABLED', false),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.2'),
            'max_tokens' => 1024,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature-to-Provider Mapping
    |--------------------------------------------------------------------------
    */
    'features' => [
        'chat' => 'openai',
        'recommendation' => 'openai',
        'search' => 'openai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    */
    'chat' => [
        'system_prompt' => "You are PageTurner AI, a friendly and knowledgeable bookstore assistant for the PageTurner Online Bookstore. You help customers find books, answer questions about orders, provide book recommendations, and assist with any bookstore-related inquiries. You have access to the store's book catalog and can search for specific titles, authors, genres, and prices. Always be helpful, concise, and enthusiastic about books. If you don't know something, say so honestly. Format your responses nicely with line breaks where appropriate.",
        'max_history' => 20,
        'response_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'per_user_per_minute' => 10,
        'per_user_per_day' => 100,
        'global_per_minute' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    */
    'cost_tracking' => [
        'enabled' => true,
        'alert_threshold_percent' => 80,
    ],
];
