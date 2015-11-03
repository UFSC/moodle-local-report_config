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

$data = array();
$line = array();
$buttons = array();

$table = new html_table();

global $DB;
$already_configured = $DB->get_records('activities_course_config', array('categoryid' => $categoryid));

if ($already_configured){
    $line[] = 'Relatórios Configurados';

    $buttons[] = html_writer::link(new moodle_url('/local/report_config/edit.php', array('categoryid' => $categoryid)),
    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'title' => get_string('edit'), 'class' => 'iconsmall')));

    $line[] = implode(' ', $buttons);
    $table->head = array(
        get_string('status', 'local_report_config'),
        get_string('edit', 'local_report_config')
    );

} else {
    $line[] = 'Relatórios Não Configurados';

    $table->head = array(
        get_string('status', 'local_report_config'),
    );
}

$data[] = $line;

$table->colclasses = array('leftalign name', 'leftalign description', 'leftalign size', 'centeralign', 'centeralign source', 'centeralign action');
$table->id = 'relationships';
$table->attributes['class'] = 'admintable generaltable';
$table->data = $data;
echo html_writer::table($table);

if ($line[0] == 'Relatório Não Configurado'){
    echo $OUTPUT->single_button(new moodle_url('/local/report_config/edit.php', array('categoryid' => $categoryid)), get_string('add', 'local_report_config'));
}

echo $renderer->page_footer();