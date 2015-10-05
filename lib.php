<?php

defined('MOODLE_INTERNAL') || die();

function local_report_config_extends_settings_navigation(navigation_node $navigation) {
    global $PAGE;

    if (is_a($PAGE->context, 'context_coursecat')) { // && has_capability('local/tutores:manage', $PAGE->context)) {
        $category_node = $navigation->get('categorysettings');

        if ($category_node) {
            $category_node->add(
                get_string('reportconfig', 'local_report_config'),
                new moodle_url('/local/report_config/index.php', array('categoryid' => $PAGE->context->instanceid)),
                navigation_node::TYPE_SETTING); //, null, null, new pix_icon('icon', '', 'local_tutores'));
        }
    }
}