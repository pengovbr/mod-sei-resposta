<?php

namespace Tests\Functional;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

class ConfiguracaoSistemaRespostaTest extends BaseTestCase
{
    public function test_criar_configuracao()
    {
        $this->webDriver->get(SIP_URL."/login.php?sigla_orgao_sistema=".SISTEMA_ORGAO."&sigla_sistema=SEI");
        $this->webDriver->findElement(WebDriverBy::id("txtUsuario"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->click();
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("Acessar"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath(mb_convert_encoding("//*[text()=\"Administração\"]", 'UTF-8', 'ISO-8859-1')))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[text()=\"Sistemas\"]"))->click();
        $this->webDriver->findElement(WebDriverBy::linkText("Novo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selOrgao"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = '".SISTEMA_ORGAO."']"))->click();
        $this->webDriver->findElement(WebDriverBy::id("selOrgao"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtSigla"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtSigla"))->sendKeys("PD_GOV_BR");
        $this->webDriver->findElement(WebDriverBy::id("txtNome"))->sendKeys("Protocolo Digital");
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarSistema"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath(mb_convert_encoding("//*[text()=\"Administração\"]", 'UTF-8', 'ISO-8859-1')))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[text()=\"Sistemas\"]"))->click();
        $this->webDriver->findElement(WebDriverBy::linkText("Listar"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[@id=\"divInfraAreaTabela\"]/table/tbody/tr[4]/td[6]/a[1]"))->click();
        $this->webDriver->findElement(WebDriverBy::cssSelector("#btnNovo > .infraTeclaAtalho"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtIdentificacao"))->sendKeys("SeiResposta");
        $this->webDriver->findElement(WebDriverBy::id("txtDescricao"))->sendKeys("Resposta ao protoloco digital");
        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='divChkSinServidor']/div/label"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtServidor"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtServidor"))->sendKeys("*");
        $this->webDriver->findElement(WebDriverBy::id("txtServidor"))->sendKeys(WebDriverKeys::ENTER);
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarServico"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath(mb_convert_encoding("//img[@title='Operações']", 'UTF-8', 'ISO-8859-1')))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnNovo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaOperacaoServico"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Consultar Processo']"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarOperacaoServico"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnNovo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaOperacaoServico"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Enviar Processo']"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarOperacaoServico"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnNovo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaOperacaoServico"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Gerar Processo']"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarOperacaoServico"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnNovo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaOperacaoServico"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Incluir Documento']"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarOperacaoServico"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnNovo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaOperacaoServico"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Consultar Documento']"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarOperacaoServico"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnFechar"))->click();
        $this->webDriver->findElement(WebDriverBy::id("btnFechar"))->click();
        $this->webDriver->quit();
    }

    /**
     * @return void
     * @depends test_criar_configuracao
     */
    public function test_criar_documento()
    {
        $this->webDriver->get(SIP_URL."/login.php?sigla_orgao_sistema=".SISTEMA_ORGAO."&sigla_sistema=SEI");
        $this->webDriver->findElement(WebDriverBy::id("txtUsuario"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->click();
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("Acessar"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath(mb_convert_encoding("//*[text()=\"Administração\"]", 'UTF-8', 'ISO-8859-1')))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[text()=\"Tipos de Documento\"]"))->click();
        $this->webDriver->findElement(WebDriverBy::linkText("Novo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selGrupoSerie"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Internos (com modelo)']"))->click();
        $this->webDriver->findElement(WebDriverBy::id("selGrupoSerie"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtNome"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtNome"))->sendKeys("Resposta pelo Protocolo Digital");
        $this->webDriver->findElement(WebDriverBy::id("txaDescricao"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txaDescricao"))->sendKeys(mb_convert_encoding("Indicado nos Parâmetros para o envio de Resposta pelo Protocolo Digital", 'UTF-8', 'ISO-8859-1'));
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaAplicabilidade"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Documentos internos e externos']"))->click();
        $this->webDriver->findElement(WebDriverBy::id("selStaAplicabilidade"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selModelo"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Geral_c-Num_c-Unid_s-Data_c-Int']"))->click();
        $this->webDriver->findElement(WebDriverBy::id("selModelo"))->click();
        $dropdown = $this->webDriver->findElement(WebDriverBy::id("selStaNumeracao"));
        $dropdown->findElement(WebDriverBy::xpath("//option[. = 'Sequencial Anual na Unidade']"))->click();
        $this->webDriver->findElement(WebDriverBy::id("selStaNumeracao"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarSerie"))->click();
        $this->webDriver->quit();
    }

    /**
     * @return void
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     * @depends test_criar_documento
     */
    public function test_criar_tipo_processo()
    {
        $this->webDriver->get(SIP_URL."/login.php?sigla_orgao_sistema=".SISTEMA_ORGAO."&sigla_sistema=SEI");
        $this->webDriver->findElement(WebDriverBy::id("txtUsuario"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->click();
        $this->webDriver->findElement(WebDriverBy::id("pwdSenha"))->sendKeys("teste");
        $this->webDriver->findElement(WebDriverBy::id("Acessar"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[text()=\"Administração\"]"))->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//*[text()=\"Tipos de Processo\"]"))->click();
        $this->webDriver->findElement(WebDriverBy::linkText("Novo"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtNome"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtNome"))->sendKeys("Protocolização de documentos para o Protocolo Central do ME");
        $this->webDriver->findElement(WebDriverBy::id("txaDescricao"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txaDescricao"))->sendKeys("Protocolização de documentos para o Protocolo Central do ME");
        $this->webDriver->findElement(WebDriverBy::cssSelector("#divSinRestritoPermitido .infraCheckboxLabel"))->click();
        $this->webDriver->findElement(WebDriverBy::cssSelector("#divSinPublicoPermitido .infraCheckboxLabel"))->click();
        $this->webDriver->findElement(WebDriverBy::cssSelector("#divOptPublicoSugestao .infraRadioLabel"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtAssunto"))->click();
        $this->webDriver->findElement(WebDriverBy::id("txtAssunto"))->sendKeys("modernização");
        $this->webDriver->wait(30,30)
            ->until(WebDriverExpectedCondition::visibilityOfAnyElementLocated((WebDriverBy::cssSelector("#divInfraAjaxtxtAssunto li:nth-child(1) > a"))));
        $element = $this->webDriver->findElement(WebDriverBy::cssSelector("#divInfraAjaxtxtAssunto li:nth-child(1) > a"));
        $this->webDriver->action()->moveToElement($element)->clickAndHold()->perform();
        $element = $this->webDriver->findElement(WebDriverBy::cssSelector("#selAssuntos > option"));
        $this->webDriver->action()->moveToElement($element)->clickAndHold()->perform();
        $this->webDriver->findElement(WebDriverBy::cssSelector("body"))->click();
        $this->webDriver->findElement(WebDriverBy::name("sbmCadastrarTipoProcedimento"))->click();
    }
}
