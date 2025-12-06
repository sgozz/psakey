<?php
/**
 * API per ricevere notifiche dal telefono
 *
 * Uso: POST /api/notify.php
 * Parametri:
 *   - action: "add" (nuovo messaggio) | "reset" (azzera contatore) | "set" (imposta valore)
 *   - app: nome app (es. "whatsapp", "telegram") - opzionale
 *   - count: numero messaggi (solo per action=set) - opzionale
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// File contatore (in tmpfs, sopravvive al riavvio solo se non si spegne)
$counterFile = '/tmp/psakey_notifications.json';

// Inizializza contatore se non esiste
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, json_encode([
        'whatsapp' => 0,
        'telegram' => 0,
        'other' => 0,
        'total' => 0,
        'last_update' => null
    ]));
}

// Leggi contatore attuale
$data = json_decode(file_get_contents($counterFile), true);

// Gestisci richiesta
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'add';
$app = isset($_REQUEST['app']) ? strtolower($_REQUEST['app']) : 'whatsapp';

// Normalizza nome app
if (!in_array($app, ['whatsapp', 'telegram'])) {
    $app = 'other';
}

switch ($action) {
    case 'add':
        // Incrementa contatore
        $data[$app]++;
        $data['total']++;
        $data['last_update'] = date('Y-m-d H:i:s');
        break;

    case 'reset':
        // Azzera contatore (quando l'utente ha letto i messaggi)
        if ($app === 'all') {
            $data['whatsapp'] = 0;
            $data['telegram'] = 0;
            $data['other'] = 0;
            $data['total'] = 0;
        } else {
            $data['total'] -= $data[$app];
            $data[$app] = 0;
        }
        $data['last_update'] = date('Y-m-d H:i:s');
        break;

    case 'set':
        // Imposta valore specifico
        $count = isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 0;
        $oldCount = $data[$app];
        $data[$app] = $count;
        $data['total'] = $data['total'] - $oldCount + $count;
        $data['last_update'] = date('Y-m-d H:i:s');
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// Salva contatore
file_put_contents($counterFile, json_encode($data));

// Risposta
echo json_encode([
    'success' => true,
    'data' => $data
]);
