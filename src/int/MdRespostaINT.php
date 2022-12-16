<?php

require_once dirname(__FILE__).'/../../../SEI.php';

class MdRespostaINT extends InfraINT {

  public static function getCaminhoIcone($imagem, $relPath = null)
    {
      $versao=substr(SEI_VERSAO, 0, 1);

    if ($versao>3){

      switch ($imagem) {
        case 'enviar_resposta_sei3.png':
            return 'enviar_resposta_sei4.png';
            break;
        default:
          if($relPath==null){
                return $imagem;
          }        
            return $relPath . $imagem;
            break;
      }

    }

    if($relPath==null){
        return $imagem;
    }        
      return $relPath . $imagem;

  }

  public static function getCssCompatibilidadeSEI4($arquivo)
    {

      $versao = substr(SEI_VERSAO, 0, 1);

    if ($versao > 3) {

      switch ($arquivo) {
        case 'md_resposta_sei3.css':
            return 'md_resposta_sei4.css';
            break;
        default:
            return $arquivo;
            break;
      }
    }

      return $arquivo;
  }
    
}
