<?

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

    case 'md_resposta_enviar':
    	
    	if ($_GET['acao']=='md_resposta_enviar'){
    	  $strTitulo = 'Formulário Módudo de Resposta';  
      }
		  
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
      
      break;
     
    	default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

	$arrComandos[] = '<button type="button" onclick="submeterFormulario();" accesskey="E" name="btnEnviar" value="Enviar" class="infraButton"><span class="infraTeclaAtalho">E</span>nviar</button>';
	$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
  
  $strLinkAjaxTextoPadrao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=texto_padrao_buscar_conteudo');

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

#lblMensagem {position:absolute;left:0%;top:0%;}
#txaMensagem {position:absolute;left:0%;top:8%;width:95%;}

#lblArquivo {position:absolute;left:0%;top:0%;width:95%;}
#filArquivo {position:absolute;left:0%;top:40%;width:95%;}

#divOpcao {position:absolute;left:23%;top:0%;width:30%;}

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
////<script>

function emailTokenizer(input, selection, selectCallback, opts) {
	var original = input, // store the original so we can compare and know if we need to tell the search to update its text
			dupe = false, // check for whether a token we extracted represents a duplicate selected choice
			token, // token
			index, // position at which the separator was found
			i, l, // looping variables
			separator; // the matched separator
	while (true) {
		index = -1;

		for (i = 0, l = opts.tokenSeparators.length; i < l; i++) {
			separator = opts.tokenSeparators[i];
			index = input.indexOf(separator);
			if (index >= 0) {
				var a=input.indexOf('"');
				if (a==-1 || a>index ) break;
				var b=input.indexOf('"',a+1);
				if (b==-1 || b<index)	break;
				index = input.indexOf(separator,b);
			}
		}

		if (index < 0) break; // did not find any token separator in the input string, bail

		token = input.substring(0, index);
		input = input.substring(index + separator.length);

		if (input.length>0 && input.substr(-1,1)!=separator) input=input+separator;

		if (token.length > 0) {
			token = opts.createSearchChoice.call(this, token, selection);
			if (token !== undefined && token !== null && opts.id(token) !== undefined && opts.id(token) !== null) {
				dupe = false;
				for (i = 0, l = selection.length; i < l; i++) {
					if (opts.id(token) === opts.id(selection[i])) {
						dupe = true; break;
					}
				}

				if (!dupe) selectCallback(token);
			}
		}
	}

	if (original!==input) return input;
}

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
//  el.parent('li').remove();
  var hdn=$('#hdnDestinatario');
  var term=hdn.select2('container').find('input').val();
  hdn.select2('close');
  hdn.select2('search',term);

}

function format(result, container, query, escapeMarkup) {
  var markup=[];
  Select2.util.markMatch(result.text, query.term, markup, escapeMarkup);
  return markup.join("")+"<a href='#' class='remover' onmousedown='removeItem(event,\""+container.attr('id')+"\");'>Esquecer</a>";;
}

function autocompletarEmails(input) {
  $(input).select2({
    tags: true,
    formatResult: format,

    minimumInputLength: 1,
    formatInputTooShort: "",
    separator:';',
		tokenizer: emailTokenizer,
    tokenSeparators: [";",","],
    createSearchChoice: function (term, data) {
      if (infraValidarEmail(infraTrim(term))) return { id:infraTrim(term),text:infraTrim(term) };
    },
    initSelection: function (element, callback) {
      var data = [];
      var emails = element.val().split(";");
      $(emails).each(function () {
        data.push({
          id: this.toString(),
          text: this.toString()
        });
      });
      $(element).val('');
      callback(data);
    },
    multiple: true,
    ajax: {
      type:"POST",
      url: "<?=$strLinkEmails;?>",
      dataType: "json",
      data: function (term, page) {
        return {
          palavras_pesquisa: infraTrim(term)
        };
      },
      results: function (data, page) {
        return {
          results: data
        };
      }
    }
  });
}

$(document).ready(function () {
  autocompletarEmails("#hdnDestinatario");

  $("#hdnDestinatario").select2("container").find("ul.select2-choices").sortable({
    containment: "parent",
    start: function () {
      $("#hdnDestinatario").select2("onSortStart");
    },
    update: function () {
      $("#hdnDestinatario").select2("onSortEnd");
    }
  });

});
		    
var objLupaGrupo = null;
var objAjaxTextoPadrao = null;
var objUpload = null;

function inicializar(){
  
  <?if ($bolEnvioOK){ ?>
    self.setTimeout('window.close()',1000);
  <?}?>

  infraEfeitoTabelas();
  
  objAjaxTextoPadrao.processarResultado = function(arr) {
    if (arr != null) {
      infraInserirCursor(document.getElementById('txaMensagem'), arr['Conteudo']);
    }
  };
  objAjaxTextoPadrao.executar();
  
  //Anexos
  objUpload = new infraUpload('frmAnexos','<?=$strLinkAnexos?>');
  objUpload.finalizou = function(arr){
   	objTabelaAnexos.adicionar([arr['nome_upload'],arr['nome'],arr['data_hora'],arr['tamanho'],infraFormatarTamanhoBytes(arr['tamanho'])]);
  }

	if ('<?=$strSinOuvidoriaTipoProcedimento?>' == 'S') {
		document.getElementById('txaMensagem').focus();
	}else {
		if (document.getElementById('selDe').value=='null') {
			document.getElementById('selDe').focus();
		} else {
      if ('<?=$_GET['acao']?>'=='documento_email_circular'){
        document.getElementById('txtAssunto').focus();
      }else{
        $('.select2-input').focus();
      }
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
    alert('Informe a Descrição da Resposta.');
    document.getElementById('txaMensagem').focus();
    return false;
  }

  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum documento selecionado.');
    return;
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
<form id="frmEnviarResposta" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" style="display:inline;">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
?>
  <div id="divAssuntoMensagem" class="infraAreaDados" style="height:30em;">
  
  <label id="lblMensagem" for="txaMensagem" accesskey="" class="infraLabelObrigatorio">Descrição da Resposta:</label> 
  <textarea id="txaMensagem" name="txaMensagem" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'15':'16'?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" onselect="infraPosicionarCursor(this);" onclick="infraPosicionarCursor(this);" onkeyup="infraPosicionarCursor(this);"></textarea>
  <input type="hidden" id="hdnIdDocumentoCircular" name="hdnIdDocumentoCircular" value="<?=$strIdDocumentoCircular?>"/>

  </div>

  <div id="divDocumentosProcesso" style="margin-top:.7em;">
     <?
     PaginaSEI::getInstance()->montarAreaTabela($strResultadoDocumentos,$numDocumentos);
     ?>
  </div>
  
  </br>

  <div id="divRespostaConclusiva" class="infraAreaDados" style="height:5em;">
  
  <label id="lblRespostaConclusiva" accesskey="" class="infraLabelObrigatorio">Resposta é Conclusiva?</label> 
  
  <div id="divOpcao">
    <input type="radio" id="Sim" name="chkRespostaConclusiva" value="S">
    <label for="Sim">Sim</label><br>
    <input type="radio" id="Não" name="chkRespostaConclusiva" value="N">
    <label for="Não">Não</label><br>
  </div>
  </div>  
</form>
<? 
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos,true);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>