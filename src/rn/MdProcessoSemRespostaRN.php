<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdProcessoSemRespostaRN extends InfraRN {

  public function __construct(){
      parent::__construct();
  }
    
  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }
    
  protected function cadastrarProcessoSemRespostaControlado(MdProcessoSemRespostaDTO $objMdProcessoSemRespostaDTO){
    try {
    
        $objMdProcessoSemRespostaBD = new MdProcessoSemRespostaBD($this->getObjInfraIBanco());
        $objMdProcessoSemRespostaBD->cadastrar($objMdProcessoSemRespostaDTO);

    }catch(Exception $e){
        throw new InfraException('Erro cadastrando processo sem resposta.', $e);
    }
  }

  protected function retirarProcessoSemRespostaControlado(MdProcessoSemRespostaDTO $objMdProcessoSemRespostaDTO){
    try {
    
        $objMdProcessoSemRespostaBD = new MdProcessoSemRespostaBD($this->getObjInfraIBanco());
        $objMdProcessoSemRespostaBD->excluir($objMdProcessoSemRespostaDTO);

    }catch(Exception $e){
        throw new InfraException('Erro excluindo processo sem resposta.', $e);
    }
  }

  protected function consultarProcessoSemRespostaConectado(MdProcessoSemRespostaDTO $objMdProcessoSemRespostaDTO){
    try {
    
        $objMdProcessoSemRespostaBD = new MdProcessoSemRespostaBD($this->getObjInfraIBanco());
        $objMdProcessoSemRespostaDTO = $objMdProcessoSemRespostaBD->consultar($objMdProcessoSemRespostaDTO);
    
        return $objMdProcessoSemRespostaDTO;

    }catch(Exception $e){
        throw new InfraException('Erro consultando processo sem resposta.', $e);
    }
  }
}
?>