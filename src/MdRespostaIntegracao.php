<?


class MdRespostaIntegracao extends SeiIntegracao{

  const VERSAO_MODULO = "1.1.2";

  public function getNome(){
    return 'Módulo de Resposta';
  }

  public function getVersao() {
    return self::VERSAO_MODULO;
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

    $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
    $objMdRespostaParametroDTO -> retStrValor();
    $objMdRespostaParametroDTO -> setStrNome('PARAM_TIPO_PROCESSO');

    $objMdRespostaParametroRN = new MdRespostaParametroRN();
    $objMdRespostaTipoProcessoDTO = $objMdRespostaParametroRN->consultar($objMdRespostaParametroDTO);

    $objProcedimentoDTO = new ProcedimentoDTO();
    $objProcedimentoDTO->retStrStaEstadoProtocolo();
    $objProcedimentoDTO->setDblIdProcedimento($_GET['id_procedimento']);

    $objProcedimentoRN = new ProcedimentoRN();
    $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);    

    $bolProcessoEstadoNormal = !in_array($objProcedimentoDTO->getStrStaEstadoProtocolo(), array(
      ProtocoloRN::$TE_PROCEDIMENTO_SOBRESTADO,
      ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO
    ));

    $objMdRespostaDTO = new MdRespostaDTO();
    $objMdRespostaDTO->retNumIdResposta();
    $objMdRespostaDTO->setStrSinConclusiva(MdRespostaEnvioRN::$EV_RESPOSTA);
    $objMdRespostaDTO->setDblIdProcedimento($_GET['id_procedimento']);
  
    $objMdRespostaRN = new MdRespostaRN();
    $objMdResposta = $objMdRespostaRN->listarResposta($objMdRespostaDTO);

    $liberarAcesso = false;
    if(!isset($objMdResposta[0])){
      if(is_object($objMdRespostaTipoProcessoDTO)){
        $arrTipoProcesso = unserialize($objMdRespostaTipoProcessoDTO->getStrValor());
        foreach($arrTipoProcesso as $valor){
          if($objProcedimentoAPI->getIdTipoProcedimento() == $valor){
            $liberarAcesso=true;
          }
        }
      }
    }

    if (SessaoSEI::getInstance()->verificarPermissao('md_resposta_enviar') 
      && $objProcedimentoAPI->getSinAberto()=='S' 
      && $objProcedimentoAPI->getCodigoAcesso() > 0
      && $liberarAcesso
      && $bolProcessoEstadoNormal) {
      $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();
      $strLinkBotaoResposta  = '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_resposta_enviar&acao_origem=arvore_visualizar&acao_retorno=arvore_visualizar&id_procedimento='.$_GET['id_procedimento'].'&arvore=1').'" tabindex="'.$numTabBotao.'" class="botaoSEI">';
      $strLinkBotaoResposta .= '<img class="infraCorBarraSistema" tabindex="'.$numTabBotao.'" src="'.$strDiretorioImagens.'/img/'.MdRespostaINT::getCaminhoIcone("enviar_resposta_sei3.png").'" alt="Enviar Resposta" title="Enviar Resposta" />';
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