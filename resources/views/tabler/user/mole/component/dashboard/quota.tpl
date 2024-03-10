{assign "quota" ["total" => 50, "available" => 40, "reset_date" => 1709126493 ]}

<div class="p-2">
    <div class="card rounded-4 bg-secondary text-bg-dark p-4">
        <div class="fs-4 fw-bold text-light">Remaining Data This Month</div>
        <span class="fs-7 fw-light text-light">Resets on
            {$data.current_plan.next_reset_date|date_format:"%b %e, %Y"}</span>
        <div class="usage_container">
            <div class="circle w-50">
                <svg id="usage" data-percent="0.8">
<circle id="circle" cx="50%" cy="50%" r="35%" stroke="{if $user.transfer_enable == 0}#202232{else}#00FF00{/if}" stroke-width="10"
                        fill="transparent" />
                </svg>
                <div class="text d-flex align-items-end">
                    <span class="m-0 p-0 fs-2">{$data_usage/1024/1024/1024}GB</span>
                    <span class="m-0 p-0 fs-7 fw-light text-light">/{$user.transfer_enable/1024/1024/1024}GB</span>
                </div>
            </div>
        </div>
        <a class="btn btn-info fs-5 fw-bold" href="/user/plan/addition-quota">Get one-time extra data</a>
    </div>
</div>

<style>
    .usage_container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .circle {
        border-radius: 50%;
        position: relative;
    }

    svg {
        width: 100%;
        height: 100%;
    }

    .text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        font-weight: bold;
    }
</style>

{* <script>
    let size = document.getElementById("usage").clientWidth * 0.85;
    console.log(size)
    let circle = document.getElementById("circle");
    let percent = parseFloat(document.getElementById("usage").getAttribute("data-percent"));
    console.log(percent);
circle.setAttribute("stroke-dasharray", `${size * 3.14 * percent} ${size * 3.14 * (1 - percent)}`);
circle.setAttribute("stroke-dashoffset", `${-size * 3.14 * (1 - percent) / 2}`);
</script> *}