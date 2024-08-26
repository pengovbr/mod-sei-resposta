#!/bin/bash

standalone_destruir(){

    ret=$(docker ps | grep seleniumchrome | cat)  
    if [ "$ret" != "" ]; then
        echo "Vamos parar o selenium"
        docker stop seleniumchrome
        sleep 5
    fi

    ret=$(docker ps -a | grep seleniumchrome | cat)  
    if [ "$ret" != "" ]; then
        echo "Vamos remover o selenium"
        docker rm seleniumchrome
        sleep 2
    fi

}


standalone_pingar(){

    for i in {1..4}; do 
        echo 'Tentando acessar SeleniumChrome...' 
        var=$(docker logs  seleniumchrome | grep "Started Selenium Standalone" | cat); 
        if [ "$var" != "" ]; then 
            echo 'SeleniumChrome respondeu com sucesso....'
            break 
        else 
            echo 'Aguardando SeleniumChrome...'; 
        fi 
        sleep 5  
    done
  	var=$(docker logs  seleniumchrome | grep "Started Selenium Standalone");  
    if [ "$var" = "" ]; then echo 'Selenium nao subiu. Saindo do teste'; exit 1; fi;

}

standalone_subir(){

    standalone_destruir

    echo "Vamos subir um container de Seleniumchrome standalone"
    docker run -d --rm --name seleniumchrome  -p 4444:4444 --network=host -v /dev/shm:/dev/shm selenium/standalone-chrome:4.1.3-20220405

    standalone_pingar

}

standalone_verificar(){
    
    ret=$(docker ps | grep seleniumchrome | cat)
    
    if [ "$ret" == "" ]; then
        standalone_subir
    else
        standalone_pingar
    fi

}

rodar_teste(){
    
    if [ "$SELENIUMTEST_MODALIDADE" == "LOCAL" ]; then
        pytest -x --tb=short seleniumPython/$1
    fi
    
    if [ "$SELENIUMTEST_MODALIDADE" == "STANDALONE" ] || [ "$SELENIUMTEST_MODALIDADE" == "REMOTE" ]; then

        if [ "$SELENIUMTEST_MODALIDADE" == "STANDALONE" ]; then

            standalone_verificar
            
        fi
        
        docker run --rm -t -v "$PWD":/t -w /t \
            -e "SELENIUMTEST_SISTEMA_URL=$SELENIUMTEST_SISTEMA_URL" \
            -e "SELENIUMTEST_SISTEMA_ORGAO=$SELENIUMTEST_SISTEMA_ORGAO" \
            -e "SELENIUMTEST_MODALIDADE=$SELENIUMTEST_MODALIDADE" \
            -e "SELENIUMTEST_SELENIUMHOST_URL=$SELENIUMTEST_SELENIUMHOST_URL" \
            --network=host supergovbr/pytestseleniumdocker:latest sh -c \
            "echo '127.0.0.1 seleniumchrome' >> /etc/hosts && pytest --disable-pytest-warnings -W ignore::DeprecationWarning -o junit_family=xunit2 --junitxml=/resultado/resultado.xml -x --tb=short seleniumPython/$1"
    fi
    

}

paralelizar(){

  pids=""
  i=1
  for p in $@; do

      pytest -x --tb=short seleniumPython/$p/ &
      pids[$i]=$!
      sleep 5

      i=$(($i+1))
  done

  for pid in ${pids[*]}; do
      wait $pid
  done

}

db_restart(){
    # escreva aqui o comando para restartar o db

    if [ "$SELENIUMTEST_RESTART_DB" == "true" ]; then

        docker restart oracle

      	echo "Vamos tentar acessar a pagina de login do SUPER, vamos aguardar por 45 segs."
      	for number in 1 2 3 4 5 6 7 8 9 ; do \
      	    echo 'Tentando acessar...'; var=$(curl -s -L $HOST_URL/sei | grep "txtUsuario" | cat); \
      			if [ "$var" != "" ]; then \
      					echo 'Pagina respondeu com tela de login' ; \
      					break ; \
      			else \
      			    echo 'Aguardando resposta ...'; \
      			fi; \
      			sleep 5; \
      	done
    fi

}

backup(){

    # escreva aqui o comand de bakcup para o seu ambiente
    if [ "$SELENIUMTEST_BACKUP" == "true" ]; then
        docker exec -it mysql bash -c "mysqldump -pP@ssword --databases sei sip > $1"
    fi
}


atualizar_base_login_multiorgao(){

    # escreva aqui o comando para liberar o orgao sem ad ou ldap

    docker cp utils/alterar_orgao_login.php httpd:/

    docker exec -t httpd bash -c "php /alterar_orgao_login.php"


}

