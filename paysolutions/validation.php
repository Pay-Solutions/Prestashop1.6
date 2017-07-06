<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/paysolutions.php');
include_once(dirname(__FILE__).'/../../init.php');


$payment_status	 = $_POST['payment_status'];
$id_order = $_POST['refno'];
$amount = $_POST['total'];
//$psbRef = $_POST['apCode'];



//$id_cart = $params['cart']->id;
if($payment_status =='00')
{
	$objOrder = new Order($id_order); //order with id=1 
	$history = new OrderHistory();
	$history->id_order = (int)$objOrder->id;
	$history->changeIdOrderState(2, (int)($objOrder->id)); //order status 2 = Payment Accepted
}

else
{
	$objOrder = new Order($id_order);
	$history = new OrderHistory();
	$history->id_order = (int)$objOrder->id;
	$history->changeIdOrderState(8, (int)($objOrder->id)); //order status 8 = Payment Error
}


?>