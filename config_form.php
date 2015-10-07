<?php

require_once("$CFG->libdir/formslib.php");

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');
require_once('config_form.php');
require_once($CFG->dirroot . '/report/unasus/locallib.php');

$categoryid = required_param('categoryid', PARAM_INT);
$context = context_coursecat::instance($categoryid);
$base_url = new moodle_url("/local/report_config/index.php", array('categoryid' => $categoryid));

$PAGE->set_url($base_url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_report_config'));
$PAGE->set_heading(get_string('pluginname', 'local_report_config'));

require_login();

class Config_form extends moodleform {

    private $dados = array();

    public function definition() {

        $categoryid = required_param('categoryid', PARAM_INT);
        $atividades_curso = get_activities_courses();

        $mform = $this->_form;

        foreach ($atividades_curso as $id_course => $activities){

            $i = 0;

            foreach ($activities as $activity){

                if(!isset($activities_module[$id_course])){
                    $activities_module[$id_course][] = $mform->addElement('static', 'course_name', $activity->course_name);
                    $mform->setType('course_name', PARAM_TEXT);
                }

                $name = $id_course . '-' . $i;

                $activities_module[$id_course][] = $mform->addElement('checkbox', $name, $activity->name);
                $mform->setType($name, PARAM_ALPHANUM);
                $mform->setDefault($name, true);

                $this->dados[$id_course][$activity->name] = $name;

                $i++;
            }

            $activities_module = '';
        }
        $mform->addElement('hidden', 'categoryid', $categoryid);
        $mform->setType('categoryid', PARAM_INT);

        $this->add_action_buttons();
    }

    function get_dados() {
        return $this->dados;
    }

}

