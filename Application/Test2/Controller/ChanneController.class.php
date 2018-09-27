<?php
/**
 * Created by PhpStorm.
 * User: sunew
 * Date: 2018/2/6
 * Time: 9:59
 */
namespace Test2\Controller;
use Think\Controller;

class ChanneController extends Controller
{
    CONST TABLE_PREFIX = 'mall_'; //表前缀
    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 机会兑换商品
     * @param $good_id
     * @param $user_id
     */
    public function exchange_channe_to_goods($good_id,$user_id,$type_id)
    {
        //查询商品支持兑换类型
        $mall_goods_tbl = M('goods_tbl','mall_','DB_CONFIG4');
        //$good_info = $mall_goods_tbl->where(array('id'=>$good_id))->getField('id,get_type,integral,status,is_show',true);
        $good_info = $mall_goods_tbl->where(array('id'=>$good_id))->field('id,get_type,integral,status,is_show')->find();
        $check_result = $this->check_channe_goods($good_info,$type_id);
        if($check_result['code']<=0)
        {
            return $check_result;
        }

        if($type_id == 1)
        {
            //积分兑换
            $member_result = $this->member_channe_by_point($user_id,$good_info);
            if(!$member_result['code'])
            {
                return $member_result;
            }
            else
            {
                $member_result['message'] .= ' 添加兑换记录完成!';
                return $member_result;
            }
        }
        else
        {
            //签到 | 生日 类型兑换
            $member_result = $this->member_channe_by_no_point($user_id,$type_id);
            if($member_result['code']<0)
            {
                return $member_result;
            }
            else
            {
                $e_id = $member_result['data']['opp_id'];
                $add_result = $this->add_exchange_code_data($user_id,$good_id,$e_id,$type_id);
                if(!$add_result)
                {
                    $result_data = array('code'=>-200,'message'=>'兑换表记录添加异常','data'=>array());
                    return $result_data;
                }

                $member_result['message'] .= ' 添加兑换记录完成';
                $member_result['data']['code_id'] = $add_result;
                return $member_result;
            }
        }

    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 检查兑换商品 是否能兑换
     * @param $good_info
     * @param $type_id
     * @return array
     */
    public function check_channe_goods($good_info,$type_id)
    {
        $type_id_arr = explode(',',$good_info['get_type']);
        $result_data = array('code'=>200,'message'=>'可以正常兑换','data'=>array());
        if(!in_array($type_id,$type_id_arr))
        {
            $type_id_str = $this->get_type_id_str($type_id);
            $result_data = array('code'=>-200,'message'=>'该兑换商品不支持'.$type_id_str.'兑换','data'=>array());
        }

        if(!$good_info['status'] || !$good_info['is_show'] )
        {
            $result_data['code'] = -300;
            $result_data['message'] = '该兑换商品不存在或已下架';
        }

        return $result_data;
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 积分兑换 商品
     * @param $user_id
     * @param $good_info
     * @return array
     */
    private function member_channe_by_point($user_id,$good_info)
    {
        //$add_user_id = 100; //兑换人ID
        $result_data = array('code'=>200,'message'=>'积分兑换商品成功','data'=>array());
        $member_controller = A('Member');
        $member_point_result = $member_controller->update_member_data_by_user_id_and_type_id($user_id,101,array('point'=>$good_info['integral']));

        $result_data['code'] = $member_point_result['status'];
        $result_data['message'] = $member_point_result['msg'];

        return $result_data;

    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 签到 | 生日 类型兑换商品
     * @param $user_id
     * @param $type_id
     * @return array
     */
    private function member_channe_by_no_point($user_id,$type_id)
    {
        $now_time = time(); //当前时间
        $result_data = array('code'=>200,'message'=>'兑换成功','data'=>array('opp_id'=>0));
        $mall_exchange_opp_tbl = M('exchange_opp_tbl',self::TABLE_PREFIX);

        //TODO 是否处理过期兑换机会
        //$this->check_member_exchange_time($user_id);

        //2签到 3生日类兑换
        $type_str = $type_id==2?'签到类':'生日类';
        $where = array(
            'user_id'=>$user_id,
            'type_id'=>$type_id,
            'status'=>1,
            'validity_time'=>array('EGT',$now_time)
        );
        $opp_list = $mall_exchange_opp_tbl->where($where)->order('validity_time desc')->select();
        if($opp_list)
        {
            $e_id = $opp_list[0]['id'];
            $update_data = array(
                'ex_time'=>time(),
                'status'=>2,
            );
            $result = $mall_exchange_opp_tbl->where(array('id'=>$e_id))->save($update_data);
            $bool = $result!==false?true:false;
            if($bool)
            {
                $result_data['code'] = 200;
                $result_data['message'] = '会员'.$user_id.'已成功兑换1次'.$type_str.'的机会';
                $result_data['data']['opp_id'] = $e_id;
            }
            else
            {
                $result_data['code'] = -300;
                $result_data['message'] = '网络异常,请重新尝试兑换';
            }
        }
        else
        {
            $result_data['code'] = -200;
            $result_data['message'] = '不能兑换,会员无'.$type_str.'兑换机会的次数';
        }

        return $result_data;

    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 查询兑换code记录表
     * @param $user_id
     * @param $code
     */
    public function get_exchange_code_data($user_id,$code)
    {
        $mall_db_config = C('MALL_DB_CONFIG');
        $mall_exchange_code_tbl = M('exchange_code_tbl','mall_',$mall_db_config);
        $where = array(
            'user_id'=>$user_id,
            'code'=>$code,
        );
        $list = $mall_exchange_code_tbl->where($where)->find();
        if($list)
        {
            /*if(count($list)>2)
            {
                return array('code'=>-300,'message'=>'存在会员code重复,暂不能使用,请检查','data'=>array());
            }
            else
            {*/
            return array('code'=>200,'message'=>'查询code记录成功','data'=>array($list));
            //}
        }
        else
        {
            return array('code'=>-200,'message'=>'无效code记录','data'=>array());
        }
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 消兑换code记录表
     * @param $user_id
     * @param $code
     * @param $data=array(bill_id,op_id)
     */
    public function change_exchange_code_data($user_id,$code,$data)
    {
        $mall_exchange_code_tbl = M('exchange_code_tbl','mall_');

        $exchange_code_data = $this->get_exchange_code_data($user_id,$code);
        if($exchange_code_data['code']>=0)
        {
            if($exchange_code_data['data'][0]['status']==1)
            {
                $update_data = array(
                    'use_time'=>time(),
                    'op_id'=>$data['op_id'],//下单人
                    'order_phone'=>$data['order_phone'], //下单电话
                    'order_sn'=>$data['order_sn'], //订单号
                    'status'=>2
                );
                $update_result = $mall_exchange_code_tbl->where(array('user_id'=>$user_id,'code'=>$code))->save($update_data);
                if($update_result!==false)
                {
                    return array('code'=>200,'message'=>'消费兑换code成功','data'=>array());
                }
                else
                {
                    return array('code'=>-200,'message'=>'网络异常,请重新尝试消费code','data'=>array());
                }
            }
            else
            {
                return array('code'=>-300,'message'=>'该会员code已使用,请使用其他code','data'=>array());
            }
        }
        else
        {
            return array('code'=>-200,'message'=>'无效code,请检查会员code是否正确','data'=>array());
        }
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 添加兑换表 记录
     * @param $user_id
     * @param $good_id
     * @param $e_id
     * @param $type_id
     * @return int|mixed
     */
    public function add_exchange_code_data($user_id,$good_id,$e_id,$type_id)
    {
        $mall_exchange_code_tbl = M('exchange_code_tbl','mall_');
        //$product_model = M('product',null);
        $goods_tbl = M('goods_tbl','mall_');
        //$good_name = $product_model->where(array('id'=>$good_id))->getField('name');
        $good_name = $goods_tbl->where(array('id'=>$good_id))->getField('titles');
        //$admin_info = $this->user;
        $randCode = $this->randCode(); //8位code
        $check_code_result = $this->check_code_and_user($user_id,$randCode); //code唯一处理
        $add_data = array(
            'exchange_opp_id'=>$e_id,
            'code'=>$check_code_result,//8位字母+数字
            'goods_id'=>$good_id,
            'goods_name'=>$good_name,
            'user_id'=>$user_id,
            'type_id'=>$type_id,
            'add_time'=>time(),
            //'use_time'=>time(),
            //'op_id'=>$admin_info['obj_id']
        );
        $add_id = $mall_exchange_code_tbl->data($add_data)->add();
        return $result = $add_id?$check_code_result:0;
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-10
     * @Description: 检查code 是否重复
     * @param $user_id
     * @param $code
     * @return mixed
     */
    public function check_code_and_user($user_id,&$code)
    {
        //TODO 优化出现多次调用或者是死循环
        $mall_exchange_code_tbl = M('exchange_code_tbl','mall_');

        $find_data = $mall_exchange_code_tbl->where(array('user_id'=>$user_id, 'code'=>$code))->find();
        if($find_data)
        {
            sleep(1);
            $code = $this->randCode(); //8位code
            $this->check_code_and_user($user_id,$code);
        }
        else
        {
            return $code;
        }
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 处理会员 过期的兑换机会
     * @param int $user_id
     */
    public function check_member_exchange_time($user_id)
    {
        //$mall_db_config = C('MALL_DB_CONFIG');
        $mall_exchange_opp_tbl = M('exchange_opp_tbl',self::TABLE_PREFIX);
        $member_exchange_list = $mall_exchange_opp_tbl->where(array('user_id'=>$user_id))->select();
        if($member_exchange_list)
        {
            foreach ($member_exchange_list as $k => $v)
            {
                if($v['validity_time']<time() && $v['status']==1 )
                {
                    $mall_exchange_opp_tbl->where(array('id'=>$v['id']))->update(array('status'=>3));
                }
            }
        }
    }

    /**
     * @Author: sunew
     * @Date: 2018-2-6
     * @Description: 获取 积分类型信息
     * @param int $type_id
     * @return string
     */
    public function get_type_id_str($type_id=1)
    {
        if($type_id==1)
        {
            $str = '积分类型';
        }
        elseif($type_id==2)
        {
            $str = '签到类型';
        }
        else
        {
            $str = '生日类型';
        }
        return $str;
    }

    /**
     *+----------------------------------------------------------
     * 生成随机字符串
     *+----------------------------------------------------------
     * @param int       $length  要生成的随机字符串长度
     * @param string    $type    随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
     *+----------------------------------------------------------
     * @return string
     *+----------------------------------------------------------
     */
    public function randCode($length = 8, $type = 0) {
        $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0) {
            array_pop($arr);
            $string = implode("", $arr);
        } elseif ($type == "-1") {
            $string = implode("", $arr);
        } else {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }

    public function test()
    {
        header('Content-type:text/html;charset=utf-8');
        $res = $this->exchange_channe_to_goods(1,3,2); //签到 | 生日 兑换测试
        //$res = $this->exchange_channe_to_goods(1,3,1);
        var_dump($res);
        $data = array(
            'op_id'=>100,//下单人
            'order_phone'=>13826443396, //下单电话
            'order_sn'=>'TB123456821', //订单号
        );
        $res2 = $this->change_exchange_code_data(3,$res['data']['code_id'],$data);
        var_dump($res2);
        echo $this->randCode();
    }

    public function test_d()
    {
        /*$res = $this->get_exchange_code_data(3,'WGF8MM1f');
        var_dump($res);
        exit();*/
        $v = 1;
        echo $r = $this->digui($v);
    }

    public function digui($v)
    {
        /*if($v<10)
        {
            echo '<pre>';
            var_dump($v);
            echo '</pre>';
            $v++;
            echo '<pre>';
            var_dump($v);
            echo '</pre>';
            $this->digui($v);
        }
        echo '<pre>--';
        var_dump($v);
        echo '</pre>';
        return $v;*/

        if($v<10)
        {
            $v++;
            echo '<pre>';
            var_dump($v);
            echo '</pre>';
            return $this->digui($v);
        }
        else
        {
            echo '<pre>++';
            var_dump($v);
            echo '</pre>';
            return $v;
        }

    }

}