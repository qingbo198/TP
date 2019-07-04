<?php

namespace Admin\Controller;

use Think\Controller;

class ArticleController extends Controller
{
	public function index()
	{
		$result = M('article a')
			->field('a.id,a.writer,a.title,a.last_time,a.pid,name')
			->join('left join category c on a.pid = c.id' )
			->select();
		//echo M()->getLastSql();die;
		$this->assign('result', $result);
		$this->display();
	}
	
	public function add()
	{
		$category = M('category');
		$article = M('article');
		$result = $category->select();
		$result = findson($result, 0, 0);
		if (isset($_POST['submit'])) {
			$data['title'] = I('param.title');
			$data['content'] = I('param.content');
			$data['writer'] = I('param.writer');
			$data['pid'] = I('param.select');
			$data['lasttime'] = date("Y-m-d H:i;s");
			//print_r($data);die;
			if ($insertId = $article->data($data)->add()) {
				$this->success('操作成功', 'index');
			} else {
				$this->error('操作失败');
			}
		} else {
			$this->assign('list', $result);
			$this->display();
		}
	}
	
	//编辑新闻
	public function edit()
	{
		$id = $_GET['id'];
		$pid = $_GET['pid'];
		$cate = M('category');
		$article = M('article');
		$result = $article->find($id);
		$all = M('category')->select();
		$category = father($all, $pid);
		//print_r($category);die;
		if (isset($_POST['submit'])) {
			$id = I('param.wyid');
			$data['title'] = I('param.title');
			$data['content'] = I('param.content');
			$data['writer'] = I('param.writer');
			$data['pid'] = I('param.pid');
			$data['last_time'] = date("Y-m-d H:i;s");
			//print_r($data);die;
			if ($sql = $article->where('id=' . $id)->data($data)->save()) {
				$this->success('操作成功', 'index');
			} else {
				$this->error('操作失败');
			}
		} else {
			$this->assign('category', $category);
			$this->assign('result', $result);
			$this->display();
		}
	}
}




