<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<?php if(is_array($arr)): $i = 0; $__LIST__ = $arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; echo ($vo["id"]); ?> <br/>
	<?php echo ($vo['id']); ?> <br/>
	<?php echo ($vo["id"]); ?> <br/><?php endforeach; endif; else: echo "" ;endif; ?>
<?php echo ($data["create_date"]); ?> <br/>
<?php echo ($data['create_date']); ?> <br/>
<?php echo ($data["create_date"]); ?><br/>

<?php echo ($create_date); ?><br/>

<?php echo (date('y-m-d',$data["create_date"])); ?><br/>

<?php echo date('Y-m-d',$create_date);?><br/>
<form action="<?php echo U('User/check_data');?>" method="post"> 
<!-- <form action="<?php echo U('User/login');?>" method="post"> -->
    <input type="text" name="name" value="" placeholder="姓名" />
    <input type="text" name="age" value="" placeholder="年龄" />
    <input type="text" name="phone" value="" placeholder="联系方式" />
    <input type="submit" value="enter"/>
</form>
</body>
</html>