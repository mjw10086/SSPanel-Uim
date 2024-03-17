{if $smarty.server.REQUEST_URI eq "/user/billing/one-time-topup"}
    {assign "current_route" 1}
{elseif $smarty.server.REQUEST_URI eq "/user/billing/automatic-topups"}
    {assign "current_route" 2}
{elseif $smarty.server.REQUEST_URI eq "/user/billing/withdraw"}
    {assign "current_route" 3}
{elseif $smarty.server.REQUEST_URI eq "/user/billing/billing-history"}
    {assign "current_route" 4}
{elseif $smarty.server.REQUEST_URI eq "/user/billing/balance-history"}
    {assign "current_route" 5}
{else}
    {assign "current_route" 1}
{/if}

<div class="card bg-tertiary text-light px-3 py-4">
    <div class="mb-5">
        <div class="fs-4 fw-bold">Availble credit: ${$user.money}</div>
        <div class="fs-8 fw-light text-gray">
            {if $expected_suffice_till === null}
                You don't have any plan now
            {else}
                Should last till {$expected_suffice_till|date_format:"%b %e, %Y"}
            {/if}
        </div>
    </div>
    <div class="mb-5">
        <span class="fs-8 fw-lighter text-gray">Balance & billing</span>
        <hr class="mt-0" />
        <div class="d-flex flex-column gap-2 fs-6 fw-normal">
            <span class="{if $current_route == 1}bg-quinary{/if} rounded-2 p-2 ">
                <a href="/user/billing/one-time-topup"
                    class="nav-link d-flex align-items-center justify-content-between gap-4">
                    <div>
                        <img src="/assets/icons/top-up{if $current_route == 1}-info{/if}.svg" alt="SVG Image"
                            class="me-2">
                        One Time top-up
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </span>
            <span class="{if $current_route == 2}bg-quinary{/if} rounded-2 p-2">
                <a href="/user/billing/automatic-topups"
                    class="nav-link d-flex align-items-center justify-content-between gap-4">
                    <div>
                        <img src="/assets/icons/automatic-topup{if $current_route == 2}-info{/if}.svg" alt="SVG Image"
                            class="me-2">
                        Set up automatic top-ups
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </span>
            <span class="{if $current_route == 3}bg-quinary{/if} rounded-2 p-2">
                <a href="/user/billing/withdraw"
                    class="nav-link d-flex align-items-center justify-content-between gap-4">
                    <div>
                        <img src="/assets/icons/withdraw{if $current_route == 3}-info{/if}.svg" alt="SVG Image"
                            class="me-2">
                        Withdraw balance
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </span>
        </div>
    </div>
    <div class="mb-5">
        <span class="fs-8 fw-lighter text-gray">History</span>
        <hr class="mt-0" />
        <div class="d-flex flex-column gap-2 fs-6 fw-normal">
            <a href="/user/billing/billing-history"
                class="{if $current_route == 4}bg-quinary{/if} rounded-2 p-3 nav-link d-flex align-items-center justify-content-between">
                Billing history
                <i class="bi bi-chevron-right"></i>
            </a>
            <a href="/user/billing/balance-history"
                class="{if $current_route == 5}bg-quinary{/if} rounded-2 p-3 nav-link d-flex align-items-center justify-content-between">
                Top up & withdraw history
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</div>