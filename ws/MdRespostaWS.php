<?

require_once dirname(__FILE__).'/../../../SEI.php';


class MdRespostaWS extends InfraWS {

	public function getObjInfraLog(){
		return LogSEI::getInstance();
	}

	public function listarResposta($objSOAP) {
  	try {
            
        $InfraException = new InfraException();
  			
  		InfraDebug::getInstance()->setBolLigado(false);
  		InfraDebug::getInstance()->setBolDebugInfra(false);
  		InfraDebug::getInstance()->limpar();
  			
		SessaoSEI::getInstance(false);

		$SiglaSistema = $objSOAP->SiglaSistema;
		$IdentificacaoServico = $objSOAP->IdentificacaoServico;
		$arrIdProcedimento = $objSOAP->IdProcedimentos->IdProcedimento;
		$arrNumProcedimento = $objSOAP->NumProcedimentos->NumProcedimento;
		$IdResposta = $objSOAP->IdResposta;
  			
  		$objServicoDTO = self::obterServico($SiglaSistema, $IdentificacaoServico);
  			
		$this->validarAcessoAutorizado(explode(',', str_replace(' ', '', $objServicoDTO->getStrServidor())));

		$arrObjProcedimentoDTO = new ProcedimentoDTO();
		$arrObjProcedimentoDTO->retDblIdProcedimento();
		$arrObjProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
		if(empty($arrIdProcedimento)){
			is_array($arrNumProcedimento) ? $arrNumProcedimento : $arrNumProcedimento = array($arrNumProcedimento);
			$arrObjProcedimentoDTO->setStrProtocoloProcedimentoFormatadoPesquisa($arrNumProcedimento, InfraDTO::$OPER_IN);
		}else{
			is_array($arrIdProcedimento) ? $arrIdProcedimento : $arrIdProcedimento = array($arrIdProcedimento);
			$arrObjProcedimentoDTO->setDblIdProcedimento($arrIdProcedimento, InfraDTO::$OPER_IN);
		}
		
		// Consulta nas classes de regra de negócio
		$objProcedimentoRN = new ProcedimentoRN();
		$arrObjProcedimentoDTO = $objProcedimentoRN->listarRN0278($arrObjProcedimentoDTO);
		$arrObjProcedimentoDTOIndexado = InfraArray::indexarArrInfraDTO($arrObjProcedimentoDTO, "IdProcedimento");

		if(empty($arrIdProcedimento)){
			foreach ($arrObjProcedimentoDTO as $objProcedimento){
				$arrIdProcedimento[] = $objProcedimento->getDblIdProcedimento();
			}
		}
		
		$objMdRespostaDTO = new MdRespostaDTO();
		
		//campos que serão retornados
		$objMdRespostaDTO->retNumIdResposta();
		$objMdRespostaDTO->retDblIdProcedimento();
		$objMdRespostaDTO->retDblIdDocumento();
		$objMdRespostaDTO->retStrMensagem();
		$objMdRespostaDTO->retStrSinConclusiva();
		$objMdRespostaDTO->retDthDthResposta();
		$objMdRespostaDTO->retDblIdDocumentoAnexo(); 
		
		$objMdRespostaDTO->setDblIdProcedimento($arrIdProcedimento, InfraDTO::$OPER_IN);
		
		if($IdResposta != null || $IdResposta != ""){
			$objMdRespostaDTO->setNumIdResposta($IdResposta);
		}

  		$objMdRespostaRN = new MdRespostaRN();
		$arrObjMdRespostaDTO = $objMdRespostaRN->listarResposta($objMdRespostaDTO);

		if (count($arrObjMdRespostaDTO)){
  
		  $IdResposta = "";
		  foreach($arrObjMdRespostaDTO as $objMdRespostaDTO ){
			if($IdResposta != $objMdRespostaDTO->getNumIdResposta()){
				$IdResposta = $objMdRespostaDTO->getNumIdResposta();

				$arrDocumentos = array();
				foreach($arrObjMdRespostaDTO as $objDocumentos){
					if($IdResposta == $objDocumentos->getNumIdResposta()){
						$arrDocumentos[] = (object) array('IdDocumento' => $objDocumentos->getDblIdDocumentoAnexo());
					}
				}
				
				$Resposta[] = (object) array("Resposta" =>  (object) array(
					'IdResposta' => $objMdRespostaDTO->getNumIdResposta(),
					'IdProcedimento' => $objMdRespostaDTO->getDblIdProcedimento(),
					'NumProtocolo' => $arrObjProcedimentoDTOIndexado[$objMdRespostaDTO->getDblIdProcedimento()]->getStrProtocoloProcedimentoFormatado(),
					'IdDocumento' => $objMdRespostaDTO->getDblIdDocumento(),
					'Mensagem' => $objMdRespostaDTO->getStrMensagem(),
					'SinConclusiva' => $objMdRespostaDTO->getStrSinConclusiva(),
					'DthResposta' => $objMdRespostaDTO->getDthDthResposta(),
					'IdDocumentos' => $arrDocumentos
				));
			}
		  }

		  return $Resposta;

		}		

        if ($arrObjMdRespostaDTO==null) {
            throw new InfraException('Nenhuma resposta encontrada.');
        }
  
  	} catch (Exception $e) {
  		$this->processarExcecao($e);
  	}
  }

  private function obterServico($SiglaSistema, $IdentificacaoServico){
		
	  $objUsuarioDTO = new UsuarioDTO();
	  $objUsuarioDTO->retNumIdUsuario();
	  $objUsuarioDTO->setStrSigla($SiglaSistema);
	  $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SISTEMA);
	  
	  $objUsuarioRN = new UsuarioRN();
	  $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
	  
	  if ($objUsuarioDTO==null){
	    throw new InfraException('Sistema ['.$SiglaSistema.'] não encontrado.');
	  }
	  
	  $objServicoDTO = new ServicoDTO();
	  $objServicoDTO->retNumIdServico();
	  $objServicoDTO->retStrIdentificacao();
	  $objServicoDTO->retStrSiglaUsuario();
	  $objServicoDTO->retNumIdUsuario();
	  $objServicoDTO->retStrServidor();
	  $objServicoDTO->retStrSinLinkExterno();
	  $objServicoDTO->retNumIdContatoUsuario();
	  $objServicoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
	  $objServicoDTO->setStrIdentificacao($IdentificacaoServico);
			
	  $objServicoRN = new ServicoRN();
	  $objServicoDTO = $objServicoRN->consultar($objServicoDTO); 
			
	  if ($objServicoDTO==null){
		throw new InfraException('Serviço ['.$IdentificacaoServico.'] do sistema ['.$SiglaSistema.'] não encontrado.');
	  }
			
	  return $objServicoDTO;
	  
	}
}

$servidorSoap = new SoapServer("MdResposta.wsdl",array('encoding'=>'ISO-8859-1'));
$servidorSoap->setClass("MdRespostaWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$servidorSoap->handle();
}
?>