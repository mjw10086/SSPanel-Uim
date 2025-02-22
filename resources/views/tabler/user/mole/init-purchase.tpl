{include file='user/mole/component/head.tpl'}

<body class="bg-dark container-fluid h-100 p-0 text-light" style="background-image: url(/assets/pics/Rectangle.svg);">
    <div class="container d-flex justify-content-between align-items-center pt-4 pb-2">
        <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center">
                <img src="/assets/icons/logo.svg" alt="SVG Image" style="width: 33px;">
                <span class="ms-2">IRONLINK</span>
            </span>
            <span class="fs-6 text-gray fw-light">Check-Out</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-4 fs-6 fw-bold">Already have an account?</span>
            <a class="btn btn-info fw-bold px-4 py-2 fs-6 fw-bold" href="/auth/login">Log in</a>
        </div>
    </div>
    <hr />
    <form class="container d-flex pb-5 pt-4" action="/init-purchase/create" method="post">
        <div class="col-7 px-4">
            <div class="card rounded-4 bg-quinary p-5 py-4 text-light">
                <input hidden name="product_id" value="{$product.id}" />
                <div class="text-primary fs-4">Step 1</div>
                <div class="fs-2">Enter the email for your account</div>
                <div class="mt-4">
                    <label class="form-label fs-7 text-gray fw-light">Your Email</label>
                    <input id="email_input" name="email" class="form-control bg-quinary text-light py-3" type="email"
                        placeholder="Email address" required />
                </div>
                <div class="mb-3 mt-4 w-100 d-flex justify-content-center align-items-center">
                    <span class="line" style="width: calc(50% - 70px);"></span>
                    <span class="underline-text px-3 fs-6 fw-light">or continue with</span>
                    <span class="line" style="width: calc(50% - 70px);"></span>
                </div>
                <div style="display: none;">
                    <input id="oauth_type" type="text" name="type" />
                    <input id="oauth_id" type="text" name="id" />
                    <input id="oauth_name" type="text" name="name" />
                    <input id="validation" type="text" name="validation">
                </div>
                <div class="d-flex justify-content-center fs-7 gap-4">
                    <button id="telegram_login" class="btn btn-outline-light btn-sm py-2" type="button">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <img src="/assets/icons/telegram.svg" style="width: 25px;">
                            <div>Log In With Telegram</div>
                        </div>
                    </button>
                    <button id="google_login" class="btn btn-outline-light btn-sm py-2" type="button">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <img src="/assets/icons/google.svg" style="width: 25px;">
                            <div>Log In With Google</div>
                        </div>
                    </button>
                    <button class="btn btn-outline-light btn-sm py-2" type="button">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <img src="/assets/icons/apple.svg" style="width: 15px; margin: 3px;">
                            <div>Log In With Apple</div>
                        </div>
                    </button>
                </div>
            </div>
            <div class="card rounded-4 bg-quinary p-5 py-4 mt-5 text-light">
                <div class="text-primary fs-4">Step 2</div>
                <div class="fs-2">Select a payment method</div>
                <div class="d-flex flex-column gap-3 mt-5">
                    <div class="form-check bg-quinary border border-light rounded-2">
                        <div class="d-flex align-items-center justify-content-between p-3">
                            <div>
                                <input class="form-check-input me-3 border-3 border-white bg-quinary" type="radio"
                                    name="paymentSelect" id="paywithusdt" value="usdt" required>
                                <label class="form-check-label fw-lighter fs-6" for="paywithusdt">
                                    USDT/Tether via Polygon lowest fees
                                </label>
                            </div>
                            <img src="/assets/icons/tether.svg" style="height: 30px;">
                        </div>
                    </div>
                    <div class="form-check bg-quinary border border-light rounded-2">
                        <div class="d-flex align-items-center justify-content-between p-3">
                            <div>
                                <input class="form-check-input me-3 border-3 border-white bg-quinary" type="radio"
                                    name="paymentSelect" id="paywithcrypto" value="crypto" required>
                                <label class="form-check-label fw-lighter fs-6" for="paywithcrypto">
                                    Any other crypto via your network of choice
                                </label>
                            </div>
                            <img src="/assets/icons/crypto.svg" style="height: 23px;">
                        </div>
                    </div>
                    <div>
                        <div class="form-check bg-default rounded-2">
                            <div class="d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <input class="form-check-input me-3 border-3 border-white bg-quinary" disabled
                                        type="radio" name="paymentSelect" id="paywithcard" value="card" required>
                                    <label class="form-check-label fw-lighter fs-6" for="paywithcard">
                                        Pay with credit or debit card
                                    </label>
                                </div>
                                <img src="/assets/icons/credit_card.svg" style="height: 30px;">
                            </div>
                        </div>
                        <div class="text-warning fs-6 fw-light mt-1 ms-2 mb-3">
                            Minimum ~$10 is required to top-up with cards
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5 px-5">
            <div class="w-100 card rounded-4 bg-quaternary p-5 text-light">
                <div class="text-info fs-3">Order summary</div>
                <div class="mt-3">
                    <span class="fs-3 fw-light">Plan:</span><span class="fs-2 fw-bold"> {$product.name}</span>
                </div>
                <div class="fw-light text-gray fs-5">${$product.price}/month, {$product.content.bandwidth} GB/ month,
                    {$product.content.ip_limit} devices</div>
                <div class="fs-6 mt-4">
                    <span class="fw-light">purchased period: </span> <span class="fw-bolder">1-Month</span>
                </div>
                <div class="fs-7 fw-light text-gray">Next payment due on: {$next_pay|date_format:"%b %e, %Y"}</div>
                <a id="coupon_add" class="text-gray mt-3" href="#">Got coupon? </a>
                <div id="coupon_input" class="mt-3" hidden>
                    <div class="fs-7 fw-light text-gray mb-2">Enter Your Coupon Code</div>
                    <div class="d-flex gap-3">
                        <input id="coupon_code" class="form-control bg-quaternary text-light" type="text"
                            name="coupon_code" />
                        <button type="button" class="btn btn-info" hx-post="/user/coupon" hx-swap="none"
                            hx-vals='js:{ coupon: document.getElementById("coupon_code").value, product_id: {$product.id} }'>Apply</button>
                    </div>
                    <div id="coupon_check_msg" class="my-2">

                    </div>
                    <a id="coupon_remove" class="text-danger">Remove Coupon</a>
                </div>
                <hr class="my-4 me-5" />
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-6 text-gray fw-light">
                            <span>Tax country: </span><a class="text-gray" href="#">united states</a>
                        </div>
                        <div class="fs-6 text-gray fw-lighter">sale tax: %0</div>
                    </div>
                    <div>$0</div>
                </div>
                <div class="fs-3 fw-bold d-flex justify-content-between mt-4 my-5">
                    <span>Total</span>
                    <span id="price" class="fw-bolder">$1.5</span>
                </div>
                <button class="btn btn-info fs-5 fw-bold" type="submit">Proceed</button>
                <div class="d-flex mt-2 justify-content-start">
                    <input id="tos" type="checkbox" class="me-2" required />
                    <div class="fs-6">I have read and agree to the terms of service</div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="operationResult" aria-hidden="true" aria-labelledby="operationResult" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="operationResultRender">
            </div>
        </div>
    </div>

</body>

<script>
    var raw_price = 1.5
    var coupon_add = document.getElementById("coupon_add");
    var coupon_input = document.getElementById("coupon_input");
    var coupon_remove = document.getElementById("coupon_remove");

    coupon_add.addEventListener('click', function(event) {
        event.preventDefault()
        coupon_input.removeAttribute("hidden");
        coupon_add.setAttribute("hidden", true);
    })

    coupon_remove.addEventListener('click', function(event) {
        event.preventDefault()
        coupon_input.setAttribute("hidden", true);
        coupon_add.removeAttribute("hidden");
        document.getElementById("price").innerHTML = "$" + raw_price;
        document.getElementById("coupon_code").value = "";
    })

    document.addEventListener('htmx:afterRequest', function(evt) {
        if (evt.detail.xhr.status != 404 && evt.detail.successful == true) {
            var result = JSON.parse(evt.detail.xhr.response);
            document.getElementById("coupon_check_msg").innerHTML = result["msg"];
            if (result["success"]) {
                document.getElementById("price").innerHTML = "$" + result["price"];
            }
        }
    });


    // telegram oauth
    var telegram_login = document.getElementById("telegram_login");
    telegram_login.addEventListener('click', function(event) {
        var oauthUrl =
            "https://oauth.telegram.org/auth?bot_id={$telegram_id}&origin={$base_url}&scope=identity&nonce={$uuid}";

            var oauthWindow = window.open(oauthUrl, "Telegram OAuth Login", "height=600,width=800");

        if (oauthWindow) {
            oauthWindow.focus();
        } else {
            alert("The window cannot be opened. Please check your browser's pop-up window settings.");
        }
    })

    var google_login = document.getElementById("google_login");
    google_login.addEventListener('click', function(event) {
        var oauthUrl =
            "https://accounts.google.com/o/oauth2/auth?client_id={$google_client_id}&redirect_uri={$base_url}/init-purchase/oauth/callback/google&scope=profile%20email&response_type=code&access_type=offline";

            var oauthWindow = window.open(oauthUrl, "Google OAuth Login", "height=600,width=800");

        if (oauthWindow) {
            oauthWindow.focus();
        } else {
            alert("The window cannot be opened. Please check your browser's pop-up window settings.");
        }
    })

    var oauth_type = document.getElementById("oauth_type");
    var oauth_id = document.getElementById("oauth_id");
    var oauth_name = document.getElementById("oauth_name");
    var validation = document.getElementById("validation");
    var email_input = document.getElementById("email_input");

    window.addEventListener('message', function(event) {
        console.log(event)
        if (event.origin === "https://oauth.telegram.org") {
            fetch('{$base_url}/init-purchase/oauth/callback/telegram', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: event.data}).then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            }).then(res => {
                oauth_type.value = "telegram";
                oauth_id.value = res["data"]["id"];
                oauth_name.value = res["data"]["name"];
                validation.value = res["data"]["validation"];
                email_input.removeAttribute("required");
            }).catch(error => {
                console.error('There has been a problem with your fetch operation:',
                    error);
            });
        } else if(event.origin === "{$base_url}"){
            if (event.data["oauth"] == "google") {
                if (event.data["status"] == "success") {
                    oauth_type.value = "google";
                    oauth_id.value = event.data["data"]["id"];
                    oauth_name.value = event.data["data"]["name"];
                    validation.value = event.data["data"]["validation"];
                    email_input.value = event.data["data"]["email"];
                    email_input.removeAttribute("required");
                } else if (event.data["status"] == "duplicate") {
                    var myModal = new bootstrap.Modal(document.getElementById('operationResult'));
                    document.getElementById("operationResultRender").innerHTML = ` <div class="d-flex justify-content-center">
                            You tried signing in with a different authentication method than the one you used during signup. Please try again using your original authentication method, and bind this login method in account settings.
                        </div>`;
                    myModal.show();
                }
            }
        }
    })
</script>


</html>