<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/coursecatlib.php');

function get_activities_courses() {

    $categoryid = required_param('categoryid', PARAM_INT);

    $courses = get_nome_modulos($categoryid);
    $ids_courses = array();

    foreach ($courses as $id => $course) {
        foreach ($course as  $c) {
            foreach ($c as $id_course => $course_name) {
                // Get the ID for each course
                $ids_courses[] = $id_course;
            }
        }
    }

   return get_atividades_cursos($ids_courses, false, false, false);
}
