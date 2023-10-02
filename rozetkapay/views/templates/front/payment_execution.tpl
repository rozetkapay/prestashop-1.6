{if $pageType == 'comfire'}

    {capture name=path}
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" 
           title="{l s='Go back to the Checkout'}">{l s='Checkout'}</a>
        <span class="navigation-pipe">{$navigationPipe}</span>
        {$text_title}
    {/capture}



    <h2>{l s='Order summary'}</h2>

    {assign var='current_step' value='payment'}
    {include file="$tpl_dir./order-steps.tpl"}

    {if $nbProducts <= 0}
        <p class="warning">{l s='Your shopping cart is empty.' mod='bankwire'}</p>
    {else}

        <h3>{$text_payment_execution_h3} {$text_title}</h3>
        <form action="{$urlCreatPay}" method="post">
            {if $showIcon }
            <p>
                <img src="{$this_path_bw}img/logo.png" height="49" style="float:left; margin: 0px 10px 5px 0px;" />                
            </p>
            {/if}
            <p style="margin-top:20px;">
                - {l s='The total amount of your order is' mod='bankwire'}
                <span id="amount" class="price">{displayPrice price=$total}</span>
                {if $use_taxes == 1}
                    {l s='(tax incl.)' mod='bankwire'}
                {/if}
            </p>
            <p>
                -
                {if $currencies|@count > 1}
                    {l s='We allow several currencies to be sent via bank wire.' mod='bankwire'}
                    <br /><br />
                    {l s='Choose one of the following:' mod='bankwire'}
                    <select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">

                        {foreach from=$currencies item=currency}
                            <option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
                        {/foreach}
                    </select>
                {else}
                    {l s='We allow the following currency to be sent via bank wire:' mod='bankwire'}&nbsp;<b>{$currencies.0.name}</b>
                    <input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
                {/if}
            </p>
            <p>

                <b>{l s='Please confirm your order by clicking "I confirm my order".' mod='bankwire'}</b>
            </p>
            <p class="cart_navigation" id="cart_navigation">
                <input type="submit" value="{l s='I confirm my order' mod='bankwire'}" class="exclusive_large" />
                <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='bankwire'}</a>
            </p>
        </form>
    {/if}

{else}

    {capture name=path}
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" 
           title="{l s='Go back to the Checkout'}">{l s='Checkout'}</a>
        <span class="navigation-pipe">{$navigationPipe}</span>
        {$text_title}
    {/capture}

    <div class="row">

        <div class="col-sm-6">
            <a href="{$urlPay}" class="button_large">{$text_button_pay}</a>
        </div>
    </div>
    <div class="col-sm-6">
        {if $isPay }
            <div id="rozetkapay_pay">
                <img src="{$payQRcode}">
            </div>
        {/if}
    </div>


</div>
{if !$isPay }
    <div class="row" style="color: red">
        {$message}

    </div>
{/if}

{/if}
