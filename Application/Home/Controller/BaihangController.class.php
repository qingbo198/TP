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
			$start_time = 1530216651;
			$end_time = 1550216653;

			$where['second_verify_time'] = array('between',array($start_time,$end_time));
			$msg = M('lzh_borrow_info bi');
			$list = $msg->order('bi.add_time')
			->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
			,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id')
			->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
			->join('left join lzh_members as m on m.id = bi.borrow_uid')
			->where($where)
			->limit(2)
			->select();
			echo $msg->getLastSql().'<br>';
			//print_r($list);die();
			if(!empty($list)){
				foreach ($list as $key => $value) {
					//接口数据///////////////////////////
					$parama['reqID'] = $this->randReqID($value['borrow_uid']);//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
					$parama['uploadTs'] = date("Y-m-d\TH:i:s",time());//记录生成时间
					$parama['name'] = $value['real_name'];//借款人姓名
					if(substr($value['idcard'],17,1) == 'x'){
						$parama['pid'] = substr($value['idcard'],0,17).'X';
					}else{
						$parama['pid'] = $value['idcard'];//身份证号码
					}
					$parama['mobile'] = $value['cell_phone'];//手机号码
					$parama['loanID'] = $value['borrow_id'];//贷款编号
					$parama['originalLoan'] = null;//原贷款编号
					$parama['guaranteeType'] = 2;//贷款担保类型
					$parama['loanPurpose'] = 1;//贷款用途
					$parama['applyDate'] = date("Y-m-d\TH:i:s",$value['add_time']);//贷款申请时间
					$parama['accountOpenDate'] = date("Y-m-d\TH:i:s",$value['first_verify_time']);//账户开立时间
					$parama['issueDate'] = date("Y-m-d\TH:i:s",$value['second_verify_time']);//贷款放款时间
					$parama['dueDate'] = date("Y-m-d",$value['deadline']);//贷款到期日期
					$parama['loanAmount'] = $value['borrow_money'];//贷款到期日期
					$parama['totalTerm'] = $value['borrow_duration'];//还款总期数
					$parama['targetRepayDateType'] = 2;//账单日类型
					$parama['termPeriod'] = -1;//每期还款周期
					//获取还款记录信息
					$detail = M('lzh_investor_detail');
					$result = $detail->where('borrow_id='.$value['borrow_id'])->select();
					foreach($result as $k=>$v){
						$data[] = date("Y-m-d",$v['deadline']);
					}
					$data = array_unique($data);
					$parama['targetRepayDateList'] = implode(",",$data);//账单日列表
					$parama['firstRepaymentDate'] = reset($data);//首次应还款日
					unset($data);
					$parama['gracePeriod'] = 7;//宽限期
					$parama['device'] = array('deviceType'=> 2,'imei'=>null,'mac'=>null,'ipAddress'=>null, 'osName'=>6);//设备信息
					
					echo 'debug<br><pre>'; print_r($parama);
					//echo $this->toJson($parama).'<br>';die;
				}
			}else{
				echo "无新增借款数据";
			}
				
			//echo 'debug<br><pre>'; //print_r($borrow_bhlist); var_dump($borrow_bhlist); exit;	
			}
		
		
		
		//还款业务数据D3(增量数据)
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
					$parama['opCode'] = 'A';//操作代码
					$parama['uploadTs'] = date('Y-m-d\TH:i:s',time());//记录生成时间
					$parama['loanId'] = $value['bid'];//贷款编号
					$parama['name'] = $value['real_name'];//借款人姓名
					if(substr($value['idcard'],17,1) == 'x'){
						$parama['pid'] = substr($value['idcard'],0,17).'X';
					}else{
						$parama['pid'] = $value['idcard'];//身份证号码
					}
					$parama['mobile'] = $value['cell_phone'];//手机号码
					$parama['termNo'] = $value['has_pay'];//当期还款期数
					$parama['termStatus'] = 'normal';//本期还款状态

					$detail = M('lzh_investor_detail');
					$result = $detail->where('borrow_id='.$value['bid'])->select();
					foreach($result as $k=>$v){
						if($v['sort_order'] == $value['has_pay']){
							$parama['targetRepaymentDate'] = date('Y-m-d',$v['deadline']);//本期应还款日
							$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s',$v['repayment_time']);//实际还款时间
							$parama['plannedPayment'] += $v['capital'];//本期计划应还款金额,只包含本金
							$parama['targetRepayment'] = $v['capital'];//无逾期时为本期还款金额
							$parama['realRepayment'] += $v['receive_capital'];//本次还款金额
							$parama['overdueStatus'] = '';//当前逾期天数
							$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s',strtotime("-1 hour",  time()));//本期还款状态确认时间
							$parama['overdueAmount'] = 0;//当前逾期总额
							if($value['repayment_type'] == 5){
								$parama['remainingAmount'] = 0;
							}elseif ($value['repayment_type'] == 4){
								if($value['has_pay'] == $value['borrow_duration']){
									$parama['remainingAmount'] = 0;
								}else{
									$parama['remainingAmount'] = $value['borrow_money'];//贷款余额：未还金额
								}
							}
						}
					}
					if($value['repayment_type'] == 5){
						$parama['loanStatus'] = ($value['has_pay'] == 1)? 3 : 1;//本笔贷款状态
					}else{
						$parama['loanStatus'] = ($value['has_pay'] == $value['borrow_duration'])? 3 : 1;
					}

					echo $this->toJson($parama).'<br>';die;
				}


			}else{
				echo "无还款数据";die;
			}
			

			
		}
		
		
		//百行征信存量数据C1、D2、D3
		public function baihang_stock()
		{
			if(true){
				//D2贷款账户信息
				$map['second_verify_time&borrow_status'] = array(array('gt','1472054399'),array('in',array('7','9')),'_multi'=>true);
				$list_account = M('lzh_borrow_info bi')
					->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
					,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id')
					->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
					->join('left join lzh_members as m on m.id = bi.borrow_uid')
					->where($map)
					->limit(4)
					->select();
				echo M()->getLastSql().'<br>';
				$singleLoanAccountInfo = "#singleLoanAccountInfo"."\r\n";//D2数据头
				if(!empty($list_account)){
					foreach ($list_account as $key => $value) {
						//接口数据///////////////////////////
						$account['reqID'] = $value['borrow_id']."D2"."U".$value['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
						$account['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
						$account['uploadTs'] = date("Y-m-d\TH:i:s",time());//记录生成时间
						$account['name'] = $value['real_name'];//借款人姓名
						if(substr($value['idcard'],17,1) == 'x'){
							$account['pid'] = substr($value['idcard'],0,17).'X';
						}else{
							$account['pid'] = $value['idcard'];//身份证号码
						}
						$account['mobile'] = $value['cell_phone'];//手机号码
						$account['loanID'] = $value['borrow_id'];//贷款编号
						$account['originalLoan'] = null;//原贷款编号
						$account['guaranteeType'] = 2;//贷款担保类型
						$account['loanPurpose'] = 1;//贷款用途
						$account['applyDate'] = date("Y-m-d\TH:i:s",$value['add_time']);//贷款申请时间
						$account['accountOpenDate'] = date("Y-m-d\TH:i:s",$value['first_verify_time']);//账户开立时间
						$account['issueDate'] = date("Y-m-d\TH:i:s",$value['second_verify_time']);//贷款放款时间
						$account['dueDate'] = date("Y-m-d",$value['deadline']);//贷款到期日期
						$account['loanAmount'] = $value['borrow_money'];//贷款到期日期
						$account['totalTerm'] = $value['borrow_duration'];//还款总期数
						$account['targetRepayDateType'] = 2;//账单日类型
						$account['termPeriod'] = -1;//每期还款周期
						//获取还款记录信息
						$detail = M('lzh_investor_detail');
						$result = $detail->where('borrow_id='.$value['borrow_id'])->select();
						foreach($result as $k=>$v){
							$data[] = date("Y-m-d",$v['deadline']);
						}
						$data = array_unique($data);
						$account['targetRepayDateList'] = implode(",",$data);//账单日列表
						$account['firstRepaymentDate'] = reset($data);//首次应还款日
						unset($data);
						$account['gracePeriod'] = 7;//宽限期
						$account['device'] = array('deviceType'=> 2,'imei'=>null,'mac'=>null,'ipAddress'=>null, 'osName'=>6);//设备信息
						
						echo 'debug<br><pre>'; print_r($account);
						//echo $this->toJson($parama).'<br>';die;
						$singleLoanAccountInfo .= $this->toJson($account)."\r\n";
						
					}
					//echo $singleLoanAccountInfo;
					die;//D2数据
				}else{
					echo "无新增贷款数据";
				}
			}
			
			
			
			//D3贷款贷后还款数据
			$where['second_verify_time&borrow_status'] = array(array('gt','1472054399'),array('in',array('7','9')),'_multi'=>true);
			//$where['bi.id'] = 1534;
			//$where['bi.id'] = array('in',array('1227','1534'));
			
			//获取以上标的 还款明细  id.repayment_time, id.borrow_id, id.investor_uid,, id.sort_order, id.total, id.status, id.deadline, id.capital, id.interest, id.receive_capital, id.receive_interest
			$list = M("lzh_borrow_info bi")
				->field("bi.id as bid,bi.is_advanced,has_pay,is_prepayment,bi.borrow_uid,bi.borrow_money,repayment_type,lz.zhaiquan_name,real_name,lz.zhaiquan_idcard,mi.cell_phone")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($where)
				->limit(4)
				->select();
			//echo M()->getLastSql(); exit;
			$singleLoanRepayInfo = "#singleLoanRepayInfo"."\r\n";
			$arr1 = array();
			$arr2 = array();
			foreach ($list as $k=>$v){
				$result = M('lzh_investor_detail')->where('borrow_id='.$v['bid'])->select();
				foreach ($result as $kk=>$vv){
					$arr1[date('Y-m-d',$vv['repayment_time'])]['total'] = $vv['total'];
				}
				//echo 'debug<br><pre>';print_r($arr1);die;
				$real_total = count($arr1);//实际总共还款期数
				//echo $real_total;die;
				foreach($result as $kkk=>$vvv){
					//先息后本提前还款
					//处理每期计划还款金额和实际还款金额数据
					if(($v['repayment_type'] == 4 && $v['is_advanced'] != 0) || ($v['repayment_type'] == 4 && $v['is_prepayment'] != 0)){
						if($vvv['sort_order'] <= $real_total){
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
							$arr2[date('Y-m-d',$vvv['repayment_time'])]['total'] = $vvv['total'];
						}
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					}else{
						//非先息后本提前还款
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['total'] = $vvv['total'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					}
				}
				//组装接口数据
				foreach ($arr2 as $kkkk=>$vvvv){
					$parama['reqID'] = $v['bid']."D3".$v['borrow_uid']."D".$vvvv['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';//操作代码   A- “新增数据”，M-“修改数据”，D-“删除数据
					$parama['uploadTs'] = date('Y-m-d\TH:i:s',time());//记录生成时间:
					$parama['loanId'] = $v['bid'];//贷款编号
					$parama['name'] = $v['real_name'];//姓名
					if(substr($v['zhaiquan_idcard'],17,1) == 'x'){
						$parama['pid'] = substr($v['zhaiquan_idcard'],0,17).'X';
					}else{
						$parama['pid'] = $v['zhaiquan_idcard'];//身份证号码
					}
					$parama['mobile'] = $v['cell_phone'];//手机号码
					$parama['termNo'] = $vvvv['sort_order'];//当前还款期数
					$parama['termStatus'] = 'normal';//本期还款状态
					$parama['targetRepaymentDate'] = date('Y-m-d',$vvvv['deadline']);//本期应还款日
					$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s',$vvvv['repayment_time']);//实际还款时间
					$parama['plannedPayment'] = $vvvv['plan_capital']+$vvvv['plan_interest'];//本期计划应还款金额
					$parama['targetRepayment'] = $vvvv['plan_capital']+$vvvv['plan_interest'];//本期剩余应还款金额
					$parama['realRepayment'] = $vvvv['receive_capital']+$vvvv['receive_interest'];//本次还款金额
					$parama['overdueStatus'] = '';//当前逾期天数
					$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s',strtotime("-1 hour",  time()));//本笔还款状态确认时间
					$parama['overdueAmount'] = 0;//当前逾期总额
					if($v['repayment_type'] == 5){
						$parama['remainingAmount'] = 0;
					}elseif ($v['repayment_type'] == 4){
						if($vvvv['sort_order'] == $real_total){
							$parama['remainingAmount'] = 0;
						}else{
							$parama['remainingAmount'] = $v['borrow_money'];//贷款剩余额度
						}
					}
					if($v['repayment_type'] == 5){
						$parama['loanStatus'] = 3;
					}else{
						$parama['loanStatus'] = ($vvvv['sort_order'] == $real_total)? 3 : 1;//本笔贷款状态
					}
					
					//unset($real_total);
					//echo 'debug<br><pre>';print_r($parama);
					$singleLoanRepayInfo .= $this->toJson($parama)."\r\n";
					unset($arr1,$arr2);
				}
			}
			echo $singleLoanRepayInfo;
			$all = $singleLoanAccountInfo.$singleLoanRepayInfo;
			//echo $all;
			
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