.PHONY: .env help clean build all install restart down destroy up config

# Parâmetros de execução do comando MAKE
# Opções possíveis para spe (sistema de proc eletronico): sei4, sei41, super
sistema=super

-include .testselenium.env
-include .env
-include .modulo.env


# Parâmetros de configuração
base = mysql

ifndef HOST_URL
HOST_URL=http://localhost:8000
endif

MODULO_NOME = mod-sei-resposta
MODULO_PASTAS_CONFIG = $(MODULO_NOME)
MODULO_PASTA_NOME = $(notdir $(shell pwd))
VERSAO_MODULO := $(shell grep 'const VERSAO_MODULO' ./src/MdRespostaIntegracao.php | cut -d'"' -f2)
SEI_SCRIPTS_DIR = dist/sei/scripts/$(MODULO_PASTAS_CONFIG)
SEI_CONFIG_DIR = dist/sei/config/$(MODULO_PASTAS_CONFIG)
SEI_MODULO_DIR = dist/sei/web/modulos/$(MODULO_NOME)
SIP_SCRIPTS_DIR = dist/sip/scripts/$(MODULO_PASTAS_CONFIG)
TEST_FUNC = tests_$(sistema)/funcional

-include $(TEST_FUNC)/.testselenium.env
-include $(TEST_FUNC)/.env
-include $(TEST_FUNC)/.modulo.env

ARQUIVO_CONFIG_SEI=$(SEI_PATH)/sei/config/ConfiguracaoSEI.php
ARQUIVO_ENV_RESPOSTA=.modulo.env
MODULO_COMPACTADO = $(MODULO_NOME)-$(VERSAO_MODULO).zip
CMD_INSTALACAO_SEI = echo -ne '$(SEI_DATABASE_USER)\n$(SEI_DATABASE_PASSWORD)\n' | php atualizar_versao_sei.php
CMD_INSTALACAO_SIP = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php atualizar_versao_sip.php
CMD_INSTALACAO_RECURSOS_SEI = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php atualizar_recursos_sei.php
CMD_INSTALACAO_SEI_MODULO = echo -ne '$(SEI_DATABASE_USER)\n$(SEI_DATABASE_PASSWORD)\n' | php sei_atualizar_versao_modulo_resposta.php
CMD_INSTALACAO_SIP_MODULO = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php sip_atualizar_versao_modulo_resposta.php

RED=\033[0;31m
NC=\033[0m
YELLOW=\033[1;33m

MENSAGEM_AVISO_MODULO = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Necessário configurar a chave de configuração do módulo no arquivo de configuração do SEI (ConfiguracaoSEI.php) e prover o modulo na pasta correta $(NC)\n               $(YELLOW)'Modulos' => array('MdRespostaIntegracao' => 'mod-sei-resposta') $(NC)
MENSAGEM_AVISO_ENV = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Configurar parâmetros de autenticação do ambiente de testes do módulo de Resposta no arquivo .modulo.env $(NC)
MENSAGEM_AVISO_FONTES = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Nao foi possivel localizar o fonte do Super. Verifique o valor SEI_PATH no arquivo .env $(NC)

CMD_CURL_SUPER_LOGIN = curl -s -L $(HOST_URL)/sei | grep "txtUsuario"

define TESTS_MENSAGEM_ORIENTACAO
Leia o arquivo README relacionado aos testes.
O arquivo encontra-se nesse repositorio na pasta de testes funcionais.

Pressione y para continuar [y/n]...
endef
export TESTS_MENSAGEM_ORIENTACAO

ifeq (, $(shell groups |grep docker))
 CMD_DOCKER_SUDO=sudo
else
 CMD_DOCKER_SUDO=
endif

ifeq (, $(shell which docker-compose))
 CMD_DOCKER_COMPOSE=$(CMD_DOCKER_SUDO) docker compose
else
 CMD_DOCKER_COMPOSE=$(CMD_DOCKER_SUDO) docker-compose
endif

all: clean dist


dist: cria_json_compatibilidade
	@mkdir -p $(SEI_SCRIPTS_DIR)
	@mkdir -p $(SEI_CONFIG_DIR)
	@mkdir -p $(SEI_MODULO_DIR)
	@mkdir -p $(SIP_SCRIPTS_DIR)
	@cp -Rf src/* $(SEI_MODULO_DIR)/
	@cp docs/INSTALL.md dist/INSTALACAO.md
	@cp docs/UPGRADE.md dist/ATUALIZACAO.md
	@cp docs/changelogs/CHANGELOG-$(VERSAO_MODULO).md dist/NOTAS_VERSAO.md
	@cp compatibilidade.json dist/compatibilidade.json
	@mv $(SEI_MODULO_DIR)/scripts/sei_atualizar_versao_modulo_resposta.php $(SEI_SCRIPTS_DIR)/
	@mv $(SEI_MODULO_DIR)/scripts/sip_atualizar_versao_modulo_resposta.php $(SIP_SCRIPTS_DIR)/
	@rm -rf $(SEI_MODULO_DIR)/config
	@rm -rf $(SEI_MODULO_DIR)/scripts
	@cd dist/ && zip -r $(MODULO_COMPACTADO) INSTALACAO.md ATUALIZACAO.md NOTAS_VERSAO.md compatibilidade.json sei/ sip/	
	@rm -rf dist/sei dist/sip dist/INSTALACAO.md dist/ATUALIZACAO.md
	@echo "Construção do pacote de distribuição finalizada com sucesso"


clean:
	@rm -rf dist
	@echo "Limpeza do diretório de distribuição do realizada com sucesso"


.env:
	@if [ ! -f ".env" ]; then \
	cp envs/$(base).env .env; \
	echo "Arquivo .env nao existia. Copiado o arquivo default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de subir o ambiente."; \
	echo ""; sleep 5; \
	fi


.modulo.env:
	@if [ ! -f ".modulo.env" ]; then \
	cp envs/modulo.env .modulo.env; \
	fi


.testselenium.env:
	@if [ ! -f ".testselenium.env" ]; then \
	cp envs/testselenium.env .testselenium.env ; \
	echo "Arquivo .testselenium.env nao existia. Copiado default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de rodar os testes."; \
	echo ""; sleep 5; \
	fi


check-super-path:
	@if [ ! -f $(SEI_PATH)/sei/web/SEI.php ]; then \
	echo "$(MENSAGEM_AVISO_FONTES)\n" ; \
	exit 1 ; \
	fi


check-module-config:
	@docker cp utils/verificar_modulo.php httpd:/
	@$(CMD_DOCKER_COMPOSE) exec -T httpd bash -c "php /verificar_modulo.php" ; ret=$$?; echo "$$ret"; if [ ! $$ret -eq 0 ]; then echo "$(MENSAGEM_AVISO_MODULO)\n"; exit 1; fi


# acessa o super e verifica se esta respondendo a tela de login
check-super-isalive:
	@echo ""
	@echo "Vamos tentar acessar a pagina de login do $(sistema), vamos aguardar por 45 segs."
	@for number in 1 2 3 4 5 6 7 8 9 ; do \
	    echo 'Tentando acessar...'; var=$$(echo $$($(CMD_CURL_SUPER_LOGIN))); \
			if [ "$$var" != "" ]; then \
					echo 'Pagina respondeu com tela de login' ; \
					break ; \
			else \
			    echo 'Aguardando resposta ...'; \
			fi; \
			sleep 5; \
	done


prerequisites-up: .env .modulo.env check-super-path


prerequisites-modulo-instalar: check-super-path check-module-config check-super-isalive


install: prerequisites-modulo-instalar
	$(CMD_DOCKER_COMPOSE) exec -T -w /opt/sei/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SEI_MODULO)";
	$(CMD_DOCKER_COMPOSE) exec -T -w /opt/sip/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SIP_MODULO)";
	@echo "==================================================================================================="
	@echo ""
	@echo "Fim da instalação do módulo"


up: prerequisites-up
	$(CMD_DOCKER_COMPOSE) up -d
	make check-super-isalive


config:
	@cp -f envs/$(base).env .env
	@echo "Ambiente configurado para utilizar a base de dados $(base). (base=[mysql|oracle|sqlserver])"


down: 
	$(CMD_DOCKER_COMPOSE) down


restart: down up


destroy: 
	$(CMD_DOCKER_COMPOSE) down --volumes


# mensagens de orientacao para first time buccaneers
tests-functional-orientations:
	@if [ "$$CI" != "true" ] && [ -z "$$MSGORIENTACAO" ]; then \
		read -p "$$TESTS_MENSAGEM_ORIENTACAO" sure && case "$$sure" in [yY]) true;; *) false;; esac \
	fi


# validar os testes antes de rodar
tests-functional-validar: tests-functional-orientations
	@if [ -z "$$SELENIUMTEST_SISTEMA_URL" ] || [ -z "$$SELENIUMTEST_SISTEMA_ORGAO" ]; then \
	    echo "Variaveis de ambientes: SELENIUMTEST_SISTEMA_URL, SELENIUMTEST_SISTEMA_ORGAO, SELENIUMTEST_MODALIDADE nao definidas."; \
			echo "Verifique se o arquivo de configuracao para os testes esta criado (.testselenium.env)"; \
			echo "Existe um modelo desse arquivo na pasta envs."; \
			exit 1; \
	fi


tests-functional-prerequisites: .testselenium.env tests-functional-validar


# roda apenas os testes, o ajuste de data inicial e a criacao do ambiente ja devem ter sido realizados
tests-functional: tests-functional-prerequisites check-super-isalive
	@echo "Vamos iniciar a execucao dos testes..."
	@cd $(TEST_FUNC) && HOST_URL=$(HOST_URL) ./testes.sh


tests-functional-soap:
	docker run -i --network=host --rm -v "$$PWD"/$(TEST_FUNC)/SoapUI:/opt/soapui/projects -v "$$PWD"/$(TEST_FUNC)/SoapUI/result:/opt/soapui/projects/testresult lukastosic/docker-soapui -e$(HOST_URL)/sei/modulos/$(MODULO_NOME)/ws/MdRespostaWS.php -s"SeiMdRespostaSOAP TestSuite" -r -j -f/opt/soapui/projects/testresult -I "/opt/soapui/projects/MdRespostaWS-soapui-project.xml"

update: ## Atualiza banco de dados através dos scripts de atualização do sistema
	$(CMD_DOCKER_COMPOSE) run --rm -w /opt/sei/scripts/ httpd sh -c "$(CMD_INSTALACAO_SEI)"; true
	$(CMD_DOCKER_COMPOSE) run --rm -w /opt/sip/scripts/ httpd sh -c "$(CMD_INSTALACAO_SIP)"; true
	$(CMD_DOCKER_COMPOSE) run --rm -w /opt/sip/scripts/ httpd sh -c "$(CMD_INSTALACAO_RECURSOS_SEI)"; true

tests-functional-full: tests-functional tests-functional-soap

generate-der: up
	docker run --network host --rm -v .:/work -w /work ghcr.io/k1low/tbls doc --rm-dist mariadb://$(SEI_DATABASE_USER):$(SEI_DATABASE_PASSWORD)@localhost:3306/sei

cria_json_compatibilidade:
	$(shell ./gerar_json_compatibilidade.sh)