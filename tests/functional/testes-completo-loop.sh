#!/bin/bash

# script feito para rodar usando o makefile localizado em dirs superiores

# Script para rodar os testes funcionais completo
# como o teste apresenta alguns falsos positivos eh comum ter q rodar mais de uma vez para dar certo
# esse script identifica quando deu erro e roda novamente do zero, recriando o ambiente

# certifique-se de que a vm não seja utilizada e também tenha recurso suficiente pois quanto mais pesada mais propenso
# a falsos positivos nos testes

DIR=$( dirname "${BASH_SOURCE[0]}" )
source $DIR/utils/carga-functions.sh

set +e

RET2=1
COUNT=1
while [ $COUNT -le $SELENIUMTEST_RETRYTESTS ] && [ ! "$RET2" == "0" ];
do
  
  COUNT=$(( $COUNT + 1 )) 
  
  standalone_destruir
  
  if [ "$SELENIUMTEST_MODALIDADE" == "STANDALONE" ]; then
      standalone_subir
  fi
  
  make -C ../../ destroy
  make -C ../../ up
  RET=$?
  if [ ! "$RET" == "0" ]; then
    echo "Erro ao subir ambiente docker. Verifique."
    echo "Abandonando..."
    exit 1
  fi

  
  RET=1
  while [ ! "$RET" == "0" ]
  do
      echo ""
      echo "Esperando o sistema ficar online ...."

      curl -s -L http://localhost:8000/sei | grep "txtUsuario" > /dev/null

      RET=$?
      sleep 5
    
  done  
  
  make -C ../../ install
  
  
  RET=$?
  if [ ! "$RET" == "0" ]; then
    echo "Abandonando execucao..."
    exit 1
  fi
  

  make -C ../../ MSGORIENTACAO=n tests-functional
  
  RET2=$?
    
    
done

if [ $COUNT -gt $SELENIUMTEST_RETRYTESTS ] && [ ! "$RET2" == "0" ] ;then
  echo "Atingiu a quantidade de tentativas definidas. "
  echo "Abandonando..."
  exit 1
fi 
