<?php

require_once dirname(__FILE__).'/../../../SEI.php';
require_once dirname(__FILE__).'/../../../../../sip/web/Sip.php';
require_once dirname(__FILE__).'/../../../../../sip/web/BancoSip.php';

class MdRespostaVersaoRN extends InfraRN {

    private $numSeg = 0;
    private $versaoAtualDesteModulo = '0.0.1';
    private $nomeParametroModulo = 'MR_VERSAO';
    private $historicoVersoes = array('0.0.1');

    public function __construct(){
        $this->inicializar(' SEI - INICIALIZAR ');
    }

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    private function inicializar($strTitulo){

        ini_set('max_execution_time','0');
        ini_set('memory_limit','-1');
        
        try {
            @ini_set('zlib.output_compression','0');
            @ini_set('implicit_flush', '1');
        } catch(Exception $e) {}
        
        BancoSEI::getInstance()->abrirConexao();
        BancoSEI::getInstance()->abrirTransacao();
        
        ob_implicit_flush();
        
        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();
        
        $this->logar($strTitulo);

    }

    private function logar($strMsg){
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    private function finalizar($strMsg=null, $bolErro){

        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');
        } else {
            $strMsg = 'ERRO: '.$strMsg;
        }
        
        if ($strMsg!=null){
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        BancoSEI::getInstance()->cancelarTransacao();
        BancoSEI::getInstance()->fecharConexao();
        InfraDebug::getInstance()->limpar();
        $this->numSeg = 0;
        die;

    }

    /**
    * @throws InfraException
    */
    protected function atualizarVersaoControlado(){
        
        try {
            
            if (!(BancoSEI::getInstance() instanceof InfraMySql) && !(BancoSEI::getInstance() instanceof InfraSqlServer) && !(BancoSEI::getInstance() instanceof InfraOracle)){
                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSEI::getInstance()),true);
            }
            
            //Selecionando versгo a ser instalada
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $strVersaoPreviaModuloResposta = $objInfraParametro->getValor('MR_VERSAO', false);
            
            $instalacao = array();
            switch($this->versaoAtualDesteModulo) {
                case '0.0.1':
                    // Versгo do plugin com suporte apenas ao Mysql
                    $instalacao = $this->instalarv001($strVersaoPreviaModuloResposta);
                    break;
                default:
                    $instalacao["operacoes"] = null;
                    $instalacao["erro"] = "Erro instalando/atualizando Mуdulo de Resposta - Gov.br no SEI. Versгo do mуdulo".$strVersaoPreviaModuloResposta." invбlida";
                    break;      
            }
            if (isset($instalacao["erro"])) {
                 $this->finalizar($instalacao["erro"],true);
            } else {
                 $this->logar("Instalaзгo/Atualizaзгo realizada com sucesso");
            }
            
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
    
            LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
            
            BancoSEI::getInstance()->confirmarTransacao();
            BancoSEI::getInstance()->fecharConexao();
            InfraDebug::getInstance()->limpar();
            
        } catch(Exception $e) {
            
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            
            BancoSEI::getInstance()->cancelarTransacao();
            BancoSEI::getInstance()->fecharConexao();
    
            InfraDebug::getInstance()->limpar();
            throw new InfraException('Erro instalando/atualizando Mуdulo de Resposta - Gov.br no SEI.', $e);
                    
        }
    
    }
  
    private function instalarv001($strVersaoPreviaModuloResposta){

        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $this->logar(' INICIANDO OPERACOES DA INSTALACAO DA VERSAO 0.0.1 DO MODULO RESPOSTA NA BASE DO SEI');
        
        $versao = '0.0.1';

        $resultado = array();
        $resultado["operacoes"] = null;

        if(InfraString::isBolVazia($strVersaoPreviaModuloResposta)){

            //Criando a tabela de pacotes nos trГЄs bancos
            BancoSEI::getInstance()->executarSql("CREATE TABLE md_resposta_envio ( 
                id_resposta bigint(20) NOT NULL,
                id_procedimento bigint(20) NOT NULL,
                id_documento bigint(20) NOT NULL,
                mensagem varchar(5000) NOT NULL,
                sin_conclusiva char(1) NOT NULL,
                dth_resposta datetime NOT NULL,
                PRIMARY KEY (id_resposta),
                UNIQUE KEY ak_resposta (id_documento,id_procedimento),
                UNIQUE KEY id_documento_UNIQUE (id_documento)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            BancoSEI::getInstance()->executarSql("CREATE TABLE md_resposta_parametro (
                nome varchar(100) NOT NULL,
                valor mediumtext,
                PRIMARY KEY (nome)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            BancoSEI::getInstance()->executarSql("CREATE TABLE md_resposta_rel_documento (
                id_resposta bigint(20) NOT NULL,
                id_documento bigint(20) NOT NULL,
                PRIMARY KEY (id_resposta,id_documento),
                CONSTRAINT fk_md_resposta_rel_documento_resposta_envio FOREIGN KEY (id_resposta) REFERENCES md_resposta_envio (id_resposta)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            if (BancoSEI::getInstance() instanceof InfraMySql){
                BancoSEI::getInstance()->executarSql('create table seq_md_resposta_envio (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
            } else if (BancoSEI::getInstance() instanceof InfraSqlServer){
                BancoSEI::getInstance()->executarSql('create table seq_md_resposta_envio (id bigint identity(1,1), campo char(1) null)');
            } else if (BancoSEI::getInstance() instanceof InfraOracle){
                BancoSEI::getInstance()->criarSequencialNativa('seq_md_resposta_envio', 1);
            }

            BancoSEI::getInstance()->executarSql('insert into infra_parametro(nome,valor) values(\'MR_VERSAO\', \''.$this->versaoAtualDesteModulo.'\')');   
                
        }else if(trim($strVersaoPreviaModuloResposta)==$versao){

            $resultado["erro"] = "Erro instalando/atualizando MГіdulo Protocolo Integrado no SEI. VersГЈo ".$strVersaoPreviaModuloResposta." jГЎ instalada";
            return $resultado;

        }
        
        return $resultado;
    }
    
}

?>