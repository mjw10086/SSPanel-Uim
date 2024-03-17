{include file='user/mole/component/head.tpl'}

<body class="bg-main h-100">
    <div class="container-fluid h-100 p-0 d-flex poppins" style="max-width: 1440px;">
        {include file='user/mole/component/nav.tpl'}
        <div class="h-100 px-5 py-4"
            style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
            <div class="p-2">
                <div class="fs-2 fw-bold text-light">Dashboard</div>
            </div>
            <div class="d-flex flex-wrap">
                {include file='user/mole/component/dashboard/current_plan.tpl'}
                {include file='user/mole/component/dashboard/balance.tpl'}
                <div class="col-4">
                    {if $activated_order !== null}
                        {include file='user/mole/component/dashboard/devices.tpl'}
                        {include file='user/mole/component/dashboard/quota.tpl'}
                    {/if}
                </div>
                <div class="col-8 p-2">
                    {include file='user/mole/component/dashboard/announcements.tpl'}
                </div>
            </div>
        </div>
    </div>
</body>

</html>