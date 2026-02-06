<?php

/**
 * CLI script to create an H5P activity in a course.
 *
 * Usage: php create_h5p_activity.php --courseid=21 --section=1 --h5pfile=/path/to/file.h5p [--name="Activity Name"]
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
$longoptions = ['courseid:', 'section:', 'h5pfile:', 'name::'];
$options = getopt($shortoptions, $longoptions);

if (!isset($options['courseid']) || !isset($options['h5pfile'])) {
    echo "Usage: php create_h5p_activity.php --courseid=COURSEID --section=SECTIONNUM --h5pfile=/path/to/file.h5p [--name=\"Activity Name\"]\n";
    exit(1);
}

$courseid = (int)$options['courseid'];
$sectionnum = isset($options['section']) ? (int)$options['section'] : 0;
$h5pfilepath = $options['h5pfile'];
$activityname = $options['name'] ?? basename($h5pfilepath, '.h5p');

// Validate course
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Validate H5P file
if (!file_exists($h5pfilepath)) {
    echo "Error: H5P file not found: $h5pfilepath\n";
    exit(1);
}

// Get course module info for h5pactivity
$module = $DB->get_record('modules', ['name' => 'h5pactivity'], '*', MUST_EXIST);

try {
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
    $data->intro = '<p>H5P 인터랙티브 콘텐츠</p>';
    $data->introformat = FORMAT_HTML;
    $data->displayoptions = 15; // Binary: frame(1) + export(2) + embed(4) + copyright(8)
    $data->enabletracking = 1;
    $data->grademethod = 1;
    $data->reviewmode = 2;
    $data->visible = 1;
    $data->visibleoncoursepage = 1;

    // Grade settings
    $data->grade = 100;
    $data->gradecat = 0;

    // Upload the H5P file
    $fs = get_file_storage();
    $usercontext = context_user::instance($admin->id);

    // Prepare file record for draft area
    $draftitemid = file_get_unused_draft_itemid();
    $filerecord = [
        'contextid' => $usercontext->id,
        'component' => 'user',
        'filearea'  => 'draft',
        'itemid'    => $draftitemid,
        'filepath'  => '/',
        'filename'  => basename($h5pfilepath)
    ];

    // Create file in draft area
    $file = $fs->create_file_from_pathname($filerecord, $h5pfilepath);

    if (!$file) {
        echo "Error: Failed to upload H5P file\n";
        exit(1);
    }

    // Set packagefile to draft item id
    $data->packagefile = $draftitemid;

    // Create the activity
    $coursemodule = add_moduleinfo($data, $course);

    echo "Success! H5P activity created:\n";
    echo "- Activity ID: {$coursemodule->instance}\n";
    echo "- Course Module ID: {$coursemodule->coursemodule}\n";
    echo "- Name: {$activityname}\n";
    echo "- Section: {$sectionnum}\n";
    echo "- URL: {$CFG->wwwroot}/mod/h5pactivity/view.php?id={$coursemodule->coursemodule}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
