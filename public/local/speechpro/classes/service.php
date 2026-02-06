<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

namespace local_speechpro;

/**
 * SpeechPro service wrapper.
 *
 * @package    local_speechpro
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

class service
{
    public static function get_endpoint(): string
    {
        $endpoint = get_config('local_speechpro', 'endpoint');
        if (!$endpoint) {
            // Use external SpeechPro server (직접 엔드포인트)
            $endpoint = 'http://112.220.79.222:33005/speechpro';
        }
        return rtrim($endpoint, '/');
    }

    public static function get_timeout(): int
    {
        $timeout = (int) get_config('local_speechpro', 'timeout');
        if ($timeout <= 0) {
            $timeout = 30;
        }
        return $timeout;
    }

    public static function normalize_spaces(string $text): string
    {
        $special = [
            "\u{00A0}" => ' ',
            "\u{2002}" => ' ',
            "\u{2003}" => ' ',
            "\u{2009}" => ' ',
            "\t" => ' ',
        ];
        $text = strtr($text, $special);
        while (strpos($text, '  ') !== false) {
            $text = str_replace('  ', ' ', $text);
        }
        return trim($text);
    }

    protected static function request(string $path, array $payload): array
    {
        $url = self::get_endpoint() . $path;
        $timeout = self::get_timeout();

        // Use native curl instead of Moodle's curl class to bypass security restrictions
        $ch = curl_init($url);
        if (!$ch) {
            throw new \RuntimeException('Failed to initialize curl');
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException($error ?: 'SpeechPro request failed');
        }

        if ($status >= 400) {
            throw new \RuntimeException(
                'SpeechPro request failed (HTTP ' . $status . '): ' . substr($response, 0, 300)
            );
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            // 응답이 JSON이 아닌 경우 상세한 디버그 정보 제공
            throw new \RuntimeException(
                'Invalid JSON response from SpeechPro. Response: ' . substr($response, 0, 200)
            );
        }

        return $data;
    }

    public static function gtp(string $text, ?string $requestid = null): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('text is required');
        }
        $text = self::normalize_spaces($text);
        if (!$requestid) {
            $requestid = 'gtp_' . bin2hex(random_bytes(4));
        }
        $payload = [
            'id' => $requestid,
            'text' => $text,
        ];
        $data = self::request('/gtp', $payload);
        return [
            'id' => $data['id'] ?? $requestid,
            'text' => $data['text'] ?? $text,
            'syll_ltrs' => $data['syll ltrs'] ?? '',
            'syll_phns' => $data['syll phns'] ?? '',
            'error_code' => $data['error code'] ?? 0,
        ];
    }

    public static function model(string $text, string $syll_ltrs, string $syll_phns, ?string $requestid = null): array
    {
        if ($text === '' || $syll_ltrs === '' || $syll_phns === '') {
            throw new \InvalidArgumentException('text, syll_ltrs, syll_phns are required');
        }
        if (!$requestid) {
            $requestid = 'model_' . bin2hex(random_bytes(4));
        }
        $payload = [
            'id' => $requestid,
            'text' => $text,
            'syll ltrs' => $syll_ltrs,
            'syll phns' => $syll_phns,
        ];
        $data = self::request('/model', $payload);
        return [
            'id' => $data['id'] ?? $requestid,
            'text' => $data['text'] ?? $text,
            'syll_ltrs' => $data['syll ltrs'] ?? $syll_ltrs,
            'syll_phns' => $data['syll phns'] ?? $syll_phns,
            'fst' => $data['fst'] ?? '',
            'error_code' => $data['error code'] ?? 0,
        ];
    }

    public static function score(string $text, string $syll_ltrs, string $syll_phns, string $fst, string $audio): array
    {
        if ($text === '' || $syll_ltrs === '' || $syll_phns === '' || $fst === '' || $audio === '') {
            throw new \InvalidArgumentException('text, syll_ltrs, syll_phns, fst, audio are required');
        }
        $payload = [
            'id' => 'score_' . bin2hex(random_bytes(4)),
            'text' => $text,
            'syll ltrs' => $syll_ltrs,
            'syll phns' => $syll_phns,
            'fst' => $fst,
            'wav usr' => base64_encode($audio),
        ];
        $data = self::request('/scorejson', $payload);
        return $data;
    }

    public static function evaluate(string $text, string $audio): array
    {
        // Step 1: Get syllables from GTP
        $gtp = self::gtp($text);
        if ($gtp['error_code'] != 0) {
            throw new \RuntimeException('GTP failed with error code: ' . $gtp['error_code']);
        }

        // Step 2: Get FST model from Model
        $model = self::model($text, $gtp['syll_ltrs'], $gtp['syll_phns']);
        if ($model['error_code'] != 0) {
            throw new \RuntimeException('Model failed with error code: ' . $model['error_code']);
        }

        // Step 3: Get pronunciation score
        $score = self::score($text, $model['syll_ltrs'], $model['syll_phns'], $model['fst'], $audio);

        return [
            'success' => true,
            'text' => $text,
            'score' => $score['score'] ?? null,
            'gtp' => $gtp,
            'model' => $model,
            'scoreData' => $score,
        ];
    }
}
