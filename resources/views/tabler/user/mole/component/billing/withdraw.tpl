<iframe id="hiddenFrame" name="hiddenFrame" style="display: none;"></iframe>

<div style="height: calc(100% - 20px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;"
    hx-get="/user/billing/cryptomus-network-list" hx-trigger="load" hx-swap="beforeend"
    hx-target="#cryptomus-service-list">
    <div class="fs-4 fw-bold">Withdraw balance</div>
    <div class="fs-6 fw-light mt-2">Withdrawals allowed via crypto. You will bear the payment fee.</div>
    <form class="p-4 col-10" hx-post="/user/billing/withdraw/create" hx-trigger="submit" hx-swap="innerHTML"
        hx-target="#withdrawDetail" target="hiddenFrame" id="withdraw-form">
        <div class="d-flex flex-column mb-4 gap-3">
            <label class="fs-3 fw-bold">Enter amount</label>
            <div>
                <div class="input-group mb-3">
                    <span class="input-group-text border-0 m-0 ps-3 pe-0 bg-tertiary text-light fs-6">$</span>
                    <input type="number" id="amount" name="amount"
                        class="bg-tertiary border-0 ps-2 form-control placeholder-gray text-light p-3 fs-6 fw-light"
                        min="1" value="1" step="0.01" required />
                </div>
            </div>
        </div>
        <hr class="mx-5" />
        <div>
            <label class="fs-3 fw-bold">Enter your wallet address & network</label>
            <div class="mt-3">
                <div>
                    <label class="fs-7 fw-light">Wallet Address</label>
                    <input type="text" name="address"
                        class="form-control text-light fs-6 fw-light bg-quinary border-0 mt-1 py-3"
                        placeholder="wallet address" required>
                </div>
                <div class="mt-2">
                    <label class="fs-7 fw-light">Network</label>
                    <select class="form-select text-gray fs-6 fw-light bg-quinary border-0 mt-1 py-3"
                        aria-label="withdraw network" name="network" required id="cryptomus-service-list">
                        <option value="" disabled selected>select network & currency</option>
                    </select>
                </div>
            </div>
        </div>
        <button class="mt-4 mb-3 btn btn-info w-100" type="submit">submit request for withdraw</button>
        <hr class="mx-5" />
        <div class="text-secondary fs-6 fw-light mt-1 d-flex">
            <i class="bi bi-info-circle-fill me-2 text-info"></i>
            <div>Your withdrawal request may take up to 7 business days to process. Thank you for your patience!
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    document.getElementById("withdraw-form").addEventListener("submit", function(event) {
        event.preventDefault();
        var myModal = new bootstrap.Modal(document.getElementById('withdrawResult'));
        document.getElementById("withdrawDetail").innerHTML = ` <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;
        myModal.show();
    });
</script>

<div class="modal fade" id="withdrawResult" aria-hidden="true" data-bs-backdrop="static"
    aria-labelledby="withdrawResultLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="withdrawDetail">
           
        </div>
    </div>
</div>