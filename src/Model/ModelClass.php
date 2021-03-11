<?php


namespace App\Model;


use App\Lib\LibCommonClass;
use App\Lib\LibGetClass;
use Doctrine\DBAL\ParameterType;
use Exception;
use PDO;

class ModelClass
{
  private $dbConnection, $libGetClass, $libCommonClass;

  public function __construct(DataBaseAdapterClass $dataBaseAdapterClass, LibGetClass $libGetClass, LibCommonClass $libCommonClass)
  {
    //$this->dbConnection = $dbConnection;
    $this->dbConnection = $dataBaseAdapterClass->getDbConnection();
    $this->libGetClass = $libGetClass;
    $this->libCommonClass = $libCommonClass;
  }

  public function getRecList($sql, $voyId)
  {

    $retArray = $this->dbConnection->fetchAllAssociative($sql, array($voyId), array(PDO::PARAM_INT));
    $retArray = $this->libCommonClass->setNumericTypes($retArray);
    return $retArray; //array('reportId' => intval($reportId), 'errStr' => $errStr, 'sql' => $sql);
  }

  public function eovReportId($sqlCommon, $voyId, $reportType, $reportId)
  {
    $errStr = "eovReportId::: ";
    $exceptionCode = 0;

    try {
      if ($reportId == 0) {
        //  trim: replace "\n" with ""
        $sqlCommon['get_voysum_rec_count'] = str_replace("\n", '', $sqlCommon['get_voysum_rec_count']);
        $sql = str_replace('?', '%s', $sqlCommon['get_voysum_rec_count']);
        $sql = vsprintf($sql, array($voyId, "'" . $reportType . "'"));

        $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['get_voysum_rec_count'], array($voyId, $reportType), array(PDO::PARAM_INT, PDO::PARAM_STR));
        $reportId = $retArray[0]["reccount"] + 1;
      } else {
        //  trim: replace "\n" with ""
        $sqlCommon['check_voysum_rec'] = str_replace("\n", '', $sqlCommon['check_voysum_rec']);
        $sql = str_replace('?', '%s', $sqlCommon['check_voysum_rec']);
        $sql = vsprintf($sql, array($voyId, "'" . $reportType . "'", $reportId));

        $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['check_voysum_rec'], array($voyId, $reportType, $reportId), array(PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT));
        $reportId = (($retArray[0]["reccount"] == 1) ? $reportId : 0);
      }
      $errStr .= (($reportId) ? "Success. " : "No Record created with this ID in 'voyage_summary' table. ");
    } catch (Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    //return json_encode( array('reportId' => $reportId, 'errStr' => $errStr, 'sql' => $sql) );
    return array('reportId' => intval($reportId), 'errStr' => $errStr, 'sql' => $sql);
  }
  //##########################################################################################

  public function getCpgwxArray($sqlCommon, $voyId)
  {
    $errStr = "getCpgwxJson::: ";
    $retCode = 0;
    $exceptionCode = 0;
    $cpGwxArray = null;

    try {
      $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['get_cp_gwx'], array($voyId), array(PDO::PARAM_INT));
      //$cpgwxJson = $retArray[0]["cpgwx_json"];
      $cpGwxArray = $retArray[0];
      unset($cpGwxArray['cpgoodwxid']);

      $cpGwxArray = $this->libCommonClass->setNumericTypes($cpGwxArray);
      $errStr .= " Success.";
      $retCode = 1;
    } catch (\Doctrine\DBAL\Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('cpGwxArray' => $cpGwxArray, 'retCode' => $retCode, 'errStr' => $errStr);
  }

  public function setVoysumRecord($sqlCommon, $setFieldsStr, $voyId, $reportType, $reportId)
  {

    $errStr = "setSumrecord::: ";
    $sql = "";
    $count = 0;
    $retCode = 0;
    $exceptionCode = 0;

    try {
      //  trim: replace "\n" with ""
      $sqlCommon['set_voysum'] = str_replace("\n", '', $sqlCommon['set_voysum']);
      $sql = str_replace('?', '%s', $sqlCommon['set_voysum']);
      $sql = vsprintf($sql, array($setFieldsStr, $voyId, "'" . $reportType . "'", $reportId));

      //$count = $this->dbConnection->executeStatement( $sqlCommon['set_voysum'], array($setFieldsStr, $voyId, $reportType, $reportId), array(PDO::PARAM_STR,  PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT));
      $count = $this->dbConnection->executeStatement( $sql);

      $errStr .= " Success.";
      $retCode = 1;
    }
    catch (Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('delRecordCount' => $count, 'retCode' => $retCode, 'exceptionCode' => $exceptionCode, 'errStr' => $errStr, 'sql' => $sql);
  }

  public function delSumRecord($sqlCommon, $voyId, $reportType, $reportId)
  {
    $errStr = "delSumrecord::: ";
    $retCode = 0;
    $count = 0;
    $retArray = null;
    $exceptionCode = 0;
    $voySumJson = null;
    $voysumArray = null;
    $sql = "";

    try {
      //  trim: replace "\n" with ""
      $sqlCommon['delete_voy_sum'] = str_replace("\n", '', $sqlCommon['delete_voy_sum']);
      $sql = str_replace('?', '%s', $sqlCommon['delete_voy_sum']);
      $sql = vsprintf($sql, array($voyId, "'" . $reportType . "'", $reportId));

      $count = $this->dbConnection->executeStatement( $sqlCommon['delete_voysum'], array($voyId, $reportType, $reportId), array(PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT));

      $errStr .= " Success.";
      $retCode = 1;
    }
    catch (Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('delRecordCount' => $count, 'retCode' => $retCode, 'exceptionCode' => $exceptionCode, 'errStr' => $errStr, 'sql' => $sql);
  }


  public function getVoysumJson($sqlCommon, $voyId, $reportType, $reportId)
  {
    $errStr = "getVoysumJson::: ";
    $retCode = 0;
    $retArray = null;
    $exceptionCode = 0;
    $voySumJson = null;
    $voysumArray = null;
    $sql = "";

    try {
      //  trim: replace "\n" with ""
      $sqlCommon['get_voysum_json'] = str_replace("\n", '', $sqlCommon['get_voysum_json']);
      $sql = str_replace('?', '%s', $sqlCommon['get_voysum_json']);
      $sql = vsprintf($sql, array($voyId, "'" . $reportType . "'", $reportId));

      // ======>   If result is gotten as JSON - "json_decode" applyed to this result will give us "POSTGRES ARRAY" elements as "PHP ARRAYS"
      $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['get_voysum_json'], array($voyId, $reportType, $reportId), array(PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT));

      // ======>   If result is gotten as ARRAY - this result will give us "POSTGRES ARRAY" elements as "PHP STRINGS"
      //$retArray = $this->dbConnection->fetchAllAssociative( $sqlCommon['get_voysum_array'], array($voyId, $reportType, $reportId), array(\PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_INT));
      //$voysumArray = $retArray[0];

      $voySumJson = $retArray[0]["voysum_json"];
      $errStr .= " Success.";
      $retCode = 1;
    }
    catch (\Doctrine\DBAL\Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('voySumJson' => $voySumJson, 'retCode' => $retCode, 'exceptionCode' => $exceptionCode, 'errStr' => $errStr, 'sql' => $sql);
  }

  public function getCpText($sqlCommon, $voyId)
  {
    $errStr = "getSubReports::: ";
    $retCode = 0;
    $exceptionCode = 0;
    $retJson = null;
    $retArray = null;

    try {
      //  trim: replace "\n" with ""
      $sqlCommon['get_cp_text'] = str_replace("\n", '', $sqlCommon['get_cp_text']);
      $sql = str_replace('?', '%s', $sqlCommon['get_cp_text']);
      $sql = vsprintf($sql, array($voyId));

      $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['get_cp_text'], array($voyId), array(PDO::PARAM_INT));

      $retJson = str_replace('"', '\'', json_encode($retArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      $errStr .= " Success.";
      $retCode = 1;
    } catch (\Doctrine\DBAL\Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('subRepArray' => $retArray, 'retCode' => $retCode, 'errStr' => $errStr, 'sql' => $sql);
  }



  public function getSubReports($sqlCommon, $voyId)
  {
    $errStr = "getSubReports::: ";
    $retCode = 0;
    $exceptionCode = 0;
    $retJson = null;
    $retArray = null;

    try {
      //  trim: replace "\n" with ""
      $sqlCommon['get_sub_reports'] = str_replace("\n", '', $sqlCommon['get_sub_reports']);
      $sql = str_replace('?', '%s', $sqlCommon['get_sub_reports']);
      $sql = vsprintf($sql, array($voyId));

      $retArray = $this->dbConnection->fetchAllAssociative($sqlCommon['get_sub_reports'], array($voyId), array(PDO::PARAM_INT));

      $retJson = str_replace('"', '\'', json_encode($retArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      $errStr .= " Success.";
      $retCode = 1;
    } catch (\Doctrine\DBAL\Exception $e) {
      $exceptionCode = $e->getCode();
      $errStr .= ("Error:: line: " . $e->getLine() . ",  Error: '" . $e->getMessage() . "', exceptionCode: " . $exceptionCode);
    }
    return array('subRepArray' => $retArray, 'retCode' => $retCode, 'errStr' => $errStr, 'sql' => $sql);
  }
}
