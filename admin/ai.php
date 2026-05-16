<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'chat') {
    $message = mb_strtolower(trim($_POST['message'] ?? ''));
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'response' => 'Введите сообщение']);
        exit;
    }
    
    // Получаем статистику заявок
    $leadsDir = __DIR__ . '/../leads';
    $files = glob($leadsDir . '/*.json');
    
    $stats = ['total' => 0, 'cta' => 0, 'faq' => 0, 'callback' => 0, 'today' => 0];
    $today = date('Y-m-d');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content) {
            $leads = json_decode($content, true);
            if ($leads) {
                foreach ($leads as $lead) {
                    $stats['total']++;
                    if (isset($lead['type'])) {
                        if ($lead['type'] === 'cta') $stats['cta']++;
                        elseif ($lead['type'] === 'faq') $stats['faq']++;
                        elseif ($lead['type'] === 'callback') $stats['callback']++;
                    }
                    if (isset($lead['timestamp']) && strpos($lead['timestamp'], $today) === 0) {
                        $stats['today']++;
                    }
                }
            }
        }
    }
    
    $response = getSmartResponse($message, $stats);
    echo json_encode(['success' => true, 'response' => $response]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);

function getSmartResponse($message, $stats) {
    // Синонимы для разных команд
    $statSynonyms = ['статистик', 'сколько', 'всего', 'покажи', 'статы', 'стата', 'цифры', 'отчет'];
    $todaySynonyms = ['сегодня', 'за сегодня', 'сегодняш', 'новые', 'новых'];
    $helpSynonyms = ['помощь', 'что ты умеешь', 'команды', 'help', 'возможности'];
    $analysisSynonyms = ['анализ', 'проанализируй', 'разбери', 'оцени', 'как дела'];
    
    // Помощь
    foreach ($helpSynonyms as $word) {
        if (strpos($message, $word) !== false) {
            return "🤖 Мои команды:\n\nстатистика - общая статистика\nсегодня - заявки за сегодня\nанализ - анализ и рекомендации\nпомощь - эта справка\n\nПросто напишите слово!";
        }
    }
    
    // Статистика общая
    foreach ($statSynonyms as $word) {
        if (strpos($message, $word) !== false) {
            $ctaPercent = $stats['total'] > 0 ? round($stats['cta'] / $stats['total'] * 100) : 0;
            $faqPercent = $stats['total'] > 0 ? round($stats['faq'] / $stats['total'] * 100) : 0;
            
            return "📊 СТАТИСТИКА ЗАЯВОК\n━━━━━━━━━━━━━━━━━━━━━━━━━\n📋 Всего: {$stats['total']}\n📅 За сегодня: {$stats['today']}\n━━━━━━━━━━━━━━━━━━━━━━━━━\n🔧 На ремонт: {$stats['cta']} ({$ctaPercent}%)\n❓ Вопросы: {$stats['faq']} ({$faqPercent}%)\n📞 Обратный звонок: {$stats['callback']}\n━━━━━━━━━━━━━━━━━━━━━━━━━";
        }
    }
    
    // За сегодня
    foreach ($todaySynonyms as $word) {
        if (strpos($message, $word) !== false) {
            if ($stats['today'] == 0) {
                return "📅 За сегодня заявок не поступало.\n\nПока тихо. Обычно заявки приходят в рабочее время.";
            } elseif ($stats['today'] == 1) {
                return "📅 За сегодня поступила 1 заявка.\n\nПроверьте её в таблице выше!";
            } else {
                return "📅 За сегодня поступило {$stats['today']} заявок.\n\nОтличная активность! Не забудьте их обработать.";
            }
        }
    }
    
    // Анализ
    foreach ($analysisSynonyms as $word) {
        if (strpos($message, $word) !== false) {
            if ($stats['total'] == 0) {
                return "Нет данных для анализа. Пока нет ни одной заявки.";
            }
            
            $ctaPercent = round($stats['cta'] / $stats['total'] * 100);
            
            if ($ctaPercent > 60) {
                return "АНАЛИЗ ЗАЯВОК\n\n$ctaPercent% заявок - на ремонт!\n\nРекомендация: обрабатывайте заявки на ремонт в первую очередь - это самые горячие клиенты.";
            } elseif ($stats['faq'] > $stats['cta']) {
                return "АНАЛИЗ ЗАЯВОК\n\nВопросов больше, чем заявок на ремонт.\n\nРекомендация: добавьте на сайт больше информации о ценах и сроках.";
            } else {
                return "АНАЛИЗ ЗАЯВОК\n\nСбалансированный поток: {$stats['cta']} заявок на ремонт, {$stats['faq']} вопросов.\n\nВсё идёт хорошо. Продолжайте в том же духе!";
            }
        }
    }
    
    // Если ничего не распознали
    return "Я не понял вопрос.\n\nВот что я умею:\nстатистика - общая статистика\nсегодня - заявки за сегодня\nанализ - анализ и рекомендации\nпомощь - список команд\n\nНапишите одно из слов!";
}
?>