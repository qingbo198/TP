<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
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
			.clear{clear: both;}
			.header{height: 40px;width: 100%;line-height: 40px;background: #333333;position: fixed;top: 0;}
			.header a{text-decoration: none;color:#b0b0b0;}
			.user_login span{float:right;padding-right: 7px;}
			.nav li{float:left;margin-left: 7px;list-style: none;}
			.nav li a:hover{color:white;cursor: pointer;}
			.nav{margin-left: 15.5%;}
			.nav span{margin-left:6px;color: #b0b0b0;}
			.header_nav_out{border:solid red 0px;width:100%;height:100px;}
			.header_nav{width:1230px;margin: 0 auto;height:55px;border:solid green 0px;}
			.logo_mi{width:55px;height:55px;background: #FF6700;background-position:center center;background-image: url('/TP/Public/Image/mi-logo.png');
				margin-top: -24px;float: left;
				}
			.logo_11{float: left;margin-top: -30px;margin-left: 10px;}
			.header_nav ul{margin-top: 40px;}
			.header_nav li{float: left;margin-left: 15px;list-style: none;font-size: 16px;}
			.header_nav a:hover{color: #FF6700;cursor: pointer;}
			.banner{border:solid green 0px;width:1230px;height:460px;margin: 0 auto;}
			.banner_left{border:solid red 0px;width:225px;height:460px;background: #616161;}
			.banner_left li{list-style: none;border:solid green 0px;height:40px;line-height: 40px;padding-left:40px;
				color: white;}
			.banner_left li:hover{background: #FF6700;}
			.banner_left ul{margin-top: 18px;width:100%;margin-left: -40px;}
			.banner div{float:left;}
			.banner_right{border:solid blue 0px;width: 1001px;height:459px;}
			.product_out{border:solid green 0px;width:1150px;margin: 0 auto;padding:15px 40px;text-align: center;}
			.product{width: 200px;height:250px;border:solid #ccc 1px;float: left;margin-right: 40px;margin-left: 43px;
				font-family: "微软雅黑", "宋体", Tahoma, Arial, sans-serif;margin-top: 50px;}
			.pic{text-align: center;}
			.sp{margin-top: 10px;display: block;font-size: 16px;cursor: pointer}
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
	</head>
	<body>
		<!--		  <a href="<?php echo U('Message/index');?>">留言板</a><a>登录</a><a>注册</a>-->
		<!--通过添加父元素并且设置高度和header一样实现元素不遮挡-->
		<div style="height: 40px;">
		<div class="header">
			<ul class="nav">
				<li><a href="/TP/Home/Index/index">小米商城</a><span>|</span></li>
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
									<span style='display: block;margin-top: -2px;margin-left: 5px;font-size: 15px;'>(".($_SESSION['shop']['total'] ? $_SESSION['shop']['total'] : 0) .")</span>
								</a>
							</span>
							<span>
								<a>您好&nbsp&nbsp".$_SESSION['user']."</a>
								<a href='/TP/Home/User/quit'>&nbsp退出&nbsp</a>
							</span>":"

							<span>
								<a  href='/TP/Home/Index/shop' class='iconfont'>&#xe60b;
									<span style='display: block;margin-top: -2px;margin-left: 5px;font-size: 15px;'>(".($_SESSION['shop']['total'] ? $_SESSION['shop']['total'] : 0).")</span>
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
		</div>
		
		<div class="header_nav_out">
			<div class="header_nav">
				<div class="logo_mi"></div>
				<div class="logo_11"><img src="/TP/Public/Image/cms_15410669089659_TZmzr.gif"></div>
				<ul>
					<li><a>小米手机</a><li>
					<li><a>红米</a><li>
					<li><a>电视</a><li>
					<li><a>笔记本</a><li>
					<li><a>空调</a><li>
					<li><a>新品</a><li>
					<li><a>路由器</a><li>
					<li><a>服务</a><li>
					<li><a>智能硬件</a><li>
					<li><a>社区</a><li>
					<li><a href="dsmm://dsmm/<?php echo ($res["appoint"]); ?>">test</a><li>
				</ul>
			</div>
			<div class="banner">
				<div class="banner_left">
					<ul>
						<li><a>手机 电话卡</a></li>
						<li><a>电视 盒子</a></li>
						<li><a>笔记本 平板</a></li>
						<li><a>家电 插线板</a></li>
						<li><a>出行 穿戴</a></li>
						<li><a>智能 路由器</a></li>
						<li><a>电源 配件</a></li>
						<li><a>健康 儿童</a></li>
						<li><a>耳机 音响</a></li>
						<li><a>生活 箱包</a></li>
					</ul>
				</div>
				<div class="banner_right"><img src="/TP/Public/Image/xmad_15427942125674_JlLUW.jpg" width="100%" height="459px;"/></div>
			</div>
			<div class="product_out">
				<?php if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="product">
						<div class="pic">
							<img src="<?php echo ($vo['img'][0]); ?>" width="200px;" height="200px;" />
							<span class="sp" data-id="<?php echo ($vo["id"]); ?>">加入购物车</span>
						</div>
					</div><?php endforeach; endif; else: echo "" ;endif; ?>
				<div class="clear"></div>
				<span class="pages">
					<?php echo ($page); ?>
				</span>
			</div>
		</div>
		<div class="clear"></div>
		<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<style>
			.bottom{margin-top: 120px;font-size: 16px;width: 100%;margin-bottom: 50px;}
			.first{width: 1060px;margin: 0 auto;border-bottom: solid #ccc 1px;height: 80px}
			.ul_f li{float: left;list-style: none;margin-left: 45px;line-height: 80px;}
			.ul_f li span{margin-left: 45px;color: #ccc;}
			.second .link{float: left;width: 150px;height: 112px;}
			.second{width: 1060px;margin: 0 auto;border: solid red 0px;height: 112px;margin-top: 40px;}
			.link dt{font-size: 14px;}
			.link dd{font-size: 12px;margin-top: 10px;}
			.belief{width: 230px;margin: 0 auto;margin-top: 20px;border: solid red 0px;}
			.link1{margin-left:120px;}
		</style>
	</head>
	<body>
		<div class="bottom">
			<div class="first">
				<ul class="ul_f">
					<li><a>预约维修服务</a><span>|</span></li>
					<li><a>7天无理由退货</a><span>|</span></li>
					<li><a>15天免费换货</a><span>|</span></li>
					<li><a>满150元包邮</a><span>|</span></li>
					<li><a>520余家售后网点</a><span></span></li>
				</ul>
			</div>
			<div class="second">

				<dl class="link link1">
					<dt>帮助中心</dt>
					<dd>账户管理</dd>
					<dd>购物指南</dd>
					<dd>订单操作</dd>
				</dl>
				<dl class="link">
					<dt>服务支持</dt>
					<dd>售后政策</dd>
					<dd>自助服务</dd>
					<dd>相关下载</dd>
				</dl>
				<dl class="link">
					<dt>线下门店</dt>
					<dd>小米之家</dd>
					<dd>服务网点</dd>
					<dd>授权体验店</dd>
				</dl>
				<dl class="link">
					<dt>关于小米</dt>
					<dd>了解小米</dd>
					<dd>加入小米</dd>
					<dd>投资者关系</dd>
				</dl>
				<dl class="link">
					<dt>关注我们</dt>
					<dd>新浪微博</dd>
					<dd>官方微信</dd>
					<dd>联系我们</dd>
				</dl>
				<dl class="link">
					<dt>特色服务</dt>
					<dd>F 码通道</dd>
					<dd>礼物码</dd>
					<dd>防伪查询</dd>
				</dl>
			</div>
			<p class="belief">探索黑科技，小米为发烧而生！</p>
		</div>
				

		
	</body>
</html>

		<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
		<script>
			$(function(){
			    $('.sp').click(function(){
			        //layer.alert(111);
                    var id = $(this).attr('data-id');//获取自定义属性值
					$.ajax({
						url:"<?php echo U('Index/shop');?>",
						type:'post',
						data:{id:id},
						dataType:'json',
						success:function (data) {
                            if(data.status=='1'){
                                layer.msg(data.msg,{icon:1});
                            }else if(data.status=='2'||data.status=='3'){
                                layer.msg(data.msg,{icon:2});
                            }
                        }
					})

				})
			})
		</script>
	</body>
</html>