<?

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();
 
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();

  SessaoSEI::getInstance()->validarLink();
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $arrComandos = array();

  //Filtrar parâmetros
  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }

  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }
  
  $bolEnvioOK = false;

  switch($_GET['acao']){

    case 'md_resposta_enviar':
      
      $strTitulo = 'Enviar Resposta ao Protocolo Eletrônico';
      
      $arrProtocolos = array();
      $arrProtocolos[] = $_GET['id_procedimento'];     
      
      $objMdRespostaEnvioDTO = new MdRespostaEnvioDTO();
      $objMdRespostaEnvioDTO->setNumIdResposta(null);
		  $objMdRespostaEnvioDTO->setStrMensagem($_POST['txaMensagem']);
      $objMdRespostaEnvioDTO->setDblIdProtocolo($_GET['id_procedimento']);
      $objMdRespostaEnvioDTO->setStrSinConclusiva($_POST['rdoSinConclusiva']);
      $objMdRespostaEnvioDTO->setDthDthResposta(InfraData::getStrDataHoraAtual());
		  
      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->retNumIdUnidadeGeradoraProtocolo();
      $objProcedimentoDTO->setDblIdProcedimento($_GET['id_procedimento']);
      $objProcedimentoDTO->setStrSinDocTodos('S');
        
      $objProcedimentoRN = new ProcedimentoRN();
      $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

			if(count($arr) == 0){
				throw new InfraException('Processo não encontrado.');
			}
			
			$objProcedimentoDTO = $arr[0];
      
      $bolAnexouDocumento = false;
      			
			$objDocumentoRN = new DocumentoRN();
			
			$numDocumentos = 0;
			
			if (InfraArray::contar($objProcedimentoDTO->getArrObjDocumentoDTO())){
				
			  $strCheck = PaginaSEI::getInstance()->getThCheck();
			   
			  $bolAcaoDocumentoVisualizar = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar');

			  if (count($objProcedimentoDTO->getArrObjDocumentoDTO())) {

          $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
          $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS);
          $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
          $objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($objProcedimentoDTO->getArrObjDocumentoDTO(), 'IdDocumento'));

          $objProtocoloRN = new ProtocoloRN();
          $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO), 'IdProtocolo');

          foreach ($objProcedimentoDTO->getArrObjDocumentoDTO() as $objDocumentoDTO) {
            if (isset($arrObjProtocoloDTO[$objDocumentoDTO->getDblIdDocumento()]) && $objDocumentoRN->verificarSelecaoEmail($objDocumentoDTO)) {
              $strResultadoDocumentos .= '<tr class="infraTrClara">';

              $strSinValor = 'N';
              if (isset($_GET['id_documento']) && $_GET['id_documento'] == $objDocumentoDTO->getDblIdDocumento()) {
                $strSinValor = 'S';
              }

              $strResultadoDocumentos .= '<td align="center" class="infraTd">';
              $strResultadoDocumentos .= PaginaSEI::getInstance()->getTrCheck($numDocumentos++, $objDocumentoDTO->getDblIdDocumento(), $objDocumentoDTO->getStrProtocoloDocumentoFormatado(), $strSinValor);
              $strResultadoDocumentos .= '</td>';

              $strResultadoDocumentos .= '<td align="center" class="infraTd">';
              if ($bolAcaoDocumentoVisualizar) {
                $strResultadoDocumentos .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&id_documento='.$objDocumentoDTO->getDblIdDocumento()).'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="protocoloNormal" style="font-size:1em !important;">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</a>';
              } else {
                $strResultadoDocumentos .= $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
              }
              $strResultadoDocumentos .= '</td>';

              $strResultadoDocumentos .= '<td  class="infraTd">';
              $strResultadoDocumentos .= PaginaSEI::tratarHTML($objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero());
              $strResultadoDocumentos .= '</td>';

              $strResultadoDocumentos .= '<td align="center" class="infraTd">';
              $strResultadoDocumentos .= $objDocumentoDTO->getDtaGeracaoProtocolo();
              $strResultadoDocumentos .= '</td>';

              $strResultadoDocumentos .= '</tr>';

            }
          }
        }
			}
			
      $strResultadoDocumentos = '<table id="tblDocumentos" width="95%" class="infraTable" summary="Lista de Documentos">
 						  									<caption class="infraCaption" >'.PaginaSEI::getInstance()->gerarCaptionTabela("Documentos",$numDocumentos).'</caption> 
						 										<tr>
						  										<th class="infraTh" width="10%">'.$strCheck.'</th>
						  										<th class="infraTh" width="15%">Nº SEI</th>
						  										<th class="infraTh">Documento</th>
						  										<th class="infraTh" width="15%">Data</th>
						  										
						  									</tr>'.
                                $strResultadoDocumentos.
                                '</table>';
        

      if (isset($_POST['hdnFlagEnvio'])){
      	
     	  try{
					$objMdRespostaEnvioDTO->setArrIdDocumentosProcesso(PaginaSEI::getInstance()->getArrStrItensSelecionados());

					$objMdRespostaEnvioRN = new MdRespostaEnvioRN();
					$objDocumentoDTO = $objMdRespostaEnvioRN->cadastrar($objMdRespostaEnvioDTO);
					
					if ($_GET['acao']!='responder_formulario'){
					  PaginaSEI::getInstance()->setStrMensagem(PaginaSEI::getInstance()->formatarParametrosJavaScript('Resposta enviada.'),PaginaSEI::$TIPO_MSG_AVISO);
					}
          
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem='.$_GET['acao'].'&id_procedimento='.$_GET['id_procedimento'].'&id_documento='.$objDocumentoDTO->getDblIdDocumento().'&atualizar_arvore=1'));
          die;
					
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
	    }
      
      break;
     
    	default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

	$arrComandos[] = '<button type="button" onclick="submeterFormulario();" accesskey="E" name="btnEnviar" value="Enviar" class="infraButton"><span class="infraTeclaAtalho">E</span>nviar</button>';
	$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="history.go(-1);" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

  $strItensSelProcedimentos = ProcedimentoINT::conjuntoCompletoFormatadoRI0903($arrProtocolos);

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>

#lblProcedimentos {position:absolute;left:0%;top:0%;}
#selProcedimentos {position:absolute;left:0%;top:45%;width:95.5%;}

#lblMensagem {position:absolute;left:0%;top:5%;}
#txaMensagem {position:absolute;left:0%;top:13%;width:95%;}

#fldSinConclusiva {position:relative;top:65%;width:93%;}

.remover {display:none;color:blue;float:right;font-size:0.8em;}
.select2-highlighted a {display: inline}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->adicionarStyle('js/select2/select2.css');
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('js/select2/select2.min.js');
PaginaSEI::getInstance()->adicionarJavaScript('js/select2/select2_locale_pt-BR.js');
PaginaSEI::getInstance()->abrirJavaScript();

?>
//<script>

function inicializar(){
  
  <?if ($bolEnvioOK){ ?>
    self.setTimeout('window.close()',1000);
  <?}?>

  if (INFRA_IE == 0){
     window.scrollTo(0,0);  
  }else{
     self.setTimeout('window.scrollTo(0,0);',100);  
  }
}

function validarEnvio() {
  
  if (document.getElementById('selProcedimentos').value == 'null') {
    alert('Processo não informado.');
    document.getElementById('selProcedimentos').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txaMensagem').value)=='') {
    alert('Informe a Mensagem.');
    document.getElementById('txaMensagem').focus();
    return false;
  }

  if (document.getElementById('hdnInfraItensSelecionados') == null){
    alert('Não há documento(s) no processo.');
    return false;
  }

  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum documento selecionado.');
    return false;
  }

  if (!document.getElementById('optDefinitiva').checked && !document.getElementById('optParcial').checked && !document.getElementById('optSemResposta').checked){
    alert('Selecione o Tipo de resposta.');
    return false;
  }
    
  return true;
}

function submeterFormulario(){	
	if (validarEnvio()){
	
	  infraExibirAviso(false);
	  
    var arrBotoesEnviar = document.getElementsByName('btnEnviar');
    for(var i=0; i < arrBotoesEnviar.length; i++){
       arrBotoesEnviar[i].disabled = true;
    } 
	    
    document.getElementById('frmResposta').submit();
  }
}

function descricaoResposta(obj){

  document.getElementById('divDescrciaoResposta').style.display='inline';
  switch (obj.value) {
    case 'R':
      document.getElementById('divDescrciaoResposta').innerHTML='OBS: Preencha o campo Mensagem e anexe o(s) documento(s) necessários.';
      break;
    case 'A':
      document.getElementById('divDescrciaoResposta').innerHTML='OBS: Preencha o campo Mensagem e anexe o(s) documento(s) necessários. *O solicitante terá até 10 dias contados da ciência para responder.';
      break;
    case 'C':
      document.getElementById('divDescrciaoResposta').innerHTML='OBS: Preencha o campo Mensagem e anexe o(s) documento(s) necessários. A solicitação será concluída no Portal de Serviços, tendo em vista não caber resposta.';
      break;
  }
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmResposta" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" style="display:inline;">

  <div id="divProcedimentos" class="infraAreaDados" style="height:4em;">
	 	<label id="lblProcedimentos" for="selProcedimentos" class="infraLabelObrigatorio">Processos:</label>
	  <select id="selProcedimentos" name="selProcedimentos" disabled="disabled" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  <?=$strItensSelProcedimentos?>
    </select>
  </div>

  <div id="divMensagem" class="infraAreaDados" style="height:30em;">
    <label id="lblMensagem" for="txaMensagem" accesskey="" class="infraLabelObrigatorio">Mensagem:</label>
    <textarea id="txaMensagem" name="txaMensagem" maxlength="5000" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'15':'16'?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onselect="infraPosicionarCursor(this);" onclick="infraPosicionarCursor(this);" onkeyup="infraPosicionarCursor(this);"><?=PaginaSEI::tratarHTML($objMdRespostaEnvioDTO->getStrMensagem())?></textarea>
    <input type="hidden" id="hdnFlagEnvio" name="hdnFlagEnvio" value="1" />
  </div>

  <div id="divDocumentosProcesso" style="margin-top:.7em;">
     <?
     PaginaSEI::getInstance()->montarAreaTabela($strResultadoDocumentos,$numDocumentos);
     ?>
  </div>

  <fieldset id="fldSinConclusiva" class="infraFieldset" style="margin-top:3em; height:5em">
    	<legend class="infraLegend">&nbsp;Resposta ao Gov.br&nbsp;</legend>
    	
      <div id="divOptDefinitiva" class="infraDivRadio" style="position:absolute;left:5%;"> 
        <input type="radio" name="rdoSinConclusiva" id="optDefinitiva" onclick="descricaoResposta(this)" value="<?=MdRespostaEnvioRN::$EV_RESPOSTA?>" class="infraRadio"/>
        <span id="spnDefinitiva"><label id="lblDefinitiva" for="optDefinitiva" class="infraLabelRadio" >Enviar resposta</label></span>
      </div>
    
      <div id="divOptParcial" class="infraDivRadio" style="position:absolute;left:40%;">	  
        <input type="radio" name="rdoSinConclusiva" id="optParcial" onclick="descricaoResposta(this)" value="<?=MdRespostaEnvioRN::$EV_AJUSTE?>" class="infraRadio"/>
        <span id="spnParcial"><label id="lblParcial" for="optParcial" class="infraLabelRadio" >Enviar para ajuste/complementação</label></span>
      </div>
      
      <div id="divOptSemResposta" class="infraDivRadio" style="position:absolute;left:80%;">
        <input type="radio" name="rdoSinConclusiva" id="optSemResposta" onclick="descricaoResposta(this)" value="<?=MdRespostaEnvioRN::$EV_CONCLUSAO?>" class="infraRadio"/>
        <span id="spnSemResposta"><label id="lblSemResposta" for="optSemResposta" class="infraLabelRadio" >Enviar para conclusão</label></span>
      </div>
  	  <div id="divDescrciaoResposta" style="position:relative;left:5%;top:65%; display:none"></div>
  </fieldset> 

</form>
<? 
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos,true);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>