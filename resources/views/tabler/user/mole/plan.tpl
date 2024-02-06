{include file='user/mole/component/head.tpl'}

<body class="bg-main container-fluid h-100 p-0 d-flex poppins">
    {include file='user/mole/component/nav.tpl'}
    <div class="h-100 px-5 py-4 w-100"
        style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
        <div class="p-2">
            <div class="fs-2 fw-bold text-light">Manage Plan</div>
        </div>
        <div class="d-flex flex-wrap align-items-center">
            <div class="col-12 p-2">
                <div class="card rounded-4 bg-secondary text-light py-4 px-5">
                    {include file='user/mole/component/plan/current_plan.tpl'}
                    <hr class="mx-5 my-4" />
                    {include file='user/mole/component/plan/avaliable_plans.tpl'}
                </div>
            </div>
        </div>
    </div>
</body>

</html>