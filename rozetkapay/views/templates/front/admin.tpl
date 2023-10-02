
{if isset($keygen_success)}
    <div class="alert alert-success">{l s='Key generation successful. [1]Request your Public Key ID[/1]. Amazon Pay will send your Public Key ID to the email address associated with your Amazon Pay merchant account. Check your inbox for an email from Amazon Pay with your [2]Public Key ID[/2], and then enter it to the Public Key ID field.' tags=['<a href="JavaScript:void(0)" id="public_key_mail_init_2">', '<b>']  mod='amazonpay'}</div>
{elseif isset($keygen_error)}
    <div class="alert alert-warning" role="alert">{l s='Key generation failed. To generate your keys manually, follow the steps in the Amazon Pay integration guide: ' mod='amazonpay'} <a href="https://developer.amazon.com/docs/amazon-pay-automatic/delivery-notifications.html#keys" target="_blank">https://developer.amazon.com/docs/amazon-pay-automatic/delivery-notifications.html</a></div>
{/if}

<ul class="nav nav-tabs" role="tablist">    
    <li class="active"><a href="#general" role="tab" data-toggle="tab">{$tab_general}</a></li>
    <li><a href="#order_status" role="tab" data-toggle="tab">{$text_tab_order_status}</a></li>
    <li><a href="#view" role="tab" data-toggle="tab">{$text_tab_view}</a></li>
    <li><a href="#sandbox" role="tab" data-toggle="tab">{l s='SandBox'}</a></li>
</ul>
<form class="defaultForm form-horizontal" action="{$url_submit}" method="post" enctype="multipart/form-data" novalidate="">

    <div class="tab-content">
        <div class="tab-pane active" id="general">
            <div class="panel">

                <div class="row"> 

                    <div class="form-group required">
                        <label class="control-label col-lg-2 text-right">
                            {$text_login}
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="login" value="{$setting_login}" class="form-control">
                            {if $error_login }
                                <div class="text-danger">{$error_login}</div>
                            {/if}
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-lg-2 text-right">
                            {$text_password}
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="password" value="{$setting_password}" class="form-control">
                            {if $error_login }
                                <div class="text-danger">{$error_login}</div>
                            {/if}
                        </div>
                    </div>


                    <div class="form-group">

                        <label class="control-label col-lg-2 text-right">
                            {$text_qr_code}
                        </label>
                        <div class="col-lg-9">                            
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="qr_code" id="qr_code_on" value="1"{if $setting_qr_code == 1 }checked="checked"{/if}>
                                <label for="qr_code_on">{l s='Yas'}</label>
                                <input type="radio" name="qr_code" id="qr_code_off" value="0"{if $setting_qr_code == 0 }checked="checked"{/if}>
                                <label for="qr_code_off">{l s='No'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{$text_send_info_customer_status}</label>
                        <div class="col-sm-2">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="send_info_customer_status" id="send_info_customer_status_on"
                                       {if $setting_send_info_customer_status == 1 }checked="checked"{/if} value="1">
                                <label for="send_info_customer_status_on">{l s='Yas'}</label>
                                <input type="radio" name="send_info_customer_status" id="send_info_customer_status_off"
                                       {if $setting_send_info_customer_status == 0 }checked="checked"{/if} value="0">
                                <label for="send_info_customer_status_off">{l s='No'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>

                        <label class="col-sm-2 control-label">{$text_send_info_products_status}</label>
                        <div class="col-sm-2">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="send_info_product_status" id="send_info_product_status_on"
                                       {if $setting_send_info_product_status == 1 }checked="checked"{/if} value="1">
                                <label for="send_info_product_status_on">{l s='Yas'}</label>
                                <input type="radio" name="send_info_product_status" id="send_info_product_status_off"
                                       {if $setting_send_info_product_status == 0 }checked="checked"{/if} value="0">
                                <label for="send_info_product_status_off">{l s='No'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>

                </div>

            </div>


        </div>

        <div class="tab-pane" id="order_status">
            <div class="panel">

                <div class="row">    
                    <div class="form-group">
                        <label class="control-label col-lg-3">{$text_order_status_init}</label>
                        <div class="col-lg-3">
                            <select name="order_status_init" class=" fixed-width-xl" id="order_status_init">
                                <option value="0" {if $setting_order_status_init == 0 } selected="selected"{/if}>---</option>
                                {foreach from=$order_statuses item=order_status}
                                    <option value="{$order_status.id_order_state}"
                                            {if $setting_order_status_init == $order_status.id_order_state } selected="selected"{/if}
                                            >{$order_status.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">{$text_order_status_pending}</label>
                        <div class="col-lg-3">
                            <select name="order_status_pending" class=" fixed-width-xl" id="order_status_pending">
                                <option value="0" {if $setting_order_status_pending == 0 } selected="selected"{/if}>---</option>
                                {foreach from=$order_statuses item=order_status}
                                    <option value="{$order_status.id_order_state}"
                                            {if $setting_order_status_pending == $order_status.id_order_state } selected="selected"{/if}>
                                        {$order_status.name}</option>
                                    {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">{$text_order_status_success}</label>
                        <div class="col-lg-3">
                            <select name="order_status_success" class=" fixed-width-xl" id="order_status_success">
                                {foreach from=$order_statuses item=order_status}
                                    <option value="{$order_status.id_order_state}"
                                            {if $setting_order_status_success == $order_status.id_order_state } selected="selected"{/if}
                                            >{$order_status.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">{$text_order_status_failure}</label>
                        <div class="col-lg-3">
                            <select name="order_status_failure" class=" fixed-width-xl" id="order_status_failure">
                                {foreach from=$order_statuses item=order_status}
                                    <option value="{$order_status.id_order_state}"
                                            {if $setting_order_status_failure == $order_status.id_order_state } selected="selected"{/if}>
                                        {$order_status.name}</option>
                                    {/foreach}
                            </select>
                        </div>
                    </div>


                </div>

            </div>



        </div>


        <div class="tab-pane" id="view">
            <div class="panel">
                <div class="row"> 


                    <div class="form-group">
                        <label class="control-label col-lg-2 text-right">
                            {$text_view_title_default}
                        </label>
                        <div class="col-lg-9">                            
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="view_title_default" id="view_title_default_on" 
                                       {if $setting_view_title_default == 1 }checked="checked"{/if} value="1">
                                <label for="view_title_default_on">{l s='Yas'}</label>
                                <input type="radio" name="view_title_default" id="view_title_default_off" 
                                       {if $setting_view_title_default == 0 }checked="checked"{/if}value="0">
                                <label for="view_title_default_off">{l s='No'}</label>                                
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">{l s=''}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status">{$text_view_title}</label>
                        <div class="col-sm-4">
                            {foreach $languages as $language}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <!--<img src="../img/tmp/lang_mini_1_1.jpg?time=1690181549" alt="" class="imgm img-thumbnail"> -->
                                        {$language.name}
                                    </span>
                                    <input type="text" name="view_title[{$language.iso_code}]"
                                           value="{if isset($setting_view_title[$language.iso_code])}{$setting_view_title[$language.iso_code]}{/if}"
                                           minlength="5" maxlength="80" class="form-control" />
                                </div>
                                {if isset($error_title[$language.iso_code])}
                                    <div class="text-danger">{$error_title[$language.iso_code]}</div>
                                {/if}
                            {/foreach}                            
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-lg-2 text-right">
                            {$text_view_icon_status}
                            <img src="../modules/rozetkapay/img/logo.png" height="32">
                        </label>
                        <div class="col-lg-9">                            
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="view_icon_status" id="view_icon_status_on" 
                                       {if $setting_view_icon_status == 1 }checked="checked"{/if} value="1">
                                <label for="view_icon_status_on">{l s='Yas'}</label>
                                <input type="radio" name="view_icon_status" id="view_icon_status_off" 
                                       {if $setting_view_icon_status == 0 }checked="checked"{/if}value="0">
                                <label for="view_icon_status_off">{l s='No'}</label>                                
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="tab-pane" id="sandbox">
            <div class="panel">

                <div class="row"> 

                    <div class="form-group">
                        <div class="warning col-sm-12">
                            <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> 
                                {$text_help_test}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-2 text-right">
                            {l s='Sandbox'}
                        </label>
                        <div class="col-lg-9">                            
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="sandbox_status" id="sandbox_status_on" 
                                       {if $setting_sandbox_status == 1 }checked="checked"{/if} value="1">
                                <label for="sandbox_status_on">{l s='Yas'}</label>
                                <input type="radio" name="sandbox_status" id="sandbox_status_off" 
                                       {if $setting_sandbox_status == 0 }checked="checked"{/if}value="0">
                                <label for="sandbox_status_off">{l s='No'}</label>                                
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">{l s=''}</p>
                        </div>
                    </div>


                    <div class="form-group">

                        <label class="control-label col-lg-2 text-right">
                            {$text_log_status}
                        </label>
                        <div class="col-lg-9">                            
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="log_status" id="log_status_on" 
                                       {if $setting_log_status == "1" }checked="checked"{/if} value="1">
                                <label for="log_status_on">{l s='Yas'}</label>
                                <input type="radio" name="log_status" id="log_status_off" 
                                       {if $setting_log_status == "0" }checked="checked"{/if} value="0">
                                <label for="log_status_off">{l s='No'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">{l s=''}</p>
                        </div>

                    </div>


                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status">{$text_test_cards}</label>
                        <div class="col-sm-10">
                            <div class="well well-sm">
                                card=4242424242424242  exp=any cvv=any  3ds=Yes result=success<br>
                                card=5454545454545454  exp=any cvv=any  3ds=Yes result=success<br>
                                card=4111111111111111  exp=any cvv=any  3ds=No result=success<br>
                                card=4200000000000000  exp=any cvv=any  3ds=Yes result=rejected<br>
                                card=5105105105105100  exp=any cvv=any  3ds=Yes result=rejected<br>
                                card=4444333322221111  exp=any cvv=any  3ds=No result=rejected<br>
                                card=5100000020002000  exp=any cvv=any  3ds=No result=rejected<br>
                                card=4000000000000044  exp=any cvv=any  3ds=No result=insufficient-funds<br>
                            </div>
                        </div>
                    </div>


                    <div id="log_warning" class="form-group">
                        <div class="warning col-sm-12">
                            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> 
                                <div id="log_warning_text"></div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-exclamation-triangle"></i>{$text_test_log_title}</h3>
                            <div class="pull-right">
                                <a id="log_refresh" onclick="nt_log_refresh()" data-toggle="tooltip" class="btn btn-warning">
                                    <i class="fa fa-refresh"></i> {$button_log_refresh}
                                </a>
                                <a href="{$urlLogDownload}" target="_blank" data-toggle="tooltip" class="btn btn-primary">
                                    <i class="fa fa-download"></i>{$button_log_download}
                                </a>
                                <a href="{$urlLogClear}" data-toggle="tooltip" class="btn btn-danger">
                                    <i class="fa fa-eraser"></i>{$button_log_clear}
                                </a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="col-lg-12">
                                <textarea readonly id="log" rows="10" class="textarea-autosize"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <button type="submit" value="1" id="submitSetting" name="submitSetting" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> Сохранить
    </button>
</form>
<script>
    function nt_log_refresh() {
        let but = $('#log_refresh')
        $('#log_warning').hide()
        $.ajax({
            url: '{$urlLogRefresh}',
            dataType: 'json',
            beforeSend: function () {
                but.hide()
            },
            complete: function () {
                but.show()
            },
            success: function (json) {
                but.show()
                if (json.ok) {

                } else {
                    $('#log_warning_text').text(json.warning)
                    $('#log_warning').show()
                }
                $('#log').text(json.log)
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr);
            }
        });
        return false;
    }
    nt_log_refresh();
</script>
