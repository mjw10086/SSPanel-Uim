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
                <span class="line" style="width: 33%;"></span>
                <span class="underline-text px-3 fs-6 fw-light">Secure 3rd party login</span>
                <span class="line" style="width: 33%;"></span>
            </div>
            <div class="d-flex fs-7">
                <div class="card m-2 text-light border-light" style="background-color: transparent;">
                    <div class="d-flex align-items-center justify-content-center gap-2 px-2 py-1">
                        <img src="/assets/icons/telegram.svg" style="width: 25px;">
                        <div>Log In With Telegram</div>
                    </div>
                </div>
                <div class="card m-2 text-light border-light" style="background-color: transparent;">
                    <div class="d-flex align-items-center justify-content-center gap-2 px-2 py-1">
                        <img src="/assets/icons/google.svg" style="width: 25px;">
                        <div>Log In With Google</div>
                    </div>
                </div>
                <div class="card m-2 text-light border-light" style="background-color: transparent;">
                    <div class="d-flex align-items-center justify-content-center gap-2 px-2 py-1">
                        <img src="/assets/icons/apple.svg" style="width: 15px; margin: 3px;">
                        <div>Log In With Apple</div>
                    </div>
                </div>
            </div>
            <div class="my-4 w-100 d-flex justify-content-center align-items-center">
                <span class="line" style="width: 33%;"></span>
                <span class="underline-text px-3 fs-6 fw-light">or sign in with email</span>
                <span class="line" style="width: 33%;"></span>
            </div>
            <form id="login-form" class="w-100 d-flex flex-column justify-content-center align-items-center gap-3 mb-3"
                hx-post="/auth/login" hx-swap="innerHTML" hx-target="#operationResultRender">
                <div class="col-9">
                    <label for="email" class="form-label">Your Email</label>
                    <input type="email" class="form-control text-light" id="email" name="email"
                        placeholder="Email Address" style="background-color: transparent;" autocomplete="on" required>
                </div>
                <div class="col-9">
                    <label for="passwd" class="form-label">Password</label>
                    <div class="input-group border border-1 rounded">
                        <input id="password_input" type="password" class="form-control text-light border-0" id="passwd"
                            name="passwd" placeholder="" style="background-color: transparent;" autocomplete="on"
                            required>
                        <span id="togglePasswordVisible_btn" class="input-group-text bg-quaternary text-light border-0"
                            id="basic-addon1"><i class="bi bi-eye"></i></span>
                    </div>
                    <a class="form-text text-end text-light" style="float: right;" href="/password/reset">Forget
                        password</a>
                </div>
                <div class="col-9">
                    <button class="w-100 btn btn-info fs-5" type="submit">
                        Sign in
                    </button>
                </div>
            </form>

            <a class="text-light" href="/auth/register">Don't have an account? Click to register.</a>
        </div>
    </div>

    <div class="modal fade" id="operationResult" aria-hidden="true" aria-labelledby="operationResult" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="operationResultRender">
            </div>
        </div>
    </div>


    <script type="text/javascript">
        document.getElementById("login-form").addEventListener("submit", function(event) {
            event.preventDefault();
            var myModal = new bootstrap.Modal(document.getElementById('operationResult'));
            document.getElementById("operationResultRender").innerHTML = ` <div class="d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
            myModal.show();
        });

        var togglePasswordVisible_btn = document.getElementById("togglePasswordVisible_btn");
        togglePasswordVisible_btn.addEventListener('click', function(event) {
            var passwdInput = document.getElementById("password_input");
            if (passwdInput.type == "text") {
                passwdInput.type = "password";
                togglePasswordVisible_btn.innerHTML = '<i class="bi bi-eye"></i>';
            } else {
                passwdInput.type = "text";
                togglePasswordVisible_btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
            }
        });
    </script>
</body>


</html>