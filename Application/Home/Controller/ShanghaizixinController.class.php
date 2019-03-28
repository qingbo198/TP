<?php

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");
	class ShanghaizixinController extends Controller {
		
		//上海资信批量查询借款人征信报告
		public function search_report(){
			header("Content-type: text/html; charset=gb2312");
			$msg = M("lzh_members m");
			$where['is_vip'] = 1;
			$where['jshbank_status'] = array(array('EQ',1),array('EQ',2),'OR');
			$where['is_jshbank|is_asyn_chinapnr'] = 1;
			$list = $msg->order('m.id')
				->group(uid)
				->field('mi.uid,real_name,idcard,bi.id,borrow_name,borrow_money,is_jshbank,is_asyn_chinapnr')
				->join('left join lzh_member_info as mi on m.id = mi.uid')
				->join('left join lzh_members_status as ms on m.id = ms.uid')
				->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
				->where($where)
				->limit(1,20)
				->select();
			//echo M()->getLastSql();die();
			$name = "王万里";
			$name1 = "万里";
			echo strlen($name).strlen($name1);die;
			//print_r($list);die;
			$info = '';
			foreach ($list as $k=>$v){
				
				$info .= '01'.//报告板式  01-网络金融版个人信用报告  03-网络金融特殊交易版信用报告  长度2 位置1-2
					str_pad($v['real_name'],30," ",STR_PAD_RIGHT ).//被查询人的姓名 长度30 位置3-32
					'0'.//证件类型 0-身份证 1-户口簿 2-护照 3-军官证  长度1 位置33
					$v['idcard']. //证件号码 长度18 位置 34-51
					'25'.//查询原因  01-贷后管理 02-贷款审批 08-担保资格审查 25-资信审查 长度2 位置 52-53
					'0'//生成文件类型 0-html格式 1-xml格式 长度1 位置 54
					."<br>";
				
			}
			echo $info;
			echo $zixinname = Q10153000HUV00.date('Ymd',time())."1A".".txt";
		}
		
		
		
		
	}