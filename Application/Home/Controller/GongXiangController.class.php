<?php

namespace Home\Controller;

use Think\Controller;

header("Content-type: text/html; charset=utf-8");

class GongXiangController extends Controller
{
	//信息共享平台接口(首次报送)
	//截止到报送月月初的还款中标的数据..
	public function first_sub()
	{
		$hide = 1;
		$start_time = strtotime('2019-02-01 00:00:00');
		$end_time = strtotime('2019-02-28 23:59:59');
		$march_time = strtotime('2019-03-1 00:00:00');
		//三月之前注册且处在还款中的标的
		$status['second_verify_time&borrow_status'] = array(array('lt', $end_time), array('eq', 6), '_multi' => true);
		//$status['bi.id'] = 1547;
		$list = M("lzh_borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id,borrow_uid,zhaiquan_idcard,cell_phone,second_verify_time,deadline,borrow_money,repayment_type,zhaiquan_name,mi.real_name,mi.idcard")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($status)
			//->limit(4)
			->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list_array[] = $v['borrow_id'];
			}
		}
		//echo 'debug<br><pre>'; print_r($list_array); //exit;
		//在二月底之前产生且二月之后结束的标的
		$where_feb['second_verify_time&repayment_time&borrow_status'] = array(array(array('lt', $end_time)), array('gt', $march_time), array('in', array('7', '9')), '_multi' => true);
		$feb = M('lzh_investor_detail id')
			->field('repayment_time,borrow_id')
			->join('left join lzh_borrow_info as lbi on id.borrow_id = lbi.id')
			->where($where_feb)
			->select();
		
		//echo M()->getLastSql();die;
		//echo 'debug<br><pre>'; print_r($feb); exit;
		foreach ($feb as $k => $v) {
			$feb_array[] = $v['borrow_id'];
		}
		$feb_array = array_unique($feb_array);
		//echo 'debug<br><pre>'; print_r($feb_array); exit;
		//合并二月处在还款中二月之后结束的标的和直到当前处在还款中的标的
		$list = array_merge($feb_array, $list_array);
		//echo 'debug<br><pre>'; print_r($list); exit;
		
		$status_merge['bi.id'] = array('in', $list);
		$list_all = M("lzh_borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id,borrow_uid,zhaiquan_idcard,cell_phone,second_verify_time,deadline,borrow_money,repayment_type,zhaiquan_name,mi.real_name,mi.idcard")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($status_merge)
			//->limit(4)
			->select();
		//->count();
		//echo $list_all;die;
		
		
		//echo M()->getLastSql(); die;
		
		//excel头部
		$row = array();
		$row[0] = array('姓名', '证件类型', '证件号码', '业务发生机构', '业务号', '业务类型', '业务种类', '开户日期', '到期日期', '授信额度', '业务发生日期', '余额', '当前逾期总额', '本月还款状态');
		$i = 1;
		
		$info = '';
		foreach ($list_all as $key => $value) {
			if ($hide) {
				$real_name = mb_substr($value['real_name'], 0, 1, 'utf-8') . $this->choose_name();
				if (substr($value['idcard'], 17, 1) == 'x') {
					$idcard = substr($value['idcard'], 0, 17) . 'X';
					$idcard = substr($idcard, 0, 2) . "9999" . substr($idcard, 6, 12);
				} else {
					$idcard = $value['idcard'];//身份证号码
					$idcard = substr($idcard, 0, 2) . "9999" . substr($idcard, 6, 12);
				}
			} else {
				$real_name = $value['real_name'];
				if (substr($value['idcard'], 17, 1) == 'x') {
					$idcard = substr($value['idcard'], 0, 17) . 'X';
				} else {
					$idcard = $value['idcard'];//身份证号码
				}
			}
			
			//业务类型
			if ($value['repayment_type'] == 4) {
				$borrow_type = 2;//2 分期还款；先息后本
			} elseif ($value['repayment_type'] == 5) {
				$borrow_type = 3;//3 一次性还款；末期本息
			}
			$where['repayment_time&borrow_id'] = array(array('between', array($start_time, $end_time)), array('eq', $value['borrow_id']), '_multi' => true);
			$result = M('lzh_investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
			//echo M()->getLastSql(); die;
			//print_r($result);//die;
			//业务发生日期、当月还款状态
			if ($value['second_verify_time'] > $start_time && $value['second_verify_time'] < $end_time) {
				$happenday = date('Ymd', $value['second_verify_time']);//新开立的债权融资业务
				$repay_status = "*";
			} elseif (!empty($result)) {
				$happenday = date('Ymd', $result[0]['repayment_time']);//当月发生还款（先息后本）
				$repay_status = "N";
			} elseif ($value['repayment_type'] == 5) {
				$happenday = date('Ymd', $end_time);//当月不需还款（末期本息）
				$repay_status = "*";
			}
			//余额
			$last_money = $value['borrow_money'];//(首次报送的都为还款中的标的所以余额为借款金额)
			
			
			$info .= $real_name . "," .//姓名
				"0" . "," .//证件类型
				$idcard . "," .//证件号码
				"91320200323591589D" . "," .//业务发生机构:社会信用代码;
				$value['borrow_id'] . "," .//业务号:系统内唯一标识贷款账户的标识符。
				$borrow_type . "," .//业务类型
				"11" . "," .//业务种类
				date('Ymd', $value['second_verify_time']) . "," .//开户日期:当业务类型为 2、3、5 时，该数据项为首次放款日期；
				date('Ymd', $value['deadline']) . "," .//到期日期
				intval($value['borrow_money']) . "," .//授信额度
				$happenday . "," .//业务发生日期
				intval($last_money) . "," .//余额
				"0" . "," .//当前逾期总额
				$repay_status
				. "\r\n";
			
			//导出excel;
			if (false) {
				$export = 1;//设置导出；
				$row[$i]['A'] = $real_name;
				$row[$i]['B'] = 0;
				$row[$i]['C'] = $idcard;
				$row[$i]['D'] = "91320200323591589D";
				$row[$i]['E'] = $value['borrow_id'];
				$row[$i]['F'] = $borrow_type;
				$row[$i]['G'] = 11;//实际还款时间
				$row[$i]['H'] = date('Ymd', $value['second_verify_time']);
				$row[$i]['I'] = date('Ymd', $value['deadline']);
				$row[$i]['J'] = intval($value['borrow_money']);
				$row[$i]['K'] = $happenday;
				$row[$i]['L'] = intval($last_money);
				$row[$i]['M'] = 0;
				$row[$i]['N'] = $repay_status;
				$i++;
			}
			
			
		}
		$filename = "Feb";
		//echo $info;
		$txtname = "121EXPORTTRADEINFO.txt";
		$this->creZip($txtname, $info);
		if ($export) {
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}
		
	}
	
	
	//信息共享平台接口(再次报送)
	//当月10号之前报送上月数据
	public function next_sub()
	{
		$hide = 1;
		//echo phpinfo();die;
		$start_time = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));//上月第一天开始时间
		$end_time = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));//上月最后一天结束时间
		$april_time = strtotime('2019-04-01 00:00:00');
		$march_time = strtotime('2019-03-1 00:00:00');
		// $start_time = strtotime('2019-04-01 00:00:00');
		// $end_time = strtotime('2019-04-30 23:59:59');
		//echo $start_time."---".$end_time;die;
		
		
		//截止到当前time()处在还款中的标的：所要上报月份的标的状态也一定处在还款中
		//三月结束之前注册且处在还款中的标的
		$status['second_verify_time&borrow_status'] = array(array('lt', $end_time), array('eq', 6), '_multi' => true);
		//$status['bi.id'] = 1547;
		$list = M("lzh_borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->where($status)
			//->limit(4)
			->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list_array[] = $v['borrow_id'];
			}
		}
		//echo 'debug<br><pre>'; print_r($list_array); //exit;
		
		//二月底之前产生且二月之后结束的标的
		$where_feb['second_verify_time&repayment_time&borrow_status'] = array(array(array('lt', $march_time)), array('gt', $march_time), array('in', array('7', '9')), '_multi' => true);
		$feb = M('lzh_investor_detail id')
			->field('repayment_time,borrow_id')
			->join('left join lzh_borrow_info as lbi on id.borrow_id = lbi.id')
			->where($where_feb)
			->select();
		
		//echo M()->getLastSql();die;
		
		foreach ($feb as $k => $v) {
			$feb_array[] = $v['borrow_id'];
		}
		$feb_array = array_unique($feb_array);
		//echo '1 debug<br><pre>'; print_r($feb_array); //exit;
		
		
		//三月底之前产生且在四月份结束的标的
		$where_mar['second_verify_time&repayment_time&borrow_status'] = array(array(array('lt', $end_time)), array('gt', $april_time), array('in', array('7', '9')), '_multi' => true);
		$mar = M('lzh_investor_detail id')
			->field('repayment_time,borrow_id')
			->join('left join lzh_borrow_info as lbi on id.borrow_id = lbi.id')
			->where($where_mar)
			->select();
		
		//echo M()->getLastSql();die;
		//echo 'debug<br><pre>'; print_r($mar); exit;
		foreach ($mar as $k => $v) {
			$mar_array[] = $v['borrow_id'];
		}
		//四月之后结束的标的
		$mar_array = array_unique($mar_array);
		//echo '2 debug<br><pre>'; print_r($mar_array); exit;
		//三月份结束的标的
		$march_array = array_diff($feb_array, $mar_array);
		//echo 'debug<br><pre>'; print_r($march_array); exit;
		
		//合并还款中标的和2月之后结束的标的
		$arr = array_merge($list_array, $feb_array);
		
		$status_last['bi.id'] = array('in', $arr);
		//$status_last['bi.id'] =1679;
		$list = M("lzh_borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id,borrow_uid,cell_phone,second_verify_time,borrow_status,deadline,borrow_money,repayment_type,mi.real_name,mi.idcard")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->where($status_last)
			//->limit(4)
			->select();
		//->count();
		//echo $list;die;
		//echo M()->getLastSql(); die;
		
		//excel头部
		$row = array();
		$row[0] = array('姓名', '证件类型', '证件号码', '业务发生机构', '业务号', '业务类型', '业务种类', '开户日期', '到期日期', '授信额度', '业务发生日期', '余额', '当前逾期总额', '本月还款状态');
		$i = 1;
		
		
		$info = '';
		foreach ($list as $key => $value) {
			if ($hide) {
				$real_name = mb_substr($value['real_name'], 0, 1, 'utf-8') . $this->choose_name();
				if (substr($value['idcard'], 17, 1) == 'x') {
					$idcard = substr($value['idcard'], 0, 17) . 'X';
					$idcard = substr($idcard, 0, 2) . "9999" . substr($idcard, 6, 12);
				} else {
					$idcard = $value['idcard'];//身份证号码
					$idcard = substr($idcard, 0, 2) . "9999" . substr($idcard, 6, 12);
				}
			} else {
				$real_name = $value['real_name'];
				if (substr($value['idcard'], 17, 1) == 'x') {
					$idcard = substr($value['idcard'], 0, 17) . 'X';
				} else {
					$idcard = $value['idcard'];//身份证号码
				}
			}
			//业务类型
			if ($value['repayment_type'] == 4) {
				$borrow_type = 2;//2 分期还款；先息后本
			} elseif ($value['repayment_type'] == 5) {
				$borrow_type = 3;//3 一次性还款；末期本息
			}
			$where['repayment_time&borrow_id'] = array(array('between', array($start_time, $end_time)), array('eq', $value['borrow_id']), '_multi' => true);
			$result = M('lzh_investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
			//echo M()->getLastSql(); die;
			//print_r($result);//die;
			
			
			//业务发生日期、当月还款状态、余额
			//当月产生标的
			if ($value['second_verify_time'] > $start_time && $value['second_verify_time'] < $end_time) {
				$happenday = date('Ymd', $value['second_verify_time']);//新开立的债权融资业务
				$repay_status = "*";
				$last_money = $value['borrow_money'];
			}
			//还款中的标的
			if (!empty($result)) {
				if ($value['borrow_status'] == 6) {
					if ($value['repayment_type'] == 4) {
						$happenday = date('Ymd', $result[0]['repayment_time']);//先息后本当期发生还款 标的未结束
						$repay_status = "N";
						$last_money = $value['borrow_money'];
					} elseif ($value['repayment_type'] == 5) {
						$happenday = date('Ymd', $end_time);
						$repay_status = "*";
						$last_money = $value['borrow_money'];
					}
				}
			} elseif ($value['repayment_type'] == 5 && $value['borrow_status'] == 6) {//末期本息当期未发生还款
				$happenday = date('Ymd', $end_time);
				$repay_status = "*";
				$last_money = $value['borrow_money'];
			}
			//三月四月结束还款的
			if (in_array($value['borrow_id'], $feb_array)) {
				if (in_array($value['borrow_id'], $march_array)) {    //三月发生还款标的结束
					$happenday = date('Ymd', $result[0]['repayment_time']);
					$repay_status = "C";//结清
					$last_money = 0;
				} else {
					if ($value['repayment_type'] == 4) {
						$happenday = date('Ymd', $result[0]['repayment_time']);//先息后本当期发生还款 标的未结束
						$repay_status = "N";
						$last_money = $value['borrow_money'];
					} elseif ($value['repayment_type'] == 5) {
						$happenday = date('Ymd', $end_time);
						$repay_status = "*";
						$last_money = $value['borrow_money'];
					}
				}
			}
			
			//输出信息
			$info .= $real_name . "," .//姓名
				"0" . "," .//证件类型
				$idcard . "," .//证件号码
				"91320200323591589D" . "," .//业务发生机构:社会信用代码;
				$value['borrow_id'] . "," .//业务号:系统内唯一标识贷款账户的标识符。
				$borrow_type . "," .//业务类型
				"11" . "," .//业务种类
				date('Ymd', $value['second_verify_time']) . "," .//开户日期:当业务类型为 2、3、5 时，该数据项为首次放款日期；
				date('Ymd', $value['deadline']) . "," .//到期日期
				intval($value['borrow_money']) . "," .//授信额度
				$happenday . "," .//业务发生日期
				intval($last_money) . "," .//余额
				"0" . "," .//当前逾期总额
				$repay_status//本月还款状态
				. "\r\n";
			
			//导出excel;
			if (false) {
				$export = 1;//设置导出；
				$row[$i]['A'] = $real_name;
				$row[$i]['B'] = 0;
				$row[$i]['C'] = $idcard;
				$row[$i]['D'] = "91320200323591589D";
				$row[$i]['E'] = $value['borrow_id'];
				$row[$i]['F'] = $borrow_type;
				$row[$i]['G'] = 11;//实际还款时间
				$row[$i]['H'] = date('Ymd', $value['second_verify_time']);
				$row[$i]['I'] = date('Ymd', $value['deadline']);
				$row[$i]['J'] = intval($value['borrow_money']);
				$row[$i]['K'] = $happenday;
				$row[$i]['L'] = intval($last_money);
				$row[$i]['M'] = 0;
				$row[$i]['N'] = $repay_status;
				$i++;
			}
		}
		$filename = 'Mar';
		//echo $info;
		$txtname = "121EXPORTTRADEINFO.txt";
		$this->creZip($txtname, $info);
		
		if ($export) {
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}
		
	}
	
	
	//信息共享平台生成生成txt并压缩成zip文件
	private function creZip($filename, $txtcontent)
	{
		$dir = dirname(dirname(dirname(dirname(__FILE__)))) . '\\Uploads\\gongxiang';
		//echo $dir;die;
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true); //创建文件夹
		}
		$file_dir = $dir . '\\' . $filename;
		$txtcontent = iconv('utf-8', "GBK", $txtcontent); //转gbk格式
		file_put_contents($file_dir, $txtcontent, true);
		//return;
		//生成压缩文件
		$packagename = '323591589' . date('YmdHi', time()) . '12' . '0001' . '.zip';
		$zipname = $dir . '\\' . $packagename;
		$zip = new \ZipArchive();
		$zip->open($zipname, \ZipArchive::CREATE);//打开压缩包
		$zip->addFile($file_dir, basename($file_dir));//向压缩包中添加文件
		if ($zip->open($zipname, \ZipArchive::CREATE) !== TRUE) {
			$zipstatus = 0;
			return 'zip文件生成失败';
		} else {
			$zipstatus = 1;
		}
		$zip->close();  //关闭压缩包
	}
	
	
	//末期本息提前还款利息不对查询
	public function check()
	{
		//D3贷款贷后还款数据
		$where['second_verify_time&borrow_status&repayment_type'] = array(array('gt', strtotime('2016-08-24 23:59:59')), array('in', array('7', '9')), array('eq', 5), '_multi' => true);
		$where['cell_phone'] = array('neq', '');
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
		$singleLoanRepayInfo = "#singleLoanRepayInfo" . "\r\n";
		foreach ($list as $k => $v) {
			$result = M('lzh_investor_detail')->where('borrow_id=' . $v['bid'])->select();
			
			foreach ($result as $kk => $vv) {
				if ($vv['interest'] == $vv['receive_interest']) {
					$array[] = $vv['borrow_id'];
				}
			}
			$array = array_unique($array);
		}
		echo 'debug<br><pre>';
		print_r($array);
		exit;
		
	}
	
	
	function getFloatValue($f, $len)
	{
		return number_format($f, $len, '.', '');
	}
	
	function choose_name()
	{
		$array_name = array('一', '二', '更', '大', '锤', '小', '花', '四', '五', '六', '七', '娟', '罐', '轴', '车', '军', '山', '哈', '库', '是', '这', '谁', '水');
		$random = array_rand($array_name, 2);
		$name = '';
		foreach ($random as $v) {
			$name .= $array_name[$v];
		}
		return $name;
	}
	
}