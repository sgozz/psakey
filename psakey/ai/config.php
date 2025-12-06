<?php
/**
 * Configurazione AI Chat
 *
 * Modifica questi valori con le tue API key
 */

return [
    // Provider attivo: 'claude', 'openai', 'groq', 'ollama'
    'provider' => 'groq',

    // API Keys (inserisci la tua)
    'api_keys' => [
        'claude' => 'sk-ant-xxxxx',      // https://console.anthropic.com/
        'openai' => 'sk-xxxxx',           // https://platform.openai.com/
        'groq'   => 'gsk_xxxxx',          // https://console.groq.com/ (gratuito!)
    ],

    // Configurazione Ollama (locale, gratuito ma pesante)
    'ollama' => [
        'host' => 'http://localhost:11434',
        'model' => 'llama2',
    ],

    // Modelli per provider
    'models' => [
        'claude' => 'claude-3-haiku-20240307',  // Veloce ed economico
        'openai' => 'gpt-3.5-turbo',            // Economico
        'groq'   => 'llama-3.1-8b-instant',     // Gratuito e veloce!
    ],

    // System prompt (personalizza il comportamento)
    'system_prompt' => "Sei un assistente in auto. Rispondi in modo breve e conciso (max 2-3 frasi). Sei utile per: indicazioni, meteo, informazioni generali. Rispondi sempre in italiano.",

    // Limiti
    'max_tokens' => 150,  // Risposte brevi per lo schermo piccolo
];
