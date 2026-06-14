<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/report_config/lib.php');

/**
 * Testes unitários da classe Config do plugin local_report_config.
 *
 * Config::__construct percorre a árvore de atividades ($dados) e, comparando com
 * os campos do formulário enviado ($fromform), monta a lista das atividades NÃO
 * marcadas — exatamente o conjunto que add_or_update_config_report() persiste
 * para ocultar do relatório.
 *
 * Formato dos dados (conforme Config_form em config_form.php:56,79):
 *   $dados[$courseid][$moduleid][$activityid] = "$courseid-$moduleid-$activityid"
 *       O valor da folha é o NOME do checkbox montado no formulário.
 *   $fromform = objeto devolvido por moodleform::get_data(); cada checkbox MARCADO
 *       vira uma propriedade cujo nome é aquele mesmo "courseid-moduleid-activityid"
 *       (checkbox desmarcado não é enviado pelo navegador, logo não vira propriedade).
 *   Resultado: Config coleta o $activityid (a chave da folha) de toda atividade
 *       cujo nome de checkbox NÃO está presente em $fromform.
 *
 * @group local_report_config
 * @covers \Config
 */
class local_report_config_config_testcase extends advanced_testcase {

    /**
     * Atividades desmarcadas (nome de checkbox ausente no form) têm seu activityid
     * coletado e agrupado por curso e por módulo; as marcadas são ignoradas.
     */
    public function test_unchecked_activities_are_collected() {
        // module_id 5 e 6, activity ids 11/12/20; o valor é o nome do checkbox.
        $dados = array(
            100 => array(
                5 => array(
                    11 => '100-5-11', // Marcada.
                    12 => '100-5-12', // Desmarcada -> coleta activityid 12.
                ),
                6 => array(
                    20 => '100-6-20', // Marcada -> módulo sem entrada.
                ),
            ),
            200 => array(
                7 => array(
                    30 => '200-7-30', // Desmarcada -> coleta activityid 30.
                ),
            ),
        );

        // Propriedades do form = checkboxes marcados. 'categoryid' (hidden) e
        // 'submitbutton' são ruído realista do moodleform e não devem interferir.
        $fromform = (object) array(
            '100-5-11' => 1,
            '100-6-20' => 1,
            'categoryid' => 42,
            'submitbutton' => 'Salvar',
        );

        $config = new Config($dados, $fromform, 42);

        $expected = array(
            100 => array(5 => array(12)),
            200 => array(7 => array(30)),
        );
        $this->assertSame($expected, $config->get_config_report());
    }

    /**
     * Quando todas as atividades estão marcadas, nada é coletado.
     */
    public function test_all_checked_collects_nothing() {
        $dados = array(
            100 => array(
                5 => array(11 => '100-5-11', 12 => '100-5-12'),
            ),
        );
        $fromform = (object) array(
            '100-5-11' => 1,
            '100-5-12' => 1,
            'categoryid' => 7,
            'submitbutton' => 'Salvar',
        );

        $config = new Config($dados, $fromform, 7);

        $this->assertSame(array(), $config->get_config_report());
    }

    /**
     * O categoryid recebido é preservado no objeto.
     */
    public function test_categoryid_is_preserved() {
        $config = new Config(array(), (object) array('submitbutton' => 'Salvar'), 99);

        $this->assertSame(99, $config->categoryid);
        $this->assertSame(array(), $config->get_config_report());
    }

    /**
     * Teste de caracterização: documenta um comportamento sutil do construtor.
     *
     * A coleta acontece dentro de um laço sobre $fromform (com break). Se o form
     * vier VAZIO, o laço interno nunca executa e NENHUMA atividade é coletada —
     * mesmo que, do ponto de vista do usuário, todas estejam desmarcadas. Este
     * teste fixa o comportamento atual; se a intenção for coletar todas nesse
     * caso, a mudança quebrará aqui de propósito.
     */
    public function test_empty_form_collects_nothing_even_when_all_unchecked() {
        $dados = array(
            100 => array(5 => array(11 => '100-5-11')),
        );
        $fromform = (object) array(); // Nenhum campo enviado.

        $config = new Config($dados, $fromform, 1);

        $this->assertSame(array(), $config->get_config_report());
    }
}
