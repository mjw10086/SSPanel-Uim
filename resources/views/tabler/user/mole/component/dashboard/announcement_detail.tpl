<div>
    <span class="fs-7 fw-light text-lightgray" id="announcementDetailTitle">
        {$ann.date|date_format:"%Y-%m-%d"} {$ann.title}
    </span>
    <p class="fs-5 fw-light text-light mt-3" id="announcementDetailContent">
        {$ann.content}
    </p>
</div>
