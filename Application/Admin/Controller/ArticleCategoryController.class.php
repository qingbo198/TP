<?php
	
	namespace Admin\Controller;
	use Think\Controller;
	class ArticleCategoryController extends Controller{
		//文章分类列表
		public function index(){
			
			$category = M('category');
			$result = $category->select();
			$result = findson($result,0,0);
			//print_r($result);
			$this->assign('list',$result);
			$this->display();
		}

		//新增文章分类
		public function add(){
			$check = $_GET['id'];
			//print_r($id);die;
			$category = M('category');
			$result = $category->select();
			$result = findson($result,0,0);
			if(isset($_POST['submit'])){
				$select = I("param.select");
				$select = 0 ? $data['fid'] = 0 : $data['fid'] = I("param.select");
				$data['name'] = I("param.name");
				if($insertId = $category->add($data)){
					$this->success("操作成功","index");
				}else{
					$this->error("操作失败");
				}
			}else{
				$this->assign('list',$result);
				$this->assign('check',$check);
				$this->display();
			}
		}

		//编辑文章分类
		public function edit(){
			$id = $_GET['id'];
			$fid = $_GET['fid'];
			$cate = M('category');
			$category = M('category')->find($id);
			$all = M('category')->select();
			$result = father($all,$fid);
			if(isset($_POST['submit'])){
				$data['fid'] = I("param.fid");
				$id = I("param.id");
				$data['name'] = I("param.name");
				if($insertId = $cate->where('id='.$id)->data($data)->save()){
					$this->success("操作成功","index");
				}else{
					$this->error("操作失败");
				}
			}else{
				$this->assign('result',$result);
				$this->assign('category',$category);
				$this->display();
			}
		}
		
		
		//删除文章分类
		public function del(){
			$id = $_GET['id'];
			$category = M('category');
			if($result = $category->where('fid='.$id)->select()){
				$this->error('有子类无法删除','index');
			}else if($result =$category->where('id='.$id)->delete()){
				$this->success('操作成功','index');
			}else{
				$this->error('操作失败');
			}
		}
	}

