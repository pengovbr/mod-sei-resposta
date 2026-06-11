<?php

use PHPUnit\Extensions\Selenium2TestCase;

/**
 * Classe base contendo rotinas comuns utilizadas nos casos de teste do módulo de Resposta.
 */
class CenarioBaseTestCase extends Selenium2TestCase
{
    protected $paginaBase = null;
    protected $paginaControleProcesso = null;
    protected $paginaDocumento = null;
    protected $paginaAssinaturaDocumento = null;
    protected $paginaEnviarResposta = null;

    public function setUpPage(): void
    {
        $this->paginaBase = new PaginaTeste($this);
        $this->paginaControleProcesso = new PaginaControleProcesso($this);
        $this->paginaDocumento = new PaginaDocumento($this);
        $this->paginaAssinaturaDocumento = new PaginaAssinaturaDocumento($this);
        $this->paginaEnviarResposta = new PaginaEnviarResposta($this);
        $this->currentWindow()->maximize();
    }

    private static function runDatabaseSetup(): void
    {
        $bancoSEI = new DatabaseUtils('SEI');
        $bancoSIP = new DatabaseUtils('SIP');

        // Criação de Sistema
        $result = $bancoSEI->query("SELECT MAX(id_contato) as id FROM contato");
        $maximoContatos = $result[0]['ID'];
        $idContatoAssociado = $maximoContatos + 1;

        $dataHoraAtual = date('Y-m-d H:i:s');
        $databaseType = getenv('DATABASE_TYPE') ?: 'Oracle';
        if (strtoupper($databaseType) === 'ORACLE') {
            $query = "INSERT INTO contato (
                        id_contato,
                        id_contato_associado,
                        id_unidade_cadastro,
                        id_tipo_contato,
                        nome,
                        idx_contato,
                        sigla,
                        dth_cadastro,
                        sta_natureza,
                        sin_ativo,
                        sin_endereco_associado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, TO_TIMESTAMP(?, 'YYYY-MM-DD HH24:MI:SS'), ?, ?, ?)";
        } else {
            // Para SQL Server ou outros que aceitam 'YYYY-MM-DD HH:MI:SS' diretamente
            $query = "INSERT INTO contato (
                        id_contato,
                        id_contato_associado,
                        id_unidade_cadastro,
                        id_tipo_contato,
                        nome,
                        idx_contato,
                        sigla,
                        dth_cadastro,
                        sta_natureza,
                        sin_ativo,
                        sin_endereco_associado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }
        $bancoSEI->execute(
            $query,
            array(
                $idContatoAssociado,
                $idContatoAssociado,
                110000001,
                2,
                'Protocolo Digital',
                'pd_gov_br protocolo digital',
                'PD_GOV_BR',
                $dataHoraAtual,
                'F',
                'S',
                'N'
            )
        );
        $result = $bancoSEI->query("SELECT id_contato FROM contato where sigla = 'PD_GOV_BR'");
        $idContatoProtocoloDigital = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID_CONTATO'] : $result[0]['id_contato']);

        $result = $bancoSEI->query("SELECT MAX(id_usuario) as id FROM usuario");
        $maximoUsuarios = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $maximoUsuarios += 1;

        $bancoSEI->execute(
            "INSERT INTO usuario (
                id_usuario,
                id_contato,
                id_orgao,
                id_origem,
                sigla,
                nome,
                nome_registro_civil,
                sta_tipo,
                sin_ativo,
                sin_gov_br
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $maximoUsuarios,
                $idContatoProtocoloDigital,
                0,
                null,
                'PD_GOV_BR',
                'Protocolo Digital',
                'Protocolo Digital',
                UsuarioRN::$TU_SISTEMA,
                'S',
                'N'
            )
        );

        // Criação de Tipo de Documento	
        $result = $bancoSEI->query("SELECT MAX(id_serie) as id FROM serie");
        $maximoSeries = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idSerieResposta = $maximoSeries + 1;

        $bancoSEI->execute(
            "INSERT INTO serie (
                id_serie,
                id_grupo_serie,
                id_modelo,
                nome,
                descricao,
                sin_ativo,
                sin_interessado,
                sin_destinatario,
                sta_numeracao,
                sin_assinatura_publicacao,
                sta_aplicabilidade,
                sin_interno,
                sin_usuario_externo,
                sin_valor_monetario
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $idSerieResposta,
                1,
                48,
                'Resposta pelo Protocolo Digital',
                'Indicado nos Parametros para o envio de Resposta pelo Protocolo Digital',
                'S',
                'N',
                'N',
                '3',
                'S',
                'T',
                'N',
                'N',
                'N'
                )
        );

        // Criação de Tipo de Procedimento
        $result = $bancoSEI->query("SELECT MAX(id_tipo_procedimento) as id FROM tipo_procedimento");
        $maximoTiposProcedimentos = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idTipoProcedimentoAssociado = $maximoTiposProcedimentos + 1;

        $bancoSEI->execute(
            "INSERT INTO tipo_procedimento (
                id_tipo_procedimento,
                nome,
                descricao,
                sin_ativo,
                sta_nivel_acesso_sugestao,
                sin_interno,
                sin_ouvidoria,
                sin_individual,
                sin_ouvidoria_anonimo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $idTipoProcedimentoAssociado,
                'Protocolizacao de documentos para o Protocolo Central do ME',
                'Protocolizacao de documentos para o Protocolo Central do ME',
                'S',
                '0',
                'N',
                'N',
                'N',
                'N',
            )
        );
        
        $result = $bancoSEI->query("SELECT MAX(id_nivel_acesso_permitido) as id FROM nivel_acesso_permitido");
        $maximoNivelAcessoPermitido = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idNivelAcessoPermitidoAssociado = $maximoNivelAcessoPermitido + 1;
        $bancoSEI->execute(
            "INSERT INTO nivel_acesso_permitido (
                id_nivel_acesso_permitido,
                id_tipo_procedimento,
                sta_nivel_acesso
            ) VALUES (?, ?, ?)",
            array(
                $idNivelAcessoPermitidoAssociado,
                $idTipoProcedimentoAssociado,
                '0',
            )
        );

        $idNivelAcessoPermitidoAssociado += 1;
        $bancoSEI->execute(
            "INSERT INTO nivel_acesso_permitido (
                id_nivel_acesso_permitido,
                id_tipo_procedimento,
                sta_nivel_acesso
            ) VALUES (?, ?, ?)",
            array(
                $idNivelAcessoPermitidoAssociado,
                $idTipoProcedimentoAssociado,
                '1',
            )
        );
        
        $result = $bancoSEI->query("SELECT MAX(id_tipo_procedimento) as id FROM rel_tipo_procedimento_assunto");
        $maximoRelTipoProcedimentoAssunto = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idRelTipoProcedimentoAssuntoAssociado = $maximoRelTipoProcedimentoAssunto + 1;
        $bancoSEI->execute(
            "INSERT INTO rel_tipo_procedimento_assunto (
                id_tipo_procedimento,
                id_assunto_proxy,
                sequencia
            ) VALUES (?, ?, ?)",
            array(
                $idTipoProcedimentoAssociado,
                '30',
                '0',
            )
        );

        // Criação do Serviço

        $result = $bancoSEI->query("SELECT MAX(id_servico) as id FROM servico");
        $maximoServicos = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idServicoAssociado = $maximoServicos + 1;
        $bancoSEI->execute(
            "INSERT INTO servico (
                id_servico,
                id_usuario,
                identificacao,
                descricao,
                servidor,
                sin_link_externo,
                sin_ativo,
                sin_chave_acesso,
                sin_servidor
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $idServicoAssociado,
                $maximoUsuarios,
                'SeiResposta',
                'Resposta ao protocolo digital',
                '*',
                'N',
                'S',
                'N',
                'S'
            )
        );

        $result = $bancoSEI->query("SELECT MAX(id_operacao_servico) as id FROM operacao_servico");
        $maximoOperacoesServico = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID'] : $result[0]['id']);
        $idOperacaoServicoAssociado = $maximoOperacoesServico + 1;
        $bancoSEI->execute(
            "INSERT INTO operacao_servico (id_operacao_servico, id_servico, sta_operacao_servico) VALUES (?, ?, ?)",
            array($idOperacaoServicoAssociado, $idServicoAssociado, '2')
        );

        $idOperacaoServicoAssociado += 1;
        $bancoSEI->execute(
            "INSERT INTO operacao_servico (id_operacao_servico, id_servico, sta_operacao_servico) VALUES (?, ?, ?)",
            array($idOperacaoServicoAssociado, $idServicoAssociado, '15')
        );

        $idOperacaoServicoAssociado += 1;
        $bancoSEI->execute(
            "INSERT INTO operacao_servico (id_operacao_servico, id_servico, sta_operacao_servico) VALUES (?, ?, ?)",
            array($idOperacaoServicoAssociado, $idServicoAssociado, '0')
        );

        $idOperacaoServicoAssociado += 1;
        $bancoSEI->execute(
            "INSERT INTO operacao_servico (id_operacao_servico, id_servico, sta_operacao_servico) VALUES (?, ?, ?)",
            array($idOperacaoServicoAssociado, $idServicoAssociado, '1')
        );

        $idOperacaoServicoAssociado += 1;
        $bancoSEI->execute(
            "INSERT INTO operacao_servico (id_operacao_servico, id_servico, sta_operacao_servico) VALUES (?, ?, ?)",
            array($idOperacaoServicoAssociado, $idServicoAssociado, '3')
        );

        // Configuraçao do Modulo
        $result = $bancoSEI->query("SELECT id_usuario FROM usuario where sigla = 'PD_GOV_BR'");
        $idUsuarioPDGovBr = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID_USUARIO'] : $result[0]['id_usuario']);
        $result = $bancoSEI->query("SELECT id_usuario FROM usuario where sigla = 'Intranet'");
        $idUsuarioIntranet = ((strtoupper($databaseType) === 'ORACLE') ? $result[0]['ID_USUARIO'] : $result[0]['id_usuario']);

        $bancoSEI->execute(
            "INSERT INTO md_resposta_parametro (
                nome,
                valor
            ) VALUES (?, ?)",
            array(
                'PARAM_SISTEMA',
                serialize(array($idUsuarioPDGovBr, $idUsuarioIntranet)),
            )
        );
        $bancoSEI->execute(
            "INSERT INTO md_resposta_parametro (
                nome,
                valor
            ) VALUES (?, ?)",
            array(
                'PARAM_TIPO_DOCUMENTO_RESULTADO',
                "94",
            )
        );
        $bancoSEI->execute(
            "INSERT INTO md_resposta_parametro (
                nome,
                valor
            ) VALUES (?, ?)",
            array(
                'PARAM_TIPO_DOCUMENTO_AJUSTE_COMPLEMENTACAO',
                $idSerieResposta,
            )
        );  
    }

    public static function setUpBeforeClass(): void
    {
        $bancoSEI = new DatabaseUtils('SEI');

        // Verifica no banco se o setup já foi feito
        $result = $bancoSEI->query("SELECT * FROM usuario WHERE sigla = 'PD_GOV_BR'");

        // Executa o setup apenas se ainda não tiver sido feito
        if (!isset($result[0])) {
            self::runDatabaseSetup();
        }
    }

    public static function tearDownAfterClass(): void
    {
    }

    public function setUp(): void
    {
        $this->setHost(PHPUNIT_HOST);
        $this->setPort(intval(PHPUNIT_PORT));
        $this->setBrowser(PHPUNIT_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTS_URL);
        $this->setDesiredCapabilities(
            array(
                'platform' => 'LINUX',
                'chromeOptions' => array(
                    'w3c' => false,
                    'args' => [
                        '--profile-directory=' . uniqid(),
                        '--disable-features=TranslateUI',
                        '--disable-translate',
                    ],
                )
            )
        );
    }

    protected function definirContextoTeste($nomeContexto)
    {
        return array(
            'URL' => constant($nomeContexto . '_URL'),
            'ORGAO' => constant($nomeContexto . '_SIGLA_ORGAO'),
            'SIGLA_UNIDADE' => constant($nomeContexto . '_SIGLA_UNIDADE'),
            'LOGIN' => constant($nomeContexto . '_USUARIO_LOGIN'),
            'SENHA' => constant($nomeContexto . '_USUARIO_SENHA'),
            'TIPO_DOCUMENTO' => constant($nomeContexto . '_TIPO_DOCUMENTO'),
            'CARGO_ASSINATURA' => constant($nomeContexto . '_CARGO_ASSINATURA'),
        );
    }

    protected function acessarSistema($url, $siglaUnidade, $login, $senha)
    {
        $this->url($url);
        PaginaLogin::executarAutenticacao($this, $login, $senha);
        PaginaTeste::selecionarUnidadeContexto($this, $siglaUnidade);
        $this->url($url);
    }

    /**
     * Abre um processo a partir do seu protocolo, navegando pelo Controle de Processos.
     */
    protected function abrirProcesso($protocolo)
    {
        $this->paginaBase->navegarParaControleProcesso();
        $this->paginaControleProcesso->abrirProcesso($protocolo);
    }

    /**
     * Assina o documento atualmente aberto, reutilizando a PaginaAssinaturaDocumento.
     */
    protected function assinarDocumento($cargoAssinante, $loginSenha)
    {
        $this->paginaDocumento->navegarParaAssinarDocumento();
        sleep(2);

        $this->paginaAssinaturaDocumento->selecionarCargoAssinante($cargoAssinante);
        $this->paginaAssinaturaDocumento->assinarComLoginSenha($loginSenha);
        sleep(5);
    }

    /**
     * Gera um processo no SEI por meio da chamada SOAP "gerarProcedimento" do SeiWS,
     * retornando o protocolo formatado do processo criado.
     *
     * Centraliza a etapa de preparacao (mock SOAP) comum aos cenarios de Enviar Resposta.
     */
    protected function gerarProcessoViaWebService(
        $contexto,
        $idUnidade = '110000001',
        $idTipoProcedimento = '100000381',
        $siglaSistema = 'PD_GOV_BR',
        $identificacaoServico = 'SeiResposta'
    ) {
        $url = $contexto['URL'] . '/ws/SeiWS.php';

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

        return $procedimentoFormatado;
    }

    /**
     * Extrai o valor de ProcedimentoFormatado da resposta SOAP, independentemente do prefixo de namespace.
     */
    protected function extrairProcedimentoFormatado($response)
    {
        $padrao = '/<(?:[\w-]+:)?ProcedimentoFormatado[^>]*>(.*?)<\/(?:[\w-]+:)?ProcedimentoFormatado>/s';
        if (!preg_match($padrao, $response, $partes)) {
            return null;
        }

        $valor = str_replace(array('<![CDATA[', ']]>'), '', $partes[1]);
        return trim($valor);
    }

    protected function selecionarUnidadeInterna($unidadeDestino)
    {
        PaginaTeste::selecionarUnidadeContexto($this, $unidadeDestino);
    }

    protected function sairSistema()
    {
        $this->paginaBase->sairSistema();
    }

    public static function generateRandomString($length = 10)
    {
        return substr(
            str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 62))),
            1,
            $length
        );
    }
}
