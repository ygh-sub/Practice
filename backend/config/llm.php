<?php

return [
    'use_mock' => env('LLM_USE_MOCK', false),
    'provider' => env('LLM_API_PROVIDER', 'openai'),
    'api_key' => env('LLM_API_KEY'),
    'api_url' => env('LLM_API_URL', 'https://api.openai.com/v1/chat/completions'),
    'model' => env('LLM_API_MODEL', 'gpt-3.5-turbo'),
    'temperature' => (float) env('LLM_API_TEMPERATURE', 0.7),
    'max_tokens' => (int) env('LLM_API_MAX_TOKENS', 300),
];