<div style="height: calc(100% - 20px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <div class="fs-4 fw-bold">Automatic top-ups</div>
    <div class="fs-6 fw-light mt-2">Your balance will be automatically refilled whenever it's low. It ensures
        uninterrupted service and improves convenience by avoiding manual handling of payments. </div>
    <div class="text-secondary fs-6 fw-light text-gray mt-3">
        <i class="bi bi-info-circle-fill me-2"></i>
        You will be able to cancel the service & withdraw your remaining balance anytime.
    </div>
    <form class="p-4 col-10" action="/user/billing/recurrence/create" method="post">
        {if $current_recurrence !== null}
            <label class="fs-3 fw-bold">Current Payment Method</label>
            <div class="form-check bg-quinary rounded-2 mt-3">
                <div class="d-flex align-items-center justify-content-between p-3 ps-2">
                    <div>
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
                            Cryptomus
                        </label>
                    </div>
                    <a class="text-danger nav-link" data-bs-target="#cancelRecurrenceEnsure"
                        data-bs-toggle="modal">remove</a>
                </div>
            </div>
        {/if}
        <div class="my-4">
            <label class="fs-3 fw-bold">Select payment method</label>
            <div class="form-check bg-quinary rounded-2 mt-3">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <input class="form-check-input me-3 border-3 border-white bg-darkblue" type="radio"
                            name="paymentSelect" value="card" required>
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
                            Pay with credit or debit card
                        </label>
                    </div>
                    <img src="/assets/icons/credit_card.svg" style="height: 30px;">
                </div>
            </div>
            <div class="form-check bg-quinary rounded-2 mt-3">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <input class="form-check-input me-3 border-3 border-white bg-darkblue" type="radio"
                            name="paymentSelect" value="crypto" required>
                        <label class="form-check-label fw-lighter fs-6" for="flexRadioDefault1">
                            Any other crypto via your network of choice
                        </label>
                    </div>
                    <img src="/assets/icons/crypto.svg" style="height: 23px;">
                </div>
            </div>
        </div>
        <hr class="mx-5 my-5" />
        <button class="btn btn-info w-100 fs-5 fw-normal" type="submit">Proceed to payment authorization</button>
    </form>
</div>


<div class="modal fade" id="cancelRecurrenceEnsure" aria-hidden="true" aria-labelledby="cancelRecurrenceEnsure"
    tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="cancelRecurrenceEnsureRender">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold">SRemove Payment Metod?</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body border-0 fs-5 fw-light my-1">
                Cryptomus recurrence will be Removed from your automatic top-up.
            </div>
            <div class="modal-footer border-0">
                <div class="w-100 d-flex justify-content-between fs-5">
                    <div class="col-6 pe-2">
                        <a class="w-100 btn btn-info fw-normal" data-bs-dismiss="modal" aria-label="Close">
                            Cancel
                        </a>
                    </div>
                    <div class="col-6 ps-2">
                        <a class="w-100 btn btn-danger fw-normal" hx-get="/user/billing/recurrence/cancel">
                            remove
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>