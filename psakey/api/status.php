<?php
/**
 * API per leggere lo stato delle notifiche
 *
 * Uso: GET /api/status.php
 * Risposta JSON con contatori per app
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// File contatore
$counterFile = '/tmp/psakey_notifications.json';

// Se non esiste, ritorna zero
if (!file_exists($counterFile)) {
    echo json_encode([
        'whatsapp' => 0,
        'telegram' => 0,
        'other' => 0,
        'total' => 0,
        'last_update' => null
    ]);
    exit;
}

// Leggi e ritorna
$data = json_decode(file_get_contents($counterFile), true);
echo json_encode($data);
