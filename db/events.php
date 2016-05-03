<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'   => '\core\event\course_updated',
        'callback'    => 'local_report_config_observer::report_checkbox_filler',
        'includefile' => '/local/report_config/classes/observer.php',
    ),

);