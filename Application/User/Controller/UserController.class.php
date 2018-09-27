<?php
namespace User\Controller;
use Think\Controller;

/**
* 
*/
class UserController extends Controller
{

    CONST STATUS = 'finishing';
    protected $controller;

	public function __construct()
	{
		parent::__construct();
        $this->controller = D('user');
	}

    public function get_all_user()
    {
        $user_model = M('User','ts_','DB_CONFIG2');
        $list = $user_model->select();
        var_dump($list);
    }

    public function add($name='')
    {
        $class = $this->controller;
        var_dump($class->where('id=1')->find());
        $arr = array(
            array('id'=>1,'name'=>'liming'),
        );
        $data['create_date'] = time();
        //var_dump($data);
        //$create_date = time();
        $test['create_date'] = time();
        //$this->assign($create_date);
        $this->assign('data',$data);
        $this->assign('arr',$arr);
        $this->display();
    }
    public function check_data()
    {
        header('Content-type:text/html;Charset=utf-8');
        //$name = I('name');
        /*$add_data = array(
            'name'=>$name,
        );*/
         $user_model = D('user');
        $data = I('post.');
         var_dump($data);
        $data = $user_model->create();
        echo '入口<br/>';
        var_dump($data);
        //if (!$user_model->create($add_data)) // 验证非POST数据
        if (!$user_model->create()) // POST提交生效
        {     // 如果创建失败 表示验证没有通过 输出错误提示信息
            exit($user_model->getError());
        }
        else
        {     // 验证通过 可以进行其他数据操作
        }
    }

    public function test_db($db_num=1)
    {
        //var_dump(C('DB_CONFIG1'));
        $model = D('user');
        $list = $model->select();
        var_dump($list);
        echo '<br/>';
        $db_str = 'DB_CONFIG'.$db_num;
        if(!C($db_str)) $model->change_db(C($db_str));

        $res = $model->find();
        var_dump($res);
    }

    //简单模拟 登录错误次数限制
    public function login()
    {
        header("Content-type: text/html; charset=utf-8"); 
        $time_interval = 60*1; //一分钟
        $name = I('post.name');
        $phone  = I('post.phone');
        $user_model = M('user');
        $map['name'] = array('like','%wei%');
        $user_info = $user_model->where($map)->field('id,last_login_time,login_error_num')->find();
        var_dump($user_info);
        echo '<br/>';
        $cha = time()-$user_info['last_login_time'];
        echo $cha.'<br/>';
        $new_login_time = date('Y-m-d H:i',$user_info['last_login_time']+60);
        if($name!='wei'||$phone!='123')
        {
            echo '-----';
            echo time().'<br/>';
            // 登录失败
            if($cha<$time_interval) //限制时间内
            {
                if($user_info['login_error_num']>=3)
                {
                    echo '登录失败超过3次,请于'.$new_login_time.'后重新登录';exit();
                }
                //登录错误次数+1
                $user_model->where(array('id'=>$user_info['id']))->setInc('login_error_num'); 
                echo '登录错误次数+1';
            }
            else
            {
                //登录错误次数变成1并记录登录时间
                $data = array('login_error_num'=>1,'last_login_time'=>time());
                $user_model->where(array('id'=>$user_info['id']))->setField($data);
                echo '登录错误次数变成1 并记录登录时间';
            }
        }
        else
        {
            echo '*****';
            if($cha<$time_interval) //限制时间内
            {
                if($user_info['login_error_num']>=3)
                {
                    echo '登录失败超过3次,请于'.$new_login_time.'后重新登录';exit();
                }
            }
            // 更新最后一次登录时间
            $user_model->where(array('id'=>$user_info['id']))->setField('last_login_time',time());
            echo '登录成功';exit();
        }
    }

    public function test_time_cha()
    {
        echo session_id().'<br/>';
        $ip = get_client_ip();
        $ip = '192.168.1.2';
        $long_ip = ip2long( $ip );
        $long_ip2 = bindec(decbin(ip2long( $ip )));
        echo $ip.'*'.$long_ip.'*'.$long_ip2.'<br/>';
        header("Content-type: text/html; charset=utf-8"); 
        //PHP计算两个时间差的方法 
        $startdate="2010-12-11 11:40:00";
        $enddate="2012-12-12 11:45:09";
        $cha = strtotime($enddate)-strtotime($startdate);
        $cha = 60 *10;
        $date=floor($cha/86400);
        $hour=floor($cha%86400/3600);
        $minute=floor($cha%86400/60);
        $second=floor($cha%86400%60);
        echo $date."天<br>";
        echo $hour."小时<br>";
        echo $minute."分钟<br>";
        echo $second."秒<br>";
    }

}