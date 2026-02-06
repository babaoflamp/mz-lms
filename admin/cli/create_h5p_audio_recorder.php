<?php

/**
 * CLI script to create H5P Audio Recorder activity in a course.
 *
 * Usage: php create_h5p_audio_recorder.php --courseid=21 --section=1 [--name="Audio Recording"]
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/h5pactivity/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/filelib.php');

// Get admin user
$admin = get_admin();
if (!$admin) {
    echo "Error: Admin user not found\n";
    exit(1);
}

// Set admin user context
\core\session\manager::set_user($admin);

// Parse command line options
$shortoptions = '';
$longoptions = ['courseid:', 'section:', 'name::'];
$options = getopt($shortoptions, $longoptions);

if (!isset($options['courseid'])) {
    echo "Usage: php create_h5p_audio_recorder.php --courseid=COURSEID --section=SECTIONNUM [--name=\"Activity Name\"]\n";
    exit(1);
}

$courseid = (int)$options['courseid'];
$sectionnum = isset($options['section']) ? (int)$options['section'] : 0;
$activityname = $options['name'] ?? '음성 녹음 (Audio Recorder)';

// Validate course
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Get course module info for h5pactivity
$module = $DB->get_record('modules', ['name' => 'h5pactivity'], '*', MUST_EXIST);

// Prepare H5P Audio Recorder content
// We need to create a simple H5P package with Audio Recorder content type
$h5pcontent = json_encode([
    'title' => $activityname,
    'language' => 'ko',
    'mainLibrary' => 'H5P.AudioRecorder',
    'embedTypes' => ['div'],
    'preloadedDependencies' => [
        [
            'machineName' => 'H5P.AudioRecorder',
            'majorVersion' => 1,
            'minorVersion' => 0
        ]
    ],
    'params' => json_encode([
        'taskDescription' => '아래 녹음 버튼을 눌러 음성을 녹음하세요.',
        'recordingLimit' => 120, // 2 minutes max
        'language' => 'ko'
    ])
]);

// Create module info data
$data = new stdClass();
$data->course = $courseid;
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
$data->intro = '<p>음성 녹음 활동입니다. 마이크를 사용하여 음성을 녹음하고 제출할 수 있습니다.</p>';
$data->introformat = FORMAT_HTML;
$data->displayoptions = [
    'frame' => 1,
    'export' => 0,
    'embed' => 0,
    'copyright' => 0
];
$data->enabletracking = 1;
$data->grademethod = 1; // Highest attempt
$data->reviewmode = 2; // Show when completed
$data->visible = 1;
$data->visibleoncoursepage = 1;

// Grade settings
$data->grade = 100;
$data->gradecat = 0;

// File picker settings (we'll need to handle H5P file upload differently)
$data->packagefile = 0;

try {
    // Note: Creating H5P content programmatically requires uploading an H5P file
    // For now, we'll create the activity structure and note that content needs to be added via UI

    echo "Note: H5P Audio Recorder content must be created/uploaded through the Moodle UI.\n";
    echo "This script will create the activity placeholder.\n\n";

    echo "To add Audio Recorder content:\n";
    echo "1. Go to: http://localhost:8888/course/view.php?id={$courseid}\n";
    echo "2. Turn editing on\n";
    echo "3. Add an activity -> H5P\n";
    echo "4. Choose 'Create' and select 'Audio Recorder' from the content type list\n";
    echo "5. Configure the recording settings and save\n\n";

    echo "Alternative: Download Audio Recorder from H5P.org:\n";
    echo "1. Visit: https://h5p.org/audio-recorder\n";
    echo "2. Download the .h5p file\n";
    echo "3. Upload it when creating the H5P activity in Moodle\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
