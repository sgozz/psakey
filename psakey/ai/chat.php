<?php
/**
 * AI Chat Proxy
 *
 * Gestisce richieste verso diverse API AI
 * Uso: POST /ai/chat.php con parametro 'message'
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Carica configurazione
$config = require('config.php');

// Leggi messaggio
$message = isset($_REQUEST['message']) ? trim($_REQUEST['message']) : '';

if (empty($message)) {
    echo json_encode(['error' => 'Messaggio vuoto', 'response' => '']);
    exit;
}

// Seleziona provider
$provider = $config['provider'];
$response = '';
$error = '';

try {
    switch ($provider) {
        case 'claude':
            $response = callClaude($message, $config);
            break;
        case 'openai':
            $response = callOpenAI($message, $config);
            break;
        case 'groq':
            $response = callGroq($message, $config);
            break;
        case 'ollama':
            $response = callOllama($message, $config);
            break;
        default:
            $error = 'Provider non supportato';
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

echo json_encode([
    'response' => $response,
    'error' => $error,
    'provider' => $provider
]);

// ============================================
// FUNZIONI PER OGNI PROVIDER
// ============================================

function callClaude($message, $config) {
    $apiKey = $config['api_keys']['claude'];
    $model = $config['models']['claude'];

    $data = [
        'model' => $model,
        'max_tokens' => $config['max_tokens'],
        'system' => $config['system_prompt'],
        'messages' => [
            ['role' => 'user', 'content' => $message]
        ]
    ];

    $result = httpPost(
        'https://api.anthropic.com/v1/messages',
        $data,
        [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json'
        ]
    );

    $json = json_decode($result, true);
    if (isset($json['content'][0]['text'])) {
        return $json['content'][0]['text'];
    }
    if (isset($json['error'])) {
        throw new Exception($json['error']['message']);
    }
    return '';
}

function callOpenAI($message, $config) {
    $apiKey = $config['api_keys']['openai'];
    $model = $config['models']['openai'];

    $data = [
        'model' => $model,
        'max_tokens' => $config['max_tokens'],
        'messages' => [
            ['role' => 'system', 'content' => $config['system_prompt']],
            ['role' => 'user', 'content' => $message]
        ]
    ];

    $result = httpPost(
        'https://api.openai.com/v1/chat/completions',
        $data,
        [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]
    );

    $json = json_decode($result, true);
    if (isset($json['choices'][0]['message']['content'])) {
        return $json['choices'][0]['message']['content'];
    }
    if (isset($json['error'])) {
        throw new Exception($json['error']['message']);
    }
    return '';
}

function callGroq($message, $config) {
    $apiKey = $config['api_keys']['groq'];
    $model = $config['models']['groq'];

    $data = [
        'model' => $model,
        'max_tokens' => $config['max_tokens'],
        'messages' => [
            ['role' => 'system', 'content' => $config['system_prompt']],
            ['role' => 'user', 'content' => $message]
        ]
    ];

    $result = httpPost(
        'https://api.groq.com/openai/v1/chat/completions',
        $data,
        [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]
    );

    $json = json_decode($result, true);
    if (isset($json['choices'][0]['message']['content'])) {
        return $json['choices'][0]['message']['content'];
    }
    if (isset($json['error'])) {
        throw new Exception($json['error']['message']);
    }
    return '';
}

function callOllama($message, $config) {
    $host = $config['ollama']['host'];
    $model = $config['ollama']['model'];

    $data = [
        'model' => $model,
        'prompt' => $config['system_prompt'] . "\n\nUtente: " . $message . "\n\nAssistente:",
        'stream' => false
    ];

    $result = httpPost(
        $host . '/api/generate',
        $data,
        ['Content-Type: application/json']
    );

    $json = json_decode($result, true);
    if (isset($json['response'])) {
        return $json['response'];
    }
    return '';
}

// ============================================
// UTILITY
// ============================================

function httpPost($url, $data, $headers) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('Errore connessione: ' . $error);
    }

    return $result;
}
