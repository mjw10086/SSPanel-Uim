{include file='user/mole/component/head.tpl'}

<body class="bg-main h-100">
    <div class="container-fluid h-100 p-0 d-flex poppins" style="max-width: 1440px;">
        {include file='user/mole/component/nav.tpl'}
        <div class="h-100 px-5 py-4 w-100"
            style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
            <div class="p-2">
                <div class="fs-2 fw-bold text-light">Devices And App</div>
            </div>
            {include file='user/mole/component/devices/devices_manage.tpl'}
        </div>
    </div>

    {include file='user/mole/component/notification.tpl'}
</body>

</html>