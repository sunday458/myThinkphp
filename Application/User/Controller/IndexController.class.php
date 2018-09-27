<?php
namespace User\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        parent::__construct();
        header('Content-type:text/html;charset=utf-8');
        echo '欢迎来的User下的默认模块<br>';
        echo session_id();
         echo '<br>';
        echo date('Y-m-d H:i:s');
    }

    public function test()
    {
    	header('Content-type:text/html;charset=utf-8');
    	echo '欢迎来的User下的默认模块';
    }
}