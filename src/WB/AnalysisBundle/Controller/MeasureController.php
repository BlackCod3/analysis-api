<?php


namespace WB\AnalysisBundle\Controller;

use MeasureRecorder\Config\ConfigDev;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MeasureRecorder\Config\Config;
use MeasureRecorder\Model\Measure;
use MeasureRecorder\Client;
use Symfony\Component\HttpKernel\Exception\HttpException;
use WB\AnalysisBundle\Handler\MeasureHandler;


class MeasureController extends FOSRestController
{


    public function getMeasureAction(Request $request)
    {
        $query = $request->query->all();

        print_r($this->getParameter('max_qt'));

        /**
         * @var Client
         */
        $recorder = $this->get('wb_analysis.recorder')->getClient();

        $preparedQuery = MeasureHandler::prepareQuery($query);
        $result = $recorder->getMeasure($preparedQuery);

        $result = MeasureHandler::formatMeasure($result);

        return new JsonResponse($result);
    }


    public function postMeasureAction(Request $request)
    {
        try {
            $params = json_decode($request->getContent(), true)[0];

            /**
             * @var Client
             */
            $recorder = $this->get('wb_analysis.recorder')->getClient();
            $maxQt = $this->getParameter('max_qt');

            if(is_array($params)){
                $measureHandler = new MeasureHandler($recorder, $params);
                $result = $measureHandler->serveRequest($maxQt);
            }else{
                $result = array(
                    'code' => 400,
                    'Message' =>'Bad Request'
                );
            }
        }catch(\Exception $e){
            $result = array(
                'code' => 500,
                'Message' =>'Internal Error'
            );
        }

        return new JsonResponse($result);
    }
}