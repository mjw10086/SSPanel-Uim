<div class="modal-header border-0">
    <h1 class="modal-title fs-4 fw-bold" id="purchaseResult">Insufficient balance</h1>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body border-0 fs-5 fw-light my-1">
    The current account balance is ${$balance}.<br> To purchase this plan, an additional deposit of
    ${$price-$balance} is
    required
</div>
<div class="modal-footer border-0">
    <div class="w-100 d-flex justify-content-between fs-5">
        <a class="w-100 btn btn-outline-info fw-normal" href="/user/billing/one-time-topup">
            Proceed to recharge
        </a>
    </div>
</div>