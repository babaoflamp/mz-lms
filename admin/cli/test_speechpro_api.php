<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

use local_speechpro\service;

echo "=== SpeechPro Service Test ===\n\n";

echo "1. Configuration:\n";
echo "   Endpoint: " . service::get_endpoint() . "\n";
echo "   Timeout: " . service::get_timeout() . "s\n\n";

echo "2. Testing GTP endpoint...\n";
try {
    $result = service::gtp("안녕하세요");
    echo "   ✅ Success!\n";
    echo "   Text: " . ($result['text'] ?? 'N/A') . "\n";
    echo "   Syll ltrs: " . ($result['syll_ltrs'] ?? 'N/A') . "\n";
    echo "   Syll phns: " . ($result['syll_phns'] ?? 'N/A') . "\n";
    echo "   Error code: " . ($result['error_code'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n\n";
}

echo "3. Testing Model endpoint...\n";
try {
    $result = service::model("안녕하세요", "안_녕_하_세_요", "aa nf_nn yv ng_h0 aa_s0 ee_yo");
    echo "   ✅ Success!\n";
    echo "   FST length: " . strlen($result['fst'] ?? '') . " characters\n";
    echo "   Error code: " . ($result['error_code'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n\n";
}

echo "Test complete!\n";
