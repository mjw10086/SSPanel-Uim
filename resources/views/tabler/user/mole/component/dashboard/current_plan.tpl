<div class="col-4 p-2">
    <div class="card rounded-4 bg-secondary p-4 d-flex">
        <div class="fs-1 fw-bold text-light">{$activated_order.product_name}</div>
        <span class="fs-7 fw-light text-lightgray">
            {$user.transfer_enable/1024/1024/1024}GB/Month with up to {$user.node_iplimit} devices
        </span>
        <a href="/user/plan" class="btn btn-default mt-4 fs-5 fw-normal text-light">Manage Plan</a>
    </div>
</div>