<?php
if(!function_exists('select_item_group'))
{
  function select_item_group($code = NULL)
  {
    $sc = '';
    $ci = &get_instance();
    $ci->load->model('masters/item_group_model');
    $options = $ci->item_group_model->get_data(); //--- OUOM

    if (!empty($options))
    {
      foreach ($options as $rs)
      {
        $sc .= '<option value="' . $rs->code . '" ' . is_selected(strval($code), strval($rs->code)) . '>' . $rs->code . ' | ' . $rs->name . '</option>';
      }
    }

    return $sc;
  }
}

if(!function_exists('item_group_array'))
{
  function item_group_array()
  {
    $ds = [];
    $ci = &get_instance();
    $ci->load->model('masters/item_group_model');
    $data = $ci->item_group_model->get_data();

    if (! empty($data))
    {
      foreach ($data as $rs)
      {
        $ds[$rs->name] = ['code' => $rs->code, 'name' => $rs->name];
      }
    }

    return $ds;
  }
}

if(!function_exists('product_group_array'))
{
  function product_group_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_group_model');
    $ds = $ci->product_group_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_main_group_array'))
{
  function product_main_group_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_main_group_model');
    $ds = $ci->product_main_group_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_sub_group_array'))
{
  function product_sub_group_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_sub_group_model');
    $ds = $ci->product_sub_group_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}


if(!function_exists('product_brand_array'))
{
  function product_brand_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_brand_model');
    $ds = $ci->product_brand_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_kind_array'))
{
  function product_kind_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_kind_model');
    $ds = $ci->product_kind_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_collection_array'))
{
  function product_collection_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_collection_model');
    $ds = $ci->product_collection_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_type_array'))
{
  function product_type_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_type_model');
    $ds = $ci->product_type_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

if(!function_exists('product_category_array'))
{
  function product_category_array()
  {
    $ci =& get_instance();
    $ci->load->model('masters/product_category_model');
    $ds = $ci->product_category_model->get_all();
    $arr = [];

    if($ds)
    {
      foreach($ds as $rs)
      {
        $arr[$rs->code] = $rs->name;
      }
    }

    return $arr;
  }
}

