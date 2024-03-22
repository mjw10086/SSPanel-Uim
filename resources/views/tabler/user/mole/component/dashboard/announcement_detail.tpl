<div>
    <span class="fs-7 fw-light text-lightgray" id="announcementDetailTitle">
        {$ann.date|date_format:"%Y-%m-%d"} {$ann.title}
    </span>
    <p class="fs-5 fw-light text-light mt-3" id="announcementDetailContent">
        {$ann.content}
    </p>
</div>

<div class="border-0">
    <div class="w-100 d-flex justify-content-between fs-5">
        <div class="col-6 pe-2">
            <button class="w-100 btn btn-outline-info fw-normal" id="dismiss_btn" data-bs-dismiss="modal"
                aria-label="Close" hx-post="/user/announcement/{$ann.id}/read" hx-swap="none">
                Dismiss
            </button>
        </div>
        <div class="col-6 ps-2">
            <button class="w-100 btn btn-default fw-normal" id="mark_unread_btn" data-bs-dismiss="modal"
                aria-label="Close" hx-post="/user/announcement/{$ann.id}/unread" hx-swap="none">
                Mark unread
            </button>
        </div>
    </div>
</div>