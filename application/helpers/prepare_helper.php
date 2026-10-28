<?php

function stockInZone(?array $stockZone)
{
	$sc = "";

	if(!empty($stockZone))
	{		
		foreach( $stockZone as $rs)
		{
			$sc .= $rs->name .' : '. number($rs->qty).' <br/>';
		}
	}

	return empty($sc) ? "ไม่มีสินค้า" : $sc;
}

function prepareFromZone($id_order, $id_pd)
{
	$sc = "";
	$prepare = new prepare();
	$qs = $prepare->prepareFromZone($id_order, $id_pd);
	if( dbNumRows($qs) > 0)
	{
		while($rs = dbFetchObject($qs))
		{
			$sc .= $rs->name.' : '.number($rs->qty).'<br/>';
		}
	}
	return $sc;
}



//---- 	ออเดอร์ที่กำลังจัดเป็นของฉันหรือเปล่า
function isMine($id_order)
{
	$id_emp	= getCookie('user_id');
	$state = new state();
	echo $state->hasEmployeeState($id_order, 4, $id_emp);
}

?>
