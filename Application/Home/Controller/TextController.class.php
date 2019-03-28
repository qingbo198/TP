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
		
		
		
	}