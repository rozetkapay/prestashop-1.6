<script>
    $('#tabOrder').append('<li><a href="#rozetkapay_order"><i class="icon-file-text"></i> RozetkaPay </a></li>');
</script>
<div class="tab-pane" id="rozetkapay_order">

    <div rozetkapay_alert></div>

    <div class="form-group">
        <label class="col-sm-2 control-labe text-right"> {$text_refund_amount}</label>
        <div class="col-sm-2">
            <input type="number" id="rozetkapay_refund" min="1" value="{$total}" max="{$total}" class="form-control">
        </div>
        <div class="col-sm-8">
            <button data-loading-text="" onclick="RozetkaPayRedund()" class="btn btn-primary"><i class="fa fa-reply"></i> 
                {$text_refund_button}
            </button>
        </div>
    </div>
    <hr>
    
    <div class="form-group" rozetkapay_list_transaction>
        <div class="form-horizontal">
            <div class="table-responsive">
                <table class="table" id="shipping_table">
                    <thead>
                        <tr>
                            <th>
                                <span class="title_box ">{$text_transaction_datatime}</span>
                            </th>
                            <th>
                                <span class="title_box ">{$text_transaction_amount}</span>
                            </th>
                            <th>
                                <span class="title_box ">{$text_transaction_status}</span>
                            </th>
                            <th>
                                <span class="title_box ">{$text_transaction_type}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
    </div>

    <script>


        

        var alertBox = {
            'success': function (text) {
                $('[rozetkapay_alert]').html('<div class="alert alert-success">' +
                        '<i class="fa fa-exclamation-circle"></i> <span>' +
                        text + '</span>' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '</div>')
            },
            'error': function (text) {
                $('[rozetkapay_alert]').html('<div class="alert alert-danger">' +
                        '<i class="fa fa-exclamation-circle"></i> <span>' +
                        text + '</span>' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '</div>')
            }
        }
        $(document).ready(function () {
            RozetkaPayInfo()
        })

        function RozetkaPayInfo() {
            $.ajax({
                url: '{$urlRayInfo}',
                type: 'post',
                data: {
                    id_order: '{$id_order}'
                },
                dataType: 'json'
            }).done(function (json) {
                console.log(json);
                if (json.error !== false) {
                    for (var i in json.error) {
                        item = json.error[i]
                        json.alert += '<br>' + item
                    }
                }
                if (json.ok) {
                    let table = $('[rozetkapay_list_transaction] table tbody')
                    let item
                    table.html('')
                    for (var i in json.details) {

                        item = json.details[i]

                        table.append(
                                '<tr>' +
                                ' <td class="text-left">' + item.created_at + '</td>' +
                                ' <td class="text-left">' + item.amount + ' ' + item.currency + '</td>' +
                                ' <td class="text-left">' + item.status + '</td>' +
                                ' <td class="text-left">' + item.type + '</td>' +
                                '</tr>'
                                )

                    }
                } else {

                }

            });
        }
        function RozetkaPayRedund() {

            let total = $('#rozetkapay_refund').val()

            if (total > 0 && confirm('{$text_confirm}')) {
                $('[onclick="RozetkaPayRedund()"').prop('disabled', true)
                $.ajax({
                    url: '{$urlPayRefund}',
                    type: 'post',
                    data: {
                        id_order: '{$id_order}',
                        total: total
                    },
                    dataType: 'json'
                }).done(function (json) {
                    console.log(json);

                    if (json.error !== false) {
                        for (var i in json.error) {
                            item = json.error[i]
                            json.alert += '<br>' + item
                        }
                    }

                    if (json.ok) {
                        alertBox.success(json.alert)
                    } else {
                        alertBox.error(json.alert)
                    }


                    $('[onclick="RozetkaPayRedund()"').prop('disabled', false)
                    RozetkaPayInfo();

                });

            }

        }
    </script>
</div>