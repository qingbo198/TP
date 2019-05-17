<?php

namespace Home\Controller;
use Think\Controller;


	class TextController extends Controller {
	
		public function index(){
			header("Content-type: text/html; charset=UTF-8");	
			$name = "王小二";
			$name = iconv("UTF-8","GB2312//IGNORE",$name);
			$str = strlen($name);
			echo $str;die;
		}
		
		public function related(){
			
			/*******关联文章标题展示开始****/
			$result = M("article")->find($id);
			//如果当前文章没有关键词则展示相同分类的文章
			if(empty($result['art_keyword'])){
				$where['type_id'] = $typeid;
				$related_article=M("article")->field('id,title,art_keyword')->where($where)->order('id desc')->select();
				//echo 'debug<br><pre>'; print_r($related_article); exit;
				$related = array_rand($related_article,6);
				$related_array = array();
				foreach ($related as $v){
					array_push($related_array,$related_article[$v]);
				}
			}else{
				//关键字不为空则进一步通过关键字匹配相关文章的标题
				$where['type_id'] = $typeid;
				$where['art_keyword'] = array('neq','');
				$where['id'] = array('neq',$id);
				$related_list = M("article")->field('id,title,art_keyword,type_id')->where($where)->limit(6)->order('id desc')->select();
				//echo 'debug<br><pre>'; print_r($related_list); exit;
				$arrall = array();
				foreach ($related_list as $k=>$v){
					if(strpos($v['art_keyword'],'，') !== false){
						$array = explode('，',$v['art_keyword']);
						foreach ($array as $value){
							if(strpos($result['art_keyword'],$value) !== false){
								$arrall[] = $v['id'];
							}
						}
						$arrall = array_unique($arrall);
					}else{
						if(strpos($result['art_keyword'],$v['art_keyword']) !== false){
							$arrall[] = $v['id'];
						}
					}
				}
				$status_last['id'] = array('in',$arrall);
				$related_array = M("article")->field('id,title,art_keyword,type_id')->where($status_last)->order('id desc')->select();
			}
			$this->assign('related_art',$related_array);
			
			/*******关联文章标题展示结束****/
			
		}
	}