<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdProcessoSemRespostaDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'md_resposta_processo';
  }

  public function montar() {
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdProcedimento', 'id_procedimento');
    $this->configurarPK('IdProcedimento',InfraDTO::$TIPO_PK_INFORMADO);

    $this->configurarFK('IdProtocolo', 'procedimento', 'id_procedimento');
  }
  
}
?>