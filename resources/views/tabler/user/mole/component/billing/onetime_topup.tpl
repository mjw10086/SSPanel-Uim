<div style="height: calc(100% - 20px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <div class="fs-4 fw-bold">Top Up credit</div>
    <div class="fs-6 fw-light mt-2">Credit will be be used to auto-renew your
        Ironlink
        plan. You can withdraw it anytime.</div>
    <form class="p-4 col-10">
        <div class="d-flex flex-column mb-4 gap-3">
            <label class="fs-3 fw-bold">Enter amount</label>
            <div>
                <input type="text" id="disabledTextInput"
                    class="bg-tertiary border-0 form-control text-gray p-3 fs-6 fw-light" value="$8" />
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
                            name="flexRadioDefault">
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
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
                            name="flexRadioDefault">
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
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
                            name="flexRadioDefault">
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
                            Any other crypto via your network of choice
                        </label>
                    </div>
                    <img src="/assets/icons/crypto.svg" style="height: 23px;">
                </div>
            </div>
        </div>
        <hr class="mx-5 my-5" />
        <button class="btn btn-info w-100 fs-5 fw-normal">Proceed To Pay</button>
    </form>
</div>