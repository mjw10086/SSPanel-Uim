<div class="col-4 p-2">
    <div class="card rounded-4 bg-secondary p-4 d-flex">
        <div class="fs-1 fw-bold text-light">{$data.current_plan.name}</div>
        <span class="fs-7 fw-light text-lightgray">
            {$data.current_plan.data_quota/1024/1024}GB/Month with up to {$data.current_plan.devices_limit} devices
        </span>
        <a href="/user/plan" class="btn btn-default mt-4 fs-5 fw-normal text-light">Manage Plan</a>
    </div>
</div>