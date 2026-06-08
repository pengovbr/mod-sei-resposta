# Testes funcionais do Módulo de Resposta

Testes funcionais em PHP com PHPUnit + Selenium, executados em containers Docker.

## Pré-requisitos

- Docker e Docker Compose
- Fontes do SEI configurados em `SEI_PATH` (arquivo `tests_resposta/.env`)
- Módulo instalado no ambiente (`make install`)

## Estrutura

| Pasta / arquivo | Descrição |
|-----------------|-----------|
| `tests/` | Casos de teste PHPUnit (Selenium) |
| `src/paginas/` | Page Objects da interface SEI |
| `src/config/` | Configuração SEI/SIP do ambiente de testes |
| `SoapUI/` | Testes de Web Service (SoapUI) |
| `docker-compose.yml` | Ambiente: httpd, database, selenium, proxy |
| `phpunit.xml` | Configuração PHPUnit |

## Execução

Na raiz do módulo:

```bash
make config base=postgresql   # ou mysql, oracle, sqlserver
make up
make update
make install
make test-functional-resposta                    # suite completa
make test-functional-resposta teste=NomeDoTeste  # teste específico
make tests-functional-soap                       # testes SoapUI
make tests-functional-full                       # Selenium + SoapUI
```

## Variáveis de ambiente

- `tests_resposta/.env` ? banco, SEI_PATH, imagens Docker
- `tests_resposta/.modulo.env` ? credenciais do módulo (copiado de `envs/modulo.env`)
- `.testselenium.env` ? parâmetros legados de validação (raiz do módulo)

## Migração (Python ? PHP)

Os testes Selenium em Python (`tests_sei5/funcional/seleniumPython/`) foram substituídos por esta estrutura.
Os cenários devem ser reimplementados em `tests_resposta/tests/` como classes PHPUnit.
