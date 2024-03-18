{include file='admin/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">Withdraw #{$withdraw->id}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">operate withdraw</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Info</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">user id</div>
                                    <div class="datagrid-content">{$withdraw.user_id}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">type</div>
                                    <div class="datagrid-content">{$withdraw.type}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">amount</div>
                                    <div class="datagrid-content">${$withdraw.amount}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">create time</div>
                                    <div class="datagrid-content">{$withdraw.create_time}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">update time</div>
                                    <div class="datagrid-content">{$withdraw.update_time}</div>
                                </div>
                            </div>
                            <div class="datagrid-item" style="margin-top: 25px;">
                                <div class="datagrid-title">to account</div>
                                <div class="datagrid-content">{$withdraw.to_account}</div>
                            </div>
                            <div class="datagrid-item" style="margin-top: 25px;">
                                <div class="datagrid-title">addition message</div>
                                <div class="datagrid-content">{$withdraw.addition_msg}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-light">
                            <h3 class="card-title">Operation</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">status</label>
                                <div class="col">
                                    <select id="status" name="status" class="col form-select">
                                        <option value="pending" {if $withdraw.status === "pending"}selected{/if}>pending
                                        </option>
                                        <option value="rejected" {if $withdraw.status === "rejected"}selected{/if}>
                                            rejected</option>
                                        <option value="success" {if $withdraw.status === "success"}selected{/if}>success
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">transfer id</label>
                                <div class="col">
                                    <input id="transfer_id" name="transfer_id" type="text" class="form-control"
                                        value="{$withdraw.transfer_id}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">note</label>
                                <div class="col">
                                    <input id="note" name="note" type="text" class="form-control"
                                        value="{$withdraw.note}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button id="save-product" type="submit" href="#" class="btn btn-primary"
                                    style="margin-top: 20px;">
                                    <i class="icon ti ti-device-floppy"></i>
                                    save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#save-product").click(function() {
        $.ajax({
            url: '/admin/withdraw/{$withdraw->id}',
            type: 'POST',
            dataType: "json",
            data: {
                "status": $('#status').val(),
                "note": $('#note').val(),
                "transfer_id": $('#transfer_id').val(),
            },
            success: function(data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    $('#success-dialog').modal('show');
                    window.setTimeout("location.href=top.document.referrer", {$config['jump_delay']});
                } else {
                    $('#fail-message').text(data.msg);
                    $('#fail-dialog').modal('show');
                }
            }
        })
    });
</script>

{include file='admin/footer.tpl'}