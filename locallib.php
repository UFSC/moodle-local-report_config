<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/coursecatlib.php');

function get_activities_courses() {

    $categoryid = required_param('categoryid', PARAM_INT);

    $courses = get_nome_modulos($categoryid);
    $ids_courses = array();

    foreach ($courses as $id => $course) {
        // Verificação necessária para cursos com Turma A e B
        if(!is_array($course)){
            $ids_courses[] = $id;
        } else {
            foreach ($course as $c) {
                foreach ($c as $id_course => $course_name) {
                    // Get the ID for each course
                    $ids_courses[] = $id_course;
                }
            }
        }
    }

    $assigns = query_assign_courses($ids_courses);
    $foruns = query_forum_courses($ids_courses);
    $quizes = query_quiz_courses($ids_courses);
    $databases = query_database_courses($ids_courses);
    $scorms = query_scorm_courses($ids_courses);

    $group_array = new GroupArray();

    foreach ($assigns as $atividade) {
        $group_array->add($atividade->course_id, new report_unasus_assign_activity_config($atividade));
    }

    foreach ($foruns as $forum) {
        $group_array->add($forum->course_id, new report_unasus_forum_activity_config($forum));
    }

    foreach ($quizes as $quiz) {
        $group_array->add($quiz->course_id, new report_unasus_quiz_activity_config($quiz));
    }

    foreach ($databases as $database) {
        $group_array->add($database->course_id, new report_unasus_db_activity_config($database));
    }

    foreach ($scorms as $scorm) {
        $group_array->add($scorm->course_id, new report_unasus_scorm_activity_config($scorm));
    }

    return $group_array->get_assoc();
}
