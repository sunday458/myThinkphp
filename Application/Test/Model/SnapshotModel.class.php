<?php
namespace Test\Model;
use Think\Model;
/**
* 
*/
class SnapshotModel extends Model
{
	protected $tableName = 'snapshot_log'; 
	//protected $trueTableName = 'top_depts'; 

	const STATUS_RECOMMEND_Y = 101;      // 推荐到首页
	const STATUS_RECOMMEND_N = 100;      // 推荐到首页
	const STATUS_RECOMMEND_NONE = -999;      // 推荐到首页
	const STATUS_DRAFT       = 0;      // 编辑状态，未提交审核
	const STATUS_PASSED      = 1;      // 审核通过	
	public $evaluation = array(
			1=>'提示信息1',
    		2=>'提示信息2',
    		3=>'提示信息3',
    		4=>'提示信息4',
	);
	private $web = 'http://www.baidu,com';
	protected $url = 'http://www.dn.com';
	
	//① 方式 相当于 ②方式
	/*function __construct()
	{
		parent::__construct();   // 模型的调用必须继承 父类，不然就不写构造方法
	}*/
     //②方式
	function _initialize(){

	}

	/*function _set( $name,$value ){
		$this->$name = $value;
	}

	function _get( $name ){
		if(isset($this->$name))
		{
			return($this->$name);
		}else
		{
			return(NULL);
		}
	}*/

//私用，保护的属性，可以被外部调用，需要设置方法,无法直接调用
	function getWeb(){
		return $this->web;
	}

	function setWeb($web){
		$this->web = $web;
	}

	function getUrl(){
		return $this->url;
	}

	function setUrl($url){
		$this->url = $url;
	}


	/*protected $_validate = array(
		array('cover', '1,500', '文章内容必填，500字以内', Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
		array('title', '1,50', '文章标题必填，50字以内', Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
		array('brief', '1,300', '文章简介必填，300字以内', Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
		array('content', '1,99999', '文章内容必填，100字以上', Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
			
		//array('tags', '0,100', '文章标签100字以内', Model::VALUE_VALIDATE, 'length', Model::MODEL_BOTH),
	);
	
	protected $_auto = array(
		array("last_update", "time", Model::MODEL_INSERT, "function"),
		array("created", "time", Model::MODEL_INSERT, "function"),
		array("status", self::STATUS_DRAFT, Model::MODEL_INSERT),
	);
*/
	function myModelAction1(){
		echo time().'myModelAction1';
	}

	private function myModelAction2(){
		echo 'myModelAction2'.time();
	}

	protected function myModelAction3(){
		echo 'myModelAction3'.time();
	}

}