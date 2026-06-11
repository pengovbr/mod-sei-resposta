<?php

/**
 * Pagina especifica do modulo de Resposta, responsavel por encapsular todas as
 * manipulacoes de tela (Selenium) dos cenarios de "Enviar Resposta".
 *
 * Segue o mesmo padrao das demais paginas do projeto (extends PaginaTeste e
 * recebe o caso de teste no construtor), centralizando as interacoes de UI que
 * antes ficavam espalhadas diretamente nas classes de teste.
 */
class PaginaEnviarResposta extends PaginaTeste
{
    const TIMEOUT_PADRAO = 30000;

    public function __construct($test)
    {
        parent::__construct($test);
    }

    /**
     * Inclui um documento do tipo "Despacho" com nivel de acesso publico e fecha a
     * janela do editor aberta apos o salvamento (sem editar o conteudo).
     */
    public function incluirDespachoPublico()
    {
        $this->test->frame(1);
        $this->aguardarElemento("//img[@alt='Incluir Documento']");
        $this->test->byXPath("//img[@alt='Incluir Documento']")->click();

        $this->test->frame(0);
        $this->test->byLinkText('Despacho')->click();
        $this->test->byCssSelector('#divOptPublico .infraRadioLabel')->click();
        $this->aguardarElemento('#divInfraBarraComandosInferior > #btnSalvar', 'css');

        $janelasAntes = $this->test->windowHandles();
        $this->test->byCssSelector('#divInfraBarraComandosInferior > #btnSalvar')->click();
        $this->fecharJanelaEditor($janelasAntes);
    }

    /**
     * Seleciona o primeiro item da arvore do processo (utilizado quando se deseja
     * selecionar o documento incluido sem assina-lo).
     */
    public function selecionarPrimeiroDocumentoArvore()
    {
        $this->test->frame(0);
        $this->test->byXPath('//span')->click();
        sleep(3);
    }

    /**
     * Recarrega a arvore do processo a partir do menu superior.
     */
    public function atualizarArvore()
    {
        $this->test->frame(null);
        $this->test->frame(0);
        $this->test->byXPath("//div[@id='topmenu']/a[2]")->click();
    }

    /**
     * Aciona a funcionalidade "Enviar Resposta" e posiciona o foco no formulario.
     */
    public function navegarParaEnviarResposta()
    {
        $this->test->frame(null);
        $this->test->frame(1);
        $this->aguardarElemento("//img[@alt='Enviar Resposta']");
        $this->test->byXPath("//img[@alt='Enviar Resposta']")->click();
        $this->test->frame(0);
    }

    /**
     * Preenche o campo de mensagem do formulario de envio.
     */
    public function preencherMensagem($mensagem)
    {
        $this->aguardarElemento('txaMensagem', 'id');
        $this->test->byId('txaMensagem')->click();
        $this->test->byId('txaMensagem')->value($mensagem);
    }

    /**
     * Seleciona o documento de anexo no formulario de envio (checkbox imgInfraCheck).
     */
    public function selecionarDocumentoAnexo()
    {
        $this->test->byId('imgInfraCheck')->click();
    }

    public function marcarRespostaDefinitiva()
    {
        $this->test->byId('lblDefinitiva')->click();
    }

    public function marcarRespostaParcial()
    {
        $this->test->byId('lblParcial')->click();
    }

    public function enviar()
    {
        $this->test->byName('btnEnviar')->click();
    }

    /**
     * Le o texto do alerta exibido e o confirma (aceita), retornando o texto lido.
     */
    public function confirmarAlerta()
    {
        return $this->alertTextAndClose(true);
    }

    /**
     * Aguarda o documento aparecer na arvore do processo e retorna a quantidade encontrada.
     */
    public function aguardarDocumentoNaArvore($nomeDocumento)
    {
        $this->test->frame(null);
        $this->test->frame(0);
        $xpath = "//span[contains(.,'" . $nomeDocumento . "')]";
        $this->aguardarElemento($xpath);
        return $this->contarPorXPath($xpath);
    }

    /**
     * Seleciona (clica) um documento da arvore do processo pelo nome.
     */
    public function selecionarDocumentoNaArvore($nomeDocumento)
    {
        $this->test->byXPath("//span[contains(.,'" . $nomeDocumento . "')]")->click();
    }

    /**
     * Posiciona o foco no conteudo do documento de resposta gerado, aguardando seu carregamento.
     */
    public function aguardarConteudoResposta($nomeDocumento)
    {
        $this->test->frame(null);
        $this->test->frame(1);
        $this->test->frame(0);
        $this->aguardarElemento("//label[contains(.,'" . $nomeDocumento . "')]");
    }

    /**
     * Conta a quantidade de elementos que correspondem a um XPath no contexto atual.
     */
    public function contarPorXPath($xpath)
    {
        return count($this->test->elements($this->test->using('xpath')->value($xpath)));
    }

    /**
     * Indica se a acao "Enviar Resposta" ainda esta disponivel no processo.
     */
    public function acaoEnviarRespostaDisponivel()
    {
        $this->test->frame(null);
        $this->test->frame(1);
        $this->aguardarElemento("//img[@alt='Consultar/Alterar Processo']");
        return $this->contarPorXPath("//img[@alt='Enviar Resposta']") > 0;
    }

    /**
     * Aguarda um elemento ficar disponivel na pagina.
     *
     * @param string $seletor    Valor do seletor (xpath, css ou id).
     * @param string $estrategia Estrategia de busca: 'xpath' (padrao), 'css' ou 'id'.
     */
    private function aguardarElemento($seletor, $estrategia = 'xpath', $timeout = self::TIMEOUT_PADRAO)
    {
        $this->test->waitUntil(function ($test) use ($seletor, $estrategia) {
            try {
                switch ($estrategia) {
                    case 'css':
                        $elemento = $test->byCssSelector($seletor);
                        break;
                    case 'id':
                        $elemento = $test->byId($seletor);
                        break;
                    default:
                        $elemento = $test->byXPath($seletor);
                        break;
                }

                return $elemento !== null ? true : null;
            } catch (\Exception $e) {
                return null;
            }
        }, $timeout);
    }

    /**
     * Aguarda a abertura da janela do editor, fecha-a e retorna o foco para a janela raiz.
     */
    private function fecharJanelaEditor(array $janelasAntes, $timeout = self::TIMEOUT_PADRAO)
    {
        $janelaEditor = $this->test->waitUntil(function ($test) use ($janelasAntes) {
            $janelasAtuais = $test->windowHandles();
            if (count($janelasAtuais) > count($janelasAntes)) {
                $diferenca = array_values(array_diff($janelasAtuais, $janelasAntes));
                return !empty($diferenca) ? $diferenca[0] : null;
            }
            return null;
        }, $timeout);

        $janelaRaiz = $this->test->windowHandle();
        $this->test->window($janelaEditor);
        $this->test->closeWindow();
        $this->test->window($janelaRaiz);
    }
}
