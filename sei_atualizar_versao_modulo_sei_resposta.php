<?php

require_once dirname(__FILE__).'/../web/SEI.php';

class ModuloRespostaAtualizarSeiRN extends MdRespostaVersaoRN {

    const PARAMETRO_VERSAO = '1.0.1';
    const PARAMETRO_MODULO = 'MOD_RESPOSTA_VERSAO';

    private $objMetaBD = null;

    public function __construct() {
        parent::__construct();

        $this->objMetaBD = new InfraMetaBD(BancoSEI::getInstance());		
    }

    protected function atualizarVersaoConectado() {
        try {

            $this->inicializar('INICIANDO ATUALIZACAO DO MODULO RESPOSTA NO SEI ' . self::PARAMETRO_VERSAO);

            //testando se esta usando BDs suportados
            if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
                !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
                !(BancoSEI::getInstance() instanceof InfraOracle)) {

                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class(BancoSEI::getInstance()), true);
            }

            SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, SessaoSEI::$UNIDADE_TESTE);

            //testando permissoes de criações de tabelas
            $objMetaBD = new InfraMetaBD(BancoSEI::getInstance());

            if (count($objMetaBD->obterTabelas('resposta_sei_teste')) == 0) {
                BancoSEI::getInstance()->executarSql('CREATE TABLE resposta_sei_teste (id ' . $objMetaBD->tipoNumero() . ' null)');
            }
            BancoSEI::getInstance()->executarSql('DROP TABLE resposta_sei_teste');

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

            // Aplicação de scripts de atualização de forma incremental
            // Ausência de [break;] proposital para realizar a atualização incremental de versões
            $strVersaoModulo = $objInfraParametro->getValor(self::PARAMETRO_MODULO, false);
            switch ($strVersaoModulo) {
                case '': $this->instalarV100(); // Nenhuma versão instalada
                case '1.1.0': $this->instalarV110();
                    break;
                default:
                $this->finalizar('VERSAO DO MÓDULO JÁ CONSTA COMO ATUALIZADA');
                break;
            }

            $this->finalizar('FIM');
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException("Erro atualizando VERSAO: $e", $e);
        }
    }

    /**
     * Remove todos os índices criados para o conjunto de tabelas informado
     */
    protected function removerIndicesTabela($parobjMetaBD, $parFiltroTabelas)
    {
        $arrTabelasExclusao = is_array($parFiltroTabelas) ? $parFiltroTabelas : array($parFiltroTabelas);
        foreach ($arrTabelasExclusao as $strTabelaExclusao) {
            $arrStrIndices = $parobjMetaBD->obterIndices(null, $strTabelaExclusao);
            foreach ($arrStrIndices as $strTabela => $arrStrIndices) {
                if($strTabela == $strTabelaExclusao){
                    foreach ($arrStrIndices as $strNomeIndice => $arrStrColunas) {
                        $parobjMetaBD->excluirIndice($strTabelaExclusao, $strNomeIndice);
                    }
                }
            }
        }
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
        $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
        $objInfraParametroDTO = $objInfraParametroBD->consultar($objInfraParametroDTO);
        $objInfraParametroDTO->setStrValor($parStrNumeroVersao);
        $objInfraParametroBD->alterar($objInfraParametroDTO);
    }


    /**
     * Remove a chave primária da tabela indicada, removendo também o índice vinculado, caso seja necessário
     *
     * Necessário dependendo da versão do banco de dados Oracle utilizado que pode não remover um índice criado com mesmo
     * nome da chave primária, impedindo que este objeto seja recriado posteriormente na base de dados
     *
     * @param [type] $parStrNomeTabela
     * @param [type] $parStrNomeChavePrimario
     * @return void
     */
    private function excluirChavePrimariaComIndice($parStrNomeTabela, $parStrNomeChavePrimaria, $bolSuprimirErro=false)
    {
        try{
            $this->objMetaBD->excluirChavePrimaria($parStrNomeTabela, $parStrNomeChavePrimaria);

            try{
                $this->objMetaBD->excluirIndice($parStrNomeTabela, $parStrNomeChavePrimaria);
            } catch(\Exception $e) {
                //Caso o índice não seja localizado, nada deverá ser feito pois a existência depende de versão do banco de dados
            }
        } catch(Exception $e) {
            // Mensagem de erro deve ser suprimida caso seja indicado pelo usuário
            if(!$bolSuprimirErro){
                throw $e;
            }
        }
    }


    private function excluirChaveEstrangeira($parStrTabela, $parStrNomeChaveEstrangeira, $bolSuprimirErro=false)
    {
        try{
            $this->objMetaBD->excluirChaveEstrangeira($parStrTabela, $parStrNomeChaveEstrangeira);
        } catch(\Exception $e){
            // Mensagem de erro deve ser suprimida caso seja indicado pelo usuário
            if(!$bolSuprimirErro){
                throw $e;
            }
        }
    }


    /* Contêm atualizações da versao 1.0.0 do modulo */
    protected function instalarV100() {

        $objMetaBD = $this->objMeta;

        $objMetaBD->criarTabela(array(
            'tabela' => 'md_resposta_envio',
            'cols' => array(
                'id_resposta' => array($objMetaBD->tipoNumeroGrande(), MdMetaBD::NNULLO),
                'id_procedimento' => array($objMetaBD->tipoNumeroGrande(), MdMetaBD::NNULLO),
				'id_documento' => array($objMetaBD->tipoNumeroGrande(), MdMetaBD::NNULLO),
				'mensagem' => array($objMetaBD->tipoTextoGrande(), MdMetaBD::NNULLO),
				'sin_conclusiva' => array($objMetaBD->tipoTextoFixo(1), MdMetaBD::NNULLO),
				'dth_resposta' => array($objMetaBD->tipoDataHora(), MdMetaBD::NNULLO),
            ),
            'pk' => array('cols'=>array('id_resposta')),
            'fks' => array(
                'procedimento' => array('nome' => 'fk_md_resposta_procedimento',
                    'cols' => array('id_procedimento', 'id_procedimento')),
			),
            'fks' => array(
                'documento' => array('nome' => 'fk_md_resposta_documento',
                    'cols' => array('id_documento', 'id_documento')),
            )
        ));

        $objMetaBD->criarTabela(array(
            'tabela' => 'md_resposta_parametro',
            'cols' => array(
                'nome' => array($objMetaBD->tipoTextoVariavel(100), MdMetaBD::NNULLO),
                'valor' => array($objMetaBD->tipoTextoGrande(), MdMetaBD::NNULLO),
            ),
            'pk' => array('cols'=>array('nome'))
        ));

        $objMetaBD->criarTabela(array(
            'tabela' => 'md_resposta_rel_documento',
            'cols' => array(
                'id_resposta' => array($objMetaBD->tipoNumeroGrande(), MdMetaBD::NNULLO),
                'id_documento' => array($objMetaBD->tipoNumeroGrande(), MdMetaBD::NNULLO)
            ),
            'pk' => array('cols'=>array('id_resposta','id_documento')),
            'fks' => array(
                'md_resposta_envio' => array('nome' => 'fk_md_resposta_doc_resposta',
                    'cols' => array('id_resposta', 'id_resposta')),
			),
        ));


        //----------------------------------------------------------------------
        // Sequência: md_seq_resposta_envio
        //----------------------------------------------------------------------
        BancoSEI::getInstance()->criarSequencialNativa('md_seq_resposta_envio', 1);

        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(self::PARAMETRO_MODULO);
        $objInfraParametroDTO->setStrValor(self::PARAMETRO_VERSAO);
        $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
        $objInfraParametroBD->cadastrar($objInfraParametroDTO);

        $this->logar(' EXECUTADA A INSTALACAO DA VERSAO 1.0.0 DO MODULO RESPOSTA NO SEI COM SUCESSO');
    }

    /* Contêm atualizações da versao 1.1.0 do modulo */
    protected function instalarV110()
    {
        $this->atualizarNumeroVersao("1.1.0");
    }

}

try {

    require_once dirname(__FILE__).'/../web/SEI.php';

    //Normaliza o formato de número de versão considerando dois caracteres para cada item (3.0.15 -> 030015)
    $numVersaoAtual = explode('.', SEI_VERSAO);
    $numVersaoAtual = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoAtual);
    $numVersaoAtual = intval(join($numVersaoAtual));

    //Normaliza o formato de número de versão considerando dois caracteres para cada item (3.1.0 -> 030100)
    // A partir da versão 3.1.0 é que o SEI passa a dar suporte ao UsuarioScript/SenhaScript
    $numVersaoScript = explode('.', "3.1.0");
    $numVersaoScript = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoScript);
    $numVersaoScript = intval(join($numVersaoScript));

    if ($numVersaoAtual >= $numVersaoScript) {
        BancoSEI::getInstance()->setBolScript(true);

        if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','UsuarioScript')){
            throw new InfraException('Chave BancoSEI/UsuarioScript não encontrada.');
        }

        if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','UsuarioScript'))){
            throw new InfraException('Chave BancoSEI/UsuarioScript não possui valor.');
        }

        if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','SenhaScript')){
            throw new InfraException('Chave BancoSEI/SenhaScript não encontrada.');
        }

        if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','SenhaScript'))){
            throw new InfraException('Chave BancoSEI/SenhaScript não possui valor.');
        }
    }

    $objAtualizarRN = new ModuloRespostaAtualizarSeiRN();
    $objAtualizarRN->atualizarVersao();
    exit(0);
} catch (Exception $e) {
    print InfraException::inspecionar($e);
    exit(1);
}

print PHP_EOL;
