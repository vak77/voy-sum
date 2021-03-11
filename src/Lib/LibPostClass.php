<?php


namespace App\Lib;


class LibPostClass
{
  public function spreadDataAmongLegs($dataArray)
  {
    $dataReturnArray = [];

    $voysumFieldArray = [
      "timegw" => "timetotal_gwx_byleg",
      "timetotal" => "timetotal_byleg",
      "timeallowtotal" => "timetotalallow_eov_byleg",
      "spdperftotal" => "perfspeed_byleg", "spdperfgw" => "perfspeed_gwx_byleg",
      "wxfactotal" => "wxfac_byleg", "curfactotal" => "curfac_byleg",
      "wxfacgw" => "wxfac_gwx_byleg", "curfacgw" => "curfac_gwx_byleg",
      "focpval" => "fo_cons_eov_allow_byleg", "docpval" => "do_cons_eov_allow_byleg"
      /*,
      "focpval_" => "fo_cons_allow_gwx_byleg", "docpval_" => "do_cons_allow_gwx_byleg"
      */
    ];

    $voysumFieldRowArray = array_values($voysumFieldArray);

    $cpTermsArray = $dataArray["cpterms"];

    foreach($voysumFieldRowArray as $fieldRow)
      $dataReturnArray[$fieldRow] = [];

    //==========================================================
    foreach($cpTermsArray as $cpKey => $cpRow)
    {
      if(!is_array($cpRow) || (!array_key_exists("legindex", $cpRow)))
        continue;
      $legIndexArray = $cpRow["legindex"];

      foreach($voysumFieldArray as $fieldKey => $fieldRow)
      {
        if(preg_match_all('/focp|docp/', $fieldKey, $matches ) == 1)
        {
          $cpVal = (($cpRow[$fieldKey] < 0.0) ? 0.0 : $cpRow[$fieldKey]);
          foreach ($legIndexArray as $legInd)
            $dataReturnArray[$fieldRow][] = round($dataReturnArray["timetotalallow_eov_byleg"][$legInd] * $cpVal / 24.0, 2);
        }
        else
        {
          $fieldValueOrigTotal = 0.0;

          $ratio = 1.0;
          $matches = null;
          if (preg_match_all('/spd|wxf|cur/', $fieldKey, $matches) == 1)
          {
            if (preg_match_all('/gw/', $fieldKey, $matches) == 1)
            {
              foreach ($legIndexArray as $legInd)
              {
                $fieldValueOrigTotal += $dataArray[$fieldKey][$legInd] * $dataArray["timegw"][$legInd];
              }

              $ratio = (($cpRow["timegw"] && $cpRow[$fieldKey]) ? ($fieldValueOrigTotal / $cpRow["timegw"]) / $cpRow[$fieldKey] : 1.0);
            }
            else
            {
              foreach ($legIndexArray as $legInd)
              {
                $fieldValueOrigTotal += $dataArray[$fieldKey][$legInd] * $dataArray["timetotal"][$legInd];
              }

              $ratio = (($cpRow["timetotal"] && $cpRow[$fieldKey]) ? ($fieldValueOrigTotal / $cpRow["timetotal"]) / $cpRow[$fieldKey] : 1.0);
            }
          }
          else
          {
            foreach ($legIndexArray as $legInd)
            {
              $fieldValueOrigTotal += $dataArray[$fieldKey][$legInd];
            }

            $ratio = ($cpRow[$fieldKey] ? $fieldValueOrigTotal / $cpRow[$fieldKey] : 1.0);
          }

          //foreach ($legIndexArray as $legKey => $legInd)
          foreach ($legIndexArray as $legInd)
          {
            $dataReturnArray[$fieldRow][] = ($ratio ? round($dataArray[$fieldKey][$legInd] / $ratio, 2) : 0.0);
          }
        }
      }
    }

    $dataReturnArray = array_map(function($key, $row) {return $key . " = '{" . implode(',', $row) . "}'"; }, array_keys($dataReturnArray), $dataReturnArray);
    $sqlStr = implode(', ', $dataReturnArray);


    return $sqlStr;
  }
}