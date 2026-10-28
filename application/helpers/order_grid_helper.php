<?php
function gridHeader($colors = [])
{  
  $sc = '<tr class="font-size-12"><td>&nbsp;</td>';

  if(count($colors ?? []))
  {
    foreach( $colors as $color)
    {
      $sc .= '<td class="text-center middle"><strong>'.$color['code'] . '<br/>'. $color['name'].'</strong></td>';
    }
  }

  $sc .= '</tr>';
  return $sc;
}
