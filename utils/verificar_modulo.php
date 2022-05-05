<?php
require_once '/opt/sei/web/SEI.php';

if (! array_key_exists( 'MdRespostaIntegracao' , $SEI_MODULOS)){

    exit(1);

}else{
  
    foreach ($SEI_MODULOS as $strModulo => $seiModulo) {
        if($strModulo=='MdRespostaIntegracao'){

          $v = $seiModulo->getVersao();
          exit(0);

        }
    }


    exit(1);

}
?>