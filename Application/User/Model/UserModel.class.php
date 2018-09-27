<?php
namespace User\Model;
use Think\Model;

class UserModel extends Model{
 
	//保存类实例的静态成员变量
	//private static $_instance;
    //protected $connection = 'mysql://root:root@localhost:3306/my_test';
    //protected $connection = 'DB_CONFIG2';
    protected $_validate =array(
        array('name','require','name为空'), // 在新增的时候验证name字段是否唯一
        array('phone','checkPhone','联系方式不正确',0,'callback'), 
    );
 
	//private标记的构造方法
	/*public function __construct(){
		parent::__construct();
	}*/
 
	/*//创建__clone方法防止对象被复制克隆
	private function __clone(){
		trigger_error('禁止复制对象!',E_USER_ERROR);
	}
	 
	//单例方法,用于访问实例的公共的静态方法
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	 
	public function test(){
		echo '调用方法成功';
	}*/

    public function change_db($db_config = array())
    {
        //var_dump($db_config);
        $this->db(1,$db_config);
        /*if(!$model){
           $this->db(1,"DB_CONFIG1");
        }*/
    }

    protected function checkPhone($phone)
    {
    	header("Content-type: text/html; charset=utf-8"); 
		//$tel='102154126348';
		$tel=$phone;
		//echo $tel;
		$is_abroad = "/^[0-9]{5,}$/";//国外电话判断,大于5位数字
		$isMob="/^1[3-8]{1}[0-9]{9}$/";
		$isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
		if(!preg_match($isMob,$tel) && !preg_match($isTel,$tel) && !preg_match($is_abroad,$tel) )
		{
		  return false;
		  //echo '<script>alert("'.$phone.'");</script>';
		  //echo '<script>alert("手机或电话号码格式不正确。如果是固定电话，必须形如(xxxx-xxxxxxxx)!");history.go(-1);</script>';
		  exit ();  
		}
		else
		{
			return true;
			//echo 'success'.$phone;
		}
    }

}
 
//用new实例化private标记构造函数的类会报错
//$danli = new Danli();
 
//正确方法,用双冒号::操作符访问静态方法获取实例
/*$danli = Danli::getInstance();
$danli->test();
 
//复制(克隆)对象将导致一个E_USER_ERROR
$danli_clone = clone $danli; */