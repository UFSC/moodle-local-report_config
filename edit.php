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
 * Página de edição da configuração de relatórios da categoria.
 *
 * @package    local_report_config
 * @copyright  2026 UFSC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/report_config/locallib.php');
require_once($CFG->dirroot . '/local/report_config/config_form.php');
require_once($CFG->dirroot . '/report/unasus/locallib.php');

$categoryid = required_param('categoryid', PARAM_INT);
$context = context_coursecat::instance($categoryid);
$base_url = new moodle_url("/local/report_config/edit.php", array('categoryid' => $categoryid));

$PAGE->set_url($base_url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_report_config'));
$PAGE->set_heading(get_string('pluginname', 'local_report_config'));

require_login();

$renderer = $PAGE->get_renderer('local_report_config');

$returnurl = new moodle_url('/local/report_config/index.php', array('categoryid' => $categoryid));

echo $renderer->page_header();

$mform = new Config_form();

if ($mform->is_cancelled()) {
//    redirect($returnurl);
} else if ($fromform = $mform->get_data()) {
    $dados = $mform->get_dados();

    $config = new Config($dados, $fromform, $categoryid);
    $config->add_or_update_config_report();
}

$mform->display();

echo $renderer->page_footer();