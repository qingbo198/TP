<?php

namespace Admin\Controller;
use Think\Controller;
class ArticleController extends Controller{
	public function index(){
		//$article =M('article');
		//$resule = $article->select();
		//print_r($result);die;
		$result = M()->table(array('tp_article'=>'a','tp_category'=>'c'))
						  ->field('a.id,a.writer,a.title,a.lasttime,a.fid,c.name')
						  ->where('a.fid=c.id')
						  ->select();
		$this->assign('result',$result);
		$this->display();
	}
	
	public function add(){
		$category = M('category');
		$article = M('article');
		$result = $category->select();
		$result = findson($result,0,0);
		if(isset($_POST['submit'])){
			$data['title'] = I('param.title');
			$data['content'] = I('param.content');
			$data['writer'] = I('param.writer');
			$data['fid'] = I('param.select');
			$data['lasttime'] =  date("Y-m-d H:i;s");
			//print_r($data);die;
			if($insertId = $article->data($data)->add()){
				$this->success('操作成功','index');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->assign('list',$result);
			$this->display();
		}
	}
	
	//编辑新闻
	public function edit(){
		$id = $_GET['id'];
		$fid = $_GET['fid'];
		$cate = M('category');
		$article = M('article');
		$result = $article->find($id);
		$all = M('category')->select();
		$category = father($all,$fid);
		//print_r($category);die;
		if(isset($_POST['submit'])){
			$id = I('param.wyid');
			$data['title'] = I('param.title');
			$data['content'] = I('param.content');
			$data['writer'] = I('param.writer');
			$data['fid'] = I('param.fid');
			$data['lasttime'] =  date("Y-m-d H:i;s");
			//print_r($data);die;
			if($sql = $article->where('id='.$id)->data($data)->save()){
				$this->success('操作成功','index');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->assign('category',$category);
			$this->assign('result',$result);
			$this->display();
		}
	}
}




