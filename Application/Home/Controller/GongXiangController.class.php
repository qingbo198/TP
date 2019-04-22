<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class GongXiangController extends Controller {
		//信息共享平台接口(首次报送)
		//截止到报送月月初的还款中标的数据
		public function first_sub(){
			//echo phpinfo();die;
			// $start_time = date('Y-m-01 00:00:00', strtotime('-1 month'));//上月第一天开始时间
			// $end_time = date('Y-m-t 23:59:59', strtotime('-1 month'));//上月最后一天结束时间
			$start_time = strtotime('2019-04-01 00:00:00');
			$end_time = strtotime('2019-04-30 23:59:59');
			$status['second_verify_time&borrow_status'] = array(array('lt',$end_time),array('eq',6),'_multi'=>true);
			//$status['bi.id'] = 1547;
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as borrow_id,borrow_uid,cell_phone,second_verify_time,deadline,borrow_money,repayment_type,mi.real_name,mi.idcard")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); die;
			$info = '';
			foreach ($list as $key=>$value){
				//证件号码
				if(substr($value['idcard'],17,1) == 'x'){
					$idcard = substr($value['idcard'],0,17).'X';
				}else{
					$idcard = $value['idcard'];//身份证号码
				}
				//业务类型
				if($value['repayment_type'] == 4){
					$borrow_type = 2;//2 分期还款；先息后本
				}elseif ($value['repayment_type'] == 5){
					$borrow_type = 3;//3 一次性还款；末期本息
				}
				$where['repayment_time&borrow_id'] = array(array('between',array($start_time,$end_time)),array('eq',$value['borrow_id']),'_multi'=>true);
				$result = M('lzh_investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
				//echo M()->getLastSql(); die;
				//print_r($result);//die;
				//业务发生日期、当月还款状态
				if($value['second_verify_time'] > $start_time && $value['second_verify_time'] < $end_time){
					$happenday = date('Ymd',$value['second_verify_time']);//新开立的债权融资业务
					$repay_status = "*";
				}elseif (!empty($result)){
					$happenday = date('Ymd',$result[0]['repayment_time']);//当月发生还款（先息后本）
					$repay_status = "N";
				}elseif ($value['repayment_type'] == 5){
					$happenday = date('Ymd',$end_time);//当月不需还款（末期本息）
					$repay_status = "*";
				}
				//余额
				$last_money = $value['borrow_money'];//(首次报送的都为还款中的标的所以余额为借款金额)
				
				
				
				//输出信息
				$info .= $value['real_name'].",".//姓名
					"0".",".//证件类型
					$idcard.",".//证件号码
					"91320200323591589D".",".//业务发生机构:社会信用代码;
					$value['borrow_id'].",".//业务号:系统内唯一标识贷款账户的标识符。
					$borrow_type.",".//业务类型
					"11".",".//业务种类
					date('Ymd',$value['second_verify_time']).",".//开户日期:当业务类型为 2、3、5 时，该数据项为首次放款日期；
					date('Ymd',$value['deadline']).",".//到期日期
					intval($value['borrow_money']).",".//授信额度
					$happenday.",".//业务发生日期
					intval($last_money).",".//余额
					"0".",".//当前逾期总额
					$repay_status
					."\r\n";
			}
			echo $info;
			$txtname = "121EXPORTTRADEINFO.txt";
			$this->creZip($txtname,$info);
		
		}
		
		
		//信息共享平台接口(再次报送)
		//当月10号之前报送上月数据
		public function next_sub(){
			//echo phpinfo();die;
			$start_time = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));//上月第一天开始时间
			$end_time = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));//上月最后一天结束时间
			$start_time = strtotime('2019-04-01 00:00:00');
			$end_time = strtotime('2019-04-30 23:59:59');
			//echo $start_time."---".$end_time;die;
			//上月新增借款
			$status_new['second_verify_time'] = array('between',array($start_time,$end_time));
			$list_new = M('lzh_borrow_info')
				->order('second_verify_time')
				->field('id')
				->where($status_new)
				->select();
			//echo M()->getLastSql();die;
			//print_r($list_new);die;
			if(!empty($list_new)){
				foreach ($list_new as $K=>$v){
					$list_new_array[] = $v['id'];
				}
			}
			//print_r($list_new_array);die;
			
			//上月还款标的数据
			$status_repay['repayment_time'] = array('between',array($start_time,$end_time));
			$repay_list = M('lzh_investor_detail')
				->DISTINCT(true)
				->order('repayment_time')
				->field('borrow_id')
				->where($status_repay)
				->select();
			//echo 'debug<br><pre>'; print_r($repay_list); exit;
			if(!empty($repay_list)){
				foreach ($repay_list as $k=>$v){
					$repay_list_array[] = $v['borrow_id'];
				}
			}
			//echo 'debug<br><pre>'; print_r($repay_list_array); exit;
			$arr = array_merge($list_new_array,$repay_list_array);//合并新增标的和还款数据
			//echo 'debug<br><pre>'; print_r($arr);die;
			
			
			$status['bi.id'] = array('in',$arr);
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as borrow_id,borrow_uid,cell_phone,second_verify_time,borrow_status,deadline,borrow_money,repayment_type,mi.real_name,mi.idcard")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); die;
			$info = '';
			foreach ($list as $key=>$value){
				//证件号码
				if(substr($value['idcard'],17,1) == 'x'){
					$idcard = substr($value['idcard'],0,17).'X';
				}else{
					$idcard = $value['idcard'];//身份证号码
				}
				//业务类型
				if($value['repayment_type'] == 4){
					$borrow_type = 2;//2 分期还款；先息后本
				}elseif ($value['repayment_type'] == 5){
					$borrow_type = 3;//3 一次性还款；末期本息
				}
				$where['repayment_time&borrow_id'] = array(array('between',array($start_time,$end_time)),array('eq',$value['borrow_id']),'_multi'=>true);
				$result = M('lzh_investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
				//echo M()->getLastSql(); die;
				//print_r($result);//die;
				//业务发生日期、当月还款状态、余额
				if($value['second_verify_time'] > $start_time && $value['second_verify_time'] < $end_time){
					$happenday = date('Ymd',$value['second_verify_time']);//新开立的债权融资业务
					$repay_status = "*";
					$last_money = $value['borrow_money'];
				}
				//当期产生还款数据
				if (!empty($result)){ //末期本息当期还款 标的结束
					if(($value['repayment_type'] == 5 && $value['borrow_status'] == 7) || ($value['repayment_type'] == 5 && $value['borrow_status'] == 9)){
						$happenday = date('Ymd',$result[0]['repayment_time']);
						$repay_status = "C";//结清
						$last_money = 0;
					}elseif (($value['repayment_type'] == 4 && $value['borrow_status'] == 6)){
						$happenday = date('Ymd',$result[0]['repayment_time']);//先息后本当期发生还款 标的未结束
						$repay_status = "N";
						$last_money = $value['borrow_money'];
					}elseif (($value['repayment_type'] == 4 && $value['borrow_status'] == 7) || ($value['repayment_type'] == 4 && $value['borrow_status'] == 9)){
						$happenday = date('Ymd',$result[0]['repayment_time']);//先息后本当月发生还款 标的结束
						$repay_status = "C";//结清
						$last_money = 0;
					}
				}elseif ($value['repayment_type'] == 5 && $value['borrow_status'] == 6){//末期本息当期未发生还款
					$happenday = date('Ymd',$end_time);
					$repay_status = "*";
					$last_money = $value['borrow_money'];
				}
				
				
				
				//输出信息
				$info .= $value['real_name'].",".//姓名
					"0".",".//证件类型
					$idcard.",".//证件号码
					"91320200323591589D".",".//业务发生机构:社会信用代码;
					$value['borrow_id'].",".//业务号:系统内唯一标识贷款账户的标识符。
					$borrow_type.",".//业务类型
					"11".",".//业务种类
					date('Ymd',$value['second_verify_time']).",".//开户日期:当业务类型为 2、3、5 时，该数据项为首次放款日期；
					date('Ymd',$value['deadline']).",".//到期日期
					intval($value['borrow_money']).",".//授信额度
					$happenday.",".//业务发生日期
					intval($last_money).",".//余额
					"0".",".//当前逾期总额
					$repay_status//本月还款状态
					."<br>";
			}
			echo $info;
			$txtname = "121EXPORTTRADEINFO.txt";
			//$this->creZip($txtname,$info);
			
		}
		
		
		
		
		
		
		//信息共享平台生成生成txt并压缩成zip文件
		private function creZip($filename,$txtcontent){
			$dir = dirname(dirname(dirname(dirname(__FILE__)))) . '\\Uploads\\gongxiang';
			//echo $dir;die;
			if (!file_exists($dir)) {
				mkdir($dir,0777,true); //创建文件夹
			}
			$file_dir = $dir.'\\'.$filename;
			$txtcontent=iconv('utf-8',"GBK",$txtcontent); //转gbk格式
			file_put_contents($file_dir, $txtcontent, true);
			//return;
			//生成压缩文件
			$packagename = '323591589'.date('YmdHi',time()).'12'.'0001'.'.zip';
			$zipname=$dir.'\\'.$packagename;
			$zip = new \ZipArchive();
			$zip->open($zipname,\ZipArchive::CREATE);//打开压缩包
			$zip->addFile($file_dir,basename($file_dir));//向压缩包中添加文件
			if ($zip->open($zipname,\ZipArchive::CREATE) !== TRUE) {
				$zipstatus = 0;
				return 'zip文件生成失败';
			} else {
				$zipstatus = 1;
			}
			$zip->close();  //关闭压缩包
		}
		
		
		
		//末期本息提前还款利息不对查询
		public function check(){
			//D3贷款贷后还款数据
			$where['second_verify_time&borrow_status&repayment_type'] = array(array('gt',strtotime('2016-08-24 23:59:59')),array('in',array('7','9')),array('eq',5),'_multi'=>true);
			$where['cell_phone'] = array('neq','');
			$where['is_advanced'] = 2;
			//$where['bi.id'] = 1522;//1240
			//$where['bi.id'] = array('in',array('1227','1534'));
			
			//获取以上标的 还款明细  id.repayment_time, id.borrow_id, id.investor_uid,, id.sort_order, id.total, id.status, id.deadline, id.capital, id.interest, id.receive_capital, id.receive_interest
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as bid,bi.is_advanced,has_pay,is_prepayment,bi.borrow_uid,borrow_status,bi.borrow_money,repayment_type,lz.zhaiquan_name,real_name,lz.zhaiquan_idcard,mi.cell_phone")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($where)
			// 	->count();
			// echo $list;die;
				//->limit(4)
				->select();
			//echo M()->getLastSql(); exit;
			$singleLoanRepayInfo = "#singleLoanRepayInfo"."\r\n";
			foreach ($list as $k=>$v) {
				$result = M('lzh_investor_detail')->where('borrow_id=' . $v['bid'])->select();
				
				foreach ($result as $kk => $vv) {
					if($vv['interest'] == $vv['receive_interest']){
						$array[] = $vv['borrow_id'];
					}
				}
				$array = array_unique($array);
			}
			echo 'debug<br><pre>'; print_r($array); exit;
			
		}
		
		
		
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}
		
	}