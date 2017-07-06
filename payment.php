<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/paysolutions.php');
include_once(dirname(__FILE__).'/../../init.php');

$paysolutionsURL = $_REQUEST["paysolutionsUrl"];
$id_cart = $_REQUEST["id_cart"];
$busines = $_REQUEST["busines"];
$reqURL = $_REQUEST["reqURL"];
$curr = $_REQUEST["curr"];
$email = $_REQUEST["customer_email"];
$products = $_REQUEST["products"];
$amount = $_REQUEST["total"];

$paysolutions = new Paysolutions();

$paysolutions->validateOrder($id_cart, 15, $amount, $paysolutions->displayName, NULL, array(), (int)$currencyCode, false, $customer->secure_key);

$id_order = Order::getOrderByCartId(intval($id_cart));
$postURL = $_REQUEST["postURL"]."&id_order=".$id_order;
?>

<form action="<?=$paysolutionsURL?>" method="post" name="payso_form" id="payso_form" class="hidden"> <!--//psb_form-->
	<input type="hidden" name="payso" value="payso" />
	<input type="hidden" name="refno" value="<?=$id_order?>" />
	<input type="hidden" name="merchantid" value="<?=$busines?>" />
	<input type="hidden" name="reqURL" value="<?=$reqURL?>" />
	<input type="hidden" name="postURL" value="<?=$postURL?>" />
	<input type="hidden" name="currencyCode" value="<?=$curr?>" />
	<input type="hidden" name="customeremail" value="<?=$email?>" />
	<input type="hidden" name="productdetail" value="Purchase Number :<?=$id_order?>" />
	<input type="hidden" name="total" value="<?=$amount?>" />
	<input type="hidden" name="paypal_amt" value="<?=$amount?>" />
</form>

<?php
echo "<SCRIPT language='JavaScript'>";
echo "document.payso_form.submit();";
echo "</SCRIPT>";
?>