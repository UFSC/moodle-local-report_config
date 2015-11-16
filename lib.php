<?php

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');

function local_report_config_extends_settings_navigation(navigation_node $navigation) {
    global $PAGE, $DB;

    $categoryid = $PAGE->context->instanceid;

    $already_configured = $DB->get_records('activities_course_config', array('categoryid' => $categoryid));
    $show_config_option = $DB->get_record('course_categories', array('id' => $PAGE->context->instanceid));

    if (is_a($PAGE->context, 'context_coursecat') && has_capability('local/report_config:manage', $PAGE->context)) {
        $category_node = $navigation->get('categorysettings');

        if ($category_node && $show_config_option->coursecount > 1) {
            $category_node->add(
                get_string('reportconfig', 'local_report_config'),
                new moodle_url('/local/report_config/edit.php', array('categoryid' => $categoryid)),
                navigation_node::TYPE_SETTING);

            if (empty($already_configured)){

                $atividades_curso = get_activities_courses($categoryid);

                foreach ($atividades_curso as $id_course => $activities){
                    foreach ($activities as $atividade) {
                        $record = new stdClass();
                        $record->activityid = $atividade->id;
                        $record->courseid = $atividade->course_id;
                        $record->categoryid = $categoryid;

                        $DB->insert_record('activities_course_config', $record);
                    }
                }
            }
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

        $DB->delete_records('activities_course_config', array('categoryid' => $this->categoryid));

        foreach ($this->config_report as $courseid => $config) {

            foreach ($config as $activity) {
                $record2 = new stdClass();
                $record2->activityid = $activity;
                $record2->courseid = $courseid;
                $record2->categoryid = $this->categoryid;

                $DB->insert_record('activities_course_config', $record2);
            }
        }
    }
}