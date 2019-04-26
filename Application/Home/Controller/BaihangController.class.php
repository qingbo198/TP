<?php

namespace Home\Controller;

use Think\Controller;

header("Content-type: text/html; charset=utf-8");

class BaihangController extends Controller
{
	//贷款申请信息(增量数据)C1
	public function SendtoBaiHang_C1()
	{
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		//echo $start_time;die;
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点
		$status['second_verify_time'] = array('between', array($start_time, $end_time));
		
		$list_apply = M("lzh_borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id,zhaiquan_idcard,cell_phone,borrow_money,zhaiquan_name,real_name,idcard")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($status)
			->limit(4)
			->select();
		echo M()->getLastSql();
		$loanApplyInfo = "#loanApplyInfo" . "\r\n";
		foreach ($list_apply as $m => $n) {
			$apply['name'] = $n['real_name'];//姓名：只能为合法的中国姓名
			if (substr($n['idcard'], 17, 1) == 'x') {
				$apply['pid'] = substr($n['idcard'], 0, 17) . 'X';
			} else {
				$apply['pid'] = $n['idcard'];//身份证号码
			}
			$apply['mobile'] = $n['cell_phone'];//手机号码
			$apply['queryReason'] = 1;//查询原因 1：授信审批
			$apply['guaranteeType'] = 2;//贷款担保类型 2：抵押
			$apply['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
			$apply['customType'] = 99;//客户类型  99：人群未知
			$apply['applyAmount'] = $n['borrow_money'];//贷款申请金额
			$apply['homeAddress'] = '';//家庭地址
			$apply['homePhone'] = '';//家庭电话
			$apply['workName'] = '';//工作单位名称
			$apply['workAddress'] = '';//工作单位地址
			$apply['workPhone'] = '';//工作单位电话
			$apply['device'] = array('deviceType' => '', 'imei' => '', 'mac' => '', 'ipAddress' => '', 'osName' => '');//设备信息
			$loanApplyInfo .= $this->toJson($apply);
			//echo 'debug<br><pre>'; print_r($apply);
		}
		//echo $loanApplyInfo;
	}
	
	
	//百行D2接口报送贷款账户信息(增量数据)
	//T+1 隔天上报
	public function SendtoBaiHang_D2()
	{
		
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		//echo $start_time;die;
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点
		$start_time = 1530216651;
		$end_time = 1550216653;
		
		$where['second_verify_time'] = array('between', array($start_time, $end_time));
		$list = M('lzh_borrow_info bi')
			->order('second_verify_time')
			->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
			,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id,lz.borrow_type,repayment_type')
			->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
			->join('left join lzh_members as m on m.id = bi.borrow_uid')
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($where)
			->limit(2)
			->select();
		echo M()->getLastSql() . '<br>';
		//print_r($list);die();
		if (!empty($list)) {
			foreach ($list as $key => $value) {
				//接口数据///////////////////////////
				$parama['reqID'] = $value['borrow_id'] . "D2" . "U" . $value['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
				$parama['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
				$parama['uploadTs'] = date("Y-m-d\TH:i:s", $value['add_time']);//记录生成时间：本业务在机构业务系统发生的时间
				$parama['name'] = $value['real_name'];//借款人姓名
				if (substr($value['idcard'], 17, 1) == 'x') {
					$parama['pid'] = substr($value['idcard'], 0, 17) . 'X';
				} else {
					$parama['pid'] = $value['idcard'];//身份证号码
				}
				$parama['mobile'] = $value['cell_phone'];//手机号码
				$parama['loanID'] = $value['borrow_id'];//贷款编号
				$parama['originalLoan'] = null;//原贷款编号
				if ($value['borrow_type'] == 1) {
					$parama['guaranteeType'] = 2;//贷款担保类型 2 抵押
				} elseif ($value['borrow_type'] == 2) {
					$parama['guaranteeType'] = 3;//贷款担保类型 3 质押
				}
				$parama['loanPurpose'] = 1;//贷款用途
				$parama['applyDate'] = date("Y-m-d\TH:i:s", $value['add_time']);//贷款申请时间
				$parama['accountOpenDate'] = date("Y-m-d\TH:i:s", $value['first_verify_time']);//账户开立时间
				$parama['issueDate'] = date("Y-m-d\TH:i:s", $value['second_verify_time']);//贷款放款时间
				$parama['dueDate'] = date("Y-m-d", $value['deadline']);//贷款到期日期
				$parama['loanAmount'] = $value['borrow_money'];//贷款金额
				if ($value['repayment_type'] == 4) {
					$parama['totalTerm'] = $value['borrow_duration'];//还款总期数
				} elseif ($value['repayment_type'] == 5) {
					$parama['totalTerm'] = 1; //末期本息视为单期
				}
				$parama['targetRepayDateType'] = 2;//账单日类型
				$parama['termPeriod'] = -1;//每期还款周期
				//获取还款记录信息
				$detail = M('lzh_investor_detail');
				$result = $detail->where('borrow_id=' . $value['borrow_id'])->select();
				foreach ($result as $k => $v) {
					$data[] = date("Y-m-d", $v['deadline']);
				}
				$data = array_unique($data);
				$parama['targetRepayDateList'] = implode(",", $data);//账单日列表
				$parama['firstRepaymentDate'] = reset($data);//首次应还款日
				unset($data);
				$parama['gracePeriod'] = 7;//宽限期
				$parama['device'] = array('deviceType' => 2, 'imei' => null, 'mac' => null, 'ipAddress' => null, 'osName' => 6);//设备信息
				
				echo 'debug<br><pre>';
				print_r($parama);
				//echo $this->toJson($parama).'<br>';die;
			}
		} else {
			echo "无新增借款数据";
		}
		
		//echo 'debug<br><pre>'; //print_r($borrow_bhlist); var_dump($borrow_bhlist); exit;
	}
	
	
	//还款业务数据D3(增量数据)
	public function SendtoBaiHang_D3()
	{
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点
		
		$start_time = strtotime('2019-3-18 23:59:59');
		$end_time = strtotime('2019-3-20 00:00:00');
		
		$status['repayment_time'] = array('between', array($start_time, $end_time));
		$list_repayment = M('lzh_investor_detail lid')
			->DISTINCT(true)
			->field('lid.borrow_id')
			->where($status)->select();
		//echo M()->getLastSql();echo "<br>";die();
		if (!empty($list_repayment)) {
			foreach ($list_repayment as $k => $v) {
				$repay_array[] = $v['borrow_id'];
			}
			$status2['bi.id'] = array('in', $repay_array);
			//$status2['bi.id'] = 1825;
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field('bi.id as bid, mi.real_name, mi.cell_phone, 	
				mi.idcard,bi.is_advanced,bi.is_prepayment,bi.repayment_type,bi.borrow_duration,bi.borrow_uid,bi.borrow_status,bi.borrow_money,bi.borrow_status')
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->where($status2)
				->select();
			//echo M()->getLastSql(); die();
			foreach ($list as $key => $value) {
				$detail = M('lzh_investor_detail');
				$result = $detail->where('borrow_id=' . $value['bid'])->select();
				//若当期发生提前还款（先息后本）
				$arr1 = array();
				$arr2 = array();
				foreach ($result as $m => $n) {
					$arr1[date('Y-m-d', $n['repayment_time'])]['total'] = $n['total'];
				}
				unset($arr1['1970-01-01']);//删除还未还款的期数
				$real_total = count($arr1);
				
				//echo 'debug<br><pre>'; print_r($arr1);echo $real_total; exit;
				foreach ($result as $kkk => $vvv) {
					//先息后本提前还款
					//处理每期计划还款金额和实际还款金额数据
					if (($value['repayment_type'] == 4 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 4 && $value['is_prepayment'] != 0)) {
						if ($vvv['sort_order'] <= $real_total) {
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['total'] = $vvv['total'];
						}
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					} else {
						//非先息后本提前还款
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['total'] = $vvv['total'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					}
				}
				unset($arr2['1970-01-01']);
				
				//echo 'debug<br><pre>'; print_r($arr2); exit;
				
				foreach ($arr2 as $k => $v) {
					if (($v['repayment_time'] > $start_time) && ($v['repayment_time'] < $end_time)) {
						$parama['reqID'] = $value['bid'] . "D3" . "U" . $value['borrow_uid'] . "D" . $v['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
						$parama['opCode'] = 'A';//操作代码
						$parama['uploadTs'] = date('Y-m-d\TH:i:s', $v['repayment_time']);//记录生成时间:本业务在机构业务系统发生的时间；
						$parama['loanId'] = $value['bid'];//贷款编号
						$parama['name'] = $value['real_name'];//借款人姓名
						if (substr($value['idcard'], 17, 1) == 'x') {
							$parama['pid'] = substr($value['idcard'], 0, 17) . 'X';
						} else {
							$parama['pid'] = $value['idcard'];//身份证号码
						}
						$parama['mobile'] = $value['cell_phone'];//手机号码
						$parama['termNo'] = $v['sort_order'];//当期还款期数
						$parama['termStatus'] = 'normal';//本期还款状态
						$parama['targetRepaymentDate'] = date('Y-m-d', $v['deadline']);//本期应还款日
						$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s', $v['repayment_time']);//实际还款时间
						$parama['plannedPayment'] = $v['plan_capital'] + $v['plan_interest'];//本期计划应还款金额
						$parama['targetRepayment'] = $v['receive_capital'] + $v['receive_interest'];//无逾期时为本期还款金额
						$parama['realRepayment'] = $v['receive_capital'] + $v['receive_interest'];//本次还款金额
						$parama['overdueStatus'] = '';//当前逾期天数
						$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s', strtotime("+1 hour", $v['repayment_time']));//本期还款状态确认时间
						$parama['overdueAmount'] = 0;//当前逾期总额
						if ($value['repayment_type'] == 5) {
							$parama['remainingAmount'] = 0;
						} elseif ($value['repayment_type'] == 4) { //是否是先息后本
							if ($value['borrow_status'] == 6) { //判断是不是还款中标的
								$parama['remainingAmount'] = $value['borrow_money'];//贷款余额：未还金额
							} elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9) {
								$parama['remainingAmount'] = ($v['sort_order'] == $real_total) ? 0 : $value['borrow_money'];
							}
						}
						if ($value['repayment_type'] == 5) {
							$parama['loanStatus'] = 3;//本笔贷款状态
						} elseif ($value['repayment_type'] == 4) {
							if ($value['borrow_status'] == 6) { //判断是不是还款中标的
								$parama['loanStatus'] = 1;
							} elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9) {
								$parama['loanStatus'] = ($v['sort_order'] == $real_total) ? 3 : 1;
							}
						}
					}
				}
				unset($arr1, $arr2);
				
				echo 'debug<br><pre>';
				print_r($parama);
				
				//echo $this->toJson($parama).'<br>';die;
			}
			
			
		} else {
			echo "无还款数据";
			die;
		}
		
		
	}
	
	
	//百行征信存量数据C1、D2、D3
	public function baihang_stock()
	{
		if (false) {
			//C1贷款申请信息接口
			// $status['second_verify_time&borrow_status'] = array(array('gt',strtotime('2016-08-24 23:59:59')),array('in',array('7','9')),'_multi'=>true);
			// $status['cell_phone'] = array('neq','');
			$status['bi.id'] = array('in', array('1644', '1650', '1054', '1059', '1226', '1227', '1003', '1021', '1818', '1821'));
			
			$list_apply = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as borrow_id,borrow_uid,zhaiquan_idcard,cell_phone,borrow_money,zhaiquan_name,mi.real_name,mi.idcard")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); //die;
			$loanApplyInfo = "#loanApplyInfo" . "\r\n";
			
			
			$regx = "/^[\u4E00-\u9FA5\uf900-\ufa2d·s]{2,20}$/";
			foreach ($list_apply as $m => $n) {
				// if(!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$n['real_name'])){
				// 	$res[] = $n['borrow_uid'];
				// }
				$apply['reqID'] = $n['borrow_id'] . "C1" . "U" . $n['borrow_uid'];//记录唯一标识
				$apply['uploadTs'] = '';//记录生成时间  非必填
				$apply['name'] = $n['real_name'];//姓名：只能为合法的中国姓名
				$apply['pid'] = $n['idcard'];//身份证号码
				$apply['mobile'] = $n['cell_phone'];//手机号码
				$apply['queryReason'] = 1;//查询原因 1：授信审批
				$apply['guaranteeType'] = 2;//贷款担保类型 2：抵押
				$apply['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
				$apply['customType'] = 99;//客户类型  99：人群未知
				$apply['applyAmount'] = $n['borrow_money'];//贷款申请金额
				$apply['homeAddress'] = '';//家庭地址
				$apply['homePhone'] = '';//家庭电话
				$apply['workName'] = '';//工作单位名称
				$apply['workAddress'] = '';//工作单位地址
				$apply['workPhone'] = '';//工作单位电话
				$apply['device'] = array('deviceType' => '', 'imei' => '', 'mac' => '', 'ipAddress' => '', 'osName' => '');//设备信息
				$loanApplyInfo .= $this->toJson($apply) . "\r\n";
				//echo 'debug<br><pre>'; print_r($apply);
			}
			echo $loanApplyInfo;
			//echo 'debug<br><pre>'; print_r($res);
		}
		
		
		//die;
		
		if (true) {
			//D2贷款账户信息
			$map['second_verify_time&borrow_status&repayment_type&borrow_duration'] = array(array('gt', strtotime('2016-08-24 23:59:59')), array('in', array('6', '7', '9')), array('eq', '5'), array('gt', 12), '_multi' => true);
			$map['cell_phone'] = array('neq', '');
			//$map['lz.type'] = 1;//自然人
			$list_account = M('lzh_borrow_info bi')
				->order('second_verify_time')
				->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
					,bi.deadline,bi.borrow_money,repayment_type,bi.borrow_duration,borrow_uid,bi.id borrow_id,lz.borrow_type')
				->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
				->join('left join lzh_members as m on m.id = bi.borrow_uid')
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($map)
				//->limit(4)
				//->count();
				//echo $list_account;die;
				->select();
			echo M()->getLastSql() . '<br>';
			die;
			$singleLoanAccountInfo = "#singleLoanAccountInfo" . "\r\n";//D2数据头
			if (!empty($list_account)) {
				foreach ($list_account as $key => $value) {
					//接口数据///////////////////////////
					$account['reqID'] = $value['borrow_id'] . "D2" . "U" . $value['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$account['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
					$account['uploadTs'] = date("Y-m-d\TH:i:s", $value['add_time']);//记录生成时间
					$account['name'] = $value['real_name'];//借款人姓名
					if (substr($value['idcard'], 17, 1) == 'x') {
						$account['pid'] = substr($value['idcard'], 0, 17) . 'X';
					} else {
						$account['pid'] = $value['idcard'];//身份证号码
					}
					$account['mobile'] = $value['cell_phone'];//手机号码
					$account['loanID'] = $value['borrow_id'];//贷款编号
					$account['originalLoan'] = null;//原贷款编号
					if ($value['borrow_type'] == 1) {
						$account['guaranteeType'] = 2;//贷款担保类型 2 抵押
					} elseif ($value['borrow_type'] == 2) {
						$account['guaranteeType'] = 3;//贷款担保类型 3 质押
					}
					$account['loanPurpose'] = 1;//贷款用途
					$account['applyDate'] = date("Y-m-d\TH:i:s", $value['add_time']);//贷款申请时间
					$account['accountOpenDate'] = date("Y-m-d\TH:i:s", $value['first_verify_time']);//账户开立时间
					$account['issueDate'] = date("Y-m-d\TH:i:s", $value['second_verify_time']);//贷款放款时间
					$account['dueDate'] = date("Y-m-d", $value['deadline']);//贷款到期日期
					$account['loanAmount'] = $value['borrow_money'];//贷款到期日期
					if ($value['repayment_type'] == 4) {
						$account['totalTerm'] = $value['borrow_duration'];//还款总期数
					} elseif ($value['repayment_type'] == 5) {
						$account['totalTerm'] = 1; //末期本息视为单期
					}
					$account['targetRepayDateType'] = 2;//账单日类型
					$account['termPeriod'] = -1;//每期还款周期
					//获取计划还款信息
					$detail = M('lzh_investor_detail');
					$result = $detail->where('borrow_id=' . $value['borrow_id'])->select();
					foreach ($result as $k => $v) {
						$data[] = date("Y-m-d", $v['deadline']);
					}
					$data = array_unique($data);
					$account['targetRepayDateList'] = implode(",", $data);//账单日列表
					$account['firstRepaymentDate'] = reset($data);//首次应还款日
					unset($data);
					$account['gracePeriod'] = 7;//宽限期
					$account['device'] = array('deviceType' => 2, 'imei' => null, 'mac' => null, 'ipAddress' => null, 'osName' => 6);//设备信息
					
					echo 'debug<br><pre>';
					print_r($account);
					$singleLoanAccountInfo .= $this->toJson($account) . "\r\n";
					
				}
				//echo $singleLoanAccountInfo;
				
			} else {
				echo "无新增贷款数据";
			}
		}
		//die;
		
		if (false) {
			//D3贷款贷后还款数据
			// $where['second_verify_time&borrow_status'] = array(array('gt',strtotime('2016-08-24 23:59:59')),array('in',array('7','9')),'_multi'=>true);
			// $where['cell_phone'] = array('neq','');
			// $where['lz.type'] = 1;
			$where['bi.id'] = 1522;//1240
			//$where['bi.id'] = array('in',array('1227','1534'));
			
			//获取以上标的 还款明细  id.repayment_time, id.borrow_id, id.investor_uid,, id.sort_order, id.total, id.status, id.deadline, id.capital, id.interest, id.receive_capital, id.receive_interest
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as bid,bi.is_advanced,has_pay,is_prepayment,bi.borrow_uid,borrow_status,bi.borrow_money,repayment_type,lz.zhaiquan_name,real_name,lz.zhaiquan_idcard,mi.cell_phone")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($where)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); exit;
			$singleLoanRepayInfo = "#singleLoanRepayInfo" . "\r\n";
			foreach ($list as $k => $v) {
				$result = M('lzh_investor_detail')->where('borrow_id=' . $v['bid'])->select();
				$arr1 = array();
				$arr2 = array();
				foreach ($result as $kk => $vv) {
					$arr1[date('Y-m-d', $vv['repayment_time'])]['total'] = $vv['total'];
				}
				//echo 'debug<br><pre>';print_r($arr1);die;
				unset($arr1['1970-01-01']);//删除还未还款的期数
				$real_total = count($arr1);//实际总共还款期数
				//echo $real_total;die;
				foreach ($result as $kkk => $vvv) {
					//先息后本提前还款
					//处理每期计划还款金额和实际还款金额数据
					if (($v['repayment_type'] == 4 && $v['is_advanced'] != 0) || ($v['repayment_type'] == 4 && $v['is_prepayment'] != 0)) {
						if ($vvv['sort_order'] <= $real_total) {
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
							$arr2[date('Y-m-d', $vvv['repayment_time'])]['total'] = $vvv['total'];
						}
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					} else {
						//非先息后本提前还款
						//echo 111;die;
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_interest'] += $vvv['interest'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = $vvv['repayment_time'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['deadline'] = $vvv['deadline'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['total'] = $vvv['total'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['plan_capital'] += $vvv['capital'];
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					}
				}
				//print_r($arr2);die;
				unset($arr2['1970-01-01']);//删除还未还款的期数
				//组装接口数据
				foreach ($arr2 as $kkkk => $vvvv) {
					$parama['reqID'] = $v['bid'] . "D3" . "U" . $v['borrow_uid'] . "D" . $vvvv['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';//操作代码   A- “新增数据”，M-“修改数据”，D-“删除数据
					$parama['uploadTs'] = date('Y-m-d\TH:i:s', $vvvv['repayment_time']);//记录生成时间:
					$parama['loanId'] = $v['bid'];//贷款编号
					$parama['name'] = $v['real_name'];//姓名
					if (substr($v['zhaiquan_idcard'], 17, 1) == 'x') {
						$parama['pid'] = substr($v['zhaiquan_idcard'], 0, 17) . 'X';
					} else {
						$parama['pid'] = $v['zhaiquan_idcard'];//身份证号码
					}
					$parama['mobile'] = $v['cell_phone'];//手机号码
					$parama['termNo'] = $vvvv['sort_order'];//当前还款期数
					$parama['termStatus'] = 'normal';//本期还款状态
					$parama['targetRepaymentDate'] = date('Y-m-d', $vvvv['deadline']);//本期应还款日
					$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s', $vvvv['repayment_time']);//实际还款时间
					$parama['plannedPayment'] = $vvvv['plan_capital'] + $vvvv['plan_interest'];//本期计划应还款金额
					$parama['targetRepayment'] = $vvvv['receive_capital'] + $vvvv['receive_interest'];//本期剩余应还款金额
					$parama['realRepayment'] = $vvvv['receive_capital'] + $vvvv['receive_interest'];//本次还款金额
					$parama['overdueStatus'] = '';//当前逾期天数
					$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s', strtotime("+1 hour", $vvvv['repayment_time']));//本笔还款状态确认时间
					$parama['overdueAmount'] = 0;//当前逾期总额
					if ($v['repayment_type'] == 5) {
						$parama['remainingAmount'] = 0;
					} elseif ($v['repayment_type'] == 4) { //是否是先息后本
						if ($v['borrow_status'] == 6) { //判断是不是还款中标的
							$parama['remainingAmount'] = $v['borrow_money'];//贷款余额：未还金额
						} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
							$parama['remainingAmount'] = ($vvvv['sort_order'] == $real_total) ? 0 : $v['borrow_money'];
						}
					}
					if ($v['repayment_type'] == 5) {
						$parama['loanStatus'] = 3;
					} elseif ($v['repayment_type'] == 4) {
						if ($v['borrow_status'] == 6) { //判断是不是还款中标的
							$parama['loanStatus'] = 1;
						} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
							$parama['loanStatus'] = ($vvvv['sort_order'] == $real_total) ? 3 : 1;
						}
					}
					
					//unset($real_total);
					echo 'debug<br><pre>';
					print_r($parama);
					$singleLoanRepayInfo .= $this->toJson($parama) . "\r\n";
				}
				unset($arr1, $arr2);
			}
			//echo $singleLoanRepayInfo;
		}
		
		$all = $loanApplyInfo . $singleLoanAccountInfo . $singleLoanRepayInfo;
		//echo $all;
	}
	
	
	//查询末期本息提前还款利息不对的标的
	
	
	function getFloatValue($f, $len)
	{
		return number_format($f, $len, '.', '');
	}
	
	
	function randReqID($param)
	{
		return $param . date('YmdHis') . str_pad(mt_rand(1, 99999), 8, '0', STR_PAD_LEFT); //流水号
	}
	
	function toJson($array)
	{
		$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
	}
	
	function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
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