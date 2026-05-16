<?php
session_start();

// Простая авторизация через PHP
$valid_user = 'admin';
$valid_pass = 'PromVent2026';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $valid_user || 
    $_SERVER['PHP_AUTH_PW'] !== $valid_pass) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied';
    exit;
}

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$leadsDir = __DIR__ . '/../leads';

if (!is_dir($leadsDir)) {
    mkdir($leadsDir, 0777, true);
}

$files = glob($leadsDir . '/*.json');
rsort($files);

$allLeads = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content) {
        $leads = json_decode($content, true);
        if ($leads) {
            foreach ($leads as $lead) {
                $allLeads[] = $lead;
            }
        }
    }
}

usort($allLeads, function($a, $b) {
    $timeA = strtotime($a['timestamp'] ?? $a['date'] ?? '1970-01-01');
    $timeB = strtotime($b['timestamp'] ?? $b['date'] ?? '1970-01-01');
    return $timeB - $timeA;
});

$today = date('Y-m-d');
$todayCount = 0;
$ctaCount = 0;
$faqCount = 0;
$callbackCount = 0;
foreach ($allLeads as $lead) {
    if (isset($lead['type'])) {
        if ($lead['type'] === 'cta') $ctaCount++;
        elseif ($lead['type'] === 'faq') $faqCount++;
        elseif ($lead['type'] === 'callback') $callbackCount++;
    }
    if (isset($lead['timestamp']) && strpos($lead['timestamp'], $today) === 0) $todayCount++;
}

$csrf_token = $_SESSION['csrf_token'];
?>

<?php
$leadsDir = __DIR__ . '/../leads';

if (!is_dir($leadsDir)) {
    mkdir($leadsDir, 0777, true);
}

$files = glob($leadsDir . '/*.json');
rsort($files);

$allLeads = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content) {
        $leads = json_decode($content, true);
        if ($leads) {
            foreach ($leads as $lead) {
                $allLeads[] = $lead;
            }
        }
    }
}

usort($allLeads, function($a, $b) {
    $timeA = strtotime($a['timestamp'] ?? $a['date'] ?? '1970-01-01');
    $timeB = strtotime($b['timestamp'] ?? $b['date'] ?? '1970-01-01');
    return $timeB - $timeA;
});

// Подсчитываем статистику для JS
$today = date('Y-m-d');
$todayCount = 0;
$ctaCount = 0;
$faqCount = 0;
$callbackCount = 0;
foreach ($allLeads as $lead) {
    if (isset($lead['type'])) {
        if ($lead['type'] === 'cta') $ctaCount++;
        elseif ($lead['type'] === 'faq') $faqCount++;
        elseif ($lead['type'] === 'callback') $callbackCount++;
    }
    if (isset($lead['timestamp']) && strpos($lead['timestamp'], $today) === 0) $todayCount++;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки - ПромВент Админ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #1f6392, #0e4a70); color: white; padding: 20px 30px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .header h1 { font-size: 1.8rem; display: flex; align-items: center; gap: 10px; }
        .stats { background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-size: 1.2rem; }
        .filters { background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .filters label { font-weight: 600; color: #333; }
        select, button { padding: 8px 16px; border-radius: 8px; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 14px; }
        button { background: #1f6392; color: white; border: none; transition: all 0.3s; }
        button:hover { background: #0e4a70; transform: translateY(-1px); }
        .refresh-btn { margin-left: auto; }
        .table-wrapper { background: white; border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; position: sticky; top: 0; }
        tr:hover { background: #f8f9fa; }
        .type-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .type-cta { background: #e3f2fd; color: #1976d2; }
        .type-faq { background: #fff3e0; color: #f57c00; }
        .type-callback { background: #e8f5e9; color: #388e3c; }
        .view-btn { background: #1f6392; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; }
        .view-btn:hover { background: #0e4a70; }
        .details-preview { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #666; font-size: 13px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(4px); }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 20px; max-width: 600px; width: 90%; max-height: 85vh; overflow-y: auto; animation: modalIn 0.3s ease; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-header { background: linear-gradient(135deg, #1f6392, #0e4a70); color: white; padding: 20px 25px; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { font-size: 1.4rem; display: flex; align-items: center; gap: 10px; }
        .modal-close { background: none; border: none; color: white; font-size: 28px; cursor: pointer; }
        .modal-body { padding: 25px; }
        .detail-row { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: 700; color: #1f6392; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .detail-value { color: #333; font-size: 16px; line-height: 1.5; }
        .detail-value.large { background: #f8f9fa; padding: 12px; border-radius: 10px; font-family: monospace; white-space: pre-wrap; }
        .footer-buttons { display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .delete-btn { background: #dc3545; color: white; }
        .delete-btn:hover { background: #c82333; }
        .empty-state { text-align: center; padding: 60px; color: #999; }
        
        /* AI Assistant */
        .admin-ai-btn { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: linear-gradient(135deg, #1f6392, #0e4a70); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 20px rgba(31, 99, 146, 0.4); z-index: 10000; border: none; transition: all 0.3s ease; }
        .admin-ai-btn:hover { transform: scale(1.05); }
        .admin-ai-btn i { font-size: 32px; color: white; }
        .admin-ai-badge { position: absolute; top: -5px; right: -5px; background: #10b981; color: white; font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 20px; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .admin-ai-window { position: fixed; bottom: 100px; right: 30px; width: 380px; height: 520px; background: white; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); display: none; flex-direction: column; overflow: hidden; z-index: 10001; opacity: 0; transform: translateY(20px) scale(0.95); transition: all 0.3s ease; }
        .admin-ai-window.open { display: flex; opacity: 1; transform: translateY(0) scale(1); }
        .admin-ai-header { background: linear-gradient(135deg, #1f6392, #0e4a70); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-ai-header h3 { font-size: 16px; display: flex; align-items: center; gap: 8px; margin: 0; }
        .admin-ai-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; opacity: 0.8; }
        .admin-ai-close:hover { opacity: 1; }
        .admin-ai-messages { flex: 1; overflow-y: auto; padding: 15px; background: #f8fafc; display: flex; flex-direction: column; gap: 12px; }
        .admin-message { display: flex; gap: 10px; animation: fadeIn 0.3s ease; }
        .admin-message.user { flex-direction: row-reverse; }
        .admin-message-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 16px; }
        .admin-message.bot .admin-message-avatar { background: #e2e8f0; }
        .admin-message.user .admin-message-avatar { background: #1f6392; color: white; }
        .admin-message-content { max-width: 75%; padding: 10px 14px; border-radius: 18px; font-size: 13px; line-height: 1.5; white-space: pre-line; }
        .admin-message.bot .admin-message-content { background: white; color: #1e2a3a; border-radius: 18px 18px 18px 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .admin-message.user .admin-message-content { background: #1f6392; color: white; border-radius: 18px 18px 4px 18px; }
        .admin-ai-quick { padding: 12px 15px; background: #ffffff; border-top: 1px solid #eef2f6; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .admin-quick-btn { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; padding: 8px 18px; border-radius: 40px; font-size: 12px; font-weight: 600; cursor: pointer; color: #1f6392; transition: all 0.25s ease; }
        .admin-quick-btn:hover { background: linear-gradient(135deg, #1f6392 0%, #0e4a70 100%); border-color: #1f6392; color: white; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(31, 99, 146, 0.3); }
        .admin-ai-input { padding: 15px; background: white; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; }
        .admin-ai-input input { flex: 1; padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 25px; outline: none; font-size: 14px; }
        .admin-ai-input input:focus { border-color: #1f6392; }
        .admin-ai-input button { width: 40px; height: 40px; background: #1f6392; border: none; border-radius: 50%; color: white; cursor: pointer; font-size: 16px; }
        .admin-ai-input button:hover { background: #0e4a70; transform: scale(1.05); }
        .admin-ai-typing { padding: 8px 15px; display: none; gap: 4px; }
        .admin-ai-typing span { width: 8px; height: 8px; background: #1f6392; border-radius: 50%; display: inline-block; animation: typingAnim 1.4s infinite; }
        .admin-ai-typing span:nth-child(2) { animation-delay: 0.2s; }
        .admin-ai-typing span:nth-child(3) { animation-delay: 0.4s; }
        .admin-ai-typing.active { display: flex; }
        @keyframes typingAnim { 0%, 60%, 100% { transform: translateY(0); opacity: 0.4; } 30% { transform: translateY(-6px); opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 500px) { .admin-ai-window { width: calc(100vw - 40px); right: 20px; height: 480px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Заявки с сайта ПромВент</h1>
        <div class="stats">📊 Всего: <?= count($allLeads) ?></div>
    </div>
    
    <div class="filters">
        <label>🔍 Фильтр по типу:</label>
        <select id="typeFilter">
            <option value="all">📋 Все заявки</option>
            <option value="cta">🔧 Заявки на ремонт</option>
            <option value="faq">❓ Вопросы</option>
            <option value="callback">📞 Обратный звонок</option>
        </select>
        <button onclick="filterTable()">Применить</button>
        <button onclick="location.reload()" class="refresh-btn">🔄 Обновить</button>
        <button onclick="exportToCSV()">📎 Экспорт в CSV</button>
    </div>
    
    <div class="table-wrapper">
        <table id="leadsTable">
            <thead><tr><th>🕐 Время</th><th>📌 Тип</th><th>👤 Имя</th><th>📱 Телефон</th><th>📝 Описание</th><th>🌐 IP</th><th>📖 Детали</th></tr></thead>
            <tbody>
                <?php if (empty($allLeads)): ?>
                    <tr><td colspan="7" class="empty-state">📭 Нет заявок</td></tr>
                <?php else: ?>
                    <?php foreach ($allLeads as $index => $lead): ?>
                    <tr class="type-<?= htmlspecialchars($lead['type'] ?? 'unknown') ?>">
                        <td><?= htmlspecialchars($lead['timestamp'] ?? $lead['date'] ?? '-') ?></td>
                        <td><span class="type-badge type-<?= htmlspecialchars($lead['type'] ?? 'unknown') ?>"><?= getTypeName($lead['type'] ?? 'unknown') ?></span></td>
                        <td><?= htmlspecialchars($lead['name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($lead['phone'] ?? '-') ?></td>
                        <td class="details-preview"><?= htmlspecialchars(mb_substr($lead['service'] ?? $lead['question'] ?? '-', 0, 50)) ?></td>
                        <td><?= htmlspecialchars($lead['ip'] ?? '-') ?></td>
                        <td><button class="view-btn" onclick="showLeadDetails(<?= $index ?>)">📖 Подробнее</button></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Модальное окно -->
<div id="leadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h2><span>📄</span><span id="modalTitle">Детали заявки</span></h2><button class="modal-close" onclick="closeModal()">&times;</button></div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<!-- AI Ассистент с поддержкой ИИ -->
<button class="admin-ai-btn" id="adminAiBtn">
    <i class="bi bi-robot"></i>
    <span class="admin-ai-badge">AI</span>
</button>

<div class="admin-ai-window" id="adminAiWindow">
    <div class="admin-ai-header">
        <h3><i class="bi bi-robot"></i> AI Ассистент (нейросеть)</h3>
        <button class="admin-ai-close" id="adminAiClose">×</button>
    </div>
    <div class="admin-ai-messages" id="adminAiMessages">
        <div class="admin-message bot">
            <div class="admin-message-avatar">🤖</div>
            <div class="admin-message-content">👋 Привет! Я AI-ассистент на базе нейросети.<br><br>Я могу:<br>• 📊 Показать статистику<br>• 📅 Заявки за сегодня<br>• 🔍 Анализировать данные<br>• 💬 Отвечать на любые вопросы<br><br>Спросите меня о чём угодно!</div>
        </div>
    </div>
    <div class="admin-ai-quick">
        <button class="admin-quick-btn" data-question="статистика">📊 Статистика</button>
        <button class="admin-quick-btn" data-question="сегодня">📅 За сегодня</button>
        <button class="admin-quick-btn" data-question="анализ">🔍 Анализ</button>
        <button class="admin-quick-btn" data-question="помощь">❓ Помощь</button>
    </div>
    <div class="admin-ai-typing" id="adminAiTyping"><span></span><span></span><span></span></div>
    <div class="admin-ai-input">
        <input type="text" id="adminAiInput" placeholder="Спросите меня о чём угодно...">
        <button id="adminAiSendBtn"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<script>
const leadsData = <?php echo json_encode($allLeads, JSON_UNESCAPED_UNICODE); ?>;
const statsData = {
    total: <?= count($allLeads) ?>,
    today: <?= $todayCount ?>,
    cta: <?= $ctaCount ?>,
    faq: <?= $faqCount ?>,
    callback: <?= $callbackCount ?>
};

function showLeadDetails(index) {
    const lead = leadsData[index];
    if (!lead) return;
    const modal = document.getElementById('leadModal');
    document.getElementById('modalTitle').innerHTML = getTypeIcon(lead.type) + ' ' + getTypeName(lead.type);
    let html = `<div class="detail-row"><div class="detail-label">🕐 Дата</div><div class="detail-value">${escapeHtml(lead.timestamp || lead.date || '-')}</div></div>
    <div class="detail-row"><div class="detail-label">👤 Имя</div><div class="detail-value">${escapeHtml(lead.name || '-')}</div></div>
    <div class="detail-row"><div class="detail-label">📱 Телефон</div><div class="detail-value">${escapeHtml(lead.phone || '-')}</div></div>`;
    if (lead.service) html += `<div class="detail-row"><div class="detail-label">🛠 Услуга</div><div class="detail-value">${escapeHtml(lead.service)}</div></div>`;
    if (lead.question) html += `<div class="detail-row"><div class="detail-label">❓ Вопрос</div><div class="detail-value large">${escapeHtml(lead.question)}</div></div>`;
    html += `<div class="detail-row"><div class="detail-label">🌐 IP</div><div class="detail-value">${escapeHtml(lead.ip || '-')}</div></div>
    <div class="footer-buttons"><button class="view-btn" onclick="copyLeadInfo(${index})">📋 Копировать</button><button class="view-btn delete-btn" onclick="deleteLead(${index})">🗑 Удалить</button></div>`;
    document.getElementById('modalBody').innerHTML = html;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('leadModal').classList.remove('active');
    document.body.style.overflow = '';
}

function copyLeadInfo(index) {
    const lead = leadsData[index];
    let text = `ЗАЯВКА\nИмя: ${lead.name}\nТелефон: ${lead.phone}\nТип: ${getTypeName(lead.type)}`;
    if (lead.service) text += `\nУслуга: ${lead.service}`;
    if (lead.question) text += `\nВопрос: ${lead.question}`;
    navigator.clipboard.writeText(text).then(() => alert('✅ Скопировано'));
}

function deleteLead(index) {
    if (!confirm('Удалить заявку?')) return;
    
    // Получаем CSRF-токен из мета-тега или переменной
    const csrfToken = '<?= $csrf_token ?>';
    
    fetch('/admin/delete_lead.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'index=' + index + '&csrf_token=' + csrfToken
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            alert('✅ Удалено');
            location.reload();
        } else {
            alert('❌ ' + r.message);
        }
    })
    .catch(e => alert('❌ Ошибка'));
}

function filterTable() {
    const filter = document.getElementById('typeFilter').value;
    document.querySelectorAll('#leadsTable tbody tr').forEach(row=>{row.style.display=(filter==='all'||row.classList.contains('type-'+filter))?'':'none';});
}

function exportToCSV() {
    let csv = "Дата,Тип,Имя,Телефон,Описание,IP\n";
    leadsData.forEach(l=>{csv+=`"${l.timestamp||''}","${getTypeName(l.type)}","${l.name||''}","${l.phone||''}","${l.service||l.question||''}","${l.ip||''}"\n`;});
    const blob=new Blob(["\uFEFF"+csv],{type:'text/csv'});
    const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='leads.csv';a.click();URL.revokeObjectURL(a.href);
}

function escapeHtml(t){if(!t)return '';const d=document.createElement('div');d.textContent=t;return d.innerHTML;}
function getTypeName(t){const types={cta:'Заявка на ремонт',faq:'Вопрос',callback:'Обратный звонок'};return types[t]||t;}
function getTypeIcon(t){const icons={cta:'🔧',faq:'❓',callback:'📞'};return icons[t]||'📋';}

document.getElementById('leadModal').addEventListener('click',e=>{if(e.target===document.getElementById('leadModal'))closeModal();});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

// ========== AI АССИСТЕНТ С ПОДДЕРЖКОЙ OLLAMA (НЕЙРОСЕТЬ) ==========
(function() {
    const aiBtn = document.getElementById('adminAiBtn');
    const aiWindow = document.getElementById('adminAiWindow');
    const aiClose = document.getElementById('adminAiClose');
    const aiInput = document.getElementById('adminAiInput');
    const aiSendBtn = document.getElementById('adminAiSendBtn');
    const aiMessages = document.getElementById('adminAiMessages');
    const aiTyping = document.getElementById('adminAiTyping');
    
    let isOpen = false;
    
    aiBtn.onclick = function() {
        isOpen = !isOpen;
        if (isOpen) {
            aiWindow.classList.add('open');
            aiInput.focus();
        } else {
            aiWindow.classList.remove('open');
        }
    };
    
    aiClose.onclick = function() {
        isOpen = false;
        aiWindow.classList.remove('open');
    };
    
    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `admin-message ${isUser ? 'user' : 'bot'}`;
        messageDiv.innerHTML = `<div class="admin-message-avatar">${isUser ? '👤' : '🤖'}</div><div class="admin-message-content">${text.replace(/\n/g, '<br>')}</div>`;
        aiMessages.appendChild(messageDiv);
        aiMessages.scrollTop = aiMessages.scrollHeight;
    }
    
    // Быстрые ответы без нейросети
    function getQuickResponse(message) {
    const lowerMsg = message.toLowerCase();
    
    // Статистика
    if (lowerMsg.includes('статистик') || lowerMsg.includes('стат') || 
        lowerMsg.includes('сколько заявок') || lowerMsg.includes('всего')) {
        return `📊 Статистика заявок:\n\n📋 Всего заявок: ${statsData.total}\n📅 За сегодня: ${statsData.today}\n🔧 На ремонт: ${statsData.cta}\n❓ Вопросы: ${statsData.faq}\n📞 Обратные звонки: ${statsData.callback}`;
    }
    
    // Сегодня
    if (lowerMsg.includes('сегодня') || lowerMsg.includes('за сегодня')) {
        if (statsData.today == 0) return "📅 За сегодня заявок не поступало.\n\nПока тихо. Обычно заявки приходят в рабочее время (9:00-20:00).";
        if (statsData.today == 1) return "📅 За сегодня поступила 1 заявка.\n\nПроверьте её в таблице выше! Не забудьте обработать.";
        return `📅 За сегодня поступило ${statsData.today} заявок.\n\nОтличная активность! Рекомендую обработать их в порядке очереди.`;
    }
    
    // Анализ
    if (lowerMsg.includes('анализ') || lowerMsg.includes('проанализируй')) {
        if (statsData.total == 0) return "Нет данных для анализа. Пока нет ни одной заявки.";
        const ctaPercent = Math.round(statsData.cta / statsData.total * 100);
        if (ctaPercent > 60) {
            return `🔍 ${ctaPercent}% заявок - на ремонт!\n\n✅ Рекомендация: обрабатывайте заявки на ремонт в первую очередь - это самые горячие клиенты. Перезванивайте в течение 5-10 минут.`;
        } else if (statsData.faq > statsData.cta) {
            return `🔍 Вопросов (${statsData.faq}) больше, чем заявок на ремонт (${statsData.cta}).\n\n✅ Рекомендация: добавьте на сайт больше информации о ценах и сроках, это снизит количество вопросов.`;
        } else {
            return `🔍 Сбалансированный поток: ${statsData.cta} ремонтов, ${statsData.faq} вопросов.\n\n✅ Всё идёт хорошо! Продолжайте в том же духе.`;
        }
    }
    
    // Помощь
    if (lowerMsg.includes('помощь') || lowerMsg.includes('что ты умеешь') || lowerMsg.includes('команды')) {
        return "🤖 Доступные команды:\n\n📊 статистика - общая статистика\n📅 сегодня - заявки за сегодня\n🔍 анализ - анализ и рекомендации\n❓ помощь - эта справка\n\nА также я отвечаю на любые вопросы через нейросеть! Просто спросите.";
    }
    
    return null;
}
    
    // Вызов Ollama (нейросеть)
// Вызов Ollama с моделью llama3.2:3b
async function callOllama(message) {
    const prompt = `Ты - профессиональный AI помощник для администратора сайта ПромВент.

    ВАЖНОЕ ПРАВИЛО: Ты ОБЯЗАН отвечать ТОЛЬКО на РУССКОМ языке. Ни слова на английском, испанском, немецком или любом другом языке. Только русский!

Текущая статистика заявок:
- Всего заявок: ${statsData.total}
- За сегодня: ${statsData.today}
- Заявки на ремонт (CTA): ${statsData.cta}
- Вопросы (FAQ): ${statsData.faq}
- Обратные звонки: ${statsData.callback}

Правила ответа:
1. Отвечай только на русском языке
2. Пиши кратко - 2-3 предложения
3. Будь дружелюбным и полезным
4. Если вопрос про статистику - используй цифры выше
5. Если спрашивают про анализ - дай конкретную рекомендацию

ОТВЕТЬ НА ВОПРОС (ТОЛЬКО НА РУССКОМ ЯЗЫКЕ, НИ СЛОВА НА ДРУГИХ ЯЗЫКАХ):

Вопрос: ${message}

РУССКИЙ ОТВЕТ:`;

    try {
        const response = await fetch('http://localhost:11434/api/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model: 'llama3.2:3b',
                prompt: prompt,
                stream: false,
                temperature: 0.5,
                max_tokens: 300
            })
        });
        
        const data = await response.json();
        
        if (data.response) {
            return data.response.trim();
        } else {
            return "🤔 Не удалось получить ответ. Попробуйте переформулировать вопрос.";
        }
    } catch (error) {
        console.error('Ollama error:', error);
        return "⚠️ Нейросеть временно недоступна.\n\nДоступны команды: статистика, сегодня, анализ, помощь";
    }
}

function isRussian(text) {
    // Проверяем, есть ли в тексте латиница (английские буквы)
    const hasLatin = /[a-zA-Z]/.test(text);
    // Если есть английские буквы - плохо
    if (hasLatin) return false;
    return true;
}

// Измените sendMessage, добавив проверку:
async function sendMessage() {
    const message = aiInput.value.trim();
    if (!message) return;
    
    addMessage(message, true);
    aiInput.value = '';
    aiSendBtn.disabled = true;
    aiTyping.classList.add('active');
    
    const quickResponse = getQuickResponse(message);
    
    if (quickResponse) {
        setTimeout(() => {
            aiTyping.classList.remove('active');
            addMessage(quickResponse, false);
            aiSendBtn.disabled = false;
            aiInput.focus();
        }, 200);
    } else {
        let aiResponse = await callOllama(message);
        
        // Если ответ содержит английские буквы - пробуем ещё раз
        if (!isRussian(aiResponse)) {
            console.log('Не русский ответ, пробуем ещё раз...');
            const retryPrompt = `Ответь на этот вопрос ТОЛЬКО по-русски, без английских слов: ${message}`;
            const retryResponse = await fetch('http://localhost:11434/api/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    model: 'llama3.2:3b',
                    prompt: retryPrompt,
                    stream: false,
                    temperature: 0.3,
                    max_tokens: 200
                })
            });
            const retryData = await retryResponse.json();
            if (retryData.response && isRussian(retryData.response)) {
                aiResponse = retryData.response;
            }
        }
        
        aiTyping.classList.remove('active');
        addMessage(aiResponse, false);
        aiSendBtn.disabled = false;
        aiInput.focus();
    }
}
    
    async function sendMessage() {
        const message = aiInput.value.trim();
        if (!message) return;
        
        addMessage(message, true);
        aiInput.value = '';
        aiSendBtn.disabled = true;
        aiTyping.classList.add('active');
        
        // Сначала проверяем быстрые команды
        const quickResponse = getQuickResponse(message);
        
        if (quickResponse) {
            setTimeout(() => {
                aiTyping.classList.remove('active');
                addMessage(quickResponse, false);
                aiSendBtn.disabled = false;
                aiInput.focus();
            }, 300);
        } else {
            // Если не команда - вызываем нейросеть
            const aiResponse = await callOllama(message);
            aiTyping.classList.remove('active');
            addMessage(aiResponse, false);
            aiSendBtn.disabled = false;
            aiInput.focus();
        }
    }
    
    document.querySelectorAll('.admin-quick-btn').forEach(btn => {
        btn.onclick = function() {
            aiInput.value = this.dataset.question;
            sendMessage();
        };
    });
    
    aiSendBtn.onclick = sendMessage;
    aiInput.onkeypress = function(e) {
        if (e.key === 'Enter') sendMessage();
    };
})();
</script>

<?php
function getTypeName($type) {
    $types = ['cta' => 'Заявка на ремонт', 'faq' => 'Вопрос', 'callback' => 'Обратный звонок'];
    return $types[$type] ?? $type;
}
?>
</body>
</html>