<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
$refNo = (int) $_REQUEST['refno'];
$order = new Order($refNo);
if($order->id) {
    $history = new OrderHistory();
    $history->id_order = (int)$order->id;
    $history->changeIdOrderState(2, (int)($order->id));
}
