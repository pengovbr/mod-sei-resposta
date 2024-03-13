#!/bin/bash

#ATENCAO ANTES DE RODAR DO ZERO, O AMBIENTE DEVE TER SIDO CONSTRUIDO, LEIA O README DOS TESTES

set -e


DIR=$( dirname "${BASH_SOURCE[0]}" )

source $DIR/utils/carga-functions.sh


rodar_teste 00-CriarSistema 
rodar_teste 01-Configs 
rodar_teste 02-SEI-MR-EnviarResposta 