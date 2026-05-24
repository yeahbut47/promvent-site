<?php
/**
 * Модульные тесты для сайта ПромВент
 * Запуск: php tests/run_tests.php
 */

echo "\n";
echo "============================================================\n";
echo "     PromVent Test Suite - Running Unit Tests\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;
$errors = [];

function assert_equal($actual, $expected, string $testName): void {
    global $passed, $failed, $errors;
    if ($actual === $expected) {
        echo "  ✅ PASS: $testName\n";
        $passed++;
    } else {
        $msg = "  ❌ FAIL: $testName\n"
             . "     Expected: " . var_export($expected, true) . "\n"
             . "     Got:      " . var_export($actual, true);
        echo $msg . "\n";
        $errors[] = $testName;
        $failed++;
    }
}

function assert_true(bool $condition, string $testName): void {
    assert_equal($condition, true, $testName);
}

// ─── Вспомогательные функции (логика приложения) ───────────────

function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_phone(string $phone): bool {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 11 && ($digits[0] === '7' || $digits[0] === '8')) return true;
    if (strlen($digits) === 10 && $digits[0] === '9') return true;
    return false;
}

function validate_name(string $name): bool {
    $trimmed = trim($name);
    return strlen($trimmed) >= 2
        && strlen($trimmed) <= 50
        && !preg_match('/^[0-9]+$/', $trimmed);
}

function validate_question(string $q): int {
    $len = strlen(trim($q));
    return $len >= 10 && $len <= 1000;
}

function sanitize_input(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function format_lead(string $name, string $phone, string $service): string {
    $name    = sanitize_input($name);
    $phone   = sanitize_input($phone);
    $service = sanitize_input($service);
    return "Заявка: $name | $phone | $service";
}

function is_valid_service(string $service): bool {
    $allowed = ['ventilation', 'conditioning', 'automation', 'repair'];
    return in_array($service, $allowed, true);
}

// ─────────────────────────────────────────────────────────
// Тест 1: Версия PHP
// ─────────────────────────────────────────────────────────
echo "Test 1: PHP version check\n";
assert_true(
    version_compare(PHP_VERSION, '7.4.0', '>='),
    'PHP версия >= 7.4 (' . PHP_VERSION . ')'
);

// ─────────────────────────────────────────────────────────
// Тест 2: Валидация email
// ─────────────────────────────────────────────────────────
echo "\nTest 2: Email validation\n";
assert_true(validate_email('client@example.com'),  'корректный email принимается');
assert_true(!validate_email('not-an-email'),        'строка без @ отклоняется');
assert_true(!validate_email('missing@'),            'email без домена отклоняется');
assert_true(!validate_email(''),                    'пустая строка отклоняется');

// ─────────────────────────────────────────────────────────
// Тест 3: Валидация телефона
// ─────────────────────────────────────────────────────────
echo "\nTest 3: Phone validation\n";
assert_true(validate_phone('+7 999 123-45-67'),     'формат +7 с пробелами принимается');
assert_true(validate_phone('89991234567'),           'формат 8XXXXXXXXXX принимается');
assert_true(validate_phone('79161234567'),           'формат 7XXXXXXXXXX принимается');
assert_true(validate_phone('+7 (916) 123-45-67'),   'формат со скобками принимается');
assert_true(!validate_phone('12345'),               'короткий номер отклоняется');
assert_true(!validate_phone(''),                    'пустой номер отклоняется');

// ─────────────────────────────────────────────────────────
// Тест 4: Валидация имени
// ─────────────────────────────────────────────────────────
echo "\nTest 4: Name validation\n";
assert_true(validate_name('Иван'),              'имя из одного слова принимается');
assert_true(validate_name('Иван Петров'),       'имя из двух слов принимается');
assert_true(!validate_name(''),                 'пустое имя отклоняется');
assert_true(!validate_name('A'),               'слишком короткое имя отклоняется');
assert_true(!validate_name('12'),              'имя из цифр отклоняется');

// ─────────────────────────────────────────────────────────
// Тест 5: Валидация вопроса
// ─────────────────────────────────────────────────────────
echo "\nTest 5: Question validation\n";
assert_true(validate_question('Как отремонтировать вентиляцию?'), 'нормальный вопрос принимается');
assert_true(validate_question('1234567890'),    'ровно 10 символов принимается');
assert_true(!validate_question('123456789'),   '9 символов отклоняется');
assert_true(!validate_question(''),            'пустой вопрос отклоняется');

// ─────────────────────────────────────────────────────────
// Тест 6: Санитизация ввода
// ─────────────────────────────────────────────────────────
echo "\nTest 6: Input sanitization\n";
assert_equal(
    sanitize_input('<script>alert("xss")</script>'),
    'alert(&quot;xss&quot;)',
    'XSS-атака нейтрализуется (теги удалены, спецсимволы экранированы)'
);
assert_equal(
    sanitize_input('  Иван Иванов  '),
    'Иван Иванов',
    'пробелы по краям убираются'
);
assert_equal(
    sanitize_input('<b>жирный</b> текст'),
    'жирный текст',
    'HTML-теги удаляются'
);

// ─────────────────────────────────────────────────────────
// Тест 7: Форматирование заявки
// ─────────────────────────────────────────────────────────
echo "\nTest 7: Lead formatting\n";
assert_equal(
    format_lead('Иван', '+79991234567', 'Вентиляция'),
    'Заявка: Иван | +79991234567 | Вентиляция',
    'корректная заявка форматируется правильно'
);
assert_equal(
    format_lead('<b>Хакер</b>', '0', '<script>'),
    'Заявка: Хакер | 0 | ',
    'вредоносный ввод в заявке очищается'
);

// ─────────────────────────────────────────────────────────
// Тест 8: Типы услуг
// ─────────────────────────────────────────────────────────
echo "\nTest 8: Service types\n";
assert_true(is_valid_service('ventilation'),    'вентиляция — допустимая услуга');
assert_true(is_valid_service('conditioning'),   'кондиционирование — допустимая услуга');
assert_true(is_valid_service('automation'),     'автоматика — допустимая услуга');
assert_true(is_valid_service('repair'),         'ремонт — допустимая услуга');
assert_true(!is_valid_service('hack'),          'недопустимая услуга отклоняется');
assert_true(!is_valid_service(''),              'пустая строка отклоняется');

// ─────────────────────────────────────────────────────────
// Итог
// ─────────────────────────────────────────────────────────
$total = $passed + $failed;
echo "\n";
echo "============================================================\n";
echo "  Test Summary: $passed passed, $failed failed out of $total\n";
echo "============================================================\n\n";

if ($failed > 0) {
    echo "❌ SOME TESTS FAILED!\n";
    foreach ($errors as $e) {
        echo "  • $e\n";
    }
    exit(1);
}

echo "✅ ALL TESTS PASSED!\n";
exit(0);
