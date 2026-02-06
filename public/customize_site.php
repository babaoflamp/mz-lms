<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/adminlib.php');

// 1. Upgrade Database if needed (Theme registration)
// This is usually done via admin/cli/upgrade.php, but we assume it might be needed.
// We will run upgrade.php separately in the terminal.

// 2. Update Site Configuration
echo "Updating Site Configuration...\n";
$site = get_site();
$site->fullname = 'Global Korean Learning Platform';
$site->shortname = 'K-Edu';
$site->summary = 'Learn Korean naturally with our immersive platform. 외국인을 위한 최고의 한국어 학습 커뮤니티.';
$DB->update_record('course', $site);

// 3. Set Frontpage settings
echo "Configuring Frontpage...\n";
// Show courses and search box
set_config('frontpage', '3,5', 'moodle');
set_config('frontpageloggedin', '3,5', 'moodle');

// 4. Set Theme to Moove
echo "Setting Theme to Moove...\n";
set_config('theme', 'moove');

// 5. Add Custom CSS (Google Fonts)
$customhead = "
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
<link href='https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;700&display=swap' rel='stylesheet'>
<style>
    body, h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-family: 'Noto Sans KR', sans-serif !important;
    }
    .brand { font-weight: 700; color: #3b5998; }
</style>
";
set_config('additionalhtmlhead', $customhead);

// 6. Create Category and Course if not exists
echo "Creating Sample Content...\n";
if (!$DB->record_exists('course_categories', ['name' => 'Korean Language'])) {
    $category = new stdClass();
    $category->name = 'Korean Language';
    $category->description = 'Courses for learning Korean language.';
    $category->parent = 0;
    $category->sortorder = 999;
    $catid = $DB->insert_record('course_categories', $category);

    // Create Course
    $course = new stdClass();
    $course->category = $catid;
    $course->fullname = 'Beginner Korean (Hangul)';
    $course->shortname = 'KOR101';
    $course->summary = 'Start your journey by learning the Korean alphabet, Hangul.';
    $course->format = 'topics';
    $course->startdate = time();
    $course->visible = 1;

    // Use create_course to handle all defaults
    if (!$DB->record_exists('course', ['shortname' => 'KOR101'])) {
        $course = create_course($course);
        echo "Created course: " . $course->fullname . "\n";
    } else {
        echo "Course KOR101 already exists.\n";
    }
} else {
    echo "Content already exists.\n";
}

// 7. Clear Caches
echo "Purging caches...\n";
purge_all_caches();

// 8. Configure Theme Moove Settings (Frontpage Marketing & Numbers)
echo "Configuring Theme Moove Features...\n";

// Marketing Section
set_config('marketingheading', '학습 기능 소개', 'theme_moove');
set_config('marketingcontent', 'K-Edu는 최신 교육 공학 기술을 바탕으로 한국어 학습에 최적화된 경험을 제공합니다.', 'theme_moove');

// Feature 1
set_config('marketing1heading', '체계적인 커리큘럼', 'theme_moove');
set_config('marketing1content', '입문부터 고급까지 단계별로 구성된 맞춤형 학습 과정을 제공합니다.', 'theme_moove');
// Feature 2
set_config('marketing2heading', '원어민 튜터링', 'theme_moove');
set_config('marketing2content', '한국인 원어민 선생님과 1:1로 대화하며 실전 회화 실력을 키워보세요.', 'theme_moove');
// Feature 3
set_config('marketing3heading', '커뮤니티 활동', 'theme_moove');
set_config('marketing3content', '전 세계 학습자들과 함께 한국어로 소통하며 즐겁게 공부할 수 있습니다.', 'theme_moove');
// Feature 4
set_config('marketing4heading', '문화 체험', 'theme_moove');
set_config('marketing4content', '언어뿐만 아니라 한국의 다양한 문화를 생생하게 체험할 수 있는 기회를 드립니다.', 'theme_moove');

// Numbers Section Content
$numbers_content = '<h2>25,000명 이상의 학습자가 선택했습니다.</h2>
<p>복잡한 과정 없이 누구나 쉽게 한국어를 배울 수 있습니다.<br class="d-none d-sm-block d-md-none d-xl-block">
지금 바로 시작하세요. 당신의 한국어 실력이 하루가 다르게 성장할 것입니다.</p>';
set_config('numbersfrontpagecontent', $numbers_content, 'theme_moove');

echo "Theme Moove settings updated.\n";

echo "Done! Please refresh your browser.\n";
