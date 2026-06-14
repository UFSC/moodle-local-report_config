@local_report_config @javascript
Feature: Atividades elegíveis aparecem para configuração mesmo ocultas
  Para configurar atividades de cursos em qualquer estado
  Como gestor com a capability local/report_config:manage
  Preciso ver no formulário as atividades elegíveis (com conclusão) ainda que o
  curso esteja oculto ou a própria atividade esteja oculta.

  # Três atividades elegíveis (assign com conclusão e nota) em três estados de
  # visibilidade. O formulário (get_ordered_courses_activities) não filtra por
  # c.visible nem cm.visible, então todas devem aparecer como checkbox.
  Background:
    Given the following config values are set as admin:
      | enablecompletion | 1 |
    And the following "categories" exist:
      | name      | category | idnumber |
      | Categoria | 0        | CATA     |
    And the following "courses" exist:
      | fullname      | shortname | category | enablecompletion | visible |
      | Curso Visivel | curaV     | CATA     | 1                | 1       |
      | Curso Oculto  | curaO     | CATA     | 1                | 0       |
    And the following "activities" exist:
      | activity | course | name                      | idnumber | intro | completion | grade | visible |
      | assign   | curaV  | Atividade Normal          | av1      | x     | 1          | 100   | 1       |
      | assign   | curaV  | Atividade Oculta          | av2      | x     | 1          | 100   | 0       |
      | assign   | curaO  | Atividade Em Curso Oculto | ao1      | x     | 1          | 100   | 1       |
    And the following "users" exist:
      | username | firstname | lastname |
      | gestor   | Gabriela  | Gestora  |
    And the following "role assigns" exist:
      | user   | role    | contextlevel | reference |
      | gestor | manager | Category     | CATA      |
    And the following "permission overrides" exist:
      | capability                 | permission | role    | contextlevel | reference |
      | local/report_config:manage | Allow      | manager | Category     | CATA      |

  Scenario: Atividades de curso oculto e atividade oculta aparecem para configuração
    Given I log in as "gestor"
    When I am on the report config edit page for "CATA"
    # Curso visível + atividade visível.
    Then the field "Atividade Normal" matches value "1"
    # Curso visível + atividade OCULTA.
    And the field "Atividade Oculta" matches value "1"
    # Curso OCULTO + atividade visível.
    And the field "Atividade Em Curso Oculto" matches value "1"
