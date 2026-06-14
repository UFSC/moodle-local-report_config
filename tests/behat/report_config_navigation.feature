@local_report_config @javascript
Feature: Link de configuração de relatórios na navegação da categoria
  Para configurar quais atividades aparecem nos relatórios da UNA-SUS
  Como gestor com a capability local/report_config:manage
  Preciso ver o link "Configuração Relatórios" nas configurações da categoria,
  e somente quando a categoria tem ao menos um curso.

  # A capability local/report_config:manage não é concedida a nenhum papel por
  # padrão (db/access.php tem archetypes vazio), por isso ela é concedida via
  # permission override sobre o papel manager — e só nas categorias do "gestor".
  # O "naogestor" também tem o papel manager (logo, vê o menu de administração da
  # categoria), mas numa categoria SEM o override, isolando a guarda da capability.
  Background:
    Given the following "categories" exist:
      | name                | category | idnumber |
      | Categoria com curso | 0        | CATA     |
      | Categoria vazia     | 0        | CATV     |
      | Outra com curso     | 0        | CATB     |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Curso A  | curaA     | CATA     |
      | Curso B  | curaB     | CATB     |
    And the following "users" exist:
      | username  | firstname | lastname |
      | gestor    | Gabriela  | Gestora  |
      | naogestor | Nelson    | Comum    |
    And the following "role assigns" exist:
      | user      | role    | contextlevel | reference |
      | gestor    | manager | Category     | CATA      |
      | gestor    | manager | Category     | CATV      |
      | naogestor | manager | Category     | CATB      |
    And the following "permission overrides" exist:
      | capability                 | permission | role    | contextlevel | reference |
      | local/report_config:manage | Allow      | manager | Category     | CATA      |
      | local/report_config:manage | Allow      | manager | Category     | CATV      |

  Scenario: Gestor vê o link numa categoria que tem curso
    Given I log in as "gestor"
    When I am on the course category page for "CATA"
    Then "Configuração Relatórios" "link" should exist in current page administration

  Scenario: Gestor não vê o link numa categoria sem cursos
    Given I log in as "gestor"
    When I am on the course category page for "CATV"
    Then "Configuração Relatórios" "link" should not exist in current page administration

  Scenario: Usuário sem a capability não vê o link mesmo com curso na categoria
    Given I log in as "naogestor"
    When I am on the course category page for "CATB"
    Then "Configuração Relatórios" "link" should not exist in current page administration
