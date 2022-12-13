<?php

require_once dirname(__FILE__) . '/../../web/Sip.php';

class VersaoSipRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_RESPOSTA';
    const NOME_MODULO = 'M�dulo de Respsota';

    private $arrRecurso = array();
    private $arrMenu = array();

  public function __construct()
    {
      parent::__construct();
  }

  protected function inicializarObjInfraIBanco()
    {
      return BancoSip::getInstance();
  }

  protected function verificarVersaoInstaladaControlado()
    {
      $objInfraParametroDTO = new InfraParametroDTO();
      $objInfraParametroDTO->setStrNome(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
      $objInfraParametroDB = new InfraParametroBD(BancoSip::getInstance());
    if ($objInfraParametroDB->contar($objInfraParametroDTO) == 0) {
        $objInfraParametroDTO->setStrValor('0.0.0');
        $objInfraParametroDB->cadastrar($objInfraParametroDTO);
    }
  }

    //TODO: Necess�rio refatorar m�todo abaixo devido a baixa qualidade da codifica��o
  public function addRecursosToPerfil($numIdPerfil, $numIdSistema) {

    if (!empty($this->arrRecurso)) {

        $objDTO = new RelPerfilRecursoDTO();
        $objBD = new RelPerfilRecursoBD(BancoSip::getInstance());

      foreach ($this->arrRecurso as $numIdRecurso) {

        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setNumIdPerfil($numIdPerfil);
        $objDTO->setNumIdRecurso($numIdRecurso);

        if ($objBD->contar($objDTO) == 0) {
            $objBD->cadastrar($objDTO);
        }
      }
    }
  }

    //TODO: Necess�rio refatorar m�todo abaixo devido a baixa qualidade da codifica��o
  public function addMenusToPerfil($numIdPerfil, $numIdSistema) {

    if (!empty($this->arrMenu)) {

        $objDTO = new RelPerfilItemMenuDTO();
        $objBD = new RelPerfilItemMenuBD(BancoSip::getInstance());

      foreach ($this->arrMenu as $array) {

        list($numIdItemMenu, $numIdMenu, $numIdRecurso) = $array;

        $objDTO->setNumIdPerfil($numIdPerfil);
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setNumIdRecurso($numIdRecurso);
        $objDTO->setNumIdMenu($numIdMenu);
        $objDTO->setNumIdItemMenu($numIdItemMenu);

        if ($objBD->contar($objDTO) == 0) {
            $objBD->cadastrar($objDTO);
        }
      }
    }
  }

  public function atribuirPerfil($numIdSistema) {
      $objDTO = new PerfilDTO();
      $objBD = new PerfilBD(BancoSip::getInstance());
      $objRN = $this;

      // Vincula a um perfil os recursos e menus adicionados
      $fnCadastrar = function($strNome, $numIdSistema) use($objDTO, $objBD, $objRN) {

          $objDTO->unSetTodos();
          $objDTO->setNumIdSistema($numIdSistema);
          $objDTO->setStrNome($strNome, InfraDTO::$OPER_LIKE);
          $objDTO->setNumMaxRegistrosRetorno(1);
          $objDTO->retNumIdPerfil();

          $objPerfilDTO = $objBD->consultar($objDTO);

        if (!empty($objPerfilDTO)) {
            $objRN->addRecursosToPerfil($objPerfilDTO->getNumIdPerfil(), $numIdSistema);
            $objRN->addMenusToPerfil($objPerfilDTO->getNumIdPerfil(), $numIdSistema);
        }
      };

      $fnCadastrar('ADMINISTRADOR', $numIdSistema);
  }
  
  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_0_0_0($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_0_0($strVersaoAtual)
    {
      session_start();

      SessaoSip::getInstance(false);

      $id_sistema = '';
      $id_perfil = '';
      $id_menu = '';
      $id_item_menu_pai = '';

      //Consulta do Sistema
      $sistemaDTO = new SistemaDTO();
      $sistemaDTO->setStrSigla('SEI');
      $sistemaDTO->setNumRegistrosPaginaAtual(1);
      $sistemaDTO->retNumIdSistema();

      $sistemaRN = new SistemaRN();
      $sistemaDTO = $sistemaRN->consultar($sistemaDTO);

    if (!empty($sistemaDTO)) {
        $id_sistema = $sistemaDTO->getNumIdSistema();
    }

      //Consulta do Menu
      $menuDTO = new MenuDTO();
      $menuDTO->setNumIdSistema($id_sistema);
      $menuDTO->setNumRegistrosPaginaAtual(1);
      $menuDTO->retNumIdMenu();

      $menuRN = new MenuRN();
      $menuDTO = $menuRN->consultar($menuDTO);

    if (!empty($menuDTO)) {
        $id_menu = $menuDTO->getNumIdMenu();
    }

      //Consulta do Perfil
      $perfilDTO = new PerfilDTO();
      $perfilDTO->setStrNome('%Administrador%', InfraDTO::$OPER_LIKE);
      $perfilDTO->setNumIdSistema($id_sistema);
      $perfilDTO->setNumRegistrosPaginaAtual(1);
      $perfilDTO->retNumIdPerfil();

      $perfilRN = new PerfilRN();
      $perfilDTO = $perfilRN->consultar($perfilDTO);

    if (!empty($perfilDTO)) {
        $id_perfil = $perfilDTO->getNumIdPerfil();
    }

      //Consulta do Item de menu pai
      $itemMenuDTO = new ItemMenuDTO();
      $itemMenuDTO->setStrRotulo('Administra��o', InfraDTO::$OPER_LIKE);
      $itemMenuDTO->setNumIdSistema($id_sistema);
      $itemMenuDTO->setNumRegistrosPaginaAtual(1);
      $itemMenuDTO->retNumIdItemMenu();

      $itemMenuRN = new ItemMenuRN();
      $itemMenuDTO = $itemMenuRN->consultar($itemMenuDTO);

    if (!empty($itemMenuDTO)) {
        $id_item_menu_pai = $itemMenuDTO->getNumIdItemMenu();
    }

      //Cria fun��o gen�rica de cadastro de recursos
      $fnCadastrarRecurso = function ($id_sistema, $nome, $descricao, $caminho, $ativo) {
          $recursoDTO = new RecursoDTO();
          $recursoDTO->setNumIdSistema($id_sistema);
          $recursoDTO->setStrNome($nome);
          $recursoDTO->setStrDescricao($descricao);
          $recursoDTO->setStrCaminho($caminho);
          $recursoDTO->setStrSinAtivo($ativo);

          $recurtoRN = new RecursoRN();
          $recursoDTO = $recurtoRN->cadastrar($recursoDTO);

          return $recursoDTO->getNumIdRecurso();
      };

      // Cria a fun��o gen�ria para o cadastramento de menus
      $fnItemMenu = function ($id_menu, $id_item_menu_pai, $id_sistema, $id_recurso_listar, $rotulo, $nova_janela, $ativo, $sequencia) {
          $itemMenuNovoDTO = new ItemMenuDTO();
          $itemMenuNovoDTO->setNumIdMenuPai($id_menu);
          $itemMenuNovoDTO->setNumIdItemMenuPai($id_item_menu_pai);
          $itemMenuNovoDTO->setNumIdSistema($id_sistema);
          $itemMenuNovoDTO->setNumIdRecurso($id_recurso_listar);
          $itemMenuNovoDTO->setStrRotulo($rotulo);
          $itemMenuNovoDTO->setStrIcone(null);
          $itemMenuNovoDTO->setStrDescricao(null);
          $itemMenuNovoDTO->setStrSinNovaJanela($nova_janela);
          $itemMenuNovoDTO->setStrSinAtivo($ativo);
          $itemMenuNovoDTO->setNumSequencia($sequencia);
          $itemMenuNovoDTO->setNumIdMenu($id_menu);

          $itemMenuNovoRN = new ItemMenuRN();
          $itemMenuNovoDTO = $itemMenuNovoRN->cadastrar($itemMenuNovoDTO);
          return $itemMenuNovoDTO->getNumIdItemMenu();
      };


      // Recurso de acesso ao m�dulo
      $id_recurso_parametro_enviar = $fnCadastrarRecurso($id_sistema, 'md_resposta_enviar', 'Enviar resposta ao portal do gor.br', 'controlador.php?acao=md_resposta_enviar', 'S');
      $this->arrRecurso[] = $id_recurso_parametro_enviar;

      //Recurso de acesso as configura��es
      $id_recurso_parametro_configuracao = $fnCadastrarRecurso($id_sistema, 'md_resposta_configuracao', 'Configura��o dos Parametros Gerais do M�dulo', 'controlador.php?acao=md_resposta_configuracao', 'S');
      $this->arrRecurso[] = $id_recurso_parametro_configuracao;

      ###########################################################ITENS DE MENU#############################################################################################
      // Cria o item de menu M�dulo de Resposta - Gov.br
      $id_menu_resposta_configuracao = $fnItemMenu($id_menu, $id_item_menu_pai, $id_sistema, null, 'M�dulo de Resposta - Gov.br', 'N', 'S', 10);

      //Cria o item de menu de edi��o de par�metros
      $id_menu_parametros = $fnItemMenu($id_menu, $id_menu_resposta_configuracao, $id_sistema, $id_recurso_parametro_configuracao, 'Par�metros de Configura��o', 'N', 'S', 1);
      $this->arrMenu[] = array($id_menu_parametros, $id_menu, $id_recurso_parametro_configuracao);

      //Atribui as permiss�es aos recursos e menus
      $this->atribuirPerfil($id_sistema);
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_0_1($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_1_0($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_1_1($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_1_2($strVersaoAtual)
    {
  }
  
  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_2_0($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_2_1($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_2_2($strVersaoAtual)
    {
  }
}

try {
    session_start();

    SessaoSip::getInstance(false);
    BancoSip::getInstance()->setBolScript(true);

    $objVersaoSipRN = new VersaoSipRN();
    $objVersaoSipRN->verificarVersaoInstalada();
    $objVersaoSipRN->setStrNome(VersaoSipRN::NOME_MODULO);
    $objVersaoSipRN->setStrParametroVersao(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSipRN->setArrVersoes(
        array(
            '0.0.0' => 'versao_0_0_0',
            '1.0.0' => 'versao_1_0_0',
            '1.0.1' => 'versao_1_0_1',
            '1.1.0' => 'versao_1_1_0',
            '1.1.1' => 'versao_1_1_1',
            '1.1.2' => 'versao_1_1_2',
            '1.2.0' => 'versao_1_2_0',
            '1.2.1' => 'versao_1_2_1',
            '1.2.2' => 'versao_1_2_2',
        )
    );

    $objVersaoSipRN->setStrVersaoAtual(array_key_last($objVersaoSipRN->getArrVersoes()));
    $objVersaoSipRN->setStrVersaoInfra('1.595.1');
    $objVersaoSipRN->setBolMySql(true);
    $objVersaoSipRN->setBolOracle(true);
    $objVersaoSipRN->setBolSqlServer(true);
    $objVersaoSipRN->setBolPostgreSql(true);
    $objVersaoSipRN->setBolErroVersaoInexistente(true);

    $objVersaoSipRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
  try {
      LogSip::getInstance()->gravar(InfraException::inspecionar($e));
  } catch (Exception $e) {
  }
    exit(1);
}
