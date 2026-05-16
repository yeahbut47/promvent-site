<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Защита от прямого доступа не с сайта
$allowed_referers = ['localhost', 'promvent.loc', '127.0.0.1'];
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$is_valid_referer = false;
foreach ($allowed_referers as $allowed) {
    if (strpos($referer, $allowed) !== false) {
        $is_valid_referer = true;
        break;
    }
}

// Для локальной разработки можно отключить проверку
if (!$is_valid_referer && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    echo json_encode(['success' => false, 'message' => 'Неверный источник']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Лимит запросов от одного IP (простая защита от флуда)
$ip = $_SERVER['REMOTE_ADDR'];
$limit_file = __DIR__ . '/request_limits.json';
$limits = file_exists($limit_file) ? json_decode(file_get_contents($limit_file), true) : [];
$now = time();

if (isset($limits[$ip])) {
    // Очищаем старые записи (старше 1 часа)
    $limits[$ip] = array_filter($limits[$ip], function($t) use ($now) {
        return $t > $now - 3600;
    });
    
    if (count($limits[$ip]) > 20) {
        sendResponse(false, 'Слишком много запросов. Попробуйте позже.');
        return;
    }
}
$limits[$ip][] = $now;
file_put_contents($limit_file, json_encode($limits));

try {
    switch ($action) {
        case 'send_cta':
            handleCtaForm();
            break;
        case 'send_faq':
            handleFaqForm();
            break;
        case 'send_callback':
            handleCallbackForm();
            break;
        default:
            sendResponse(false, 'Неизвестное действие');
    }
} catch (Exception $e) {
    sendResponse(false, 'Внутренняя ошибка сервера');
}

function sendResponse($success, $message, $extra = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($extra)) {
        $response = array_merge($response, $extra);
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

function handleCtaForm() {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    
    if (empty($name) || strlen($name) < 2) {
        sendResponse(false, 'Введите корректное имя');
        return;
    }
    
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10) {
        sendResponse(false, 'Введите корректный номер телефона');
        return;
    }
    
    if (empty($service)) {
        sendResponse(false, 'Выберите тип услуги');
        return;
    }
    
    $leadData = [
        'type' => 'cta',
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8')
    ];
    
    $result = sendNewLeadNotification($leadData);
    
    if ($result) {
        sendResponse(true, 'Заявка принята! Специалист свяжется с вами в ближайшее время.');
    } else {
        sendResponse(false, 'Ошибка при сохранении заявки');
    }
}

function handleFaqForm() {
    $question = trim($_POST['question'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (strlen($question) < 10) {
        sendResponse(false, 'Вопрос должен содержать минимум 10 символов');
        return;
    }
    
    if (empty($name) || strlen($name) < 2) {
        sendResponse(false, 'Введите корректное имя');
        return;
    }
    
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10) {
        sendResponse(false, 'Введите корректный номер телефона');
        return;
    }
    
    $leadData = [
        'type' => 'faq',
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        'question' => htmlspecialchars($question, ENT_QUOTES, 'UTF-8')
    ];
    
    $result = sendNewLeadNotification($leadData);
    
    if ($result) {
        sendResponse(true, 'Вопрос отправлен! Наш специалист ответит вам в ближайшее время.');
    } else {
        sendResponse(false, 'Ошибка при отправке вопроса');
    }
}

function handleCallbackForm() {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name) || strlen($name) < 2) {
        sendResponse(false, 'Введите корректное имя');
        return;
    }
    
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10) {
        sendResponse(false, 'Введите корректный номер телефона');
        return;
    }
    
    $leadData = [
        'type' => 'callback',
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8')
    ];
    
    $result = sendNewLeadNotification($leadData);
    
    if ($result) {
        sendResponse(true, 'Заявка принята! Мы перезвоним вам в ближайшее время.');
    } else {
        sendResponse(false, 'Ошибка при сохранении заявки');
    }
}
?>