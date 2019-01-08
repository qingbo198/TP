<?php
    
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    
    namespace Think;
    
    /**
     * Description of UcServer
     *
     * @author qingbo198
     */
    class UcServer
    {
        
        public function __construct()
        {
            include_once(ROOT_PATH . 'config.inc.php');
            include_once(ROOT_PATH . 'uc_client/client.php');
        }
        
        /**
         * 会员同步注册
         * @param type $username
         * @param type $password
         * @param type $email
         * @return string
         */
        public function register($username, $password, $email)
        {
            $uid = uc_user_register($username, $password, $email); //UCenter的注册验证函数
            if ($uid <= 0) {
                if ($uid == -1) {
                    return '用户名不合法';
                } elseif ($uid == -2) {
                    return '包含不允许注册的词语';
                } elseif ($uid == -3) {
                    return '用户名已经存在';
                } elseif ($uid == -4) {
                    return 'Email 格式有误';
                } elseif ($uid == -5) {
                    return 'Email 不允许注册';
                } elseif ($uid == -6) {
                    return '该 Email 已经被注册';
                } else {
                    return '未定义';
                }
            } else {
                return intval($uid); //返回一个非负数
            }
        }
        
        public function login($username, $password)
        {
            list($uid, $uname, $password, $email) = uc_user_login($username, $password);
            if ($uid > 0) {
                //echo $uid;
                setcookie("username", $uname, time() + intval(24 * 3600));
                return uc_user_synlogin($uid);
                //echo "论坛同步登录成功";
                //print_r($_COOKIE);
            } elseif ($uid == -1) { //为-1时用户不存在，直接去注册
                $nuid = uc_user_register($username, $password, $username . '@qq.com');
                //echo $username;
                //echo $nuid;die();
                if ($nuid <= 0) {
                    if ($nuid == -1) {
                        return '用户名不合法';
                    } elseif ($nuid == -2) {
                        return '包含不允许注册的词语';
                    } elseif ($nuid == -3) {
                        return '用户名已经存在';
                    } elseif ($nuid == -4) {
                        return 'Email 格式有误';
                    } elseif ($nuid == -5) {
                        return 'Email 不允许注册';
                    } elseif ($nuid == -6) {
                        return '该 Email 已经被注册';
                    } else {
                        return '未定义';
                    }
                } else {
                    setcookie("username", $username, time() + intval(24 * 3600));
                    return uc_user_synlogin($nuid);
                    //echo "论坛同步登录成功";
                }
            } elseif ($uid == -2) {
                return '密码错误';
            } else {
                return '未定义';
            }
        }
        
        //同步退出
        public function quit()
        {
            echo uc_user_synlogout();
            echo "同步退出成功";
        }
        
        
    }
		