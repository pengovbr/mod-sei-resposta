/*

Pipeline Jenkins que automatiza checkout, up e roda os testes 1x apenas
No seu cluster selenium vc precisa de um agente com a label SUPER-RESPOSTA, e tb instalado o
o docker e docker-compose

*/

pipeline {
    agent {
        node{
            label "SUPER-RESPOSTA"
        }
    }

    parameters {
        choice(
            name: 'database',
            choices: "mysql\noracle\nsqlserver",
            description: 'Qual o banco de dados' )
    	  string(
    	      name: 'urlGitSuper',
    	      defaultValue:"github.com:supergovbr/super.git",
    	      description: "Url do git onde encontra-se o Super")
        string(
            name: 'credentialGitSuper',
            defaultValue:"gitcredsuper",
            description: "Jenkins Credencial do git onde encontra-se o Super")
	      string(
	          name: 'branchGitSuper',
	          defaultValue:"main",
	          description: "Branch/Tag do git onde encontra-se o Super")
        string(
            name: 'urlGit',
            defaultValue:"github.com:spbgovbr/mod-sei-resposta.git",
            description: "Url do git onde encontra-se o módulo")
        string(
            name: 'credentialGit',
            defaultValue:"gitcredmoduloresposta",
            description: "Jenkins Credencial do git onde encontra-se o módulo")
	      string(
	          name: 'branchGit',
	          defaultValue:"master",
	          description: "Branch/Versao do git onde encontra-se módulo")

    }

    stages {

        stage('Inicializar Job'){
            steps {

                script{
                    DATABASE = params.database
                    GITURL = params.urlGit
					          GITCRED = params.credentialGit
					          GITBRANCH = params.branchGit
                    GITURLSUPER = params.urlGitSuper
					          GITCREDSUPER = params.credentialGitSuper
					          GITBRANCHSUPER = params.branchGitSuper
                    SUPERLOCATION = "${WORKSPACE}/super"

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

        stage('Checkout-Super'){
            
            steps {

                dir('super'){

                    sh """
                    git config --global http.sslVerify false
                    """

                    git branch: 'main',
                        credentialsId: GITCREDSUPER,
                        url: GITURLSUPER
                
                    sh """
                    git checkout ${GITBRANCHSUPER}
                    ls -l
                    """
                    
                    script {
                        if (fileExists("src")){
                          println "Achei"
                            SUPERLOCATION = "${WORKSPACE}/super/src"
                        }else{println "nachei"}
                    }

                }
                
            }
        }
        
        stage('Checkout-Modulo'){
            
            steps {
                dir('modulo'){
                    sh """
                    git config --global http.sslVerify false
                    """

                    git branch: 'master',
                        credentialsId: GITCRED,
                        url: GITURL

                    sh """
                    git checkout ${GITBRANCH}
                    ls -l
                
                    make destroy
                    """

                }
            }
        }


        stage('Build Env - Run Tests'){

            steps {
                dir('modulo'){
                  
                  sh """
                  ls
                  cd ${SUPERLOCATION}
                  git checkout ${GITBRANCHSUPER}
                  cd -
                  sed -i "s|SEI_PATH=../../../../|SEI_PATH=${SUPERLOCATION}|" .env
                  
                  # subir e parar o super para construir o arquivo de config
                  # necessario 2x pois no Vagrant antigo ele persiste o ConfiguracaoSEI.php e na segunda o ConfiguracaoSEI.php~
                  # so depois q tiver os 2 arquivos posso alterar sem q o entrypoint o altere automaticamente
              
                  make up
                  make destroy
                  make up
                  make destroy
              
                  # habilitar o modulo no config
              
                  sed -i "s|'Modulos' => array(|'Modulos' => array( 'MdRespostaIntegracao' => 'mod-sei-resposta',|g" ${SUPERLOCATION}/sei/config/ConfiguracaoSEI.php
                  
                  ls -l ${SUPERLOCATION}/sei/config/
                  cat ${SUPERLOCATION}/sei/config/ConfiguracaoSEI.php
                  
                  rm -rf .env
                  rm -rf .testselenium.env
                  
                  make base="${DATABASE}" config
                  make .testselenium.env

                  sed -i "s|SEI_PATH=../../../../|SEI_PATH=${SUPERLOCATION}|" .env
                
                  make up
                  make install
                  make MSGORIENTACAO=n tests-functional-full



                  """
                
                }
                

            }
        }
    }
    post {
        always {
            dir('modulo'){
              sh """
              docker stop seleniumchrome || true
              make destroy || true
              
              """
            }
        }
    }
}
