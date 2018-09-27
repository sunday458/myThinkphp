<?php
namespace Common\Common\Util;

class InstanceHelper{
 
	private $config;
	
	private static $instance;
	
	public function __construct() {
		$instanceHelperConfig = C("INSTANCE_HELPER_MAPPING");
		if (!empty($instanceHelperConfig)) {
			$this->config = $instanceHelperConfig;
		} else {
			throw new Exception("找不到InstanceHelper的配置");
		}
	}
	
	/**
	 * 获取当前类的对象
	 */
	static private function getInstance() {
		if (empty(InstanceHelper::$instance)) {
			InstanceHelper::$instance = new InstanceHelper();
		}
		return InstanceHelper::$instance;
	}
	
	/**
	 * 获取指定“类”的对象
	 * @param string $classKey 类对应的key，这个key是固定的，在instance_helper_config.php里面定义
	 * @return class instance or null
	 */
	static public function createInstance($classKey) {
		$ins = InstanceHelper::getInstance();
		$className = $ins->config[$classKey];
		if (!empty($className)) {
			$newclass = new \ReflectionClass($className); // 建立反射类  
			$newinstance  = $newclass->newInstanceArgs(); // 实例化类
			return $newinstance;
		} else {
			return null;
		}
	}
}