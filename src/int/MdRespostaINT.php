<?php

require_once __DIR__.'/../../../SEI.php';

class MdRespostaINT extends InfraINT {

  public static function getCaminhoIcone($imagem, $relPath = null)
    {
      $versao=substr(SEI_VERSAO, 0, 1);

    if ($versao>3){

      switch ($imagem) {
        case 'enviar_resposta_sei3.png':
            return 'enviar_resposta_sei4.png';
        default:
          if($relPath==null){
                return $imagem;
          }        
            return $relPath . $imagem;
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

      return match ($arquivo) {
          'md_resposta_sei3.css' => 'md_resposta_sei4.css',
          default => $arquivo,
      };
    }

      return $arquivo;
  }
    
}
