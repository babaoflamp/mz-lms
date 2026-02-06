<?php
/**
 * CLI script to deploy/validate H5P content
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

$admin = get_admin();
\core\session\manager::set_user($admin);

echo "=== H5P 콘텐츠 배포 상태 확인 ===\n\n";

// Check H5P content in database
$h5pcontents = $DB->get_records('h5p', [], 'id DESC', '*', 0, 10);

if (empty($h5pcontents)) {
    echo "⚠️  배포된 H5P 콘텐츠가 없습니다.\n";
    echo "\n해결 방법:\n";
    echo "1. Moodle 관리자 대시보드에서 'H5P 활동' 모듈을 사용하세요\n";
    echo "2. 강좌 → 편집 모드 → '활동 또는 리소스 추가' → 'H5P 활동' 선택\n";
    echo "3. .h5p 파일을 업로드하고 저장하면 자동으로 배포됩니다\n";
} else {
    echo "✅ 배포된 H5P 콘텐츠:\n\n";
    foreach ($h5pcontents as $content) {
        echo "ID: {$content->id}\n";
        echo "경로: {$content->pathnamehash}\n";
        echo "메인 라이브러리: {$content->mainlibraryid}\n";
        echo "생성 시간: " . date('Y-m-d H:i:s', $content->timecreated) . "\n";
        echo "---\n";
    }
}

// Check H5P activities
echo "\n=== H5P 활동 모듈 확인 ===\n\n";
$h5pactivities = $DB->get_records('h5pactivity', [], 'id DESC', '*', 0, 10);

if (empty($h5pactivities)) {
    echo "⚠️  생성된 H5P 활동이 없습니다.\n";
} else {
    echo "✅ H5P 활동 목록:\n\n";
    foreach ($h5pactivities as $activity) {
        $course = $DB->get_record('course', ['id' => $activity->course]);
        echo "ID: {$activity->id}\n";
        echo "이름: {$activity->name}\n";
        echo "강좌: {$course->fullname}\n";
        echo "생성 시간: " . date('Y-m-d H:i:s', $activity->timecreated) . "\n";
        echo "---\n";
    }
}

echo "\n💡 팁: H5P 콘텐츠는 반드시 'H5P 활동' 모듈을 통해 추가해야 합니다!\n";
