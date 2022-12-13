<?
/**
* MINIST�RIO DA ECONOMIA
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
      $strTitulo = 'Configura��o do M�dulo de Respostas';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmSalvar'])) {
        try{
          foreach ($_POST as $campo => $valor) { 
           
            switch($campo) {
              case 'selTipoDocumento':
                $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
                $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_TIPO_DOCUMENTO);
                $objMdRespostaParametroDTO->setStrValor($valor);
                $arrObjMdRespostaParametroDTO[] = $objMdRespostaParametroDTO;
                  break;
              case 'selSistema':
                  $objMdRespostaParametroDTO = new MdRespostaParametroDTO();
                  $objMdRespostaParametroDTO->setStrNome(MDRespostaParametroRN::PARAM_SISTEMA);
                  $objMdRespostaParametroDTO->setStrValor(serialize($_POST['selSistema']));
                  $arrObjMdRespostaParametroDTO[] = $objMdRespostaParametroDTO;
                  break;              
            }

          }

          $objMdRespostaParametroDTO = $objMdRespostaParametroRN->atribuir($arrObjMdRespostaParametroDTO);

          if ($_GET['acao']!='responder_formulario'){
            PaginaSEI::getInstance()->setStrMensagem(PaginaSEI::getInstance()->formatarParametrosJavaScript('Mapeamento cadastrado com sucesso.'), PaginaSEI::$TIPO_MSG_AVISO);
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
        throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }

  $strParametroSistema = array();
  $strParametroTipoDoc = null;

  foreach($arrObjMdRespostaParametroDTO as $objMdRespostaDTO){

    switch($objMdRespostaDTO->getStrNome()) {

      case 'PARAM_SISTEMA':
        $arrSistema = unserialize($objMdRespostaDTO->getStrValor());
        foreach($arrSistema as $valor){
          $strParametroSistema[] = $valor;
        }
          break;
      case 'PARAM_TIPO_DOCUMENTO':
        $strParametroTipoDoc = $objMdRespostaDTO->getStrValor();
          break;
    }

  }

  $strItensSelSistema = UsuarioINT::montarSelectSiglaSistema('null', '&nbsp;', $strParametroSistema);
  
  $objSerieDTO = new SerieDTO();
  $objSerieDTO->retNumIdSerie();
  $objSerieDTO->retStrNome();
  
  // Consulta nas classes de regra de neg�cio
  $objSerieRN = new SerieRN();
  $arrObjSerieDTO = $objSerieRN->listarRN0646($objSerieDTO);
  
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

#lblTipoDocumento {position:absolute;left:0%;top:35%;width:50%;}
#selTipoDocumento {position:absolute;left:0%;top:42%;width:50%;}

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
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmRespostaCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.htmlspecialchars($_GET['acao'], ENT_QUOTES, 'ISO-8859-1').'&acao_origem='.htmlspecialchars($_GET['acao'], ENT_QUOTES, 'ISO-8859-1'))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblSistema" for="selSistema" accesskey="s" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>istema:</label>
  <select id="selSistema" name="selSistema[]" multiple="multiple" onkeypress="return infraMascaraNumero(this, event);" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelSistema?>
  </select>
  
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
