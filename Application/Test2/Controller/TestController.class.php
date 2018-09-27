<?php 
namespace Test2\Controller;
use Think\Controller;

class TestController extends Controller{
	function create_code(){
		//① 主键作为种子
		/*$ret_id = $this->add_code_data();
		$uniqid = uniqid( $ret_id );*/
		//② 时间作为种子
		$user_id = rand(10000,99999);
		$now_time = time();
		$uniqid  = uniqid($user_id);
		$s_str   = substr($uniqid,-5,5);
		$str_pad = str_pad($s_str,8,'*',STR_PAD_BOTH);
		$str_code = $s_str.randCode(3);
		var_dump($uniqid); 
		echo '<br>';
		var_dump($s_str); 
		echo '<br>';
		var_dump($str_pad); 
		echo '<br>';
		var_dump($str_code); 
		echo '<br>';
	}

	function add_code_data(){
		$code_mdoel = M('CodeTbl');
		$add['user_id'] = 100008;
		$add['rand_str'] = "randCode(3)";
		$add['add_time'] = time();
		$ret_id = $code_mdoel->data($add)->add();
		if ($ret_id) {
			return $ret_id;
		}
		return false;
	}

	public function get_short_url($url = '')
    {
    	$key = 'yueyue!@#456';
    	$curl_url = 'http://t.yueus.com/create_inside_short_url.php';
    	if(empty($url)) $url = 'http://www.yueus.com';
    	$hash = md5($url.$key);
    	//$str = file_get_contents($curl_url);
    	$curl_url = $curl_url.'?url='.$url.'&hash='.$hash;
    	echo $curl_url;echo '<br>';
    	$str = $this->doCurlGetRequest($curl_url);
    	//$str = $this->my_curl($curl_url);
        $hash = '';
        //$curl_result = _curl($url);
        $json_data = json_decode($str);
        $state = $json_data->state;
        $url   = $json_data->url;
        $info  = $json_data->info;

        var_dump( array('state'=>$state,'url'=>$url,'info'=>$info) );
    }

    /**
	*@desc 封闭curl的调用接口，get的请求方式。
	*/
	function doCurlGetRequest($url){
		$ch = curl_init($url) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;  
		return $output;
	}

	function my_curl($url){
		// 1. 初始化
		 $ch = curl_init();
		 // 2. 设置选项，包括URL
		 curl_setopt($ch,CURLOPT_URL,$url);
		 curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		 curl_setopt($ch,CURLOPT_HEADER,0);
		 // 3. 执行并获取HTML文档内容
		 $output = curl_exec($ch);
		 if($output === FALSE ){
		 	echo "CURL Error:".curl_error($ch);
		 }
		 // 4. 释放curl句柄
		 curl_close($ch);

		 var_dump($output);
	}

	public function send_data()
	{
		$data = array(
			'name'=>'xxxx',
			'list'=>array(array(1=>'123','2'=>'456')),
		);
		//var_dump($data);
		$this->assign('data',$data);
		$this->display();
	}
}

?>