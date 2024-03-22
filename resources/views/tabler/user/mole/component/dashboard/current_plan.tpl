<div class="col-4 p-2">
    <div class="card rounded-4 bg-secondary p-4 d-flex">
        {if $activated_order !== null}
            <div class="fs-1 fw-bold text-light">{$activated_order.product_name}</div>
            <span class="fs-7 fw-light text-lightgray">
                {$user.transfer_enable/1024/1024/1024}GB/Month with up to {$user.node_iplimit} devices
            </span>
        {else}
            <div class="fs-1 fw-bold text-light">no plan currently</div>
            <span class="fs-7 fw-light text-lightgray mb-3"></span>
        {/if}
        <a href="/user/plan" class="btn btn-default mt-4 fs-5 fw-normal text-light">Manage plan</a>
    </div>
</div>