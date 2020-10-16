<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>商品分类编辑</title>
		<link href="/Public/Css/style.css" rel="stylesheet" type="text/css" />
		<!--<script src="/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="/Public/Js/my.js" type="text/javascript"></script>-->
		
	
<style>
.error{
	color:red;
}
</style>
</head>
<span>商品分类编辑</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
  <table class="tb_add">
	<tr>
		<td width="200px" align="right"> <label for="username">所属分类</label></td>
		<td>
			<select id="catgory">
				<?php if($list_one['pid'] == 0): ?><option value="0" selected>顶级分类</option><?php endif; ?>
				<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($check == $v['id']): ?><option value="<?php echo ($v["id"]); ?>" selected><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option>
						<?php else: ?>
						<option value="<?php echo ($v["id"]); ?>"><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="name">分类名称:</label></td>
		
		<td>
			<input type="text" id="name" value=<?php echo ($list_one['name']); ?>>
		</td>
	</tr>
	
	
	<tr>
		<td colspan=2 align="center"><input class="submit" type="submit" id="sub" value="编辑"></td>
	</tr>

   </table>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
	$(function(){
	    var sub = $('#sub');
	    sub.click(function(){
			var id = <?php echo ($list_on); ?>;
			var pid = $('#catgory').val();
			var name = $('#name').val();
			$.ajax({
				url:'edit',
				type:'post',
				data:{pid:pid,id:id,name:name},
				dataType:'json',
				success:function(data){
					if(data.status == '1'){
					    layer.msg(data.msg,{icon:1});
					}else{
                        layer.msg(data.msg,{icon:2});
					}
				}
			})

		})

	})
</script>
</body>
</html>