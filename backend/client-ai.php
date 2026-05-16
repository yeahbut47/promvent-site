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
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'response' => 'Введите сообщение']);
        exit;
    }
    
    $response = callYandexGPT($message);
    echo json_encode(['success' => true, 'response' => $response]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);

function callYandexGPT($message) {
    // Системный промпт с информацией о компании
    $systemPrompt = "Ты — виртуальный помощник компании ПромВент. Ты общаешься с клиентами и помогаешь им.

ИНФОРМАЦИЯ О КОМПАНИИ:
- Название: ПромВент
- Услуги: ремонт промышленной вентиляции, настройка и ремонт автоматики, ремонт промышленных кондиционеров (чиллеры, фанкойлы)
- Опыт работы: с 2010 года
- Сотрудников: 15 инженеров
- Выполнено проектов: более 500

ЦЕНЫ:
- Диагностика: от 5 000 ₽
- Выезд специалиста: от 3 000 ₽
- Ремонт вентиляции: от 15 000 ₽
- Ремонт автоматики: от 10 000 ₽
- Ремонт кондиционеров: от 12 000 ₽

СРОКИ:
- Аварийный выезд: за 2 часа
- Простой ремонт: от 1 дня
- Сложный ремонт: 3-5 дней
- Запчасти под заказ: 2-5 дней

ГАРАНТИЯ:
- Ремонт вентиляции: до 24 месяцев
- Ремонт автоматики: до 18 месяцев
- Ремонт кондиционеров: до 18 месяцев

КОНТАКТЫ:
- Телефон: +7 (999) 999-99-99
- Email: info@promvent.ru
- Режим работы: Пн-Пт 9:00-20:00, Сб-Вс 10:00-18:00

ПРОИЗВОДИТЕЛИ:
- Вентиляция: Systemair, Ventmachine, Веза, Ostberg, Ruck
- Автоматика: Siemens, Schneider Electric, ABB, Овен
- Кондиционеры: Carrier, Daikin, Trane, York, Hitachi

ПРАВИЛА ОТВЕТОВ:
1. Отвечай на русском языке, естественно, как живой консультант
2. Будь дружелюбным, используй эмодзи
3. Отвечай кратко (2-4 предложения)
4. Если клиент спрашивает цену — назови базовую и предложи оставить заявку
5. Если клиент хочет заказать услугу — предложи оставить заявку или позвонить
6. Всегда предлагай помощь и дальнейшие действия

Ответь клиенту на вопрос: $message";

    $data = [
        'modelUri' => 'gpt://' . YANDEX_FOLDER_ID . '/yandexgpt-lite',
        'completionOptions' => [
            'stream' => false,
            'temperature' => 0.7,
            'maxTokens' => 500
        ],
        'messages' => [
            ['role' => 'user', 'text' => $systemPrompt]
        ]
    ];
    
    $ch = curl_init(YANDEX_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Api-Key ' . YANDEX_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Логируем для отладки
    error_log("YandexGPT HTTP: $httpCode");
    error_log("YandexGPT Response: " . substr($response, 0, 500));
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if (isset($result['result']['alternatives'][0]['message']['text'])) {
            return $result['result']['alternatives'][0]['message']['text'];
        }
    }
    
    // Если YandexGPT не работает, используем умный fallback
    return smartFallback($message);
}

function smartFallback($message) {
    $message = mb_strtolower($message);
    
    // Приветствия
    if (strpos($message, 'привет') !== false || strpos($message, 'здравствуй') !== false) {
        return "👋 Здравствуйте! Я помощник ПромВент. Чем могу помочь?\n\n" .
               "Я могу рассказать об услугах, ценах, сроках или помочь с заявкой.";
    }
    
    // Услуги
    if (strpos($message, 'услуг') !== false || strpos($message, 'ремонтируете') !== false) {
        return "🔧 Мы ремонтируем:\n\n" .
               "• Промышленную вентиляцию\n" .
               "• Автоматику и щиты управления\n" .
               "• Промышленные кондиционеры (чиллеры, фанкойлы)\n\n" .
               "Какой у вас тип оборудования?";
    }
    
    // Цены
    if (strpos($message, 'цен') !== false || strpos($message, 'стоим') !== false) {
        return "💰 Ориентировочные цены:\n\n" .
               "• Диагностика: от 5 000 ₽\n" .
               "• Выезд специалиста: от 3 000 ₽\n" .
               "• Ремонт: от 10 000 до 50 000 ₽\n\n" .
               "Точную стоимость скажем после диагностики. Оставить заявку?";
    }
    
    // Сроки
    if (strpos($message, 'срок') !== false || strpos($message, 'долго') !== false) {
        return "⏱️ Сроки работ:\n\n" .
               "• Аварийный выезд: за 2 часа\n" .
               "• Обычный ремонт: 1-3 дня\n" .
               "• Сложный ремонт: 3-5 дней\n\n" .
               "Уточните, какое оборудование нужно отремонтировать?";
    }
    
    // Контакты
    if (strpos($message, 'контакт') !== false || strpos($message, 'телефон') !== false) {
        return "📞 Связаться с нами:\n\n" .
               "• Телефон: +7 (999) 999-99-99\n" .
               "• Email: info@promvent.ru\n" .
               "• Режим работы: 9:00-20:00 ежедневно\n\n" .
               "Можете оставить заявку — мы перезвоним за 5 минут!";
    }
    
    // Заявка
    if (strpos($message, 'заявк') !== false || strpos($message, 'оставить') !== false) {
        return "📝 Чтобы оставить заявку:\n\n" .
               "1. Нажмите кнопку «Оставить заявку» на сайте\n" .
               "2. Заполните форму в разделе «Готовы начать ремонт?»\n" .
               "3. Позвоните нам: +7 (999) 999-99-99\n\n" .
               "Мы свяжемся с вами в течение 5 минут!";
    }
    
    // Спасибо
    if (strpos($message, 'спасиб') !== false) {
        return "😊 Пожалуйста! Обращайтесь, если будут ещё вопросы.\n\n" .
               "Хотите оставить заявку или уточнить что-то ещё?";
    }
    
    // Если ничего не подошло
    return "🤔 Я вас услышал.\n\n" .
           "Что именно вас интересует?\n" .
           "• Услуги компании\n" .
           "• Цены и стоимость\n" .
           "• Сроки выполнения\n" .
           "• Контакты\n" .
           "• Как оставить заявку\n\n" .
           "Или позвоните нам: +7 (999) 999-99-99";
}
?>