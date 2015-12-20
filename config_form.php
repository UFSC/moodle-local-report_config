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

        global $DB;

        $categoryid = required_param('categoryid', PARAM_INT);
        $atividades_curso = get_activities_courses();

        $mform = $this->_form;

        $settings = $DB->get_records('activities_course_config', array('categoryid' => $categoryid));

        $tcc = new stdClass();
        $tcc->name = 'TCC';
        $tcc->id = 1;

        $tcc_module = end($atividades_curso);
        array_push($tcc_module, $tcc);

        array_pop($atividades_curso);
        array_push($atividades_curso, $tcc_module);

        foreach ($atividades_curso as $id_course => $activities){

            $i = 0;

            foreach ($activities as $activity){

                $mform->addElement('html', '<div>');

                if(!isset($activities_module[$id_course])){
                    $mform->addElement('html', '<h4>');

                    $activities_module[$id_course][] = $mform->addElement('html', $activity->course_name);

                    $mform->addElement('html', '</h4>');
                    $mform->setType('course_name', PARAM_TEXT);
                }

                /* Estrutura que traz os capítulos dos TCCs para dentro da configuração */

//                if (get_class($activity) == 'report_unasus_lti_activity'){
//                    $name = $activity->position . '-' . $i;
//
//                    /* Os capítulos do TCC tem todos o mesmo id da atividade. Posição do capítulo é usado como índice do array */
//                    $index = $activity->position;
//                } else {
//                    $name = $id_course . '-' . $i;
//                }

                $name = $id_course . '-' . $i;

                $mform->addElement('html', '</div>');

                $activities_module[$id_course][] = $mform->addElement('checkbox', $name, $activity->name);
                $mform->setType($name, PARAM_ALPHANUM);


                // Se ainda não há configuração para o relatório, o default são todas atividades selecionadas
                if(empty($settings)) {
                    $mform->setDefault($name, true);
                } else {
                    foreach($settings as $config){
                        if($config->activityid == $activity->id){
                            $mform->setDefault($name, true);
                        }
                    }
                }

                if ($activity->id != 10) {
                    $index = $activity->id;
                }

                $this->dados[$id_course][$index] = $name;

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

