<?php
    
    namespace Admin\Controller;
    
    use Think\Controller;
    
    class IndexController extends Controller
    {
        
        public function _initialize()
        {
            // 判断用户是否登陆
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                $this->success('未登录，请登录', U('Admin/login'), 2);
                exit;
            }
        }
        
        public function index()
        {
            
            
            $this->display();
        }
        
    }
		