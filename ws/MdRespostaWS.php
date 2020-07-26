<?

require_once dirname(__FILE__).'/../../../SEI.php';


class MdRespostaWS extends InfraWS {

  public function getObjInfraLog(){
		return LogSEI::getInstance();
  }

  public function listarResposta($SiglaSistema, $IdentificacaoServico, $IdProcedimento, $IdResposta = "") {
  	try {
            
        $InfraException = new InfraException();
  			
  		InfraDebug::getInstance()->setBolLigado(false);
  		InfraDebug::getInstance()->setBolDebugInfra(false);
  		InfraDebug::getInstance()->limpar();
  			
  		SessaoSEI::getInstance(false);
  			
  		$objServicoDTO = self::obterServico($SiglaSistema, $IdentificacaoServico);
  			
		$this->validarAcessoAutorizado(explode(',', str_replace(' ', '', $objServicoDTO->getStrServidor())));

		$objProcedimentoDTO = new ProcedimentoDTO();
		$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
		$objProcedimentoDTO->setDblIdProcedimento($IdProcedimento);
		
		// Consulta nas classes de regra de negócio
		$objProcedimentoRN = new ProcedimentoRN();
		$objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);	
		
		$objMdRespostaDTO = new MdRespostaDTO();
		
		//campos que serão retornados
		$objMdRespostaDTO->retNumIdResposta();
		$objMdRespostaDTO->retDblIdProcedimento();
		$objMdRespostaDTO->retDblIdDocumento();
		$objMdRespostaDTO->retStrMensagem();
		$objMdRespostaDTO->retStrSinConclusiva();
		$objMdRespostaDTO->retDthDthResposta();
		$objMdRespostaDTO->retDblIdDocumentoAnexo(); 
		
		$objMdRespostaDTO->setDblIdProcedimento($IdProcedimento);
		
		if($IdResposta != null || $IdResposta != ""){
			$objMdRespostaDTO->setNumIdResposta($IdResposta);
		}

  		$objMdRespostaRN = new MdRespostaRN();
		$arrObjMdRespostaDTO = $objMdRespostaRN->listarResposta($objMdRespostaDTO);
		
		$ret = array();

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

				$ret[] = (object) array(
					'IdResposta' => $objMdRespostaDTO->getNumIdResposta(),
					'IdProcedimento' => $objMdRespostaDTO->getDblIdProcedimento(),
					'NumProtocolo' => $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),
					'IdDocumento' => $objMdRespostaDTO->getDblIdDocumento(),
					'Mensagem' => $objMdRespostaDTO->getStrMensagem(),
					'SinConclusiva' => $objMdRespostaDTO->getStrSinConclusiva(),
					'DthResposta' => $objMdRespostaDTO->getDthDthResposta(),
					'IdDocumentos' => $arrDocumentos);
			}
		  }
  
		  return $ret;

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