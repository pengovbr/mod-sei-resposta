# Módulo de Resposta (Protocolo GOV.BR)

O módulo é responsável por integrar o Sistema Eletrônico de Informações (SEI) à plataforma GOV.BR. Tem como objetivo possibilitar que o Órgão ou Entidade se comunique com o cidadão via SPE para demandar ajustes ou complementações e enviar os resultados das solicitações recebidas via Protocolo GOV.BR.


A utilização deste módulo adicionará nova funcionalidade ao SEI, permitindo:
 - Solicitar ajustes ou complementos à documentação enviada pelo cidadão;
 - Enviar resultados à processos administrativos abertos por meio do Protocolo GOV.BR

## Manual de Utilização

Esta seção tem por objetivo demonstrar a funcionalidade que será disponibilizada pelo módulo de Resposta do SEI à plataforma Protocolo GOV.BR.

### Pré-requisitos
- SEI com acesso externo liberado

### Informações Obrigatórias para Envio da Resposta

Para permitir a interoperabilidade entre o SEI e a plataforma Gov.br, definiu-se um padrão de dados para intercâmbio. Este padrão define atributos que são obrigatórios e/ou opcionais.

Ao enviar resposta são obrigatórios os campos **processo, mensagem, lista de documentos e tipo de resposta**. O SEI fará validações das informações pendentes para envio e exibirá mensagens para o usuário, tais como:

- "Informe a Mensagem";
- "Nenhum documento selecionado" (Verifica se o foi selecionado pelo menos um documento interno assinado ou se possui algum documento externo);
- "Selecione o Tipo de resposta"
  

Solicitação de ajustes:

<img width="1900" height="831" alt="Módulo Resposta - Solicitação de justes" src="https://github.com/user-attachments/assets/bcc1cada-7923-4934-8443-3a041a26338f" />

---

Envio de resultado:

<img width="1899" height="855" alt="Módulo Resposta - Envio de resultado" src="https://github.com/user-attachments/assets/8b30be6a-0f8f-4530-8feb-70b80c0f6520" />


### Consulta às respostas enviadas

O módulo disponibiliza um webservice para as respostas enviadas. Para consultá-las, deve-se acessar o serviço, fornecendo as seguintes informações:

O wsdl pode ser acessado em:```<URL-SUPER>/controlador_ws.php?servico=MdRespostaWS```

```php
      <seim:RespostaRequest>
         <SiglaSistema xsi:type="xsd:string"></SiglaSistema>
         <IdentificacaoServico xsi:type="xsd:string"></IdentificacaoServico>
         <!--Optional:-->
         <IdProcedimentos xsi:type="seim:IdProcedimentos">
            <!--0 to 100 repetitions:-->
            <IdProcedimento xsi:type="xsd:int"></IdProcedimento>
         </IdProcedimentos>
         <!--Optional:-->
         <NumProcedimentos xsi:type="seim:NumProcedimentos">
            <!--0 to 100 repetitions:-->
            <NumProcedimento xsi:type="xsd:string"></NumProcedimento>
         </NumProcedimentos>
         <!--0 to 100 repetitions:-->
         <IdResposta xsi:type="xsd:string"></IdResposta>
      </seim:RespostaRequest>
```
