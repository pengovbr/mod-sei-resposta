# Testes do Módulo de Resposta

## Orientações para rodar os testes manualmente

Existem 2 tipos de teste escritos para o módulo:
- selenium: testa as funcionalidades do modulo após a instalação no Super
- soap: testa as chamadas remotas via Soap do módulo

#### Pré-requisitos
- Selenium Python
- SoapUI https://www.soapui.org/downloads/soapui/
- SuperBr com o módulo de Resposta instalado

## Orientações para rodar os testes

Após subir um ambiente do zero, obrigatoriamente deve-se executar na ordem os testes Selenium e depois o testes no SoapUI
Caso vc use ferramentas próprias basta referenciar os testes na pasta tests do projeto

Ou você pode rodar tudo usando o Makefile.

- No arquivo .testselenium.env (criado apos rodar make config ou subir o ambiente), existem as opçoes de automação do teste. Ver algumas abaixo:

- Os testes rodam em 3 modalidades:
	- **Local:** precisa ter instalado localmente o pytest e também o driver seleniumcrhome na versão adequada. Nessa modalidade você pode visualizar o teste rodando em seu browser
	- **Standalone:** (default) roda os testes usando conteineres
	- **Remote:** caso queira rodar os testes em ambiente externo e usando um grid selenium

- **IMPORTANTE!** Antes de rodar o teste:
	- clone o projeto do zero
	- rode o ``` make up ``` e depois o ``` make install ``` 
	- verifique as mensagens e resolva qualquer orientação
	- tente acessar a pagina inicial do sistema pelo http://localhost:8000
	- se tudo certo, basta rodar ``` make tests-functional-full ``` (nesse caso vai rodar os testes em selenium e depois os testes em soap)