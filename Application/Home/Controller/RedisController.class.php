<?php
	
    
    namespace Home\Controller;
    
    use Think\Controller;
	//use Think\Cache\Driver\Redis;
	header("Content-type: text/html; charset=utf-8");
    class RedisController extends Controller
    {

        
        public function index()
        {
			//创建一个redis对象
			$redis = new \Redis();
	
			//连接本地的 Redis 服务
			$redis->connect('127.0.0.1', 6379);
	
			//密码验证,如果没有可以不设置
			//$redis->auth('123456');
	
			//查看服务是否运行
			//echo "Server is running: " . $redis->ping();
			echo '<br/>';
			//设置缓存
			$redis->set('username','zhang san',3600);
	
			//获取缓存
			//$user_name = $redis->get('username');
			//var_dump($user_name);
			$strCacheKey  = 'Test_bihu';
	
			//SET 应用
			$arrCacheData = [
				'name' => 'job',
				'sex'  => '男',
				'age'  => '30'
			];
			$redis->set($strCacheKey, json_encode($arrCacheData));
			$redis->expire($strCacheKey, 30);  # 设置30秒后过期
			$json_data = $redis->get($strCacheKey);
			$data = json_decode($json_data);//获得$data对象
			//print_r($data->name); //输出数据
	
	
	
			$strQueueName  = 'Test_bihu_queue';
	
			//进队列
			$redis->rpush($strQueueName, json_encode(['uid' => 1,'name' => 'Job']));
			$redis->rpush($strQueueName, json_encode(['uid' => 2,'name' => 'Tom']));
			$redis->rpush($strQueueName, json_encode(['uid' => 3,'name' => 'John']));
			echo "---- 进队列成功 ---- <br /><br />";
	
			//查看队列
			$strCount = $redis->lrange($strQueueName, 0, 2);
			echo "当前队列数据为： <br />";
			//print_r($strCount);
	
			//出队列
			$redis->lpop($strQueueName);
			echo "<br /><br /> ---- 出队列成功 ---- <br /><br />";
	
			//查看队列
			$strCount = $redis->lrange($strQueueName, 0, -1);
			echo "当前队列数据为： <br />";
			print_r($strCount);
		}
	
	
		//查询昨天借款、还款数据接口
		public function search_data(){
			//$start_time = strtotime(date("Y-m-d"),time())-86400;
			$start_time = strtotime('2019-06-2 00:00:00');
			//echo $start_time;die;
			//$end_time = strtotime(date("Y-m-d"),time());
			$end_time = strtotime('2019-06-3 00:00:00');
			//今日借款
			$where['second_verify_time'] = array('between',array($start_time,$end_time));
			$reg_list = M('lzh_borrow_info')
				->field('id,second_verify_time')
				->where($where)
				->select();
			//echo M()->getLastSql();die;
			if(!empty($reg_list)){
				$data_reg = '';
				foreach ($reg_list as $k=>$v){
					$data_reg .= $v['id']."|";
				}
				echo "今日借款标的".$data_reg."<br>";
			}else{
				echo "今日无借款"."<br>";
			}
			//今日还款
			$status['repayment_time'] = array('between',array($start_time,$end_time));
			$repay_list = M('lzh_investor_detail id')
				->distinct(true)
				->field('borrow_id')
				->where($status)
				->select();
			if(!empty($repay_list)){
				$data_repay = '';
				foreach ($repay_list as $k=>$v){
					$data_repay .= $v['borrow_id']."|";
				}
				echo "今日还款标的".$data_repay;
			}else{
				echo "今日无还款";
			}
		
		}
		
		
    }
		