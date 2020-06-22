<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 17/05/2020 - criado por Higo Cavalcante
*
*
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaConfiguracaoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  protected function cadastrarControlado(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao_cadastrar',__METHOD__,$objMdRespostaConfiguracaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();

      $objMdRespostaConfiguracaoBD = new MdRespostaConfiguracaoBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaConfiguracaoBD->cadastrar($objMdRespostaConfiguracaoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro configurando Resposta.',$e);
    }
  }

  protected function listarConectado(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao_listar',__METHOD__,$objMdRespostaConfiguracaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objMdRespostaConfiguracaoBD = new MdRespostaConfiguracaoBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaConfiguracaoBD->listar($objMdRespostaConfiguracaoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando mapeamento de sistemas.',$e);
    }
  }

  protected function desativarControlado(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao_desativar',__METHOD__,$objMdRespostaConfiguracaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objMdRespostaConfiguracaoBD = new MdRespostaConfiguracaoBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaConfiguracaoBD->desativar($objMdRespostaConfiguracaoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro desativando mapeamento de sistemas.',$e);
    }
  }

  protected function reativarControlado(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao_reativar',__METHOD__,$objMdRespostaConfiguracaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objMdRespostaConfiguracaoBD = new MdRespostaConfiguracaoBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaConfiguracaoBD->reativar($objMdRespostaConfiguracaoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro reativando mapeamento de sistemas.',$e);
    }
  }

  private function validarNumIdPais(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO, InfraException $objInfraException){
  	if (InfraString::isBolVazia($objMdRespostaConfiguracaoDTO->getNumIdPais())){
	      $objInfraException->adicionarValidacao('País não selecionado.');
	  }
  }
}
?>