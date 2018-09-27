<?php
namespace Test\Controller;
use Think\Controller;
class Test2Controller extends Controller{
	public function test_phone()
	{
		header("Content-type: text/html; charset=utf-8"); 
		$tel='102154126348';
		echo $tel;
		$isMob="/^1[3-8]{1}[0-9]{9}$/";
		$isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
		if(!preg_match($isMob,$tel) && !preg_match($isTel,$tel))
		{
		  echo '<script>alert("手机或电话号码格式不正确。如果是固定电话，必须形如(xxxx-xxxxxxxx)!");history.go(-1);</script>';
		  exit ();  
		}
		else
		{
			echo 'success';
		}
	}

	public function test_json()
	{
		$result = array('status'=>0,'msg'=>'参数格式不正确,请检查！','list'=>array());
        
        return $this->ajaxReturn($result);
	}

	public function test_get_json()
	{
		var_dump(date('t'));
		var_dump(getthemonth(date('Y-m-d')));
		$get_id = I('get.c_id');
        $get_type = I('get.type');
        $get_mcheck = I('get.mcheck');
		$token_str = $get_id.$get_type.'LIYANJI2017';
		//echo $token_str;
        $md5_token = md5($token_str);
		if($get_mcheck!==$md5_token)
        {
            echo 'error:'.$get_mcheck.' and right：'.$md5_token.'<br/>';
        }
        else
        {
        	echo 'success';
        }
		$res = $this->test_json();
		var_dump($res);
	}

}