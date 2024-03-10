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
            <div class="col-4 px-4 d-flex flex-column justify-content-end fs-5 gap-4">
                <div class="d-flex gap-4">
                    <button id="purchase_addition_quota_btn" class="w-100 btn btn-info py-2" data-bs-target="#additionQuota" data-bs-toggle="modal">
                        <div class="fs-5 fw-bold">Buy Extra Data</div>
                        <div class="fs-6 fw-light">vaild until Feb 1, 2024</div>
                    </button>
                </div>
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

<div class="modal fade" id="additionQuota" aria-hidden="true" aria-labelledby="additionQuota" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="additionQuotaRender">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold">Add-ons</h1>
                <button type="button" class="btn-close btn-small btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <hr class="mx-5 my-3" />
            <div class="modal-body border-0 fs-5 fw-light my-1">
                <div class="fs-4 fw-bold mb-2">Available credit: ${$user.money}</div>
                <div class="fs-6 fw-light">Choose how many gigs you want to add to your line</div>
                <div class="d-flex mt-4 justify-content-around gap-3">
                    {foreach $data_plans as $data_plan}
                        <div>
                            <div class="px-4 py-3 rounded-3 card text-light border-light d-flex flex-column gap-2 justify-content-center align-items-center"
                                style="background-color: transparent;">
                                <div class="fs-5 fw-bold">{$data_plan.name}</div>
                                <div>Price: ${$data_plan.price}</div>
                                <button class="btn btn-info fs-7 fw-bold" {if $user.money < $data_plan.price}disabled{/if}
                                    hx-get="/user/plan/purchase-quota?product_id={$data_plan.id}" hx-swap="innerHTML"
                                    hx-target="#operationResultRender" data-bs-target="#operationResult"
                                    data-bs-toggle="modal">Confirm</button>
                            </div>
                            {if $user.money < $data_plan.price}
                                <div class="text-secondary fs-8 fw-light text-gray mt-1">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    You dont have enough balance.
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="modal-footer border-0">
                <a class="text-info nav-link" href="/user/billing/one-time-topup">Top-Up</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="operationResult" aria-hidden="true" aria-labelledby="operationResult" tabindex="-1"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="operationResultRender">
        </div>
    </div>
</div>

{if $addition_quota}
    <script>
        document.getElementById("purchase_addition_quota_btn").click();
    </script>
{/if}