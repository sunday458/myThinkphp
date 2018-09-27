<?php 
namespace Test2\Model;
use Think\Model;
class MattersModel extends Model {  
	protected $tablePrefix = 'yj_';   
	protected $tableName = 'contract_log'; 

	public function add_contract_log_data($add_data)
	{
		$add_id = $this->data($add_data)->add();
		return $add_id?$add_id:0;
	}

	public function update_contract_log_data($where,$update_data)
	{
		$update_data = $this->where($where)->save($update_data);
		return $update_data !== false?true:false;
	}

	public function get_contract_log_data($where)
	{
		$result = $this->where($where)->find();
		return $result?$result:array();
	}

	public function get_contract_log_list($where)
	{
		$result = $this->where($where)->select();
		return $result?$result:array();
	}


}