<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaRN extends InfraRN {

  public function __construct(){
      parent::__construct();
  }
    
  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }
    
  protected function listarRespostaConectado(MdRespostaDTO $objMdRespostaDTO){
    try {
    
        $objMdRespostaBD = new MdRespostaBD($this->getObjInfraIBanco());
        $arrObjMdRespostaDTO = $objMdRespostaBD->listar($objMdRespostaDTO);
    
        return $arrObjMdRespostaDTO;

    }catch(Exception $e){
        throw new InfraException('Erro listando respostas.', $e);
    }
  }
}
?>