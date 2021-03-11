<?php


namespace App\Lib;


use Symfony\Component\HttpFoundation\Response;

class LibCommonClass
{
  public function setNumericTypes($array): array
  {
    foreach($array as $key => $val )
    {
      if(is_array($val))
        $val = $this->setNumericTypes($val);
      else if(is_numeric($val))
      {
        $float = floatval($val);
        $int = intval($val);
        if ($float == (float)$int)
          $val = intval($val);
        else
          $val = floatval($val);
      }
      $array[$key] = $val;
    }
   return $array;
  }

  public function response($array)
  {
    $response = new Response();
    $response->setContent(json_encode( $array, JSON_UNESCAPED_SLASHES));
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
  }
}