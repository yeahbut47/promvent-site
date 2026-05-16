<?php
// Настройки сайта
define('SITE_NAME', 'ПромВент');
define('ADMIN_EMAIL', 'info@promvent.ru');
define('ADMIN_PHONE', '+79999999999');

// Режим отладки
define('DEBUG', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Функция для сохранения заявок в файл
function saveLeadToFile($leadData) {
    $logDir = __DIR__ . '/../leads';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $filename = $logDir . '/leads_' . date('Y-m-d') . '.json';
    
    $leads = [];
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        if ($content) {
            $leads = json_decode($content, true) ?: [];
        }
    }
    
    $leadData['timestamp'] = date('Y-m-d H:i:s');
    $leadData['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $leadData['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $leads[] = $leadData;
    
    file_put_contents($filename, json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if (DEBUG) {
        error_log("Lead saved to file: " . print_r($leadData, true));
    }
    
    return true;
}

// Функция отправки уведомления о новой заявке
function sendNewLeadNotification($leadData) {
    // Просто сохраняем в файл
    return saveLeadToFile($leadData);
}

function getTypeName($type) {
    $types = [
        'cta' => 'Заявка на ремонт',
        'faq' => 'Вопрос из FAQ',
        'callback' => 'Обратный звонок'
    ];
    return $types[$type] ?? $type;
}
?>