<div class="card rounded-4 bg-secondary p-4">
    <div class="fs-4 fw-bold mb-3 text-light">Announcements</div>
    {foreach $data.announcements as $ann}
        <div class="my-3">
            <span class="fs-7 fw-light text-lightgray">
                {$ann.create_date|date_format:"%Y-%m-%d"} {$ann.title}
            </span>
            <p class="fs-5 fw-light text-light mt-3">
                {$ann.content}
            </p>
            <div class="d-flex mt-4 fs-5">
                <div class="col-6 pe-2">
                    <button class="w-100 btn btn-default fw-normal">Open</button>
                </div>
                <div class="col-6 ps-2">
                    <button class="w-100 btn btn-outline-default fw-normal">Dismiss</button>
                </div>
            </div>
        </div>
        {if not $ann@last}
            <hr class="mx-5 my-4 border border-gray" />
        {/if}
    {{/foreach}}
</div>