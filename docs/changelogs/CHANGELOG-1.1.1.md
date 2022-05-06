# NOTAS DE VERSÃO MOD-SEI-RESPOSTA (versão 1.1.1)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de integração do SuperBr com Platafoma Gov.br.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-sei-resposta-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo

## Lista de Melhorias e Correções de Problemas

Ajustes gerais e adaptaçao para o novo modelo de distribuição.

#### [Issue #39] Padronização dos arquivos do módulo

Atividades de organização da pasta do módulo

 * Organização das pastas
 * Atualização de documentação de README, instalação, atualização e utilização
 * Elaboração do docker-composer específico para projeto
 * Criação do Makefile
 * Elaboração do GitAction
 * Criação target de teste funcional no makefile (ocultando comandos em python)
 * Realização de testes

#### Criação de Jobs Jenkins

Criados jobs no Jenkins para execucao do build e teste