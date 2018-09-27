<?php 
namespace Test2\Controller;
use Think\Controller;

class ActivityController extends Controller{
	
    protected $activity_model;

	public function __construct()
	{
		parent::__construct();
		$this->activity_model = D('Activity');
	}

	public function add_activity()
	{
		$data = I('post.');
        var_dump($data);
        $data = $this->activity_model->create();
        echo '入口<br/>';
        var_dump($data);
        $return_data = array(
        	'status' => 0, 
        	'msg' => '优惠活动创建失败！', 
    	);
        if (!$this->activity_model->create()) // POST提交生效
        {     
            //exit($this->activity_model->getError());
            $return_data['msg'] = $this->activity_model->getError();
        }
        else
        {     // 验证通过 可以进行其他数据操作
              $add_id = $this->activity_model->add($data);
              if($add_id)
              {
              	 $return_data['status'] = 1;
              	 $return_data['msg'] = '优惠活动创建成功！';
              }
        }
        return $this->ajaxReturn($return_data);
	}

	public function update_activity()
	{
		$data = I('post.');
        var_dump($data);
        $data = $this->activity_model->create();
        echo '入口<br/>';
        var_dump($data);
        $return_data = array(
        	'status' => 0, 
        	'msg' => '优惠活动创建更新失败！', 
    	);
        if (!$this->activity_model->create()) // POST提交生效
        {     
            //exit($this->activity_model->getError());
            $return_data['msg'] = $this->activity_model->getError();
        }
        else
        {     // 验证通过 可以进行其他数据操作
              $add_id = $this->activity_model->add($data);
              if($add_id)
              {
              	 $return_data['status'] = 1;
              	 $return_data['msg'] = '优惠活动更新成功！';
              }
        }
        return $this->ajaxReturn($return_data);
	}

	/*public function get_activity_list()
	{
		$input = I('get.');
		$where['begin_time'] = array('EGT',$input['begin_time']);
		$where['end_time'] = array('ELT',$input['end_time']);
		$where['activity_name'] = array('LIKE',"%$input['activity_name']%");
		$where['activity_type'] = $input['activity_type'];

		$list = $this->activity_model->get_activity_list($where);

		var_dump($list);
	}*/

	public function check_activity_by_user_id_or_goods_id()
	{
		//$input = I('post.');
		/*$input = I('get.');
		if(!$input)
		{
			$return_data['msg'] = '参数格式错误或非法数据！';
			return $return_data;
		}*/
		$activity_id = $input['activity_id'];
		$activity_id = 3;
		//$user_id     = $input['user_id'];
		$user_lv     = $input['user_lv'];
		$user_lv     = 1;
		$goods_id    = $input['goods_id'];
		$goods_id    = 3;

		$return_data = array(
        	'status' => 0, 
        	'msg' => '商品优惠活动Id:'.$activity_id.'不存在！', 
    	);
		
		$where['id'] = $activity_id;
		$activity_data = $this->activity_model->get_activity_data($where);
		var_dump($activity_data);
		if($activity_data)
		{
			$return_data['status'] = 1;
			$return_data['msg'] = '等级和商品符合本次商品优惠活动！';
			$return_data['data'] = array();
			//获取优惠活动商品表数据
			$activities_goods_list_model = M('preferential_activities_goods_list','yj_');
			$activity_goods_list_id = $activity_data['activity_goods_list_id'];
			$activities_goods_list_data = $activities_goods_list_model->where("id=$activity_goods_list_id")->find();
			var_dump($activities_goods_list_data);
			$goods_list = explode(',',$activities_goods_list_data['goods_list']);
			if(in_array($goods_id, $goods_list))
			{
				echo $activities_goods_list_data['goods_list'];
			}
			else
			{
				echo $goods_id;
				echo '<br>';
				echo $activities_goods_list_data['goods_list'];
			}

			//获取参与本次活动的商品列表
			if($goods_id && (!in_array($goods_id, $activity_data['activity_goods_list_id']) || !in_array($goods_id, $activity_data['add_goods_id']) ))
			{
				$return_data['msg'] = '该商品不参与本次商品优惠活动！';
				//根据规则计算商品优惠
			}
			//获取参与本次活动的会员等级
			if($user_lv && !in_array($user_lv, $activity_data['level']))
			{
				$return_data['msg'] = '该客户不符合本次优惠活动的会员等级！';
				//根据规则计算客户等级优惠
			}
		}

		return $this->ajaxReturn($return_data);
	}

	protected function get_activity_discount($activity_id,$user_lv,$money=0)
	{
		$discount  =C('LEVEL_DISCOUNT');
		$return_data = array(
			'status' => 0 , 
			'msg' => '无ID编号:'.$activity_id.'的优惠活动！' , 
		);
		$where['activity_id'] = $activity_id;
		$activity_data = $this->activity_model->get_activity_data($where);
		if (!$activity_data) 
		{
			return $return_data;
		}
		if($activity_data['is_add'])
		{
			//优惠叠加
			$discount_money = $discount[$member_lv] * $money;
			$discount_money = $this->get_money_by_activity_rules($activity_data['activity_rules_id'],$activity_data['activity_rules_mode'],$discount_money);
		}
		else
		{
			$discount_money = $this->get_money_by_activity_rules($activity_data['activity_rules_id'],$activity_data['activity_rules_mode'],$money);
		}
		$return_data['status'] = 1;
        $return_data['msg'] = '获取该客户本次优惠活动的优惠价格成功！';
        $return_data['money'] = $discount_money;

		return $return_data;
	}

	protected function get_money_by_activity_rules($rules_id,$activity_rules_mode,$money=0)
	{
		$rules = C('ACTIVITY_RULES');
		if($rules_id == $rules[1])
		{
			$money = $activity_rules_mode * $money;
		}
		elseif($rules_id == $rules[2]) 
		{
			
		}

		return $money;
		
	}

	public function get_product_by_id()
	{
		if(IS_POST)
		{
			$post_data = I('post.');
			var_dump($post_data);
		}
		//$post_data = I('post.');
		header('Content-type:text/html;charset=utf-8');
     	$product_list = $this->get_product_class_goods_by_id(65);
        /*echo '<pre>';
        var_dump($product_list);
        echo '</pre>';*/
        $this->assign('product_class_list',$product_class_list);
        $this->assign('product_list',$product_list);
        $this->display('test/send_data');
	}

	public function get_product_by_id2()
	{
		if(IS_AJAX)
		{
			$type_id = I('type_id');
			$where['first_class'] = $type_id;
			$product_model = M('product',NULL,'DB_CONFIG3');  
			$product_list = $product_model->where($where)->field('id,code,name,first_class,second_class')->select();
			if($product_list)
			{
				$result = array(
					'status'=>1,
					'list'=>$product_list,
				);
			}
			else
			{
				$result = array(
					'status'=>0,
					'list'=>array(),
				);
			}
			$this->ajaxReturn($result);
		}
		
	}

	public function get_product_by_checked_id()
	{
        $product_list = $this->get_product_class_goods_by_id(53);

		$activity_id = 3;
		$where['id'] = $activity_id;
		$activity_data = $this->activity_model->get_activity_data($where);
		//var_dump($activity_data);
		if($activity_data)
		{
			$activities_goods_list_model = M('preferential_activities_goods_list','yj_');
			$activity_goods_list_id = $activity_data['activity_goods_list_id'];
			$activities_goods_list_data = $activities_goods_list_model->where("id=$activity_goods_list_id")->find();
			$goods_list = explode(',',$activities_goods_list_data['goods_list']);
			foreach ($product_list as $k => $v) 
			{
				if(in_array($v['id'],$goods_list)) 
				{
					$product_list[$k]['checked'] = 'checked="checked"';
				}
			}
		}
		echo '<pre>';
		var_dump($product_list);
		echo '</pre>';
	}

	public function get_product_class_goods_by_id($id='')
	{
		$where['first_class'] = $id;
		$product_class_model = M('product_class',NULL,'DB_CONFIG3'); //产品分类表
        $opt['where'] = array('flag' =>1, 'parent' =>0);
        $product_class_list = $product_class_model->where($opt['where'])->select();
        //var_dump($product_class_list);
		$product_model = M('product',NULL,'DB_CONFIG3');             //产品详细表
        $product_list = $product_model->where($where)->field('id,code,name,first_class,second_class')->select();
        return $product_list?$product_list:array();
	}

	public function add_activity_goods_list()
	{
		$activity_goods_list_model = M('preferential_activities_goods_list','yj_');
		if(IS_POST)
		{
			$post_data = I('post.');
			var_dump($post_data);
			$add_data = array(
				'goods_type_id'=>$post_data['product_type_id'],
				'goods_list'=>implode(',',$post_data['product_lists']),
				'add_time'=>time(),
			);
			var_dump($add_data);
			$activity_goods_list_id = $activity_goods_list_model->data($add_data)->add();
			var_dump($activity_goods_list_model->getLastsql());
		}
	}

	public function info()
	{
		//产品类别

		$opt['where'] = array('a.flag' =>1, 'a.parent' =>0);

		$clist = D('Category')->get_list_category($opt);
	}

	public function test()
	{
		header('Content-type:text/html;charset=utf-8');
		/*$res = $this->check_activity_by_user_id_or_goods_id();
		var_dump($res);*/
		//$this->get_product_by_checked_id();
		//print_r(str_split("Shanghai",strlen('Shanghai')));
		$this->display('test/send_data');
	}


    public function test_checkbox()
    {
    	$input_data = I('post.');
    	$product_list = $input_data['product_list']; //产品列表字符串 例 116_53_
    	$json_product_str = json_encode($product_list);  //产品列表字符串保存                    
    	$product_class = implode(',',$input_data['product_class']); //大类ID
    	foreach ($product_list as $v) 
    	{
    		$product_list_goods_array = explode('_', $v);
    		$first_class = 1;
    		$product_list_goods_array = array_filter($product_list_goods_array);
			$num = count($product_list_goods_array);
			//var_dump($num);
    		//$product_goods_list .= $product_list_goods_array[0].',';
			//$class = array();
			/*var_dump($product_list_goods_array);
			echo '/<br>';*/
    		for ($i=1; $i <= $num; $i++) 
    		{ 
    			$k = $num - $i;
    			//echo $product_list_goods_array[$k].'<br>';
    			/*echo $product_list_goods_array[$k];
    			echo '+<br>';
    			echo $class[$i];
    			echo '-<br>';*/
    			$strpos = strpos($class[$i],$product_list_goods_array[$k]);
    			if($strpos===false)
    			{
    				$class[$i].= $product_list_goods_array[$k].',';
    			}
    			/*var_dump($class);
    			echo '*<br>';*/
    		}
    	} 
    	var_dump($input_data);
    	//var_dump($product_list);
    	//var_dump($product_class);
    	//var_dump($product_goods_list);
    	var_dump($class);
    	$product_model = M('product',NULL,'DB_CONFIG3'); //产品列表
    	$product_where['first_class'] = array('IN',substr($class[1],0,strlen($class[1])-1));
    	$product_where['second_class'] = array('IN',substr($class[2],0,strlen($class[2])-1));
    	$product_goods_list = $product_model->where($product_where)->getField('id',true);
    	//echo $product_model->getLastsql();
    	var_dump(implode(',', $product_goods_list));
    	$this->display('test/index');
    }

	public function get_product_class()
	{
		//header('Content-type:text/html;charset=utf-8');
		$product_class_model = M('product_class',NULL,'DB_CONFIG3'); //产品分类表
        $opt['where'] = array('flag' =>1);
        $product_class_list = $product_class_model->where($opt['where'])->field('id,name,parent')->order('parent,id')->select();
        $product_class_type_list = $product_class_model->where($opt['where'])->group('parent')->order('parent,id')->getField('parent',true);
        //echo '<pre>';
        //var_dump($product_class_list);
        //var_dump($product_class_type_list);
        //echo '</pre>';
        foreach ($product_class_type_list as $v) 
        {
        	foreach ($product_class_list as $p_k => $p_v) 
        	{
        		if($v == $p_v['parent'])
        		{
        			$class_list[] = $p_v;
        		}
        	}
        	$class_type_array[$v] = $class_list;
        	$class_list = array();
        }
        /*echo '<pre>';
        var_dump($class_type_array);
        echo '</pre>';*/
        //$this->display('test/send_data');
        if($class_type_array)
		{
			$result = array(
				'status'=>1,
				'list'=>$product_class_list,
			);
		}
		else
		{
			$result = array(
				'status'=>0,
				'list'=>array(),
			);
		}
		$this->ajaxReturn($result);
	}

	public function get_activity_money_by_goods_id_and_customer_id($goods_id,$customer_id,$is_json=0)
	{

		header('Content-type:text/html;charset=utf-8');

		if(!is_array($goods_id))
		{
			$goods_id = "$goods_id";
		}
		echo 'goods_id: ';
		var_dump($goods_id);
		echo '<br>';
		echo '<br>';
		$data = array();
		$return_data = array(
			'result' => -1,
			'data' => $data,
		);
		$activity_model = M('preferential_activities','yj_');
		$activity_goods_list_model = M('preferential_activities_goods_list','yj_');
		// TODO 查询会员等级
		$member_model = M('member_level_and_point_tbl','yj_','DB_CONFIG3'); //会员积分表
		$member_data  = $member_model->where(array('customer_id'=>$customer_id))->getField('level');
		$user_lv = $member_data['level']?$member_data['level']:1;
		//$user_lv = 2;
		// 会员等级优惠
		$discount = C('LEVEL_DISCOUNT');
		$member_lv_discount = $discount[$user_lv];
		echo 'member_lv_discount: ';
		var_dump($member_lv_discount);
		echo '<br>';
		echo '<br>';

		// TODO  商品是否属于多个优惠活动？
		$act_id_array = $activity_goods_list_model->where(array('goods_id'=>array('IN',$goods_id)))->getField('goods_id,act_id',true);
		$act_id_array = array_unique($act_id_array);
		echo 'act_id_array: ';
		var_dump($act_id_array);
		echo '<br>';
		echo '<br>';

		//$act_list_array = $activity_model->where(array('id'=>array('IN',$act_id_array)))->field('id,begin_time,end_time,level,is_add,activity_rules_id,activity_rules_mode')->select();
		$product_model = M('product',NULL,'DB_CONFIG3'); //商品类

		//商品销售价格
		$sell_price_array = $product_model->where(array('id'=>array('IN',$goods_id)))->getField('id,sell_price',true);
		if(!$sell_price_array)
		{
			$return_data['message'] = '暂无该商品记录！';
			if($is_json)
			{
				return $this->ajaxReturn($return_data);
			}
			else
			{
				return $return_data;
			}
		}
		echo 'sell_price_array: ';
		var_dump($sell_price_array);
		echo '<br>';
		echo '<br>';

		if(!$act_id_array)
		{
			// 非优惠活动商品
		    $return_data['result']  = 1;
		    $return_data['message'] = '查询会员'.$customer_id.'商品优惠售价成功';
		    foreach ($sell_price_array as $k => $v) 
		    {
		    	$activity_data['goods_id'] = $k;
				$activity_data['sell_price'] = $v*$member_lv_discount;
				$data[$k] = $activity_data;
				$activity_data = array();
		    }
		    $return_data['data'] = $data;
		    echo '<br>';
			echo $k.' 非优惠活动商品';
			echo '<br>';
		    if($is_json)
			{
				return $this->ajaxReturn($return_data);
			}
			else
			{
				return $return_data;
			}
		}
		else
		{
			//优惠活动 查询
		    $act_list_array = $activity_model->where(array('id'=>array('IN',$act_id_array)))->getField('id,begin_time,end_time,level,is_add,activity_rules_id,activity_rules_mode');
		    echo 'act_list_array: ';
		    var_dump($act_list_array);
			echo '<br>';
			echo '<br>';
			//时间判断
			$now_time = time();
			foreach ($act_id_array as $k => $v) 
			{
				$begin_time = $act_list_array[$v]['begin_time'];
				$end_time   = $act_list_array[$v]['end_time'];
				$activity_data['goods_id'] = $k;
				$activity_data['sell_price'] = $sell_price_array[$k];
				$data[$k] = $activity_data;
				$activity_data = array();

				if( $now_time < $begin_time || $now_time > $end_time )
				{
					//时间不在优惠时间段范围内
					//$activity_data['goods_id'] = $k;
					//$activity_data['sell_price'] = $sell_price_array[$k]*$member_lv_discount;
					$data[$k]['sell_price'] = round($sell_price_array[$k],2);
					$data[] = $activity_data;
					$activity_data = array();
					echo '<br>';
					echo $k.' 时间不在优惠时间段范围内';
					echo '<br>';					
				}
				else
				{
					//适用会员等级数组
					$member_lv_array = explode(',',$act_list_array[$v]['level']);
					
					//会员等级是否适用优惠活动
					if(in_array($user_lv, $member_lv_array))
					{
						//优惠是否叠加
						if($act_list_array[$v]['is_add'])  
						{
							//TODO 满减判断
							$activity_data['goods_id'] = $k;
							$data[$k]['sell_price'] = round($sell_price_array[$k]*$member_lv_discount*$act_list_array[$v]['activity_rules_mode']/10,2);
							//echo $sell_price_array[$k].'*'.$member_lv_discount.'*'.$act_list_array[$v]['activity_rules_mode']/10;
						}
						else
						{
							//$activity_data['goods_id'] = $k;
							//$activity_data['sell_price'] = $sell_price_array[$k]*$act_list_array[$v]['activity_rules_mode'];
							$data[$k]['sell_price'] = round($sell_price_array[$k]*$member_lv_discount,2);
							/*$data[] = $activity_data;
							$activity_data = array();*/
						}

					}
					else
					{
						//会员等级 不适用优惠活动
						//$activity_data['goods_id'] = $k;
						//$activity_data['sell_price'] = $sell_price_array[$k];
						$data[$k]['sell_price'] = round($sell_price_array[$k],2);
						$data[] = $activity_data;
						$activity_data = array();
						echo '<br>';
						echo $k.' 会员等级 不适用优惠活动';
						echo '<br>';	
					}	
				}
			}
			$return_data['result']  = 1;
		    $return_data['message'] = '查询会员优惠商品售价成功！';
		    $return_data['data'] = $data;
		}

		if($is_json)
		{
			return $this->ajaxReturn($return_data);
		}
		else
		{
			return $return_data;
		}
	
	}

	public function test_act_money()
	{

		$goods_id = array(106,107);
		//$goods_id = array(1012,1078); //无商品
		//$goods_id = 107;
		if(is_array($goods_id))
		{
			$goods_str = implode(',', $goods_id);
		}else
		{
			$goods_str = $goods_id;
		}
		
		//$goods_id = array(497,500);
		//$goods_id = 497;
		//$customer_id = 1000;
		//$customer_id = 4605; //lv5
		$customer_id = 6364; //lv4
		//$customer_id = 2983; //lv2
		echo '客户id: '.$customer_id.' 商品id: '.$goods_str.'<br/>';
		$res = $this->get_activity_money_by_goods_id_and_customer_id($goods_id,$customer_id);
		var_dump($res);
	}

	public function test_submit()
	{
		header('Content-type:text/html;charset=utf-8');
		
		/*$sql = " select COLUMN_NAME from INFORMATION_SCHEMA.Columns where table_name='employee' and table_schema='test' ";
		$employee_change_log_tbl = M('employee_info_log','crm_');
		$res = $employee_change_log_tbl->query($sql);
		$arr = array_column($res, 'column_name');
		echo '<pre>';
		var_dump($arr);
		echo '</pre>';*/
		
		$employee_csv_tbl = M('employee_csv',NULL);
		$employee_tbl = M('employee',NULL);
		$e_list = $employee_tbl->where(array('id'=>180))->find();

		$company_name = C('COMPANY_NAME');
		$add_id = $employee_csv_tbl->add($e_list);
		var_dump($add_id);
		exit();

		if(IS_POST)
		{
			$input_data = I('post.');
			$type = $input_data['type'];
			var_dump($input_data);
			if(empty($input_data))
			{
				return array('status'=>0,'msg'=>'异常数据,请检查提交数据！');
			}

            $remark = '';
			if($input_data['mySubmit']=='入职')
			{
				$type = 1;
			}
			elseif ( $input_data['mySubmit']=='转正' ) 
			{
				$type = 2;
			}
			elseif ( $input_data['mySubmit']=='离职' ) 
			{
				$type = 3;
				$remark = $input_data['my_text'].'离职';
			}
			elseif ( $input_data['mySubmit']=='复职' ) 
			{
				$type = 4;
			}

			$add_data = array(
				'e_id'=>1,
				'type'=>$type,
				'op_id'=>100,
				'op_time'=>time()-100,
				'remark'=>$remark,
				'add_time'=>time(),
			);
			$employee_change_log_tbl = M('employee_info_log','crm_');
			//$data = $employee_change_log_tbl->create();
			$add_id = $employee_change_log_tbl->add($add_data);
			var_dump($add_id);
		}
	}

	public function get_uploader_data()
	{
		error_reporting(E_ALL);
		echo $dir2 = dirname(dirname(dirname(__DIR__))).'/Public/test_img2/';
		$input = $_FILES;
		var_dump($_FILES);
		//$input = I('file.');
		//$url = '/Public/img/';
		$dir = './Public/test_img2/';
		if(!file_exists($dir))
		{
			mkdir($dir,0777,true);
		}
		//$url = $dir2.$_FILES["file"]["name"];
		$url = $dir.'002.jpg';
		var_dump($url);
		/*if (file_exists($url . $_FILES["file"]["tmp_name"]))
		{*/
			var_dump(move_uploaded_file($_FILES["file"]["tmp_name"],$url));
			//var_dump(move_uploaded_file($_FILES["file"]["tmp_name"],"D:\phpStudy\PHPTutorial\WWW\myThinkphp/Public/test_img2/001.jpg"));
		//}
		
		if($input)
		{
			$img_url = __ROOT__.'/Public/img/'.$input['name'];
		}
		$return_data['code'] = 200;
		$return_data['message'] = 'ok';
		$return_data['data'] = array('url'=>$img_url);
		$this->ajaxReturn($return_data);
		//var_dump($input);exit();
	}

	public function test_num()
	{
		$num = 10.021;
		$num = '10.021';
		$num = 0.01;
		$num = '00.001sx';
		if(is_numeric($num))
		{
			echo $num;
		}
		else
		{
			echo 'error';
		}
	}

}