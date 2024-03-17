<div class="p-2">
    <div class="card rounded-4 bg-secondary text-bg-dark p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="fs-4 fw-bold">Your devices</div>
            <a href="/user/devices" class="nav-link fs-4 fw-bold text-info">Edit</a>
        </div>
        <span class="fs-7 fw-light text-light mt-1">
            {$user_devices.activated_count} devices activated
        </span>
        <ul class="my-4 list-unstyled">
            {foreach $user_devices.devices as $device}
                {if $device.status eq "activated"}
                    <li class="text-light d-flex align-items-center mb-2">
                        <div class="green-circle me-3"></div>
                        <span class="fs-5 fw-light text-gray">{$device.name}</span>
                    </li>
                {/if}
            {/foreach}
        </ul>
        <a href="/user/devices/activation" class="btn btn-info fs-5 fw-bold">Activate a new device</a>
    </div>
</div>