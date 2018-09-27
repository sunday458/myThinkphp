<?php
namespace Test\Controller;
use Think\Controller;
//use Lib;
/**
* 
*/
class TestController extends Controller
{
	
	function __construct()
	{
		parent::__construct();
	}

	function testEcho(){
		echo date('Y-m-d');
	}

	function testArrayLv(){
		$arr = array(
			1=>1,
			2=>array('no2' =>'no2' ),
			3=>3,
			4=>array('4' =>4,2=>'4-2','no4-3'=>'no4-3' ),

		);
		$arr2 = array();
		$arr2[] = $arr;
		echo '数组维度:'.$ret = array_depth($arr2);
		echo '<br/>';
		print_r($arr2);
	}

	function testArrAsKey(){
		$arr = array(
			1=>'no1',
			2=>'no2',
			'no3'=>'no-3',
			4=>array('4' =>4),
		);
		$arr2 = array(
			1=>array(1=>1,2=>2,3=>3),
			2=>array(1=>1,2=>2,3=>3),
			);
		$arr3 = array(3,4,5);
		$ret = array_as_key( $arr3,'1');
		var_dump($ret);
	}

	function testTimingTask(){
		//1.确定需要推送信息的人员列表
		//2.收集需要推送的信息
		//3.设置满足的条件进行推送
		$model = M('CrmMessageLogTbl');
		$nowTime = time();
		$where['status'] = 0;
		$where['start_time'] = array('elt',$nowTime);
		$where['end_time'] = array('egt',$nowTime);
		$list = $model->where($where)->field('id,start_time,end_time')->select();
		if (empty($list)) return false;
		$send_list = array();

		foreach ($list as $key => $value) {
		/*	if ($nowTime<$value['start_time']) {
				$data['status'] = false;
				$data['msg'] = '活动尚未开始';
				$this->ajaxReturn($data);
			}elseif ($nowTime>$value['end_time']) {
				$data['status'] = false;
				$data['msg'] = '活动已经结束';
				$this->ajaxReturn($data);
			}else{
				$send_list[] = $value;
			}*/
			if ($value['start_time']<=$nowTime && $nowTime<=$value['end_time']) {
				$send_list[] = $value;
			}
		}
		var_dump($list);
		 var_dump($send_list);
		echo $model->getLastSql();
		$date['start_time'] = mktime(0,0,0,7,20,2016);
		$date['end_time'] = mktime(0,0,0,7,31,2016);
		//print_r($date);exit();
		//4.正式推送消息，并记录入库
		//5.更新已经发送消息的记录状态
	    $data['status'] = ture;
		$data['msg'] = '更新完毕';	
		foreach ($send_list as $k => $v) {
			$update_where = array('id'=>$v['id']);
			$v['status'] = 1;
			$update_data = db_arr_to_update_str($v);
			print_r($update_data);
			// $ret = $model->where($update_where)->setField($update_data);
			$ret = $model->where($update_where)->setField('status',1);
			echo $model->getLastSql();
			if ($ret == false) {
			    $data['status'] = false;
				$data['msg'] = '网络错误';				
			}	
		}
		// echo '<br/>';
		// echo $model->getLastSql();
     
		//$this->ajaxReturn($data);
		
	}

	function testTime( $date,$is_int = false ){
		if($is_int){ //时间戳
			$time['yesterday'] = $date - 60*60*24;  //昨天
			$time['last_week_day'] = $date - 60*60*24*7; //上周
			$time['last_thirty_day'] = $date - 60*60*24*30; //30天之前

		}else{ //字符串 例:1999-1-1			
	        $time['yesterday'] = strtotime($date)-60*60*24; //昨天
	        $time['last_week_day'] = strtotime($date)-60*60*24*7; //上周
	        $time['last_thirty_day'] = strtotime($date) -60*60*24*30; //30天前	      
		}
		//print_r($time);exit();
        return $time;
	}

	function get_data_list(){
		$list = array(
			1=>array('pv'=>100,'uv'=>120,'scale'=>50),
			2=>array('pv'=>120,'uv'=>130,'scale'=>25),
			3=>array('pv'=>130,'uv'=>140,'scale'=>40),
		);
		$yesterday_list = array(
            1=>array('pv'=>90,'uv'=>90,'scale'=>42),
			2=>array('pv'=>78,'uv'=>100,'scale'=>62),
			3=>array('pv'=>153,'uv'=>150,'scale'=>34),
		);
		$last_thirty_day_list = array(
            1=>array('pv'=>44,'uv'=>98,'scale'=>57),
			2=>array('pv'=>78,'uv'=>18,'scale'=>13),
			3=>array('pv'=>102,'uv'=>80,'scale'=>54),
		);
		foreach ($list as $k => $v) {
			foreach ($yesterday_list as $_k => $_v) {
				if ($k == $_k) {
					/*echo $v['pv'].'/'.$v['scale'];
					echo "<br/>";
					echo $_v['pv'].'/'.$_v['scale'];
					echo "<br/>";*/
					$list[$k]['yesterday_growth_rate'] = (sprintf('%.4f',($v['pv']-$_v['pv'])/$_v['pv'])*100).'%';
				$list[$k]['yestarday_contrast_scale']=sprintf('%.2f',$v['scale']-$_v['scale']).'%';
				}
			}
			foreach ($last_thirty_day_list as $_k => $_v) {
				if ($k == $_k) {
					$list[$k]['thirty_growth_rate'] = (sprintf('%.4f',($v['pv']-$_v['pv'])/$_v['pv'])*100).'%';
				$list[$k]['thirty_contrast_scale']=sprintf('%.2f',$v['scale']-$_v['scale']).'%';
				}
			}
		}

		print_r($list);exit();
	}

	function get_app_list(){
		$app_model = M('AppPageVisitDataTbl');
		$fields = 'id,visit_time,page,location_id,row,col,dmid,pv,ip,SUM(pv) as pv_total';
		$now_date = '2016-7-17';
		$date = $this->testTime($now_date);
		$now_where = array(
           'visit_time' => array('eq',$now_date),
			);
		$yesterday_where = array(
           'visit_time' => array('eq',date('Y-m-d',$date['yesterday'])),
			);
		$list = $app_model->where($now_where)->field($fields)->group('row')->order('pv desc')->limit(4)->select();
		var_dump($app_model->getLastSql());
		var_dump($list);
		$yesterday_list = $app_model->where($yesterday_where)->field($fields)->limit(4)->select();
	}

	function testFileWrite(){

        $file_name = 'sql_'.date('Ym').'.log';
        $log = __ROOT__.'/Log/'.$file_name;
		$list = array(
			1=>array('pv'=>100,'uv'=>120,'scale'=>50),
			2=>array('pv'=>120,'uv'=>130,'scale'=>25),
			3=>array('pv'=>130,'uv'=>140,'scale'=>40),
		);
		$serialize_list = serialize($list);
		$json_list = json_encode($list);
		$filename = 'D:/file.log';
		if (!file_exists($log)) {
			$fp=fopen($log, "w+"); //打开文件指针，创建文件
			//$res = mkdir($filename);
			var_dump($fp);
			chmod($log);
		}
		if(is_writable($log)){
			echo file_put_contents($log, $json_list.PHP_EOL,FILE_APPEND);
		}else{
    		echo "文件 $log 不可写";	
        }
		/*file_exists()：查看文件是否存在，返回布尔值
		filesize()：查看文件大小，可直接echo输出
		unlink()：删除文件，注意PHP中没有delete函数。*/
	}

	function testFileRead(){
		/*$file = fopen('d://file.txt', 'r');
		if(!$file)
		{
		    echo'文件不存在';
		    exit;
		}
		while (!feof($file))
		{
		    $rose=fgets($file);
		    $james=fopen("d:/",$list);
		    fclose($james);
		}*/
		$filename = 'D:/file.log';
		$ret = file_get_contents($filename);
		var_dump($ret);
	}
/**
* 数据更新 入库
*/
	function test_rand_update($update_data = array(),$is_cache = false){
		$num = rand(1,10);
		$app_model = M('AppPageVisitDataTbl');
		/*$data_array = array(
			1=>array('id'=>1,'status'=>1),
			2=>array('id'=>2,'status'=>2),
			3=>array('id'=>3,'status'=>3),
		);*/
		$error_num = 0;
		$total = count($update_data);
		if ($num == 1){  // 1/10 大概10次更新一次
			if($is_cache){
				foreach ($update_data as $k => $v) {
					$data['status'] = $v['status'];
					$ret = $app_model->where('id='.$v['id'])->save($data);
	                if ($ret==false) $error_num++;
			    }
			}else{
				foreach ($update_data as $k => $v) {
					$ret = $app_model->where('id='.$v['id'])->setInc('status',rand(3,7));
	                $ret==false? ($error_num++) : '';
			    }
			}
			
			return (($total-$error_num) == $total)? ture : false ;	
		}
		return false;
	}

/**
* 查询数据，并且写入 缓存
*/
	function get_test_app_list(){
		$app_model = M('AppPageVisitDataTbl');
		$fields = 'id,visit_time,page,location_id,row,col,dmid,pv,ip,SUM(pv) as pv_total';
		$now_date = '2016-7-17';
		$list = $app_model->field($fields)->group('row')->order('pv desc')->limit(5)->select();
		$cache = S(array('type'=>'xcache','prefix'=>'think','expire'=>60));
		$cache->app_list = $list; // 设置缓存 
		// 获取缓存
		$app_list = $cache->app_list;
		return $app_list;      
	}
/**
* 前端使用 获取 列表信息
*获取 缓存列表信息
*/
	function get_test_cache_app_list($is_cache = ture){
		$app_list = S('app_list');
		if (isset($app_list)&&!empty($app_list)&&$is_cache == ture) {
			var_dump($app_list);
			echo "</*br*//>";
			echo "yes,cache is here";
		}else{
            $ret = $this->get_test_app_list();
            var_dump($ret);
			echo "<br/>";
			echo "no,cache isnot here";
		}
	}
/**
* 前端使用更新 数据
* 更新 缓存的数据
*/
	function update_cache_data($id = ''){
		//这里是获取缓存的数据，没有真实数据，故暂时其他数据替代
		if (empty($id)) { //队列调用
			//默认将缓存数据全部更新一次
			$cache_data = $this->get_test_cache_app_list(); 
			//在更新 缓存的时候，同时随机去 更新同步到数据库
			$ret = $this->test_rand_update($cache_data,ture);
			if ($ret) {  // 数据更新同步到数据库成功
				$this->get_test_cache_app_list(false);
				return ture;
			}
			return false;
		}else{   // 直接给前端调用的，传入 $id 即可
			$cache_data = $this->get_test_cache_app_list();
			foreach ($cache_data as $key => $value) {
			 	if ($value['id'] == $id) {
			 		$cache_data[$key]['status'] = $value['status'] + rand(3,7);
			 	}
			} 
			//在更新 缓存的时候，同时随机去 更新同步到数据库
			$ret = $this->test_rand_update($cache_data);
			if ($ret) {  // 数据更新同步到数据库成功
				$this->get_test_cache_app_list(false);
				return ture;
			}
			$cache = S(array('type'=>'xcache','prefix'=>'think','expire'=>60));
			$cache->app_list = $cache_data; // 设置缓存 
			return ture;
		}	
	}

	function updateAll(){
		// ① 首先 从数据库查询数据 放入 缓存
		// ② 前端显示的 数据 来自 ① 缓存，操作的数据变化 同时存入 缓存
		// ③ 缓存中的 数据 定时去 执行 更新方法，真正 同步 到数据库
		// ④ 更新之后的 数据库 数据 再次更新到 缓存，替代之前 旧版的缓存
		// ⑤ 后期可以在 缓存 中加入时间，判断 是否 无效或者已旧 的数据
		// ⑥ 这里的 缓存数据如果要做的更加准确，就必须精准到 哪些记录改变
	}

	function get_test_log(){
		$log = new \Test\Org\Log();
		$filename = __ROOT__.'/logs';
		$content = date('Y-m-d').get_client_ip();
		$log->writeLog($filename,$content);
		$log_list = $log->readLog($filename);
		var_dump( $log_list );
		echo $filename;
	}

	function buy_after_buy_iframe(){
		$this->display();
	}

	function test_strpos(){
		$k = 'yesterday_uv_scale';
		$k2 = 'yesterday_total_price_divide_buyer_scale';
		//$start_index = strpos($k,'yesterday_');
        $end_index = strpos($k, '_scale');
        $start_len = strlen('yesterday_');
        //$end_len = strlen('_scale');
        echo $start_index.'-'.$end_index;
        echo $end_len.'-'.$start_len;
        $str = substr($k,$start_len,$end_index-$start_len);
        var_dump($str);
		$v = -9;
	var_dump(strpos($k2,'total_price_divide_buyer'));
		if( strpos('yesterday',$k) )
    	{
          if( abs($v)>10 )
          {
              echo $v;
          }else
          {
              echo '<10%';                       
          }
 		}else
 		{
 			echo 'no';
 		}
	}

	//获取文件列表，包含带后缀的文件
	function getFile($dir) {
	    $fileArray[]=NULL;
	    if (false != ($handle = opendir ( $dir ))) {
	        $i=0;
	        while ( false !== ($file = readdir ( $handle )) ) {
	            //去掉"“.”、“..”以及带“.xxx”后缀的文件
	            if ($file != "." && $file != ".."&&strpos($file,".")) {
	                $fileArray[$i]="./imageroot/current/".$file;
	                if($i==100){
	                    break;
	                }
	                $i++;
	            }
	        }
	        //关闭句柄
	        closedir ( $handle );
	    }
	    return $fileArray;
	}

	//获取文件目录全部列表,该方法返回数组
	function getDir($dir) {
	    $dirArray[]=NULL;
	    if (false != ($handle = opendir ( $dir ))) {
	        $i=0;
	        while ( false !== ($file = readdir ( $handle )) ) {
	            //去掉"“.”、“..”以及带“.xxx”后缀的文件
	            if ($file != "." && $file != ".."&&!strpos($file,".")) {
	                $dirArray[$i]=$file;
	                $i++;
	            }
	        }
	        //关闭句柄
	        closedir ( $handle );
	    }
	    return $dirArray;
	}

	function test_dir(){
		$filename = 'D:/';
		/*$arr = $this->getFile($filename);
		var_dump($arr);*/
		$arr2 = $this->getDir($filename);
		var_dump($arr2);
	}

    function add_user_cache_data()
    {
        $_cache_key='';
        $id=120;
        $model = M('UserPortraitTbl');
        
        $cache_data = $model->field('id,user_id,goods_id')->select();
        if($cache_data)
        {
        	foreach ($cache_data as $k => $v) {
        		$_cache_key  = "new_rank_v2:pai_big_data_class:add_look_or_order_cache_data:" .'user_portrait_tbl' .'|'.$id.'|'.date("Ymd");
        		if ($v['user_id'] == $id) {
        			$array_result['id']   = $v['id'];
                    $array_result['user_id']   = $v['user_id'];
                    $array_result['key']   = $_cache_key;
                    $arr[]  = $v['goods_id'];
                    $result[] = $array_result;
        		}
        		$array_result['goods_id'] = $arr;
        	}
        	$result[] = $array_result;
        }
       
        $data = $result;
        
        var_dump( $data );

    }


    function url_hash(){
    	/*$b = 5;
    	$a = floor($b/3);
    	$c = $a == 0?0:($a*2);
    	var_dump($a,$c);exit();*/
    	$model = M('PageStayTimeTbl');
    	$res = $model->field('url_hash, stay_time, g_session_id,visit_time')->limit(60)->select();
    	//echo $model->getLastSql();exit();
    	foreach ($res as $key => $value) {
            $result[$value['url_hash']][]=$value;
            $tmp[$value['stay_time']] = $value['id'];
        }
        //TODO 3分法考虑数组越界，和少数据的情况
        foreach ($result as $k => $v) {
        	$ret[] = $this->three_algorithms($v);
        }
    	//var_dump(($result));
    	var_dump(($ret));
    }
	// 3分算法
    function three_algorithms($array){
    	//var_dump($array);
    	//$array = array_column($array,'url_hash','stay_time');
    	foreach ($array as $key => $value) {
			$stay_time[$key] = $value['stay_time'];
			$url_hash[$key] = $value['url_hash'];
		}
		array_multisort($stay_time, $url_hash, $array); 
		$s_length = count($array);
		//$start = ceil($s_length / 3);  //向上取整
		$start = floor($s_length / 3); //向下取整
		$end = $start * 2;
		for($i = $start; $i<=$end; $i++)
		{
		    $res_arr[] = $array[$i];
		}
		//print_r($array);
		unset($array);
		return $res_arr;
		/*var_dump(count($array));
		var_dump($res_arr);*/
   
    }

    function preg_data(){
    	$rules = " /^((?:\d+\.?){4}) .*?\[(.*?)\].*?imgtj\.(yueus|mojikids|51snap)\.(com|cn)\/(\S*?.css)\??(\S+)? .*?\"(http:\/\/.*?|-)\".*?\"(.*?)\".*?\"(.*?)\"/i ";
    	$rules2 = " /^((?:\d+\.?){4}) .*?\[(.*?)\].*?imgtj\.yueus\.com\/(\S*?.css)\??(\S+)? .*?\"(http:\/\/.*?|-)\".*?\"(.*?)\".*?\"(.*?)\"/i ";
    	$rules3 = " /^((?:\d+\.?){4}) .*?\[(.*?)\].*?imgtj\.[yueus\.(com|cn)|mojikids\.(com|cn)|51snap\.(com|cn)]\/(\S*?.css)\??(\S+)? .*?\"(http:\/\/.*?|-)\".*?\"(.*?)\".*?\"(.*?)\"/i ";

    	$str = '61.144.21.109 - - [21/Feb/2017:09:36:34 +0800] "GET http://imgtj.yueus.com/mojikids.css?tmp=591830.4342982634&_spx=1536x864undefined HTTP/1.1" 403 354 "http://www.mojikids.com/" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36" "yue_session_id=050782735ff99bb71d03347667093824; yue_g_session_id=6272cd5b8d3d50e77f2b5caf210885b4; visitor_flag=1487640993; visitor_p=mojikids.css; visitor_r=; visitor_b=; visitor_l=mojikids.css"';
    	$str2 = '14.29.52.9 - - [21/Feb/2017:11:00:02 +0800] "HEAD http://imgtj.yueus.com/yueyue_touch.css?url=touch://?query=a:8:{s:8:\"app_name\";s:15:\"poco_yuepai_web\";s:5:\"param\";a:4:{s:7:\"user_id\";s:0:\"\";s:15:\"client_platform\";s:2:\"pc\";s:11:\"location_id\";s:9:\"101029001\";s:16:\"request_platform\";s:3:\"web\";}s:7:\"version\";s:5:\"4.3.1\";s:7:\"os_type\";s:3:\"web\";s:5:\"ctime\";i:1487646002;s:3:\"uri\";s:1:\"/\";s:6:\"method\";s:3:\"GET\";s:10:\"ip_address\";s:11:\"221.5.39.25\";}&touch=1&ip_addr=3708102425&tmp=f15db242a85e020a4899c77787a7d080 HTTP/1.1" 403 312 "-" "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1" "--header';

    	preg_match($rules, $str, $matches);
    	//var_dump($matches);
    	echo '<br>';
    	unset($matches[3]);
    	unset($matches[4]);
    	$matches = array_values($matches);
    	//var_dump($matches);
    	preg_match($rules3, $str, $matches2);
    	var_dump($matches2);

    	/*var_dump(strpos('/disk/data/imgtj/add/imgtj-yueus.022013.gz','parser'));*/
    	//$this->mystrtotime()
    }

    function mystrtotime($time)
    {
        $curtime = strtotime($time);

        if ($curtime * 1 <= 0) {
            $regex = "|^(.*?)/(.*?)/(.*?):(.*) +.*$|";

            if (preg_match($regex, $time)) {
                $time = preg_replace($regex, "\$1 \$2 \$3 \$4", $time);
            }

            $curtime = strtotime($time);
        }

        return $curtime;
    }


    function test_my_pro($style = 'page',$data = array())
    {
    	$arr = array(
    			array(1=>1,2=>2),
    			array(3=>3,2=>2),
    			array(4=>4,2=>2),
    		);
    	$location_arr = array(array('location_id'=>'','location_name'=>'全部'));
    	$arr2 = array_merge($location_arr,$arr);
    	var_dump($arr2);
    	$str = '全部';
    	$str2 = iconv(utf8, GBK, $str);
    	$res = $this->is_utf8($str);
    	$res2 = $this->is_utf8($str2);
    	var_dump($res);
    	var_dump($res2);
    	exit();
    	$res = $this->get_name_by_part_id(49);
    	var_dump($res);
    	$str = trim('topic_id=2354&type=ling&part=40 ');
    	$num = strrpos($str, 'part=40');
    	echo $num;exit();
    	$part_id = intval(substr($str, $num+5));
    	var_dump($part_id);
    	$str2 = trim('topic_id=2354&type=wx_friend ');
    	$num2 = strrpos($str2, 'type=');
    	$part_id2 = substr($str2, $num2+5);
    	var_dump($part_id2);
    }

    function get_name_by_part_id( $part_id )
    {
        $part_name = array(
            40 => '蝈蝈小姐',
            41 => '华星桀',
            42 => '爱生活的猫',
            43 => '马琳',
            44 => '蒲之末落',
            45 => 'Yummy'
        );
        $key = array_keys($part_name);
        if(in_array($part_id,$key)) return $part_name[$part_id];
        return null;
    }

    function is_utf8($word)
	{
		if (preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function test_referer_url()
	{
		$this->display('test_referer_url');
	}

	function test_jump_url()
	{
		$url = 'http://localhost/myThinkphp/index.php/Test/Test/test_referer_url.html';
		$res = $this->get_curl_data($url);
		var_dump($res);
	}

	function test_pa_url2()
	{
		$url  = 'http://www.yueus.com///';
		//$url = 'http://www.yueus.com/?user_id=123';
		$url = 'http://www.yueus.com/?url=http%3a%2f%2fgoods.yueus.com%2f%3fgoods_id%3d2139651&puid=3c2163mm&token_info=3c21xzaw3dasdmm';

		$url = trim($url);
		$parse_url = parse_url($url);
		//var_dump($parse_url);echo "<br>";
		if(!empty($parse_url['path']) && isset($parse_url['path']) && empty($parse_url['query']))
		{
			$path_len = strlen($parse_url['path']);
			if(!empty($parse_url['query']) && ($path_len >1))
			{
				$visit_url =$url;
			}
		}
		else
		{
			//var_dump($parse_url); // $parse_url['query']
			//echo $num = substr_count($parse_url['query'],'&');
			$num = substr_count($parse_url['query'],'&');
			if($num >= 2)
			{
				/*$rules = '/^(url=){1}\w*&$ | &{1}\w*&$/i';
				preg_match($rules, $parse_url['query'], $m);
				var_dump($m);*/
				$parse_arr = explode('&', $parse_url['query']);
				var_dump($parse_arr);
				$visit_url = '';
				foreach ($parse_arr as $k => $v) {
					if(!strstr($v, 'puid='))
					{
						if(strstr($v, 'url=')) $visit_url .= $v;
						$visit_url .= '&'.$v;
					}
					else
					{
						$puid_strpos = strpos($v, 'puid=');
						$puid = substr($v, $puid_strpos+5);
					}
				}
				echo $puid;
				echo $visit_url;
			}
			else
			{
				/*$url_strpos = strrpos($parse_url['query'], 'url=');
				$visit_url = substr($parse_url['query'], $url_strpos+4);
				var_dump(urldecode($visit_url));
				$puid_strpos = strrpos($parse_url['query'], 'puid=');
				substr($parse_url['query'], $puid_strpos+5);*/

				$rules = '/^(url=){1}\w*&$ | &{1}\w*&$/i';
				preg_match($rules, $parse_url['query'], $m);
				var_dump($m);
			}
		}
		
	}

	function test_pa_url($url = '')
	{
		//$url = 'http://pa.yueus.com?url=http%3A%2F%2Fgoods.yueus.com%2F%3Fgoods_id%3D2163347&puid=24c3747ad2978d7212d16def9ad127e3';
		//$url = 'http://www.yueus.com/url=http%3a%2f%2fgoods.yueus.com%2f%3fgoods_id%3d2139651&puid=3c2163mm&token_info=3c21xzaw3dasdmm&url=http%3a%2f%2fgoods.yueus.com&c=xccc#d=dd12&a=xcv#b=1245';
		//$url = 'http://pa.yueus.com?url=http%3A%2F%2Fwww.yueus.com%2Fmall%2Fuser%2Ftopic%2Findex.php%3Ftopic_id%3D757%26online%3D1&puid=f012df394c2a4cea9caabc2e36babfc7';
		$url = 'url=http%3A%2F%2Fwww.yueus.com%2Fmall%2Fuser%2Ftopic%2Findex.php%3Ftopic_id%3D757%26online%3D1';

		$url = trim($url);
		if(!strstr($url, 'http://') && !strstr($url, 'https://'))
		{
			$url = 'http://pa.yueus.com?'.$url;
		}
		if(strstr($url, '#'))
		{
			$strrpos = strpos($url, '#');
			$omit_url = substr($url, $strrpos);
			$url = substr($url, 0,$strrpos);			
			//var_dump($url);echo "<br>";
			//var_dump($omit_url);
		}
		$parse_url = parse_url($url);
		/*var_dump(strpos($parse_url['query'],'url='));
		if((strpos($parse_url['query'],'url=') !== false))
		{
			echo 11111;
		}else{
			echo 2222;
		}exit();*/
		//var_dump($parse_url);echo "<br>";
		if($parse_url['path'] && !$parse_url['query'])
		{
			if(strstr($parse_url['path'], 'url=') && strstr($parse_url['path'], 'puid='))
			{
				$url = $parse_url['scheme'].'://'.$parse_url['host'].'/?'.substr($parse_url['path'], 1);
				if(strstr($url, '#'))
				{
					$strrpos = strrpos($url, '#');
					$omit_url = substr($url, $strrpos);
					$url = substr($url, 0,$strrpos);			
				}
				$parse_url = parse_url($url);
			}
		}

		
		//var_dump($parse_url);echo "<br>";
		/*var_dump($url);
		var_dump($omit_url);
		var_dump($parse_url);
		exit();*/
		//var_dump($parse_url); // $parse_url['query']

		//$num = substr_count($parse_url['query'],'&');
		$parse_url_query = urldecode($parse_url['query']);
		$parse_arr = explode('&', $parse_url_query);
		//var_dump($parse_arr);exit();
		$visit_url = '';
		foreach ($parse_arr as $k => $v) {
			if(!strstr($v, 'puid='))
			{
				/*if(!strstr($v, '#'))
				{*/
					if(strstr($v, 'url=') && (strpos($v,'url=') !== false) && $k == 0)
					{
					    $visit_url .= substr($v,4);
					}
					else
					{
						$visit_url .= '&'.$v;
					}
				//}
			}
			else
			{
				$puid_strpos = strpos($v, 'puid=');
				$puid = substr($v, $puid_strpos+5);
			}
		}
		//if ($omit_url) $visit_url = $visit_url.$omit_url;
		if ($omit_url)
		{
			$omit_url = $this->deal_special_symbols($omit_url);
			$visit_url = $visit_url.$omit_url;
		} 
		/*echo $puid.'<br>';
		echo $visit_url;*/
		var_dump(array('url'=>$visit_url,'puid'=>$puid));exit();
		return  array('url'=>$visit_url,'puid'=>$puid);
		
	}

	function deal_special_symbols($str = '')
	{
		//echo $str;echo '<br>';
		static $result;
		if(strstr($str, '#') && strstr($str, '&'))
		{
			$omit_begin = strpos($str, '&');
			$omit_strpos = strpos($str, '#',$omit_begin);
			//echo $omit_begin.'---'.$omit_strpos ;echo '<br>';
			if(!$omit_strpos)
			{
				$url = substr($str,$omit_begin);
				$result .= $url;
			}
			else
			{
				$url = substr($str,$omit_strpos);
				//echo $url;echo '<br/>';
				$omit_url = substr($str, $omit_begin,$omit_strpos - $omit_begin);
				$result .= $omit_url;
				//echo $result;echo '<br/>';//exit();
				$this->deal_special_symbols($url);
			}
			
			
			//return $result;
		}/*else
		{
			return $result;
		}*/
		return $result;
		
	}

	function test_referer_header()
	{
		$res = $this->get_header_info();
		var_dump($res);
		echo '*****';
		var_dump($_SERVER['HTTP_REFERER']);
	}

	function get_header_info()
	{
		$headers = array(); 
		foreach ($_SERVER as $key => $value) { 
			if (isset($_SERVER['PHP_AUTH_DIGEST'])) 
			{ 
    			$headers['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST']; 
			} elseif(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { 
    			$headers['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']); 
			}
			if (isset($_SERVER['CONTENT_LENGTH'])) { 
    			$headers['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH']; 
			} 
			if (isset($_SERVER['CONTENT_TYPE'])) { 
    			$headers['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE']; 
			}
	    	if ('HTTP_' == substr($key, 0, 5)) { 
	        	$headers[str_replace('_', '-', substr($key, 5))] = $value; 
	    	} 
		}

		return $headers;
	}

	function my_test_pa_url()
	{
		set_time_limit(36000);
		echo date('Y-m-d h:i:s');echo '<br>';
		$model = M('PaDtUrlQrcodeTbl');
		$result = $model->limit(2000,1200)->select();
		//var_dump($result);exit();
		$err_arr = array();
		$count = 0;
		foreach ($result as $k => $v) {
	        $visit_url = '';
	        $visit_url = $this->test_pa_url($v['curl']);
	        $info = $this->get_curl_data($visit_url['url']);
	        $res = $info['http_code'];
	        if($res != 200)
	        {
	        	if($res == 301 || $res == 302)
	        	{
	        		$redirect_url = file_get_contents($info['redirect_url']);
					if( empty($redirect_url) || (!strlen($redirect_url)))
					{
						$err_arr[] = array('url'=>$v['curl'],'puid'=>$v['puid'],'status'=>$res,'visit_url'=>$visit_url['url'],'visit_puid'=>$visit_url['puid']);
	            		$count++;
					}
	        	}else
	        	{
	        		//$err_arr[] = array('url'=>$v['url'],'status'=>$res,'puid'=>$v['puid']);
		            $err_arr[] = array('url'=>$v['curl'],'puid'=>$v['puid'],'status'=>$res,'visit_url'=>$visit_url['url'],'visit_puid'=>$visit_url['puid']);
		            $count++;
	        	}
	        }
    	}
    	echo $count;echo '<br/>';
	    foreach ($err_arr as $key => $value) {
	        echo $value['status'];echo ' --- ';
	        //echo $value['url'];
	        var_dump($value);
	        echo '<br/>';
	    }
	    echo '<br>结束';
	}

	function get_curl_data($url = '')
	{
		$url = 'http://a61.rrxiu.me/v/2dz24a?from_code=1dac8fcd3c195979984a4416a7c0a8a2';
		// 1. 初始化
		$ch = curl_init();
		// 2. 设置选项，包括URL
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		//var_dump($info);exit();
		//$http_code = $info['http_code'];
		// 4. 释放curl句柄
		curl_close($ch);
		var_dump($info);exit();
		return $info ;

		/*$res = file_get_contents($url);
		var_dump(strlen($res));
		var_dump($res);*/
	}

	function test_short_url()
	{ 
		$url = 'www.yueus.com';
		$short_url_class = new \Lib\ShortUrl();
		$result =$short_url_class->encode($url);
		var_dump($result);
	}

	function test_draw()
	{
		$user_str = '100000,100001,100002,100003,100004,1000005,100006,100007,100008,100009,100010,100000040';
		var_dump($user_str);
		//$user_str = str_replace('，',',',$user_str);
		$user_id_arr = explode(',', str_replace('，',',',$user_str));
		var_dump($user_id_arr);
		/*$draw_class = new \Lib\LuckyDraw();
		$res = $draw_class->make();
		var_dump($res);*/
	}

	function test_num()
	{
		$now_num = array('one'=>3,'two'=>5,'three'=>7,'four'=>3);
		$total  = 0; //期数
		$ones   = array(1=>1,2=>2,3=>30,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,0=>1);
		$twos   = array(1=>1,2=>2,3=>3,4=>4,5=>50,6=>6,7=>7,8=>8,9=>9,0=>1);
		$threes = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>70,8=>8,9=>9,0=>1);
		$fours  = array(1=>1,2=>2,3=>15,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,0=>1);
		
		$draw_num = array($ones,$twos,$threes,$fours);
		$i =0;
		foreach ($draw_num as $value) {
			if($i==0)
			{
				$num_key = 'one';
			}elseif ($i==1) {
				$num_key = 'two';
			}elseif ($i==2) {
				$num_key = 'three';
			}elseif ($i==3) {
				$num_key = 'four';
			}
			foreach ($value as $k => $v) {
				$keys = array_keys($value);
				//var_dump($keys);echo '+<br>';
				//echo $now_num["$num_key"];echo '++<br>';
				if(in_array($now_num["$num_key"], $keys) && $k==$now_num["$num_key"] )
				{
					//echo $now_num["$num_key"].'-<br>';
					/*$ones[$k].'<br>';
					echo $k.'<br>';
					echo $ones[$k] = $ones[$k]+1;*/
					$value[$k]++;
					//echo $value[$k];echo '*<br>';
					$draw_num[$i][$k]++;
					break;
				} 
			}
			
			$i++;
			//echo $i;echo '--<br>';
			
		}
		
		echo '<br>';
		var_dump($draw_num);
		$total = array_sum($draw_num[0]);
		//平均值之一 如果大于等于这个几率，接近这个几率
		//越接近平均值,出现的记录越接近出现值

	}

	function test_js_index()
	{
		$expire = 1806204809;
		$identify = 'anonymous';
		$access_key = 'd28c3528e24ef9da8e31c71cb28011424c7b1c20';
		$access_token = 'aaea65ff0c57b902e74992c2238a507540fbe825';
		$data = '';
		$url = "http://optimus.poco.cn/event/insert?expire=$expire&identify=$identify&access_key=$access_key&access_token=$access_token&data='urlsafebase64encode($data)'";
		$res = file_put_contents($url);
		var_dump($res);
	}

	function test_js_tj()
	{
		$url = 'http://optimus.poco.cn/event/insert?expire=1806204809&identify=anonymous&access_key=d28c3528e24ef9da8e31c71cb28011424c7b1c20&access_token=aaea65ff0c57b902e74992c2238a507540fbe825&data=eyJ2YSI6eyJzIjoxMiwicCI6MTJ9LCJ2YiI6eyJuZXRBbGwiOjYzLCJuZXREbnMiOjMwLCJuZXRUY3AiOjE5LCJzcnYiOjY3fSwiZXZlbnRfbmFtZSI6InN5c3RlbSJ9';
		$res = makeRequest($url);
		var_dump($res);
		
	}

	function test_count_time_data()
	{
		$url = 'http://report.yueus.com/real_time_data/real_time_analysis_data.php?project_type=&begin_time=2017-06-12+09%3A12&end_time=2017-06-12+10%3A30&act=';
		$url_data = get_url_data($url);
		var_dump($url_data);
		$res = $this->test_real_time_data();
		/*echo '<pre>';
		var_dump($res);echo '<br/>';
		echo '</pre>';*/
		if(count($res)>=2) echo '超过255<br>';

		$params = array(
		            'total_rows'=>100, #(必须)
		            'method'    =>'html', #(必须)
		            'parameter' =>'xxx.com/20-0-0-0-40-?.html',  #(必须)
		            'now_page'  =>$_GET['p'],  #(必须)
		            'list_rows' =>10, #(可选) 默认为15
		);
		$page = new \Lib\Page($params);
		echo  $page->show(1);

		echo '<pre>';
		//var_dump($res);
		echo '</pre>';
		foreach ($res as $k => $arr) {
			//if($k == 2 )var_dump($v);
			echo '<pre>';
			var_dump(count($arr));
			//var_dump($arr);
			if(count($arr)<=30) var_dump($arr);
			//var_dump($arr);
			/*foreach ($arr as $k => $v) {
				echo count($v);echo "<br/>";
			}*/
			echo '</pre>';
			/*foreach ($arr as $v) {
				echo $time  = intval($v['time']/1000000000);echo '<br>';
			}*/
			//echo $time  = ($v['time']/100000000);echo '<br>';
			//$this->add_real_time_data($arr);
		}
	}

	function test_real_time_data($offset = 0, $max_count = 255)
	{
		static $result;
		ini_set('precision', 20);
		$module     = 'optimus';         //模块名称
        $func_name  = '/event/realtimeFlow'; //方法名称
        $time       = time();           //执行时间
        $key        = 'bodyauth';       //钥匙
        //$begin_time = strtotime(date('Y-m-d h:i:00'));
        $begin_time = strtotime(date('2017-6-12 09:00:00'));
        $begin_time = strtotime(date('2017-7-1 09:00:00'));
        //$end_time   = strtotime(date('Y-m-d h:i:59'));
        $end_time   = strtotime(date('2017-6-12 18:00:59'));
        $end_time   = strtotime(date('2017-7-8 18:00:59'));

        $params['event_name'] = 'yueyue';
        $params['offset'] = (int)$offset;
        //$params['max_count'] = (int)$max_count;
        $params['start_nstime'] = $begin_time * 1000000000;
        $params['end_nstime'] = $end_time * 1000000000;

        $json_params = json_encode($params);
        $sign = sha1($module . $func_name . $json_params . $time . $key);
        //构造发送内容
        $post_array['time'] = $time;
        $post_array['sign'] = $sign;
        $post_array['params'] = $params;

        $expire = 1806204809;
        $identify = 'anonymous';
        $access_key = 'd28c3528e24ef9da8e31c71cb28011424c7b1c20';
        $access_token = 'aaea65ff0c57b902e74992c2238a507540fbe825';
        $url = "/event/realtimeFlow?expire=$expire&identify=$identify&access_key=$access_key&access_token=$access_token";

        $request = $this->http_post_json($url, json_encode($post_array),'optimus.poco.cn');
        $json_data = json_decode($request[1], true);
        $json_values = $json_data['data']['Values'];
        //var_dump($json_values);exit();
        $result[] = $json_values;
        if(count($json_values) == 255)
        {
        	$offset = $offset + 255;
        	$res = $this->test_real_time_data($offset);
        }

        //return array('c'=>$offset,'result'=>$result);
        return $result;
        
	}

	private function http_post_json($url, $jsonStr, $host = 'optimus.poco.cn')
    {
        /*$post_url   = 'http://14.18.242.143';*/
        //$post_url   = 'http://optimus.poco.cn';
        $post_url   = 'http://optimus-api.yueus.com';
        $host = 'optimus-api.yueus.com';
        $url = $post_url . $url;
        //var_dump($url);echo '<br>';//exit();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Host:' . $host ,
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $log_arr['post_url'] = $post_url;
        $log_arr['url'] = $url;
        $log_arr['jsonStr'] = $jsonStr;
        $log_arr['status'] = array($httpCode, $response);
        

        //发送log 开始
        //pai_log_class::add_log($log_arr, 'IM2_HTTP_POST_JSON', 'IM_SERVER');
        //发送log 结束

        return array($httpCode, $response);
    }

    private function array_to_object($arr)
    {
        if (gettype($arr) != 'array')
        {
            return;
        }
        foreach ($arr as $k => $v)
        {
            if (gettype($v) == 'array' || getType($v) == 'object')
            {
                $arr[$k] = (object)$this->array_to_object($v);
            }
        }
        return (object)$arr;
    }

    function getInt64($strMd5Val)
	{
	    $intStrLen = strlen($strMd5Val);
	    $arrMd5Val = array();
	    for ($i = 0; $i < $intStrLen; ++$i)
	    {
	        $arrMd5Val[$i] = substr($strMd5Val, $i, 1);
	    }
	    $intStrHalfLen = $intStrLen / 2;
	    $arrRes = array();
	    $arrRes[0] = intval(0);
	    $arrRes[1] = intval(0);
	    for ($i = 0; $i < $intStrHalfLen; ++$i)
	    {
	        $arrRes[0] = intval((($arrRes[0]<<4)|getDecimalVal($arrMd5Val[$i])));
	        $arrRes[1] = intval((($arrRes[1]<<4)|getDecimalVal($arrMd5Val[$intStrHalfLen + $i])));
	    }
	    return $arrRes;
	}

	function NumToStr($num){
	    if (stripos($num,'e')===false) return $num;
	    $num = trim(preg_replace('/[=\'"]/','',$num,1),'"');//出现科学计数法，还原成字符串
	    $result = "";
	    while ($num > 0){
	        $v = $num - floor($num / 10)*10;
	        $num = floor($num / 10);
	        $result   =   $v . $result;
	    }
	    return $result;
    }

    function add_real_time_data($add_data)
    {
    	$model = M('RealTimeDataTbl');
    	$add['va'] = $add_data['va']?$add_data['va']:'';
    	$add['vb'] = $add_data['vb']?$add_data['vb']:'';
    	$add['project_type'] = $add_data['project_name']?$add_data['project_name']:'';
    	$add['event_name'] = $add_data['event_name']?$add_data['event_name']:'';
    	$add['referrer_url'] = $add_data['referrer']?$add_data['referrer']:'';
    	$add['referrer_domain'] = $add_data['referrer_domain']?$add_data['referrer_domain']:'';
    	$add['first_visit_time'] = $add_data['ft']?$add_data['ft']:'';//首次浏览时间
    	$add['first_visit_referer_url'] = $add_data['fc']?$add_data['fc']:'';//当前访问地址
    	$add['first_visit_url'] = $add_data['fr']?$add_data['fr']:'';//首次来源地址
    	$add['last_visit_time'] = $add_data['tr']?$add_data['tr']:'';//上次页面访问时间
    	$add['now_visit_time'] = $add_data['tc']?$add_data['tc']:'';//当前页面访问时间
    	//$add['user_id'] = $add_data['uid']?($add_data['uid'] == 'anonymous'?'':$add_data['uid']):''; // 用户ID
    	$add['user_remarks'] =  $add_data['ur']?$add_data['ur']:''; //用户备注
    	$add['visit_pages'] = $add_data['tp']?$add_data['tp']:''; //本次访问页数
    	$add['push_id'] = $add_data['pp']?$add_data['pp']:'';  //推广ID
    	$add['browser_resolution'] = $add_data['bf']?$add_data['bf']:''; //浏览器分辨率
    	$add['user_agent'] = $add_data['user_agent']?$add_data['user_agent']:'';//UA
    	$add['ip'] = $add_data['ip']?$add_data['ip']:'';
    	$add['isp'] = $add_data['isp']?$add_data['isp']:'';
    	$add['g_session_id'] = $add_data['session_id']?$add_data['session_id']:'';
    	$add['user_id'] = $add_data['ui']?$add_data['ui']:'';
    	$add['country'] = $add_data['country']?$add_data['country']:'';
    	$add['province'] = $add_data['province']?$add_data['province']:'';
    	$add['city'] = $add_data['city']?$add_data['city']:'';
    	$add['add_time'] = date('Y-m-d h:i:s');
    	//var_dump($add);exit();
    	$res = $model->data($add)->add();
    	echo $model->getLastSql();
    	//echo '';
    	//var_dump($res);
    	
    }

    function get_project_name($url='')
    {
    	$url = "http%3A%2F%2Flist.yueus.com%2Fmarket%2F";
    	$url = "http://list.yueus.com/market";
    	if(empty($url))  return false;
    	$yueyue = array('yueus');
    	$url = urldecode($url);
    	echo $url;
    	$parse_url = parse_url($url);
    	var_dump($parse_url);
    	if($parse_url)
    	{
    		if(strpos($parse_url['host'],'yueus') !== false) return 'yueyue';
    		if(strpos($parse_url['host'],'share') !== false) return 'share';
    		if(strpos($parse_url['host'],'supe') !== false)  return 'supe';
    		if(strpos($parse_url['host'],'mojikids') !== false) return 'mojikids';

    	}else
    	{
    		return '';
    	}
    }

    function deal_real_time_data($str)
    {
    	$arr = array('bf','event_name','fc','fr','ft','ip','isp','referrer','referrer_domain','rl','tc','time','tp','tr','uid','user_agent');
    	if(in_array($str, $arr)) return $str;
    	return false;
    }

    function test_send_real_time_data()
    {
    	$host = 'optimus.poco.cn';
    	$jsonStrLen = 2000;
        $url = 'http://optimus.poco.cn/event/insert?expire=1806204809&identify=anonymous&access_key=d28c3528e24ef9da8e31c71cb28011424c7b1c20&access_token=aaea65ff0c57b902e74992c2238a507540fbe825&data=eyJ0aW1lIjoxNDk0MjkzODQyLCJwYXJhbXMiOnsiZXZlbnRzIjpbeyJuYW1lIjoicGFnZSIsImZpZWxkcyI6eyJybCI6MCwiZnQiOjE0OTQyOTM4NDIsImZyIjowLCJmYyI6Imh0dHAlM0ElMkYlMkYxMjcuMC4wLjElMkZ5dWV5dWVfcmVwbyUyRnl1ZV91aSUyRnRlc3QlMkZ0ai10ZXN0MS5odG1sIiwidHIiOjAsInRjIjoiMTQ5NDI5Mzg0MiIsInRwIjoxLCJiZiI6IjE5MjAqMTA4MCIsImV2ZW50X25hbWUiOiJwYWdlIn19XX0sInNpZ24iOiIwN2QyY2U1YzhmNWI3YWZjMzRlMzY1YThjZDQ5OTBhMjVjOWJhYzVkIn0=';
        //var_dump($url);echo '<br>';
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Host:' . $host ,
                'Content-Type: application/json; charset=utf-8',
                //'Content-Length: ' . strlen($url)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //$res = get_curl_data($url);
        //$res = file_put_contents($url);
        //return array($httpCode, $response);
        echo '<pre>';
        var_dump(array($httpCode, $response));
        //var_dump($res);
        echo '</pre>';
        
    }

    public function group_array($array='')
    {
    	$limit_num = 4;
		$list = array(1,2,3,4,5,6,7,8,9);
		$count = count($list);
		if($count>$limit_num)
		{
			$num = ceil($count/$limit_num);
			echo $num.'<br>';
			for ($i=0; $i < $num ; $i++) { 
				if($i == 0)
				{
					$arr = array_slice($list, 0, $limit_num);
				}
				elseif($i == ($num-1))
				{
					$arr = array_slice($list, $i*$limit_num);
				}
				else
				{
					$arr = array_slice($list,$i*$limit_num,$limit_num);
				}
			
		 		$new_arr[] = $arr;
		 		$arr = '';
		 		//echo $i.'+'.$num.'=='.(2*$i+$num-1).'<br>';

			}
		}else
		{
			$new_arr[] = $list;
		}
		//var_dump($new_arr);
		print_r($new_arr);
		foreach ($new_arr as $k => $v) {
			var_dump($v);
		}
		//var_dump(array_slice($list, 0, $num-1));
    }

   public function add_time()
   {
   	 $num = 10;
   	  // 查询数据过多，分次查询 ，分次入库
   	  // 如果一次性能完成，就一次性完成
   	  if(  $num > 100 )
   	  {
   	  	 echo '超过数量';
   	  }else
   	  {
   	  	 echo '其他操作';
   		 $name = 'this is name';
   		 $age = 12;
   		 $data['name'] = $name;
   		 $data['age'] = $age;
   		 var_dump($data);
   		 $result = $this->ajaxReturn($data);
   		 var_dump($result);

   		 // 数据量统计
   		 // 数据分组 组合重新分批
   		 // 分批的数据检查？是否推送
   	  }
   }

   public function fine_bi()
   {
   	// 功能:商业智能数据分析 -PS 重要操作 1更新多维数据库 2保存
   		// 1. 业务包(表与表之前关联)
   	    // 2. ETL(分析原有数据满足分析) , 明细分析 ,服务器数据集(表)
   		// 3. 表格制作 时间默认月(维度折叠)
   		// 4. 图表(柱状图，饼图，折线图，地图，仪表盘)
   		// 5. 计算指标(公式类 ，配置类)
   	    // 6. 过滤设置(指标，维度筛选)
   		// 7. 排序
   		// 8. 控件设置(筛选) 可在finesbox直接进行(文本类，数值类，时间类控件)
   		// 9. 通用查询(查看者能自由筛选数据内容)
   		// 10.管理驾驶舱(自由设置，多个控件，图表的相互关联)
   		// 11.exvelview功能
   		// 12.分析结果保存
		// 13.权限设置 
   }

   function get_json_data()
   {
   		$get_data  = $_GET;
   		$post_data = $_POST;
   		if($post_data)
   		{
   			sleep(5);
   			$post_data['content'] = 'Hello '.date('Y-m-d h:i:s');
   			echo json_encode($post_data);exit();
   		}
   		$now_time = time();
   		$now_date = date('Y-m-d h:i:s');
   		$result = array('state'=>0,'name'=>'linming','content'=>$now_date);
   		if($get_data)
   		{
   			$result['content'] = $now_date.' '.$now_time;
   			echo json_encode($result);
   		}
   		else
   		{
   			echo json_encode($result);
   			
   		}
   }

   public function check_im_real_time_data_table($table_name='')
   {
   	    $first_day = date('d',1496246400); 
   	    var_dump($first_day);
   	    $date_time = date('Ym',time());
	   	$table_name = trim('pai_im_real_time_data_tbl_'.$date_time);
	   	$res = $this->check_table_exists($db_name='test',$table_name);
	   	var_dump($res);
   	    if($first_day == '01' && !$res)  //每月1号
   	    {
	        //$table_str_name = 'pai_pa_referer_url_log_'.date('Ymd',time());
	        $sql_str = " CREATE TABLE IF NOT EXISTS {$table_name} (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `project_type` char(20) NOT NULL,
						  `event_name` char(20) NOT NULL,
						  `pv` int(11) NOT NULL,
						  `uv` int(11) NOT NULL,
						  `ip` int(11) NOT NULL,
						  `add_time` date NOT NULL,
						  PRIMARY KEY (`id`),
						  KEY `add_time` (`add_time`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
	        $model = M();
	        $result = $model->query($sql_str);
	        var_dump($result);
   	    }
   }

   public function check_table_exists($db_name='',$table_name='')
   {
        $sql = " show tables from $db_name like '%{$table_name}%' ";
        //$ret = db_simple_getdata($sql,false,$this->yueyue_report_host_ip);
        $model = M();
	    $result = $model->query($sql);
        if(!$result) return $result = array();
        return true;
   }

   public function add_im_date_real_time_data($add_data='')
   {
   		$begin_time = strtotime(date('Y-m-d 00:00:00',time()));
   		$begin_time = strtotime('2017-06-01');
   		$end_time = strtotime(date('Y-m-d 23:59:59',time()));
   		$end_time = strtotime('2017-06-02');
   		$where = "where now_visit_time >= $begin_time and now_visit_time <= $end_time ";
   		$sql = " SELECT project_type,COUNT(*) AS PV, COUNT(DISTINCT(ip)) AS IP, COUNT(DISTINCT(g_session_id)) AS UV FROM `test`.`pai_real_time_data_tbl` $where group by project_type";
   		$im_model = M('RealTimeDataTbl');
   		$res = $im_model->query($sql);
   		var_dump($res);
   		//exit();
   		$model = M('ImRealTimeDataTbl_201706');
   		foreach ($res as $k => $v) {
   			$add_data['project_type'] = $v['project_type'];
   			$add_data['pv'] = $v['pv'];
   			$add_data['uv'] = $v['uv'];
   			$add_data['ip'] = $v['ip'];
   			$add_data['add_time'] = date('Y-m-d');
   			//$result = $model->data($add_data)->add();
   			var_dump($add_data);
   			//var_dump($result);
   		}
   }

    /*
    * 临时表 表名替换
    */
    public function change_table_name($table_name,$is_do = false)
    {
        $add_time = date('Ymd',time()-3600*24);
        $new_table_name = "`test`.`{$table_name}_{$add_time}`";
        $temporary_table_name = "`test`.`{$table_name}_tmp`";  //临时表名

        // 数据异常，保持原表，修改临时表
        if( $is_do )
        {
            $sql_str = "RENAME TABLE $temporary_table_name TO $new_table_name";  //临时表异常 改名处理
            //db_simple_getdata($sql_str, TRUE, 24);
            return ;
        }

        /* var_dump($new_table_name);echo "\r\n";
         var_dump($temporary_table_name);exit;*/
        $sql_str = "RENAME TABLE `test`.`$table_name` TO $new_table_name";  //原表改名
        $sql_new_str = "RENAME TABLE $temporary_table_name TO `test`.`$table_name`";  //正式表改名
        /*var_dump($sql_str);echo "\r\n";
        var_dump($sql_new_str);exit;*/
        try {
            db_simple_getdata($sql_str, TRUE, 24);
            db_simple_getdata($sql_new_str, TRUE, 24);
        } catch (Exception $e) {
            echo '数据有误Caught exception: ', $e->getMessage(), "\n";
        }
    }

     /**
		* 1 获取 统计JS的数据
		* 2 先创建每日 日表，数据入库     - pai_im_real_time_data_tbl_2017xxxx
		* 3 定时执行 每日数据  汇总到 总表 - pai_im_main_real_time_data_tbl
		* 4 定时执行 收集每日数据的 PV UV IP - pai_im_real_time_data_tbl
     */

/**
* 创建每日 统计数据表
*/
	public function create_new_table()
	{
		$date_time = date('Ym',time());
	   	$table_name = trim('pai_real_time_data_tbl_'.$date_time);
	   	$res = $this->check_table_exists($db_name='test',$table_name);
		$sql_str = " CREATE TABLE `$table_name` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `project_type` char(10) NOT NULL COMMENT '项目 类型',
		  `event_name` char(50) NOT NULL COMMENT '事件名',
		  `va` text NOT NULL COMMENT '服务后端',
		  `vb` text NOT NULL COMMENT '服务前端',
		  `referrer_url` varchar(250) NOT NULL COMMENT '来源地址(rl)',
		  `referrer_domain` char(50) NOT NULL COMMENT '来源地址域名',
		  `first_visit_time` int(11) NOT NULL COMMENT '首次浏览时间(ft',
		  `first_visit_referer_url` varchar(250) NOT NULL COMMENT '首次来源地址( fr',
		  `first_visit_url` varchar(250) NOT NULL COMMENT '首次访问地址( fc',
		  `last_visit_time` int(11) NOT NULL COMMENT '上次页面访问时间( tr',
		  `now_visit_time` int(11) NOT NULL COMMENT '本次页面访问时间(tc',
		  `visit_pages` int(11) NOT NULL COMMENT '本次访问页数( tp',
		  `user_id` int(11) NOT NULL COMMENT '用户ID( ui',
		  `user_remarks` varchar(250) NOT NULL COMMENT '用户备注(ur',
		  `push_id` varchar(250) NOT NULL COMMENT '推广ID(pp',
		  `browser_resolution` char(10) NOT NULL COMMENT '浏览器分比率(bf',
		  `user_agent` text NOT NULL COMMENT 'UA',
		  `g_session_id` char(50) NOT NULL,
		  `ip` char(20) NOT NULL COMMENT 'IP',
		  `isp` char(20) NOT NULL COMMENT '网络运营商',
		  `country` char(10) NOT NULL COMMENT '国家',
		  `province` char(10) NOT NULL COMMENT '省份',
		  `city` char(10) NOT NULL COMMENT '城市',
		  `add_time` datetime NOT NULL COMMENT '入库时间',
		  PRIMARY KEY (`id`),
		  KEY `project_type` (`project_type`),
		  KEY `now_visit_time` (`now_visit_time`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ";
		$model = M();
	    $result = $model->query($sql_str);
	}

	public function add_main_real_time_data_tbl()
	{
		$sql_str = " select project_type,event_name,va,vb,referrer_url,referrer_domain,first_visit_time,first_visit_referer_url,first_visit_url,last_visit_time,now_visit_time,visit_pages,user_id,user_remarks,push_id,browser_resolution,user_agent,g_session_id,ip,isp,country,province,city from `test`.`pai_real_time_data_tbl_20170619` ";
		//$sql_str = " SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name = 'pai_real_time_data_tbl_20170619' AND table_schema = 'test'; ";
		$model = M('RealTimeDataTbl_20170619');
		$result = $model->query($sql_str);
		/*foreach ($result as $key => $value) {
			$str .= $value['column_name'].',';
		}
		var_dump($result);exit();*/
		if($result)
		{
			$add_time = date('Y-m-d h:i:s',time());
			foreach ($result as $k => $v) 
			{
				$v['add_time'] = $add_time;
				var_dump($v);exit();
				//$this->add_real_time_data($v);
				echo M()->getLastSql();
				var_dump($v);exit();
			}
		}

	}

	function get_title_json_data()
   {
   		$get_data  = $_GET;
   		$post_data = $_POST;
   		if($post_data)
   		{
   			//sleep(5);
   			$post_data['content'] = 'Hello '.date('Y-m-d h:i:s');
   			echo json_encode($post_data);exit();
   		}
   		$now_time = time();
   		$now_date = date('Y-m-d h:i:s');
   		$result = array('state'=>0,'name'=>'linming','content'=>$now_date);
   		if($get_data)
   		{
   			$result['content'] = $now_date.' '.$now_time;
   			echo json_encode($result);
   		}
   		else
   		{
   			echo json_encode($result);	
   		}
   }

   function test_while()
    {
    	header("Content-type: text/html; charset=utf-8"); 
        $sum = 1;
        while (1) {
            if($sum <=6)
            {
                echo '当前时间:'.date('Ymd h:i:s').'<br/>';
                $sum++;
                sleep(1);
            }
            else
            {
                echo 'over sum='.$sum;
                break;
            }
        }
    }

    function test_db_log()
    {
    	$file_path = "D:/tmp/mySql.log";
		/*if(file_exists($file_path)){
			$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
			$str = str_replace("\r\n","<br />",$str);
		    echo $str;
	    }*/
	    /*if(file_exists($file_path)){
			$file_arr = file($file_path);
			for($i=0;$i<count($file_arr);$i++){//逐行读取文件内容
				echo $file_arr[$i]."<br />";
		    }   
	    }*/
	    $data = read_log_data($file_path);
	    //var_dump($data);exit();
	    $arr = explode("\t", $data); 
	    var_dump($arr);exit();
	    $result =array();
	    foreach ($arr as $k => $v) {
	    	//echo $v;exit();
	    	if(strripos($v,'select')) $result[]=$v;
	    }
	    var_dump($result);

    }

    function sql_data()
    {
    	$model = M('user');
    	$data1 = $model->where('id=1')->field('name')->find();
    	$data2 = $model->where(array('id' => 1))->getField('name');
    	var_dump($data1);
    	var_dump($data2);
    }

    public function get_department_all_parent_id($parent_id)
    {
        static $parent_id_array;
        //static $last_parent_id;
        //$department_model = M('department');
        $department_model = M('department',null,'DB_CONFIG3');
        $next_parent_id = $department_model->where(array('id'=>$parent_id))->getField('parent');
        //echo $parent_id.'<br>';
        /*if($next_parent_id > 0)
        {
        	 //$last_parent_id = $next_parent_id;
        	 $parent_id_array[] = $next_parent_id;
        	 $this->get_department_all_parent_id($next_parent_id);
        }else
        {
        	$parent_id_array[] = $parent_id;
        	//$last_parent_id = $parent_id;
        }*/
        $parent_id_array[] = $parent_id;
        //echo $parent_id.'<br>';
        if($next_parent_id > 0)
        {
            //$last_parent_id = $next_parent_id;
            //$parent_id_array[] = $next_parent_id;
            $this->get_department_all_parent_id($next_parent_id);
        }
        return $parent_id_array;
        
    }

    private function get_department_all_parent_id2($parent_id)
    {    
        $department_model = M('department',null,'DB_CONFIG3');
        $next_parent_id = $department_model->where(array('id'=>$parent_id))->getField('parent');
        $parent_id_array[] = $parent_id;
        if($next_parent_id > 0)
        {
            $parent_id_array = array_merge($parent_id_array,$this->get_department_all_parent_id($next_parent_id));
        }
        return array_unique($parent_id_array);
    }

    private function get_department_all_parent_id3($parent_id,&$parent_id_array)
    {    
        $department_model = M('department',null,'DB_CONFIG3');
        $next_parent_id = $department_model->where(array('id'=>$parent_id))->getField('parent');
        if($next_parent_id > 0)
        {
            $parent_id_array[] = $next_parent_id;
            $this->get_department_all_parent_id3($next_parent_id,$parent_id_array);
        }
        //return array_unique($parent_id_array);
    }

    private function demo1($son_id,&$parent_ids)
    {
    	$model = M('parent','ts_','DB_CONFIG2');
        $parent_id = $model->where(array('son_id'=>$son_id))->getField('parent_id');
       // echo $parent_id;exit;
        if($parent_id > 0)
        {
            $parent_ids[] = $parent_id;
            $this->demo1($parent_id,$parent_ids);
        }
    }

    private function demo2($son_id,$parent_ids)
    {
    	$model = M('parent','ts_','DB_CONFIG2');
        $parent_id = $model->where(array('son_id'=>$son_id))->getField('parent_id');
        if($parent_id > 0)
        {
            $parent_ids[] = $parent_id;
            $this->demo2($parent_id,$parent_ids);
        }else
        {
        	return $parent_ids;
        }
    }

    private function demo3($son_id)
    {
    	$parent_id_array = array();
    	$model = M('parent','ts_','DB_CONFIG2');
        $parent_id = $model->where(array('son_id'=>$son_id))->getField('parent_id');
        $parent_id_array[] = $parent_id;
        if($parent_id > 0)
        {
            $parent_id_array = array_merge($parent_id_array,$this->demo3($parent_id));
        }
        return array_unique($parent_id_array);
    }

    public function test_parent_id($id=100)
    {
    	//$id = 128;
    	//$id = 177;
    	//$res = $this->get_department_all_parent_id2($id);
    	$parent_ids = array();
    	$res = $this->demo3($id,$parent_ids);
    	var_dump($parent_ids);
    	var_dump($res);
    	var_dump(end($res));
    	var_dump(reset($res));
    }

    public function test_session($session=array('name' =>'zhangsan'))
    {
    	if(!$session) return false;
    	// 初始化缓存
    	/*$cache = S(array('prefix'=>'think','expire'=>600));
    	$cache->name = $session; // 设置缓存*/
    	//cookie('session',$session,array('expire'=>15,'prefix'=>'think_'));
    	//session(array('prefix'=>'think_','expire'=>10));
    	session(array('name'=>$session,'expire'=>10));
    	// unset($cache->name); // 删除缓存   
    }

    public function test_get_session()
    {
    	/*$cache = S(array('prefix'=>'think','expire'=>600));
    	$value = $cache->name;  // 获取缓存*/
    	var_dump(session('?name'));
    	//session(array('prefix'=>'think_','expire'=>10));
    	$value = session('name');
    	var_dump($value);
    	//session('name',null);
    	//S('name',null);
    }

    public function test_get_user()
    {
    	header("Content-type: text/html; charset=utf-8"); 
    	$user_info = M('employee',NULL,'DB_CONFIG3')->where(array('id'=>2078))->getField('id,dep_id,name');
    	/*$map['id'] = array('egt','2078');
    	$map['name'] = array('like',"秋%");
    	$user_info = M('employee',NULL,'DB_CONFIG3')->where($map)->limit(5)->getField('id,dep_id,name',true);*/
    	echo M('employee',NULL,'DB_CONFIG3')->getLastSql();
    	//$user_info = M('employee',NULL,'DB_CONFIG3')->where($map)->field('id,dep_id,name')->find();
    	echo '<pre>';
    	var_dump($user_info);
    	echo '</pre>';
    }

    public function test_area()
    {
    	//header("Content-type: text/html; charset=utf-8");
    	$file_path = "D:/001.txt";
		if(file_exists($file_path)){
			$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
			$arr = explode('#', $str);
			foreach ($arr as  $v) {
				//$arr2 = array();
				//$temp = array();
				$arr2 = explode('$', $v);// 0-省 1- x市zz|x市yy
				$name = $arr2[0];
				$temp['name'] = $name;
				//foreach ($arr2 as $v2) {
				$arr3 = explode('|', $arr2[1]);// 0-a市+区 1-b市+区  
					//var_dump($arr3);exit();
				foreach ($arr3 as $v3) {
					//$num = strpos($v3, ',');
					$v3_arr = explode(',',$v3);
					if($v3_arr && is_array($v3_arr))
					{
						//array_map(' ',$v3_arr);
						/*foreach ($v3_arr as $key => $value) {
							$v3_arr[$key] = "'".$value."'";
						}*/
					}
					$v_str = implode(',', $v3_arr);
					/*var_dump($v3);
					var_dump($v3_arr);
					var_dump($v_str);*/
					//exit();
					$num = strpos($v_str, ',');
					//var_dump(substr($v_str,$num+1));
					$str_arr = explode(',', substr($v_str,$num+1));
					//var_dump($str_arr);exit();
					$area[] = array(
						'name'=>  substr($v_str,0,$num),
						//'areaList'=>  substr($v_str,$num+1),
						'areaList'=> $str_arr,
					);
					/*$area[] = array(
						'name'=>  substr($v3,0,$num),
						'areaList'=>  substr($v3,$num+1),
					);*/
					$str_arr = array();
				}
				//}
				$temp['cityList'] = $area;
				$area = array();
				$pro[]=$temp;
			}	
	    	$t_arr = array(
	    		'name'=>'北京市',
	    		'cityList'=>array(
	    			array(
	    				'name'=>'北京市2',
						'areaList'=>array(1,2,3,4,5,6),
    				),
	    			array(
	    				'name'=>'县区',
    					'areaList'=>array(10,11,12),
    				),
    			),
    		);
    		/*echo '<pre>';
	    	var_dump($pro);
	    	echo '</pre>';*/
	    }
	    
	    //$res = $this->ajaxReturn($pro);
	    //return $res;
	    //$res = json_encode(array($t_arr,$t_arr));
	    $res = json_encode($pro);
	    //file_put_contents("test.txt", $res, FILE_APPEND);
	    echo '<pre>';
    	var_dump($res);
    	echo '</<pre>';
    	//$str = implode('**', $pro);
    	//echo $str;
    	//
    
    }

    public function get_area_json()
    {
    	$test_arr = array(
			array(
				'name'=>'北京',
				'cityList'=>array(
					array(
						'name'=>'市区',
						'areaList'=>array(
							'东城区','西城区'
						),
					),
					array(
						'name'=>'县',
						'areaList'=>array(
							'密云县','延庆县'
						),
					),
				),
			),
		); 
		//$res = $this->ajaxReturn($test_arr);
		$res = json_encode($test_arr);
		var_dump(__EXT__);
    	//$res = $this->test_area();
    	var_dump($res);
    }

    /*
     * 添加酒店和房型 事务例子
     * */
    public function insertAll($arr_hotel=array(),$arr_room=array()){
        $model = new Model();
        $model->startTrans();
        $flag=false;
        $hid = $model->table(C('DB_PREFIX').'hotel')->add($arr_hotel);
        if( $hid && count($arr_room) ==0 )
        {//如果没有传入房型的信息则,直接提交数据
          
            $flag=true;
        }
        else if( $hid && count($arr_room) >= 0)
        {//存在对应房型信息,则添加对应的酒店编号,并处理提交
            for($i=0 ; $i<count($arr_room) ; $i++)
            {
                $arr_room[$i]['hid'] = $hid;
            }
            $rid = $model->table(C('DB_PREFIX').'room')->addAll($arr_room);
            if( $rid )
            {
                $model->commit();
                $flag=true;
            }
        }
        if(!$flag)
        { 
           $model->rollback();
        }
        else
        {
           $model->commit();
        }
        return $flag;
    }

    //public function test_kuaidi($express_data=array())
    public function test_kuaidi()
    {
    	header("Content-type: text/html; charset=utf-8"); 

    	/*if (empty($express_data)) {
    		return array();
    	}*/
    	$express_data = array(
	    		'no'=>'811898215423',
	    		'type'=>''
		); 
    	$type = $express_data['type']?$express_data['type']:'auto';
    	$no = $express_data['no'];
    	$host = "http://wuliu.market.alicloudapi.com";
	    $path = "/kdi";
	    $method = "GET";
	    $appcode = "f7b00a3f14fd41e4aea219c0b8a3ee6b";
	    //$appcode = "530787c7dce84686adae544e76ef5c3d";
	    $headers = array();
	    array_push($headers, "Authorization:APPCODE " . $appcode);
	    //$querys = "no=462587770684&type=zto";
	    $querys = "no=$no&type=$type";
	    $bodys = "";
	    $url = $host . $path . "?" . $querys;

	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    //curl_setopt($curl, CURLOPT_HEADER, true);
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    if (1 == strpos("$".$host, "https://"))
	    {
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    //var_dump(curl_exec($curl));
	    $output = curl_exec($curl);
	    //return $output;
	    echo '<pre>';
	    var_dump(json_decode($output,ture));
	    echo '</pre>';
    }

    public function test_wuliu($find_data=array())
    {
    	//查询数据库记录，存在直接返回数据，不存在则调用快递接口,入库
    	/*$find_data = array(
    		'no'=>'811898215423',
    		'type'=>''
		); */
		/*$find_data = array(
    		'no'=>'EA165135000CN',
    		'type'=>'EMS'
		); */
		if (!$find_data) {
			$result_res['status'] = 0;
    		$result_res['msg'] = '非法数据,请检查数据格式！';
    		$this->ajaxReturn($result_res);
		}
    	$json_res = $this->test_kuaidi($find_data);
    	$res = json_decode($json_res,ture);
    	echo '<pre>';
    	var_dump($res);
    	echo '</pre>';exit();
    	if(!$res['status']) // 状态 0 正常
    	{
    		$sign_time = '';
    		$log = '';
    		if($res['result']['deliverystatus']==3)
    		{
    			$sign_time = strtotime($res['result']['list'][0]['time']);
    			$log = json_encode($res['result']['list']);
    		}
			$add['bill_id'] = 10000;
			$add['company_code'] = $res['result']['type'];
			$add['courier_no'] = $res['result']['number'];
			$add['waybill_process'] = json_encode($res['result']['list']);
			$add['waybill_state'] = $res['result']['deliverystatus']; //1在途中 2正在派件 3已签收 4派送失败
			$add['sign_time'] = $sign_time;
			$add['log'] = $log;
			$add['add_time'] = time();
			$add['update_time'] = time();
			$model = M('courier_bill_log',null);
			$id = $model->data($add)->add();
			if($id)
			{
				echo 'suceesss'.$id;
				$result_res['status'] = 1;
    			$result_res['msg'] = '物流数据入库成功';
    			$result_res['log_id'] = $id;
				return $result_res;
			}
			else
			{
				echo 'error';
				$result_res['status'] = 0;
    			$result_res['msg'] = '物流数据入库失败';
				return $result_res;
			}
    	}
    	else
    	{
    		$result_res['status'] = 0;
    		$result_res['msg'] = $res['msg'];
    		$this->ajaxReturn($result_res);
    	}
    }

    public function test_update_wuliu($id=5,$company_code=0)
    {
    	$now_time = time();//当前时间
        $find_data =$this->get_courier_bill_log_data($id,$company_code);
        if(!$find_data)
        {
        	$express_data = array(
	    		'no'=>'811898215423',
	    		'type'=>''
			); 
        	$express_info = $this->test_wuliu($express_data);
        }
        $hour = 0;
        $hour = floor(($now_time-$find_data['update_time'])%86400/3600);
        //var_dump($find_data);
        echo '<br>'.$hour.'h';
        if($hour>=3&&$find_data['waybill_state']!=3)
        {
    		// 和该记录 更新时间对比在3H内且未签收则返回数据，超过则更新数据再返回
	    	$bill_data = array(
	    		'no'=>'462587770684',
	    		'type'=>'zto'
			); 
	    	$json_res = $this->test_kuaidi($bill_data);
	    	$res = json_decode($json_res,ture);
	    	if(!$res['status'])
	    	{
	    		$up_data['waybill_process'] = json_encode($res['result']['list']);
				$up_data['waybill_state'] = $res['result']['deliverystatus']; //1在途中 2正在派件 3已签收 4派送失败
				$up_data['sign_time'] = $sign_time;
				$up_data['log'] = $log;
				$up_data['update_time'] = time();
				$updata_res = $model->where(array('id'=>$id))->save($up_data);
				if($updata_res!==false)
				{
					$result_data['status'] = 1;
	    			$result_data['msg'] = '数据读取成功';   			
					$list = $this->get_courier_bill_log_data($id);
					$result_data['data'] = $list;
					return $this->ajaxReturn($result_data);
				}
				else
				{
					$result_data['status'] = 0;
	    			$result_data['msg'] = '物流数据更新失败';
	    			$this->ajaxReturn($result_data);
				}
	    	}
	    	else
	    	{
	    		//数据读取失败
	    		echo '数据读取失败';
	    		$result_data['status'] = 0;
	    		$result_data['msg'] = '数据读取失败';
	    		$this->ajaxReturn($result_data);
	    	}
        }
        else
        {
        	echo '<pre>';
        	var_dump($find_data);
        	echo '</pre>';
        	$result_data['status'] = 1;
			$result_data['msg'] = '数据读取成功';	    			
			$result_data['data'] = $find_data;	    			
			return $this->ajaxReturn($result_data);
        }
    }

    public function get_courier_bill_log_data($id=5,$company_code=0)
    {
    	if (!$id&&!$company_code) {
    		return array();
    	}
    	if($id)
    	{
    		$where['id']=$id;
    	}
    	if($company_code)
    	{
    		$where['company_code']=$company_code;	
    	}
    	$courier_bill_log_model = M('courier_bill_log',null);
        $find_data = $courier_bill_log_model->where($where)->find();
        return $find_data?$find_data:array();
    }

    public function test_wuliu_data()
    {
    	$res = $this->get_courier_bill_log_data();
    	$res[waybill_process] = json_decode($res[waybill_process],1);
    	$res[log] = json_decode($res[log],1);
    	//var_dump($res[waybill_process]);
    	foreach( $res[waybill_process] as $v )
        {
        	$str.="<tr>";
        	$str.="<td>{$res['bill_id']}</td>";
        	$str.="<td>{$res['courier_no']}</td>";
        	$str.="<td>{$result['courier_company_name']}</td>";
        	$str.="<td>{$v['status']}</td>";
            $str.="<td>{$v['time']}</td>";
            $str.='</tr>';
        }
    	echo '<pre>';
    	//var_dump($res);
    	var_dump($str);
    	echo '</pre>';
    }

    public function test_array_map()
    {
    	$a = 'A';
    	echo strtolower($a).'<br>';
    	$arr = array('AAA'=>'aAa');
    	var_dump($this->arr_strtolower($arr,1,0));
    }
    public function arr_strtolower($arr,$key=1,$val=1)
    {
    	foreach ($arr as $k => $v) {
    		if($key)
    		{
    			$low_str = strtolower($k);
    			$v = $val?strtolower($v):$v;
    			$arr1[$low_str] = $v;
    		}else
    		{
    			$v = $val?strtolower($v):$v;
    			$arr1[$k] = $v;
    		}
    	}
    	
    	return $arr1;
    	
    }

}