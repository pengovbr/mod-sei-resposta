<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaEnvioDTO extends InfraDTO {

  public function getStrNomeTabela() {
     return 'md_resposta_envio';
  }

  public function montar() {
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdResposta', 'id_resposta');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdProtocolo', 'id_procedimento');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdDocumento', 'id_documento');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Mensagem', 'mensagem');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinConclusiva', 'sin_conclusiva');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DthResposta', 'dth_resposta');

    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjAnexoDTO');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Anexos');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'IdDocumentosProcesso');

    $this->configurarPK('IdResposta', InfraDTO::$TIPO_PK_SEQUENCIAL);

    $this->configurarFK('IdResposta', 'seq_md_resposta_envio', 'id');
    $this->configurarFK('IdDocumento', 'documento', 'id_documento');
    $this->configurarFK('IdProtocolo', 'procedimento', 'id_procedimento');
  }
  
}
?>
