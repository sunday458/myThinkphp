<?php 
namespace Test2\Model;
use Think\Model;
class ActivityModel extends Model {  
	protected $tablePrefix = 'yj_';   
	protected $tableName = 'preferential_activities'; 

	protected $_validate =array(
        array('activity_name','require','优惠活动名称必填！'),
        array('begin_time','require','开始时间必填'),
        array('end_time','require','结束时间必填'),
        array('activity_goods_id','require','参与活动的商品必选或必填'),
        array('level','require','适用会员等级必选'),
        //array('phone','checkPhone','联系方式不正确',0,'callback'), 
    );

	public function add_activity_data($add_data)
	{
		$add_id = $this->data($add_data)->add();
		return $add_id?$add_id:0;
	}

	public function update_activity_data($where,$update_data)
	{
		$update_data = $this->where($where)->save($update_data);
		return $update_data !== false?true:false;
	}

	public function get_activity_data($where)
	{
		$result = $this->where($where)->find();
		return $result?$result:array();
	}

	public function get_activity_list($where)
	{
		$result = $this->where($where)->select();
		return $result?$result:array();
	}

	public function get_activity_list_count($where='')
	{
		if($where)
		{
			$count = $this->where($where)->count('id');
		}
		$count = $this->count('id');
		return $count?$count:0;
	}

	



}