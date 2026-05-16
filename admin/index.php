<?php

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель ПромВент</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a202c, #0f172a);
            color: white;
            padding: 30px 0;
        }
        
        .admin-logo {
            padding: 0 24px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }
        
        .admin-logo h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-nav {
            display: flex;
            flex-direction: column;
        }
        
        .admin-nav a {
            padding: 14px 24px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(31, 99, 146, 0.5);
            color: white;
        }
        
        .admin-nav a i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        /* Main content */
        .admin-main {
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .admin-header h2 {
            font-size: 1.3rem;
            color: #1e2a3a;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .iframe-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: calc(100vh - 160px);
        }
        
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 70px;
            }
            .admin-logo h1 span,
            .admin-nav a span {
                display: none;
            }
            .admin-logo h1 {
                justify-content: center;
            }
            .admin-nav a {
                justify-content: center;
            }
            .admin-main {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-logo">
            <h1>
                <i class="bi bi-building"></i>
                <span>ПромВент</span>
            </h1>
        </div>
        <nav class="admin-nav">
            <a href="leads.php" target="adminFrame" class="active">
                <i class="bi bi-envelope-paper"></i>
                <span>Заявки</span>
            </a>
        </nav>
    </div>
    
    <div class="admin-main">
        <div class="admin-header">
            <h2><i class="bi bi-envelope-paper"></i> Управление заявками</h2>
            <button class="logout-btn" onclick="logout()">
                <i class="bi bi-box-arrow-right"></i> Выход
            </button>
        </div>
        
        <div class="iframe-container">
            <iframe src="leads.php" name="adminFrame" id="adminFrame"></iframe>
        </div>
    </div>
</div>

<script>
    function logout() {
        // Очищаем авторизацию
        location.href = 'logout.php';
    }
</script>
</body>
</html>