<?


require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaParametroRN extends InfraRN {

  const PARAM_SISTEMA = 'PARAM_SISTEMA';
  const PARAM_TIPO_DOCUMENTO = 'PARAM_TIPO_DOCUMENTO';
  const PARAM_TIPO_PROCESSO = 'PARAM_TIPO_PROCESSO';
  
  public function __construct(){
    parent::__construct();
  }
  
  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
 
  public function atribuir($arrObjMdRespostaParametroDTO) {

    for ($i = 0; $i < count($arrObjMdRespostaParametroDTO); $i++) {
      $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
      $objMdRespostaParametroDTO = $arrObjMdRespostaParametroDTO[$i];

      try {
        $objMdRespostaParametroDTO = $this->cadastrar($objMdRespostaParametroDTO);
      } catch (\Exception $th) {
        $this->alterar($objMdRespostaParametroDTO);
      }

    }

    return $arrObjMdRespostaParametroDTO;
  }

  protected function cadastrarControlado(MdRespostaParametroDTO $objMdRespostaParametroDTO) 
  {
    try{
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao',__METHOD__,$objMdRespostaConfiguracaoDTO);
      
      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->cadastrar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro cadastrando parâmetro(s) do módulo de resposta.',$e);
    }
  }

  protected function excluirControlado(MdRespostaParametroDTO $objMdRespostaParametroDTO) 
  {
    try{
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao',__METHOD__,$objMdRespostaParametroDTO);
      
      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->excluir($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro excluíndo parâmetro(s) do módulo de resposta.',$e);
    }
  }

  protected function alterarControlado(MdRespostaParametroDTO $objMdRespostaParametroDTO) 
  {
    try{
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao',__METHOD__,$objMdRespostaConfiguracaoDTO);
      
      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->alterar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro alterando parâmetro(s) do módulo de resposta.',$e);
    }
  }
  
  
  protected function consultarConectado($objMdRespostaParametroDTO) 
  {
    try {
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaParametroDTO);
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->consultar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro consultado parâmetro(s) do módulo de resposta.',$e);
    }
  }
  
  protected function listarConectado($objMdRespostaParametroDTO) 
  {
    try {
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaParametroDTO);
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $arrObjMdRespostaParametroDTO = $objMdRespostaParametroBD->listar($objMdRespostaParametroDTO);
      
      return $arrObjMdRespostaParametroDTO;
      
    }catch(Exception $e){
      throw new InfraException('Erro listar parâmetros do módulo de resposta.',$e);
    }
  }  
}
?>