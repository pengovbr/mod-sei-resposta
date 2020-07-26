<?


class MdRespostaIntegracao extends SeiIntegracao{

  public function __construct(){
  }

  public function getNome(){
    return 'Módulo de Resposta';
  }

  public function getVersao() {
    return '0.0.1';
  }

  public function getInstituicao(){
    return 'Ministério da Economia - ME';
  }

  public function processarControlador($strAcao){

    switch($strAcao) {

      case 'md_resposta_configuracao':
        require_once dirname(__FILE__).'/resposta_configuracao.php';
        return true;
        
      case 'md_resposta_enviar':
        require_once dirname(__FILE__).'/resposta_envio.php';
        return true;
    }

    return false;
  }


  public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI){

    $arrBotoes = array();
    $objPaginaSEI = PaginaSEI::getInstance();
    $strDiretorioImagens = self::getDiretorio();

    $strParametros = '';
  
    if (isset($_GET['id_procedimento'])){
      $strParametros .= "&id_procedimento=".$_GET['id_procedimento'];
    }

    $objMdRespostaParametroRN = new MdRespostaParametroRN();
    $objMdRespostaTipoProcessoDTO = $objMdRespostaParametroRN->consultar(MDRespostaParametroRN::PARAM_TIPO_PROCESSO);

    if (SessaoSEI::getInstance()->verificarPermissao('md_resposta_enviar') 
      && $objProcedimentoAPI->getSinAberto()=='S' 
      && $objProcedimentoAPI->getCodigoAcesso() > 0
      && $objProcedimentoAPI->getIdTipoProcedimento() == $objMdRespostaTipoProcessoDTO->getStrValor()) {
      $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();
      $strLinkBotaoResposta  = '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_resposta_enviar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$_GET['id_procedimento'].'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI">';
      $strLinkBotaoResposta .= '<img class="infraCorBarraSistema" tabindex="'.$numTabBotao.'" src="'.$strDiretorioImagens.'/imagens/enviar_resposta.png" alt="Enviar Resposta" title="Enviar Resposta" />';
      $strLinkBotaoResposta .= '</a>';

      $arrBotoes[] = $strLinkBotaoResposta;
    }    

    return $arrBotoes;
  }

  public static function getDiretorio() {
    $arrConfig = ConfiguracaoSEI::getInstance()->getValor('SEI', 'Modulos');
    $strPastaModulo = $arrConfig['MdRespostaIntegracao'];
    return "modulos/".$strPastaModulo;
  }

}
?>