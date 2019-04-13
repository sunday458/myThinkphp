<?php
namespace Weixin\Controller;
use Think\Controller;
use Think\Cache\Driver\Redis; //引入redis

/**
* 秒杀demo 控制器
*/
class RedisController extends Controller
{
    protected $objRedis;
    public $timeout = 3;

    private $minNum = 60; //单个用户每分访问数(令牌桶算法)
    private $dayNum = 25; //单个用户每天总的访问量(令牌桶算法)
	
    /**
     * @desc 设置redis实例
     *
     * @param obj object | redis实例
     */
    public function __construct()
    {
        parent::__construct();
        $redis = new Redis();
        $options['host'] = C('REDIS_HOST'); 
        $options['port'] = C('REDIS_PORT'); 
        $this->objRedis = $redis->connect('Redis',$options);
    }

    public function index()
    {
        $redis = $this->objRedis;
        /*$redis = new Redis();
        //$redis->connect('127.0.0.1',6379);
        $options['host'] = C('REDIS_HOST'); // ip  xxx.xxx.xxx.xxx 
        $options['port'] = C('REDIS_PORT'); // 端口号 6379
 
        $redis->connect('Redis',$options);*/
        $redis_name = 'miaosha';

        echo '<pre>';
        echo $redis->lLen($redis_name);
        echo '<br/>';
        var_dump($redis->lrange($redis_name,0,-1)); 
        $redis->lpop($redis_name);
		//echo 111111;exit();
	}

    public function miaosha()
    {
        $redis = new Redis();
        //$redis->connect('127.0.0.1',6379);
        $options['host'] = C('REDIS_HOST'); // ip  xxx.xxx.xxx.xxx 
        $options['port'] = C('REDIS_PORT'); // 端口号 6379
 
        $redis->connect('Redis',$options);
        $redis_name = 'miaosha';
        //$redis_name = 'SecKill';

        //$u_id = I('u_id');
        $u_id = rand(1000,2000);
        $num = 10; //秒杀人员上限
        //当前人数少于 上限人员时，继续参与秒杀
        //$lock_name = $this->getLockCacheKey($redis_name); //锁名
        $lock_name =$redis_name; //锁名
        $this->lock($lock_name);//加锁
        //$redis->watch($redis_name); //监听,当监听值发生变化则失败
        if($redis->lLen($redis_name) < $num)
        {
            //$redis->multi($redis_name); //事务开始
            //$newExpire = $this->getLock($redis_name);
            $redis->rPush($redis_name,$u_id.'%'.microtime());            
            //$this->releaseLock($redis_name,$newExpire);                
            //$redis->exec($redis_name); //事务提交
            
            echo $u_id.' 秒杀成功<br/>';
        }
        else
        {
            echo '秒杀活动结束<br/>';
        }
        $this->unlock($lock_name);//解锁
        $redis->close();
    }

    public function add_data()
    {
        $redis = new Redis();
        //$redis->connect('127.0.0.1',6379);
        $options['host'] = C('REDIS_HOST'); // ip  xxx.xxx.xxx.xxx 
        $options['port'] = C('REDIS_PORT'); // 端口号 6379
 
        $redis->connect('Redis',$options);
        $redis_name = 'miaosha';

        $redis_queue_tbl = M('redis_queue',null);

        //死循环执行
        //while (1) {
        while ($redis->lLen($redis_name) > 0) {
            //从队列最左侧取出数据，是否存在
            $user = $redis->lpop($redis_name);
            if(!$user||$user=='null')
            {
                sleep(1);
                continue;
            }

            //切割出时间 u_id
            $user_arr = explode('%', $user);
            $add_data = array(
                'u_id'=>$user_arr[0],
                'time_stamp'=>$user_arr[1],
                'ip' =>get_client_ip() , 
                'add_time' =>time() , 
            );

            //保存入库
            $add_result = $redis_queue_tbl->add($add_data);

            if(!$add_result)
            {
                //数据插入异常，回滚机制
                $redis->rPush($redis_name,$user);
            }

            sleep(1);
        }
        
        //关闭释放redis
        $redis->close();

    }

    public function test($value='')
    {
        //高并发 高压力测试
    }

    public function test_add()
    {
        $redis_queue_tbl = M('redisQueue',NULL);
        //$product_tbl = M('product',NULL);
        //$find_data = $product_tbl->where('id=43')->find();
        //var_dump($find_data);
        echo '<pre>';
        //var_dump($redis_queue_tbl);
        $add_data = array(
            'u_id' =>1000 , 
            'time_stamp' =>microtime() , 
            'ip' =>get_client_ip() , 
            'add_time' =>time() , 
        );
        $add_result = $redis_queue_tbl->add($add_data);
        echo $redis_queue_tbl->getLastSql();
        var_dump($add_result);
    }

    public function testRedis()
    {
 
        $redis = new Redis();
 
        $options = array();
        $options['host'] = C('REDIS_HOST'); // ip  xxx.xxx.xxx.xxx 
        $options['port'] = C('REDIS_PORT'); // 端口号 6379
 
        $redis->connect('Redis',$options);
        $redis->set('test2','hello world2!');
        echo $redis->get("test2");
    }

    /**
     * @desc 获取锁键名
     */
    public function getLockCacheKey($key)
    {
        return "lock_{$key}";
    }

    /**
     * @desc 获取锁
     *
     * @param key string | 要上锁的键名
     * @param timeout int | 上锁时间
     */
    public function getLock($key, $timeout = NULL)
    {
        $timeout = $timeout ? $timeout : $this->timeout;
        $lockCacheKey = $this->getLockCacheKey($key);
        $expireAt = time() + $timeout;
        $isGet = (bool)$this->objRedis->setnx($lockCacheKey, $expireAt);
        if ($isGet) 
        {
            return $expireAt;
        }

        while (1) 
        {
            usleep(10);
            $time = time();
            $oldExpire = $this->objRedis->get($lockCacheKey);
            if ($oldExpire >= $time) 
            {
                continue;
            }
            $newExpire = $time + $timeout;
            $expireAt = $this->objRedis->getset($lockCacheKey, $newExpire);
            if ($oldExpire != $expireAt) 
            {
                continue;
            }
            $isGet = $newExpire;
            break;
        }
        return $isGet;
    }

    /**
     * @desc 释放锁
     *
     * @param key string | 加锁的字段
     * @param newExpire int | 加锁的截止时间
     *
     * @return bool | 是否释放成功
     */
    public function releaseLock($key, $newExpire)
    {
        $lockCacheKey = $this->getLockCacheKey($key);
        if ($newExpire >= time()) 
        {
            return $this->objRedis->del($lockCacheKey);
        }
        return true;
    }

    public function lock($key, $expire = 60)
    {
        if(!$key) {
            return false;
        }
        $redis = $this->objRedis;
        do {
        if($acquired = ($redis->setnx("Lock:{$key}", time()))) { 
                // 如果redis不存在，则成功
                //$redis->expire($key, $expire);//锁的过期时间,防止执行异常锁一直在
                $redis->expire("Lock:{$key}", $expire);//锁的过期时间,防止执行异常锁一直在
                break;
            }

            usleep($expire);

        } while (true);

        return true;
    }

    //释放锁(解锁)
    //public function release($key)
    public function unlock($key)
    {
        if(!$key) {
            return false;
        }
        $redis = $this->objRedis;
        $redis->del("Lock:{$key}");
        $redis->close();
    }

    /*借助文件排他锁，在处理下单请求的时候，用flock锁定一个文件，如果锁定失败说明有其他订单正在处理，此时要么等待要么直接提示用户"服务器繁忙"。
     *文件锁也分为排它锁（LOCK_EX）和共享(LOCK_SH)锁两种
     */
    public function FunctionName1($value='')
    {
       //阻塞(等待)模式
        $fp = fopen("lock.txt", "w+");
        if(flock($fp,LOCK_EX)) { // 锁定当前指针
            //..处理订单
            flock($fp,LOCK_UN);
        }
        fclose($fp);
    }

    public function FunctionName2($value='')
    {
        //非阻塞模式

        $fp = fopen("lock.txt", "w+");
        if(flock($fp,LOCK_EX | LOCK_NB)) {
            //..处理订单
            flock($fp,LOCK_UN);
        } else {
          echo "系统繁忙，请稍后再试";
        }
        fclose($fp);
    }

    /**
     * 问题：当一个脚本被一个客户端访问都正常，但当多个客户端同时并发访问时，这个脚本的结果会出现不正确，这个问题需要使用锁机制来解决。在我们这个网站中需要用到锁的地方就是高并发下定单时减少商品库存量时。
    *这就要涉及到锁机制，在同一个段只允许一个人访问，防止数据数显错误！！
    *锁有两种：一种是mysql的表锁，另一个是php文件锁
    *首先介绍的是：mysql的锁
    *语法是
    *加锁：LOCK TABLE 表名 READ|WRITE,表名2 READ|WRITE……
    *解锁 ： UNLOCK TABLES (注意这里tables，解锁多个表)
    *解释一下：
    *1.READ读锁（共享锁）：如果以这种方式锁定表，那么在锁定的过程中所有客户端只有读这张表 
    *2.WRITE：写锁（排它锁）：如果以这种方式锁定表，那么只有锁定这个表的客户端可以操作这张表，其他客户端只能操作个表直到锁释放为止。
     */ 
    
    public function test_api()
    {
        $params = I();
        $u_id = rand(1000,1200);
        $result = $this->minLimit();
        /*var_dump($result);
        if(!$result['status']){
            $this->ajaxReturn($result);
        }*/
        echo 'success'.date('Y-m-d H:i:s');
    }
    

    /**
     * @param      string  $uid    The uid
     * 令牌桶算法 PHP + Redis
     */
    public function minLimit($uid=12000)
    {
        $minNumKey = $uid . '_minNum';
        $dayNumKey = $uid . '_dayNum';
        $resMin    = $this->getRedis($minNumKey, $this->minNum, 60);
        $resDay    = $this->getRedis($dayNumKey, $this->dayNum, 86400);

        echo '<pre>';    
        $redis = new Redis();
        $min_data = $redis->get($minNumKey);
        $day_data = $redis->get($dayNumKey);
        /*var_dump($redis->del($minNumKey));
        var_dump($redis->del($dayNumKey));*/
        var_dump($min_data,$day_data);

        //并发超次数或者总次数上限返回提示
        if (!$resMin['status'] || !$resDay['status']) {
            //exit($resMin['msg'] . $resDay['msg']);
            var_dump($resMin['msg'] . $resDay['msg']);
        }
        var_dump($resMin,$resDay);
    }

    /**
     * @param      <type>   $key      The key
     * @param      integer  $initNum  The initialize number
     * @param      integer  $expire   The expire
     * @return     array    The redis.
     * @desc  令牌桶算法实现
     * 代码要点：
        *1：首先定义规则
        *单个用户每分钟访问次数（$minNum），单个用户每天总的访问次数（$dayNum），接口总的访问次数等不同的规则
        *2：计算速率
        *该代码示例以秒为最小的时间单位，速率=访问次数/时间（$initNum / $expire）
        *3：每次访问后补充的令牌个数计算方式
        *获取上次访问的时间即上次存入令牌的时间，计算当前时刻与上次访问的时间差乘以速率就是此次需要补充的令牌个数，注意补充令牌后总的令牌个数不能大于初始化的令牌个数，以补充数和初始化数的最小值为准
        *4：程序流程
        *第一次访问时初始化令牌个数（$minNum），存入Redis同时将当前的时间戳存入以便计算下次需要补充的令牌个数。第二次访问时获取剩余的令牌个数，并添加本次应该补充的令牌个数，补充后如何令牌数>0则当前访问是有效的可以访问，否则令牌使用完毕不可访问。先补充令牌再判断令牌是否>0的原因是由于还有速率这个概念即如果上次剩余的令牌为0但是本次应该补充的令牌>1那么本次依然可以访问。
        *5：针对并发的处理
        *使用Redis的乐观锁机制
     */
    public function getRedis($key, $initNum, $expire)
    {
        $nowtime  = time();
        $result   = ['status' => true, 'msg' => ''];
        $redis = new Redis();
        $redis->watch($key); // Watch 命令用于监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断(配合redis事务使用)
        $limitVal = $redis->get($key); //有坑？TP自带的redis 返回结果都是数组形式，自动转化？
        if ($limitVal) {
            //$limitVal = json_decode($limitVal, true);
            $newNum   = min($initNum, ($limitVal['num'] - 1) + (($initNum / $expire) * ($nowtime - $limitVal['time'])));
            if ($newNum > 0) {
                $redisVal = json_encode(['num' => $newNum, 'time' => time()]);
            } else {
                return ['status' => false, 'msg' => '当前时刻令牌消耗完！'];
            }
        } else {
            $redisVal = json_encode(['num' => $initNum, 'time' => time()]);
        }

        $redis->multi();  // Multi 命令用于标记一个事务块的开始
        $redis->set($key, $redisVal);
        $rob_result = $redis->exec(); // EXEC 命令原子性(atomic)执行，redis的事务机制是很简单的，不存在回滚，配合watch 使用
        if (!$rob_result) {
            $result = ['status' => false, 'msg' => '访问频次过多！'];
        }
        return $result;
    }



}