<div class="w-100 mt-3">
    {if $activated_order !== null}
        <div class="fs-4 fw-bold mb-2">Current plan</div>
        <div class="d-flex justify-content-between align-items-end">
            <div class="col-7 fw-normal fs-5 text-light">
                <div class="d-flex justify-content-between my-2">
                    <span class="fw-light">{$activated_order.product_name}</span>
                    <span>${$activated_order.price}/Month</span>
                </div>
                <div class="d-flex justify-content-between my-2">
                    <span class="fw-light">Next payment</span>
                    <span>{$next_payment_date|date_format:"%b %e, %Y"}</span>
                </div>
                <div class="d-flex justify-content-between my-2">
                    <span class="fw-light">Active devices</span>
                    <span>{$user_devices.activated_count}/{$user_devices.limited_count} device</span>
                </div>
                <div class="d-flex justify-content-between my-2">
                    <span class="fw-light">Remaining data this month</span>
                    <span>{$data_usage/1024/1024/1024}GB/{$user.transfer_enable/1024/1024/1024}GB</span>
                </div>
                <div class="d-flex justify-content-between my-2">
                    <span class="fw-light">Member since</span>
                    <span>{$member_since|date_format:"%b %e, %Y"}</span>
                </div>
            </div>
            <div class="col-4 px-4 d-flex flex-column fs-5 gap-2">
                <button class="w-100 btn btn-outline-danger fw-normal" data-bs-target="#cancelPlanModal"
                    data-bs-toggle="modal">Cancel</button>
            </div>
        </div>
    {else}
        <div class="fs-4 fw-bold mb-2">You don't have any plan now</div>
    {/if}
</div>

<div class="modal fade" id="cancelPlanModal" aria-hidden="true" aria-labelledby="cancelPlanModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold" id="cancelPlanModalLabel">Cancel Plan</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body border-0 fs-5 fw-light my-1">
                Are you sure you want to cancel your plan?
            </div>
            <div class="modal-footer border-0">
                <div class="w-100 d-flex justify-content-between fs-5">
                    <div class="col-6 pe-2">
                        <button class="w-100 btn btn-outline-info fw-normal" data-bs-dismiss="modal">
                            No, keep it
                        </button>
                    </div>
                    <div class="col-6 ps-2">
                        <button class="w-100 btn btn-danger text-dark fw-normal" hx-get="/user/plan/cancel">
                            Yes, cancel the plan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>