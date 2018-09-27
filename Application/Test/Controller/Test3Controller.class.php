<?php
namespace Test\Controller;
use Think\Controller;
/*define("TOKEN", "weixin");
$wechatObj = new Test3Controller();
$wechatObj->responseMsg();*/
class Test3Controller extends Controller{
	private $players = array();
	private $count = 2;

	/**
	* @param [array] 选手名单
	* @param [integer] 分组数
	*/
	function __construct($players, $count = 2)
	{
		$this->players = $players;
		$this->count = $count;
	}

	/**
	* @return [array] 分组后的结果
	*/
	/*
	* 思路：
	* 元素替换法
	* 默认把倒叙排列的数组分成两个数组，最大的$quantity个为一组，剩下最小的为另外一组，
	* 依次用最小的里面的最大的元素替换最大的组里的最小的，然后对数组求和，
	* 把和跟平均数比较，如果最大的数组的和小于等于平均数，就停止，返回最大的数组，
	* 利用array_diff()得到另外一个数组。
	*/
	public function divide($group = array(),$count=2) 
	{
		$group_score = array();
		//转换成只有score的一维数组
		foreach ($group as $gro)
		{
		   foreach ($gro as $k=>$v)
		   {
		       if($k=="score")
		       {
		           array_push($group_score, $v);
		       }
		   }
		}
		$con = count($group_score); 
		$quantity = round($con/$count); //每组的人数
		$sum = array_sum($group_score);
		$average = round($sum/$count); // 分数取平均
		rsort($group_score); //数组倒叙排列


		$big_array = array();
		for($a=0;$a<$quantity;$a++)
		{
		   array_push($big_array, $group_score[$a]);
		}
		$small_array = array();
		$small_array = array_diff($group_score, $big_array);

        for($a=$con-$quantity;$a>=0;$a--)
        {
            if(array_sum($big_array)>$average)
            {
                for($b=$quantity;$b<$con;)
                {
                    list($big_array[$a],$small_array[$b])=array($small_array[$b],$big_array[$a]);
                    if(array_sum($big_array)>$average)
                    {
                        $b++;
                    }
                    else 
                    {
                        break;
                    }
                }
            }
        }
		//还原数组
		$big = array();
		$small = array();
		foreach ($group as $gro)
		{
		   foreach ($gro as $k=>$v)
		   {
		       if($k=="score")
		       {
		           if(in_array($v, $big_array))
		           {
		               $big []= $gro;
		           }
		           else
		           {
		               $small []= $gro;
		           }
		       }
		   }
		}
		print_r($big);
		echo '+++++++';
		print_r($small);
	}


	public function test_data()
	{
		$arr = array(
			array("player"=>"a","score"=>22),
			array("player"=>"b","score"=>15),
			array("player"=>"c","score"=>24),
			array("player"=>"d","score"=>10),
			array("player"=>"e","score"=>30),
		);

		$this->divide($arr);
      
	}

	public function test()
	{
		echo $links_num = ceil(5/3);
		$numbers=array(3,5,1,22,11,5);
		sort($numbers);

		$arrlength=count($numbers);
		for($x=0;$x<$arrlength;$x++)
	    {
		   echo $numbers[$x];
		   echo "<br>";
	    }
	}

	public function test2()
	{
		// TODO 查询客服在线列表 

		if ( 空闲状态的客服数 > 0 )
		{
	    	// 分配给最先进入空闲状态的那个客服
		}
		else
		{
		    // 说明现在每个客服都有用户在咨询
		    // 并且可能某些客服下还有在排队等待的用户
		    // 获取每个客服 **当前咨询任务** 的开始时间
		    
		    //将这个用户分配给 当前资讯任务 的开始时间最早的并且后面排队人数最少的哪个客服
		    // 其实上面还有一个权重的问题，比如 A 客服 当前客户资讯任务开始时间比 B 早 2分钟，但是 A 后面排队的人数比 B 客服多 1个，这就要靠 权重值 来计算是分配给 A 还是 B。
		}
	}

	public function user_list()
	{
		$url = " https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN ";
		//TODO 分批 递归去调用 客户列表 并 入库
		
		$user_list = array();
		$user_list = json_decode($user_list,true);
		if($user_list['total']>$user_list['count'])
		{
			$links_num = ceil($user_list['total']/$user_list['count']);
			$next_openid = '';
			for ($i=0; $i < $links_num; $i++) 
			{ 
				$get_user_list = array();
				$all_user = array();
				$get_user_list = $this->get_weixin_user_list($next_openid);
				$next_openid = $get_user_list['next_openid'];
				$all_user = $get_user_list['data'];
				foreach ($all_user as $k => $v) 
				{
					//粉丝入库
				}

			}
		}
		else
		{
			//未超过上限,直接入库
			$all_user = $user_list['data'];
			foreach ($all_user as $k => $v) 
			{
				//粉丝入库
			}

		}

		//TODO 每次查询最后做记录log,方便下次拉取用户列表开始位置
	}

	public function get_weixin_user_list($next_openid='')
	{
		if($next_openid)
		{
			$url = " https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=".$next_openid;
		}

		//查询
		$list = array();
		$list = json_decode($list,true);
		return $list;
	}

	public function responseMsg()
	{
		//接收“关注”动作xml消息
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if(!empty($postStr))
		{
			//xml做安全防注入处理
			libxml_disable_entity_loader(true);
			//生成一个对象
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $MsgType = $postObj->MsgType;
            $Event = $postObj->Event;
            $time = time();
            if($MsgType=='event')
            {

                if ($Event=='subscribe') 
                {
                    //消息模板
                    $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";             
        
	                $contentStr = "欢迎来到丽颜肌公众号!";
	                //通过php函数sprintf进行填充模板里面的百分号
	                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
	                echo $resultStr;
                }
                elseif ($Event=='unsubscribe') 
                {
                	//不关注就无法收到信息,只改变状态
                }
            }
		}
		else 
		{
            echo "";
            exit;
        }

        /*$wechatObj = new Test3Controller();
		$wechatObj->responseMsg();*/

	}

	public function deal_list($list=array(),$c=100)
	{
		$total = count($list);
		$num = ceil($total/$c);
		$all_list = array();
		for ($i=0; $i < $num; $i++) 
		{ 
			if(count($all_list[$i])>$c)
			{
				$all_list[$i+1][] = $list['openid'];
			}
			else
			{
				$all_list[$i][] = $list['openid'];
			}
		}

		return $all_list;
	}

    // 详细客服分配log  
	public function allot_data_log($user_id,$group_id,$allot_data)
	{
		$wp_weixin_kf_allot_data_log = M('weixin_kf_allot_data_log','wp_');
		$add_data = array(
				'update_time' => time() , 
				'allot_log' => $allot_data , 
				'group_id' => $group_id , 
				'user_id' => $user_id , 
		);
		$id = $wp_weixin_kf_allot_data_log->add($add_data);
		$result = $id?1:0;
		return $result;
	}

	// 客服分配log,只记录最近最后1条
	public function allot_log($allot_log_id,$allot_data,$is_update = 0)
	{
		$wp_weixin_kf_allot_log = M('weixin_kf_allot_log','wp_');
		if($is_update)
		{
			$update_data = array(
				'update_time' => time() , 
				'allot_log' => $allot_data , 
				'allot_log_id' => $allot_log_id , 
			);
			$id = $wp_weixin_kf_allot_log->where(array('id' => 1, ))->save($allot_data);
			$result = $id!==false?1:0;
		}
		else
		{
			$add_data = array(
				'update_time' => time() , 
				'allot_log' => $allot_data , 
				'allot_log_id' => $allot_log_id , 
			);
			$id = $wp_weixin_kf_allot_log->add($add_data);
			$result = $id?1:0;
		}

		$return_data['code'] = 4001;
        $return_data['message'] = '微信用户绑定客服失败!';
        $return_data['data'] = array();

        if($result)
        {
        	$return_data['code'] = 200;
        	$return_data['message'] = '微信用户绑定客服成功!';
        }

        var_dump($return_data);

	}


	public function get_allot_data_log($uid=0)
	{
		$wp_weixin_kf_allot_log = M('weixin_kf_allot_data_log','wp_');
		//$kf_allot_log = $wp_weixin_kf_allot_log->where(array('user_id'=>$uid))->order('update_time desc')->getField('allot_log');
		$kf_allot_log = $wp_weixin_kf_allot_log->order('update_time desc')->getField('allot_log');
		$kf_allot_log = $kf_allot_log?json_decode($kf_allot_log,true):array();
		if(!$kf_allot_log)
		{
			$wp_weixin_kf_group = M('weixin_kf_group','wp_');
			$group_data = $wp_weixin_kf_group->field('id')->select();
			foreach ($group_data as $v) 
			{
				$kf_allot_log[$v['id']] = 0;
			}

		}
		return $kf_allot_log;
	}

    public function get_allot_data_log2($uid=0)
    {
        $wp_weixin_kf_allot_log = M('weixin_kf_allot_data_log','wp_');

        $kefu_id = $wp_weixin_kf_allot_log->order('update_time desc')->getField('kefu_id');
        $kefu_id = $kefu_id?$kefu_id:0;

        return $kefu_id;
    }

	public function kefu_data($group_id=1)
	{
		//S('kefu',null);
		$kefu = S('kefu');
		
		if(empty($kefu)||!isset($kefu))
		{
			// 初始化设置客服分配数据
            $kefu = array();
			//$wp_weixin_kf = M('weixin_kf','wp_');
			$wp_weixin_kf_group = M('weixin_kf_group','wp_');
			$group_data = $wp_weixin_kf_group->field('id')->select();
			//var_dump($group_data);
			foreach ($group_data as $v) 
			{
				$kefu[$v['id']] = 0;
			}
			//var_dump($kefu);
			S('kefu',$kefu);
		}
		/*else
		{
			//TODO 分配客服ID,并更新缓存数据,将每次的对应的组别ID替换
		}*/

		return $kefu;
	}

	public function do_kefu()
	{
		header('Content-type:text/html;charset=utf-8');
		/*$kefu_data = S('kefu');
		var_dump($kefu_data);*/
		echo '<br/>';
		$openid = 'ogdQy0zMEEyttLkL8trq-pua2X9s';
		//$openid = 'ogdQy0_Rs6wpzsBe2rZil8b26kO0';
		$openid = 'ogdQy03AN7bsAIbFAKPJko5Jojyg';

        $wp_apps_follow = M('apps_follow','wp_');
        //根据openid查询user表uid
        $uid = $wp_apps_follow->where(array('openid'=>$openid))->getField('uid');

		$type = 1;
		if($type == 1)
		{
			//TODO  获取openid

			//$result = $this->user_kefu_initialization($openid);
            $group_id = $type;
            $result = $this->fen_kefu_data($uid,$group_id);
		}
		elseif ($type == 2) 
		{
			//再次关注
			
		}
		elseif ($type == 3) 
		{
			//选择 指定分组客服
			$group_id = 4;
			//$result = $this->user_kefu_initialization($openid,$group_id);
			$result = $this->fen_kefu_data($uid,$group_id);
		}
		echo '<br/>over';
		//var_dump($result);
	}

	public function test_kefu_data()
	{
		//$kefu_data = S('kefu');
		//$kefu_data[3] = 2;
        $kefu_data = S('kefu'); //缓存更新 客服分配情况数据
		var_dump($kefu_data);
        $kefu = $this->kefu_data();
        var_dump($kefu);
	}

	public function user_kefu_initialization($openid,$group_id=1)
	{
		$kefu_data = $this->kefu_data($group_id); //客服分配情况数据
		// 首次关注公众号,分配 给售前
		$wp_weixin_kf = M('weixin_kf','wp_');
		$where['id'] = array('gt',$kefu_data[$group_id]);
		$where['group_id'] = $group_id;
		$where['status'] = 1;
		$k_id = $wp_weixin_kf->where($where)->order('id asc')->getField('id');
		/*echo $wp_weixin_kf->getLastSql();
		echo '<br/>';*/
		if(!$k_id)
		{
			//重新从头开始分配
			$kefu_data[$group_id] = 0;
			$where['id'] = array('gt',$kefu_data[$group_id]);
			$k_id = $wp_weixin_kf->where($where)->order('id asc')->getField('id');
            if(!$k_id)
            {
                echo '该分组未分配客服';
                exit;
            }
		}
       /* echo '<br/>';
		var_dump($k_id);
        echo '<br/>-1111-';
        var_dump($kefu_data);
        echo '-1111-<br/>';*/

		$wp_apps_follow = M('apps_follow','wp_');
		//根据openid查询user表uid
		$uid = $wp_apps_follow->where(array('openid'=>$openid))->getField('uid');
		//echo $wp_apps_follow->getLastSql();exit();
		$wp_user = M('user','wp_');
        $user_info = $wp_user->where(array('uid'=>$uid))->field('kf_id,kf_detail')->find();
        $kf_detail = $user_info['kf_detail'];
        //$user_kf_id = (int)$user_info['kf_id'];

		if($kf_detail)
		{
            echo '<br/>';
            echo '1-11111-';
			$kf_detail = json_decode($kf_detail,true);
           /* var_dump($kf_detail);
            echo '<br/>';
            echo $group_id;
            echo '<br/>';
            echo $kf_detail[$group_id];*/

			if(empty($kf_detail[$group_id]))
			{
                echo '<br/>';
                echo '1-11111-2222';
                echo '<br/>';
                var_dump($kefu_data);
				//分组客服ID不存在,新分配的客服ID
                $kf_detail[$group_id] = (int)$k_id;
                $kefu_data[$group_id] = (int)$k_id;
                echo '<br/>';
                var_dump($kefu_data);

				//客服ID 和 用户 绑定
				$update_data['kf_id'] = (int)$k_id;
				$update_data['kf_detail'] = $kf_detail?json_encode($kf_detail):'';
               /* echo '<br/>';
                var_dump($update_data);*/

				$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);

			}
			else
			{
                echo '<br/>';
                echo '1-11111-3333';
				// 分组客服ID存在,沿用之前分配的客服ID
				$update_data['kf_id'] = (int)$kf_detail[$group_id];
				$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
			}

            //exit;
		}
		else
		{
			//首次分配客服
			$kefu_data[$group_id] = (int)$k_id;
			//S('kefu',$kefu_data); //缓存更新 客服分配情况数据
            echo '<br/>-2222-';
            var_dump($kefu_data);
            echo '-2222-<br/>';
			$update_data = array(
				'kf_id' =>$k_id,
				'kf_detail'=>$kefu_data?json_encode($kefu_data):'',
			);
			$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
		}

        $return_data['code'] = 4001;
        $return_data['message'] = '微信用户绑定客服失败!';
        $return_data['data'] = array();

        if($update_result!==false)
        {
        	$return_data['code'] = 200;
        	$return_data['message'] = '微信用户绑定客服成功!';
            echo '<br/>***';
            var_dump($kefu_data);
            S('kefu',$kefu_data); //缓存更新 客服分配情况数据
            echo '<br/>***';
			var_dump($kefu_data);
        }

        return $return_data;
	}

	public function user_kefu_allot($openid,$group_id=1)
	{
		$wp_apps_follow = M('apps_follow','wp_');
		//根据openid查询user表uid
		$uid = $wp_apps_follow->where(array('openid'=>$openid))->getField('uid');
		//echo $wp_apps_follow->getLastSql();exit();
		//$kefu_data = $this->get_allot_data_log($uid);
		$kefu_data = $this->get_allot_data_log($uid);
        var_dump($kefu_data);echo '<br/>';

		// 首次关注公众号,分配 给售前
		$wp_weixin_kf = M('weixin_kf','wp_');
		$where['id'] = array('gt',$kefu_data[$group_id]);
		$where['group_id'] = $group_id;
		$where['status'] = 1;
		$k_id = $wp_weixin_kf->where($where)->order('id asc')->getField('id');

        $k_id = (!empty($k_id))?$k_id:0;
        echo $wp_weixin_kf->getLastSql().'<br/>';
        echo '客服ID:'.$k_id.'<br/>';
        var_dump($k_id);echo '<br/>';
	
		if(!$k_id)
		{
			/*$kefu_data[$group_id] = 0;
			$where['id'] = array('gt',$kefu_data[$group_id]);*/

            $where['id'] = array('gt',0);
			$k_id = $wp_weixin_kf->where($where)->order('id asc')->getField('id');
            $k_id = (!empty($k_id))?$k_id:0;
            echo $wp_weixin_kf->getLastSql().'<br/>';
            echo '客服ID:'.$k_id.'<br/>';
            var_dump($k_id);echo '<br/>';
            if(!$k_id)
            {
                echo '该分组未分配客服';
                exit;
            }
		}
        //exit;
		$wp_user = M('user','wp_');
        $user_info = $wp_user->where(array('uid'=>$uid))->field('kf_id,kf_detail')->find();
        $kf_detail = $user_info['kf_detail'];

		if($kf_detail)
		{
            echo '<br/>';
            echo '1-11111-';
			$kf_detail = json_decode($kf_detail,true);

			if(empty($kf_detail[$group_id]))
			{
                echo '<br/>';
                echo '1-11111-2222';
                echo '<br/>';
                var_dump($kefu_data);
				//分组客服ID不存在,新分配的客服ID
                $kf_detail[$group_id] = (int)$k_id;
                $kefu_data[$group_id] = (int)$k_id;
                echo '<br/>';
                var_dump($kefu_data);

				//客服ID 和 用户 绑定
				$update_data['kf_id'] = (int)$k_id;
				$update_data['kf_detail'] = $kf_detail?json_encode($kf_detail):'';
               /* echo '<br/>';
                var_dump($update_data);*/

				$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
			}
			else
			{
                echo '<br/>';
                echo '1-11111-3333';
				// 分组客服ID存在,沿用之前分配的客服ID
                //$kefu_data[$group_id] = (int)$kf_detail[$group_id];
				$update_data['kf_id'] = (int)$kf_detail[$group_id];
				$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
			}
		}
		else
		{
			//首次分配客服
			$kefu_data[$group_id] = (int)$k_id;
			//S('kefu',$kefu_data); //缓存更新 客服分配情况数据
            echo '<br/>-2222-';
            var_dump($kefu_data);
            echo '-2222-<br/>';
			$update_data = array(
				'kf_id' =>$k_id,
				'kf_detail'=>$kefu_data?json_encode($kefu_data):'',
			);
			$update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
		}

        $return_data['code'] = 4001;
        $return_data['message'] = '微信用户绑定客服失败!';
        $return_data['data'] = array();

        if($update_result!==false)
        {
        	$return_data['code'] = 200;
        	$return_data['message'] = '微信用户绑定客服成功!';
            echo '<br/>***';
            var_dump($kefu_data);
            $allot_result = $this->allot_data_log($uid,$group_id,json_encode($kefu_data));
            echo '<br/>***';
			var_dump($kefu_data);
        }

        return $return_data;
	}

    public function fen_kefu_data($uid,$group_id=1)
    {
        // 事务标志
        $return_status = 1;

        //先检查用户是否初次分配客服
        $wp_user = M('user','wp_');
        $user_info = $wp_user->where(array('uid'=>$uid))->field('kf_id,kf_detail')->find();
        echo '<br/>';
        var_dump($user_info);
        $kf_detail = $user_info['kf_detail']?$user_info['kf_detail']:'';


        if( !empty($kf_detail) )
        {
            $kf_detail = $kf_detail?json_decode($kf_detail,true):'';
            echo '<br/>';
            var_dump($kf_detail);
            if( empty($kf_detail[$group_id]) )
            {
                echo '<br/>';
                echo '用户客服ID=0';

                $kefu_data = $this->get_db_kefu_data();
                $user_kefu_id = $this->give_kefu_id($group_id,$kefu_data[$group_id]);
                echo '<br/>';
                echo '<pre>';
                var_dump($kefu_data);
                echo '<br/>';
                var_dump($user_kefu_id);
                echo '</pre>';

                if( empty($user_kefu_id) )
                {
                    $user_kefu_id = $this->give_kefu_id($group_id,0);
                    if( empty($user_kefu_id) )
                    {
                        $return_data['code'] = 4001;
                        $return_data['message'] = '分组'.$group_id.'的客服未设置,请先配置对应客服';
                        return $return_data;
                    }
                }

                //分组客服ID不存在,新分配的客服ID
                $kf_detail[$group_id] = (int)$user_kefu_id;
                $kefu_data[$group_id] = (int)$user_kefu_id;

                //客服ID 和 用户 绑定
                $update_data['kf_id'] = (int)$user_kefu_id;
                $update_data['kf_detail'] = $kf_detail?json_encode($kf_detail):'';

                echo '<br/>';
                echo '<pre>';
                var_dump($kefu_data);
                echo '<br/>';
                var_dump($user_kefu_id);
                echo '<br/>';
                var_dump($kf_detail);
                echo '</pre>';
                //exit;

                //更新 客服总分配 记录
                $kefu_update_result = $this->update_db_kefu_data($kefu_data);
                $kefu_update_result!==false&&$return_status?$return_status=1:$return_status=0;

                //更新 用户个人客服分配记录
                $update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
                $update_result!==false&&$return_status?$return_status=1:$return_status=0;

            }
            else
            {
                echo '<br/>';
                echo '沿用之前分配的客服ID';
                // 分组客服ID存在,沿用之前分配的客服ID
                $update_data['kf_id'] = (int)$kf_detail[$group_id];
                $update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
                $update_result!==false&&$return_status?$return_status=1:$return_status=0;

                echo '<br/>';
                echo '<pre>';
                var_dump($kf_detail);
                echo '</pre>';
            }

        }
        else
        {
            echo '<br/>';
            echo '初次分配客服';
            //初次分配客服,默认给售前
            //$user_kefu_data[$group_id] = (int)$k_id;
            $kefu_data = $this->get_db_kefu_data(); // 用户 客服初始化
            $user_kefu_id = $this->give_kefu_id($group_id,$kefu_data[$group_id]);

            echo '<br/>';
            echo '<pre>';
            var_dump($kefu_data);
            echo '<br/>';
            var_dump($user_kefu_id);
            echo '</pre>';

            if( empty($user_kefu_id) )
            {
                $user_kefu_id = $this->give_kefu_id($group_id,0);
                if( empty($user_kefu_id) )
                {
                    $return_data['code'] = 4001;
                    $return_data['message'] = '分组'.$group_id.'的客服未设置,请先配置对应客服';
                    return $return_data;
                }
            }

            foreach($kefu_data as $k=>$v)
            {
                if($group_id == $k)
                {
                    $kefu_data[$group_id] = (int)$user_kefu_id;
                }
                else
                {
                    $kefu_data[$k] = 0;
                }
            }


            //更新 客服总分配 记录
            $kefu_update_result = $this->update_db_kefu_data($kefu_data);
            $kefu_update_result!==false&&$return_status?$return_status=1:$return_status=0;

            $update_data = array(
                'kf_id' =>(int)$user_kefu_id,
                'kf_detail'=>$kefu_data?json_encode($kefu_data):'',
            );
            //更新 用户个人客服分配记录
            $update_result = $wp_user->where(array('uid'=>$uid))->save($update_data);
            $update_result!==false&&$return_status?$return_status=1:$return_status=0;

            echo '<br/>';
            echo '<pre>';
            var_dump($kefu_data);
            echo '<br/>';
            var_dump($user_kefu_id);
            echo '</pre>';

        }

        $return_data['code'] = 4002;
        $return_data['message'] = '微信用户绑定客服失败!';
        $return_data['data'] = array();

        if($return_status)
        {
            $return_data['code'] = 200;
            $return_data['message'] = '微信用户绑定客服成功!';
        }

        //return $return_data;
        var_dump($return_data);

    }

    public function give_kefu_id($group_id,$kf_id)
    {
        $wp_weixin_kf = M('weixin_kf','wp_');
        $where['id'] = array('gt',$kf_id);
        $where['group_id'] = $group_id;
        $where['status'] = 1;
        $k_id = $wp_weixin_kf->where($where)->order('id asc')->getField('id');
        $k_id = $k_id?$k_id:0;

        return $k_id;
    }

    public function get_db_kefu_data($group_id=0)
    {
        $wp_weixin_kf_allot_log = M('weixin_kf_allot_log','wp_');
        $allot_log = $wp_weixin_kf_allot_log->getField('allot_log');
        $allot_log = $allot_log?json_decode($allot_log,true):array();

        var_dump($allot_log);
        if( empty($allot_log) )
        {
            echo 1111;
        }
        else
        {
            echo 2222;
        }

        if($group_id)
        {
            // 用户初始化
            $wp_weixin_kf_group = M('weixin_kf_group','wp_');
            $group_data = $wp_weixin_kf_group->field('id')->select();
            foreach ($group_data as $v)
            {
                $allot_log[$v['id']] = 0;
            }
        }
        else
        {
            if( empty($allot_log) )
            {
                // 初始化设置客服分配数据
                $wp_weixin_kf_group = M('weixin_kf_group','wp_');
                $group_data = $wp_weixin_kf_group->field('id')->select();
                foreach ($group_data as $v)
                {
                    $allot_log[$v['id']] = 0;
                }

                $add_data = array(
                    'allot_log'=>json_encode($allot_log),
                    'update_time'=>time(),
                );

                $add_id = $wp_weixin_kf_allot_log->add($add_data); //客服分配 初始化

            }
        }

        return $allot_log;
    }

    public function update_db_kefu_data($kefu_data)
    {
        $wp_weixin_kf_allot_log = M('weixin_kf_allot_log','wp_');
        $update_data = array(
            'allot_log'=>json_encode($kefu_data),
            'update_time'=>time(),
        );
        $update_result = $wp_weixin_kf_allot_log->where(array('id'=>1))->save($update_data);
        $result = $update_result!==false?1:0;

        return $result;
    }

    public function all_kefu_cache_data($kf_id = 0)
    {
        //客服数据缓存
        /*$config = array(
            'type'=>'redis',
            'host'=>'127.0.0.1',
            'port'=>'6379',
            'prefix'=>'weiphp_',
            'expire'=>0
        );

        S($config);*/
        $kefu_20180427_1 = array(
            100=>1,
            1001=>1,
            132=>1,
            125=>array('chat_total'=>1,'fans_total'=>1,'reply_total'=>1)
        );

        $all_kefu_data = S('all_kefu');


    }

    public function change_kf_statistics_data($kf_id=1,$type=1)
    {
        $now_date = date('Ymd');
    	$kf_statistics_tbl = M('kf_statistics_tbl','wp_');

        $kefu_id = $kf_statistics_tbl->where(array('kf_id'=>$kf_id,'statistics_time'=>$now_date))->getField('kf_id');

        $return_status = 1;

        if(!$kefu_id)
        {
            // 不存在 新增操作
            $add_data = array(
                'kf_id'=>$kf_id,
                'statistics_time'=>date('Ymd'),
                'session_total'=>0, //会话总数
                'chat_total'=>0, //当天新增聊天总数
                'fans_total'=>0, //当天新增粉丝总数
                'reply_total'=>0, //客服回复总数
                'update_time'=>time(),
            );
            $add_id = $kf_statistics_tbl->add($add_data);
            $add_id&&$return_status?$return_status=1:$return_status=0;
        }

        //存在 更新操作
        $sql = $this->get_kefu_sql($kf_id,$type);
        $exe_result = $kf_statistics_tbl->execute($sql);
        $exe_result!=false&&$return_status?$return_status=1:$return_status=0;

        /*$update_data = array(
            'statistics_time'=>date('Ymd'),
            'session_total'=>1, //会话总数
            'chat_total'=>1, //当天新增聊天总数
            'fans_total'=>1, //当天新增粉丝总数
            'reply_total'=>1, //客服回复总数
            'update_time'=>time(),
        );

        $update_result = $kf_statistics_tbl->where(array('kf_id'=>$kf_id))->save($update_data);
        $update_result!=false&&$return_status?$return_status=1:$return_status=0;*/

        $return_data['code'] = 4001;
        $return_data['message'] = '网络异常,客服每天数据记录失败!';
        $return_data['data'] = array();

        if($return_status)
        {
            $return_data['code'] = 200;
            $return_data['message'] = '客服每天数据记录成功!';
        }

        //return $return_data;
        var_dump($return_data);

    }

    public function get_kefu_sql($kf_id,$type=1,$fans_id=0)
    {
        $now_date = date('Ymd');
        $now_time = time();
        switch($type)
        {
            case 1:
                //新增聊天总数
                $sql = " UPDATE `wp_kf_statistics_tbl` SET chat_total=chat_total+1,`update_time`=$now_time WHERE `kf_id` = $kf_id AND `statistics_time`= $now_date ";
                break;
            case 2:
                //粉丝数
                $sql = " UPDATE `wp_kf_statistics_tbl` SET fans_total=fans_total+1,`update_time`=$now_time WHERE `kf_id` = $kf_id AND `statistics_time`= $now_date ";
                break;
            case 3:
                // 回复总数
                $sql = " UPDATE `wp_kf_statistics_tbl` SET reply_total=reply_total+1,`update_time`=$now_time WHERE `kf_id` = $kf_id AND `statistics_time`= $now_date ";
                break;
            case 4:
                // 会话总数(按人)
                $kefu_data = $this->get_kefu_cache($kf_id);
                $session_total = count($kefu_data);
                $sql = " UPDATE `wp_kf_statistics_tbl` SET session_total=$session_total,`update_time`=$now_time WHERE `kf_id` = $kf_id AND `statistics_time`= $now_date ";
                break;
        }

        return $sql;
    }

    public function set_kefu_cache($kf_id,$user_id)
    {
        $user_id = rand(1,11);
        $kf_id = (int)$kf_id;
        $user_id = (int)$user_id;
        $now_date = date('Ymd');
        $key_name = 'redis_'.$now_date.'_'.$kf_id;
        //S($key_name,null);
        $kefu_array = S($key_name);
        $kefu_array = is_array($kefu_array)?$kefu_array:array();
        $kefu_array[$user_id] = $kf_id;
        var_dump($key_name);
        var_dump($kefu_array);
        $kefu_cache = S($key_name,$kefu_array);

    }

    public function get_kefu_cache($kf_id)
    {
        $kf_id = (int)$kf_id;
        $now_date = date('Ymd');
        $key_name = 'redis_'.$now_date.'_'.$kf_id;
        $kefu_cache = S($key_name);
        return $kefu_cache;
    }

    public function test_my_data()
    {
        $type = rand(1,4);
        $this->change_kf_statistics_data($kf_id=1,$type);
    }

    /**
     * @Author: sunew
     * @Date: 2018-5-2
     * @Description: 客服 每日数据统计 列表
     */
    public function apps_follow_subscribe_list()
    {
        $input_data = I();
        //$where['s.type_id'] = 1;
        $begin_time = $input_data['begin_time']?date('Ymd',strtotime('2018-4-1')):'';
        $end_time = $input_data['end_time']?date('Ymd',strtotime($input_data['end_time'])):date('Ymd');

        if($begin_time && $end_time)
        {
            $where['s.statistics_time'] = array(array('egt',$begin_time),array('elt',$end_time),'AND');
        }
        elseif($begin_time && !$end_time)
        {
            $where['s.statistics_time'] = array('egt',$begin_time);
        }
        else
        {
            $where['s.statistics_time'] = array('elt',$end_time);
        }

        //$kf_statistics_tbl = D ( 'Common/KfStatistics' );
        $kf_statistics_tbl = M('kf_statistics_tbl','wp_');

        $total = $kf_statistics_tbl->alias('s')->where($where)->count('s.id');
        var_dump($total);
        $page_no = $input_data['page_no']?$input_data['page_no']:1;
        $page_num = 20; //默认20条

        $fields = " s.id,s.kf_id,s.statistics_time,s.session_total,s.chat_total,s.fans_total,s.fans_total,w.name,w.weixin_id,w.emp_id,w.group_id,w.weixin_qcode ";
        $join = " LEFT JOIN wp_weixin_kf w on w.id = s.kf_id ";
        $order = " s.statistics_time desc ";

        $list = $kf_statistics_tbl->alias('s')->where($where)->field($fields)->join($join)->page($page_no,$page_num)->order($order)->select();
        echo M()->getLastSql();

        var_dump($list);

    }

    public function test_digui()
    {
    	$id = 1;
    	$code = 'sss';
    	/*$id = rand(1,4);
    	echo $id;*/
    	/*$arr = array(1,2,3);
        dump(in_array($id,$arr));*/
    	$res = $this->digui($id,$code);
    	var_dump($res);
    }

    public function digui($id=1,$user_code='',$in=0)
    {
    	$code = $id;
    	$arr = array(1,2,4);
    	echo $in;echo '<br/>';
        if(in_array($id, $arr))
        {
            $in+=1;
			if($in>10)
			{
				$code = '';
			}
			else
			{
				$id = rand(1,4);
				$code = $this->digui($id,$user_code,$in);			
			}
        }
        return $code;
    }

	/**
	 * 字符串转化为数组，支持中英文逗号空格
	 *
	 * @param     string  $strs  带有特殊符号的字符串
	 * @return    int
	 */
	function strsToArray($strs) {
		$result = array();
		$array = array();
		$strs = str_replace('，', ',', $strs);
		$strs = str_replace(' ', ',', $strs);
		$array = explode(',', $strs);
		foreach ($array as $key => $value) {
			if ('' != ($value = trim($value))) {
				$result[] = $value;
			}
		}
		return $result;
	}

	//替代eval实现字符串转数组
	function array_encode($string){  
	    //删除空格  
	    $string=str_replace(' ','',$string);  
	    //容错空数组  
	    if($string=='array()'){  
	        return array();  
	    }  
	    //数组格式容错  
	    if(substr($string,0,6)=='array('&&$string[strlen($string)-1]==')'){  
	        $Array=array();  
	        $array=substr($string,6,strlen($string)-7);  
	        //容错，不要分隔小数组中的逗号  
	        if(strpos($array,'array(')===0){  
	            $array=str_replace(",array",",#array",$array);  
	            $array=explode(',#',$array);  
	        }else{  
	            $array=explode(',',$array);  
	        }  
	        if(strpos($array[0],'array(')===0){  
	            //小数组  
	            foreach($array as $key => &$value){  
	                $Array[]=array_encode($value);  
	            }  
	        }elseif(strpos($array[0],'=>')){  
	            //键值对数组  
	            foreach($array as $key => &$value){  
	                //容错，不要分隔小数组中的键值符号  
	                if(strpos($value,'array(')>0){  
	                    $value=str_replace("=>array","=>#array",$value);  
	                    $value=explode('=>#',$value);  
	                }else{  
	                    $value=explode('=>',$value);  
	                }  
	                if(!(strpos($value[1],'\'')===0||strpos($value[1],'"')===0||strpos($value[1],'array')===0)){  
	                    if(strpos($value[1],'.')>0){  
	                        //双精度  
	                        $Array[preg_replace("/'|\"/","",$value[0])]=(double)$value[1];  
	                    }else{  
	                        //整形  
	                        $Array[preg_replace("/'|\"/","",$value[0])]=(int)$value[1];  
	                    }  
	                }elseif(strpos($value[1],'array')===0){  
	                    //小数组  
	                    $Array[preg_replace("/'|\"/","",$value[0])]=array_encode($value[1]);  
	                }else{  
	                    //字符串  
	                    $Array[preg_replace("/'|\"/","",$value[0])]=preg_replace("/'|\"/","",$value[1]);  
	                }  
	            }  
	        }else{  
	            //索引数组  
	            foreach($array as $key =>&$value){  
	                if(!(strpos($value,'\'')===0||strpos($value,'"')===0||strpos($value,'array')===0)){  
	                    if(strpos($value,'.')>0){  
	                        //双精度  
	                        $Array[]=(double)$value;  
	                    }else{  
	                        //整形  
	                        $Array[]=(int)$value;  
	                    }  
	                }elseif(strpos($value,'array')===0){  
	                    //小数组  
	                    $Array[]=array_encode($value);  
	                }else{  
	                    //字符串  
	                    $Array[]=preg_replace("/'|\"/","",$value);  
	                }  
	            }  
	        }  
	        return $Array;  
	    }else{  
	        return false;  
	    }  
	}  

	public function test_copy()
	{
		$url="http://sports.qq.com/photo/?pgv_ref=aio"; 
    	//file_get_contents() 函数把整个文件读入一个字符串中 
    	$str=file_get_contents($url); 
		$rule = "/<img([^>]*)\s*src=('|\")([^'\"]+)('|\")/i";
		preg_match_all($rule, $str, $matches);
		if($matches)
		{
			foreach ($matches[3] as $k => $v) 
			{
				$num = rand(1,1000);
				//echo $v;exit();
				$v1 = "http://mat1.gtimg.com/news/2013pic/picLogo.png";
				$v = "C:/Users/Administrator/Desktop/pic/51e.jpg";
				//myCopyFunc($v,"D:/tmp/001.PNG");  
				saveImage($v1);
				exit();
			}
		}
	}

    public function test_null()
    {
		header('Content-type:text/html;charset=utf-8');
        echo __ROOT__.'/index/index';
        /*$file = 'people.txt';
        $person = "John Smith";
        file_put_contents($file, $person.PHP_EOL,FILE_APPEND);exit();*/
        $model = M('user');
        $where['name|age'] = 1;
        //$where['id'] = array('IN','1,null');
        //$where['id'] = array('like',array('%thinkphp%','%tp'),'OR');
        //$where['name'] = 1;
        $where['email'] = '1234';
        //$where['_logic'] = 'or';
        $model->where($where)->select();
        $test_month = '2018-6';
        var_dump($days = date('t', strtotime('2018-6-1')));
        var_dump(date('j'));
        echo date('Ym01', strtotime($test_month));
   		echo "<br/>";
  		echo date('Ymt', strtotime($test_month));
  		echo "<br/>";
        echo $model->getLastSql();exit();

        $ext = 'gif|jpg|jpeg|bmp|png';
		$str = '<p><img title="绿色软件" alt="绿色软件" onload="ResizeImage(this,860)" src="http://www.jb51.net /data/soft_img/2010091101619.jpg" /></p><p><img title="绿色软件" alt="绿色软件" onload="ResizeImage(this,860)" src="http://www.jb51.net /data/soft_img/2010091029938.jpg" /></p><p><img title="绿色软件" alt="绿色软件" onload="ResizeImage(this,860)" src="http://www.jb51.net /data/soft_img/2010092839019.jpg" /></p>';
		//$rule = '/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i';
		$url="http://sports.qq.com/photo/?pgv_ref=aio"; 
    	//file_get_contents() 函数把整个文件读入一个字符串中 
    	$str=file_get_contents($url); 
		$rule = "/<img([^>]*)\s*src=('|\")([^'\"]+)('|\")/i";
		preg_match_all($rule, $str, $matches);
		dump($matches);
        exit();
        $str = "array ( 'options' => '演员|1 歌手|2 导演|3 制作|4 监制|5 作曲|6 小说|7 企业|8 主持|9 教师|10 摄影|11 播音|12 学生|13 童星|14 网红|15 模特|16 车模|16 声优|18 情色|19 其他|20', 'boxtype' => 'select', 'fieldtype' => 'varchar', 'minnumber' => '1', 'width' => '110', 'size' => '1', 'defaultvalue' => '', 'outputtype' => '0', 'filtertype' => '1', )";
        //eval("\$arr = ".$str.';');
        $arr = $this->array_encode($str);
        var_dump($arr);
        var_dump($arr['options']);
        exit();
        //var_dump(trim($arr['options']));
        //$array = $this->strsToArray($str);
        //var_dump($array);
        $a = explode("\s", $arr['options']);
        var_dump(ord('\s'));
        //var_dump($a = str_split($arr['options'], 9.5));
        foreach ($a as $v) 
        {
        	$str_arr = explode("|", $v);
        	/*$array['id'] = $str_arr[1];
        	$array['name'] = $str_arr[0];
        	$new_arr[] = $array;*/
        	$new_arr[$str_arr[1]] = $str_arr[0];
        }
        //var_dump($new_arr);
        exit();

        $a1 = array('{$categorydir}','{$catdir}','{$catid}','{$page}');
        $a2 = array($category_dir='',$category['catdir']='a',$catid=1,$page=1);
        $urlrule = 'a/index.html';
        $urls = str_replace($a1,$a2,$urlrule);
    	dump($urls);
    	exit();

        $arr = array(0=>"");
        echo count($tags_data);
        if(count($tags_data)>0)
        {
    		echo count($tags_data);
        }
        else
        {
    		echo 'error';
        }
        exit();
         //今天起止时间
        
        $time = time();
        $now_start_time = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $now_end_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        //echo $now_start_time;
        $ts_user = M('user');
        $update_data = array(
        	'age'=>22,
        	'last_login_time'=>time(),
        	'login_error_num'=>rand(1,4),
    	);
    	$update_field_array = array_keys($update_data);
    	$update_field = implode(',', $update_field_array);
    	var_dump($update_field);
    	$update_result = $ts_user->field('login_error_num')->where(array('id'=>14))->save($update_data);
    	var_dump($update_result);echo '<br/>';

    	$list = $ts_user->alias('u')->where(array('u.id'=>array('EGT',1)))->getField('u.name,u.id,u.email');
    	dump($list);

        /*echo __APP__;
        echo APP_PATH;
        echo U('Home/Index/index','','html');*/
        /*$begin_date = 20180410;
        $begin_time = strtotime(20180410);
        $end_date = 20180422;
        $end_time = strtotime(20180422);
        $day = 60*60*24;
        echo $end_time;
        echo date('Y-m-d',$end_time);
        //exit;
        
        for($i=$begin_time;$i<=$end_time;$i=$i+$day)
        {
            echo '<br/>';
            //echo $begin_time;echo '<br/>';
            echo date('Y-m-d',$i);
            echo '<br/>';
            echo $i;echo '<br/>';
            //exit;
        }*/
        //exit;
        
        //

		$wp_weixin_kf_allot_log = M('weixin_kf_allot_data_log','wp_');

		$wp_weixin_kf_allot_log->order('add_time desc,id desc')->find();
		
		echo M()->getLastSql();

        $str = "null";
        $str = null;
        if(empty($str))
        {
            echo 1111;
        }
        else
        {
            echo 2222;
        }
        exit;
    }

    public function add_customer($name='my_name',$phone_first=18902685513,$region_id=1382644,$street='北京路1号')
    {
    	/*$name = $name;
    	$phone_first = $phone_first;
    	$region_id = $region_id;
    	$street = $street;*/
    	if(!$name || !$phone_first || !$region_id || !$street )
    	{
			$return_data['code']=4002;
            $return_data['message'] = "数据异常,请检查数据格式";
            $this->ajaxReturn($return_data);
    	}
    	$code = 'CU'.date('YmdHis').rand(100,999);
    	$source = 97; // 在线商城待处理
    	$mobile_type = 1;
    	$dark_yellow_muscle = 1;
    	$dry_muscle = 1;
    	$cdate = date('Y-m-d H:i:s');
    	//TODO 客服销售员ID待处理
    	/*$user = $this->user;
		$emp_id = $user['obj_id'];*/
		$emp_id = 100;

		$customer_model = M('customer',null);
		//简单的校验 该号码是否已存在
		$find_data = $customer_model->where(array('phone_first'=>$phone_first))->find();
		if($find_data)
		{
			$return_data['code']=4001;
            $return_data['message'] = "该号码用户已注册成为客户,不能重复添加";
            $this->ajaxReturn($return_data);
		}

		$add_data = array(
			'name'=>$name,
			'code'=>$code,
			'source'=>$source,
			'mobile_type'=>$mobile_type,
			'region_id'=>$region_id,
			'street'=>$street,
			'dark_yellow_muscle'=>$dark_yellow_muscle,
			'dry_muscle'=>$dry_muscle,
			'cdate'=>$cdate,
			'emp_id'=>$emp_id,
		);

		$add_id = $customer_model->add($add_data);
		if($add_id)
		{
			$return_data['code']=200;
            $return_data['message'] = "添加客户成功";
		}
		else
		{
			$return_data['code']=4010;
            $return_data['message'] = "网络异常,添加客户失败";
		}
		$this->ajaxReturn($return_data);

    }

    public function check_mall_user_is_exist($phone=13458689725,$customer_id=1990)
    {
    	$mall_user_tbl = M('user_tbl','mall_');
    	$mall_user_data =$mall_user_tbl->where(array('cellphone'=>$phone))->field('id,nickname,customer_id,status')->find();
    	//var_dump($mall_user_data);
    	if(!$mall_user_data['customer_id'])
    	{
    		$update_data = array(
    			'customer_id'=>$customer_id,
			);
			$update_result = $mall_user_tbl->where(array('cellphone'=>$phone))->save($update_data);
			if($update_result!==false)
			{
				//初始化积分表
				$m_id = $mall_user_data['id'];
				$member_result = $this->initialize_member_point($m_id);
				if($member_result['code']==200)
				{
					$return_data['code']=200;
            		$return_data['message'] = "会员绑定客户ID成功";
				}
				else
				{
					$return_data['code']=4020;
            		$return_data['message'] = "网络异常,会员初始化失败,绑定了客户ID失败!";
				}

			}
			else
			{
				$return_data['code']=4010;
            	$return_data['message'] = "网络异常,会员绑定了客户ID失败!!";
			}
    	}
    	else
    	{
			//$return_data['code']=4001;
			$return_data['code']=200;
            $return_data['message'] = "客户已注册成为会员,并已经绑定了客户ID";
    	}

    	$this->ajaxReturn($return_data);

    }

    public function initialize_member_point($m_id)
    {
    	$member_level_and_point_tbl = M('member_level_and_point_tbl','mall_');

    	$id = $member_level_and_point_tbl->where(array('user_id'=>$m_id))->getField('id');

    	if(!$id)
    	{
			$add_data = array(
	            'user_id'=>$m_id,
	            'update_time'=>time(),
	            'level'=>1,
	            'plus_point_total'=>0,
	            'minus_point_total'=>0,
	            'bill_finish_num'=>0,
	            'bill_pay_price'=>0,
	        );
	    	$add_id = $member_level_and_point_tbl->add($add_data);
	    	if($add_id)
			{
				$return_data['code']=200;
	        	$return_data['message'] = "会员积分表初始化成功";
			}
			else
			{
				$return_data['code']=4010;
	        	$return_data['message'] = "网络异常,会员积分表初始化失败";
			}
			return $return_data;
    	}

    	$return_data['code']=200;
    	$return_data['message'] = "会员积分表已存在,无需再次初始化";
    	return $return_data;
    	
    }

    function xing_zuo($date=20180901) 
    {
    	if(!strtotime($date))
    	{
			echo 'error';exit();
    	}
    	$month=date('m',strtotime($date));
    	$day=date('d',strtotime($date));
		// 检查参数有效性 
		if ($month < 1 || $month > 12 || $day < 1 || $day > 31) return false;

		// 星座名称以及开始日期
		$constellations = array(
			array( "20" => "宝瓶座"),
			array( "19" => "双鱼座"),
			array( "21" => "白羊座"),
			array( "20" => "金牛座"),
			array( "21" => "双子座"),
			array( "22" => "巨蟹座"),
			array( "23" => "狮子座"),
			array( "23" => "处女座"),
			array( "23" => "天秤座"),
			array( "24" => "天蝎座"),
			array( "22" => "射手座"),
			array( "22" => "摩羯座")
		);

		list($constellation_start, $constellation_name) = each($constellations[(int)$month-1]);

		if ($day < $constellation_start) list($constellation_start, $constellation_name) = each($constellations[($month -2 < 0) ? $month = 11: $month -= 2]);

		var_dump($constellation_name);
	}

	public function xing_zuo2($date=201802281)
	{
		$arr = array(
			1=> array('id'=>1,'name' =>'水瓶座' ,'begin_date'=>'01-20','end_date'=>'02-18' ),
			2=> array('id'=>2,'name' =>'双鱼座' ,'begin_date'=>'02-19','end_date'=>'03-20' ),
			3=> array('id'=>3,'name' =>'白羊座' ,'begin_date'=>'03-21','end_date'=>'04-19' ),
			4=> array('id'=>4,'name' =>'金牛座' ,'begin_date'=>'04-20','end_date'=>'05-20' ),
			5=> array('id'=>5,'name' =>'双子座' ,'begin_date'=>'05-21','end_date'=>'06-21' ),
			6=> array('id'=>6,'name' =>'巨蟹座' ,'begin_date'=>'06-22','end_date'=>'07-22' ),
			7=> array('id'=>7,'name' =>'狮子座' ,'begin_date'=>'07-23','end_date'=>'08-22' ),
			8=> array('id'=>8,'name' =>'处女座' ,'begin_date'=>'08-23','end_date'=>'09-22' ),
			9=> array('id'=>9,'name' =>'天秤座' ,'begin_date'=>'09-23','end_date'=>'10-23' ),
			10=> array('id'=>10,'name' =>'天蝎座' ,'begin_date'=>'10-24','end_date'=>'11-22' ),
			11=> array('id'=>11,'name' =>'射手座' ,'begin_date'=>'11-23','end_date'=>'12-21' ),
			12=> array('id'=>12,'name' =>'魔羯座' ,'begin_date'=>'12-22','end_date'=>'01-19' ),
		);
		if(!strtotime($date))
    	{
			var_dump($arr);exit();
    	}
    	$month = date('m',strtotime($date));
    	$day = date('d',strtotime($date));
    	$s = $month.'.'.$day;
		if($s>=3.21 && $s<=4.19){
		    echo '你是白羊座';
		}elseif($s>=4.20 && $s<=5.20){
		    echo '你是金牛座';
		}elseif($s>=5.21 && $s<=6.21){
		    echo '你是双子座';
		}elseif($s>=6.22 && $s<=7.22){
		    echo '你是巨蟹座';
		}elseif($s>=7.23 && $s<=8.22){
		    echo '你是狮子座';
		}elseif($s>=8.23 && $s<=9.22){
		    echo '你是处女座';
		}elseif($s>=9.23 && $s<=10.23){
		    echo '你是天秤座';
		}elseif($s>=10.24 && $s<=11.22){
		    echo '你是天蝎座';
		}elseif($s>=11.23 && $s<=12.21){
		    echo '你是射手座';
		}elseif($s>=12.22 && $s<=1.19){
		    echo '你是魔羯座';
		}elseif($s>=1.20 && $s<=2.18){
		    echo '你是水平座';
		}elseif($s>=2.19 && $s<=3.20){
		    echo '你是双鱼座';
		}
	}


	public function test_try()
	{
	
		$add_data = array('name' =>'beijing' ,'age'=>23,'nickname'=>'haha' );
		$update_data = array('age' =>age+1 ,'name'=>1111 );	
		//$add_result = $model->add($add_data)->buildSql();
		//echo $model->getLastSql();
		//exit();
		try
        {
        	$model = M('user','ts_');
        	//$add_result = $model->add($add_data);
        	$model->where(array('id'=>1))->save($update_data);
        	echo $model->getLastSql();exit(); 
            //$add_result = $model->add($add_data);
            if($add_result['code']==200)
            {
                $return_data['code'] = 200;
                $return_data['message'] = 'yes';
                $return_data['data'] = $add_data;
            }
            else
            {
                $return_data['code'] = 4005;
                $return_data['message'] = 'add_error';
            }

            $this->ajaxReturn($return_data);
        }
        catch (Exception $e)
        {
        	throw new Exception();
            $error = $e->getMessage();
            $return_data['code'] = 4022;
            $return_data['message'] = '网络原因,异常数据,终止执行！';
            $data['error'] = $error;
            $return_data['data'] = $data;
            //$this->ajaxReturn($return_data);
            var_dump($error);exit();
            return false;
            exit();
        }

	}

	public function base64EncodeImage ($image_file = 'D:/001.jpg') 
	{
		$image_file = 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1532086150922&di=e8ad99200feee2b17d02106d19e8b2e9&imgtype=0&src=http%3A%2F%2Fimg.mp.sohu.com%2Fupload%2F20170717%2Fa750210a4d8c49b3967b2e5d8b69f961.png';
		$base64_image = '';
		$image_info = getimagesize($image_file);
		$image_data = fread(fopen($image_file, 'r'), filesize($image_file));
		$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
		var_dump($base64_image);
	}

	public function imgToBase64($img_file='')
    {
    	 $img_file='https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1532086150922&di=e8ad99200feee2b17d02106d19e8b2e9&imgtype=0&src=http%3A%2F%2Fimg.mp.sohu.com%2Fupload%2F20170717%2Fa750210a4d8c49b3967b2e5d8b69f961.png';
    	 $img_file='D:/001.jpg';
         $img_base64 = '';
         if (file_exists($img_file))
         {
             $app_img_file = $img_file; // 图片路径
             $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

             //echo '<pre>' . print_r($img_info, true) . '</pre><br>';
             $fp = fopen($app_img_file, "r"); // 图片是否可读权限

             if ($fp) {
                           $filesize = filesize($app_img_file);
                 $content = fread($fp, $filesize);
                 $file_content = chunk_split(base64_encode($content)); // base64编码
                 switch ($img_info[2])
                 {   //判读图片类型
                     case 1: $img_type = "gif";
                                           break;
                     case 2: $img_type = "jpg";
                                           break;
                     case 3: $img_type = "png";
                                           break;
                 }

                 $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码

             }
             fclose($fp);
         }

         //return $img_base64; //返回图片的base64
         var_dump($img_base64); //返回图片的base64
    }

    public function jisuan()
    {
		$url = 'http://beauty.yanmiban.com/hufu/';
		var_dump(strpos($url, 'http://'));exit();
		$value = array(
		    array('name'=>'liming','age'=>20),
		    array('name'=>'小狼','age'=>22),
		    array('name'=>'小狼','age'=>10),
		);
    	$arr= array_sort($value,'age','desc');
		var_dump($arr);
		$arr= array_merge($arr);
		var_dump($arr);
		exit();

    	$n = 8*3;
    	$hp = 70*3;
    	$cat = 5*3;
    	$black = 8;

    	$sum = $cat*$n + $black*$n;
    	//var_dump($sum);
    	$a = 'http://m.yanmiban.com/beauty/skin/';
    	$a1 = 'skin';
    	var_dump(strrpos($a, $a1));
    	var_dump(substr($a, 0,29));
    	echo __FILE__;echo '<br>';
		echo dirname(__FILE__);
    }

}