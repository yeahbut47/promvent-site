<?php
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] !== 'PromVent2026') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}

// Проверка CSRF-токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'CSRF атака']);
    exit;
}

$leadsDir = __DIR__ . '/../leads';
$index = $_POST['index'] ?? null;

if ($index === null) {
    echo json_encode(['success' => false, 'message' => 'Не указан индекс заявки']);
    exit;
}

// Собираем все заявки
$files = glob($leadsDir . '/*.json');
rsort($files);

$allLeads = [];
$leadFileMap = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content) {
        $leads = json_decode($content, true);
        if ($leads) {
            foreach ($leads as $leadIndex => $lead) {
                $allLeads[] = $lead;
                $leadFileMap[count($allLeads) - 1] = ['file' => $file, 'index' => $leadIndex];
            }
        }
    }
}

if (!isset($leadFileMap[$index])) {
    echo json_encode(['success' => false, 'message' => 'Заявка не найдена']);
    exit;
}

$fileInfo = $leadFileMap[$index];
$file = $fileInfo['file'];
$leadIndexInFile = $fileInfo['index'];

$content = file_get_contents($file);
$leads = json_decode($content, true);

array_splice($leads, $leadIndexInFile, 1);

if (empty($leads)) {
    unlink($file);
} else {
    file_put_contents($file, json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Логирование удаления
$log = date('Y-m-d H:i:s') . " - Удалена заявка индекс $index\n";
file_put_contents(__DIR__ . '/delete_log.txt', $log, FILE_APPEND);

echo json_encode(['success' => true]);
?>