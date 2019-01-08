$.validator.setDefaults({
    submitHandler: function() {
	  form.submit();
    }
});
$().ready(function() {
// 在键盘按下并释放及提交后验证提交表单
  $("#signupForm").validate({
	    rules: {
	     
	      username: {
	        required: true,
	        minlength: 2
	      },
	      password: {
	        required: true,
	        minlength: 5
	      },
	      confirm_password: {
	        required: true,
	        minlength: 5,
	        equalTo: "#password"
	      },
			addr:{
				required:true
		  },
			phone:{
				required:true
		  }
	    },
	    messages: {
	      username: {
	        required: "请输入用户名",
	        minlength: "用户名必需由两个字母组成"
	      },
	      password: {
	        required: "请输入密码",
	        minlength: "密码长度不能小于 5 个字母"
	      },
	      confirm_password: {
	        required: "请输入密码",
	        minlength: "密码长度不能小于 5 个字母",
	        equalTo: "两次密码输入不一致"
	      },
		addr:{
			required:"请输入地址"
		  },
		phone:{
			required:"请输入手机号码"
		  }
	    }
	});
});