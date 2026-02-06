<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');

echo "강의 삭제 스크립트\n";
echo "================\n\n";

// 1. "15주 강의" 강좌 찾기
$courseName = '15주 강의';
echo "강좌명: $courseName 검색 중...\n";

$course = $DB->get_record('course', ['fullname' => $courseName]);

if ($course) {
    echo "찾음!\n";
    echo "강좌 ID: " . $course->id . "\n";
    echo "강좌명: " . $course->fullname . "\n";
    echo "단축명: " . $course->shortname . "\n";

    // 2. 강좌 삭제 확인
    echo "\n정말로 이 강좌를 삭제하시겠습니까? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);

    if (trim(strtolower($line)) === 'y') {
        try {
            delete_course($course, false);
            echo "\n✅ 강좌가 성공적으로 삭제되었습니다.\n";
        } catch (Exception $e) {
            echo "\n❌ 오류: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n취소되었습니다.\n";
    }
} else {
    echo "❌ 강좌 '$courseName'을 찾을 수 없습니다.\n";

    // 전체 강좌 목록 출력
    echo "\n현재 등록된 강좌 목록:\n";
    $courses = $DB->get_records('course', array(), '', 'id, fullname, shortname');
    foreach ($courses as $c) {
        if ($c->id != SITEID) { // 사이트 강좌 제외
            echo "  - ID: {$c->id}, 강좌명: {$c->fullname}, 단축명: {$c->shortname}\n";
        }
    }
}
