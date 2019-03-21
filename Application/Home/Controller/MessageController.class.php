<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class MessageController extends Controller {
		//末期本息提前还款时  还款本金和利息不对的标的.,,,..；
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
			// $ii = strtotime('2016-9-25');
			// echo $ii;die;
			$beg = strtotime('2016-8-24 23:59:59');//2016.8.24  1472054399
			$begin = strtotime('2016-9-24 23:59:59');
			$end = strtotime('2016-9-25 23:59:59'); //1472140799
			//for($i = 1;$i<10;$i++){
				$where['second_verify_time&borrow_status'] = array(array('between',array($begin,$end)),array('in',array('7','9')),'_multi'=>true);
				$list = M('lzh_borrow_info bi')
					->field('bi.id')
					->join('LEFT JOIN lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
					->join('LEFT JOIN lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
					->join('LEFT JOIN lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
					->join('LEFT JOIN lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
					->where($where)
					->select();
				echo M()->getLastSql();echo "<br>";
				//print_r($list);
				
				//echo "我是当天成立的";print_r($list1);echo '<br>';
				$status['repayment_time&second_verify_time'] =array(array('between',array($begin,$end)),array('gt',$beg),'_multi'=>true);
				$list_repayment = M('lzh_investor_detail lid')
					->DISTINCT(true)
					->field('lid.borrow_id')
					->join('left join lzh_borrow_info as lbi on lbi.id =lid.borrow_id')
					->where($status)->select();
				echo M()->getLastSql();echo "<br>";
				if(!empty($list) && !empty($list_repayment)){
					foreach($list as $keyy=>$valuee){
						$list1[] = $valuee['id'];
					}
					foreach ($list_repayment as $k=>$v){
						$list_repayment1[] = $v['borrow_id'];
					}
					echo "<a style='color: blue'>我是新成立和还款中合并后的标：</a>";
					$new_array = array_merge($list1,$list_repayment1);
					print_r($new_array);echo "<br>";
				}elseif(!empty($list)&&empty($list_repayment)){
					foreach($list as $keyy=>$valuee){
						$list1[] = $valuee['id'];
					}
					echo "<a style='color: green'>我是当天成立的</a>";
					$new_array = $list1;
					print_r($new_array);echo "<br>";
				}elseif(empty($list) && !empty($list_repayment)){
					foreach ($list_repayment as $k=>$v){
						$list_repayment1[] = $v['borrow_id'];
					}
					echo "<a style='color: red'>我是当天还款的</a>";
					$new_array = $list_repayment1;
					print_r($new_array);echo "<br>";
				}
				
				if(!empty($new_array)){
					$status2['bi.id'] = array('in',$new_array);
					//$status2['bi.id'] = 1266;
					$list_new_array = M('lzh_borrow_info bi')
						->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,has_pay,
				        borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,
				        idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage')
						->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
						->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
						->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
						->join('left join lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
						->order('id asc')
						->where($status2)
						->select();
					//echo M()->getLastSql();
					//print_r($list_new_array);die;
					$borrow_info = '';//项目信息
					$borrower = '';//借款人信息
					$investor = '';//出借人信息
					
					$borrow_ids = array();
					
					foreach($list_new_array as $aaa=>$value){
						$repay_detail = M('lzh_investor_detail')->field('borrow_id,repayment_time,deadline,interest,capital,receive_interest,receive_capital,substitute_money,sort_order,total')->where('borrow_id='.$value['id'])->select();
						//print_r($repay_detail);die;
						//约定还款计划；
						$arr = array();
						foreach($repay_detail as $k=>$v){
							$arr[$v['deadline']]['capital'] += $v['capital'];
							$arr[$v['deadline']]['interest'] += $v['interest'];
							$arr[$v['deadline']]['deadline'] = $v['deadline'];
						}
						//print_r($arr);die;
						foreach($arr as $kk=>$vv){
							$data[] = date("Y-m-d",$vv['deadline']).":".$this->getFloatValue($vv['capital'],4).":".$this->getFloatValue($vv['interest'],4);
						}
						
						//当天发生还款当期实际还款记录
						//非提前还款
						$arr1 = array();
						foreach($repay_detail as $kkk=>$vvv){
							$arr1[$vvv['borrow_id'].":".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
							$arr1[$vvv['borrow_id'].":".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
							$arr1[$vvv['borrow_id'].":".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
							$arr1[$vvv['borrow_id'].":".date('Y-m-d',$vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
							$arr1[$vvv['borrow_id'].":".date('Y-m-d',$vvv['repayment_time'])]['sort_order'] = $vvv['sort_order'];
						}
						unset($arr1['1970-01-01']);
						//print_r($arr1);echo $value['id'];//die;echo $begin."-".$end;
						
						//本期还款状态；
						$arr2 = array();
						foreach($repay_detail as $yyy=>$y){
							$arr2[$value['id']."-".date('Y-m-d',$y['repayment_time'])]['total'] = $y['total'];
						}
						//print_r($arr2);die;
						$repay_num = count($arr2);//累计实际还款次数
						
						//针对提前还款标的
						if(count($arr1)!=$repay_detail[0]['total']){
							//echo 111;die;
							$arr3 = array();
							foreach($repay_detail as $real=>$re){
								if($re['sort_order']<=count($arr1)) {
									$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['receive_interest'] += $re['receive_interest'];
									$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['sort_order'] = $re['sort_order'];
								}
								$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['receive_capital'] += $re['receive_capital'];
								$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['repayment_time'] = date('Y-m-d',$re['repayment_time']);
								$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['substitute_money'] += $re['substitute_money'];
							}
							//print_r($arr3);die;
							unset($arr1);
							$arr1 =array();
							$arr1 = $arr3;
							//print_r($arr1);die;
						}
						
						$present_capital = '';
						$present_interest = '';
						foreach ($arr1 as $eee=>$e){
							//当期实际还款记录以及还款状态
							if((strtotime($e['repayment_time']) > $begin )&& (strtotime($e['repayment_time']) < $end)){
								$repayment_time = $e['repayment_time'];
								$receive_capital = $e['receive_capital'];
								$receive_interest = $e['receive_interest'];
								$repay_way = $e['substitute_money'];
								$repay_way = $repay_way == 0 ? "01" : "03";
								
								//发生还款时的状态   非提前还款标的
								if($value['is_advanced'] == 0 || $value['is_prepayment'] == 0){
									//除了和最后一期都是还款中的状态 02
									if($e['sort_order'] != $repay_detail[0]['total']){
										$repay_status = '02';
									}else{
										$repay_status = '03';//最后一期正常还款已结清；
									}
								}else{
									//提前标的最后一期之前还款时的状态 02
									if($e['sort_order'] != $repay_num){
										$repay_status = '02';
									}else{
										$repay_status = '03';//最后一期提前还款已结清；
									}
								}
							}elseif(($value['second_verify_time'] > $begin) && ($value['second_verify_time'] < $end)){
								//项目新成立无还款记录；
								$repayment_time = date("Y-m-d",$value['second_verify_time']);
								$receive_capital = 0;
								$receive_interest = 0;
								$repay_way = "01";
								$repay_status = '01';
							}
							//累计还款本金、利息
							//echo strtotime($e['repayment_time'])."---".$end;die;
							if(strtotime($e['repayment_time']) < $end){
								$present_capital += $e['receive_capital'];
								$present_interest += $e['receive_interest'];
							}elseif(($value['second_verify_time'] > $begin) && ($value['second_verify_time'] < $end)){
								$present_capital = 0;
								$present_interest = 0;
							}
							//剩余本金利息
							$capital_last = $value['borrow_money'] - $present_capital;
							//提前还款标的最后一期剩余的利息为0;
								if(($value['is_advanced'] != 0 || $value['is_prepayment'] != 0)){
									if((strtotime($e['repayment_time']) > $begin )&& (strtotime($e['repayment_time']) < $end)){
										if($e['sort_order'] == $repay_num){
											$interest_last = 0;
										}else{
											$interest_last =$value['borrow_interest'] - $present_interest;
										}
									}
								}else{
									$interest_last =$value['borrow_interest'] - $present_interest;
								}
							
							
						}
						//unset($arr1);
						//当期实际还款记录   还款时间 本金 利息 还款方式
						$data1 = $repayment_time.":".$this->getFloatValue($receive_capital,4).":".$this->getFloatValue($receive_interest,4).":".$repay_way;
						
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
						
						//担保公司
						$motrgage = $value['mortgage'];
						
						//还款方式
						if($value['repayment_type'] == 4){
							$repayment_type = '01';
						}elseif($value['repayment_type'] == 5){
							$repayment_type = '05';
						}
						
						
						//项目信息
						$borrow_info .= "91320200323591589D1".$value['id']."|".//项目唯一编号，，
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
							implode(";",$data)."|".//约定还款计划
							$data1."|".//实际还款记录
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
							."<br>";
						
						unset($data);
						unset($data1);
						unset($present_capital);
						unset($present_interest);
						
						//取消执行
						if(true){
							//借款人累计借款次数
							// $capitalinfo = getMemberBorrowScan($value['borrow_uid']);
							$count_borrow['borrow_uid'] = $value['borrow_uid'];
							$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
							$num = M('lzh_borrow_info')->where($count_borrow)->count();
							// $num = $capitalinfo['tj']['jkcgcs'];
							
							//借款角色
							
							$borrower_type = '01';//借款人类型 01:自然人 02:法人
							//证件号码
							if(substr($value['zhaiquan_idcard'],17,1) == 'x'){
								$idcode = substr($value['zhaiquan_idcard'],0,17).'X';
							}else{
								$idcode = $value['zhaiquan_idcard'];
							}
							//性别
							$sexint = substr($value['zhaiquan_idcard'],16,1);
							
							if($sexint % 2 == 0){
								$sex = '2';
							}elseif ($sexint % 2 != 0){
								$sex = '1';
							}else{
								$sex = '0';
							}
							//职业种类
							$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
							//所属地区
							$area = substr($idcode,0,6);
							//开户银行名称
							$bankname = $value['zhaiquan_bankinfo'];
							
							//企业借款人
							if($value['lztype'] == 2){
								$borrower_type = '02';
								$sex = '';
								$career = '';
								$area = substr($idcode,1,6);
							}
							
							
							//借款人信息
							$borrower .= "91320200323591589D1".$value['id']."|".//项目唯一编号
								$borrower_type."|".//借款人类型
								$value['borrow_uid']."|".//借款人ID
								"01"."|".//证件类型
								$idcode."|".//证件号码////////////////////////////待添加
								$sex."|".//性别
								"|".//借款人年平均收入
								"|".//借款人主要收入来源
								$career."|".//职业类型80000不便分类的其他从业人员
								$area."|".//所属地区////////////////////////////待添加
								"|".//实缴资本
								"|".//注册资本
								"|".//所属行业
								"|".//机构成立时间
								$bankname."|".//开户银行名称////////////////////////////待添加
								"|".//收款账户开户行所在地区
								"|".//借款人信用评级
								$num.//借款人累计借款次数
								"<br>";//\r\n
							
							//存储所有标的ID
							$borrow_ids[] = $value['id'];
						}
						//取消执行END
					}
					
					$status3['id.borrow_id'] = array('in', $borrow_ids);
					
					$investor_arr = M('lzh_borrow_investor id')
						->field('borrow_id,investor_uid,investor_capital,idcard')
						->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
						->where($status3)
						->select();
					echo M()->getLastSql();echo "<hr>";
					//echo 'debug<br><pre>'; print_r($investor_arr); exit;
					foreach($investor_arr as $invest=>$inv){
						//出借人身份证号码
						if(substr($inv['idcard'],17,1) == 'x'){
							$idcode_investor = substr($inv['idcard'],0,17).'X';
						}else{
							$idcode_investor = $inv['idcard'];
						}
						$investor .= "91320200323591589D1".$inv['borrow_id']."|".//项目唯一编号
							"01"."|".//出借人类型
							$inv['investor_uid']."|".//出借人ID
							"01"."|".//证件类型
							$idcode_investor."|".//证件号码////////////////////////////待添加
							"|".//职业类型
							"|".//所属地区
							"|".//所属行业
							$this->getFloatValue($inv['investor_capital'],4)."|".//出借金额
							"01"//出借状态
							."<br>";//\r\n
						unset($total_investor);
						
						
					}
					echo $borrow_info."<hr>";
					echo $borrower."<hr>";
					echo $investor;
					
					
				}else{
					echo '当天无业务数据';
				}
				
				unset($list);
				unset($list1);
				unset($list_repayment1);
				unset($new_array);
				echo "<hr>";
			// 	$begin += 86400;
			// 	$end += 86400;
			// 		echo $begin."--".$end;
			// }
		}
		
		
		
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}
		
		
		
	}