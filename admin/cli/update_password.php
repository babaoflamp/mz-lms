<?php

/**
 * CLI script to update user passwords
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/moodlelib.php');

$admin = $DB->get_record('user', ['username' => 'admin']);
if ($admin) {
    echo "Found admin user: {$admin->username}\n";
    $admin->password = hash_internal('Mz1234!@');
    $DB->update_record('user', $admin);
    echo "✓ Admin password updated to: Mz1234!@\n";
} else {
    echo "✗ Admin user not found\n";
}

$student = $DB->get_record('user', ['username' => 'student']);
if ($student) {
    echo "Found student user: {$student->username}\n";
    $student->password = hash_internal('Mz1234!@');
    $DB->update_record('user', $student);
    echo "✓ Student password updated to: Mz1234!@\n";
} else {
    echo "✗ Student user not found\n";
}

echo "\nPassword update completed!\n";
