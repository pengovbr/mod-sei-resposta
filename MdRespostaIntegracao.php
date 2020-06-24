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

      //case 'md_resposta_configuracao_cadastrar':
      case 'md_resposta_configuracao':
        require_once dirname(__FILE__).'/resposta_configuracao.php';
        return true;

      case 'md_resposta_configuracao_listar':
      case 'md_resposta_configuracao_desativar':
      case 'md_resposta_configuracao_reativar':
        require_once dirname(__FILE__).'/resposta_configuracao_listar.php';
        return true;    
        
      case 'md_procedimento_enviar_resposta':
        require_once dirname(__FILE__).'/resposta_envio.php';
        return true;
    }

    return false;
  }


  public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI){

    $arrBotoes = array();
    $objPaginaSEI = PaginaSEI::getInstance();
    $strDiretorioImagens = self::getDiretorio();

    //if (SessaoSEI::getInstance()->verificarPermissao('md_procedimento_enviar_resposta') && $objProcedimentoAPI->getSinAberto()=='S' && $objProcedimentoAPI->getCodigoAcesso() > 0) {
    if (SessaoSEI::getInstance()->verificarPermissao('md_resposta_configuracao') && $objProcedimentoAPI->getSinAberto()=='S' && $objProcedimentoAPI->getCodigoAcesso() > 0) {
      $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();
      $strLinkBotaoResposta  = '<a href="#" tabindex="'.$numTabBotao.'" class="botaoSEI">';
      $strLinkBotaoResposta .= '<img class="infraCorBarraSistema" tabindex="'.$numTabBotao.'" src="'.$strDiretorioImagens.'/imagens/abc_grande.png" alt="Enviar Resposta" title="Enviar Resposta" />';
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