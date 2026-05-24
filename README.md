# promvent-site

# ПромВент — Сайт-визитка для компании по ремонту промышленной вентиляции, автоматики и кондиционеров

## 📋 О проекте
Корпоративный сайт для компании, специализирующейся на ремонте промышленной вентиляции, автоматики и кондиционеров. Проект разрабатывается в рамках лабораторных работ по курсу "Коллективная разработка ПО".

**Цель проекта:** Создать сайт с одной страницей для привлечения новых клиентов, демонстрации экспертизы компании и сбора заявок.

## 🔗 Полезные ссылки
- **Документация проекта (Wiki):** [[ссылка на Яндекс Wiki]](https://wiki.yandex.ru/_i/#2ad29ea8-aae4-4856-846b-5cf8a5a71532)
- **Макеты в Figma:** [[ссылка на Figma]](https://www.figma.com/design/fkIbpIghULpHXA2fmXWX8W/Prototyping-in-Figma?node-id=0-1&t=VDFqg6F5u7AlF2P6-1)
- **Доска задач (Яндекс Трекер):** [[ссылка на доску]](https://tracker.yandex.ru/pages/projects/1) 

## 👥 Команда
| Роль | Имя |
|------|-----|
| Project Manager | Степанов Кирилл |
| UI/UX-дизайнер | Калиниченко Юлия |
| Backend-разработчик | Протасова Юлия |

## 🛠 Технологии

- **Фронтенд:** HTML, CSS, JavaScript
- **Бэкенд:** PHP 8.2
- **Хранилище заявок:** JSON-файлы (папка `leads/`)
- **ИИ-ассистент:** Ollama (llama3.2:3b, локально)
- **Хостинг:** GitHub Pages (статика) + PHP-сервер для бэкенда
- **CI/CD:** GitHub Actions

## 🚀 Быстрый старт

### Требования

- PHP >= 7.4 ([скачать для Windows](https://windows.php.net/download/), версия Thread Safe x64)
- Ollama ([скачать](https://ollama.com/download/windows)) — опционально, для AI-ассистента

### Установка и запуск

```bash
# 1. Клонировать репозиторий
git clone https://github.com/julia-myaso00/promvent-site.git
cd promvent-site

# 2. Создать папку для заявок
mkdir leads

# 3. Запустить локальный PHP-сервер
php -S localhost:8000
```

Сайт будет доступен по адресу:

| Страница     | URL                                    |
|--------------|----------------------------------------|
| Главная      | http://localhost:8000                  |
| Админ-панель | http://localhost:8000/admin/leads.php  |

Логин для админки: `admin` / `PromVent2026`

### Сборка проекта

Проект не требует компиляции. Команда сборки проверяет наличие всех критических файлов:

```bash
php -r "
\$files = ['index.php','admin/leads.php','backend/api.php','backend/config.php'];
foreach (\$files as \$f) {
    echo (file_exists(\$f) ? '[OK] ' : '[MISSING] ') . \$f . PHP_EOL;
}
"
```

Или через composer:

```bash
composer run build
```

### Запуск тестов

```bash
# Через PHP напрямую
php tests/run_tests.php

# Через composer
composer run test
```

Тесты не требуют запущенного сервера и выполняются полностью локально.

### Линтинг (проверка синтаксиса PHP)

```bash
composer run lint
```

Или вручную:

```bash
find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {}
```

## 🔄 CI/CD

Проект использует GitHub Actions. При каждом `push` и `pull_request` автоматически:

1. Проверяется синтаксис всех PHP-файлов
2. Запускаются unit-тесты
3. Выполняются smoke-тесты (проверка доступности страниц)
4. При мерже в `main` — автоматический деплой на GitHub Pages

Подробнее: [CI/CD Pipeline](docs/ci-cd-pipeline.md)

**Ссылка на задеплоенный сайт:** https://julia-myaso00.github.io/promvent-site/

## ⚙️ Установка AI-ассистента (опционально)

```bash
# Установить Ollama
# Скачайте OllamaSetup.exe с https://ollama.com/download/windows и запустите

# Скачать модель
ollama pull llama3.2:3b

# Проверить
ollama list
```

## 🐛 Решение проблем

| Проблема                    | Решение                                        |
|-----------------------------|------------------------------------------------|
| `php` не найден             | Установите PHP и добавьте в PATH               |
| Ошибка подключения к Ollama | Запустите Ollama (значок в трее)               |
| Белая страница              | Убедитесь, что `index.php` существует          |
| 404 на страницах            | Проверьте, что сервер запущен из корня проекта |

