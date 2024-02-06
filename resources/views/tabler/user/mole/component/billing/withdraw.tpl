<div style="height: calc(100% - 20px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <div class="fs-4 fw-bold">Withdraw balance</div>
    <div class="fs-6 fw-light mt-2">Withdrawals allowed via crypto. You will bear the payment fee.</div>
    <form class="p-4 col-10">
        <div class="d-flex flex-column mb-4 gap-3">
            <label class="fs-3 fw-bold">Enter amount</label>
            <div>
                <input type="text" id="disabledTextInput"
                    class="bg-quinary border-0 form-control text-gray py-3 px-3 fs-6 fw-light" value="$10" />
            </div>
        </div>
        <hr class="mx-5" />
        <div>
            <label class="fs-3 fw-bold">Enter your wallet address & network</label>
            <div class="mt-3">
                <div>
                    <label class="fs-7 fw-light">Wallet Address</label>
                    <input type="text" class="form-control text-gray fs-6 fw-light bg-quinary border-0 mt-1 py-3"
                        value="wallet address">
                </div>
                <div class="mt-2">
                    <label class="fs-7 fw-light">Network</label>
                    <select class="form-select text-gray fs-6 fw-light bg-quinary border-0 mt-1 py-3"
                        aria-label="Default select example">
                        <option selected>select network</option>
                        <option value="1">
                            <div>TRC20</div>
                            <div>Tron(TRX)</div>
                        </option>
                        <option value="2">
                            <div>TRC20</div>
                            <div>Tron(TRX)</div>
                        </option>
                        <option value="3">
                            <div>TRC20</div>
                            <div>Tron(TRX)</div>
                        </option>
                    </select>
                </div>
            </div>
        </div>
        <button class="mt-4 mb-3 btn btn-info w-100">submit request for withdraw</button>
        <hr class="mx-5" />
        <div class="text-secondary fs-6 fw-light mt-1 d-flex">
            <i class="bi bi-info-circle-fill me-2 text-info"></i>
            <div>Your withdrawal request may take up to 7 business days to process. Thank you for your patience!
            </div>
        </div>
    </form>
</div>