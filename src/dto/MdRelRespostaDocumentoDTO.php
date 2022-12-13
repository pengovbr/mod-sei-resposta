<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRelRespostaDocumentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
     return 'md_resposta_rel_documento';
  }

  public function montar() {
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdResposta', 'id_resposta');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdDocumento', 'id_documento');

    $this->configurarPK('IdResposta', InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarPK('IdDocumento', InfraDTO::$TIPO_PK_INFORMADO);

    $this->configurarFK('IdResposta', 'seq_md_resposta_envio', 'id');
    $this->configurarFK('IdDocumento', 'documento', 'id_documento');
  }
  
}
?>
