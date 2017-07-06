<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
if (!defined('_PS_VERSION_'))
	exit;
	
class Paysolutions extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();
	public function __construct()
	{
		$this->name = 'paysolutions';
		$this->tab = 'payments_gateways';
		$this->version = '1.6';
		$this->author = 'PrestaShop';
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		
        parent::__construct();
		
        /* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('PAYSOLUTIONS Secure Gateway');
        $this->description = $this->l('Accepts payments by PAYSOLUTIONS Co.,Ltd (Thailand)');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}
	public function getPaysolutionsUrl()
	{
		return "https://www.thaiepay.com/epaylink/payment.aspx";
	}
	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn')
		)
			return false;
		return true;
	}
	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYSOLUTIONS_MERCHANTID') || !parent::uninstall())
			return false;
		return true;
	}
	public function getContent()
	{
		$this->_html = '<h2>PAYSOLUTIONS Payment Module</h2>';
		if (isset($_POST['submitPaysolutions']))
		{
			if (empty($_POST['MerchantID']))
				$this->_postErrors[] = $this->l('PAYSOLUTIONS MerchantID is required.');
			
				
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('PAYSOLUTIONS_MERCHANTID', $_POST['MerchantID']);
				Configuration::updateValue('PAYSOLUTIONS_VISA', isset($_POST['accept_visa']) ? $_POST['accept_visa']:0 );
				Configuration::updateValue('PAYSOLUTIONS_AMEX', isset($_POST['accept_amex']) ? $_POST['accept_amex']:0);
				Configuration::updateValue('PAYSOLUTIONS_CHARGE', $_POST['charge']);
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displayPaysolutions();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	
	public function displayPaysolutions()
	{
		$this->_html .= '
		<img src="../modules/paysolutions/paysolutions.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This is an official PAYSOLUTIONS payment gateway for Prestashop').'</b><br /><br />
		'.$this->l('You MUST configure your PAYSOLUTIONS account first before using this module.').'
		<br /><br /><br /><br/>';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('PAYSOLUTIONS_MERCHANTID','PAYSOLUTIONS_AMEX','PAYSOLUTIONS_VISA','PAYSOLUTIONS_CHARGE'));
		// E-Mail Address
		$MerchantID = array_key_exists('MerchantID', $_POST) ? $_POST['MerchantID'] : (array_key_exists('PAYSOLUTIONS_MERCHANTID', $conf) ? $conf['PAYSOLUTIONS_MERCHANTID'] : '');
		$accept_amex = array_key_exists('accept_amex', $_POST) ? $_POST['accept_amex'] : (array_key_exists('PAYSOLUTIONS_AMEX', $conf) ? $conf['PAYSOLUTIONS_AMEX'] : '');
		$accept_visa = array_key_exists('accept_visa', $_POST) ? $_POST['accept_visa'] : (array_key_exists('PAYSOLUTIONS_VISA', $conf) ? $conf['PAYSOLUTIONS_VISA'] : '');
		
		$charge = array_key_exists('charge', $_POST) ? $_POST['charge'] : (array_key_exists('PAYSOLUTIONS_CHARGE', $conf) ? $conf['PAYSOLUTIONS_CHARGE'] : '0');
		
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			
			<label>'.$this->l('MerchantID').'</label>
			<div class="margin-form">
				<input type="text" size="33" name="MerchantID" value="'.htmlentities($MerchantID, ENT_COMPAT, 'UTF-8').'" />
			</div>
			
			<br />
			
			<center><input type="submit" name="submitPaysolutions" value="'.$this->l('Update settings').'" class="button" /></center>
		
		<br />
		
		</fieldset>		
		</form>		
		<br /><br />		
		';
	}

	public function hookPayment($params)
	{
		global $smarty;

		$address = new Address(intval($params['cart']->id_address_invoice));
		$customer = new Customer(intval($params['cart']->id_customer));
		$business = Configuration::get('PAYSOLUTIONS_MERCHANTID');
		
		$currency = $this->getCurrency();

			//if (!Validate::isEmail($business))
			//return $this->l('PAYSOLUTIONS error: (invalid or undefined account MerchantID)');

		if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency))
			return $this->l('PAYSOLUTIONS error: (invalid address or customer)');
			
		$products = $params['cart']->getProducts();
		
		foreach ($products as $key => $product)
		{
			$products[$key]['name'] = str_replace('"', '\'', $product['name']);
			if (isset($product['attributes']))
				$products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
			$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
			$products[$key]['paysolutionsAmount'] = number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '');
		}
		$ChargeMultiplier = (Configuration::get('PAYSOLUTIONS_CHARGE') + 100) / 100;
		
		$smarty->assign(array(
			'address' => $address,
			'country' => new Country(intval($address->id_country)),
			'customer' => $customer,
			'business' => $business,
			'currency' => $currency,
			'accept_visa' => Configuration::get('PAYSOLUTIONS_VISA'),
			'accept_amex' => Configuration::get('PAYSOLUTIONS_AMEX'),
			'paysolutionsUrl' => $this->getPaysolutionsUrl(),
			'amount' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4) * $ChargeMultiplier , $currency), 2, '.', ''),
			'shipping' =>  number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
			'discounts' => $params['cart']->getDiscounts(),
			'products' => $products,
			'total' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3)* $ChargeMultiplier , $currency), 2, '.', ''),
			'id_cart' => intval($params['cart']->id),
			'postUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order-confirmation&key='.$customer->secure_key.'&id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id),
			'reqUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/paysolutions/validation.php',
			'this_path' => $this->_path,
			'Charge' => Configuration::get('PAYSOLUTIONS_CHARGE'),
		));
		
		return $this->display(__FILE__, 'payment.tpl');
    }
	
	public function getL($key)
	{
		$translations = array(
			'mc_gross' => $this->l('Paysolutions key \'mc_gross\' not specified, can\'t control amount paid.'),
			'payment_status' => $this->l('Paysolutions key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('Paysolutions key \'custom\' not specified, can\'t rely to cart'),
			'txn_id' => $this->l('Paysolutions key \'txn_id\' not specified, transaction unknown'),
			'mc_currency' => $this->l('Paysolutions key \'mc_currency\' not specified, currency unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('Paysolutions Transaction ID: '),
			'verified' => $this->l('The Paysolutions transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the Paysolutions server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'socketmethod' => $this->l('Verification failure (using fsockopen). Returned: '),
			'curlmethod' => $this->l('Verification failure (using cURL). Returned: '),
			'curlmethodfailed' => $this->l('Connection using cURL failed'),
		);
		return $translations[$key];
	}
	public function hookPaymentReturn($params)
	{
		$payment_status	 = substr($_POST["result"], 0, 2);
		$id_order = trim(substr($_POST["result"],2));
		$amount = $_POST['amt'];

		global $smarty;
		
		$smarty->assign(array(
			'payment_status' => $payment_status,
			'id_order' => $id_order,
			'amount' => $amount,
	));
		
	return $this->display(__FILE__, 'order-confirmation.tpl');

	}
}
?>