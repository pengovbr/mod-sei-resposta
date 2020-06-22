<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 29/04/2016 - criado por mga@trf4.jus.br
 *
 */

 /*
 No SIP criar os recursos md_resposta_processar, md_resposta_processar e md_abc_andamento_lancar e adicionar em um novo perfil chamado MD_ABC_Básico.
*/

class MdRespostaIntegracao extends SeiIntegracao{

  public function __construct(){
  }

  public function getNome(){
    return 'Módulo de Resposta';
  }

  public function getVersao() {
    return '1.0.0';
  }

  public function getInstituicao(){
    return 'TRF4 - Tribunal Regional Federal da 4ª Região';
  }

  public function processarControlador($strAcao){

    switch($strAcao) {

      case 'md_resposta_configuracao_cadastrar':
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

    if (SessaoSEI::getInstance()->verificarPermissao('md_procedimento_enviar_resposta') && $objProcedimentoAPI->getSinAberto()=='S' && $objProcedimentoAPI->getCodigoAcesso() > 0) {
      $arrBotoes[] = '<a href="#" onclick="enviarRespostalProcedimento();" tabindex="'.$numTabBotao.'" class="botaoSEI"><img class="infraCorBarraSistema"  tabindex="'.$numTabBotao.'" src="modulos/resposta/imagens/abc_grande.png" alt="Enviar Resposta" title="Enviar Resposta"/>';
    }

    return $arrBotoes;
  }

}
?>