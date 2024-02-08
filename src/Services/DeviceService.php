<?php

namespace App\Services;

use App\Models\Device;
use App\Models\UserDevices;


final class DeviceService
{
  private $apiUrl = 'https://baidu.com/';

  public function getUserDeviceList($userid): array
  {
    $api_data = $this->fetchDataFromRemoteApi();
    $user_devices = json_decode($api_data, true);

    return $user_devices;
  }

  public function removeDeviceFromUser($userid, $deviceid): array
  {
    $api_data = $this->fetchDataFromRemoteApi();
    $user_devices = json_decode($api_data, true);
    $user_devices['activated_count'] = 2;

    return $user_devices;
  }

  public function activateUserDevice($userid, $deviceid): array
  {
    $api_data = $this->fetchDataFromRemoteApi();
    $user_devices = json_decode($api_data, true);
    $user_devices['activated_count'] = 2;

    return $user_devices;
  }

  public function deactivatedUserDevice($userid, $deviceid): array
  {
    $api_data = $this->fetchDataFromRemoteApi();
    $user_devices = json_decode($api_data, true);
    $user_devices['activated_count'] = 2;

    return $user_devices;
  }

  public function getActivateCode($userid): string
  {
    return "dsahdjsahdashklsh";
  }


  private function fetchDataFromRemoteApi(): string
  {
    return '{
            "devices": [
              {
                "id": 1,
                "name": "iPhone 12 Pro",
                "status": "activated"
              },
              {
                "id": 2,
                "name": "Macbook Air M2",
                "status": "deactivated"
              },
              {
                "id": 3,
                "name": "Samsung Galaxy A1",
                "status": "deactivated"
              },
              {
                "id": 4,
                "name": "Samsung Galaxy A1",
                "status": "activated"
              },
              {
                "id": 5,
                "name": "Samsung Galaxy A1",
                "status": "activated"
              }
            ],
            "activated_count": 3,
            "total_count": 5
          }';
  }
}
