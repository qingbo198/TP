<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class BaihangController extends Controller {
		
		//百行D2接口报送贷款账户信息
		//T+1
		//循环执行任务方法
		public function SendtoBaiHang_D2(){
		
			$start = date("Y-m-d",(time()-86400));
			$start_time = strtotime($start)-1;//前天23:59：59开始时间
			$end = date("Y-m-d",time());
			$end_time = strtotime($end);//前一天上报数据截止时间今天零点
			
			
			$where['second_verify_time'] = array('between',array($start_time,$end_time));
			
			$list = M('lzh_borrow_info')->field()->where($where)->select();
			
			///////////////////
			
			
			//echo 'debug<br><pre>'; //print_r($borrow_bhlist); var_dump($borrow_bhlist); exit;
			
			
			$biao_id = intval($_GET['biao_id']);
			$where['bi.id'] = 1492;//$biao_id;
			$msg = M('lzh_members m');
			//$where['jshbank_status'] = array(array('EQ',1),array('EQ',2),'OR');
			$list = $msg->order('bi.add_time')
			->field('real_name,add_time,real_name,mi.idcard,cell_phone,first_verify_time,m.reg_time,bi.second_verify_time
			,bi.deadline,bi.borrow_money,bi.borrow_duration')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->find();
			//echo $msg->getLastSql().'<br>';die;
			//print_r($list);die();
			$reqID = M('lzh_borrow_baihang')->where('borrow_id='.$biao_id)->find();
			
			
			
			//接口数据///////////////////////////
			$parama['reqID'] = $reqID['request_id'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
			$parama['opCode'] = 'A';
			$parama['uploadTs'] = date("Y-m-d\TH:i:s",time());//记录生成时间
			$parama['name'] = $list['real_name'];//借款人姓名
			if(substr($list['idcard'],17,1) == 'x'){
				$parama['pid'] = substr($list['idcard'],0,17).'X';
			}else{
				$parama['pid'] = $list['idcard'];
			}
			$parama['mobile'] = $list['cell_phone'];
			$parama['loanID'] = $biao_id;
			$parama['originalLoan'] = null;
			$parama['guaranteeType'] = 2;
			$parama['loanPurpose'] = 1;
			$parama['applyDate'] = date("Y-m-d\TH:i:s",$list['add_time']);
			$parama['accountOpenDate'] = date("Y-m-d\TH:i:s",$list['first_verify_time']);
			$parama['issueDate'] = date("Y-m-d\TH:i:s",$list['second_verify_time']);
			$parama['dueDate'] = date("Y-m-d",$list['deadline']);
			$parama['loanAmount'] = $list['borrow_money'];
			$parama['totalTerm'] = $list['borrow_duration'];
			$parama['targetRepayDateType'] = 2;
			$parama['termPeriod'] = -1;
			//获取还款记录信息
			$detail = M('lzh_investor_detail');
			$result = $detail->where('borrow_id='.$biao_id)->select();
			foreach($result as $k=>$v){
				$data[] = date("Y-m-d",$v['deadline']);
			}
			$data = array_unique($data);
			$parama['targetRepayDateList'] = implode(",",$data);
			$parama['firstRepaymentDate'] = reset($data);
			unset($data);
			$parama['gracePeriod'] = 3;
			$parama['device'] = array('deviceType'=> 2,'imei'=>null,'mac'=>null,'ipAddress'=>null, 'osName'=>6);
			//接口数据end/////////////
			
			//数据入库lzh_borrowadd_baihang
			// $data['borrow_id'] = $biao_id;
			// $data['status'] = 0;
			// $data['send_time'] = date('Y-m-d',strtotime("+1 day",  time()));
			// M('borrowadd_baihang')->data($data)->add();
			// print_r($data);echo '<br>';
			
			// $parama = $this->JSON($parama);
			//  echo $parama;die;
			
			echo 'debug<br><pre>'; print_r($parama); exit;
			
		}
		
		
		
		public function SendtoBaiHang_D3(){
			$biao_id = $_GET['biao_id'];
			//获取正在还款及已完成的 存管标的信息
			$where['id'] = $biao_id;
			$where['is_jshbank'] = 1;
			$borrow_list = M("borrow_info")
				->field("id,borrow_name,borrow_uid")
				->where($where)
				->find();
			
			if(empty($borrow_list)) { echo 'NO data'; exit;}
			
			$borrow_id_arr = array();
			foreach ($borrow_list as $key => $value) {
				$borrow_id_arr[] = $value['id'];
			}
			
			//$map['id.borrow_id'] = array('in', $borrow_id_arr);
			$map['bi.id'] = $biao_id;
			
			//获取以上标的 还款明细
			$list = M("borrow_info bi")
				->field('bi.id as bid, mj.username, mj.mobile, mj.idcode,bi.has_pay,bi.repayment_type,bi.borrow_duration,bi.borrow_money')
				//->field("id.repayment_time, id.borrow_id, id.investor_uid, id.borrow_uid,bi.has_pay,id.sort_order, id.total, id.status, id.deadline, id.capital, id.interest, id.receive_capital, id.receive_interest, mj.username, mj.mobile, mj.idcode")
				->join("lzh_member_jshbank mj on mj.uid  = bi.borrow_uid")
				//->join("lzh_borrow_info bi on bi.id = id.borrow_id")
				->where($map)
				->find();
			// echo M()->getLastSql(); exit;
			
			$expireDays = ($value['deadline'] - $value['repayment_time'])/(24 *3600);
			$parama['reqID'] = $this->randReqID($biao_id);//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
			$parama['opCode'] = 'A';
			$parama['uploadTs'] = date('Y-m-d\TH:i:s',time());
			$parama['loanId'] = $list['bid'];
			$parama['name'] = $list['username'];
			if(substr($list['idcode'],17,1) == 'x'){
				$parama['pid'] = substr($list['idcode'],0,17).'X';
			}else{
				$parama['pid'] = $list['idcode'];
			}
			$parama['mobile'] = $list['mobile'];
			$parama['termNo'] = $list['has_pay'];
			$parama['termStatus'] = 'normal';
			$detail = M('investor_detail');
			$result = $detail->where('borrow_id='.$biao_id)->select();
			foreach($result as $k=>$v){
				if($v['sort_order'] == $list['has_pay']){
					$parama['targetRepaymentDate'] = date('Y-m-d',$v['deadline']);
					$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s',$v['repayment_time']);
					$parama['plannedPayment'] += $v['capital'] + $v['interest'];
					$parama['targetRepayment'] = 0;
					$parama['realRepayment'] += $v['capital'] + $v['interest'];
					$parama['overdueStatus'] = '';
					$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s',strtotime("-1 hour",  time()));;
					$parama['overdueAmount'] = 0;
					if($list['repayment_type'] == 5){
						$parama['remainingAmount'] = 0;
					}elseif ($list['repayment_type'] == 4){
						if($list['has_pay'] == $list['borrow_duration']){
							$parama['remainingAmount'] = 0;
						}else{
							$parama['remainingAmount'] = $list['borrow_money'];//贷款剩余额度
						}
					}
				}
			}
			//根据还款方式判断本笔贷款状态  4:先息后本; 5:末期本息;
			if($list['repayment_type'] == 5){
				$parama['loanStatus'] = ($list['has_pay'] == 1)? 3 : 1;
			}else{
				$parama['loanStatus'] = ($list['has_pay'] == $list['borrow_duration'])? 3 : 1;
			}
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
		
		
		
	}