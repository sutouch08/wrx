<?php

//--- convert discount text to array
function parse_discount_text($discText = '', $price = 0)
{
	$disc = array(
		'discount1' => 0,
		'discount2' => 0,
		'discount3' => 0,
		'discount_amount' => 0
	);

	if (!empty($discText))
	{
		$step = explode('+', $discText);
		$i = 1;
		
		foreach ($step as $discLabel)
		{
			if ($i < 4)
			{
				$key = 'discount' . $i;
				$arr = explode('%', $discLabel);
				$arr[0] = floatval($arr[0]);
				$discount = count($arr) == 1 ? $arr[0] : ($arr[0] * 0.01) * $price; //--- ส่วนลดต่อชิ้น
				$disc[$key] = count($arr) == 1 ? $arr[0] : $arr[0] . '%'; //--- discount label
				$disc['discount_amount'] += $discount;
				$price -= $discount;
			}

			$i++;
		}
	}

	return $disc;
}

//--- แสดงป้ายส่วนลด
function discountLabel($disc1 = 0, $disc2 = 0, $disc3 = 0)
{
	$disc1 = getDiscLabel($disc1);
	$disc2 = getDiscLabel($disc2);
	$disc3 = getDiscLabel($disc3);

	$label = empty($disc1) ? '' : $disc1;
	$label .= ! empty($disc1) && ! empty($disc2) ? '+' . $disc2 : '';
	$label .= ! empty($disc2) && ! empty($disc3) ? '+' . $disc3 : '';
	return $label;
}

function getDiscLabel($disc)
{
	$disc = trim($disc ?? '');

	if(!empty($disc))
	{
		$arr = explode('%', $disc);
		
		if (count($arr ?? []) > 1)
		{
			$d = floatval(trim($arr[0] ?? ''));
			return $d > 0 ? $d . '%' : '';
		}

		return floatval(trim($arr[0] ?? ''));
	}

	return "";
}

function discountAmountToPercent($amount = 0, $qty = 0, $price = 0)
{
	if ($amount > 0 && $qty > 0 && $price > 0)
	{
		return (($amount / $qty) * 100) / $price;
	}

	return 0;
}
