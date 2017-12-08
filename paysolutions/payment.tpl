<div class="row">
	<div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a style="padding: 10px;" href="javascript:$('#paysolutions_form').submit();" title="{l s='Pay with Paysolutions' mod='paysolutions'}">
		<img src="{$module_template_dir}paysolutions.png" alt="{l s='Pay with Paysolutions' mod='paysolutions'}" />PAYSOLUTIONS<span> (Pay with Paysolutions)</span>
            </a>
        </p>
    </div>
</div>
<form action="{$base_dir}modules/paysolutions/payment.php" method="post" id="paysolutions_form" class="hidden">
    <input type="hidden" name="paysolutionsUrl" value="{$paysolutionsUrl}" />
	<input type="hidden" name="id_cart" value="{$id_cart}" />
	<input type="hidden" name="busines" value="{$business}" />
	<input type="hidden" name="reqURL" value="{$reqUrl}" />
	<input type="hidden" name="postURL" value="{$postUrl}" />
	{if $currency->iso_code == "USD"}
		<input type="hidden" name="curr" value="840" />
	{/if}
	<input type="hidden" name="customer_email" value="{$customer->email}" />
	<input type="hidden" name="products" value="{$custom}" />
	<input type="hidden" name="total" value="{$total}" />
</form>