<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

echo "Libraries loaded.\n";

$course_shortname = 'KOR102';
$course = $DB->get_record('course', ['shortname' => $course_shortname]);

if (!$course) {
    die("Course $course_shortname not found. Please run create_sample_lecture.php first.\n");
}

$module = $DB->get_record('modules', ['name' => 'quiz']);
if (!$module) {
    die("Quiz module not found.\n");
}

$topics = [
    2 => ['title' => 'Lesson 2: Numbers (숫자)', 'intro' => 'Learn Korean numbers (Sino-Korean and Native Korean).'],
    3 => ['title' => 'Lesson 3: Shopping (쇼핑)', 'intro' => 'How to ask for prices and buy items.'],
    4 => ['title' => 'Lesson 4: Restaurant (식당)', 'intro' => 'Ordering food and drinks.'],
    5 => ['title' => 'Lesson 5: Transportation (교통)', 'intro' => 'Taking the bus, subway, and taxi.'],
    6 => ['title' => 'Lesson 6: Directions (길 묻기)', 'intro' => 'Asking for and giving directions.'],
    7 => ['title' => 'Lesson 7: Hotel (호텔)', 'intro' => 'Checking in and out of hotels.'],
    8 => ['title' => 'Lesson 8: Emergencies (비상 상황)', 'intro' => 'Asking for help in emergencies.'],
    9 => ['title' => 'Lesson 9: Weather (날씨)', 'intro' => 'Talking about the weather.'],
    10 => ['title' => 'Lesson 10: Review (복습)', 'intro' => 'Review of all previous lessons.'],
];

foreach ($topics as $section_num => $topic) {
    $quiz_name = $topic['title'];

    // Check if exists
    if ($DB->record_exists('quiz', ['course' => $course->id, 'name' => $quiz_name])) {
        echo "Quiz '$quiz_name' already exists.\n";
        continue;
    }

    // Ensure section exists
    course_create_sections_if_missing($course, $section_num);
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $section_num], '*', MUST_EXIST);

    // Prepare Quiz Data
    $quiz = new stdClass();
    $quiz->course = $course->id;
    $quiz->name = $quiz_name;
    $quiz->intro = $topic['intro'];
    $quiz->introformat = FORMAT_HTML;
    $quiz->timeopen = 0;
    $quiz->timeclose = 0;
    $quiz->timelimit = 0;
    $quiz->overduehandling = 'autosubmit';
    $quiz->graceperiod = 0;
    $quiz->preferredbehaviour = 'deferredfeedback';
    $quiz->attempts = 0;
    $quiz->attemptonlast = 0;
    $quiz->grademethod = QUIZ_GRADEHIGHEST;
    $quiz->decimalpoints = 2;
    $quiz->questiondecimalpoints = -1;
    $quiz->reviewattempt = 69888;
    $quiz->reviewcorrectness = 4352;
    $quiz->reviewmarks = 4352;
    $quiz->reviewspecificfeedback = 4352;
    $quiz->reviewgeneralfeedback = 4352;
    $quiz->reviewrightanswer = 4352;
    $quiz->reviewoverallfeedback = 4352;
    $quiz->questionsperpage = 1;
    $quiz->navmethod = 'free';
    $quiz->shuffleanswers = 1;
    $quiz->sumgrades = 0;
    $quiz->grade = 10;
    $quiz->timecreated = time();
    $quiz->timemodified = time();
    $quiz->password = '';
    $quiz->subnet = '';
    $quiz->browsersecurity = '-';
    $quiz->delay1 = 0;
    $quiz->delay2 = 0;
    $quiz->showuserpicture = 0;
    $quiz->showblocks = 0;
    $quiz->completionattemptsexhausted = 0;
    $quiz->completionpass = 0;
    $quiz->completionminattempts = 0;
    $quiz->canredoquestions = 0;
    $quiz->allowofflineattempts = 0;

    // Create Dummy CM first
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
    $quiz->coursemodule = $cmid;

    try {
        $instance_id = quiz_add_instance($quiz);
    } catch (Exception $e) {
        $DB->delete_records('course_modules', ['id' => $cmid]);
        echo "Error creating quiz '$quiz_name': " . $e->getMessage() . "\n";
        if (isset($e->debuginfo)) {
            echo "Debug Info: " . $e->debuginfo . "\n";
        }
        continue;
    }

    // Add to section
    course_add_cm_to_section($course, $cmid, $section_num);
    $DB->set_field('course_modules', 'visible', 1, ['id' => $cmid]);

    echo "Created Quiz: $quiz_name\n";
}

rebuild_course_cache($course->id);
echo "Done!\n";
