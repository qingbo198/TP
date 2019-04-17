<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class GongXiangController extends Controller {
		//信息共享平台接口(首次报送)
		//截止到报送月月初的还款中标的数据
		public function first_sub(){
			$start_time = strtotime('2019-04-01 00:00:00');
			$end_time = strtotime('2019-04-30 23:59:59');
			$status['second_verify_time&borrow_status'] = array(array('lt',$end_time),array('eq',6),'_multi'=>true);
			//$status['bi.id'] = 1547;
			$list = M("lzh_borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as borrow_id,borrow_uid,zhaiquan_idcard,cell_phone,second_verify_time,deadline,borrow_money,repayment_type,zhaiquan_name,mi.real_name,mi.idcard")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($status)
				->limit(4)
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
				//print_r($result);die;
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
					
					."<br>";
			}
			echo $info;
		
		}
		
		
		
		
		
		
		
		
		
		
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}
		
	}