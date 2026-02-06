<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

// Test 1: GTP
$payload1 = [
    'id' => 'test_gtp',
    'text' => '안녕하세요',
];

echo "=== GTP Test ===\n";
$response1 = curl_test('http://112.220.79.222:33005/speechpro/gtp', $payload1);
$data1 = json_decode($response1, true);

if (!$data1) {
    echo "❌ GTP failed\n";
    exit(1);
}

echo "✓ GTP success\n";
echo "syll_ltrs: " . $data1['syll ltrs'] . "\n";
echo "syll_phns: " . $data1['syll phns'] . "\n\n";

// Test 2: Model
$payload2 = [
    'id' => 'test_model',
    'text' => '안녕하세요',
    'syll_ltrs' => $data1['syll ltrs'],
    'syll_phns' => $data1['syll phns'],
];

echo "=== Model Test ===\n";
echo "Payload: " . json_encode($payload2, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
$response2 = curl_test('http://112.220.79.222:33005/speechpro/model', $payload2);
$data2 = json_decode($response2, true);

if (!$data2) {
    echo "❌ Model failed\n";
    exit(1);
}

echo "✓ Model success\n";
echo "fst length: " . strlen($data2['fst']) . "\n\n";

echo "✅ All tests passed!\n";

function curl_test($url, $payload)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($status >= 400) {
        echo "❌ Status: $status\n";
        echo "Error: $error\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
        return null;
    }

    return $response;
}
