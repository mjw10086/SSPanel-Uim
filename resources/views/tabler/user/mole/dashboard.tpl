{include file='user/mole/component/head.tpl'}

<body class="bg-main container-fluid h-100 p-0 d-flex poppins">
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
                {include file='user/mole/component/dashboard/devices.tpl'}
                {include file='user/mole/component/dashboard/quota.tpl'}
            </div>
            <div class="col-8 p-2">
                {include file='user/mole/component/dashboard/announcements.tpl'}
            </div>
        </div>
    </div>
</body>

</html>