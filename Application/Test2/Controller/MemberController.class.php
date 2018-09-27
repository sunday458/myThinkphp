<?php
namespace Test2\Controller;
use Think\Controller;
use PHPExcel;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Settings;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;

/**
 * 会员等级与积分model
 * 
 */
class MemberController extends Controller
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

    CONST MODULO = 10; //取模被除数
    CONST TABLE_PREFIX = 'mall_'; //表前缀

    protected $member_tbl;

    //protected $member_log_tbl;

    protected $member_limit_tbl;

    protected $user;

    protected $admin_array;

    public function __construct()
    {
        parent::__construct();
        $this->member_tbl = D('Member');
        //$this->member_log_tbl = M('$log_table_name','yj_');
        $this->member_limit_tbl = M('member_level_and_point_limit_tbl',self::TABLE_PREFIX);
        $this->get_login_user_info();
        $admin_array = array(1);
        $this->admin_array = $admin_array;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 检查某会员 积分表是否存在数据，无数据则添加
     * @param array $where
     * @return bool|int
     */
    public function check_member_data_is_exist($where,$add = array())
    {
        $member_tbl_model = $this->member_tbl;
        $find_data = $member_tbl_model->find_member_tbl_data($where);
        $user_id = $where['user_id'];
        $bill_finish_num = $this->get_user_orders_num($user_id); //获取交易成功单数
        $level = $find_data['level']?$find_data['level']:1; //获取会员等级
        /*var_dump($bill_finish_num);
        var_dump(M()->getLastsql());exit();*/
        if(!$find_data)
        {   //初始化 某个会员总积分表
            if($add)
            {
                $add_data = $add;
            }
            else
            {
                $add_data = array(
                    'user_id'=>$user_id,
                    'update_time'=>time(),
                    'level'=>$level,
                    'plus_point_total'=>0,
                    'minus_point_total'=>0,
                    'bill_finish_num'=>$bill_finish_num,
                );
            }
           
            $id = $member_tbl_model->add_member_tbl_data($add_data);
            //var_dump($member_tbl_model->getLastsql());exit;
            return $id?$id:0;
        }
        return true;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 处理会员相应type_id积分操作,更新积分  （积分更新入口）
     * @param $user_id 客户ID
     * @param $type_id 操作ID
     * @param $point   更新积分
     * @param int $orders_num 更新交易成功订单数
     * @param array $params  附加信息数组
     * @return array
     */
    public function update_member_point_by_type_id($user_id,$type_id,$point,$orders_num=0,$params=array())
    {
        //header("Content-type: text/html; charset=utf-8");
        $result_status = 1;  // 事务是否提交标志 1提交 0回滚
        $retrun_data = array(
            'status'=>0,
            'msg'=>'会员总积分表积分和Log更新失败！',
        );
        $type_id = (int)$type_id;
        $user_id = (int)$user_id;
        $log_num = $user_id%($this::MODULO);
        // 获取 积分行为 | 加减分 配置数据
        $member_point_info = C('MEMBER_POINT_MSG');
        //$member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');

        $member_tbl_model = $this->member_tbl;

        // 条件拼装
        $member_tbl_where['user_id'] = $user_id;
        $params = $params?json_encode($params):'';

        //会员总积分表不存在会员数据,添加会员积分数据,这块不在事务里处理
        $member_exist_res = $this->check_member_data_is_exist($member_tbl_where);
        if(!$member_exist_res)
        {
            $retrun_data['msg']='会员总积分表记录首次创建,入库失败';
        }
        //var_dump(M()->getLastsql());exit();

        // 3. 积分记录LOG表 入库
        M()->startTrans(); // 事务开启

        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,$this::TABLE_PREFIX);
        $log_add_data = array(
            'type_id' => $type_id,
            'add_time' => time(),
            'user_id'=>$user_id,
            'point' => $point?$point:0,
            'remark'=>$member_point_info[$type_id],
            'params'=>$params,//参数信息
        );
        //var_dump($log_add_data);exit;
        $log_id = $member_log_tbl_model->data($log_add_data)->add();
        //echo M()->getLastsql();exit();
        $log_id&&$result_status?$result_status=1:$result_status=0;
        if(!$log_id)
        {
            $retrun_data['msg']=$log_table_name.'表入库失败';
        }

        // 会员积分表 对应加减积分更新
        $member_tbl_data = $member_tbl_model->find_member_tbl_data($member_tbl_where);
        if($type_id < 100)  //小于100 加分操作
        {
            //订单数目存在，更新订单记录
            if( $orders_num )
            {
                $update_data['bill_finish_num'] = $member_tbl_data['bill_finish_num'] + $orders_num;
            }
            $plus_point_total = $member_tbl_data['plus_point_total'] + $point;
            //TODO LV会员是否积分上限 或者 每日积分上限
            $update_data['plus_point_total'] = $plus_point_total;
            $update_data['update_time'] = time();
            $update_result = $member_tbl_model->update_member_tbl_data($member_tbl_where,$update_data);
            if(!$update_result)
            {
                $retrun_data['msg']='会员总积分表积分更新失败！！';
            }
            $update_result&&$result_status?$result_status=1:$result_status=0;
        }
        elseif($type_id > 100 && $type_id <1000 )   //大于100 且小于1000 减分操作
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
                    $retrun_data['msg']='会员总积分表积分更新失败！！';
                }
                $update_result&&$result_status?$result_status=1:$result_status=0;
            }
        }
        elseif($type_id == 1000)
        {
            //管理员级别 无条件 强制修改用户积分
            $admin_array = $this->admin_array;
            $user_info = $this->user;
            if(!in_array($user_info['id'],$admin_array))
            {
                $retrun_data['msg'] = '1000以上的操作,你无对应权限！';
                return $retrun_data;
            }
            if($point>0)
            {
                //正数为 加分操作
                $plus_point_total = $member_tbl_data['plus_point_total'] + $point;
                //TODO LV会员是否积分上限 或者 每日积分上限
                $update_data['plus_point_total'] = $plus_point_total;
                $update_data['update_time'] = time();
                $update_result = $member_tbl_model->update_member_tbl_data($member_tbl_where,$update_data);
                if(!$update_result)
                {
                    $retrun_data['msg']='强制增加积分-会员总积分表积分更新失败';
                }
                $update_result&&$result_status?$result_status=1:$result_status=0;
            }
            else
            {
                //负数为 扣分操作
                $minus_point_total = $member_tbl_data['minus_point_total'] + $point;
                $update_data['minus_point_total'] = $minus_point_total;
                $update_data['update_time'] = time();
                $update_result = $member_tbl_model->update_member_tbl_data($member_tbl_where,$update_data);
                if(!$update_result)
                {
                    $retrun_data['msg']='强制扣除积分-会员总积分表积分更新失败';
                }
                $update_result&&$result_status?$result_status=1:$result_status=0;
            }

        }
        //var_dump($update_data);
        //var_dump(M()->getLastsql());exit();

        if($result_status)
        {
            M()->commit();   //事务提交
            $retrun_data['status'] = 1;
            $retrun_data['msg'] = '会员总积分表积分和Log更新成功！';
            if($type_id == 1000)
            {
                $retrun_data['msg'] = '强制更改积分-会员总积分表积分更新成功！！';
            }
        }
        else
        {
            M()->rollback(); //事务回滚
            /*$retrun_data['status'] = 0;
            $retrun_data['msg'] = '会员总积分表积分和Log更新失败！！！';*/
        }

        //var_dump($retrun_data); //查看测试数据返回
        return $retrun_data;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 会员操作 类型分支判断 (主入口)
     * @param int $lv
     * @param int $type_id
     * @return array
     */
    public function update_member_data_by_user_id_and_type_id($user_id,$type_id,$data=array())
    {
        if(!$type_id || !$user_id )
        {
            return array('status'=>0,'msg'=>'user_id或type_id为空值');
        }
        $retrun_data['status'] = 0;
        $retrun_data['msg'] = '非法type_id类型!';
        $member_point_info = C('MEMBER_POINT_MSG');
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');
        //type_id 类型判断是否合法
        $key_arr = array_keys($member_point_info);
        if(!in_array($type_id,$key_arr))
        {
            return $retrun_data;
        }

        $user_id = $user_id;
        //会员等级判断处理
        $member_level_data = $this->check_member_level_branch($user_id);
        $lv_num = $member_level_data['multiple'];
        $point = $member_add_or_reduce_point_info[$type_id];

        $log_num = $user_id%($this::MODULO);
        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,$this::TABLE_PREFIX);
        if( $type_id == 1 || $type_id == 2 || $type_id == 3 )
        {
            //签到 | 7天签到 | 30天签到
            $sign_result = $this->sign_in($user_id); //签到
            if(!$sign_result['status'])
            {
                $retrun_data['msg'] = $sign_result['msg'];
                return $retrun_data;
            }

            $member_limit_tbl_data = $this->member_limit_tbl->where(array('user_id'=>$user_id))->order('add_time desc')->find(); //查询会员积分签到表最后1次的签到数据
            //TODO 积分规则确定？
            $point = $member_limit_tbl_data['init_total_point'];
            $deal_result = $this->update_member_point_by_type_id($user_id,$type_id,$point);
            if(!$deal_result['status'])
            {
                $retrun_data['msg'] = $deal_result['msg'];
                return $retrun_data;
            }

            $retrun_data['status'] = 1;
            $retrun_data['msg'] = '会员签到成功！';
        }
        elseif ( $type_id == 4)
        {
            //消费成功，+积分 更新交易成功数
            $money = $data['money'];
            $goods_id = $data['goods_id'];
            // TODO 交易单数确定？
            $orders_num = $data['orders_num']?$data['orders_num']:1;
            $orders_code = $data['orders_code']?$data['orders_code']:'';
            $member_level_up_data = $this->member_level_up_by_money($user_id,$money);
            $params = array(
                'goods_id'  => $goods_id,
                'money'     => $money,
                'orders_code'=> $orders_code,
                'orders_num'=> $orders_num,
            );
            $update_member_result = $this->update_member_point_by_type_id($user_id,$type_id,$member_level_up_data['point'],$orders_num,$params);
            if(!$update_member_result['status'])
            {
                $retrun_data['msg'] = $update_member_result['msg'];
                return $retrun_data;
            }
            else
            {
                //等级更新
                $member_tbl = $this->member_tbl;
                $update_result = $member_tbl->update_member_tbl_data(array('user_id'=>$user_id),array('level'=>$member_level_up_data['next_lv']));
                $retrun_data['status'] = 1;
                $retrun_data['msg'] = $update_member_result['msg'];
                return $retrun_data;
            }
        }
        elseif( $type_id == 101 )
        {
            //兑换
            $point = $data['point'];
            $user_info = $this->user;
            $add_user_info = array('add_user_id'=>$user_info['obj_id']);
            $deal_result = $this->update_member_point_by_type_id($user_id,$type_id,$point,0,$add_user_info);
            if(!$deal_result['status'])
            {
                $retrun_data['msg'] = $deal_result['msg'];
                return $retrun_data;
            }
            else
            {
                $retrun_data['status'] = 1;
                $retrun_data['msg'] = '会员兑换成功';
            }
        }
        elseif( $type_id == 1000 )
        {
            //具有强制加减分的人员才能执行
            $admin_array = $this->admin_array;
            $user_info = $this->user;
            if(!in_array($user_info['id'],$admin_array))
            {
                $retrun_data['msg'] = '1000以上的操作,你无对应权限！';
                return $retrun_data;
            }
            $point = $data['point'];
            $deal_result = $this->update_member_point_by_type_id($user_id,$type_id,$point,0,$user_info);
            if(!$deal_result['status'])
            {
                $retrun_data['msg'] = $deal_result['msg'];
                return $retrun_data;
            }

            $retrun_data['status'] = 1;
            $retrun_data['msg'] = '系统操作成功';

        }

        return $retrun_data;
        //return array('status'=>1,'msg'=>'获取会员成功数据','point'=>$point);
    }


    /**
     * @Author: sunew
     * @Date: 2017-12-7
     * @Description: 会员等级  判断
     * @param $user_id
     * @param int $lv
     */
    public function check_member_level_branch($user_id,$lv=1)
    {
        /*if(!$lv)
        {
            return array('status'=>0,'msg'=>'level为空值');
        }*/
        $return_data = array(
            'status'=>0,
            'msg'=>'无此会员积分记录！',
            'member_data'=>array(),
        );
        //会员等级和消费有关，与积分无关
        $where['user_id'] = $user_id;
        $member_data = $this->member_tbl->find_member_tbl_data($where);
        $member_level = $member_data['level'];
        if(!$member_data)
        {
           return $return_data;
        }
        $member_data = array(
            'user_id'=>$user_id,
            'level'=>$member_level,
        );
        /*
        累计消费满600元可成为普通颜蜜，并获得新人礼包（100积分+起泡器一个），且在以后购买当中享受9.8折会员优惠，
        每消费1元得1积分
        累计消费满2000元，升级为白银会员，享受9.6折优惠，每消费1元得1积分
        累计消费满6000元，升级为黄金会员，享受9.4折优惠，每消费1元得1积分
        累计消费满12000元，升级为铂金会员，享受9.2折优惠，每消费1元得2积分
        累计消费满20000元，升级为钻石会员，享受9折优惠，每消费1元得2积分
        */
        $discount  =C('LEVEL_DISCOUNT'); //会员折扣配置
        if($member_level == 1)
        {
            // 非会员
            $member_data['discount'] = $discount[1];  // 折扣
            $member_data['multiple'] = 1;  // 消费获得的积分倍率
        }
        elseif( $member_level ==2 )
        {
            //普通会员
            $member_data['discount'] = $discount[2];  // 折扣
            $member_data['multiple'] = 1;  // 消费获得的积分倍率
        }
        elseif( $member_level ==3 )
        {
            //白金会员
            $member_data['discount'] = $discount[3];
            $member_data['multiple'] = 1;  // 消费获得的积分倍率
        }
        elseif( $member_level ==4 )
        {
            //黄金会员
            $member_data['discount'] = $discount[4];
            $member_data['multiple'] = 1;  // 消费获得的积分倍率
        }
        elseif( $member_level ==5 )
        {
            //铂金会员
            $member_data['discount'] = $discount[5];
            $member_data['multiple'] = 2;  // 消费获得的积分倍率
        }
        elseif( $member_level ==6 )
        {
            //钻石会员
            $member_data['discount'] = $discount[6];
            $member_data['multiple'] = 2;  // 消费获得的积分倍率
        }
        $return_data['member_data'] = $member_data;
        return $return_data;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-8
     * @Description: 会员消费 升级
     * @param $user_id
     * @param $money
     */
    public function member_level_up_by_money($user_id,$money)
    {
        //查询已消费金额
        $last_money = $this->get_user_order_total_money($user_id);
        $total_money= $last_money + $money; //总消费金额

        //查询原等级
        $where['user_id'] = $user_id;
        $member_data = $this->member_tbl->find_member_tbl_data($where);
        $now_member_lv = $member_data['level'];

        //升级的等级和积分
        $point = $this->money_to_point($now_member_lv,$money);
        $next_member_lv = $this->money_to_level($total_money);
        return array('now_lv'=>$now_member_lv,'next_lv'=>$next_member_lv,'point'=>$point['point']);
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-8
     * @Description: 消费金额 换算 积分
     * @param $lv
     * @param $money
     * @return int
     */
    public function money_to_point($lv,$money)
    {
        /*
         *'LEVEL_POINT' => array(
                // 会员等级 => 消费最低金额
                1 => 0 ,
                2 => 600 ,
                3 => 2000 ,
                4 => 6000 ,
                5 => 12000 ,
                6 => 20000 ,
           ),
         */
        $money = (integer)$money;
        $level_ration = C('LEVEL_RATIO');//会员等级 => 积分倍率
        if ($lv == 1)
        {
            $point = 0 ;
        }
        elseif( $lv>1 && $lv<4 )
        {
            $point = $money * $level_ration[3];
        }
        elseif($lv>=4 && $lv<6)
        {
            $point = $money * $level_ration[5];
        }
        elseif( $lv>=6 )
        {
            $point = $money * $level_ration[6];
        }

        return array('point'=>$point);
    }

    /**
     * @Author: sunew
     * @Date: 2017-11-9
     * @Description: 消费金额 转换 等级
     * @param $money
     * @return int
     */
    public function money_to_level($money)
    {
        $level_point = C('LEVEL_POINT');
        if( $money<$level_point[2] && $money>=0 )
        {
            $next_lv = 1;
        }
        elseif($money>=$level_point[2] && $money<$level_point[3])
        {
            $next_lv = 2;
        }
        elseif($money>=$level_point[3] && $money<$level_point[4])
        {
            $next_lv = 3;
        }
        elseif($money>=$level_point[4] && $money<$level_point[5])
        {
            $next_lv = 4;
        }
        elseif($money>=$level_point[5] && $money<$level_point[6])
        {
            $next_lv = 5;
        }
        elseif($money>=$level_point[6])
        {
            $next_lv = 6;
        }

        return $next_lv;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 获取客户订单完成交易数
     * @param $user_id
     * @param int $status
     * @return int
     */
    public function get_user_orders_num($user_id,$status=9)
    {
        //todo 成功完成订单的次数确定？
        $order_model = M('orders',null,'DB_CONFIG3');
        //$order_model = M('order','yj_');
        //$order_model = D('order');
        $where['c_id'] = $user_id;
        $where['status'] = $status;
        $num = $order_model->where($where)->count();
        //var_dump($order_model->getLastsql());
        return $num?$num:0;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-8
     * @Description: 获取客户消费总金额
     * @param $user_id
     */
    public function get_user_order_total_money($user_id)
    {
        //TODO 哪些状态的订单交易 金额算入消费积分金额？
        $status_array = array(9);
        $order_model = M('orders',null,'DB_CONFIG3');
        //$order_model = M('order','yj_');
        //$order_model = D('order');
        $where['c_id'] = $user_id;
        $where['status'] = array('IN',$status_array);
        $money = $order_model->where($where)->sum('payed_price');
        //var_dump($order_model->getLastsql());
        return $money?$money:0;
    }

    /**
     * 签到
     */
    public function sign_in($user_id=0)
    {
        $time = time();
        //今天起止时间
        $now_start_time = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $now_end_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        //昨天起止时间
        $last_start_time = mktime(0,0,0,date("m",$time),date("d",$time)-1,date("Y",$time));
        $last_end_time = mktime(23,59,59,date("m",$time),date("d",$time)-1,date("Y",$time));

        $member_point_info = C('MEMBER_POINT_MSG');
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');

        if(!$user_id)
        {
            $user_info = $this->user;
            //$where['user_id'] = $user_id;
            $user_id = $user_info['id'];
        }
        else
        {
            $user_id = $user_id;
        }

        $where['user_id'] = $user_id;
        //$where['type_id'] = $type_id;
        $log_num = $user_id%($this::MODULO);
        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
        $member_log_tbl_model = M($log_table_name,$this::TABLE_PREFIX);
        //$last_time_member_data = $member_log_tbl_model->where($where)->order('add_time desc')->find();
        $member_limit_tbl_data = $this->member_limit_tbl->where($where)->order('add_time desc')->find();
        //$last_sign_time = $last_time_member_data['add_time'];
        $last_sign_time = $member_limit_tbl_data['add_time'];
        // TODO 每月是否重置？
        $now_month = date('m',$time);
        $last_month = date('m',$last_sign_time);

        // 检测某会员是否在签到积分表有数据？无则创建
        /*$member_limit_exist_res = $this->check_member_level_and_point_limit_tbl(array('user_id' => $user_id));
        if(!$member_limit_exist_res['status'])
        {
            return array('status' =>0 ,'msg'=>$member_limit_exist_res['msg'] );
        }*/
        //$member_limit_data = $member_limit_exist_res['data'];
        $result_status = 1; // 事务提交标志 1提交 0回滚
        $return_data = array('status' =>0 ,'msg'=>'会员签到积分表|LOG表|总分表配置数据成功！' );
        $init_total_point = 0; //会员LOG记录入库的总积分

        M()->startTrans(); // 事务开始
        $add_data = array(
            'user_id' =>$user_id  ,
            //'type_id' =>7 ,
            'add_time' =>time() ,
            'init_add_time' =>time() ,
            'init_total_point' =>0 ,
            'init_continues' =>0 ,
            //'continues' => 0 ,
            //'total_point' => $total_point ,
            //'total_point' => 0 ,
        );
        if(!$member_limit_tbl_data)  //首次签到
        {
            //首次签到 = 普通签到
            //TODO 累计分数的判断处理
            $add_data['continues'] = 1;
            $add_data['type_id'] = $member_point_info[4];
            $add_data['total_point'] = $member_add_or_reduce_point_info[7]; // type_id =7普通签到
            $init_total_point =  $member_add_or_reduce_point_info[7];
            $add_data['init_continues'] = 1;
            $add_data['init_total_point'] = $init_total_point;
            $add_data['init_add_time'] = time();
        }
        else
        {
            if( $now_month == $last_month ) // 当月,未跨月
            {
                if($last_sign_time>=$last_start_time && $last_sign_time<=$last_end_time)
                {
                    $month_days = date('t');
                    // 昨天已签到,属于连续签到,次数+1
                    $continues = $member_limit_tbl_data['continues'] + 1;
                    if($continues == 7)
                    {
                        // 满足7天
                        /*$update_data = array(
                            'user_id' =>$user_id ,
                            'type_id' =>8 , //连续7天
                            'total_point' =>$member_limit_tbl_data['total_point'] ,
                            'continues' =>$continues,
                            'add_time' =>time() ,
                        );*/
                        //TODO 累计分数的判断处理
                        $add_data['continues'] = $continues;
                        $add_data['init_continues'] = $continues;
                        $add_data['total_point'] = $member_add_or_reduce_point_info[7]; // type_id =8
                        $init_total_point =  $member_add_or_reduce_point_info[7]+$member_limit_tbl_data['init_total_point'];
                        $add_data['init_total_point'] = $init_total_point;
                    }
                    /*elseif( $continues == 30 )
                    {
                        //满足30天 todo 是否满足全月签到？
                        //TODO 累计分数的判断处理
                        $add_data['continues'] = $continues;
                        $add_data['init_continues'] = $continues;
                        $init_total_point = $member_add_or_reduce_point_info[9]+$member_limit_tbl_data['init_total_point'];
                        $add_data['init_total_point'] = $init_total_point;
                        $add_data['total_point'] = $member_add_or_reduce_point_info[9];
                    }*/
                    /*$update_limit_id = $this->update_member_level_and_point_limit_tbl(array('user_id' =>$user_id),$update_data);
                    if (!$update_limit_id)
                    {
                        return array('status' =>0 ,'msg'=>'会员签到积分表更新失败！' );
                    }*/
                }
                elseif($last_sign_time>=$now_start_time && $last_sign_time<=$now_end_time)
                {
                    // 今天已签到
                    return $return_data = array('status'=>0,'msg'=>'今天已签到！');
                }
            }
            else
            {
                //跨月,普通签到,会员签到次数重置 =1
                $add_data['continues'] = 1;
                $add_data['init_continues'] = 1;
                $add_data['init_total_point'] = $member_add_or_reduce_point_info[7];
                $add_data['init_add_time'] = time();
                $add_data['total_point'] = $member_add_or_reduce_point_info[7];
                $add_data['add_time'] = time();
            }
        }
        $add_limit_id = $this->add_member_level_and_point_limit_tbl($add_data);
        /*if(!$add_limit_id)
        {
            $return_data = array('status' =>0 ,'msg'=>'会员签到积分表添加记录失败！' );
        }*/

        $add_limit_id&&$result_status?$result_status=1:$result_status=0;

        if($result_status)
        {
            M()->commit(); //事务提交
            $return_data =  array('status' =>1 ,'msg'=>'会员签到积分表配置数据成功！' );
        }
        else
        {
            M()->rollback(); //事务回滚
            $return_data['msg']='会员签到积分表配置数据失败！！' ;
        }
        //var_dump($last_time_member_data);
        //var_dump($now_month,$last_month);
        //var_dump($return_data);
        return $return_data;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 检测 会员签到积分表 数据是否存在，无则创建
     * @param $where
     * @return array
     */
    public function check_member_level_and_point_limit_tbl($where)
    {
        $member_limit_tbl_data = $this->member_limit_tbl->where($where)->order('add_time desc')->find();
        if(!$member_limit_tbl_data)
        {
            //$member_data = $this->member_tbl->find_member_tbl_data($where);
            //$total_point = $member_data['plus_point_total'] - $member_data['minus_point_total'];
            $add_data = array(
                'user_id' =>$where['user_id'] ,
                'type_id' =>7 ,
                'add_time' =>time() ,
                'init_add_time' =>time() ,
                'init_total_point' =>0 ,
                'init_continues' =>0 ,
                'continues' => 0 ,
                //'total_point' => $total_point ,
                'total_point' => 0 ,
            );
            $add_limit_id = $this->add_member_level_and_point_limit_tbl($add_data);
            if(!$add_limit_id)
            {
                return array('status' =>0 ,'msg'=>'会员签到积分表初始化失败！' );
            }
            $list = $this->member_limit_tbl->where(array('id'=>$add_limit_id))->order('add_time desc')->find();
            return array('status' =>1 ,'msg'=>'会员签到积分表初始化成功！','data'=>$list );
        }
        return array('status' =>1 ,'msg'=>'会员签到积分表初始化成功！','data'=>$member_limit_tbl_data );
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 添加 会员签到积分表 数据
     * @param $add_data_array
     * @return int|mixed
     */
    public function add_member_level_and_point_limit_tbl($add_data_array)
    {
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl',$this::TABLE_PREFIX);
        $id = $member_limit_tbl_model->data($add_data_array)->add();
        return $id?$id:0;
    }

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 更新 会员签到积分表 数据
     * @param $where
     * @param $update_data_array
     * @return bool
     */
    public function update_member_level_and_point_limit_tbl($where,$update_data_array)
    {
        $member_limit_tbl_model = M('member_level_and_point_limit_tbl',$this::TABLE_PREFIX);
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

    /**
     * @Author: sunew
     * @Date: 2017-12-6
     * @Description: 获取登录会员基本数据
     */
    protected function get_login_user_info()
    {
        //$uid = $this->_check_login();
        /*if(!$uid)
        {
            //$this->redirect('Login/login');
            $data = array(
                'status'=>0,
                'msg'=>'请先登录',
            );
            return $this->ajaxReturn($data);
        }
        //获取会员信息
        $opt['where'] = array('id' =>$uid, 'status' =>1, 'flag' =>1);
        $user = D('User')->get_list_user($opt, 1);*/
        $user = array('id' => 2 );
        $this->user = $user;
    }

    /**
     * 判断登录
     */
    protected function _check_login()
    {
        return session('ext_uid');
    }

    public function test_deal_data()
    {
        header('Content-type:text/html;charset=utf-8');
        $user_id = 3;
        $type_id = 4;
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');
        $point = $member_add_or_reduce_point_info[$type_id];
        $res = $this->update_member_point_by_type_id($user_id,$type_id,$point);
        var_dump($res);
        echo date('Y-m-d H:i:s');
    }

    public function test_member_data()
    {
        header('Content-type:text/html;charset=utf-8');
        $user_id = 3;
        $type_id = 4;
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');
        $point = $member_add_or_reduce_point_info[$type_id];
        $res = $this->update_member_data_by_user_id_and_type_id($user_id,$type_id);
        echo '<br>测试<br>';
        var_dump($res);
        echo date('Y-m-d H:i:s').' '.time();
    }

    public function test()
    {
        /*$content = date("Y-m-d H:i:s");
        $file1 = __ROOT__.'/newfile.txt';
        var_dump($file1);
        $fp = fopen($file1, 'w');
        var_dump($fp);
        fwrite($fp, $content);
        fclose($fp);
        file_put_contents($file1, $content, FILE_APPEND);
        echo 1111;*/
        $lv = 1;
        $money = 700;
        var_dump($this->money_to_point($lv,$money));

    }

    public function test_initialization()
    {
        ini_set('max_execution_time', '0'); //设置超时
        header('Content-type:text/html;charset=utf-8');
        //$user_model = M('users',NULL,'DB_CONFIG3');
        $customer_model = M('customer',NULL,'DB_CONFIG3');
        $orders_model   = M('orders',NULL,'DB_CONFIG3');
        $where['status'] = array('IN','6,9');
        //$user_list = $customer_model->group('id')->getField('id',true);
        $user_list = $orders_model->where($where)->group('c_id')->getField('c_id',true);
        //$user_list = $customer_model->field('id')->group('id')->select();
        //var_dump(count($user_list));
        //var_dump($customer_model->getLastsql());exit();
        //var_dump($orders_model->getLastsql());exit();
        echo '开始<br/>';
        //$user_list = array(7937);
        $c = 1;//计数器
        if($user_list)
        {
            echo count($user_list).'<br/>';
            G('begin');
            foreach ($user_list as $v) {
                $v = (int)$v;
                if($v)
                {
                    $money = $this->get_user_order_total_money($v);
                    $bill_finish_num = $this->get_user_orders_num($v); //获取交易成功单数
                    $point = $this->money_to_point(1,$money); // 会员积分  
                    $level = $this->money_to_level($money);
                    //$customer_model->where("id=$v")->setField('level',$level);
                    //var_dump($customer_model->getLastsql());exit();
                    $add_data = array(
                        'user_id'=>$v,
                        'update_time'=>time(),
                        'level'=>$level,
                        'plus_point_total'=>$point,
                        'minus_point_total'=>0,
                        'bill_finish_num'=>$bill_finish_num,
                    );                                               
                    //var_dump($add_data);exit();
                    $add_id = $this->check_member_data_is_exist(array('user_id'=>$v),$add_data);
                    $add_data =array();
                    if($add_id)
                    {
                        $user_id = $v;
                        $log_num = $user_id%($this::MODULO);
                        $log_table_name = 'member_level_and_point_log_'.$log_num.'_tbl';
                        $member_log_tbl_model = M($log_table_name,$this::TABLE_PREFIX);
                        $add_log_data = array(
                            'type_id'  =>10001,
                            'add_time' =>time(),
                            'user_id' =>$user_id,
                            'point' =>$point,
                            'remark' =>'初始化会员积分',
                            'params' =>'初始化会员积分!',
                        );
                        $log_id = $member_log_tbl_model->data($add_log_data)->add();
                        if($log_id)
                        {
                            $customer_model->where("id=$v")->setField('level',$level);
                            //echo $customer_model->getLastsql();
                            /*$c++;
                            if($c == 500)
                            {
                                G('end');
                                echo G('begin','end').'s<br/>';
                                echo '结束<br/>';
                                exit();
                            }*/
                        }
                    }
                }
            }    
            G('end');
            echo G('begin','end').'s<br/>';
            echo '结束<br/>';
            /*开始
            2093
            97.6236s
            结束*/
        }
    }

    public function test_customer_initialization($user_id,$level=0)
    {
        $level?$level=$level:$level=1;
        $customer_model = M('customer',null);
        $update_data = array('level'=>$level);
        $where['id'] = $user_id;
        $result = $customer_model->where($where)->save($update_data);
        if($result!==false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function add_member_opp($user_id=1,$type_id=1,$validity_time=0)
    {
        $mall_exchange_opp_tbl = M('exchange_opp_tbl','mall_');
        $add_data = array(
            'user_id'=>$user_id,
            'type_id'=>$type_id,
            'status'=>$type_id==1?1:2, //默认1签到;2生日
            'validity_time'=>$validity_time?$validity_time:(time()+3600*24*30),
            'ex_time'=>'',
            'add_time'=>time(),
        );
        $add_id = $mall_exchange_opp_tbl->data($add_data)->add();
        return $add_id?$add_id:0;
    }

    public function update_member_opp($value='')
    {
        $mall_exchange_opp_tbl = M('exchange_opp_tbl','mall_');
    }

    public function test2()
    {
        header('Content-type:text/html;charset=utf-8');
        //$mall_db_config= C('MALL_DB_CONFIG');
        $mall_db_config= C('DB_CONFIG4');
        var_dump($mall_db_config);
        $mall_user_tbl = M('user_tbl','mall_',$mall_db_config);
        $member_id = $mall_user_tbl->where(array('customer_id'=>1))->getField('id');
        var_dump($member_id);
        echo M()->getLastsql();
        exit();
        $type_id = 10002;
        $retrun_data['status'] = 0;
        $retrun_data['msg'] = '非法type_id类型---!';
        $member_point_info = C('MEMBER_POINT_MSG');
        $member_add_or_reduce_point_info = C('MEMBER_ADD_OR_REDUCE_POINT');
        //type_id 类型判断是否合法
        $key_arr = array_keys($member_point_info);
        var_dump($key_arr);
        echo '<br/>';echo '<br/>';
        var_dump(in_array($type_id,$key_arr));
        echo '<br/>';echo '<br/>';
        if(!in_array($type_id,$key_arr))
        {
            var_dump($retrun_data);
        }

        //$mall_customer_tbl = M('customer_tbl','mall_','DB_CONFIG4');
        //$dsn = 'mysql://sew:sew865419!@120.76.240.241/mall_db';
        $db_config = C('MALL_DB_CONFIG');
        $mall_customer_tbl = M('customer_tbl','mall_',$db_config);;
        $customer_info = $mall_customer_tbl->field('id,name,phone_first,is_associated')->find();
        if(!$customer_info['is_associated'])
        {
            //关联user表,不做修改
        }
        var_dump($customer_info);
        $type_id_array = array('goods_id'=>306,'money'=>600,'order_code'=>'cx1ys27364');
        //61003 67693
        $result = $this->update_member_data_by_user_id_and_type_id(64283,4,$type_id_array);
        echo '<br/>';echo '<br/>';echo '<br/>';
        var_dump($result);
    }

    public function test_user()
    {
        //var_dump(Loader::import('org.Util.PHPExcel'));exit;
        //require_once ROOT_PATH.'/extend/UploadFile.php';
        header('Content-type:text/html;charset=utf-8');
        import("ORG.Util.PHPExcel");
        $file_name = 'D:/001.xls';
        $objPHPExcel = PHPExcel_IOFactory::load($file_name);

        $sheet = $objPHPExcel->getSheet(0);

        $sheetData = $objPHPExcel->getSheet(0)->toArray(null, true, true, true);

        array_shift($sheetData);

        $employee_csv_tbl = M('employee_csv',null);
        $str_val_array = array('id','code','name','dep_name','position_name','entry_date','quit_date','education','specialty','phone','id_card','birthday','sex','native_place','nation','qq','email','introduction','now_living_address','company_ownership','contract_ownership','correction_time','quit_type','quit_reason','bank_card_number','bank_card_type','fixed_time');
        foreach ($sheetData as $k => $v) 
        {
            $user_data['id'] = $v['A'];
            $user_data['code'] = $v['B'];
            $user_data['name'] = $v['C'];
            $user_data['entry_date'] = $v['F'];
            $user_data['phone'] = $v['J'];
            $user_data['qq'] = $v['P'];
            $user_data['email'] = $v['Q'];
            $user_data['bank_card_number'] = $v['Y'];
            $user_data['bank_card_type'] = $v['Z'];
            //$user_data['is_change'] = 0;

            $e_add_data[] = $user_data;
        }
        //var_dump($e_add_data);
        $shipping_cost_arr = array();

        $add_result = $employee_csv_tbl->addAll($e_add_data);
        var_dump($add_result);exit();

        $sc_data = array();

        /*foreach($sheetData as $key => $val)
        {

            if(trim($val['A']) && trim($val['B']) && trim($val['D']))
            {

                $opt['where'] = array('d_code' =>trim($val['A']));

                $res = $model->is_exist($opt);

                if($res)
                {

                    $opt['data'] = array('id' =>$res['id'], 'postage_money' =>trim($val['B']), 'service_money' =>trim($val['C']), 'need_shipping_fee' =>trim($val['D']), 'is_checked' =>1);

                    $sid = $model->save($opt['data']);

                    if($sid !== false)
                    {

                        $sc_data = array(

                            'o_id' =>$res['id'],

                            'd_code' =>trim($val['A']),

                            'batch_no' =>$name,

                            'msg' =>"订单编号: {$res['code']}，物流编号：{$val['A']}，代收货款金额: {$val['B']}，服务费：{$val['C']}， 应付金额：{$val['D']}.",

                            'cdate' =>date('Y-m-d H:i:s'),

                            'flag' =>1,

                            'status' =>1,

                        );

                    }
                    else
                    {
                        $sc_data = array(

                            'o_id' =>$res['id'],

                            'd_code' =>trim($val['A']),

                            'batch_no' =>$name,

                            'msg' =>"更新订单但信息失败！ 订单编号: {$res['code']}，物流编号：{$val['A']}，代收货款金额: {$val['B']}，服务费：{$val['C']}， 应付金额：{$val['D']}.",

                            'cdate' =>date('Y-m-d H:i:s'),

                            'flag' =>1,

                            'status' =>0,

                        );
                    }
                }
                else
                {

                    $sc_data = array(

                        'o_id' =>'',

                        'd_code' =>trim($val['A']),

                        'batch_no' =>$name,

                        'msg' =>"无该订单数据，物流编号：{$val['A']}.",

                        'cdate' =>date('Y-m-d H:i:s'),

                        'flag' =>1,

                        'status' =>0,

                    );

                }

            }

            $shipping_cost_arr[] = $sc_data;

            unset($sc_data);

        }*/

        var_dump($sheetData);

        exit();

    }

    public function daochu()
    {
        header('Content-type:text/html;charset=utf-8');

        $education = C('EDUCATION');//学历
        $sex = C('SEX');//性别
        $nation = C('NATION');//民族
        $company = C('COMPANY_NAME');//公司归属
        $quit_type = C('QUIT_TYPE');//离职类型
        //$employee_csv_tbl = M('employee_csv',NULL);
        $employee_tbl = M('employee',NULL);
        $e_name_array = $employee_tbl->getField('id,name'); //员工名称目录
        //$e_list = $employee_tbl->where(array('id'=>180))->select();
        $e_list = $employee_tbl->alias('e')->where(array('e.id'=>180))->field(array('e.*','dep.name'=>'dep_name'))->join('department dep on dep.id=e.dep_id','left')->limit(1)->select();

        foreach ($e_list as $k => $v) 
        {
           $e_list[$k]['introduction'] = $v['introduction']?$e_name_array[$v['introduction']]:'';
           $e_list[$k]['education'] = $v['education']?$education[$v['education']]['value']:'';
           $e_list[$k]['sex'] = $sex[$v['sex']]['value'];
           $e_list[$k]['nation'] = $v['nation']?$nation[$v['nation']]['value']:'';
           $e_list[$k]['company_ownership'] = $v['company_ownership']?$company[$v['company_ownership']]:'';
           $e_list[$k]['contract_ownership'] = $v['contract_ownership']?$company[$v['contract_ownership']]:'';
           $e_list[$k]['quit_type'] = $v['quit_type']?$quit_type[$v['quit_type']]:'';
           $e_list[$k]['fixed_time'] = date('Y-m-d',$v['fixed_begin_time']).'至'.date('Y-m-d',$v['fixed_end_time']);
        }
        /*var_dump($e_list);exit();
        echo M()->getLastsql();exit();*/
        
        
        //var_dump($e_list);exit();

        $title = array('ID','编号','姓名','部门','职位','入职时间','离职时间','学历','专业','联系方式','身份证','生日','性别','籍贯','民族','QQ','邮箱','介绍人','现居住地址','公司归属','合同归属','转正时间','离职类型','离职原因','银行卡号','银行卡类型','限期时间'); //设置表头       

        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO',);          

        $str_val_array = array('id','code','name','dep_name','position_name','entry_date','quit_date','education','specialty','phone','id_card','birthday','sex','native_place','nation','qq','email','introduction','now_living_address','company_ownership','contract_ownership','correction_time','quit_type','quit_reason','bank_card_number','bank_card_type','fixed_time');

        $excel_name = "员工信息表";

        $this->excel_export_function($e_list, $excel_name, $title, $letter,$str_val_array);

        exit();

    
    }

    //导出功能

    function excel_export_function($listExcel, $excel_name, $title, $letter,$str_val_array=array())
    {
        //$listExcel数据源 $excel_name excel名称 $title表头
        ob_clean();
        set_time_limit(0);
        //ini_set("memory_limit", "100M");

        import("ORG.Util.PHPExcel");

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;

        $cacheSettings = array('memoryCacheSize' => '2048MB');

        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objPHPExcel = new PHPExcel();

        $file_name = $excel_name.date('Y-m-d His');

        $objPHPExcel->setActiveSheetIndex(0);

        $j =0;

        foreach($title as $t_val)
        {

            $index = $letter[$j];

            $objPHPExcel->getActiveSheet()->setCellValue($index."1", $t_val);

            $j++;

        }

        $arr_count = count($listExcel);

        $i = 2;

        if($str_val_array)
        {
            foreach($listExcel as $key=>$val)
            {
                $j =0;
                foreach($str_val_array as $val2)
                {

                    $index = $letter[$j];
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($index.$i, $val[$val2], PHPExcel_Cell_DataType::TYPE_STRING);
                    $j++;
                }
                $i++;
            }
        }
        else
        {
            foreach($listExcel as $key=>$val)
            {
                $j =0;
                foreach($val as $val2)
                {

                    $index = $letter[$j];
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($index.$i, $val2, PHPExcel_Cell_DataType::TYPE_STRING);
                    $j++;
                }
                $i++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle("{$excel_name}");

        header('Content-Type:application/vnd.ms-excel');

        header('Content-Disposition:attachment;filename="'.$file_name.'.xls"');

        header('Cache-Control:max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output');

        exit();

    }

    public function test4()
    {
       $in = 9;
       $res = $this->test_rand($in);
       var_dump($res);echo '<br/>';
    
        /*$add = array();
        $mall_user_tbl = M('user_tbl','mall_');//会员表
        $add = array(
            'id'=>2,
            'nickname'=>1,
            'pw'=>1,
            'cellphone'=>1,
            'customer_id'=>1,
            'wx_id'=>1,
            'add_time'=>time(),
            'status'=>1,
        );
        $id = $mall_user_tbl->add($add);
        var_dump($id);*/
    }

    public function check_code_and_user($user_id,$in=0)
    {
        //TODO 优化出现多次调用或者是死循环
        $mall_db_config = C('MALL_DB_CONFIG');
        $mall_exchange_code_tbl = M('exchange_code_tbl','mall_',$mall_db_config);
        //
        $user_id = (int)$user_id;
        $code = $this->randCode();
        $find_data = $mall_exchange_code_tbl->where(array('user_id'=>$user_id, 'code'=>$code))->find();
        if($find_data)
        {
            $in+=1;
            if($in>10)
            {
                $code = '';
            }
            else
            {
                $code = $this->check_code_and_user($user_id,$in);           
            }
        }
        return $code;
    }

    public function test_rand($in=1)
    {
        $user = M('user_tbl','mall_');
        $find_data = $user->where(array('id'=>$in))->getField('id');
        //var_dump($find_data);
        if($find_data)
        {
            $in+=1;
            if($in>10)
            {
                $code = '';
            }
            else
            {
                $code = $this->test_rand($in);           
            }
        }
        return $code ;
    }

    public function test5($number=1)
    {
        echo strlen(session_id());
        $e_info = M('employee',null)->where(array('id'=>180))->find();
        $return_data['status'] = 1;
        $return_data['msg'] = '查询员工信息记录成功';
        $url = U('athus/update_user',array('id'=>$e_info['id']));
        $return_data['data'] = array('list'=>$e_info,'type'=>1,'url'=>$url);


        $this->ajaxReturn($return_data);exit();
        $this->ajaxReturn(array('status'=>1,'msg'=>'员工信息记录添加成功！','data'=>array()));exit();

        $return_data['code'] = 200;
        $return_data['message'] = '抽奖码记录生成成功';
        $return_data['data'] = array();
        $code = $this->randCode(); //随机码生成
        if($number>1)
        {
            for ($i=1; $i <=$number ; $i++) 
            { 
               $code = $this->check_draw_code_data($code);
               //$add = array();
               $code_array[] = $code;
               if(in_array($code, $code_array))
               {
                    $code = $this->check_draw_code_data($code);
                    $code_array[] = $code;
               }
               var_dump($code_array);
               $add = array(
                    'code'=>$code,
                    'code_name'=>'',
                    'type_id'=>1,
                    'status'=>1,
                    'add_time'=>time(),
                    /*'start_time'=>time(),
                    'end_time'=>time()+3600*24*30,*/
                );
               echo '<pre>';
               var_dump($code);
               echo '</pre>';
               $draw_code_array[] = $add;
            }
            /*echo '<pre>';
            var_dump($draw_code_array);
            echo '</pre>';*/

        }
        else
        {
            $code = 'T2bsuUic';
            $draw_code = $this->check_draw_code_data($code);
            /*var_dump($draw_code);
            exit();*/
           
            if($draw_code)
            {
                $crm_draw_code_tbl = M('luck_draw_code_tbl','crm_');
                $add = array(
                    'code'=>$draw_code,
                    'code_name'=>'',
                    'type_id'=>1,
                    'status'=>1,
                    'add_time'=>time(),
                    /*'start_time'=>time(),
                    'end_time'=>time()+3600*24*30,*/
                );
                $add_id = $crm_draw_code_tbl->add($add);
                if(!$add_id)
                {
                    $return_data['code'] = -200;
                    $return_data['message'] = '抽奖码记录生成失败!';
                }
            }
            else
            {
                $return_data['code'] = -200;
                $return_data['message'] = '抽奖码记录生成失败!!';
            }
        }
        
        //return $return_data;
        var_dump($return_data);
        echo '<br/>';
    }

    public function test_add_code()
    {
        header('Content-type:text/html;charset=utf-8');
        $n = 1000;
        G('begin');
        for ($i=1; $i <= $n ; $i++) 
        { 
            $this->test5();
        }
        G('end');
        echo '生成抽奖码'.$n.'条结束';
        echo G('begin','end').'s';
    }

    public function check_draw_code_data($code,$in=0)
    {
        $crm_draw_code_tbl = M('luck_draw_code_tbl','crm_');
        $find_data = $crm_draw_code_tbl->where(array('code'=>$code))->find();
        if($find_data)
        {
            $in+=1;
            if($in>10)
            {
                $code = '';
            }
            else
            {
                $code = $this->randCode();
                $code = $this->check_draw_code_data($code,$in);           
            }
        }
        return $code;
    }

    public function randCode($length = 8, $type = 0 ,$is_up = 1)
    {
        $arr = array(
            1 => "23456789", 
            2 => "abcdefghjkmnpqrstuvwxyz", 
            3 => "ABCDEFGHIJKLMNPQRSTUVWXYZ", 
            4 => "~@#$%^&*(){}[]|"
        );
        if ($type == 0)
        {
            array_pop($arr);
            $string = implode("", $arr);
        }
        elseif ($type == "-1")
        {
            $string = implode("", $arr);
        }
        else
        {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++)
        {
            $code .= $string[rand(0, $count)];
        }
        if($is_up)
        {
            $code = strtoupper($code); //转大写
        }
        return $code;
    }

}
