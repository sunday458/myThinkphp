<?php
/**
 * ��Ա�ȼ������model
 * @author ljl
 * 
 * 
 */

class pai_mall_member_level_and_point_class extends POCO_TDG
{

      //�������͵Ĵ���  
//    0=>'�ȼ�',
//    1=>'���û�ע��',
//    2=>'����ע��',
//    3=>'�������',
//    4=>'��Ʒ����',
//    5=>'��������',
//    6=>'��ͼ����',
//    7=>'ǩ��',
//    8=>'����7��ǩ��',
//    9=>'����30��ǩ��',
//    10=>'ÿ�շ���',
//    12=>'�������۷���(ͬһ����id����ֻ����һ��)',
//    10000=>'ϵͳ',
    
    //����ǰ׺
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
     * ��ȡ��ҳɹ���������
     * @param type $user_id
     */
    public function get_success_order($buyer_user_id)//����
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
     * ���»�Ա�ȼ�
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
       
        //��Ϊû���»���������Ҫ�����ݿ�
        $order_total = $this->get_success_order($buyer_user_id);
        $rule_info = $this->get_rule_info(1);
        $this->set_mall_member_level_and_point_tbl();
        if( ! empty($rule_info['level_rule']) )
        {
            $data = array();
            foreach($rule_info['level_rule'] as $k => $v)
            {
                //�ܶ�����������������
                if($order_total < $v['order_number'])
                {
                    if($rule_info['level_rule'][$k])
                    {
                        //����Ǵ����Ա�ĵȼ� (���ǵȼ�ֵ)
                        $data['level'] = $k;
                        $rs = $this->update($data,"user_id='{$buyer_user_id}'");
						if($rs)
                        {
                            $log_data = array();
                            $log_data['user_id'] = $buyer_user_id;
                            $log_data['add_time'] = time();
                            $log_data['type_id'] = 0;//0����ȼ���type_id
                            $log_data['remark'] = '���µȼ�Ϊ'.$data['level'];
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
     * ��ȡ��������
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
        
        //���û���
        POCO::setCache($cache_key, $rule_config, array('life_time'=>30*86400));
        
        return $rule_config;
        
    }
    
    /**
     * �����̳ǿ۳����ֲ���
     * @param type $buyer_user_id
     * @param type $type_id
     * @param type $params
     */
    function update_member_point_for_integral($user_id,$type_id,$point,$integral_goods_info = array())
    {
		$return = array(
						'result'=>-21,
						'message'=>'��Ǹ���һ�ʧ�ܣ�������',
						);
		//��ȡ������Ʒ����
		$user_id = (int)$user_id;
		$type_id = (int)$type_id;
		$point = (int)$point;
		if(!$user_id or !$type_id)
		{
			return $return;
		}
		$user_info = $this->get_member_level_and_point_info($user_id);		
		//��Ա�ȼ�����
		if($integral_goods_info['member_level'])
		{
			$return = array(
							'result'=>-22,
							'message'=>'���Ļ�Ա�ȼ�����',
							);
			$member_level = explode(',',$integral_goods_info['member_level']);			
			if(!in_array($user_info['level'],$member_level))
			{
				return $return;
			}
		}
		$return = array(
						'result'=>-23,
						'message'=>'��Ļ�Ա���ֲ���',
						);
		$remaining_point = $user_info['plus_point_total']-$user_info['minus_point_total'];		
		if($remaining_point<$point)
		{
			return $return;
		}
		//�۳���Ա����
		$return = array(
						'result'=>-24,
						'message'=>'��Ǹ����������������',
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
		//�����¼
		$log_data = array();
		$log_data['user_id'] = $user_id;
		$log_data['point'] = -$point;
		$log_data['add_time'] = time();
		$log_data['type_id'] = $type_id;
		$log_data['remark'] = "ͨ�������̳�����{$point}";
		$log_id = $this->insert_member_log($log_data);
		//pai_log_class::add_log('update insert_member_log', 'type', 'integral');
		if(!$log_id)
		{
			return $return;
		}
		$return = array(
						'result'=>1,
						'message'=>'�һ��ɹ�',
                        );		
		return $return;
    }
    
    /**
     * �������Ƿ��й��򶩵���ID��¼
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
            $mall_order_api_obj = POCO::singleton('pai_mall_order_api_class');   // ʵ����������
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
     * �������Ƿ��з��������
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
     * ���»�Ա����ǰ̨����
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
            return array('status'=>-101,'msg'=>'ûuser_id����type_id');
        }
        if( ! in_array($type_id,array(1,2,3,4,5,6,7,10,12,13)) )
        {
            return array('status'=>-102,'msg'=>'type_id��������');
        }
        if($type_id == 4) //������Ʒ�������Ҫ�����������
        {
            if(empty($params['prices']))
            {
                return array('status'=>-41,'msg'=>'����params�е�key,pricesΪ��');
            }
        }
        if($type_id == 12)
        {
            //�ж���û�����
            $check_order_id_rs = $this->check_order_id($params['order_id'],$buyer_user_id);
            if( ! $check_order_id_rs )
            {
                return array('status'=>-42,'msg'=>"�������:{$params['order_id']}��������(û��¼����û��)");
            }
            //�ж���û�����
            $check_order_id_limit_rs = $this->check_user_limit_order($buyer_user_id,$type_id,$params['order_id']);
            if( $check_order_id_limit_rs )
            {
                return array(
                    'status'=>-43,
                    'msg'=>"����id{$params['order_id']}�Ѿ��������һ����",
                );
            }
        }
        
        $time_now = time();
        //���ʱ��ͨ��������������
        $params['add_time'] = $time_now;
        //2015��12��31�Ŵ������ݾ�����ʱ��
//        if($time_now > strtotime($this->_stop_time))
//        {
//            $new_tmp_data = array();
//            $new_tmp_data['user_id'] = $buyer_user_id;
//            $new_tmp_data['add_time'] = time();
//            $new_tmp_data['type_id'] = $type_id;
//            $new_tmp_data['prices'] = $params['prices'];
//            $this->insert_point_tmp($new_tmp_data);
//            return array('status'=>1,'msg'=>'�ɹ�');
//        }else
//        {
//	          return array('status'=>1,'msg'=>'�ɹ�');
//            
//        }
        
        $get_limit_level_point_info = $this->get_limit_and_level_and_point_cache($buyer_user_id);
        
        $rule_info = $this->get_rule_info(1);
       
        //ע��������ע�����
        if($type_id == 1 || $type_id == 2)
        {
            if( $get_limit_level_point_info['limit_data'][$type_id]['continues'] == 1)
            {
                return array('status'=>-1,'msg'=>'ע��������ע��ֻ����һ��');
            }
        }else if($type_id == 3) //������ѵ����
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] == 5 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
             )
            {
                return array('status'=>-1,'msg'=>'�������ÿ�����5��');
            }
        }else if($type_id == 4)  //������Ʒ�����
        {
            //update bill_finish_num ֱ�Ӽ�1
            $this->update_member_bill_finish_number($buyer_user_id, 1);

            //�����û��ȼ� ��Ϊû���»��������,��Ҫ�����ݿ�
            $this->update_member_level($buyer_user_id);
            
            //���»���
            $this->set_limit_and_level_and_point_cache($buyer_user_id);
            
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['total_point'] >= $rule_info['point_rule'][$type_id]['max_point']
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
				return array('status'=>-1,'msg'=>'������Ʒÿ�����1000����,��������Ѿ��ﵽ��');
            }
        }else if($type_id == 7 && ! $params['sign_test']) //ǩ�������
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] >=1 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
                return array('status'=>-1,'msg'=>'ǩ��һ��ֻ��ǩһ��');
            }
        }else if($type_id == 10) //ÿ���������
        {
            if( 
                $get_limit_level_point_info['limit_data'][$type_id]['continues'] >=1 
                && date('Y-m-d',$time_now) == date('Y-m-d',$get_limit_level_point_info['limit_data'][$type_id]['add_time'])
                    
            )
            {
                return array('status'=>-1,'msg'=>'����һ��ֻ��ǩһ��');
            }
        }
        
        //�����ǩ�������ֱ�ӵ��� ע��,����������Ҳ��ʵʱ����
        if($type_id == 7 || $type_id==1 || $type_id==12 )
        {
            return $this->update_member_point_for_sql($buyer_user_id, $type_id,$params);
        }else
        {
            //�����
            $this->set_mall_member_level_and_duilie_tmp_tbl();
            $duilie_tmp_data = array();
            $duilie_tmp_data['user_id'] = $buyer_user_id;
            $duilie_tmp_data['type_id'] = $type_id;
            $duilie_tmp_data['add_time'] = $params['add_time'];
            $duilie_tmp_data['prices'] = $params['prices'];
            $this->insert($duilie_tmp_data);

            //����������
//            $point_params_ary = array();
//            $point_params_ary['point_params'] = array(
//                'buyer_user_id'=>$buyer_user_id,
//                'type_id'=>$type_id,
//                'params'=>$params,
//            );
//            $route_key = 'mall.integral.point.type_id.update';
//            $this->send_queue($point_params_ary, $route_key);
//            return array('status'=>1,'msg'=>'�ɹ�');
            $rs = $this->exec_cmd_pai_mall_update_member_point($buyer_user_id, $type_id,$params);
            if($rs)
            {
                return array('status'=>1,'msg'=>'�ɹ�');
            }
        }
    }
 
    /**
     * ���Ͷ���
     * @param array $post
     * @param string $route_key
     */
    public function send_queue($post, $route_key)
    {
        $rabbitmq = POCO::singleton('pai_rabbitmq_class');
        $rabbitmq->producer($route_key, $post);

         //���ı�
        $ary = array(
          'post'=>$post,
           'route_key'=>$route_key,
        );
        //mall_add_file_log("point_test_in", "point_test_in1", $ary);
    }

    /**
     * ���������ߴ���
     * @param type $coupon_id
     * @return boolean
     */
    public function member_consumber_do_point($point_params)
    {
        //���ı�
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
     * ���е��õķ���
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
        return array('result'=>1,'message'=>'�ɹ�');
    }
    
    /**
     * ���»�Ա���� ����˽�л�
     * @param type $buyser_user_id ��ҵ��û�id
     * @param type $type_id ����id
     * @param type $params ����Ʒ��Ҫ���׵��۵ľ������� $params['prices'] = 100 ��������Ҫ�Ŀ��Բ���
     * @return boolean
     */
    private function update_member_point_for_sql($buyer_user_id,$type_id,$params= array())
    {
        //�����ǩ���ſ�������
        if($type_id ==7 )
        {
            //��������
            POCO_TRAN::begin($this->getServerId());
        }
        
		$pa = array(
		            'message'=>'����',
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
        //���׵����һ��Ҫ�м�Ǯ
        if($type_id == 4)
        {
            if(empty($params['prices']))
            {
                return false;
            }
        } 
        
        //���ʱ���Ϊ��,�͸���ǰʱ��
        if(empty($params['add_time']))
        {
            $params['add_time'] = time();
        }
        
        //��ȡ���ֵ�����id
        $get_point_type_id_config = pai_mall_load_config('member_get_point_type_id');
        
        $rule_info = $this->get_rule_info(1);
        if(empty($rule_info['point_rule']))
        {
            return false;
        }
        //��ȡ���ֵ�����
        $get_point_name = $rule_info['point_rule'][$type_id]['get_point_name'];
        //�̶���õĻ���
        $can_get_point = $rule_info['point_rule'][$type_id]['can_get_point'];
        //�Ƿ����
        $is_rand = $rule_info['point_rule'][$type_id]['is_rand'];
        //���������
        $point_rand_s = $rule_info['point_rule'][$type_id]['point_rand_s'];
        //�������ֹ
        $point_rand_e = $rule_info['point_rule'][$type_id]['point_rand_e'];
        //������ 0Ϊ������
        $max_times = $rule_info['point_rule'][$type_id]['max_times'];
        //������ ��Ϊ������
        $max_point = $rule_info['point_rule'][$type_id]['max_point'];
        
        //�����û��ֵĳ�ʼ��Ϊ0
        $can_get_point_extra = 0;
        
        //�Ƿ�����30��ǩ��
        $is_sign_30 = false;
        //�Ƿ�����7��ǩ��
        $is_sign_7 = false;
        
        //���ƿ���߼�===========================
        //�ж��Ƿ������������߷�ֵ����  -1Ϊ������
		//
		
        if($max_times != '0' || $max_point !='')
        {
            $level_and_point_limit_info = $this->get_member_level_and_point_limit($buyer_user_id, $type_id);
            if( ! $level_and_point_limit_info  || $order_id )
            {
                //insert����
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
                
                //����ǽ�����Ʒ���ý��׷�ֵ
                if($type_id == 4)
                {
                    //���Ҳֻ����1000
                    $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                    
                    //�Ƿ��ʼ��
                    if($params['is_init'])
                    {
                        $limit_data['init_total_point'] = $get_transfer_point;
                    }else 
                    {
                        $limit_data['total_point'] = $get_transfer_point;
                    }
                    
                    
                }else  //�����ʹ���������
                {
                    $limit_data['continues'] = 1;
                }
                $limit_id = $this->insert_member_level_and_point_limit($limit_data);
                if($type_id == 7)
                {
                    if( !  $limit_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-71,'msg'=>'�������Ʊ�ʧ��');
                    }
                }
                
            }else
            {
                //ע��������ע��ֻ��һ��
                if($type_id ==1 || $type_id == 2 )
                {
                    $this->set_limit_and_level_and_point_cache($buyer_user_id);
                    return array('status'=>-4,'msg'=>'���ڵ�����ֻ�ܲ���һ��');
                }
                //update ����
                $time_now = $params['add_time'];
                $org_time = $level_and_point_limit_info['add_time'];
                $org_init_add_time = $level_and_point_limit_info['init_add_time'];
                
                $limit_data = array();
                $limit_data['user_id'] = $buyer_user_id;
                $limit_data['type_id'] = $type_id;
                $limit_data['add_time'] = $params['add_time'];
                
                
                //��������һ������ ���Ҳ��ǳ�ʼ�������  �����ǩ�����Բ�����ͬһ��Ҳ��������߼� //�����ǩ�����ԣ�ͬһ��ģ���������һ��ģ����������ǩ���߼�
                if((date("Y-m-d",$time_now) == date('Y-m-d',$org_time+86400) && ! $params['is_init']) 
                        
                        || ( $params['sign_test'] && (  date("Y-m-d",$time_now) == date('Y-m-d',$org_time) || date("Y-m-d",$time_now) == date('Y-m-d',$org_time+86400)  ) )
                        
                )
                {
                   //����ǽ�����Ʒ�����
                   if($type_id == 4)
                   {
                       //��ȡ������Ʒ�Ļ���
                       $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                       if($params['is_init'])
                       {
                            $limit_data['init_total_point'] = $get_transfer_point;
                       }else
                       {
                            $limit_data['total_point'] = $get_transfer_point;
                       } 
                       
                       
                   }else if($type_id == 7)//�����ǩ�������
                   {
                        $limit_data['continues'] = $level_and_point_limit_info['continues']+1;
                        if($limit_data['continues']%30 == 0) //����30��
                        {
                            $is_sign_30 = true;
                            if( ! empty($rule_info['point_rule'][9]['can_get_point']))
                            {
                                $can_get_point_extra = (int)$rule_info['point_rule'][9]['can_get_point'];
                            }else
                            {
                                $can_get_point_extra = 0;
                            }
                            
                        }else if($limit_data['continues']%7 == 0)//����7��
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
                       //���������ÿ��һ��͹�һ��
                       $limit_data['continues'] = 1;
                   } 
                   
                    $limit_data['add_time'] = $params['add_time'];
                    $rs_limit_id = $this->update_member_level_and_point_limit($limit_data);
                    if($type_id == 7)
                    {
                        if( !  $rs_limit_id )
                        {
                            POCO_TRAN::rollback($this->getServerId());
                            return array('status'=>-72,'msg'=>'���»�Ա���Ʊ�ʧ��');
                        }
                    }
                }else  //���ǲ�����һ������
                {
                    
                    //����һЩ��������========================================================
                    $time_now = $params['add_time'];
                    //��ֹ�ظ�ǩ�� //ǩ����
                    if($type_id == 7 || $type_id==10)
                    {
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) && ! $params['sign_test'])
                        {
                            $this->set_limit_and_level_and_point_cache($buyer_user_id);
                            return array('status'=>-1,'msg'=>'һ��ֻ��һ��');
                        }
                    }else if($type_id == 3)//������ѣ����һ��5��
                    {
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']))
                        {
                            $now_continues = $level_and_point_limit_info['continues']+1;
                            if($max_times < $now_continues)
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-3,'msg'=>'������ѣ����һ��'.$max_times);
                            }
                        }
                    }else if($type_id == 4)
                    {
                        if($params['is_init'])
                        {
                            //�����������ֵ�Ƿ��Ѿ��ﵽ
                            if(
                                $level_and_point_limit_info['init_total_point'] == $max_point 
                                && date('Y-m-d',$params['init_add_time']) == date('Y-m-d',$level_and_point_limit_info['init_add_time']) 
                            )
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-41,'msg'=>"ÿ�콻����Ʒ��ʼ�����ķ�ֵ:{$max_point}�Ѿ��ﵽ��");
                            }
                        }else
                        {
                            //�����������ֵ�Ƿ��Ѿ��ﵽ
                            if(
                                $level_and_point_limit_info['total_point'] == $max_point 
                                && date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) 
                            )
                            {
                                $this->set_limit_and_level_and_point_cache($buyer_user_id);
                                return array('status'=>-41,'msg'=>"ÿ�콻����Ʒ���ķ�ֵ:{$max_point}�Ѿ��ﵽ��");
                            }
                        }
                        
                        
                        
                    }
                    //==================================����===========================================
                    
                    
                    
                    //������Խ�����Ʒ���߷ǽ�����Ʒ��� ��������������ÿ������ֵ���д���========================
                    if($type_id == 4)//����ǽ�����Ʒ�����
                    {
                        //���Ҳֻ����1000
                        $get_transfer_point = $this->get_transfer_goods_point($buyer_user_id, $params);
                        
                        //����ǳ�ʼ�������������ͬһ��
                        if($params['is_init'] && date('Y-m-d',$params['init_add_time']) == date('Y-m-d',$level_and_point_limit_info['init_add_time']) )
                        {
                            //���ڵĵ������
                            $now_level_and_point_limit_info['init_total_point'] = $level_and_point_limit_info['init_total_point']+$get_transfer_point;
                            //�������ֵ������������
                            if($max_point < $now_level_and_point_limit_info['init_total_point'])
                            {
                                //�͸��µ���������ֵΪ��������
                                $limit_data['init_total_point'] = $max_point;
                            }else  //�������ֵû����������޾��糣����ȥ
                            {
                                $limit_data['init_total_point'] = $now_level_and_point_limit_info['init_total_point'];
                            }
                        }//�����ͬһ���������Ҳ��ǳ�ʼ�������
                        else if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']) && ! $params['is_init'])
                        {
                            //���ڵĵ������
                            $now_level_and_point_limit_info['total_point'] = $level_and_point_limit_info['total_point']+$get_transfer_point;
                            //�������ֵ������������
                            if($max_point < $now_level_and_point_limit_info['total_point'])
                            {
                                //�͸��µ���������ֵΪ��������
                                $limit_data['total_point'] = $max_point;
                            }else  //�������ֵû����������޾��糣����ȥ
                            {
                                $limit_data['total_point'] = $now_level_and_point_limit_info['total_point'];
                            }
                        }else //����ͬһ��������ֱ�Ӷ����ٷ�
                        {
                            if($params['is_init'])
                            {
                                $limit_data['init_total_point'] = $get_transfer_point;
                            }else
                            {
                                $limit_data['total_point'] = $get_transfer_point;
                            }
                            
                        }
                        
                    }else  //�������Ʒ���׵����
                    {
                        //�����ͬһ��ľ�ֱ�Ӷ�����������1
                        if(date('Y-m-d',$time_now) == date('Y-m-d',$level_and_point_limit_info['add_time']))
                        {
                            
                            $limit_data['continues'] = $level_and_point_limit_info['continues']+1;
                            
                        }else //�������ͬһ���,�Ͷ�������������һ����һ����
                        {
                            $limit_data['continues'] = 1;
                        }
                    }
                    //==================================����===========================================
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
                            return array('status'=>-73,'msg'=>'���»�Ա���Ʊ�ʧ��');
                        }
                    }
                }
                
                
            }
            
        }
        
        //������Ի�û��ֵ��߼�====================================================
        //�ж��Ƿ���� ���Ҳ�����Ʒ������
        if($is_rand && $type_id !=4 )
        {
           //�����ֵ 
           $can_get_point = $this->get_rand_point($type_id); 
        }
        
        //������Ʒ�ķ�ֵ������⴦��
        if($type_id == 4)
        {
            //�����õ����ٷ�
            $can_get_point = $get_transfer_point;
            
            //����ǳ�ʼ������� ������ͬһ������
            if($params['is_init'])
            {
                //����Ǵ���1000�ģ���һ��������  //���ԭ����+��λ�õĻ��ִ��ڵ��������ֵ���Ʋ���ԭʼ���ֲ�Ϊ������� ���������� ������ͬһ������
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
                
                
            }else //�ǳ�ʼ�������
            {
                //����Ǵ���1000�ģ���һ��������  //���ԭ����+��λ�õĻ��ִ��ڵ��������ֵ���Ʋ���ԭʼ���ֲ�Ϊ������� ���������� ������ͬһ������
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
        
        
        //���»����Լ���log��¼���߼�======================================
        //���ֵĻ������
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
                    return array('status'=>-74,'msg'=>'������ܱ�ʧ��');
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
                $log_data['remark'] = '��'.$get_point_type_id_config[$type_id]."���".$can_get_point."����";
                $log_id = $this->insert_member_log($log_data);
                if($type_id == 7)
                {
                    if( ! $log_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-75,'msg'=>'������־��ʧ��');
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
                    return array('status'=>-76,'msg'=>'���»�Ա����ʧ��');
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
                    $order_text = "[�������:{$order_id}]";
                }else
                {
                    $order_text = '';
                }
                $log_data['remark'] = '��'.$get_point_type_id_config[$type_id]."���".$can_get_point."����".$order_text;
                
                $log_id = $this->insert_member_log($log_data);
                if($type_id == 7)
                {
                    if( ! $log_id )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-77,'msg'=>'���»�Ա����ʧ��');
                    }
                }
            }
            
            //�Ƿ�����30�� ���� �Ƿ�����7��
            if($is_sign_30 || $is_sign_7)
            {
                $update_data = array();
                $update_data['point'] = $can_get_point_extra; //�����ܻ�ö��ٷ�
                $update_data['user_id'] = $buyer_user_id;
                $rs = $this->update_member_level_and_point($update_data, '+');
                if($type_id == 7)
                {
                    if( ! $rs )
                    {
                        POCO_TRAN::rollback($this->getServerId());
                        return array('status'=>-78,'msg'=>'���»�Ա����ʧ��');
                    }
                }
                if($rs)
                {
                    $log_data = array();
                    $log_data['type_id'] = $is_sign_30 ? 9 : 8;
                    $log_data['add_time'] = $params['add_time'];
                    $log_data['user_id'] = $buyer_user_id;
                    $log_data['point'] = $can_get_point_extra;
                    $log_data['remark'] = '��'.$get_point_type_id_config[$log_data['type_id']]."���".$can_get_point_extra."����";
                    $log_id = $this->insert_member_log($log_data);
                    if($type_id == 7)
                    {
                        if( ! $log_id )
                        {
                            POCO_TRAN::rollback($this->getServerId());
                            return array('status'=>-79,'msg'=>'������־ʧ��');
                        }
                    }
                }
            }
            

        }
        
        //���»���
        $this->set_limit_and_level_and_point_cache($buyer_user_id,$can_get_point_extra);
        
        //�����ǩ��������ύ����
        if($type_id == 7)
        {
            //�����ύ
            POCO_TRAN::commmit($this->getServerId());
        }
        
        
        return array('status'=>1,'msg'=>'�ɹ�');
        
        
    }
    
    /**
     * �������ƺͻ�Ա�ȼ�����ֵĻ���
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
     * ��ȡ�������Ա�ȼ�����ֻ���
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
     * �첽�����Ա�ȼ������
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
     * ��ȡ������Ʒ���ݵ��ۺ͵ȼ�ֵ��ȡ�Ļ���ֵ
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
        
        //����ȼ���õĻ���
        //��ʽ ��(�ȼ�ֵ*����)/10 ����ȡ��
        $level_val = $rule_info['level_rule'][$user_leve_info['level']]['level_val'];
        $can_get_point = ceil(($level_val*$params['prices'])/10);
        $max_times_config = (int)$rule_info['point_rule']['4']['max_point'];
        //����������õ�Ҳֻ�Ǹ����õ����ֵ
        if($can_get_point > $max_times_config )
        {
            $can_get_point = $max_times_config;
        }
        
        return $can_get_point;
    }
    
    /**
     *  ��ȡ�û��ȼ���������
     * @param type $buyer_user_id
     * @return type
     */
    public function get_member_level_and_point_info($buyer_user_id)
    {
        $this->set_mall_member_level_and_point_tbl();
        return $this->find("user_id='$buyer_user_id'");
    }
    
    /**
     * �����Ա��־
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
     * ���»�����ȼ�
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
        
        //ȡ����ֵ
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
     * �����Ա�ȼ�����ֱ�
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
     * ��ȡ��Ա��ȼ����������
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
     * �����Ա����ֵ�����
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
     * ���»�Ա����ֵ��������
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
     * ��ȡ��Ӧ���Ե������ֵ
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
	 * ��Ա�ȼ�������б�
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
	 * ��Ա�ȼ��������־�б�
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
     * ��ȡ�û�����־
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
     * ѡ���ĸ�user_id ������log��
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
     * ͨ����̨�������»���
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
            //����ȡ����ֵ
            $point = abs($option_point);
        }
        //�ȸ��»���
        $rs = $this->update_member_level_and_point(array('point'=>$point,'user_id'=>$buyer_user_id),$option);
        
        if($rs)
        {
            //�������־
            $log_data = array();
            $log_data['user_id'] = $buyer_user_id;
            $log_data['point'] = $option_point;
            $log_data['add_time'] = time();
            $log_data['type_id'] = 10000;//10000����ϵͳ
            //$log_data['remark'] = $remark.",ͨ��ϵͳ���{$option_point}����,������id:{$option_user_id}";
            $log_data['remark'] = $remark?$remark."���{$option_point}����":"ͨ��ϵͳ���{$option_point}����,������id:{$option_user_id}";
            
            $log_id = $this->insert_member_log($log_data);
            if($log_id)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * ���»�Ա��ʼ��������
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
     * ���»�Ա�ɽ�����
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
     * ������ʱ��
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
     * ��ʱ�����ݵ�����ϵͳ
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
	 * ������ʱ����
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
     * ��ȡ��������
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
     * �жϽ�����ûǩ��
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
     * �û�����ǩ��������
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
     * �û�ǩ��������
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
     * ������ٵ���������
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
     * ���ö��е���ʱ�����Ѿ���
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
     * ��ȡ����ʵ�ĵȼ����л�����ٱʶ����ſ�������
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
