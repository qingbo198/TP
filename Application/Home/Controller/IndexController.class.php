<?php
    
    namespace Home\Controller;
    
    use Think\Controller;
    
    class IndexController extends Controller
    {

//				public function _initialize() {
//						// 判断用户是否登陆
//						$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
//						if (!$user) {
//								$this->success('未登录，请登录', U('User/login'), 2);
//								exit;
//						}
//				}
        
        public function index()
        {
            $product = M('product');
            $count = $product->count();
            //echo $count;
            $p = getpage($count,12);
            $result = $product->field(true)->limit($p->firstRow, $p->listRows)->select();
            //print_r($result);die;
            //$result = $product->select();
            foreach($result as $key=>$value){
                $result[$key]['img'] = json_decode($value['img']);
            }
            $this->assign('result',$result);
            $this->assign('page',$p->show());
            $this->display();
        }
        
        
        public function quit()
        {
            session('user', NULL);
            $this->success('退出成功', U('Index/index'));
        }
        
        
        //购物车页面
        public function shop(){
            if($_POST){
                $total = 0;
                //unset($_SESSION['shop']);die;
                $arr = $_SESSION['shop'];
                $product = M('product');
                $result = $product->where('id='.$_POST['id'])->find();
                $result['img'] = json_decode($result['img']);
                //print_r($result);die;
                if(empty($arr)){
                    $arr[$result['id']] = [
                        'id'=> $result['id'],
                        'name'=>$result['name'],
                        'price'=>$result['price'],
                        'img'=>$result['img'],
                        'num'=>1
                    ];
                    foreach($arr as $k=>$v){
                        $total += $v['num'];
                    }
                    $arr['total'] = $total;
                    $_SESSION['shop'] = $arr;
                    print_r($_SESSION['shop']);die;
                }else{
                    if(array_key_exists($result['id'],$_SESSION['shop'])){
                        $arr[$result['id']]['num'] += 1;
                    }else{
                        $arr[$result['id']] = [
                            'id'=> $result['id'],
                            'name'=>$result['name'],
                            'price'=>$result['price'],
                            'img'=>$result['img'],
                            'num'=>1
                        ];
                    }
                    foreach($arr as $k=>$v){
                        $total += $v['num'];
                    }
                    $arr['total'] = $total;
                    $_SESSION['shop'] = $arr;
                    print_r($_SESSION['shop']);die;
                }
            }else{
                $this->assign('list',$_SESSION['shop']);
                $this->display();
            }
        }
        
        //获取session
        function session(){
            if($_POST['plus'] == 'plus'){
                $_SESSION['shop'][$_POST['id']]['num'] += 1;
                $_SESSION['shop']['total'] += 1;
                echo json_encode($_SESSION['shop']);
                exit;
            }elseif($_POST['reduce'] == 'reduce'){
                $_SESSION['shop'][$_POST['id']]['num'] -= 1;
                $_SESSION['shop']['total'] -= 1;
                echo json_encode($_SESSION['shop']);
                exit;
            }
            
        }
        
    }
		