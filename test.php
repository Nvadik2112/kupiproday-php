<?php
// test_signature.php - Только проверка подписи
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

echo "=== ПРОВЕРКА ПОДПИСИ ТОКЕНА ===\n\n";

// 1. Ключ (должен быть одинаковым везде)
$key = 'secret_key'; // тот же, что в .env
echo "1. Используемый ключ: '{$key}'\n";
echo "   Длина: " . strlen($key) . " символов\n\n";

// 2. Реальный токен из вашего ответа
$realToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjgsInVzZXJuYW1lIjoibnZhZGlrMjExMiIsImlhdCI6MTc2NjY3ODM0MCwiZXhwIjoxNzY3MjgzMTQwfQ.xJ2PruBr8cFgWf5vjCF5MiY_7MYFtQ_Hx8CToX_5rVg";

echo "2. Реальный токен из /signin:\n";
echo "   " . $realToken . "\n\n";

// 3. Разберём токен на части
$parts = explode('.', $realToken);
if (count($parts) !== 3) {
    die("❌ Неверный формат JWT\n");
}

echo "3. Анализ частей токена:\n";
echo "   Header:  " . $parts[0] . "\n";
echo "   Payload: " . $parts[1] . "\n";
echo "   Signature: " . substr($parts[2], 0, 20) . "...\n\n";

// 4. Декодируем payload (без проверки подписи)
$payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
$payload = json_decode($payloadJson, true);

echo "4. Содержимое payload:\n";
echo "   sub (user id): " . ($payload['sub'] ?? 'NOT FOUND') . "\n";
echo "   username: " . ($payload['username'] ?? 'NOT FOUND') . "\n";
echo "   iat (issued at): " . date('Y-m-d H:i:s', $payload['iat']) . "\n";
echo "   exp (expires): " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
echo "   Токен действителен до: " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
echo "   Сейчас время: " . date('Y-m-d H:i:s') . "\n";
echo "   Токен просрочен? " . (time() > $payload['exp'] ? '❌ ДА' : '✅ НЕТ') . "\n\n";

// 5. Проверим подпись разными способами
echo "5. ПРОВЕРКА ПОДПИСИ:\n";

// Способ A: Через JWT::decode
try {
    $decoded = JWT::decode($realToken, new Key($key, 'HS256'));
    echo "   ✅ JWT::decode: ПОДПИСЬ ВЕРНА\n";
} catch (Exception $e) {
    echo "   ❌ JWT::decode: " . $e->getMessage() . "\n";
}

// Способ B: Вручную проверим подпись
echo "\n6. РУЧНАЯ ПРОВЕРКА ПОДПИСИ:\n";

// Что должно быть подписано: header.payload
$dataToSign = $parts[0] . "." . $parts[1];

// Вычисляем ожидаемую подпись
$expectedSignature = hash_hmac('sha256', $dataToSign, $key, true);
$expectedSignatureBase64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

// Полученная подпись (третья часть токена)
$actualSignature = $parts[2];

echo "   Данные для подписи: header.payload\n";
echo "   Ожидаемая подпись:  " . $expectedSignatureBase64 . "\n";
echo "   Фактическая подпись: " . $actualSignature . "\n";
echo "   Совпадают? " . ($expectedSignatureBase64 === $actualSignature ? '✅ ДА' : '❌ НЕТ') . "\n";

if ($expectedSignatureBase64 !== $actualSignature) {
    echo "\n   🔍 Детальное сравнение:\n";
    echo "   Первые 10 символов ожидаемой: '" . substr($expectedSignatureBase64, 0, 10) . "'\n";
    echo "   Первые 10 символов фактической: '" . substr($actualSignature, 0, 10) . "'\n";

    // Попробуем с разными вариантами ключа
    echo "\n   Попробуем другие варианты ключа:\n";

    $keyVariants = [
        $key,
        trim($key),
        $key . ' ', // с пробелом в конце
        ' ' . $key, // с пробелом в начале
        $key . "\n", // с переводом строки
        $key . "\r\n", // с Windows переводом строки
    ];

    foreach ($keyVariants as $i => $variant) {
        $sig = hash_hmac('sha256', $dataToSign, $variant, true);
        $sigBase64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sig));

        if ($sigBase64 === $actualSignature) {
            echo "   ✅ Найден правильный ключ! Вариант #{$i}: '" . addslashes($variant) . "'\n";
            break;
        }
    }
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";