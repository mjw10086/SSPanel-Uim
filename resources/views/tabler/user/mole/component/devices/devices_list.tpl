<div class="col-6 py-4 pe-5 ps-4 h-100">
    <div class="mb-5">
        <div class="fs-4 fw-bold">Active Devices</div>
        <div class="fs-7 fw-light text-gray">{$data.user_devices.activated_count} out of {$data.user_devices.total_count} devices limit
        </div>
        <hr class="ms-4" />
        <ul class="mt-4 list-unstyled">
            {foreach $data.user_devices.devices as $device}
                {if $device.status eq "activated" }
                    <li class="text-light mb-2 d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="green-circle me-2"></div>
                            <span class="fs-5 text-light fw-light">{$device.name}</span>
                            <a class="text-gray fs-6 ms-2 fw-light">remove</a>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </li>
                {/if}
            {{/foreach}}
        </ul>
        <button id="activate_app_btn" class="w-100 btn btn-info mt-3 fs-5 fw-bold">Download & activate app</button>
    </div>
    <div>
        <div>Deactivated Devices</div>
        <div class="fs-7 fw-light text-gray">{$data.user_devices.total_count - $data.user_devices.activated_count} devices</div>
        <hr class="ms-4" />
        <ul class="mt-4 list-unstyled">
            {foreach $data.user_devices.devices as $device}
                {if $device.status eq "deactivated" }
                <li class="text-light mb-2 d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="red-circle me-2"></div>
                        <span class="fs-5 text-light fw-light">{$device.name}</span>
                        <a class="text-gray fs-6 ms-2 fw-ligh">remove</a>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch">
                    </div>
                </li>
                {/if}
            {{/foreach}}
        </ul>
    </div>
</div>

<script>
    var activate_app_btn = document.getElementById("activate_app_btn");

    function activate_app_panel_show(event) {
        var activate_app_panel = document.getElementById('activate_app_panel');
        var devices_and_app_panel = document.getElementById('devices_and_app_panel');

        activate_app_panel.classList.toggle('visually-hidden');
        devices_and_app_panel.classList.toggle('h-100');
    }

    activate_app_btn.addEventListener('click', activate_app_panel_show);
</script>