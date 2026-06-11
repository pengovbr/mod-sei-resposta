.PHONY: .env .modulo.env help clean build all install restart down destroy up config test-functional-resposta install-phpunit-vendor vendor tests-functional-soap tests-functional-full

-include .testselenium.env

# Parâmetros de configuração
base = mysql
RESPOSTA_TEST_FUNC = tests_resposta

-include $(RESPOSTA_TEST_FUNC)/.env
-include $(RESPOSTA_TEST_FUNC)/.modulo.env

ifndef HOST_URL
HOST_URL=http://org-http:8000
endif

ifeq (, $(shell groups |grep docker))
 CMD_DOCKER_SUDO=sudo
else
 CMD_DOCKER_SUDO=
endif

ifeq (, $(shell which docker-compose))
 CMD_DOCKER_COMPOSE=$(CMD_DOCKER_SUDO) docker compose
 CMD_COMPOSE_FUNC = $(CMD_DOCKER_COMPOSE) -f $(RESPOSTA_TEST_FUNC)/docker-compose.yml --env-file $(RESPOSTA_TEST_FUNC)/.env
else
 CMD_DOCKER_COMPOSE=$(CMD_DOCKER_SUDO) docker-compose
 CMD_COMPOSE_FUNC = $(CMD_DOCKER_COMPOSE) -f $(RESPOSTA_TEST_FUNC)/docker-compose.yml --env-file $(RESPOSTA_TEST_FUNC)/.env
endif

MODULO_NOME = mod-sei-resposta
MODULO_PASTAS_CONFIG = $(MODULO_NOME)
MODULO_PASTA_NOME = $(notdir $(shell pwd))
VERSAO_MODULO := $(shell grep 'const VERSAO_MODULO' ./src/MdRespostaIntegracao.php | cut -d'"' -f2)
SEI_SCRIPTS_DIR = dist/sei/scripts/$(MODULO_PASTAS_CONFIG)
SEI_CONFIG_DIR = dist/sei/config/$(MODULO_PASTAS_CONFIG)
SEI_MODULO_DIR = dist/sei/web/modulos/$(MODULO_NOME)
SIP_SCRIPTS_DIR = dist/sip/scripts/$(MODULO_PASTAS_CONFIG)
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

CMD_CURL_SUPER_LOGIN = curl -s -L $(HOST_URL)/sei | grep "txtUsuario"

define TESTS_MENSAGEM_ORIENTACAO
Leia o arquivo README relacionado aos testes.
O arquivo encontra-se nesse repositorio na pasta tests_resposta.

Pressione y para continuar [y/n]...
endef
export TESTS_MENSAGEM_ORIENTACAO

FILE_VENDOR_FUNCIONAL = $(RESPOSTA_TEST_FUNC)/vendor/autoload.php

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
	@if [ ! -f "$(RESPOSTA_TEST_FUNC)/.env" ]; then \
	cp envs/$(base).env $(RESPOSTA_TEST_FUNC)/.env; \
	echo "Arquivo $(RESPOSTA_TEST_FUNC)/.env nao existia. Copiado o arquivo default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de subir o ambiente."; \
	echo ""; sleep 5; \
	fi


.modulo.env:
	@if [ ! -f "$(RESPOSTA_TEST_FUNC)/.modulo.env" ]; then \
	cp envs/modulo.env $(RESPOSTA_TEST_FUNC)/.modulo.env; \
	echo "Arquivo $(RESPOSTA_TEST_FUNC)/.modulo.env nao existia. Copiado o arquivo default da pasta envs."; \
	fi


.testselenium.env:
	@if [ ! -f ".testselenium.env" ]; then \
	cp envs/testselenium.env .testselenium.env ; \
	echo "Arquivo .testselenium.env nao existia. Copiado default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de rodar os testes."; \
	echo ""; sleep 5; \
	fi


check-super-isalive:
	@echo ""
	@echo "Vamos tentar acessar a pagina de login do SEI, vamos aguardar por 45 segs."
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


prerequisites-up: .env .modulo.env


prerequisites-modulo-instalar: check-super-isalive


install: prerequisites-modulo-instalar
	$(CMD_COMPOSE_FUNC) exec -T -w /opt/sei/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SEI_MODULO)";
	$(CMD_COMPOSE_FUNC) exec -T -w /opt/sip/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SIP_MODULO)";
	@echo "==================================================================================================="
	@echo ""
	@echo "Fim da instalação do módulo"


up: prerequisites-up prepare-upload-tmp
	$(CMD_COMPOSE_FUNC) up -d


prepare-upload-tmp:
	@if [ ! -d "$(RESPOSTA_TEST_FUNC)/.tmp" ]; then \
		echo "Criando diretório .tmp..."; \
		mkdir -p "$(RESPOSTA_TEST_FUNC)/.tmp"; \
		chmod -R 777 "$(RESPOSTA_TEST_FUNC)/.tmp"; \
	fi


config:
	@cp -f envs/$(base).env $(RESPOSTA_TEST_FUNC)/.env
	@echo "Ambiente configurado para utilizar a base de dados $(base). (base=[mysql|oracle|sqlserver|postgresql])"


down: prerequisites-up
	$(CMD_COMPOSE_FUNC) down


restart: down up


destroy: prerequisites-up
	$(CMD_COMPOSE_FUNC) down --volumes


tests-functional-orientations:
ifndef MSGORIENTACAO
	@if [ "$$CI" != "true" ]; then \
		read -p "$$TESTS_MENSAGEM_ORIENTACAO" sure && case "$$sure" in [yY]) true;; *) false;; esac; \
	fi
endif


tests-functional-validar: tests-functional-orientations
	@if [ -z "$$SELENIUMTEST_SISTEMA_URL" ] || [ -z "$$SELENIUMTEST_SISTEMA_ORGAO" ]; then \
	    echo "Variaveis de ambientes: SELENIUMTEST_SISTEMA_URL, SELENIUMTEST_SISTEMA_ORGAO nao definidas."; \
			echo "Verifique se o arquivo de configuracao para os testes esta criado (.testselenium.env)"; \
			echo "Existe um modelo desse arquivo na pasta envs."; \
			exit 1; \
	fi


tests-functional-prerequisites: .testselenium.env tests-functional-validar


# make teste=NomeDoTeste test-functional-resposta  (sem teste= executa a suite funcional)
test-functional-resposta: .env $(FILE_VENDOR_FUNCIONAL) up vendor
	$(CMD_COMPOSE_FUNC) run --rm php-test-functional /tests/vendor/bin/phpunit -c /tests/phpunit.xml --testdox /tests/tests/$(addsuffix .php,$(teste)) ;

$(FILE_VENDOR_FUNCIONAL):
	make install-phpunit-vendor

install-phpunit-vendor:
	$(CMD_COMPOSE_FUNC) run --rm -w /tests php-test-functional bash -c './composer.phar install'

vendor: $(RESPOSTA_TEST_FUNC)/composer.json
	$(CMD_COMPOSE_FUNC) run --rm -w /tests php-test-functional bash -c './composer.phar install'

tests-functional-soap: tests-functional-prerequisites check-super-isalive
	@if [ "$(chave)" ]; then \
		sed -i -E 's|(>)[^<]*(</ChaveAcesso>)|\1$(chave)\2|g' $(RESPOSTA_TEST_FUNC)/SoapUI/MdRespostaWS-soapui-project.xml ; \
		echo "Arquivo de testes atualizado com a chave de acesso informada."; \
	fi
	docker run -i --network=host --rm \
		-v "$$PWD"/$(RESPOSTA_TEST_FUNC)/SoapUI:/opt/soapui/projects \
		-v "$$PWD"/$(RESPOSTA_TEST_FUNC)/SoapUI/result:/opt/soapui/projects/testresult \
		lukastosic/docker-soapui \
		-e$(HOST_URL)/sei/modulos/$(MODULO_NOME)/ws/MdRespostaWS.php \
		-s"SeiMdRespostaSOAP TestSuite" -r -j -f/opt/soapui/projects/testresult \
		-I "/opt/soapui/projects/MdRespostaWS-soapui-project.xml"

tests-functional-full: test-functional-resposta tests-functional-soap

update:
	$(CMD_COMPOSE_FUNC) run --rm -w /opt/sei/scripts/ httpd sh -c "$(CMD_INSTALACAO_SEI)"; true
	$(CMD_COMPOSE_FUNC) run --rm -w /opt/sip/scripts/ httpd sh -c "$(CMD_INSTALACAO_SIP)"; true
	$(CMD_COMPOSE_FUNC) run --rm -w /opt/sip/scripts/ httpd sh -c "$(CMD_INSTALACAO_RECURSOS_SEI)"; true

generate-der: up
	docker run --network host --rm -v .:/work -w /work ghcr.io/k1low/tbls doc --rm-dist mariadb://$(SEI_DATABASE_USER):$(SEI_DATABASE_PASSWORD)@localhost:3306/sei

cria_json_compatibilidade:
	$(shell ./gerar_json_compatibilidade.sh)
