<?php
/**
 * CLI script to create an H5P test course with multiple activities
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/h5pactivity/lib.php');

// Get admin user
$admin = get_admin();
if (!$admin) {
    echo "Error: Admin user not found\n";
    exit(1);
}

// Set admin user context
\core\session\manager::set_user($admin);

echo "=== H5P 테스트 강좌 생성 ===\n\n";

// Create course
$course = new stdClass();
$course->fullname = 'H5P 인터랙티브 콘텐츠 테스트';
$course->shortname = 'h5p-test';
$course->category = 1; // Miscellaneous category
$course->summary = '<p>다양한 H5P 인터랙티브 콘텐츠를 테스트하고 체험할 수 있는 강좌입니다.</p>';
$course->summaryformat = FORMAT_HTML;
$course->format = 'topics';
$course->numsections = 5;
$course->visible = 1;
$course->startdate = time();
$course->enddate = 0;
$course->enablecompletion = 1;

try {
    $newcourse = create_course($course);
    echo "✅ 강좌 생성 완료!\n";
    echo "   강좌 ID: {$newcourse->id}\n";
    echo "   강좌명: {$newcourse->fullname}\n\n";

    // Get module info for h5pactivity
    $module = $DB->get_record('modules', ['name' => 'h5pactivity'], '*', MUST_EXIST);

    // Add existing H5P files
    $h5pdir = __DIR__ . '/../../h5p/';
    $h5pfiles = [
        'audio-recorder-142-1214919.h5p' => 'Audio Recorder - 음성 녹음 테스트',
        'interactive-video-2-618.h5p' => 'Interactive Video - 인터랙티브 비디오'
    ];

    $fs = get_file_storage();
    $usercontext = context_user::instance($admin->id);
    $sectionnum = 1;

    foreach ($h5pfiles as $filename => $activityname) {
        $filepath = $h5pdir . $filename;
        
        if (!file_exists($filepath)) {
            echo "⚠️  파일을 찾을 수 없음: $filename\n";
            continue;
        }

        echo "📦 H5P 액티비티 생성 중: $activityname\n";

        // Prepare draft file
        $draftitemid = file_get_unused_draft_itemid();
        $filerecord = [
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftitemid,
            'filepath'  => '/',
            'filename'  => $filename
        ];

        $file = $fs->create_file_from_pathname($filerecord, $filepath);

        // Create module data
        $data = new stdClass();
        $data->course = $newcourse->id;
        $data->section = $sectionnum;
        $data->module = $module->id;
        $data->modulename = 'h5pactivity';
        $data->instance = 0;
        $data->add = 'h5pactivity';
        $data->update = 0;
        $data->return = 0;
        $data->cmidnumber = '';

        // H5P activity specific fields
        $data->name = $activityname;
        $data->intro = '<p>H5P 인터랙티브 콘텐츠를 체험해보세요.</p>';
        $data->introformat = FORMAT_HTML;
        $data->displayoptions = 15;
        $data->enabletracking = 1;
        $data->grademethod = 1;
        $data->reviewmode = 2;
        $data->visible = 1;
        $data->visibleoncoursepage = 1;
        $data->grade = 100;
        $data->gradecat = 0;
        $data->packagefile = $draftitemid;

        // Add the activity
        $coursemodule = add_moduleinfo($data, $newcourse);
        echo "   ✅ 추가 완료: $activityname (CM ID: {$coursemodule->coursemodule})\n";
        
        $sectionnum++;
    }

    // Add sample page for downloading more H5P content
    echo "\n📄 H5P 다운로드 안내 페이지 생성 중...\n";
    
    $pagemodule = $DB->get_record('modules', ['name' => 'page'], '*', MUST_EXIST);
    
    $pagedata = new stdClass();
    $pagedata->course = $newcourse->id;
    $pagedata->section = $sectionnum;
    $pagedata->module = $pagemodule->id;
    $pagedata->modulename = 'page';
    $pagedata->instance = 0;
    $pagedata->add = 'page';
    $pagedata->update = 0;
    $pagedata->return = 0;
    $pagedata->name = 'H5P 콘텐츠 다운로드 가이드';
    $pagedata->intro = '';
    $pagedata->introformat = FORMAT_HTML;
    $pagedata->visible = 1;
    $pagedata->visibleoncoursepage = 1;
    
    $pagedata->content = <<<HTML
<div style="max-width: 800px; margin: 0 auto; padding: 2rem; background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; color: white;">
    <h2 style="color: #60a5fa; margin-bottom: 1.5rem;">🎯 H5P 콘텐츠 다운로드 가이드</h2>
    
    <div style="background: white; color: #1e293b; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
        <h3 style="color: #1e3a8a; margin-bottom: 1rem;">1️⃣ H5P 공식 예제 다운로드</h3>
        <p><strong>H5P.org 예제 허브:</strong></p>
        <ul>
            <li><a href="https://h5p.org/content-types-and-applications" target="_blank" style="color: #2563eb;">H5P 콘텐츠 타입 목록</a></li>
            <li><a href="https://h5p.org/h5p/embed/617" target="_blank" style="color: #2563eb;">Interactive Video 예제</a></li>
            <li><a href="https://h5p.org/h5p/embed/1214919" target="_blank" style="color: #2563eb;">Audio Recorder 예제</a></li>
            <li><a href="https://h5p.org/quiz" target="_blank" style="color: #2563eb;">Quiz (Question Set) 예제</a></li>
        </ul>
        <p style="margin-top: 1rem;"><em>각 예제 페이지에서 "Reuse" 버튼을 클릭하면 .h5p 파일을 다운로드할 수 있습니다.</em></p>
    </div>

    <div style="background: white; color: #1e293b; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
        <h3 style="color: #1e3a8a; margin-bottom: 1rem;">2️⃣ 추천 H5P 콘텐츠 타입</h3>
        <ul>
            <li><strong>Course Presentation</strong> - 슬라이드 기반 프레젠테이션</li>
            <li><strong>Interactive Video</strong> - 퀴즈가 포함된 인터랙티브 비디오</li>
            <li><strong>Question Set</strong> - 다양한 문제 유형의 퀴즈</li>
            <li><strong>Drag and Drop</strong> - 드래그 앤 드롭 학습</li>
            <li><strong>Fill in the Blanks</strong> - 빈칸 채우기</li>
            <li><strong>Timeline</strong> - 타임라인 시각화</li>
            <li><strong>Audio Recorder</strong> - 음성 녹음 및 평가</li>
        </ul>
    </div>

    <div style="background: white; color: #1e293b; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
        <h3 style="color: #1e3a8a; margin-bottom: 1rem;">3️⃣ H5P 콘텐츠 업로드 방법</h3>
        <ol>
            <li>H5P 파일(.h5p)을 다운로드</li>
            <li>이 강좌의 편집 모드 활성화</li>
            <li>"활동 또는 리소스 추가" 클릭</li>
            <li>"H5P" 선택</li>
            <li>다운로드한 .h5p 파일 업로드</li>
            <li>저장 후 테스트</li>
        </ol>
    </div>

    <div style="background: #dbeafe; color: #1e3a8a; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #2563eb;">
        <strong>💡 팁:</strong> H5P.org에서 무료 계정을 만들면 온라인 에디터로 직접 콘텐츠를 제작할 수도 있습니다!
    </div>
</div>
HTML;
    $pagedata->contentformat = FORMAT_HTML;

    $pagecm = add_moduleinfo($pagedata, $newcourse);
    echo "   ✅ 안내 페이지 추가 완료 (CM ID: {$pagecm->coursemodule})\n";

    echo "\n🎉 H5P 테스트 강좌 생성 완료!\n";
    echo "강좌 URL: {$CFG->wwwroot}/course/view.php?id={$newcourse->id}\n";

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    exit(1);
}
