<?php

namespace Atiladanvi\CepRepository\Repositories;

use Atiladanvi\CepRepository\Address;
use Atiladanvi\CepRepository\AddressFactory;
use Atiladanvi\CepRepository\Contracts\CepRepositoryContract;
use Atiladanvi\CepRepository\Contracts\Transformable;
use Atiladanvi\CepRepository\Resources\AddressTransformer;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;


abstract class CepRepositoryAbstract implements CepRepositoryContract, Transformable
{
    /**
     * Client instance
     *
     * @var \Atiladanvi\CepRepository\Contracts\ClientContract|mixed
     */
    protected $client;

    /**
     * Adress transformer
     *
     * @var \Atiladanvi\CepRepository\Resources\AddressTransformer
     */
    protected $addressTransform;

    /**
     * Data manager instance
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * @var string|null
     */
    private $responseContents;

    /**
     * CepRepositoryAbstract constructor
     *
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = app($client);
        $this->addressTransform = app(AddressTransformer::class);
        $this->manager =  new Manager();
        $this->manager->setSerializer(app(ArraySerializer::class));
    }

    /**
     * Get de address by cep
     *
     * @param string $cep
     * @return \Atiladanvi\CepRepository\Address|null
     * @throws \Exception
     */
    public function get(string $cep): ?Address
    {
        try{
            $this->responseContents = $this->client
                ->setCep($cep)
                ->request()
                ->getBody()
                ->getContents();
        }catch (BadResponseException | \Exception $e) {
            Log::error("Error to get cep: $cep: Message: {$e->getMessage()}");
            return null;
        }

        return AddressFactory::create((object)$this->createData());
    }

    /**
     * Create the data to transform
     *
     * @return array|null
     */
    protected function createData(): ?array
    {
        $data = $this->parseContents($this->responseContents);

        return $this->manager
            ->createData(
                new Item((object)$this->transform($data), $this->addressTransform)
            )->toArray();
    }

    /**
     * Transform method abstract
     *
     * @param $data
     * @return array
     */
    abstract public function transform($data): array;

    /**
     * Parse response content
     *
     * @param $responseContent
     * @return mixed
     */
    protected function parseContents($responseContent)
    {
        return json_decode($responseContent);
    }
}
