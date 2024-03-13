{include file='user/mole/component/head.tpl'}

<body class="bg-main h-100">
    <div class="container-fluid h-100 p-0 d-flex poppins" style="max-width: 1440px;">
        {include file='user/mole/component/nav.tpl'}
        <div class="panel h-100 px-5 py-4 w-100"
            style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
            <div class="p-2">
                <div class="fs-2 fw-bold text-light">Account</div>
            </div>
            <div
                class="d-flex flex-wrap align-items-center {if $smarty.server.REQUEST_URI eq "/user/account/notification"} h-100 {/if}">
                <div class="col-12 p-2 {if $smarty.server.REQUEST_URI eq "/user/account/notification"} h-100 {/if}">
                    <div
                        class="card rounded-4 bg-secondary text-bg-dark pt-5 px-4 {if $smarty.server.REQUEST_URI eq "/user/account/notification"} h-100 {/if}">
                        <img src="/assets/icons/account-info.svg" style="height: 60px;" alt="icon" class="mb-3">
                        <span class="text-center fw-bold mb-2" style="font-size: 22px;">Manage account</span>
                        <span class="text-center fs-6 fw-light mb-3">Protected by Ironlink since
                            {$data.account_info.join_data|date_format:"%b %e, %Y"}</span>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-8 d-flex justify-content-between fs-4 fw-normal">
                                <a href="/user/account/info"
                                    class="nav-link col-6 border-bottom text-center p-2 {if $smarty.server.REQUEST_URI neq "/user/account/notification"} border-info text-info {/if}">Account</a>
                                <a href="/user/account/notification"
                                    class="nav-link col-6 border-bottom text-center p-2 {if $smarty.server.REQUEST_URI eq "/user/account/notification"} border-info text-info {/if}">Notification</a>
                            </div>
                        </div>
                        {if $smarty.server.REQUEST_URI eq "/user/account/info"}
                            {include file='user/mole/component/account/account.tpl'}
                        {elseif $smarty.server.REQUEST_URI eq "/user/account/notification"}
                            {include file='user/mole/component/account/notification.tpl'}
                        {else}
                            {include file='user/mole/component/account/account.tpl'}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>