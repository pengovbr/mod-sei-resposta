<?php

/**
 * Conversao do teste funcional em Python
 * (tests_sei5/funcional/seleniumPython/02-SEI-MR-EnviarResposta/test_02EnviarRespostaValidacaoMensagem.py).
 *
 * O teste e dividido em duas etapas:
 *  - testGerarProcesso: realiza a chamada mock SOAP "gerarProcedimento" no SeiWS para criar o processo;
 *  - testEnviarRespostaValidacaoMensagem: tenta enviar a resposta sem preencher a mensagem e valida o alerta.
 *
 * As manipulacoes de tela sao delegadas a PaginaEnviarResposta (src/paginas), seguindo o padrao
 * de paginas adotado nos demais modulos do projeto.
 */
class EnviarRespostaValidacaoMensagemTest extends FixtureCenarioBaseTestCase
{
    public static $contextoTeste;
    public static $procedimentoFormatado;

    /**
     * Etapa 1: chamada mock SOAP para gerar o processo que recebera a resposta.
     *
     * @return string Protocolo formatado do processo gerado.
     */
    public function testGerarProcesso()
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);
        self::$procedimentoFormatado = $this->gerarProcessoViaWebService(self::$contextoTeste);

        return self::$procedimentoFormatado;
    }

    /**
     * Etapa 2: validacao do alerta de mensagem obrigatoria.
     *
     * @depends testGerarProcesso
     */
    public function testEnviarRespostaValidacaoMensagem($procedimentoFormatado)
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);
        $pagina = $this->paginaEnviarResposta;

        // Autenticacao no SEI e abertura do processo gerado via SOAP
        $this->acessarSistema(
            self::$contextoTeste['URL'],
            self::$contextoTeste['SIGLA_UNIDADE'],
            self::$contextoTeste['LOGIN'],
            self::$contextoTeste['SENHA']
        );
        $this->abrirProcesso($procedimentoFormatado);

        // Tentar enviar a resposta sem preencher a mensagem
        $pagina->navegarParaEnviarResposta();
        $pagina->enviar();

        // Validacao: alerta informando que a mensagem e obrigatoria
        $textoAlerta = $pagina->confirmarAlerta();
        $this->assertStringContainsString('Informe a Mensagem', $textoAlerta);
    }
}
