<?php 
namespace Test2\Model;
use Think\Model;
class MemberModel extends Model {  
	protected $tablePrefix = 'mall_';   
	protected $tableName = 'member_level_and_point_tbl'; 

	public function add_member_tbl_data($add_data)
	{
		$add_id = $this->data($add_data)->add();
		return $add_id?$add_id:0;
	}

	public function update_member_tbl_data($where,$update_data)
	{
		$update_data = $this->where($where)->save($update_data);
		return $update_data !== false?true:false;
	}

	public function find_member_tbl_data($where)
	{
		$result = $this->where($where)->find();
		return $result?$result:array();
	}

	public function get_member_tbl_list($where)
	{
		$result = $this->where($where)->select();
		return $result?$result:array();
	}

	public function get_member_level($user_id)
	{
		/*$where['customer_id'] = $user_id
		$level = $this->where($where)->getField('level');
		var_dump($this->getLastsql());exit();
		return $level?$level:1;*/
	}
	



}