<?

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaEnvioRN extends InfraRN {
  
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  public function cadastrarControlado(MdRespostaEnvioDTO $objMdRespostaEnvioDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_resposta_enviar',__METHOD__,$objMdRespostaEnvioDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarDblIdProtocolo($objMdRespostaEnvioDTO, $objInfraException);
      $this->validarStrMensagem($objMdRespostaEnvioDTO, $objInfraException);
      $this->validarStrSinConclusiva($objMdRespostaEnvioDTO, $objInfraException);

      $objInfraException->lancarValidacoes();      

      $objDocumentoDTO = $this->gerarDocumento($objMdRespostaEnvioDTO);

      $objRespostaEnvioBD = new MdRespostaEnvioBD($this->getObjInfraIBanco());

      $objMdRespostaEnvioDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());

      $ret = $objRespostaEnvioBD->cadastrar($objMdRespostaEnvioDTO);

      if ($objMdRespostaEnvioDTO->isSetArrIdDocumentosProcesso()) {

        $objMdRelRespostaDocumentoBD = new MdRelRespostaDocumentoBD($this->getObjInfraIBanco());

        $objRelRespostaDocumentoDTO = new MdRelRespostaDocumentoDTO();
        foreach ($objMdRespostaEnvioDTO->getArrIdDocumentosProcesso() as $dblDocumentosProcesso) {
          $objRelRespostaDocumentoDTO->setNumIdResposta($ret->getNumIdResposta());
          $objRelRespostaDocumentoDTO->setDblIdDocumento($dblDocumentosProcesso);
          $objMdRelRespostaDocumentoBD->cadastrar($objRelRespostaDocumentoDTO);
        }
      }

      return $objDocumentoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro no envio da resposta ao Protocolo Eletrônico.',$e);
    }
  }

  protected function gerarDocumentoControlado(MdRespostaEnvioDTO $objMdRespostaEnvioDTO) {
    try{

      ini_set('max_execution_time','600');
      ini_set('memory_limit','1024M');

      $strMensagem = $objMdRespostaEnvioDTO->getStrMensagem();
      $dthDataAtual = $objMdRespostaEnvioDTO->getDthDthResposta();

      $strSinConclusiva = 'Não';
      if($objMdRespostaEnvioDTO->getStrSinConclusiva() == 'S'){
        $strSinConclusiva = 'Sim';
      }

      $this->prepararAnexos($objMdRespostaEnvioDTO);

      $objMdRespostaParametroRN = new MdRespostaParametroRN();
      
      $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
      $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_TIPO_DOCUMENTO);
      $objMdRespostaParametroDTO->retStrValor();

      $objParametroTipoDocumentoDTO = $objMdRespostaParametroRN->consultar($objMdRespostaParametroDTO);

      $objDocumentoDTO = new DocumentoDTO();
      $objDocumentoDTO->setDblIdDocumento(null);
      $objDocumentoDTO->setDblIdProcedimento($objMdRespostaEnvioDTO->getDblIdProtocolo());
      $objDocumentoDTO->setNumIdSerie($objParametroTipoDocumentoDTO->getStrValor());

      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
      $objProcedimentoDTO->setDblIdProcedimento($objMdRespostaEnvioDTO->getDblIdProtocolo());
      
      // Consulta nas classes de regra de negócio
      $objProcedimentoRN = new ProcedimentoRN();
      $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
            
      
   	  $strXML = '';
   	  $strXML .= '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
   	  $strXML .= '<documento>'."\n";
      $strXML .= '<atributo nome="Data" titulo="Data de Envio">'.InfraString::formatarXML($dthDataAtual).'</atributo>'."\n";
      $strXML .= '<atributo nome="Processo" titulo="Processo">'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'</atributo>'."\n";
      $strXML .= '<atributo nome="Mensagem" titulo="Mensagem">'.InfraString::formatarXML($strMensagem).'</atributo>'."\n";
      $strXML .= '<atributo nome="RespostaConclusiva" titulo="Resposta Conclusiva?">'.InfraString::formatarXML($strSinConclusiva).'</atributo>'."\n";

    	$objDocumentoDTO->setStrConteudo(null);
    	$objDocumentoDTO->setDblIdDocumentoEdoc(null);
    	$objDocumentoDTO->setDblIdDocumentoEdocBase(null);
   	  $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    	$objDocumentoDTO->setStrNumero(null);
    	$objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_FORMULARIO_AUTOMATICO);

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setDblIdProtocolo(null);
      $objProtocoloDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
      $objProtocoloDTO->setStrDescricao(null);
  	  $objProtocoloDTO->setDtaGeracao(InfraData::getStrDataAtual());
			$objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO(array());							

      $objProtocoloDTO->setArrObjParticipanteDTO(array());						
			$objProtocoloDTO->setArrObjObservacaoDTO(array());
	 		$objProtocoloDTO->setArrObjAnexoDTO($objMdRespostaEnvioDTO->getArrObjAnexoDTO());
	 		$objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

      $objDocumentoRN = new DocumentoRN();
	 		$objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

	 		//busca os anexos para gravar com o id possibilitando link na consulta
	 		$objAnexoDTO = new AnexoDTO();
	 		$objAnexoDTO->retNumIdAnexo();
	 		$objAnexoDTO->retStrNome();
	 		$objAnexoDTO->retNumTamanho();
	 		$objAnexoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
	 		
	 		$objAnexoRN = new AnexoRN();
	 		$arrObjAnexoDTOBanco = $objAnexoRN->listarRN0218($objAnexoDTO);
	 		
    	$strXML .= '<atributo nome="Anexos" titulo="Anexos">'."\n";
      foreach($arrObjAnexoDTOBanco as $objAnexoDTO){
        $strXML .= '<valores>'."\n";
        $strXML .= '<valor id="'.$objAnexoDTO->getNumIdAnexo().'" tipo="ANEXO">';
        $strXML .= InfraString::formatarXML($objAnexoDTO->getStrNome());
        $strXML .= '</valor>'."\n";
        $strXML .= '</valores>'."\n";
      }
      $strXML .= '</atributo>'."\n";        
      
    	$strXML .= '</documento>';

    	$dto = new DocumentoDTO();
    	$dto->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
    	$dto->setStrConteudo(InfraUtil::filtrarISO88591($strXML));
    	$objDocumentoRN->atualizarConteudoRN1205($dto);
      
      $arrObjAtributoAndamentoDTO = array();
      $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
      $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
      $objAtributoAndamentoDTO->setStrValor($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
      $objAtributoAndamentoDTO->setStrIdOrigem($objDocumentoDTO->getDblIdDocumento());
      $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

      return $objDocumentoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro na geração da resposta ao Protocolo Eletrônico.',$e);
    }
  }

  private function prepararAnexos(MdRespostaEnvioDTO $objMdRespostaEnvioDTO)
  {

    $arrStrIds = $objMdRespostaEnvioDTO->getArrIdDocumentosProcesso();

    $objDocumentoRN = new DocumentoRN();
    $objAnexoRN = new AnexoRN();

    if (InfraArray::contar($arrStrIds)) {

      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->setDblIdProcedimento($objMdRespostaEnvioDTO->getDblIdProtocolo());
      $objProcedimentoDTO->setStrSinDocTodos('S');
      $objProcedimentoDTO->setArrDblIdProtocoloAssociado($arrStrIds);

      $objProcedimentoRN = new ProcedimentoRN();
      $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

      if (InfraArray::contar($arr) == 0) {
        throw new InfraException('Processo não encontrado.');
      }

      $objProcedimentoDTO = $arr[0];

      $arrObjDocumentoDTO = InfraArray::indexarArrInfraDTO($objProcedimentoDTO->getArrObjDocumentoDTO(), 'IdDocumento');

      //criar arquivos temporários para os documentos selecionados
      foreach ($arrStrIds as $strIdDocumento) {

        if (!isset($arrObjDocumentoDTO[$strIdDocumento])) {
          throw new InfraException('Documento não encontrado ou não pertence ao processo.');
        }

        $objDocumentoDTO = $arrObjDocumentoDTO[$strIdDocumento];

        if (!$objDocumentoRN->verificarSelecaoEmail($objDocumentoDTO)) {
          throw new InfraException('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' não pode ser enviado por e-mail.');
        }

        $objDocumentoRN->bloquearProcessado($objDocumentoDTO);

        if ($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {

          $objAnexoDTO = new AnexoDTO();
          $objAnexoDTO->retStrNome();
          $objAnexoDTO->retNumIdAnexo();
          $objAnexoDTO->retStrProtocoloFormatadoProtocolo();
          $objAnexoDTO->setDblIdProtocolo($strIdDocumento);
          $objAnexoDTO->retDthInclusao();

          $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

          foreach ($arrObjAnexoDTO as $objAnexoDTO) {

            if ($objAnexoDTO == null) {
              throw new InfraException('Anexo não encontrado.');
            }

            $strNomeArquivo = InfraUtil::formatarNomeArquivo($objDocumentoDTO->getStrNomeSerie().'_'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'_'.$objAnexoDTO->getStrNome());

            $strNomeUpload = $objAnexoRN->gerarNomeArquivoTemporario();

            copy($objAnexoRN->obterLocalizacao($objAnexoDTO), DIR_SEI_TEMP.'/'.$strNomeUpload);

            $numTamanhoAnexo = filesize(DIR_SEI_TEMP.'/'.$strNomeUpload);

            $arrAnexos[] = array($strNomeUpload, $strNomeArquivo, InfraData::getStrDataHoraAtual(), $numTamanhoAnexo, InfraUtil::formatarTamanhoBytes($numTamanhoAnexo));
          }

        } else if ($objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_EDOC && $objDocumentoDTO->getDblIdDocumentoEdoc() != null) {

          $objEDocRN = new EDocRN();
          $strHtml = $objEDocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);

          $strNomeArquivoHtml = InfraUtil::formatarNomeArquivo($objDocumentoDTO->getStrNomeSerie().'_'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'.html');
          $strNomeArquivoUploadHtml = $objAnexoRN->gerarNomeArquivoTemporario();

          if (file_put_contents(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml, $strHtml) === false) {
            throw new InfraException('Erro criando arquivo html temporário para envio da resposta.');
          }

          $numTamanhoHtml = filesize(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml);

          $arrAnexos[] = array($strNomeArquivoUploadHtml, $strNomeArquivoHtml, InfraData::getStrDataHoraAtual(), $numTamanhoHtml, InfraUtil::formatarTamanhoBytes($numTamanhoHtml));

        } else if ($objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO) {

          $objEditorDTO = new EditorDTO();
          $objEditorDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
          $objEditorDTO->setNumIdBaseConhecimento(null);
          $objEditorDTO->setStrSinCabecalho('S');
          $objEditorDTO->setStrSinRodape('S');
          $objEditorDTO->setStrSinCarimboPublicacao('S');
          $objEditorDTO->setStrSinIdentificacaoVersao('N');

          $objEditorRN = new EditorRN();
          $strHtml = $objEditorRN->consultarHtmlVersao($objEditorDTO);

          $strNomeArquivoHtml = InfraUtil::formatarNomeArquivo($objDocumentoDTO->getStrNomeSerie().'_'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'.html');
          $strNomeArquivoUploadHtml = $objAnexoRN->gerarNomeArquivoTemporario();

          if (file_put_contents(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml, $strHtml) === false) {
            throw new InfraException('Erro criando arquivo html temporário para envio da resposta.');
          }

          $numTamanhoHtml = filesize(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml);

          $arrAnexos[] = array($strNomeArquivoUploadHtml, $strNomeArquivoHtml, InfraData::getStrDataHoraAtual(), $numTamanhoHtml, InfraUtil::formatarTamanhoBytes($numTamanhoHtml));

        } else if ($objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO || $objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO) {

          $strHtml = $objDocumentoRN->consultarHtmlFormulario($objDocumentoDTO);

          $strNomeArquivoHtml = InfraUtil::formatarNomeArquivo($objDocumentoDTO->getStrNomeSerie().'_'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'.html');
          $strNomeArquivoUploadHtml = $objAnexoRN->gerarNomeArquivoTemporario();

          if (file_put_contents(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml, $strHtml) === false) {
            throw new InfraException('Erro criando arquivo html temporário para envio da resposta.');
          }

          $numTamanhoHtml = filesize(DIR_SEI_TEMP.'/'.$strNomeArquivoUploadHtml);

          $arrAnexos[] = array($strNomeArquivoUploadHtml, $strNomeArquivoHtml, InfraData::getStrDataHoraAtual(), $numTamanhoHtml, InfraUtil::formatarTamanhoBytes($numTamanhoHtml));
        }
      }
    }

    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $numConversaoAnexoHtmlParaPdf = $objInfraParametro->getValor('SEI_EMAIL_CONVERTER_ANEXO_HTML_PARA_PDF', false);

		if ($numConversaoAnexoHtmlParaPdf === '1'){

		  $numAnexos = InfraArray::contar($arrAnexos);
		  for($i=0; $i<$numAnexos; $i++){

        if (substr($arrAnexos[$i][1],-4) == '.htm' || substr($arrAnexos[$i][1],-5) == '.html'){

          $strArquivoHtml = $arrAnexos[$i][0].'.html';

          rename(DIR_SEI_TEMP.'/'.$arrAnexos[$i][0], DIR_SEI_TEMP.'/'.$strArquivoHtml);

          $strArquivoPdf = $objAnexoRN->gerarNomeArquivoTemporario('.pdf');

          $strComandoPdf = DIR_SEI_BIN.'/wkhtmltopdf-amd64 --quiet '.DIR_SEI_TEMP.'/'.$strArquivoHtml.' ' .DIR_SEI_TEMP.'/'.$strArquivoPdf .' 2>&1';

          $ret = shell_exec($strComandoPdf);
          if ($ret != ''){
            throw new InfraException('Erro gerando PDF.', null, "Comando - ".$strComandoPdf."\n\nRetorno - ".$ret);
          }

          unlink(DIR_SEI_TEMP.'/'.$strArquivoHtml);

          $strNomePdf = substr($arrAnexos[$i][1], 0, strlen($arrAnexos[$i][1])-(substr($arrAnexos[$i][1],-4)=='.htm'?4:5)).'.pdf';
          $numTamanhoPdf = filesize(DIR_SEI_TEMP.'/'.$strArquivoPdf);

          $arrAnexos[$i] = array($strArquivoPdf, $strNomePdf, InfraData::getStrDataHoraAtual(), $numTamanhoPdf, InfraUtil::formatarTamanhoBytes($numTamanhoPdf));
        }
      }
    }

		$arrObjAnexoDTO = array();
    $arrAnexosTemp = array();
		foreach($arrAnexos as $anexo){
			$objAnexoDTO = new AnexoDTO();
      $objAnexoDTO->setStrSinExclusaoAutomatica('N');
      $objAnexoDTO->setNumIdAnexo($anexo[0]);
      $objAnexoDTO->setStrNome($anexo[1]);
      $objAnexoDTO->setDthInclusao($anexo[2]);
      $objAnexoDTO->setNumTamanho($anexo[3]);
      $objAnexoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
			$arrObjAnexoDTO[] = $objAnexoDTO;

      $arrAnexosTemp[$objAnexoDTO->getStrNome()] = DIR_SEI_TEMP.'/'.$objAnexoDTO->getNumIdAnexo();
    }

    $objMdRespostaEnvioDTO->setArrObjAnexoDTO($arrObjAnexoDTO);
    $objMdRespostaEnvioDTO->setArrAnexos($arrAnexosTemp);
  }

  private function validarDblIdProtocolo(MdRespostaEnvioDTO $objMdRespostaEnvioDTO, InfraException $objInfraException){
  	if (InfraString::isBolVazia($objMdRespostaEnvioDTO->getDblIdProtocolo())){
	      $objInfraException->adicionarValidacao('Processo não selecionado.');
	  }
  }
  
  private function validarStrMensagem(MdRespostaEnvioDTO $objMdRespostaEnvioDTO, InfraException $objInfraException){
  	if (InfraString::isBolVazia($objMdRespostaEnvioDTO->getStrMensagem())){
	      $objInfraException->adicionarValidacao('Mensagem não Informada.');
	  }
  }

  private function validarStrSinConclusiva(MdRespostaEnvioDTO $objMdRespostaEnvioDTO, InfraException $objInfraException){
  	if (InfraString::isBolVazia($objMdRespostaEnvioDTO->getStrSinConclusiva())){
	      $objInfraException->adicionarValidacao('Resposta conclusiva não selecionada.');
	  }
  }  
}
?>