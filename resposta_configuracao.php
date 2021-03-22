<?
/**
* MINISTÉRIO DA ECONOMIA
*
* 17/06/2020 - criado por Higo Cavalcante
*
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';
  
  session_start();

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  $objMdRespostaParametroRN = new MdRespostaParametroRN();

  $arrComandos = array();

  switch($_GET['acao']){

    case 'md_resposta_configuracao':
      $strTitulo = 'Configuração do Módulo de Respostas';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmSalvar'])) {
        try{
          foreach ($_POST as $campo => $valor) { 
           
            switch($campo) {
              case 'selTipoDocumento':
              case 'selSistema':
                $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
                if($campo == 'selSistema'){
                  $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_SISTEMA);
                }elseif($campo == 'selTipoDocumento'){
                  $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_TIPO_DOCUMENTO);
                }
                $objMdRespostaParametroDTO->setStrValor($valor);
                $arrObjMdRespostaParametroDTO[] = $objMdRespostaParametroDTO;
              break;
              case 'selTipoProcesso':
                  $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
                  $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_TIPO_PROCESSO);
                  $objMdRespostaParametroDTO->setStrValor(serialize($_POST['selTipoProcesso']));
                  $arrObjMdRespostaParametroDTO[] = $objMdRespostaParametroDTO;
              break;              
            }

          }

          $objMdRespostaParametroDTO = $objMdRespostaParametroRN->atribuir($arrObjMdRespostaParametroDTO);

          if ($_GET['acao']!='responder_formulario'){
            PaginaSEI::getInstance()->setStrMensagem(PaginaSEI::getInstance()->formatarParametrosJavaScript('Mapeamento cadastrado com sucesso.'),PaginaSEI::$TIPO_MSG_AVISO);
          }

        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }

      $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
      $objMdRespostaParametroDTO->retStrNome();
      $objMdRespostaParametroDTO->retStrValor();

      $objMdRespostaParametroRN = new MdRespostaParametroRN();
      $arrObjMdRespostaParametroDTO = $objMdRespostaParametroRN->listar($objMdRespostaParametroDTO);

      break;
  	
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strParametroSistema = null;
  $strParametroTipoDoc = null;
  $strParametroProcesso = array();

  foreach($arrObjMdRespostaParametroDTO as $objMdRespostaDTO){

    switch($objMdRespostaDTO->getStrNome()) {

      case 'PARAM_SISTEMA':
        $strParametroSistema = $objMdRespostaDTO->getStrValor();
      break;
      case 'PARAM_TIPO_PROCESSO':
        $arrTipoProcesso = unserialize($objMdRespostaDTO->getStrValor());
        foreach($arrTipoProcesso as $valor){
          $strParametroProcesso[] = $valor;
        }
      break;
      case 'PARAM_TIPO_DOCUMENTO':
        $strParametroTipoDoc = $objMdRespostaDTO->getStrValor();
      break;
    }

  }

  $strItensSelSistema = UsuarioINT::montarSelectSiglaSistema('null','&nbsp;', $strParametroSistema);
  
  $objSerieDTO = new SerieDTO();
  $objSerieDTO->retNumIdSerie();
  $objSerieDTO->retStrNome();
  
  // Consulta nas classes de regra de negócio
  $objSerieRN = new SerieRN();
  $arrObjSerieDTO = $objSerieRN->listarRN0646($objSerieDTO);


  $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
  $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
  $objTipoProcedimentoDTO->retStrNome();
  $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

  $objTipoProcedimentoRN = new TipoProcedimentoRN();
  $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);
  
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

#lblSistema {position:absolute;left:0%;top:0%;width:50%;}
#selSistema {position:absolute;left:0%;top:6%;width:50%;}

#lblTipoProcesso {position:absolute;left:0%;top:16%;width:50%;}
#selTipoProcesso {position:absolute;left:0%;top:22%;width:50%;}

#lblTipoDocumento {position:absolute;left:0%;top:50%;width:50%;}
#selTipoDocumento {position:absolute;left:0%;top:55%;width:50%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


function inicializar(){
  document.getElementById('selSistema').focus();  
  infraEfeitoTabelas();
}

function OnSubmitForm() {
  return validarFormParametrosCadastro();
}

function validarFormParametrosCadastro() {

  if (!infraSelectSelecionado('selSistema')) {
    alert('Selecione o Sistema.');
    document.getElementById('selSistema').focus();
    return false;
  }

  if (!infraSelectSelecionado('selTipoProcesso')) {
    alert('Selecione o Tipo de Processo.');
    document.getElementById('selTipoProcesso').focus();
    return false;
  }

  if (!infraSelectSelecionado('selTipoDocumento')) {
    alert('Selecione o Tipo de Documento.');
    document.getElementById('selTipoDocumento').focus();
    return false;
  }

  return true;
}
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmRespostaCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblSistema" for="selSistema" accesskey="s" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>istema:</label>
  <select id="selSistema" name="selSistema" onkeypress="return infraMascaraNumero(this, event);" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelSistema?>
  </select>

  <label id="lblTipoProcesso" for="selTipoProcesso" accesskey="p" class="infraLabelObrigatorio">Tipo de <span class="infraTeclaAtalho">P</span>rocesso:</label>
  <?
  echo '<select id="selTipoProcesso" name="selTipoProcesso[]" multiple="multiple" size="5" onkeypress="return infraMascaraNumero(this, event);" class="infraSelect" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'">';
  echo InfraINT::montarSelectArrInfraDTO('null', '', $strParametroProcesso, $arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
  echo '<select>';  
  ?>
  
  <label id="lblTipoDocumento" for="selTipoDocumento" accesskey="t" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">T</span>ipo Documento:</label>
  <?
  echo '<select id="selTipoDocumento" name="selTipoDocumento" onkeypress="return infraMascaraNumero(this, event);" class="infraSelect" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'">';
  echo InfraINT::montarSelectArrInfraDTO('null', '&nbsp;', $strParametroTipoDoc, $arrObjSerieDTO, 'IdSerie', 'Nome');
  echo '<select>';  

  PaginaSEI::getInstance()->fecharAreaDados();
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>