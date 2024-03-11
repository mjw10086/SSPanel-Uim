<div style="height: calc(100% - 20px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <div class="fs-4 fw-bold">Top Up credit</div>
    <div class="fs-6 fw-light mt-2">Credit will be be used to auto-renew your
        Ironlink
        plan. You can withdraw it anytime.</div>
    <form class="p-4 col-10" action="/user/billing/topup/create" method="post">
        <div class="d-flex flex-column mb-4 gap-3">
            <label class="fs-3 fw-bold" for="topup_amount">Enter amount</label>
            <div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 m-0 ps-3 pe-0 bg-tertiary text-light fs-6">$</span>
                    <input type="number" id="topup_amount" name="topup_amount"
                        class="bg-tertiary border-0 ps-2 form-control placeholder-gray text-light p-3 fs-6 fw-light"
                         min="1" value="10" required />
                </div>

                <div class="text-secondary fs-6 fw-light text-gray mt-1">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Credit should suffice till Jan 3 2024.
                </div>
            </div>
        </div>
        <hr class="mx-5" />
        <div class="my-4">
            <label class="fs-3 fw-bold">Select payment method</label>
            <div class="form-check bg-default rounded-2 mt-3">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <input class="form-check-input me-3 border-3 border-white bg-darkblue" type="radio"
                            name="paymentSelect" id="paywithcard" value="card" required disabled>
                        <label class="form-check-label fw-lighter fs-6" for="paywithcard">
                            Pay with credit or debit card
                        </label>
                    </div>
                    <img src="/assets/icons/credit_card.svg" style="height: 30px;">
                </div>
            </div>
            <div class="text-warning fs-6 fw-light mt-1 mb-3">
                Minimum ~$10 is required to top-up with cards
            </div>
            <div class="form-check bg-quinary rounded-2 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <input class="form-check-input me-3 border-3 border-white bg-darkblue" type="radio"
                            name="paymentSelect" id="paywithusdt" value="usdt" required>
                        <label class="form-check-label fw-lighter fs-6" for="paywithusdt">
                            USDT/Tether via Polygon lowest fees
                        </label>
                    </div>
                    <img src="/assets/icons/tether.svg" style="height: 30px;">
                </div>
            </div>
            <div class="form-check bg-quinary rounded-2">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <input class="form-check-input me-3 border-3 border-white bg-darkblue" type="radio"
                            name="paymentSelect" id="paywithcrypto" value="crypto" required>
                        <label class="form-check-label fw-lighter fs-6" for="paywithcrypto">
                            Any other crypto via your network of choice
                        </label>
                    </div>
                    <img src="/assets/icons/crypto.svg" style="height: 23px;">
                </div>
            </div>
        </div>
        <hr class="mx-5 my-5" />
        <button type="submit" value="Submit" class="btn btn-info w-100 fs-5 fw-normal">Proceed To Pay</button>
    </form>
</div>