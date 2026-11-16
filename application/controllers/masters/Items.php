<?php
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;

class Items extends PS_Controller
{
  public $menu_code = 'DBITEM';
  public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'PRODUCT';
  public $title = 'เพิ่ม/แก้ไข รายการสินค้า';
  public $segment = 4;
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url() . 'masters/items';        
    $this->load->model('masters/products_model');
    $this->load->model('masters/product_group_model');
    $this->load->model('masters/product_main_group_model');
    $this->load->model('masters/product_sub_group_model');
    $this->load->model('masters/product_kind_model');
    $this->load->model('masters/product_type_model');
    $this->load->model('masters/product_style_model');
    $this->load->model('masters/product_brand_model');
    $this->load->model('masters/product_collection_model');
    $this->load->model('masters/product_category_model');
    $this->load->model('masters/product_color_model');
    $this->load->model('masters/product_size_model');    
    $this->load->model('masters/product_image_model');        
    $this->load->helper('product_brand');    
    $this->load->helper('product_kind');
    $this->load->helper('product_collection');
    $this->load->helper('product_type');
    $this->load->helper('product_group');
    $this->load->helper('product_category');
    $this->load->helper('product_main_group');
    $this->load->helper('product_sub_group');
    $this->load->helper('product_images');
    $this->load->helper('unit');
    $this->load->helper('item');
  }
  
  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'item_code', ''),
      'name' => get_filter('name', 'item_name', ''),
      'barcode' => get_filter('barcode', 'item_barcode', ''),
      'color' => get_filter('color', 'color', ''),
      'size' => get_filter('size', 'size', ''),
      'group' => get_filter('group', 'group', 'all'),
      'sub_group' => get_filter('sub_group', 'sub_group', 'all'),
      'category' => get_filter('category', 'category', 'all'),
      'kind' => get_filter('kind', 'kind', 'all'),
      'type' => get_filter('type', 'type', 'all'),
      'brand' => get_filter('brand', 'brand', 'all'),
      'collection' => get_filter('collection', 'collection', 'all'),
      'year' => get_filter('year', 'year', 'all'),
      'active' => get_filter('active', 'active', 'all')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();    
    $rows = $this->products_model->count_rows($filter);    
    $products = $this->products_model->get_list($filter, $perpage, $this->uri->segment($this->segment));

    if (!empty($products))
    {
      $mainGroup = [];
      $group = []; 
      $subGroup = [];
      $category = [];
      $kind = [];
      $type = [];
      $brand = [];
      $collection = [];

      foreach ($products as $rs)
      {
        if( ! empty($rs->main_group_code) && empty($mainGroup[$rs->main_group_code]))
        {
          $mainGroup[$rs->main_group_code] = $this->product_main_group_model->get_name($rs->main_group_code);
        }

        if( ! empty($rs->group_code) && empty($group[$rs->group_code]))
        {
          $group[$rs->group_code] = $this->product_group_model->get_name($rs->group_code);
        }

        if( ! empty($rs->sub_group_code) && empty($subGroup[$rs->sub_group_code]))
        {
          $subGroup[$rs->sub_group_code] = $this->product_sub_group_model->get_name($rs->sub_group_code);
        }

        if( ! empty($rs->category_code) && empty($category[$rs->category_code]))
        {
          $category[$rs->category_code] = $this->product_category_model->get_name($rs->category_code);
        }

        if( ! empty($rs->kind_code) && empty($kind[$rs->kind_code]))
        {
          $kind[$rs->kind_code] = $this->product_kind_model->get_name($rs->kind_code);
        }

        if( ! empty($rs->type_code) && empty($type[$rs->type_code]))
        {
          $type[$rs->type_code] = $this->product_type_model->get_name($rs->type_code);
        }

        if( ! empty($rs->brand_code) && empty($brand[$rs->brand_code]))
        {
          $brand[$rs->brand_code] = $this->product_brand_model->get_name($rs->brand_code);
        }

        if( ! empty($rs->collection_code) && empty($collection[$rs->collection_code]))
        {
          $collection[$rs->collection_code] = $this->product_collection_model->get_name($rs->collection_code);
        }

        $rs->main_group_name = empty($rs->main_group_code) ? NULL : $mainGroup[$rs->main_group_code];
        $rs->group_name = empty($rs->group_code) ? NULL : $group[$rs->group_code];
        $rs->sub_group_name = empty($rs->sub_group_code) ? NULL : $subGroup[$rs->sub_group_code];
        $rs->kind_name = empty($rs->kind_code) ? NULL : $kind[$rs->kind_code];
        $rs->type_name = empty($rs->type_code) ? NULL : $type[$rs->type_code];
        $rs->category_name = empty($rs->category_code) ? NULL : $category[$rs->category_code];
        $rs->brand_name = empty($rs->brand_code) ? NULL : $brand[$rs->brand_code];
        $rs->collection_name = empty($rs->collection_code) ? NULL : $collection[$rs->collection_code];
      }
    }

    $filter['data'] = $products;
    $init = pagination_config($this->home . '/index/', $rows, $perpage, $this->segment);
    $this->pagination->initialize($init);
    $this->load->view('masters/product_items/items_list', $filter);
  }


  public function import_items()
  {
    if( ! isset($_FILES['uploadFile']))
    {
      echo "No file uploaded";
      exit;
    }

    $file = 'uploadFile';    
    $path = $this->config->item('upload_path') . 'items/';
    $config = array(
      "allowed_types" => "xlsx",
      "upload_path" => $path,
      "file_name"  => "import_items",
      "max_size" => 5120,
      "overwrite" => TRUE
    );

    $this->load->library("upload", $config);

    if (! $this->upload->do_upload($file))
    {
      echo $this->upload->display_errors();
      exit;
    }

    $info = $this->upload->data();
    $excel = IOFactory::load($info['full_path']);
    $collection  = $excel->getActiveSheet()->toArray(NULL, TRUE, TRUE, TRUE);

    if(empty($collection))
    {
      echo "The uploaded file is empty";
      exit;
    }

    $i = 1;
    $count = count($collection ?? []);
    $limit = intval(getConfig('IMPORT_ROWS_LIMIT')) + 1;

    if($count > $limit)
    {
      echo "The uploaded file exceeds the maximum allowed rows";
      exit;
    }

    $units = unit_array();
    $itemGroups = item_group_array();

    foreach ($collection as $rs)
    {
      if ($i === 1)
      {
        $i++;
        $headCol = array(
          'A' => 'Code',
          'B' => 'Name',
          'C' => 'Barcode',
          'D' => 'Model',
          'E' => 'Color',
          'F' => 'Size',
          'G' => 'Group',
          'H' => 'MainGroup',
          'I' => 'SubGroup',
          'J' => 'Category',
          'K' => 'Kind',
          'L' => 'Type',
          'M' => 'Brand',
          'N' => 'Collection',
          'O' => 'Year',
          'P' => 'Cost',
          'Q' => 'Price',
          'R' => 'Unit',
          'S' => 'CountStock',
          'T' => 'IsAPI',
          'U' => 'OldModel',
          'V' => 'OldCode',
          'W' => 'ApiRate',
          'X' => 'Active',
          'Y' => 'ManageBatch',
          'Z' => 'ItemGroup'
        );

        foreach ($headCol as $col => $field)
        {
          if ($rs[$col] !== $field)
          {           
            $this->error = 'Column ' . $col . ' Should be ' . $field;
            exit;           
          }
        }

        continue;
      }

      if (!empty($rs['A']))
      {
        $code_pattern = '/[^a-zA-Z0-9_-]/';
        $rs['D'] = str_replace(array("\n", "\r"), '', $rs['D'] ?? ''); //--- เอาตัวขึ้นบรรทัดใหม่ออก

        $style = preg_replace($code_pattern, '', get_null(trim($rs['D'] ?? '')));
        $old_style = NULL;
        $color_code = get_null(trim($rs['E'] ?? ''));
        $size_code = get_null(trim($rs['F'] ?? ''));
        $group_code = get_null(trim($rs['G'] ?? ''));
        $main_group_code = get_null(trim($rs['H'] ?? ''));
        $sub_group_code = get_null(trim($rs['I'] ?? ''));
        $category_code = get_null(trim($rs['J'] ?? ''));
        $kind_code = get_null(trim($rs['K'] ?? ''));
        $type_code = get_null(trim($rs['L'] ?? ''));
        $brand_code = get_null(trim($rs['M'] ?? ''));
        $collection_code = get_null(trim($rs['N'] ?? ''));
        $year = empty($rs['O'] ?? '') ? '0000' : trim($rs['O'] ?? '');
        $unit = empty(trim($rs['R'] ?? '')) ? 'PCS' : trim($rs['R'] ?? '');
        $unit_id = empty($units[$unit]) ? 1 : $units[$unit]['id'];
        $unit_group = empty($units[$unit]) ? 1 : $units[$unit]['group'];
        $has_batch = trim($rs['Y'] ?? '') == 1 ? 1 : 0;
        $item_group_name = empty(trim($rs['Z'] ?? '')) ? 'FG' : trim($rs['Z'] ?? '');
        $item_group = empty($itemGroups[$item_group_name]) ? 103 : $itemGroups[$item_group_name]['code'];
        $is_api = empty($rs['T'] ?? '') ? 0 : ((trim($rs['T'] ?? '') == 'Y' or trim($rs['T'] ?? '') == 1) ? 1 : 0);
        $api_rate = empty($rs['W'] ?? '') ? 0 : (floatval(trim($rs['W'] ?? '')));
        $api_rate = $api_rate < 0 ? 0 : ($api_rate > 100 ? 100 : $api_rate);

        if (!empty($color_code) && ! $this->product_color_model->is_exists($color_code))
        {          
          echo "Color : {$color_code}  does not exists at row {$i}";
          exit;
        }
        
        if (!empty($size_code) && ! $this->product_size_model->is_exists($size_code))
        {
          echo "Size : {$size_code}  does not exists at row {$i}";
          exit;
        }
        
        if (!empty($group_code) && ! $this->product_group_model->is_exists($group_code))
        {
          echo "Product Group : {$group_code}  does not exists at row {$i}";
          exit;
        }

        if (!empty($main_group_code) && ! $this->product_main_group_model->is_exists($main_group_code))
        {
          echo "Product Main Group : {$main_group_code}  does not exists at row {$i}";
          exit;
        }

        if (!empty($sub_group_code) && ! $this->product_sub_group_model->is_exists($sub_group_code))
        {
          echo "Product Sub Group : {$sub_group_code}  does not exists at row {$i}";
          exit;
        }

        if (!empty($category_code) && ! $this->product_category_model->is_exists($category_code))
        {
          echo "Product Category : {$category_code} does not exists at row {$i}";
          exit;
        }

        if (!empty($kind_code) && ! $this->product_kind_model->is_exists($kind_code))
        {
          echo "Product Kind : {$kind_code} does not exists at row {$i}";
          exit;
        }

        if (!empty($type_code) && ! $this->product_type_model->is_exists($type_code))
        {
          echo "Product Type : {$type_code} does not exists at row {$i}";
          exit;
        }

        if (!empty($brand_code) && ! $this->product_brand_model->is_exists($brand_code))
        {
          echo "Brand : {$brand_code} does not exists at row {$i}";
          exit;
        }

        if (! empty($collection_code) && ! $this->product_collection_model->is_exists($collection_code))
        {
          echo "Collection : {$collection_code} does not exists at row {$i}";
          exit;
        }        

        if (!empty($style))
        {
          if (! $this->product_style_model->is_exists($style))
          {
            $ds = array(
              'code' => $style,
              'name' => $style,
              'group_code' => $group_code,
              'main_group_code' => $main_group_code,
              'sub_group_code' => $sub_group_code,
              'category_code' => $category_code,
              'kind_code' => $kind_code,
              'type_code' => $type_code,
              'brand_code' => $brand_code,
              'collection_code' => $collection_code,
              'year' => $year,
              'cost' => round(parseFloat(trim($rs['P'] ?? '')), 2),
              'price' => round(parseFloat(trim($rs['Q'] ?? '')), 2),
              'unit_code' => $unit,
              'unit_id' => $unit_id,
              'unit_group_id' => $unit_group,
              'has_batch' => $has_batch,
              'item_group' => $item_group,
              'count_stock' => trim($rs['S'] ?? '') === 'N' ? 0 : 1,
              'is_api' => $is_api,
              'api_rate' => $api_rate,
              'update_user' => $this->_user->uname,
              'old_code' => $old_style
            );

            if ($this->product_style_model->add($ds))
            {              
              $this->export_style($style);
            }
          }
        }

        $rs['A'] = str_replace(array("\n", "\r"), '', $rs['A'] ?? '');
        $code = preg_replace($code_pattern, '', trim($rs['A'] ?? ''));
        $old_code = NULL;
        $arr = array(
          'code' => $code,
          'name' => trim($rs['B'] ?? ''),
          'barcode' => get_null(trim($rs['C'] ?? '')),
          'style_code' => get_null(trim($rs['D'] ?? '')),
          'color_code' => get_null(trim($rs['E'] ?? '')),
          'size_code' => get_null(trim($rs['F'] ?? '')),
          'group_code' => get_null(trim($rs['G'] ?? '')),
          'main_group_code' => get_null(trim($rs['H'] ?? '')),
          'sub_group_code' => get_null(trim($rs['I'] ?? '')),
          'category_code' => get_null(trim($rs['J'] ?? '')),
          'kind_code' => get_null(trim($rs['K'] ?? '')),
          'type_code' => get_null(trim($rs['L'] ?? '')),
          'brand_code' => get_null(trim($rs['M'] ?? '')),
          'collection_code' => get_null(trim($rs['N'] ?? '')),
          'year' => trim($rs['O'] ?? ''),
          'cost' => round(parseFloat(trim($rs['P'] ?? '')), 2),
          'price' => round(parseFloat(trim($rs['Q'] ?? '')), 2),
          'unit_code' => $unit,
          'unit_id' => $unit_id,
          'unit_group_id' => $unit_group,
          'has_batch' => $has_batch,
          'item_group' => $item_group,
          'count_stock' => trim($rs['S'] ?? '') === 'N' ? 0 : 1,
          'is_api' => $is_api,
          'api_rate' => $api_rate,
          'update_user' => $this->_user->uname,
          'old_style' => $old_style,
          'old_code' => $old_code
        );

        if (isset($rs['X']) && $rs['X'] != '' && ($rs['X'] == 'N' or $rs['X'] == 'n' or $rs['X'] == 'Y' or $rs['X'] == 'y'))
        {
          $arr['active'] = $rs['X'] == 'N' ? 0 : 1;
        }

        if ($this->products_model->is_exists($code))
        {
          if($this->products_model->update($code, $arr)) $this->do_export($code);
        }
        else
        {
          if($this->products_model->add($arr)) $this->do_export($code);
        }        
      }

      $i++;
    } //-- end foreach

    echo 'success';
  }


  public function add_new()
  {
    $this->load->view('masters/product_items/items_add_view');
  }


  public function add()
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if (! empty($ds))
    {
      $code = trim($ds->code ?? '');

      if ($this->products_model->is_exists($code))
      {
        $sc = FALSE;
        $this->error = "{$code} already exists";
      }

      if ($sc === TRUE)
      {
        $user = $this->_user->uname;

        $arr = array(
          'code' => $code,
          'name' => trim($ds->name ?? ''),
          'barcode' => get_null(trim($ds->barcode ?? '')),
          'style_code' => trim($ds->style ?? ''),
          'color_code' => get_null($ds->color_code),
          'size_code' => get_null($ds->size_code),
          'item_group' => get_null($ds->item_group),
          'group_code' => get_null($ds->group_code),
          'main_group_code' => get_null($ds->main_group_code),
          'sub_group_code' => get_null($ds->sub_group_code),
          'category_code' => get_null($ds->category_code),
          'kind_code' => get_null($ds->kind_code),
          'type_code' => get_null($ds->type_code),
          'brand_code' => get_null($ds->brand_code),
          'collection_code' => get_null($ds->collection_code),
          'year' => $ds->year,
          'api_rate' => $ds->api_rate,
          'cost' => round($ds->cost, 2),
          'price' => round($ds->price, 2),
          'unit_code' => $ds->unit_code,
          'unit_id' => $ds->unit_id,
          'unit_group_id' => get_null($ds->unit_group_id),
          'has_batch' => $ds->has_batch,
          'count_stock' => $ds->count_stock == 0 ? 0 : 1,
          'can_sell' => $ds->can_sell == 0 ? 0 : 1,
          'active' => $ds->active == 0 ? 0 : 1,
          'is_api' => $ds->is_api == 0 ? 0 : 1,
          'update_user' => $user,
          'old_style' => get_null($ds->old_style),
          'old_code' => get_null($ds->old_code)
        );

        if (! $this->products_model->add($arr))
        {
          $sc = FALSE;
          $this->error = "Failed to add new item";
        }        
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Missing required parameter";
    }

    $this->_response($sc);
  }



  public function edit($id)
  {
    $item = $this->products_model->get_by_id($id);

    if (! empty($item))
    {
      $this->load->view('masters/product_items/items_edit_view', $item);
    }
    else
    {
      $this->page_error();
    }
  }



  public function duplicate($id)
  {
    $item = $this->products_model->get_by_id($id);

    if (! empty($item))
    {
      $this->load->view('masters/product_items/items_duplicate_view', $item);
    }
    else
    {
      $this->page_error();
    }
  }


  public function update()
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if (! empty($ds))
    {
      $code = $ds->code ?? '';

      $arr = array(
        'name' => trim($ds->name ?? ''),
        'barcode' => get_null(trim($ds->barcode ?? '')),
        'style_code' => trim($ds->style ?? ''),
        'color_code' => get_null($ds->color_code),
        'size_code' => get_null($ds->size_code),
        'item_group' => get_null($ds->item_group),
        'group_code' => get_null($ds->group_code),
        'main_group_code' => get_null($ds->main_group_code),
        'sub_group_code' => get_null($ds->sub_group_code),
        'category_code' => get_null($ds->category_code),
        'kind_code' => get_null($ds->kind_code),
        'type_code' => get_null($ds->type_code),
        'brand_code' => get_null($ds->brand_code),
        'collection_code' => get_null($ds->collection_code),
        'year' => $ds->year,
        'api_rate' => $ds->api_rate,
        'cost' => round($ds->cost, 2),
        'price' => round($ds->price, 2),
        'unit_code' => $ds->unit_code,
        'unit_id' => $ds->unit_id,
        'unit_group_id' => get_null($ds->unit_group_id),
        'has_batch' => $ds->has_batch,
        'count_stock' => empty($ds->count_stock) ? 0 : 1,
        'can_sell' => empty($ds->can_sell) ? 0 : 1,
        'active' => empty($ds->active) ? 0 : 1,
        'is_api' => empty($ds->is_api) ? 0 : 1,
        'update_user' => $this->_user->uname,
        'old_style' => get_null($ds->old_style),
        'old_code' => get_null($ds->old_code)
      );

      if (! $this->products_model->update($code, $arr))
      {
        $sc = FALSE;
        $this->error = "Update failed";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Missing form data";
    }

    if ($sc === TRUE)
    {
      $this->do_export($code);     
    }

    echo $sc === TRUE ? 'success' : $this->error;
  }


  public function view_detail($id)
  {
    $item = $this->products_model->get_by_id($id);

    if (! empty($item))
    {
      $this->load->view('masters/product_items/items_view_detail', $item);
    }
    else
    {
      $this->page_error();
    }
  }


  public function is_exists_code($code, $old_code = '')
  {
    echo ($this->products_model->is_exists($code, $old_code)) ? 'รหัสซ้ำ' : 'ok';
  }



  public function toggle_can_sell($code)
  {
    $status = $this->products_model->get_status('can_sell', $code);
    $status = $status == 1 ? 0 : 1;

    if ($this->products_model->set_status('can_sell', $code, $status))
    {
      echo $status;
    }
    else
    {
      echo 'fail';
    }
  }


  public function toggle_active($code)
  {
    $status = $this->products_model->get_status('active', $code);
    $status = $status == 1 ? 0 : 1;
    echo ($this->products_model->set_status('active', $code, $status)) ? $status : 'fail';
  }


  public function toggle_api($code)
  {
    $status = $this->products_model->get_status('is_api', $code);
    $status = $status == 1 ? 0 : 1;
    echo ($this->products_model->set_status('is_api', $code, $status)) ? $status : 'fail';
  }


  public function delete_item()
  {
    $sc = TRUE;
    $id = $this->input->get('id');
    $item = $this->products_model->get_by_id($id);

    if (! empty($item))
    {
      if (! $this->products_model->has_transection($item->code))
      {
        if (! $this->products_model->delete_item_by_id($id))
        {
          $sc = FALSE;
          $this->error = "ลบรายการไม่สำเร็จ";
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "ไม่สามารถลบ {$item->code} ได้ เนื่องจากสินค้ามี Transcetion เกิดขึ้นแล้ว";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Missing required parameter";
    }

    echo $sc === TRUE ? 'success' : $this->error;
  }

  
  public function do_export($code, $method = 'A')
  {
    $sc = TRUE;
    $item = $this->products_model->get($code);
    //--- เช็คข้อมูลในฐานข้อมูลจริง
    $exst = $this->products_model->is_sap_exists($item->code);
    $method = $exst === TRUE ? 'U' : $method;
    //--- เช็คข้อมูลในถังกลาง
    $middle = $this->products_model->get_un_import_middle($item->code);

    if (!empty($middle))
    {
      foreach ($middle as $mid)
      {
        $this->products_model->drop_middle_item($mid->DocEntry);
      }
    }

    $ds = array(
      'ItemCode' => $item->code, //--- รหัสสินค้า
      'ItemName' => limitText($item->name, 97), //--- ชื่อสินค้า
      'FrgnName' => NULL,   //--- ชื่อสินค้าภาษาต่างประเทศ
      'ItmsGrpCod' => ! empty($item->item_group) ? $item->item_group : getConfig('ITEM_GROUP_CODE'),  //--- กลุ่มสินค้า (ต้องตรงกับ SAP)
      'VatGourpSa' => getConfig('SALE_VATE_CODE'), //--- รหัสกลุ่มภาษีขาย
      'CodeBars' => $item->barcode, //--- บาร์โค้ด
      'VATLiable' => 'Y', //--- มี vat หรือไม่
      'PrchseItem' => 'Y', //--- สินค้าสำหรับซื้อหรือไม่
      'SellItem' => 'Y', //--- สินค้าสำหรับขายหรือไม่
      'InvntItem' => $item->count_stock == 1 ? 'Y' : 'N', //--- นับสต้อกหรือไม่
      'SalUnitMsr' => $item->unit_code, //--- หน่วยขาย
      'BuyUnitMsr' => $item->unit_code, //--- หน่วยซื้อ
      'CntUnitMsr' => $item->unit_code,
      'UgpEntry' => $item->unit_group_id,
      'PUoMEntry' => $item->unit_id,
      'SUoMEntry' => $item->unit_id,
      'IUoMEntry' => $item->unit_id,
      'ManBtchNum' => $item->has_batch == 1 ? 'Y' : 'N',
      'VatGroupPu' => getConfig('PURCHASE_VAT_CODE'), //---- รหัสกลุ่มภาษีซื้อ (ต้องตรงกับ SAP)
      'ItemType' => 'I', //--- ประเภทของรายการ F=Fixed Assets, I=Items, L=Labor, T=Travel
      'InvntryUom' => $item->unit_code, //--- หน่วยในการนับสต็อก
      'validFor' => $item->active == 1 ? 'Y' : 'N',
      'U_MODEL' => $item->style_code,
      'U_COLOR' => $item->color_code,
      'U_SIZE' => $item->size_code,
      'U_GROUP' => $item->group_code,
      'U_MAINGROUP' => $item->main_group_code,
      'U_MAJOR' => $item->sub_group_code,
      'U_CATE' => $item->category_code,
      'U_SUBTYPE' => $item->kind_code,
      'U_TYPE' => $item->type_code,
      'U_BRAND' => $item->brand_code,
      'U_YEAR' => $item->year,
      'U_COST' => $item->cost,
      'U_PRICE' => $item->price,
      'U_OLDCODE' => $item->old_code,
      'F_E_Commerce' => $method,
      'F_E_CommerceDate' => sap_date(now(), TRUE)
    );

    if (! $this->products_model->add_item($ds))    
    {
      $sc = FALSE;
      $this->error = "Update Item failed";
    }

    return $sc;
  }


  public function export_style($style_code)
  {
    $style = $this->product_style_model->get($style_code);

    if (!empty($style))
    {
      $ext = $this->product_style_model->is_middle_exists($style_code);
      $flag = $ext === TRUE ? 'U' : 'A';
      $arr = array(
        'Code' => $style->code,
        'Name' => $style->name,
        'UpdateDate' => sap_date(now(), TRUE),
        'Flag' => $flag
      );

      return $this->product_style_model->add_sap_model($arr);
    }

    return FALSE;
  }

  public function download_template()
  {
    $this->load->helper('download');
    $file = 'templates/items-master-template.xlsx';

    if (file_exists($file))
    {
      force_download($file, NULL);
    }
    else
    {
      $this->page_error();
    }
  }

  public function clear_filter()
  {
    $filter = array('item_code', 'item_name', 'item_barcode', 'color', 'size', 'group', 'sub_group', 'category', 'kind', 'type', 'brand', 'collection', 'year', 'active');
    clear_filter($filter);
  }
}
