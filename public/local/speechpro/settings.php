<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

/**
 * Settings for local_speechpro.
 *
 * @package    local_speechpro
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_speechpro', get_string('pluginname', 'local_speechpro'));

    $settings->add(new admin_setting_configtext(
        'local_speechpro/endpoint',
        get_string('endpoint', 'local_speechpro'),
        get_string('endpoint_desc', 'local_speechpro'),
        'http://112.220.79.222:33005/speechpro'
    ));

    $settings->add(new admin_setting_configtext(
        'local_speechpro/timeout',
        get_string('timeout', 'local_speechpro'),
        get_string('timeout_desc', 'local_speechpro'),
        '30'
    ));

    $ADMIN->add('localplugins', $settings);
}
