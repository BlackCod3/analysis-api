<?php


namespace WB\AnalysisBundle\Service;


use MeasureRecorder\Client;

class RecorderClientService
{

    /**
     * @var Client
     */
    protected $client;

    public function __construct($env){
        $this->client = new Client($env);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}