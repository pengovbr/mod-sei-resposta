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
      
      $strTitulo = 'Enviar Resposta pelo Protocolo Digital';
      
      $arrProtocolos = array();
      $arrProtocolos[] = $_GET['id_procedimento'];     
      
      $objMdRespostaEnvioDTO = new MdRespostaEnvioDTO();
      $objMdRespostaEnvioDTO->setNumIdResposta(null);
		  $objMdRespostaEnvioDTO->setStrMensagem($_POST['txaMensagem']);
      $objMdRespostaEnvioDTO->setDblIdProtocolo($_GET['id_procedimento']);
      //$objMdRespostaEnvioDTO->setStrSinConclusiva($_POST['rdoSinConclusiva']);
      $objMdRespostaEnvioDTO->setStrSinConclusiva(MdRespostaEnvioRN::$EV_RESPOSTA);
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
<link rel="stylesheet" href="<?php print MdRespostaIntegracao::getDiretorio(); ?>/css/<?php print MdRespostaINT::getCssCompatibilidadeSEI4("md_resposta_sei3.css"); ?>" type="text/css" />
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

  /*if (!document.getElementById('optDefinitiva').checked && !document.getElementById('optParcial').checked){
    alert('Selecione o Tipo de resposta.');
    return false;
  }*/
    
  return true;
}

function submeterFormulario(){	
  if (validarEnvio()){

    transmitir=true;

    /*if(document.getElementById('optDefinitiva').checked ){
      if (!confirm("Confirma o envio da resposta? \nEssa ação não poderá ser desfeita.")) {
        transmitir=false;
      }
    }*/

    if (!confirm("Confirma o envio da resposta? \nEssa ação não poderá ser desfeita. \n\nOBS.: Após a verificação da resposta pelo solicitante no portal gov.br, será anexado automaticamente no processo o Termo de Ciência de Recebimento da Resposta.")) {
        transmitir=false;
    }    

    if(transmitir){
      infraExibirAviso(false);
      var arrBotoesEnviar = document.getElementsByName('btnEnviar');
      for(var i=0; i < arrBotoesEnviar.length; i++){
        arrBotoesEnviar[i].disabled = true;
      } 
      
      document.getElementById('frmResposta').submit();
    }

    return transmitir;

  }
}

function descricaoResposta(obj){

  document.getElementById('divDescricaoResposta').style.display='inline';
  switch (obj.value) {
    case 'R':
      document.getElementById('divDescricaoResposta').innerHTML='OBS: Preencha o campo Mensagem e anexe o(s) documento(s) necessários.';
      break;
    case 'A':
      document.getElementById('divDescricaoResposta').innerHTML='OBS: Preencha o campo Mensagem e anexe o(s) documento(s) necessários. *O solicitante terá até 10 dias contados da ciência para responder.';
      break;
  }
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>

<link rel="stylesheet" href="<?php print MdRespostaIntegracao::getDiretorio(); ?>/css/<?php print MdRespostaINT::getCssCompatibilidadeSEI4("md_resposta_sei3.css"); ?>" type="text/css" />

<form id="frmResposta" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" style="display:inline;">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
  <div id="divProcedimentos" class="infraAreaDados">
	 	<label id="lblProcedimentos" for="selProcedimentos" class="infraLabelObrigatorio">Processo:</label>
	  <select id="selProcedimentos" name="selProcedimentos" disabled="disabled" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  <?=$strItensSelProcedimentos?>
    </select>
  </div>
  <br/>
  <div id="divMensagem" class="infraAreaDados">
    <label id="lblMensagem" for="txaMensagem" accesskey="" class="infraLabelObrigatorio">Mensagem:</label>
    <textarea id="txaMensagem" name="txaMensagem" maxlength="1000" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'15':'16'?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onselect="infraPosicionarCursor(this);" onclick="infraPosicionarCursor(this);" onkeyup="infraPosicionarCursor(this);"><?=PaginaSEI::tratarHTML($objMdRespostaEnvioDTO->getStrMensagem())?></textarea>
    <input type="hidden" id="hdnFlagEnvio" name="hdnFlagEnvio" value="1" />
  </div>
  <br/>
  <div id="divDocumentosProcesso">
     <?
     PaginaSEI::getInstance()->montarAreaTabela($strResultadoDocumentos,$numDocumentos);
     ?>
  </div>
  <br/>
  
  <!--fieldset id="fldSinConclusiva" class="infraFieldset" style="height:6em">
    	<legend class="infraLegend">&nbsp;<?=MdRespostaEnvioRN::$TX_TITULO?>&nbsp;</legend>
    	<div class="group">
        <div id="divOptDefinitiva" class="infraDivRadio"> 
          <input type="radio" name="rdoSinConclusiva" id="optDefinitiva" onclick="descricaoResposta(this)" value="<?=MdRespostaEnvioRN::$EV_RESPOSTA?>" class="infraRadio"/>
          <span id="spnDefinitiva"><label id="lblDefinitiva" for="optDefinitiva" class="infraLabelRadio" ><?=MdRespostaEnvioRN::$TX_RESPOSTA?></label></span>
        </div>
      
        <div id="divOptParcial" class="infraDivRadio">	  
          <input type="radio" name="rdoSinConclusiva" id="optParcial" onclick="descricaoResposta(this)" value="<?=MdRespostaEnvioRN::$EV_AJUSTE?>" class="infraRadio"/>
          <span id="spnParcial"><label id="lblParcial" for="optParcial" class="infraLabelRadio" ><?=MdRespostaEnvioRN::$TX_AJUSTE?></label></span>
        </div>
      </div>
  	  
      <div id="divDescricaoResposta"></div>
  </fieldset--> 

</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>