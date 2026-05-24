# CI/CD Pipeline — ПромВент

## Общая схема пайплайна

```
push / pull_request
        │
        ▼
┌───────────────────────────────────────────────────┐
│                  CI — Build & Test                │
│                                                   │
│  1. Lint          PHP syntax check (все .php)     │
│  2. Unit tests    php tests/run_tests.php         │
│  3. Smoke tests   curl → localhost:8080           │
│  4. Build check   критические файлы присутствуют  │
│  5. Upload artifact  (сохраняется 7 дней)         │
└───────────────────┬───────────────────────────────┘
                    │  только при push в main
                    ▼
┌───────────────────────────────────────────────────┐
│              CD — Deploy to GitHub Pages          │
│                                                   │
│  1. Download artifact                             │
│  2. Prepare deployment  (leads/, build-info.txt)  │
│  3. Push to gh-pages branch                       │
└───────────────────────────────────────────────────┘
                    │
                    ▼
     https://julia-myaso00.github.io/promvent-site/
```

## Триггеры

| Событие                       | Что запускается        |
|-------------------------------|------------------------|
| `push` в ветку `main`         | CI + CD                |
| `push` в ветку `develop`      | CI                     |
| `pull_request` → `main`       | CI (деплой не идёт)    |

## Шаги CI

### 1. Lint — PHP syntax check

Проходит по всем `.php`-файлам проекта (кроме `vendor/`) и проверяет синтаксис через `php -l`. Если хотя бы один файл содержит ошибку — шаг падает и весь пайплайн останавливается.

Локально:
```bash
find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {}
```

### 2. Unit tests

Запускает `tests/run_tests.php`. Тесты покрывают:

- валидацию имени (длина, только цифры)
- валидацию телефонного номера (российские форматы)
- валидацию сообщения (длина)
- запись и чтение JSON-файла заявки
- наличие критических файлов проекта

Локально:
```bash
php tests/run_tests.php
```

Тесты возвращают `exit(0)` при успехе и `exit(1)` при провале — CI корректно реагирует на код выхода.

### 3. Smoke tests

Поднимает встроенный PHP-сервер (`php -S localhost:8080`) и проверяет:

- главная страница отвечает HTTP 200
- `/backend/api.php` не возвращает 500

Это минимальная проверка работоспособности сайта как единого целого — отличается от unit-тестов тем, что проверяет поведение через HTTP, а не логику функций.

### 4. Build check

Проверяет, что в репозитории присутствуют все критические файлы:

```
index.php
index.html
admin/leads.php
backend/api.php
backend/config.php
```

### 5. Upload artifact

Сохраняет снимок кода для передачи в CD-джобу. Хранится 7 дней.

## Шаги CD

CD запускается только при `push` в `main` и только после успешного CI (`needs: ci`).

### 1. Download artifact

Скачивает артефакт, собранный в CI-джобе, — это гарантирует, что деплоится именно тот код, который прошёл все проверки.

### 2. Prepare deployment

Создаёт папку `leads/` (для записи заявок) и формирует `build-info.txt` с SHA коммита и временем деплоя.

### 3. Deploy to GitHub Pages

Использует action `peaceiris/actions-gh-pages@v3`. Публикует содержимое репозитория в ветку `gh-pages`. GitHub автоматически обслуживает эту ветку как статический сайт.

**Токен:** используется встроенный `GITHUB_TOKEN` — дополнительных секретов настраивать не нужно.

**URL после деплоя:** https://julia-myaso00.github.io/promvent-site/

## Защита ветки main

В настройках репозитория (Settings → Branches) для ветки `main` включены:

- **Require status checks to pass before merging** — шаг `CI — Build & Test` должен быть зелёным
- **Require branches to be up to date** — ветка должна содержать актуальные изменения из `main`

Это означает: если CI падает, кнопка «Merge» в Pull Request блокируется.

## Секреты

| Секрет         | Где используется            | Как получить                      |
|----------------|-----------------------------|-----------------------------------|
| `GITHUB_TOKEN` | Деплой на GitHub Pages      | Создаётся GitHub автоматически    |

Дополнительных секретов не требуется.

## Как посмотреть логи сборки

1. Открыть репозиторий на GitHub
2. Перейти в раздел **Actions** (вкладка сверху)
3. Выбрать нужный запуск из списка
4. Кликнуть на джобу (`CI — Build & Test` или `CD — Deploy to GitHub Pages`)
5. Развернуть нужный шаг — там будет полный вывод команды

Если шаг упал — строка с ошибкой подсвечена красным.

## Как добавить новый шаг в пайплайн

Открыть файл `.github/workflows/ci-cd.yml` и добавить новый `- name:` блок в нужную джобу. Пример — добавить проверку кодировки файлов после линтинга:

```yaml
- name: Check file encoding
  run: |
    ERRORS=0
    while IFS= read -r -d '' file; do
      if ! file "$file" | grep -q "UTF-8\|ASCII"; then
        echo "Wrong encoding: $file"
        ERRORS=$((ERRORS + 1))
      fi
    done < <(find . -name "*.php" -print0)
    [ "$ERRORS" -eq 0 ] || exit 1
    echo "OK: all files are UTF-8"
```

Шаги выполняются последовательно. Если шаг упал — следующие не запускаются.

## Ссылки

- **Рабочий сайт:** https://julia-myaso00.github.io/promvent-site/
- **Конфигурация пайплайна:** [.github/workflows/ci-cd.yml](../.github/workflows/ci-cd.yml)
- **Unit-тесты:** [tests/run_tests.php](../tests/run_tests.php)
- **История запусков:** https://github.com/julia-myaso00/promvent-site/actions
