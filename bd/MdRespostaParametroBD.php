<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 10/06/2020 - criado por Higo Cavalcante
*
*
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaParametroBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }

}
?>