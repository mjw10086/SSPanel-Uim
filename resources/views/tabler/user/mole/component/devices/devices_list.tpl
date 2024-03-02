<div class="col-6 py-4 pe-5 ps-4 h-100" id="devices_list">
    <div class="mb-5">
        <div class="fs-4 fw-bold">Active Devices</div>
        <div class="fs-7 fw-light text-gray">{$user_devices.activated_count} out of {$user_devices.limited_count}
            devices
            limit
        </div>
        <hr class="ms-4" />
        <ul class="mt-4 list-unstyled">
            {foreach $user_devices.devices as $device}
                {if $device.status eq "activated" }
                    <li class="text-light mb-2 d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="green-circle me-2"></div>
                            <span class="fs-5 text-light fw-light">{$device.name}</span>
                            <a class="text-gray fs-6 ms-2 fw-light" hx-delete="/user/devices/{$device.id}" hx-swap="outerHTML"
                                hx-target="#devices_list">remove</a>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" checked
                                hx-post="/user/devices/deactivate" hx-vars="id:'{$device.id}'" hx-swap="outerHTML"
                                hx-target="#devices_list">
                        </div>
                    </li>
                {/if}
            {/foreach}
        </ul>
        {if $user_devices.activated_count < $user_devices.limited_count }
            <button id="activate_app_btn" class="w-100 btn btn-info mt-3 fs-5 fw-bold" hx-get="/user/devices/activate-code"
                hx-swap="innerHTML" {if $smarty.server.REQUEST_URI eq "/user/devices/activation"}hx-trigger="load" {/if}
                hx-target="#activate_app_panel">Download &
                activate app</button>
        {/if}
    </div>
    {if $user_devices.devices|count - $user_devices.activated_count > 0}
        <div>
            <div>Deactivated Devices</div>
            <div class="fs-7 fw-light text-gray">{$user_devices.devices|count - $user_devices.activated_count} devices</div>
            <hr class="ms-4" />
            <ul class="mt-4 list-unstyled">
                {foreach $user_devices.devices as $device}
                    {if $device.status eq "deactivated" }
                        <li class="text-light mb-2 d-flex justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="red-circle me-2"></div>
                                <span class="fs-5 text-light fw-light">{$device.name}</span>
                                <a class="text-gray fs-6 ms-2 fw-ligh" hx-delete="/user/devices/{$device.id}" hx-swap="outerHTML"
                                    hx-target="#devices_list">remove</a>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" hx-post="/user/devices/activate"
                                    hx-vars="id:'{$device.id}'" hx-swap="outerHTML" hx-target="#devices_list">
                            </div>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    {/if}
</div>