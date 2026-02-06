<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

/**
 * Capabilities for local_speechpro.
 *
 * @package    local_speechpro
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/speechpro:use' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],
];
