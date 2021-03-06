<?php

namespace Atiladanvi\CepRepository\Repositories;

use Atiladanvi\CepRepository\Clients\CORREIROSClient;
use SimpleXMLElement;

class CepCORREIROSRepository extends CepRepositoryAbstract
{
    public function __construct()
    {
        parent::__construct(CORREIROSClient::class);
    }

    protected function parseContents($responseContents)
    {
        return (object) (array) $this->readXml($responseContents);
    }

    private function readXml($xml)
    {
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);
        $xml = new SimpleXMLElement(utf8_encode($xml));

        return $xml->xpath('//soapBody')[0]
            ->xpath('//ns2consultaCEPResponse')[0]
            ->xpath('//return')[0];
    }

    public function transform($data): array
    {
        return [
            'cep' => $data->cep,
            'estado' => $data->uf,
            'municipio' => $data->cidade,
            'bairro' => $data->bairro,
            'logradouro' => $data->end,
        ];
    }
}
