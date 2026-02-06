<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

/**
 * CLI script to add SpeechPro embedded page to a course.
 *
 * @package    local_speechpro
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/page/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

list($options, $unrecognized) = cli_get_params([
    'courseid' => null,
    'section' => 1,
    'name' => 'SpeechPro 발음 평가',
    'help' => false,
], [
    'c' => 'courseid',
    's' => 'section',
    'n' => 'name',
    'h' => 'help',
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || empty($options['courseid'])) {
    $help = <<<EOT
Add SpeechPro embedded page to a course.

Options:
 -h, --help                Print out this help
 -c, --courseid            Course ID (required)
 -s, --section             Section number (default: 1)
 -n, --name                Activity name (default: SpeechPro 발음 평가)

Example:
 php local/speechpro/cli/add_embed_page.php --courseid=21 --section=1
EOT;
    echo $help;
    exit(0);
}

$courseid = (int) $options['courseid'];
$sectionnum = (int) $options['section'];
$name = trim($options['name']);

$course = $DB->get_record('course', ['id' => $courseid], '*');
if (!$course) {
    cli_error('Course not found: ' . $courseid);
}

global $USER, $PAGE;
$USER = get_admin();
\core\session\manager::set_user($USER);
$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_course($course);

course_create_sections_if_missing($course, $sectionnum);
$section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $sectionnum]);
if (!$section) {
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 0]);
}
if (!$section) {
    cli_error('Course section not found for course id ' . $courseid);
}

$module = $DB->get_record('modules', ['name' => 'page'], '*');
if (!$module) {
    cli_error('Page module not found in this Moodle site.');
}

$iframeurl = $CFG->wwwroot . '/local/speechpro/index.php';
$content = '<div style="position:relative;padding-top:56.25%;">
  <iframe src="' . $iframeurl . '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allow="microphone"></iframe>
</div>';

$data = new stdClass();
$data->course = $course->id;
$data->name = $name ?: 'SpeechPro 발음 평가';
$data->intro = 'SpeechPro 발음 평가 도구를 강좌 안에서 사용합니다.';
$data->introformat = FORMAT_HTML;
$data->content = $content;
$data->contentformat = FORMAT_HTML;
$data->display = 0;
$data->printheading = 1;
$data->printintro = 1;
$data->printlastmodified = 0;
$data->section = $sectionnum;
$data->visible = 1;
$data->visibleoncoursepage = 1;
$data->module = $module->id;
$data->modulename = 'page';
$data->instance = 0;
$data->add = 'page';
$data->revision = 0;

add_moduleinfo($data, $course);

cli_writeln('SpeechPro embedded page added to course ID ' . $courseid . ' (section ' . $sectionnum . ').');
