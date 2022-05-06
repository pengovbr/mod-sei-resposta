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
        string(
            name: 'generalParams',
            defaultValue: 'VAR_SUPER_VERSAO=v4.0.3.1,VAR_RESPOSTA_VERSAO=master,database=mysql;VAR_SUPER_VERSAO=v4.0.3.1,VAR_RESPOSTA_VERSAO=master,database=sqlserver;VAR_SUPER_VERSAO=v4.0.3.1,VAR_RESPOSTA_VERSAO=master,database=oracle;',
            description: 'Informe como no exemplo todas as versoes q deseja testar (separeted by ",", splited by ";")')
        string(
            name: 'urlGit',
            defaultValue:"https://github.com/spbgovbr/mod-sei-resposta.git",
            description: "Url do git onde se encontra o módulo")
        string(
            name: 'credentialGit',
            defaultValue:"githubcred",
            description: "Jenkins Credencial do git onde se encontra o módulo")
	      string(
	          name: 'sourceSuperLocation',
	          defaultValue:"~/super/FonteSuper",
	          description: "Localizacao do fonte do Super no servidor onde vai rodar o job")
	      string(
	          name: 'qtdTentativas',
	          defaultValue:"3",
	          description: "Quantidade de tentativas caso o teste falhe")
    }

    stages {

        stage('Inicializar Job'){
            steps {
                script{
                    
                    if ( env.BUILD_NUMBER == '1' ){
                        currentBuild.result = 'ABORTED'
                        warning('Informe os valores de parametro iniciais. Caso eles n tenham aparecido faça login novamente')
                    }
                    
                    GENERALPARAMS = params.generalParams
                    GITURL = params.urlGit
					          GITCRED = params.credentialGit
					          GITBRANCH = params.branchGit
                    SUPERLOCATION = params.sourceSuperLocation
                    QTDTENTATIVAS = params.qtdTentativas
                    
                    arrGeneral = GENERALPARAMS.split(';')
                    
                }

                sh """
                echo ${WORKSPACE}

                """
            }
        }

        stage('Call BuildEnvironment Job'){
            steps {
                script {
                    
                    def paramValue
                    def super_versao
                    def mod_resposta_versao
                    def bd
                    
                    for (int i = 0; i < arrGeneral.length; i++) {
                        paramValue = arrGeneral[i].split(',')
                        super_versao = paramValue[0].split('=')[1]
                        mod_resposta_versao = paramValue[1].split('=')[1]
                        bd = paramValue[2].split('=')[1]

                        stage("Montando Ambiente Rodando Testes ${paramValue[0]} / ${paramValue[1]} / ${paramValue[2]}" ) {

                            retry(QTDTENTATIVAS){

                                build job: '01.00-UpAndTest.groovy',
                                    parameters:
                                        [
                                            string(name: 'database', value: bd),
                                            string(name: 'urlGit', value: GITURL),
                                            string(name: 'credentialGit', value: GITCRED),
                                            string(name: 'branchGit', value: mod_resposta_versao),
                                            string(name: 'sourceSuperLocation', value: SUPERLOCATION)
                                        ], wait: true
                            }


                        }


                    }

                }
            }

        }

    }

}
