<div class="d-flex justify-content-center">
    <div class="col-8 pt-4">
        <div class="mb-5 fs-8">
            <div class="mb-2 d-flex justify-content-between align-items-end">
                <div class="fs-6 fw-bold">Get Notified Wherever You Are</div>
                <div class="fs-7 fw-normal text-lightgray">Reset to default setting</div>
            </div>
            <div class="card rounded-3 bg-tertiary text-bg-dark px-4 py-3">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-light">
                            <div class="fs-5">Email Notification</div>
                            <div class="fs-7">{$data.account_info.email}</div>
                        </div>
                        <div class="form-check form-switch">
<input class="form-check-input" type="checkbox" role="switch" {if $data.notification_setting.email}checked{/if}
                                style="height: 18px; width: 40px">
                        </div>
                    </div>
                    <hr class="m-0 border border-secondary" />
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-light">
                            <div class="fs-5">Telegram Notification</div>
                            <div class="d-flex align-items-center">
                                <img src="/assets/icons/telegram.svg" style="height: 28px;" alt="icon">
                                <div class="fs-7">@{$data.account_info.OAuth.telegram.username}</div>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" {if $data.notification_setting.telegram}checked{/if}
                                style="height: 18px; width: 40px">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>