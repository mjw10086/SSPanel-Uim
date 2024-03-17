{include file='user/mole/component/head.tpl'}

<body class="bg-main h-100">
    <div class="container-fluid h-100 p-0 d-flex poppins" style="max-width: 1440px;">
        {include file='user/mole/component/nav.tpl'}
        <div class="h-100 px-5 py-4 w-100"
            style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
            <div class="p-2">
                <div class="fs-2 fw-bold text-light">FAQ</div>
            </div>
            <div class="col-12 p-2">
                <div class="card rounded-4 bg-secondary text-light px-4 py-3 h-100">
                    <div class="d-flex">
                        {include file='user/mole/component/faq/faqs.tpl'}
                        {include file='user/mole/component/faq/contact.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {include file='user/mole/component/notification.tpl'}
</body>

</html>