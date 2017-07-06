<?php

include(dirname(__FILE__).'/config/config.inc.php');
//include(dirname(__FILE__).'/config/init.php');


//$refno = '0000000012';
echo $refno = $_POST['refno'];
 $new_var = (int)$refno;



	$sql = 'UPDATE '._DB_PREFIX_.'orders SET current_state = "2"
    WHERE id_order = '.$new_var;
if ($row = Db::getInstance()->getRow($sql))
   
	
	
?>