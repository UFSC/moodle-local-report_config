<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Funções de biblioteca e extensão de navegação do plugin local_report_config.
 *
 * @package    local_report_config
 * @copyright  2026 UFSC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/report_config/locallib.php');

/**
 * Adiciona o link de configuração de relatórios às configurações da categoria.
 *
 * Só inclui o nó quando o contexto é de categoria, o usuário tem a capability
 * local/report_config:manage e a categoria possui ao menos um curso.
 *
 * @param navigation_node $navigation nó de navegação da página atual
 * @return void
 */
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

                $atividades_curso = get_ordered_courses_activities($categoryid);

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

/**
 * Monta e persiste a configuração das atividades exibidas nos relatórios.
 *
 * A partir dos dados do formulário, determina quais atividades foram desmarcadas
 * e grava esse conjunto em activities_course_config.
 */
class Config {

    /** @var array Atividades desmarcadas, agrupadas por curso e módulo. */
    public $config_report;

    /** @var int Id da categoria sendo configurada. */
    public $categoryid;

    /**
     * @param array $dados árvore [course_id][module_id][activity_id] => nome do checkbox
     * @param stdClass $fromform dados retornados por moodleform::get_data()
     * @param int $categoryid id da categoria sendo configurada
     */
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

    /**
     * Devolve as atividades desmarcadas, agrupadas por curso e módulo.
     *
     * @return array
     */
    function get_config_report() {
        return $this->config_report;
    }

    /**
     * Regrava activities_course_config para a categoria.
     *
     * Apaga todas as entradas da categoria e insere uma para cada atividade
     * desmarcada (o conjunto montado no construtor) — são as atividades ocultadas
     * dos relatórios.
     *
     * @return void
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
