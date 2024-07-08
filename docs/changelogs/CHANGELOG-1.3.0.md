# NOTAS DE VERSÃO MOD-SEI-RESPOSTA (versão 1.2.2)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de integração do SuperBr com Platafoma Gov.br.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-sei-resposta-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo

## Compatibilidade de versões
* O módulo é compatível com as seguintes versões do **SEI**:
    * 4.0.3 até 4.1.2 (com exceção da 4.1.0) 


## Lista de Melhorias e Correções de Problemas

Ajustes para inclusão das opções de resposta definitiva e/ou solicitando ajuste/complementação assim criação de serviço para desabilitar a funcionalidade de envio de respostas para processo que não há necessidade de resposta.

### Explosão de Erros Falso Positivo "Nenhuma resposta encontrada" #62

Remoção no log da mensagem "Nenhuma resposta encontrada" como erro.
Passa ser INFO.

### Alterar termos que aparecem em tela e incluir mensagem de acordo com opção selecionada pelo usuário #66

Alterado labels e ordem do campo "Enviar resposta", "Solicitação de ajuste ou complementação"

### Alterar label em menu e em campo(s) para configuração de tipo(s) de documento(s) de resposta #67

Possibilidade de configurar mais de um tipo de documento de resposta e alteração
no label do menu.
