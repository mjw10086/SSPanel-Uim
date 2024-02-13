<div class="card rounded-4 bg-tertiary text-light px-5 py-3 fw-normal">
    <div class="mb-4">
        <div class="mb-2 fs-5">1. Download application</div>
        <div class="d-flex flex-column gap-2">
            <div class="d-flex gap-2 align-items-center">
                <a href="https://play.google.com/store/apps/details?id=com.ss.android.ugc.trill" target="_blank"
                    class="nav-link card bg-quaternary text-light px-2 py-2" style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="col-3">
                            <img src="/assets/icons/google-play.svg">
                        </div>
                        <div class="col-9 fw-light">
                            <div class="fs-8 m-0">Get it on</div>
                            <div class="fs-6 m-0">Google Play</div>
                        </div>
                    </div>
                </a>
                <span>or</span>
                <a href="/assets/apps/android/mole.apk" class="nav-link card bg-quaternary text-light px-2 py-2"
                    style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="col-3">
                            <img src="/assets/icons/android.svg">
                        </div>
                        <div class="col-9">
                            <div class="fs-8 fw-light m-0">Android direct</div>
                            <div class="fs-6 fw-normal m-0">Download</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="/assets/apps/macos/mole_arm.dmg" class="nav-link card bg-quinary text-light px-2 py-2"
                    style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="col-3">
                            <img src="/assets/icons/macos.svg">
                        </div>
                        <div class="col-9">
                            <div class="fs-8 fw-light m-0">MacOS intel</div>
                            <div class="fs-6 fw-normal m-0">Download</div>
                        </div>
                    </div>
                </a>
                <span>or</span>
                <a href="/assets/apps/macos/mole_x86.dmg" class="nav-link card bg-quinary text-light ps-2 py-2"
                    style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-1">
                        <div class="col-3">
                            <img src="/assets/icons/macos.svg">
                        </div>
                        <div class="col-9">
                            <div class="fs-8 fw-light m-0">MacOS Apple Silicon</div>
                            <div class="fs-6 fw-normal m-0">Download</div>
                        </div>
                    </div>
                </a>
            </div>
            <div>
                <a href="https://apps.apple.com/us/app/tiktok/id835599320" target="_blank"
                    class="nav-link card bg-quaternary text-light px-2 py-2" style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="col-3">
                            <img src="/assets/icons/apple_store.svg">
                        </div>
                        <div class="col-9">
                            <div class="fs-8 fw-light m-0">Get it on</div>
                            <div class="fs-6 fw-normal m-0">App Store</div>
                        </div>
                    </div>
                </a>
            </div>
            <div>
                <a href="/assets/apps/windows/mole.exe" class="nav-link card bg-quinary text-light px-2 py-2"
                    style="width: 160px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="col-3">
                            <img src="/assets/icons/windows.svg">
                        </div>
                        <div class="col-9">
                            <div class="fs-8 fw-light m-0">Windows 10+</div>
                            <div class="fs-6 fw-normal m-0">Download</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <div class="mb-2 fs-5">2. Copy the Activation Code</div>
        <div class="px-5">
            <div class="input-group">
                <input type="text" value="{$activateCode.code}" id="activate_code"
                    class="form-control p-3 fs-6 fw-normal bg-quaternary border-0 text-light" readonly=true>
                <button id="copy" class="input-group-text fs-7 fw-light bg-quaternary border-0 text-light"
                    data-clipboard-target="#activate_code">copy<i class="bi bi-copy ms-1"></i></button>
            </div>
            <div class="text-gray fw-light fs-6 mt-1 d-flex justify-content-between fw-light text-gray">
                <div>
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span>Code valid for 30 minutes</span>
                </div>
                <span class="text-warning fw-normal" id="code_valid_time"></span>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <div class="fs-5">3. Paste the code to the newly installed app</div>
    </div>
    <div>
        <div class="fs-5">4. Activate another device(optional)</div>
        <div class="px-5">
            <button class="my-3 btn btn-default w-100 fs-5 fw-normal" hx-get="/user/devices/activate-code" hx-swap="innerHTML"
                hx-target="#activate_app_panel">Generate activation
                code</button>
        </div>
    </div>
</div>
<div class="px-4 my-3">
    <div class="text-center mb-3 fs-4 fw-light">Anything went wrong?</div>
    <div class="d-flex justify-content-between">
        <div class="col-6 px-2">
            <a href="/user/faq" class="col-12 btn btn-default fs-5 fw-normal">FAQ</a>
        </div>
        <div class="col-6 px-2">
            <a href="/user/faq" class="col-12 btn btn-outline-info ffs-5 fw-normal">Contact support</a>
        </div>
    </div>
</div>

<script type="text/javascript">
    var clipboard = new ClipboardJS('#copy')

    clipboard.on('success', function(e) {
        console.info('Action:', e.action)
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);
        e.clearSelection();
    })
    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });

    var devices_and_app_panel = document.getElementById('devices_and_app_panel');
    devices_and_app_panel.classList.remove('h-100');

    var code_valid_time = document.getElementById('code_valid_time');
    var remain_time = {$activateCode.remain_time};
    var timer = setInterval(function() {
        var minutes = Math.floor(remain_time / 60);
        var seconds = remain_time % 60;
        var formattedMinutes = (minutes < 10 ? '0' : '') + minutes;
        var formattedSeconds = (seconds < 10 ? '0' : '') + seconds;
        var formatted_remain_time = formattedMinutes + ':' + formattedSeconds;
        code_valid_time.innerHTML = formatted_remain_time;
        remain_time -= 1;
    }, 1000);
</script>