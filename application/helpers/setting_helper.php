<?php
function select_running(int $min = 3, int $max = 6, $se = '')
{
  $option = "";
  $se = intval($se);
  
  while($min <= $max)
  {
    $option .= '<option value="'.$min.'" '.is_selected($min, $se).'>'.$min.'</option>';
    $min++;
  }

  return $option;
}
