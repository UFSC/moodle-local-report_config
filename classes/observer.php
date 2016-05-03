<?php

defined('MOODLE_INTERNAL') || die();


class local_report_config_observer {

    public static function report_checkbox_filler(\core\event\course_updated $event) {
        global $CFG, $DB;

        //TODO: Verificar se esta verificação é realmente necessária
        if(is_siteadmin($event->userid) || isguestuser($event->userid)) {
            return true;        }


        $courseid = $event->courseid;

        //var_dump($categoryid);
        var_dump($courseid);
        exit();

        //TODO: Falta pegar o categoryid dinamicamente
        $DB->delete_records('activities_course_config', array('categoryid' => $categoryid, 'courseid' => $courseid));

    }
}