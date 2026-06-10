<?php

use PHPUnit\Extensions\Selenium2TestCase;

/**
 * Classe base contendo rotinas comuns utilizadas nos casos de teste do módulo de Resposta.
 */
class CenarioBaseTestCase extends Selenium2TestCase
{
    protected $paginaBase = null;

    public function setUpPage(): void
    {
        $this->paginaBase = new PaginaTeste($this);
        $this->currentWindow()->maximize();
    }

    private static function runDatabaseSetup(): void
    {
        $bancoSEI = new DatabaseUtils('SEI');
        $bancoSIP = new DatabaseUtils('SIP');

        // Criação de Sistema
        $result = $bancoSEI->query("SELECT MAX(id_contato) as id FROM contato");
        $maximoContatos = $result[0]['id'];
        $idContatoAssociado = $maximoContatos + 1;

        $bancoSEI->execute(
            "INSERT INTO contato (
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $idContatoAssociado,
                $idContatoAssociado,
                110000001,
                2,
                'Protocolo Digital',
                'pd_gov_br protocolo digital',
                'PD_GOV_BR',
                \InfraData::getStrDataHoraAtual(),
                'F',
                'S',
                'N'
            )
        );

        $result = $bancoSEI->query("SELECT id_contato FROM contato where sigla = 'PD_GOV_BR' ORDER BY id_contato DESC LIMIT 1");
        $idContatoProtocoloDigital = $result[0]['id_contato'];

        $result = $bancoSEI->query("SELECT MAX(id_usuario) as id FROM usuario");
        $maximoUsuarios = $result[0]['id'];
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
        $maximoSeries = $result[0]['id'];
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
        $maximoTiposProcedimentos = $result[0]['id'];
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
                'Protocolização de documentos para o Protocolo Central do ME',
                'Protocolização de documentos para o Protocolo Central do ME',
                'S',
                '0',
                'N',
                'N',
                'N',
                'N',
            )
        );
        
        $result = $bancoSEI->query("SELECT MAX(id_nivel_acesso_permitido) as id FROM nivel_acesso_permitido");
        $maximoNivelAcessoPermitido = $result[0]['id'];
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
        $maximoRelTipoProcedimentoAssunto = $result[0]['id'];
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
        $maximoServicos = $result[0]['id'];
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
        $maximoOperacoesServico = $result[0]['id'];
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
        $result = $bancoSEI->query("SELECT id_usuario FROM usuario where sigla = 'PD_GOV_BR' ORDER BY id_usuario DESC LIMIT 1");
        $idUsuarioPDGovBr = $result[0]['id_usuario'];
        $result = $bancoSEI->query("SELECT id_usuario FROM usuario where sigla = 'Intranet' ORDER BY id_usuario DESC LIMIT 1");
        $idUsuarioIntranet = $result[0]['id_usuario'];

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
