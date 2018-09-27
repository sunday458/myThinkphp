<?php
return array(
	//'配置项'=>'配置值'
	 //数据库配置信息，方法一
     'DB_TYPE'   => 'mysql', // 数据库类型
     //'DB_HOST'   => 'localhost', // 服务器地址
     'DB_HOST'   => '127.0.0.1', // 服务器地址
     'DB_NAME'   => 'test', // 数据库名
     'DB_USER'   => 'root', // 用户名
     'DB_PWD'    => 'root', // 密码
     'DB_PORT'   => 3306, // 端口
     'DB_PREFIX' => 'ts_', // 数据库表前缀
     'SHOW_PAGE_TRACE' =>true,  // 页面 trace 调试
     //'URL_HTML_SUFFIX' => 'html|shtml|xml', //伪静态
     
     // 数据库连接 方法二 => 数据库类型://用户名:密码@数据库地址:数据库端口/数据库名
     //'DB_DSN' => 'mysql://username:password@localhost:3306/DbName'

     //其他项目配置参数
     /*'AUTOLOAD_NAMESPACE' => array(   //自定义类
          'Lib' => APP_PATH.'Lib',
     )*/

    //数据库配置1
   /* 'DB_CONFIG1' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        //'DB_HOST'   => 'localhost', // 服务器地址
        'DB_HOST'   => '127.0.0.1', // 服务器地址
        'DB_NAME'   => 'test', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'root', // 密码
        'DB_PORT'   => 3306, // 端口
        'DB_PREFIX' => 'ts_', // 数据库表前缀
    ),*/
    //数据库配置2
    //'DB_CONFIG2' => 'mysql://root:root@localhost:3306/my_test'
    'DB_CONFIG2' => array(
        'db_type'  => 'mysql',
        'db_user'  => 'root',
        'db_pwd'   => 'root',
        'db_host'  => 'localhost',
        'db_port'  => 3306,
        'db_name'  => 'my_test',
        'db_prefix' => 'ts_'
    ),
    'DB_CONFIG3' => array(
        'db_type'  => 'mysql',
        'db_user'  => 'sew',
        'db_pwd'   => 'sew865419!',
        'db_host'  => '192.168.6.250',
        'db_port'  => 3306,
        'db_name'  => 'crm_wan_asiaski',
        'db_prefix' => ''
    ),
    'DB_CONFIG4' => array(
        'db_type'  => 'mysql',
        'db_user'  => 'sew',
        'db_pwd'   => 'sew865419!',
        'db_host'  => '120.76.240.241',
        'db_port'  => 3306,
        'db_name'  => 'mall_db',
        'db_prefix' => 'mall_'
    ),

    'DEFAULT_MODULE'     => 'User', //默认模块
                                    //
    'MEMBER_POINT_MSG'=>array(
        1=>'等级',
        2=>'邀请积分激励',
        3=>'消费积分',
        4=>'签到',
        5=>'连续7天签到',
        101=>'兑换',  //减分行为 编号从100后开始(不包括100)
        102=>'抽奖',
        10000=>'系统',
        10002=>'系统减积分',
        10003=>'初始化积分',
        10004=>'年度清空积分',
    ),

);