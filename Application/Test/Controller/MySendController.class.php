<?php
namespace Test\Controller;
use Think\Controller;

class MySendController extends Controller{
  function index(){
    echo U('MySend/send');
    $this->display('send_email');
  }

  function send(){
    $mailtoname   = $_POST['mailtoname'];
    $mailtomail   = $_POST['mailtomail'];
    $mailcc       = $_POST['mailcc'];
    $mailbcc      = $_POST['mailbcc'];
    $mailpriority = $_POST['mailpriority'];
    $mailsubject  = $_POST['mailsubject'];
    $mailbody     = $_POST['mailbody'];
    if (empty ( $mailtoname) || empty ( $mailtomail) ) {
      die ( "Recipient is blank! ") ;
    }else{
      $to = $mailtoname . " <" . $mailtomail . ">" ;
    }
    if ( empty ( $mailsubject) ) {
     $mailsubject=" ";
    }
    if (($mailpriority>0) && ($mailpriority<6)) {
      $mailheader = "X-Priority: ". $mailpriority ."\n";
    }
    $mailheader.= "From: " . "Sales Team <sales@yourdomain.com>\n";
    $mailheader.= "X-Sender: " . "support@yourdomain.com\n";
    $mailheader.= "Return-Path: " . "support@yourdomain.com\n";
    if (!empty($mailcc)) {
     $mailheader.= "Cc: " . $mailcc ."\n";
    }
    if (!empty($mailbcc)) {
     $mailheader.= "Bcc: " . $mailbcc ."\n";
    }
    if (empty($mailbody)) {
     $mailbody=" ";
    }
    $result = mail ($to, $mailsubject, $mailbody, $mailheader);
    echo "<center><b>Mail sent to ". "$to". "<br />";
    echo $mailsubject. "<br />";
    echo $mailbody. "<br />";
    echo $mailheader. "<br />";
    if ($result) {
      echo "<p><b>Email sent successfully!</b></p>";
    }else{
      echo "<p><b>Email could not be sent. </b></p>";
    }
  }

  function test_send(){
    $mail = new \Test\Org\SendEmail();
    $mail->setServer("smtp.mxhichina.com", "no-reply@yueus.com", "T855hjpt84d1");
    $receiver = array('sunew@yueus.com','wuzf@yueus.com');
    $cc_receiver = array('1207120376@qq.com');
    $mail->setFrom('no-reply@yueus.com');
    foreach ($receiver as $k => $v) {
       $mail->setReceiver($v);
    }
    foreach ($cc_receiver as $key => $val) {
       $mail->setCc($val);
    }
    //$mail->setReceiver("XXXXX");
   // $mail->setReceiver("XXXXX");
    //$mail->setCc("XXXXXX");
    //$mail->setCc("XXXXXX");
    //$mail->setBcc("XXXXX");
   // $mail->setBcc("XXXXX");
    $mail->setMailInfo("test", "<b>test</b>");
    $mail->sendMail();
  }

  function testTime()
  {
        $nowTime   = time();  //当前时间
        $send_time = $nowTime - 3600*24;
        $chart_begin_time = strtotime(date('Y-m-d 09:12:00'))-3600*24;
        $chart_end_time   = strtotime(date('Y-m-d 09:12:59'))-3600*24;

        if ($send_time >= $chart_begin_time && $send_time <=$chart_end_time ) {
          echo 'yes';
        }else
        {
          echo 'no';
        }

        if(date('H',$nowTime) == '10'){
           echo '时间正确';
        }else
        {
          echo '没到时间';
        }
        $user_start_time = date('Y-m-d',$nowTime-3600*24);
        $user_end_time = date('Y-m-d',$nowTime-3600*24);
         $BetweenTwoDays = round(($user_end_time-$user_start_time)/3600/24) ; // 相差几天
        echo $BetweenTwoDays.'--';
        if($BetweenTwoDays <= 0) $BetweenTwoDays=1;
        echo $BetweenTwoDays;
  }

  function test_csv(){
    /**
 * 生成默认以逗号分隔的CSV文件
 * 解决：内容中包含逗号(,)、双引号("")
 * @author zf
 * @version 2012-11-14
 */
      header("Content-Type: application/vnd.ms-excel; charset=GB2312");
      header("Content-Disposition: attachment;filename=CSV数据.csv ");
       
      $rs = array(
          array('aa', "I'm li lei", '"boy"', '￥122,300.00'),
          array('cc', 'I\'m han mei', '"gile"', '￥122,500.00'),
      );
      $arr = array(array('id','name','yy','money'));
      foreach ($rs as  $value) {
        $arr[] = $value;
      }
      $str = '';
      foreach ($arr as $row) {
          $str_arr = array();
          foreach ($row as $column) {
              $str_arr[] = '"' . str_replace('"', '""', $column) . '"';
          }
          $str.=implode(',', $str_arr) . PHP_EOL;
      }
      echo $str;
  }

  function get_test_arr(){
    $arr = array();
    for ($i=0; $i < 3; $i++) { 
      $arr[] = array(1,2,3,4);
    }

    var_dump($arr);
  }

}
  