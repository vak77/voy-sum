<?php


namespace App\Lib;


use Symfony\Component\HttpFoundation\Response;

class LibGetClass
{
  public function getResponse($array)
  {
    $response = new Response();
    $response->setContent(json_encode( $array, JSON_UNESCAPED_SLASHES));
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
  }


  public function getPerfValsOverall($voySumArray)
  {
    $disttotal = $distgw = $timetotal = $timegw = 0.0;
    $fototal = $dototal = $fogw = $dogw = 0.0;
    $wxfactotal = $wxfacgw = $curfactotal = $curfacgw = 0.0;
    $timeallowtotal = $timeallowgw = 0.0;
    $foallowtotal = $doallowtotal = $foallowgw = $doallowgw = 0.0;

    $cpTermsArray = &$voySumArray['cpterms'];
    foreach($cpTermsArray as $key => $row)
    {
      $disttotal += $row['disttotal'];
      $distgw += $row['distgw'];
      $timetotal += $row['timetotal'];
      $timegw += $row['timegw'];

      $wxfactotal += $row['wxfactotal'] * $row['timetotal'];
      $wxfacgw += $row['wxfacgw'] * $row['timegw'];
      $curfactotal += $row['curfactotal'] * $row['timetotal'];
      $curfacgw += $row['curfacgw'] * $row['timegw'];

      $fototal += $row['fototal'];
      $dototal += $row['dototal'];

      $fogw += $row['fogw'];
      $dogw += $row['dogw'];

      $timeallowtotal += $row['timeallowtotal'];
      $timeallowgw += $row['timeallowgw'];

      $foallowtotal += $row['foallowtotal'];
      $doallowtotal += $row['doallowtotal'];
      $foallowgw += $row['foallowgw'];
      $doallowgw += $row['doallowgw'];
    }

    $spdavgtotal = (($timetotal) ? $disttotal / $timetotal : -99.0);
    $spdavggw = (($timegw) ? $distgw / $timegw : -99.0);

    $wxfactotal = (($timetotal) ? $wxfactotal / $timetotal : -99.0);
    $wxfacgw = (($timegw) ? $wxfacgw / $timegw : -99.0);
    $curfactotal = (($timetotal) ? $curfactotal / $timetotal : -99.0);
    $curfacgw = (($timetotal) ? $curfacgw / $timegw : -99.0);

    $spdperftotal = (($timetotal) ? $spdavgtotal - $wxfactotal - $curfactotal : -99.0);
    $spdperfgw = (($timegw) ? $spdavggw - $wxfacgw - $curfacgw : -99.0);

    $overallValsKey = max(array_keys($cpTermsArray)) + 1;
    $cpTermsArray[$overallValsKey] = compact( 'disttotal', 'distgw', 'timetotal', 'timegw',
      'spdavgtotal', 'spdavggw', 'wxfactotal', 'wxfacgw', 'curfactotal', 'curfacgw',
      'spdperftotal', 'spdperfgw', 'timeallowtotal', 'timeallowgw',
      'fototal', 'dototal', 'fogw', 'dogw',
      'foallowtotal', 'doallowtotal', 'foallowgw', 'doallowgw' );

    $cpTermsArray[$overallValsKey] = array_map( function($val) {return round($val, 2);}, $cpTermsArray[$overallValsKey]);

    return $voySumArray;
  }


  public function getPerfValsPerCpTerm($voySumArray)
  {
    $brobcal = 'LDN';
    if($voySumArray['cpgwx'])
      $brobcal = $voySumArray['cpgwx']['brobcal'];

    $cpTermsArray = &$voySumArray['cpterms'];
    foreach($cpTermsArray as $key => &$row)
    {
      $distTotal = $distGw = $timeTotal = $timeGw = 0.0;
      $curFacTotal = $curFacGw = $wxFacTotal = $wxFacGw =  0.0;

      $foTotal = $doTotal = $foGw = $doGw = 0.0;

      $cpSpdNoAbt = $row['cpspdnoabt'];
      $cpSpdAbtFlag = $row['cpspdabtflag'];
      $cpSpdAbtAmt = $row['cpspdabtamt'];
      $cpSpd = $cpSpdNoAbt - (($cpSpdAbtFlag) ? $cpSpdAbtAmt : 0.0);

      $legIndexArray = $row['legindex'];
      foreach($legIndexArray as $legIndex)
      {
        $distTotal += $voySumArray['disttotal'][$legIndex];
        $distGw += $voySumArray['distgw'][$legIndex];
        $timeTotal += $voySumArray['timetotal'][$legIndex];
        $timeGw += $voySumArray['timegw'][$legIndex];

        $ifoTotal = $voySumArray['ifoacttotal'][$legIndex]; $ifoTotal = (($ifoTotal > 0.0) ? $ifoTotal : 0.0);
        $hfoTotal = $voySumArray['hfoacttotal'][$legIndex]; $hfoTotal = (($hfoTotal > 0.0) ? $hfoTotal : 0.0);
        $mdoTotal = $voySumArray['mdoacttotal'][$legIndex]; $mdoTotal = (($mdoTotal > 0.0) ? $mdoTotal : 0.0);
        $mgoTotal = $voySumArray['mgoacttotal'][$legIndex]; $mgoTotal = (($mgoTotal > 0.0) ? $mgoTotal : 0.0);
          $foTotal += round($ifoTotal + $hfoTotal, 2);
          $doTotal += round($mdoTotal + $mgoTotal, 2);
        $ifoGw = $voySumArray['ifoactgw'][$legIndex]; $ifoGw = (($ifoGw > 0.0) ? $ifoGw : 0.0);
        $hfoGw = $voySumArray['hfoactgw'][$legIndex]; $hfoGw = (($hfoGw > 0.0) ? $hfoGw : 0.0);
        $mdoGw = $voySumArray['mdoactgw'][$legIndex]; $mdoGw = (($mdoGw > 0.0) ? $mdoGw : 0.0);
        $mgoGw = $voySumArray['mgoactgw'][$legIndex]; $mgoGw = (($mgoGw > 0.0) ? $mgoGw : 0.0);
          $foGw += round($ifoGw + $hfoGw, 2);
          $doGw += round($mdoGw + $mgoGw, 2);

        $wxFacTotal += round($voySumArray['wxfactotal'][$legIndex] * $voySumArray['timetotal'][$legIndex], 4);
        $wxFacGw += round($voySumArray['wxfacgw'][$legIndex] * $voySumArray['timegw'][$legIndex], 2);
        $curFacTotal += round($voySumArray['curfactotal'][$legIndex] * $voySumArray['timetotal'][$legIndex], 4);
        $curFacGw += round($voySumArray['curfacgw'][$legIndex] * $voySumArray['timegw'][$legIndex], 2);
      }

      $row['disttotal'] = round($distTotal, 2);
      $row['distgw'] = round($distGw, 2);
      $row['timetotal'] = round($timeTotal, 2);
      $row['timegw'] = round($timeGw, 2);

      //$row['spdavgtotal'] = (($timeTotal) ? (round($distTotal / $timeTotal, 2)) : -99.0);
      $row['spdavgtotal'] = (($timeTotal) ? (round($distTotal / $timeTotal, 4)) : -99.0);
      $row['spdavggw'] = (($timeGw) ? (round($distGw / $timeGw, 2)) : -99.0);
      //$row['wxfactotal'] = (($timeTotal) ? round(round($wxFacTotal, 2) / $timeTotal, 2) : -99.0);
      $row['wxfactotal'] = (($timeTotal) ? round($wxFacTotal / $timeTotal, 4) : -99.0);
      $row['wxfacgw'] = (($timeGw) ? round(round($wxFacGw, 2) / $timeGw, 2) : -99.0);
      //$row['curfactotal'] = (($timeTotal) ? round(round($curFacTotal, 2) / $timeTotal, 2) : -99.0);
      $row['curfactotal'] = (($timeTotal) ? round($curFacTotal / $timeTotal, 4) : -99.0);
      $row['curfacgw'] = (($timeGw) ? round(round($curFacGw, 2) / $timeGw, 2) : -99.0);

      $spdPerfTotal = (($timeTotal) ? round($row['spdavgtotal'] - $row['wxfactotal'] - $row['curfactotal'], 4) : -99.0);
      //$spdPerfTotal = (($timeTotal) ? round($row['spdavgtotal'] - $row['curfactotal'], 2) : -99.0);
      $row['spdperftotal'] = $spdPerfTotal;
      //$spdPerfGw = (($timeGw) ? round($row['spdavggw'] - $row['curfacgw'], 2) : -99.0);
      $spdPerfGw = (($timeGw) ? round($row['spdavggw'] - $row['wxfacgw'] - $row['curfacgw'], 2) : -99.0);
      $row['spdperfgw'] = $spdPerfGw;

      $spdVariationTotal = $row['spdavgtotal'] + ($cpSpd - $spdPerfTotal);
      $spdVariationGw = $row['spdavggw'] + ($cpSpd - $spdPerfGw);

      $timeAllowTotal = (($spdVariationTotal > 0.0) ? round($distTotal / $spdVariationTotal, 2) : -99.0);
      $row['timeallowtotal'] = $timeAllowTotal;
      $timeAllowGw = (($spdVariationGw > 0.0) ? round($distGw / $spdVariationGw, 2) : -99.0);
      $row['timeallowgw'] = $timeAllowGw;

      $row['fototal'] = round($foTotal, 3);
      $row['dototal'] = round($doTotal, 3);

      $row['fogw'] = round($foGw, 3);
      $row['dogw'] = round($doGw, 3);

      //....................................................................................
      // Consumption Allowance Computation: For London CP "Allowable Time" is used; for New York CP the smallest "Allowable Time" or "Actual Sailing Time" is used.
      $foCpVal = floatval($row['focpval']);  $foCpAbtFlag = floatval($row['focpabtflag']);  $foCpAbtAmt = floatval($row['focpabtamt']);
      $foCpVal = (($foCpVal > 0.0) ? $foCpVal : 0.0);
      $doCpVal = floatval($row['docpval']);  $doCpAbtFlag = floatval($row['docpabtflag']);  $doCpAbtAmt = floatval($row['docpabtamt']);
      $doCpVal = (($doCpVal > 0.0) ? $doCpVal : 0.0);

      $timeConsAllowTotal = $timeAllowTotal;
      $timeConsAllowGw = $timeAllowGw;
      if($brobcal !== 'LDN')
      {
        $timeConsAllowTotal = (($timeAllowTotal < $timeTotal) ? $timeAllowTotal : $timeTotal);
        $timeConsAllowGw = (($timeAllowGw < $timeGw) ? $timeAllowGw : $timeGw);
      }

      $row['foallowtotal'] = round($foCpVal * $timeConsAllowTotal / 24.0, 3);
      $row['doallowtotal'] = round($doCpVal * $timeConsAllowTotal / 24.0, 3);
      $row['foallowgw'] = round($foCpVal * $timeConsAllowGw / 24.0, 3);
      $row['doallowgw'] = round($doCpVal * $timeConsAllowGw / 24.0, 3);

    }

    return $voySumArray;
  }


  public function getCpConsByLeg($voySumArray)
  {
    $cpconsArray = array_intersect_key($voySumArray, array_flip( preg_grep('/cpcons/', array_keys($voySumArray)) ));
    $voySumArray =  array_diff_key($voySumArray, $cpconsArray);

    $consCodeArray = $cpconsArray[array_values(preg_grep('/code/', array_keys($cpconsArray)))[0]];
    $consValArray = $cpconsArray[array_values(preg_grep('/val/', array_keys($cpconsArray)))[0]];
    $consAbtFlagArray = $cpconsArray[array_values(preg_grep('/abtflag/', array_keys($cpconsArray)))[0]];
    $consAbtAmtArray = $cpconsArray[array_values(preg_grep('/abtamt/', array_keys($cpconsArray)))[0]];

    $cpTermsArray = array();
    $cpSpdUniqueArray = array();
    foreach($voySumArray['legnum'] as $key => $legNum)
    {
      $codeArray = explode('/', $consCodeArray[$key]);
      $valArray = explode('/', $consValArray[$key]);
      $abtflagArray = explode('/', $consAbtFlagArray[$key]);
      $abtamtArray = explode('/', $consAbtAmtArray[$key]);

      $ifoKeysArray = (preg_grep('/IFO|HSFO/', $codeArray));
        $ifoValArray = array_values(array_intersect_key($valArray, $ifoKeysArray));
        $maxIfoVal = floatval(max($ifoValArray));
        $maxIfoKey = array_search($maxIfoVal, $ifoValArray);
      $ifoAbtFlag = intval(array_values(array_intersect_key($abtflagArray, $ifoKeysArray))[$maxIfoKey]);
      $ifoAbtAmt = floatval(array_values(array_intersect_key($abtamtArray, $ifoKeysArray))[$maxIfoKey]);

      $hfoKeysArray = (preg_grep('/HFO|LSFO/', $codeArray));
        $hfoValArray = array_values(array_intersect_key($valArray, $hfoKeysArray));
        $maxHfoVal = floatval(max($hfoValArray));
        $maxHfoKey = array_search($maxHfoVal, $hfoValArray);
      $hfoAbtFlag = intval(array_values(array_intersect_key($abtflagArray, $hfoKeysArray))[$maxHfoKey]);
      $hfoAbtAmt = floatval(array_values(array_intersect_key($abtamtArray, $hfoKeysArray))[$maxHfoKey]);

      $mdoKeysArray = (preg_grep('/MDO/', $codeArray));
        $mdoValArray = array_values(array_intersect_key($valArray, $mdoKeysArray));
        $maxMdoVal = floatval(max($mdoValArray));
        $maxMdoKey = array_search($maxMdoVal, $mdoValArray);
      $mdoAbtFlag = intval(array_values(array_intersect_key($abtflagArray, $mdoKeysArray))[$maxMdoKey]);
      $mdoAbtAmt = floatval(array_values(array_intersect_key($abtamtArray, $mdoKeysArray))[$maxMdoKey]);

      $mgoKeysArray = (preg_grep('/MGO/', $codeArray));
        $mgoValArray = array_values(array_intersect_key($valArray, $mgoKeysArray));
        $maxMgoVal = floatval(max($mgoValArray));
        $maxMgoKey = array_search($maxMgoVal, $mgoValArray);
      $mgoAbtFlag = intval(array_values(array_intersect_key($abtflagArray, $mgoKeysArray))[$maxMgoKey]);
      $mgoAbtAmt = floatval(array_values(array_intersect_key($abtamtArray, $mgoKeysArray))[$maxMgoKey]);

      // ====== prepare "cpterms" =================================
      if(!in_array($voySumArray['cpspd'][$key], $cpSpdUniqueArray))
      {
        $cpSpdUniqueArray[] = $voySumArray['cpspd'][$key];
        $cpTermsRow = array();

        $cpTermsRow['legindex'][] = $key;
        $cpTermsRow['legnum'][] = $legNum;  // $voySumArray['legnum'][$key];

        $cpTermsRow['cpspdnoabt'] = $voySumArray['cpspdnoabt'][$key];
        $cpSpdDiff = abs($voySumArray['cpspdnoabt'][$key] - $voySumArray['cpspd'][$key]);
        $cpTermsRow['cpspdabtflag'] = intval($cpSpdDiff > 0.0);
        $cpTermsRow['cpspdabtamt'] = $cpSpdDiff;

        $cpTermsRow['ifocpcode'] = 'IFO/HSFO';
        $cpTermsRow['ifocpval'] = $maxIfoVal;
        $cpTermsRow['ifocpabtflag'] = $ifoAbtFlag;
        $cpTermsRow['ifocpabtamt'] = $ifoAbtAmt;
        $cpTermsRow['hfocpcode'] = 'HFO/LSFO';
        $cpTermsRow['hfocpval'] = $maxHfoVal;
        $cpTermsRow['hfocpabtflag'] = $hfoAbtFlag;
        $cpTermsRow['hfocpabtamt'] = $hfoAbtAmt;
        $cpTermsRow['mdocpcode'] = 'MDO';
        $cpTermsRow['mdocpval'] = $maxMdoVal;
        $cpTermsRow['mdocpabtflag'] = $mdoAbtFlag;
        $cpTermsRow['mdocpabtamt'] = $mdoAbtAmt;
        $cpTermsRow['mgocpcode'] = 'MGO';
        $cpTermsRow['mgocpval'] = $maxMgoVal;
        $cpTermsRow['mgocpabtflag'] = $mgoAbtFlag;
        $cpTermsRow['mgocpabtamt'] = $mgoAbtAmt;

        $cpTermsRow['focpcode'] = 'FO';
        $cpTermsRow['focpval'] = max([$maxIfoVal, $maxHfoVal]);
        $cpTermsRow['focpabtflag'] = max([$cpTermsRow['ifocpabtflag'], $cpTermsRow['hfocpabtflag']]);
        $cpTermsRow['focpabtamt'] = max([$cpTermsRow['ifocpabtamt'], $cpTermsRow['hfocpabtamt']]);

        $cpTermsRow['docpcode'] = 'DO';
        $cpTermsRow['docpval'] = max([$maxMdoVal, $maxMgoVal]);
        $cpTermsRow['docpabtflag'] = max([$cpTermsRow['mdocpabtflag'], $cpTermsRow['mgocpabtflag']]);
        $cpTermsRow['docpabtamt'] = max([$cpTermsRow['mdocpabtamt'], $cpTermsRow['mgocpabtamt']]);

        $cpTermsArray[$cpTermsRow['cpspdnoabt']] = $cpTermsRow; //  <===== if element with the same 'cpspdnoabt' does already exist in "cpTermsArray" - it will be replaced
        //  so, "cpTermsArray" would include only unique elements
      }
      else
      {
        $cpTermsArray[$voySumArray['cpspdnoabt'][$key]]['legindex'][] = $key;
        $cpTermsArray[$voySumArray['cpspdnoabt'][$key]]['legnum'][] = $legNum;  // $voySumArray['legnum'][$key];
      }
    }

    ksort($cpTermsArray,SORT_NUMERIC);
    //krsort($cpTermsArray,SORT_NUMERIC);
    $cpTermsArray = array_values($cpTermsArray);
    /*
    $cpSpdNoAbtColumn = array_column($cpTermsArray, 'cpspdnoabt');
    array_multisort($cpTermsArray, SORT_ASC, $cpSpdNoAbtColumn);
    */

    $voySumArray['cpterms'] = $cpTermsArray;
    unset($voySumArray['cpspd']);
    unset($voySumArray['cpspdnoabt']);
    unset($voySumArray['cpspdabtflag']);
    unset($voySumArray['cpspdabtamt']);

    return $voySumArray;
  }
}
