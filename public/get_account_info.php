<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->libdir . '/clilib.php');

// Get admin user (usually id 2)
$admin = $DB->get_record('user', array('id' => 2));

if ($admin) {
    echo "Admin Account Info:\n";
    echo "ID: " . $admin->id . "\n";
    echo "Username: " . $admin->username . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Auth: " . $admin->auth . "\n";
    echo "Suspended: " . $admin->suspended . "\n";
} else {
    echo "Admin user not found (id=2).\n";
}

// List all users count
$count = $DB->count_records('user', array('deleted' => 0));
echo "Total active users: " . $count . "\n";
