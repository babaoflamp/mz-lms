<?php

/**
 * CLI script to enable guest access for all courses.
 *
 * Usage: php enable_guest_access.php [--courseid=ID]
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/enrol/guest/lib.php');
require_once($CFG->libdir . '/enrollib.php');

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
$longoptions = ['courseid::'];
$options = getopt($shortoptions, $longoptions);

// Get target courses
if (isset($options['courseid'])) {
    $courseid = (int)$options['courseid'];
    $courses = $DB->get_records('course', ['id' => $courseid]);
} else {
    // All courses except site course
    $sql = "SELECT * FROM {course} WHERE id != 1";
    $courses = $DB->get_records_sql($sql);
}

if (!$courses) {
    echo "No courses found\n";
    exit(1);
}

$enrol_guest = enrol_get_plugin('guest');
if (!$enrol_guest) {
    echo "Error: Guest enrollment plugin not found\n";
    exit(1);
}

$updated = 0;
$skipped = 0;

foreach ($courses as $course) {
    // Check if guest enrollment already exists
    $instance = $DB->get_record('enrol', [
        'courseid' => $course->id,
        'enrol' => 'guest'
    ]);

    if ($instance) {
        // Already has guest enrollment
        echo "[SKIP] Course {$course->id} ({$course->shortname}) - Guest access already enabled\n";
        $skipped++;
    } else {
        // Add guest enrollment
        try {
            $enrol_guest->add_instance($course);
            echo "[OK] Course {$course->id} ({$course->shortname}) - Guest access enabled\n";
            $updated++;
        } catch (Exception $e) {
            echo "[ERROR] Course {$course->id} ({$course->shortname}) - " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Updated: {$updated} courses\n";
echo "Skipped: {$skipped} courses (already had guest access)\n";
echo "Total: " . count($courses) . " courses\n";
