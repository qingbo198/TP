<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>新增文章分类</title>
		<link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<!--<script src="/TP/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/my.js" type="text/javascript"></script>-->
		
		
			
	</head>
<style>
.error{
	color:red;
}
</style>
</head>
<span>新增文章分类</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
<form class="cmxform" id="signupForm" method="post" action="<?php echo U('ArticleCategory/add');?>">
  <table class="tb_add">
	<tr>
		<td width="200px" align="right"> <label for="username">所属分类</label></td>
		<td>
			<select  name="select" style="width:120px;">
				<option value="0">顶级分类</option>
				<?php if(is_array($list)): foreach($list as $key=>$v): if($check == $v['id']): ?><option  value="<?php echo ($v["id"]); ?>" selected><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option>
				<?php else: ?>
					<option  value="<?php echo ($v["id"]); ?>"><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option><?php endif; endforeach; endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="name">分类名称:</label></td>
		<td><input id="name" name="name" type="text"  style="width:115px;" value=""></td>
	</tr>
	
	
	<tr>
		<td colspan=2 align="center"><input class="submit" type="submit" name="submit" value="新增"></td>
	</tr>

   </table>
</form>
</body>
</html>