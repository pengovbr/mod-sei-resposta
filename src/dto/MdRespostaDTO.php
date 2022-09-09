<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaDTO extends InfraDTO {

	public function __construct(){
		parent::__construct();
	}

	public function getStrNomeTabela() {
		return 'md_resposta_envio';
	}

	public function montar() {
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdResposta','id_resposta');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,'IdProcedimento', 'id_procedimento');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,'IdDocumento', 'id_documento');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'Mensagem', 'mensagem');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'SinConclusiva', 'sin_conclusiva');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,'DthResposta', 'dth_resposta');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,'IdRespostaAnexo', 'id_resposta', 'md_resposta_rel_documento');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,'IdDocumentoAnexo', 'id_documento', 'md_resposta_rel_documento');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'ProtocoloFormatadoAnexos', 'panx.protocolo_formatado','protocolo panx');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'ProtocoloFormatadoResposta', 'presp.protocolo_formatado','protocolo presp');

		$this->configurarFK('IdResposta', 'md_resposta_rel_documento', 'id_resposta', InfraDTO::$TIPO_FK_OPCIONAL);
		$this->configurarFK('IdDocumentoAnexo', 'protocolo panx', 'panx.id_protocolo', InfraDTO::$TIPO_FK_OPCIONAL);
		$this->configurarFK('IdDocumento', 'protocolo presp', 'presp.id_protocolo', InfraDTO::$TIPO_FK_OPCIONAL);

	}
}
?>