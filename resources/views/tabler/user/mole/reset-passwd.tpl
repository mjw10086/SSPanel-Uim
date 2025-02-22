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
                    <div class="input-group border border-1 rounded">
                        <input id="password_input" type="password" class="form-control text-light border-0"
                            id="password" name="password" placeholder="password" style="background-color: transparent;"
                            autocomplete="on" required>
                        <span id="togglePasswordVisible_btn" class="input-group-text text-light border-0" style="background-color: transparent;"
                            id="basic-addon1"><i class="bi bi-eye"></i></span>
                    </div>
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


<script>
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


</html>