<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 17/05/2020 - criado por Higo Cavalcante
*
*
* Versão no CVS: $Id$
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaConfiguracaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
     return 'rel_resposta_usuario_sistema';
  }

  public function montar() {

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');
     $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'SinAtivo','sin_ativo');

     $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'Sigla','sigla','usuario');
     $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,'Nome','nome','usuario');
  
     $this->configurarPK('IdUsuario',InfraDTO::$TIPO_PK_INFORMADO);
     $this->configurarFK('IdUsuario', 'usuario', 'id_usuario');

     $this->configurarExclusaoLogica('SinAtivo', 'N');

  }

}
?>