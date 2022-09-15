<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/report_config/locallib.php');

function local_report_config_extend_settings_navigation(navigation_node $navigation) {
    global $PAGE, $DB;

    $categoryid = $PAGE->context->instanceid;

    $already_configured = $DB->get_records('activities_course_config', array('categoryid' => $categoryid));
    $show_config_option = $DB->get_record('course_categories', array('id' => $PAGE->context->instanceid));

    if (is_a($PAGE->context, 'context_coursecat') && has_capability('local/report_config:manage', $PAGE->context)) {
        $category_node = $navigation->get('categorysettings');

        if ($category_node && $show_config_option->coursecount >= 1) {
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

                        $DB->delete_records('activities_course_config', array(
                                                                            'categoryid' => $record->categoryid,
                                                                            'courseid' => $record->courseid,
                                                                            'activityid' => $record->activityid));
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

        //Transformação de um objeto e seus respectivos atributos em um array
        $arrayform = get_object_vars($fromform);
        $uncheckedActivities = array();

        //TODO: otimizar laço.
        //$dados = todos os cursos (de todas atividades listadas)
        foreach ($dados as $course_id => $module_data) {

            //$module_data = passa por todos os module_id (tipos de módulos)
            foreach ($module_data as $module_id => $activities) {

                //$activities = todas atividades listadas (por um curso = $course_id)
                foreach ($activities as $id_activity => $activity) {

                    // passar por todos da lista de checados do form
                    foreach ($fromform as $id_activity_form => $data_form) {

                        if (!array_key_exists($activity, $arrayform)) {
                        $uncheckedActivities[$course_id][$module_id][] = $id_activity;
                            break;
                        }
                    }
                }
            }
        }

        $this->config_report = $uncheckedActivities;
        $this->categoryid = $categoryid;
    }

    function get_config_report() {
        return $this->config_report;
    }

    /*
     * Função executada após clicar no botão de Salvar do formulário (onde cria-se uma nova configuração)
     * de configuração de relatórios.
     * Seu funcionamento é deletar todas as entradas na tabela e adicionar as entradas do array $config_report que foi
     * contruído durante a execução desta classe. Este array guarda as atividades não checadas do formulário.
     */
    function add_or_update_config_report() {
        global $DB;

        $DB->delete_records('activities_course_config', array('categoryid' => $this->categoryid));

        foreach ($this->config_report as $courseid => $modules) {

            foreach ($modules as $moduleid => $activities) {#$activity) {

                foreach ($activities as $activity_order => $data) {#$activity) {

                    $record2 = new stdClass();
                    $record2->activityid = $data;
                    $record2->moduleid = $moduleid;
                    $record2->courseid = $courseid;
                    $record2->categoryid = $this->categoryid;

                    $DB->insert_record('activities_course_config', $record2);
                }
            }
        }
    }
}