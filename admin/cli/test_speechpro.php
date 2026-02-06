<?php

/**
 * Test SpeechPro evaluate endpoint
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

use local_speechpro\service;

$admin = get_admin();
\core\session\manager::set_user($admin);

echo "Testing SpeechPro service...\n\n";

// Test 1: Check endpoint
echo "1. Endpoint: " . service::get_endpoint() . "\n";
echo "2. Timeout: " . service::get_timeout() . "\n\n";

// Test 2: GTP
try {
    echo "3. Testing GTP...\n";
    $gtp = service::gtp("안녕하세요");
    echo "   Result: " . json_encode($gtp, JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

// Test 3: Model
try {
    echo "4. Testing Model...\n";
    $model = service::model("안녕하세요", "안녕하세요", "an-nyəŋ-ha-se-yo");
    echo "   Result: " . json_encode($model, JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

// Test 4: Dummy audio
try {
    echo "5. Testing Evaluate (with dummy audio)...\n";
    // Create a minimal WAV file (44 bytes header + 1 second of silence)
    $audio = pack(
        'a4Va4a4VvvVVvva4V',
        'RIFF',
        36 + 16000 * 2,
        'WAVE',
        'fmt ',
        16,
        1,
        1,
        16000,
        32000,
        2,
        16,
        'data',
        16000 * 2
    );
    $audio .= str_repeat("\0", 16000 * 2); // 1 second of silence at 16kHz

    $result = service::evaluate("안녕하세요", $audio);
    echo "   Result: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

echo "Test complete.\n";
