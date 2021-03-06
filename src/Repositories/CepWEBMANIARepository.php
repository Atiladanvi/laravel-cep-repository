<?php

namespace Atiladanvi\CepRepository\Repositories;

use Atiladanvi\CepRepository\Clients\WEBMANIAClient;

class CepWEBMANIARepository extends CepRepositoryAbstract
{
    public function __construct()
    {
        parent::__construct(WEBMANIAClient::class);
    }

    public function transform($data): array
    {
        return [
            'cep' => $data->cep,
            'estado' => $data->uf,
            'municipio' => $data->cidade,
            'bairro' => $data->bairro,
            'logradouro' => $data->endereco,
        ];
    }
}
