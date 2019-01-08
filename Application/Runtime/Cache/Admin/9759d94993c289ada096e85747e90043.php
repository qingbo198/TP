<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>新增文章</title>
		<link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<!--<script src="/TP/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/TP/Public/Js/my.js" type="text/javascript"></script>-->
		<link href="/TP/Public/Css/article/umeditor.css" type="text/css" rel="stylesheet">
		<script type="text/javascript" src="/TP/Public/Js/article/jquery.min.js"></script>
		<script type="text/javascript" charset="utf-8" src="/TP/Public/Js/article/umeditor.config.js"></script>
		<script type="text/javascript" charset="utf-8" src="/TP/Public/Js/article/umeditor.min.js"></script>
		<script type="text/javascript" src="/TP/Public/Js/article/zh-cn.js"></script>
		
		
			
	</head>
<style>

.error{
	color:red;
}
</style>
</head>
<span>新增文章</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
<form class="cmxform" id="signupForm" method="post" action="<?php echo U('Article/add');?>">
  <table class="tb_add">
	<tr>
		<td width="500px" align="right"> <label for="username">所属分类</label></td>
		<td>
			<select  name="select" style="width:120px;">
				<option value="0">顶级分类</option>
				<?php if(is_array($list)): foreach($list as $key=>$v): ?><option  value="<?php echo ($v["id"]); ?>"><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option><?php endforeach; endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" width='30%'><label for="title">文章标题:</label></td>
		<td><input id="title" name="title" type="text"  style="width:115px;" value=""></td>
	</tr>
	<tr>
		<td align="right">文章内容:</td>
		<td height="245px">
			<textarea type="text/plain" id="myEditor" style="width:500px;height:240px;resize: none;" name="content">

			</textarea>
		</td>
	</tr>
	<tr>
		<td  align="right">作者:</td>
		<td><input type='text' name="writer" value=""></td>
	</tr>
	<tr>
		<td colspan=2 align="center"><input class="submit" type="submit" name="submit" value="新增"></td>
	</tr>

   </table>
</form>
	
	<script type="text/javascript">
		  //实例化编辑器
		 var um = UM.getEditor('myEditor');
		 
		UE.getEditor('myEditor', {
			autoHeightEnabled: false
		})

	</script>	
</body>
</html>