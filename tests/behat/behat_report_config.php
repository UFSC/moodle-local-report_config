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
 * Passos Behat específicos do local_report_config.
 *
 * @package    local_report_config
 * @category   test
 * @copyright  2026 UFSC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file is required by Behat before config.php.
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Definições de passos Behat específicas do local_report_config.
 *
 * O nome do arquivo PRECISA coincidir com o nome da classe para que o gerenciador
 * de configuração do Behat do Moodle o carregue como contexto de step-definition.
 */
class behat_report_config extends behat_base {

    /**
     * Visita a página de uma categoria de curso identificada pelo idnumber.
     *
     * É nesse contexto (CONTEXT_COURSECAT) que
     * local_report_config_extend_settings_navigation() injeta — ou não — o nó de
     * navegação "Configuração Relatórios", conforme a capability e o coursecount.
     *
     * @Given /^I am on the course category page for "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber idnumber da categoria
     */
    public function i_am_on_the_course_category_page_for($idnumber) {
        global $DB;

        $categoryid = $DB->get_field('course_categories', 'id', array('idnumber' => $idnumber), MUST_EXIST);
        $url = new moodle_url('/course/index.php', array('categoryid' => $categoryid));
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }

    /**
     * Visita o formulário de configuração de relatórios (edit.php) de uma categoria.
     *
     * Resolve a categoria pelo idnumber. Uma re-visita após salvar é um GET novo,
     * que reexecuta Config_form::definition() e relê activities_course_config para
     * definir os defaults dos checkboxes — é assim que se verifica a persistência.
     *
     * @Given /^I am on the report config edit page for "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber idnumber da categoria
     */
    public function i_am_on_the_report_config_edit_page_for($idnumber) {
        global $DB;

        $categoryid = $DB->get_field('course_categories', 'id', array('idnumber' => $idnumber), MUST_EXIST);
        $url = new moodle_url('/local/report_config/edit.php', array('categoryid' => $categoryid));
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }
}
