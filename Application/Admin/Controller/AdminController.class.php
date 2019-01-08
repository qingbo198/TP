<?php
	
	namespace Admin\Controller;
	use Think\Controller;

	class AdminController extends Controller{
		public function login(){
			if(isset($_POST['submit'])){
				$admin = M('user');
				$where['user'] = I('param.user');
				$where['password'] =I('param.password');
				if($result = $admin->where($where)->select()){
					//print_r($result);die;
					$_SESSION['user'] = I('param.user');
					$this->success('登录成功',U('Index/index'),2);
				}else{
					$this->error('登录失败');
				}
			}else{
				$this->display();
			}
		}
		
		public function quit(){
			session('user',NULL);
			$this->success('退出成功',U('Admin/login'),2);
		}
	}


		


