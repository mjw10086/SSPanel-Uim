{include file='user/mole/component/head.tpl'}

<body class="bg-main container-fluid h-100 p-0 d-flex poppins">
    {include file='user/mole/component/nav.tpl'}
    <div class="h-100 px-5 py-4 w-100"
        style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
        <div class="p-2">
            <div class="fs-2 fw-bold text-light">Devices And App</div>
        </div>
        {include file='user/mole/component/devices/devices_manage.tpl'}
    </div>
    </div>
</body>

</html>