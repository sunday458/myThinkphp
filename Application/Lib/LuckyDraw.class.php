<?php
namespace Lib;

/**
 *  抽奖
 */
class LuckyDraw {
     
    /*
     * 奖项数组
     * 是一个二维数组，记录了所有本次抽奖的奖项信息，
     * 其中id表示中奖等级，prize表示奖品，v表示中奖概率。
     * 注意其中的v必须为整数，你可以将对应的 奖项的v设置成0，即意味着该奖项抽中的几率是0，
     * 数组中v的总和（基数），基数越大越能体现概率的准确性。
     * 本例中v的总和为100，那么平板电脑对应的 中奖概率就是1%，
     * 如果v的总和是10000，那中奖概率就是万分之一了。
     * 
     */
    var $prize_arr = array( 
        '0' => array('id'=>1,'prize'=>'平板电脑','v'=>0), 
        '1' => array('id'=>2,'prize'=>'数码相机','v'=>2), 
        '2' => array('id'=>3,'prize'=>'音箱设备','v'=>10), 
        '3' => array('id'=>4,'prize'=>'4G优盘','v'=>16), 
        '4' => array('id'=>5,'prize'=>'10Q币','v'=>22), 
        '5' => array('id'=>6,'prize'=>'下次没准就能中哦','v'=>50), 
    );  

     //抽奖的开始时间
    var $begin_time="2017-3-30 14:00:00"; //开始时间  0-不限制
    //抽奖的结束时间
    var $stop_time="0";  //结束时间  0-不限制

    public function get_lucky_draw_conf($prize='',$scale=0)
    {
        // 读取中奖的配置信息(数据库、conf文件、缓存、写定)
        $prize = $prize?$prize:array(  //奖品
            'TV'=>3,
            'radio'=>10,
            'maozi'=>20,
        );

        $scale = $scale?$scale:50;  //中奖几率

        return array('prize'=>$prize,'scale'=>$scale);
    }

    /*
     * 经典的概率算法，
     * $proArr是一个预先设置的数组，
     * 假设数组为：array(100,200,300,400)，
     * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内， 
     * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
     * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
     * 这样 筛选到最终，总会有一个数满足要求。
     * 就相当于去一个箱子里摸东西，
     * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
     * 这个算法简单，而且效率非常高，尤其是大数据量的项目中效率非常棒。
     */
    private function get_rand($proArr) { 
        $result = '';  
        //概率数组的总概率精度 
        $proSum = array_sum($proArr);  //计算数组总和
        //概率数组循环 
        foreach ($proArr as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum); 
            /*echo $randNum;echo '<br>';
            echo $proCur;echo '<br>';
            echo $proSum;echo '<br>';*/
            if ($randNum <= $proCur) { 
                $result = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            }       
        } 
        unset ($proArr);  
        return $result; 
    } 

    /*
     * 抽奖入口
     * 每次前端页面的请求，PHP循环奖项设置数组，
     * 通过概率计算函数get_rand获取抽中的奖项id。
     * 将中奖奖品保存在数组$res['yes']中，
     * 而剩下的未中奖的信息保存在$res['no']中，
     */
    public function make()
    {
        $prize_arr=  $this->prize_arr;
        foreach ($prize_arr as $key => $val) { 
            $arr[$val['id']] = $val['v'];  // 奖品号=>对应奖品中奖几率
        } 
        /*var_dump($arr);
        echo '----<br/>';*/
        $rid = $this->get_rand($arr); //根据概率获取奖项id 
        //var_dump($rid);exit();
        $res['yes'] = $prize_arr[$rid-1]['prize']; //中奖项 
        unset($prize_arr[$rid-1]); //将中奖项从数组中剔除，剩下未中奖项 
        shuffle($prize_arr); //打乱数组顺序 
        for($i=0;$i<count($prize_arr);$i++){ 
            $pr[] = $prize_arr[$i]['prize']; 
        } 
        $res['no'] = $pr; 
        return $res;
    }

    public function test_make()
    {
        $qq_no=  trim($_POST['qq_no']);
        import('ORG.Util.Input');
        $qq_no=Input::getVar($qq_no);
          
        if(empty($qq_no)){
            $this->ajaxReturn(1, '请正确填写QQ号码！');
            exit;
        }
          
        if(!empty($this->begin_time) && time()<strtotime($this->begin_time)){
            $this->ajaxReturn(1, '抽奖还没有开始，开始时间为：'.$this->begin_time);
            exit;
        }
          
        if(!empty($this->stop_time) && time()>strtotime($this->stop_time)){
            $this->ajaxReturn(1, '本次抽奖已经结束，结束时间为：'.$this->stop_time);
            exit;
        }
          
         //获取奖项信息数组，来源于私有成员
        $prize_arr=  $this->prize_arr;
          
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        //$rid中奖的序列号码
        $rid = $this->get_rand($arr); //根据概率获取奖项id
          
        $str = $prize_arr[$rid - 1]['prize']; //中奖项 
          
        $Choujiang=M('Choujiang');
          
            //从数据库中获取特定QQ号已经参加抽奖的次数，如果大于等于3则提示次数用完
        if($Choujiang->where("qq_no='{$qq_no}'")->count()>=3){
            $str='您3次抽奖机会已经用完！';
            $rid=0;
            //从数据库中获取特定奖项序号的次数，大于等于设置的最大次数则提示奖品被抽完，如果需要一直中最后一个纪念奖，则修改该处即可
        }elseif ($Choujiang->where("rid={$rid}")->count()>=$prize_arr[$rid-1]['num']) {
            $str='很抱歉，您所抽中的奖项已经中完！';
            $rid=0;
        }
        //生成一个用户抽奖的数据，用来记录到数据库
        $data=array(
            'rid'=>$rid,
            'pop'=>$str,
            'qq_no'=>$qq_no,
            'input_time'=>time()
        );
        //将用户抽奖信息数组写入数据库
          
        $Choujiang->add($data);
        unset($Choujiang);
          
         //ajax返回信息
        $this->ajaxReturn(1, $str);
    }
         
}