<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/report/unasus/locallib.php');

function get_activities_courses($categoryid = null) {

    $categoryid = $categoryid == null ? required_param('categoryid', PARAM_INT) : $categoryid;

    $courses = get_name_modulos($categoryid);
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

    $assigns    = report_unasus_query_assign_courses($ids_courses);
    $foruns     = report_unasus_query_forum_courses($ids_courses);
    $quizes     = report_unasus_query_quiz_courses($ids_courses);
    $databases  = report_unasus_query_database_courses($ids_courses);
    $scorms     = report_unasus_query_scorm_courses($ids_courses);
    $ltis       = report_unasus_query_lti_courses_moodle($ids_courses);

    $group_array = new report_unasus_GroupArray();

    foreach ($assigns as $atividade) {
        $group_array->add($atividade->course_id, new report_unasus_assign_activity_report_config($atividade));
    }

    foreach ($foruns as $forum) {
        $group_array->add($forum->course_id, new report_unasus_forum_activity_report_config($forum));
    }

    foreach ($quizes as $quiz) {
        $group_array->add($quiz->course_id, new report_unasus_quiz_activity_report_config($quiz));
    }

    foreach ($databases as $database) {
        $group_array->add($database->course_id, new report_unasus_db_activity_report_config($database));
    }

    foreach ($scorms as $scorm) {
        $group_array->add($scorm->course_id, new report_unasus_scorm_activity_report_config($scorm));
    }

    foreach ($ltis  as $lti) {
        $group_array->add($lti->course_id, new report_unasus_lti_activity_report_config($lti));
    }

    return $group_array->get_assoc();
}

function get_name_modulos($categoria_curso) {
    $modulos = report_unasus_get_id_nome_modulos($categoria_curso, 'get_records_sql', false);

    // Interar para criar array dos modulos separados por grupos
    $listall = array();
    $list = array();

    foreach ($modulos as $key => $modulo) {
        if ($modulo->depth == 1) {
            $listall[$key] = $modulo->fullname;
        } else {
            $list[$modulo->category][$key] = $modulo->fullname;
        }
    }

    foreach ($list as $key => $l) {
        array_push($listall, array($key => $l));
    }

    return $listall;
}
