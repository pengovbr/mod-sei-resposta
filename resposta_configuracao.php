<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
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
  $objMdRespostaParametroSistemaDTO = null;
  
  $arrComandos = array();

  switch($_GET['acao']){

    case 'md_resposta_configuracao':
      $strTitulo = 'Configuração do Módulo de Respostas';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmSalvar'])) {
        try{          
          $strParametroSistema = $_POST['selSistema'];
          $objMdRespostaParametroSistemaDTO = $objMdRespostaParametroRN->atribuir(MDRespostaParametroRN::PARAM_SISTEMA, $strParametroSistema);
          PaginaSEI::getInstance()->setStrMensagem('Mapeamento cadastrado com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      } else {
        $objMdRespostaParametroSistemaDTO = $objMdRespostaParametroRN->consultar(MDRespostaParametroRN::PARAM_SISTEMA);
      }

      break;
  	
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $strParametroSistema = !is_null($objMdRespostaParametroSistemaDTO) ? $objMdRespostaParametroSistemaDTO->getStrValor() : null;  
  $strItensSelSistema = UsuarioINT::montarSelectSiglaSistema('null','&nbsp;', $strParametroSistema);
  
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
  <label id="lblSistema" for="selSistema" accesskey="g" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>istema:</label>
  <select id="selSistema" name="selSistema" onkeypress="return infraMascaraNumero(this, event);" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelSistema?>
  </select>
  
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>