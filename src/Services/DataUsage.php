<?php

namespace App\Services;


final class DataUsage
{
  private $apiUrl = 'https://baidu.com/';

  public static function getUserDataUsage($userid): int
  {
    $api_data = DataUsage::fetchDataFromRemoteApi();

    return $api_data;
  }

  public static function cancelUserPlan($userid)
  {

  }


  private static function fetchDataFromRemoteApi(): int
  {
    return 8 * 1024 * 102 * 1024;
  }
}
