<?php
namespace MyDb\Controller;
use Think\Controller;
use MyDb\Org\Util;
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


// 动态建表，按月份(是否考虑一份总记录表，其他都是总记录表按月份拆分的月份表)
	function checkTables($table_name){
		$db_name = C('DB_NAME');
		$prefix = C('DB_PREFIX');
		$date = date('Y-m');
        $table_num = date('Ym',strtotime($date));
        $table_name = $prefix.$table_name.'_'.$table_num;
		$sql = " show tables from $db_name like '%{$table_name}%' ";
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

/**
*  自定义拼装的 sql 语句(待完善)
*/
    public function testSql($b_select_count = false,$action='',$type_id=0,$referer='',$where_str,$groupby='id',$order_by = 'add_time DESC',$limit = '0,20',$fields = '*')
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

    /**
    * 适用于非模型类的调用,sql组装语句
    */
    public function test_str_to_sql($b_select_count = false,$table_name='',$type_id=0,$referer='',$where_str,$groupby='id',$order_by = 'add_time DESC',$limit = '0,20',$fields = '*')
    {
        $table_name = trim($table_name); //库名.表名
        $type_id = (int)$type_id;
        $referer = trim($referer);
        // ①这里也可以考虑将参数 数组形式传入之后展开 分别处理
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
            //todo 这里对于部分合并的计总数，需要判断 groupby处理
            return $this->findCount ( $where_str,$fields);
        }
        // 查询条件
        $whereby = $where_str != '' ? "WHERE {$where_str}" : '';
        //group by
        $groupby = $groupby != '' ? "GROUP BY {$groupby}": '';
        // 处理排序
        $sortby = $order_by != '' ? " ORDER BY {$order_by}" : '';
        $sql_str = "SELECT {$fields} FROM {$table_name} {$whereby} {$groupby} {$sortby}";
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

    /**
     * 条件拼装成sql, 适用面比较广
     * @param string $puid
     * @param $where_str
     * @param string $groupby
     * @param string $order_by
     * @param string $limit
     * @param string $fields
     * @return string
     */
    private function str_to_sql($table_name,$where_str,$groupby,$order_by,$limit,$fields='*',$count=false )
    {
        // 查询条件
        $whereby = $where_str != '' ? "WHERE {$where_str}" : '';
        //group by
        $groupby = $groupby != '' ? "GROUP BY {$groupby}": '';
        // 处理排序
        $sortby = $order_by != '' ? " ORDER BY {$order_by}" : '';
        $sql_str = "SELECT {$fields} FROM {$table_name} {$whereby} {$groupby} {$sortby}";
        $count_sql_str = " SELECT COUNT(*) AS c FROM {$table_name} {$whereby} ";
        if($limit)
        {
            $check_limit = $this->check_limit_str($limit);
            if ($check_limit)
            {
                $sql_str .= " LIMIT {$limit}";
            }
            else
            {
                $sql_str .= " LIMIT 0,1000";
            }
        }
        if($count) return $count_sql_str;
        return $sql_str;
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

/**
* 一个统计 数组去重累加的方法 demo
*/
    /**
     * @Author: sunew
     * @Date: 2017-11-9
     * @Description: do something...
     * @param $puid_arr
     * @param $where_str
     * @return array
     */
    public function get_pa_visited_uv_ip_total($puid_arr,$where_str)
    {
        if(empty($where_str))  $where_str = '';
        $result = $this->get_pa_dt_visited_uv_ip($puid_arr,$where_str);

        if(!$result)  return array('uv'=>0,'ip'=>0);
        $ip_arr = array();
        $uv_arr = array();
        $ip_num = 0;
        $uv_num = 0;
        foreach($result['ip'] as $v)
        {
            if(!empty($ip_arr))
            {
                if(!in_array($v,$ip_arr))
                {
                    $ip_num ++; //若已统计好的数，需要 $ip_num +=
                    $ip_arr[] = $v;
                }
            }else
            {
                $ip_arr[] = $v;
                $ip_num = 1;
            }
        }
        foreach($result['uv'] as $_v)
        {
            if(!empty($uv_arr))
            {
                if(!in_array($_v,$uv_arr))
                {
                    $uv_num ++; //若已统计好的数，需要 $ip_num +=
                    $uv_arr[] = $_v;
                }
            }else
            {
                $uv_arr[] = $_v;
                $uv_num = 1;
            }
        }
        unset($uv_arr);
        unset($ip_arr);
        unset($result);

        return array('uv'=>$uv_num,'ip'=>$ip_num);
    }

    function myTestSql()
    {
        // sql 去重合并合计查询
        $sql = "  SELECT visit_time,referer_outside_host, referer_outside_path, referer_outside_file, referer_outside_query ,referer_outside_unfiltered,
current_page_url_unfiltered, COUNT(*) AS PV, COUNT(DISTINCTROW(g_session_id)) AS UV, COUNT(DISTINCTROW(ip)) AS IP
FROM www_log_tmp_db.yueus_tmp_tbl_20161212
WHERE  visit_time >= 1481472000 AND visit_time <= 1481558399 AND tj_spread_regedit = '5fe538b955c7ca53441f866b0a2f1bf1' AND current_page_url_host = 'pa.yueus.com'
GROUP BY referer_outside_host, referer_outside_path, referer_outside_file, referer_outside_query ,current_page_url_unfiltered
ORDER BY visit_time DESC
LIMIT 0,999999 ";

        // 数据2列数据交换
        $sql2 = " UPDATE `test` a INNER JOIN `test` b ON a.id=b.id
SET a.current_url=b.current_encode_url ,a.current_encode_url=b.current_url
 ";
       //查询 某个库下 的所有 表名
        $sql3 = " select table_name FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'test'  "
    }
}
