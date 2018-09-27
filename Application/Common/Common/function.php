<?php

/**
  * 随机字符
  * @param number $length 长度
  * @param string $type 类型
  * @param number $convert 转换大小写
  * @return string
  */
	 function random($length=6, $type='string', $convert=0){

	     $config = array(
	         'number'=>'1234567890',
	         'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
	         'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
	         'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
	     );
	     
	     if(!isset($config[$type])) $type = 'string';
	     $string = $config[$type];
	     
	     
	     $code = '';
	     $strlen = strlen($string) -1;
	     for($i = 0; $i < $length; $i++){
	         $code .= $string{mt_rand(0, $strlen)};
	     }
	     if(!empty($convert)){
	         $code = ($convert > 0)? strtoupper($code) : strtolower($code);
	     }
	     return $code;
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
	 function randCode($length = 5, $type = 0) {
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

/*
 生成一定长度的随机数字串
*/
	function generate_code($length = 6) {
	     return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
	}

/**
* 使用函数及数组来获取当月第一天及最后一天
*/

	function getthemonth($date){
	    $firstday = date('Y-m-01', strtotime($date));
	    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
	    return array($firstday,$lastday);
	}
    
/**
 * 友好提示信息
 * @param $msg 信息
 * @param bool $b_reload
 * @param null $url
 * @param bool $parents
 */
	function js_pop_msg($msg,$b_reload = false,$url=NULL,$parents=false)
	{
	    echo "<script language='javascript'>alert('{$msg}');";
	    if($url && $parents==true) echo "parent.location.href = '{$url}';";
	    if($url && !$parents) echo "location.href = '{$url}';";
	    if($b_reload) echo "history.back();";
	    echo "</script>";
	    //echo "<script>window.alert('退出成功');location.href='http://www.yueus.com/yue_admin/login_e.php?referer_url=http%3A%2F%2Fyp.yueus.com%2Fyue_admin_v2%2Fordinary_orgmg%2Findex.php';</script>";
	    exit;
	}   

/**
	 * 移动设备判断
	 * @return boolean  true:移动设备；false:pc
	 */
	function _isMobile(){
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA'])){
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT'])){
			$clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])){
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
				return true;
			}
		}
		return false;
	}

	/**
    **请求接口，返回JSON数据
    **@url:接口地址
    **@params:传递的参数
    **@ispost:是否以POST提交，默认GET
    */
   function _curl($url,$params=false,$ispost=0){
   	$httpInfo = array();
   	$ch = curl_init();
   
   	curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0 );
   	curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
   	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
   	curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
   	curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
   	curl_setopt($ch,CURLOPT_HEADER,FALSE);
   	if( $ispost )
   	{ 		
   		curl_setopt( $ch , CURLOPT_POST , true );
   		curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
   		curl_setopt( $ch , CURLOPT_URL , $url );
   	}
   	else
   	{
   		if($params){
   			curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
   
   		}else{
   			curl_setopt( $ch , CURLOPT_URL , $url);
   		}
   	}
   	$response = curl_exec( $ch );
   	if ($response === FALSE) {
   		#echo "cURL Error: " . curl_error($ch);
   		return false;
   	}
   	$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
   	$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );

   	return $response;
   }
	
	/**
	 * 以指定key的value作为多维数组的key, $is_kv:true 返回仅包含key的value数组
	 */
	function array_as_key($array, $key, $is_kv = false) {
	    $data = array();
	    foreach ($array as $v) {
	        if ($is_kv)
	            $data[] = $v[$key];
	        else
	            $data[$v[$key]] = $v;
	    }
	    return $data;
	}

	/**
	 * 表单验证机制(待完善)：手机号码，邮箱，姓名
	 * 验证错误，ajax返回
	 * @param string $lable 验证规则标签[中文姓名：'nameCN';邮箱:'email';手机号码:'phone']
	 * @param string $tel 验证字段
	 * 手机号码开头数字：15[0~9](154除外);
	 * 				   13[0~9],18[0~9]
	 * 				   17[6|7|8],14[5|7]	
	 * error_code:20开头
	 */
    function _checkSubmitRule($lable,$value){
    	switch ($lable){
    		case  'nameCN' :
    			$reg = "/^[\x80-\xff]{6,16}$/";
    			$data['info'] = '姓名验证不合格';
    			$data['error_code'] = 201;
    			break;
    		case  'email':
    			$reg = '/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/';//ps:待开发，测试阶段
    			$data['info'] = '邮箱验证不合格';
    			$data['error_code'] = 202;
    			break;
    		case  'phone':
    			$reg = '/^0?(1[7|3|8][0-9]\d{8}|15[012356789][0-9]\d{7}|14[5|7][0-9]\d{7})$/';	
    			$data['info'] = '请输入正确的手机号码';
    			$data['error_code'] = 203;
    			break;
    	};   	
//17[6|7|8][0-9]\d{7}|
    	$matchResult = preg_match($reg,$value);
    	if (!$matchResult){
    		$data['status'] = false;
    		header('Content-Type:application/json; charset=utf-8');
    		exit(json_encode($data));
    	}else{
    		return $data = true;//验证通过，直接输出状态  		
    	}
 	
    }

    /**
	 * 数组转换成update_sql字符串
	 * 数组：array("FIELD1"=>"val1","FIELD2"="val2"')
	 * 转换成字符串：FIELD1='val1', FIELD2='val2'
	 * @access private
	 */
	// if (!function_exists("db_arr_to_update_str"))
	// {
	    function db_arr_to_update_str($array)
	    {
	        $sign = "";
	        $sql_str = "";
	        foreach ($array as $k=>$v)
	        {
	            $sql_str.= $sign."{$k}=:x_{$k}";
	            sqlSetParam($sql_str, "x_{$k}", $v);
	            $sign = ",";
	        }
	        return $sql_str;
	    }
	// }

	    /**
	 * 替换sql中参数，例子：
	 * $sql="SELECT * FROM a WHERE field1=:x_field1";
	 * sqlSetParam($sql,"x_field1","值");
	 * $sql将会替换成"SELECT * FROM a WHERE field1='值'"
	 */
	// if (!function_exists("sqlSetParam"))
	// {
	    function sqlSetParam(&$sql,$paramName,$paramValue)
	    {
	        $tmp=&$sql;

	        if (get_magic_quotes_gpc())
	        {
	            $paramValue = stripslashes($paramValue);
	        }


	        $tmp=str_replace(":".$paramName,"'".mysql_escape_string($paramValue)."'",$tmp);
	        $tmp=str_replace("@".$paramName,"'".mysql_escape_string($paramValue)."'",$tmp);
	    }
	// }

	    /**
     * 获取数组的维度
     * @param $array
     * @return int
     */
    function array_depth($array) {
        if(!is_array($array)) return 0;
        $max_depth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = array_depth($value) + 1;
                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }
        return $max_depth;
    }

    /**
* 转换IPv6地址为bin
* @param string $ip 返回类型 0 数字 1 返回False
* @return mixed
*/
function ip2bin($ip) 
{ 
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) 
	return base_convert(ip2long($ip),10,2); 
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) 
	return false; 
	if(($ip_n = inet_pton($ip)) === false) return false; 
	$bits = 15; // 16 x 8 bit = 128bit (ipv6) 
	while ($bits >= 0) 
	{ 
	$bin = sprintf("%08b",(ord($ip_n[$bits]))); 
	$ipbin = $bin.$ipbin; 
	$bits--; 
	} 
	return $ipbin; 
} 
/**
* 转换bin地址为IPv6 或IPv4
* @param long $bin 返回类型 0 IPv4 IPv6地址
* @return mixed
*/
function bin2ip($bin) 
{ 
	if(strlen($bin) <= 32) // 32bits (ipv4) 
	return long2ip(base_convert($bin,2,10)); 
	if(strlen($bin) != 128) 
	return false; 
	$pad = 128 - strlen($bin); 
	for ($i = 1; $i <= $pad; $i++) 
	{ 
	$bin = "0".$bin; 
	} 
	$bits = 0; 
	while ($bits <= 7) 
	{ 
	$bin_part = substr($bin,($bits*16),16); 
	$ipv6 .= dechex(bindec($bin_part)).":"; 
	$bits++; 
} 
	return inet_ntop(inet_pton(substr($ipv6,0,-1))); 
} 
/**
* 获取客户端IP地址
* @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
* @return mixed
*/
function get_client_ip6($type = 0) {
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL) return $ip[$type];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	$pos = array_search('unknown',$arr);
	if(false !== $pos) unset($arr[$pos]);
	$ip = trim($arr[0]);
	}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u",ip2bin($ip));
	$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}
/**
*作废
*获取上一个月的今天时间,未对31,30号等做处理
*/
/* function last_month_today($time,$is_int = false) {
    if (!$is_int) {
    	$time = strtotime($time);
    }
    $last_month_time = mktime(date("G", $time), date("i", $time),
        date("s", $time), date("n", $time), - 1, date("Y", $time));
    return date(date("Y-m", $last_month_time) . "-d", $time);
 }*/

 /**
 * 计算上一个月的今天，如果上个月没有今天，则返回上一个月的最后一天
 * @param type $time
 * @return type
 */
function last_month_today($time,$is_int = false){
	if (!$is_int) {
    	$time = strtotime($time);
    }
    $last_month_time = mktime(date("G", $time), date("i", $time),
                date("s", $time), date("n", $time), 0, date("Y", $time));
    $last_month_t =  date("t", $last_month_time);

    if ($last_month_t < date("j", $time)) {
        return date("Y-m-t", $last_month_time);
    }

    return date(date("Y-m", $last_month_time) . "-d", $time);
}

/**
*生成CSV文件
*生成文件名,数据，表头
*/
	function add_csv($file_name,$arr,$csv_header=''){
        /**
         * 生成默认以逗号分隔的CSV文件
         * 解决：内容中包含逗号(,)、双引号("")
         */
        header("Content-Type: application/vnd.ms-excel; charset=GBK");
        header("Content-Disposition: attachment;filename=$file_name ");

        $str = '';
        if(!empty($csv_header))
        {
            $res_arr = $arr;
            $arr = array();
            $arr = $csv_header;
            foreach ($res_arr as  $v) {
                $arr[] = $v;
            }
        }
        foreach ($arr as $row) {
            $str_arr = array();
            foreach ($row as $column) {
                $str_arr[] = '"' . str_replace('"', '""', $column) . '"';
            }
            $str.=implode(',', $str_arr) . PHP_EOL;
        }
        return $str;
    }

/**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    /*protected function ajaxReturn($data,$type='JSON') {
        //if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');  
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
        }
    }*/

    function get_path_url(){
    	//测试网址:     http://localhost/blog/testurl.php?id=5
    	//获取域名或主机地址 
		echo $_SERVER['HTTP_HOST']."<br>"; //localhost

		//获取网页地址 
		echo $_SERVER['PHP_SELF']."<br>"; //blog/testurl.php

		//获取网址参数 
		echo $_SERVER["QUERY_STRING"]."<br>"; //id=5

		//获取用户代理 
		echo $_SERVER['HTTP_REFERER']."<br>"; //来源页？

		//获取完整的url
		echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		//http://localhost/blog/testurl.php?id=5

		//包含端口号的完整url
		echo 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
		// http://localhost:80/blog/testurl.php?id=5

		//只取路径
		$url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]; 
		echo dirname($url);
		//http://localhost/blog
    }

    function get_all_table_name($db_name,$where=''){
    	$sql = " SELECT table_name FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = '{$db_name}'.$where ";
    }

    //通过IP获取用户所属城市(新浪接口)
    //返回格式: array(10) { ["start"]=> int(-1) ["end"]=> int(-1) ["country"]=> string(6) "中国" ["province"]=> string(6) "上海" ["city"]=> string(6) "上海" ["district"]=> string(0) "" ["isp"]=> string(0) "" ["type"]=> string(0) "" ["desc"]=> string(0) "" ["ip"]=> string(13) "101.90.252.27" }
    function GetIpLookup($ip = ''){  
	    if(empty($ip)){  
	        // $ip = GetIp();  
	        return false;
	    }  
	    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);  
	    if(empty($res)){ return false; }  
	    $jsonMatches = array();  
	    preg_match('#\{.+?\}#', $res, $jsonMatches);  
	    if(!isset($jsonMatches[0])){ return false; }  
	    $json = json_decode($jsonMatches[0], true);  
	    if(isset($json['ret']) && $json['ret'] == 1){  
	        $json['ip'] = $ip;  
	        unset($json['ret']);  
	    }else{  
	        return false;  
	    }  
    	return $json;  
	}  

	/*
	*检查字符的编码(待调整，暂不适用)
	*/
	function check_char($str){
		mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		if(strtolower($encode) == strtolower('EUC-CN')) $encode = 'GB2312';
    	return $encode; 
	}
    /*
    * 解析地址
    */
	function get_url_data($url,$type=''){
		$url_info = parse_url($url);
		if (empty($type)) {
			return $url_info;	
		}elseif ($type == 'host') {
			return $url_info['host'];
		}elseif ($type == 'path') {
			return $url_info['path'];
		}elseif ($type == 'scheme') {
			return $url_info['scheme'];
		}elseif ($type == 'user') {
			return $url_info['user'];
		}elseif ($type == 'pass') {
			return $url_info['pass'];
		}elseif ($type == 'fragment') {
			return $url_info['fragment'];
		}elseif($type == 'query') {
			return $url_info['query'];
		}
	}

	/*
	*  utf8编码判断
	*/
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

	/*
	* 数组转编码
	*/
	function array_iconv($data, $in_charset='gbk', $out_charset='utf-8')
    {
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                $data[$k] = $this->array_iconv($v);
            }
            return $data;
        }
        else
        {
            return mb_convert_encoding($data, $out_charset, $in_charset);
        }
    }
    /*
    * 获取 页面的 header信息( referer 来源)
    */
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

	function get_curl_data($url = '')
	{
		if(empty($url)) return false;
		//$url = 'www.yueus.com';
		// 1. 初始化
		$ch = curl_init();
		// 2. 设置选项，包括URL
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		/*if($output === FALSE ){
			echo "CURL Error:".curl_error($ch);
		}*/
		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		/*var_dump($info = curl_getinfo($ch));
		var_dump($info['http_code']);*/
		// 4. 释放curl句柄
		curl_close($ch);
		return $http_code ;
	}

	function create_short_url()
	{
		 $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	}

    /**
	* 发送HTTP请求并获得响应
	* @param url 请求的url地址
	* @param param 发送的http参数
	*/
	function makeRequest($url, $param, $httpMethod = 'GET') {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if ($httpMethod == 'GET') {
                curl_setopt($oCurl, CURLOPT_URL, $url . "?" . http_build_query($param));
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        } else {
                curl_setopt($oCurl, CURLOPT_URL, $url);
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($oCurl, CURLOPT_POST, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($param));
        }

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
                return $sContent;
        } else {
                return FALSE;
        }
	}

	function getDecimalVal($ch)
	{
	    if (is_numeric($ch))
	    {
	        return intval(ord($ch) - ord('0'));
	    }
	    else
	    {
	        return intval(ord($ch) - ord('a') + 10);
	    }
	}

	function read_log_data($file_path,$type=1)
	{
		if(!file_exists($file_path)) return false;
		switch ($type) {
			case 1:
				$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
				//$str = str_replace("\r\n","\t",$str);
		    	return $str;
				break;
			case 2:
				$file_arr = file($file_path);
				//$str = '';
				for($i=0;$i<count($file_arr);$i++){//逐行读取文件内容
					$str .= $file_arr[$i];
					//echo $file_arr[$i]."<br />";
			    }   
			    return $str;
				break;
			default:
				
				break;
		}	
	}
	// 提取字符串中数字
	function findNum($str='',$type=1)
	{
        $str=trim($str);
        if(empty($str)){
        	return '';
        }
        if($type == 1)
        {
        	$reg='/(\d{3}(\.\d+)?)/';//匹配数字的正则表达式
	        preg_match_all($reg,$str,$result); 
	        if(is_array($result)&&!empty($result)&&!empty($result[1])&&!empty($result[1][0])){
	            return $result[1][0];
	        }
	        return '';
        }
        elseif ($type ==2) 
        {
        	$temp=array('1','2','3','4','5','6','7','8','9','0');
	        $result='';
	        for($i=0;$i<strlen($str);$i++){
	            if(in_array($str[$i],$temp)){
	                $result.=$str[$i];
	            }
	        }
        	return $result;
        }
        else
        {
        	$result='';
	        for($i=0;$i<strlen($str);$i++){
	            if(is_numeric($str[$i])){
	                $result.=$str[$i];
	            }
	        }
	        return $result;
        }
        
    }


    //递归模型 10次退出
    function test_rand($in=1)
    {
        $user = M('user_tbl','mall_');
        $find_data = $user->where(array('id'=>$in))->getField('id');
        //var_dump($find_data);
        if($find_data)
        {
            $in+=1;
            if($in>10)
            {
                $in = '';
            }
            else
            {
                $in = $this->test_rand($in);           
            }
        }
        return $in;
    }

    //PHP自身提供的copy文件函数：应用拷贝图片  
    //copy("来源","地点")  
    //$file_path=iconv("utf-8","gb2312","含中文路径");  
    //将utf-8编码转为gb2312码  
    /*if(!copy("C:\\bh.PNG","D:\\bh2.png")) { 
        echo 'error'; 
    } else { 
        echo 'ok'; 
    }*/  
    //自制拷贝文件的函数  
    function myCopyFunc($res, $des) 
    {  
        if(file_exists($res)) 
        {  
            $r_fp=fopen($res,"r");  
              
            //定位  
            $pos=strripos($des,"\\");  
            $dir=substr($des,0,$pos);  
            if(!file_exists($dir)) 
            {  
                //可创建多级目录  
                mkdir($dir,0777,true);  
                echo "创建目录成功<br/>";  
            }  
  
            $d_fp=fopen($des,"w+");  
            //$fres=fread($r_fp,filesize($res));  
  
            //边读边写  
            $buffer=1024;  
            $fres="";  
            while(!feof($r_fp))
            {  
                $fres=fread($r_fp,$buffer);  
                fwrite($d_fp,$fres);  
            }  
  
            fclose($r_fp);  
            fclose($d_fp);  
  
            echo "复制成功";  
        } 
        else 
        {  
            echo "源文件不存在";  
        }  
    }  

    function saveImage($path) 
    {
 		if(!preg_match('/\/([^\/]+\.[a-z]{3,4})$/i',$path,$matches))
 		{
 			die('Use image please');
 		}

		//目录检测
		$save_path = "D:/tmp/".date('Y').'/'.date('md').'/';
 		if(!file_exists($save_path)) 
        {  
            //可创建多级目录  
            mkdir($save_path,0777,true);  
            echo "创建目录成功<br/>";  
        } 
	 
	 	$image_name = strToLower($matches[1]);
	 	$image_name = '002.jpg';
	 	$ch = curl_init ($path);
	 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	 	$img = curl_exec ($ch);
	 	curl_close ($ch);
	 	//$save_path = "D:/tmp/";//存放路径
	 	$fp = fopen($save_path.$image_name,'w');
	 	fwrite($fp, $img);
	 	fclose($fp);
	}

	//递归层级关系
	function get_tree($data,$parent_id=0,$level=0)
	{
	    static $list=array();
	    foreach($data as $row)
	    {
	        if($row['parent_id']==$parent_id)
	        {
	            $row['level']=$level;
	            $list[]=$row;
	            //进行递归
	            $this->get_tree($data,$row['id'],$level+1);
	        }
	    }
	    return $list;
	}

	/**
	 * @param $arr 数组
	 * @param $id   id
	 * @param $level  层级
	 * @return array
	 */
	function demo($arr,$id,$level)
	{
		/*
		 $arr = array(
		    array('id'=>1,'name'=>'电脑','pid'=>0),
		    array('id'=>2,'name'=>'手机','pid'=>0),
		    array('id'=>3,'name'=>'笔记本','pid'=>1),
		    array('id'=>4,'name'=>'台式机','pid'=>1),
		    array('id'=>5,'name'=>'智能机','pid'=>2),
		    array('id'=>6,'name'=>'功能机','pid'=>2),
		    array('id'=>7,'name'=>'超级本','pid'=>3),
		    array('id'=>8,'name'=>'游戏本','pid'=>3),
		);
		*/

	    $list =array();
	    foreach ($arr as $k=>$v)
	    {
	        if ($v['pid'] == $id)
	        {
	            $v['level']=$level;
	            $v['son'] = demo($arr,$v['id'],$level+1);
	            $list[] = $v;
	        }
	    }
	    return $list;
	}
	//print_r(demo($arr,0,0));
	//
	function array_sort($array,$keys,$type='asc'){
	//$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序
	    $keysvalue = $new_array = array();
	    foreach ($array as $k=>$v){
	        $keysvalue[$k] = $v[$keys];
	    }
	    if($type == 'asc'){
	        asort($keysvalue);
	    }else{
	        arsort($keysvalue);
	    }
	    reset($keysvalue);
	    foreach ($keysvalue as $k=>$v){
	        $new_array[$k] = $array[$k];
	    }
	    return $new_array;
	}
    

    function test_number()
    {
		$down  = '  1912  ';
		$up    = '  23456781011  ';
		$first = '  8  '; 
		$yu_1  = '  =>  |   ';
		$yu_2  = '  =>  |   ';
		$yu_3  = '  =>  |   ';
		$yu_4  = '  =>  |   '; 

		$lang = '    ';

		$t0   = '  9-6 112-4  ';
		$t0   = '    ';
		$t1   = '  37- 8910-4   ';
		$t2   = '    ';
		$t3   = '    ';
		$tip  = '    ';

		$table = "
		   1			7
		   2		    8
		   3			9
		   4/9=37105	10
		   5		    11/8=1
		   6=10+11		12
		";

		$style = '   ';
		$park  = '  
			1   	7 
			2		8
			3		9  
			4		10 
			5		11 
			6  		12 
		';

    }

