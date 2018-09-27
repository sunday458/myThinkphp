<?php
namespace Test2\Controller;
use Think\Controller;
/**
 * 会员等级与积分model
 * 
 */
class MemberController extends Controller
{
    CONST MODULO = 10; //取模被除数
    
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
    

    protected $member_tbl;

    //protected $member_log_tbl;
    
    protected $member_limit_tbl;
    
    public function __construct()
	{
		parent::__construct();
        $this->member_tbl = D('member');
        //$this->member_log_tbl = M('$log_table_name','yj_');
        $this->member_limit_tbl = M('member_level_and_point_limit_tbl','yj_');
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

    public function test()
    {
        $member_point_msg = C('MEMBER_POINT_MSG'); //积分行为信息
        $customer_id = 152;
        $log_id = $customer_id%($this::MODULO);
        var_dump($log_id);
        
        $member_model = D('member');
        $list = $member_model->select();
        $where['id'] =1;
        $data = $member_model->get_member_tbl_data($where);
        var_dump($data);
    }

    public function get_model_by_table_name($table_name)
    {
        $model = M();
        $table_model = $model->table($table_name);
        return $table_model?$table_model:false;
    }

    public function add_member_level_and_point_tbl()
    {
        $member_model = M('member_level_and_point_tbl','yj_');
        $find_data = $member_model->find();
        $data['name'] = 'ThinkPHP';
        $data['email'] = 'ThinkPHP@gmail.com';
        $add_data = $member_model->create($data);
        $add_id = $member_model->add($add_data);
        if($add_id)
        {
            echo $add_id;
        }
        else
        {
            echo 'error';
        }
        var_dump($add_data);
    }

    public function add_member_level_and_point_log_tbl($log_id,$add_data)
    {
        $table_name = 'member_level_and_point_log_'.$log_id.'_tbl';
        $member_log_model = M($table_name,'yj_');
        var_dump($member_log_model->select());
        $data['name'] = 'ThinkPHP';
        $data['email'] = 'ThinkPHP@gmail.com';
        $add_data = $$member_log_model->create($data);
        $add_id = $$member_log_model->add($add_data);
        if($add_id)
        {
            echo $add_id;
        }
        else
        {
            echo 'error';
        }
        var_dump($add_data);
    }

    public function update_member_level_and_point_tbl($id,$update_data)
    {
        $member_model = M('member_level_and_point_tbl','yj_');
        $data['name'] = 'ThinkPHP';
        $data['email'] = 'ThinkPHP@gmail.com';
        $update_data = $member_model->create($data);
        $update_id = $member_model->where(array('id' =>$id))->setField($update_data);
        if($update_id)
        {
            echo $update_id;
        }
        else
        {
            echo 'error';
        }
    }

    public function check_member_data_is_exist($where=array())
    {
        $member_tbl_model = $this->member_tbl;
        $find_data = $member_tbl_model->find_member_tbl_data($where);
        $customer_id = $where['customer_id'];
        $customer_id = 1;
        $bill_finish_num = $this->get_user_orders_num($customer_id);
        /*var_dump($bill_finish_num);
        var_dump(M()->getLastsql());exit();*/
        if(!$find_data)
        {   //初始化 某个用户积分表
            $add_data = array(
                'customer_id'=>$customer_id,
                'update_time'=>time(),
                'level'=>1,
                'plus_point_total'=>0,
                'minus_point_total'=>0,
                'bill_finish_num'=>$bill_finish_num,
            );
            $id = $member_tbl_model->add_member_tbl_data($add_data);
            return $id?$id:0;
        }
        return true;
    }

    public function deal_member_point($input_data)
    {
        header("Content-type: text/html; charset=utf-8"); 
        $result_status = 1;  // 事务是否提交标志 1提交 0回滚
        $retrun_data = array(
            'status'=>0,
            'msg'=>'用户积分表积分和Log更新失败！',
            //'data'=>array(),
        );
        //$input_data = I('input_data');
        if(!$input_data)
        {
            $retrun_data['msg']='参数错误,请检查参数格式!';
            return $retrun_data;
        }
        // 1.判断 用户的涉及积分变动的操作的类型
        $type_id = $input_data['type_id'];
        // todo 用户的基本信息获取
        //$customer_id = $input_data['customer_id'];
        $customer_id = 1;
        $log_num = $customer_id%($this::MODULO);
        // 2. 获取 积分行为 | 加减分 配置数据
        $member_point_info = C('MEMBER_POINT_MSG'); 
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT'); 

        // 3. todo type_id 类型的分支判断
        $key_arr = array_keys($member_point_info);
        if(!in_array($type_id,$key_arr ))
        {
            $retrun_data['msg']='非法type_id类型!';
            return $retrun_data;
        }

        // 4.根据 用户的操作行为 type_id  执行对应的加减分
        $point = $member_add_or_reduce_point_info[$type_id];
        //积分表不存在用户数据,添加用户积分数据,这块不能在事务里处理
        $member_tbl_model = $this->member_tbl;
        // 条件拼装
        $member_tbl_where['customer_id'] = $customer_id;

        $member_exist_res = $this->check_member_data_is_exist($member_tbl_where);   
        if(!$member_exist_res)
        {
            $retrun_data['msg']='用户积分记录首次创建,入库失败';
            return $retrun_data;
        }
        //var_dump(M()->getLastsql());exit();

        // 5. 积分记录LOG表 入库
        M()->startTrans(); // 事务开启
        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,'yj_');
        $log_add_data = array(
            'type_id' => $type_id, 
            'add_time' => time(), 
            'customer_id'=>$customer_id,
            'point' => $point, 
            'remark'=>$member_point_info[$type_id],
            'params'=>'',//参数信息
        );
        $log_id = $member_log_tbl_model->data($log_add_data)->add();
        $log_id&&$result_status?$result_status=1:$result_status=0;
        if(!$log_id)
        {
            $retrun_data['msg']=$log_table_name.'表入库失败';
        }
        // 用户积分表 对应加减积分更新
        $member_tbl_data = $member_tbl_model->find_member_tbl_data($member_tbl_where);
        if($type_id < 100)
        {
            $plus_point_total = $member_tbl_data['plus_point_total'] + $point;
            $update_data['plus_point_total'] = $plus_point_total;
            $update_data['update_time'] = time();
            $update_result = $member_tbl_model->update_member_tbl_data($member_tbl_where,$update_data);
            if(!$update_result)
            {
                $retrun_data['msg']='用户积分表积分更新失败！！';
            }
            $update_result&&$result_status?$result_status=1:$result_status=0;
        }
        else
        {
            $minus_point_total = $member_tbl_data['minus_point_total'] + $point;
            if($minus_point_total > $member_tbl_data['plus_point_total'])
            {
                $retrun_data['msg']='很抱歉,积分不足！';
                $result_status = 0;
            }
            else
            {
                $update_data['minus_point_total'] = $minus_point_total;
                $update_data['update_time'] = time();
                $update_result = $member_tbl_model->update_member_tbl_data($member_tbl_where,$update_data);
                if(!$update_result)
                {
                    $retrun_data['msg']='用户积分表积分更新失败！！';
                }
                $update_result&&$result_status?$result_status=1:$result_status=0;
            }  
        }
        //var_dump($update_data);
        //var_dump(M()->getLastsql());exit();
        
        // 6. todo 旧的用户积分表 积分 更新,非事务数据表，需要另外处理
        
        if($result_status)
        {
            M()->commit();   //事务提交
            $retrun_data['status'] = 1;
            $retrun_data['msg'] = '用户积分表积分和Log更新成功！';
        }
        else
        {
            M()->rollback(); //事务回滚
        }

        var_dump($retrun_data); //查看测试数据返回
        //return $retrun_data;
    }

    /**
     *  用户操作 类型分支判断
     *
     * @param      integer  $type_id  The type identifier
     *
     * @return     <type>   ( description_of_the_return_value )
     */
    public function check_type_id_branch($lv=1,$type_id=7)
    {
        /*if(!$type_id)
        {
            return array('status'=>0,'msg'=>'type_id为空值');
        }*/
        //获取用户基础数据
        $customer_id = 1;
        $lv_num = $this->check_level_branch($lv);
        $member_point_info = C('MEMBER_POINT_MSG'); 
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT'); 
        $log_num = $customer_id%($this::MODULO);
        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,'yj_');
        // todo 积分等级倍率
        if( $type_id == 1 ) 
        {
            //注册只能获取一次积分
            $where['type_id'] = 1;
            $where['customer_id'] = $customer_id;
            $member_data = $member_log_tbl_model->where($where)->find();
            if($member_data)
            {
                return array('status'=>0,'msg'=>'该用户已获取过注册积分！');
            }
        }
        elseif ( $type_id == 2 ) 
        {
            
        }
        elseif ( $type_id == 7 ) 
        {
            //签到        
        }
        $point = $member_add_or_reduce_point_info[$type_id]*$lv_num;
        return array('status'=>1,'msg'=>'获取用户成功数据','point'=>$point);
    }

    /**
     * 用户等级  分支判断
     *
     * @param      integer  $lv 用户积分等级
     *
     * @return     <type>   ( description_of_the_return_value )
     */
    public function check_level_branch($lv=1)
    {
        /*if(!$lv)
        {
            return array('status'=>0,'msg'=>'level为空值');
        }*/
    }

    /**
     * 获取客户订单完成交易数
     *
     * @param      <type>  $user_id  The user identifier
     *
     * @return     <type>  The user orders number.
     */
    public function get_user_orders_num($user_id,$status=5)
    {
        //todo 查询用户成功订单数目  todo 成功完成订单的次数确定
        $order_model = M('order','yj_');
        $where['c_id'] = $user_id;
        $where['status'] = $status;
        $num = $order_model->where($where)->count();
        return $num?$num:0;
    }

    /**
     * 签到
     * @return     <type>  ( description_of_the_return_value )
     */
    public function sign_in()
    {
        $time = time();
        //今天起止时间
        $now_start_time = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $now_end_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        //昨天起止时间
        $last_start_time = mktime(0,0,0,date("m",$time),date("d",$time)-1,date("Y",$time));
        $last_end_time = mktime(23,59,59,date("m",$time),date("d",$time)-1,date("Y",$time));
        $customer_id = 1;
        // todo  获取用户数据
        $where['customer_id'] = $customer_id; 
        $where['type_id'] = 7; 
        $log_num = $customer_id%($this::MODULO);
        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,'yj_');
        $last_time_member_data = $member_log_tbl_model->where($where)->order('add_time desc')->find();
        $last_sign_time = $last_time_member_data['add_time'];
        // TODO 每月是否重置？
        $now_month = date('m',$time);
        $last_month = date('m',$last_sign_time);

        // 检测用户是否在签到积分表 有数据
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl','yj_');
        $member_limit_data = $member_limit_tbl_model->where(array('customer_id' => $customer_id))->find();
        if(!$member_limit_data)
        {
            $member_data = $this->member_tbl->find_member_tbl_data(array('customer_id' => $customer_id));
            $total_point = $member_data['plus_point_total'] - $member_data['minus_point_total'];
            $add_data = array(
                'customer_id' =>$customer_id ,
                'type_id' =>$type_id ,
                'init_add_time' =>time() ,
                'add_time' =>time() ,
                'init_total_point' =>0 ,
                'continues' => 0 ,
                'total_point' => $total_point ,
            );
            $add_limit_id = $this->member_tbl->data($add_data)->add();
            if(!$add_limit_id)
            {
                return array('status' =>0 ,'msg'=>'用户签到积分表初始化失败！' );
            }
        }
        if( $now_month == $last_month )
        {
            // 当月,未跨月
            if($last_sign_time>=$last_start_time && $last_sign_time<=$last_end_time)
            {
                $month_days = date('t');
                // 昨天已签到,属于连续签到,次数+1
                $continues = $continues + 1;
                if($continues == 7)
                {
                    // 满足7天
                    $update_data = array(
                        'customer_id' =>$customer_id , 
                        'type_id' =>$type_id , 
                        'total_point' =>$total_point , 
                        'continues' =>$continues, 
                        'add_time' =>time() , 
                    );
                }
                elseif( $continues == 30 )
                {
                    //满足30天 todo是否满足全月签到？
                    $update_data = array(
                        'customer_id' =>$customer_id , 
                        'type_id' =>$type_id , 
                        'total_point' =>$total_point , 
                        'continues' =>$continues, 
                        'add_time' =>time() , 
                    );
                }
                $update_limit_id = $member_limit_tbl_model->where(array('customer_id' =>$customer_id))->save($update_data);
                if ($update_limit_id === false) 
                {
                    return array('status' =>0 ,'msg'=>'用户签到积分表更新失败！' );
                }
            }
            elseif($last_sign_time>=$now_start_time && $last_sign_time<=$now_end_time) 
            {
                // 今天已签到
                return array('status'=>0,'msg'=>'今天已签到！');
            }
            else
            {
                // 普通签到,用户签到次数重置 =1
            }
        }
        else
        {
            //跨月,普通签到,用户签到次数重置 =1
        }
        //var_dump($last_time_member_data);
        var_dump($now_month,$last_month);
    }

    public function update_member_limit_tbl()
    {
        //1. 更新yj_member_level_and_point_tbl表
        //2. 更新yj_member_level_and_point_log_tbl表
        //$update_result = $this->deal_member_point();
        //3. 更新yj_member_level_and_point_limit_tbl表,针对签到
        //$sign_in_data = $this->sign_in();
        $member_limit_where['customer_id'] = $customer_id;
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl','yj_');
        $find_data = $member_limit_tbl_model->where($member_limit_where)->find();
        if(!$find_data)
        {
            $this->check_member_level_and_point_limit_tbl($member_limit_where);
        }
        $update_data = array(
            'customer_id' =>$customer_id , 
            'type_id' =>$type_id , 
            'total_point' =>$total_point , 
            'continues' =>$continues, 
            'add_time' =>time() , 
        );
        $update_limit_id = $this->update_member_level_and_point_limit_tblwhere($member_limit_where,$update_data);
        if (!$update_limit_id) 
        {
            return array('status' =>0 ,'msg'=>'用户签到积分表更新失败！' );
        }

        return array('status' =>1 ,'msg'=>'用户签到积分表更新成功！' );
        
    }

    public function check_member_level_and_point_limit_tbl($where)
    {
        //$member_data = $this->member_tbl->find_member_tbl_data(array('customer_id' => $customer_id));
        $member_data = $this->member_tbl->find_member_tbl_data($where);
        $total_point = $member_data['plus_point_total'] - $member_data['minus_point_total'];
        $add_data = array(
            'customer_id' =>$customer_id ,
            'type_id' =>$type_id ,
            'init_add_time' =>time() ,
            'add_time' =>time() ,
            'init_total_point' =>0 ,
            'continues' => 0 ,
            'total_point' => $total_point ,
        );
        $add_limit_id = $this->member_limit_tbl->add_member_level_and_point_limit_tbl($add_data);
        if(!$add_limit_id)
        {
            return array('status' =>0 ,'msg'=>'用户签到积分表初始化失败！' );
        }
    }

    public function add_member_level_and_point_limit_tbl($add_data_array)
    {
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl','yj_');
        $id = $member_limit_tbl_model->data($add_data_array)->add();
        return $id?$id:0;    
    }

    public function update_member_level_and_point_limit_tbl($where,$update_data_array)
    {
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl','yj_');
        $result = $member_limit_tbl_model->where($where)->save($update_data_array);
        if($result!==false)
        {
            return true;
        }
        else
        {
            return false;
        }   
    }

    public function test_deal_data()
    {
        header('Content-type:text/html;charset:utf-8');
        $input_data['type_id'] = 101;
        $res = $this->deal_member_point($input_data);
        //var_dump($res);
        echo date('Y-m-d H:i:s');
    }


}
