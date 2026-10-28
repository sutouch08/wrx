<?php
function _check_login()
{
  $ci =& get_instance();
  $uid = get_cookie('uid');

  if(empty($uid) OR !$ci->user_model->verify_uid($uid))
  {
    redirect(base_url().'users/authentication');
  }
}


function get_permission(?string $menu = '', ?string $uid = NULL)
{
  $ci =& get_instance();
  $uid = $uid ?? get_cookie('uid');
  $menu = $menu ?? ''; 
  $user = $ci->user_model->get_user_by_uid($uid);

  if(empty($user))
  {
    return reject_permission();
  }

  //--- If super admin
  if($user->id_profile == -987654321)
  {
    return (object)[
      'can_view' => 1,
      'can_add' => 1,
      'can_edit' => 1,
      'can_delete' => 1,
      'can_approve' => 1
    ];    
  }

  $pm = $ci->user_model->get_permission($menu, $uid, $user->id_profile);

  if (empty($pm))
  {
    return reject_permission();
  }

  if (getConfig('CLOSE_SYSTEM') == 2)
  {
    $pm->can_add = 0;
    $pm->can_edit = 0;
    $pm->can_delete = 0;
    $pm->can_approve = 0;
  }

  return $pm;
}

function reject_permission()
{
  return (object) [
    'can_view' => 0,
    'can_add' => 0,
    'can_edit' => 0,
    'can_delete' => 0,
    'can_approve' => 0
  ];
}

function _can_view_page($can_view = 0)
{
  if( ! $can_view)
  {
    $ci =& get_instance();
    $ci->load->view('deny_page');    
  }
}

function user_in(?string $txt = '')
{
  $sc = ['0'];
  $ci =& get_instance();
  $ci->load->model('users/user_model');
  $users = $ci->user_model->search($txt ?? '');

  if(!empty($users))
  {
    foreach($users as $rs)
    {
      $sc[] = $rs->uname;
    }
  }

  return $sc;
}

function select_user_id($user_id = NULL)
{
  $sc = "";
  $ci =& get_instance();
  $list = $ci->user_model->get_all();

  if( ! empty($list))
  {
    foreach($list as $rs)
    {
      $sc .= '<option value="'.$rs->id.'" data-uname="'.$rs->uname.'" data-uid="'.$rs->uid.'" '.is_selected($rs->id, $user_id).'>'.$rs->uname.' : '.$rs->name.'</option>';
    }
  }

  return $sc;
}

function select_uname($uname = NULL)
{
  $sc = "";
  $ci =& get_instance();
  $list = $ci->user_model->get_all();

  if( ! empty($list))
  {
    foreach($list as $rs)
    {
      $sc .= '<option value="'.$rs->uname.'" data-id="'.$rs->id.'" data-uid="'.$rs->uid.'" '.is_selected($rs->uname, $uname).'>'.$rs->name.'</option>';
    }
  }

  return $sc;
}

function select_user($uname = NULL)
{
	$ds = '';
	$ci =& get_instance();
	$ci->load->model('users/user_model');
	$option = $ci->user_model->get_all();

	if( ! empty($option))
	{
		foreach($option as $rs)
		{
			$ds .= '<option value="'.$rs->uname.'" '.is_selected($rs->uname, $uname).'>'.$rs->uname." | ".$rs->name.'</option>';
		}
	}

	return $ds;
}


function display_name($uname = '')
{
  $ci =& get_instance();
  $ci->load->model('users/user_model');
  return $ci->user_model->get_name($uname);  
}
