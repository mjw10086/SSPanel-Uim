<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;

final class DeviceService
{
  private $apiUrl = 'https://baidu.com/';

  private static function setUserDeviceList($userDevices)
  {
    $handle = fopen("/var/www/devices.json", "w");
    fwrite($handle, json_encode($userDevices));
    fclose($handle);
  }

  public static function getUserDeviceList($userid): array
  {
    $handle = fopen("/var/www/devices.json", "r");
    $content = "";
    while (!feof($handle)) {
      $line = fgets($handle);
      if (!is_bool($line)) {
        $content .= $line;
      }
    }
    fclose($handle);
    return json_decode($content, true);
  }

  public static function addDeviceToUser($userid): array
  {
    $userDevices = DeviceService::getUserDeviceList($userid);
    $length = 10; // 生成的字符串长度
    $random_bytes = random_bytes($length);
    $random_string = bin2hex($random_bytes);

    array_push(
      $userDevices["devices"],
      [
        "id" => Uuid::uuid4()->toString(),
        "name" => $random_string,
        "status" => $userDevices["activated_count"] < $userDevices["limited_count"] ? "activated" : "deactivated"
      ]
    );

    if ($userDevices["activated_count"] < $userDevices["limited_count"]) {
      $userDevices["activated_count"] += 1;
    }

    DeviceService::setUserDeviceList($userDevices);
    return $userDevices;
  }


  public static function removeDeviceFromUser($userid, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($userid);
    foreach ($userDevices["devices"] as $index => $device) {
      if ($device["id"] === $deviceid) {
        unset($userDevices["devices"][$index]);
        if ($device["status"] === "activated") {
          $userDevices["activated_count"] -= 1;
        }
      }
    }
    DeviceService::setUserDeviceList($userDevices);
    return $userDevices;
  }

  public static function activateUserDevice($userid, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($userid);
    if($userDevices["activated_count"] >= $userDevices["limited_count"]){
      return $userDevices;
    }
    foreach ($userDevices["devices"] as $index => $device) {
      if ($device["id"] === $deviceid) {
        if ($device["status"] === "deactivated") {
          $userDevices["devices"][$index]["status"] = "activated";
          $userDevices["activated_count"] += 1;
        }
        break;
      }
    }
    DeviceService::setUserDeviceList($userDevices);
    return $userDevices;
  }

  public static function deactivatedUserDevice($userid, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($userid);
    foreach ($userDevices["devices"] as $index => $device) {
      if ($device["id"] === $deviceid) {
        if ($device["status"] === "activated") {
          $userDevices["devices"][$index]["status"] = "deactivated";
          $userDevices["activated_count"] -= 1;
        }
        break;
      }
    }
    DeviceService::setUserDeviceList($userDevices);
    return $userDevices;
  }

  public static function getActivateCode($userid): array
  {
    // remain_time -> second
    // status -> Inactive, Activated, Expired
    return ["code" => "dsahdjsahdashklsh", "status" => "Inactive"];
  }


  private static function fetchDataFromRemoteApi(): string
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
            "limited_count": 5
          }';
  }
}
