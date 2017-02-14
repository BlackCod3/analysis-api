<?php


namespace WB\AnalysisBundle\Handler;

use MeasureRecorder\Model\Measure;
use MeasureRecorder\Client;
use Symfony\Component\HttpFoundation\Response;


class MeasureHandler
{
    const MANDATORY_FIELDS = array('v', 't', 'wct', 'wui', 'wuui', 'ec', 'ea', 'tid', 'ds', 'sn', 'an');
    const MEASURE_TYPE = array('pageview', 'screenview', 'event');

    # list could be mocked in mongo... or here
    # not sure if wui or wuui
    const USER_WUI_MOCK = array('john-f', 'robert-k', 'richard-z', 'ben-x', 'max-d', 'dim-sum', 'foo-bar');

    const V = 1;


    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Measure
     */
    protected $measure;

    public function __construct($client, $meta){
        $this->client = $client;
        $this->measure = new Measure($meta);
    }

    public function isRedundant(){
        $result = $this->client->getMeasure(
            array('creationDate' =>  $this->measure->getCreationDate(),
                'meta.t' => $this->measure->getMeta('t')
            )
        );

        if(!$result){
            return false;
        }

        return true;
    }

    public  function hasMandatoryFields(){

        foreach (self::MANDATORY_FIELDS as $field){
            if(!array_key_exists($field, $this->measure->getMetas())){
                return false;
            }
        }

        return true;
    }

    public function userExists(){

        if(!in_array($this->measure->getMeta('wui'), self::USER_WUI_MOCK)){
            return false;
        }

        return true;
    }

    public function isAcceptableQT($maxQt){

        if($this->measure->getMeta('qt') > $maxQt){
            return false;
        }

        return true;
    }

    public function isAvailableApiVersion(){

        if($this->measure->getMeta('v') != self::V){
            return false;
        }

        return true;
    }

    public function serveRequest($maxQt){
        $response = new Response( 'OK ! Measure successfully recorder', 200);

        if($this->isRedundant()){
            return new Response( 'Same measure already recorded in no later than a second', 403);
        }

        if(!$this->hasMandatoryFields()){
            return new Response( 'Bad request: Missing values', 400);
        }

        if(!$this->userExists()){
            return new Response( 'Resource not found: User not found', 404);
        }

        if(!$this->isAcceptableQT($maxQt)){
            return new Response( 'Important queue time for cure=rent measure, rejecting it', 403);
        }

        if(!$this->isAvailableApiVersion()){
            return new Response( 'Important queue time for cure=rent measure, rejecting it', 505);
        }

        try{
            $this->client->saveMeasure($this->measure);
        }catch(\Exception $e){
            return new Response( 'Internal error', 505);
        }

        return $response;
    }

    public static function prepareQuery($query){
        $prepared = array();

        foreach ($query as $key => $value){
            $prepared["meta.".$key] = $value;
        }

        return $prepared;
    }

    public static function formatMeasure($grossResult){
        $measures = array();

        foreach($grossResult as $key => $value){
            $record = array();
            $record['_id'] = (string)$value->_id;
            $record['creationDate'] = $value->creationDate->toDateTime()->format('Y-m-d H:i:s') ;
            $record['meta'] = $value->meta;

            $measures[] = $record;
        }

        return $measures;
    }
}