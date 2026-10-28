<?php
function dispatch_status($status = NULL)
{
  $status = $status ?? '0';

  $ds = array(
    'P' => 'Pending',
    'R' => 'Released',
    'S' => 'Shipped',
    'C' => 'Closed',
    'D' => 'Canceled'
  );

  return empty($ds[$status]) ? 'Unknow' : $ds[$status];
}
