<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaDTO extends InfraDTO {

	public function __construct(){
		parent::__construct();
	}

	public function getStrNomeTabela() {
		return 'md_resposta_rel_documento';
	}

	public function montar() {
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdRespostaAnexo', 'id_resposta');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,'IdDocumentoAnexo', 'id_documento');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,'IdResposta','id_resposta','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,'IdProcedimento', 'id_procedimento','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,'IdDocumento', 'id_documento','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'Mensagem', 'mensagem','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'SinConclusiva', 'sin_conclusiva','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,'DthResposta', 'dth_resposta','md_resposta_envio');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'ProtocoloFormatadoAnexos', 'panx.protocolo_formatado','protocolo panx');
		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'ProtocoloFormatadoResposta', 'presp.protocolo_formatado','protocolo presp');

		$this->configurarFK('IdRespostaAnexo', 'md_resposta_envio', 'id_resposta');
		$this->configurarFK('IdDocumentoAnexo', 'protocolo panx', 'panx.id_protocolo');
		$this->configurarFK('IdDocumento', 'protocolo presp', 'presp.id_protocolo');

	}
}
?>