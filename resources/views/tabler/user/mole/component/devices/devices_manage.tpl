<div class="col-12 p-2 {if $smarty.server.REQUEST_URI neq "/user/devices/activate"}h-100{/if}" id="devices_and_app_panel">
    <div class="card rounded-4 bg-secondary text-light px-4 py-3 h-100">
        <div class="d-flex">
            {include file='user/mole/component/devices/devices_list.tpl'}
            <div class="{if $smarty.server.REQUEST_URI neq "/user/devices/activate"}visually-hidden{/if} col-6 p-3"
                id="activate_app_panel">
                {include file='user/mole/component/devices/activation.tpl'}
            </div>
        </div>
    </div>
</div>