# NOTAS DE VERSÃO MOD-SEI-RESPOSTA (versão 1.2.2)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de integração do SuperBr com Platafoma Gov.br.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-sei-resposta-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo

## Lista de Melhorias e Correções de Problemas

Ajustes para inclusão das opções de resposta definitiva e/ou solicitando ajuste/complementação assim criação de serviço para desabilitar a funcionalidade de envio de respostas para processo que não há necessidade de resposta.

#### [Issue #52] Erro na listagem de resposta para ajuste

Ao tentar, utilizando o endpoint de listarResposta, recuperar a resposta para ajuste sem documento anexo o sistema está apresentando erro.