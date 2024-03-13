<div class="modal-header border-0">
    <h1 class="modal-title fs-4 fw-bold" id="operationResult">{$status}</h1>
    <button type="button" class="btn-close btn-close-white" onclick="reloadPage()"></button>
</div>
<div class="modal-body border-0 fs-5 fw-light my-1">
    {$message}
</div>
<div class="modal-footer border-0">
    <div class="w-100 d-flex justify-content-between fs-5">
        <button class="w-100 btn btn-outline-info fw-normal" onclick="reloadPage()">
            OK
        </button>
    </div>
</div>

<script>
    function reloadPage() {
        location.reload();
    }
</script>