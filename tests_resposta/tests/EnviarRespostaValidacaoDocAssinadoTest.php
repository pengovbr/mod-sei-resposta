<?php

use PHPUnit\Framework\TestCase;

/**
 * Conversao do teste funcional em Python
 * (tests_sei5/funcional/seleniumPython/02-SEI-MR-EnviarResposta/test_05EnviarRespostaValidacaoDocAssinado.py).
 *
 * O teste e dividido em duas etapas:
 *  - testGerarProcesso: realiza a chamada mock SOAP "gerarProcedimento" no SeiWS para criar o processo;
 *  - testEnviarRespostaValidacaoDocAssinado: executa o cenario de UI (Selenium) e valida que, mesmo
 *    com um documento incluido e assinado, o envio sem selecionar o documento exibe o alerta
 *    "Nenhum documento selecionado.".
 */
class EnviarRespostaValidacaoDocAssinadoTest extends FixtureCenarioBaseTestCase
{
    public static $contextoTeste;
    public static $procedimentoFormatado;

    private const ID_UNIDADE = '110000001';
    private const ID_TIPO_PROCEDIMENTO = '100000381';
    private const SIGLA_SISTEMA = 'PD_GOV_BR';
    private const IDENTIFICACAO_SERVICO = 'SeiResposta';

    /**
     * Etapa 1: chamada mock SOAP para gerar o processo que recebera a resposta.
     * Equivalente ao metodo Python test_00GerarProcesso (linhas 40-91).
     *
     * @return string Protocolo formatado do processo gerado.
     */
    public function testGerarProcesso()
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);

        $url = self::$contextoTeste['URL'] . '/ws/SeiWS.php';

        $idUnidade = self::ID_UNIDADE;
        $idTipoProcedimento = self::ID_TIPO_PROCEDIMENTO;
        $siglaSistema = self::SIGLA_SISTEMA;
        $identificacaoServico = self::IDENTIFICACAO_SERVICO;

        $payload = <<<XML
<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sei="Sei" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
    <soapenv:Header/>
    <soapenv:Body>
        <sei:gerarProcedimento soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
            <SiglaSistema xsi:type="xsd:string">{$siglaSistema}</SiglaSistema>
            <IdentificacaoServico xsi:type="xsd:string">{$identificacaoServico}</IdentificacaoServico>
            <IdUnidade xsi:type="xsd:string">{$idUnidade}</IdUnidade>
            <Procedimento xsi:type="sei:Procedimento">
                <IdTipoProcedimento xsi:type="xsd:string">{$idTipoProcedimento}</IdTipoProcedimento>
                <NumeroProtocolo xsi:type="xsd:string"></NumeroProtocolo>
                <DataAutuacao xsi:type="xsd:string"></DataAutuacao>
                <Especificacao xsi:type="xsd:string"></Especificacao>
                <Assuntos xsi:type="sei:ArrayOfAssunto" SOAP-ENC:arrayType="sei:Assunto[]"/>
                <Interessados xsi:type="sei:ArrayOfInteressado" SOAP-ENC:arrayType="sei:Interessado[]"/>
                <Observacao xsi:type="xsd:string"></Observacao>
                <NivelAcesso xsi:type="xsd:string">0</NivelAcesso>
                <IdHipoteseLegal xsi:type="xsd:string"></IdHipoteseLegal>
            </Procedimento>
            <Documentos xsi:type="sei:ArrayOfDocumento" SOAP-ENC:arrayType="sei:Documento[]"/>
            <ProcedimentosRelacionados xsi:type="sei:ArrayOfProcedimentoRelacionado" SOAP-ENC:arrayType="xsd:string[]"/>
            <UnidadesEnvio xsi:type="sei:ArrayOfIdUnidade" SOAP-ENC:arrayType="xsd:string[]"/>
            <SinManterAbertoUnidade xsi:type="xsd:string"></SinManterAbertoUnidade>
            <SinEnviarEmailNotificacao xsi:type="xsd:string"></SinEnviarEmailNotificacao>
            <DataRetornoProgramado xsi:type="xsd:string"></DataRetornoProgramado>
            <DiasRetornoProgramado xsi:type="xsd:string"></DiasRetornoProgramado>
            <SinDiasUteisRetornoProgramado xsi:type="xsd:string"></SinDiasUteisRetornoProgramado>
            <IdMarcador xsi:type="xsd:string"></IdMarcador>
            <TextoMarcador xsi:type="xsd:string"></TextoMarcador>
        </sei:gerarProcedimento>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_POST, true);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=utf-8'));

        $response = curl_exec($curlHandler);
        $erroCurl = curl_error($curlHandler);
        $codigoHttp = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
        curl_close($curlHandler);

        $this->assertEmpty($erroCurl, 'Falha na chamada SOAP gerarProcedimento: ' . $erroCurl);
        $this->assertEquals(200, $codigoHttp, 'A chamada SOAP gerarProcedimento nao retornou HTTP 200.');
        $this->assertNotEmpty($response, 'A resposta da chamada SOAP gerarProcedimento veio vazia.');

        $procedimentoFormatado = $this->extrairProcedimentoFormatado($response);
        $this->assertNotEmpty(
            $procedimentoFormatado,
            'Nao foi possivel extrair o ProcedimentoFormatado da resposta SOAP: ' . $response
        );

        self::$procedimentoFormatado = $procedimentoFormatado;

        return $procedimentoFormatado;
    }

    /**
     * Etapa 2: cenario de validacao do envio sem selecionar o documento (mesmo assinado).
     * Equivalente ao metodo Python test_05EnviarRespostaValidacaoDocAssinado (linha 92 em diante).
     *
     * @depends testGerarProcesso
     */
    public function testEnviarRespostaValidacaoDocAssinado($procedimentoFormatado)
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);

        // Autenticacao no SEI (login via SIP, equivalente ao acesso direto feito no teste Python)
        $urlBase = preg_replace('#/sei/?$#', '', self::$contextoTeste['URL']);
        $this->url($urlBase . '/sip/login.php?sigla_orgao_sistema=' . self::$contextoTeste['ORGAO'] . '&sigla_sistema=SEI');
        $this->byId('txtUsuario')->value(self::$contextoTeste['LOGIN']);
        $this->byId('pwdSenha')->value(self::$contextoTeste['SENHA']);
        $this->byId('sbmAcessar')->click();

        // Abrir o processo gerado via SOAP
        $this->frame(null);
        $this->byLinkText($procedimentoFormatado)->click();

        // Incluir documento (Despacho) no processo
        $this->frame(1);
        $this->waitUntil(function () {
            return $this->elementoVisivel("//img[@alt='Incluir Documento']");
        }, 30000);
        $this->byXPath("//img[@alt='Incluir Documento']")->click();

        $this->frame(0);
        $this->byLinkText('Despacho')->click();
        $this->byCssSelector('#divOptPublico .infraRadioLabel')->click();
        $this->waitUntil(function () {
            return $this->elementoVisivel('#divInfraBarraComandosInferior > #btnSalvar', 'css');
        }, 30000);

        // Salvar abre a janela do editor; fecha-se a janela sem editar o conteudo
        $janelasAntes = $this->windowHandles();
        $this->byCssSelector('#divInfraBarraComandosInferior > #btnSalvar')->click();
        $janelaEditor = $this->aguardarNovaJanela($janelasAntes, 30000);
        $janelaRaiz = $this->windowHandle();
        $this->window($janelaEditor);
        $this->closeWindow();
        $this->window($janelaRaiz);

        // Assinar o documento
        $this->frame(1);
        $this->waitUntil(function () {
            return $this->elementoVisivel("//img[@alt='Assinar Documento']");
        }, 30000);
        $this->byXPath("//img[@alt='Assinar Documento']")->click();

        $this->frame(null);
        $this->frame(2);
        $this->waitUntil(function () {
            return $this->elementoVisivel('selCargoFuncao', 'id');
        }, 30000);
        $this->select($this->byId('selCargoFuncao'))->selectOptionByLabel('Corregedor');
        $this->byId('pwdSenha')->value(self::$contextoTeste['SENHA']);
        $this->byId('btnAssinar')->click();
        sleep(5);

        // Navegar de volta a arvore do processo
        $this->frame(null);
        $this->frame(0);
        $this->byXPath("//div[@id='topmenu']/a[2]")->click();
        sleep(3);

        // Acessar a acao "Enviar Resposta"
        $this->frame(null);
        $this->frame(1);
        $this->waitUntil(function () {
            return $this->elementoVisivel("//img[@alt='Enviar Resposta']");
        }, 30000);
        $this->byXPath("//img[@alt='Enviar Resposta']")->click();

        // Preencher a mensagem e marcar envio definitivo SEM selecionar o documento (sem imgInfraCheck)
        $this->frame(0);
        $this->byId('txaMensagem')->click();
        $this->byId('txaMensagem')->value('teste');
        $this->byId('lblDefinitiva')->click();
        $this->byName('btnEnviar')->click();

        // Validacao: alerta informando que nenhum documento foi selecionado para o envio
        $textoAlerta = $this->alertText();
        $this->assertStringContainsString('Nenhum documento selecionado', $textoAlerta);
        $this->acceptAlert();
    }

    /**
     * Extrai o valor de ProcedimentoFormatado da resposta SOAP, independentemente do prefixo de namespace.
     */
    private function extrairProcedimentoFormatado($response)
    {
        $padrao = '/<(?:[\w-]+:)?ProcedimentoFormatado[^>]*>(.*?)<\/(?:[\w-]+:)?ProcedimentoFormatado>/s';
        if (!preg_match($padrao, $response, $partes)) {
            return null;
        }

        $valor = str_replace(array('<![CDATA[', ']]>'), '', $partes[1]);
        return trim($valor);
    }

    /**
     * Verifica se um elemento esta presente/visivel na pagina, retornando null em caso de ausencia
     * para permitir o uso dentro de waitUntil.
     *
     * @param string $seletor   Valor do seletor (xpath, css ou id).
     * @param string $estrategia Estrategia de busca: 'xpath' (padrao), 'css' ou 'id'.
     * @return bool|null
     */
    private function elementoVisivel($seletor, $estrategia = 'xpath')
    {
        try {
            switch ($estrategia) {
                case 'css':
                    $elemento = $this->byCssSelector($seletor);
                    break;
                case 'id':
                    $elemento = $this->byId($seletor);
                    break;
                default:
                    $elemento = $this->byXPath($seletor);
                    break;
            }

            return $elemento !== null ? true : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Aguarda a abertura de uma nova janela do navegador e retorna o seu handle.
     * Equivalente ao metodo Python wait_for_window.
     */
    private function aguardarNovaJanela(array $janelasAntes, $timeout = 30000)
    {
        $novaJanela = $this->waitUntil(function () use ($janelasAntes) {
            $janelasAtuais = $this->windowHandles();
            if (count($janelasAtuais) > count($janelasAntes)) {
                $diferenca = array_values(array_diff($janelasAtuais, $janelasAntes));
                return !empty($diferenca) ? $diferenca[0] : null;
            }
            return null;
        }, $timeout);

        return $novaJanela;
    }
}
