<?php


namespace App\Lib;


class LibPostClass
{
  public function spreadDataAmongLegs($dataArray)
  {
    $cpTermsArray = $dataArray["cpterms"];

    foreach($cpTermsArray as $key => &$cpRow)
    {
      $legIndexArray = $cpRow["legindex"];
      $timeTotalArray = array_intersect_key($dataArray["timetotal"], $legIndexArray);
        $timeTotal = array_sum($timeTotalArray);
      $timeGwArray = array_intersect_key($dataArray["timegw"], $legIndexArray);
        $timeGw = array_sum($timeGwArray);

      $wxFacTotalWeighted = $cpRow["wxfactotal"] * $timeTotal;
      $wxFacGwWeighted = $cpRow["wxfacgw"] * $timeGw;
      $wxFacTotalArray = [];
      foreach($legIndexArray as $legKey => $legRow)
      {
        $wxFacTotalArray[] = $dataArray["timetotal"][$legRow];

      }


      $timeGwx = 0.0;
      $wxFacTotal = $wxFacGwx = 0.0;

    }

    return $cpTermsArray;
  }
}