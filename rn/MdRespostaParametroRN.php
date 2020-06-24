<?


require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaParametroRN extends InfraRN {

  const PARAM_SISTEMA = 'PARAM_SISTEMA';
  
  public function __construct(){
    parent::__construct();
  }
  
  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
 
  public function atribuir($parNomeParametro, $parValorParametro) 
  {
    $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
    $objMdRespostaParametroDTO->setStrNome($parNomeParametro);
    $objMdRespostaParametroDTO->setStrValor($parValorParametro);

    try {
      $this->alterar($objMdRespostaParametroDTO);  
    } catch (\Exception $th) {
      $objMdRespostaParametroDTO = $this->cadastrar($objMdRespostaParametroDTO);
    }

    return $objMdRespostaParametroDTO;
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
      throw new InfraException('Erro alterando do módulo de resposta.',$e);
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
      throw new InfraException('Erro alterando do módulo de resposta.',$e);
    }
  }
  
  
  protected function consultarConectado($parStrNomeParametro) 
  {
    try {
      
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_configuracao', __METHOD__, $parStrNomeParametro);
      
      $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
      $objMdRespostaParametroDTO->setStrNome($parStrNomeParametro);
      $objMdRespostaParametroDTO->retStrValor();
      
      $objMdRespostaParametroBD = new MdRespostaParametroBD($this->getObjInfraIBanco());
      $ret = $objMdRespostaParametroBD->consultar($objMdRespostaParametroDTO);
      
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro consultado parâmetros do módulo de resposta.',$e);
    }
  }
  
  
  // private function validarNumIdPais(MdRespostaConfiguracaoDTO $objMdRespostaConfiguracaoDTO, InfraException $objInfraException){
    // 	if (InfraString::isBolVazia($objMdRespostaConfiguracaoDTO->getNumIdPais())){
      //       $objInfraException->adicionarValidacao('País não selecionado.');
      //   }
      // }
    }
    ?>