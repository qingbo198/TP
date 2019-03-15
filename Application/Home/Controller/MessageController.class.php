<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class MessageController extends Controller {
		//末期本息提前还款时  还款本金和利息不对的标的；
		public function index()
		{
			set_time_limit(0);
			$where['second_verify_time&borrow_status&is_advanced&repayment_type'] = array(array('gt','1472054399'),7,1,5,'_multi'=>true);
			$list = M('lzh_borrow_info bi')->where($where)->select();
			echo M('lzh_borrow_info bi')->getLastSql();echo "<hr>";
			//echo 'debug<br><pre>'; print_r($list); exit;
			$arr1 = array();
			foreach ($list as $key=>$value){
				$result = M('lzh_investor_detail')->where('borrow_id='.$value['id'])->select();
				//print_r($result);die;
				foreach ($result as $k=>$v){
					if($result[0]['receive_interest']==0&&$result[0]['receive_capital']==0){
						$arr1[] = $v['borrow_id'];
					}
				}
			}
			$arr1 = array_unique($arr1);
			print_r($arr1);
			//echo 111;
			//$this->display();
		}
		
		
		
		//先息后本提前还款如期被覆盖标的查询
		public function check_repaytime()
		{
			set_time_limit(0);
			//$where['borrow_status'] = 6;
			$where['second_verify_time&borrow_status&is_advanced&repayment_type'] = array(array('gt','1472054399'),7,2,4,'_multi'=>true);
			//$where['_logic'] = 'OR';
			//$where['bi.id'] = 679;//1835 1834 1614 1613 1604 1563
			$list = M('lzh_borrow_info bi')
				//     ->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,
				// borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_prepayment,
				// idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid')
				//     ->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
				//     ->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
				//     ->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
				->where($where)
				//->limit(10)
				->select();
			echo M('lzh_borrow_info bi')->getLastSql();echo "<hr>";
			
			foreach ($list as $key=>$value){
				$result = M('lzh_investor_detail')->where('borrow_id='.$value['id'])->select();
				//实际还款记录
				//print_r($result);die;
				$arr1 = array();
				foreach($result as $kkk=>$vvv){
					if($result[0]['repayment_time']>$result[0]['deadline']){
						$arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['receive_capital'] += $vvv['receive_capital'];
						$arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['receive_interest'] += $vvv['receive_interest'];
						$arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
						
						if($vvv['repayment_time'] > $vvv['deadline']){
						    M('lzh_investor_detail')->where('id='.$vvv['id'])->setField('repayment_time',strtotime("-9 hour",  $vvv['deadline']));
						}
					}
				}
				unset($arr1[$value['id']."-".'1970-01-01']);
				if(count($arr1) == 1){
					//print_r($arr1);
					$new[] = $value['id'];
				}
			}
			print_r($new);
		}
		
		
		
		//计算提前还款的实际利息
		public function calculate(){
			//未计利息天数
			//$unborrow_days = ceil((1544111999 - 1543294217)/86400);
			$deadlinetime = 1553875199;
			$repaytime = 1553500000;
			$interest_uid = 14300.00;
			$interest_total = 0;
			$repayday = date('Y-m-d',$repaytime);
			$deadlineday = date('Y-m-d',$deadlinetime);
			echo "实际还款时间:".$repayday."<br>"."约定还款时间:".$deadlineday."<hr>";
			$unborrow_days = ceil(($deadlinetime - $repaytime)/86400);
			echo '<br>未计算天数 = '.$unborrow_days.'天<br>';
			
			$month_days = 30;
			$borrow_days = $unborrow_days < 30 ? ($month_days-$unborrow_days+1) : 0; //实际计息天数
			echo '<br>实际计息天数 = '.$borrow_days.'天<br>';
			//如果每月还息
			$interest = $interest_uid/30 * $borrow_days;
			$borrow_interest = $interest_total/30 * $borrow_days;
			
			echo '<br>实际出借人收到利息='. $interest;
			echo '<br>实际借款人还款利息='. $borrow_interest;
			exit;
		}
		
		//末期本息提前还款计算实际利息
		public function calculate_mo(){
			$second_verify_time = 1510738943;
			$repaytime = 1515719179;
			$deadlinetime = 1573833599;
			$interest_uid = 11400.00;
			//$interest_total = 0;
			$repayday = date('Y-m-d',$repaytime);
			$deadlineday = date('Y-m-d',$deadlinetime);
			$beginday = date('Y-m-d',$second_verify_time);
			$borrow_day = ceil(($repaytime - $second_verify_time)/86400);
			echo "借款开始时间:".$beginday."<br>"."实际还款时间:".$repayday."<br>"."约定还款时间:".$deadlineday."<br>"."实际借款天数:".$borrow_day."天"."<hr>";
			$money_real = ceil(($repaytime - $second_verify_time)/86400)*($interest_uid/730);
			echo $money_real;
			
		}
		
		
		//2016年8月24日以后
		public function zhj_borrow(){
			$beg = strtotime('2016-8-24 23:59:59');//2016.8.24
			$begin = strtotime('2016-8-24 23:59:59');
			$end = strtotime('2016-8-28 23:59:59');
			//for($i = 1;$i<100;$i++){
				$where['second_verify_time&second_verify_time&borrow_status'] = array(array('gt',$begin),array('elt',$end),array('in',array('7','9')),'_multi'=>true);
				$list = M('lzh_borrow_info bi')
					//->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo')
					->field('bi.id')
					->join('LEFT JOIN lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
					->join('LEFT JOIN lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
					->join('LEFT JOIN lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
					->join('LEFT JOIN lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
					->where($where)
					->select();
				//echo M()->getLastSql();echo "<br>";
				//print_r($list);
				$status['repayment_time&repayment_time&second_verify_time'] =array(array('gt',$begin),array('elt',$end),array('gt',$beg),'_multi'=>true);
				if(!empty($list)){
					$list_repayment = M('lzh_investor_detail lid')
						->field('lid.borrow_id')
						->join('left join lzh_borrow_info as lbi on lbi.id =lid.borrow_id')
						->where($status)->select();
					echo "<hr>";
					//print_r($list_repayment);echo "哈哈哈哈哈哈哈";
					foreach($list as $keyy=>$valuee){
						$list1[] = $valuee['id'];
					}
					echo "我是当天成立的";print_r($list1);
					if(!empty($list_repayment)){
						foreach ($list_repayment as $k=>$v){
							$list_repayment1[] = $v['borrow_id'];
						}
						echo "<a style='color: red'>我是当天还款的</a>";print_r($list_repayment1);
						$new_array = array_merge($list1,$list_repayment1);
						echo "<a style='color: yellow'>我是合并后的</a>";print_r($new_array);
					}else{
						$new_array = $list1;
					}
				}else{
					$list_repayment = M('lzh_investor_detail lid')
						->field('lid.borrow_id')
						->join('left join lzh_borrow_info as lbi on lbi.id =lid.borrow_id')
						->where($status)->select();
					foreach ($list_repayment as $k=>$v){
						$list_repayment1[] = $v['borrow_id'];
					}
					$new_array = $list_repayment1;
				}
				if(!empty($new_array)){
					$status2['bi.id'] = array('in',$new_array);
					$list_new_array = M('lzh_borrow_info bi')
						->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,has_pay,
            borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,
            idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage')
						->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
						->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
						->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
						->join('left join lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
						->where($status2)
						// ->limit(50)
						->select();
					echo M()->getLastSql();;
					//print_r($list_new_array);
					
					foreach($list_new_array as $kkk=>$value){
						$repay_detail = M('lzh_investor_detail')->where('borrow_id='.$value['id'])->select();
						//提前还款
						//if($value['is_advanced'] != 0||$vvv['is_prepayment']= 1){
						if(true){
							//本期还款状态；
							
							$arr1 = array();
							foreach($repay_detail as $yyy=>$y){
								// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
								// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
								// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
								$arr1[$value['id']."-".date('Y-m-d',$y['repayment_time'])]['total'] = $y['total'];
							}
							//print_r($arr1);die;
							$repay_num = count($arr1);
							$receive_capital = '';
							$receive_interest = '';
							$repayment_time = '';
							$repay_way = '';
							$present_capital = '';
							$present_interest = '';
							foreach ($repay_detail as $eee=>$e){
								//当期实际还款记录以及还款状态
								if($e['repayment_time']>$begin&&$e['repayment_time']<$end){
									$repayment_time = date("Y-m-d",$e['repayment_time']);
									$receive_capital += $e['reveive_capital'];
									$receive_interest += $e['reveive_capital'];
									$repay_way += $e['substitute_money'];
									$repay_way = $repay_way == 0 ? "01" : "03";
									$repay_status = $e['sort_order'] == $repay_num ? '04':'02';
								}else{
									$repayment_time = date("Y-m-d",$value['second_verify_time']);
									$receive_capital = 0;
									$receive_interest = 0;
									$repay_way = "01";
								}
								//实际累计还款本金，利息
								if($e['sort_order']<= $repay_num){
									$present_capital += $e['reveive_capital'];
									$present_interest += $e['receive_interest'];
								}
							}
							//当期实际还款记录   还款时间 本金 利息 还款方式
							$data = $repayment_time.":".$receive_capital.":".$receive_interest.":".$repay_way;
							
							
						}
						//项目费用、费率
						switch ($value['borrow_duration']) {
							case '1':
								$borrow_fee_rate = (11.76+1)/100;
								break;
							case '3':
								$borrow_fee_rate = (10.56+1.6)/100;
								break;
							case '6':
								$borrow_fee_rate = (9.96+2.2)/100;
								break;
							case '12':
								$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76+2.6)/100 : (8.26+2.6)/100;
								break;
							case '18':
								$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76+3.6)/100 : (8.26+3.6)/100;
								break;
							case '24':
								$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76+4.6)/100 : (8.26+4.6)/100;
								break;
						}
						$borrow_fee =  $value['borrow_money'] * $borrow_fee_rate;
						
						//出借人个数
						// $investor_num = M('investor_detail')->where('borrow_id='.$value['id'])->count('DISTINCT investor_uid');
						$investor_num = M('lzh_borrow_investor')->where('borrow_id='.$value['id'])->count();
						//echo $investor_num;die;
						
						$motrgage = $value['mortgage'];
						
						
						//还款方式
						if($value['repayment_type'] == 4){
							$repayment_type = '01';
						}elseif($value['repayment_type'] == 5){
							$repayment_type = '05';
						}
						
						
						//剩余本金利息
						$capital_last = $value['borrow_money'] - $present_capital;
						$interest_last =$value['borrow_interest'] - $present_interest;
						
						
						$borrow_info = '';
						//项目信息
						$borrow_info .= "91320200323591589D1".$value['id']."|".//项目唯一编号
							"91320200323591589D"."|".//社会信用代码
							"1"."|".//平台序号".
							$value['id']."|".//项目编号
							"01"."|".//项目类型 //个体直接借贷
							$value['borrow_name']."|".//项目名称
							date("Ymd",$value['second_verify_time'])."|".//项目成立日期
							$this->getFloatValue($value['borrow_money'],4).//借款金额
							"|CNY|".//借款币种
							date("Ymd",$value['second_verify_time'])."|".//借款起息日
							date("Ymd",$value['deadline'])."|".//借款到期日期
							ceil(($value['deadline']-$value['second_verify_time'])/(60*60*24))."|".//借款期限  ？？
							$this->getFloatValue(($value['borrow_interest_rate']/100),8)."|".//出借利率
							$this->getFloatValue($borrow_fee_rate,8)."|".//项目费率 //待处理？
							$this->getFloatValue($borrow_fee,4)."|".//项目费用 //待处理？
							$this->getFloatValue(0,4)."|".//其他费用
							"02"."|".//还款保证措施
							$value['borrow_duration']."|".//还款期数
							"02"."|".//担保方式
							$motrgage."|".//担保公司名称
							//implode(";",$data)."|".//约定还款计划
							implode(";",$data)."|".//实际还款记录
							$this->getFloatValue($present_capital,4)."|".//实际累计本金偿还额
							$this->getFloatValue($present_interest,4)."|".//实际累计利息偿还额
							$this->getFloatValue($capital_last,4)."|".//借款剩余本金余额
							$this->getFloatValue($interest_last,4)."|".//借款剩余应付利息
							"1"."|".//是否支持转让
							$repay_status."|".//项目状态
							"|".//逾期原因
							"|".//逾期次数
							$repayment_type."|".//还款方式
							"03"."|".//借款用途
							$investor_num//出借人个数
							."\r\n";
						
					}
					echo $borrow_info."<br>";
					
					
					
					
					
					
					
					
					
					
					
					
				}else{
					echo '当天无业务数据';
				}
				
				
				
				
				
				
				unset($list);
				echo "<hr>";
				$begin += 86400;
				$end += 86400;
			//}
			
		}
		
		
		
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}
		
		
		
	}