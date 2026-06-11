<?php

/**
 * Conversao do teste funcional em Python
 * (tests_sei5/funcional/seleniumPython/02-SEI-MR-EnviarResposta/test_06EnviarRespostaAjusteSemDocumentoAnexo.py).
 *
 * O teste e dividido em duas etapas:
 *  - testGerarProcesso: realiza a chamada mock SOAP "gerarProcedimento" no SeiWS para criar o processo;
 *  - testEnviarRespostaAjusteSemDocumento: executa o cenario de UI (Selenium) e valida o envio para
 *    ajuste/complementacao (envio parcial) sem selecionar documento de anexo.
 *
 * As manipulacoes de tela sao delegadas a PaginaEnviarResposta (src/paginas), seguindo o padrao
 * de paginas adotado nos demais modulos do projeto.
 */
class EnviarRespostaAjusteSemDocumentoTest extends FixtureCenarioBaseTestCase
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
     * Etapa 2: cenario de validacao do envio para ajuste/complementacao sem documento de anexo.
     *
     * @depends testGerarProcesso
     */
    public function testEnviarRespostaAjusteSemDocumento($procedimentoFormatado)
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

        // Enviar Resposta: envio Parcial (ajuste/complementacao) sem selecionar documento de anexo
        $pagina->navegarParaEnviarResposta();
        $pagina->preencherMensagem('teste');
        $pagina->marcarRespostaParcial();
        $pagina->enviar();

        // Confirmacao do envio para ajuste/complementacao
        $textoAlerta = $pagina->confirmarAlerta();
        $this->assertStringContainsString('Confirma o envio para ajuste', $textoAlerta);
        $this->assertStringContainsString('desfeita', $textoAlerta);

        // Validacao: documento "Resposta pelo Protocolo Digital" presente na arvore apos o envio
        $this->assertGreaterThan(
            0,
            $pagina->aguardarDocumentoNaArvore('Resposta pelo Protocolo Digital'),
            'Documento "Resposta pelo Protocolo Digital" nao encontrado na arvore do processo.'
        );
        $pagina->selecionarDocumentoNaArvore('Resposta pelo Protocolo Digital');

        // Validacao do conteudo do documento de resposta (sem documento de anexo)
        $pagina->aguardarConteudoResposta('Resposta pelo Protocolo Digital');
        $this->assertGreaterThan(
            0,
            $pagina->contarPorXPath("//label[contains(.,'Resposta pelo Protocolo Digital')]"),
            'Documento "Resposta pelo Protocolo Digital" nao encontrado no conteudo da resposta.'
        );

        // Apos o envio parcial, a acao "Enviar Resposta" nao deve mais estar disponivel no processo
        $pagina->atualizarArvore();
        $this->assertFalse(
            $pagina->acaoEnviarRespostaDisponivel(),
            'A acao "Enviar Resposta" ainda esta disponivel apos o envio para ajuste/complementacao.'
        );
    }
}
