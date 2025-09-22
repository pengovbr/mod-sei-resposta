<?

require_once dirname(__FILE__).'/../../../SEI.php';


class MdRespostaWS extends InfraWS {

  public function getObjInfraLog(){
      return LogSEI::getInstance();
  }
    
  public function __call($func, $params) {
    try{

        // Dorme por 03 segundos
        sleep(3);

        SessaoSEI::getInstance(false);

        $objInfraException = new InfraException();

      if (!method_exists($this, $func.'Monitorado')) {
        $objInfraException->lancarValidacao('Serviço ['.get_class($this).'.'.$func.'] não encontrado.');
      }

        BancoSEI::getInstance()->abrirConexao();

        $SiglaSistema = $params[0]->SiglaSistema;
        $IdentificacaoServico = $params[0]->IdentificacaoServico;
        $arrIdProcedimento = (array) $params[0]?->IdProcedimentos?->IdProcedimento;
        $arrNumProcedimento = (array) $params[0]?->NumProcedimentos?->NumProcedimento;          
        $IdResposta = $params[0]?->IdResposta;
                    
        $objServicoDTO = self::obterServico($SiglaSistema, $IdentificacaoServico);
                    
        $this->validarAcessoAutorizado(explode(',', str_replace(' ', '', $objServicoDTO->getStrServidor())));

        $this->validarArrayProcedimento($objInfraException, $arrIdProcedimento, $arrNumProcedimento);

        SessaoSEI::getInstance()->setObjServicoDTO($objServicoDTO);

        $numSeg = InfraUtil::verificarTempoProcessamento();

        $debugWebServices = (int)ConfiguracaoSEI::getInstance()->getValor('SEI', 'DebugWebServices', false, 0);

      if ($debugWebServices) {
          InfraDebug::getInstance()->setBolLigado(true);
          InfraDebug::getInstance()->setBolDebugInfra(($debugWebServices==2));
          InfraDebug::getInstance()->limpar();

          InfraDebug::getInstance()->gravar("Serviço: ".$func."\nParâmetros: ".$this->debugParametros($params));

        if ($debugWebServices==1) {
          LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(), InfraLog::$DEBUG);
        }
      }

        $ret = call_user_func_array(array($this, $func.'Monitorado'), $params);

      if ($debugWebServices==2) {
          LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(), InfraLog::$DEBUG);
      }

      try {

          $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

          $objMonitoramentoServicoDTO = new MonitoramentoServicoDTO();
          $objMonitoramentoServicoDTO->setNumIdServico($objServicoDTO->getNumIdServico());
          $objMonitoramentoServicoDTO->setStrOperacao($func);
          $objMonitoramentoServicoDTO->setDblTempoExecucao($numSeg*1000);
          $objMonitoramentoServicoDTO->setStrIpAcesso(InfraUtil::getStrIpUsuario());
          $objMonitoramentoServicoDTO->setDthAcesso(InfraData::getStrDataHoraAtual());
          $objMonitoramentoServicoDTO->setStrServidor(substr($_SERVER['SERVER_NAME'].' ('.$_SERVER['SERVER_ADDR'].')', 0, 250));
          $objMonitoramentoServicoDTO->setStrUserAgent(substr($_SERVER['HTTP_USER_AGENT'], 0, 250));

          $objMonitoramentoServicoRN = new MonitoramentoServicoRN();
          $objMonitoramentoServicoRN->cadastrar($objMonitoramentoServicoDTO);

      }catch(Exception $e){
        try{
            LogSEI::getInstance()->gravar('Erro monitorando acesso do serviço.'."\n".InfraException::inspecionar($e));
        }catch (Exception $e){}
      }

        BancoSEI::getInstance()->fecharConexao();

        return $ret;

    }catch(Exception $e){

      try{
          BancoSEI::getInstance()->fecharConexao();
      }catch(Exception $e2){}

        $this->processarExcecao($e);
    }
  }

  protected function listarRespostaMonitorado($objSOAP) {
    $arrObjMdRespostaDTO = array();
    try {
                
      $objInfraException = new InfraException();
                
        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->limpar();
                
        SessaoSEI::getInstance(false);

        $SiglaSistema = $objSOAP?->SiglaSistema;
        $IdentificacaoServico = $objSOAP?->IdentificacaoServico;
        $arrIdProcedimento = $objSOAP?->IdProcedimentos?->IdProcedimento;
        $arrNumProcedimento = $objSOAP?->NumProcedimentos?->NumProcedimento;
        $IdResposta = $objSOAP?->IdResposta;

        if (empty($arrIdProcedimento) && empty($arrNumProcedimento) && empty($IdResposta)) {
          $objInfraException->lancarValidacao('Identificador e/ou processo da resposta não foi informado.');
        }

        $arrObjProcedimentoDTO = new ProcedimentoDTO();
        $arrObjProcedimentoDTO->retDblIdProcedimento();
        $arrObjProcedimentoDTO->retStrProtocoloProcedimentoFormatado();

        if(empty($arrIdProcedimento)){
          $arrIdProcedimento = array();
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
      
        if (!empty($arrIdProcedimento) && is_array($arrIdProcedimento)){
          $objMdRespostaDTO = new MdRespostaDTO();
              
          //campos que serão retornados
          $objMdRespostaDTO->retNumIdResposta();
          $objMdRespostaDTO->retDblIdProcedimento();
          $objMdRespostaDTO->retDblIdDocumento();
          $objMdRespostaDTO->retStrMensagem();
          $objMdRespostaDTO->retStrSinConclusiva();
          $objMdRespostaDTO->retDthDthResposta();
          $objMdRespostaDTO->retDblIdDocumentoAnexo(); 
          $objMdRespostaDTO->retStrProtocoloFormatadoAnexos(); 
          $objMdRespostaDTO->retStrProtocoloFormatadoResposta(); 
              
          $objMdRespostaDTO->setDblIdProcedimento($arrIdProcedimento, InfraDTO::$OPER_IN);
              
          if($IdResposta != null || $IdResposta != ""){
              $objMdRespostaDTO->setNumIdResposta($IdResposta);
          }

          $objMdRespostaRN = new MdRespostaRN();
          $arrObjMdRespostaDTO = $objMdRespostaRN->listarResposta($objMdRespostaDTO);

          if (empty($arrObjMdRespostaDTO)) {
            $mensagem = (object) array(
                'mensagem' => ""
            );

            return new SoapVar($mensagem, null, null, null);
          }

        }

        if (count($arrObjMdRespostaDTO) > 0){
      
            $IdRespostaRetorno = "";
            $arrResposta = new ArrayObject();
                  
          foreach($arrObjMdRespostaDTO as $objMdRespostaDTO ){
            if($IdRespostaRetorno != $objMdRespostaDTO->getNumIdResposta()){
              $IdRespostaRetorno = $objMdRespostaDTO->getNumIdResposta();

              $arrDocumentos = [];
              foreach($arrObjMdRespostaDTO as $objDocumentos){
                if($IdRespostaRetorno == $objDocumentos->getNumIdResposta()){
                  $soapVar = new SoapVar($objDocumentos->getStrProtocoloFormatadoAnexos(), XSD_STRING, null, null, 'ProtocoloDocumento');
                  $arrDocumentos[] = $soapVar;
                }
              }
                          
              $Resposta = (object) array(
              'IdResposta' => (int) $objMdRespostaDTO->getNumIdResposta(),
              'IdProcedimento' => (int) $objMdRespostaDTO->getDblIdProcedimento(),
              'NumProtocolo' => (string) $arrObjProcedimentoDTOIndexado[$objMdRespostaDTO->getDblIdProcedimento()]->getStrProtocoloProcedimentoFormatado(),
              'ProtocoloDocumento' => (string) $objMdRespostaDTO->getStrProtocoloFormatadoResposta(),
              'Mensagem' => (string) $objMdRespostaDTO->getStrMensagem(),
              'SinConclusiva' => (string) $objMdRespostaDTO->getStrSinConclusiva(),
              'DthResposta' => (string) $objMdRespostaDTO->getDthDthResposta(),
              'ProtocoloDocumentosAnexados' => new SoapVar($arrDocumentos,SOAP_ENC_OBJECT,null,null,'ProtocoloDocumento')
                );

                $soapVarResposta = new SoapVar($Resposta, null, null, null, 'Resposta');
                $arrResposta->append($soapVarResposta);
            }
          }

            return $arrResposta;
        }        
    } catch (Exception $e) {
        $this->processarExcecao($e);
    }
  }

  protected function cadastrarProcessoSemRespostaMonitorado($objSOAP){
    try{

        $IdProcedimento = $objSOAP?->IdProcedimento;

      if (InfraString::isBolVazia($IdProcedimento)) {
        throw new InfraException('Processo não informado.');
      }

        $objMdProcessoSemRespostaDTO = new MdProcessoSemRespostaDTO();
        $objMdProcessoSemRespostaDTO->setDblIdProcedimento($IdProcedimento);

        $objMdProcessoSemRespostaRN = new MdProcessoSemRespostaRN();
        $objMdProcessoSemRespostaRN->cadastrarProcessoSemResposta($objMdProcessoSemRespostaDTO);

        $mensagem = (object) array(
            'mensagem' => (string) "Cadastro efetuado com sucesso."
        );

        return new SoapVar($mensagem, null, null, null);

    }catch(Exception $e){
        $this->processarExcecao($e);
    }
  }

  protected function retirarProcessoSemRespostaMonitorado($objSOAP){
    try{

        $IdProcedimento = $objSOAP?->IdProcedimento;

      if (InfraString::isBolVazia($IdProcedimento)) {
        throw new InfraException('Processo não informado.');
      }

        $objMdProcessoSemRespostaDTO = new MdProcessoSemRespostaDTO();
        $objMdProcessoSemRespostaDTO->setDblIdProcedimento($IdProcedimento);

        $objMdProcessoSemRespostaRN = new MdProcessoSemRespostaRN();
        $objMdProcessoSemRespostaRN->retirarProcessoSemResposta($objMdProcessoSemRespostaDTO);

        $mensagem = (object) array(
            'mensagem' => (string) "Exclusão efetuada com sucesso."
        );

        return new SoapVar($mensagem, null, null, null);

    }catch(Exception $e){
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
        
      $objInfraException = new InfraException();

    if ($objUsuarioDTO==null){
        $objInfraException->lancarValidacao('Sistema ['.$SiglaSistema.'] não encontrado.');
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
        $objInfraException->lancarValidacao('Serviço ['.$IdentificacaoServico.'] do sistema ['.$SiglaSistema.'] não encontrado.');
    }
                
      return $objServicoDTO;
        
  }

  private function validarArrayProcedimento($objInfraException, $arrIdProcedimento, $arrNumProcedimento){
            
    if (count($arrIdProcedimento) > 100) {
        $objInfraException->lancarValidacao('Número de repetições do atributo [IdProcedimento] superior ao permitido.');
    }

    if (count($arrNumProcedimento) > 100) {
        $objInfraException->lancarValidacao('Número de repetições do atributo [NumProcedimento] superior ao permitido.');
    }
        
  }
}

$servidorSoap = new SoapServer("MdResposta.wsdl", array('encoding'=>'ISO-8859-1'));
$servidorSoap->setClass("MdRespostaWS");

//Só processa se acessado via POST
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $servidorSoap->handle();
}
?>
