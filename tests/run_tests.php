<?php
/**
 * PromVent — Unit Test Suite
 * Запуск: php tests/run_tests.php
 */

$passed = 0;
$failed = 0;

function test(string $name, bool $result): void {
    global $passed, $failed;
    if ($result) {
        echo "  [PASS] $name\n";
        $passed++;
    } else {
        echo "  [FAIL] $name\n";
        $failed++;
    }
}

echo "\n";
echo "=================================================\n";
echo "  PromVent Unit Tests\n";
echo "=================================================\n\n";

// =================================================
// Блок 1: Валидация имени
// =================================================
echo "Block 1: Name validation\n";

function validateName(string $name): bool {
    $trimmed = trim($name);
    return strlen($trimmed) >= 2
        && strlen($trimmed) <= 50
        && !preg_match('/^\d+$/', $trimmed);
}

test('Valid name: "Иван"',               validateName('Иван'));
test('Valid name: "Иван Петров"',        validateName('Иван Петров'));
test('Valid name with spaces: " Анна "', validateName(' Анна '));
test('Invalid: empty string',            !validateName(''));
test('Invalid: single char "A"',         !validateName('A'));
test('Invalid: digits only "123"',       !validateName('123'));
test('Invalid: over 50 chars',           !validateName(str_repeat('а', 51)));

echo "\n";

// =================================================
// Блок 2: Валидация телефона
// =================================================
echo "Block 2: Phone validation\n";

function validatePhone(string $phone): bool {
    $digits = preg_replace('/\D/', '', $phone);
    $len = strlen($digits);
    if ($len === 11 && ($digits[0] === '7' || $digits[0] === '8')) return true;
    if ($len === 10 && $digits[0] === '9') return true;
    return false;
}

test('Valid: "+7 999 123-45-67"',     validatePhone('+7 999 123-45-67'));
test('Valid: "89991234567"',          validatePhone('89991234567'));
test('Valid: "79161234567"',          validatePhone('79161234567'));
test('Valid: "+7 (916) 123-45-67"',  validatePhone('+7 (916) 123-45-67'));
test('Valid: "9161234567" (10 dig.)', validatePhone('9161234567'));
test('Invalid: "12345"',             !validatePhone('12345'));
test('Invalid: empty string',        !validatePhone(''));
test('Invalid: "00000000000"',       !validatePhone('00000000000'));

echo "\n";

// =================================================
// Блок 3: Валидация вопроса/сообщения
// =================================================
echo "Block 3: Message validation\n";

function validateMessage(string $msg): bool {
    $trimmed = trim($msg);
    return strlen($trimmed) >= 10 && strlen($trimmed) <= 1000;
}

test('Valid: normal question',         validateMessage('Как отремонтировать вентиляцию?'));
test('Valid: exactly 10 chars',        validateMessage('1234567890'));
test('Valid: exactly 1000 chars',      validateMessage(str_repeat('а', 1000)));
test('Invalid: empty string',         !validateMessage(''));
test('Invalid: 9 chars',              !validateMessage('123456789'));
test('Invalid: 1001 chars',           !validateMessage(str_repeat('а', 1001)));

echo "\n";

// =================================================
// Блок 4: Работа с файловым хранилищем заявок
// =================================================
echo "Block 4: Leads file storage\n";

$leadsDir = __DIR__ . '/../leads';

if (!is_dir($leadsDir)) {
    mkdir($leadsDir, 0777, true);
}

test('Leads directory exists',         is_dir($leadsDir));
test('Leads directory is writable',    is_writable($leadsDir));

$testFile = $leadsDir . '/test_lead_' . time() . '.json';
$testLead = ['name' => 'Тест', 'phone' => '+79991234567', 'type' => 'unit_test'];
$writeOk  = file_put_contents($testFile, json_encode($testLead, JSON_UNESCAPED_UNICODE)) !== false;
test('Write lead JSON file',           $writeOk);

$readBack = json_decode(file_get_contents($testFile), true);
test('Read back JSON equals original', $readBack === $testLead);

if (file_exists($testFile)) {
    unlink($testFile);
}
test('Cleanup test file',              !file_exists($testFile));

echo "\n";

// =================================================
// Блок 5: Наличие критических файлов проекта
// =================================================
echo "Block 5: Critical project files\n";

$root = __DIR__ . '/..';
$criticalFiles = [
    'index.php'          => 'Main entry point',
    'admin/leads.php'    => 'Admin panel',
    'backend/api.php'    => 'API handler',
    'backend/config.php' => 'Config file',
];

foreach ($criticalFiles as $file => $label) {
    test("File exists: $label ($file)", file_exists("$root/$file"));
}

echo "\n";

// =================================================
// Итог
// =================================================
$total = $passed + $failed;
echo "=================================================\n";
printf("  Results: %d/%d passed", $passed, $total);
if ($failed > 0) {
    printf(", %d FAILED", $failed);
}
echo "\n";
echo "=================================================\n\n";

if ($failed > 0) {
    echo "FAILED — fix the errors above and re-run.\n\n";
    exit(1);
}

echo "ALL TESTS PASSED\n\n";
exit(0);
