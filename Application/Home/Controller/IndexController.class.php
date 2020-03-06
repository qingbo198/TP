<?php
	
    
    namespace Home\Controller;
    
    use app\admin\model\J_store;
	use Think\Controller;
	header("Content-type: text/html; charset=utf-8");
    class IndexController extends Controller
    {
		// public function _initialize() {
		// 		// 判断用户是否登陆
		// 		$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
		// 		if (!$user) {
		// 				$this->success('未登录，请登录', U('User/login'), 2) ;
		// 				exit;
		// 		}
		// }
        
        public function index()
        {
        	$storee = M('j_storee');
        	$result = $storee->field('name,store_id')->select();
        	$store = M('j_store');
        	$res = $store->field('title')->select();
        	foreach ($res as $key=>$value){
				$array[] = $value['title'];
			}
        	foreach ($result as $more=>$m){
        		if(!in_array($m['name'],$array)){
        			$tem[] = $m['store_id'];
				}
			}
        	echo 'debug<br><pre>'; print_r($tem); exit;
        	
        	
        	//echo 'debug<br><pre>'; print_r($result); exit;
        	//echo phpinfo();
			$product = M('product');
			$count = $product->count();
			//echo $count;
			$p = getpage($count,12);
			$result = $product->field(true)->limit($p->firstRow, $p->listRows)->select();
			//print_r($result);die;
			//$result = $product->select();
			foreach($result as $key=>$value){
				$result[$key]['img'] = json_decode($value['img']);
			}
			$res['appoint'] = "userCenter";
			$this->assign('res',$res);
			$this->assign('result',$result);
			$this->assign('page',$p->show());
			$this->display();
			die;
        	
        	
        	
        	
        	
        	
        	
        	
        	// $nubb = array('373','382','383','385','386','387','388','391','392','393','395','396','397','399','401','405','407','408','409','410','411','412','413','414','415','417','418','420','421','422','423','424','425','426','427','428','429','430','431','432','433','434','435','436','437','438','439','440','442','443','444','445','446','447','448','449','452','454','456','466','467','468','469','470','471','472','474','475','478','480','484','485','492','495','496','497','498','499','504','505','507','511','515','516','520','521','522','524','525','526','529','530','531','532','536','537','541','542','543','544','545','549','550','551','552','554','555','556','561','562','563','564','565','566','567','569','570','574','575','576','577','578','579','581','583','584','585','586','587','588','594','595','597','598','599','600','601','603','604','605','606','607','608','609','614','615','616','619','621','625','626','627','629','630','631','633','635','636','642','643','644','645','650','651','652','653','654','657','658','659','660','661','662','663','664','665','666','667','668','669','671','672','674','675','676','677','678','686','687','691','693','697','698','706','708','711','721','722','723','724','726','741','742','745','746','749','751','755','769','770','791','793','806','814','820','821','822','827','837','838','849','863','864','875','877','878','879','882','884','885','886','887','892','893','894','895','900','901','902','903','906','908','909','910','912','913','914','915','916','917','924','926','927','928','930','932','940','946','949','955','956','957','959','960','964','968','972','974','975','976','977','979','983','985','992','993','997','999','1002','1004','1005','1007','1010','1012','1014','1017','1018','1023','1028','1030','1037','1039','1040','1045','1047','1048','1049','1051','1053','1054','1055','1056','1059','1060','1061','1062','1064','1073','1074','1080','1081','1082','1084','1085','1087','1088','1090','1091','1092','1093','1094','1095','1105','1106','1107','1108','1109','1113','1114','1117','1132','1133','1134','1135','1136','1137','1138','1139','1140','1143','1149','1150','1151','1156','1158','1162','1163','1165','1166','1167','1168','1173','1174','1176','1180','1181','1182','1183','1186','1187','1188','1192','1193','1194','1195','1197','1201','1202','1203','1206','1212','1215','1232','1234','1237','1238','1239','1246','1247','1248','1249','1256','1276','1298','1300','1301','1302','1303','1304','1307','1332','1333','1335','1336','1337','1338','1339','1341','1347','1368','1374','1376','1396','1397','1398','1403','1523','1524','1526','1528','1531','1532','1533','1534','1535','1536','1537','1538','1540','1542','1543','1544','1545','1546','1548','1549','1550','1551','1552','1553','1554','1558','1559','1560','1562','1564','1567','1568','1572','1573','1575','1579','1582','1583','1584','1588','1589','1590','1591','1594','1595','1596','1600','1601','1602','1603','1608','1610','1611','1615','1616','1617','1618','1620','1621','1622','1623','1624','1629','1630','1632','1635','1636','1637','1638','1644','1645','1646','1650','1652','1653','1655','1660','1661','1662','1663','1664','1666','1667','1668','1670','1672','1673','1677','1678','1679','1681','1682','1683','1684','1685','1688','1692','1694','1696','1697','1698','1699','1700','1701','1702','1704','1711','1712','1717','1721','1722','1725','1726','1727','1730','1734','1736','1738','1739','1741','1743','1744','1746','1747','1752','1753','1761','1763','1766','1767','1769','1773','1775','1776','1778','1785','1791','1792','1796','1799','1808','1820','1822','1824','1826','1828','1831','1833','1837','1839','1840','1841','1844','1845','1846','1847','1848','1849','1850','1851','1852','1853');
        	// echo count($nubb);die;
            set_time_limit(0);
            // /$where['borrow_status'] = 6;
            //$where['second_verify_time&borrow_status&is_advanced&repayment_type'] = array(array('gt','1472054399'),7,2,4,'_multi'=>true);
			//$where['second_verify_time&borrow_status'] = array(array('gt','1472054399'),7,'_multi'=>true);
            //$where['_logic'] = 'OR';
			$where['bi.id'] = 1834;//1835 1834 1614 1613 1604 1563
            $list = M('lzh_borrow_info bi')
           //     ->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,
           // borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_prepayment,
           // idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid')
           //     ->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
           //     ->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
           //     ->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
                ->where($where)
                //->limit(20)
                ->select();
            echo M('lzh_borrow_info bi')->getLastSql();echo "<hr>";//die;
            
            foreach ($list as $key=>$value){
                $result = M('lzh_investor_detail')->where('borrow_id='.$value['id'])->select();
                //实际还款记录
                //print_r($result);die;
                $arr1 = array();
				$arr2 = array();
				//只有一个还款时间
				foreach($result as $kk=>$vv){
					if($result[0]['repayment_time']>$result[0]['deadline']){
						$arr2[$value['id']."-".(date('Y-m-d',$vv['repayment_time']))]['receive_capital'] += $vv['receive_capital'];
						$arr2[$value['id']."-".(date('Y-m-d',$vv['repayment_time']))]['receive_interest'] += $vv['receive_interest'];
						$arr2[$value['id']."-".(date('Y-m-d',$vv['repayment_time']))]['repayment_time'] = date('Y-m-d',$vv['repayment_time']);
			
						// if($vvv['repayment_time'] > $vvv['deadline']){
						//     M('lzh_investor_detail')->where('id='.$vvv['id'])->setField('repayment_time',strtotime("-9 hour",  $vvv['deadline']));
						// }
					}
				}
				unset($arr2[$value['id']."-".'1970-01-01']);
				if(count($arr2) == 1){
				//print_r($arr1);
					$new1[] = $value['id'];
				}
                //还款时间大于一个
                foreach($result as $kkk=>$vvv){
                    if($result[0]['repayment_time']>$result[0]['deadline']){
                        $arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['receive_capital'] += $vvv['receive_capital'];
                        $arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['receive_interest'] += $vvv['receive_interest'];
                        $arr1[$value['id']."-".(date('Y-m-d',$vvv['repayment_time']))]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
                        
						$new[] = $value['id'];
                    }
                    $new = array_unique($new);
                }
                unset($arr1[$value['id']."-".'1970-01-01']);
			}
			echo count($new);
			foreach ($new as $hhh=>$mmm){
            	$newss[] = $mmm;
			}
			//print_r($newss);
			foreach($newss as $uuu=>$yyy){
            	if(!in_array($yyy,$new1)){
            		$last[] = $yyy;
				}
			 }
			 print_r($new1);echo "<hr>";
			 $new1 = array_slice($new1,0,1);
			 print_r($new1);
			 //修改提前还款时间；
			$status2['borrow_id'] = array('in',$new1);
			$result_new = M('lzh_investor_detail')->field('repayment_time,deadline,id')->where($status2)->select();
			//print_r($result_new);die;
			foreach ($result_new as $way=>$wa){
				if($wa['repayment_time'] > $wa['deadline']){
					M('lzh_investor_detail')->where('id='.$wa['id'])->setField('repayment_time',strtotime("-9 hour",  $wa['deadline']-328));
				}
			}
			 //print_r($last);echo count($last);
			
			
        
        }
        
        
        public function quit()
        {
            session('user', NULL);
            $this->success('退出成功', U('Index/index'));
        }
        
        
        //购物车页面
        public function shop(){
            if($_POST){
                $total = 0;
                //unset($_SESSION['shop']);die;
                $arr = $_SESSION['shop'];
                $product = M('product');
                $result = $product->where('id='.$_POST['id'])->find();
                $result['img'] = json_decode($result['img']);
                //print_r($result);die;
                if(empty($arr)){
                    $arr[$result['id']] = [
                        'id'=> $result['id'],
                        'name'=>$result['name'],
                        'price'=>$result['price'],
                        'img'=>$result['img'],
                        'num'=>1
                    ];
                    foreach($arr as $k=>$v){
                        $total += $v['num'];
                    }
                    $arr['total'] = $total;
                    $_SESSION['shop'] = $arr;
                    print_r($_SESSION['shop']);die;
                }else{
                    if(array_key_exists($result['id'],$_SESSION['shop'])){
                        $arr[$result['id']]['num'] += 1;
                    }else{
                        $arr[$result['id']] = [
                            'id'=> $result['id'],
                            'name'=>$result['name'],
                            'price'=>$result['price'],
                            'img'=>$result['img'],
                            'num'=>1
                        ];
                    }
                    foreach($arr as $k=>$v){
                        $total += $v['num'];
                    }
                    $arr['total'] = $total;
                    $_SESSION['shop'] = $arr;
                    print_r($_SESSION['shop']);die;
                }
            }else{
                $this->assign('list',$_SESSION['shop']);
                $this->assign('total',$_SESSION['shop']['total']);
                $this->display();
            }
        }
        
        //获取session
        function session(){
            if($_POST['plus'] == 'plus'){
                $_SESSION['shop'][$_POST['id']]['num'] += 1;
                $_SESSION['shop']['total'] += 1;
                echo json_encode($_SESSION['shop']);
                exit;
            }elseif($_POST['reduce'] == 'reduce'){
                $_SESSION['shop'][$_POST['id']]['num'] -= 1;
                $_SESSION['shop']['total'] -= 1;
                echo json_encode($_SESSION['shop']);
                exit;
            }
            
        }
        
        //删除商品
		function delete(){
        	$id = $_POST['id'];
        	//print_r($_SESSION['shop']);die;
        	$_SESSION['shop']['total'] -= $_SESSION['shop'][$id]['num'];
        	unset($_SESSION['shop'][$id]);
			$msg = [
				'status'=>'1',
				'msg'=>'删除成功'
			];
			echo json_encode($msg);
			exit;
		}
        
        //修改提前还款的实际利息
		public function inde(){
			set_time_limit(0);
			//$where['borrow_status'] = 6;
			//$where['second_verify_time&borrow_status&is_advanced&repayment_type'] = array(array('gt','1472054399'),7,2,4,'_multi'=>true);
			// $where['second_verify_time&borrow_status'] = array(array('gt','1472054399'),7,'_multi'=>true);
			// $where['_logic'] = 'OR';
			$where['bi.id'] = 1349;//1835 1834 1614 1613 1604 1563
			$list = M('lzh_borrow_info bi')
				->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,
            borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,
            idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard')
				->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
				->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
				->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
				->join('left join lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
				->where($where)
				->limit(10)
				->select();
			echo M('lzh_borrow_info bi')->getLastSql();echo "<hr>";
			//echo 'debug<br><pre>'; print_r($list); exit;
			$borrow_info = '';//项目信息
			$borrower = '';//借款人信息
			$investor = '';//出借人信息
			foreach($list as $key=>$value){
				$detail = M('lzh_investor_detail');
				$result = $detail->where('borrow_id='.$value['id'])->select();
				//echo 'debug<br><pre>'; print_r($result); //exit;
				//约定还款计划；
				$arr = array();
				foreach($result as $k=>$v){
					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}
				foreach($arr as $kk=>$vv){
					$data[] = date("Y-m-d",$vv['deadline']).":".$this->getFloatValue($vv['capital'],4).":".$this->getFloatValue($vv['interest'],4);
				}
				//实际还款记录
				//先息后本提前还款
				if($value['repayment_type'] == 4&&$value['is_advanced']!=0){
					$arr1 = array();
					foreach($result as $kkk=>$vvv){
						// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
						// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
						$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['total'] = $vvv['total'];
					}
					//unset($arr1['1970-01-01']);
					//print_r($arr1);die;
					//echo count($arr1);die;
					//$total = $result[0]['total'];
					//echo $total;die;
					//判断是否为最后一期提前还款
					if(count($arr1)!=$result[0]['total']){
						$arr3 = array();
						foreach($result as $real=>$re){
							// if($re['sort_order']<count($arr1)){
							// 	$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['receive_interest'] += $re['receive_interest'];
							// }
							if($re['sort_order']<=count($arr1)) {
								$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['receive_interest'] += $re['receive_interest'];
							}
							$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['receive_capital'] += $re['receive_capital'];
							$arr3[$value['id']."-".date('Y-m-d',$re['repayment_time'])]['repayment_time'] = date('Y-m-d',$re['repayment_time']);
						}
						//print_r($arr3);
						unset($arr1);
						$arr1 =array();
						$arr1 = $arr3;
					}else{
						$arr1 = array();
						foreach($result as $kkk=>$vvv){
							$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
							$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
							$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
						}
						unset($arr1['1970-01-01']);
						//print_r($arr1);die;
					}
				}else{
					$arr1 = array();
					foreach($result as $kkk=>$vvv){
						$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
						$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						$arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
					}
					unset($arr1['1970-01-01']);
					//print_r($arr1);die;
				}
				
				//末期本息未到还款期限
				if($value['repayment_type'] == 5 && $value['borrow_status'] == 6){
					$data1[] = date("Y-m-d",$value['second_verify_time']).":".'0'.":".'0'.":"."01";
				}elseif($value['repayment_type'] == 4 && $value['borrow_status'] == 6 && $value['has_pay'] == 0){
					//先息后本项目成立后还未发生还款
					$data1[] = date("Y-m-d",$value['second_verify_time']).":".'0'.":".'0'.":"."01";
				}else{
					//已经发生还款
					foreach($arr1 as $kkkk=>$vvvv){
						$data1[] = $vvvv['repayment_time'].":".$this->getFloatValue($vvvv['receive_capital'],4).":".$this->getFloatValue($vvvv['receive_interest'],4).":"."01";
					}
				}
				
				//实际累计本金、利息偿还额
				$present_capital = '';
				$present_interest = '';
				//末期本息未到还款期限
				if($value['repayment_type'] == 5 && $value['borrow_status'] == 6){
					$present_capital = 0;
					$present_interest = 0;
					//先息后本项目成立后还未发生还款
				}elseif($value['repayment_type'] == 4 && $value['borrow_status'] == 6 && $value['has_pay'] == 0) {
					$present_capital = 0;
					$present_interest = 0;
				}else{
					foreach($result as $item=>$it){
						$present_capital += $it['receive_capital'];//累计偿还本金
					}
					foreach ($arr1 as $pppp=>$pp){
						$present_interest += $pp['receive_interest'];//累计偿还利息
					}
					
				}
				
				//根据还款方式判断本笔贷款状态  4:先息后本; 5:末期本息;
				if($value['borrow_status'] == 6){ //还款中
					$repayment_status = '02';
				}elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9){ //正常还款已结束
					$repayment_status = '03';
				}elseif ($value['borrow_status'] == 7 && $value['is_prepayment'] == 1){ //提前还款已结束
					$repayment_status = '04';
				}elseif ($value['borrow_status'] == 7 && $value['is_advanced']!=0){ //标识标的 提前还款已结束
					$repayment_status = '04';
				}
				//剩余本金、利息
				if($repayment_status == '03'|| $repayment_status == '04'){
					$capital_last = 0;
					$interest_last = 0;
				}else{
					$capital_last = $value['borrow_money'] - $present_capital;
					$interest_last =$value['borrow_interest'] - $present_interest;
				}
				//还款方式
				if($value['repayment_type'] == 4){
					$repayment_type = '01';
				}elseif($value['repayment_type'] == 5){
					$repayment_type = '05';
				}
				//出借人个数
				$investor_num = M('lzh_investor_detail')
					->where('borrow_id='.$value['id'])
					->count('DISTINCT investor_uid');
				//echo $investor_num;die;
				
				
				
				//项目信息
				$borrow_info .= "91320200323591589D1".$value['id']."|".//项目唯一编号
					"91320200323591589D"."|".//社会信用代码
					"1"."|".//平台序号".
					$value['id']."|".//项目编号
					"项目类型|".//项目类型
					$value['borrow_name']."|".//项目名称
					date("Ymd",$value['second_verify_time'])."|".//项目成立日期
					$this->getFloatValue($value['borrow_money'],4).//借款金额
					"|CNY|".//借款币种
					date("Ymd",$value['second_verify_time'])."|".//借款起息日
					date("Ymd",$value['deadline'])."|".//借款到期日期
					ceil(($value['deadline']-$value['second_verify_time'])/(60*60*24))."|".//借款期限
					$this->getFloatValue(($value['borrow_interest_rate']/100),8)."|".//出借利率
					$this->getFloatValue(($value['borrow_fee']/$value['borrow_money']),8)."|".//项目费率
					$this->getFloatValue($value['borrow_fee'],4)."|".//项目费用
					$this->getFloatValue(0,4)."|".//其他费用
					"02"."|".//还款保证措施
					$value['borrow_duration']."|".//还款期数
					"02"."|".//担保方式
					"无锡合众汇财资产管理有限公司|".//担保公司名称
					implode(";",$data)."|".//约定还款计划
					implode(";",$data1)."|".//实际还款记录
					$this->getFloatValue($present_capital,4)."|".//实际累计本金偿还额
					$this->getFloatValue($present_interest,4)."|".//实际累计利息偿还额
					$this->getFloatValue($capital_last,4)."|".//借款剩余本金余额
					$this->getFloatValue($interest_last,4)."|".//借款剩余应付利息
					"1"."|".//是否支持转让
					$repayment_status."|".//项目状态
					"|".//逾期原因
					"|".//逾期次数
					$repayment_type."|".//还款方式
					"03"."|".//借款用途
					$investor_num//出借人个数
					."<br>";
				
				unset($data);
				unset($data1);
				
				
				//借款人累计借款次数
				$capitalinfo = 1;//getMemberBorrowScan($value['borrow_uid']);
				$num = 1;//$capitalinfo['tj']['jkcgcs'];
				
				//借款角色
				
				$borrower_type = 01;//借款人类型 01:自然人 02:法人
				//证件号码
				if(substr($value['zhaiquan_idcard'],17,1) == 'x'){
					$idcode = substr($value['zhaiquan_idcard'],0,17).'X';
				}else{
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				if($value['sex'] == "男"){
					$sex = '1';
				}elseif ($value['sex'] == "女"){
					$sex = '2';
				}else{
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($value['zhaiquan_idcard'],0,6);
				//开户银行名称
				$bankname = $value['bankname'];
				
				//企业借款人
				if($value['custrole_type'] == 2){
					$borrower_type = 02;
					$idcode = '91210100MA0P50736W';
					$sex = '';
					$career = '';
					$area = substr($idcode,1,6);
					$bankname = '中国建设银行股份有限公司沈阳天龙支行';
				}
				
				
				//借款人信息
				$borrower .= "91320200323591589D1".$value['id']."|".//项目唯一编号
					"01"."|".//借款人类型
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
					"<br>";
				
				
				
				
				
			}
			
			//出借人信息
			//$status['borrow_status'] = 6;
			// $status['second_verify_time&borrow_status'] = array(array('gt','1472054399'),7,'_multi'=>true);
			// // $status['_logic'] = 'OR';
			// //$status['lbi.id'] = 1835;
			// //$status['lbi.id'] = array('in','1032,915,914,910,916,976,977,979,1765,1789');
			// $investor_arr = M('investor_detail id')
			// 	->distinct(true)
			// 	->field('lbi.id,investor_uid,idcode,idcard')
			// 	->join('lzh_borrow_info as lbi on lbi.id = id.borrow_id')
			// 	->join('lzh_member_jshbank as lmj on lmj.uid = id.investor_uid')
			// 	->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
			// 	->where($status)
			// 	//->limit(10)
			// 	->select();
			// echo M('investor_detail id')->getLastSql();echo "<hr>";
			// //echo 'debug<br><pre>'; print_r($investor_arr); exit;
			// foreach($investor_arr as $invest=>$inv) {
			// 	$investor_list = M('investor_detail')->where('borrow_id=' . $inv['id'])->select();
			// 	//echo 'debug<br><pre>'; print_r($investor_list); exit;
			// 	$total_investor = '';
			// 	foreach ($investor_list as $zhj => $z) {
			// 		if ($z['investor_uid'] == $inv['investor_uid']) {
			// 			$total_investor += $z['capital'];
			// 		}
			// 	}
			// 	//出借人身份证号码
			// 	if (substr($inv['idcard'], 17, 1) == 'x') {
			// 		$idcode_investor = substr($inv['idcard'], 0, 17) . 'X';
			// 	} else {
			// 		$idcode_investor = $inv['idcard'];
			// 	}
			//
			// 	//$idcode_investor = $inv['idcode']==''?$inv['idcard']:$inv['idcode'];
			// 	// $idcode_investor = $inv['idcard'];
			//
			// 	$investor .= "91320200323591589D1" . $inv['id'] . "|" .//项目唯一编号
			// 		"01" . "|" .//出借人类型
			// 		$inv['investor_uid'] . "|" .//出借人ID
			// 		"01" . "|" .//证件类型
			// 		$idcode_investor . "|" .//证件号码////////////////////////////待添加
			// 		"|" .//职业类型
			// 		"|" .//所属地区
			// 		"|" .//所属行业
			// 		getFloatValue($total_investor, 4) . "|" .//出借金额
			// 		"01"//出借状态
			// 		. "<br>";
			// 	unset($total_investor);
			// }
			
			
			echo $borrow_info;echo "<hr>";
			echo $borrower;echo "<hr>";
			
		}
	
		function getFloatValue($f,$len)
		{
			return  number_format($f,$len,'.','');
		}
        
    }
		