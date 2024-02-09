<div class="col-7 py-4 pe-5 ps-4 h-100">
    <div>
        <div class="fs-7 fw-light">search</div>
        <div class="input-group mt-2 mb-4">
            <span class="input-group-text rounded-start-pill bg-quinary border-0 text-light"><i
                    class="bi bi-search"></i></span>
            <input type="text" class="form-control rounded-end-pill bg-quinary border-0 text-light"
                placeholder="Search">
        </div>
    </div>
    <div class="fs-5 fw-bold">Answers to your questions</div>
    <hr />
    {foreach $faq_list as $faq}
        <div class="d-flex fs-6 fw-light gap-3 faq_folder justify-content-between">
            <span data-parameter="{$faq.id}">
                {$faq.title}
            </span>
            <i class="bi bi-plus-lg" data-parameter="{$faq.id}"></i>
        </div>
        <div class="visually-hidden fs-4 fw-light text-gray mt-2" id="a_{$faq.id}">
            {$faq.content}
        </div>
        <hr class="mt-4" />
    {/foreach}
</div>

<script>
    var buttons = document.querySelectorAll('.faq_folder');

    function handleClick(event) {
        var parameter = event.target.dataset.parameter;
        var answer = document.getElementById('a_' + parameter);
        answer.classList.toggle('visually-hidden');
    }

    buttons.forEach(function(button) {
        button.addEventListener('click', handleClick);
    });
</script>