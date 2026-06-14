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
 * Testes de elegibilidade e visibilidade das atividades no local_report_config.
 *
 * @package    local_report_config
 * @category   test
 * @copyright  2026 UFSC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/report_config/locallib.php');
require_once($CFG->dirroot . '/lib/completionlib.php'); // Constantes COMPLETION_TRACKING_*.

/**
 * Testes de elegibilidade e visibilidade das atividades listadas para configuração.
 *
 * get_ordered_courses_activities() é a função usada tanto pelo formulário
 * (Config_form) quanto pela navegação. Uma atividade é listada para configuração
 * quando tem rastreamento de conclusão (cm.completion != 0) e é de um tipo
 * suportado (assign/forum/quiz/data/scorm/lti) — esse é o gate real da query
 * report_unasus_query_activities_ordered_courses().
 *
 * Requisito coberto aqui: a atividade elegível deve aparecer para ser configurada
 * MESMO com o curso oculto ou a atividade oculta (sem filtro de cm.visible nem de
 * c.visible no caminho do report_config). A nota é exibida, porém não é gate.
 *
 * @group local_report_config
 * @covers ::get_ordered_courses_activities
 */
class local_report_config_locallib_testcase extends advanced_testcase {

    /**
     * Cria categoria + curso + um assign elegível (conclusão manual + nota),
     * variando a visibilidade do curso e da atividade.
     *
     * @param int $coursevisible 1 visível, 0 oculto
     * @param int $activityvisible 1 visível, 0 oculto
     * @param int $completion COMPLETION_TRACKING_* (NONE torna a atividade inelegível)
     * @return array [categoryid, courseid, cmid, nome da atividade]
     */
    private function setup_activity($coursevisible, $activityvisible, $completion) {
        $this->resetAfterTest(true);
        // Conclusão precisa estar habilitada no site para que o módulo mantenha
        // cm.completion != 0 ao ser criado (add_moduleinfo zera caso contrário).
        set_config('enablecompletion', 1);

        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $course = $generator->create_course(array(
            'category' => $category->id,
            'enablecompletion' => 1,
            'visible' => $coursevisible,
        ));
        $assign = $generator->create_module('assign', array(
            'course' => $course->id,
            'name' => 'Atividade Elegivel',
            'completion' => $completion,
            'grade' => 100,
            'visible' => $activityvisible,
        ));

        return array($category->id, $course->id, $assign->cmid, 'Atividade Elegivel');
    }

    /**
     * Nomes das atividades listadas por get_ordered_courses_activities para um curso.
     *
     * @param int $categoryid id da categoria
     * @param int $courseid id do curso
     * @return string[]
     */
    private function listed_activity_names($categoryid, $courseid) {
        $result = get_ordered_courses_activities($categoryid);
        $names = array();
        if (isset($result[$courseid])) {
            foreach ($result[$courseid] as $activity) {
                $names[] = $activity->name;
            }
        }
        return $names;
    }

    /**
     * Caso base: curso visível + atividade visível, elegível, é listada.
     * Também confirma que a conclusão de fato persistiu (cm.completion != 0),
     * pré-condição que, se falhar, mascararia todos os demais testes.
     */
    public function test_eligible_activity_is_listed() {
        global $DB;

        list($categoryid, $courseid, $cmid, $name) = $this->setup_activity(1, 1, COMPLETION_TRACKING_MANUAL);

        $this->assertNotEquals(0, $DB->get_field('course_modules', 'completion', array('id' => $cmid)),
            'Pré-condição: a atividade precisa ter conclusão habilitada (cm.completion != 0).');
        $this->assertContains($name, $this->listed_activity_names($categoryid, $courseid));
    }

    /** A atividade elegível continua listada mesmo com o CURSO oculto. */
    public function test_listed_even_when_course_is_hidden() {
        list($categoryid, $courseid, , $name) = $this->setup_activity(0, 1, COMPLETION_TRACKING_MANUAL);

        $this->assertContains($name, $this->listed_activity_names($categoryid, $courseid));
    }

    /** A atividade elegível continua listada mesmo com a ATIVIDADE oculta. */
    public function test_listed_even_when_activity_is_hidden() {
        list($categoryid, $courseid, , $name) = $this->setup_activity(1, 0, COMPLETION_TRACKING_MANUAL);

        $this->assertContains($name, $this->listed_activity_names($categoryid, $courseid));
    }

    /** A atividade elegível continua listada com curso E atividade ocultos. */
    public function test_listed_even_when_both_hidden() {
        list($categoryid, $courseid, , $name) = $this->setup_activity(0, 0, COMPLETION_TRACKING_MANUAL);

        $this->assertContains($name, $this->listed_activity_names($categoryid, $courseid));
    }

    /**
     * Negativo (torna os testes acima não-triviais): sem rastreamento de conclusão
     * a atividade NÃO é listada, ainda que visível e com nota. Documenta que o gate
     * é a conclusão, não a visibilidade.
     */
    public function test_activity_without_completion_is_not_listed() {
        list($categoryid, $courseid, , $name) = $this->setup_activity(1, 1, COMPLETION_TRACKING_NONE);

        $this->assertNotContains($name, $this->listed_activity_names($categoryid, $courseid));
    }
}
