# NOTAS DE VERSÃO MOD-SEI-RESPOSTA (versão 1.2.0)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de integração do SuperBr com Platafoma Gov.br.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-sei-resposta-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo

## Lista de Melhorias e Correções de Problemas

Ajustes para inclusão das opções de resposta definitiva e/ou solicitando ajuste/complementação assim criação de serviço para desabilitar a funcionalidade de envio de respostas para processo que não há necessidade de resposta.

#### [Issue #16] WSDL retornado está apontando para [servidor]

Tem que retornar o mesmo domínio da origem da chamada do wsdl.

#### [Issue #43] Permitir a resposta parcial de uma solicitação junto ao Cidadão

Disponibilizar a opção para indicar se a resposta a ser enviada para o Protocolo Digital é uma resposta Parcial ou Definitiva.

#### [Issue #44] Desabilitar botão de resposta para aquelas solicitações concluídas no Protocolo Digital

Necessário controle para o que módulo possa identificar as solicitações que já foram concluídas diretamente no Protocolo Digital e não disponibilize a funcionalidade de resposta para estes processos.