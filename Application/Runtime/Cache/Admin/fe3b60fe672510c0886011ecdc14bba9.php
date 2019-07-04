<?php if (!defined('THINK_PATH')) exit();?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>新闻列表</title>
		<link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
			
	</head>

	<body>
	<span>新闻列表</span>&nbsp;&nbsp;
	<a href="javascript:history.go(-1);">返回>></a>
	
		<div>
			<form>
				<table class="tb">
					<tr>
						<th>ID</th>
						<th width="25%">新闻标题</th>
						<th>作者</th>
						<th>所属分类</th>
						<th>最近更新时间</th>
						<th>操作</th>
					</tr>
					<?php if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
							<td><?php echo ($vo['id']); ?></td>
							<td><?php echo ($vo['title']); ?></td>
							<td><?php echo ($vo['writer']); ?></td>
							<td><?php echo ($vo['name']); ?></td>
							<td><?php echo ($vo['last_time']); ?></td>
							<td>
								<a href="add?id=<?php echo $vo['id'] ?>" class="button_add">新增</a>
								<a href="edit?pid=<?php echo $vo['pid']?>&id=<?php echo $vo['id'] ?>" class="button_edit">编辑</a>
								<a href="del?id=<?php echo $vo['id']?>" class="button_del">删除</a>
							</td>
						</tr><?php endforeach; endif; else: echo "" ;endif; ?>
					

					 
				</table>
				
			</form>
			
		</div>
		
	</body>
</html>