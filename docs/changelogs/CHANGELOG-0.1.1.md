# NOTAS DE VERSÃO MOD-SEI-RESPOSTA (versão 0.1.1)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de integração do SEI com Platafoma Gov.br.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-sei-resposta-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo

## Lista de Melhorias e Correções de Problemas

O foco desta versão foi a implementação da possibilidade de envio de múltiplos processos para obtenção de resposta assim como a correção de erros.


#### [Issue #9] Pequena melhoria para atender a necessidade de envio de múltiplos processos

Alteração para atender a necessidade de envio de múltiplos processos limitado a no máximo 100 (cem). 

#### [Issue #10] Correção do erro ao tentar acessar qualquer processo com o módulo instalado mais não configurado

Ao tentar acessar um processo com o módulo instalado mas não configurado gerava um erro na classe MdRespostaIntegracao.