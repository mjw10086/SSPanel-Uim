<div class="fs-4 fw-bold">Billing History</div>
<div class="fs-6 fw-light mt-2">You can see here the history of balance top ups and balance withdrawals
</div>
<div class="py-3 col-11"
    style="height: calc(100% - 50px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: darkgrey lightgrey;">
    <table class="table fs-6 fw-light table-empty">
        <thead>
            <tr>
                <th scope="col" class="text-gray">Date</th>
                <th scope="col" class="text-gray">Plan</th>
                <th scope="col" class="text-gray text-end">Price</th>
                <th scope="col" class="text-gray text-end">Doc</th>
            </tr>
        </thead>
        <tbody>
            {foreach $billing_history as $billing}
                <tr>
                    <th scope="row" class="fw-light">{$billing.create_time|date_format:"%b %e, %Y"}</th>
                    <td class="fw-light">{$billing.remark}</td>
                    <td class="fw-light text-end">{if $billing.amount < 0}-{else}+{/if}${abs($billing.amount)}</td>
                    <td class="fw-light text-end"><a href="#">Invoice</a></td>
                </tr>
            {{/foreach}}
        </tbody>
    </table>
</div>