<?php
class Product_image_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert('images', $ds) : FALSE;
  }

  public function update_product_image(array $ds = array())
  {
    return !empty($ds) ? $this->db->replace('image_product', $ds) : FALSE;
  }

  public function get_style_images($style)
  {
    $rs = $this->db->where('style', $style)->get('images');
    return $rs->result();
  }

  public function get_id_image($code)
  {
    $rs = $this->db->where('code', $code)->get('image_product');
    return $rs->num_rows() > 0 ? $rs->row()->id_image : 0;
  }

  public function get_new_id()
  {
    $rs = $this->db->select_max('id')->get('images');
    return $rs->num_rows() == 1 ? $rs->row()->id + 1 : 1;
  }

  public function get_style_code($id)
  {
    $rs = $this->db->where('id', $id)->get('images');
    return $rs->num_rows() > 0 ? $rs->row()->style : '';    
  }

  public function has_cover($style)
  {
    return $this->db->where('style', $style)->where('cover', 1)->count_all_results('images') > 0;
  }

  public function unset_cover($style)
  {
    return $this->db->set('cover', 0)->where('style', $style)->update('images');
  }

  public function set_cover($id)
  {
    return $this->db->set('cover', 1)->where('id', $id)->update('images');
  }

  public function get_cover($code)
  {
    $rs = $this->db->where('style', $code)->where('cover', 1)->get('images');
    return $rs->num_rows() > 0 ? $rs->row()->id : $this->get_exists_style_image($code);
  }

	//--- pick a single image randomly in style
	public function get_exists_style_image($code)
	{
		 $rs = $this->db
		 ->select('image_product.id_image')
		 ->from('image_product')
		 ->join('products', 'image_product.code = products.code', 'left')
		 ->where('products.style_code', $code)
		 ->where('image_product.id_image IS NOT NULL', NULL, FALSE)
		 ->order_by('image_product.id_image', 'ASC')
		 ->limit(1)
		 ->get();

    return $rs->num_rows() > 0 ? $rs->row()->id_image : 0;
	}

  public function delete_product_image($id)
  {
    $this->db->trans_start();
    $this->db->where('id', $id)->delete('images');
    $this->db->where('id_image', $id)->delete('image_product');
    $this->db->trans_complete();
    return $this->db->trans_status();
  }
}
