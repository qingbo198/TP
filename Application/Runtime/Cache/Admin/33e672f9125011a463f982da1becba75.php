<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>编辑文章分类</title>
		<link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<!--<script src="/TP/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/my.js" type="text/javascript"></script>-->
		
	
<style>
.error{
	color:red;
}
</style>
</head>
<span>编辑文章分类</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
<form class="cmxform" id="signupForm" method="post" action="edit">
  <table class="tb_add">
	<tr>
		<td width="200px" align="right"> <label for="username">所属分类</label></td>
		<td>
		<?php if(!empty($result)): if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input type="text" name="father" value=<?php echo ($vo["name"]); ?>  readonly="readonly">
			<input type="hidden" name="fid" value=<?php echo ($vo["id"]); ?>><?php endforeach; endif; else: echo "" ;endif; endif; ?>
		<?php if(empty($result)): ?><input type="text" name="father" value="顶级分类"  readonly="readonly"><?php endif; ?>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="name">分类名称:</label></td>
		
		<td>
				<input type="text" name="name" value=<?php echo ($category['name']); ?>>
				<input type="hidden" name="id" value=<?php echo ($category['id']); ?>>
		</td>
	</tr>
	
	
	<tr>
		<td colspan=2 align="center"><input class="submit" type="submit" name="submit" value="编辑"></td>
	</tr>

   </table>
</form>
</body>
</html>