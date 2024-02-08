<?php

declare(strict_types=1);

namespace App\Models;

// $user_devices = [
//     "devices" => [
//         ["id" => "1ad98eaa-aebc-4dda-af35-1965d2c22316", "name" => "iPhone 12 Pro", "status" => "activated"],
//         ["id" => "35d82401-c122-4d8f-8e18-95362923caee", "name" => "Macbook Air M2", "status" => "deactivated"],
//         ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "deactivated"],
//         ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "activated"],
//         ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "activated"]
//     ],
//     "activated_count" => 3,
//     "total_count" => 5
// ];

final class Device {
    public int $id;
    public string $name;
    public string $status;

    public function __construct($id, $name, $status) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
    }
}

final class UserDevices {
    public int $activated_count;
    public int $total_count;
    public $devices;

    public function __construct($activated_count, $total_count, $devices) {
        $this->activated_count = $activated_count;
        $this->total_count = $total_count;
        $this->devices = $devices;
    }
}