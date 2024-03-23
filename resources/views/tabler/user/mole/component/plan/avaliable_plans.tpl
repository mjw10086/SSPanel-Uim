<div class="w-100 mb-3">
    <span class="fs-4 fw-bold">Change plan</span>
    <div class="w-100 d-flex flex-nowrap mt-3 justify-content-center">
        {foreach $available_plans as $plan}
            <div class="plan-div card mx-3 rounded-4 bg-darkblue2 text-bg-dark p-4" style="width: 250px;">
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
                        data-bs-toggle="modal" data-bs-planid="{$plan.id}"
                        {if $activated_order !== null && $plan.id eq $activated_order.product_id} disabled {/if}>
                        {if $activated_order !== null && $plan.id eq $activated_order.product_id}
                            Current plan
                        {else}
                            Switch to this plan
                        {/if}
                    </button>
                </div>
            </div>
        {{/foreach}}
    </div>
</div>



<div class="modal fade" id="switchPlanConfirm" aria-hidden="true" aria-labelledby="switchPlanConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
        <form id="purchase_form" class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" hx-get=""
            hx-swap="innerHTML" hx-target="#purchaseResultRender">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold" id="switchPlanConfirm">Change your plan</h1>
                <button id="switchPlanConfirm_close_btn" type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body border-0 fs-5 fw-light mb-1">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 text-gray fw-light">Current plan: {$activated_order.product_name}</span>
                    <span class="fs-6 text-gray">{$user.transfer_enable/1024/1024/1024}GB/month</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fs-5 text-gray fw-light">Refund for unused data or time</span>
                    <span class="fs-6 text-gray">${$refund_amount}</span>
                </div>
                <div id="balance_insufficient_note" class="fs-6 d-flex text-warning mt-4 visually-hidden">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span> We're sorry, but your current credit balance of ${$user->money} is insufficient to process
                        the plan
                        change. To
                        proceed with the plan change, please top up your account by visiting the 'Top-up' page.</span>
                </div>
                <hr class="mx-5" style="margin: 40px 0;" />
                <div class="d-flex justify-content-between">
                    <h1 class="modal-title fs-4 fw-bold" id="switchPlanConfirm">New plan</h1>
                    <h1 class="modal-title fs-4 fw-bold" id="new_plan_name">Starter</h1>
                </div>
                <div class="d-flex justify-content-between my-3">
                    <span class="fs-5 text-gray fw-light">Pricing</span>
                    <span class="fs-5" id="new_plan_price">$1.5/month</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 text-gray fw-light">Start Plan</span>
                    <span class="fs-5" id="new_plan_start_date">Feb 1, 2024</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 text-gray fw-light">Active devices</span>
                    <span class="fs-5" id="new_plan_device">up to 2 devices</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 text-gray fw-light">Data per month</span>
                    <span class="fs-5" id="new_plan_quota">10GB/month</span>
                </div>
                <div id="term_agree_input" class="form-check mt-5">
                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required>
                    <label class="form-check-label fs-6 fw-light" for="flexCheckDefault">
                        I have read and agree to Terms of Service and Privacy Policy
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center">
                <div id="form_submit_container" class="d-flex justify-content-center fs-5">
                    <button id="purchase_btn" class="btn btn-info fw-normal px-5 py-0">
                        <span class="d-flex flex-column justify-content-center align-items-center">
                            <span class="fs-5 fw-bold">Confirm plan change</span>
                            <span class="fs-6 fw-light" id="balance_decrease">Your balance will decrease by $0.4</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="purchaseResult" aria-hidden="true" aria-labelledby="purchaseResult" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3" id="purchaseResultRender">
        </div>
    </div>
</div>

<script>
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    let currentDate = new Date();
    let month = months[currentDate.getMonth()];
    let day = currentDate.getDate();
    let year = currentDate.getFullYear();

    let formattedDate = month + " " + day + ", " + year;
    document.getElementById("new_plan_start_date").innerHTML = formattedDate;

    var plan_list = {};

    {foreach $available_plans as $plan}
    plan_list[{$plan.id}] = {$plan|json_encode};
    {/foreach}

    var switchPlanConfirm = document.getElementById('switchPlanConfirm')
    switchPlanConfirm.addEventListener('show.bs.modal', event => {
        var button = event.relatedTarget
        var planid = button.getAttribute('data-bs-planid')
        var purchase_form = switchPlanConfirm.querySelector('#purchase_form')

        purchase_form.setAttribute("hx-get", "/user/plan/purchase?product_id=" + planid)
        var content = JSON.parse(plan_list[parseInt(planid)]["content"]);
        var price = parseFloat(plan_list[parseInt(planid)]["price"]);
        if(parseFloat({$user->money}) + parseFloat({$refund_amount}) < price){
            document.getElementById("balance_insufficient_note").classList.remove("visually-hidden");
            document.getElementById("term_agree_input").classList.add("visually-hidden");
            document.getElementById("form_submit_container").innerHTML = '<a href="/user/billing/one-time-topup" class="btn btn-info px-5 fs-5 fw-bold" style="width: 250px">Top-up</a>';
        }

        document.getElementById("new_plan_name").innerHTML = plan_list[parseInt(planid)]["name"];
        document.getElementById("new_plan_price").innerHTML = "$" + price + "/month";
        document.getElementById("new_plan_device").innerHTML = "up to " + content["ip_limit"] + " devices";
        document.getElementById("new_plan_quota").innerHTML = content["bandwidth"] + "GB/month";
        var balance_change = price - parseFloat({$refund_amount});
        document.getElementById("balance_decrease").innerHTML = "Your balance will " + (balance_change > 0 ?
            "decrease" : "increase") + " by $" + Math.abs(balance_change);

        htmx.process(switchPlanConfirm)
    })

    document.body.addEventListener('htmx:afterSwap', function(event) {
        console.log(event)
        if (event.detail.pathInfo.finalRequestPath.includes('/user/plan/purchase?product_id=')) {
            document.getElementById('switchPlanConfirm_close_btn').click();
            var purchaseResult = new bootstrap.Modal(document.getElementById('purchaseResult'));
            purchaseResult.show();
        }
    });
</script>