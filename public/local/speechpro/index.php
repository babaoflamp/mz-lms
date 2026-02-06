<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

/**
 * SpeechPro pronunciation evaluation page.
 *
 * @package    local_speechpro
 */

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('local/speechpro:use', context_system::instance());

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/speechpro/index.php'));
$PAGE->set_title(get_string('page_title', 'local_speechpro'));
$PAGE->set_heading(get_string('page_heading', 'local_speechpro'));

$PAGE->requires->js(new moodle_url('/local/speechpro/recorder.js'));
$PAGE->requires->strings_for_js([
    'status_ready',
    'status_recording',
    'status_processing',
    'status_done',
    'error_generic',
    'error_recording',
    'error_permission',
    'error_network',
], 'local_speechpro');

$renderer = $PAGE->get_renderer('core');

echo $renderer->header();
?>
<div class="local-speechpro">
    <div class="card">
        <div class="card-body">
            <h3 class="mb-3"><?php echo get_string('page_heading', 'local_speechpro'); ?></h3>
            <p class="text-muted"><?php echo get_string('page_intro', 'local_speechpro'); ?></p>

            <div class="form-group">
                <label for="speechpro-text"><?php echo get_string('label_text', 'local_speechpro'); ?></label>
                <input id="speechpro-text" type="text" class="form-control" placeholder="<?php echo get_string('placeholder_text', 'local_speechpro'); ?>">
            </div>

            <div class="d-flex gap-2 mb-3">
                <button id="speechpro-record" class="btn btn-primary">
                    <?php echo get_string('button_record', 'local_speechpro'); ?>
                </button>
                <button id="speechpro-stop" class="btn btn-secondary" disabled>
                    <?php echo get_string('button_stop', 'local_speechpro'); ?>
                </button>
                <button id="speechpro-evaluate" class="btn btn-success" disabled>
                    <?php echo get_string('button_evaluate', 'local_speechpro'); ?>
                </button>
            </div>

            <div id="speechpro-status" class="alert alert-info">
                <?php echo get_string('status_ready', 'local_speechpro'); ?>
            </div>

            <div id="speechpro-result" class="mt-3"></div>
        </div>
    </div>
</div>
<?php

// Footer.
echo $renderer->footer();
