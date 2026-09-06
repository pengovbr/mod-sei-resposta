JSON_FMT='{"name":"Módulo Resposta", "version": "%s", "compatible_with": [%s]}'
VERSAO_MODULO=$(grep 'const VERSAO_MODULO' src/MdRespostaIntegracao.php | cut -d'"' -f2)
VERSOES=$(sed -n -e "/COMPATIBILIDADE_MODULO_SEI = \[/,/;/ p" src/MdRespostaIntegracao.php \
           | sed -e '1d;$d' | sed -e '/\/\//d' \
           | sed -e "s/'/\"/g"| tr -d '\n'| tr -d ' ')

printf "$JSON_FMT" "$VERSAO_MODULO" "$VERSOES" > compatibilidade.json
