<div class="col-7 py-4 pe-5 ps-4 h-100">
    <div>
        <div class="fs-7 fw-light">search</div>
        <div class="input-group mt-2 mb-4">
            <span class="input-group-text rounded-start-pill bg-quinary border-0 text-light"><i
                    class="bi bi-search"></i></span>
            <input type="text" id="faq_search" class="form-control rounded-end-pill bg-quinary border-0 text-light"
                placeholder="Search">
        </div>
    </div>
    <div class="fs-5 fw-bold">Answers to your questions</div>
    <hr />
    <div id="faq_list">
    </div>
</div>

<script>
    function render_faq(faq_list) {
        var faq_arr = "";

        for (var key in faq_list) {
            var faq_html = '<div class="d-flex fs-6 fw-light gap-3 faq_folder justify-content-between">' +
                '<span data-parameter="' + faq_list[key]["id"] + '">' +
                faq_list[key]["title"] +
                '</span>' +
                '<i class="bi bi-plus-lg" data-parameter="' + faq_list[key]["id"] + '"></i>' +
                '</div>' +
                '<div class="visually-hidden fs-4 fw-light text-gray mt-2" id="a_' + faq_list[key]["id"] + '">' +
                faq_list[key]["content"] +
                '</div>' +
                '<hr class="mt-4" />';

            faq_arr += faq_html;
        }

        document.getElementById('faq_list').innerHTML = faq_arr;


        var buttons = document.querySelectorAll('.faq_folder');

        function handleClick(event) {
            var parameter = event.target.dataset.parameter;
            var answer = document.getElementById('a_' + parameter);
            answer.classList.toggle('visually-hidden');
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', handleClick);
        });
    }

    var faq_list = {};

    {foreach $faq_list as $key => $value}
        faq_list[{$key|json_encode}] = {$value|json_encode};
    {/foreach}

    render_faq(faq_list)

    var search_input = document.getElementById('faq_search');

    search_input.addEventListener('input', function(event) {
        var result = filter_faq(event.target.value)
        render_faq(result);
    })

    function filter_faq(input) {
        var res = {};
        for (var key in faq_list) {
            if (faq_list[key]["content"].toLowerCase().includes(input.toLowerCase()) ||
                faq_list[key]["title"].toLowerCase().includes(input.toLowerCase())) {
                res[key] = faq_list[key];
            }
        }
        return res;
    }
</script>