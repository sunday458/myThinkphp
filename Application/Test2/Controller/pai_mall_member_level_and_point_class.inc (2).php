<?php
/**
 * 会员等级与积分model
 * @author ljl
 * 
 * 
 */

class pai_mall_member_level_and_point_class extends POCO_TDG
{

      //各种类型的代表  
//    0=>'等级',
//    1=>'新用户注册',
//    2=>'受邀注册',
//    3=>'邀请好友',
//    4=>'商品交易',
//    5=>'发表评价',
//    6=>'带图评价',
//    7=>'签到',
//    8=>'连续7天签到',
//    9=>'连续30天签到',
//    10=>'每日分享',
//    12=>'订单评价分享(同一订单id分享只会算一次)',
//    10000=>'系统',
    
    //缓存前缀
	public $_redis_cache_name_prefix = "G_YUEUS_MALL_USER_";
    
    public $_stop_time = '2015-12-31 12:30';
    
    public function __construct()
	{
		$this->setServerId('101');
		$this->setDBName('mall_log_db');
	}
	
	private function set_mall_member_level_and_point_rule_tbl()
    {
        $this->setTableName('mall_member_level_and_point_rule_tbl');
    }
    
    private function set_mall_member_level_and_point_tbl()
    {
        $this->setTableName('mall_member_level_and_point_tbl');
    }
    
    private function set_mall_member_level_and_point_limit_tbl()
    {
        $this->setTableName('mall_member_level_and_point_limit_tbl');
    }
    
    private function set_mall_member_level_and_point_log_tbl()
    {
        $this->setTableName('mall_member_level_and_point_log_tbl');
    }
    
    private function set_mall_member_level_and_tmp_tbl()
    {
        $this->setTableName('mall_member_level_and_tmp_tbl');
    }
    
    private function set_mall_member_level_and_duilie_tmp_tbl()
    {
        $this->setTableName('mall_member_level_and_duilie_tmp_tbl');
    }
    
    
    /**
     * 获取买家成功订单笔数
     * @param type $user_id
     */
    public function get_success_order($buyer_user_id)//缓存
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $this->set_mall_member_level_and_point_tbl();
        $rs = $this->find("user_id='{$buyer_user_id}'");
        return (int)$rs['bill_finish_num'];
        
    }
    
    /**
     * 更新会员等级
     * @param type $buyer_user_id
     * @return boolean
     */
    public function update_member_level($buyer_user_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id  )
        {
            return false;
        }
       
        //因为没更新缓存所以需要读数据库
        $order_total = $this->get_success_order($buyer_user_id);
        $rule_info = $this->get_rule_info(1);
        $this->set_mall_member_level_and_point_tbl();
        if( ! empty($rule_info['level_rule']) )
        {
            $data = array();
            foreach($rule_info['level_rule'] as $k => $v)
            {
                //总订单数少于升级笔数
                if($order_total < $v['order_number'])
                {
                    if($rule_info['level_rule'][$k])
                    {
                        //这个是储存会员的等级 (不是等级值)
                        $data['level'] = $k;
                        $rs = $this->update($data,"user_id='{$buyer_user_id}'");
						if($rs)
                        {
                            $log_data = array();
                            $log_data['user_id'] = $buyer_user_id;
                            $log_data['add_time'] = time();
                            $log_data['type_id'] = 0;//0代表等级的type_id
                            $log_data['remark'] = '更新等级为'.$data['level'];
                            $this->insert_member_log($log_data);
                        }
                        
                        break;
                    }
                    
                }
            }
        }
        
        return true;
        
    }
    
    /**
     * 获取规则详情
     * @param type $id
     * @return boolean
     */
    public function get_rule_info($id)
    {
        $cache_key = "MEMBER_LEVEL_AND_POINT_RULE";
        $return = POCO::getCache($cache_key);
        
        if( ! empty($return) )
        {
            return $return;
        }
        
        $id = (int)$id;
        
        if( ! $id )
        {
            return false;
        }
        $rule_config = array();
        $this->set_mall_member_level_and_point_rule_tbl();
        $rule_config = $this->find("id='{$id}'");
        $rule_config['level_rule'] = unserialize($rule_config['level_rule']);
        $rule_config['point_rule'] = unserialize($rule_config['point_rule']);
        $level_config = $point_config = array();
        if( ! empty($rule_config['level_rule']) )
        {
            foreach($rule_config['level_rule'] as $k => $v)
            {
                $level_config[$v['level_key']] = $v;
            }
            unset($rule_config['level_rule']);
            $rule_config['level_rule'] = $level_config;
        }
        if( ! empty($rule_config['point_rule']) )
        {
            foreach($rule_config['point_rule'] as $k => $v)
            {
                $point_config[$v['key']] = $v;
            }
            unset($rule_config['point_rule']);
            $rule_config['point_rule'] = $point_config;
        }
        
        //设置缓存
        POCO::setCache($cache_key, $rule_config, array('life_time'=>30*86400));
        
        return $rule_config;
        
    }
    
    /**
     * 积分商城扣除积分操作
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     */
    function update_member_point_for_integral($user_id,$type_id,$point,$integral_goods_info = array())
    {
		$return = array(
						'result'=>-21,
						'message'=>'抱歉，兑换失败，请重试',
						);
		//获取积分商品数据
		$user_id = (int)$user_id;
		$type_id = (int)$type_id;
		$point = (int)$point;
		if(!$user_id or !$type_id)
		{
			return $return;
		}
		$user_info = $this->get_member_level_and_point_info($user_id);		
		//会员等级限制
		if($integral_goods_info['member_level'])
		{
			$return = array(
							'result'=>-22,
							'message'=>'您的会员等级不符',
							);
			$member_level = explode(',',$integral_goods_info['member_level']);			
			if(!in_array($user_info['level'],$member_level))
			{
				return $return;
			}
		}
		$return = array(
						'result'=>-23,
						'message'=>'你的会员积分不足',
						);
		$remaining_point = $user_info['plus_point_total']-$user_info['minus_point_total'];		
		if($remaining_point<$point)
		{
			return $return;
		}
		//扣除会员积分
		$return = array(
						'result'=>-24,
						'message'=>'抱歉，参数错误，请重试',
						);
		$data = array(
		              'user_id' => $user_id,
		              'point' => $point,					  
					  );
		//pai_log_class::add_log('update update_member_level_and_point', 'type', 'integral');
		$re = $this->update_member_level_and_point($data,'-');
		if(!$re)
		{
			return $return;
		}
		//插入记录
		$log_data = array();
		$log_data['user_id'] = $user_id;
		$log_data['point'] = -$point;
		$log_data['add_time'] = time();
		$log_data['type_id'] = $type_id;
		$log_data['remark'] = "通过积分商城消耗{$point}";
		$log_id = $this->insert_member_log($log_data);
		//pai_log_class::add_log('update insert_member_log', 'type', 'integral');
		if(!$log_id)
		{
			return $return;
		}
		$return = array(
						'result'=>1,
						'message'=>'兑换成功',
                        );		
		return $return;
    }
    
    /**
     * 检查买家是否有购买订单的ID纪录
     * @param type $order_id
     * @return boolean
     */
    private function check_order_id($order_id,$buyer_user_id)
    {
        $order_id = mall_simple_filter($order_id);
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $order_id || ! $buyer_user_id )
        {
            return false;
        }
        $cache_key = $this->G_YUEUS_MALL_USER_.'_buyer_order_id_'.$order_id.'|'.$buyer_user_id;
        $one = poco::getCache($cache_key);
        if($one)
        {
            return true;
        }else
        {
//            $sql = "select order_id from mall_db.mall_order_tbl where order_sn='{$order_id}' and buyer_user_id='{$buyer_user_id}' limit 1";
//            $rs = $this->query($sql);
//            $one = $rs['0'];
            $mall_order_api_obj = POCO::singleton('pai_mall_order_api_class');   // 实例化订单类
            $one = $mall_order_api_obj->get_order_full_info($order_id, $buyer_user_id, 'buyer');
        }
        
        if($one)
        {
            poco::setCache($cache_key, $one, array('life_time'=>86400*30));
            return true;
        }
        return false;
    }
    
    /**
     * 检查买家是否有分享过订单
     * @param type $user_id
     * @param type $type_id
     * @param type $order_id
     * @return boolean
     */
    public function check_user_limit_order($user_id,$type_id,$order_id)
    {
        $user_id = (int)$user_id;
        $type_id = (int)$type_id;
        $order_id = trim($order_id);
        if( ! $user_id || ! $type_id || ! $order_id )
        {
            return true;
        }
        $cache_key = $this->G_YUEUS_MALL_USER_.'_buyer_order_id_limit_'.$user_id.'|'.$type_id.'|'.$order_id;
        $one = poco::getCache($cache_key);
        if($one)
        {
            return true;
        }else
        {
            $this->set_mall_member_level_and_point_limit_tbl();
            $one = $this->find("user_id='{$user_id}' and type_id='{$type_id}' and object_id='{$order_id}'");
        }
        if($one)
        {
            poco::setCache($cache_key, $one, array('life_time'=>86400*30));
            return true;
        }
        return false;
        
        
        
        
    }
    
    
    /**
     * 更新会员积分前台方法
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     */
    function update_member_point_for_front($buyer_user_id,$type_id,$params=array())
    {
        $buyer_user_id = (int)$buyer_user_id;
        $type_id = (int)$type_id;
		$pa = array(
		            'user_id'=>$buyer_user_id,
		            'type_id'=>$type_id,
		            'params'=>$params,
		            //'s'=>$_SERVER,
					);
		pai_log_class::add_log($pa, 'start', 'member_point_2');
        if( ! $buyer_user_id || ! $type_id)
        {
            return array('status'=>-101,'msg'=>'没user_id或者type_id');
        }
        if( ! in_array($type_id,array(1,2,3,4,5,6,7,10,12,13)) )
        {
            return array('status'=>-102,'msg'=>'type_id参数不对');
        }
        if($type_id == 4) //交易商品的情况需要单价这个参数
        {
            if(empty($params['prices']))
            {
                return array('status'=>-41,'msg'=>'数组params中的key,prices为空');
            }
        }
        if($type_id == 12)
        {
            //判断有没购买过
            $check_order_id_rs = $this->check_order_id($params['order_id'],$buyer_user_id);
            if( ! $check_order_id_rs )
            {
                return array('status'=>-42,'msg'=>"订单编号:{$params['order_id']}参数不对(没纪录或者没传)");
            }
            //判断有没分享过
            $check_order_id_limit_rs = $this->check_user_limit_order($buyer_user_id,$type_id,$params['order_id']);
            if( $check_order_id_limit_rs )
            {
                return array(
                    'status'=>-43,
                    'msg'=>"订单id{$params['order_id']}已经被分享过一次了",
                );
            }
        }
        
        $time_now = time();
        //添加时间通过参数保存起来
        $params['add_time'] = $time_now;
        //2015年12月31号打后的数据就入临时表
//        if($time_now > strtotime($this->_stop_time))
//        {
//            $new_tmp_data = array();
//            $new_tmp_data['user_id'] = $buyer_user_id;
//            $new_tmp_data['add_time'] = time();
//            $new_tmp_data['type_id'] = $type_id;
//            $new_tmp_data['prices'] = $params['prices'];
//            $this->insert_point_tmp($new_tmp_data);
//            return array('status'=>1,'msg'=>'成功');
//        }else
//        {
//	          return array('status'=>1,'msg'=>'成功');
//            
//        }
        
        $get_limit_level_point_info = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        
        $rule_info = $this->get_rule_info(1);
       
        //注册与受邀注册情况
        if($type_id == 1 || $type_id == 2)
        {
            if( $get_limit_level_point_info['limit_data'][$type_id]['continues'] == 1)
            {
                return array('status'=>-1,'msg'=>'注册与受邀注册只能有一次');
            }
        }else if($type_id == 3) //邀请好友的情况
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] == 5 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
             )
            {
                return array('status'=>-1,'msg'=>'邀请好友每天最多5次');
            }
        }else if($type_id == 4)  //交易商品的情况
        {
            //update bill_finish_num 直接加1
            $this->update_member_bill_finish_number($buyer_user_id, 1);

            //更新用户等级 因为没更新缓存的数据,需要读数据库
            $this->update_member_level($buyer_user_id);
            
            //更新缓存
            $this->set_limit_and_level_and_point_cache($buyer_user_id);
            
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['total_point'] >= $rule_info['point_rule'][$type_id]['max_point']
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
				return array('status'=>-1,'msg'=>'交易商品每天最多1000积分,今天积分已经达到了');
            }
        }else if($type_id == 7 && ! $params['sign_test']) //签到的情况
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] >=1 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
                return array('status'=>-1,'msg'=>'签到一天只能签一次');
            }
        }else if($type_id == 10) //每天分享的情况
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] >=1 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
                return array('status'=>-1,'msg'=>'分享一天只能签一次');
            }
        }
        
        //如果是签到情况就直接调用 注册,分享订单评价也是实时调用
        if($type_id == 7 || $type_id==1 || $type_id==12 )
        {
            return $this->update_member_point_for_sql($buyer_user_id, $type_id,$params);
        }else
        {
            //入队列
            $this->set_mall_member_level_and_duilie_tmp_tbl();
            $duilie_tmp_data = array();
            $duilie_tmp_data['user_id'] = $buyer_user_id;
            $duilie_tmp_data['type_id'] = $type_id;
            $duilie_tmp_data['add_time'] = $params['add_time'];
            $duilie_tmp_data['prices'] = $params['prices'];
            $this->insert($duilie_tmp_data);

            //队列生产者
//            $point_params_ary = array();
//            $point_params_ary['point_params'] = array(
//                'buyer_user_id'=>$buyer_user_id,
//                'type_id'=>$type_id,
//                'params'=>$params,
//            );
//            $route_key = 'mall.integral.point.type_id.update';
//            $this->send_queue($point_params_ary, $route_key);
//            return array('status'=>1,'msg'=>'成功');
            $rs = $this->exec_cmd_pai_mall_update_member_point($buyer_user_id, $type_id,$params);
            if($rs)
            {
                return array('status'=>1,'msg'=>'成功');
            }
        }
    }
 
    /**
     * 发送队列
     * @param array $post
     * @param string $route_key
     */
    public function send_queue($post, $route_key)
    {
        $rabbitmq = POCO::singleton('pai_rabbitmq_class');
        $rabbitmq->producer($route_key, $post);

         //记文本
        $ary = array(
          'post'=>$post,
           'route_key'=>$route_key,
        );
        //mall_add_file_log("point_test_in", "point_test_in1", $ary);
    }

    /**
     * 队列消费者处理
     * @param type $coupon_id
     * @return boolean
     */
    public function member_consumber_do_point($point_params)
    {
        //记文本
        //require_once('/disk/data/htdocs232/poco/pai/yue_admin/task/include/basics.fun.php');
        //mall_add_file_log("point_test", "point_test_out2", $point_params);
        if( empty($point_params) )
        {
            return false;
        }
        $buyer_user_id  = $point_params['point_params']['buyer_user_id'];
        $type_id  = $point_params['point_params']['type_id'];
        $params  = $point_params['point_params']['params'];

        $this->update_member_point_for_sql($buyer_user_id,$type_id,$params);
        return true;

    }
 
    /**
     * 队列调用的方法
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     * @return type
     */
    public function update_member_point_exec_cmd($buyer_user_id,$type_id,$params= array())
    {
        return $this->update_member_point_for_sql($buyer_user_id,$type_id,$params);
    }
    
    public function test_sql_test($buyer_user_id,$type_id,$params= array())
    {
        $this->update_member_point_for_sql($buyer_user_id,$type_id,$params);
        return array('result'=>1,'message'=>'成功');
    }
    
    /**
     * 更新会员积分 核心私有化
     * @param type $buyser_user_id 买家的用户id
     * @param type $type_id 类型id
     * @param type $params 如商品需要交易单价的就这样传 $params['prices'] = 100 其他不需要的可以不传
     * @return boolean
     */
    private function update_member_point_for_sql($buyer_user_id,$type_id,$params= array())
    {
        //如果是签到才开启事务
        if($type_id ==7 )
        {
            //开户事务
            POCO_TRAN::begin($this->getServerId());
        }
        
		$pa = array(
		            'message'=>'队列',
		            'user_id'=>$buyer_user_id,
		            'type_id'=>$type_id,
		            'params'=>$params,
					);
		pai_log_class::add_log($pa, 'start', 'member_point2');

		$buyer_user_id = (int)$buyer_user_id;
        $type_id = (int)$type_id;
        $order_id = trim($params['order_id']);
        if( ! $buyer_user_id || ! $type_id )
        {
            return false;
        }
        //交易的情况一定要有价钱
        if($type_id == 4)
        {
            if(empty($params['prices']))
            {
                return false;
            }
        } 
        
        //如果时间戳为空,就给当前时间
        if(empty($params['add_time']))
        {
            $params['add_time'] = time();
        }
        
        //获取积分的类型id
        $get_point_type_id_config = pai_mall_load_config('member_get_point_type_id');
        
        $rule_info = $this->get_rule_info(1);
        if(empty($rule_info['point_rule']))
        {
            return false;
        }
        //获取积分的名称
        $get_point_name = $rule_info['point_rule'][$type_id]['get_point_name'];
        //固定获得的积分
        $can_get_point = $rule_info['point_rule'][$type_id]['can_get_point'];
        //是否随机
        $is_rand = $rule_info['point_rule'][$type_id]['is_rand'];
        //随机积分起
        $point_rand_s = $rule_info['point_rule'][$type_id]['point_rand_s'];
        //随机积分止
        $point_rand_e = $rule_info['point_rule'][$type_id]['point_rand_e'];
        //最大次数 0为不限制
        $max_times = $rule_info['point_rule'][$type_id]['max_times'];
        //最大积分 空为不限制
        $max_point = $rule_info['point_rule'][$type_id]['max_point'];
        
        //额外获得积分的初始化为0
        $can_get_point_extra = 0;
        
        //是否连续30天签到
        $is_sign_30 = false;
        //是否连续7天签到
        $is_sign_7 = false;
        
        //限制块的逻辑===========================
        //判断是否有最大次数或者分值限制  -1为不限制
		//
		
        if($max_times != '0' || $max_point !='')
        {
            $level_and_point_limit_info = $this->get_member_level_and_point_limit($buyer_user_id, $type_id);
            if( ! $level_and_point_limit_info  || $order_id )
            {
                //insert操作
                $limit_data = array();
                $limit_data['type_id'] = $type_id;
                $limit_data['user_id'] = $buyer_user_id;
                $limit_data['object_id'] = $order_id;
                if($params['init_add_time'])
                {
                    $limit_data['init_add_time'] = $params['init_add_time'];
                }else
                {
                    $limit_data['add_time'] = $params['add_time'];
                }
                
                //如果是交易商品就用交易分值
                if($type_id == 4)
                {
                    //最多也只能是1000
                    $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                    
                    //是否初始化
                    if($params['is_init'])
                    {
                        $limit_data['init_total_point'] = $get_transfer_point;
                    }else 
                    {
                        $limit_data['total_point'] = $get_transfer_point;
                    }
                    
                    
                }else  //其他就存连续次数
                {
                    $limit_data['continues'] = 1;
                }
                $limit_id = $this->insert_member_level_and_point_limit($limit_data);
                if($type_id == 7)
                {
                    if( !  $limit_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-71,'msg'=>'插入限制表失败');
                    }
                }
                
            }else
            {
                //注册与受邀注册只能一次
                if($type_id ==1 || $type_id == 2 )
                {
                    $this->set_limit_and_level_and_point_cache($buyer_user_id);
                    return array('status'=>-4,'msg'=>'所在的类型只能操作一次');
                }
                //update 操作
                $time_now = $params['add_time'];
                $org_time = $level_and_point_limit_info['add_time'];
                $org_init_add_time = $level_and_point_limit_info['init_add_time'];
                
                $limit_data = array();
                $limit_data['user_id'] = $buyer_user_id;
                $limit_data['type_id'] = $type_id;
                $limit_data['add_time'] = $params['add_time'];
                
                
                //这是连续一天的情况 并且不是初始化的情况  如果是签到测试并且是同一样也进入这个逻辑 //如果是签到测试，同一天的，或者连续一天的，都进这个连签的逻辑
                if((date("Y-m-d",$time_now) == date('Y-m-d',$org_time+86400) && ! $params['is_init']) 
                        
                        || ( $params['sign_test'] && (  date("Y-m-d",$time_now) == date('Y-m-d',$org_time) || date("Y-m-d",$time_now) == date('Y-m-d',$org_time+86400)  ) )
                        
                )
                {
                   //如果是交易商品的情况
                   if($type_id == 4)
                   {
                       //获取交易商品的积分
                       $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                       if($params['is_init'])
                       {
                            $limit_data['init_total_point'] = $get_transfer_point;
                       }else
                       {
                            $limit_data['total_point'] = $get_transfer_point;
                       } 
                       
                       
                   }else if($type_id == 7)//如果是签到的情况
                   {
                        $limit_data['continues'] = $level_and_point_limit_info['continues']+1;
                        if($limit_data['continues']%30 == 0) //连续30天
                        {
                            $is_sign_30 = true;
                            if( ! empty($rule_info['point_rule'][9]['can_get_point']))
                            {
                                $can_get_point_extra = (int)$rule_info['point_rule'][9]['can_get_point'];
                            }else
                            {
                                $can_get_point_extra = 0;
                            }
                            
                        }else if($limit_data['continues']%7 == 0)//连续7天
                        {
                            $is_sign_7 = true;
                            if( ! empty($rule_info['point_rule'][8]['can_get_point']))
                            {
                                $can_get_point_extra = (int)$rule_info['point_rule'][8]['can_get_point'];
                            }else
                            {
                                $can_get_point_extra = 0;
                            }

                        }
                   }else
                   {
                       //其他情况就每过一天就归一了
                       $limit_data['continues'] = 1;
                   } 
                   
                    $limit_data['add_time'] = $params['add_time'];
                    $rs_limit_id = $this->update_member_level_and_point_limit($limit_data);
                    if($type_id == 7)
                    {
                        if( !  $rs_limit_id )
                        {
                            POCO_TRAN::rollback($this->getServerId());
                            return array('status'=>-72,'msg'=>'更新会员限制表失败');
                        }
                    }
                }else  //这是不连续一天的情况
                {
                    
                    //这是一些条件限制========================================================
                    $time_now = $params['add_time'];
                    //防止重复签到 //签到的
                    if($type_id == 7 || $type_id==10)
                    {
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) && ! $params['sign_test'])
                        {
                            $this->set_limit_and_level_and_point_cache($buyer_user_id);
                            return array('status'=>-1,'msg'=>'一天只能一次');
                        }
                    }else if($type_id == 3)//邀请好友，最多一天5个
                    {
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']))
                        {
                            $now_continues = $level_and_point_limit_info['continues']+1;
                            if($max_times < $now_continues)
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-3,'msg'=>'邀请好友，最多一天'.$max_times);
                            }
                        }
                    }else if($type_id == 4)
                    {
                        if($params['is_init'])
                        {
                            //检查今天的最大分值是否已经达到
                            if(
                                $level_and_point_limit_info['init_total_point'] == $max_point 
                                && date('Y-m-d',$params['init_add_time']) == date('Y-m-d',$level_and_point_limit_info['init_add_time']) 
                            )
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-41,'msg'=>"每天交易商品初始化最大的分值:{$max_point}已经达到了");
                            }
                        }else
                        {
                            //检查今天的最大分值是否已经达到
                            if(
                                $level_and_point_limit_info['total_point'] == $max_point 
                                && date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) 
                            )
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-41,'msg'=>"每天交易商品最大的分值:{$max_point}已经达到了");
                            }
                        }
                        
                        
                        
                    }
                    //==================================结束===========================================
                    
                    
                    
                    //这是针对交易商品或者非交易商品情况 对连续次数或者每天最大分值进行处理========================
                    if($type_id == 4)//如果是交易商品的情况
                    {
                        //最多也只能是1000
                        $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                        
                        //如果是初始化的情况并且是同一天
                        if($params['is_init'] && date('Y-m-d',$params['init_add_time']) == date('Y-m-d',$level_and_point_limit_info['init_add_time']) )
                        {
                            //现在的当天积分
                            $now_level_and_point_limit_info['init_total_point'] = $level_and_point_limit_info['init_total_point']+$get_transfer_point;
                            //如果积分值大于最大的上限
                            if($max_point < $now_level_and_point_limit_info['init_total_point'])
                            {
                                //就更新当天最大积分值为最大的上限
                                $limit_data['init_total_point'] = $max_point;
                            }else  //如果积分值没大于最大上限就如常加上去
                            {
                                $limit_data['init_total_point'] = $now_level_and_point_limit_info['init_total_point'];
                            }
                        }//如果是同一天的情况并且不是初始化的情况
                        else if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) && ! $params['is_init'])
                        {
                            //现在的当天积分
                            $now_level_and_point_limit_info['total_point'] = $level_and_point_limit_info['total_point']+$get_transfer_point;
                            //如果积分值大于最大的上限
                            if($max_point < $now_level_and_point_limit_info['total_point'])
                            {
                                //就更新当天最大积分值为最大的上限
                                $limit_data['total_point'] = $max_point;
                            }else  //如果积分值没大于最大上限就如常加上去
                            {
                                $limit_data['total_point'] = $now_level_and_point_limit_info['total_point'];
                            }
                        }else //不是同一天的情况就直接读多少分
                        {
                            if($params['is_init'])
                            {
                                $limit_data['init_total_point'] = $get_transfer_point;
                            }else
                            {
                                $limit_data['total_point'] = $get_transfer_point;
                            }
                            
                        }
                        
                    }else  //如果非商品交易的情况
                    {
                        //如果是同一天的就直接对连续次数加1
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']))
                        {
                            
                            $limit_data['continues'] = $level_and_point_limit_info['continues']+1;
                            
                        }else //如果不是同一天的,就对连续次数进行一个归一处理
                        {
                            $limit_data['continues'] = 1;
                        }
                    }
                    //==================================结束===========================================
                    if($params['is_init'])
                    {
                        $limit_data['init_add_time'] = $params['init_add_time'];
                    }else
                    {
                        $limit_data['add_time'] = $params['add_time'];
                    }
                    
                    $rs_limit_id = $this->update_member_level_and_point_limit($limit_data);
                    if($type_id == 7)
                    {
                        if( !  $rs_limit_id )
                        {
                            POCO_TRAN::rollback($this->getServerId());
                            return array('status'=>-73,'msg'=>'更新会员限制表失败');
                        }
                    }
                }
                
                
            }
            
        }
        
        //计算可以获得积分的逻辑====================================================
        //判断是否随机 并且不是商品交易类
        if($is_rand && $type_id !=4 )
        {
           //随机分值 
           $can_get_point = $this->get_rand_point($type_id); 
        }
        
        //交易商品的分值情况特殊处理
        if($type_id == 4)
        {
            //可以拿到多少分
            $can_get_point = $get_transfer_point;
            
            //如果是初始化的情况 并且是同一天的情况
            if($params['is_init'])
            {
                //如果是大于1000的，作一个减处理  //如果原积分+今次获得的积分大于当天的最大分值限制并且原始积分不为最大限制 就作个减法 并且是同一天的情况
                if( 
                    ($level_and_point_limit_info['init_total_point']+$can_get_point) > $max_point
                        &&
                    $level_and_point_limit_info['init_total_point']!=$max_point
                        &&
                    date('Y-m-d',$params['init_add_time']) == date('Y-m-d',$level_and_point_limit_info['init_add_time'])   
                )
                {
                    $can_get_point = $max_point-$level_and_point_limit_info['init_total_point'];
                    
                }
                
                
            }else //非初始化的情况
            {
                //如果是大于1000的，作一个减处理  //如果原积分+今次获得的积分大于当天的最大分值限制并且原始积分不为最大限制 就作个减法 并且是同一天的情况
                if( 
                    ($level_and_point_limit_info['total_point']+$can_get_point) > $max_point
                        &&
                    $level_and_point_limit_info['total_point']!=$max_point
                        &&
                    date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time'])
                        
                )
                {
                    $can_get_point = $max_point-$level_and_point_limit_info['total_point'];
                }
            }
            
            
        }
        
        
        //更新汇总以及用log记录的逻辑======================================
        //积分的汇总情况
        $level_and_point_info = $this->get_member_level_and_point_info($buyer_user_id);
        
        if( ! $level_and_point_info )
        {
            //insert
            $level_point_data = array();
            $level_point_data['user_id'] = $buyer_user_id;
            $level_point_data['plus_point_total'] = $can_get_point;
            $level_point_data['update_time'] = $params['add_time'];
            $id = $this->insert_member_level_and_point($level_point_data);
            if($type_id == 7)
            {
                if( ! $id )
                {
                    POCO_TRAN::rollback($this->getServerId());
                    return array('status'=>-74,'msg'=>'插入汇总表失败');
                }
            }
            if($id)
            {
                $log_data = array();
                $log_data['type_id'] = $type_id;
                if($params['init_add_time'])
                {
                    $log_data['add_time'] = $params['init_add_time'];
                }else
                {
                    $log_data['add_time'] = $params['add_time'];
                }
                $log_data['user_id'] = $buyer_user_id;
                $log_data['point'] = $can_get_point;
                $log_data['remark'] = '由'.$get_point_type_id_config[$type_id]."获得".$can_get_point."积分";
                $log_id = $this->insert_member_log($log_data);
                if($type_id == 7)
                {
                    if( ! $log_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-75,'msg'=>'插入日志表失败');
                    }
                }
            }
        }else
        {
            //update
            $update_data = array();
            
            $update_data['point'] = $can_get_point;
            $update_data['user_id'] = $buyer_user_id;
            $rs = $this->update_member_level_and_point($update_data, '+');
            if($type_id == 7)
            {
                if( ! $rs )
                {
                    POCO_TRAN::rollback($this->getServerId());
                    return array('status'=>-76,'msg'=>'更新会员汇总失败');
                }
            }
            if($rs)
            {
                $log_data = array();
                $log_data['type_id'] = $type_id;
                if($params['init_add_time'])
                {
                    $log_data['add_time'] = $params['init_add_time'];
                }else
                {
                    $log_data['add_time'] = $params['add_time'];
                }
                
                $log_data['user_id'] = $buyer_user_id;
                $log_data['point'] = $can_get_point;
                if($order_id && $type_id==12)
                {
                    $order_text = "[订单编号:{$order_id}]";
                }else
                {
                    $order_text = '';
                }
                $log_data['remark'] = '由'.$get_point_type_id_config[$type_id]."获得".$can_get_point."积分".$order_text;
                
                $log_id = $this->insert_member_log($log_data);
                if($type_id == 7)
                {
                    if( ! $log_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-77,'msg'=>'更新会员汇总失败');
                    }
                }
            }
            
            //是否连续30天 或者 是否连续7天
            if($is_sign_30 || $is_sign_7)
            {
                $update_data = array();
                $update_data['point'] = $can_get_point_extra; //额外能获得多少分
                $update_data['user_id'] = $buyer_user_id;
                $rs = $this->update_member_level_and_point($update_data, '+');
                if($type_id == 7)
                {
                    if( ! $rs )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-78,'msg'=>'更新会员汇总失败');
                    }
                }
                if($rs)
                {
                    $log_data = array();
                    $log_data['type_id'] = $is_sign_30 ? 9 : 8;
                    $log_data['add_time'] = $params['add_time'];
                    $log_data['user_id'] = $buyer_user_id;
                    $log_data['point'] = $can_get_point_extra;
                    $log_data['remark'] = '由'.$get_point_type_id_config[$log_data['type_id']]."获得".$can_get_point_extra."积分";
                    $log_id = $this->insert_member_log($log_data);
                    if($type_id == 7)
                    {
                        if( ! $log_id )
                        {
                            POCO_TRAN::rollback($this->getServerId());
                            return array('status'=>-79,'msg'=>'插入日志失败');
                        }
                    }
                }
            }
            

        }
        
        //更新缓存
        $this->set_limit_and_level_and_point_cache($buyer_user_id,$can_get_point_extra);
        
        //如果是签到情况就提交事务
        if($type_id == 7)
        {
            //事务提交
            POCO_TRAN::commmit($this->getServerId());
        }
        
        
        return array('status'=>1,'msg'=>'成功');
        
        
    }
    
    /**
     * 更新限制和会员等级与积分的缓存
     * @param type $buyer_user_id
     * @return boolean
     */
    public function set_limit_and_level_and_point_cache($buyer_user_id,$can_get_point_extra=0)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $cache_info = array();
        
        $this->set_mall_member_level_and_point_tbl();
        $level_and_point_info = $this->find("user_id='{$buyer_user_id}'");
        $this->select_which_tbl($buyer_user_id);
        $add_time_s = strtotime(date('Y-m-d',time()));
        $add_time_e = $add_time_s+86400;
        $sign_log_one = $this->find("user_id='{$buyer_user_id}' and type_id='7' and add_time > '{$add_time_s}' and add_time < '{$add_time_e}'",'id desc');
        if( ! empty($level_and_point_info) )
        {
            $cache_info = $level_and_point_info;
            if( ! empty($sign_log_one) )
            {
                $cache_info['today_point'] = (int)($sign_log_one['point']+$can_get_point_extra);
            }else
            {
                $cache_info['today_point'] = 0;
            }
        }else
        {
            $cache_info['id'] = '';
            $cache_info['user_id'] = $buyer_user_id;
            $cache_info['update_time'] = '';
            $cache_info['level'] = 1;
            $cache_info['plus_point_total'] = '';
            $cache_info['minus_point_total'] = '';
            $cache_info['bill_finish_num'] = '';
            $cache_info['today_point'] = '';
        }
        $this->set_mall_member_level_and_point_limit_tbl();
        $limit_list = $this->findAll("user_id='{$buyer_user_id}'");
        if( ! empty($limit_list) )
        {
            $limit_key_list = array();
            foreach($limit_list as $k => $v)
            {
                $limit_key_list[$v['type_id']] = $v;
            }
        }
        if(empty($limit_key_list))
        {
            $field_ary = array();
            $field_ary = array('id'=>'','type_id'=>'','user_id'=>'','add_time'=>'','continues'=>'','total_point'=>'','init_continues'=>'','init_total_point'=>'');
            $limit_key_list[1] = $field_ary;
            $limit_key_list[2] = $field_ary;
            $limit_key_list[3] = $field_ary;
            $limit_key_list[4] = $field_ary;
            $limit_key_list[5] = $field_ary;
            $limit_key_list[6] = $field_ary;
            $limit_key_list[7] = $field_ary;
        }
        $cache_info['limit_data'] = $limit_key_list;
        
        $cache_key = $this->_redis_cache_name_prefix."{$buyer_user_id}";
        POCO::setCache($cache_key, $cache_info, array('life_time'=>3*86400)); 
		return $cache_info; 
        
    }
    
    /**
     * 获取限制与会员等级与积分缓存
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     * @return type
     */
    public function get_limit_and_level_and_point_cache($buyer_user_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id  )
        {
            return false;
        }
        $cache_key = $this->_redis_cache_name_prefix."{$buyer_user_id}";
        $rs = POCO::getCache($cache_key);
        if( empty($rs))
        {
            $rs = $this->set_limit_and_level_and_point_cache($buyer_user_id);
        }
        return $rs;
    }
    
    /**
     * 异步处理会员等级与积分
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     * @return boolean
     */
	public function exec_cmd_pai_mall_update_member_point($buyer_user_id,$type_id,$params=array())
	{
		$pai_gearman_obj = POCO::singleton('pai_gearman_class');		
		$cmd_type = 'pai_mall_update_member_point';		
		$cmd_params = array(
                                'user_id' => $buyer_user_id,
                                'type_id' => $type_id,
                                'params' => $params,
							);
		$send_rst = $pai_gearman_obj->send_cmd($cmd_type,$cmd_params);
        
        return true;
	}	
    
    /**
     * 获取交易商品根据单价和等级值获取的积分值
     * @param type $buyer_user_id
     * @param type $params
     * @return boolean
     */
    public function get_transfer_goods_point($buyer_user_id,$params=array())
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( !$buyer_user_id || empty($params['prices']) )
        {
            return false;
        }
        $user_leve_info = $this->get_member_level_and_point_info($buyer_user_id);
       
        $rule_info = $this->get_rule_info(1);
        
        //计算等级获得的积分
        //公式 是(等级值*单价)/10 向上取整
        $level_val = $rule_info['level_rule'][$user_leve_info['level']]['level_val'];
        $can_get_point = ceil(($level_val*$params['prices'])/10);
        $max_times_config = (int)$rule_info['point_rule']['4']['max_point'];
        //如果大于配置的也只是给配置的最大值
        if($can_get_point > $max_times_config )
        {
            $can_get_point = $max_times_config;
        }
        
        return $can_get_point;
    }
    
    /**
     *  获取用户等级与积分情况
     * @param type $buyer_user_id
     * @return type
     */
    public function get_member_level_and_point_info($buyer_user_id)
    {
        $this->set_mall_member_level_and_point_tbl();
        return $this->find("user_id='$buyer_user_id'");
    }
    
    /**
     * 插入会员日志
     * @param type $data
     * @return boolean
     */
    public function insert_member_log($data)
    {
        if(empty($data))
        {
            return false;
        }
        $this->select_which_tbl($data['user_id']);
        return $this->insert($data);
    }
    
    /**
     * 更新积分与等级
     * @param type $data
     * @param type $operation
     * @return boolean
     */
    public function update_member_level_and_point($data=array(),$operation='+')
    {
        if(empty($data) || empty($operation))
        {
            return false;
        }
        $set = '';
        $update_time = time();
        
        //取绝对值
        $data['point'] = abs($data['point']);
        if($operation == '+')
        {
            $set = "`plus_point_total` = `plus_point_total`+{$data['point']},update_time='{$update_time}'";
        }else if($operation == '-')
        {
            $set = "`minus_point_total` = `minus_point_total`+{$data['point']},update_time='{$update_time}'";
        }
        $this->set_mall_member_level_and_point_tbl();
        $sql = "update {$this->_db_name}.mall_member_level_and_point_tbl set {$set} where user_id='{$data['user_id']}'";
        $this->query($sql);
        return true;
    }
    /**
     * 插入会员等级与积分表
     * @param type $data
     * @return boolean
     */
    public function insert_member_level_and_point($data)
    {
        if(empty($data))
        {
            return false;
        }
        $this->set_mall_member_level_and_point_tbl();
        return $this->insert($data);
    }
    
    
    /**
     * 获取会员与等级的限制情况
     * @param type $buyer_user_id
     * @param type $type_id
     * @return boolean
     */
    public function get_member_level_and_point_limit($buyer_user_id,$type_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        $type_id = (int)$type_id;
        if( ! $buyer_user_id || ! $type_id )
        {
            return false;
        }
        $this->set_mall_member_level_and_point_limit_tbl();
        return $this->find("user_id='{$buyer_user_id}' and type_id='{$type_id}'");
    }
    
    /**
     * 插入会员与积分的限制
     * @param type $data
     * @return boolean
     */
    public function insert_member_level_and_point_limit($data)
    {
        if(empty($data))
        {
            return false;
        }
        
        $data['type_id'] = (int)$data['type_id'];
        $data['user_id'] = (int)$data['user_id'];
        $data['add_time'] = (int)$data['add_time'];
        $data['continues'] = (int)$data['continues'];
        $data['total_point'] = (int)$data['total_point'];
        $data['object_id'] = trim($data['object_id']);
        if(empty($data['object_id']))
        {
            $data['object_id'] = 0;
        }
        
        $this->set_mall_member_level_and_point_limit_tbl();
        $sql = "INSERT INTO mall_log_db.`mall_member_level_and_point_limit_tbl` (`type_id`, `user_id`, `add_time`,`continues`,`total_point`,`object_id`) 
        VALUES ('{$data['type_id']}','{$data['user_id']}','{$data['add_time']}','{$data['continues']}','{$data['total_point']}','{$data['object_id']}') "
        . "ON DUPLICATE KEY UPDATE `total_point`=`total_point`+{$data['total_point']},`continues`=`continues`+{$data['continues']}"; 
        
        $this->query($sql);
        
        return true;
        
    }
    
    /**
     * 更新会员与积分的限制情况
     * @param type $data
     * @return boolean
     */
    public function update_member_level_and_point_limit($data)
    {
        if(empty($data))
        {
            return false;
        }
        $this->set_mall_member_level_and_point_limit_tbl();
        return $this->update($data,"user_id='{$data['user_id']}' and type_id='{$data['type_id']}'");
    }
    
    /**
     * 获取相应策略的随机分值
     * @param type $type_id
     * @return boolean
     */
    public function get_rand_point($type_id)
    {
        $type_id = (int)$type_id;
        if( ! $type_id )
        {
            return false;
        }
        $rule_info = $this->get_rule_info(1);
        if( ! empty($rule_info['point_rule']) )
        {
            $type_id_info = $rule_info['point_rule'][$type_id];
            if($type_id_info['is_rand'] == 1)
            {
                $rand_point = rand((int)$type_id_info['point_rand_s'],(int)$type_id_info['point_rand_e']);
                return $rand_point;
            }
            
        }
        
        return false;
    }
    
    /*
	 * 会员等级与积分列表
	 * @param bool $b_select_count
	 * @param string $where_str 
	 * @param string $order_by
	 * @param string $limit 
	 * @param string $fields 
	 * return array
	 */
	public function get_member_level_and_point_list($b_select_count = false, $where_str = '', $order_by = 'id DESC', $limit = '0,10', $fields = '*')
	{
		$this->set_mall_member_level_and_point_tbl();
        if ($b_select_count == true)
		{
			$ret = $this->findCount ( $where_str );
		} else
		{
			$ret = $this->findAll ( $where_str, $limit, $order_by, $fields );
		}
		return $ret;
	}
    
    /*
	 * 会员等级与积分日志列表
	 * @param bool $b_select_count
	 * @param string $where_str 
	 * @param string $order_by
	 * @param string $limit 
	 * @param string $fields 
	 * return array
	 */
	public function get_member_level_and_point_log_list($b_select_count = false,$params = array(),$where_str = '', $order_by = 'id DESC', $limit = '0,10', $fields = '*')
	{
		$rs = $this->select_which_tbl($params['user_id']);
        if( ! $rs )
        {
            return false;
        }
		if ($b_select_count == true)
		{
			$ret = $this->findCount ( $where_str );
		} else
		{
			$ret = $this->findAll ( $where_str, $limit, $order_by, $fields );
		}
		return $ret;
	}
    
    /**
     * 获取用户的日志
     * @param type $buyer_user_id
     */
    public function get_log_list_for_front($buyer_user_id,$limit='0,10')
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        return $this->get_member_level_and_point_log_list(false, array('user_id'=>$buyer_user_id), " user_id='{$buyer_user_id}' and type_id!='0'",'id desc',$limit);
    }
    
    /**
     * 选择哪个user_id 余数的log表
     * @param type $month
     * @return boolean|string
     */
    public function select_which_tbl($buyer_user_id)
    {
        $this->setDBName('mall_log_db');
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $mod = $buyer_user_id%10;
        $tbl_name = "mall_member_level_and_point_log_".$mod."_tbl";
        $this->setTableName($tbl_name);
        return true;
    }
    
    /**
     * 通过后台操作更新积分
     */
    public function do_system_user_level_and_point($post)
    {
        global $yue_login_id;
        $option_point = (int)$post['option_point'];
        $buyer_user_id = (int)$post['user_id'];
        $remark = addslashes(trim($post['remark']));
        $option_user_id = $yue_login_id;
        if( ! $buyer_user_id || $option_point == 0)
        {
            return false;
        }
        
        if($option_point > 0)
        {
            $option = '+';
            $point = $option_point;
        }else
        {
            $option = '-';
            //负数取绝对值
            $point = abs($option_point);
        }
        //先更新积分
        $rs = $this->update_member_level_and_point(array('point'=>$point,'user_id'=>$buyer_user_id),$option);
        
        if($rs)
        {
            //后插入日志
            $log_data = array();
            $log_data['user_id'] = $buyer_user_id;
            $log_data['point'] = $option_point;
            $log_data['add_time'] = time();
            $log_data['type_id'] = 10000;//10000代表系统
            //$log_data['remark'] = $remark.",通过系统获得{$option_point}积分,操作人id:{$option_user_id}";
            $log_data['remark'] = $remark?$remark."获得{$option_point}积分":"通过系统获得{$option_point}积分,操作人id:{$option_user_id}";
            
            $log_id = $this->insert_member_log($log_data);
            if($log_id)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 更新会员初始化最大积分
     * @param type $buyer_user_id
     * @param type $number
     * @return boolean
     */
    public function update_member_init_total_point($buyer_user_id,$number)
    {
        $this->set_mall_member_level_and_point_limit_tbl();
        $this->update(array('init_total_point'=>$number),"user_id='$buyer_user_id'");
        return true;
    }
    
    /**
     * 更新会员成交单数
     * @param type $buyer_user_id
     * @param type $number
     * @return boolean
     */
    public function update_member_bill_finish_number($buyer_user_id,$number)
    {
        $buyer_user_id = (int)$buyer_user_id;
        $number = (int)$number;
        if( ! $buyer_user_id || ! $number )
        {
            return false;
        }
        $sql = "update {$this->_db_name}.mall_member_level_and_point_tbl set `bill_finish_num`=`bill_finish_num`+{$number} where user_id='{$buyer_user_id}'";
        $this->query($sql);
        return true;
    }
    
    /**
     * 插入临时表
     * @param type $data
     */
    public function insert_point_tmp($data)
    {
        if(empty($data))
        {
            return false;
        }
        $this->set_mall_member_level_and_tmp_tbl();
        return $this->insert($data);
    }
    
    /**
     * 临时表数据到积分系统
     */
    public function tmp_to_point()
    {
        exit('no_use');
        $sql = "select id,user_id,add_time,type_id,prices from mall_log_db.`mall_member_level_and_tmp_tbl` where is_do='0' and user_id='115203' order by add_time asc";
        $rs = $this->query($sql);
        $i = 1;
        $id_range = array();
        foreach($rs as $v)
        {
            $params = array();
            $params = array('init_add_time'=>$v['add_time'],'is_init'=>true,'prices'=>$v['prices']);
            $res = $this->update_member_point_for_sql($v['user_id'], $v['type_id'], $params);
            $id_range[] = $v['id'];
            if($i%100==0)
            {
                $sql1 = "update mall_log_db.`mall_member_level_and_tmp_tbl` set `is_do`='1' where `id` in (".implode(",",$id_range).")";
                $res1 = $this->query($sql1);
                $id_range = array();
            }
            
            $i++;
        }
        if($id_range)
		{
        $sql1 = "update mall_log_db.`mall_member_level_and_tmp_tbl` set `is_do`='1' where `id` in (".implode(",",$id_range).")";
        $res1 = $this->query($sql1);
		}
        
    }
    
    /*
	 * 插入临时数据
	 */	
	public function insert_tmp($time)
	{
		$time = $time?$time:time();
        
		$sql = "SELECT user_id,add_time FROM pai_db.`pai_user_tbl` where add_time<=$time order by user_id asc";
		$re = $this->query($sql);
        
        $in = 1;
		$in_detail = array();
		foreach($re as $val)
		{
			//echo $val['user_id']."------".$val['add_time']."<br>";
			$in_detail[] = "('1','".$val['user_id']."','".$val['add_time']."')";
			if($in%1000==0)
			{
				$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`) VALUES ".implode(',',$in_detail);
				$this->query($in_sql);
				//echo $in."<br>".$in_sql."<br>";
				$in_detail = array();
			}
			$in++;
		}
		$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`) VALUES ".implode(',',$in_detail);
		//echo $in."<br>".$in_sql."<br>";
		$this->query($in_sql);
		
		echo count($re);
		echo "<br>over<br>";
		
		$sql2 = "SELECT from_user_id,add_time FROM mall_db.`mall_comment_seller_tbl` where add_time<=$time order by comment_id asc";
		$re2 = $this->query($sql2);
		$in = 1;
		$in_detail = array();
		foreach($re2 as $val)
		{
			//echo $val['from_user_id']."------".$val['add_time']."<br>";
			$in_detail[] = "('5','".$val['from_user_id']."','".$val['add_time']."')";
			if($in%1000==0)
			{
				$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`) VALUES ".implode(',',$in_detail);
				$this->query($in_sql);
				//echo $in."<br>".$in_sql."<br>";
				$in_detail = array();
			}
			$in++;
		}
		$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`) VALUES ".implode(',',$in_detail);
		//echo $in."<br>".$in_sql."<br>";
		$this->query($in_sql);
		
		echo count($re2);
		echo "<br>over<br>";
		
		$sql3 = "SELECT buyer_user_id,add_time,sign_time,pending_amount FROM mall_db.`mall_order_tbl` where status=8 and sign_time<=$time order by order_id asc";
		$re3 = $this->query($sql3);
		$in = 1;
		$in_detail = array();
		foreach($re3 as $val)
		{
			//echo $val['buyer_user_id']."------".$val['sign_time']."------".$val['add_time']."<br>";
			$in_detail[] = "('4','".$val['buyer_user_id']."','".($val['sign_time']?$val['sign_time']:$val['add_time'])."','".$val['pending_amount']."')";
			if($in%1000==0)
			{
				$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`,`prices`) VALUES ".implode(',',$in_detail);
				$this->query($in_sql);
				//echo $in."<br>".$in_sql."<br>";
				$in_detail = array();
			}
			$in++;
		}
		$in_sql = "INSERT INTO mall_log_db.`mall_member_level_and_tmp_tbl` (`type_id`, `user_id`, `add_time`,`prices`) VALUES ".implode(',',$in_detail);
		//echo $in."<br>".$in_sql."<br>";
		$this->query($in_sql);
		echo count($re3);
		echo "<br>over<br>";
    }
    
    /**
     * 获取测试数据
     * @param type $user_id
     * @return boolean
     */
    public function get_tmp_data($user_id)
    {
        $user_id = (int)$user_id;
        if( ! $user_id )
        {
            return false;
        }
        $this->set_mall_member_level_and_tmp_tbl();
        $rs = $this->findAll("user_id='{$user_id}'",null,"add_time asc");
        if( ! empty($rs) )
        {
            foreach($rs as $k => $v)
            {
                $rs[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }
        return $rs;
    }
    
    /**
     * 判断今天有没签到
     * @param type $buyer_user_id
     * @return boolean
     */
    public function buyer_user_id_is_sign_or_not_today($buyer_user_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $point_and_limit_data = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        if(empty($point_and_limit_data['limit_data']['7']))
        {
            return false;
        }
        $time_s = strtotime(date('Y-m-d',time()));
        $time_e = $time_s+86400;
        if($point_and_limit_data['limit_data']['7']['add_time'] >= $time_s  && $point_and_limit_data['limit_data']['7']['add_time'] < $time_e)
        {
            return true;
        }else
        {
            return false;
        }
    }
    
    /**
     * 用户连续签到多少天
     * @param type $buyer_user_id
     * @return boolean|int
     */
    public function buyer_user_id_sign_continues_day($buyer_user_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $point_and_limit_data = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        if(empty($point_and_limit_data['limit_data']['7']))
        {
            return 0;
        }
        $add_time_s = strtotime(date('Y-m-d',time()-86400));
        $add_time_e = strtotime( date('Y-m-d',time()) )+86400;
        if($point_and_limit_data['limit_data']['7']['add_time'] >= $add_time_s && $point_and_limit_data['limit_data']['7']['add_time'] <= $add_time_e )
        {
            return $point_and_limit_data['limit_data']['7']['continues'];
        }else
        {
            return 0;
        }
        
    }
    
    /**
     * 用户签到多少天
     * @param type $buyer_user_id
     * @return boolean
     */
    public function buyser_user_id_sign_total_day($buyer_user_id)
    {
        $buyer_user_id  = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $mod = $buyer_user_id%10;
        $sql = "select count(*) as total from mall_log_db.mall_member_level_and_point_log_{$mod}_tbl where user_id='{$buyer_user_id}' and type_id='7'";
        $rs = $this->query($sql);
        return (int)$rs['0']['total'];
    }
    
    /**
     * 还差多少单可以升级
     * @param type $buyer_user_id
     * @return boolean|int
     */
    public function buyer_user_id_need_order_numbers_to_upgrade_level($buyer_user_id)
    {
        $buyer_user_id  = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $rule_info = $this->get_rule_info(1);
        $point_and_limit_data = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        if(empty($point_and_limit_data))
        {
            return false;
        }
        $need_order_numbers = (int)($rule_info['level_rule'][$point_and_limit_data['level']]['order_number'] - $point_and_limit_data['bill_finish_num']);
        if($need_order_numbers < 0 )
        {
            $need_order_numbers = 0;
        }
        return $need_order_numbers;
    }
    
    /**
     * 设置队列的临时数据已经做
     * @param type $buyer_user_id
     * @param type $type_id
     * @return boolean
     */
    public function set_duilie_tmp_is_do($buyer_user_id,$type_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        $type_id = (int)$type_id;
        if( ! $buyer_user_id || ! $type_id )
        {
            return false;
        }
        $this->set_mall_member_level_and_duilie_tmp_tbl();
        return $this->update(array('is_do'=>1,'update_time'=>time()),"user_id='{$buyer_user_id}' and type_id='{$type_id}' and is_do='0'");
    }
    
    /**
     * 获取最真实的等级还有还差多少笔订单才可以升级
     * @param type $buyer_user_id
     */
    public function get_real_level_and_upgrade_order_numbers($buyer_user_id)
    {
        $buyer_user_id = (int)$buyer_user_id;
        if( ! $buyer_user_id )
        {
            return false;
        }
        $rule_info = $this->get_rule_info(1);
        $limit_cache = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        $bill_finish_number = (int)$limit_cache['bill_finish_num'];
        $real_level = 1;
        $real_need_orders = 5;
        if( ! empty($rule_info['level_rule']) )
        {
            foreach($rule_info['level_rule'] as $k => $v)
            {
                if($bill_finish_number < $v['order_number'])
                {
                    $real_level = $k;
                    $real_need_orders = (int)($v['order_number']-$bill_finish_number);
                    break;
                }
            }
            return array('real_level'=>$real_level,'real_need_orders'=>$real_need_orders);
        }else
        {
            return array('real_level'=>$real_level,'real_need_orders'=>$real_level);
        }
        
        
    }
}
