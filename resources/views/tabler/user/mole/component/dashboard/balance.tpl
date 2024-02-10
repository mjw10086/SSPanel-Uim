{* {assign "balance" ["amount" => 15, "last_until" => 1709126493 ]} *}

<div class="col-8 p-2">
    <div class="card rounded-4 bg-secondary p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="fw-normal text-light">
                <span class="fs-3">Available credit: </span>
                <span class="fs-2">${$user.money}</span>
            </div>
            <a href="/user/billing/billing-history" class="nav-link fs-5 fw-bold text-info">Billing History</a>
        </div>
        <span class="fs-7 fw-light text-lightgray mb-1">
            {if $expected_suffice_till === null}
                You don't have any plan now
            {else}
                Should last till {$expected_suffice_till|date_format:"%b %e, %Y"}
            {/if}
        </span>
        <div class="d-flex mt-4 fs-5">
            <div class="col-6 pe-2">
                <a href="/user/billing/one-time-topup" class="w-100 btn btn-default fw-normal">Top up</a>
            </div>
            <div class="col-6 ps-2">
                <a href="/user/billing/automatic-topups" class="w-100 btn btn-outline-default fw-normal">
                    Set up automatic top-ups
                </a>
            </div>
        </div>
    </div>
</div>