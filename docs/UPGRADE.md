# Manual de Atualização do Módulo de Resposta à plataforma Gov.br

O objetivo deste documento é descrever os procedimentos para realização da ATUALIZAÇÃO do Módulo de Resposta à plataforma Gov.br (**mod-sei-resposta**) no Sistema Eletrônico de Informações (SEI).

**ATENÇÃO: Caso esta seja a primeira instalação do módulo no SEI, veja as instruções detalhadas no documento INSTALACAO.md, presente no arquivo de distribuição do módulo (mod-sei-resposta-VERSAO.zip)**

Para maiores informações, entre em contato pelo telefone 0800 978-9005 ou diretamente pela Central de Serviços do Processo Eletrônico Nacional, endereço https://www.gov.br/economia/pt-br/assuntos/processo-eletronico-nacional

Este documento está estruturado nas seguintes seções:

1.  **[Atualização](#atualização)**:  
    Procedimentos para realizar a atualização de uma nova versão do módulo

2.  **[Suporte](#suporte)**:  
    Canais de comunicação para resolver problemas ou tirar dúvidas sobre o módulo e os demais componentes.

3.  **[Problemas Conhecidos](#problemas-conhecidos)**:  
    Canais de comunicação para resolver problemas ou tirar dúvidas sobre o módulo e os demais componentes.

---

## 1. ATUALIZAÇÃO

Esta seção descreve os passos obrigatórios para **ATUALIZAÇÃO** do **`mod-sei-resposta`**.  
Todos os itens descritos nesta seção são destinados à equipe de tecnologia da informação, responsável pela execução dos procedimentos técnicos de instalação e manutenção da infraestrutura do SEI.

### Pré-requisitos

- **mod-sei-resposta 1.0.0 ou versão superior instalada**;
- **SEI versão 4.0.3 ou versão superior instalada**;
- Usuário de acesso ao banco de dados do SEI e SIP com permissões para criar e atualizar o banco de dados do SEI e SIP.

### Procedimentos:


### 1.1 Fazer backup dos bancos de dados do SEI, SIP e dos arquivos de configuração do sistema.

Todos os procedimentos de manutenção do sistema devem ser precedidos de backup completo de todo o sistema a fim de possibilitar a sua recuperação em caso de falha. A rotina de instalação descrita abaixo atualiza tanto o banco de dados, como os arquivos pré-instalados do módulo e, por isto, todas estas informações precisam ser resguardadas.

---

### 1.2. Baixar o arquivo de distribuição do **mod-sei-resposta**

Necessário realizar o _download_ do pacote de distribuição do módulo **mod-sei-resposta** para instalação ou atualização do sistema SEI. O pacote de distribuição consiste em um arquivo zip com a denominação **mod-respposta-VERSAO**.zip e sua última versão pode ser encontrada em https://github.com/spbgovbr/mod-sei-resposta/releases

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

### 1.6. Atualizar a base de dados do SIP com as tabelas do **mod-sei-resposta**

A atualização realizada no SIP não cria nenhuma tabela específica para o módulo, apenas é aplicada a criarção os recursos, permissões e menus de sistema utilizados pelo **mod-sei-resposta**. Todos os novos recursos criados possuem o prefixo **md_resposta_** para fácil localização pelas funcionalidades de gerenciamento de recursos do SIP.

O script de atualização da base de dados do SIP fica localizado em `<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts/mod-sei-resposta/sip_atualizar_versao_modulo_resposta.php`

```bash
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts/mod-sei-resposta/sip_atualizar_versao_modulo_resposta.php
```

---

### 1.7. Atualizar a base de dados do SEI com as tabelas do **mod-sei-resposta**

Nesta etapa é instalado/atualizado as tabelas de banco de dados vinculadas do **mod-sei-resposta**. Todas estas tabelas possuem o prefixo **md_resposta_** para organização e fácil localização no banco de dados.

O script de atualização da base de dados do SIP fica localizado em `<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts/mod-sei-resposta/sei_atualizar_versao_modulo_resposta.php`

```bash
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts/mod-sei-resposta/sei_atualizar_versao_modulo_resposta.php
```

---
---

## 2. PROBLEMAS CONHECIDOS

Para maiores informações sobre problemas conhecidos e os procedimentos que devem ser feitos para corrigi-los, consulte a seção _PROBLEMAS CONHECIDOS_ No arquivo `INSTALACAO.md` presente no arquivo de distribuição do módulo (mod-sei-resposta-VERSAO.zip).

---
---

## 3. SUPORTE

Em caso de dúvidas ou problemas durante o procedimento de atualização, favor entrar em conta pelos canais de atendimento disponibilizados na Central de Atendimento do Processo Eletrônico Nacional, que conta com uma equipe para avaliar e responder esta questão de forma mais rápida possível.

Para mais informações, contate a equipe responsável por meio dos seguintes canais:

- [Portal de Atendimento (PEN): Canal de Atendimento](https://portaldeservicos.economia.gov.br) - Módulo do Barramento
- Telefone: 0800 978 9005
