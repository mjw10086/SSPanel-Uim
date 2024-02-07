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
                    <button class="w-100 btn btn-default fw-normal" data-bs-target="#announcementDetailModal"
                        data-bs-toggle="modal" data-bs-annid="{$ann.id}">Open</button>
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


<div class="modal fade" id="announcementDetailModal" aria-hidden="true" aria-labelledby="announcementDetailModalLabel"
    tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2 bg-quinary text-light opacity-100 p-3">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-4 fw-bold" id="announcementDetailModalLabel">Announcements</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body border-0 fs-5 fw-light my-1">
                <div>
                    <span class="fs-7 fw-light text-lightgray" id="announcementDetailTitle">

                    </span>
                    <p class="fs-5 fw-light text-light mt-3" id="announcementDetailContent">

                    </p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <div class="w-100 d-flex justify-content-between fs-5">
                    <div class="col-6 pe-2">
                        <button class="w-100 btn btn-outline-info fw-normal">
                            Dismiss
                        </button>
                    </div>
                    <div class="col-6 ps-2">
                        <button class="w-100 btn btn-default fw-normal">
                            Mark unread
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var announcementDetailModal = document.getElementById('announcementDetailModal')
    announcementDetailModal.addEventListener('show.bs.modal', event => {
        var button = event.relatedTarget
        var recipient = button.getAttribute('data-bs-annid')
        var anntitle = announcementDetailModal.querySelector('#announcementDetailTitle')
        var annContent = announcementDetailModal.querySelector('#announcementDetailContent')

        modalTitle.textContent = 'New message to ' + recipient
        modalBodyInput.value = recipient
    })
</script>