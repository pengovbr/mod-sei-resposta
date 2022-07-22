# Manual de Instalação do Módulo de Resposta à plataforma Gov.br

O objetivo deste documento é descrever os procedimento para realizar a INSTALAÇÃO INICIAL do Módulo de Resposta à plataforma Gov.br (**mod-sei-resposta**) no Sistema Eletrônico de Informações (SEI).

**ATENÇÃO: Caso o módulo já se encontre instalado em uma versão anterior, siga as instruções detalhadas de atualização no documento ATUALIZACAO.md presente no arquivo de distribuição do módulo (mod-sei-resposta-VERSAO.zip)**

A utilização deste módulo adicionará nova funcionalidade ao SEI, permitindo:
 - Enviar resposta à processos administrativos abertos por meio da plataforma Gov.br

Este documento está estruturado nas seguintes seções:

1. **[Instalação](#instalação)**:
    Procedimentos destinados à Equipe Técnica responsáveis pela instalação do módulo nos servidores web e atualização do banco de dados.

2. **[Suporte](#suporte)**:
    Canais de comunicação para resolver problemas ou tirar dúvidas sobre o módulo.

2. **[Configuração](#configuração)**:
    Procedimentos destinados aos Administradores do SEI responsáveis pela configuração do módulo através da funcionalidades de administração do sistema.

---

## 1. INSTALAÇÃO

Esta seção descreve os passos obrigatórios para **INSTALAÇÃO** do **```**mod-sei-resposta**```**.  
Todos os itens descritos nesta seção são destinados à equipe de tecnologia da informação, responsáveis pela execução dos procedimentos técnicos de instalação e manutenção da infraestrutura do SEI.

### Pré-requisitos
 - **SEI versão 4.0.3 ou superior instalada**;
 - Usuário de acesso ao banco de dados do SEI e SIP com permissões para criar novas estruturas no banco de dados

### Procedimentos:

### 1.1 Fazer backup dos bancos de dados do SEI, SIP e dos arquivos de configuração do sistema.

Todos os procedimentos de manutenção do sistema devem ser precedidos de backup completo de todo o sistema a fim de possibilitar a sua recuperação em caso de falha. A rotina de instalação descrita abaixo atualiza tanto o banco de dados, como os arquivos pré-instalados do módulo e, por isto, todas estas informações precisam ser resguardadas.

---

### 1.2. Baixar o arquivo de distribuição do **mod-sei-resposta**

Necessário realizar o _download_ do pacote de distribuição do módulo **mod-sei-resposta** para instalação ou atualização do sistema SEI. O pacote de distribuição consiste em um arquivo zip com a denominação **mod-sei-resposta-VERSAO**.zip e sua última versão pode ser encontrada em https://github.com/spbgovbr/mod-sei-resposta/releases

---

### 1.3. Descompactar o pacote de instalação e atualizar os arquivos do sistema

Após realizar a descompactação do arquivo zip de instalação, será criada uma pasta contendo a seguinte estrutura:

```
/**mod-sei-resposta**-VERSAO
    /sei              # Arquivos do módulo posicionados corretamente dentro da estrutura do SEI
    /sip              # Arquivos do módulo posicionados corretamente dentro da estrutura do SIP
    INSTALACAO.md     # Instruções de instalação do **mod-sei-resposta**
    ATUALIZACAO.md    # Instruções de atualização do **mod-sei-resposta**
    NOTAS_VERSAO.MD   # Registros de novidades, melhorias e correções desta versão
```

Importante enfatizar que os arquivos contidos dentro dos diretórios `sei` e `sip` não substituem nenhum código-fonte original do sistema. Eles apenas posicionam os arquivos do módulos nas pastas corretas de scripts, configurações e pasta de módulos, todos posicionados dentro de um diretório específico denominado `mod-sei-resposta` para deixar claro quais scripts fazem parte do módulo.

Os diretórios `sei` e `sip` descompactados acima devem ser mesclados com os diretórios originais através de uma cópia simples dos arquivos.

Observação: O termo VERSAO deve ser substituído nas instruções abaixo pelo número da versão do módulo que está sendo instalado.

```
$ cp /tmp/**mod-sei-resposta**-VERSAO.zip <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>
$ cd <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>
$ unzip **mod-sei-resposta**-VERSAO.zip
```

---

### 1.4.  Habilitar módulo **mod-sei-resposta** no arquivo de configuração do SEI

Esta etapa é padrão para a instalação de qualquer módulo no SEI para que ele possa ser carregado junto com o sistema. Edite o arquivo **sei/config/ConfiguracaoSEI.php** para adicionar a referência ao módulo de resposta na chave **[Modulos]** abaixo da chave **[SEI]**:    

```php
'SEI' => array(
    'URL' => ...,
    'Producao' => ...,
    'RepositorioArquivos' => ...,
    'Modulos' => array('MdRespostaIntegracao' => 'mod-sei-resposta'),
    ),
```

Adicionar a referência ao módulo de resposta no array da chave 'Modulos' indicada acima:

```php
'Modulos' => array('MdRespostaIntegracao' => 'mod-sei-resposta')
```
---

### 1.5. Atualizar a base de dados do SIP com as tabelas do **mod-sei-resposta**

A atualização realizada no SIP não cria nenhuma tabela específica para o módulo, apenas é aplicada a criarção os recursos, permissões e menus de sistema utilizados pelo **mod-sei-resposta**. Todos os novos recursos criados possuem o prefixo **md_resposta_** para fácil localização pelas funcionalidades de gerenciamento de recursos do SIP.

O script de atualização da base de dados do SIP fica localizado em ```<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/web/modulos/mod-sei-resposta/sip_atualizar_versao_modulo_resposta.php``` e deverá ser copiado para ```<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts```.

```bash
$ cp <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/web/modulos/mod-sei-resposta/sip_atualizar_versao_modulo_resposta.php <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts/sip_atualizar_versao_modulo_resposta.php
```

---

### 1.7. Atualizar a base de dados do SEI com as tabelas do **mod-sei-resposta**

Nesta etapa é instalado/atualizado as tabelas de banco de dados vinculadas do **mod-sei-resposta**. Todas estas tabelas possuem o prefixo **md_resposta_** para organização e fácil localização no banco de dados.

O script de atualização da base de dados do SIP fica localizado em ```<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/web/modulos/mod-sei-resposta/sei_atualizar_versao_modulo_resposta.php``` e deverá ser copiado para ```<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts```.

```bash
$ cp <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/web/modulos/mod-sei-resposta/sei_atualizar_versao_modulo_resposta.php <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts/sei_atualizar_versao_modulo_resposta.php
```

---

## 2. CONFIGURAÇÕES

Esta seção descreve os passos de configuração do módulo de Integração do SEI com o Barramento de Serviços do PEN. Todos os itens descritos nesta seção são destinados aos Administradores do sistema SEI da instituição, responsáveis pela alteração de configurações gerais do sistema através do menu de administração do SEI (**SEI >> Administração >> Módulo de Resposta - Gov.br**)


### 2.1. Configurar os parâmetros do Módulo de Resposta - Gov.br
Acesse a funcionalidade **[SEI > Administração > Módulo de Resposta - Gov.br > Parâmetros de Configuração]** para configurar os parâmetros de funcionamento do módulo:  

#### Sistema
*Identificação do sistema que terá acesso ao webservice de resposta.*

#### Tipo de Processo:
*Identificação do Tipo de Processo que terá acesso a funcionalidade de envio de resposta à processos administrativos abertos por meio da plataforma Gov.br.*  

Como o envio é realizado de forma automática, o sistema precisa atribuir um Tipo de Processo padrão. Sugerimos a criação de um tipo de processo específico para estes processos, permitindo a fácil identificação e reclassificação, caso necessário. Segue abaixo um exemplo de Tipo de Processo que pode ser criado para esta situação:

    Nome: Protocolização de documentos para o Protocolo Central do ME
    Descrição: Processos recebidos através da plataforma Gov.br
    // O assunto deve ser definido juntamente com a área de documentação
    Sugestão de Assuntos: a classificar
    Níveis de Acesso Permitidos: Restrito e Público 
    Nível de Acesso Sugerido: Público 
    Processo único no órgão por usuário interessado: Não
    Interno do Sistema: Não       


#### Tipo de Documento:
*Identificação do Tipo de Documento que será utilizado para emissão da resposta à plataforma Gov.br.*  

Como o envio é realizado de forma automática, o sistema precisa atribuir um Tipo de Documento padrão. Com isto, sugerimos a criação de um tipo de documento específico para estes processos, permitindo a fácil identificação e reclassificação, caso necessário. Segue abaixo um exemplo de Tipo de Documento que pode ser criado para esta situação:

    Grupo: Internos (com modelo)
    Nome: Resposta pelo Protocolo Digital
    Descrição: Indicado nos Parâmetros para o envio de Resposta pelo Protocolo Digital
    Aplicabilidade: Documentos internos e externos
    Modelo: Geral_c-Num_c-Unid_s-Data_c-Int
    Tipo de Numeração: Sequencial Anual na Unidade
    Sugestão de Assuntos: a classificar
    Permitir publicação apenas para documentos assinados: Sim
    Permite interessados: Sim


## 3. SUPORTE

Em caso de dúvidas ou problemas durante o procedimento de atualização, favor entrar em conta pelos canais de atendimento disponibilizados na Central de Atendimento do Processo Eletrônico Nacional, que conta com uma equipe para avaliar e responder esta questão de forma mais rápida possível.

Para mais informações, contate a equipe responsável por meio dos seguintes canais:

- [Portal de Atendimento (PEN): Canal de Atendimento](https://portaldeservicos.economia.gov.br) - Módulo do Barramento
- Telefone: 0800 978 9005