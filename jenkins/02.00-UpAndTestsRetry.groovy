/*

Pipeline Jenkins que automatiza o job 01.00
Em caso de erro ele faz nova tentativa de execucao limitada a quantidade de tentativas informada
na execucao
Util para rodar de forma assincrona pois os testes eventualmente possuem falso positivo
No seu cluster selenium vc precisa de um agente com a label SUPERGD e o user do agente precisa ter permissao de sudo,
alem do docker e docker-compose

*/

pipeline {
    agent any


    parameters {
        choice(
            name: 'database',
            choices: "mysql\noracle\nsqlserver",
            description: 'Qual o banco de dados' )
        string(
            name: 'urlGit',
            defaultValue:"https://github.com/spbgovbr/mod-sei-resposta.git",
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
	          defaultValue:"~/super/FonteSuper",
	          description: "Localizacao do fonte do Super no servidor onde vai rodar o job")
	      string(
	          name: 'qtdTentativas',
	          defaultValue:"5",
	          description: "Quantidade de tentativas caso o teste falhe")

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
                    QTDTENTATIVAS = params.qtdTentativas

                    if ( env.BUILD_NUMBER == '1' ){
                        currentBuild.result = 'ABORTED'
                        warning('Informe os valores de parametro iniciais. Caso eles n tenham aparecido faça login novamente')
                    }

                }

                sh """
                echo ${WORKSPACE}

                """
            }
        }

        stage('Build Env - Run Tests'){

            steps {

                retry(QTDTENTATIVAS){

                    build job: '01.00-UpAndTest.groovy',
                        parameters:
                            [
                                string(name: 'database', value: DATABASE),
                                string(name: 'urlGit', value: GITURL),
                                string(name: 'credentialGit', value: GITCRED),
                                string(name: 'branchGit', value: GITBRANCH),
                                string(name: 'sourceSuperLocation', value: SUPERLOCATION)
                            ], wait: true
                }

            }

        }

    }
}
