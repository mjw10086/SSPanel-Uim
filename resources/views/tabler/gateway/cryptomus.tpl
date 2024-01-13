<div class="card-inner">
    <h4>
        Cryptomus
    </h4>
    <p class="card-heading"></p>
    <div id="paypal-button-container">
        <form action="/user/payment/purchase/cryptomus" method="post">
            <div class="form-group form-group-label">
                <input id="price" name="price" value="{$invoice->price}" hidden>
                <input id="invoice_id" name="invoice_id" value="{$invoice->id}" hidden>
                <button class="btn btn-flat waves-attach" type="submit"><i class="icon ti ti-credit-card"></i></button>
            </div>
        </form>
    </div>
</div>