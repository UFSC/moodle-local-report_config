<?php

defined('MOODLE_INTERNAL') || die();

class local_report_config_renderer extends plugin_renderer_base
{

    public function __construct(moodle_page $page, $target)
    {
        parent::__construct($page, $target);

        $this->categoryid = required_param('categoryid', PARAM_INT);
    }

    public function page_header()
    {
        $output = '';

        // Imprime cabeçalho da página
        $output .= $this->header();
        $title = get_string('reportconfig', 'local_report_config');
        $output .= $this->heading_with_help($title, 'reportconfig', 'local_report_config');

        return $output;
    }

    public function page_footer()
    {
        return $this->footer();
    }
}

