<?php

namespace App\Controller;


use App\Lib\LibCommonClass;
use App\Lib\LibGetClass;
use App\Lib\LibPostClass;
use App\Model\DataBaseAdapterClass;
use App\Model\ModelClass;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use function getenv;

/**
 * Class VoySumController
 * @Route("/voysum")
 * @package App\Controller
 */
class VoySumController extends AbstractController
{
  private $dbConnParamArray, $dbConnection, $dbName, $dbHost, $dbPort, $sqlYaml;
  private $guzzleClient, $sumrecEndPoint, $endPointDbHost;

  public function StatusCodeHandling(RequestException $e)
  {
    if ($e->getResponse()->getStatusCode() == '400') {
      //$this->prepare_access_token();
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    } elseif ($e->getResponse()->getStatusCode() == '422') {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    } elseif ($e->getResponse()->getStatusCode() == '500') {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    } elseif ($e->getResponse()->getStatusCode() == '401') {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    } elseif ($e->getResponse()->getStatusCode() == '403') {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    } else {
      $response = json_decode($e->getResponse()->getBody()->getContents());
      return $response;
    }
  }

  //public function __construct( RequestStack $requestStack, KernelInterface $kernel, LibGetClass $libGetClass, DataBaseAdapterClass $dataBaseAdapterClass)
  public function __construct(RequestStack $requestStack, KernelInterface $kernel, DataBaseAdapterClass $dataBaseAdapterClass)
  {
    $this->dbConnection = $this->dbConnParamArray = $this->dbName = $this->dbHost = $this->dbPort = $this->sqlYaml = null;
    $this->guzzleClient = $this->sumrecEndPoint = $this->endPointDbHost = null;

    $sqlDir = $kernel->getProjectDir() . "/config/sql/";
    $this->sqlYaml = Yaml::parseFile($sqlDir . "voy-sum.yaml");

    $dotenv = new Dotenv(true);
    $dotenv->loadEnv($kernel->getProjectDir() . "/.env");

    $this->dbName = $requestStack->getCurrentRequest()->get('dbname') ?: null;
    $dbHostArray = explode(":", $requestStack->getCurrentRequest()->get('dbhost') ?: null);  //    >attributes->get('dbhost') ?: null;

    if ($dbHostArray) {
      $this->dbHost = $dbHostArray[0];
      $this->dbPort = ((count($dbHostArray) > 1) ? $dbHostArray[1] : "5432");
    }

    // A "\" before the beginning of a function represents the Global Namespace
    // or "use <function name>;" <===== among other "use" at the top of the file
    $this->sumrecEndPoint = getenv('SUMREC_ENDPOINT');
    $this->endPointDbHost = getenv('SUMREC_DBHOST');

    $this->guzzleClient = new Client();

    $dataBaseAdapterClass->setConnection($this->dbName, $this->dbHost, $this->dbPort);
    $this->dbConnection = $dataBaseAdapterClass->getDbConnection();
    $dbConnArray =  (array)$this->dbConnection;
    $dbConnJson = json_encode($dbConnArray);
      $dbConnJson = preg_replace("/\\u0000/", '', $dbConnJson);
      $dbConnJson = preg_replace("/\\\\/", '', $dbConnJson);
      $dbConnJson = preg_replace("/DoctrineDBAL/", '', $dbConnJson);
    $dbConnArray = json_decode($dbConnJson,true);

    $this->dbConnParamArray =  $dbConnArray["Connectionparams"];

    try {
      $this->dbConnection->connect();
    }
    catch (Exception $e) {
      $errMessage = str_replace('"', '\'', $e->getMessage()) . " **** " .
        "DB = " . $this->dbConnParamArray['dbname'] .
        ", HOST = " . $this->dbConnParamArray['host'] . ":" . $this->dbConnParamArray['port'];
      $errArray = ['Error' => ["No Connection to the database" => $errMessage]];
      die(json_encode($errArray, JSON_PRETTY_PRINT));
    }
  }

  /**
   * @Route("/reclist/{voyId}", name="reclist", requirements={"voyId"="\d+"})
   * @param ModelClass $modelClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @return Response
   */
  public function getSumRecList(ModelClass $modelClass, LibCommonClass $libCommonClass, $voyId)
  {
    if (!$this->dbConnection)
      //return new Response("No Connection to the database [". $this->dbname . "] on host [" . $this->dbhost . "]");
      return $this->json("No Connection to the database [" . $this->dbName . "] on host [" . $this->dbHost . "]");

    $retArray = $modelClass->getRecList($this->sqlYaml['common']['get_rec_list'], $voyId);
    /*
    $response = new Response();
    $response->setContent(json_encode( $retArray, JSON_UNESCAPED_SLASHES));
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Access-Control-Allow-Origin', '*');
    return $response;
    */
    return $libCommonClass->response($retArray);
  }

  /**
   * @Route("/subreplist/{voyId}", name="subreplist", requirements={"voyId"="\d+"})
   * @param ModelClass $modelClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @return JsonResponse|Response
   */
  public function getSubRepList(ModelClass $modelClass, LibCommonClass $libCommonClass, $voyId)
  {
    if (!$this->dbConnection)
      //return new Response("No Connection to the database [". $this->dbname . "] on host [" . $this->dbhost . "]");
      return $this->json("No Connection to the database [" . $this->dbName . "] on host [" . $this->dbHost . "]");

    $retArray = null;
    $retSubReportsArray = $modelClass->getSubReports($this->sqlYaml['common'], $voyId);
    if ($retSubReportsArray['retCode'])
      $retArray = $retSubReportsArray['subRepArray'];
    else
      $retArray = ['Error' => 'No Sub-Reports'];

    return $libCommonClass->response($retArray);
  }

  /**
   * @Route("/cptext/{voyId}", name="cptext", requirements={"voyId"="\d+"})
   * @param ModelClass $modelClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @return JsonResponse|Response
   */
  public function cptext(ModelClass $modelClass, LibCommonClass $libCommonClass, $voyId)
  {
    if (!$this->dbConnection)
      //return new Response("No Connection to the database [". $this->dbname . "] on host [" . $this->dbhost . "]");
      return $this->json("No Connection to the database [" . $this->dbName . "] on host [" . $this->dbHost . "]");

    $retArray = null;
    $retSubReportsArray = $modelClass->getCpText($this->sqlYaml['common'], $voyId);
    if ($retSubReportsArray['retCode'])
      $retArray = $retSubReportsArray['subRepArray'];
    else
      $retArray = ['Error' => 'No CP Text'];

    return $libCommonClass->response($retArray);
  }
  /**
   * @Route("/getsumrecendpoint", name="getsumrecendpoint")
   * @param LibGetClass $libGetClass
   * @return array|false|string
   */
  public function getSumrecEndPoint(LibCommonClass $libCommonClass)
  {
    return $libCommonClass->response(['sumrecEndPoint' => $this->sumrecEndPoint]);
  }

  /**
   * @Route("/delsumrec/{voyId}/{reportType}/{reportId}", name="delsumrec", requirements={"voyId"="\d+"})
   * @param ModelClass $modelClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @param $reportType
   * @param $reportId
   * @return JsonResponse|Response
   */
  public function delSumRec(ModelClass $modelClass, LibCommonClass $libCommonClass, $voyId, $reportType, $reportId)
  {
    if (!$this->dbConnection)
      return $this->json("No Connection to the database [" . $this->dbName . "] on host [" . $this->dbHost . "]");

    $voyId = intval($voyId);
    if ($reportType === 'MID') { ; }

    $retArray = $modelClass->delSumRecord($this->sqlYaml['common'], $voyId, $reportType, $reportId);

    if ($retArray['retCode'] == 0)
      return $this->json($retArray["errStr"]);
    else
      $delRecordCount = $retArray['delRecordCount'];

    return $libCommonClass->response(['delRecordCount' => $delRecordCount]);
  }


  // /* @Route("/postsumrec/{voyId}/{reportType?'EOV'}/{reportId}", name="postsumrec", methods={"POST", "PUT"}, requirements={"voyId"="\d+"})

  /**
   * @Route("/postsumrec/{voyId}/{reportType?'EOV'}/{reportId?0}/{pointToPoint?}", name="postsumrec", methods={"POST", "POSt"}, requirements={"voyId"="\d+"})
   * @param Request $request
   * @param ModelClass $modelClass
   * @param LibPostClass $libPostClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @param $reportType
   * @param $reportId
   * @return Response
   */
  public function postSumRec(Request $request, ModelClass $modelClass, LibPostClass $libPostClass, LibCommonClass $libCommonClass, $voyId, $reportType, $reportId)
  {
    $postDataJson = $request->getContent(); //"timetotal":[348.7,471.9,16,0,327.1],"timegw":[0,328.6,0,0,39.1];

    $postDataArray = json_decode($postDataJson, true);
    //$postDataJson = json_encode($postDataArray);
    if (!$postDataArray) {
      $postDataJson = preg_replace('/(?<=:)(?=,)/', '"EMPTY"', $postDataJson);
      $postDataArray = json_decode($postDataJson, true);
      $errorArray = ['description' => 'Some element of provided data is NULL. Look for \'EMPTY\''];
      return $libCommonClass->response(['error' => $errorArray, 'data' => $postDataArray]);
    }

    $setFieldsStr = $libPostClass->spreadDataAmongLegs($postDataArray);

    $returnArray = $modelClass->setVoysumRecord($this->sqlYaml['common'], $setFieldsStr, $voyId, $reportType, $reportId);

    return $libCommonClass->response($returnArray);
  }

  /**
   * @Route("/sumrec/{voyId}/{reportType?'EOV'}/{reportId?0}/{pointToPoint?}", name="sumrec", requirements={"voyId"="\d+"})
   * @param ModelClass $modelClass
   * @param LibGetClass $libGetClass
   * @param LibCommonClass $libCommonClass
   * @param $voyId
   * @param $reportType
   * @param $reportId
   * @param $pointToPoint
   * @return Response
   */
  public function getSumRec(ModelClass $modelClass, LibGetClass $libGetClass, LibCommonClass $libCommonClass, $voyId, $reportType, $reportId, $pointToPoint)
  {
    //$argsArray = ['voyageid' => $voyId, 'reporttype' => $reportType, 'reportid' => $reportId];

    if (!$this->dbConnection)
      return $this->json("No Connection to the database [" . $this->dbName . "] on host [" . $this->dbHost . "]");

    $voyId = intval($voyId);
    $pointToPointArray = ($pointToPoint ? explode(",", $pointToPoint) : null);
    if ($reportType === 'MID') {
      //if ((!$pointToPointArray || (count($pointToPointArray) != 2)) && (!$reportId))
      if (!$pointToPointArray || (count($pointToPointArray) != 2))
        return $libCommonClass->response(['Error' => "Report Type is 'MID' but neither 'point-to-point' nor 'report ID' provided"]);
    }

    $retArray = $modelClass->eovReportId($this->sqlYaml['common'], $voyId, $reportType, $reportId);
    if ($retArray['reportId'] == 0)
      return $this->json($retArray["errStr"]);
    //return new Response($retArray["errStr"]);
    else
      $reportId = $retArray['reportId'];

    //Create new or update existing summary record
    $gwxResponse = null;
    $result = null;
    try {
      // ======> can not create new MID report yet because can not extract piece of voyage yet <==============================
      $gwxResponse =
        $this->guzzleClient->get(
          $this->sumrecEndPoint . "?voyageid=" . $voyId . "&reporttype=" . $reportType . "&reportid=" . $reportId . "&dbname=" . $this->dbName . "&dbhost=" . $this->endPointDbHost
        );
      $result = $gwxResponse->getBody()->getContents();
    } catch (GuzzleException $e) {
      $result = $this->StatusCodeHandling($e);
    }

    $retValsArray = null;
    $voySumArray = null;
    $retArray = $modelClass->getVoysumJson($this->sqlYaml['common'], $voyId, $reportType, $reportId);

    //$retCode = $retArray['retCode'];
    if ($retArray['retCode']) {
      $voySumArray = json_decode($retArray['voySumJson'], true);

      $retArray = $modelClass->getCpgwxArray($this->sqlYaml['common'], $voyId);
      if ($retArray['retCode'])
        $voySumArray['cpgwx'] = $retArray['cpGwxArray'];
      else
        $voySumArray['cpgwx'] = null;

      //$voySumArray['wxfacgw'] = array_fill(0, count($voySumArray['wxfacgw']), 0.0); //  <===== make "wxfacgw" all 0.0

      $voySumArray = $libGetClass->getCpConsByLeg($voySumArray);
      $voySumArray = $libGetClass->getPerfValsPerCpTerm($voySumArray);
      $voySumArray = $libGetClass->getPerfValsOverall($voySumArray);

      $argsArray = ['voyageid' => $voyId, 'reporttype' => $reportType, 'reportid' => $reportId];
      //$retInt = array_unshift($voySumArray, $argsArray);
      //$voySumArray = $argsArray + $voySumArray;
      $voySumArray = array_merge($argsArray, $voySumArray);

      /*
      $retArray = $modelClass->getCpgwxArray($this->sqlYaml['common'], $voyId);
      if($retArray['retCode'])
        $voySumArray['cpgwx'] = $retArray['cpGwxArray'];
      */
    }

    //return $libGetClass->response(['Error' => $retArray['errStr']]);
    return $libCommonClass->response($voySumArray);
  }
}
