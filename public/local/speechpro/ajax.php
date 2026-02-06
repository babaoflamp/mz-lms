<?php
// This file is part of Moodle - http://moodle.org/

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

use local_speechpro\service;

// Wrap everything in output buffering
ob_start();

try {
    // Check if logged in (but don't require it - allow for debugging)
    if (!isloggedin() || isguestuser()) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You must be logged in to use this service'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check sesskey
    if (!confirm_sesskey(optional_param('sesskey', '', PARAM_RAW))) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid session key'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check capability
    if (!has_capability('local/speechpro:use', context_system::instance())) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You do not have permission to use this service'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $action = required_param('action', PARAM_ALPHA);

    switch ($action) {
        case 'config':
            $response = [
                'success' => true,
                'endpoint' => service::get_endpoint(),
                'timeout' => service::get_timeout(),
            ];
            break;

        case 'evaluate':
            $text = required_param('text', PARAM_RAW);

            // 디버그: 파일 정보 로깅
            error_log("DEBUG: FILES array: " . json_encode($_FILES));

            if (empty($_FILES['audio'])) {
                $response = ['success' => false, 'error' => 'audio file not found in FILES array'];
                break;
            }

            if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                $response = ['success' => false, 'error' => 'file upload error: ' . $_FILES['audio']['error']];
                break;
            }

            $tmp_file = $_FILES['audio']['tmp_name'];
            if (!file_exists($tmp_file)) {
                $response = ['success' => false, 'error' => 'uploaded file does not exist at ' . $tmp_file];
                break;
            }

            if (!is_uploaded_file($tmp_file)) {
                // ngrok이나 프록시 환경에서 is_uploaded_file()이 실패할 수 있으므로 경고만 함
                error_log("WARNING: is_uploaded_file() returned false, but file exists");
            }

            $audio = file_get_contents($tmp_file);
            if ($audio === false) {
                $response = ['success' => false, 'error' => 'failed to read uploaded file'];
                break;
            }

            if (empty($audio)) {
                $response = ['success' => false, 'error' => 'uploaded audio is empty'];
                break;
            }

            $response = service::evaluate($text, $audio);
            break;

        default:
            $response = ['success' => false, 'error' => 'invalid action'];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
