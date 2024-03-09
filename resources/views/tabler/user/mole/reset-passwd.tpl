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
            <span class="my-1 fw-bold fs-2">Reset your password</span>
            <form class="w-100 d-flex flex-column justify-content-center align-items-center gap-3 mb-3 mt-5"
                hx-post="/password/token/{$token}">
                <div class="col-9">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control text-light" id="password" name="password"
                        placeholder="password" style="background-color: transparent;" autocomplete="on" required>
                </div>
                <div class="col-9 mt-4">
                    <button class="w-100 btn btn-info fs-5" type="submit">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>