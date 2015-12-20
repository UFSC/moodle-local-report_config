<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/report_config:manage' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
        )
    )
);