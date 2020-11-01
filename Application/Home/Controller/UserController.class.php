<?php
    
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor .
     */
    
    namespace Home\Controller;
    
    use Think\Controller;
    
    class UserController extends Controller
    {
        //同步登录
        public function login()
        {
            
            if (isset($_POST['username'])) {
                $user = M('user');
                $where['password'] = $_POST['password'];
                $where['username'] = $_POST['username'];
                $result = $user->where($where)->select();
                $UcServer = new \Think\UcServer;
                $msg = $UcServer->login($_POST['username'], $_POST['password']);
                if ($result != false) {
                    $_SESSION['user'] = I('param.username');
                    $arr = array('status' => '2', 'content' => '登录成功','msg'=>$msg);
                    echo json_encode($arr);
                    exit;
                } else {
                    $arr = array('status' => '1','a'=>'1','content' => $msg);
                    echo json_encode($arr);
                }
            } else {
                $this->display();
            }
        }
        
        //同步注册
        public function register()
        {
            if (isset($_POST['username'])) {
                $user = M('user');
                $where['username'] = trim($_POST['username']);
                $result = $user->where($where)->select();
                if ($result != NULL) {
                    $arr = array('status' => '1', 'content' => '用户名已注册');
                    echo json_encode($arr);
                    exit;
                }
                $data['username'] = trim($_POST['username']);
                $data['password'] = trim($_POST['pwd']);
                $data['phone'] = trim($_POST['phone']);
                $email = trim($_POST['email']);
                $data['email'] = trim($_POST['email']);
                $data['addr'] = trim($_POST['addr']);
                $data['lasttime'] = date("Y-m-d H:i;s");
                $UcServer = new \Think\UcServer;
                $uid = $UcServer->register(trim($_POST['username']), trim($_POST['pwd']), $email); //注册到UCenter
                if ($uid > 0) {
                    $res = $user->add($data);
                    $msg = $UcServer->login($_POST['username'], $_POST['pwd']);
                    if ($res != false) {
                        $_SESSION['user'] = trim($_POST['username']);
                        $arr = array('status' => '2', 'content' => '注册成功','msg'=>$msg);
                        echo json_encode($arr);
                        exit;
                    } else {
                        $this->error("注册失败");
                    }
                }else{
                    $arr = array('status' => '1','content' => $uid);
                    echo json_encode($arr);
                }
            } else {
                $this->display();
            }
        }
        
        //同步退出
        public function quit()
        {
            session('user', NULL);
            $UcServer = new \Think\UcServer;
            $UcServer->quit();
            $this->success('退出成功', U('Index/index'));
            
        }
        
    }
		