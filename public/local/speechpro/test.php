<?php
define('AJAX_SCRIPT', true);
ob_start();

try {
    require_once(__DIR__ . '/../../config.php');

    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'message' => 'Test successful',
        'sesskey_required' => !empty($_POST['sesskey']),
        'logged_in' => isloggedin(),
        'is_guest' => isguestuser(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
