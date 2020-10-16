<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>编辑文章</title>
		<link href="/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<!--<script src="/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/Public/Js/my.js" type="text/javascript"></script>-->
		<link href="/Public/Css/article/umeditor.css" type="text/css" rel="stylesheet">
		<script type="text/javascript" src="/Public/Js/article/jquery.min.js"></script>
		<script type="text/javascript" charset="utf-8" src="/Public/Js/article/umeditor.config.js"></script>
		<script type="text/javascript" charset="utf-8" src="/Public/Js/article/umeditor.min.js"></script>
		<script type="text/javascript" src="/Public/Js/article/zh-cn.js"></script>
		
		
			
	</head>
<style>

.error{
	color:red;
}
</style>
</head>
<span>编辑文章</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
<form class="cmxform" id="signupForm" method="post" action="<?php echo U('Article/edit');?>">
  <table class="tb_add">
	<tr>
		<td width="500px" align="right"> <label for="username">所属分类</label></td>
		<td>
			<?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input type="text" name="father" value=<?php echo ($vo["name"]); ?>  readonly="readonly">
				<input type="hidden" name="pid" value=<?php echo ($vo["id"]); ?>><?php endforeach; endif; else: echo "" ;endif; ?>
		</td>
	</tr>
	<tr>
		<td align="right" width='30%'><label for="title">文章标题:</label></td>
		<td><input id="title" name="title" type="text"  style="width:115px;" value="<?php echo ($result['title']); ?>"></td>
	</tr>
	<tr>
		<td align="right">文章内容:</td>
		<td>
			<textarea type="text/plain" id="myEditor" style="width:500px;height:240px;resize: none;" name="content">
				<?php echo ($result['content']); ?>
			</textarea>
		</td>
	</tr>
	<tr>
		<td  align="right">作者:</td>
		<td><input type='text' name="writer" value="<?php echo ($result['writer']); ?>"></td>
	</tr>
	<tr>
		<td colspan=2 align="center">
			<input type="hidden" name="wyid" value="<?php echo ($result['id']); ?>">
			<input class="submit" type="submit" name="submit" value="编辑">
		</td>
	</tr>

   </table>
</form>
	
	<script type="text/javascript">
		  //实例化编辑器
		 var um = UM.getEditor('myEditor');

	</script>	
</body>
</html>