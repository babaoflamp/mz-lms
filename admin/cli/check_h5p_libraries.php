<?php
/**
 * Check H5P libraries installation status
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

$admin = get_admin();
\core\session\manager::set_user($admin);

echo "=== H5P 라이브러리 상태 확인 ===\n\n";

// Check if H5P libraries are installed
$libraries = $DB->get_records('h5p_libraries', [], 'title ASC');

if (empty($libraries)) {
    echo "❌ H5P 라이브러리가 설치되어 있지 않습니다!\n\n";
    echo "해결 방법:\n";
    echo "1. 사이트 관리 → H5P → H5P 설정\n";
    echo "2. 'H5P 허브와 통신 활성화' 체크\n";
    echo "3. 사이트 관리 → H5P → H5P 콘텐츠 유형 관리\n";
    echo "4. '사용 가능한 H5P 콘텐츠 유형 가져오기' 클릭\n";
    echo "5. 필요한 라이브러리 설치\n";
} else {
    echo "✅ 설치된 H5P 라이브러리:\n\n";
    foreach ($libraries as $lib) {
        echo "- {$lib->title} (v{$lib->majorversion}.{$lib->minorversion})\n";
    }
    echo "\n총 " . count($libraries) . "개 라이브러리 설치됨\n";
}

// Check H5P hub communication setting
echo "\n=== H5P 허브 통신 설정 ===\n";
$hubcommunication = get_config('core', 'site_hub_communication');
if ($hubcommunication) {
    echo "✅ H5P 허브 통신 활성화됨\n";
} else {
    echo "⚠️  H5P 허브 통신 비활성화됨\n";
    echo "   활성화 방법: 사이트 관리 → H5P → H5P 설정\n";
}
