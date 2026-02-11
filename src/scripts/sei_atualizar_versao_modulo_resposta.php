<?php

require_once dirname(__FILE__) . '/../../web/SEI.php';

class VersaoSeiRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_RESPOSTA';
    const NOME_MODULO = 'Módulo de Resposta';

    protected $objInfraBanco;
    protected $objMetaBD;
    protected $objInfraSequencia;
    protected $objInfraParametro;

  public function __construct()
    {
      parent::__construct();
      ini_set('max_execution_time', '0');
      ini_set('memory_limit', '-1');

      SessaoSEI::getInstance(false);

      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(true);
      InfraDebug::getInstance()->limpar();
  }

  protected function inicializarObjInfraIBanco()
    {
      return BancoSEI::getInstance();
  }

  protected function verificarVersaoInstaladaControlado()
    {
      $objInfraParametroDTO = new InfraParametroDTO();
      $objInfraParametroDTO->setStrNome(VersaoSeiRN::PARAMETRO_VERSAO_MODULO);
      $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
    if ($objInfraParametroBD->contar($objInfraParametroDTO) == 0) {
        $objInfraParametroDTO->setStrValor('0.0.0');
        $objInfraParametroBD->cadastrar($objInfraParametroDTO);
    }
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_0_0_0($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_0_0($strVersaoAtual)
    {
      $this->objInfraBanco = BancoSEI::getInstance();
      $this->objMetaBD = new InfraMetaBD($this->objInfraBanco);
      $this->objInfraSequencia = new InfraSequencia($this->objInfraBanco);
      $this->objInfraParametro = new InfraParametro($this->objInfraBanco);

    try {

        // Cria a tabela de resposta
        $this->objInfraBanco->executarSql('CREATE TABLE md_resposta_envio (
                id_resposta ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL ,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL ,
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL ,
                mensagem ' . $this->objMetaBD->tipoTextoGrande() . '  NOT NULL ,
                sin_conclusiva ' . $this->objMetaBD->tipoTextoFixo(1) . '  NOT NULL ,
                dth_resposta ' . $this->objMetaBD->tipoDataHora() . '  NOT NULL
            )');
            
        $this->objMetaBD->adicionarChavePrimaria('md_resposta_envio', 'pk_md_resposta_envio', array('id_resposta'));
        $this->objMetaBD->adicionarChaveEstrangeira('fk_md_resposta_procedimento', 'md_resposta_envio', array('id_procedimento'), 'procedimento', array('id_procedimento'));
        $this->objMetaBD->adicionarChaveEstrangeira('fk_md_resposta_documento', 'md_resposta_envio', array('id_documento'), 'documento', array('id_documento'));
    
        // Cria a tabela de configuração
        $this->objInfraBanco->executarSql('CREATE TABLE md_resposta_parametro (
                nome ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoGrande() . '  NOT NULL
            )');

        $this->objMetaBD->adicionarChavePrimaria('md_resposta_parametro', 'pk_md_resposta_parametro_nome', array('nome'));
    
        // Cria a tabela de relacionamento
        $this->objInfraBanco->executarSql('CREATE TABLE md_resposta_rel_documento (
                id_resposta ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL ,
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL
            )');

        $this->objMetaBD->adicionarChavePrimaria('md_resposta_rel_documento', 'pk_md_resposta_list_documento', array('id_resposta', 'id_documento'));
        $this->objMetaBD->adicionarChaveEstrangeira('fk_md_resposta_doc_resposta', 'md_resposta_rel_documento', array('id_resposta'), 'md_resposta_envio', array('id_resposta'));
    
        // Sequência: md_seq_resposta_envio
        $this->objInfraSequencia->criarSequencia('md_resposta_envio', '1', '0', '9999999999');

    } catch (Exception $ex) {
        throw new InfraException('Erro ao atualizar a versão 1.0.0 do módulo de resposta', $ex);
    }
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
      $this->objInfraBanco = BancoSEI::getInstance();
      $this->objMetaBD = new InfraMetaBD($this->objInfraBanco);
      $this->objInfraSequencia = new InfraSequencia($this->objInfraBanco);
      $this->objInfraParametro = new InfraParametro($this->objInfraBanco);

    try {

        // Cria a tabela de processo sem resposta
        $this->objInfraBanco->executarSql('CREATE TABLE md_resposta_processo (
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL
            )');
            
        $this->objMetaBD->adicionarChavePrimaria('md_resposta_processo', 'pk_md_resposta_processo', array('id_procedimento'));

      if (BancoSEI::getInstance() instanceof InfraOracle) {
        BancoSEI::getInstance()->executarSql('ALTER TABLE md_resposta_processo ADD CONSTRAINT fk_md_resposta_processo FOREIGN KEY (id_procedimento) REFERENCES procedimento(id_procedimento)');
      }else{
        $this->objMetaBD->adicionarChaveEstrangeira('fk_md_resposta_processo', 'md_resposta_processo', array('id_procedimento'), 'procedimento', array('id_procedimento'));
      }
    } catch (Exception $ex) {
        throw new InfraException('Erro ao atualizar a versão 1.2.0 do módulo de resposta', $ex);
    }
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_2_1($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_2_2($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_1_3_0($strVersaoAtual)
    {
  }

  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_2_0_0($strVersaoAtual)
    {
  }
  
  // phpcs:ignore PSR1.Methods.CamelCapsMethodName
  public function versao_2_0_1($strVersaoAtual)
    {
  }
}

try {
    session_start();

    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $objVersaoSeiRN = new VersaoSeiRN();
    $objVersaoSeiRN->verificarVersaoInstalada();
    $objVersaoSeiRN->setStrNome(VersaoSeiRN::NOME_MODULO);
    $objVersaoSeiRN->setStrVersaoAtual(MdRespostaIntegracao::VERSAO_MODULO);
    $objVersaoSeiRN->setStrParametroVersao(VersaoSeiRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSeiRN->setArrVersoes(
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
            '1.3.0' => 'versao_1_3_0',
            '2.0.0' => 'versao_2_0_0',
            '2.0.1' => 'versao_2_0_1',
        )
    );

    $objVersaoSeiRN->setStrVersaoAtual(array_key_last($objVersaoSeiRN->getArrVersoes()));
    $objVersaoSeiRN->setStrVersaoInfra('1.595.1');
    $objVersaoSeiRN->setBolMySql(true);
    $objVersaoSeiRN->setBolOracle(true);
    $objVersaoSeiRN->setBolSqlServer(true);
    $objVersaoSeiRN->setBolPostgreSql(true);
    $objVersaoSeiRN->setBolErroVersaoInexistente(true);
    $objVersaoSeiRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
  try {
      LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
  } catch (Exception $e) {
  }
    exit(1);
}
