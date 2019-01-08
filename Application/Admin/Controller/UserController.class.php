<?php

		namespace Admin\Controller;

		use Think\Controller;

		class UserController extends Controller {

				//用户列表
				public function index() {
						$keywords = "";
						$user = M('user'); //实例化对象
						//$province = M('province');//实例化对象
						if (isset($_POST['submit']) && !empty($_POST['search'])) {
								$keywords = $_POST['search'];
								$where['username|addr|phone'] = array('like', '%' . $keywords . '%');
								$user = $user->where($where);
								//echo 111;die;
						}
						$count = $user->count();
						//$p = getpage($count,8);
						$p = new \Think\Page($count, 8);
						//$where['username'] = $keywords;
						foreach ($map as $key => $val) {
								$p->parameter[$key] = urlencode($val);
						}
						$list = $user->where($where)->order('id')->limit($p->firstRow, $p->listRows)->select();
						//$result = $province->select();
						//print_r($list);die;
						$this->assign('list', $list);
						$this->assign('result', $result);
						$this->assign('keywords', $keywords);
						$this->assign('page', $p->show()); // 赋值分页输出
						$this->display();
				}

				public function idex() {

						$user = M('user'); //实例化对象
						//$where = "id>10";
						$count = $user->count();
						$p = getpage($count, 8);
						$list = $user->order('id')->limit($p->firstRow, $p->listRows)->select();

						//print_r($list);

						$this->assign('list', $list);
						$this->assign('page', $p->show()); // 赋值分页输出
						$this->display();
				}

				//新增用户
				public function add() {
						if (isset($_POST['submit'])) {
								$user = M('user');
								$data['username'] = I('param.username'); //I函数
								$data['password'] = I('param.password');
								$data['phone'] = I('param.phone');
								$data['addr'] = I('param.addr');
								$data['lasttime'] = date("Y-m-d H:i;s");
								if ($insertId = $user->add($data)) {
										$this->success("操作成功", "index");
								} else {
										$this->error("操作失败");
								}
						} else {
								$this->display();
						}
				}

				//编辑用户
				public function edit() {
						$user = M('user');
						if (isset($_POST['submit'])) {
								$id = I('param.userId');
								$data['username'] = I('param.username'); //I函数
								$data['password'] = I('param.password');
								$data['phone'] = I('param.phone');
								$data['addr'] = I('param.addr');
								$data['lasttime'] = date("Y-m-d H:i;s");
								if ($result = $user->where('id=' . $id)->data($data)->save()) {
										$this->success("操作成功", "index");
								} else {
										$this->error("操作失败");
								}
						} else {
								$id = $_GET['id'];
								$data = $user->where('id=' . $id)->find();
								$this->assign('data', $data);
								$this->display();
						}
				}

				//删除用户
				public function del() {
						$user = M('user');
						$id = $_POST['id'];
						if ($result = $user->where('id=' . $id)->delete()) {
								echo json_encode(['code' => 1]);
								exit;
						} else {
								echo json_encode(['code' => -1, 'msg' => '操作失败']);
								exit;
						}
				}

		}
		