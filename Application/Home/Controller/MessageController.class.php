<?php
namespace Home\Controller;
use Think\Controller;
	class MessageController extends Controller {
		public function index(){
			$Message = M('message');
			$result = $Message->limit(5)->select();
			$resulter = $Message->limit(5)->order('id DESC')->select();
			//print_r($result);die;
			if(isset($_POST['submit'])){
				$data['company'] = I('param.company');
				$data['name'] = I('param.name');
				$data['position'] = I('param.position');
				$data['phone'] = I('param.phone');
				$data['question'] = I('param.question');
				$data['lasttime'] = date("Y-m-d H:i;s");
				if($insertId = $Message->data($data)->add()){
					$this->success('提交成功','index');
				}else{
					$this->error('提交失败，请重试');
				}
			}else{
				$this->assign('result',$result);
				$this->assign('resulter',$resulter);
				$this->display();
			}
		}
	}