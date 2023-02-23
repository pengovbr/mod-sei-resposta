<?php

namespace Tests\Functional\Fixtures;

use InfraRN;

abstract class Fixture extends InfraRN
{
    abstract protected function cadastrar($dados);

    protected function cadastrarInternoControlado($parametros){
        $dto = $this->cadastrar($parametros["dados"]);

        if (isset($parametros["callback"])) {
            $parametros["callback"]($dto);
        }

        return $dto;
    }

    public function carregar($dados = null, $callback = null){
        $dados = $dados ?: [];
        return $this->cadastrarInterno([
            dados => $dados,
            callback => $callback
        ]);
    }

    public function carregarVarios($dados = null, $quantidade = 1){
        $resultado = [];
        for ($i=0; $i < $quantidade; $i++) {
            $resultado[] = $this->carregar($dados);
        }
        return $resultado;
    }
}
