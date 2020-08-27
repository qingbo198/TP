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
			$redis->expire($strCacheKey, 300);  # 设置30秒后过期
			$json_data = $redis->get($strCacheKey);
			$data = json_decode($json_data);//获得$data对象
			print_r($data->name); //输出数据
	
	
	
			$strQueueName  = 'Test_bihu_queue';
	
			//进队列
			$redis->rpush($strQueueName, json_encode(['uid' => 1,'name' => 'Job']));
			$redis->rpush($strQueueName, json_encode(['uid' => 2,'name' => 'Tom']));
			$redis->rpush($strQueueName, json_encode(['uid' => 3,'name' => 'John']));
			echo "---- 进队列成功 ---- <br /><br />";
	
			//查看队列
			$strCount = $redis->lrange($strQueueName, 0, 2);
			echo "当前队列数据为： <br />";
			print_r($strCount);
	
			//出队列
			$redis->lpop($strQueueName);
			echo "<br /><br /> ---- 出队列成功 ---- <br /><br />";
	
			//查看队列
			$strCount = $redis->lrange($strQueueName, 0, -1);
			echo "当前队列数据为： <br />";
			print_r($strCount);
		}

		
    }
		