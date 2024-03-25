<div class="d-flex justify-content-center">
    <div class="col-8 py-4">
        <div class="mb-5 fs-8">
            <div class="fs-3 fw-bold mb-4">Sign in methods</div>
            <div class="d-flex flex-column gap-4">
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div>
                                <img src="/assets/icons/apple.svg" style="margin: 10px; margin-bottom: 12px" />
                                <span class="align-middle">Sign in with Apple</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                {if $data.account_info.OAuth.apple.activated}
                                    <div class="green-circle"></div>
                                    <span class="text-success">Active</span>
                                {else}
                                    <div class="gray-circle"></div>
                                    <span>Not activated</span>
                                {/if}
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div class="col-8 fs-6 fw-lighter">
                                Note: privacy protected email addresses will be rejected due to unreliable email
                                delivery on
                                Apple’s side
                            </div>
                            <button
                                class="btn btn-outline-info d-flex align-items-center justify-content-center py-0 pe-3 px-2">
                                <img src="/assets/icons/apple-info.svg" style="height: 23px; margin:0 10px;" />
                                <span class="fs-7 fw-normal">Log in with Apple</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div>
                                <img src="/assets/icons/google.svg" />
                                <span class="align-middle">Sign in with Google</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                {if $user.google_id !== ""}
                                    <div class="green-circle"></div>
                                    <span class="text-success">Active</span>
                                {else}
                                    <div class="gray-circle"></div>
                                    <span>Not activated</span>
                                {/if}
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div class="col-8 fs-6 fw-lighter">
                                Note: privacy protected email addresses will be rejected due to unreliable email
                                delivery on
                                Apple’s side
                            </div>
                            {if $user.google_id !== ""}
                                <button
                                    class="btn btn-danger text-light d-flex align-items-center justify-content-center py-0"
                                    style="width: 170px; height: 40px" hx-get="/user/account/oauth/google/deactivate"
                                    hx-swap="none">
                                    <span class="fs-6 fw-normal">Disable & Remove</span>
                                </button>
                            {else}
                                <button id="google_oauth_set"
                                    class="btn btn-outline-info d-flex align-items-center justify-content-center py-0 pe-3 px-2">
                                    <img src="/assets/icons/google.svg" style="height: 36px;" />
                                    <span class="fs-7 fw-normal">Log in with Google</span>
                                </button>

                            {/if}
                        </div>
                    </div>
                </div>
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div>
                                <img src="/assets/icons/telegram.svg" />
                                <span class="align-middle">Sign in with Telegram</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                {if $user.telegram_id !== ""}
                                    <div class="green-circle"></div>
                                    <span class="text-success">Active</span>
                                {else}
                                    <div class="gray-circle"></div>
                                    <span>Not activated</span>
                                {/if}
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div></div>
                            {if $user.telegram_id !== ""}
                                <button
                                    class="btn btn-danger text-light d-flex align-items-center justify-content-center py-0"
                                    style="width: 170px; height: 40px" hx-get="/user/account/oauth/telegram/deactivate"
                                    hx-swap="none">
                                    <span class="fs-6 fw-normal">Disable & Remove</span>
                                </button>
                            {else}
                                <button id="telegram_oauth_set"
                                    class="btn btn-outline-info d-flex align-items-center justify-content-center py-0 pe-3 px-2">
                                    <img src="/assets/icons/telegram.svg" style="height: 40px;" />
                                    <span class="fw-normal" style="font-size: 11px;">Log in with Telegram</span>
                                </button>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope fs-2 my-2 mx-3"></i>
                                <span class="">Sign in with Email</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="green-circle"></div>
                                <span class="text-success">Active</span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between gap-4">
                                <input type="email" class="form-control bg-tertiary border-0 fs-6 fw-light text-light"
                                    value="{$user.email}" id="new-email" />
                                <button class="btn btn-default fs-6 fw-normal" style="width: 270px;"
                                    hx-post="/user/account/email" hx-swap="innerHTML" hx-target="#operationResultRender"
                                    hx-vals='js:{
                                        newemail: document.getElementById("new-email").value,
                                    }' data-bs-target="#operationResult" data-bs-toggle="modal">
                                    Change email
                                </button>
                            </div>
                            <div class="d-flex justify-content-between gap-4">
                                <div class="input-group">
                                    <input type="password" id="new-passwd"
                                        class="form-control bg-tertiary border-0 fs-6 fw-light text-light" />
                                    <span id="togglePasswordVisible_btn"
                                        class="input-group-text bg-tertiary text-light border-0" id="basic-addon1"><i
                                            class="bi bi-eye"></i></span>
                                </div>
                                <button class="btn btn-default fs-6 fw-normal" style="width: 270px;"
                                    hx-post="/user/account/password" hx-swap="innerHTML"
                                    hx-target="#operationResultRender" hx-vals='js:{
                                    passwd: document.getElementById("new-passwd").value,
                                }' data-bs-target="#operationResult" data-bs-toggle="modal">Change
                                    password</button>
                            </div>
                        </div>
                        <div class=" d-flex justify-content-between mt-4">
                            <div></div>
                            <button class="col-5 btn btn-outline-danger fs-5 fw-normal">Disable &
                                remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var togglePasswordVisible_btn = document.getElementById("togglePasswordVisible_btn");
    togglePasswordVisible_btn.addEventListener('click', function(event) {
        var passwdInput = document.getElementById("new-passwd");
        if (passwdInput.type == "text") {
            passwdInput.type = "password";
            togglePasswordVisible_btn.innerHTML = '<i class="bi bi-eye"></i>';
        } else {
            passwdInput.type = "text";
            togglePasswordVisible_btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
        }
    });

    var google_oauth_set = document.getElementById("google_oauth_set");
    if (google_oauth_set != null) {
        google_oauth_set.addEventListener('click', function(event) {
            var oauthUrl =
                "https://accounts.google.com/o/oauth2/auth?client_id={$google_client_id}&redirect_uri={$base_url}/user/account/oauth/google&scope=profile%20email&response_type=code&access_type=offline";

                var oauthWindow = window.open(oauthUrl, "Google OAuth Login", "height=600,width=800");

            if (oauthWindow) {
                oauthWindow.focus();
            } else {
                alert("The window cannot be opened. Please check your browser's pop-up window settings.");
            }
        })
    }


    var telegram_oauth_set = document.getElementById("telegram_oauth_set");
    if (telegram_oauth_set != null) {
        telegram_oauth_set.addEventListener('click', function(event) {
            var oauthUrl =
                "https://oauth.telegram.org/auth?bot_id={$telegram_id}&origin={$base_url}/user/account/oauth/telegram&scope=identity&nonce={$uuid}";

                var oauthWindow = window.open(oauthUrl, "Telegram OAuth Login", "height=600,width=800");

            if (oauthWindow) {
                oauthWindow.focus();
            } else {
                alert("The window cannot be opened. Please check your browser's pop-up window settings.");
            }
        })
    }


    // telegram oauth callback
    window.addEventListener('message', function(event) {
        if (event.origin === "https://oauth.telegram.org") {
            fetch('{$base_url}/user/account/oauth/telegram', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: event.data
        }).then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data["status"] == 1) {
            window.location.href = "/user/account/info";
        }
    })
    .catch(error => {
        console.error('There has been a problem with your fetch operation:',
            error);
    });
    }else if(event.origin === "{$base_url}"){
    if (event.data["oauth"] == "google") {
        if (event.data["status"] == "success") {
            window.location = "/user/account/info";
        } else if (event.data["status"] == "duplicate") {
            var myModal = new bootstrap.Modal(document.getElementById('operationResult'));
            document.getElementById("operationResultRender").innerHTML = ` <div class="d-flex justify-content-center">
                    You tried signing in with a different authentication method than the one you used during signup. Please try again using your original authentication method, and bind this login method in account settings.
                </div>`;
            myModal.show();
        }
    }
    }
    });
</script>


<div class="modal fade" id="operationResult" aria-hidden="true" aria-labelledby="operationResult" tabindex="-1"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="operationResultRender">
        </div>
    </div>
</div>