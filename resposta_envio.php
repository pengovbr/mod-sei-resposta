<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 15/01/2008 - criado por marcio_db
*
* Versão do Gerador de Código: 1.12.1
*
* Versão no CVS: $Id$
*/

try {
    require_once dirname(__FILE__).'/../../SEI.php';

  session_start();
 
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

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
    $strParametros .= "&id_procedimento=".$_GET['id_procedimento'];
  } 

  if (isset($_GET['id_documento'])){
    $strParametros .= "&id_documento=".$_GET['id_documento'];
  } 

  if (isset($_GET['id_documento_edoc'])){
    $strParametros .= "&id_documento_edoc=".$_GET['id_documento_edoc'];
  } 
  
  $bolEnvioOK = false;
	$strSinOuvidoriaTipoProcedimento = null;

  switch($_GET['acao']){

    case 'md_procedimento_enviar_resposta':
    	
    	if ($_GET['acao']=='md_procedimento_enviar_resposta'){
    	  $strTitulo = 'Enviar Resposta';  
      }
    	
      $objEmailDTO = new EmailDTO();
      $strSinCCO = PaginaSEI::getInstance()->getCheckbox($_POST['chkSinCCO']);
		  $objEmailDTO->setStrMensagem($_POST['txaMensagem']);
			
		  $objEmailDTO->setDblIdProtocolo($_GET['id_procedimento']);
		  
      //Monta tabela de documentos do processo
      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->retNumIdUnidadeGeradoraProtocolo();
		  $objProcedimentoDTO->retStrSinOuvidoriaTipoProcedimento();
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
			$strSinOuvidoriaTipoProcedimento = $objProcedimentoDTO->getStrSinOuvidoriaTipoProcedimento();
			
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
        

      if (isset($_POST['hdnFlagEmail'])){
      	
     	  try{
					$objEmailDTO->setArrArquivosUpload(PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnAnexos']));
					$objEmailDTO->setArrIdDocumentosProcesso(PaginaSEI::getInstance()->getArrStrItensSelecionados());

					$objEmailRN = new EmailRN();
					$objDocumentoDTO = $objEmailRN->enviar($objEmailDTO);
					
					//respostas de formulario usam remetente naoresponder (o erro nao volta para a caixa da unidade)
					if ($_GET['acao']!='responder_formulario'){
					  PaginaSEI::getInstance()->setStrMensagem(PaginaSEI::getInstance()->formatarParametrosJavaScript('E-mail enviado.'."\n\n".'Verifique posteriormente a caixa postal da unidade para certificar-se de que não ocorreram problemas na entrega.'),PaginaSEI::$TIPO_MSG_AVISO);
					}
					
					$strLinkRetorno = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem='.$_GET['acao'].'&id_procedimento='.$_GET['id_procedimento'].'&id_documento='.$objDocumentoDTO->getDblIdDocumento().'&atualizar_arvore=1');
					$bolEnvioOK = true;
					
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
	    }else{
	    	
				if ($_GET['acao']=='responder_formulario') {

          $objProtocoloDTO = new ProtocoloDTO();
          $objProtocoloDTO->retStrSiglaUnidadeGeradora();
          $objProtocoloDTO->retStrSiglaOrgaoUnidadeGeradora();
          $objProtocoloDTO->retStrConteudoDocumento();
          $objProtocoloDTO->retDblIdProcedimentoDocumento();
          $objProtocoloDTO->retNumIdSerieDocumento();
          $objProtocoloDTO->retStrStaDocumentoDocumento();
          $objProtocoloDTO->setDblIdProtocolo($_GET['id_documento']);

          $objProtocoloRN = new ProtocoloRN();
          $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

          if ($objProtocoloDTO == null) {
            throw new InfraException('Formulário não encontrado.');
          }

          $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
          $arrParametros = $objInfraParametro->listarValores(array('SEI_ACESSO_FORMULARIO_OUVIDORIA', 'ID_SERIE_OUVIDORIA'));
          $bolAcessoRestritoOuvidoria = ($arrParametros['SEI_ACESSO_FORMULARIO_OUVIDORIA'] == '1');
          $numIdSerieOuvidoria = $arrParametros['ID_SERIE_OUVIDORIA'];

          if ($objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO &&
              $objProtocoloDTO->getNumIdSerieDocumento() == $numIdSerieOuvidoria){

            $objOrgaoDTO = new OrgaoDTO();
            $objOrgaoDTO->setStrSigla($objProtocoloDTO->getStrSiglaOrgaoUnidadeGeradora());
          }

          if ($bolAcessoRestritoOuvidoria &&
              $objProtocoloDTO->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO &&
              $objProtocoloDTO->getNumIdSerieDocumento() == $numIdSerieOuvidoria){

            $objEmailDTO->setStrMensagem('');

          }else {

            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->retDthAbertura();
            $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProcedimentoDocumento());
            $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_GERACAO_PROCEDIMENTO);

            $objAtividadeRN = new AtividadeRN();
            $objAtividadeDTO = $objAtividadeRN->consultarRN0033($objAtividadeDTO);

            $strConteudo = '';
            $strConteudo .= 'Formulário enviado em ' . $objAtividadeDTO->getDthAbertura() . '.' . "\n";
            $strConteudo .= DocumentoINT::formatarExibicaoConteudo(DocumentoINT::$TV_TEXTO, $objProtocoloDTO->getStrConteudoDocumento());

            $arrConteudo = explode("\n", $strConteudo);
            $strConteudo = '';
            foreach ($arrConteudo as $linha) {
              $strConteudo .= '>  ' . $linha . "\n";
            }
            $objEmailDTO->setStrMensagem("\n\n\n" . $strConteudo);
          }
				}else if ($_GET['acao']=='email_encaminhar'){
				  
      	  $objDocumentoDTO = new DocumentoDTO();
      	  $objDocumentoDTO->retStrConteudo();
      	  $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
      	  
      	  $objDocumentoRN = new DocumentoRN();
      	  $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

      	  if ($objDocumentoDTO==null){
      	    throw new InfraException('Documento não encontrado.');
      	  }
      	   
      	  $strConteudo = $objDocumentoDTO->getStrConteudo();
      	  
      		if (!InfraString::isBolVazia($strConteudo) && substr($strConteudo,0,5) == '<?xml'){
      
      			$objXml = new DomDocument('1.0','iso-8859-1');
      
      			$objXml->loadXML($strConteudo);
      
      			$arrAtributos = $objXml->getElementsByTagName('atributo');

      			foreach($arrAtributos as $atributo){
      				if ($atributo->getAttribute('nome') == 'Mensagem'){
      					 $objEmailDTO->setStrMensagem(DocumentoINT::formatarTagConteudo(DocumentoINT::$TV_TEXTO,$atributo->nodeValue));
      					 break;
      				}
      			}

      			$objAnexoRN = new AnexoRN();
      			$arrAnexos = array();
      			foreach($arrAtributos as $atributo){
      				if ($atributo->getAttribute('nome') == 'Anexos'){
      				  $arrAnexosEncaminhar = $atributo->getElementsByTagName('valor');
      				  foreach($arrAnexosEncaminhar as $objAnexoEncaminhar){
      				    foreach($objAnexoEncaminhar->attributes as $attr) {
      				      if ($attr->nodeName == 'id'){

      				        $strNomeArquivo = DocumentoINT::formatarTagConteudo(DocumentoINT::$TV_TEXTO,trim($objAnexoEncaminhar->nodeValue));
											$strNomeUpload = $objAnexoRN->gerarNomeArquivoTemporario();

      				        $objAnexoDTO = new AnexoDTO();
      				        $objAnexoDTO->retNumIdAnexo();      				        
      				        $objAnexoDTO->retDthInclusao();
      				        $objAnexoDTO->setNumIdAnexo($attr->nodeValue);
      				        $objAnexoDTO = $objAnexoRN->consultarRN0736($objAnexoDTO);      				        
              				
              				copy($objAnexoRN->obterLocalizacao($objAnexoDTO), DIR_SEI_TEMP.'/'.$strNomeUpload);
              				
              				$numTamanhoAnexo = filesize(DIR_SEI_TEMP.'/'.$strNomeUpload);
              				
              				$arrAnexos[] = array($strNomeUpload, PaginaSEI::tratarHTML($strNomeArquivo), date('d/m/Y H:i:s',time()), $numTamanhoAnexo, InfraUtil::formatarTamanhoBytes($numTamanhoAnexo));
      				      }
      				    }
      				  }
      				  $_POST['hdnAnexos'] = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrAnexos);
      				  break;
      				}
      			}
      		}
				}
	    }
      
      break;
     
    	default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

	$arrComandos[] = '<button type="button" onclick="submeterFormulario();" accesskey="E" name="btnEnviar" value="Enviar" class="infraButton"><span class="infraTeclaAtalho">E</span>nviar</button>';
	$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
  
  $strLinkAjaxTextoPadrao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=texto_padrao_buscar_conteudo');
  
  $strItensSelTextoPadrao = TextoPadraoInternoINT::montarSelectSigla('null','&nbsp;',$_POST['selTextoPadrao']);

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

#hAlterar{margin-left: 30px;}
#spLocalizadorPrincipal{margin-left: 30px;}
#resultado_localizadores{font-size: 1.1em;}	

#lblMensagem {position:absolute;left:0%;top:13%;}
#selTextoPadrao {position:absolute;left:0%;top:18%;width:95.5%;}
#txaMensagem {position:absolute;left:0%;top:24%;width:95%;}

#divDocumentosProcesso {<?=$strDisplayDocumentosProcesso?>}

#lblArquivo {position:absolute;left:0%;top:0%;width:95%;<?=$strDisplayAnexos?>}
#filArquivo {position:absolute;left:0%;top:40%;width:95%;<?=$strDisplayAnexos?>}

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


function removeItem(event,divId){
	event=event||window.event;
  event.stopPropagation();
  event.preventDefault();

  var el=$('#'+divId);
  var html=el.html();
  html=html.substring(0,html.indexOf('<a '));
  html=html.replace(/<span[^>]*>(.*)<\/span>/,'$1');
  $.ajax({
    type:"POST",
    url: "<?=$strLinkRemoverEmail;?>",
    dataType: "xml",
    data: "email="+encodeURIComponent(html)
  });

  var term=hdn.select2('container').find('input').val();
  hdn.select2('close');
  hdn.select2('search',term);

}

function format(result, container, query, escapeMarkup) {
  var markup=[];
  Select2.util.markMatch(result.text, query.term, markup, escapeMarkup);
  return markup.join("")+"<a href='#' class='remover' onmousedown='removeItem(event,\""+container.attr('id')+"\");'>Esquecer</a>";;
}
		    
var objLupaGrupo = null;
var objAjaxTextoPadrao = null;
var objUpload = null;

function inicializar(){
  
  <?if ($bolEnvioOK){ ?>
    self.setTimeout('window.close()',1000);
  <?}?>

  infraEfeitoTabelas();
    
  objLupaGrupo.finalizarSelecao = function(){
    var arrEmail=[];
    $('#selPara option').each(function(){
      var email=$(this).val();
      if (email!="") arrEmail.push(email);
    });
    $('#hdnDestinatario').val(arrEmail.join(';'));
    autocompletarEmails("#hdnDestinatario");
  };
  
  objAjaxTextoPadrao = new infraAjaxComplementar('selTextoPadrao','<?=$strLinkAjaxTextoPadrao?>');
  objAjaxTextoPadrao.prepararExecucao = function(){
    return 'id_texto_padrao_interno='+document.getElementById('selTextoPadrao').value;
  };
  objAjaxTextoPadrao.processarResultado = function(arr) {
    if (arr != null) {
      infraInserirCursor(document.getElementById('txaMensagem'), arr['Conteudo']);
    }
  };
  objAjaxTextoPadrao.executar();
  
  //Anexos
	if ('<?=$strSinOuvidoriaTipoProcedimento?>' == 'S') {
		document.getElementById('txaMensagem').focus();
	}else {
        $('.select2-input').focus();
		}
	}

  if (INFRA_IE == 0){
     window.scrollTo(0,0);  
  }else{
     self.setTimeout('window.scrollTo(0,0);',100);  
  }
}

function validarEnvio() {

  if (infraTrim(document.getElementById('txaMensagem').value)=='') {
    alert('Informe a Mensagem.');
    document.getElementById('txaMensagem').focus();
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
	    
    document.getElementById('frmEmail').submit();
  }
}

function finalizar(){
  <?if ($bolEnvioOK){ ?>
     <? if ($_GET['arvore'] == '1'){ ?>
       if (window.opener!=null){

				 <?if ($_GET['acao']=='documento_email_circular'){?>
				   window.opener.parent.document.getElementById('ifrArvore').src = '<?=$strLinkRetorno?>';
				 <?}else{?>
				   window.opener.location = '<?=$strLinkRetorno?>';
				 <?}?>

       }
     <?}?>  
  <?}?>
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();" onunload="finalizar();"');
?>
<form id="frmEmail" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" style="display:inline;">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
?>
  <div id="divAssuntoMensagem" class="infraAreaDados" style="height:35em;">
  
  <label id="lblMensagem" for="txaMensagem" accesskey="" class="infraLabelObrigatorio">Mensagem:</label>
  <select id="selTextoPadrao" name="selTextoPadrao" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"  >
  <?=$strItensSelTextoPadrao?>
  </select>    
  <textarea id="txaMensagem" name="txaMensagem" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'15':'16'?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onselect="infraPosicionarCursor(this);" onclick="infraPosicionarCursor(this);" onkeyup="infraPosicionarCursor(this);"><?=PaginaSEI::tratarHTML($objEmailDTO->getStrMensagem())?></textarea>
  <input type="hidden" id="hdnIdDocumentoCircular" name="hdnIdDocumentoCircular" value="<?=$strIdDocumentoCircular?>"/>

  </div>

  <div id="divDocumentosProcesso" style="margin-top:.7em;">
     <?
     PaginaSEI::getInstance()->montarAreaTabela($strResultadoDocumentos,$numDocumentos);
     ?>
  </div>
</form>
<? 
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos,true);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>