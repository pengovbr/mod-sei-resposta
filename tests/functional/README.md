# Testes do Módulo de Resposta

## Orientações para rodar os testes manualmente

Na pasta SeleniumIDE estão os testes.

### Selenium IDE
Para rodar os testes no Selenium IDE:

#### Pré-requisitos
- Instale o Selenium IDE [V3.17.0](https://www.seleniumhq.org/selenium-ide/) ou superior
- Conhecimento de uso básico/moderado do Selenium IDE
- SuperBr com o módulo de Resposta instalado

#### Para Rodar os Testes
- Baixe o projeto deste git (vou chamar de `<projeto>`)
- Vá até a pasta `<projeto>/tests/functional/seleniumIDE`
- Importe os arquivos *.side no Selenium IDE
- Ajuste a url dos ambientes que deseja testar 
- Execute os testes (isolados ou por suíte tests)

### SoapUI
Para rodar os testes no SoapUI:

#### Pré-requisitos
- Instale o SoapUI [V5.6.0](https://www.soapui.org/downloads/soapui/) ou superior
- Conhecimento de uso básico/moderado do SoapUI
- SuperBr com o módulo de Resposta instalado

#### Para Rodar os Testes
- Baixe o projeto deste git (vou chamar de `<projeto>`)
- Vá até a pasta `<projeto>/tests/functional/SoapUI`
- Importe o arquivo *.xml no SoapUI
- Ajuste a url dos ambientes que deseja testar 
- Execute os testes (isolados ou por TestStep)

## Orientações para rodar os testes através do Makefile

- Os testes rodam em 3 modalidades:
	- **Local:** precisa ter instalado localmente o pytest e também o driver seleniumcrhome na versão adequada. Nessa modalidade você pode visualizar o teste rodando em seu browser
	- **Standalone:** (default) roda os testes usando conteineres
	- **Remote:** caso queira rodar os testes em ambiente externo e usando um grid selenium

- **IMPORTANTE!** Antes de rodar o teste:
	- clone o projeto do zero
	- rode o ``` make up ``` e depois o ``` make install ``` 
	- verifique as mensagens e resolva qualquer orientação
	- tente acessar a pagina inicial do sistema pelo http://localhost:8000
	- se tudo certo, basta rodar ``` make tests-functional-full ```