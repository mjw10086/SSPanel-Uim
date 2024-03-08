{include file='user/mole/component/head.tpl'}

<body class="bg-main h-100">
    <div class="container-fluid h-100 p-0 d-flex poppins" style="max-width: 1440px;">
        {include file='user/mole/component/nav.tpl'}
        <div class="panel h-100 px-5 py-4 w-100"
            style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
            <div class="p-2">
                <div class="fs-2 fw-bold text-light">Balance & Billing</div>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="height: calc(100% - 55px);">
                <div class="col-12 p-2 h-100">
                    <div class="card rounded-4 bg-black text-bg-dark p-4 h-100">
                        <div class="col-12 d-flex gap-5 h-100">
                            {include file='user/mole/component/billing/billing_nav.tpl'}
                            <div class="col-7">
                                {if $smarty.server.REQUEST_URI eq "/user/billing/one-time-topup"}
                                    {include file='user/mole/component/billing/onetime_topup.tpl'}
                                {elseif $smarty.server.REQUEST_URI eq "/user/billing/automatic-topups"}
                                    {include file='user/mole/component/billing/automatic_topup.tpl'}
                                {elseif $smarty.server.REQUEST_URI eq "/user/billing/withdraw"}
                                    {include file='user/mole/component/billing/withdraw.tpl'}
                                {elseif $smarty.server.REQUEST_URI eq "/user/billing/billing-history"}
                                    {include file='user/mole/component/billing/billing_history.tpl'}
                                {elseif $smarty.server.REQUEST_URI eq "/user/billing/balance-history"}
                                    {include file='user/mole/component/billing/topup_withdraw_history.tpl'}
                                {else}
                                    {include file='user/mole/component/billing/onetime_topup.tpl'}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>