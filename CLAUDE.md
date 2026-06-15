# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## O que é

Plugin Moodle `local_report_config` (Moodle 3.8+). Permite, por **categoria de
curso**, escolher quais atividades aparecem nos relatórios UnA-SUS — atividades
não marcadas ficam ocultas na geração dos relatórios. Não tem configuração de
admin; toda a operação é por categoria.

É uma **camada fina de configuração sobre o `report_unasus`** (dependência rígida,
declarada em `version.php`). Este plugin não descobre atividades por conta própria:
ele apenas as lista (via funções do `report_unasus`) e persiste a escolha.

## Arquitetura (o essencial, que exige ler vários arquivos)

**Fonte das atividades** — `locallib.php::get_ordered_courses_activities($categoryid)`
delega inteiramente ao `report/unasus/locallib.php`
(`report_unasus_get_id_nome_modulos` → cursos da categoria;
`report_unasus_query_activities_ordered_courses` → atividades). Retorna
`[course_id => [report_unasus_generic_activity_report_config, ...]]` via
`report_unasus_GroupArray` (classe em `report/unasus/activities_datastructures.php`,
onde também vivem os objetos `report_unasus_*_activity_report_config`).

**Critério de aparição (não-óbvio)** — a query do report_unasus filtra por
`cm.completion != 0` e tipo de módulo suportado (assign/forum/quiz/data/scorm/lti).
Ou seja: **só atividades com rastreamento de conclusão habilitado aparecem**; nota
é exibida mas não é gate. **Não há filtro de visibilidade** — curso oculto ou
atividade oculta **ainda aparecem** para configuração (recurso intencional; ver
`get_name_modulos`, que chama o report_unasus com `visible=false`).

**Fluxo de salvar (semântica INVERTIDA)** — `edit.php` → `Config_form`
(`config_form.php`) monta um checkbox por atividade, nomeado
`"<courseid>-<moduleid>-<activityid>"`, com a árvore guardada em `$this->dados`.
Ao submeter, `Config` (`lib.php`) compara `$dados` com os campos do form e coleta
as atividades **desmarcadas**; `Config::add_or_update_config_report()` apaga e
regrava a tabela `activities_course_config` com **apenas as desmarcadas**. Logo:
**uma linha em `activities_course_config` significa atividade OCULTA**; marcado no
form = exibido. Na reabertura, `Config_form::definition()` relê a tabela para
desmarcar o que está oculto. (Quirk a conhecer: o laço de coleta em `Config` itera
sobre `$fromform` com `break` — form vazio não coleta nada.)

**Navegação** — `lib.php::local_report_config_extend_settings_navigation()` injeta
o link "Configuração Relatórios" nas configurações da categoria, somente quando:
contexto é `context_coursecat` **e** o usuário tem `local/report_config:manage`
**e** a categoria tem `coursecount >= 1`.

**Capability** — `db/access.php` define `local/report_config:manage` com
`archetypes` vazio: nenhum papel a recebe por padrão (conceder via override).

**Tabela ativa**: `activities_course_config` (`db/install.xml`). A tabela
`local_report_config` é legada/sem uso no fluxo atual.

## Comandos

Os runners de teste exigem um `.env` na raiz do plugin (`CORE_NAME`,
`DOCKER_VERSION`, `URL_NAME`, `SELENIUM_PORT`) — derivam o container Docker dele.
`.env` não é versionado; veja `.env.template`.

```bash
./run_tests.sh                       # toda a suíte PHPUnit
./run_tests.sh tests/config_test.php # um arquivo de teste
./run_tests.sh --reset               # recria as tabelas PHPUnit

./run_behat.sh                       # suíte Behat (tag @local_report_config)
./run_behat.sh tests/behat/report_config_save.feature  # um feature
./run_behat.sh --init                # reinicializa o ambiente Behat
./stop_behat.sh [--down]             # para os containers do Behat

moodlecheck                          # checa PHPdoc do plugin (ferramenta moodle-ufsc-devtools)
```

## Notas para testes

- **Atividades só aparecem no formulário se tiverem conclusão habilitada**. Em
  dados de teste: `enablecompletion=1` no site e no curso, e `completion=1` na
  atividade — senão `cm.completion` fica 0 e a atividade é filtrada (forma vazio,
  "field not found").
- A capability `local/report_config:manage` não tem archetype: nos cenários,
  conceda via `permission overrides` num papel (ex.: `manager`) no contexto da
  categoria. Overrides são por papel+contexto, não por usuário.
- Steps Behat customizados em `tests/behat/behat_report_config.php`
  (`I am on the course category page for "<idnumber>"`,
  `I am on the report config edit page for "<idnumber>"`).
- `tests/config_test.php` testa a lógica pura de `Config`; `tests/locallib_test.php`
  testa elegibilidade/visibilidade em `get_ordered_courses_activities` (inclui o
  caso "aparece mesmo com curso/atividade oculta").

## Convenções

- Mensagens de commit **sem** linha `Co-Authored-By` / atribuição de coautoria.
- Branch padrão: `main`. PRs miram `main`.
