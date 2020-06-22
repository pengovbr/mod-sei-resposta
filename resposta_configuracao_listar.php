<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 14/04/2008 - criado por mga
*
* Versão do Gerador de Código: 1.14.0
*
* Versão no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('resposta_configuracao_listar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){
    case 'md_resposta_configuracao_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objMdRespostaConfiguracaoDTO = new MdRespostaConfiguracaoDTO();
          $objMdRespostaConfiguracaoDTO->setNumIdUsuario($arrStrIds[$i]);
        }

        $objMdRespostaConfiguracaoRN = new MdRespostaConfiguracaoRN();
        $objMdRespostaConfiguracaoRN->desativar($objMdRespostaConfiguracaoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_resposta_configuracao_reativar':
      $strTitulo = 'Reativar Mapeamento de Sistemas';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objMdRespostaConfiguracaoDTO = new MdRespostaConfiguracaoDTO();
            $objMdRespostaConfiguracaoDTO->setNumIdUsuario($arrStrIds[$i]);
          }

          $objMdRespostaConfiguracaoRN = new MdRespostaConfiguracaoRN();
          $objMdRespostaConfiguracaoRN->reativar($objMdRespostaConfiguracaoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

    case 'md_resposta_configuracao_listar':
      $strTitulo = 'Listar Mapeamento de Sistemas';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  
  if ($_GET['acao'] == 'md_resposta_configuracao_listar'){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_resposta_configuracao_cadastrar');
    if ($bolAcaoCadastrar){    	
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_resposta_configuracao_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }
  

  $objMdRespostaConfiguracaoDTO = new MdRespostaConfiguracaoDTO();
  $objMdRespostaConfiguracaoDTO->retNumIdUsuario();
  $objMdRespostaConfiguracaoDTO->retStrSigla();
  $objMdRespostaConfiguracaoDTO->retStrNome();

  if ($_GET['acao'] == 'md_resposta_configuracao_reativar'){
    //Lista somente inativos
    $objMdRespostaConfiguracaoDTO->setBolExclusaoLogica(false);
    $objMdRespostaConfiguracaoDTO->setStrSinAtivo('N');
  }
  
  //$objMdRespostaConfiguracaoDTO->setStrStaTipo(MdRespostaConfiguracaoRN::$TU_SISTEMA);
  
  PaginaSEI::getInstance()->prepararOrdenacao($objMdRespostaConfiguracaoDTO, 'Sigla', InfraDTO::$TIPO_ORDENACAO_ASC);
  
  PaginaSEI::getInstance()->prepararPaginacao($objMdRespostaConfiguracaoDTO);

  $objMdRespostaConfiguracaoRN = new MdRespostaConfiguracaoRN();
  $arrObjMdRespostaConfiguracaoDTO = $objMdRespostaConfiguracaoRN->listar($objMdRespostaConfiguracaoDTO);

  PaginaSEI::getInstance()->processarPaginacao($objMdRespostaConfiguracaoDTO);
  
  /*$objServicoDTO = new ServicoDTO();
  $objServicoDTO->setDistinct(true);
  $objServicoDTO->retNumIdUsuario();
  
  $objServicoRN = new ServicoRN();
  $arrUsuariosServicos = InfraArray::indexarArrInfraDTO($objServicoRN->listar($objServicoDTO),'IdUsuario');
  */
  $numRegistros = count($arrObjMdRespostaConfiguracaoDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='md_resposta_configuracao_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_resposta_configuracao_reativar');
      $bolAcaoDesativar = false;
      //$bolAcaoServicoListar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_resposta_configuracao_desativar');
      //$bolAcaoServicoListar = SessaoSEI::getInstance()->verificarPermissao('servico_listar');
    }

    if ($bolAcaoDesativar){
      $bolCheck = true;
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_resposta_configuracao_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_resposta_configuracao_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }

    $strResultado = '';

    if ($_GET['acao']!='md_resposta_configuracao_reativar'){
      $strSumarioTabela = 'Reativar Mapeamento de Sistemas';
      $strCaptionTabela = 'Mapeamento de Sistemas';
    }else{
      $strSumarioTabela = 'Tabela de Mapeamento de Sistemas Inativos.';
      $strCaptionTabela = 'Mapeamento de Sistemas Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }    
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objMdRespostaConfiguracaoDTO,'ID','IdUsuario',$arrObjMdRespostaConfiguracaoDTO,true).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objMdRespostaConfiguracaoDTO,'Sigla','Sigla',$arrObjMdRespostaConfiguracaoDTO,true).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objMdRespostaConfiguracaoDTO,'Nome','Nome',$arrObjMdRespostaConfiguracaoDTO,true).'</th>'."\n";    
    
    $strResultado .= '<th class="infraTh">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario(),$arrObjMdRespostaConfiguracaoDTO[$i]->getStrSigla()).'</td>';
      }      
      $strResultado .= '<td align="center" width="10%">'.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario().'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjMdRespostaConfiguracaoDTO[$i]->getStrSigla()).'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjMdRespostaConfiguracaoDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td align="center" width="15%">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario());
      
      
      /*if ($bolAcaoServicoListar){
      	if (isset($arrUsuariosServicos[$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario()])){ 
      	  $strIconeServicos = 'sei_servicos.gif';	
      	}else{
      		$strIconeServicos = 'sei_servicos_vazio.gif';
      	}
      	
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=servico_listar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_usuario='.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensLocal().'/'.$strIconeServicos.'" title="Serviços" alt="Serviços" class="infraImg" /></a>&nbsp;';
      }*/

      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario().'"  onclick="acaoDesativar(\''.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario().'\',\''.$arrObjMdRespostaConfiguracaoDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Sistema" alt="Desativar Sistema" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario().'"  onclick="acaoReativar(\''.$arrObjMdRespostaConfiguracaoDTO[$i]->getNumIdUsuario().'\',\''.$arrObjMdRespostaConfiguracaoDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Sistema" alt="Reativar Sistema" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
    
  //$strItensSelOrgao = OrgaoINT::montarSelectSiglaRI1358('','Todos',$numIdOrgao);

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
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='md_resposta_configuracao_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do sistema \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRespostaConfiguracaoLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmRespostaConfiguracaoLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do sistema \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmRespostaConfiguracaoLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmRespostaConfiguracaoLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmRespostaConfiguracaoLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
  <?
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>