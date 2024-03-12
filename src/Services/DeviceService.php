<?php

namespace App\Services;
use App\Models\User;

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

  public static function getUserDeviceList($user): array
  {
    $limited_count = $user->node_iplimit;
    
    $handle = fopen("/var/www/devices.json", "r");
    $content = "";
    while (!feof($handle)) {
      $line = fgets($handle);
      if (!is_bool($line)) {
        $content .= $line;
      }
    }
    fclose($handle);

    $result = json_decode($content, true);
    $result["limited_count"] = $limited_count;

    return $result;
  }

  public static function addDeviceToUser(User $user): array
  {
    $userDevices = DeviceService::getUserDeviceList($user);
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


  public static function removeDeviceFromUser(User $user, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($user);
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

  public static function activateUserDevice(User $user, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($user);
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

  public static function deactivatedUserDevice(User $user, $deviceid): array
  {
    $userDevices = DeviceService::getUserDeviceList($user);
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
}
