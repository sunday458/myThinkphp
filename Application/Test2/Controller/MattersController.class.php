<?php  
namespace Test2\Controller;
use Think\Controller;
/**
* 
*/
class MattersController extends Controller
{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function get_department_list($where='')
	{
		header('Content-type:text/html;charset=utf-8');
		$department_model = M('department',null,'DB_CONFIG3');
		$where['status'] = 1;
		//$field = 'id,com_id,name,parent,parents_list';
		$field = 'id,name,code,parent,duty,remark';
		$department_list = $department_model->where($where)->field($field)->order('step desc')->select();
		$department_list = $this->get_department_list_by_sql();
		echo '<pre>';
		var_dump($department_list);
		//var_dump($this->sortOut($department_list));
		echo '</pre>';
	}

	public function get_department_list_by_sql($pid = 0, $level = 0 ,$res = array())
    {
		$where = array(
					 'parent'=>$pid,
					 'status'=>1,
					 );
		$department_model = M('department',null,'DB_CONFIG3');
		$field = 'id,name,code,parent,duty,remark';
		$data = $department_model->where($where)->field($field)->order('step DESC,id ASC')->select();
        foreach($data as $v)
        {
			$v['level'] = $level;
            $res[] = $v;
            $res = $this->get_department_list_by_sql( $v['id'], $level+1 , $res); 
		}
		return $res;
    }

	public function add_login_log($user_id=180)
	{
		header('Content-type:text/html;charset=utf-8');
		if(!$user_id)
		{
			return false;
		}

		$employee_model = M('employee',null,'DB_CONFIG3');
		$employee_data = $employee_model->where(array('id'=>$user_id))->field('id,name,dep_id,position_name,status')->find();
		//$department_model = M('department',null,'DB_CONFIG3');
		//$dep_name = $department_model->where(array('id'=>$employee_data['dep_id']))->getField('name');
		
		$add_data = array(
			'user_id' =>$user_id,
			'name' =>$employee_data['name'],
			'department_id'  =>$employee_data['dep_id'],
			//'department_name'=>$dep_name,
			'position_name'  =>$employee_data['position_name'],
			'add_time'=>time(),
		);
		var_dump($add_data);exit();

		$login_log = M('user_login_log','yj_');
		$log_id = $login_log->data($add_data)->add();
	}

	public function disable_user_login($user_id=210)
	{
		header('Content-type:text/html;charset=utf-8');
		$return_data = array(
			'status'=>0,
			'msg'=>'ID '.$user_id.'用户不存在！',
			'status'=>0,
		);
		//$employee_model = M('employee',null,'DB_CONFIG3');
		$users_model = M('users',null,'DB_CONFIG3');
		$user_data = $employee_model->where(array('id'=>$user_id))->getField('id,status');
		//echo $employee_model->getLastsql();
		if ($user_data) 
		{
			
			$update_data = array('error_status'=>2);
			$update_result = $employee_model-> where(array('id'=>$user_id))->setField($update_data);
			if($update_data!==false)
			{
				$return_data['status'] = 1;
				$return_data['msg'] = '禁用ID '.$user_id.'用户成功！';
			}else
			{
				$return_data['msg'] = '禁用ID '.$user_id.'用户失败！';
			}
		}
		
		var_dump($return_data);
		//return $this->ajaxReturn($return_data);
	}

	public function get_roles_list()
	{
		header('Content-type:text/html;charset=utf-8');
		$roles_model = M('roles','crm_','DB_CONFIG3');
		$roles_list = $roles_model->getField('id,role_name');
		var_dump($roles_list);
	}

	public function get_customer_source_list()
	{
		header('Content-type:text/html;charset=utf-8');
		$customer_source_model = M('customer_source',null,'DB_CONFIG3');
		$customer_source_list = $customer_source_model->getField('id,name');
		var_dump($customer_source_list);
	}

	static public function sortOut(&$list, $pid =0, $level =0, $html ='-'){

		static $tree = array();

/*		foreach($list as $val){

			if($val['parent'] == $pid){

				$val['auth_level'] = $level + 1;

				$val['html'] = str_repeat($html, $level);

				$val['name'] = "{$val[html]} {$val['name']}";

				$tree[] = $val;

				self::sortOut($list, $val['id'], $level +1, $html);

			}

		}
*/		foreach($list as $val)
		{
			$lev_html = str_pad('',$val['level']*2,$html,STR_PAD_LEFT);
			$val['name'] = $lev_html." {$val['name']}";
			$tree[] = $val;
		}

		return $tree;

	}

	public function tets_where()
	{
		$member_model = M('member_level_and_point_tbl','yj_');

		$limit = '100,100';
		$order = 'yj_member_level_and_point_tbl.id desc';
		$join = 'LEFT JOIN customer b ON yj_member_level_and_point_tbl.customer_id = b.id';
		$list = $member_model->where($where)->join($join)->order($order)->limit($limit)->select();
		$count = $member_model->where($where)->count();
		var_dump($list);
		//var_dump($count);
	}

	public function add_contract()
	{
		header('Content-type:text/html;charset=utf-8');
		$e_id = 180;
		$employee_model = M('employee',null);
		$contract_model = M('contract_log','yj_');
		//$contract_model = D('Matters');
		$department_model = M('department',null);
		//查询员工信息
		//$employee_info = $employee_model->where(array('id'=>$e_id))->getField('id,dep_id,name,code,entry_date,position_name,company_ownership');
		$employee_info = $employee_model->where(array('id'=>$e_id))->field('id,dep_id,name,code,entry_date,position_name,company_ownership,fixed_begin_time,fixed_end_time')->find();
		$department_name = $department_model->where(array('id'=>$employee_info['dep_id']))->getField('name');
		$employee_info['department_name'] = $department_name;
		var_dump($employee_info);
		$contract_type = 2; //1试用 2转正
		if($contract_type == 1)
		{
			//试用期信息数据
			$trial_begin_time = strtotime('2017-9-23');
			$trial_end_time = strtotime('2017-12-23');
			$add_data['trial_begin_time'] = $trial_begin_time;
			$add_data['trial_end_time'] = $trial_end_time;
		}
		elseif ($contract_type == 2) 
		{
			//转正信息数据
			$renew_begin_time = strtotime('2017-9-23');
			$renew_end_time = strtotime('2017-12-23');
			$add_data['renew_time'] = strtotime('2017-12-24');
			$add_data['renew_begin_time'] = $renew_begin_time;
			$add_data['renew_end_time'] = $renew_end_time;
			$renew_num = $contract_model->where(array('e_id'=>$e_id))->order('renew_num desc')->getField('renew_num');
			$renew_num = $renew_num?$renew_num:1;
			$add_data['renew_num'] = $renew_num+1;
		}
		$add_data['contract_type'] = $contract_type;		
		$add_data['e_id'] = $e_id;
		$add_data['add_user_id'] = 100;
		$add_data['add_time'] = time();
		var_dump($add_data);
		$return_result = 1;
		$return_data = array('status'=>-1,'msg'=>'员工续签信息添加失败');
		M()->startTrans();
		$add_id = $contract_model->add($add_data);
		$return_result&&$add_id?$return_result=1:$return_result=0;
		$update['fixed_begin_time'] = $renew_begin_time;
		$update['fixed_end_time'] = $renew_end_time;
		$update_result = $employee_model->where(array('id'=>$e_id))->save($update);
		$return_result&&($update_data!==false)?$return_result=1:$return_result=0;
		if($return_result)
		{
			M()->commit();
			$return_data['status'] = 1;
			$return_data['msg'] = '员工续签信息添加和更新员工固定限期成功';
		}
		else
		{
			M()->rollback();
			$return_data['msg'] = '员工续签信息添加失败或更新员工固定限期失败';
		}
		var_dump($return_data);

	}

	public function update_contract()
	{
		$e_id = 180; //员工ID
		$c_id = 1;   //续签表记录ID
		$post_data = I('post.');
		$return_result = 1;
		$return_data = array('status'=>-1,'msg'=>'员工续签信息添加失败');

		if($contract_type == 1)
		{
			//试用期信息数据
			$trial_begin_time = $post_data['trial_begin_time']?$post_data['trial_begin_time']:'';
			$trial_end_time = $post_data['trial_begin_time']?$post_data['trial_begin_time']:'';
			$update_contract_data['trial_begin_time'] = $trial_begin_time;
			$update_contract_data['trial_end_time'] = $trial_end_time;
			$fixed_begin_time = $trial_begin_time;
			$fixed_end_time = $trial_end_time;
			
		}
		elseif ($contract_type == 2) 
		{
			//转正信息数据
			$renew_begin_time = $post_data['renew_begin_time']?$post_data['renew_begin_time']:'';
			$renew_end_time =  $post_data['renew_end_time']?$post_data['renew_end_time']:'';
			$update_contract_data['renew_num'] = $renew_num;
			$update_contract_data['renew_begin_time'] = $renew_begin_time;
			$update_contract_data['renew_end_time'] = $renew_end_time;
			$update_contract_data['renew_time'] = $renew_time; //签约时间
			$fixed_begin_time = $renew_begin_time;
			$fixed_end_time = $renew_end_time;
		}
		$update_contract_data['update_user_id'] = 100;
		$update_contract_data['update_time'] = time();

		M()->startTrans();

		$contract_model = M('contract_log','yj_');
		$employee_model = M('employee',null);
		// 更新合同续签表
		$update_contract_result = $contract_model->where(array('id'=>$c_id))->save($update_contract_data);
		$return_result&&($update_contract_result!==false)?$return_result=1:$return_result=0;
		// 更新主表员工表
		$update['fixed_begin_time'] = $fixed_begin_time;
		$update['fixed_end_time'] = $fixed_end_time;
		$update_employee_result = $employee_model->where(array('id'=>$e_id))->save($update);
		$return_employee_result&&($update_employee_result!==false)?$return_result=1:$return_result=0;

		if($return_result)
		{
			M()->commit();
			$return_data['status'] = 1;
			$return_data['msg'] = '员工续签信息添加和更新员工固定限期成功';
		}
		else
		{
			M()->rollback();
			$return_data['msg'] = '员工续签信息添加失败或更新员工固定限期失败';
		}
		var_dump($return_data);
	}

	public function get_employee_contract_info()
	{
		header('Content-type:text/html;charset=utf-8');
		$e_id = 180;
		$sql = " SELECT a.id,a.dep_id,a.position_name,a.name,entry_date,a.phone,b.* FROM `employee` a LEFT JOIN `yj_contract_log` b ON a.id=b.e_id WHERE a.id=$e_id ORDER BY add_time DESC, renew_num DESC ";
		$employee_model = M('employee',null);
		$list = $employee_model->query($sql);
		var_dump($list);
	}

	/** 
     * @uses 根据生日计算年龄，年龄的格式是：2016-09-23 
     * @param string $birthday 
     * @return string|number 
     */  
    public function calcAge($birthday) {  
        $iage = 0;  
        if (!empty($birthday)) {  
            $year = date('Y',strtotime($birthday));  
            $month = date('m',strtotime($birthday));  
            $day = date('d',strtotime($birthday));  
              
            $now_year = date('Y');  
            $now_month = date('m');  
            $now_day = date('d');  
      
            if ($now_year > $year) {  
                $iage = $now_year - $year - 1;  
                if ($now_month > $month) {  
                    $iage++;  
                } else if ($now_month == $month) {  
                    if ($now_day >= $day) {  
                        $iage++;  
                    }  
                }  
            }  
        }  
        return $iage;  
    }  

	public function get_birthday_user_list()
	{
		$input_data = I('post.');
		$input_data['begin_time'] = '2017-2-1';
		$input_data['end_time'] = '2017-2-28';
		$now_day = date('Y-m-d');
		$begin_time = $input_data['begin_time']?$input_data['begin_time']:$now_day;
		$begin_time = date('m-d',strtotime($begin_time));
		$end_time = $input_data['end_time']?$input_data['end_time']:$now_day;
		$end_time = date('m-d',strtotime($end_time));

		$employee_model = M('employee',null);

		//$where['birthday'] = array(array('EGT',$begin_time),array('ELT',$end_time));
		$where = " DATE_FORMAT(birthday, '%m-%d') >= '$begin_time' AND DATE_FORMAT(birthday, '%m-%d') <= '$end_time'  ";
        //$b_join = " LEFT JOIN `yj_contract_log` b ON a.id=b.e_id ";
        $d_join = ' LEFT JOIN department d ON a.dep_id = d.id ';
        $fields = 'a.id as emp_id,a.dep_id,a.position_name,a.name,a.entry_date,a.phone,a.birthday,d.name as dep_name';
        $order = 'a.cdate,a.id DESC';
        $list = $employee_model->alias('a')->join($d_join)->where($where)->order($order)->field($fields)->select();

        echo M()->getLastsql();
        var_dump($list);
	}

	public function get_emp_data()
	{
		$sql = " SELECT a.id,a.dep_id,a.`position_name`,a.`name`,a.`entry_date`,a.`quit_date`,c.contract_type,c.`renew_begin_time`,c.`renew_end_time`,
c.`renew_time`,c.`renew_num`,c.`trial_begin_time`,c.`trial_end_time`,d.`name` AS dep_name FROM `employee`a LEFT JOIN `yj_contract_log` c ON a.id = c.`e_id` LEFT JOIN `department` d ON a.dep_id = d.`id` ";

		$employee_model = M('employee',null);
		$list = $employee_model->query($sql);
		var_dump($list);
	}

	public function add_discipline()
	{
		header('Content-type:text/html;charset=utf-8');
		$input_data = I('post.');
		$e_id = $input_data['e_id']?$input_data['e_id']:2450;
		$type = $input_data['type']?$input_data['type']:2;
		$reason = $input_data['reason'];
		$reword_and_punish_info = C('REWARD_AND_PUNISH');
		$discipline_model = M('discipline_log','yj_');
		//$employee_model = M('employee',null);
		//$department_model = M('department',null);
		/*//查询员工信息
		$employee_info = $employee_model->where(array('id'=>$e_id))->field('dep_id,name,code,entry_date,position_name,company_ownership')->find();
		$department_name = $department_model->where(array('id'=>$employee_info['dep_id']))->getField('name');
		$add_data['dep_name'] = $department_name;
		$add_data['entry_date'] = strtotime($employee_info['entry_date']);
		$add_data['name'] = $employee_info['name'];*/
		//先查询该类型的记录的第N次？
		$num = $discipline_model->where(array('e_id' =>$e_id,'type'=>$type ))->max('num');
		//echo M()->getLastsql();
		$add_data['e_id'] = $e_id;
		$add_data['type'] = $type;
		$add_data['reason'] = $reason?$resson:$reword_and_punish_info[$type];
		$add_data['num'] = $num?$num+1:1;
		$add_data['add_user_id'] = 146;
		$add_data['discipline_time'] = $input_data['discipline_time']?$input_data['discipline_time']:time();
		$add_data['add_time'] = time();
		var_dump($add_data);
		$add_id = $discipline_model->data($add_data)->add();
		if($add_id)
		{
			//return  array('status' =>1 ,'msg'=>'违纪记录入库成功' );
			var_dump(array('status' =>1 ,'msg'=>'违纪记录入库成功' ));
		}
		else
		{
			//return  array('status' =>-1 ,'msg'=>'违纪记录入库失败' );
			var_dump(array('status' =>-1 ,'msg'=>'违纪记录入库失败' ));
		}

	}

	public function my_discipline_list()
	{
		header('Content-type:text/html;charset=utf-8');
		if (IS_REQUEST)
        {
            $eq_element = array( 'name','e_id','cdate1','cdate2','type');
            $data = array_map('trim', I('request.'));
            foreach($data as $key=>$val)
            {
                if($val && $key != 'p' )
                {
                    if(in_array($key, $eq_element))
                    {
                        if($key == 'e_id')
                        {
                            $where['a.id'] =$val;
                        }
                        if($key == 'cdate1')
                        {
                            $begin_time = $val;
                            $where['a.add_time'] = array('EGT',$val);
                        }
                        if($key == 'cdate2')
                        {
                            if($begin_time)
                            {
                                $where['a.add_time'] = array(array('EGT',$begin_time),array('ELT',$val));
                            }else
                            {
                                $where['a.add_time'] = array('ELT',$val);
                            }
                        }
                        if($key == 'name')
                        {
                            $where['a.name'] = array('LIKE',"%$val%");
                        }  
                        if($key == 'type')
                        {
                            $where['a.type'] = $val;
                        }  
                    }
                }
            }
        }
        $where['a.status'] = 1;
		$discipline_model = M('discipline_log','yj_');
		$b_join = " LEFT JOIN employee e ON a.e_id=e.id ";
        $d_join = ' LEFT JOIN department d ON e.dep_id = d.id ';
        $fields = 'a.id as disc_id,a.type,a.e_id,a.add_time,a.num,a.reason,a.status,a.discipline_time,e.name as user_name,e.entry_date,d.name as dep_name';
        $order = 'a.add_time DESC,a.id DESC';
        $list = $discipline_model->alias('a')->join($b_join)->join($d_join)->where($where)->order($order)->field($fields)->select();
        echo M()->getLastsql();
		if(!$list)
		{
			$list = array();
		}
		echo '<pre>';
		var_dump($list);
		echo '</pre>';

	}

	public function del_my_discipline($d_id='')
	{
		$d_id = 10;
		$discipline_model = M('discipline_log','yj_');
		$update_data['status'] = 0;
		$update_result = $discipline_model->where(array('id'=>$d_id))->save($update_data);
		if($update_data!==false)
		{
			return  array('status' =>1 ,'msg'=>'违纪记录删除成功' );
		}
		else
		{
			return  array('status' =>-1 ,'msg'=>'违纪记录删除失败' );
		}
	}

	public function update_my_discipline($d_id='')
	{
		if(IS_POST)
        {
            $input_data = I('post.');
            $d_id = $input_data['d_id'];
            $type = $input_data['type'];
            $reason   = $input_data['reason'];
            $renew_time = $input_data['renew_time'];
            $discipline_time = strtotime(urldecode($input_data['discipline_time']));
            $d_id = 10;
            $update_data['type'] = $type;
            $update_data['reason'] = $reason;
            $update_data['discipline_time'] = $discipline_time;
            $update_data['update_time'] = time();
            $update_data['update_user_id'] = 146;

			$discipline_model = M('discipline_log','yj_');
			
			$update_result = $discipline_model->where(array('id'=>$d_id))->save($update_data);
			if($update_data!==false)
			{
				return  array('status' =>1 ,'msg'=>'违纪记录更新成功' );
			}
			else
			{
				return  array('status' =>-1 ,'msg'=>'违纪记录更新失败' );
			}
        }
		
	}

	public function  check_background()
	{
		$input_data = I('post.');
		$e_id = $input_data['e_id'];

		$e_id = 2450;
		$employee_model = M('employee',null);
		$department_model = M('department',null);
		//查询员工信息
		$employee_info = $employee_model->where(array('id'=>$e_id))->field('id,dep_id,name,code,entry_date,position_name,company_ownership,fixed_begin_time,fixed_end_time')->find();
		$department_name = $department_model->where(array('id'=>$employee_info['dep_id']))->getField('name');
		$employee_info['department_name'] = $department_name;

		var_dump($employee_info);
		if(IS_POST)
		{
			$add_data['check_time'] = strtotime(urldecode($input_data['check_time']));
			$add_data['result'] = $input_data['result'];
			$add_data['is_work'] = $input_data['is_work'];
			$check_background_log_model = M('check_background_log',null);
			$add_id = $check_background_log_model->add($add_data);
			if($add_id)
			{
				return  array('status' =>1 ,'msg'=>'背景调查记录添加成功' );
			}
			else
			{
				return  array('status' =>-1 ,'msg'=>'背景调查记录添加失败' );
			}
		}


	}

/**
 * 获取工作时长
 */
	public function get_work_time()
	{
		$startDate="2001-12-12"; 
		$endDate="2002-11-1"; 
		  
		$holidayArr=array("05-01","05-02","10-01","10-01","10-02","10-03","10-04","10-05","01-26","01-27","01-28","01-29"); 
		 //假期日期数组，比方国庆,五一,春节等 
		$endWeek=2; 
		 //周末是否双休.双休为2，仅仅星期天休息为1，没有休息为0 
		  
		$beginUX=strtotime($startDate); 
		$endUX=strtotime($endDate); 
		  
		for($n=$beginUX;$n<=$endUX;$n=$n+86400)
		{ 
			$week=date("w",$n); 
			$MonDay=date("m-d",$n); 
			if($endWeek)
			{
			//去处周末休息 
				if($endWeek==2)
				{ 
					if($week==0||$week==6) continue; 
				} 
				if($endWeek==1)
				{ 
					if($week==0) continue; 
				} 
			} 
			if(in_array($MonDay,$holidayArr)) continue; 
			$totalHour+=10;//每天工作10小时 
		} 
		echo "开始日期：$startDate<BR>"; 
		echo "结束日期：$endDate<BR>"; 
		echo "共花了".$totalHour."小时"; 
	}

	public function get_deparment_month_entry_data()
	{
		header('Content-type:text/html;charset=utf-8');
		$dep_id = 150;
		$now_date = date('Y-m-d');
		$date_data = getthemonth($now_date);
		$month_begin_time = $date_data[0];
		$month_begin_time = '2017-9-1';
		$month_end_time = $date_data[1];
		$month_end_time = '2017-10-31';

		$dep_model = M('department',null);
		$no_sell_dep_array = array(55,56,61,64,95,96,154,156,232,239,276);  //非销售部门
		$no_sell_where['id'] = array('in',$no_sell_dep_array);
		$no_sell_dep_data = $dep_model->where($no_sell_where)->field('id,name,code,child_list')->select();
		
		$sell_dep_array = array(102,101,133,170,267); //销售部门
		$sell_where['id'] = array('in',$sell_dep_array);
		$sell_dep_data = $dep_model->where($sell_where)->field('id,name,code,child_list')->select();
		var_dump($sell_dep_data);echo '<br>';echo '<br>';
		//$sell_dep_data = array(4=>array('id'=>279,'name'=>'8区','code'=>'888','child_list'=>'280,279,269,268'));

		//获取各个部门的人员数量
		// type 1每月入职 2每月内推 3在职 4在职内推 5每月入职 6当月离职 7当月离职内推 8非当月离职内推
		$is_work = 1;
		$where = '';
		if($is_work == 1)  //是否在职
		{
			if(strlen($where)>0)
			{
				$where = $where.' AND ';
			}
			$where .= " status = 1 ";
			//$where['entry_date'] = array(array('egt',$month_begin_time),array('elt',$month_end_time));
			if(strlen($where)>0) 
			{
				$where = $where.' AND ';
			}
			$where .= " entry_date >= '$month_begin_time' AND entry_date <= '$month_end_time' ";  
		}
		else
		{
			if(strlen($where)>0)
			{
				$where = $where.' AND ';
			}
			$where .= " status = 0 ";
			//$where['quit_date'] = array(array('egt',$month_begin_time),array('elt',$month_end_time));
			if(strlen($where)>0) 
			{
				$where = $where.' AND ';
			}
			$where .= " quit_date >= '$month_begin_time' AND quit_date <= '$month_end_time' "; 
		}
		if(strlen($where)>0) 
		{
			$where = ' WHERE '.$where;
		}
		$employee_model = M('employee',null);
		$sql = " SELECT COUNT('id') AS c,dep_id FROM employee $where GROUP BY dep_id ORDER BY dep_id ";
		//echo $sql.'<br/><br/>';
		//$employee_list = $employee_model->field('dep_id,count(id)')->group('dep_id')->count();
		$employee_list = $employee_model->query($sql);
		$employee_list = array_as_key($employee_list,'dep_id');
		var_dump($employee_list);echo '<br/>';echo '<br/>';
		//$employee_dep_key_list = array_keys($employee_list);
		//var_dump($employee_dep_key_list);echo '<br/>';
		//计算销售人数
		/*foreach ($sell_dep_data as $s_k => $s_v) 
		{
			foreach ($employee_list as $k => $v) 
			{
				$sell_str_array = explode(',', $s_v['child_list']);
				if($k==$s_v['id'] || in_array($k, $sell_str_array) )
				{
					$sell_dep_data[$s_k]['number'] = isset($sell_dep_data[$s_k]['number'])?$sell_dep_data[$s_k]['number']:0;
					$sell_dep_data[$s_k]['number'] = $sell_dep_data[$s_k]['number'] + $v['c'];
				}
			}
			$sell_str_array = array();
			//exit();
			$sell_dep_data[$s_k]['number'] = $sell_dep_data[$s_k]['number']?$sell_dep_data[$s_k]['number']:0;
		}*/
		$sell_dep_data = $this->get_dep_data_by_type($sell_dep_data,$employee_list,1);
		//var_dump($sell_dep_data);echo '<br/>';echo '<br/>';

		//计算非销售
		$no_sell_dep_data = $this->get_dep_data_by_type($no_sell_dep_data,$employee_list,2);
		//var_dump($no_sell_dep_data);echo '<br/>';
		var_dump(array_merge($no_sell_dep_data,$sell_dep_data));echo '<br/>';
	}

	public function get_dep_data_by_type($sell_array,$employee_array,$type=1)
	{

		if ($type == 1) 
		{
			//销售
			foreach ($sell_array as $s_k => $s_v) 
			{
				foreach ($employee_array as $k => $v) 
				{
					if($s_v['child_list'])
					{
						$sell_str_array = explode(',', $s_v['child_list']);
					}else
					{
						$sell_str_array = array();
					}
					if($k==$s_v['id'] || (in_array($k, $sell_str_array) && !empty($sell_str_array)) )
					{
						$sell_array[$s_k]['number'] = $sell_array[$s_k]['number'] + $v['c'];
					}
				}
				$sell_str_array = array();
				$sell_dep_data[$s_k]['number'] = $sell_dep_data[$s_k]['number']?$sell_dep_data[$s_k]['number']:0;
			}
		}
		elseif ( $type == 2 ) 
		{
			//非销售
			foreach ($sell_array as $n_k => $n_v) 
			{
				foreach ($employee_array as $k => $v) 
				{
					if($n_v['child_list'])
					{
						$sell_str_array = explode(',', $n_v['child_list']);
					}else
					{
						$sell_str_array = array();
					}
					
					if($k==$n_v['id'] || (in_array($k, $sell_str_array) && !empty($sell_str_array)))
					{
						$sell_array[$n_k]['number'] = $sell_array[$n_k]['number'] + $v['c'];
						
					}
				}
				$sell_str_array = array();
				$sell_array[$n_k]['number'] = $sell_array[$n_k]['number']?$sell_array[$n_k]['number']:0;

			}
		}

		return $sell_array;
	}

	public function get_dep_position_data()
	{
		/*$p4 = '13826443396';
		$p = is_numeric(13826443396);
		$p1 = is_numeric('0660-6606306');
		$p2 = is_numeric('6606306');
		$p3 = is_numeric(6606306);
		var_dump($p);
		var_dump($p1);
		var_dump($p2);
		var_dump($p3);
		echo strlen('6606123');
		if(is_numeric($p4)&&strlen($p4)==11)
		{
			echo '**1**<br>';
			var_dump($p4);
		}
		else
		{
			echo '--0--<br>';
			var_dump($p4);
		}*/
		/*var_dump(null);
		var_dump('');
		var_dump(0);*/
		header('Content-type:text/html;charset=utf-8');
		$now_date = date('Y-m-d');
		$date_data = getthemonth($now_date);
		$month_begin_time = $date_data[0];
		$month_begin_time = '2017-9-1';
		$month_end_time = $date_data[1];
		$month_end_time = '2017-10-31';
		$where = ' STATUS = 1 ';
		if(IS_POST)
		{
			if(strlen($where))
			{
				$where = $where.' AND ';
			}

			$where .= " entry_date >= '$month_begin_time' AND entry_date <= '$month_end_time' ";
		}
		$where = strlen($where)>0?(' WHERE '.$where):'';
		$dep_model = M('department',null);
		//$no_sell_dep_array = array(55,56,61,64,95,96,154,156,232,239,276);  //非销售部门
		$no_sell_dep_array = array(55,59,60,61,62,64,95,96,111,127,142,151,155,174,239,276,1001);  //非销售部门
		//$no_sell_dep_array = array(94,1000,1001,1002,1003);  //非销售部门
		$no_sell_where['id'] = array('in',$no_sell_dep_array);
		$no_sell_where['status'] = 1;
		//$no_sell_where['parent'] = 0;
		$no_sell_dep_data = $dep_model->where($no_sell_where)->field('id,name,code,child_list')->select();
		
		$sell_dep_array = array(102,101,133,170,267); //销售部门
		$sell_where['id'] = array('in',$sell_dep_array);
		$sell_dep_data = $dep_model->where($sell_where)->field('id,name,code,child_list')->order('name')->select();

		$employee_model = M('employee',null);
		//$sql = " SELECT COUNT('e.id') AS p_num, e.position_name,e.dep_id,d.`name` FROM employee e LEFT JOIN department d ON d.id = e.dep_id GROUP BY position_name,dep_id ORDER BY d.name,p_num DESC ";
		$sql = " SELECT COUNT('id') AS p_num ,position_id,position_name,dep_id FROM employee $where GROUP BY position_id,dep_id ORDER BY p_num DESC ";
		$sql = " SELECT COUNT('id') AS p_num ,position_id,position_name,dep_id FROM employee WHERE id IN('2570','2569','2568','2562') GROUP BY position_id,dep_id ORDER BY p_num DESC ";
		$employee_list = $employee_model->query($sql);
		//echo M()->getLastsql();exit();
		//$employee_list = array_as_key($employee_list,'dep_id');
		//var_dump($employee_list);
		
		$no_sell_dep_data = $this->deal_test_dep_position_data($no_sell_dep_data,$employee_list);
		//$no_sell_dep_data = $this->deal_dep_position_data($no_sell_dep_data,$employee_list,2);
		$sell_dep_data = $this->deal_dep_position_data($sell_dep_data,$employee_list,2);
		
		echo '<pre>';
		//var_dump($sell_dep_data);
		//var_dump($no_sell_dep_data);
		echo '</pre>';
		$new_array = array();
		foreach ($no_sell_dep_data as $k => $v) 
		{
			$new_data_array = array();
			foreach ($v['dep_data'] as $key => $value) 
			{
			 	$new_data_array['id'] = $v['id'];
			 	$new_data_array['name'] = $v['name'];
			 	$new_data_array['position_id'] = $value['position_id'];
			 	$new_data_array['position_name'] = $value['position_name'];
			 	$new_data_array['p_num'] = $value['p_num'];
			 	$new_array[] = $new_data_array;
			} 
		}
		echo '<pre>';
		var_dump($new_array);
		echo '</pre>';

	}

	public function deal_dep_position_data($sell_dep_data_array,$employee_list,$type=1)
	{
		if($type == 1)
		{
			foreach ($sell_dep_data_array as $k => $v) 
			{
				$dep_child_list_array = array();
				$dep_array = array();
				foreach ($employee_list as $e_k => $e_v) 
				{
					if($v['child_list'])
					{
						$dep_child_list_array = explode(',', $v['child_list']);
					}else
					{
						$dep_child_list_array = array();
					}
					//var_dump($dep_child_list_array);
					if($v['id'] == $e_v['dep_id'] ||  in_array($e_v['dep_id'], $dep_child_list_array) )
					{
						$dep_array[] = $e_v;
					}
				}
				$sell_dep_data_array[$k]['dep_data'] = $dep_array;
			}
		}
		elseif ( $type ==2 ) 
		{
			foreach ($sell_dep_data_array as $k => $v) 
			{
				$dep_child_list_array = array();
				$dep_array = array();
				foreach ($employee_list as $e_k => $e_v) 
				{
					if($v['child_list'])
					{
						$dep_child_list_array = explode(',', $v['child_list']);
					}else
					{
						$dep_child_list_array = array();
					}
					//var_dump($dep_child_list_array);
					if($v['id'] == $e_v['dep_id'] ||  in_array($e_v['dep_id'], $dep_child_list_array) )
					{
						$dep_array[] = $e_v;
					}
				}
				//var_dump($employee_list);exit();
				//var_dump($dep_array);exit();
				$new_dep_array = array();
				$position_name_array = array();
				/*foreach ($dep_array as $_k => $_v) 
				{
					if(in_array($_v['position_name'],$position_name_array))
					{
						/*$new_dep_array[$_k]['position_name'] = $_v['position_name'];
						$new_dep_array[$_k]['p_num'] = $new_dep_array[$_k]['p_num'] + $_v['p_num'];*/
						/*$new_dep_array[$_v['position_name']]['position_name'] = $_v['position_name'];
						$new_dep_array[$_v['position_name']]['p_num'] = $new_dep_array[$_v['position_name']]['p_num'] + $_v['p_num'];	

					}
					else
					{
						$position_name_array[] = $_v['position_name'];
						/*$new_dep_array[$_k]['position_name'] = $_v['position_name'];
						$new_dep_array[$_k]['p_num'] = $new_dep_array[$_k]['p_num'] + $_v['p_num'];	*/
						/*$new_dep_array[$_v['position_name']]['position_name'] = $_v['position_name'];
						$new_dep_array[$_v['position_name']]['p_num'] = $new_dep_array[$_v['position_name']]['p_num'] + $_v['p_num'];	
					}
				}*/
				foreach ($dep_array as $_k => $_v) 
				{
					if(in_array($_v['position_name'],$position_name_array))
					{
						
						$new_dep_array[$_v['position_name']]['position_name'] = $_v['position_name'];
						$new_dep_array[$_v['position_name']]['p_num'] = $new_dep_array[$_v['position_name']]['p_num'] + $_v['p_num'];	
					}
					else
					{
						$position_name_array[] = $_v['position_name'];
						$new_dep_array[$_v['position_name']]['position_name'] = $_v['position_name'];
						$new_dep_array[$_v['position_name']]['p_num'] = $new_dep_array[$_v['position_name']]['p_num'] + $_v['p_num'];	
					}
				}
				/*echo '<pre>';
				var_dump($new_dep_array);
				echo '</pre>';
				exit();*/
				$sell_dep_data_array[$k]['dep_data'] = $new_dep_array;
			}
		}
		
		return $sell_dep_data_array;
	}

	public function deal_test_dep_position_data($sell_dep_data_array,$employee_list)
	{
		foreach ($sell_dep_data_array as $k => $v) 
		{
			$dep_child_list_array = array();
			$dep_array = array();
			foreach ($employee_list as $e_k => $e_v) 
			{
				if($v['child_list'])
				{
					$dep_child_list_array = explode(',', $v['child_list']);
				}else
				{
					$dep_child_list_array = array();
				}
				//var_dump($dep_child_list_array);
				if($v['id'] == $e_v['dep_id'] ||  in_array($e_v['dep_id'], $dep_child_list_array) )
				{
					$dep_array[] = $e_v;
				}
			}
			//var_dump($employee_list);exit();
			//var_dump($dep_array);exit();
			$new_dep_array = array();
			$position_name_array = array();
			
			foreach ($dep_array as $_k => $_v) 
			{
				if(in_array($_v['position_id'],$position_name_array))
				{
					$new_dep_array[$_v['position_id']]['position_id'] = $_v['position_id'];
					$new_dep_array[$_v['position_id']]['position_name'] = $_v['position_name'];
					$new_dep_array[$_v['position_id']]['p_num'] = $new_dep_array[$_v['position_id']]['p_num'] + $_v['p_num'];	
				}
				else
				{
					$position_name_array[] = $_v['position_id'];
					$new_dep_array[$_v['position_id']]['position_id'] = $_v['position_id'];
					$new_dep_array[$_v['position_id']]['position_name'] = $_v['position_name'];
					$new_dep_array[$_v['position_id']]['p_num'] = $new_dep_array[$_v['position_name']]['p_num'] + $_v['p_num'];	
				}
			}
			/*echo '<pre>';
			var_dump($new_dep_array);
			echo '</pre>';
			exit();*/
			$sell_dep_data_array[$k]['dep_data'] = $new_dep_array;
		}
		
		return $sell_dep_data_array;
	}

	public function add_dep_position()
    {
    	$position_tbl = M('position_tbl','yj_');
        $input_data = I('post.');
        $dep_id = $input_data['dep_id']?$input_data['dep_id']:1001;
        $position_name = $input_data['position_name']?$input_data['position_name']:'PHP开发';
        $add_user_id = 146;
        $add_data['position_name'] = $position_name;
        $add_data['dep_id'] = $dep_id;
        $add_data['add_user_id'] = $add_user_id;
        $add_data['add_time'] = time();
        $id = $position_tbl->add($add_data);
        if($id)
        {
            $result = array(
                'status'=>1,
                'msg'=>'部门岗位添加成功',
                'data'=>array('add_id'=>$id),
            );
        }
        else
        {
            $result = array(
                'status'=>-1,
                'data'=>array('add_id'=>0),
                'msg'=>'部门岗位添加失败',
            );
        }

        $this->ajaxReturn($result);
    }

    public function update_dep_position()
    {
    	header('Content-type:text/html;charset=utf-8');
    	$input_data = I('post.');
        $id = $input_data['p_id']?$input_data['p_id']:1;

    	$position_tbl = M('position_tbl','yj_');

    	$where['p.id'] = $id;
        $d_join = ' LEFT JOIN department d ON p.dep_id = d.id ';
        $fields = 'p.id as id,p.dep_id,p.position_name,p.status,d.name as dep_name';
        $order = 'p.add_time DESC';
        $detail = $position_tbl->alias('p')->join($d_join)->where($where)->order($order)->field($fields)->select();
        var_dump($detail);exit();

        $dep_id = $input_data['dep_id']?$input_data['dep_id']:1001;
        $position_name = $input_data['position_name']?$input_data['position_name']:'PHP开发';
        $add_user_id = 146;
        $update_data['dep_id'] = $dep_id;
        $update_data['position_name'] = $position_name;
        $update_data['status'] = $input_data['status'];

        $update_result = $position_tbl->where(array('id'=>$id))->save($update_data);
        if($update_result!==false)
        {
            $result = array(
                'status'=>1,
                'data'=>array('add_id'=>$id),
            );
        }
        else
        {
            $result = array(
                'status'=>-1,
                'msg'=>'部门岗位修改失败',
            );
        }

        $this->ajaxReturn($result);

    }

    public function get_dep_position_data_list()
    {
    	header('Content-type:text/html;charset=utf-8');
    	$position_tbl = M('position_tbl','yj_');

    	if($id)
    	{
    		$where['p.id'] = $id;
    	}
    	
    	$where['p.status'] = 1;
        $d_join = ' LEFT JOIN department d ON p.dep_id = d.id ';
        $fields = 'p.id as id,p.dep_id,p.position_name,p.status,d.name as dep_name';
        $order = 'p.add_time DESC';
        $list = $position_tbl->alias('p')->join($d_join)->where($where)->order($order)->field($fields)->select();
        //echo M()->getLastsql();
        var_dump($list);exit();
    }

    public function get_dep_position_by_ajax()
    {
    	$position_tbl = M('position_tbl','yj_');
    	$subQuery = $position_tbl->field('id,dep_id')->table('yj_position_tbl')->where($where)->order('add_time')->buildSql(); 
    	// 利用子查询进行查询 
		//$position_tbl->table($subQuery.' a')->where(array('id'=>array('IN',$subQuery)))->select();
		$position_tbl->where(array('id'=>array('IN',$subQuery)))->select();
    	echo M()->getLastsql();
        $input_data = I('post.');
        $dep_id = $input_data['dep_id']?$input_data['dep_id']:1001;
        $position_name_list = $position_tbl->where(array('dep_id'=>$dep_id))->field('id,position_name,dep_id')->select();
        if($position_name_list)
        {
            $result = array(
                'status'=>1,
                'msg'=>'获取部门岗位成功',
                'data'=>array('list'=>$position_name_list),
            );
        }
        else
        {
            $result = array(
                'status'=>-1,
                'msg'=>'获取部门岗位失败',
                'data'=>array('list'=>array()),
            );
        }

        $this->ajaxReturn($result);
    }

	public function calculation_probability()
	{
		//查询各个码出现的次数统计
		//$chance_model= M('chance_log',null);
		//$cai_piao_model= M('cai_pai','cp_');
		$chance_model= M('cai_piao','cp_');
		$total = $chance_model->count('id');
		$one_array = $chance_model->order('one')->getField('one',true);
		$two_array = $chance_model->order('two')->getField('two',true);
		$three_array = $chance_model->order('three')->getField('three',true);
		$four_array = $chance_model->order('four')->getField('four',true);
		$five_array = $chance_model->order('five')->getField('five',true);
		$six_array = $chance_model->order('six')->getField('six',true);
		$seven_array = $chance_model->order('seven')->getField('seven',true);
		//var_dump($one_array);
		/*
			$one_array = $chance_model->order('no_one')->getField('no_one',true);
		$two_array = $chance_model->order('no_two')->getField('no_two',true);
		$three_array = $chance_model->order('no_three')->getField('no_three',true);
		$four_array = $chance_model->order('no_four')->getField('no_four',true);
		$five_array = $chance_model->order('no_five')->getField('no_five',true);
		$six_array = $chance_model->order('no_six')->getField('no_six',true);
		$seven_array = $chance_model->order('no_seven')->getField('no_seven',true);
		*/
		$one_values = $this->get_jilv($one_array,$total);
		//var_dump($one_values);
		$two_values = $this->get_jilv($two_array,$total);
		$three_values = $this->get_jilv($three_array,$total);
		$four_values = $this->get_jilv($four_array,$total);
		$five_values = $this->get_jilv($five_array,$total);
		$six_values = $this->get_jilv($six_array,$total);
		$seven_values = $this->get_jilv($seven_array,$total);
		//var_dump($one_values);
		$list = array(1=>$one_values,2=>$two_values,3=>$three_values,4=>$four_values,5=>$five_values,6=>$six_values,7=>$seven_values);
		/*$this->assign('one',$one_values);
		$this->assign('two',$two_values);
		$this->assign('three',$three_values);
		$this->assign('four',$four_values);
		$this->assign('five',$five_values);
		$this->assign('six',$six_values);
		$this->assign('seven',$seven_values);*/
		$cai_piao_info = $chance_model->field('opencode')->order('expect desc')->limit(10)->select();
		echo '<br>';
		print_r($cai_piao_info);
		$ceil_total = ceil($total/10);
		$this->assign('list',$list);
		$this->assign('total',$total);
		$this->assign('ceil_total',$ceil_total);
		//$this->assign('c_list',$cai_piao_info);
		//var_dump($list);
		//var_dump($cai_piao_info);
		$this->display('test/sum_num');
	}

	public function get_jilv($array=array(),$total = 0,$num=10)
	{
		$result = array(0=>array('number'=>0,'p_lv'=>0),1=>array('number'=>1,'p_lv'=>0),2=>array('number'=>2,'p_lv'=>0),3=>array('number'=>3,'p_lv'=>0),4=>array('number'=>4,'p_lv'=>0),5=>array('number'=>5,'p_lv'=>0),6=>array('number'=>6,'p_lv'=>0),7=>array('number'=>7,'p_lv'=>0),8=>array('number'=>8,'p_lv'=>0),9=>array('number'=>9,'p_lv'=>0));
		//$p_lv = sprintf("%.4f",1/10);
		$p_lv = 1/10;
		//echo '<br>';
		//$tatol = 5;
		//$chance_model= M('chance_log',null);
		//$chance_model= M('cai_piao','cp_');
		//$array = $chance_model->order('no_one')->getField('no_one',true);
		$array_values = array_count_values($array);
		$array_key = array_keys($array_values);
		//var_dump($array_key);
		/*echo '<br>';
		var_dump($array_values);
		echo '<br>';*/
		foreach ($result as $k => $v) 
		{
			if(in_array($k, $array_key))
			{
				$result[$k]['p_lv'] = sprintf("%.4f",$array_values[$k]/$total)*100;
				$result[$k]['cha'] = (sprintf("%.4f",($array_values[$k]/$total)-$p_lv))*100;
				$result[$k]['c'] = $array_values[$k];
			}
			else
			{
				$result[$k]['cha'] = ($result[$k]['p_lv']-$p_lv)*100;
				$result[$k]['c'] = 0;
			}
		}
		//var_dump($result);
		return $result;

	}

	public function add_log()
	{
		$one = mt_rand(0,9);
		$two = mt_rand(0,9);
		$three = mt_rand(0,9);
		$four = mt_rand(0,9);
		$five = mt_rand(0,9);
		$six = mt_rand(0,9);
		$seven = mt_rand(0,9);
		$number = intval($one.$two.$three.$four.$five.$six.$seven);
		//var_dump($number);
		$chance_model= M('chance_log',null);
		$add['no_one'] = $one;
		$add['no_two'] = $two;
		$add['no_three'] = $three;
		$add['no_four'] = $four;
		$add['no_five'] = $five;
		$add['no_six'] = $six;
		$add['no_seven'] = $seven;
		$add['add_time'] = time();
		$add['number'] = $number;
		$add['stage'] = $stage?$stage:time();
		$chance_model->add($add);
	}

	public function test_add_log()
	{
		/*for ($i=0; $i < 100; $i++) 
		{ 
			$this->add_log();
		}*/
		$n124 = 0956232;
		$n123 = 9762189;
	}

	public function ip_to_number()
	{
		//处理出现负数
		$ip = '192.168.101.100';
		$ip_long = sprintf('%u',ip2long($ip));
		echo $ip_long.PHP_EOL;  // 3232261476 
		echo long2ip($ip_long); // 192.168.101.100
	}

	public function cai_piao_by_ajax()
	{
		//查询各个码出现的次数统计
		//$chance_model= M('chance_log',null);
		//$cai_piao_model= M('cai_pai','cp_');
		$chance_model= M('cai_piao','cp_');
		//$total = $chance_model->count('id');
		$one_array = $chance_model->order('one')->getField('one',true);
		$two_array = $chance_model->order('two')->getField('two',true);
		$three_array = $chance_model->order('three')->getField('three',true);
		$four_array = $chance_model->order('four')->getField('four',true);
		$five_array = $chance_model->order('five')->getField('five',true);
		$six_array = $chance_model->order('six')->getField('six',true);
		$seven_array = $chance_model->order('seven')->getField('seven',true);
		
		$one_values = array_count_values($one_array);
		//var_dump($one_values);
		$two_values = array_count_values($two_array);
		$three_values = array_count_values($three_array);
		$four_values = array_count_values($four_array);
		$five_values = array_count_values($five_array);
		$six_values = array_count_values($six_array);
		$seven_values = array_count_values($seven_array);
		//var_dump($one_values);
		$list = array(1=>$one_values,2=>$two_values,3=>$three_values,4=>$four_values,5=>$five_values,6=>$six_values,7=>$seven_values);
		$return_data = array('status'=>1,'data'=>$list);
		return $this->ajaxReturn($return_data);
		
	}

	public function get_data_zishu()
	{
		$zishu = array(1,3,5,7,9);
		$heshu = array(0,2,4,6,8);
		$num = 20;
		$chance_model= M('cai_piao','cp_');
		$one_array = $chance_model->order('expect desc')->limit($num)->getField('one',true);
		//var_dump($one_array);
		$two_array = $chance_model->order('expect desc')->limit($num)->getField('two',true);
		$three_array = $chance_model->order('expect desc')->limit($num)->getField('three',true);
		$four_array = $chance_model->order('expect desc')->limit($num)->getField('four',true);
		$five_array = $chance_model->order('expect desc')->limit($num)->getField('five',true);
		$six_array = $chance_model->order('expect desc')->limit($num)->getField('six',true);
		$seven_array = $chance_model->order('expect desc')->limit($num)->getField('seven',true);

		$list = array(1=>$one_array,2=>$two_array,3=>$three_array,4=>$four_array,5=>$five_array,6=>$six_array,7=>$seven_array);

		for ($i=1; $i <=7 ; $i++) 
		{ 
			$result_array = $this->is_zishu($list[$i]);
			$return_list[$i] = $result_array['res'];
			$zh_c= array('z'=>$result_array['z'],'h'=>$result_array['h']);
			$result_array = array();
		}
		$c_list = array();
		for ($i=$num; $i >=1 ; $i--) 
		{ 
			array_push($c_list,$i);
		}
		//var_dump($c_list);
		$return_data = array('status'=>1,'data'=>$return_list,'c_list'=>$c_list,'zh_c'=>$zh_c);
		return $this->ajaxReturn($return_data);

	}

	public function is_zishu($array=array())
	{
		$zishu = array(1,3,5,7,9);
		$z = 0;
		$h = 0;
		$result = array();
		foreach ($array as $k => $v) 
		{
			if(in_array($v, $zishu))
			{
				//$result[$k]['z_num'] = $v;
				//$result['z_num'] = $v + $result['z_num'];
				array_push($result,1);
				$z++;
			}
			else
			{
				//$result['h_num'] = $v + $result['h_num'];
				array_push($result,-1);
				$h++;
			}
		}

		return array('res'=>$result,'z'=>$z,'h'=>$h);
	}

	public function update_user_detail($user_id=2230,$update_data_key='',$update_data_val=array())
	{
		$user_model = M('users',null);
		$user_detail = $user_model->where(array('id'=>$user_id))->getField('detail');
		if($user_detail)
		{
			$detail_info = unserialize($user_detail);
			//var_dump($detail_info);
			$detail_info[$update_data_key] = $update_data_val;
			$update_array = $detail_info;
		}
		else
		{
			$update_array = array($update_data_key=>$update_data_val);
		}
		$update_array_str = serialize($update_array);
		//var_dump($update_array_str);exit();
		$update_result = $user_model->where(array('id'=>$user_id))->setField('detail',$update_array_str);
		if($update_result!==false)
        {
            return true;
        }
        else
        {
            return false;
        }

	}

	public function test_update_detail()
	{
		$key = 'no_1';
		$key = 'login_info';
		$arr = array('name'=>'limin','age'=>25);
		$arr = array('login_time'=>time(),'ip'=>get_client_ip());
		$result = $this->update_user_detail(2230,$key,$arr);
		var_dump($result);
	}

	public function get_area_data()
    {
    	header('Content-type:text/html;charset=utf-8');
        $p_id =I('p_id');
        $p_id = $p_id?$p_id:440000;
        $pro_model =M('province','hat_');
        $sql = " SELECT p.provinceID,p.province,c.cityID,.c.city,c.father AS city_parent,a.areaID,a.area,a.father AS area_parent FROM `hat_province` p LEFT JOIN `hat_city` c ON c.father = p.provinceID LEFT JOIN `hat_area` a ON a.father = c.cityID WHERE provinceID = $p_id ";
        $list = $pro_model->query($sql);
        $list = empty($list)?array():$list;
        echo '<pre>';
        //var_dump($list);
        echo '</pre>';
        $province_list = $pro_model->field('id,provinceID,province')->select();
        $province_list = empty($province_list)?array():$province_list;
        echo '<pre>';
        var_dump($province_list);
        echo '</pre>';

        $result['status'] = 1;
        $result['msg'] = '查询地区数据成功';
        $result['data'] = array('list'=>$list,'province_list'=>$province_list);
        $this->ajaxReturn($result);
    }


    public function get_dep_zhiwu_data($year=2017,$type=1)
    {
    	header('Content-type:text/html;charset=utf-8');
    	$begin_time = $year.'-01-01 00:00:00';
    	$end_time = $year.'-12-31 23:59:59';

    	$dep_model = M('department',null);
    	$sell_dep_array = array(102,101,133,170,267); //销售部门
		$sell_where['id'] = array('in',$sell_dep_array);
		
		$sell_dep_data = $dep_model->where($sell_where)->field('id,name,code,child_list')->select();
		$no_sell_where_array = array();
		//var_dump($sell_dep_data);echo '<br>';echo '<br>';
		foreach ($sell_dep_data as $sell_k => $sell_v) 
		{
			$arr = array();
			$no_sell_where_array[] = $sell_v['id'];
			if($sell_v['child_list'])
			{
				$arr = explode(',', $sell_v['child_list']);
				$no_sell_where_array = array_filter(array_merge($no_sell_where_array,$arr));
				//var_dump($no_sell_where_array);echo '<br>';echo '<br>';exit();
			}
			else
			{
				$no_sell_where_array = array_push($no_sell_where_array,$sell_v['id']);
			}
		}
		//var_dump($no_sell_where_array);echo '<br>';echo '<br>';exit();
		$no_sell_where['id'] = array('not in',$no_sell_where_array); //非销售部门
		$no_sell_dep_data = $dep_model->where($no_sell_where)->field('id,name,code,child_list')->select();
		//var_dump($no_sell_dep_data);echo '<br>';echo '<br>';exit();

    	$employee_model = M('employee',null);
    	/*if($type<=4)
    	{
    		$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
    		if($type ==2 || $type== 4 )
    		{
				$where = $where." AND e.introduction !='' ";
    		}
    		//1.入职 XX年入职时间
    		$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
    		//2.入职内荐  1的基础上加个推荐人
    		$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' AND e.introduction !='' ";
    		//3.在职 XX入职到现在还在公司
    		$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
    		//4.入职内荐  3基础上 加个推荐人
			$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' AND e.introduction !='' ";
    	}
    	elseif($type>4)
    	{
    		//离职
    		$where = " WHERE e.status = 0 AND e.quit_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
    	}*/
    	if($type<=4)
        {
            //非离职(入职,在职)
            if($type == 1) 
            {
            	//每月入职
            	$where = " WHERE e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
            }
            elseif ($type == 2) 
            {
            	//每月内荐入职
            	$where = " WHERE e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' AND e.introduction !='' ";
            }
            elseif ($type == 3) 
            {
            	//在职
            	$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
            }
            elseif ($type == 4) 
            {
            	//在职内荐
            	$where = " WHERE e.status = 1 AND e.entry_date >= '$begin_time' AND e.entry_date <= '$end_time' AND e.introduction !='' ";
            }
        }
        elseif($type>4)
        {
            //离职
            $where = " WHERE e.status = 0 AND e.quit_date >= '$begin_time' AND e.entry_date <= '$end_time' ";
            if($type == 6) //当归入职且离职
            {
                $where = $where." AND DATE_FORMAT(e.entry_date, '%Y%m') = DATE_FORMAT(e.quit_date, '%Y%m') ";
            }
            elseif($type == 7) //当月入职且离职 属于内荐
            {
                $where = $where." AND DATE_FORMAT(e.entry_date, '%Y%m') = DATE_FORMAT(e.quit_date, '%Y%m') AND e.introduction !='' ";
            }
        }
    	// todo  公司归属
    	/*if(strlen($where)>0)
    	{
    		$where = $where.' AND ';
    	}*/

    	$sql = " SELECT COUNT('e.id') AS num,e.dep_id,DATE_FORMAT(e.entry_date, '%m') AS y_month,d.name FROM `employee` e LEFT JOIN department d ON d.`id` = e.`dep_id` $where GROUP BY y_month,dep_id ORDER BY y_month ";
    	echo $sql;
    	$employee_list = $employee_model->query($sql);
    	echo '<pre>';
        //var_dump($employee_list);
        echo '</pre>';

        $sell_dep_result = $this->deal_dep_position_list($sell_dep_data,$employee_list);
        $no_sell_dep_result = $this->deal_dep_position_list($no_sell_dep_data,$employee_list);
        echo '<pre>';
        var_dump(array_merge($no_sell_dep_result,$sell_dep_result));
        echo '</pre>';

    }

    public function deal_dep_position_list($sell_dep_data,$employee_array)
    {
    	foreach ($sell_dep_data as $s_k => $s_v) 
        {
        	if($s_v['child_list'])
			{
				$sell_str_array = explode(',', $s_v['child_list']);
			}
			else
			{
				$sell_str_array = array();
			}

        	for ($i=1; $i <=12 ; $i++)  //月份循环 1-12月
        	{ 
        		foreach ($employee_array as $k => $v) 
				{
					/*echo $v['dep_id'];echo '<br/>';
					echo $s_v['id'];echo '<br/>';
					echo $v['y_month'];echo '<br/>';*/
					//$v['dep_id'] = 189;
					if(($v['dep_id']==$s_v['id'] || (in_array($v['dep_id'], $sell_str_array) && !empty($sell_str_array))) && $v['y_month'] == $i )
					{
						/*echo $v['dep_id'];echo '<br/>';
						echo $s_v['id'];echo '<br/>';
						echo $v['y_month'];echo '<br/>';*/
						//var_dump($sell_str_array);echo '<br/>';
						$sell_dep_data[$s_k][$i] += $v['num'];
						/*echo $sell_dep_data[$s_k][$i];
						echo '<br/>';
						echo '<br/>';*/
					}/*else
					{
						echo $v['dep_id'].'--'.$s_v['id'].'--'.$v['y_month'];echo '<br/>';
					}*/
				}
				
				$sell_dep_data[$s_k][$i] = $sell_dep_data[$s_k][$i]?$sell_dep_data[$s_k][$i]:0;
				
        	}
        	$sell_str_array = array();
        	//exit();
        }
        /*echo '<pre>';
        var_dump($sell_dep_data);
        echo '</pre>';*/
        return $sell_dep_data;
        
    }

    public function test_val()
    {
    	$str = '1002';
    	$arr = explode(',', $str);
    	var_dump($arr);

    }

    public function change_employee_introduction()
    {
    	header('Content-type:text/html;charset=utf-8');
    	$emp_model = M('employee',null);
    	$sql = " SELECT id,name FROM `employee` 
WHERE NAME IN (SELECT introduction FROM `employee` WHERE introduction !='' GROUP BY introduction)  ";
    	//$list = $emp_model->query($sql);
    	//S('list1',$list,60*60*60);
    	$list = S('list1');
    	$list = array_as_key($list,'name');
    	$list_key = array_keys($list);
    	//var_dump($list);exit;
    	$sql2 = " SELECT * FROM `employee` WHERE introduction !='' ";
    	$list2 = $emp_model->query($sql2);
    	//var_dump($list2);
    	foreach ($list2 as $key => $value) 
    	{
    		if($value['introduction'] && in_array($value['introduction'], $list_key))
    		{
    			$update = array();
				echo $value['introduction'];echo '<br/>';
				$update['introduction'] = $list[$value['introduction']]['id'];
				$emp_model->where(array('id'=>$value['id']))->save($update);
				echo M()->getLastsql();echo '<br/>';//exit();
    		}
    	}
    }


}