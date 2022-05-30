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
            defaultValue: 'VAR_SUPER_VERSAO=4.0.3.3,VAR_RESPOSTA_VERSAO=master,database=mysql;VAR_SUPER_VERSAO=4.0.3.3,VAR_RESPOSTA_VERSAO=master,database=sqlserver;VAR_SUPER_VERSAO=4.0.3.3,VAR_RESPOSTA_VERSAO=master,database=oracle;',
            description: 'Informe como no exemplo todas as versoes q deseja testar (separeted by ",", splited by ";")')
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
                    GITURLSUPER = params.urlGitSuper
					          GITCREDSUPER = params.credentialGitSuper
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

                            warnError('Erro no build!'){

                                retry(QTDTENTATIVAS){

                                    build job: '01.00-UpAndTest.groovy',
                                        parameters:
                                            [
                                                string(name: 'database', value: bd),
                                                string(name: 'urlGit', value: GITURL),
                                                string(name: 'credentialGit', value: GITCRED),
                                                string(name: 'branchGit', value: mod_resposta_versao),
                                                string(name: 'urlGitSuper', value: GITURLSUPER),
                                                string(name: 'credentialGitSuper', value: GITCREDSUPER),
                                                string(name: 'branchGitSuper', value: super_versao),
                                                string(name: 'branchGit', value: mod_resposta_versao),
                                            ], wait: true
                                }

                            }
                            
                        }


                    }

                }
            }

        }

    }

}
