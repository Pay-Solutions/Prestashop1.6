{if $payment_status == '00'}
	<p>	
    	{l s='The payment is successful.' mod='paysolutions'}<br /><br />
		{l s='Your order ID is :' mod='paysolutions'}<strong>{$id_order}</strong><br /><br />
        {l s='Amount' mod='paysolutions'} <strong>{$amount}</strong>
	</p>
{else if $payment_status == '02'}
	<p>
		{l s='The order is complete.' mod='paysolutions'} <br /><br />
        {l s='The order is waiting for payment.' mod='paysolutions'} <br /><br />
	</p>
{else}
	<p>
		{l s='The verification of this payment transaction is failed. Please contact PAYSOLUTIONS Co. Ltd. for details.' mod='paysolutions'} 
	</p>
{/if}