<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class BaihangController extends Controller {
		
		//百行D2接口报送贷款账户信息(增量数据)
		//T+1 隔天上报
		public function SendtoBaiHang_D2(){
		
			$start = date("Y-m-d",(time()-86400));
			$start_time = strtotime($start)-1;//前天23:59：59开始时间
			//echo $start_time;die;
			$end = date("Y-m-d",time());
			$end_time = strtotime($end);//前一天上报数据截止时间今天零点
			$start_time = 1550216651;
			$end_time = 1550216653;

			$where['second_verify_time'] = array('between',array($start_time,$end_time));
			$msg = M('lzh_borrow_info bi');
			$list = $msg->order('bi.add_time')
			->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
			,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id')
			->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
			->join('left join lzh_members as m on m.id = bi.borrow_uid')
			->where($where)
			->select();
			echo $msg->getLastSql().'<br>';
			//print_r($list);die();
			if(!empty($list)){
				foreach ($list as $key => $value) {
					//接口数据///////////////////////////
					$parama['reqID'] = $this->randReqID($value['borrow_uid']);//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';
					$parama['uploadTs'] = date("Y-m-d\TH:i:s",time());//记录生成时间
					$parama['name'] = $value['real_name'];//借款人姓名
					if(substr($value['idcard'],17,1) == 'x'){
						$parama['pid'] = substr($value['idcard'],0,17).'X';
					}else{
						$parama['pid'] = $value['idcard'];
					}
					$parama['mobile'] = $value['cell_phone'];
					$parama['loanID'] = $value['borrow_id'];
					$parama['originalLoan'] = null;
					$parama['guaranteeType'] = 2;
					$parama['loanPurpose'] = 1;
					$parama['applyDate'] = date("Y-m-d\TH:i:s",$value['add_time']);
					$parama['accountOpenDate'] = date("Y-m-d\TH:i:s",$value['first_verify_time']);
					$parama['issueDate'] = date("Y-m-d\TH:i:s",$value['second_verify_time']);
					$parama['dueDate'] = date("Y-m-d",$value['deadline']);
					$parama['loanAmount'] = $value['borrow_money'];
					$parama['totalTerm'] = $value['borrow_duration'];
					$parama['targetRepayDateType'] = 2;
					$parama['termPeriod'] = -1;
					//获取还款记录信息
					$detail = M('lzh_investor_detail');
					$result = $detail->where('borrow_id='.$value['borrow_id'])->select();
					foreach($result as $k=>$v){
						$data[] = date("Y-m-d",$v['deadline']);
					}
					$data = array_unique($data);
					$parama['targetRepayDateList'] = implode(",",$data);
					$parama['firstRepaymentDate'] = reset($data);
					unset($data);
					$parama['gracePeriod'] = 3;
					$parama['device'] = array('deviceType'=> 2,'imei'=>null,'mac'=>null,'ipAddress'=>null, 'osName'=>6);

					echo $this->toJson($parama).'<br>';die;
				}
			}else{
				echo "无新增借款数据";
			}
				
			//echo 'debug<br><pre>'; //print_r($borrow_bhlist); var_dump($borrow_bhlist); exit;	
			}
		
		
		
		//还款业务数据D3
		public function SendtoBaiHang_D3(){
			$start = date("Y-m-d",(time()-86400));
			$start_time = strtotime($start)-1;//前天23:59：59开始时间
			$end = date("Y-m-d",time());
			$end_time = strtotime($end);//前一天上报数据截止时间今天零点

			$status['repayment_time'] =array('between',array($start_time,$end_time));
			$list_repayment = M('lzh_investor_detail lid')
				->DISTINCT(true)
				->field('lid.borrow_id')
				->where($status)->select();
			//echo M()->getLastSql();echo "<br>";die();
			if(!empty($list_repayment)){
				foreach ($list_repayment as $k=>$v){
					$repay_array[] = $v['borrow_id'];
				}
				$status2['bi.id'] = array('in',$repay_array);
				$list = M("lzh_borrow_info bi")
				->field('bi.id as bid, mi.real_name, mi.cell_phone, 	
				mi.idcard,bi.has_pay,bi.repayment_type,bi.borrow_duration,bi.borrow_money,bi.borrow_status')
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->where($status2)
				->select();
				//echo M()->getLastSql(); die();

				foreach ($list as $key => $value) {
					$parama['reqID'] = $this->randReqID($value['borrow_uid']);//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';
					$parama['uploadTs'] = date('Y-m-d\TH:i:s',time());
					$parama['loanId'] = $value['bid'];
					$parama['name'] = $value['real_name'];
					if(substr($value['idcard'],17,1) == 'x'){
						$parama['pid'] = substr($value['idcard'],0,17).'X';
					}else{
						$parama['pid'] = $value['idcard'];
					}
					$parama['mobile'] = $value['cell_phone'];
					$parama['termNo'] = $value['has_pay'];
					$parama['termStatus'] = 'normal';

					$detail = M('lzh_investor_detail');
					$result = $detail->where('borrow_id='.$value['bid'])->select();
					foreach($result as $k=>$v){
						if($v['sort_order'] == $value['has_pay']){
							$parama['targetRepaymentDate'] = date('Y-m-d',$v['deadline']);
							$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s',$v['repayment_time']);
							$parama['plannedPayment'] += $v['capital'] + $v['interest'];
							$parama['targetRepayment'] = 0;
							$parama['realRepayment'] += $v['capital'] + $v['interest'];
							$parama['overdueStatus'] = '';
							$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s',strtotime("-1 hour",  time()));;
							$parama['overdueAmount'] = 0;
							if($value['repayment_type'] == 5){
								$parama['remainingAmount'] = 0;
							}elseif ($value['repayment_type'] == 4){
								if($value['has_pay'] == $value['borrow_duration']){
									$parama['remainingAmount'] = 0;
								}else{
									$parama['remainingAmount'] = $value['borrow_money'];//贷款剩余额度
								}
							}
						}
					}
					if($value['repayment_type'] == 5){
						$parama['loanStatus'] = ($value['has_pay'] == 1)? 3 : 1;
					}else{
						$parama['loanStatus'] = ($value['has_pay'] == $value['borrow_duration'])? 3 : 1;
					}

					echo $this->toJson($parama).'<br>';die;
				}


			}else{
				echo "无还款数据";die;
			}
			



			
			
			 
			
			$expireDays = ($value['deadline'] - $value['repayment_time'])/(24 *3600);
			
			
			
			//根据还款方式判断本笔贷款状态  4:先息后本; 5:末期本息;
			
			echo 'debug<br><pre>'; print_r($parama); exit;
			echo $this->JSON($parama).'<br>';die;
			
		}
		
		//新增贷款、还款记录上报表操作
		public function post_table(){
			//查询标的数据
			$borrow_id = intval($_GET['borrow_id']);
			$where['bi.id'] = $borrow_id;
			$result = M('borrow_info bi')
				->field('bi.repayment_type,bi.total,bi.has_pay')
				->where($where)
				->find();
			echo 'debug<br><pre>'; print_r($result);// exit;
			
			//数据入库lah_borrow_baihang
			$list = M('borrow_baihang')
				->where('borrow_id='.$borrow_id)
				->find();
			if(empty($list)){
				$data['borrow_id'] = $borrow_id;
				$data['request_id'] = $this->randReqID($borrow_id);//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
				$data['repay_type'] = $result['repayment_type'];
				$data['has_repay'] = $result['has_pay'];
				$data['total'] = $result['total'];
				$data['status_add'] = 0;
				$data['status_repay'] = 0;
				$data['send_time'] = date('Y-m-d',strtotime("+1 day",  time()));
				
				//echo 'debug<br><pre>'; print_r($data);exit;
				M('borrow_baihang')->data($data)->add();
			}else{
				$data['send_time'] = date('Y-m-d',strtotime("+2 day",  time()));
				M('borrow_baihang')->where('borrow_id='.$borrow_id)->save($data);
			}
		}
		
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}



		function randReqID($param){
			return $param.date('YmdHis') . str_pad(mt_rand(1, 99999), 8, '0', STR_PAD_LEFT); //流水号
		}
		
		function toJson($array) {
			$this->arrayRecursive($array, 'urlencode', true);
			$json = json_encode($array);
			return urldecode($json);
		}

		function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
			static $recursive_counter = 0;
			if (++$recursive_counter > 1000) {
				die('possible deep recursion attack');

			}
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
				} else {
					$array[$key] = $function($value);
				}
				if ($apply_to_keys_also && is_string($key)) {
					$new_key = $function($key);
					if ($new_key != $key) {
						$array[$new_key] = $array[$key];
						unset($array[$key]);
					}
				}
			}
			$recursive_counter--;
		}
		
	}