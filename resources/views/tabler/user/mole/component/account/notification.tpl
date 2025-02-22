<div class="d-flex justify-content-center">
    <div class="col-8 pt-4">
        <div class="mb-5 fs-8">
            <div class="mb-2 d-flex justify-content-between align-items-end">
                <div class="fs-6 fw-bold">Get notified wherever you are</div>
                <a class="fs-7 fw-normal text-lightgray" href="#" hx-post="/user/account/notification/contact-method"
                    hx-swap="none" hx-vals='js:{
                    email: {if $user->getUserEmail() == ""}0{else}1{/if},
                    telegram: {if $user.telegram_username == ""}0{else}1{/if} }'>Reset to default
                    setting</a>
            </div>
            <div class="card rounded-3 bg-tertiary text-bg-dark px-4 py-3">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-light">
                            <div class="fs-5">Email notification</div>
                            {if $user->getUserEmail() != ""}
                                <div class="fs-7">{$user->getUsereEmail()}</div>
                            {/if}
                        </div>
                        <div class="form-check form-switch">
                            <input {if $user->getUserEmail() == ""}disabled{/if} class="form-check-input"
                                type="checkbox" id="email_notify_enable" role="switch"
                                {if $user.contact_method === 1 || $user.contact_method === 3}checked{/if}
                                style="height: 18px; width: 40px" hx-post="/user/account/notification/contact-method"
                                hx-vals='js:{
                                    email: document.getElementById("email_notify_enable").checked ? 1:0,
                                    telegram: document.getElementById("telegram_notify_enable").checked ? 1:0 }'>
                        </div>
                    </div>
                    <hr class="m-0 border border-secondary" />
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-light">
                            <div class="fs-5">Telegram notification</div>
                            <div class="d-flex align-items-center">
                                <img src="/assets/icons/telegram.svg" style="height: 28px;" alt="icon">
                                {if $user.telegram_username != ""}
                                    <div class="fs-7">@{$user.telegram_username}</div>
                                {/if}
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input {if $user.telegram_username == ""}disabled{/if} class="form-check-input"
                                type="checkbox" id="telegram_notify_enable" role="switch"
                                {if $user.contact_method === 2 || $user.contact_method === 3}checked{/if}
                                style="height: 18px; width: 40px" hx-post="/user/account/notification/contact-method"
                                hx-vals='js:{
                                    email: document.getElementById("email_notify_enable").checked ? 1:0,
                                    telegram: document.getElementById("telegram_notify_enable").checked? 1:0 }'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>