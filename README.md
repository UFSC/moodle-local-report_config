Configuração de atividades para relatórios UNA-SUS
==================================================

Este plugin auxilia em permitir apresentar ou ocultar as atividades de um curso Moodle durante a geração de dados nos relatórios UnA-SUS.

Uma lista com todas as atividades de todos os módulos do curso Moodle serão apresentdas, para que possam ser selecionadas.

As atividades selecionadas seão apesentadas nos relatórios do plugin de relatórios, enquanto as atividades não selecionadas serão desconsideradas durante a montagem, isto é, ficarão ocultas.

Instalação
----------

Este plugin deve ser instalado em "local/report_config", juntamente com o plugin dependente:

* "local/report_unasus"

Permissões
----------

Para que os relatórios possam ser visualizados corretamente as seguintes permissões devem ser definidas para os papéis:

|   Capability              | Papel | Descrição |
| --- | --- | --- |
| **local/report_config:manage** | Coordenadores (curso, avea) | Gerenciar a configuração dos relatórios de gestão de tutoria | 

Configuração
------------

Não há necessidade de configuração para este plugin
