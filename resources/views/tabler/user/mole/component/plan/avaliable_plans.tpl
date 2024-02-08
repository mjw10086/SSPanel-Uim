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
                    <button class="w-100 btn btn-info mt-3 fs-5 fw-normal" {if $plan.id eq $data.current_plan.plan_id} disabled {/if}>
                        Switch to this plan
                    </button>
                </div>
            </div>
        {{/foreach}}
    </div>
</div>