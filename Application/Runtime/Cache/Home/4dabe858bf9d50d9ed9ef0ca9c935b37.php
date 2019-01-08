<?php if (!defined('THINK_PATH')) exit();?>
<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <link href="/TP/Public/Css/home/message.css" rel="stylesheet" type="text/css" />
  <title>留言板</title>
 </head>
 <body>
	<div class="divall">
		<p class="message message1">PEAKEDNESS</p>
		<p class="message message2">留言板</p>
		<HR color="#B3B3B3">
		<div class="table">
			<form action="<?php echo U('Message/index');?>" method="post">
				<table align="center">
					<tr>
						<td colspan=2>
							<input type="text" name="company" value="您的供职机构"style="width:362px;">
						</td>
						
					</tr>
					<tr>
						<td><input type="text" name="name" value="您的姓名"  style="width:168px;"></td>
						<td><input type="text" name="position" value="您的职位"  style="width:168px;"></td>
					</tr>
					<tr>
						<td colspan=2><input type="text" name="phone" value="1321321321321" style="width:362px;"></td>
					
					</tr>
					<tr>
						<td colspan=2>
							<textarea name="question" style="width:360px;height:180px;resize:none;border-radius:10px;">请输入您的问题</textarea>
						</td>
						
					</tr>
					<tr>
						<td><input type="submit" name="submit" value="提交" style="cursor:pointer;border-radius:3px" class="last"></td>
						<td><input type="reset" value="重置" style="cursor:pointer;border-radius:3px" class="last"></td>
					</tr>
				</table>
			</form>
		</div>
		<div class="question">
			<p class="cation">大家都关心:</p>
			<div class="question1">
			<?php if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><ul>
					<li><?php echo ($vo["question"]); ?></li>
				</ul><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
			<p>最新话题:</p>
			<div class="question1">
				<?php if(is_array($resulter)): $i = 0; $__LIST__ = $resulter;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><ul>
					<li><?php echo ($vo["question"]); ?></li>
				</ul><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
		</div>
	</div>
 </body>
</html>