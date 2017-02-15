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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class MeasureController extends FOSRestController
{


    /**
     * @ApiDoc(
     *     resource=true,
     *    description="Get all measures corresponding to the given criterion",
     *    output= { "class"=Measure::class, "collection"=true, "groups"={"measure"} }
     * )
     * @Rest\Get("/collect")
     *
     * @QueryParam(name="t", requirements="(pageview|event|screenview)", default="", description="Possible values : pageview, screenview, event", nullable=false)
     * @QueryParam(name="dl", requirements="", default="", description="A valid URI reprensenting the current page")
     * @QueryParam(name="dr", requirements="", default="", description="A valid URI representing the traffic source")
     * @QueryParam(name="wct", requirements="(profile|recruiter|visitor|wizbii_employee)", default="", description="One value from : profile, recruiter, visitor and wizbii_employee")
     * @QueryParam(name="wui", requirements="", default="", description="For profile, recruiter, wizbii_employee and visitor,  their slug. For visitor, the value stored in visitor cookie")
     * @QueryParam(name="wuui", requirements="", default="", description="For profile, recruiter, wizbii_employee and visitor, the value stored in uniqUserId cookie")
     * @QueryParam(name="ec", requirements="", default="", description="Specifies the event category. Must not be empty")
     * @QueryParam(name="ea", requirements="", default="", description="Specifies the event action. Must not be empty.")
     * @QueryParam(name="el", requirements="", default="", description="Specifies the event label.")
     * @QueryParam(name="ev", requirements="\d+", default="", description="Specifies the event value. Values must be a non-negative integer.")
     * @QueryParam(name="tid", requirements="", default="", description="The tracking ID / web property ID. The format is UA-XXXX-Y. All collected data is associated by this ID.")
     * @QueryParam(name="ds", requirements="(web|apps|backend)", default="", description="Indicates the data source of the hit. Possible values are : web, apps and backend")
     * @QueryParam(name="sn", requirements="", default="", description="This parameter is optional on web properties, and required on mobile properties for screenview hits, where it is used for the 'Screen Name' of the screenview hit.")
     * @QueryParam(name="an", requirements="", default="", description="Specifies the application name")
     */
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


    /**
     * @ApiDoc(
     *    resource=true,
     *    description="Record a measure",
     *    input="json/array",
     *	  statusCodes={
     *         201="OK ! Measure successfully recorder",
     *         400= {
     *            "Bad request: Missing values",
     *            "Bad request"
     *         },
     *         404="Resource not found: User not found",
     *         409={
     *           "Same measure already recorded in no later than a second",
     *           "Important queue time for cure=rent measure, rejecting it"
     *         },
     *         501="Api version requested not available",
     *         505="Internal error"
     *     }
     * )
     *
     * @Rest\Post("/collect")
     */
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
                return new Response( 'Bad request', 400);
            }
        }catch(\Exception $e){
            return  new Response( 'Internal Error', 500);
        }

        return $result;
    }
}