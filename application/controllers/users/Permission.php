<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends PS_Controller
{
	public $menu_code = 'SCPERM'; //--- Add/Edit Profile
	public $menu_group_code = 'SC'; //--- System security
	public $title = 'Permission';
  public $permission = FALSE;

  public function __construct()
  {
    parent::__construct();
    //--- If any right to add, edit, or delete mean granted
    if($this->pm->can_add OR $this->pm->can_edit OR $this->pm->can_delete)
    {
      $this->permission = TRUE;
    }

    $this->home = base_url().'users/permission';
    $this->load->model('users/profile_model');
    $this->load->model('users/permission_model');
		$this->load->model('menu');
  }

  public function index()
  {
		$filter = array(
			'name' => get_filter('name', 'profileName', ''),
			'menu' => get_filter('menu', 'menux', 'all'),
			'permission' => get_filter('permission', 'permission', 'all')
		);

		if($this->input->post('search'))
		{
			redirect($this->home);
			exit;
		}
		
		$perpage = get_rows();
		$segment = 4;
		$rows = $this->profile_model->count_rows($filter);
		$filter['data'] = $this->profile_model->get_list($filter, $perpage, $this->uri->segment($segment));
   
    if(!empty($filter['data']))
    {
      foreach($filter['data'] as $rs)
      {
				$rs->member = $this->profile_model->count_members($rs->id);
      }
    }

		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $segment);
		$this->pagination->initialize($init);
    $this->load->view('users/permission_view', $filter);
  }


  public function edit_permission($id)
  {
    $this->load->model('menu');
    $profile = $this->profile_model->get_profile($id);
    $this->title = 'กำหนดสิทธิ์ - '.$profile->name;
    $data['data'] = $profile;
    $data['menus'] = array();
    $groups = $this->menu->get_menu_groups();

    if(!empty($groups))
    {
      foreach($groups as $group)
      {
				if($group->pm)
				{
					$ds = array(
						'group_code' => $group->code,
						'group_name' => $group->name,
						'menu' => ''
					);

					$menus = $this->menu->get_menus_by_group($group->code);

					if(!empty($menus))
					{
						$item = [];

						foreach($menus as $menu)
						{
							if($menu->valid)
							{
								$item[] = array(
									'menu_code' => $menu->code,
									'menu_name' => $menu->name,
									'permission' => $this->permission_model->get_permission($menu->code, $id)
								);								
							}
						}

						$ds['menu'] = $item;
					}

					$data['menus'][] = $ds;
				}
      }
    }

    $this->load->view('users/permission_edit_view', $data);
  }


	public function save_profile_permission()
	{
		if($this->input->post('id_profile'))
		{
			$id_profile = $this->input->post('id_profile');
			$menu = $this->input->post('menu');
			$view = $this->input->post('view');
			$add = $this->input->post('add');
			$edit = $this->input->post('edit');
			$delete = $this->input->post('delete');
			$approve = $this->input->post('approve');

			$this->permission_model->drop_profile_permission($id_profile);

			if(!empty($menu))
			{
				foreach($menu as $code)
				{
					$pm = array(
						'menu' => $code,
						'uid' => NULL,
						'id_profile' => $id_profile,
						'can_view' => isset($view[$code]) ? 1 : 0,
						'can_add' => isset($add[$code]) ? 1 : 0,
						'can_edit' => isset($edit[$code]) ? 1 : 0,
						'can_delete' => isset($delete[$code]) ? 1 : 0,
						'can_approve' => isset($approve[$code]) ? 1 : 0
					);

					$this->permission_model->add($pm);
				}
			}

			set_message('Done!');
			redirect($this->home.'/edit_permission/'.$id_profile);
		}
	}

  public function clear_filter()
  {
		$filter = array('profileName', 'menux', 'permission');
    return clear_filter($filter);    
  }
}
