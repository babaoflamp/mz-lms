<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/page/lib.php');

// Define 10 Korean courses
$courses_data = [
    [
        'fullname' => 'Introduction to Korean (한국어 입문)',
        'summary' => 'Start your journey with Hangeul and basic phrases.',
        'shortname' => 'KOR-TEST-01'
    ],
    [
        'fullname' => 'Basic Korean Grammar (기초 한국어 문법)',
        'summary' => 'Understand the structure of Korean sentences and particles.',
        'shortname' => 'KOR-TEST-02'
    ],
    [
        'fullname' => 'Korean Conversation 101 (한국어 회화 101)',
        'summary' => 'Practice speaking and listening for daily situations.',
        'shortname' => 'KOR-TEST-03'
    ],
    [
        'fullname' => 'Korean Listening Practice (한국어 듣기 연습)',
        'summary' => 'Improve your listening skills with various audio materials.',
        'shortname' => 'KOR-TEST-04'
    ],
    [
        'fullname' => 'Reading Korean Literature (한국 문학 읽기)',
        'summary' => 'Read simple stories and poems in Korean.',
        'shortname' => 'KOR-TEST-05'
    ],
    [
        'fullname' => 'Korean Culture and History (한국 문화와 역사)',
        'summary' => 'Learn about the rich traditions and history of Korea.',
        'shortname' => 'KOR-TEST-06'
    ],
    [
        'fullname' => 'Business Korean (비즈니스 한국어)',
        'summary' => 'Learn professional language for the workplace.',
        'shortname' => 'KOR-TEST-07'
    ],
    [
        'fullname' => 'TOPIK I Preparation (TOPIK I 대비반)',
        'summary' => 'Prepare for the Test of Proficiency in Korean (Level 1 & 2).',
        'shortname' => 'KOR-TEST-08'
    ],
    [
        'fullname' => 'Intermediate Korean (중급 한국어)',
        'summary' => 'Bridge the gap between basic and advanced skills.',
        'shortname' => 'KOR-TEST-09'
    ],
    [
        'fullname' => 'Advanced Korean Expression (고급 한국어 표현)',
        'summary' => 'Master complex sentence structures and nuances.',
        'shortname' => 'KOR-TEST-10'
    ]
];

// Get or create a category
$category_name = 'Korean Test Courses';
$cat = $DB->get_record('course_categories', ['name' => $category_name]);
if (!$cat) {
    echo "Creating category: $category_name\n";
    // Use core_course_category::create with an array
    $newcat = [
        'name' => $category_name,
        'parent' => 0,
    ];
    $cat = core_course_category::create($newcat);
}

// Check for Page module
$module = $DB->get_record('modules', ['name' => 'page']);
if (!$module) {
    die("Page module not found. Cannot add content.\n");
}

foreach ($courses_data as $data) {
    echo "Processing: " . $data['fullname'] . "\n";

    // Check if course exists
    if ($DB->record_exists('course', ['shortname' => $data['shortname']])) {
        echo " - Course " . $data['shortname'] . " already exists. Skipping.\n";
        continue;
    }

    // Create Course object
    $course = new stdClass();
    $course->fullname = $data['fullname'];
    $course->shortname = $data['shortname'];
    $course->category = $cat->id;
    $course->summary = $data['summary'];
    $course->format = 'topics';
    $course->numsections = 3;
    $course->startdate = time();
    $course->visible = 1;

    try {
        $course = create_course($course);
        echo " - Created course ID: " . $course->id . "\n";
    } catch (Exception $e) {
        echo " - Error creating course: " . $e->getMessage() . "\n";
        continue;
    }

    // Add a sample lecture (Page module)
    // 1. Ensure section 1 exists
    course_create_sections_if_missing($course, 1);
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);

    // 2. Create CM record
    $cm = new stdClass();
    $cm->course = $course->id;
    $cm->module = $module->id;
    $cm->instance = 0; 
    $cm->section = $section->id;
    $cm->idnumber = '';
    $cm->added = time();
    $cm->score = 0;
    $cm->indent = 0;
    $cm->visible = 1;
    $cm->visibleold = 1;
    $cm->groupmode = 0;
    $cm->groupingid = 0;
    $cm->completion = 1;
    $cm->completionview = 0;
    $cm->completionexpected = 0;
    $cm->showdescription = 0;

    $cmid = $DB->insert_record('course_modules', $cm);

    // 3. Prepare Instance Data
    $page = new stdClass();
    $page->course = $course->id;
    $page->name = 'Welcome to ' . $data['fullname'];
    $page->intro = 'Course Introduction';
    $page->introformat = FORMAT_HTML;
    $page->content = '<p>Welcome! This is a sample lecture for <strong>' . $data['fullname'] . '</strong>.</p>';
    $page->contentformat = FORMAT_HTML;
    $page->display = 0;
    $page->printheading = 1;
    $page->printintro = 1;
    $page->printlastmodified = 0;
    $page->coursemodule = $cmid; 

    // 4. Add Instance
    try {
        $instance_id = page_add_instance($page, null);
    } catch (Exception $e) {
        $DB->delete_records('course_modules', ['id' => $cmid]);
        echo " - Error creating page instance: " . $e->getMessage() . "\n";
        continue;
    }

    // 5. Link CM to Section
    course_add_cm_to_section($course, $cmid, 1);
    $DB->set_field('course_modules', 'visible', 1, ['id' => $cmid]);
    
    rebuild_course_cache($course->id);
    echo " - Added sample content.\n";
}

echo "Done.\n";