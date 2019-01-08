<?php
    /**
     * Created by PhpStorm.
     * User: Administrator
     * Date: 2018\12\6 0006
     * Time: 11:42
     */
    namespace Admin\Controller;
    use Think\Controller;
    
    class ProductCatgoryController extends Controller{
        
        //商品分类列表
        public function index(){
            $pro_cat = M('product_catgory');
            $list = $pro_cat->select();
            $list = findson($list,0,0);
            //print_r($list);
            
            
            $this->assign('list',$list);
            $this->display();
        }
        
        //新增商品分类
        public function add(){
            $check = $_GET['id'];
            $pro_cat = M('product_catgory');
            $list = $pro_cat->select();
            $list = findson($list,0,0);
            if($_POST){
                //print_r($_POST);die;
                $where['name'] = trim($_POST['name']);
                $data['name'] = trim($_POST['name']);
                $data['pid'] = trim($_POST['pid']);
                $result = $pro_cat->where($where)->find();
                //print_r($result);die;
                if($result != false){
                    $msg = [
                        'status'=>'3',
                        'msg'=>'此分类已经存在，请勿重复添加'
                    ];
                    echo json_encode($msg);
                    exit;
                }
                $res = $pro_cat->add($data);
                if($res != false){
                    $msg = [
                        'status'=>'1',
                        'msg'=>'商品分类添加成功'
                    ];
                    echo json_encode($msg);
                    exit;
                }else{
                    $msg = [
                        'status'=>'2',
                        'msg'=>'商品分类添加失败'
                    ];
                    echo json_encode($msg);
                    exit;
                }
            }else{
                $this->assign('check',$check);
                $this->assign('list',$list);
                $this->display();
            }
        }
        
        
        //编辑商品分类
        public function edit(){
            $pro_cat = M('product_catgory');
            $where['id'] = $_GET['id'];
            $pid = $_GET['pid'];
            $list_one = $pro_cat->where($where)->find();
            //print_r($list_one);die;
            $list = M('product_catgory')->select();
            $list = findson($list,0,0);
            if($_POST){
                //print_r($_POST);die;
                $id = $_POST['id'];
                $data['pid'] = $_POST['pid'];
                $data['name'] = $_POST['name'];
                $res = $pro_cat->where('id='.$id)->data($data)->save();
                if($res != false){
                    $msg = [
                        'status'=>'1',
                        'msg'=>'编辑成功'
                    ];
                    echo json_encode($msg);
                    exit;
                }else{
                    $msg = [
                        'status'=>'0',
                        'msg'=>'编辑失败！'
                    ];
                    echo json_encode($msg);
                    exit;
                }
            }else{
                $this->assign('list_one',$list_one);
                $this->assign('list_on',$list_one['id']);
                $this->assign('list',$list);
                $this->assign('check',$pid);
                $this->display();
            }
            
        }
        //删除商品分类
        public function del(){
            $id = $_POST['id'];
            $res = M('product_catgory')->where('pid='.$id)->find();
            if($res != false){
                $msg = [
                    'status'=>'3',
                    'msg'=>'该分类含有子类，无法删除！'
                ];
                echo json_encode($msg);
                exit;
            }
            $del = M('product_catgory')->where('id='.$id)->delete();
            if($del != false){
                $msg = [
                    'status'=>'1',
                    'msg'=>'删除成功'
                ];
                echo json_encode($msg);
                exit;
            }else{
                $msg = [
                    'status'=>'2',
                    'msg'=>'删除失败！'
                ];
                echo json_encode($msg);
                exit;
            }
        }
    }