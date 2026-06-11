<?php

/**
 * Conversao do teste funcional em Python
 * (tests_sei5/funcional/seleniumPython/02-SEI-MR-EnviarResposta/test_05EnviarRespostaValidacaoDocAssinado.py).
 *
 * O teste e dividido em duas etapas:
 *  - testGerarProcesso: realiza a chamada mock SOAP "gerarProcedimento" no SeiWS para criar o processo;
 *  - testEnviarRespostaValidacaoDocAssinado: inclui e assina um documento, mas tenta enviar a resposta
 *    sem seleciona-lo, validando o alerta de documento nao selecionado.
 *
 * As manipulacoes de tela sao delegadas a PaginaEnviarResposta (src/paginas), seguindo o padrao
 * de paginas adotado nos demais modulos do projeto.
 */
class EnviarRespostaValidacaoDocAssinadoTest extends FixtureCenarioBaseTestCase
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
     * Etapa 2: validacao do alerta de documento assinado, porem nao selecionado para envio.
     *
     * @depends testGerarProcesso
     */
    public function testEnviarRespostaValidacaoDocAssinado($procedimentoFormatado)
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

        // Incluir documento (Despacho) publico e assina-lo
        $pagina->incluirDespachoPublico();
        $this->assinarDocumento('Corregedor', self::$contextoTeste['SENHA']);

        // Atualizar a arvore do processo
        $pagina->atualizarArvore();
        sleep(3);

        // Preencher a mensagem e marcar envio definitivo SEM selecionar o documento (sem imgInfraCheck)
        $pagina->navegarParaEnviarResposta();
        $pagina->preencherMensagem('teste');
        $pagina->marcarRespostaDefinitiva();
        $pagina->enviar();

        // Validacao: alerta informando que nenhum documento foi selecionado para o envio
        $textoAlerta = $pagina->confirmarAlerta();
        $this->assertStringContainsString('Nenhum documento selecionado', $textoAlerta);
    }
}
