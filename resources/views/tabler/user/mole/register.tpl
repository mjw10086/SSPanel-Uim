{include file='user/mole/component/head.tpl'}

<body class="bg-secondary container-fluid h-100 p-0 d-flex flex-column justify-content-center align-items-center"
    style="background-image: url(/assets/pics/World\ Map.svg); background-size: cover; background-repeat: no-repeat;">
    <div class="card rounded-4 py-5 bg-quaternary text-light d-flex flex-column align-items-center justify-content-center"
        style="width: 650px;">
        <div class="col-11 d-flex flex-column align-items-center justify-content-center">
            <div class="col-4 d-flex justify-content-center align-items-center text-light pt-2">
                <img src="/assets/icons/logo.svg" alt="SVG Image" style="height: 30px;">
                <span class="ms-1 pb-0 fs-4" style="line-height: 40px;">IRONLINK</span>
            </div>
            <span class="my-1 fw-bold fs-2">Access Your Portal</span>
            <div class="my-4 w-100 d-flex justify-content-center align-items-center">
                <span class="line" style="width: 24%;"></span>
                <span class="underline-text px-3 fs-6 fw-light">Register an account via email</span>
                <span class="line" style="width: 24%;"></span>
            </div>
            <form class="w-100 d-flex flex-column justify-content-center align-items-center gap-3 mb-3"
                hx-post="/auth/login" hx-swap="none">
                <div class="col-9">
                    <label for="email" class="form-label">Your Email</label>
                    <input type="email" class="form-control text-light" id="email" name="email"
                        placeholder="Email Address" style="background-color: transparent;" autocomplete="on" required>
                </div>
                <div class="col-9">
                    <label for="passwd" class="form-label">Password</label>
                    <input type="password" class="form-control text-light" id="passwd" name="passwd" placeholder=""
                        style="background-color: transparent;" autocomplete="on" required>
                </div>
                <div class="col-9 d-flex mt-2">
                    <input id="tos" type="checkbox" class="me-2" required/>
                    <span class="fs-6">
                        I have read and agree to <a href="/tos" tabindex="-1"> the terms of service and privacy policy
                        </a>
                    </span>
                </div>
                <div class="col-9 mt-3">
                    <button class="w-100 btn btn-info fs-5" type="submit">
                        Sign Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>