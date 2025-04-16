<?php

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/report_config/locallib.php');
require_once($CFG->dirroot . '/local/report_config/config_form.php');
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

        global $DB;

        $categoryid = required_param('categoryid', PARAM_INT);
        $atividades_curso = get_ordered_courses_activities();

        $mform = $this->_form;

        $settings = $DB->get_records('activities_course_config', array('categoryid' => $categoryid));

        $activities_module = array();
        // passa por todos os cursos ($id_course, $activities)
        foreach ($atividades_curso as $id_course => $activities){

            // passa por todas as atividades ($activities) de um curso ($id_course)
            foreach ($activities as $activity){

                $mform->addElement('html', '<div>');

                if(!isset($activities_module[$id_course])){
                    $mform->addElement('html', '<h4>');

                    $activities_module[$id_course][] = $mform->addElement('html', $activity->course_name);

                    $mform->addElement('html', '</h4>');
                    $mform->setType('course_name', PARAM_TEXT);
                }

                $name = $id_course . '-' . $activity->module_id . '-' . $activity->id;

                $mform->addElement('html', '</div>');

                $activities_module[$id_course][] = $mform->addElement('checkbox', $name, $activity->name);
                $mform->setType($name, PARAM_ALPHANUM);


                // Se ainda não há configuração para o relatório, o default são todas atividades selecionadas
                if(empty($settings)) {
                    $mform->setDefault($name, true);
                } else {
                    foreach($settings as $config){

                        $mform->setDefault($name,( ($config->moduleid != $activity->module_id)
                            or ($config->activityid != $activity->id)) ) ;
                        if ( (($config->moduleid == $activity->module_id)
                            and ($config->activityid == $activity->id) )) {
                            break;
                        }
                    }
                }

                $this->dados[$id_course][$activity->module_id][$activity->id] = $name;
            }

            $activities_module = array();
        }

        $mform->addElement('hidden', 'categoryid', $categoryid);
        $mform->setType('categoryid', PARAM_INT);

        $this->add_action_buttons();
    }

    function get_dados() {
        return $this->dados;
    }

}
