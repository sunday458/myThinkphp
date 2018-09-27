<?php
namespace Test\Controller;
use Think\Controller;
use Test\Org\Util;
// use User\Model\UserModel;
/**
* 
*/
class IndexController extends Controller
{
	
	function __construct()
	{
		parent::__construct();
	}

/**
* TP自带的 AJAX返回方式
*/
	function myPhp(){
		$nowTime = time();
		$reportTime = strtotime(date('Ymd 18:00:00'));
		if ($reportTime<=$nowTime) {
			$data['status'] = false;
			$data['msg'] = '失败！';
			$data['user'] = null;
		}else{
			$user['user_name'] = 'wei';
			$user['age'] = 24;
			$data['status'] = true;
			$data['msg'] = '成功！';
			$data['user'] = $user;
		}
		
		$this->ajaxReturn($data);
	}
   // 调用公共函数测试
	public function myHanShu(){
		dump(getthemonth(date('Ymd')));
		echo randCode(6,-1).'<br/>';
		echo random(9,all,0);
	}
   //模型类测试
	function index(){
         //$model = M('snapshot_log');
         $d_model =D('Test/Snapshot');
         //var_dump( $d_model);
         //$list = $model->field('id,preview_key,preview_val')->select();
         $list = $d_model->field('id,preview_key')->select();
         $count = $d_model->count();
         var_dump($list,$count);
	}

    // redis测试
	function testRedis(){
		$redis = new \Redis();
		$redis->connect("127.0.0.1","6379");
		/*//存储一个 值  
		$redis->set("say","Hello World");  
		echo $redis->get("say");     //应输出Hello World  */
		  
		//存储多个值，并不是数组，redis 不支持存数组
		$array = array('first_key'=>'no1',  
		          'second_key'=>'no2',  
		          'third_key'=>'no3');  
		$json_arr = json_encode($array);
	    $redis->set('json_arr',$json_arr);
		/*$array_get = array('first_key','second_key','third_key');  
		$redis->mset($array);  
		var_dump($redis->mget($json_arr)); */
	}


    // 代码运行时间测试
	public function testRunTime(){
		G('begin');
        //$get = file_get_contents('http://www.baidu.com');
		/*$d_model =D('Test/Snapshot');
         //var_dump( $d_model);
         //$list = $model->field('id,preview_key,preview_val')->select();
         $list = $d_model->field('id,preview_key')->select();
         S('s_list',$list,array('expire' =>60));*/
         $s_list = S('s_list');
         var_dump($s_list);
         //S('s_list',null);
		G('end');
		echo G('begin','end').'s';
	}
// 用户随机验证码测试
	function code(){
		$time = time();
		$str_arr = S('str_arr');
		/*
		* 这里的key可以唯一 写成用户的手机号，短信发送防止
		*/
		if (empty($str_arr)) {
			$str_arr = array();		
			$str_arr[] = array($time,random(6),1);
			S('str_arr',$str_arr);
		}else{
			$r_arr = array($time,random(6),1);
			$str_arr[] = $r_arr;
			S('str_arr',$str_arr,60);
		}

		/*S("$time",$code,60);
		$arr = S($time);
		var_dump($arr);*/
		var_dump($str_arr);
	}
    
	function checkCode( $time,$code ){
        $code_arr = S('str_arr');
        $code_arr = empty($code_arr)? false:$code_arr; 
        if ($code_arr) {
        	foreach ($code_arr as $key => $value) {
        		$time == $value[0] ? $res['time'] = 1 :  false;
        		$code == $value[1] ? $res['code'] = 1 :  false;
        		$value[2] == 1 ? $res['num'] = 1 :  false;
        		if ($res['time'] && $res['code'] && $res['num']) {
        			$code_arr[$key][2] = 0;
        			S('str_arr',$code_arr,60);
        			$data['status'] = true;
        			$data['msg'] = '成功';
        			$this->ajaxReturn( $data );
        		}else{
        			$data['status'] = false;
					$data['msg'] = '无效验证码';
					$this->ajaxReturn( $data );
        		}
        	}
        }else{
        	$data['status'] = false;
			$data['msg'] = '请先获取验证码';
			$this->ajaxReturn( $data );
        }
	}

// 动态建表，按月份(是否考虑一份总记录表，其他都是总记录表按月份拆分的月份表)
	function checkTables($table_name){
		$db_name = C('DB_NAME');
		$prefix = C('DB_PREFIX');
		$date = date('Y-m');
        $table_num = date('Ym',strtotime($date));
        $table_name = $prefix.$table_name.'_'.$table_num;
		$sql = " show tables from $db_name like '{$table_name}' ";
		$d_model = D('Test/Snapshot');
		//$model = M();
		$res = $d_model->query($sql);
		 //echo $d_model->getLastSql();
		if ($res) {		
			$data['status'] = true;
			$data['msg'] = '数据表'.$table_name.'已经存在';
		}else{
           // $model = M();
			$sql_str = "CREATE TABLE IF NOT EXISTS {$table_name} (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `user_id` int(10) NOT NULL,
                      `img_url` varchar(100) NOT NULL,
                      `add_time` datetime NOT NULL,
                      `audit_id` int(10) NOT NULL,
                      `audit_time` datetime NOT NULL,
                      `role` varchar(100) NOT NULL,
                      `img_type` varchar(100) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `user_id` (`user_id`),
                      KEY `audit_id` (`audit_id`)
                    ) ENGINE=InnoDB CHARSET=gbk COMMENT='XXX表';";
            $ret = $d_model->execute($sql_str);
            //$ret = $model->query($sql_str); 待考究，建表成功，报错
            if ($ret !== false) {
            	$data['status'] = false;
            	$data['errcode'] = 101;
				$data['msg'] = "数据表$table_name已新建";
            }else{
            	$data['status'] = false;
            	$data['errcode'] = 102;
				$data['msg'] = "数据表$table_name创建失败";
            }     
		}
		$this->ajaxReturn( $data );
	}

//可以配合到期的 自动评价这些 填写提示信息
    function testRandData(){
/*    	$info = array(
    		1=>'提示信息1',
    		2=>'提示信息2',
    		3=>'提示信息3',
    		4=>'提示信息4',
    	);
*/        
    	$snapshotModel = D('Test/Snapshot');
    	$info = $snapshotModel->evaluation;
    	//var_dump($info);
    	$msg = $info[rand(1,4)];
        // echo $msg;
        //思路:得到已经交易成功或收到货的还未评价用户的名单，添加入用户评价记录表中
        $add_data['user_id'] = 1;
        $add_data['user_name'] = '黎明';
        $add_data['info'] = $msg;
        $add_data['add_time'] = time();
         var_dump($add_data);
        /*echo date('Y-m-d').'<br/>';
        echo date('Ymd',time()-60*60*24*30);
        var_dump($mk= mktime(0,0,0,date('m')-1,date('d'),date('Y')));
        echo date('Ymd',$mk);*/
       /* $model = M();
        $res = $model->data($add_data)->add();
        if ($res) {
        	echo '入库成功';
        }else{
            echo "入库失败，网络异常，重新尝试";
        }*/
       
    }

    // PHP反射
    /* 
    function testRef(){
    	$snapshotModel = D('Test/Snapshot');
    	 //获取类名
        $class_name = get_class($snapshotModel);
        //获取类下方法名(私有，保护方法无法获取),数组
        $action_name = get_class_methods($class_name);
        //私有，保护的变量无法获取，数组
        $class_vars = get_class_vars($class_name); 
       
        $reflect = new \ReflectionClass("$class_name"); //反射
        $vars = $reflect->getConstants(); //获取常量
        $properties = $reflect->getProperties();  //获取属性
       /* foreach($properties as $property) {  
    		echo $property->getName()."\n";  
		}  */
        /*$ec=$reflect->getMethod('myModelAction3');  //获取类中的xxx方法
        $ec2=$reflect->getMethods();  //获取类中的全部方法 

        $reflect->isInstantiable(); // 是否能实例化  
        $new_obj = $reflect->newInstanceArgs(); //实例化该类
        $web = $new_obj->getWeb(); //私有，保护属性无法直接调用
        $url = $new_obj->getUrl();   
        $res = $new_obj->myModelAction1(); //私有，保护方法无法调用
        var_dump($web,$url);
    }*/

    function testData(){
    	$num = -0.12;
    	$bool = false;
    	$arr = array();
    	$char = '';
        $em = false;

    	if ($num == $bool) {
    		echo $num.'='.$bool.'<br/>';
    	}
    	if ($arr == $bool) {
    		echo '=2'.'<br/>';
    	}
    	if ($char == $bool) {
    		echo '=3'.'<br/>';
    	}
    	if ($num >0 ) {
    		echo '>>>>';
    	}else{
    		echo '<<<<';
    	}
        echo '<br/>';
        if($em){
            echo 'this is data';
        }else{
            echo 'empty';
        }
    }

/**
*  自定义拼装的 sql 语句(待完善)
*/
    public function testSql($b_select_count = false,$action='',$type_id=0,$referer='',$where_str,$order_by = 'add_time DESC', $limit = '0,20', $fields = '*',$groupby='id')
    {

        $action = trim($action);
        $type_id = (int)$type_id;
        $referer = trim($referer);
        if(strlen($action)>0)
        {
            if(strlen($where_str)>0) $where_str .= ' AND ';
            $where_str .= "action=:x_action";
            sqlSetParam($where_str,'x_action',$action);
        }
        if($type_id>0)
        {
            if(strlen($where_str)>0) $where_str .= ' AND ';
            $where_str .= "type_id={$type_id}";
        }
        if(strlen($referer)>0 && in_array($referer,$this->referer_arr))
        {
            if(strlen($where_str)>0) $where_str .= ' AND ';
            if($referer == 'other')
            {
                $where_str .= "referer NOT IN('app','weixin')";
            }
            else
            {
                $where_str .= "referer=:x_referer";
                sqlSetParam($where_str,'x_referer',$referer);
            }
        }
        if($b_select_count == true)
        {
            return $this->findCount ( $where_str,$fields);
        }
        // 查询条件
        $whereby = $where_str != '' ? "WHERE {$where_str}" : '';
        //group by
        $groupby = $groupby != '' ? "GROUP BY {$groupby}": '';
        // 处理排序
        $sortby = $order_by != '' ? " ORDER BY {$order_by}" : '';
        $sql_str = "SELECT {$fields} FROM {$this->_db_name}.{$this->_tbl_name} {$whereby} {$groupby} {$sortby}";
        if (check_limit_str($limit))
        {
            $sql_str .= " LIMIT {$limit}";
        }
        else
        {
            $sql_str .= " LIMIT 0,1000";
        }
        return $sql;
    }

    function getTime( $date='int' ){
    	// $nowTime = time();
    	$stime=microtime(true); //获取程序开始执行的时间 

       //执行的代码 
    	$nowTime = date('Y-m-d');
    	$serven = strtotime($nowTime) - 60*60*24*7;
    	$thiry = strtotime($nowTime) - 60*60*24*30;
    	if ($date == 'str') {
    		$data['serven'] = date('Y-m-d',$serven);
    		$data['thiry'] = date('Y-m-d',$thiry);
    	}else{
    		$data['serven'] = $serven;
    		$data['thiry'] = $thiry;
    	}
    	var_dump( $data );

    	$etime=microtime(true);
		$total=$etime-$stime;   //计算差值 
		//echo "\r\n";
		echo " <br/>{$total}.s"; 

		$sql = db_arr_to_update_str($data);
		echo '<br/>'.$sql;

    }

    function myTest( $num )
    {
    	$send_type = array(
	        array('1'=>'群发短信'),
	        array('2'=>'群发小助手'),
	        array('3'=>'群发微信'),
        );
        foreach( $send_type as $k=>$v )
        {
            if( $num == key($v) ) var_dump($v[$num]);
        }

        echo 'error';
    }

    function testArr(){
    	$array = array(1,2);
    	$post_array = array(3,4);
        $test_arr = array(1,2,3,'');
    	$params = array();
        $params[] = $array;
        $params[] = $post_array;
        print_r($params);
        echo '<br/>';
        var_dump($params[0]);
        var_dump( array_filter($test_arr));
        $begin_time=strtotime(date('Y-m-d 07:01:00'))-3600*24;
        print_r($begin_time);
    }

}
