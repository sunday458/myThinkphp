<?php
namespace Test\Controller;
use Think\Controller;
use Test\Org\Util;
class MyTestController extends Controller{
	function get_test_log(){
		$log = new \Test\Org\Log();
		//$filename = __ROOT__.'/Log/log_'. date("Ymd", time()) . ".txt";
		$filename = 'Log/log_'. date("Ymd", time()) . ".log";
		$content = date('Y-m-d').'-IP:'.get_client_ip();
		$log->writeLog($filename,$content);
		$log_list = $log->readLog($filename);
		var_dump( $log_list );
		var_dump($filename);
	}

	function get_test_ip(){
		echo get_client_ip(0);
		echo "<br/>";
		echo $bin = get_client_ip6(1);
		echo bin2ip($bin);
	}
/**
* $char_arr 字段数组 例如 array('pv','uv')
* $list 需要比较的数组
* $scale_list 需要比较的数组2
*/
    function get_scale_data($list,$scale_list){
        foreach ($list as $k => $v) {
        	foreach ($scale_list as $key => $value) {
        		if ($k == $key) {
        			$arr[$k] = $list[$k] - $scale_list[$key];
        		}
        	}
        }
        return $arr;
    }

    public function get_percentage_scale_data($list,$scale_list)
    {
        foreach ($list as $k => $v) {
            foreach ($scale_list as $key => $value) {
                if ($k == $key) {
                    $arr[$k] = sprintf('%.2f',($list[$k] - $scale_list[$key])/$scale_list[$key]*100);
                }
            }
        }
        return $arr;
    }

    function test_get_scale_data(){
    	$a =  array(
    		'pv'=>100,
    		'uv'=>90
    		);
    	$b = array(
    		'pv'=>120,
    		'uv'=>80
    		);
    	$ret = $this->get_scale_data($a,$b);
    	var_dump($ret);
    	$res = $this->get_percentage_scale_data($a,$b);
    	var_dump($res);
    	echo date("d")-date("w");
    }

    function test_bewteen_day()
    {
    	$startdate=strtotime("2013-4-04");
		$enddate=strtotime("2013-4-05");
		$days=round(($enddate-$startdate)/3600/24) ;
		echo $days; //days为得到的天数;

		$last_week_start_date=date('Y-m-01',strtotime(date('Y',time()).'-'.(date('m',time())-2).'-01'));
   		$last_week_end_date=date('Y-m-d',strtotime("$last_week_start_date +1 month -1 day"));
   		echo $last_week_start_date;
   		echo $last_week_end_date;
    }

    public function last_month_today() {
    	//$time =strtotime('2016-8-31');
    	$time =strtotime('2016-9-6');
    	//$time = strtotime(date('y-m-d'));
        $last_month_time = last_month_today($time,1);
        echo $last_month_time;
    }

    /**
    *08-19
    * 这里做个邮件和日报周报的推送大致功能
    */
    public function timing_task(){
        $nowTime = time();
        $nowTimeStr = date('Y-m-d');
        $task_time = strtotime(date('Y-m-d 06:00:00'));
        //echo date('Y-m-d 6:0:0');
        if ($nowTime == $task_time) {
        	// 符合时间，做推送,分日报，周报，月报等...
        	//① 获取需要推送的数据
        	$week_date = date("w");  //周几
        	$month_date = date('Y-m-01'); //每月1号
        	if ($week_date == 1) {
        		//周报
        		$last_week_start_date = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
        		$last_week_end_date =date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
        		echo $last_week_start_date.'+'.$last_week_end_date.':发送周报';
        		//② 推送数据
        		//③ 必要记录推送的记录日志
        	}elseif($month_date == $nowTimeStr){
                //月报
                $last_month_start_date = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y")));
                $last_month_end_date = date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y")));
                echo $last_month_start_date.'+'.$last_month_end_date.':发送月报';
                //② 推送数据
        		//③ 必要记录推送的记录日志
        	}else{
        		// 是否考虑 日报和周报，月报重叠的情况
        		echo '发送日报';
        		//② 推送数据
        		//③ 必要记录推送的记录日志
        	}
        	
        	echo '推送数据成功';
        }else{
        	//不符合时间设置
        	echo '未到推送时段';
        	var_dump( array('status'=>fasle,'msg'=>'未到设置时间'));
        }
    }

    function testFileRead(){
		/*$file = fopen('D:/part-00000', 'r');
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
		/*$filename_arr = array(
			'1'=>'D:/part-00000',//路径
			'2'=>'D:/part-00001',
			'3'=>'D:/part-00002',
			'4'=>'D:/part-00003',
		);*/
		$filename_arr = array(
			/*'1'=>'D:/buy_after_buy/buy_after_buy/part-00000',//路径
			'2'=>'D:/buy_after_buy/buy_after_buy/part-00001',
			'3'=>'D:/buy_after_buy/buy_after_buy/part-00002',
			'4'=>'D:/buy_after_buy/buy_after_buy/part-00003',*/
			'4'=>'D:/part-00006',
		);
		
        $this->add_goods_look_after_look_data( $filename_arr );

		$filename = 'D:/part-00000';
		$rule  = "/^\d+<.\s{1}$/";  
		$rule  = "/.+\n{1}/";  
		/* $re1='(\\d+)';	
	  	 $re2='(.)';	
	 	 $re3='(\\s+)';	
	 	 $re4='((?:(?:[1]{1}\\d{1}\\d{1}\\d{1})|(?:[2]{1}\\d{3}))(?:[0]?[1-9]|[1][012])(?:(?:[0-2]?\\d{1})|(?:[3][01]{1})))(?![\\d])';
	  	 $re5='(.)';	
	 	 $re6='(\\d+)';	
	  	 $re7='(.)';	
	     $re8='(\\d+)';	

		  if ($c=preg_match("/".$re1.$re2.$re3.$re4.$re5.$re6.$re7.$re8."/is", $ret, $matches))
		  {
		      $int1=$matches[1][0];
		      $c1=$matches[2][0];
		      $ws1=$matches[3][0];
		      $yyyymmdd1=$matches[4][0];
		      $c2=$matches[5][0];
		      $int2=$matches[6][0];
		      $c3=$matches[7][0];
		      $int3=$matches[8][0];
		      print "($int1) ($c1) ($ws1) ($yyyymmdd1) ($c2) ($int2) ($c3) ($int3) \n";
		  }*/
		if (!empty($ret)) {
			$str = preg_match_all($rule,$ret,$result);		
		}
		// var_dump($result);
		// echo '<br>';
		// var_dump($arr);
		// echo '<br>';
		// var_dump($str_arr);
	}

	public function add_goods_look_after_look_data( $filename_arr ){
		ini_set("max_execution_time", 3600);
		ini_set('memory_limit', '1024M');
		//$model = M('GoodsLookAfterLookTbl');
		$model = M('GoodsBuyAfterBuyTbl');
		$i = 0;
		foreach ($filename_arr as $key => $value) {
			$arr = array();
			$array = file( $value );
			foreach( $array as $k =>$v )
			{
			  $arr[] = $v;
			}
						// var_dump($arr);exit();
			if (!empty($arr)) {
				$str_arr = array();
				foreach ($array as $key => $val) {
	                $begin_id = trim(substr($val,0,strrpos($val,'<')));
	                //$add_data['look_goods_id'] = $begin_id;
	                $add_data['buy_goods_id'] = $begin_id;
					$str2 = trim(substr($val,strrpos($val,' ')+1));
/*					echo $begin_id;echo '<br>';
					echo $str2;echo '<br>';
					echo $count = substr_count($str2,',');echo '<br>';exit();*/
					if ($count == 1) { //后面只有1个，例：98< 2140202,1	2104899,1
						$str_arr = explode(",",$str2);
						 // echo $str_arr[0] ;echo '<br>';
						 // echo $str_arr[1];echo '<br>';
						 //入库
						$add_data['after_goods_id'] = $str_arr[0];
						$add_data['num'] = $str_arr[1];
						// var_dump($add_data);echo '<br>';exit();
						$model->data($add_data)->add();						
						$i++;

					}else{ //后面跟着多个，例：98< 2140202,1	2104899,1
						$str_arr = explode("\t",$str2);
						foreach ($str_arr as $_k => $_v) {
							 $after_id = trim(substr($_v,0,strrpos($_v,',')));
							 $num = trim(substr($_v,strrpos($_v,',')+1));
							 // echo $after_id ;echo '<br>';
							 // echo $num;echo '<br>';
							 //入库
							 $add_data['after_goods_id'] = $after_id;
							$add_data['num'] = $num;
							// var_dump($add_data);echo '<br>';exit();
							$model->data($add_data)->add();
							$i++;
						}
					}
				    
				    // var_dump($str_arr);echo '<br>';
				    // echo $val;exit();
				}
			}
			echo '成功导入数据'.$i.'条<br/>';
		}
	}

    public function get_look_buy_relation(){
    	ini_set("max_execution_time", 3600);
		ini_set('memory_limit', '1024M');

        	// 获取缓存
		S(array('type'=>'xcache','prefix'=>'pai_','expire'=>60));
        $session_data = S('session_data');
       
    	if (empty($session_data) || !isset($session_data)) {
    	 	$this->get_session_look_buy_data();
    	 	$session_data = session('session_data');
    	 	
    	} 
       
        $look_data = $session_data['look_data'];
        $buy_data = $session_data['buy_data'];

        $look_and_buy = array();
		if (!empty($look_data) && !empty($buy_data)) {
			foreach ($look_data as $k => $v) {
				foreach ($buy_data as $_k => $_v) {
					if ($v['look_goods_id'] == $_v['buyer_goods_id']) {
						$look_and_buy[] = $_v;
					}
				}
			}
		}

		var_dump($look_and_buy);
    }

    public function get_session_look_buy_data(){
    	ini_set("max_execution_time", 3600);
		ini_set('memory_limit', '1024M');

    	$look_model = M('GoodsLookAfterLookTbl');
		$buy_model = M('GoodsBuyAfterBuyTbl');

		$look_data = $look_model->field('id,look_goods_id,after_goods_id,num')->limit(1000)->select();
		$buy_data = $buy_model->field('id,buy_goods_id,after_goods_id,num')->limit(2000)->select();
        // var_dump($look_data);
        // var_dump($buy_data);exit();
		$session_data['look_data'] = $look_data;
		$session_data['buy_data'] = $buy_data;

		// $cache = S(array('type'=>'xcache','prefix'=>'pai','expire'=>3600));
		// $cache->session_data = $session_data; // 设置缓存 
        // 获取缓存
		// $session_data = $cache->session_data;
		// return $session_data;  
		//echo '数据缓存成功';
		// var_dump($cache->session_data);
		//S('session_data',null);
        S(array('type'=>'xcache','prefix'=>'pai_','expire'=>60));
        
		S('session_data',$session_data);
		// session('session_data',$session_data);  
		//$session_data = S('session_data');
		echo 'OK'.rand();
		//return $session_data;
    }

    public function get_test_session()
    {
    	// $val = S('session_data');
    	// var_dump($val);
    	S(array('type'=>'xcache','prefix'=>'p','expire'=>60));
    	S('s', 'test_s');
    	echo S('s') . '<br>';
    	S(array('type'=>'xcache','prefix'=>'q','expire'=>60));
    	echo S('s') . '<br>';
    	S(array('type'=>'xcache','prefix'=>'p','expire'=>60));
    	echo S('s') . '<br>';
    }

    public function get_look_buy_join_data(){
    	ini_set("max_execution_time", 3600);
	    ini_set('memory_limit', '128M');

    	$look_model = M('GoodsLookAfterLookTbl');
		$buy_model = M('GoodsBuyAfterBuyTbl');

        $prefix = C('DB_PREFIX');
		$res = $buy_model
		->field("{$prefix}goods_buy_after_buy_tbl.id,{$prefix}goods_buy_after_buy_tbl.buy_goods_id")
		->join("LEFT JOIN {$prefix}goods_look_after_look_tbl look ON look.look_goods_id = {$prefix}goods_buy_after_buy_tbl.buy_goods_id")
		->where("{$prefix}goods_buy_after_buy_tbl.id<100")
		->limit(1000)
		->select();
	
		var_dump($res);
    }

    public function get_buy_and_buy_data(){
    	$buy_model = M('GoodsBuyAfterBuyTbl');
		$buy_data = $buy_model->field('id,buy_goods_id,after_goods_id,num')->limit(10)->select();
		//var_dump($buy_data);

		$buy_arr = array();
		$last_goods_id = '';
		foreach ($buy_data as $k=> $v) {
			// var_dump($v['buy_goods_id']);echo "<br>";
			$goods_id = $v['buy_goods_id'];
			$after_id = $v['after_goods_id'];
			$num      = $v['num'];
			$arr      = array();
			//var_dump($v);
			$arr[] = array($after_id,$num);
			$buy_arr[$goods_id][] = $arr;
			//var_dump($v);echo "<br>";				
			// $last_goods_id = $goods_id;

		}
		//var_dump($buy_model->getLastSql());
		var_dump($buy_arr);
    }

    public function get_percentage_yesterday_scale_data($list,$scale_list)
    {
        foreach ($list as $k => $v) {
            foreach ($scale_list as $key => $value) {
            	if ($k == $key) {
            		 $arr[] = sprintf('%.2f',($list[$k] - $scale_list[$k])/$scale_list[$k]*100);
            	}
            }
        }
        return $arr;
    }

    public function test_scalse_data(){
    	$a['a1']['a1'] = array(1,2,3,45);
    	$a['a2'][] = array(2,2,3,45);
    	$a['a3'][] = array(3,2,3,45);
    	$b['b1'][] = array(3,5,6,54);
    	$b['b2'][] = array(6,5,6,54);
    	$b['b3'][] = array(9,5,6,54);
    	var_dump($a['a2'][0]);
    	$res = $this->get_percentage_yesterday_scale_data($a,$b);
    	var_dump($res);
    }

    public function get_file_auto_classify_sort()
    {
    	$filename_arr = array(
			'1'=>'E:/daoru_info/auto_classify_sort/part-00000',//路径
			'2'=>'E:/daoru_info/auto_classify_sort/part-00001',
			'3'=>'E:/daoru_info/auto_classify_sort/part-00002',
			'4'=>'E:/daoru_info/auto_classify_sort/part-00003',
			'5'=>'E:/daoru_info/auto_classify_sort/part-00004',
			'6'=>'E:/daoru_info/auto_classify_sort/part-00005',
			'7'=>'E:/daoru_info/auto_classify_sort/part-00006',
			'8'=>'E:/daoru_info/auto_classify_sort/part-00007',
			'9'=>'E:/daoru_info/auto_classify_sort/part-00008',
			'10'=>'E:/daoru_info/auto_classify_sort/part-00009',
			'11'=>'E:/daoru_info/auto_classify_sort/part-00010',
			'12'=>'E:/daoru_info/auto_classify_sort/part-00011',
			'13'=>'E:/daoru_info/auto_classify_sort/part-00012',
			'14'=>'E:/daoru_info/auto_classify_sort/part-00013',
			'15'=>'E:/daoru_info/auto_classify_sort/part-00014',
			'16'=>'E:/daoru_info/auto_classify_sort/part-00015',
			'17'=>'E:/daoru_info/auto_classify_sort/part-00016',
			'18'=>'E:/daoru_info/auto_classify_sort/part-00017',
		);		
        $this->add_file_auto_classify_sort_data( $filename_arr );
    }

    function add_file_auto_classify_sort_data($filename_arr){
    	ini_set("max_execution_time", 3600);
		ini_set('memory_limit', '1024M');
		//$model = M('GoodsLookAfterLookTbl');
		$i = 0;
		foreach ($filename_arr as $key => $value) {
			$file = file_get_contents( 'E:/daoru_info/auto_classify_sort/part-00000' );
			$arr = explode('userID=', $file); //userID唯一才可使用
			/*$rule  = "/(userID=){1}/";  
			$str = preg_match_all($rule,$array,$result);*/
		    //var_dump($arr);exit;
		    $i = 0;
		    $y = 0;
			if (!empty($arr)) {
				foreach ($arr as $key => $val) {
					if (!empty($val)) {
						// 数组中字符串 转换为数组
						$str_arr = explode("\n", trim('userID='.$val));
						//这里还可以在拆分 按 type_id
                        //$type_id_arr = explode('type_id=', $file); 
						//拆分type_id 之后 还可以拆分 demo 获取栏目数据
						//$demo_arr = explode('demo', $type_id_arr); 
						//拆分type_id 之后 还可以拆分 money 获取消费数据
						//$money_arr = explode('money=', $type_id_arr); 
						foreach ($str_arr as $k => $v) {
							if (!empty($v)) {
								// $key = trim(substr($v,0,strrpos($v,'=')));
							 //    $val = trim(substr($v,strrpos($v,'=')+1));
								$kv_arr = explode('=', $v);
								$key = $kv_arr[0];
								$val = $kv_arr[1];
								if (!$val) {
									echo "{$key}=>not data!<br/>";
									continue;
								}
							    // if (substr_count($val,',')>=1) {
							    // 	$val_arr = explode("\t", $val);
							    // 	foreach ($val_arr as $_k => $_v) {
							    // 		$k_str = trim(substr($_v,0,strrpos($_v,',')));
							   	// 		$v_str = trim(substr($_v,strrpos($_v,',')+1));
							   	// 		echo $key.'=>'.$k_str.'+'.$v_str;
							   	// 		echo "<br>";
							   	// 		$i++;
							    // 	}
							    // }	
								echo "{$key}=>{$val}<br/>";
								$i++;
								continue;	
							}												
						}
					}
				}
			}
			echo '成功导入数据'.$i.'条<br/>';
			break;
		}
    }

    function tese_c_data(){
    	$keys = array(
    		name,
			ev_goodat,
			ev_other,
			hz_experience,
			hz_goodat,
			hz_order_way,
			hz_othergoodat,
			hz_place,
			hz_team,
			ms_certification,
			ms_experience,
			ms_forwarding,
			m_bwh,
			m_cup,
			m_cups,
			m_experience,
			m_height,
			m_level,
			m_sex,
			m_weight,
			ot_label,
			ot_otherlabel,
			p_experience,
			p_goodat,
			p_order_income,
			p_team,
			t_experience,
			t_goodat,
			t_teacher,
			t_way,
			yp_area,
			yp_background,
			yp_can_photo,
			yp_lighter,
			yp_other_equitment,
			yp_place,
			demo
    	);
    	return $keys;
    }

    function get_type(){
    	$type = array(
    		0=>'约模特',
    		1=>'约摄影',
    		2=>'约化妆',
    		3=>'约场地',
    		4=>'约培训',
    		5=>'约活动',
    		6=>'约美食',
    		7=>'其他服务',

		);
    	return $type;
    }

    function get_type_money($type_id,$num){
    	switch ($type_id) {
    		case '0':
    			$money = $this->get_model_money($num);
    			break;
    		case '1':
    			$money = $this->get_photo_money($num);
    			break;
    		case '2':
    			$money = $this->get_beauty_money($num);
    			break;
    		case '3':
    			$money = $this->get_area_money($num);
    			break;
    		case '4':
    			$money = $this->get_training_money($num);
    			break;
    		case '5':
    			$money = $this->get_activity_money($num);
    			break;
    		case '6':
    			$money = $this->get_food_money($num);
    			break;
    		default:
    			$money = 0;
    			break;
    	}
    	return $money;
    }

    function get_activity_money($num){
    	if ($num = 0) {
    		$money = '0,100';
    	}elseif ($num = 1) {
    		$money = '100,200';
    	}elseif ($num = 2) {
    		$money = '200,400';
    	}elseif ($num = 3) {
    		$money = '400,800';
    	}elseif ($num = 4) {
    		$money = '800,1500';
    	}
    	return $money;
    }
    function get_beauty_money($num){
    	if ($num = 0) {
    		$money = '0,300';
    	}elseif ($num = 1) {
    		$money = '300,500';
    	}elseif ($num = 2) {
    		$money = '500,1000';
    	}
    	return $money;
    }
    function get_food_money($num){
    	if ($num = 0) {
    		$money = '0,100';
    	}elseif ($num = 1) {
    		$money = '100,300';
    	}elseif ($num = 2) {
    		$money = '300,500';
    	}elseif ($num = 3) {
    		$money = '500,1000';
    	}
    	return $money;
    }
    function get_model_money($num){
    	if ($num = 0) {
    		$money = '0,100';
    	}elseif ($num = 1) {
    		$money = '100,300';
    	}elseif ($num = 2) {
    		$money = '300,500';
    	}elseif ($num = 3) {
    		$money = '500,800';
    	}elseif ($num = 4) {
    		$money = '800,1000';
    	}
    	return $money;
    }
    function get_photo_money($num){
    	if ($num = 0) {
    		$money = '0,500';
    	}elseif ($num = 1) {
    		$money = '500,1000';
    	}elseif ($num = 2) {
    		$money = '1000,2000';
    	}elseif ($num = 3) {
    		$money = '2000,3000';
    	}elseif ($num = 4) {
    		$money = '3000,5000';
    	}elseif ($num = 5) {
    		$money = '5000,10000';
    	}
    	return $money;
    }
    function get_training_money($num){
    	if ($num = 0) {
    		$money = '0,100';
    	}elseif ($num = 1) {
    		$money = '100,1000';
    	}elseif ($num = 2) {
    		$money = '1000,2000';
    	}elseif ($num = 3) {
    		$money = '2000,3000';
    	}elseif ($num = 4) {
    		$money = '3000,4000';
    	}
    	return $money;
    }
    function get_area_money($num){
    	if ($num = 0) {
    		$money = '0,300';
    	}elseif ($num = 1) {
    		$money = '300,500';
    	}elseif ($num = 2) {
    		$money = '500,1000';
    	}
    	return $money;
    }

    function get_data_by_cf()
    {
    	$filename_arr = array(
			//'1'=>'D:/cf_data/part-00000',
			'1'=>'D:/chatlog.txt',
		);
		
        //$this->add_data_to_cf( $filename_arr );
        $this->add_chatLog( $filename_arr );
    }

    function add_chatLog($filename_arr)
    {
    	ini_set('memory_limit','1024M');
		foreach ($filename_arr as $value) {
			$arr = array();
			$array = file( $value );
			foreach( $array as $v )
			{
			  $arr[] = json_decode($v,true);
			}
		}

		$result_data = array();
		foreach ($arr as $_k => $_v) {
			$result = $this->create_post_data($_v);
			echo json_encode($result).'<br>';
			//$result_data[] = json_encode($result);
		}
		unset($arr);
		unset($array);
		//var_dump($arr);
		var_dump($result_data);
    }

    /**
     * 发送内容构造
     * @param $post_data
     * @return array
     */
    public function create_post_data($post_data)
    {
        $type       = 'custom';
        switch($post_data['media_type'])
        {
            case 'text':
                $type  = 'text';
                $new_post_data['txt_content'] = $post_data['content'];
                break;

            case 'notify':
                $type = 'custom';

                $new_post_data['txt_content']   = $post_data['content'];
                $new_post_data['link_url']      = $post_data['link_url'];
                $new_post_data['wifi_url']      = $post_data['wifi_url'];
                $new_post_data['type']          = $post_data['media_type'];
                break;

            case 'merchandise':
            case 'card':
                $type = 'custom';

                $new_post_data['type']          = $post_data['media_type'];
                $new_post_data['card_text2']    = $post_data['card_text2'];
                $new_post_data['card_text1']    = $post_data['card_text1'];
                $new_post_data['card_title']    = $post_data['card_title'];
                $new_post_data['link_url']      = $post_data['link_url'];
                $new_post_data['wifi_url']      = $post_data['wifi_url'];
                if($post_data['file_small_url'])    $new_post_data['file_small_url']    = $post_data['file_small_url'];
                if($post_data['card_style'])        $new_post_data['card_style']        = $post_data['card_style'];
                break;

            case 'rich_text':
                $type = 'custom';

                $new_post_data['type']          = $post_data['media_type'];
                $new_post_data['card_text2']    = $post_data['card_text2'];
                $new_post_data['card_title']    = $post_data['card_title'];
                $new_post_data['link_url']      = $post_data['link_url'];
                $new_post_data['wifi_url']      = $post_data['wifi_url'];
                $new_post_data['rich_content']      = $post_data['content'];
                break;
        }

        return array('type'=>$type, 'post_data'=>$new_post_data);
    }

    function add_data_to_cf($filename_arr)
    {
    	ini_set("max_execution_time", 3600);
		ini_set('memory_limit', '1024M');
		//$model = M('GoodsLookAfterLookTbl');
		$model = M('CdData');
		$i = 0;
		foreach ($filename_arr as $key => $value) {
			$arr = array();
			$array = file( $value );
			foreach( $array as $k =>$v )
			{
			  $arr[] = $v;
			}
						 //var_dump($arr);exit();
			if (!empty($arr)) {
				$str_arr = array();
				foreach ($array as $key => $val) {
	                $user_id = trim(substr($val,1,strpos($val,',')-1));
	                $add_data['user_id'] = $user_id;
	               
					$str2 = trim(substr($val,strpos($val,',')+1,-2));
					$count = substr_count($val, ','); 
					if ($count <= 2) { //后面只有1个，例：10001,2131386,9.429839387405075
						$str_arr = explode(",",$str2);
						 // echo $str_arr[0] ;echo '<br>';
						 // echo $str_arr[1];echo '<br>';
						 //入库
						$add_data['goods_id'] = $str_arr[0];
						$add_data['scale'] = $str_arr[1];
						$add_data['add_time'] = time();
						// var_dump($add_data);echo '<br>';exit();
						$model->data($add_data)->add();						
						$i++;

					}else{ //后面跟着多个，例：98< 2140202,1	2104899,1
						$str_arr = explode("\t",$str2);
						//var_dump($str_arr);exit();
						foreach ($str_arr as $_k => $_v) {
							 $goods_id = trim(substr($_v,0,strrpos($_v,',')));
							 $scale = trim(substr($_v,strrpos($_v,',')+1));
							 // echo $after_id ;echo '<br>';
							 // echo $num;echo '<br>';
							 //入库
							$add_data['goods_id'] = $goods_id;
							$add_data['scale'] = $scale;
							$add_data['add_time'] = time();
							// var_dump($add_data);echo '<br>';exit();
							$model->data($add_data)->add();
							$i++;
						}
					}
				    
				    // var_dump($str_arr);echo '<br>';
				    // echo $val;exit();
				}
			}
			echo '成功导入数据'.$i.'条<br/>';
		}
		
    }

    function test_add_or_update_data()
    {
    	$path = 'D:/';
    	//读取数据
    	$read  = file($path);
    	//读取内容拆分
    	foreach ($read as $k => $v) {
    		//查询条件
	    	$where['goods_id'] = $v['goods_id'];
	    	$where['buy_id'] = $v['buy_id'];
	    	//组装数据
	    	$data['goods_id'] = $v['goods_id'];
	    	$data['buy_id'] = $v['buy_id'];
	    	$data['num'] = $v['num'];
    	}
    	
    	$model  = M();
    	$result = $model->field('num')->where($where)->select();
        if ($result) {
        	//数据存在,更新
            $model->where($where)->save($data);
        }else
        {
        	//数据不存在，入库
        	$model->data($data)->add();
        }
        //连续签到N天
        $n = 5; //连签5天为例
        $nowTime = strtotime(date('Y-m-d 23:59:59'));
        //推算连续的 N天时间
        $start_time = $nowTime - 3600 * $n;
        $where['add_time'] = array(array('egt',$start_time),array('elt',$nowTime));
        $where['user_id']  = $user_id;
        $yesterday = date('-1 days');

        $num = $model->where($where)->count();
        if ($num != $n) {
        	return fasle;  //未达到条件的N天 
        }else
        {
        	return true;
        }

    }

    function test_down()
    {
    	ini_set('memory_limit','1024M');
    	$look_model = M('GoodsLookAfterLookTbl');
    	$page_size = 80000;
    	G('begin');
    	$res = $look_model->select();
    	echo $depth = array_depth($res);echo "<br/>";
        echo $total_count = count($res);echo "<br/>";
        $page_count = ceil($total_count/$page_size);
        $user_arr = array();
        // todo 这里多做一个 数组数量的判断
        for($i=0;$i<= $page_count-1;$i++)
        {
            $start_limit = $i*$page_size;
            $list = $look_model->limit($start_limit,$page_size)->select();
            echo count($list);echo "<br/>";
            echo $start_limit.'-'.$page_size;echo "<br/>";
            //$user_arr = array_merge($user_arr,$list);
            $user_arr[] = $list;
        }
        //var_dump($user_arr);
        if(!is_array($user_arr)) $user_arr = array();
        //echo $depth = array_depth($user_arr);//exit();
        $user_list = array();
        if ($depth == 3) {
        	foreach ($user_arr as $_k => $_v) {
        		foreach ($_v as $key => $value) {
        			 if($value['look_goods_id'] >=2000)$user_list[] = "yuebuyer/{$value['look_goods_id']}";
        		}
        	}
        }else{
        	foreach($user_arr as $v)
	        {
	            if($v['look_goods_id'] >=2000)$user_list[] = "yuebuyer/{$v['look_goods_id']}";
	        }
        }
        G('end');
        //var_dump($user_list);echo "<br/>";
        echo count($user_list);
        echo G('begin','end').'s';
        
    }

    public function test_ret($is_do = 0)
    {
    	var_dump( $is_do );
    	if ($is_do) {
    		echo 'is_do';
    		//return;
    		exit();
    	}

    	echo 'oh，this is wrong<br/>';
    }

    function test_ret2($is_do = 0)
    {
    	$this->test_ret($is_do);
    	echo 'this is test_ret2<br/>';
    }

    function test_sum()
    {
    	$nowTime = date('Ymd',time()-3600*24);
        $tableName = 'yueus_tmp_tbl_'.$nowTime ;
        /*$sql = "SELECT
  `visit_time`, `request_filename`, `current_page_url_unfiltered`, `current_page_url`, `current_page_url_scheme`, `current_page_url_host`, `current_page_url_path`, `current_page_url_file`, `current_page_url_query`, `request_filename_param`, `referer_outside_unfiltered`, `referer_outside_url`, `referer_outside_host`, `referer_outside_path`, `referer_outside_file`, `referer_outside_query`, `ip`, `ip_location_province`, `ip_location_city`, `ip_circuit`, `user_agent`, `system`, `browser`, `device`, `login_id`, `g_session_id`, `activity_level`, `remember_userid`, `referer_stay_time`, `script_run_time`, `page_load_time`, `referer_mark`, `promotion_mark`, `tj_spread_regedit`, `screen_px`
FROM
  `www_log_tmp_db`.`$tableName` WHERE current_page_url_host = '51snap.yueus.com' AND current_page_url_path = '/goods/'
LIMIT $limit ";*/
        $model = M('YueusTmpTbl_20161108');
        $where = "current_page_url_host = '51snap.yueus.com' AND current_page_url_path = '/goods/'";
        $ret = $model->where($where)->field('current_page_url_query,g_session_id,ip,COUNT(*) AS pv,COUNT(DISTINCT g_session_id) AS uv,COUNT(DISTINCT ip) AS c_ip')->group('current_page_url_query')->select();
        if(empty($ret))  return $ret = array();
        $arr = array();
        $b_arr = array();
        $result  = array();
        foreach($ret as $k=>$v)
        {
            $arr[$k]['goods_id'] = substr($v['current_page_url_query'],strpos($v['current_page_url_query'],'goods_id=')+9);
            if(strpos($arr[$k]['goods_id'],'&'))
            {
                $arr[$k]['goods_id'] = substr($arr[$k]['goods_id'],0,strpos($arr[$k]['goods_id'],'&'));
            }
            $arr[$k]['ip'] = $v['ip'];
            $arr[$k]['g_session_id'] = $v['g_session_id'];
            $arr[$k]['pv'] = $v['pv'];
            $arr[$k]['uv'] = $v['uv'];
            $arr[$k]['c_ip'] = $v['c_ip'];
        }
        foreach($arr as $_k => $_v){
            if ( in_array($_v['goods_id'], $sum) ) 
            {
            	/*if ($_v['goods_id'] == $c_arr[$_v['goods_id']]['goods_id'] ) {*/
        		  if ($_v['goods_id'] == $b_arr[$_v['goods_id']]['goods_id'] ) {
            		$b_arr[$_v['goods_id']]['pv'] += $_v['pv'];
            		$b_arr[$_v['goods_id']]['uv'] += $_v['uv'];
            		$b_arr[$_v['goods_id']]['ip'] += $_v['c_ip'];
            	}
            }else
            {
            	$sum[] = $_v['goods_id'];
            	$b_arr[$_v['goods_id']]['goods_id'] = $_v['goods_id'];
            	$b_arr[$_v['goods_id']]['pv'] = $_v['pv'];
            	$b_arr[$_v['goods_id']]['uv'] = $_v['uv'];
            	$b_arr[$_v['goods_id']]['ip'] = $_v['c_ip'];

            	/*$c_arr[$_v['goods_id']]['goods_id'] = $_v['goods_id'];
            	$c_arr[$_v['goods_id']]['pv'] = $_v['pv'];
            	$c_arr[$_v['goods_id']]['uv'] = $_v['uv'];
            	$c_arr[$_v['goods_id']]['ip'] = $_v['ip'];*/
            }
        }
    
        //var_dump($ret);
        echo "<br/>********<br/>";
        var_dump($arr);
        echo "<br/>--------<br/>";
        var_dump($b_arr);
    }

    // 取数组中特定的 一定范围的值
    function test_array_slice(){
    	$arr = array(
           array(1),array(2),array(3),array(4),array(5),
    		);
    	$arr1 = array(1,2,3,4,5);
    	$arr = array_slice($arr,0,3);
    	var_dump($arr);
    }

    function checkUrl()
    {
    	$url = 'www.yueus.com';
    	//$url = 'www.baidu.com';
    	//TODO 是否考虑加密防注入
    	//$res = strripos($url, 'yueus.');
    	$res = strpos($url, 'yueus.');
    	if ($res) {
    		echo $res;
    	}else{
    		echo 'not';
    	}
    }

    // 递归获取父或者子级 ID 和 层数
    function test_relationship($type = 'child',$id = 100008){
    	/*echo cookie('pai_pa_num');
    	echo '<br/>';
    	echo 111;
    	cookie(null);
        echo cookie('pai_pa_num');
    	exit();*/
    	$i = 0;
    	$model = M('PaRelationshipTbl');
    	if ($type == 'child') {
    		$where = 'user_parent_id='.$id;
    		$user_id = $model->where($where)->field('user_id')->select();
    	}else{
    		$where = 'user_id='.$id;
    		$user_id = $model->where($where)->field('user_parent_id')->select();
    	}
    	var_dump($user_id);echo '<br/>';
    	if ($user_id) {
    		//if (cookie('pai_pa_num')) {
    		$pai_pa_num = $_COOKIE('pai_pa_num');
    		if ($pai_pa_num) {
    			$i = $_COOKIE('pai_pa_num');
    			/*echo '<br/>'.$i.'<br/>';
    			echo 111;echo '<br/>';*/
    		}else{
    			//cookie('num',1,array('expire'=>300,'prefix'=>'pai_pa_'));
    			setcookie('pai_pa_num',1,time()+300);
    			//$i = cookie('pai_pa_num');
    			$i = $_COOKIE('pai_pa_num');
    			/*echo '<br/>'.$i.'<br/>';
    			echo 222;echo '<br/>';*/
    		}   		
    		$i++;
    		//cookie('num',$i,array('expire'=>300,'prefix'=>'pai_pa_'));
    		setcookie('pai_pa_num',$i,time()+300);
    		//echo $i;echo '<br/>';
    		if ($type == 'child') {
    			$this->test_relationship('child',$user_id[0]['user_id']);  
    		}else{
    			$this->test_relationship('parent',$user_id[0]['user_parent_id']);  
    		}
    		 	
    	}else{   		
    		//if (cookie('pai_pa_num')) {
    		if ($_COOKIE('pai_pa_num')) {
    			echo $id;
    			echo '<br/>';
    			//$i = cookie('pai_pa_num');
    			$i = $_COOKIE('pai_pa_num');
    			var_dump(array('user_id'=>$id,'lv'=>$i)); 
    			//cookie(null,'pai_pa_');
    			setcookie('pai_pa_num','',time()-3600);
    		}else{
    			echo 'this is  Lv0';
    		}
    	}
    }

    function test_shi_cha(){
    	$time1 = strtotime('2016-12-5 12:0:0');
    	$time2 = strtotime('2016-12-6 12:0:0');
    	$time1 = 0;
    	echo $cha = ($time2 - $time1);
    	echo "<br/>";
    	$day=floor($cha/86400);; 
    	var_dump($day);
    }

    public function get_week_begin_end_date()
    {
        $time = date('Y-m-d 00:00:00',time());
        echo $time;
        $last_day = date("Y-m-d",strtotime("$time Sunday"));
        $week_first_day = date("Y-m-d",strtotime("$last_day - 6 days"));
        $week_end_day   = $last_day;
        echo('输入的时间星期第一天是：'.date("Y-m-d",strtotime("$last_day - 6 days")));
        echo('输入的时间星期最后一天是：'.$last_day);
    }

    /** 
* 生成从开始月份到结束月份的月份数组
* @param int $start 开始时间戳
* @param int $end 结束时间戳
*/ 
	function monthList($start,$end){
		 if(!is_numeric($start)||!is_numeric($end)||($end<=$start)) return '';
		 $start=date('Y-m',$start);
		 $end=date('Y-m',$end);
		 //转为时间戳
		 $start=strtotime($start.'-01');
		 $end=strtotime($end.'-01');
		 $i=0;//http://www.phpernote.com/php-function/224.html
		 $d=array();
		 while($start<=$end){
			  //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
			  $d[$i]=trim(date('Y-m',$start),' ');
			  $start+=strtotime('+1 month',$start)-$start;
			  $i++;
		 } 
		 return $d;
	}

    function my_test(){
    	$versions_str_id = '4.2.10';
    	$number = substr($versions_str_id,strpos($versions_str_id,'.')-1,1);
    	var_dump($number);
    	$time1 = strtotime('2016-12-06');
    	$time1 = strtotime('2016-12-12');
    	$ret = $this->monthList($time1,$time2);
    	var_dump($ret);

    	/*$url = 'http://goods.yueus.com/?goods_id=2151645&type_id=0';
    	echo $n = strpos($url,'&');
    	$url_str = substr($url,0,strpos($url,'&'));
    	var_dump($url_str);*/

    	$url = 'http://pa.yueus.com?url=http%3A%2F%2Fgoods.yueus.com%2F%3Fgoods_id%3D2151645%26type_id%3D0&puid=08d02Kjt';
        $begin_num = strpos($url, 'url=');
        $end_num = strpos($url, '&puid');
        $check_url_path = substr($url,$begin_num+4,$end_num - $begin_num-4);
        $val = substr($url,$begin_num+4,$end_num - $begin_num-4);
        $key = substr($url,$end_num+6);
        var_dump($key,$val);echo '<br/>';
        echo $check_url_path;

        $arr1= array(1=>2);
        $arr2 = array(3=>4);
        //array_push($arr1,$arr2);
        //foreach ($arr1 as $key => $value) {
        	$arr1['3'] = 4;
        //}
        echo '<br/>';
        var_dump($arr1);

        $name_arr=array('IM_Recovery_1481700148.log','IM_Recovery_1481700178.log');
        foreach ($name_arr as $k => $v) {
        	$begin_num = strpos($v, 'IM_Recovery_=');
        	$end_num   = strpos($v, '.');
        	$path = substr($v,$begin_num+12,$end_num - $begin_num-12);
        	var_dump($path);
        }

    }

    function max(){
    	$arr = array(1,2,4,6,3);
    	foreach($arr as $k=>$v){
		   if( $k== 0 ){
		      $min = $v;
		      $max = $v;
		   }else{
		      $min = min($min,$v);
		      $max = max($max,$v);
		   }
		   if($v == ''){
		   		$kong = $k;
   		   }
	    }

	    echo $max;
    }

    /*function add_im_data()
    {
    	$im_model = M(ImDataTbl);
    	$add['user_id'] = 100000;
    	$add['seller_id'] = 200050;
    	$add['date_time'] = 1481618000;
    	$add['add_time'] = 1481618034;
    	$add['min_time'] = 1481618052;

    	$ret = $im_model->data($add)->add();
    	INSERT IGNORE INTO testtable1(UserId,UserName,UserType) 
    	return $ret;
    }*/

    function test_parse_url (){
    	$url = 'http://www.yueus.com/yue_admin_v2/im/pai_im_list.php?act=take&id=78&user_id=120231&table_type=2';
    	$parse_ifo = parse_url($url, PHP_URL_QUERY);
    	var_dump($parse_ifo);
    	echo '<br/>';
		echo urlencode($url);

    }


    /**
    *跳出率(跳转率)是指访客浏览一个页面就离开占总访问量的比例。
    *比如一天中有5000IP通过各种接口访问该域名访问，而有500个人点进去没有到达其他页面就离开了。那么我们可以通过公式获得该入口网址的跳出率就是500/5000*100%=10%。
    *
    *访问深度=页面浏览量/访问次数
    *比如：假如用户一次访问了某个网站首页后又浏览了两个页面离开网站，另一次用户访问了网站首页后又浏览三个页面离开网站，那么在这个统计周期内，有2个访次，页面浏览量分别为3和4，那么访问深度=页面浏览量(3+4)/访次2=3.5(页/次)
    *
    */
    /**
    * demo 2次跳转 数据采集
    */
    function twice_url_data()
    {
    	//获取原链接 主域名
    	$referer_outside_host = 'pa.yueus.com';
    	//获取和原链接 跳转的链接，条件:用户(puid,uid)
    	$nowTime = time();
    	$nowDate = date('Ymd',$nowTime-3600*24);
    	$tableName = 'yueus_tmp_tbl_'.$nowDate;
    	$model = M("$tableName");
    	$where['referer_outside_host'] = $referer_outside_host; //来源地址
    	$where['login_id'] = 100002; // 用户Id
    	//$where['tj_spread_regedit'] = 100002; // 用户pid

    	// 用户一次跳转的页面链接
    	$list = $model->where("referer_outside_host = '{$referer_outside_host}'")->order('visit_time asc')->select();
    	//$list = $model->where($where)->order('visit_time asc')->select();
    	//$list = $model->where($where)->order('visit_time asc')->select();
    	
    	//var_dump($list);
    	if (empty($list)) $list = array();
    	foreach ($list as $k => $v) {
    		// current_page_url_unfiltered 当前页地址
    		// g_session_id   用户sessionID
    		// tj_spread_regedit   用户PID
    		// login_id   用户ID (可能 = 0)
    		echo $v['current_page_url_unfiltered'];
    		echo '<<<===';
    		echo $v['referer_outside_unfiltered'];
    		echo '<br/>';
    	}

    	//参考指标 ①2次跳转率  ②2次浏览情况 ③访问深度=页面浏览量/访次(页/次)
    	//二次跳转 查询
    	$twice_list = array();
    	foreach ($list as $_k => $_v) {
    		// 筛选 用户浏览的条件 
    		$twice_where['referer_outside_unfiltered'] = $_v['current_page_url_unfiltered'];
    		$twice_where['g_session_id'] = $_v['g_session_id'];
    		$twice_where['tj_spread_regedit'] = $_v['tj_spread_regedit'];
    		if ($_v['login_id'])  $twice_where['login_id'] = $_v['login_id'];
    		var_dump($twice_where);
    		echo '<br/>';
    		$twice_list[] = $model->where($twice_where)->order('visit_time asc')->select();
    	}
    	var_dump($twice_list);
    	// 2次跳转 为空，既是 2次跳转率(跳出率) = 0
    	if (empty($twice_list)) $twice_list = array();

    	foreach ($twice_list as $key => $val) {
    		// current_page_url_unfiltered 当前页地址
    		// g_session_id   用户sessionID
    		// tj_spread_regedit   用户PID
    		// login_id   用户ID (可能 = 0)
    		echo $val['current_page_url_unfiltered'];
    		echo '<<<===';
    		echo $val['referer_outside_unfiltered'];
    		echo '<br/>';
    	}	

    	// 这里对跳出率 做一个简单的统计
    	$loss_where['referer_outside_unfiltered'] = $referer_outside_host;
    	$loss_where['tj_spread_regedit'] = $puid;
    	//计算 总PV 页数
    	$total = $model->where($loss_where)->count();
    	//计算 去向页 页数
    	$keep_where['referer_outside_unfiltered'] = $referer_outside_host;
    	$keep_where['tj_spread_regedit'] = array('neq','');
    	$keep_total = $model->where($keep_where)->count();
    	//2者的 差值算是跳出流失 页数
    	$loss_total = $total - $keep_total;
    	echo $loss_total.$total.$keep_total;
    	$loss_rate = ($loss_total/$total*100).'%';
    	echo $loss_rate;

    	// 页面浏览页数 = 当前页 + 来源页 页数(未计入多次跳转)
    }
    
    /**
    * 用户链接的仓库
    * 记录和用户关联的数据
    * 方式① 在浏览数据入库的时候 同时入库
    * 方式② 定时任务数据入库(延时)
    */
    function mytestdo()
    {
    	$puid = '61sew123';
    	$db_name = C('DB_NAME');
		$prefix = C('DB_PREFIX');
		$date = date('Y-m');
        $table_name = $prefix.'pa_'.$puid.'_tbl';
		$sql = " show tables from $db_name like '%{$table_name}' ";
		$all_model = M();
		//$model = M();
		$res = $all_model->query($sql);
		 //echo $d_model->getLastSql();
		if ($res) {		
			$data['status'] = false;
			$data['errcode'] = 103;
			$data['msg'] = '数据表'.$table_name.'已存在';
		}else{
           // $model = M();
			$sql_str = "CREATE TABLE IF NOT EXISTS {$table_name} (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `user_id` int(11) NOT NULL COMMENT '关联注册者ID',
			  `puid` char(15) NOT NULL COMMENT '推广唯一标识',
			  `register_time` int(11) NOT NULL COMMENT '注册时间',
			  `referer_url` text NOT NULL COMMENT '来源链接',
			  `current_url` text NOT NULL COMMENT '跳转链接',
			  `pa_url` text NOT NULL COMMENT '推广链接',
			  `visit_time` int(11) NOT NULL COMMENT '浏览时间',
			  `add_time` int(11) NOT NULL COMMENT '添加时间',
			  PRIMARY KEY (`id`),
			  KEY `user_id` (`user_id`),
			  KEY `register_time` (`register_time`),
			  KEY `visit_time` (`visit_time`)
		 	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='XXX表';";
            $ret = $all_model->execute($sql_str);
            //$ret = $model->query($sql_str); 待考究，建表成功，报错
            if ($ret != false) {
            	$data['status'] = true;
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

    function myTest($table_name)
    {
    	/*$yue_login_id = 2023288;
    	$ret = in_array($yue_login_id,array(352925,2023288));
    	if ($ret) {
    		echo 'yes';
    	}else{
    		echo 'not';
    	}*/
    	$test_url = 'http://pa.yueus.com?url=http%3A%2F%2Fgoods.yueus.com%2F%3Fgoods_id%3D2151682%26type_id%3D0&puid=61sew123';
    	$test_referer_url = 'http://wwww.yueus.com';
    	$test_current_url = 'http://goods.com?url=http%3A%2F%2Fgoods.yueus.com%2F%3Fgoods_id';

        $add_data['user_id'] = 2016234;     
        $add_data['puid'] = 'current_url';     
        $add_data['register_time'] = time()-3600*12;     
        $add_data['referer_url'] = '';     
        $add_data['current_url'] = '';     
        $add_data['pa_url'] = $test_url;     
        $add_data['visit_time'] = time()-3600*24;     
        $add_data['add_time'] = time();     
			 
    	$add_sql = "INSERT IGNORE INTO test.$table_name (user_id,seller_id,date_time,content,reason,over_reason,status,last_user_id,last_use_time,table_type,add_time)
                VALUES ({$add_data['user_id']},{$add_data['puid']}, {$add_data['register_time']},'{$add_data[referer_url]}','{$add_data[current_url]}','{$add_data[pa_url]}',{$add_data['visit_time']},{$add_data['add_time']})";

    }

    function mytest2()
    {
    	/*$path = 'D:/phone_linshi.txt';
    	$array = file( $path );
    	echo count($array);
    	echo '<br>';
    	//print_r(array_values($array)); //返回所有键值
    	foreach ($array as $v) {
    		$str .= $v.',';
    	}
    	var_dump($str);
    	echo '<br>';
    	echo count($array);*/

    	$str = 'IM_Recovery_1484017201.log';
    	$log_number = substr($str,12,10);
    	echo $log_number;
    	$where ='12345zsd';
    	$where ='asdzcxzxxc';
    	echo $where;
    	echo $_SERVER['HTTP_USER_AGENT'] ;

        //缓存的思路
        //① 查询缓存的数据 
        //② 目标的数据是否 存在 缓存中
        //③ 存在执行操作
        //④ 不存在，先将目标数据存入缓存，在执行操作
        S('table_name_arr',null);
        $table_name_arr = S('table_name_arr');
        var_dump($table_name_arr);
        $table_name = 'table_name02';
        if(!isset($table_name_arr) || empty($table_name_arr))
        {
			$table_name_arr[] = $table_name;
            S('table_name_arr',$table_name_arr,300);
        }
        else
        {
            if(in_array($table_name,$table_name_arr))
        	{
        		var_dump($table_name_arr);
        		echo 'this is here';
        		return;
        	} 
            $table_name_arr[] = $table_name;
            S('table_name_arr',$table_name_arr,300);
        }
        $ret = S('table_name_arr');
        var_dump($ret);
        S('table_name_arr',null);    	
    }

    function myTest3()
    {
    	$ip = '101.90.252.27';
    	$info = GetIpLookup($ip);
    	echo $info['province'];
    	var_dump($info);
    	$encode = mb_detect_encoding($ip, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
    	echo $encode; 

    	$url = 'http://www.yueus.com/pc/register.php?r_url=http%3A%2F%2Fgoods.yueus.com%2Findex.php%3Fgoods_id%3D2151682%26type_id%3D0';
    	$num = strrpos($url,'r_url=');
    	echo $num;
    	$str = substr($url,$num+6);
    	$str = urldecode($str);
    	echo $str;
    	echo '<br>';
    	$url_data = get_url_data($url,'query');
    	var_dump($url_data);
    }

    function get_pa_two_table_name_log(){
    	$_cache_key     = "CLASS:MOBILE_FUNCTION:OPERATE_DATE_PROCESSING:" . $event_id . "|" . $phone_type . "|" . $begin_time . "|" . $end_time . "|" . $mult . "|" . $client_ver . "|" . date("Ymd");
        $poco_cache_obj = new poco_cache_class();
        $cache_data     = $poco_cache_obj->get_cache($_cache_key);
        
        if(!$cache_data)
        {
            $client_id = $this->get_client_id($event_id);
            

            $rs = array('pv'=>0, 'ip'=>0);
            
            for($i = strtotime($begin_time); $i <= strtotime($end_time); $i += 86400)
            {
            	$sql_str = "SELECT SUM(pv) AS pv, SUM(ip) AS ip FROM mobile_stat_db_by_{$client_id}.mobile_offline_stat_day_tbl_".date('Ym', $i)."
                        WHERE event_id = $event_id "; 
    
            if($phone_type != 'all')    $sql_str .= " AND client_sys = '{$phone_type}'";
            if($begin_time  &&  $end_time)  $sql_str .= " AND visit_time = '".date('Y-m-d', $i)."'";
                if($client_ver != 'all')             $sql_str .= " AND client_ver = '{$client_ver}'";
    
            $sql_str .= " GROUP BY event_id";        
            
            $_rs = db_simple_getdata($sql_str, TRUE, $this->server_id);
            	$rs['pv'] += $_rs['pv'];
            	$rs['ip'] += $_rs['ip'];
            
            }

            $days = (int)((strtotime($end_time) - strtotime($begin_time)) * 1 / ( 3600*24 ) + 1); 
            
            $array_result['id']     = $event_id;
            $array_result['name']   = $this->get_operate_name($event_id);
            $array_result['pv']     = round($rs['pv']*1*$mult/$days, 0);
            $array_result['ip']     = round($rs['ip']*1*$mult/$days, 0);
            $array_result['avg']    = round($array_result['pv'] /  $array_result['ip'],2);
        
            $_cache_time = 3600*24; 
$poco_cache_obj->save_cache($_cache_key,$array_result,$_cache_time);
            $cache_data = $array_result;  
        }         
        
        return $cache_data;   
    }

    public function myTest4(){
    	$url1 = 'http://goods.yueus.com/?goods_id=2149257&type_id=0';
    	$url2 = 'http://goods.yueus.com/';
    	$url3 = 'https://t.yueus.com/mUFe14';
    	$url4 = 'http://t.yueus.com/d/yUFNE3';
    	//$url3 = $_SERVER[＂SCRIPT_URI＂];
    	//header('Location:'.$url1);
    	$p = get_url_data($url3,$type='');
    	var_dump($p);
    	$path = $this->get_short_key_by_url($url4);
    	var_dump($path);

    }

    /*
    * 这里使用apche-ab(.exe)做并发的 数据碰撞测试
    * 做入库操作 查看重复 
    * SQL SELECT short_link_key,COUNT(*) AS c FROM `pai_user_short_line_master_tbl` GROUP BY short_link_key ORDER BY c DESC
    */
    function myTest5(){
    	ini_set("max_execution_time", 3600);
		ini_set('memory_limit','1024M');
    	/*echo '开始';
		echo '<br>';*/
		$model = M('TestKey');
		//$rand_arr = array();
		$user_url = 'http://goods.yueus.com/?goods_id=2132357';
		/*for($i=10000;$i<=1000000;$i++)
		{*/
		   // $yue_login_id = rand(10000,100000000);
		    $num = rand(10000,100000000);
		   // $yue_login_id = $i;
		    $yue_login_id = time()+$num;

		    $short_link_key = $this->short_url($yue_login_id.$user_url);
		    //$rand_arr = S('test_rand_arr');
			$add['key'] = $short_link_key;
		    $model->data($add)->add();	
		    //echo $model->getLastSql();

		    /*if(in_array($short_link_key,$rand_arr)){
		        echo $yue_login_id.'=>'.$short_link_key.'重复';
		        echo '<br>';
		    }else
		    {
		        //$rand_arr[] = $add['short_link_key'];
		        $rand_arr[] = $short_link_key;
		        S('test_rand_arr',$rand_arr,1800);
		    }*/
		//var_dump($rand_arr);    
		/*echo '<br>';
	    echo '结束';*/
    }

    function myTest6(){
    	/*$num = rand(10000,100000000);
    	$yue_login_id = time()+$num;
    	$rand_arr = S('test_rand_arr');
    	$rand_arr[] = $yue_login_id;
        S('test_rand_arr',$rand_arr,1800);*/
    	/*$rand_arr = S('test_rand_arr');
    	//S('test_rand_arr',null);
    	var_dump($rand_arr);*/
    	$ret = $this->myTest7(1,1,2);
    	var_dump($ret);
    }

    /*
    * 递归测试，注意递归之后每次都会返回到上一次的递归直到最开始，需要返回值
    */
    function myTest7($user_id,$url,$old_short_link_key,$num=0)
    {
    	static $return;
        $short_link_key = $user_id;
        // 这里 处理生成的KEY 碰撞情况
        if($short_link_key != $old_short_link_key)
        {
        	echo '******';
        	echo $num;
        	echo '<br/>';
            if($num>2) return $return = false;
            $num++;
            echo $num;
            $user_id++;
            $short_link_key = $user_id;
            echo '<br/>';
            echo $user_id.'=>'.$short_link_key.'=>'.$old_short_link_key;
            echo '<br/>';
            $this->myTest7($short_link_key,$url,$old_short_link_key,$num);
        }
        else
        {
        	echo '-------';
        	var_dump($user_id);
            return $return = $user_id;
           // return 'user_id';
        }
        return $return;
    }

    public function mytest8()
    {
    	$insert_data = array('user_id'=>100008,'url'=>'http://www.mojikids.com/topic/t_39/index.php');
    	$user_id = $insert_data['user_id'];
        $url = $insert_data['url'];
    	$short_url_key = $this->short_url($user_id.$url);
    	var_dump($short_url_key);exit();
    	$model = M('TestKey');
    	$add['key'] = $short_link_key;
		$model->data($add)->add();	
    }

    public function myTest9()
    {
    	$url = 'www.baidu.com';
    	$role ='yueseller';
    	$role_arr = array('yuebuyer','yueseller');
	    $role = trim($role);
	    $url = trim($url);
	    if(strlen($role)<1 || !in_array($role,$role_arr)) var_dump(1111);
	    if(strlen($url) <1)  var_dump(22222);
	    if($role == 'yuebuyer') $role = 'yueyue';

	    $url_arr = parse_url($url);
	    $httts = trim($url_arr['scheme']);
	    if(empty($httts))
    	{
    		$httts = 'http';
    		$url = 'http://'.$url;
    	} 
	    var_dump($url);
	    if($httts == 'http' || $httts == 'https')//是否为http||https
	    {
	        $url = str_replace('www.yueus.com','yp.yueus.com',$url);
	        $wifi_url = str_replace('yp.yueus.com','yp-wifi.yueus.com',$url);
	        $curl = "{$role}://goto?type=inner_web&url=".urlencode($url)."&wifi_url=".urlencode($wifi_url);
	    }
	    elseif($httts == 'yueyue' || $httts == 'yueseller')
	    {
	        $curl = $url;
	    }
	    var_dump($curl);
    }
    

    public function get_short_key_by_url($url)
    {
        $preg = '/^(http|https):\/\/t.yueus.com\/d\/([0-9a-zA-Z]+)/';
        if(preg_match($preg, $url, $m))
        {
            $this->set_info_array(1, 'OK:get_short_key_by_url');
            return $m[2];
        }else{
            $this->set_info_array(-1, 'ERR:get_short_key_by_url');
            return FAKSE;
        }
    }

    public function my_test_task()
    {
    	$query = 
    	$num = strpos($query,'query=');
        var_dump($num);
        echo '*<br/>';
    	exit();
    	$res = $this->my_task();
    	if($res)
    	{
    		var_dump(123456789);
    	}else
    	{
    		var_dump(987654321);
    	}
    }

    public function my_task()
    {
    	ignore_user_abort();//关闭浏览器仍然执行
		set_time_limit(0);//让程序一直执行下去
		$interval=3;//每隔3S时间运行
		$status = 1;
		do{
		    $nowTime=date("Y-m-d H:i:s");
		    //执行我的方法触发
	    	$task_result = $this->to_do_task();
	    	if($task_result)
	    	{
    			//$status = 0;
    			/*var_dump(123456);
    			exit();*/
    			return true;
	    	}
	    	else
	    	{
	    		var_dump(date('Y-m-d h:i:s',time()));
	    		sleep($interval);//等待时间，进行下一次操作。
	    	}
		    //file_put_contents("log.log",$msg,FILE_APPEND);//记录日志
		}while($status);
    }

    public function to_do_task()
    {
    	$nowTime = date('s',time());
    	// var_dump($nowTime);exit();
    	if($nowTime<50 && $nowTime>10)  return 1;
    	return 0;
    	/*$i = 1;
    	$num = 1;
    	while ( $i==1) {
    		$num++;
			echo $num.'<br>';
			if($num > 12) $i = $num;
    	}
    	//var_dump($i);
    	return true;*/
    }

  /**
  * 按设置划分成N数组
  */
    public function group_array()
    {
    	$arr = array(
             array('user_id'=>23),
             array('user_id'=>25),
    		);
    	$arr = array_column($arr, 'user_id');
    	var_dump($arr);exit();
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

    private function set_info_array($code, $msg)
    {
        $this->info_array['code'] = $code;
        $this->info_array['msg'] = $msg;
    }

    /*
     * 加密 key (key = user_id+url)
     */
    public function short_url($url){
        $url=crc32($url);
        $result=sprintf("%u",$url);
        return $this->code62($result);
    }

    private function code62($x){
        $show='';
        while($x>0){
            $s=$x % 62;
            if ($s>35){
                $s=chr($s+61);
            }elseif($s>9&&$s<=35){
                $s=chr($s+55);
            }
            $show.=$s;
            $x=floor($x/62);
        }
        return $show;
    }

}