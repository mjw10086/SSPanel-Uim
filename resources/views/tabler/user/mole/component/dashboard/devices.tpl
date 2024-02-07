{* {assign "devices" ["activated" => [
    [ "id" => "1ad98eaa-aebc-4dda-af35-1965d2c22316", "name" => "iPhone 12 Pro" ],
    [ "id" => "35d82401-c122-4d8f-8e18-95362923caee", "name" => "Macbook Air M2" ],
    [ "id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1" ]
], "total_count" => 5 ]} *}

<div class="p-2">
    <div class="card rounded-4 bg-secondary text-bg-dark p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="fs-4 fw-bold">Your Devices</div>
            <a href="/user/devices" class="nav-link fs-4 fw-bold text-info">Edit</a>
        </div>
        <span class="fs-7 fw-light text-light mt-1">
            {$data.user_devices.activated_count} devices activated
        </span>
        <ul class="my-4 list-unstyled">
            {foreach $data.user_devices.devices as $device}
                {if $device.status eq "activated"}
                    <li class="text-light d-flex align-items-center mb-2">
                        <div class="green-circle me-3"></div>
                        <span class="fs-5 fw-light text-gray">{$device.name}</span>
                    </li>
                {/if}
            {/foreach}
        </ul>
        <a href="/user/devices/activate" class="btn btn-info fs-5 fw-bold">Activate a new device</a>
    </div>
</div>