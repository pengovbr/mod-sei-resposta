<?


require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaParametroRN extends InfraRN {

  const PARAM_SISTEMA = 'PARAM_SISTEMA';
  const PARAM_TIPO_DOCUMENTO_AJUSTE_COMPLEMENTACAO = 'PARAM_TIPO_DOCUMENTO_AJUSTE_COMPLEMENTACAO';
  const PARAM_TIPO_DOCUMENTO_RESULTADO = 'PARAM_TIPO_DOCUMENTO_RESULTADO';
  const PARAM_TIPO_PROCESSO = 'PARAM_TIPO_PROCESSO';
  
  public function __construct(){
    parent::__construct();
  }
  
  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
 
  public function atribuir($arrObjMdRespostaParametroDTO) {

    //Valida Permissao
    SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaConfiguracaoDTO);

    //Regras de Negocio
    $objInfraException = new InfraException();
    
    $this->validarParametros($arrObjMdRespostaParametroDTO, $objInfraException);
    $objInfraException->lancarValidacoes();             


    foreach ($arrObjMdRespostaParametroDTO as $objMdRespostaParametro) {
      $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
      $objMdRespostaParametroDTO = $objMdRespostaParametro;           

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
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaConfiguracaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      $objInfraException->lancarValidacoes();   
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->cadastrar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro cadastrando parâmetro(s) do módulo de resposta.', $e);
    }
  }

  protected function excluirControlado(MdRespostaParametroDTO $objMdRespostaParametroDTO) 
  {
    try{
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaParametroDTO);
      
      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->excluir($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro excluíndo parâmetro(s) do módulo de resposta.', $e);
    }
  }

  protected function alterarControlado(MdRespostaParametroDTO $objMdRespostaParametroDTO) 
  {
    try{
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $objMdRespostaConfiguracaoDTO);
      
      //Regras de Negocio
      $objInfraException = new InfraException();

      $objInfraException->lancarValidacoes();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->alterar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro alterando parâmetro(s) do módulo de resposta.', $e);
    }
  }
  
  
  protected function consultarConectado($objMdRespostaParametroDTO) 
  {
    try {
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->consultar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro consultado parâmetro(s) do módulo de resposta.', $e);
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
      throw new InfraException('Erro listar parâmetros do módulo de resposta.', $e);
    }
  }  

  private function validarParametros($arrObjMdRespostaParametroDTO, InfraException $objInfraException){

    $sistemaVazio = true;
    $tipoProcessoVazio = true;
    $tipoDocumentoResultadoVazio = true;
    $tipoDocumentoAjusteComplementacaoVazio = true;

    foreach ($arrObjMdRespostaParametroDTO as $objMdRespostaParametroDTO) {
      if($objMdRespostaParametroDTO->getStrNome() == MDRespostaParametroRN::PARAM_SISTEMA){
        if (!InfraString::isBolVazia($objMdRespostaParametroDTO->getStrValor())){
          $sistemaVazio = false;
        }
      }

      if($objMdRespostaParametroDTO->getStrNome() == MDRespostaParametroRN::PARAM_TIPO_PROCESSO){
        if (!InfraString::isBolVazia($objMdRespostaParametroDTO->getStrValor())){
          $tipoProcessoVazio = false;
        }
      }

      if($objMdRespostaParametroDTO->getStrNome() == MDRespostaParametroRN::PARAM_TIPO_DOCUMENTO_RESULTADO){
        if (!InfraString::isBolVazia($objMdRespostaParametroDTO->getStrValor())){
          $tipoDocumentoResultadoVazio = false;
        }
      }

      if($objMdRespostaParametroDTO->getStrNome() == MDRespostaParametroRN::PARAM_TIPO_DOCUMENTO_AJUSTE_COMPLEMENTACAO){
        if (!InfraString::isBolVazia($objMdRespostaParametroDTO->getStrValor())){
          $tipoDocumentoAjusteComplementacaoVazio = false;
        }
      }
    }

    if($sistemaVazio){
      $objInfraException->adicionarValidacao('Selecione o Sistema.');
    }

    if($tipoDocumentoResultadoVazio){
      $objInfraException->adicionarValidacao('Selecione o Tipo de Documento para Resultado.');
    }

    if($tipoDocumentoAjusteComplementacaoVazio){
      $objInfraException->adicionarValidacao('Selecione o Tipo de Documento para Solicitação de Ajuste ou Complementação.');
    }

  }
}
?>
