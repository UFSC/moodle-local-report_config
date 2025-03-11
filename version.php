<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025031101; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires = 2014111006; // Requires this Moodle version (2.8.5)
$plugin->component = 'local_report_config'; // Full name of the plugin (used for diagnostics)

$plugin->maturity  = MATURITY_BETA; // this version's maturity level

$plugin->dependencies = array(
    'report_unasus' => 2025031101
);
