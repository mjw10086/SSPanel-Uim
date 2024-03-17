<div class="fs-4 fw-bold">Top up & withdrawal history</div>
<div class="fs-6 fw-light mt-2">blahNam quis convallis elit. Sed fermentum magna nec
</div>
<div class="py-3 col-10"
    style="height: calc(100% - 50px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <table class="table fs-6 fw-light table-empty">
        <thead>
            <tr>
                <th scope="col" class="text-gray">Date</th>
                <th scope="col" class="text-gray text-end">Type</th>
                <th scope="col" class="text-gray text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            {foreach $balance_history as $history}
                <tr>
                    <th scope="row" class="fw-light">{$history.create_time|date_format:"%b %e, %Y"}</th>
                    <th scope="row" class="fw-light text-end">{$history.type}</th>
                    <td class="fw-light text-end">{if $history.type eq "withdraw"}-{else}+{/if}${$history.amount}</td>
                </tr>
            {{/foreach}}
        </tbody>
    </table>
</div>