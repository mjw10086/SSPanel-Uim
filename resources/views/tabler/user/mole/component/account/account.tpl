<div class="d-flex justify-content-center">
    <div class="col-8 py-4">
        <div class="mb-5 fs-8">
            <div class="fs-3 fw-bold mb-4">Sign in methods</div>
            <div class="d-flex flex-column gap-4">
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div>
                                <img src="/assets/icons/apple.svg" />
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
                                <img src="/assets/icons/apple-info.svg" style="height: 36px;" />
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
                                <span class="align-middle">Sign in with Apple</span>
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
                                <span class="fs-7 fw-normal">Log in with Apple</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div>
                                <img src="/assets/icons/telegram.svg" />
                                <span class="align-middle">Sign in with Apple</span>
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
                                <img src="/assets/icons/telegram.svg" style="height: 36px;" />
                                <span class="fs-7 fw-normal">Log in with Apple</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card bg-quinary text-light p-3">
                    <div class="container d-flex flex-column">
                        <div class="d-flex justify-content-between fs-6 fw-light text-gray m-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope fs-2 my-2 mx-3"></i>
                                <span class="">Sign in with Apple</span>
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
                                    value="{$data.account_info.email}" />
                                <button class="btn btn-default fs-6 fw-normal" style="width: 270px;">Change
                                    email</button>
                            </div>
                            <div class="d-flex justify-content-between gap-4">
                                <input type="password"
                                    class="form-control bg-tertiary border-0 fs-6 fw-light text-light"
                                    value="********" />
                                <button class="btn btn-default fs-6 fw-normal" style="width: 270px;">Change
                                    password</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <div></div>
                            <button class="col-5 btn btn-outline-danger fs-5 fw-normal">Disable & remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>