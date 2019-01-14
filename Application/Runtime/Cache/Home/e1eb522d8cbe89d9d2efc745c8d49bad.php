<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>购物车</title>
</head>
<style>
    .list_tab{width:90%;border:solid #ccc 1px;margin:5px auto;border-collapse: collapse;text-align: center;}
    .list_tab th{border:solid #ccc 1px;}
    .list_tab td{border:solid #ccc 1px;}
    a{text-decoration: none;cursor: pointer;color:black;}
    .add_pro{margin-left: 95px;border: solid #707070 1px;border-radius: 3px;}
    .list{margin-top: 20px;}
    button{cursor: pointer;}
    .total,.total .total_div,.total button{float:right;}
    .pages a,.pages span {
        display:inline-block;
        padding:2px 5px;
        margin:0 1px;
        border:1px solid #f0f0f0;
        -webkit-border-radius:3px;
        -moz-border-radius:3px;
        border-radius:3px;
    }
    .pages a,.pages li {
        display:inline-block;
        list-style: none;
        text-decoration:none; color:grey;
        margin-top: 45px;
    }
    .pages a.first,.pages a.prev,.pages a.next,.pages a.end{
        margin:0;
    }
    .pages a:hover{
        border-color:#50A8E6;
    }
    .pages span.current{
        background:#50A8E6;
        color:#FFF;
        font-weight:700;
        border-color:#50A8E6;
    }
</style>
<body>
<!--引入头部文件-->
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>welcome to my webpage</title>
		<style>

			.iconfont{
				font-family:"iconfont" !important;
				font-size:18px;font-style:normal;
				-webkit-font-smoothing: antialiased;
				-webkit-text-stroke-width: 0.2px;
				-moz-osx-font-smoothing: grayscale;
				color:yellow;
			}
			@font-face {
				font-family: 'iconfont';  /* project id 987132 */
				src: url('//at.alicdn.com/t/font_987132_lnc4kzm5xua.eot');
				src: url('//at.alicdn.com/t/font_987132_lnc4kzm5xua.eot?#iefix') format('embedded-opentype'),
				url('//at.alicdn.com/t/font_987132_lnc4kzm5xua.woff') format('woff'),
				url('//at.alicdn.com/t/font_987132_lnc4kzm5xua.ttf') format('truetype'),
				url('//at.alicdn.com/t/font_987132_lnc4kzm5xua.svg#iconfont') format('svg');
			}


			body {font-size: 13px;font-family: "Microsoft Yahei";color: #333;}
			*{margin: 0px;}
			.header{height: 40px;width: 100%;line-height: 40px;background: #333333;}
			.header a{text-decoration: none;color:#b0b0b0;}
			.user_login span{float:right;padding-right: 7px;}
			.nav li{float:left;margin-left: 7px;list-style: none;}
			li a:hover{color:white;cursor: pointer;}
			.nav{margin-left: 15.5%;}
			.nav span{margin-left:6px;color: #b0b0b0;}
		</style>
	</head>
	<body>
		<!--		  <a href="<?php echo U('Message/index');?>">留言板</a><a>登录</a><a>注册</a>-->
		<div class="header">
			<ul class="nav">
				<li><a>小米商城</a><span>|</span></li>
				<li><a>MIUI</a><span>|</span></li>
				<li><a>loT</a><span>|</span></li>
				<li><a>云服务</a><span>|</span></li>
				<li><a>金融</a><span>|</span></li>
				<li><a>有品</a><span>|</span></li>
				<li><a>小米开放平台</a><span>|</span></li>
				<li><a>政企服务</a><span>|</span></li>
				<li><a>下载app</a><span>|</span></li>
				<li><a>Select Region</a></li>
			</ul>
			<div class="user_login">
				<?php
 echo $_SESSION['user'] ? "
						<span>
							<span>
								<a  href='/TP/Home/Index/shop' class='iconfont'>&#xe60b;
									<span class='shop' style='display: block;margin-top: -2px;margin-left: 5px;font-size: 15px;'>(".($_SESSION['shop']['total'] ? $_SESSION['shop']['total'] : 0) .")</span>
								</a>
							</span>
							<span>
								<a>您好&nbsp&nbsp".$_SESSION['user']."</a>
								<a href='/TP/Home/User/quit'>&nbsp退出&nbsp</a>
							</span>":"

							<span>
								<a  href='/TP/Home/Index/shop' class='iconfont'>&#xe60b;
									<span class='shop' style='display: block;margin-top: -2px;margin-left: 5px;font-size: 15px;'>(".($_SESSION['shop']['total'] ? $_SESSION['shop']['total'] : 0) .")</span>
								</a>
							</span>
							<span>
								<a href='/TP/Home/User/login'>登录</a>
							</span>
							<span>
								<a href='/TP/Home/User/register'>注册</a>
							</span>"; ?>
			</div>
		</div>
				

		
	</body>
</html>
<div class="list">
    <a class="add_pro">购物车列表</a>
    <table class="list_tab">
        <?php if($list == ''): ?><tr>
                <!--<th>ID</th>-->
                <th>购物车暂无商品</th>
            </tr>
        <?php else: ?>
            <tr>
                <!--<th>ID</th>-->
                <th>商品名称</th>
                <th>价格</th>
                <th>商品图片</th>
                <th>数量</th>
                <th>小计</th>
                <th>操作</th>
            </tr>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                    <!--<td><?php echo ($vo["id"]); ?></td>-->
                    <?php if($vo["name"] != ''): ?><td><?php echo ($vo["name"]); ?></td>
                        <td><?php echo ($vo["price"]); ?></td>
                        <td>
                            <img src="<?php echo ($vo['img'][0]); ?>" width="70px;" />
                        </td>
                        <td><button class="reduce" data-id="<?php echo ($vo["id"]); ?>">-</button>&nbsp&nbsp<input type="text" value="<?php echo ($vo["num"]); ?>" style="width: 30px;text-align: center;">&nbsp&nbsp<button class="plus" data-id="<?php echo ($vo["id"]); ?>">+</button></td>
                        <td class="subtotal"><?php echo ($vo["price"]*$vo["num"]); ?>元</td>
                        <td>
                            <a class="delete" data-id="<?php echo ($vo["id"]); ?>">删除</a>
                        </td><?php endif; ?>
                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
    </table>
    <div class="total" style="width:100px;height: 100px;border:solid red px;margin-right: 100px">
        <div class="total_div">总价：</div>
        <button style="margin-top: 10px;">生成订单</button>
    </div>
</div>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script src="/TP/Public/Js/jquerysession.js"></script>
<script>

//点击查看大图
    function showImg(url) {
        var img = "<img src='" + url + "' />";
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['auto', 'auto'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: img
        });
    }

    $(function(){
        //总价
        var total_div = $('.total_div').html();
        var subtotal = $('.subtotal');
        var num = 0;
        subtotal.each(function(){
            num += parseInt($(this).html());
        })
        $('.total_div').html(total_div+num+'元');





        //购物车增减
        $('.plus').click(function () {
            var num = parseInt($(this).prev().val());
            $(this).prev().val(num+1);
            var arr =  parseInt($('.shop').html());
            $('.shop').html(arr+1);
            var id = $(this).attr('data-id');//获取自定义属性值
            //alert(id);return;
            $.ajax({
                'url':"<?php echo U('Index/session');?>",
                'data':{'plus':'plus','id':id},
                'type':'post',
                'dataType':'json',
                success:function (data) {
                    window.location.reload()
                }
            })
        })
        $('.reduce').click(function () {
            var num = parseInt($(this).next().val());
            if(num == 1){
                $(this).prop('disabled','disabled');
                return;
            }
            $(this).next().val(num-1);
            var arr =  parseInt($('.shop').html());
            $('.shop').html(arr-1);
            var id = $(this).attr('data-id');//获取自定义属性值
            //alert(id);return;
            $.ajax({
                'url':"<?php echo U('Index/session');?>",
                'data':{'reduce':'reduce','id':id},
                'type':'post',
                'dataType':'json',
                success:function (data) {
                    window.location.reload()
                }
            })
        })




        //删除
        $('.delete').click(function(){
            var id = $(this).attr('data-id');//获取自定义属性值
            layer.msg('确定要删除改商品吗？',{
                time:0,//不自动关闭，
                btn:['确定','取消'],
                yes:function(){
                    $.ajax({
                        url:'delete',
                        type:'post',
                        data:{id:id},
                        dataType:'json',
                        success:function(data){
                            if(data.status=='1'){
                                layer.msg(data.msg,{icon:1});
                            }else if(data.status=='2'||data.status=='3'){
                                layer.msg(data.msg,{icon:2});
                            }

                        }
                    })
                }
            })

        })
    })
</script>
</body>
</html>