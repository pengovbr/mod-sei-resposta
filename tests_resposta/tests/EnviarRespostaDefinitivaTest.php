<?php

/**
 * Conversao do teste funcional em Python
 * (tests_sei5/funcional/seleniumPython/02-SEI-MR-EnviarResposta/test_00EnviarRespostaDefinitiva.py).
 *
 * O teste e dividido em duas etapas:
 *  - testGerarProcesso: realiza a chamada mock SOAP "gerarProcedimento" no SeiWS para criar o processo;
 *  - testEnviarRespostaDefinitiva: executa o cenario de UI (Selenium) e valida o envio da resposta definitiva.
 *
 * As manipulacoes de tela sao delegadas a PaginaEnviarResposta (src/paginas), seguindo o padrao
 * de paginas adotado nos demais modulos do projeto.
 */
class EnviarRespostaDefinitivaTest extends FixtureCenarioBaseTestCase
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
     * Etapa 2: cenario de validacao do envio da resposta definitiva.
     *
     * @depends testGerarProcesso
     */
    public function testEnviarRespostaDefinitiva($procedimentoFormatado)
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

        // Enviar Resposta definitiva selecionando o documento de anexo
        $pagina->navegarParaEnviarResposta();
        $pagina->preencherMensagem('teste');
        $pagina->selecionarDocumentoAnexo();
        $pagina->marcarRespostaDefinitiva();
        $pagina->enviar();

        // Confirmacao do envio definitivo
        $textoAlerta = $pagina->confirmarAlerta();
        $this->assertStringContainsString('Confirma o envio da resposta', $textoAlerta);
        $this->assertStringContainsString('Termo de Ci', $textoAlerta);

        // Validacao: documento "Voto" presente na arvore apos o envio
        $this->assertGreaterThan(
            0,
            $pagina->aguardarDocumentoNaArvore('Voto'),
            'Documento "Voto" nao encontrado na arvore do processo.'
        );
        $pagina->selecionarDocumentoNaArvore('Voto');

        // Validacao do conteudo do documento "Voto" (Anexos, Despacho e Voto)
        $pagina->aguardarConteudoResposta('Voto');
        $this->assertGreaterThan(
            0,
            $pagina->contarPorXPath("//b[contains(.,'Anexos')]"),
            'Secao "Anexos" nao encontrada no documento de resposta.'
        );
        $this->assertGreaterThan(
            0,
            $pagina->contarPorXPath("//a[contains(text(),'Despacho')]"),
            'Documento "Despacho" nao encontrado no anexo da resposta.'
        );
        $this->assertGreaterThan(
            0,
            $pagina->contarPorXPath("//label[contains(.,'Voto')]"),
            'Documento "Voto" nao encontrado no anexo da resposta.'
        );
    }
}
