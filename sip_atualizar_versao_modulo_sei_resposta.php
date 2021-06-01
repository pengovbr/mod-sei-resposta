<?php

require_once dirname(__FILE__).'/../web/Sip.php';

class ModuloRespostaVersaoSipRN extends InfraRN {

    const PARAMETRO_VERSAO = '1.0.0';
    const PARAMETRO_MODULO = 'MOD_RESPOSTA_VERSAO';

    private $arrRecurso = array();
    private $arrMenu = array();

    public function __construct(){
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco(){
        return BancoSip::getInstance();
    }

    /**
     * Inicia o script criando um contator interno do tempo de execução
     *
     * @return null
     */
    protected function inicializar($strTitulo) {

        session_start();
        SessaoSip::getInstance(false);

        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        ob_implicit_flush();

        $this->objDebug = InfraDebug::getInstance();
        $this->objDebug->setBolLigado(true);
        $this->objDebug->setBolDebugInfra(true);
        $this->objDebug->setBolEcho(true);
        $this->objDebug->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();
        $this->logar($strTitulo);
    }

    protected function atualizarVersaoConectado() {
        try {
            $this->inicializar('INICIANDO ATUALIZACAO DO MODULO RESPOSTA NO SIP VERSAO ' . self::PARAMETRO_VERSAO);

            //testando se esta usando BDs suportados
            if (!(BancoSip::getInstance() instanceof InfraMySql) &&
                    !(BancoSip::getInstance() instanceof InfraSqlServer) &&
                    !(BancoSip::getInstance() instanceof InfraOracle)) {

                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class(BancoSip::getInstance()), true);
            }

            //testando permissoes de criações de tabelas
            $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());

            if (count($objInfraMetaBD->obterTabelas('resposta_sip_teste')) == 0) {
                BancoSip::getInstance()->executarSql('CREATE TABLE resposta_sip_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }
            BancoSip::getInstance()->executarSql('DROP TABLE resposta_sip_teste');


            $objInfraParametro = new InfraParametro(BancoSip::getInstance());

            // Aplicação de scripts de atualização de forma incremental
            // Ausência de [break;] proposital para realizar a atualização incremental de versões
            $strVersaoModuloPen = $objInfraParametro->getValor(self::PARAMETRO_MODULO, false);
            switch ($strVersaoModuloPen) {
                //case '' - Nenhuma versão instalada
                case '': $this->instalarV100();
                    break;

                default:
                    $this->finalizar('VERSAO DO MÓDULO JÁ CONSTA COMO ATUALIZADA');
                    break;
            }

            $this->finalizar('FIM');
            InfraDebug::getInstance()->setBolDebugInfra(true);
        } catch (Exception $e) {

            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException('Erro atualizando VERSAO.', $e);
        }
    }


    /**
     * Finaliza o script informando o tempo de execução.
     *
     * @return null
     */
    protected function finalizar($strMsg=null, $bolErro=false){
        if (!$bolErro) {
          $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
          $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');
        }else{
          $strMsg = 'ERRO: '.$strMsg;
        }

        if ($strMsg!=null){
          $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

    /**
     * Adiciona uma mensagem ao output para o usuário
     *
     * @return null
     */
    protected function logar($strMsg) {
        $this->objDebug->gravar($strMsg);
    }

    /**
     * Retorna o ID do sistema
     *
     * @return int
     */
    protected function getNumIdSistema($strSigla='SIP') {

        $objDTO = new SistemaDTO();
        $objDTO->setStrSigla($strSigla);
        $objDTO->setNumMaxRegistrosRetorno(1);
        $objDTO->retNumIdSistema();

        $objRN = new SistemaRN();
        $objDTO = $objRN->consultar($objDTO);

        return (empty($objDTO)) ? '0' : $objDTO->getNumIdSistema();
    }

    /**
     *
     * @return int Código do Menu
     */
    protected function getNumIdMenu($strMenu = 'Principal', $numIdSistema = 0) {

        $objDTO = new MenuDTO();
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setStrNome($strMenu);
        $objDTO->setNumMaxRegistrosRetorno(1);
        $objDTO->retNumIdMenu();

        $objRN = new MenuRN();
        $objDTO = $objRN->consultar($objDTO);

        if (empty($objDTO)) {
            throw new InfraException('Menu ' . $strMenu . ' não encontrado.');
        }

        return $objDTO->getNumIdMenu();
    }

    /**
     * Cria novo recurso no SIP
     * @return int Código do Recurso gerado
     */
    protected function criarRecurso($strNome, $strDescricao, $numIdSistema) {

        $objDTO = new RecursoDTO();
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setStrNome($strNome);
        $objDTO->setNumMaxRegistrosRetorno(1);
        $objDTO->retNumIdRecurso();

        $objBD = new RecursoBD($this->getObjInfraIBanco());
        $objDTO = $objBD->consultar($objDTO);

        if (empty($objDTO)) {

            $objDTO = new RecursoDTO();
            $objDTO->setNumIdRecurso(null);
            $objDTO->setStrDescricao($strDescricao);
            $objDTO->setNumIdSistema($numIdSistema);
            $objDTO->setStrNome($strNome);
            $objDTO->setStrCaminho('controlador.php?acao=' . $strNome);
            $objDTO->setStrSinAtivo('S');

            $objDTO = $objBD->cadastrar($objDTO);
        }

        $this->arrRecurso[] = $objDTO->getNumIdRecurso();

        return $objDTO->getNumIdRecurso();
    }

    protected function renomearRecurso($numIdSistema, $strNomeAtual, $strNomeNovo){

        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->setBolExclusaoLogica(false);
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->retStrCaminho();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNomeAtual);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO!=null){
            $objRecursoDTO->setStrNome($strNomeNovo);
            $objRecursoDTO->setStrCaminho(str_replace($strNomeAtual,$strNomeNovo,$objRecursoDTO->getStrCaminho()));
            $objRecursoRN->alterar($objRecursoDTO);
        }
    }

    protected function consultarRecurso($numIdSistema, $strNomeRecurso)
    {
        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->setBolExclusaoLogica(false);
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNomeRecurso);
        $objRecursoDTO->retNumIdRecurso();

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO == null){
            throw new InfraException("Recurso com nome {$strNomeRecurso} não pode ser localizado.");
        }

        return $objRecursoDTO->getNumIdRecurso();
    }

    /**
     * Cria um novo menu lateral para o sistema SEI
     *
     * @return int
     */
    protected function criarMenu($strRotulo, $numSequencia, $numIdItemMenuPai, $numIdMenu, $numIdRecurso, $numIdSistema)
    {
        $objDTO = new ItemMenuDTO();
        $objDTO->setNumIdItemMenuPai($numIdItemMenuPai);
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setStrRotulo($strRotulo);
        $objDTO->setNumIdRecurso($numIdRecurso);
        $objDTO->setNumMaxRegistrosRetorno(1);
        $objDTO->retNumIdItemMenu();

        $objBD = new ItemMenuBD(BancoSip::getInstance());
        $objDTO = $objBD->consultar($objDTO);

        if (empty($objDTO)) {
            $objDTO = new ItemMenuDTO();
            $objDTO->setNumIdMenu($numIdMenu);
            $objDTO->setNumIdMenuPai($numIdMenu);
            $objDTO->setNumIdItemMenu(null);
            $objDTO->setNumIdItemMenuPai($numIdItemMenuPai);
            $objDTO->setNumIdSistema($numIdSistema);
            $objDTO->setNumIdRecurso($numIdRecurso);
            $objDTO->setStrRotulo($strRotulo);
            $objDTO->setStrDescricao(null);
            $objDTO->setNumSequencia($numSequencia);
            $objDTO->setStrSinNovaJanela('N');
            $objDTO->setStrSinAtivo('S');

            $objDTO = $objBD->cadastrar($objDTO);
        }

        if (!empty($numIdRecurso)) {
            $this->arrMenu[] = array($objDTO->getNumIdItemMenu(), $numIdMenu, $numIdRecurso);
        }

        return $objDTO->getNumIdItemMenu();
    }


    //TODO: Necessário refatorar método abaixo devido a baixa qualidade da codificação
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

    //TODO: Necessário refatorar método abaixo devido a baixa qualidade da codificação
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

        // Vincula a um perfil os recursos e menus adicionados nos métodos criarMenu e criarReturso
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


    /**
     * Atualiza o número de versão do módulo nas tabelas de parâmetro do sistema
     *
     * @param string $parStrNumeroVersao
     * @return void
     */
    private function atualizarNumeroVersao($parStrNumeroVersao)
    {
        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(array(self::PARAMETRO_MODULO), InfraDTO::$OPER_IN);
        $objInfraParametroDTO->retTodos();
        $objInfraParametroBD = new InfraParametroBD(BancoSip::getInstance());
        $objInfraParametroDTO = $objInfraParametroBD->consultar($objInfraParametroDTO);
        $objInfraParametroDTO->setStrValor($parStrNumeroVersao);
        $objInfraParametroBD->alterar($objInfraParametroDTO);
    }

    /**
     * Instala/Atualiza os módulo resposta para versão 1.0
     */
    private function instalarV100() {

        $objBD = new ItemMenuBD(BancoSip::getInstance());

        // Achar o root
        $numIdSistema = $this->getNumIdSistema('SEI');
        $numIdMenu = $this->getNumIdMenu('Principal', $numIdSistema);

        $this->criarRecurso('md_resposta_enviar', 'Enviar resposta ao portal do gor.br', $numIdSistema);

        $objDTO = new ItemMenuDTO();
        $objDTO->setNumIdSistema($numIdSistema);
        $objDTO->setNumIdMenu($numIdMenu);
        $objDTO->setStrRotulo('Administração');
        $objDTO->setNumMaxRegistrosRetorno(1);
        $objDTO->retNumIdItemMenu();

        $objDTO = $objBD->consultar($objDTO);

        if (empty($objDTO)) {
            throw new InfraException('Menu "Administração" não foi localizado');
        }

        $numIdItemMenuRoot = $objDTO->getNumIdItemMenu();

        // Gera o submenu Módulo de Resposta - Gov.br
        $numIdItemMenuPai = $this->criarMenu('Módulo de Resposta - Gov.br', 0, $numIdItemMenuRoot, $numIdMenu, null, $numIdSistema);

        // Gera o submenu Módulo de Resposta - Gov.br > Parâmetros de Configuração
        $numIdRecurso = $this->criarRecurso('md_resposta_configuracao', 'Configuração dos Parametros Gerais do Módulo', $numIdSistema);
        $this->criarMenu('Parâmetros de Configuração', 10, $numIdItemMenuPai, $numIdMenu, $numIdRecurso, $numIdSistema);        

        //Atribui as permissões aos recursos e menus
        $this->atribuirPerfil($numIdSistema);
   

        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(self::PARAMETRO_MODULO);
        $objInfraParametroDTO->setStrValor(self::PARAMETRO_VERSAO);
        $objInfraParametroBD = new InfraParametroBD(BancoSip::getInstance());
        $objInfraParametroBD->cadastrar($objInfraParametroDTO);
    }
}

try {

    //Normaliza o formato de número de versão considerando dois caracteres para cada item (3.0.15 -> 030015)
    $numVersaoAtual = explode('.', SIP_VERSAO);
    $numVersaoAtual = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoAtual);
    $numVersaoAtual = intval(join($numVersaoAtual));

    //Normaliza o formato de número de versão considerando dois caracteres para cada item (2.1.0 -> 020100)
    // A partir da versão 2.1.0 é que o SIP passa a dar suporte ao UsuarioScript/SenhaScript
    $numVersaoScript = explode('.', "2.1.0");
    $numVersaoScript = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoScript);
    $numVersaoScript = intval(join($numVersaoScript));

    if ($numVersaoAtual >= $numVersaoScript) {
        BancoSip::getInstance()->setBolScript(true);

        if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','UsuarioScript')){
            throw new InfraException('Chave BancoSip/UsuarioScript não encontrada.');
        }

        if (InfraString::isBolVazia(ConfiguracaoSip::getInstance()->getValor('BancoSip','UsuarioScript'))){
            throw new InfraException('Chave BancoSip/UsuarioScript não possui valor.');
        }

        if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','SenhaScript')){
            throw new InfraException('Chave BancoSip/SenhaScript não encontrada.');
        }

        if (InfraString::isBolVazia(ConfiguracaoSip::getInstance()->getValor('BancoSip','SenhaScript'))){
            throw new InfraException('Chave BancoSip/SenhaScript não possui valor.');
        }
    }

    $objAtualizarRN = new ModuloRespostaVersaoSipRN();
    $objAtualizarRN->atualizarVersao();
    exit(0);
} catch (Exception $e) {
    print InfraException::inspecionar($e);
    exit(1);
}

print PHP_EOL;
