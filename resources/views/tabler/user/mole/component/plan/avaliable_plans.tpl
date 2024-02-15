<div class="w-100 mb-3">
    <span class="fs-4 fw-bold">Change plan</span>
    <div class="w-100 d-flex flex-nowrap mt-3 justify-content-center">
        {foreach $available_plans as $plan}
            <div class="card mx-3 rounded-4 bg-darkblue2 text-bg-dark p-4" style="width: 250px;">
                <span class="mt-1 mb-3 fs-4 fw-normal">{$plan.name}</span>
                <div class="">
                    <div class="my-1">
                        <span class="fs-2 fw-bolder">${$plan.price}</span>
                        <span class="fs-7 fw-light text-gray">/month</span>
                    </div>
                    <div class="fs-7 fw-light text-gray">
                        {$plan.description}
                    </div>
                </div>
                <hr class="mx-2 border border-white" />
                <div class="fs-6 mb-2 text-gray fw-light">
                    {foreach $plan.features as $feature}
                        <div class="my-2">
                            {if $feature.include}
                                <i class="text-info bi bi-check-circle me-1"></i>
                            {else}
                                <i class="text-danger bi bi-x-circle me-1"></i>
                            {/if}
                            <span>{$feature.item}</span>
                        </div>
                    {{/foreach}}
                </div>
                <div class="col-12">
                    <button class="w-100 btn btn-info mt-3 fs-5 fw-normal" data-bs-target="#switchPlanConfirm"
                        data-bs-toggle="modal" data-bs-planid="{$plan.id}" {if $activated_order !== null && $plan.id eq $activated_order.product_id}
                        disabled {/if}>
                        Switch to this plan
                    </button>
                </div>
            </div>
        {{/foreach}}
    </div>
</div>



<div class="modal fade" id="switchPlanConfirm" aria-hidden="true" aria-labelledby="switchPlanConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold" id="switchPlanConfirm">Switch Plan</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body border-0 fs-5 fw-light my-1">
                Are you sure you want to switch to this plan?
            </div>
            <div class="modal-footer border-0">
                <div class="w-100 d-flex justify-content-between fs-5">
                    <div class="col-6 pe-2">
                        <button id="purchase_btn" class="w-100 btn btn-outline-info fw-normal"
                            data-bs-target="#purchaseResult" data-bs-toggle="modal" hx-get="" hx-swap="innerHTML"
                            hx-target="#purchaseResultRender">
                            Yes, Proceed
                        </button>
                    </div>
                    <div class="col-6 ps-2">
                        <button class="w-100 btn btn-danger text-dark fw-normal" data-bs-dismiss="modal">
                            No, Later
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="purchaseResult" aria-hidden="true" aria-labelledby="purchaseResult" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="purchaseResultRender">
        </div>
    </div>
</div>

<script>
    var switchPlanConfirm = document.getElementById('switchPlanConfirm')
    switchPlanConfirm.addEventListener('show.bs.modal', event => {
        var button = event.relatedTarget
        var planid = button.getAttribute('data-bs-planid')
        var purchase_btn = switchPlanConfirm.querySelector('#purchase_btn')

        purchase_btn.setAttribute("hx-get", "/user/plan/purchase?product_id=" + planid)
        htmx.process(switchPlanConfirm)
    })
</script>