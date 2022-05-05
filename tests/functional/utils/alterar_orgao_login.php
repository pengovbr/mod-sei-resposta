<?php
require_once '/opt/sip/web/Sip.php';
$conexao = BancoSip::getInstance();
$conexao->abrirConexao();
$conexao->executarSql("update orgao set sin_autenticar='N'");

echo "Orgaos Alterados com sucesso...";

?>