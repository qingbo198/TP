<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
		<title>用户注册</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<script type="text/javascript" src="/TP/Public/Js/jquery-1.9.0.min.js"></script>
			<script src="https://cdn.bootcss.com/layer/3.1.0/layer.js"></script>
			<script type="text/javascript" src="/TP/Public/Js/login.js"></script>
			<link href="/TP/Public/Css/login2.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<h1>快速注册<sup>2018</sup></h1>

		<div class="login" style="margin-top:50px;">

			<!--			<div class="header">
							<div class="switch" id="switch">
								<a class="switch_btn_focus" id="switch_qlogin" href="javascript:void(0);" tabindex="7">快速注册</a>
								<a class="switch_btn" id="switch_login" href="javascript:void(0);" tabindex="8">快速注册</a>
								<div class="switch_bottom" id="switch_bottom" style="position: absolute; width: 64px; left: 0px;"></div>
							</div>
						</div>    -->


			<div class="web_qr_login" id="web_qr_login" style="display: block; height: 430px;">

            <!--登录-->
            <div class="web_login" id="web_login">


               <div class="login-box">


						<div class="login_form">
							<form action="<?php echo U('User/register');?>" name="loginform" accept-charset="utf-8" id="login_form" class="loginForm" method="post">
								<input type="hidden" name="did" value="0"/>
								<input type="hidden" name="to" value="log"/>
                                <div class="uc"></div>
								<div class="uinArea" id="uinArea">
									<label class="input-tips"  for="username">用户名：</label>
									<div class="inputOuter" id="uArea">
										<input type="text" id="username" class="inputstyle"/>
									</div>
								</div>
								<div class="pwdArea" id="pwdArea">
									<label class="input-tips" for="pwd" >密码：</label>
									<div class="inputOuter" id="pArea">
										<input type="password" id="pwd"  class="inputstyle"/>
									</div>
								</div>
								<div class="uinArea" id="uinArea">
									<label class="input-tips" for="phone">手机：</label>
									<div class="inputOuter" id="uArea">
										<input type="text" id="phone"  class="inputstyle"/>
									</div>
								</div>
								<div class="uinArea" id="uinArea">
									<label class="input-tips" for="email">邮箱：</label>
									<div class="inputOuter" id="uArea">
										<input type="text" id="email"  class="inputstyle"/>
									</div>
								</div>
								<div class="uinArea" id="uinArea">
									<label class="input-tips" for="addr">地址：</label>
									<div class="inputOuter" id="uArea">
										<input type="text" id="addr"  class="inputstyle"/>
									</div>
								</div>


								<div style="padding-left:64px;margin-top:20px;">
									<input type="button" value="注册" style="width:150px;" id='button' class="button_blue"/></div>
							</form>
						</div>

					</div>

				</div>
            <!--登录end-->
			</div>




		</div>
		<!--注册end-->
		</div>
		<div class="jianyi">*推荐使用ie8或以上版本ie浏览器或Chrome内核浏览器访问本站</div>

		<!--		<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
				<script src="https://cdn.bootcss.com/layer/3.1.0/layer.js"></script>-->

		<script>

			//判断用户名唯一
//			$('#username').blur(function () {
//				var username = $('#username').val();
//				$.ajax({
//					type: 'get',
//					url: "<?php echo U('User/register');?>",
//					data: {username: username},
//					dataType: 'json',
//					success: function (data) {
//						layer.alert(data.content);
//						return false;
//					}
//				})
//			})

			//提交
			$('#button').click(function () {
				var username = $('#username').val();
				var pwd = $('#pwd').val();
				var phone = $('#phone').val();
				var email = $('#email').val();
				var addr = $('#addr').val();
				if (username == "") {
					layer.alert('请输入用户名');
					return;
				}
				if (pwd == "") {
					layer.alert('请输入密码');
					return;
				}
				if (phone == "") {
					layer.alert('请输入手机号码');
					return;
				}
				var pattern = /^1[345789]\d{9}$/;     //  /^1[3|4|5|8][0-9]\d{4,8}$/
				if (pattern.test(phone) == false) {
					layer.alert("请输入正确的手机号码");
					return;
				}
				if (addr == "") {
					layer.alert('请输入地址');
					return;
				}


				//提交
				$.ajax({
					type: 'post',
					url: "<?php echo U('User/register');?>",
					data: {username: username, pwd: pwd, phone: phone,email:email, addr: addr},
					dataType: 'json',
					success: function (data) {
						if (data.status == 1) {
							layer.msg(data.content, {icon: 2})
						}else if(data.status == 2){
							layer.msg(data.content, {icon: 1}, function () {
							  	$('.uc').html(data.msg);
								$(location).attr("href", "<?php echo U('Index/index');?>");
							})
						}
					}
				})

			})
		</script>

	</body>
</html>