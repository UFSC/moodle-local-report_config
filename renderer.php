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
 * Renderer do plugin local_report_config.
 *
 * @package    local_report_config
 * @copyright  2026 UFSC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderiza o cabeçalho e o rodapé das páginas de configuração de relatórios.
 */
class local_report_config_renderer extends plugin_renderer_base
{

    /** @var int Id da categoria sendo renderizada. */
    public $categoryid;

    /**
     * @param moodle_page $page página em renderização
     * @param string $target alvo de renderização (ver rendererfactory)
     */
    public function __construct(moodle_page $page, $target)
    {
        parent::__construct($page, $target);

        $this->categoryid = required_param('categoryid', PARAM_INT);
    }

    /**
     * Cabeçalho da página, com título e ícone de ajuda.
     *
     * @return string HTML
     */
    public function page_header()
    {
        $output = '';

        // Imprime cabeçalho da página
        $output .= $this->header();
        $title = get_string('reportconfig', 'local_report_config');
        $output .= $this->heading_with_help($title, 'reportconfig', 'local_report_config');

        return $output;
    }

    /**
     * Rodapé da página.
     *
     * @return string HTML
     */
    public function page_footer()
    {
        return $this->footer();
    }
}

