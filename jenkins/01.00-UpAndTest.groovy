/*

Pipeline Jenkins que automatiza checkout, up e roda os testes 1x apenas
No seu cluster selenium vc precisa de um agente com a label SUPERGD e o user do agente precisa ter permissao de sudo, 
alem do docker e docker-compose
Nao recomendado rodar no jenkins master pois o Makefile precisa alterar a data da vm durante a execucao

*/

pipeline {
    agent {
      node {
        label 'SUPERRESPOSTA'
      }
    }
    
    parameters { 
        choice(
            name: 'database',
            choices: "mysql\noracle\nsqlserver",                
            description: 'Qual o banco de dados' )
        string(
            name: 'urlGit',
            defaultValue:"https://github.com/spbgovbr/mod-resposta.git",
            description: "Url do git onde se encontra o módulo")
        string(
            name: 'credentialGit',
            defaultValue:"githubcred",
            description: "Jenkins Credencial do git onde se encontra o módulo")
	      string(
	          name: 'branchGit',
	          defaultValue:"master",
	          description: "Branch/Versao do git onde se encontra módulo")
	      string(
	          name: 'sourceSuperLocation',
	          defaultValue:"~/sei/FontesSEIcopia",
	          description: "Localizacao do fonte do Super no servidor onde vai rodar o job")
	      
    }
    
    stages {

        stage('Inicializar Job'){
            steps {
                
                script{                    
                    DATABASE = params.database
                    GITURL = params.urlGit
					          GITCRED = params.credentialGit
					          GITBRANCH = params.branchGit
                    SUPERLOCATION = params.sourceSuperLocation
                    
                    if ( env.BUILD_NUMBER == '1' ){
                        currentBuild.result = 'ABORTED'
                        warning('Informe os valores de parametro iniciais. Caso eles n tenham aparecido faça login novamente')
                    }

                }

                sh """
                echo ${WORKSPACE}
                ls -lha
                
                make destroy || true
                
                """
            }
        }
        
        stage('Checkout'){
            
            steps {
                
              sh """
              
              git config --global http.sslVerify false
              """
                
                git branch: GITBRANCH,
                    credentialsId: GITCRED,
                    url: GITURL
                
                sh """
                
                ls -l
				
                """
            }
        }
        
        stage('Build Env - Run Tests'){
        
            steps {
                
                
                sh """
                ls
                rm -rf .env
                rm -rf .testselenium.env
                
                make base="${DATABASE}" config
                make .testselenium.env
                
                sed -i "s|export SELENIUMTEST_RETRYTESTS=5|export SELENIUMTEST_RETRYTESTS=1|" .testselenium.env             
                sed -i "s|SEI_PATH=../../../../|SEI_PATH=${SUPERLOCATION}|" .env
                
                if [ "${DATABASE}" == "oracle" ]; then
                    
                    sed -i "s|export SELENIUMTEST_RESTART_DB=false|export SELENIUMTEST_RESTART_DB=true|" .testselenium.env      
                    
                fi
                
                make MSGORIENTACAO=n tests-functional-full
                
                
                
                """  
              
            }        
        }
    }
}