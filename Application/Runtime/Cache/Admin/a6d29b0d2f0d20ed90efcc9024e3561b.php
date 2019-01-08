<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>编辑用户</title>
		<link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<script src="/TP/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/my.js" type="text/javascript"></script>
		
		
			
	</head>
<style>
.error{
	color:red;
}
</style>
</head>
<a href="JavaScript:history.go(-1)">返回</a>
<body>
<form class="cmxform" id="signupForm" method="post" action="<?php echo U('User/edit');?>">
  <table class="tb_add">
	<tr>
		<td width="200px" align="right"> <label for="username">用户名:</label></td>
		<td> <input id="username" name="username" type="text" value="<?php echo ($data["username"]); ?>"></td>
	</tr>
	<tr>
		<td align="right"><label for="password">密码:</label></td>
		<td><input id="password" name="password" type="password" value="<?php echo ($data["password"]); ?>"></td>
	</tr>
	<tr>
		<td align="right"><label for="confirm_password" >确认密码:</label></td>
		<td> <input id="confirm_password" name="confirm_password" type="password" value="<?php echo ($data["password"]); ?>"></td>
	</tr>
	<tr>
		<td align="right"> <label for="addr">地址:</label></td>
		<td> <input id="addr" name="addr" type="text" value="<?php echo ($data["addr"]); ?>"></td>
	</tr>
	<tr>
		<td align="right"><label for="phone">手机:</label></td>
		<td><input id="phone" name="phone" type="text" value="<?php echo ($data["phone"]); ?>"></td>
	</tr>
	<tr>
		<td colspan=2 align="center">
			<input class="submit" type="submit" name="submit" value="提交">
			<input  type="hidden" name="userId" value="<?php echo ($data["id"]); ?>">
		
		</td>
	</tr>

   </table>
</form>
</body>
</html>