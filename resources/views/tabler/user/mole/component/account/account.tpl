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
                                {if $data.account_info.OAuth.google.activated}
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
                                <img src="/assets/icons/google.svg" style="height: 36px;" />
                                <span class="fs-7 fw-normal">Log in with Google</span>
                            </button>
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
                                {if $data.account_info.OAuth.telegram.activated}
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
                            <button
                                class="btn btn-outline-info d-flex align-items-center justify-content-center py-0 pe-3 px-2">
                                <img src="/assets/icons/telegram.svg" style="height: 40px;" />
                                <span class="fw-normal" style="font-size: 11px;">Log in with Telegram</span>
                            </button>
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
</script>


<div class="modal fade" id="operationResult" aria-hidden="true" aria-labelledby="operationResult" tabindex="-1"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="operationResultRender">
        </div>
    </div>
</div>