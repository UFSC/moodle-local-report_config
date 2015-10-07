<?php

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

$renderer = $PAGE->get_renderer('local_report_config');

echo $renderer->page_header();

$mform = new Config_form();

if ($fromform = $mform->get_data()) {
    $dados = $mform->get_dados();

    $config = new Config($dados, $fromform, $categoryid);

    echo 'Configuração salva!';
} else {
    $mform->display();
}

echo $renderer->page_footer();