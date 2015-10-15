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

class Config {

    public $config_report; //Array com os dados de configuração
    public $categoryid;

    function __construct($dados, $fromform, $categoryid) {

        $config_report = array();

        foreach ($dados as $course_id => $data) {
            foreach ($data as $name_activity => $activity) {
                foreach ($fromform as $id_activity => $data_form) {
                    if ($activity == $id_activity){
                        $config_report[$course_id][] = $name_activity;
                    }
                }
            }
        }

        $this->config_report = $config_report;
        $this->categoryid = $categoryid;
    }

    function get_config_report() {
        return $this->config_report;
    }

    function add_or_update_config_report() {
        global $DB;

        $DB->delete_records('local_report_config');
        $DB->delete_records('activities_course_config');

        foreach ($this->config_report as $courseid => $config) {

            $record = new stdClass();
            $record->courseid = $courseid;
            $record->categoryid = $this->categoryid;

            $DB->insert_record('local_report_config', $record);

            foreach ($config as $activity) {
                $record2 = new stdClass();
                $record2->activityid = $activity;
                $record2->courseid = $courseid;

                $DB->insert_record('activities_course_config', $record2);
            }
        }
    }
}