@local_report_config @javascript
Feature: Salvar a configuração de atividades dos relatórios
  Para controlar quais atividades aparecem nos relatórios da UNA-SUS
  Como gestor com a capability local/report_config:manage
  Preciso desmarcar atividades no formulário e ter essa escolha persistida,
  de modo que ao reabrir o formulário elas continuem desmarcadas.

  # O formulário (Config_form) lista um checkbox por atividade do curso, rotulado
  # pelo nome da atividade. Na primeira vez todos vêm marcados (empty($settings)).
  # Ao salvar, Config::add_or_update_config_report() grava em activities_course_config
  # apenas as atividades DESMARCADAS; numa nova visita os defaults são relidos do banco.
  # report_unasus só lista atividades com rastreamento de conclusão habilitado
  # (a query filtra por cm.completion != 0), por isso enablecompletion e completion.
  Background:
    Given the following config values are set as admin:
      | enablecompletion | 1 |
    And the following "categories" exist:
      | name                | category | idnumber |
      | Categoria com curso | 0        | CATA     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Curso A  | curaA     | CATA     | 1                |
    And the following "activities" exist:
      | activity | course | name              | idnumber | intro      | completion |
      | assign   | curaA  | Atividade Visivel | a1       | Enviar A   | 1          |
      | assign   | curaA  | Atividade Oculta  | a2       | Enviar B   | 1          |
    And the following "users" exist:
      | username | firstname | lastname |
      | gestor   | Gabriela  | Gestora  |
    And the following "role assigns" exist:
      | user   | role    | contextlevel | reference |
      | gestor | manager | Category     | CATA      |
    And the following "permission overrides" exist:
      | capability                 | permission | role    | contextlevel | reference |
      | local/report_config:manage | Allow      | manager | Category     | CATA      |

  Scenario: Atividade desmarcada é persistida e reaparece desmarcada ao reabrir
    Given I log in as "gestor"
    When I am on the report config edit page for "CATA"
    # Primeira carga: sem configuração salva, todas as atividades vêm marcadas.
    # Isto também prova que as atividades do curso surgem no formulário.
    Then the field "Atividade Visivel" matches value "1"
    And the field "Atividade Oculta" matches value "1"
    # Desmarca uma atividade e salva.
    And I set the field "Atividade Oculta" to ""
    And I press "Save changes"
    # GET novo: os defaults são relidos de activities_course_config.
    And I am on the report config edit page for "CATA"
    Then the field "Atividade Visivel" matches value "1"
    And the field "Atividade Oculta" matches value ""
