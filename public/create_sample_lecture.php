<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/page/lib.php');
require_once($CFG->libdir . '/gradelib.php');

echo "Libraries loaded.\n";

// 1. Create Course
$course_shortname = 'KOR102';
echo "Checking course $course_shortname...\n";
$course = $DB->get_record('course', ['shortname' => $course_shortname]);

if ($course) {
    echo "Deleting existing KOR102 course to clean up...\n";
    delete_course($course, false);
}

echo "Creating new course...\n";
$course = new stdClass();
$course->fullname = 'Survival Korean for Travelers';
$course->shortname = $course_shortname;
$course->category = 1;
$course->summary = 'Essential phrases for your trip to Korea.';
$course->format = 'topics';
$course->numsections = 5;
$course->startdate = time();
$course->visible = 1;

// Find category 'Korean Language'
$cat = $DB->get_record('course_categories', ['name' => 'Korean Language']);
if ($cat) {
    $course->category = $cat->id;
} else {
    $firstcat = $DB->get_records('course_categories', null, 'id ASC', 'id', 0, 1);
    if ($firstcat) {
        $course->category = reset($firstcat)->id;
    } else {
        die("No course categories found!\n");
    }
}

try {
    $course = create_course($course);
} catch (Exception $e) {
    echo "Error creating course: " . $e->getMessage() . "\n";
    die;
}
echo "Created Course: " . $course->fullname . "\n";

// 2. Add 'Page' Module (Lecture)
$module = $DB->get_record('modules', ['name' => 'page']);
if (!$module) {
    die("Page module not found.\n");
}

$page_name = 'Lesson 1: Greetings (안녕하세요?)';

// Prepare data for page_add_instance
$data = new stdClass();
$data->course = $course->id;
$data->name = $page_name;
$data->intro = 'Learn basic greetings.';
$data->introformat = FORMAT_HTML;
$data->content = '
<div style="font-family: \'Noto Sans KR\', sans-serif;">
    <h3>기본 인사말 (Basic Greetings)</h3>
    <hr>
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>안녕하세요?</strong> (An-nyeong-ha-se-yo?)</p>
        <p style="color: #666;">Hello / How are you?</p>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>반갑습니다.</strong> (Ban-gap-sum-ni-da)</p>
        <p style="color: #666;">Nice to meet you.</p>
    </div>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>감사합니다.</strong> (Gam-sa-ham-ni-da)</p>
        <p style="color: #666;">Thank you.</p>
    </div>

    <p>Practice these phrases with your friends!</p>
</div>
';
$data->contentformat = FORMAT_HTML;
$data->display = 0; // Open
$data->displayoptions = [];
$data->printheading = 1;
$data->printintro = 1;
$data->printlastmodified = 0; // Required by page_add_instance
$data->popupwidth = 620;
$data->popupheight = 450;

// Ensure Section 1 exists
echo "Ensuring Section 1 exists...\n";
course_create_sections_if_missing($course, 1);
$section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1], '*', MUST_EXIST);

// 1. Create dummy Course Module first (needed for page_add_instance)
echo "Creating dummy Course Module...\n";
$cm = new stdClass();
$cm->course = $course->id;
$cm->module = $module->id;
$cm->instance = 0; // Will be updated by page_add_instance
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

// 2. Prepare Data
$data->coursemodule = $cmid; // Important!

// 3. Create Instance
echo "Creating Page Instance...\n";
try {
    $instance_id = page_add_instance($data, null);
} catch (Exception $e) {
    // Cleanup if failed
    $DB->delete_records('course_modules', ['id' => $cmid]);
    echo "Error creating page instance: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    die;
}

// 4. Add to Section
echo "Adding to Section...\n";
course_add_cm_to_section($course, $cmid, 1);

// Set visible
$DB->set_field('course_modules', 'visible', 1, ['id' => $cmid]);

echo "Added Lecture: $page_name\n";

rebuild_course_cache($course->id);

echo "Done!\n";
