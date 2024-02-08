<?php

namespace App\Services;


final class DataUsage
{
  private $apiUrl = 'https://baidu.com/';

  public function getUserDataUsage($userid): int
  {
    $api_data = $this->fetchDataFromRemoteApi();

    return $api_data;
  }


  private function fetchDataFromRemoteApi(): int
  {
    return 94489280512;
  }
}
