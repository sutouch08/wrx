<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Discount_policy extends PS_Controller
{
  public $menu_code = 'SCPOLI';
	public $menu_group_code = 'SC';
	public $title = 'นโยบายส่วนลด';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'discount/discount_policy';
    $this->load->model('discount/discount_policy_model');
    $this->load->model('discount/discount_rule_model');
  }

  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'policy_code', ''),
      'name' => get_filter('name', 'policy_name', ''),
      'active' => get_filter('active', 'active', 'all'),
      'start_date' => get_filter('start_date', 'start_date', ''),
      'end_date' => get_filter('end_date', 'end_date', '')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }
    
		$perpage = get_rows();
    $rows = $this->discount_policy_model->count_rows($filter);					
    $filter['data'] = $this->discount_policy_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);		    
		$this->pagination->initialize($init);
    $this->load->view('discount/policy/policy_list', $filter);
  }

  public function add_new()
  {
    if(! $this->pm->can_add)
    {
      $this->deny_page(TRUE);
      exit;
    }

    $this->load->view('discount/policy/policy_add');
  }

  public function add()
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds) || !isset($ds->name) || !isset($ds->start_date) || !isset($ds->end_date))
    {
      $sc = FALSE;
      set_error('required');
    }

    if($sc === TRUE && ! $this->pm->can_add)
    {
      $sc = FALSE;
      set_error('permission');
    }

    if($sc === TRUE)
    {
      $code = $this->get_new_code();

      $arr = array(
        'code' => $code,
        'name' => $ds->name,
        'start_date' => db_date($ds->start_date),
        'end_date' => db_date($ds->end_date),
        'user' => $this->_user->uname
      );

      if( ! $this->discount_policy_model->add($arr))
      {
        $sc = FALSE;
        set_error('insert');
      }
    }

    $arr = array(
      'status' => $sc === TRUE ? 'success' : 'error',
      'message' => $sc === TRUE ? 'success' : $this->error,
      'code' => isset($code) ? $code : ''
    );

    echo json_encode($arr);
  } 

  public function edit($code)
  {    
    if(! $this->pm->can_add && ! $this->pm->can_edit)
    {
      $this->deny_page(TRUE);
      exit;
    }

    $this->load->helper('discount_rule');    
    $policy = $this->discount_policy_model->get_policy_by_code($code);
    $rules = $this->discount_rule_model->get_policy_rules($policy->id);
    $ds = array(
      'policy' => $policy,
      'rules' => $rules
    );

    $this->load->view('discount/policy/policy_edit', $ds);
  }

  public function update()
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds) || !isset($ds->id) || !isset($ds->name) || !isset($ds->start_date) || !isset($ds->end_date) || !isset($ds->active))
    {
      $sc = FALSE;
      set_error('required');
    }

    if($sc === TRUE && ! $this->pm->can_edit)
    {
      $sc = FALSE;
      set_error('permission');
    }

    if($sc === TRUE)
    {
      $arr = array(
        'name' => $ds->name,
        'start_date' => db_date($ds->start_date),
        'end_date' => db_date($ds->end_date),
        'active' => $ds->active,
        'update_user' => $this->_user->uname
      );

      if(! $this->discount_policy_model->update($ds->id, $arr))
      {
        $sc = FALSE;
        set_error('update');
      }
    }

    $this->_response($sc);
  }

  public function set_active()
  {
    $sc = TRUE;
    $id = $this->input->post('id');
    $active = $this->input->post('active');

    if(empty($id) || ($active === NULL || ($active != 0 && $active != 1)))
    {
      $sc = FALSE;
      set_error('required');
    }

    if($sc === TRUE && ! $this->pm->can_edit)
    {
      $sc = FALSE;
      set_error('permission');
    }

    if($sc === TRUE)
    {
      $arr = array(
        'active' => $active,
        'update_user' => $this->_user->uname
      );
      
      if(! $this->discount_policy_model->update($id, $arr))
      {
        $sc = FALSE;
        set_error('update');
      }
    }

    $this->_response($sc);
  }

  public function add_rule() 
  {
    $sc = TRUE;
    $id = $this->input->post('id_policy');
    $rules = $this->input->post('rules');

    if(empty($id) || empty($rules) || !is_array($rules))
    {
      $sc = FALSE;
      set_error('required');
    }

    if($sc === TRUE)
    {      
      $arr = array(
        'id_policy' => $id,
        'update_user' => $this->_user->uname
      );

      if( ! $this->discount_rule_model->update_batch($arr, $rules))
      {
        $sc = FALSE;
        set_error('update');
      }      
    }

    $this->_response($sc);
  }

  public function view_detail($code)
  {
    $this->load->helper('discount_rule');
    $rs = $this->discount_policy_model->get_policy_by_code($code);
    $ds['policy'] = $rs;
    $ds['rules']  = $this->discount_rule_model->get_policy_rules($rs->id);
    $this->load->view('discount/policy/policy_view_detail', $ds);
  }

  public function get_active_rule()
  {
  	$rules = $this->discount_rule_model->get_active_rule();    
  	$ds = [];

    if(!empty($rules))
    {
      foreach($rules as $rs)
      {
        $ds[] = array(
          'id_rule' => $rs->id,
          'ruleCode' => $rs->code,
          'ruleName' => $rs->name,
          'date_upd' => thai_date($rs->date_upd)
        );
      }
    }
    else
    {
      $ds[] = array('nodata' => 'nodata');
    }

    echo json_encode($ds);
  }


  public function delete()
  {
    $sc = TRUE;
    $id = $this->input->post('id');

    if(! $this->pm->can_delete)
    {
      $sc = FALSE;
      set_error('permission');
    }
    
    if($sc === TRUE && $this->discount_policy_model->has_transection($id))
    {
      $sc = FALSE;
      set_error('transaction');
    }

    if($sc === TRUE)
    {
      $this->db->trans_begin();
      
      $arr = array(
        'id_policy' => NULL,
        'update_user' => $this->_user->uname
      );

      if(! $this->discount_rule_model->update_by_policy($id, $arr))
      {
        $sc = FALSE;
        $this->error = "Failed to unlink policy from rules";
      }

      if($sc === TRUE)
      {
        if(! $this->discount_policy_model->delete($id))
        {
          $sc = FALSE;
          $this->error = "Failed to delete policy";
        }
      }

      if($sc === TRUE)
      {
        $this->db->trans_commit();
      }
      else
      {
        $this->db->trans_rollback();
      }
    }

    $this->_response($sc);
  }

  public function get_new_code()
  {
    $date = date('Y-m-d');
    $Y = date('y', strtotime($date));
    $M = date('m', strtotime($date));
    $prefix = getConfig('PREFIX_POLICY');
    $run_digit = getConfig('RUN_DIGIT_POLICY');
    $pre = $prefix .'-'.$Y.$M;
    $code = $this->discount_policy_model->get_max_code($pre);
    if(! is_null($code))
    {
      $run_no = mb_substr($code, ($run_digit*-1), NULL, 'UTF-8') + 1;
      $new_code = $prefix . '-' . $Y . $M . sprintf('%0'.$run_digit.'d', $run_no);
    }
    else
    {
      $new_code = $prefix . '-' . $Y . $M . sprintf('%0'.$run_digit.'d', '001');
    }

    return $new_code;
  }


  public function clear_filter()
  {
    $filter = array('policy_code', 'policy_name', 'active', 'start_date', 'end_date');
    clear_filter($filter);
    echo 'done';
  }

}//-- end class
?>
