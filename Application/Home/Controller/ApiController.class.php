<?php
// +----------------------------------------------------------------------
// | 登陆模块
// +----------------------------------------------------------------------
// | Copyright (c) 2015 https://www.51daishu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: SJAY(sjay.u@qq.com)
// +----------------------------------------------------------------------
header("Content-type: text/html; charset=utf-8");

class ApiAction extends MCommonAction
{

	var $notneedlogin = true;

	private $fileName = array(
		'binfo' => '24EXPORTBUSINESSZHAIQ.txt',         // 互联网债权类融资项目信息
		'binfobor' => '24EXPORTBUSINESSZHAIQ_BOR.txt',    // 互联网债权类融资借款人信息
		'binfoinr' => '24EXPORTBUSINESSZHAIQ_INV.txt',     // 互联网债权类融资出借人信息
		'transerinfo' => '26EXPORTBUSINESSJINR.txt',          // 互联网金融产品及收益权转让融资项目信息
		'transerinfoinr' => '26EXPORTBUSINESSJINR_INV.txt',      // 互联网金融产品及收益权转让融资受让人信息
	);
	private $bhfileName = array(
		'C1' => '江苏袋鼠妈妈金融信息服务有限公司_C1_20191014_0001.txt',         // 贷款申请数据
		'D2' => '江苏袋鼠妈妈金融信息服务有限公司_D2_20191014_0001.txt',    // 非循环贷款账户数据
		'D3' => '江苏袋鼠妈妈金融信息服务有限公司_D3_20191014_0001.txt'    // 非循环贷款贷后数据
	);

	public function index()
	{
		$refer = $this->_get('refer');
		if (!empty($refer)) {
			$_SESSION['refer'] = $refer;
		}
		$data['time'] = time();
		$this->assign('data', $data);
		$this->display();
	}


	public function getpassword()
	{
		$this->display();
	}

	//验证手机号
	public function sendphone()
	{
		$do = text($_POST['do']);
		$phone = text($_POST['cellphone']);
		$txtCode = text($_POST['txtCode']);


		//短信模板
		// $smsTxt = FS("Webconfig/smstxt");
		// $smsTxt = de_xie($smsTxt);

		//随机产生验证码
		$code = rand_string_reg(6, 1, 2);
		$datag = get_global_setting();
		$is_manual = $datag['is_manual'];

		if ($do == 'get_regcode') { //重新获取手机验证码
			$phone = session("temp_phone");

			if (!$phone) {
				$return['status'] = 0;
				$return['msg'] = '操作失败，请填写手机号后验证！';
				exit(json_encode($return));
			}
			//默认自动验证
			// $res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array($phone, $code), $smsTxt['verify_phone']));
			S('sms_verifyCode_' . $phone, $code, 600); //验证码缓存10分钟
			$content = $code . '（袋鼠妈妈账号注册验证，用于验证身份修改密码等，请勿将验证码透露给他人）【袋鼠妈妈】';
			$res = sendsms($phone, $content);

			if ($res) {
				session("temp_phone", $phone);
				$return['status'] = 1;
				$return['msg'] = '验证码已发送到您的手机，请查看短信。';
				exit(json_encode($return));
			} else {
				session("temp_phone", $phone);
				$return['status'] = 0;
				$return['msg'] = '手机短信验证码发送失败，请稍候再试';
				exit(json_encode($return));
			}
		}


		if (!preg_match("/^1[34578]\d{9}$/", $phone)) {
			exit('{"status":0,"msg":"您填写的手机号码有误，请重新填写!"}');
		}

		if ($_SESSION['code'] != sha1($txtCode)) {
			$return['status'] = 0;
			$return['msg'] = '验证码错误！';
			exit(json_encode($return));
		}
		//验证手机号是否使用

		$chkwhere['user_name'] = $phone;
		$chkwhere['user_phone'] = $phone;
		$chkwhere['_logic'] = 'or';

		$checkphone = M('members')->where($chkwhere)->getField('id');
		// $checkphone = M('members')->getFieldByUserPhone($phone, 'id');
		if ($checkphone > 0) {
			$return['status'] = 0;
			$return['msg'] = '该手机号码已被其他用户使用';
			exit(json_encode($return));
		}


		if ($is_manual == 0) { // 如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
			S('sms_verifyCode_' . $phone, $code, 600); //验证码缓存10分钟
			$content = $code . '（袋鼠妈妈账号注册验证，用于验证身份修改密码等，请勿将验证码透露给他人）【袋鼠妈妈】';
			$res = sendsms($phone, $content);

			// $res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array($phone, $code), $smsTxt['verify_phone']));
		} else { // 否则，则由后台管理员来手动审核手机验证
			$res = true;
			$phonestatus = M('members_status')->getFieldByUid($this->uid, 'phone_status');
			if ($phonestatus == 1) ajaxmsg("手机已经通过验证", 1);
			$updata['phone_status'] = 3; //待审核
			$updata1['user_phone'] = $phone;
			$a = M('members')->where("id = {$this->uid}")->count('id');
			if ($a == 1) {
				$newid = M("members")->where("id={$this->uid}")->save($updata1);
			} else {
				M('members')->where("id={$this->uid}")->setField('user_phone', $phone);
			}

			$updata2['cell_phone'] = $phone;
			$b = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if ($b == 1) $newid = M("member_info")->where("uid={$this->uid}")->save($updata2);
			else {
				$updata2['uid'] = $this->uid;
				$updata2['cell_phone'] = $phone;
				M('member_info')->add($updata2);
			}
			$c = M('members_status')->where("uid = {$this->uid}")->count('uid');
			if ($c == 1) $newid = M("members_status")->where("uid={$this->uid}")->save($updata);
			else {
				$updata['uid'] = $this->uid;
				$newid = M('members_status')->add($updata);
			}
			if ($newid) {
				ajaxmsg();
			} else ajaxmsg("验证失败", 0);
			// ////////////////////////////////////////////////////////////
		}

		if ($res) {
			session("temp_phone", $phone);
			$return['status'] = 1;
			$return['msg'] = '验证码已发送到您的手机，请查看短信。';
			exit(json_encode($return));
		} else {
			session("temp_phone", $phone);
			$return['status'] = 0;
			$return['msg'] = '手机短信验证码发送失败，请稍候再试';
			exit(json_encode($return));
		}
	}

	public function actlogin()
	{
		setcookie('LoginCookie', '', time() - 10 * 60, "/");
		//uc登陆
		/*
		$loginconfig = FS("Webconfig/loginconfig");
		$uc_mcfg  = $loginconfig['uc'];
		if($uc_mcfg['enable']==1){
			require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
			require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
		}*/
		//uc登陆
		/*if($_SESSION['verify'] != md5(strtolower($_POST['sVerCode'])))
		{
			ajaxmsg("验证码错误!",0);
		}*/


		if ($_SESSION['code'] != sha1($_POST['sVerCode'])) {
			ajaxmsg("验证码错误!", 0);
		}


		$sUserNamelen = strlen(trim($_POST['sUserName']));

		if (strpos($_POST['sUserName'], "@")) {
			$data['user_email'] = text($_POST['sUserName']);
		} elseif (($sUserNamelen == 11) && preg_match("/1[34578]{1}\d{9}$/", $_POST['sUserName'])) {
			$data['user_phone'] = text($_POST['sUserName']);
		} else {
			$data['user_name'] = text($_POST['sUserName']);
		}


		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
		if (empty($vo)) ajaxmsg("账户不存在！", 0);
		if ($vo['is_ban'] == 1) ajaxmsg("您的帐户已被冻结，请联系客服处理！", 0);
		if ($vo['is_ban'] == 2) ajaxmsg("今天登录错误次数超限，请明天再登陆", 0);
		//读取网站登录次数设置
		// $lnum =M('global')->where("code='login_num'")->getField('text');
		$login_num = 5; //intval($lnum);
		if ($login_num == '0') {        //登录错误次数不受限制
			$this->loginlog($vo, 0, 0);
		} else {
			$time = strtotime(date("Y-m-d", time()));
			$where = ' and add_time >' . $time . ' and add_time<' . ($time + 3600 * 24);
			$fail_login_num = M('member_login')->where("is_success=1 and uid={$vo['id']}{$where}")->count();
			$ttime = $login_num - 1 - $fail_login_num;
			if ($fail_login_num > $login_num) {
				ajaxmsg("账号密码错误，您还可以登录{$ttime}次", 0);
				exit;
			} elseif ($fail_login_num == $login_num) {
				$Mdata['is_ban'] = '2';
				M('members')->where('id=' . $vo['id'])->save($Mdata);
				ajaxmsg("账号密码错误，今天登录次数超限，请明天再登陆", 0);
				exit;
			} else {
				$this->loginlog($vo, 1, $ttime);
			}
		}

	}

	/**
	 *
	 * @param void $vo 登录信息
	 * @param int $type 是否开启登录次数限制 0为不限制，1为限制
	 */
	protected function loginlog($vo, $type, $ttime)
	{
		$loginconfig = FS("Webconfig/loginconfig");
		$uc_mcfg = $loginconfig['uc'];
		//if($uc_mcfg['enable']==1){
		require_once C('APP_ROOT') . "Lib/Uc/config.inc.php";
		require C('APP_ROOT') . "Lib/Uc/uc_client/client.php";
		//}
		if (!is_array($vo)) {
			//本站登陆不成功，偿试uc登陆及注册本站
			if ($uc_mcfg['enable'] == 1) {
				list($uid, $username, $password, $email) = uc_user_login(text($_POST['sUserName']), text($_POST['sPassword']));
				if ($uid > 0) {
					$regdata['txtUser'] = text($_POST['sUserName']);
					$regdata['txtPwd'] = text($_POST['sPassword']);
					$regdata['txtEmail'] = $email;
					$newuid = $this->ucreguser($regdata);
					if (is_numeric($newuid) && $newuid > 0) {
						$logincookie = uc_user_synlogin($uid);//UC同步登陆
						setcookie('LoginCookie', $logincookie, time() + 10 * 60, "/");
						$this->_memberlogin($newuid, 0);
						ajaxmsg();//登陆成功
					} else {
						ajaxmsg($newuid, 0);
					}
				} else {
					ajaxmsg("用户名或者密码错误！", 0);
				}
			} else {
				//本站登陆不成功，偿试uc登陆及注册本站
				ajaxmsg("用户名或者密码错误！", 0);
				exit;
			}
		} else {
			if ($vo['user_pass'] == md5($_POST['sPassword'])) {//本站登陆成功，uc登陆及注册UC
				//uc登陆及注册UC
				//if($uc_mcfg['enable']==1){
				$dataUC = uc_get_user($vo['user_name']);
				if ($dataUC[0] > 0) {
					$msg = uc_user_synlogin($dataUC[0]);//UC同步登陆
					setcookie('username', $vo['user_name'], time() + 10 * 60, "/");
					$return['res'] = $msg;
				} else {
					$uid = uc_user_register($vo['user_name'], $_POST['sPassword'], $vo['user_email']);
					if ($uid > 0) {
						$msg = uc_user_synlogin($uid);//UC同步登陆
						setcookie('username', $vo['user_name'], time() + 10 * 60, "/");
						$return['res'] = $msg;
					} elseif ($uid <= 0) {
						if ($uid == -1) {
							ajaxmsg('用户名不合法', 0);
						} elseif ($uid == -2) {
							ajaxmsg('包含要允许注册的词语', 0);
						} elseif ($uid == -3) {
							ajaxmsg('用户名已经存在', 0);
						} elseif ($uid == -4) {
							ajaxmsg('Email 格式有误', 0);
						} elseif ($uid == -5) {
							ajaxmsg('Email 不允许注册', 0);
						} elseif ($uid == -6) {
							ajaxmsg('该 Email 已经被注册', 0);
						} else {
							ajaxmsg('未定义', 0);
						}
					}
				}
				//}
				//uc登陆及注册UC
				$this->_memberlogin($vo['id'], 0);
				//Created By Zhangqi 2015-6-2 to update the last_log_ip and last_log_time
				$Mdata['last_log_ip'] = $_SERVER["REMOTE_ADDR"];;
				$Mdata['last_log_time'] = time();
				M('members')->where('id=' . $vo['id'])->save($Mdata);
				if (!empty($_SESSION['refer'])) {
					$jumpUrl = $_SESSION['refer'];
					$message = '用户登陆验证通过，正在跳回活动页面';
				} else {
					$jumpUrl = __APP__ . '/user';
					$message = '用户登陆验证通过,正跳转用户中心';
				}
				$return['status'] = 1;
				$return['message'] = $message;
				$return['url'] = $jumpUrl;
				exit(json_encode($return));
				// ajaxmsg("用户登陆验证通过,正跳转用户中心",1);
			} else {//本站登陆不成功
				$this->_memberlogin($vo['id'], 1);
				if ($type == '1') {
					if ($ttime <= '0') {
						$data['is_ban'] = '2';
						M('members')->where('id=' . $vo['id'])->save($data);
						ajaxmsg("账号密码错误，今天登录次数超限，请明天再登陆", 0);
					} else {
						ajaxmsg("用户名或者密码错误,您还可以登录{$ttime}次", 0);
					}
				} else {
					ajaxmsg("用户名或者密码错误！", 0);
				}
			}
		}
	}

	//手机找回密码
	public function checktxtcode()
	{
		$cellphone = text($_POST['cellphone']);
		$txtcode = text($_POST['txtcode']);
		$map['user_phone'] = $cellphone;
		$checkcellphone = M('members')->where($map)->find();

		if (!$checkcellphone) {
			ajaxmsg("", 2);
		} elseif (sha1($txtcode) != $_SESSION['code']) {
			ajaxmsg("", 3);
		} else {
			$smsTxt = FS("Webconfig/smstxt");
			$smsTxt = de_xie($smsTxt);
			$code = rand_string_reg(6, 1, 2);
			$_SESSION['phonecode'] = $code;
			$result = sendsms($cellphone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));
			if ($result == true) {
				ajaxmsg("", 1);
			} else {
				ajaxmsg("", 0);
			}

		}
	}

	public function checkphonecode()
	{
		$cellphone = text($_POST['cellphone']);
		$phonecode = text($_POST['phonecode']);

		$map['user_phone'] = $cellphone;
		$userinfo = M('members')->where($map)->find();
		if (!$userinfo) {
			ajaxmsg("", 2);
		} elseif ($phonecode != $_SESSION['phonecode']) {
			ajaxmsg("", 3);
		} else {

			//方便记录用户找回密码时间
			$vcode = rand_string($userinfo['id'], 32, 0, 7);
			session("temp_get_pass_uid", $userinfo['id']);

			// $this->assign("userinfo",$userinfo);
			// ajaxmsg($vcode,1);
			$return['status'] = 1;
			$return['msg'] = "验证成功";
			$return['username'] = $userinfo['user_name'];

			exit(json_encode($return));
		}
	}

	public function getpasswordverify()
	{
		$code = text($_GET['vcode']);
		$uk = is_verify(0, $code, 7, 60 * 1000);
		if (false === $uk) {
			$this->error("验证失败");
		} else {

			session("temp_get_pass_uid", $uk);
			$map['id'] = $uk;
			$userinfo = M('members')->where($map)->find();
			$this->assign("userinfo", $userinfo);

			$this->display('getpass');
		}
	}

	public function dosetnewpass()
	{
		$per = C('DB_PREFIX');
		$uid = session("temp_get_pass_uid");
		$pass = text($_POST['pass']);
		$pass2 = text($_POST['pass2']);
		if ($pass != $pass2) {
			ajaxmsg('两次登陆密码不一样！', 0);
		}
		if (empty($uid)) {
			ajaxmsg('修改的账户信息出错！', 0);
		}
		$oldpass = M("members")->getFieldById($uid, 'user_pass');

		if ($oldpass == md5($pass)) {
			$newid = false;
		} else {
			$newid = M()->execute("update {$per}members set `user_pass`='" . md5($_POST['pass']) . "' where id={$uid}");
		}

		if ($newid) {
			session("temp_get_pass_uid", NULL);
			ajaxmsg('', 1);
		} else {
			ajaxmsg('修改失败，请重试', 0);
		}
	}

	//登出
	public function actlogout()
	{
		$this->_memberloginout();
		//uc登陆
		$loginconfig = FS("Webconfig/loginconfig");
		$uc_mcfg = $loginconfig['uc'];
		//if($uc_mcfg['enable']==1){
		require_once C('APP_ROOT') . "Lib/Uc/config.inc.php";
		require C('APP_ROOT') . "Lib/Uc/uc_client/client.php";
		echo uc_user_synlogout();
		//$this->assign("uclogout",de_xie($logout));
		//}
		//uc登陆
		$this->success("注销成功", __APP__ . "/");
	}

	public function verify()
	{
		//import("ORG.Util.Image");
		//Image::buildImageVerify();
		Header("Content-type: image/GIF");
		import("ORG.Util.Imagecode");
		$imagecode = new Imagecode(96, 30);//(96,30);//参数控制图片宽、高
		$imagecode->imageout();

	}

	//查询当天借款、还款数据接口
	public function search_data(){
		$start_time = strtotime(date("Y-m-d"),time());
		//echo $start_time;die;
		$end_time = strtotime(date("Y-m-d"),time())+86400;
		//今日借款
		$where['second_verify_time'] = array('between',array($start_time,$end_time));
		$reg_list = M('borrow_info')
			->field('id,second_verify_time')
			->where($where)
			->select();
		//echo M()->getLastSql();die;
		if(!empty($reg_list)){
			$data_reg = '';
			foreach ($reg_list as $k=>$v){
				$data_reg .= $v['id']."|";
			}
			echo "今日借款标的".$data_reg."<br>";
		}else{
			echo "今日无借款"."<br>";
		}
		//今日还款
		$status['repayment_time'] = array('between',array($start_time,$end_time));
		$repay_list = M('investor_detail id')
			->distinct(true)
			->field('borrow_id')
			->where($status)
			->select();
		if(!empty($repay_list)){
			$data_repay = '';
			foreach ($repay_list as $k=>$v){
				$data_repay .= $v['borrow_id']."|";
			}
			echo "今日还款标的".$data_repay;
		}else{
			echo "今日无还款";
		}

	}




	/**
	 * 上海资信征信接口中
	 */

	//身份信息
	public function credit()
	{
		import("ORG.Io.Excel");
		$msg = M("members m");
		$where['is_vip'] = 1;
		//$where['jshbank_status'] = array(array('EQ',1),array('EQ',2),'OR');
		$where['is_jshbank|is_asyn_chinapnr'] = 1;
		$list = $msg->order('m.id')
			->group('mi.uid')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			//->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->select();
		//echo $msg->getLastSql();die();
		//print_r($list);die;
		$row = array();
		$row[0] = array('P2P机构代码', '姓名', '证件类型', '证件号码', '性别', '出生日期', '婚姻状况', '最高学历',
			'最高学位', '住宅电话', '手机号码', '单位电话', '电子邮件', '通讯地址', '通讯地址邮政编码', '户籍地址',
			'配偶姓名', '配偶证件类型', '配偶证件号码', '配偶工作单位', '配偶联系电话',
			'第一联系人姓名', '第一联系人关系', '第一联系人电话', '第二联系人姓名', '第二联系人关系', '第二联系人电话', '注册时间', '注册ip', '最近一次登录时间', '最近一次登录ip');
		$i = 1;
		foreach ($list as $v) {
			$row[$i]['compony'] = Q10153000HUV00;
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['card_type'] = 0;
			if (substr($v['idcard'], 17, 1) == 'x') {
				$row[$i]['idcard'] = substr($v['idcard'], 0, 17) . 'X';
			} else {
				$row[$i]['idcard'] = $v['idcard'];
			}
			if ($v['sex'] == '男') {
				$row[$i]['sex'] = 1;
			} elseif ($v['sex'] == '女') {
				$row[$i]['sex'] = 2;
			} else {
				$row[$i]['sex'] = 0;
			}
			$row[$i]['birthday'] = intval(substr($v['idcard'], 6, 8));
			if ($v['marry'] == '未婚') {
				$row[$i]['marry'] = 10;
			} elseif ($v['marry'] == '已婚') {
				$row[$i]['marry'] = 20;
			} else {
				$row[$i]['marry'] = 90;
			}
			if ($v['education'] == '高中以下') {
				$row[$i]['education'] = 70;
			} elseif ($v['education'] == '大专或本科') {
				$row[$i]['education'] = 20;
			} elseif ($v['education'] == '硕士或硕士以上') {
				$row[$i]['education'] = 10;
			} else {
				$row[$i]['education'] = 99;
			}
			if ($v['education'] == '高中以下') {
				$row[$i]['degree'] = 0;
			} elseif ($v['education'] == '大专或本科') {
				$row[$i]['degree'] = 4;
			} elseif ($v['education'] == '硕士或硕士以上') {
				$row[$i]['degree'] = 3;
			} else {
				$row[$i]['degree'] = 9;
			}
			$row[$i]['home_tel'] = '';
			$row[$i]['phone'] = $v['user_phone'];
			$row[$i]['company_tel'] = '';
			$row[$i]['email'] = '';
			$row[$i]['tel_addr'] = '暂缺';
			$row[$i]['postcode'] = 999999;
			$row[$i]['home_addr'] = '';
			$row[$i]['spouse_name'] = '';
			$row[$i]['spouse_card_type'] = '';
			$row[$i]['spouse_card_num'] = '';
			$row[$i]['spouse_com'] = '';
			$row[$i]['spouse_tel'] = '';
			$row[$i]['first_contact_name'] = $v['real_name'];
			$row[$i]['first_contact_rela'] = 8;
			if ($v['user_phone'] != '') {
				$row[$i]['first_contact_tel'] = intval(substr($v['user_phone'], 0, 7)) . 'XXXX';
			} else {
				$row[$i]['first_cintact_tel'] = '';
			}
			$row[$i]['second_contact_name'] = '';
			$row[$i]['second_contact_real'] = '';
			$row[$i]['second_contact_tel'] = '';
			$row[$i]['reg_date'] = date('Y-m-d H:i;s', $v['reg_time']);
			$row[$i]['reg_ip'] = $v['reg_ip'];
			$row[$i]['last_log_time'] = date('Y-m-d H:i;s', $v['last_log_time']);
			$row[$i]['last_log_ip'] = $v['last_log_ip'];
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, '身份信息');
		$xls->addArray($row);
		$xls->generateXML("excel");

		//$this->display();
	}

	//职业信息
	public function credit1()
	{
		import("ORG.Io.Excel");
		$msg = M("members m");
		$where['is_vip'] = 1;
		$where['is_jshbank|is_asyn_chinapnr'] = 1;
		//$where['jshbank_status'] = array(array('EQ',1),array('EQ',2),'OR');
		$list = $msg->order('m.id')
			->group('mi.uid')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			//->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->select();
		//echo $msg->getLastSql();die();
		//print_r($list);die;
		$row = array();
		$row[0] = array('P2P机构代码', '姓名', '证件类型', '证件号码', '职业', '单位名称', '单位所属行业', '单位地址', '单位地址邮政编码',
			'本单位工作起始年份', '职务', '职称', '年收入');
		$i = 1;
		foreach ($list as $v) {
			$row[$i]['compony'] = Q10153000HUV00;
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['card_type'] = 0;
			if (substr($v['idcard'], 17, 1) == 'x') {
				$row[$i]['idcard'] = substr($v['idcard'], 0, 17) . 'X';
			} else {
				$row[$i]['idcard'] = $v['idcard'];
			}
			$row[$i]['occupation'] = 'Z';
			$row[$i]['company'] = '暂缺';
			$row[$i]['profession'] = 'Z';
			$row[$i]['com_addr'] = '';
			$row[$i]['com_postcode'] = '';
			$row[$i]['com_begin'] = '';
			$row[$i]['job'] = 9;
			$row[$i]['job_call'] = 9;
			$row[$i]['income'] = '';
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, '职业信息');
		$xls->addArray($row);
		$xls->generateXML("excel");

		//$this->display();
	}

	//居住信息
	public function credit2()
	{
		import("ORG.Io.Excel");
		$msg = M("members m");
		$where['is_vip'] = 1;
		$where['is_jshbank|is_asyn_chinapnr'] = 1;
		//$where['jshbank_status'] = array(array('EQ', 1), array('EQ', 2), 'OR');
		$list = $msg->order('m.id')
			->group('mi.uid')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			//->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->select();
		//echo $msg->getLastSql();die();
		//print_r($list);die;
		$row = array();
		$row[0] = array('P2P机构代码', '姓名', '证件类型', '证件号码', '居住地址', '居住地址邮政编码', '居住状况');
		$i = 1;
		foreach ($list as $v) {
			$row[$i]['compony'] = Q10153000HUV00;
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['card_type'] = 0;
			if (substr($v['idcard'], 17, 1) == 'x') {
				$row[$i]['idcard'] = substr($v['idcard'], 0, 17) . 'X';
			} else {
				$row[$i]['idcard'] = $v['idcard'];
			}
			if (strlen($v['address']) > 15) {
				$row[$i]['addr'] = $v['address'];
			} else {
				$row[$i]['addr'] = '暂缺';
			}
			$row[$i]['addr_postcode'] = 999999;
			$row[$i]['condition'] = 9;
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, '居住信息');
		$xls->addArray($row);
		$xls->generateXML("excel");

		//$this->display();
	}

	//贷款申请信息
	public function credit3()
	{
		import("ORG.Io.Excel");
		$msg = M("members m");
		$where['is_vip'] = 1;
		//$where['jshbank_status'] = array(array('EQ', 1), array('EQ', 2), 'OR');
		$where['is_asyn_chinapnr|is_jshbank'] = 1;
		//$where['borrow_status'] = 6;
		$list = $msg->order('m.id')->field('bi.id as borrow_id,bi.borrow_money,bi.borrow_duration,bi.second_verify_time,mi.*')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			//->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('left join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->select();
		//echo $msg->getLastSql();die();
		//print_r($list);die;
		$row = array();
		$row[0] = array('P2P机构代码', '贷款申请号', '姓名', '证件类型', '证件号码', '贷款申请类型', '贷款申请金额', '贷款申请月数', '贷款申请时间', '贷款申请状态');
		$i = 1;
		foreach ($list as $v) {
			$row[$i]['compony'] = 'Q10153000HUV00';
			$row[$i]['borrow_id'] = $v['borrow_id'];
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['card_type'] = 0;
			if (substr($v['idcard'], 17, 1) == 'x') {
				$row[$i]['idcard'] = substr($v['idcard'], 0, 17) . 'X';
			} else {
				$row[$i]['idcard'] = $v['idcard'];
			}
			$row[$i]['apply_type'] = 41;
			$row[$i]['apply_money'] = intval($v['borrow_money']);
			$row[$i]['apply_months'] = $v['borrow_duration'];
			$row[$i]['apply_time'] = date('Ymd', $v['second_verify_time']);
			$row[$i]['apply_status'] = 2;
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, '贷款申请');
		$xls->addArray($row);
		$xls->generateXML("excel");

		//$this->display();
	}


	//上海资信批量查询借款人征信报告
	public function search_report()
	{
		$msg = M("members m");
		$where['is_vip'] = 1;
		$where['jshbank_status'] = array(array('EQ', 1), array('EQ', 2), 'OR');
		$where['is_jshbank|is_asyn_chinapnr'] = 1;
		$list = $msg->order('m.id')
			->group(uid)
			->field('mi.uid,real_name,idcard,bi.id,borrow_name,borrow_money,is_jshbank,is_asyn_chinapnr')
			->join('left join lzh_member_info as mi on m.id = mi.uid')
			->join('left join lzh_members_status as ms on m.id = ms.uid')
			->join('right join lzh_borrow_info as bi on m.id = bi.borrow_uid')
			->where($where)
			->limit(20)
			->select();
		//echo M()->getLastSql();die();
		//print_r($list);die;
		$info = '';
		foreach ($list as $k => $v) {
			$v['real_name'] = iconv("UTF-8", "GB2312//IGNORE", $v['real_name']);
			//echo strlen($v['real_name']);die;
			$info .= '01' .//报告板式  01-网络金融版个人信用报告  03-网络金融特殊交易版信用报告  长度2 位置1-2
				str_pad($v['real_name'], 30, " ", STR_PAD_RIGHT) .//被查询人的姓名 长度30 位置3-32
				'0' .//证件类型 0-身份证 1-户口簿 2-护照 3-军官证  长度1 位置33
				$v['idcard'] . //证件号码 长度18 位置 34-51
				'02' .//查询原因  01-贷后管理 02-贷款审批 08-担保资格审查 25-资信审查 长度2 位置 52-53
				'0'//生成文件类型 0-html格式 1-xml格式 长度1 位置 54
				. "\r\n";

		}
		echo $info;
		$zixinname = Q10153000HUV00 . date('Ymd', time()) . "1A" . ".txt";

		$this->getTxt($zixinname, $info);

	}


	//发送任意数据到百行
	public function testBaihang(){

		header("Content-Type:application/json;");

		$user_data = array(
			"name" => "张三",
			"pid" => "31010119900101001X",
			"mobile" => "13812345678",
			"queryReason" => 3,
			"guaranteeType" => 1,
			"loanPurpose" => 1,
			"customType" => 2,
			"applyAmount" => 5000,
			"loanId" => "XF20180612345678",
			"homeAddress" => "上海市闵行区紫星路 800号 8号楼 102室",
			"homePhone" => "021-12345678",
			"workName" => "百行征信有限公司",
			"workAddress" => "深圳市福田区深南大道 1006号深圳国际创新中心 E座",
			"workPhone" => "0755-12345678",
			"device" => array(
				"deviceType" => 1,
				"imei" => "ABCDEFG12345678",
				"mac" => "ABCD12345678",
				"ipAddress" => "139.129.1.54",
				"osName" => 2
			)
		);

		import("ORG.BaihangCredit.BaihangCredit");
		$BaihangCredit = new BaihangCredit();
		$return_data = $BaihangCredit->interfaceUploadClient($user_data, 'C1');
		 print_r($return_data); die;
		$data['loanid'] = 1766;
		$data['type'] = 1;
		$data['status'] = 1;
		$data['msg'] = '1766标的信息';
		$data['time'] = date('Y-m-d H:i:s',time());
		//echo 'debug<br><pre>'; print_r($data); exit;
		M('borrow_baihang')->add($data);
		
		exit;
		//echo $return_data;die;



		$dir = 'D:/wwwroot/51daishu.test.com/UF/';
		$file_name = '91320200323591589D_20190404101108_8aH';
		$file_name_real = '江苏袋鼠妈妈金融信息服务有限公司_20190404101108_8aH';
		$text_file = $dir . $file_name_real. '.txt';

		$data = array(
			'dir' => $dir,
			'file_name' => $file_name,
			'file_name_real' => $file_name_real,
			'text_file' => $text_file
		);

		$return_data = $BaihangCredit->fileUploadClient($data, 'C1');


		$verify_data = array(
			'dir' => $dir,
			'file_name' => $dir . $file_name_real. '.cry'
		);
		$return_data2 = $BaihangCredit->fileUploadValidationClient($data, 'C1');

		echo 'debug<br><pre>'; print_r($return_data); print_r($data); exit;

		//生成压缩包
		$zip_name =  $dir .$file_name. ".zip";

		$zip = new ZipArchive();
		$zipstatus = $zip->open($zip_name, ZipArchive::CREATE);   //打开压缩包
		$zip_con = file_get_contents(iconv('utf-8', 'gbk', $text_file));

		// $zip->addFile($text_file, basename($text_file));   //向压缩包中添加文件
		$zip->addFromString(iconv('utf-8', 'gbk//ignore', basename($text_file)), $zip_con);//中文使用这个


		if ($zip->open($zip_name, \ZipArchive::CREATE) !== TRUE) {
			$zipstatus = 0;
		} else {
			$zipstatus = 1;
		}
		$zip->close();  //关闭压缩包


		if($zipstatus == 1){
			$old_path = $dir. $file_name. '.zip';
			$new_path = $dir. $file_name_real. '.zip';
			$restatus = rename($old_path, iconv('UTF-8','GBK',$new_path));
		}else{
			//错误提示
		}
		if($restatus == 1){

		}else{
			//错误提示
		}
		echo 'zipstatus'.$zipstatus;
		echo '<Br>restatus'.$restatus;
		exit;


	}


	//百行贷款申请信息(增量数据)C1   接口暂未开放不报送C1 后期统一以文件方式报送
	public function SendtoBaiHang_C1()
	{
		$hide = 0; //1:脱敏  0:不脱敏
		
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		//echo $start_time;die;
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点
		//$status['second_verify_time'] = array('between', array($start_time, $end_time));

		
		$status['bi.id'] = 1868;
		$list_apply = M("borrow_info bi")
			->order('second_verify_time')
			->field("bi.id as borrow_id,zhaiquan_idcard,cell_phone,borrow_money,lz.borrow_type,zhaiquan_name,real_name,idcard")
			->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($status)
			//->limit(4)
			->select();
		echo M()->getLastSql();
		//$loanApplyInfo = "#loanApplyInfo"."\r\n";
		if (!empty($list_apply)) {
			foreach ($list_apply as $m => $n) {
				
				if ($hide) {
					$apply['name'] = mb_substr($n['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
					$apply['pid'] = mb_substr($n['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
					$apply['mobile'] = mb_substr($n['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
				} else {
					$apply['name'] = $n['real_name'];//姓名：只能为合法的中国姓名
					if (substr($n['idcard'], 17, 1) == 'x') {
						$apply['pid'] = substr($n['idcard'], 0, 17) . 'X';
					} else {
						$apply['pid'] = $n['idcard'];//身份证号码
					}
					$apply['mobile'] = $n['cell_phone'];//手机号码
				}
				
				
				$apply['queryReason'] = 3;//查询原因 1：授信审批 2:贷中管理 3：贷后管理
				if($n['borrow_type'] == 1){
					$apply['guaranteeType'] = 2;//贷款担保类型 2：抵押
				}elseif ($n['borrow_type'] == 2){
					$apply['guaranteeType'] = 3;//贷款担保类型 3：质押
				}elseif ($n['borrow_type'] == 3){
					$apply['guaranteeType'] = 4;//贷款担保类型 4：保证
				}
				$apply['guaranteeType'] = '';//贷款担保类型 4：1-授信审批”时必填，其他查询原因不填写此字段
				$apply['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
				$apply['customType'] = '';//客户类型  99：人群未知  //查询原因为“1-授信审批”时必填，其他查询原因不填写此字段
				$apply['applyAmount'] = '';//getFloatValue($n['borrow_money'], 2);//贷款申请金额
				$apply['loanId'] = "B".$n['borrow_id'];  //2:贷中管理 3：贷后管理  为时必填项 //贷款/授信账户编号
				$apply['homeAddress'] = '';//家庭地址
				$apply['homePhone'] = '';//家庭电话
				$apply['workName'] = '';//工作单位名称
				$apply['workAddress'] = '';//工作单位地址
				$apply['workPhone'] = '';//工作单位电话
				$apply['device'] = array('deviceType' => '', 'imei' => '', 'mac' => '', 'ipAddress' => '', 'osName' => '');//设备信息
				echo 'debug<br><pre>'; print_r($apply); exit;
				
				import("ORG.BaihangCredit.BaihangCredit");
				$BaihangCredit = new BaihangCredit();
				$return_data = $BaihangCredit->interfaceUploadClient($apply, 'C1');
				echo 'debug<br><pre>'; print_r($return_data); //exit;
				
				if($return_data['status']['http_code']==200){
					$status_code = 1;
					$return_data_msg = $return_data['msg'];
				}else{
					$status_code =$return_data['status']['http_code'];
					$return_data_msg = $return_data['msg'];
				}
				
				
				$data['loanid'] = $n['borrow_id'];
				$data['type'] = 1;
				$data['status'] = $status_code;
				$data['msg'] = $return_data_msg;
				$data['time'] = date('Y-m-d H:i:s',time());
				//echo 'debug<br><pre>'; print_r($data); exit;
				M('borrow_baihang')->add($data);
				
				
				
			}
			//echo $loanApplyInfo;
		} else {
			echo "无贷款申请信息";
		}

	}


	//百行D2接口报送贷款账户信息(增量数据)
	//T+1 隔天上报  开始时间 2019年8月20日 0：00以后产生的数据  新发标的报送
	public function SendtoBaiHang_D2()
	{
		$hide = 0; //1:脱敏  0:不脱敏
		
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		//echo $start_time;die;
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点

		//$where['second_verify_time'] = array('between', array($start_time, $end_time));
		$where['bi.id'] = 1697;
		$list = M('borrow_info bi')
			->order('second_verify_time')
			->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,bi.second_verify_time
				,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id,lz.borrow_type,repayment_type')
			->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
			// ->join('left join lzh_members as m on m.id = bi.borrow_uid')
			->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
			->where($where)
			->select();
		echo M()->getLastSql() . '<br>';
		//print_r($list);die();
		if (!empty($list)) {
			foreach ($list as $key => $value) {
				//账单日类型判断：末期本息为固定周期  先息后本为固定日期  每期还款周期: 末期本息borrow_duration * 30 先息后本：-1
				if ($value['repayment_type'] == 4) {
					$billday_type = 2;
					$period_day = -1;
				} elseif ($value['repayment_type'] == 5) {
					if($value['borrow_duration'] > 12){
						$billday_type = 2;
						$period_day = -1;
					} elseif ($value['borrow_duration'] == 12) {
						$billday_type = 1;
						$period_day = 365;
					} else {
						$billday_type = 1;
						$period_day = $value['borrow_duration'] * 30;
					}
				}


				//接口数据///////////////////////////
				$parama['reqID'] = $value['borrow_id'] . "D2" . "U" . $value['borrow_uid'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
				$parama['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
				$parama['uploadTs'] = date("Y-m-d\TH:i:s", strtotime("+1 hour",$value['second_verify_time']));//记录生成时间
				
				//脱敏处理
				if ($hide) {
					$parama['name'] = mb_substr($value['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
					$parama['pid'] = mb_substr($value['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
					$parama['mobile'] = mb_substr($value['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
				} else {
					$parama['name'] = $value['real_name'];//借款人姓名
					if (substr($value['idcard'], 17, 1) == 'x') {
						$parama['pid'] = substr($value['idcard'], 0, 17) . 'X';
					} else {
						$parama['pid'] = $value['idcard'];//身份证号码
					}
					$parama['mobile'] = $value['cell_phone'];//手机号码
				}
				
				
				$parama['loanId'] = "B".$value['borrow_id'];//贷款编号
				$parama['originalLoanId'] = null;//原贷款编号
				if ($value['borrow_type'] == 1) {
					$parama['guaranteeType'] = 2;//贷款担保类型 2 抵押
				} elseif ($value['borrow_type'] == 2) {
					$parama['guaranteeType'] = 3;//贷款担保类型 3 质押
				}elseif ($value['borrow_type'] == 3) {
					$parama['guaranteeType'] = 4;//贷款担保类型 4 保证
				}
				$parama['loanPurpose'] = 1;//贷款用途
				$parama['applyDate'] = date("Y-m-d\TH:i:s", $value['add_time']);//贷款申请时间
				$parama['accountOpenDate'] = date("Y-m-d\TH:i:s", $value['first_verify_time']);//账户开立时间
				$parama['issueDate'] = date("Y-m-d\TH:i:s", $value['second_verify_time']);//贷款放款时间
				$parama['dueDate'] = date("Y-m-d", $value['deadline']);//贷款到期日期
				$parama['loanAmount'] = getFloatValue($value['borrow_money'], 2);//贷款金额
				if ($value['repayment_type'] == 4) {
					$parama['totalTerm'] = $value['borrow_duration'];//还款总期数
				} elseif ($value['repayment_type'] == 5) {
					$parama['totalTerm'] = 1; //末期本息视为单期
				}
				$parama['targetRepayDateType'] = $billday_type;//账单日类型
				$parama['termPeriod'] = $period_day;//每期还款周期
				//获取计划还款信息
				$detail = M('investor_detail');
				$result = $detail->where('borrow_id=' . $value['borrow_id'])->select();
				foreach ($result as $k => $v) {
					$data[] = date("Y-m-d", $v['deadline']);
				}
				$data = array_unique($data);
				if ($value['repayment_type'] == 5) {
					if($value['borrow_duration'] > 12){
						$parama['targetRepayDateList'] = date("Y-m-d", $value['deadline']);
					}else{
						$parama['targetRepayDateList'] = '';
					}
				} elseif ($value['repayment_type'] == 4) {
					$parama['targetRepayDateList'] = implode(",", $data);//账单日列表
				}
				$parama['firstRepaymentDate'] = reset($data);//首次应还款日
				unset($data);
				$parama['gracePeriod'] = 7;//宽限期
				$parama['device'] = array('deviceType' => 2, 'imei' => null, 'mac' => null, 'ipAddress' => null, 'osName' => 6);//设备信息

				echo 'debug<br><pre>'; print_r($parama); exit;
				
				
				import("ORG.BaihangCredit.BaihangCredit");
				$BaihangCredit = new BaihangCredit();
				$return_data = $BaihangCredit->interfaceUploadClient($parama, 'D2');
				//echo 'debug<br><pre>'; print_r($return_data); exit;
				
				if($return_data['status']['http_code']==200){
					$status_code = 1;
					$return_data_msg = $return_data['msg'];
				}else{
					$status_code =$return_data['status']['http_code'];
					$return_data_msg = $return_data['msg'];
				}
				
				$data['reqid'] = $parama['reqID'];
				$data['loanid'] = $value['borrow_id'];
				$data['type'] = 2;
				$data['status'] = $status_code;
				$data['msg'] = $return_data_msg;
				$data['time'] = date('Y-m-d H:i:s',time());
				//echo 'debug<br><pre>'; print_r($data); exit;
				M('borrow_baihang')->add($data);
				
			}
		} else {
			echo "无新增借款数据";
		}

		//echo 'debug<br><pre>'; //print_r($borrow_bhlist); var_dump($borrow_bhlist); exit;
	}


	//还款业务数据D3(增量数据)
	public function SendtoBaiHang_D3()
	{
		$hide = 0; //1:脱敏  0:不脱敏
		
		$start = date("Y-m-d", (time() - 86400));
		$start_time = strtotime($start) - 1;//前天23:59：59开始时间
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//前一天上报数据截止时间今天零点

		$start_time = strtotime('2019-11-21 23:59:59');//已上报增量标的还款记录   1868（1）  1547(20、21、22)  1867（3、4、5）
		$end_time = strtotime('2019-11-23 00:00:00');

		$status['repayment_time'] = array('between', array($start_time, $end_time));
		$list_repayment = M('investor_detail lid')
			->DISTINCT(true)
			->field('lid.borrow_id')
			->where($status)->select();
		//echo M()->getLastSql();echo "<br>";die();
		if (!empty($list_repayment)) {
			foreach ($list_repayment as $k => $v) {
				$repay_array[] = $v['borrow_id'];
			}
			//$status2['bi.id'] = array('in', $repay_array);
			$status2['bi.id'] = 1868;//1868 1547  1867
			$list = M("borrow_info bi")
				->order('second_verify_time')
				->field('bi.id as bid, mi.real_name, mi.cell_phone,
				mi.idcard,bi.is_advanced,bi.is_prepayment,bi.repayment_type,bi.borrow_duration,bi.borrow_status,bi.borrow_money,bi.borrow_status,bi.borrow_uid')
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->where($status2)
				->select();
			//echo M()->getLastSql(); die();
			foreach ($list as $key => $value) {
				$detail = M('investor_detail');
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
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
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
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
					}
				}
				unset($arr2['1970-01-01']);

				//echo 'debug<br><pre>'; print_r($arr2); exit;

				foreach ($arr2 as $k => $v) {
					if (($v['repayment_time'] > $start_time) && ($v['repayment_time'] < $end_time)) {
						$parama['reqID'] = $value['bid'] . "D3" ."U". $value['borrow_uid'] . "D" . $v['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
						$parama['opCode'] = 'A';//操作代码
						$parama['uploadTs'] = date('Y-m-d\TH:i:s', strtotime('+2 hour',$v['repayment_time']));//记录生成时间:本业务在机构业务系统发生的时间；
						$parama['loanId'] = "B".$value['bid'];//贷款编号
						
						//脱敏处理
						if ($hide) {
							$parama['name'] = mb_substr($value['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
							$parama['pid'] = mb_substr($value['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
							$parama['mobile'] = mb_substr($value['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
						} else {
							$parama['name'] = $value['real_name'];//借款人姓名
							if (substr($value['idcard'], 17, 1) == 'x') {
								$parama['pid'] = substr($value['idcard'], 0, 17) . 'X';
							} else {
								$parama['pid'] = $value['idcard'];//身份证号码
							}
							$parama['mobile'] = $value['cell_phone'];//手机号码
						}
						
						
						$parama['termNo'] = $v['sort_order'];//当期还款期数
						if($v['substitute_money'] > 0){
							$parama['termStatus'] = 'thirdPartyPayIn30';//本期还款状态
						}else{
							$parama['termStatus'] = 'normal';//本期还款状态
						}
						$parama['targetRepaymentDate'] = date('Y-m-d', $v['deadline']);//本期应还款日
						$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s', $v['repayment_time']);//实际还款时间
						if (($value['repayment_type'] == 4 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 4 && $value['is_prepayment'] != 0)){
							$parama['plannedPayment'] = getFloatValue($v['receive_interest'], 2);//本期计划应还款金额
							$parama['targetRepayment'] = getFloatValue($v['receive_interest'], 2);//本期剩余应还款金额
						}else{
							$parama['plannedPayment'] = getFloatValue(($v['receive_capital'] + $v['receive_interest']), 2);//本期计划应还款金额
							$parama['targetRepayment'] = getFloatValue(($v['receive_capital'] + $v['receive_interest']), 2);//本期剩余应还款金额
						}
						$parama['realRepayment'] = getFloatValue(($v['receive_capital'] + $v['receive_interest']), 2);//本次还款金额
						$parama['overdueStatus'] = '';//当前逾期天数
						$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s', strtotime("+1 hour", $v['repayment_time']));//本期还款状态确认时间
						$parama['overdueAmount'] = getFloatValue(0, 2);//当前逾期总额
						if ($value['repayment_type'] == 5) {
							$parama['remainingAmount'] = getFloatValue(0, 2);
						} elseif ($value['repayment_type'] == 4) { //是否是先息后本
							if ($value['borrow_status'] == 6) { //判断是不是还款中标的
								$parama['remainingAmount'] = getFloatValue($value['borrow_money'], 2);//贷款余额：未还金额
							} elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9) {
								$parama['remainingAmount'] = getFloatValue(($v['sort_order'] == $real_total) ? 0 : $value['borrow_money'], 2);
							}
						}
						if ($value['repayment_type'] == 5) {
							$parama['loanStatus'] = 3;//本笔贷款状态
						} elseif ($value['repayment_type'] == 4) {
							if ($value['borrow_status'] == 6) { //判断是不是还款中标的
								$parama['loanStatus'] = 1;
							} elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9) {
								if($value['is_advanced'] != 0 || $value['is_prepayment'] != 0){
									$parama['loanStatus'] = ($v['sort_order'] == $real_total) ? 3 : 1;
								}else{
									$parama['loanStatus'] = ($v['sort_order'] == $v['total']) ? 3 : 1;
								}
								
							}
						}
					}

				}
				unset($arr1, $arr2);

				echo 'debug<br><pre>';print_r($parama);die;
				////此处开始逐条传递数据到百行征信
				
				
				import("ORG.BaihangCredit.BaihangCredit");
				$BaihangCredit = new BaihangCredit();
				$return_data = $BaihangCredit->interfaceUploadClient($parama, 'D3');
				echo 'debug<br><pre>'; print_r($return_data); //exit;
				
				if($return_data['status']['http_code']==200){
					$status_code = 1;
					$return_data_msg = $return_data['msg'];
				}else{
					$status_code =$return_data['status']['http_code'];
					$return_data_msg = $return_data['msg'];
				}
				
				$data['reqid'] = $parama['reqID'];
				$data['loanid'] = $value['bid'];
				$data['type'] = 3;
				$data['status'] = $status_code;
				$data['msg'] = $return_data_msg;
				$data['time'] = date('Y-m-d H:i:s',time());
				//echo 'debug<br><pre>'; print_r($data); exit;
				M('borrow_baihang')->add($data);


				
			}


		} else {
			echo "无还款数据";
			die;
		}


	}


	//百行征信存量数据C1、D2、D3
	public function baihang_stock()
	{
		//总共待传增量标的
		//$status['second_verify_time&second_verify_time&borrow_status&cell_phone'] = array(array('gt',strtotime('2016-08-24 23:59:59')),array('lt',strtotime('2019-06-30 23:59:59')),array('in',array('6','7','9')),array('neq',''),'_multi'=>true);

		//首次上传
		//$status['repayment_type&second_verify_time&second_verify_time&borrow_status&cell_phone'] = array(array('eq',4),array('gt',strtotime('2016-08-24 23:59:59')),array('lt',strtotime('2019-06-30 23:59:59')),array('in',array('6','7','9')),array('neq',''),'_multi'=>true);//存量数据
					//$status['repayment_type&second_verify_time&second_verify_time&borrow_status&cell_phone'] = array(array('eq',4),array('gt',strtotime('2019-06-30 23:59:59')),array('lt',strtotime('2019-08-19 23:59:59')),array('in',array('6','7','9')),array('neq',''),'_multi'=>true);//日增数据   2019 7.1零点至2019.8.19 24点
		//$status['repayment_type&borrow_duration&second_verify_time&second_verify_time&borrow_status&cell_phone'] = array(array('eq',5),array('elt',12),array('gt',strtotime('2016-08-24 23:59:59')),array('lt',strtotime('2019-06-30 23:59:59')),array('in',array('6','7','9')),array('neq',''),'_multi'=>true);//先传末期本息小于等于12个月的
		//$status['_logic'] = 'OR';

		//后期补传
		$status['repayment_type&borrow_duration&second_verify_time&borrow_status&cell_phone'] = array(array('eq',5),array('gt',12),array('gt',strtotime('2016-08-24 23:59:59')),array('in',array('6','7','9')),array('neq',''),'_multi'=>true);//先传末期本息大于12个月的


		$hide = 0;//是否脱敏显示 0 否 1 是
		//C1 接口
		if (false) {
			//C1贷款申请信息接口
			//$status['bi.id'] = 1692;
			//$status['bi.id'] = array('in',array('1691','1692','1693','1697','1700','1594','1600','1601','1225', '1226', '1227', '1429'));
			//$status['bi.id'] = array('in',array('708','841','862'));c1 手机号码格式错误
			//$status['bi.id'] = array('in', array('1225', '1226', '1227', '1429'));


			$list_apply = M("borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as borrow_id,add_time,borrow_uid,zhaiquan_idcard,cell_phone,borrow_money,zhaiquan_name,real_name,idcard,lz.borrow_type")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); die;

			//excel头部
			$row = array();
			$row[0] = array('数据唯一标识','记录生成时间','姓名', '身份证号码', '手机号码', '查询原因', '贷款担保类型', '贷款用途', '客户类型', '申请贷款金额', '贷款/授信账户编号', '家庭地址',
				'家庭电话', '工作单位名称', '工作单位地址', '工作单位电话', '设备信息', '设备类型', '设备IMEI/MEID', 'MAC地址', 'IP地址', '设备操作系统标签');
			$i = 1;

			$loanApplyInfo = "#loanApplyInfo" . "\r\n";
			foreach ($list_apply as $m => $n) {
				$apply['reqID'] = $n['borrow_id'] . "C1" . "U" . $n['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
				$apply['uploadTs'] = date("Y-m-d\TH:i:s",$n['add_time']);//记录生成时间
				
				if ($hide) {
					$apply['name'] = mb_substr($n['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
					$apply['pid'] = mb_substr($n['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
					$apply['mobile'] = mb_substr($n['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
				} else {
					$apply['name'] = $n['real_name'];//姓名：只能为合法的中国姓名
					if (substr($n['idcard'], 17, 1) == 'x') {
						$apply['pid'] = substr($n['idcard'], 0, 17) . 'X';
					} else {
						$apply['pid'] = $n['idcard'];//身份证号码
					}
					$apply['mobile'] = $n['cell_phone'];//手机号码
				}
				
				
				$apply['queryReason'] = 1;//查询原因 1：授信审批 3:贷后管理
				if ($n['borrow_type'] == 1) {
					$apply['guaranteeType'] = 2;//贷款担保类型 2：抵押
				} elseif ($n['borrow_type'] == 2) {
					$apply['guaranteeType'] = 3;//贷款担保类型 3：质押
				} elseif ($n['borrow_type'] == 3) {
					$apply['guaranteeType'] = 4;//贷款担保类型 4：保证
				}
				$apply['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
				$apply['customType'] = 99;//客户类型  99：人群未知
				$apply['applyAmount'] = getFloatValue($n['borrow_money'], 2);//贷款申请金额
				$apply['loanId'] = '';//贷款/授信账户编号 $n['borrow_id'] 此处不填写贷款编号；
				$apply['homeAddress'] = '';//家庭地址
				$apply['homePhone'] = '';//家庭电话
				$apply['workName'] = '';//工作单位名称
				$apply['workAddress'] = '';//工作单位地址
				$apply['workPhone'] = '';//工作单位电话
				$apply['device'] = array('deviceType' => '', 'imei' => '', 'mac' => '', 'ipAddress' => '', 'osName' => '');//设备信息
				$loanApplyInfo .= $this->toJson($apply) . "\r\n";
				//echo 'debug<br><pre>'; print_r($apply);

				//excel导出
				if (false) {
					$export = 1;//设置是否导出；
					$row[$i]['reqID'] = $n['borrow_id'] . "C1" . "U" . $n['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$row[$i]['uploadTs'] = date("Y-m-d\TH:i:s",$n['add_time']);//记录生成时间
					if ($hide) {
						$row[$i]['name'] = mb_substr($n['real_name'], 0, 1, 'utf-8') . $this->choose_names($n['borrow_id']);//姓名：只能为合法的中国姓名 脱敏显示
						$row[$i]['pid'] = mb_substr($n['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
						$row[$i]['mobile'] = mb_substr($n['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
					} else {
						$row[$i]['name'] = $n['real_name'];//姓名：只能为合法的中国姓名
						if (substr($n['idcard'], 17, 1) == 'x') {
							$row[$i]['pid'] = substr($n['idcard'], 0, 17) . 'X';
						} else {
							$row[$i]['pid'] = $n['idcard'];//身份证号码
						}
						$row[$i]['mobile'] = $n['cell_phone'];//手机号码
					}
					$row[$i]['queryReason'] = 1;
					if ($n['borrow_type'] == 1) {
						$row[$i]['guaranteeType'] = 2;//贷款担保类型 2：抵押
					} elseif ($n['borrow_type'] == 2) {
						$row[$i]['guaranteeType'] = 3;//贷款担保类型 3：质押
					} elseif($n['borrow_type'] == 3) {
						$row[$i]['guaranteeType'] = 4;//贷款担保类型 4：保证
					}
					$row[$i]['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
					$row[$i]['customType'] = 99;//客户类型  99：人群未知
					$row[$i]['applyAmount'] = getFloatValue($n['borrow_money'], 2);
					$row[$i]['loanId'] = '';//贷款/授信账户编号
					$row[$i]['homeAddress'] = '';
					$row[$i]['homePhone'] = '';
					$row[$i]['workName'] = '';
					$row[$i]['workAddress'] = '';
					$row[$i]['workPhone'] = '';
					$row[$i]['device'] = '';
					$row[$i]['deviceType'] = '';
					$row[$i]['imei'] = '';
					$row[$i]['mac'] = '';
					$row[$i]['ipAddress'] = '';
					$row[$i]['osName'] = '';
					$i++;
				}
			}
			$filename = 'C1';
			 //echo $loanApplyInfo; die;

		}
		
		//D2 接口
		if (false) {
			//D2贷款账户信息
			//$status['bi.id'] = 1697;
			//$status['bi.id'] = array('in',array('1691','1692','1693','1697','1700','1594','1600','1601','1225', '1226', '1227', '1429','1595','1557'));
			//$map['bi.id'] = array('in',array('1594','1600','1601'));
			//$map['bi.id'] = array('in', array('1225', '1226', '1227', '1429'));
			$list_account = M('borrow_info bi')
				->order('second_verify_time')
				->field('real_name,add_time,mi.idcard,cell_phone,first_verify_time,bi.second_verify_time
					,bi.deadline,bi.borrow_money,bi.borrow_duration,borrow_uid,bi.id borrow_id,lz.borrow_type,repayment_type')
				->join('left join lzh_member_info as mi on mi.uid = bi.borrow_uid')
				// ->join('left join lzh_members as m on m.id = bi.borrow_uid')
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql().'<br>';die;

			//excel头部
			$row = array();
			$row[0] = array('数据唯一标识', '预留操作代码', '机构系统生成本记录时间', '姓名', '身份证号码', '手机号码', '贷款编号', '原贷款编号', '贷款担保类型', '贷款用途', '贷款申请时间', '账户开立时间', '贷款放款时间', '贷款到期日期', '贷款金额', '还款总期数', '账单日类型',
				'每期还款周期', '账单日列表', '首次还款日', '逾期宽限期', '设备信息', '设备类型', '设备IMEI/MEID', 'MAC地址', 'IP地址', '设备操作系统标签');
			$i = 1;

			$singleLoanAccountInfo = "#singleLoanAccountInfo" . "\r\n";//D2数据头
			if (!empty($list_account)) {
				foreach ($list_account as $key => $value) {

					//账单日类型判断：末期本息为固定周期  先息后本为固定日期  每期还款周期: 末期本息borrow_duration * 30 先息后本：-1
					if ($value['repayment_type'] == 4) {
						$billday_type = 2;
						$period_day = -1;
					} elseif ($value['repayment_type'] == 5) {
						if($value['borrow_duration'] > 12){
							$billday_type = 2;
							$period_day = -1;
						}elseif($value['borrow_duration'] == 12) {
							$billday_type = 1;
							$period_day = 365;
						} else {
							$billday_type = 1;
							$period_day = $value['borrow_duration'] * 30;
						}

					}


					//接口数据///////////////////////////
					$account['reqID'] = $value['borrow_id'] . "D2" . "U" . $value['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$account['opCode'] = 'A';//操作代码：A- “新增数据”，M-“修改数据”，D-“删除数据”
					$account['uploadTs'] = date("Y-m-d\TH:i:s", strtotime("+1 hour",$value['second_verify_time']));//记录生成时间
					
					if ($hide) {
						$account['name'] = mb_substr($value['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
						$account['pid'] = mb_substr($value['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
						$account['mobile'] = mb_substr($value['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
					} else {
						$account['name'] = $value['real_name'];//借款人姓名
						if (substr($value['idcard'], 17, 1) == 'x') {
							$account['pid'] = substr($value['idcard'], 0, 17) . 'X';
						} else {
							$account['pid'] = $value['idcard'];//身份证号码
						}
						$account['mobile'] = $value['cell_phone'];//手机号码
					}
					
					
					
					
					
					$account['loanId'] = 'B'.$value['borrow_id'];//贷款编号
					$account['originalLoanId'] = null;//原贷款编号
					if ($value['borrow_type'] == 1) {
						$account['guaranteeType'] = 2;//贷款担保类型 2 抵押
					} elseif ($value['borrow_type'] == 2) {
						$account['guaranteeType'] = 3;//贷款担保类型 3 质押
					} elseif ($value['borrow_type'] == 3) {
						$account['guaranteeType'] = 4;//贷款担保类型 4 保证
					}
					$account['loanPurpose'] = 1;//贷款用途
					$account['applyDate'] = date("Y-m-d\TH:i:s", $value['add_time']);//贷款申请时间
					$account['accountOpenDate'] = date("Y-m-d\TH:i:s", $value['first_verify_time']);//账户开立时间
					$account['issueDate'] = date("Y-m-d\TH:i:s", $value['second_verify_time']);//贷款放款时间
					$account['dueDate'] = date("Y-m-d", $value['deadline']);//贷款到期日期
					$account['loanAmount'] = getFloatValue($value['borrow_money'], 2);//贷款金额
					if ($value['repayment_type'] == 4) {
						$account['totalTerm'] = $value['borrow_duration'];//还款总期数
					} elseif ($value['repayment_type'] == 5) {
						$account['totalTerm'] = 1; //末期本息视为单期
					}
					$account['targetRepayDateType'] = $billday_type;//账单日类型
					$account['termPeriod'] = $period_day;//每期还款周期
					//获取还款记录信息
					$detail = M('investor_detail');
					$result = $detail->where('borrow_id=' . $value['borrow_id'])->select();
					foreach ($result as $k => $v) {
						$data[] = date("Y-m-d", $v['deadline']);
					}
					$data = array_unique($data);
					if ($value['repayment_type'] == 5) {
						if($value['borrow_duration'] > 12){
							$account['targetRepayDateList'] = date("Y-m-d", $value['deadline']);
						}else{
							$account['targetRepayDateList'] = '';
						}
					} elseif ($value['repayment_type'] == 4) {
						$account['targetRepayDateList'] = implode(",", $data);//账单日列表
					}
					$account['firstRepaymentDate'] = reset($data);//首次应还款日
					$account['gracePeriod'] = 7;//宽限期
					$account['device'] = array('deviceType' => 2, 'imei' => null, 'mac' => null, 'ipAddress' => null, 'osName' => 6);//设备信息

					//echo 'debug<br><pre>'; print_r($account);
					//echo $this->toJson($parama).'<br>';die;


					//excel导出
					if (false) {
						$export = 1;//设置是否导出；
						$row[$i]['reqID'] = $value['borrow_id'] . "D2" . "U" . $value['borrow_uid'];//记录唯一标识   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
						$row[$i]['opCode'] = 'A';//预留操作代码
						$row[$i]['uploadTs'] = date("Y-m-d\TH:i:s", strtotime("+1 hour",$value['second_verify_time']));//记录生成时间
						if ($hide) {
							$row[$i]['name'] = mb_substr($value['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
							$row[$i]['pid'] = mb_substr($value['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
							$row[$i]['mobile'] = mb_substr($value['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
						} else {
							$row[$i]['name'] = $value['real_name'];//姓名：只能为合法的中国姓名
							if (substr($value['idcard'], 17, 1) == 'x') {
								$row[$i]['pid'] = substr($value['idcard'], 0, 17) . 'X';
							} else {
								$row[$i]['pid'] = $value['idcard'];//身份证号码
							}
							$row[$i]['mobile'] = $value['cell_phone'];//手机号码
						}
						$row[$i]['loanId'] = $value['borrow_id'];//贷款/授信账户编号
						$row[$i]['originalLoanId'] = '';
						if ($value['borrow_type'] == 1) {
							$row[$i]['guaranteeType'] = 2;//贷款担保类型 2：抵押
						} elseif ($value['borrow_type'] == 2) {
							$row[$i]['guaranteeType'] = 3;//贷款担保类型 3：质押
						} elseif ($value['borrow_type'] == 3) {
							$row[$i]['guaranteeType'] = 4;//贷款担保类型 4：保证
						}
						$row[$i]['loanPurpose'] = 1;//贷款用途 1：无特定场景贷款
						$row[$i]['applyDate'] = date("Y-m-d\TH:i:s", $value['add_time']);//贷款申请时间
						$row[$i]['accountOpenDate'] = date("Y-m-d\TH:i:s", $value['first_verify_time']);//账户开立时间
						$row[$i]['issueDate'] = date("Y-m-d\TH:i:s", $value['second_verify_time']);//贷款放款时间
						$row[$i]['dueDate'] = date("Y-m-d", $value['deadline']);//贷款到期日期
						$row[$i]['loanAmount'] = getFloatValue($value['borrow_money'], 2);//贷款金额
						if ($value['repayment_type'] == 4) {
							$row[$i]['totalTerm'] = $value['borrow_duration'];//还款总期数
						} elseif ($value['repayment_type'] == 5) {
							$row[$i]['totalTerm'] = 1; //末期本息视为单期
						}

						$row[$i]['targetRepayDateType'] = $billday_type;//账单日类型
						$row[$i]['termPeriod'] = $period_day;//每期还款周期
						if ($value['repayment_type'] == 5) {
							if($value['borrow_duration'] > 12){
								$row[$i]['targetRepayDateList'] = date("Y-m-d", $value['deadline']);
							}else{
								$row[$i]['targetRepayDateList'] = '';
							}
						} elseif ($value['repayment_type'] == 4) {
							$row[$i]['targetRepayDateList'] = implode(",", $data);//账单日列表
						}
						$row[$i]['firstRepaymentDate'] = reset($data);//首次应还款日
						$row[$i]['gracePeriod'] = 7;//宽限期
						$row[$i]['device'] = '';
						$row[$i]['deviceType'] = '';
						$row[$i]['imei'] = '';
						$row[$i]['mac'] = '';
						$row[$i]['ipAddress'] = '';
						$row[$i]['osName'] = '';
						$i++;
					}

					unset($data);
					$singleLoanAccountInfo .= $this->toJson($account) . "\r\n";

				}
				$filename = 'D2';
				//echo $singleLoanAccountInfo;

			} else {
				echo "无新增贷款数据";
			}
		}
		
		//D3 接口
		if (true) {
			//D3贷款贷后还款数据
			//$status['bi.id'] = array('in', array('1810','1811','1812','1813','1814','1816','1817','1819','1821','1827','1829','1830','1832','1836','1838','1842'));
			//$status['bi.id'] = array('in',array('1691','1692','1693','1697','1700','1594','1600','1601','1225', '1226', '1227', '1429'));
			//$where['bi.id'] = array('in', array('1225', '1226', '1227', '1429'));
			//$where['bi.id'] = array('in',array('1522','1644','1650'));

			//获取以上标的 还款明细  id.repayment_time, id.borrow_id, id.investor_uid,, id.sort_order, id.total, id.status, id.deadline, id.capital, id.interest, id.receive_capital, id.receive_interest
			$list = M("borrow_info bi")
				->order('second_verify_time')
				->field("bi.id as bid,bi.is_advanced,has_pay,is_prepayment,bi.borrow_uid,bi.borrow_money,bi.borrow_status,repayment_type,lz.zhaiquan_name,real_name,lz.zhaiquan_idcard,mi.cell_phone,mi.idcard")
				->join("left join lzh_member_info mi on mi.uid = bi.borrow_uid")
				->join("left join lzh_zhaiquan lz on lz.zhaiquan_tid = bi.id")
				->where($status)
				//->limit(4)
				->select();
			//echo M()->getLastSql(); exit;

			//excel头部
			$row = array();
			$row[0] = array('数据唯一标识', '预留操作代码', '机构系统生成本记录时间', '贷款编号', '姓名', '身份证号码', '手机号码', '当前还款期数', '本期还款状态', '本期应还款日', '实际还款时间', '本期应还款金额', '本期剩余应还款金额', '本次还款金额', '当前逾期天数', '本期还款状态确认时间', '当前逾期总额',
				'贷款余额', '本笔贷款状态');
			$i = 1;

			$singleLoanRepayInfo = "#singleLoanRepayInfo" . "\r\n";
			$arr1 = array();
			$arr2 = array();

			foreach ($list as $k => $v) {
				$detail_where['borrow_id'] = $v['bid'];
				$detail_where['repayment_time'] = array('lt',strtotime('2019-10-14 23:59:59'));//截止到6月30号的还款数据
				//$detail_where['repayment_time'] = array('between',array(strtotime('2019-06-30 23:59:59'),strtotime('2019-08-19 23:59:59')));//截止到6月30号的还款数据
				$result = M('investor_detail')->where($detail_where)->select();
				foreach ($result as $kk => $vv) {
					$arr1[date('Y-m-d', $vv['repayment_time'])]['total'] = $vv['total'];
				}
				unset($arr1['1970-01-01']);//删除还款中标的未还款的期数
				//echo 'debug<br><pre>';print_r($arr1);die;
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
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];//代偿
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
						$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];//代偿
					}
				}
				unset($arr2['1970-01-01']);//删除还款中标的未还款的期数
				//组装接口数据
				foreach ($arr2 as $kkkk => $vvvv) {
					$parama['reqID'] = $v['bid'] . "D3" . "U" . $v['borrow_uid'] . "D" . $vvvv['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
					$parama['opCode'] = 'A';//操作代码   A- “新增数据”，M-“修改数据”，D-“删除数据
					$parama['uploadTs'] = date('Y-m-d\TH:i:s',strtotime("+2 hour", $vvvv['repayment_time']));//记录生成时间:
					$parama['loanId'] = 'B'.$v['bid'];//贷款编号
					
					
					//三要素脱敏
					if ($hide) {
						$parama['name'] = mb_substr($v['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
						$parama['pid'] = mb_substr($v['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
						$parama['mobile'] = mb_substr($v['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
					} else {
						$parama['name'] = $v['real_name'];//姓名
						if (substr($v['idcard'], 17, 1) == 'x') {
							$parama['pid'] = substr($v['idcard'], 0, 17) . 'X';
						} else {
							$parama['pid'] = $v['idcard'];//身份证号码
						}
						$parama['mobile'] = $v['cell_phone'];//手机号码
					}
					
					
					$parama['termNo'] = $vvvv['sort_order'];//当前还款期数
					if($vvvv['substitute_money'] > 0){
						$parama['termStatus'] = 'thirdPartyPayIn30';//本期还款状态
					}else{
						$parama['termStatus'] = 'normal';//本期还款状态
					}
					$parama['targetRepaymentDate'] = date('Y-m-d', $vvvv['deadline']);//本期应还款日
					$parama['realRepaymentDate'] = date('Y-m-d\TH:i:s', $vvvv['repayment_time']);//实际还款时间
					if (($v['repayment_type'] == 4 && $v['is_advanced'] != 0) || ($v['repayment_type'] == 4 && $v['is_prepayment'] != 0)){
						$parama['plannedPayment'] = getFloatValue($vvvv['receive_interest'], 2);//本期计划应还款金额
						$parama['targetRepayment'] = getFloatValue($vvvv['receive_interest'], 2);//本期剩余应还款金额
					}else{
						$parama['plannedPayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本期计划应还款金额
						$parama['targetRepayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本期剩余应还款金额
					}
					$parama['realRepayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本次还款金额
					$parama['overdueStatus'] = '';//当前逾期天数
					$parama['statusConfirmAt'] = date('Y-m-d\TH:i:s', strtotime("+1 hour", $vvvv['repayment_time']));//本笔还款状态确认时间
					$parama['overdueAmount'] = getFloatValue(0, 2);//当前逾期总额


					if ($v['repayment_type'] == 5) {
						$parama['remainingAmount'] = getFloatValue(0, 2);
					} elseif ($v['repayment_type'] == 4) { //是否是先息后本
						if ($v['borrow_status'] == 6) { //判断是不是还款中标的
							$parama['remainingAmount'] = getFloatValue($v['borrow_money'], 2);//贷款剩余额度
						} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
							if($v['is_advanced'] != 0 || $v['is_prepayment'] != 0 ){
								$parama['remainingAmount'] = getFloatValue(($vvvv['sort_order'] == $real_total) ? 0 : $v['borrow_money'], 2);
							}else{
								$parama['remainingAmount'] = getFloatValue(($vvvv['sort_order'] == $vvvv['total']) ? 0 : $v['borrow_money'], 2);
							}
							
						}
					}
					if ($v['repayment_type'] == 5) {
						$parama['loanStatus'] = 3;
					} elseif ($v['repayment_type'] == 4) {
						if ($v['borrow_status'] == 6) { //判断是不是还款中标的
							$parama['loanStatus'] = 1;
						} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
							if($v['is_advanced'] != 0 || $v['is_prepayment'] != 0 ){
								$parama['loanStatus'] = ($vvvv['sort_order'] == $real_total) ? 3 : 1;
							}else{
								$parama['loanStatus'] = ($vvvv['sort_order'] == $vvvv['total']) ? 3 : 1;
							}
						}
					}
					//unset($real_total);
					//echo 'debug<br><pre>';print_r($parama);
					//导出excel;
					if (false) {
						$export = 1;//设置导出；
						$row[$i]['reqID'] = $v['bid'] . "D3" . "U" . $v['borrow_uid'] . "D" . $vvvv['sort_order'];//reqId   string (0,40]  机构本条记录的唯一标识，且由数字和字母构成，不含数字及字母以外的字符。
						$row[$i]['opCode'] = 'A';//操作代码   A- “新增数据”，M-“修改数据”，D-“删除数据
						$row[$i]['uploadTs'] = date('Y-m-d\TH:i:s', strtotime("+2 hour", $vvvv['repayment_time']));//记录生成时间:
						$row[$i]['loanId'] = $v['bid'];//贷款编号
						if ($hide) {
							$row[$i]['name'] = mb_substr($v['real_name'], 0, 1, 'utf-8') . "测试";//姓名：只能为合法的中国姓名 脱敏显示
							$row[$i]['pid'] = mb_substr($v['idcard'], 0, 14) . "0000";//身份证号码 脱敏显示
							$row[$i]['mobile'] = mb_substr($v['cell_phone'], 0, 7) . "0000";//手机号码  脱敏显示
						} else {
							$row[$i]['name'] = $v['real_name'];//姓名
							if (substr($v['idcard'], 17, 1) == 'x') {
								$row[$i]['pid'] = substr($v['idcard'], 0, 17) . 'X';
							} else {
								$row[$i]['pid'] = $v['idcard'];//身份证号码
							}
							$row[$i]['mobile'] = $v['cell_phone'];//手机号码
						}
						$row[$i]['termNo'] = $vvvv['sort_order'];//当前还款期数
						if($vvvv['substitute_money'] > 0){
							$row[$i]['termStatus'] = 'thirdPartyPayIn30';//本期还款状态
						}else{
							$row[$i]['termStatus'] = 'normal';//本期还款状态
						}
						$row[$i]['targetRepaymentDate'] = date('Y-m-d', $vvvv['deadline']);//本期应还款日
						$row[$i]['realRepaymentDate'] = date('Y-m-d\TH:i:s', $vvvv['repayment_time']);//实际还款时间
						if (($v['repayment_type'] == 4 && $v['is_advanced'] != 0) || ($v['repayment_type'] == 4 && $v['is_prepayment'] != 0)){
							$row[$i]['plannedPayment'] = getFloatValue($vvvv['receive_interest'], 2);//本期计划应还款金额
							$row[$i]['targetRepayment'] = getFloatValue($vvvv['receive_interest'], 2);//本期剩余应还款金额
						}else{
							$row[$i]['plannedPayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本期计划应还款金额
							$row[$i]['targetRepayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本期剩余应还款金额
						}
						$row[$i]['realRepayment'] = getFloatValue(($vvvv['receive_capital'] + $vvvv['receive_interest']), 2);//本次还款金额
						$row[$i]['overdueStatus'] = '';//当前逾期天数
						$row[$i]['statusConfirmAt'] = date('Y-m-d\TH:i:s', strtotime("+1 hour", $vvvv['repayment_time']));//本笔还款状态确认时间
						$row[$i]['overdueAmount'] = getFloatValue(0, 2);//当前逾期总额
						if ($v['repayment_type'] == 5) {
							$row[$i]['remainingAmount'] = getFloatValue(0, 2);
						} elseif ($v['repayment_type'] == 4) { //是否是先息后本
							if ($v['borrow_status'] == 6) { //判断是不是还款中标的
								$row[$i]['remainingAmount'] = getFloatValue($v['borrow_money'], 2);//贷款剩余额度
							} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
								if($v['is_advanced'] != 0 || $v['is_prepayment'] != 0 ){
									$row[$i]['remainingAmount'] = getFloatValue(($vvvv['sort_order'] == $real_total) ? 0 : $v['borrow_money'], 2);
								}else{
									$row[$i]['remainingAmount'] = getFloatValue(($vvvv['sort_order'] == $vvvv['total']) ? 0 : $v['borrow_money'], 2);
								}
							}
						}
						if ($v['repayment_type'] == 5) {
							$row[$i]['loanStatus'] = 3;
						} elseif ($v['repayment_type'] == 4) {
							if ($v['borrow_status'] == 6) { //判断是不是还款中标的
								$row[$i]['loanStatus'] = 1;
							} elseif ($v['borrow_status'] == 7 || $v['borrow_status'] == 9) {
								if($v['is_advanced'] != 0 || $v['is_prepayment'] != 0 ){
									$row[$i]['loanStatus'] = ($vvvv['sort_order'] == $real_total) ? 3 : 1;
								}else{
									$row[$i]['loanStatus'] = ($vvvv['sort_order'] == $vvvv['total']) ? 3 : 1;
								}
								
							}
						}
						$i++;
					}

					$singleLoanRepayInfo .= $this->toJson($parama) . "\r\n";
					unset($arr1, $arr2);
				}
			}
			$filename = 'D3';
			//echo $singleLoanRepayInfo;
		}

		if ($export) {
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}


		// $all = $loanApplyInfo.$singleLoanAccountInfo.$singleLoanRepayInfo;
		// echo $all;
		
		// if(!empty($loanApplyInfo)){
		// 	echo $this->createZip(iconv("UTF-8", "GB2312//IGNORE", $this->bhfileName['C1']), $loanApplyInfo);
		// }else{
		// 	echo "C1数据为空";
		// }
		//
		// if(!empty($singleLoanAccountInfo)){
		// 	$this->createZip(iconv("UTF-8", "GB2312//IGNORE", $this->bhfileName['D2']), $singleLoanAccountInfo);
		// }else{
		// 	echo "D2数据为空";
		// }
		//
		// if(!empty($singleLoanRepayInfo)){
		// 	$this->createZip(iconv("UTF-8", "GB2312//IGNORE", $this->bhfileName['D3']), $singleLoanRepayInfo);
		// }else{
		// 	echo "D3数据为空";
		// }


	}


	//信息共享平台接口(首次报送)
	//截止到报送月月初的还款中标的数据
	public function first_sub()
	{
		$hide = 1;
		$start_time = strtotime('2019-02-01 00:00:00');
		$end_time = strtotime('2019-02-28 23:59:59');
		$march_time = strtotime('2019-03-1 00:00:00');
		//三月之前注册且处在还款中的标的
		$status['second_verify_time&borrow_status'] = array(array('lt', $end_time), array('eq', 6), '_multi' => true);
		//$status['bi.id'] = 1547;
		$list = M("borrow_info bi")
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
		$feb = M('investor_detail id')
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
		$list_all = M("borrow_info bi")
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
			$result = M('investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
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
				. "<br>";

			//导出excel;
			if (true) {
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
		//$this->creZip($txtname,$info);
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
		$list = M("borrow_info bi")
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
		$feb = M('investor_detail id')
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
		$mar = M('investor_detail id')
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
		$list = M("borrow_info bi")
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
			$result = M('investor_detail')->field('repayment_time,borrow_id')->where($where)->select();
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
				. "<br>";

			//导出excel;
			if (true) {
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
		//$this->creZip($txtname,$info);

		if ($export) {
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}

	}


	public function toJson($array)
	{
		$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
	}

	public function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
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

	public function randReqID($param)
	{
		return $param . date('YmdHis') . str_pad(mt_rand(1, 99999), 8, '0', STR_PAD_LEFT); //流水号
	}


	//中互金项目信息
	public function zhj_borrow()
	{

		// $borrow_ids_arr = ''; //引入上次上报的标的ID
		$borrow_ids_arr = FS("Webconfig/borrow_ids_arr");

		// echo 'debug<br><pre>'; print_r($borrow_ids_arr); var_dump($borrow_ids_arr); exit;

		$where['borrow_status'] = 6;
		// $where['second_verify_time&borrow_status'] = array(array('gt','1472054399'),array('in',array('7','9')),'_multi'=>true);

		//$where['second_verify_time&borrow_status&is_advanced&lz.zhaiquan_bankinfo'] = array(array('gt','1472054399'),array('in',array('7', '9')),array('in',array('1','2')),array('NEQ',''),'_multi'=>true);//提前还款
		//$where['is_prepayment&lz.zhaiquan_bankinfo'] = array(1,array('NEQ',''),'_multi'=>true);//提前还款
		// $where['is_advanced'] = 0;//非提前还款
		// $where['is_prepayment'] = 0;//非提前还款
		// $where['lz.zhaiquan_bankinfo'] = array('NEQ','');//非提前还款
		// $where['bi.id'] = array('not in', $borrow_ids_arr);
		//$where['_logic'] = 'OR';
		//$where['bi.id'] = 1349;//1835 1834 1614 1613 1604 1563  array('in','1,2,3');
		//$where['bi.id'] = array('in','1032,915,914,910,916,976,977,979,1765,1789');
		$list = M('borrow_info bi')
			->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate,
        borrow_duration,repayment_type,borrow_fee,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,
        idcode,sex,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where)
			// ->limit(50)
			->select();
		echo M()->getLastSql();
		echo "<hr>";


		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息


		$borrow_ids = array();


		$row = array();
		$row[0] = array('项目唯一编号', '社会信用代码', '平台序号', '项目编号', '项目类型', '项目名称', '项目成立日期', '借款金额', '借款币种', '借款起息日', '借款到期日期', '借款期限', '出借利率', '项目费率', '项目费用', '其他费用', '还款保证措施', '还款期数', '担保方式', '担保公司名称', '约定还款计划', '实际还款记录', '实际累计本金偿还额', '实际累计利息偿还额', '借款剩余本金余额', '借款剩余应付利息', '是否支持转让', '项目状态', '逾期原因', '逾期次数', '还款方式', '借款用途', '出借人个数');
		$i = 1;


		$detail = M('investor_detail');
		foreach ($list as $key => $value) {
			// if(in_array($value['id'], $borrow_ids_arr)){
			// 	continue;
			// }


			$result = $detail->where('borrow_id=' . $value['id'])->select();
			//echo 'debug<br><pre>'; print_r($result); //exit;
			//约定还款计划；
			$arr = array();
			foreach ($result as $k => $v) {
				$arr[$v['deadline']]['capital'] += $v['capital'];
				$arr[$v['deadline']]['interest'] += $v['interest'];
				$arr[$v['deadline']]['deadline'] = $v['deadline'];
			}
			foreach ($arr as $kk => $vv) {
				$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
			}
			//实际还款记录
			//先息后本提前还款记录
			if ($value['repayment_type'] == 4 && $value['is_advanced'] != 0) {
				$arr1 = array();
				foreach ($result as $kkk => $vvv) {
					// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
					// $arr1[$value['id']."-".date('Y-m-d',$vvv['repayment_time'])]['repayment_time'] = date('Y-m-d',$vvv['repayment_time']);
					$arr1[$value['id'] . "-" . date('Y-m-d', $vvv['repayment_time'])]['total'] = $vvv['total'];
				}
				//unset($arr1['1970-01-01']);
				//print_r($arr1);die;
				//echo count($arr1);die;
				//$total = $result[0]['total'];
				//echo $total;die;
				//判断是否为最后一期提前还款
				if (count($arr1) != $result[0]['total']) {
					$arr3 = array();
					foreach ($result as $real => $re) {
						if ($re['sort_order'] < count($arr1)) {
							$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['receive_interest'] += $re['receive_interest'];
						}
						if ($re['sort_order'] == count($arr1)) {
							$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['receive_interest'] += $re['receive_interest'];
						}
						$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['receive_capital'] += $re['receive_capital'];
						$arr3[$value['id'] . "-" . date('Y-m-d', $re['repayment_time'])]['repayment_time'] = date('Y-m-d', $re['repayment_time']);
						$arr3[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
					}
					//print_r($arr3);
					unset($arr1);
					$arr1 = array();
					$arr1 = $arr3;
				} else {
					$arr1 = array();
					foreach ($result as $kkk => $vvv) {
						$arr1[$value['id'] . "-" . date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
						$arr1[$value['id'] . "-" . date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
						$arr1[$value['id'] . "-" . date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = date('Y-m-d', $vvv['repayment_time']);
						$arr1[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
					}
					unset($arr1['1970-01-01']);
					//print_r($arr1);die;
				}
			} else {
				//非提前还款实际还款记录
				$arr1 = array();
				foreach ($result as $kkk => $vvv) {
					$arr1[date('Y-m-d', $vvv['repayment_time'])]['receive_capital'] += $vvv['receive_capital'];
					$arr1[date('Y-m-d', $vvv['repayment_time'])]['receive_interest'] += $vvv['receive_interest'];
					$arr1[date('Y-m-d', $vvv['repayment_time'])]['repayment_time'] = date('Y-m-d', $vvv['repayment_time']);
					$arr1[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
				}

				unset($arr1['1970-01-01']);
			}

			//print_r($arr1);die;
			//末期本息未到还款期限
			if ($value['repayment_type'] == 5 && $value['borrow_status'] == 6) {
				$data1[] = date("Y-m-d", $value['second_verify_time']) . ":" . '0' . ":" . '0' . ":" . "01";
			} elseif ($value['repayment_type'] == 4 && $value['borrow_status'] == 6 && $value['has_pay'] == 0) {
				//先息后本项目成立后还未发生还款
				$data1[] = date("Y-m-d", $value['second_verify_time']) . ":" . '0' . ":" . '0' . ":" . "01";
			} else {
				//已经发生还款
				foreach ($arr1 as $kkkk => $vvvv) {
					$data1[] = $vvvv['repayment_time'] . ":" . getFloatValue($vvvv['receive_capital'], 4) . ":" . getFloatValue($vvvv['receive_interest'], 4) . ":" . ($vvvv['substitute_money'] == 0 ? "01" : "03");
				}
			}

			//实际累计本金、利息偿还额
			$present_capital = '';
			$present_interest = '';
			//末期本息未到还款期限
			if ($value['repayment_type'] == 5 && $value['borrow_status'] == 6) {
				$present_capital = 0;
				$present_interest = 0;
				//先息后本项目成立后还未发生还款
			} elseif ($value['repayment_type'] == 4 && $value['borrow_status'] == 6 && $value['has_pay'] == 0) {
				$present_capital = 0;
				$present_interest = 0;
			} else {
				foreach ($result as $item => $it) {
					$present_capital += $it['receive_capital'];//累计偿还本金
				}
				foreach ($arr1 as $pppp => $pp) {
					$present_interest += $pp['receive_interest'];//累计偿还利息
				}
			}

			//根据还款方式判断本笔贷款状态  4:先息后本; 5:末期本息;
			if ($value['borrow_status'] == 6) { //还款中
				$repayment_status = '02';
			} elseif ($value['borrow_status'] == 7 || $value['borrow_status'] == 9) { //正常还款已结束
				$repayment_status = '03';
			} elseif ($value['borrow_status'] == 7 && $value['is_prepayment'] == 1) { //提前还款已结束
				$repayment_status = '04';
			} elseif ($value['borrow_status'] == 7 && $value['is_advanced'] != 0) { //标识标的 提前还款已结束
				$repayment_status = '04';
			}
			//剩余本金、利息
			if ($repayment_status == '03' || $repayment_status == '04') {
				$capital_last = 0;
				$interest_last = 0;
			} else {
				$capital_last = $value['borrow_money'] - $present_capital;
				$interest_last = $value['borrow_interest'] - $present_interest;
			}
			//还款方式
			if ($value['repayment_type'] == 4) {
				$repayment_type = '01';
			} elseif ($value['repayment_type'] == 5) {
				$repayment_type = '05';
			}
			//出借人个数
			// $investor_num = M('investor_detail')->where('borrow_id='.$value['id'])->count('DISTINCT investor_uid');
			$investor_num = M('borrow_investor')->where('borrow_id=' . $value['id'])->count();
			//echo $investor_num;die;

			$motrgage = $value['mortgage'];


			// if(!empt($value['fee_option']) && false){
			// 	//不用此数据
			// 	$fee_option = implode('|', $value['fee_option']);
			// 	$borrow_fee = 0;
			// 	foreach ($fee_option as $key => $value) {
			// 		$borrow_fee += $value;
			// 	}
			// }else{
			switch ($value['borrow_duration']) {
				case '1':
					$borrow_fee_rate = (11.76 + 1) / 100;
					break;
				case '3':
					$borrow_fee_rate = (10.56 + 1.6) / 100;
					break;
				case '6':
					$borrow_fee_rate = (9.96 + 2.2) / 100;
					break;
				case '12':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
					break;
				case '18':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
					break;
				case '24':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
					break;
			}
			$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
			// }


			//项目信息
			$borrow_info .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
				"91320200323591589D" . "|" .//社会信用代码
				"1" . "|" .//平台序号".
				$value['id'] . "|" .//项目编号
				"01" . "|" .//项目类型 //个体直接借贷
				$value['borrow_name'] . "|" .//项目名称
				date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
				getFloatValue($value['borrow_money'], 4) .//借款金额
				"|CNY|" .//借款币种
				date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
				date("Ymd", $value['deadline']) . "|" .//借款到期日期
				ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
				getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
				getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
				getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
				getFloatValue(0, 4) . "|" .//其他费用
				"02" . "|" .//还款保证措施
				$value['borrow_duration'] . "|" .//还款期数
				"02" . "|" .//担保方式
				$motrgage . "|" .//担保公司名称
				implode(";", $data) . "|" .//约定还款计划
				implode(";", $data1) . "|" .//实际还款记录
				getFloatValue($present_capital, 4) . "|" .//实际累计本金偿还额
				getFloatValue($present_interest, 4) . "|" .//实际累计利息偿还额
				getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
				getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
				"1" . "|" .//是否支持转让
				$repayment_status . "|" .//项目状态
				"|" .//逾期原因
				"|" .//逾期次数
				$repayment_type . "|" .//还款方式
				"03" . "|" .//借款用途
				$investor_num//出借人个数
				. "\r\n";


			if (false) {
				//导出EXCEL
				$row[$i]['id'] = "91320200323591589D1" . $value['id'];
				$row[$i]['no2'] = '91320200323591589D';
				$row[$i]['no3'] = '1';
				$row[$i]['no4'] = $value['id'];
				$row[$i]['no5'] = '01';
				$row[$i]['no6'] = $value['borrow_name'];
				$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
				$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
				$row[$i]['no9'] = 'CNY';
				$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
				$row[$i]['no11'] = date("Ymd", $value['deadline']);
				$row[$i]['no12'] = ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
				$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
				$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
				$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
				$row[$i]['no16'] = getFloatValue(0, 4);
				$row[$i]['no17'] = '02';
				$row[$i]['no18'] = $value['borrow_duration'];
				$row[$i]['no19'] = '02';
				$row[$i]['no20'] = $motrgage;
				$row[$i]['no21'] = implode(";", $data);
				$row[$i]['no22'] = implode(";", $data1);
				$row[$i]['no23'] = getFloatValue($present_capital, 4);
				$row[$i]['no24'] = getFloatValue($present_interest, 4);
				$row[$i]['no25'] = getFloatValue($capital_last, 4);
				$row[$i]['no26'] = getFloatValue($interest_last, 4);
				$row[$i]['no27'] = '1';
				$row[$i]['no28'] = $repayment_status;
				$row[$i]['no29'] = '';
				$row[$i]['no30'] = '';
				$row[$i]['no31'] = $repayment_type;
				$row[$i]['no32'] = '03';
				$row[$i]['no33'] = $investor_num;

				$i++;
			}
			unset($data);
			unset($data1);


			//取消执行
			if (true) {
				//借款人累计借款次数
				// $capitalinfo = getMemberBorrowScan($value['borrow_uid']);
				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();
				// $num = $capitalinfo['tj']['jkcgcs'];

				//借款角色

				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}


				//借款人信息
				$borrower .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n

				//存储所有标的ID
				$borrow_ids[] = $value['id'];
			}
			//取消执行END

		}

		// import("ORG.Io.Excel");
		// $xls = new Excel_XML('UTF-8', false, 'load_borrow_userlist_20190315');
		// $xls->addArray($row);
		// $xls->generateXML("load_borrow_userlist_20190315");
		// echo 'OK';
		//
		// exit;
		//导出数据

		//记录已上传的标的ID
		// tracerLog($borrow_ids, 'borrow_ids.log');

		//写入配置文件中
		//FS("borrow_ids_arr",$borrow_ids, "Webconfig/");

		//出借人信息
		//$status['borrow_status'] = 6;
		// $status['second_verify_time&borrow_status'] = array(array('gt','1472054399'),7,'_multi'=>true);

		$status2['id.borrow_id'] = array('in', $borrow_ids);
		// $status['_logic'] = 'OR';
		//$status['lbi.id'] = 1835;
		//$status['lbi.id'] = array('in','1032,915,914,910,916,976,977,979,1765,1789');
		$investor_arr = M('borrow_investor id')
			// ->distinct(true)
			->field('borrow_id,investor_uid,investor_capital,idcard')
			// ->join('lzh_member_jshbank as lmj on lmj.uid = id.investor_uid')
			->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
			// ->where($status)
			->where($status2)
			//->limit(10)
			->select();
		//echo M()->getLastSql();echo "<hr>";
		//echo 'debug<br><pre>'; print_r($investor_arr); exit;
		foreach ($investor_arr as $invest => $inv) {
			// $investor_list = M('investor_detail')->where('borrow_id='.$inv['id'])->select();
			// //echo 'debug<br><pre>'; print_r($investor_list); exit;
			// $total_investor = '';
			// foreach($investor_list as $zhj=>$z){
			//     if($z['investor_uid'] == $inv['investor_uid']){
			//         $total_investor += $z['capital'];
			//     }
			// }
			//出借人身份证号码
			if (substr($inv['idcard'], 17, 1) == 'x') {
				$idcode_investor = substr($inv['idcard'], 0, 17) . 'X';
			} else {
				$idcode_investor = $inv['idcard'];
			}

			//$idcode_investor = $inv['idcode']==''?$inv['idcard']:$inv['idcode'];
			// $idcode_investor = $inv['idcard'];

			$investor .= "91320200323591589D1" . $inv['borrow_id'] . "|" .//项目唯一编号
				"01" . "|" .//出借人类型
				$inv['investor_uid'] . "|" .//出借人ID
				"01" . "|" .//证件类型
				$idcode_investor . "|" .//证件号码////////////////////////////待添加
				"|" .//职业类型
				"|" .//所属地区
				"|" .//所属行业
				getFloatValue($inv['investor_capital'], 4) . "|" .//出借金额
				"01"//出借状态
				. "\r\n";//\r\n
			unset($total_investor);
		}


		// echo $borrow_info;echo "<hr>";
		// echo $borrower;echo "<hr>";
		// echo $investor;

		//生成txt文件
		$this->getTxt($this->fileName['binfo'], $borrow_info);
		$this->getTxt($this->fileName['binfobor'], $borrower);
		$this->getTxt($this->fileName['binfoinr'], $investor);

	}


	//按天生成中互金上报数据(存量数据)
	public function create_nifa_borrow()
	{


		$is_export = 0; //是否导出EXCEL文件

		//已确认存管标提前还款标的
		$repayment_borrowlist = array('1561', '1599', '1636', '1548', '1586', '1639', '1574', '1541', '1593', '1581', '1596', '1590', '1603', '1669', '1699', '1580', '1700', '1585', '1611', '1683', '1595', '1688', '1549', '1533', '1612', '1658', '1635', '1621', '1592', '1577', '1751', '1527', '1557', '1563', '1648', '1691', '1521', '1719', '1597', '1714', '1649', '1587', '1815', '1641', '1749', '1689', '1556', '1571', '1784', '1529', '1539', '1619', '1693', '1729', '1779', '1604', '1633', '1614', '1631', '1674', '1656', '1690', '1665', '1778', '1676', '1835', '1757', '1735', '1695', '1742', '1794', '1576', '1605', '1713', '1613', '1834', '1745', '1843', '1823');

		$row = array();
		$row[0] = array('项目唯一编号', '社会信用代码', '平台序号', '项目编号', '项目类型', '项目名称', '项目成立日期', '借款金额', '借款币种', '借款起息日', '借款到期日期', '借款期限', '出借利率', '项目费率', '项目费用', '其他费用', '还款保证措施', '还款期数', '担保方式', '担保公司名称', '约定还款计划', '实际还款记录', '实际累计本金偿还额', '实际累计利息偿还额', '借款剩余本金余额', '借款剩余应付利息', '是否支持转让', '项目状态', '逾期原因', '逾期次数', '还款方式', '借款用途', '出借人个数');
		$i = 1;


		if (!IS_POST) {
			// $postdays = 10; //定义跑多少天
			// $this->assign('postdays', $postdays);
			$this->display();

			return;
		}


		// $begin_date = strtotime('2016-8-24') + $days * 24*3600;
		// $begin_date = strtotime('2016-8-24') + $days * 24*3600;

		$days = intval($_POST['day']);
		//结束时间
		$stop_time = strtotime('2016-12-31 23:59:59');

		//已结清
		$borrow_verify_time = strtotime('2016-08-24 23:59:59');
		$begin_time = strtotime('2016-09-30 23:59:59') + $days * 24 * 3600;
		$end_time = strtotime('2016-10-02 00:00:00') + $days * 24 * 3600;


		if ($begin_time >= $stop_time) {

			$return['status'] = 2;
			$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
			$return['info'] = '上报结束';
			exit(json_encode($return));
		}


		$where['second_verify_time'] = array('between', array($begin_time, $end_time));
		$where['borrow_status'] = array('in', array('7', '9'));


		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息
		$borrow_ids = array(); //记录已生成的标的


		//项目成立数据
		if (true) {
			$list = M('borrow_info bi')
				->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
				->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
				->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
				->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
				->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
				->where($where)
				// ->limit(50)
				->select();

			$detail = M('investor_detail');
			foreach ($list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = $detail->where('borrow_id=' . $value['id'])->select();
				//约定还款计划
				$arr = array();
				$investor_interest = 0; //实际包括加息券、投资红包的
				foreach ($result as $k => $v) {
					$investor_interest += $v['interest'];
					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}
				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}
				//实际还款计划
				$real_repaymentlist = date("Y-m-d", $value['second_verify_time']) . ':0:0:01';

				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				$repayment_status = '01';

				//剩余本金、利息
				$capital_last = $value['borrow_money'];
				$interest_last = $investor_interest; //$value['borrow_interest'];

				//还款方式
				if ($value['repayment_type'] == 4) {
					$repayment_type = '01';
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05';
				}

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['id'])->count();

				//担保方式
				if ($value['borrow_type'] == 1) {
					$guarantee_way = '02';
				} elseif ($value['borrow_type'] == 2) {
					$guarantee_way = '03';
				}

				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['deadline']) . "|" .//借款到期日期
					ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					$guarantee_way . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}
				//借款人信息
				$borrower .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['id'];
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['deadline']);
					$row[$i]['no12'] = ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = getFloatValue($present_capital, 4);
					$row[$i]['no24'] = getFloatValue($present_interest, 4);
					$row[$i]['no25'] = getFloatValue($capital_last, 4);
					$row[$i]['no26'] = getFloatValue($interest_last, 4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}
				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['id'];

			}
		}

		//还款数据
		$where2['repayment_time'] = array('between', array($begin_time, $end_time));
		$where2['second_verify_time'] = array('gt', $borrow_verify_time);
		// $where2['borrow_id'] = '864';
		$repayment_list = M('investor_detail id')
			->group('borrow_id')
			->field('id.repayment_time, id.borrow_id, id.capital, id.interest, id.receive_interest, id.receive_capital, id.deadline,id.sort_order,id.total, bi.borrow_name,bi.second_verify_time,bi.borrow_money,bi.deadline as borrow_deadline,bi.borrow_interest_rate, bi.borrow_duration,bi.repayment_type,bi.borrow_interest,bi.borrow_uid,bi.borrow_status,bi.borrow_zhaiquan, bi.is_prepayment,bi.is_advanced,lz.zhaiquan_idcard,lz.type as lztype, lz.zhaiquan_bankinfo,lz.mortgage,lz.borrow_type')
			->join('lzh_borrow_info as bi on bi.id = id.borrow_id')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where2)
			// ->limit(50)
			->select();

		if (!empty($repayment_list)) {
			foreach ($repayment_list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = M('investor_detail')->where('borrow_id=' . $value['borrow_id'])->select();
				//约定还款计划
				$arr = array();

				//实际累计本金、利息偿还额
				$total_present_capital = '0'; //累计还款本金
				$total_present_interest = 0; //项目总还款利息

				$repay_present_capital = '0'; //累计还款本金
				$repay_present_interest = 0; //累计还款利息

				$current_present_capital = '0'; //当期本金总和
				$current_present_interest = '0'; //当期利息总和

				$current_repayment_time = '';

				foreach ($result as $k => $v) {
					$total_present_interest += $v['receive_interest'];//累计偿还利息
					//所有小于当期的数据
					if ($v['sort_order'] <= $value['sort_order']) {
						$repay_present_interest += $v['receive_interest'];//累计偿还利息
					}
					if ($v['sort_order'] == $value['sort_order']) {
						$current_present_interest += $v['receive_interest'];//当期偿还利息
						$current_repayment_time = $v['repayment_time'];
					}

					// if($value['sort_order'] < $value['total'] ){
					// 	// $next_sort =
					// }

					// if(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] < $value['total']){
					// 	// $next_repayment_time[] = $result[$k+1]['repayment_time'];
					// 	$next_repayment_time =  $value['repayment_time'] + date('t') * 24 * 3600; //下期还款日期
					// }elseif(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] == $value['total']){
					// 	//最后一期
					// }

					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}

				// echo 'debug<br><pre>'; echo $repay_present_interest; print_r($value); print_r($result); exit;

				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}


				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				//默认还款中
				$repayment_status = '02';

				//实际还款记录  当期实际的还款日期、本金、利息、还款来源
				if ($value['sort_order'] < $value['total']) {
					//不是最后一期
					$current_present_capital = 0;
					$next_sort = $value['sort_order'] + 1;
					$next_repayment_time_real = M('investor_detail')->where(array('borrow_id' => $value['borrow_id'], 'sort_order' => $next_sort))->select();

					if (date('Y-m-d', $next_repayment_time_real[0]['repayment_time']) == date('Y-m-d', $value['repayment_time'])) {
						$repayment_status = '04';
						//当下一期的还款时间等于当期还款时间，则标的为提前还款标的
						// 且还款本金=标的金额
						$current_present_capital = $value['borrow_money'];
					}

				} else {
					//最后一期
					$current_present_capital = $value['borrow_money'];
					// $total_present_capital = $value['borrow_money'];

					if (date('Y-m-d', $value['repayment_time']) < date('Y-m-d', $value['deadline'])) {
						$repayment_status = '04';
					} else {
						$repayment_status = '03';
					}
				}
				$real_repaymentlist = date("Y-m-d", $current_repayment_time) . ':' . getFloatValue($current_present_capital, 4) . ':' . getFloatValue($current_present_interest, 4) . ':01';


				if ($value['repayment_type'] == 4) {
					$repayment_type = '01'; //还款方式
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05'; //还款方式
				}

				//实际累计本金偿还额 ，实际累计利息偿还额 取标的的信息
				if ($repayment_status == 3) {
					//正常还款结束
					$total_present_capital = $value['borrow_money'];
					// $repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} elseif ($repayment_status == 4) {
					//提前还款结束
					$total_present_capital = $value['borrow_money'];
					$repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} else {
					//正常还款
					//剩余本金、利息
					$capital_last = $value['borrow_money'] - $current_present_capital;
					$interest_last = $total_present_interest - $repay_present_interest;
				}


				$capital_last .= ' =' . $value['borrow_money'] . ' - ' . $current_present_capital;
				$interest_last .= ' =' . $total_present_interest . ' - ' . $repay_present_interest;

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['borrow_id'])->count();

				//担保方式
				if ($value['borrow_type'] == 1) {
					$guarantee_way = '02';
				} elseif ($value['borrow_type'] == 2) {
					$guarantee_way = '03';
				}


				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['borrow_id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['borrow_deadline']) . "|" .//借款到期日期
					// round(($d2-$d1)/3600/24)
					// 1469079439    1500652799
					//timediff($value['borrow_deadline'], $value['second_verify_time'])."|".

					ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					$guarantee_way . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($total_present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($repay_present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}


				//借款人信息
				$borrower .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['borrow_id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['borrow_id'] . '-还款标';
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['borrow_deadline']);
					$row[$i]['no12'] = ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = $total_present_capital; //getFloatValue($total_present_capital,4);
					$row[$i]['no24'] = $repay_present_interest; //getFloatValue($repay_present_interest,4);
					$row[$i]['no25'] = $capital_last; //getFloatValue($capital_last,4);
					$row[$i]['no26'] = $interest_last; //getFloatValue($interest_last,4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}

				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['borrow_id'];
			}
		}


		if ($is_export) {
			$filename = 'load_borrow_userlist_' . date('Y-m-d', $begin_time) . '~' . date('Y-m-d', $end_time);
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}


		//出借人记录
		$status2['id.borrow_id'] = array('in', $borrow_ids);
		$investor_arr = M('borrow_investor id')
			->field('borrow_id,investor_uid,investor_capital,idcard')
			->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
			->where($status2)
			//->limit(10)
			->select();

		foreach ($investor_arr as $invest => $inv) {
			//出借人身份证号码
			if (substr($inv['idcard'], 17, 1) == 'x') {
				$idcode_investor = substr($inv['idcard'], 0, 17) . 'X';
			} else {
				$idcode_investor = $inv['idcard'];
			}

			$investor .= "91320200323591589D1" . $inv['borrow_id'] . "|" .//项目唯一编号
				"01" . "|" .//出借人类型
				$inv['investor_uid'] . "|" .//出借人ID
				"01" . "|" .//证件类型
				$idcode_investor . "|" .//证件号码////////////////////////////待添加
				"|" .//职业类型
				"|" .//所属地区
				"|" .//所属行业
				getFloatValue($inv['investor_capital'], 4) . "|" .//出借金额
				"01"//出借状态
				. "\r\n";//\r\n
		}
		// echo $borrow_info."<hr>";
		// echo $borrower."<hr>";
		// echo $investor."<br><br>";

		if (true) {
			//生成txt文件
			$filedir = '91320200323591589D' . date('Ymd', ($begin_time + 1)) . '24001';

			if (empty($borrow_info) || empty($borrower) || empty($investor)) {

				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 2, //2为数据空
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				// $chk = M('nifa_tongji')->where(array('filename' =>$filedir))->find();
				// if(!empty($chk)){
				// 	$adddata['id'] = $chk['id'];
				// 	$adddata['status'] = $return['success'] == 'true' ? 1 : 0;
				// 	$adddata['update_time'] = NOW_TIME;
				// }
				M('nifa_tongji')->add($adddata);


				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = '当天数据为空';
				exit(json_encode($return));

			}

			$dataTxt = array(
				'borrow_info' => $borrow_info,
				'borrower' => $borrower,
				'investor' => $investor,
			);

			$create_res = $this->createTxt($dataTxt, $filedir);
			// $this->createTxt($this->fileName['binfobor'], $borrower, $filedir);
			// $this->createTxt($this->fileName['binfoinr'], $investor, $filedir);

			if ($create_res) {
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 1, //
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				//上报数据
				$sourcePath = 'D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/' . $filedir . '.zip';

				$nifa_url = 'http://localhost:8888/nifa/sftp/upload?systemid=1&stype=24&sourcePath=' . urlencode($sourcePath);

				//D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/91320200323591589D2017052524001.zip
				// header("Location:".$nifa_url);

				$result = $this->getUrl($nifa_url);
				$return = json_decode($result, true);
				if ($return['success'] == 'true') {
					$adddata['post_status'] = 1;
					$msg = 'zip文件生成成功，上报成功';
				} else {
					$adddata['post_status'] = 0;
					$msg = 'zip文件生成成功，上报失败';
				}

				M('nifa_tongji')->add($adddata);

				$return['status'] = 1;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . $msg;
				exit(json_encode($return));
			} else {
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . 'zip文件生成失败';
				exit(json_encode($return));
			}
		}

	}

	//按天生成中互金上报数据（前一天发布的标的和还款标的）(增量数据)
	public function create_nifa_borrow_new()
	{


		$is_export = 0; //是否导出EXCEL文件

		//已确认存管标提前还款标的
		$repayment_borrowlist = array('1561', '1599', '1636', '1548', '1586', '1639', '1574', '1541', '1593', '1581', '1596', '1590', '1603', '1669', '1699', '1580', '1700', '1585', '1611', '1683', '1595', '1688', '1549', '1533', '1612', '1658', '1635', '1621', '1592', '1577', '1751', '1527', '1557', '1563', '1648', '1691', '1521', '1719', '1597', '1714', '1649', '1587', '1815', '1641', '1749', '1689', '1556', '1571', '1784', '1529', '1539', '1619', '1693', '1729', '1779', '1604', '1633', '1614', '1631', '1674', '1656', '1690', '1665', '1778', '1676', '1835', '1757', '1735', '1695', '1742', '1794', '1576', '1605', '1713', '1613', '1834', '1745', '1843', '1823');

		$row = array();
		$row[0] = array('项目唯一编号', '社会信用代码', '平台序号', '项目编号', '项目类型', '项目名称', '项目成立日期', '借款金额', '借款币种', '借款起息日', '借款到期日期', '借款期限', '出借利率', '项目费率', '项目费用', '其他费用', '还款保证措施', '还款期数', '担保方式', '担保公司名称', '约定还款计划', '实际还款记录', '实际累计本金偿还额', '实际累计利息偿还额', '借款剩余本金余额', '借款剩余应付利息', '是否支持转让', '项目状态', '逾期原因', '逾期次数', '还款方式', '借款用途', '出借人个数');
		$i = 1;


		//新成立和还款中
		$start = date("Y-m-d", (time() - 86400));
		$begin_time = strtotime($start) - 1;//前天23:59：59开始时间
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//上报数据截止时间今天00:00:00;


		$where['second_verify_time'] = array('between', array($begin_time, $end_time));


		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息
		$borrow_ids = array(); //记录已生成的标的


		//项目成立数据
		if (true) {
			$list = M('borrow_info bi')
				->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage')
				->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
				->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
				->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
				->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
				->where($where)
				// ->limit(50)
				->select();
			//echo M()->getLastSql().'<br>';die;
			$detail = M('investor_detail');
			foreach ($list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = $detail->where('borrow_id=' . $value['id'])->select();
				//约定还款计划
				$arr = array();
				$investor_interest = 0; //实际包括加息券、投资红包的
				foreach ($result as $k => $v) {
					$investor_interest += $v['interest'];
					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}
				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}
				//实际还款计划
				$real_repaymentlist = date("Y-m-d", $value['second_verify_time']) . ':0:0:01';

				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				$repayment_status = '01';

				//剩余本金、利息
				$capital_last = $value['borrow_money'];
				$interest_last = $investor_interest; //$value['borrow_interest'];

				//还款方式
				if ($value['repayment_type'] == 4) {
					$repayment_type = '01';
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05';
				}

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['id'])->count();


				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['deadline']) . "|" .//借款到期日期
					ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					"02" . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}
				//借款人信息
				$borrower .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['id'];
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['deadline']);
					$row[$i]['no12'] = ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = getFloatValue($present_capital, 4);
					$row[$i]['no24'] = getFloatValue($present_interest, 4);
					$row[$i]['no25'] = getFloatValue($capital_last, 4);
					$row[$i]['no26'] = getFloatValue($interest_last, 4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}
				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['id'];

			}
		}

		//还款中标的 的还款数据
		$where2['repayment_time'] = array('between', array($begin_time, $end_time));
		// $where2['borrow_id'] = '864';
		$repayment_list = M('investor_detail id')
			->group('borrow_id')
			->field('id.repayment_time, id.borrow_id, id.capital, id.interest, id.receive_interest, id.receive_capital, id.deadline,id.sort_order,id.total, bi.borrow_name,bi.second_verify_time,bi.borrow_money,bi.deadline as borrow_deadline,bi.borrow_interest_rate, bi.borrow_duration,bi.repayment_type,bi.borrow_interest,bi.borrow_uid,bi.borrow_status,bi.borrow_zhaiquan, bi.is_prepayment,bi.is_advanced,lz.zhaiquan_idcard,lz.type as lztype, lz.zhaiquan_bankinfo,lz.mortgage')
			->join('lzh_borrow_info as bi on bi.id = id.borrow_id')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where2)
			// ->limit(50)
			->select();

		if (!empty($repayment_list)) {
			foreach ($repayment_list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = M('investor_detail')->where('borrow_id=' . $value['borrow_id'])->select();
				//约定还款计划
				$arr = array();

				//实际累计本金、利息偿还额
				$total_present_capital = '0'; //累计还款本金
				$total_present_interest = 0; //项目总还款利息

				$repay_present_capital = '0'; //累计还款本金
				$repay_present_interest = 0; //累计还款利息

				$current_present_capital = '0'; //当期本金总和
				$current_present_interest = '0'; //当期利息总和

				$current_repayment_time = '';

				foreach ($result as $k => $v) {
					$total_present_interest += $v['receive_interest'];//累计偿还利息
					//所有小于当期的数据
					if ($v['sort_order'] <= $value['sort_order']) {
						$repay_present_interest += $v['receive_interest'];//累计偿还利息
					}
					if ($v['sort_order'] == $value['sort_order']) {
						$current_present_interest += $v['receive_interest'];//当期偿还利息
						$current_repayment_time = $v['repayment_time'];
					}

					// if($value['sort_order'] < $value['total'] ){
					// 	// $next_sort =
					// }

					// if(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] < $value['total']){
					// 	// $next_repayment_time[] = $result[$k+1]['repayment_time'];
					// 	$next_repayment_time =  $value['repayment_time'] + date('t') * 24 * 3600; //下期还款日期
					// }elseif(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] == $value['total']){
					// 	//最后一期
					// }

					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}

				// echo 'debug<br><pre>'; echo $repay_present_interest; print_r($value); print_r($result); exit;

				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}


				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				//默认还款中
				$repayment_status = '02';

				//实际还款记录  当期实际的还款日期、本金、利息、还款来源
				if ($value['sort_order'] < $value['total']) {
					//不是最后一期
					$current_present_capital = 0;
					$next_sort = $value['sort_order'] + 1;
					$next_repayment_time_real = M('investor_detail')->where(array('borrow_id' => $value['borrow_id'], 'sort_order' => $next_sort))->select();

					if (date('Y-m-d', $next_repayment_time_real[0]['repayment_time']) == date('Y-m-d', $value['repayment_time'])) {
						$repayment_status = '04';
						//当下一期的还款时间等于当期还款时间，则标的为提前还款标的
						// 且还款本金=标的金额
						$current_present_capital = $value['borrow_money'];
					}

				} else {
					//最后一期
					$current_present_capital = $value['borrow_money'];
					// $total_present_capital = $value['borrow_money'];

					if (date('Y-m-d', $value['repayment_time']) < date('Y-m-d', $value['deadline'])) {
						$repayment_status = '04';
					} else {
						$repayment_status = '03';
					}
				}
				$real_repaymentlist = date("Y-m-d", $current_repayment_time) . ':' . getFloatValue($current_present_capital, 4) . ':' . getFloatValue($current_present_interest, 4) . ':01';


				if ($value['repayment_type'] == 4) {
					$repayment_type = '01'; //还款方式
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05'; //还款方式
				}

				//实际累计本金偿还额 ，实际累计利息偿还额 取标的的信息
				if ($repayment_status == 3) {
					//正常还款结束
					$total_present_capital = $value['borrow_money'];
					// $repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} elseif ($repayment_status == 4) {
					//提前还款结束
					$total_present_capital = $value['borrow_money'];
					$repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} else {
					//正常还款
					//剩余本金、利息
					$capital_last = $value['borrow_money'] - $current_present_capital;
					$interest_last = $total_present_interest - $repay_present_interest;
				}


				$capital_last .= ' =' . $value['borrow_money'] . ' - ' . $current_present_capital;
				$interest_last .= ' =' . $total_present_interest . ' - ' . $repay_present_interest;

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['borrow_id'])->count();


				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['borrow_id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['borrow_deadline']) . "|" .//借款到期日期
					// round(($d2-$d1)/3600/24)
					// 1469079439    1500652799
					//timediff($value['borrow_deadline'], $value['second_verify_time'])."|".

					ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					"02" . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($total_present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($repay_present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}


				//借款人信息
				$borrower .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['borrow_id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['borrow_id'] . '-还款标';
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['borrow_deadline']);
					$row[$i]['no12'] = ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = $total_present_capital; //getFloatValue($total_present_capital,4);
					$row[$i]['no24'] = $repay_present_interest; //getFloatValue($repay_present_interest,4);
					$row[$i]['no25'] = $capital_last; //getFloatValue($capital_last,4);
					$row[$i]['no26'] = $interest_last; //getFloatValue($interest_last,4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}

				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['borrow_id'];
			}
		}


		if ($is_export) {
			$filename = 'load_borrow_userlist_' . date('Y-m-d', $begin_time) . '~' . date('Y-m-d', $end_time);
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}


		//出借人记录
		$status2['id.borrow_id'] = array('in', $borrow_ids);
		$investor_arr = M('borrow_investor id')
			->field('borrow_id,investor_uid,investor_capital,idcard')
			->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
			->where($status2)
			//->limit(10)
			->select();

		foreach ($investor_arr as $invest => $inv) {
			//出借人身份证号码
			if (substr($inv['idcard'], 17, 1) == 'x') {
				$idcode_investor = substr($inv['idcard'], 0, 17) . 'X';
			} else {
				$idcode_investor = $inv['idcard'];
			}

			$investor .= "91320200323591589D1" . $inv['borrow_id'] . "|" .//项目唯一编号
				"01" . "|" .//出借人类型
				$inv['investor_uid'] . "|" .//出借人ID
				"01" . "|" .//证件类型
				$idcode_investor . "|" .//证件号码////////////////////////////待添加
				"|" .//职业类型
				"|" .//所属地区
				"|" .//所属行业
				getFloatValue($inv['investor_capital'], 4) . "|" .//出借金额
				"01"//出借状态
				. "\r\n";//\r\n
		}
		// echo $borrow_info."<hr>";
		// echo $borrower."<hr>";
		// echo $investor."<br><br>";

		if (true) {
			//生成txt文件
			$filedir = '91320200323591589D' . date('Ymd', ($begin_time + 1)) . '24001';

			if (empty($borrow_info) || empty($borrower) || empty($investor)) {

				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 2, //2为数据空
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				// $chk = M('nifa_tongji')->where(array('filename' =>$filedir))->find();
				// if(!empty($chk)){
				// 	$adddata['id'] = $chk['id'];
				// 	$adddata['status'] = $return['success'] == 'true' ? 1 : 0;
				// 	$adddata['update_time'] = NOW_TIME;
				// }
				M('nifa_tongji')->add($adddata);


				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = '当天数据为空';
				exit(json_encode($return));

			}

			$dataTxt = array(
				'borrow_info' => $borrow_info,
				'borrower' => $borrower,
				'investor' => $investor,
			);

			$create_res = $this->createTxt($dataTxt, $filedir);
			// $this->createTxt($this->fileName['binfobor'], $borrower, $filedir);
			// $this->createTxt($this->fileName['binfoinr'], $investor, $filedir);

			if ($create_res) {
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 1, //
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				//上报数据
				$sourcePath = 'D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/' . $filedir . '.zip';

				$nifa_url = 'http://localhost:8888/nifa/sftp/upload?systemid=1&stype=24&sourcePath=' . urlencode($sourcePath);

				//D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/91320200323591589D2017052524001.zip
				// header("Location:".$nifa_url);

				$result = $this->getUrl($nifa_url);
				$return = json_decode($result, true);
				if ($return['success'] == 'true') {
					$adddata['post_status'] = 1;
					$msg = 'zip文件生成成功，上报成功';
				} else {
					$adddata['post_status'] = 0;
					$msg = 'zip文件生成成功，上报失败';
				}

				M('nifa_tongji')->add($adddata);

				$return['status'] = 1;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . $msg;
				exit(json_encode($return));
			} else {
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . 'zip文件生成失败';
				exit(json_encode($return));
			}
		}

	}

	//手动修改上报当天的时间进行覆盖上报
	public function create_nifa_borrow_only()
	{


		$is_export = 0; //是否导出EXCEL文件

		//已确认存管标提前还款标的
		$repayment_borrowlist = array('1561', '1599', '1636', '1548', '1586', '1639', '1574', '1541', '1593', '1581', '1596', '1590', '1603', '1669', '1699', '1580', '1700', '1585', '1611', '1683', '1595', '1688', '1549', '1533', '1612', '1658', '1635', '1621', '1592', '1577', '1751', '1527', '1557', '1563', '1648', '1691', '1521', '1719', '1597', '1714', '1649', '1587', '1815', '1641', '1749', '1689', '1556', '1571', '1784', '1529', '1539', '1619', '1693', '1729', '1779', '1604', '1633', '1614', '1631', '1674', '1656', '1690', '1665', '1778', '1676', '1835', '1757', '1735', '1695', '1742', '1794', '1576', '1605', '1713', '1613', '1834', '1745', '1843', '1823');

		$row = array();
		$row[0] = array('项目唯一编号', '社会信用代码', '平台序号', '项目编号', '项目类型', '项目名称', '项目成立日期', '借款金额', '借款币种', '借款起息日', '借款到期日期', '借款期限', '出借利率', '项目费率', '项目费用', '其他费用', '还款保证措施', '还款期数', '担保方式', '担保公司名称', '约定还款计划', '实际还款记录', '实际累计本金偿还额', '实际累计利息偿还额', '借款剩余本金余额', '借款剩余应付利息', '是否支持转让', '项目状态', '逾期原因', '逾期次数', '还款方式', '借款用途', '出借人个数');
		$i = 1;


		//新成立和还款中
		$begin_time = strtotime('2016-12-29 23:59:59'); //3.26 1857  4.4 1858  4.8 1859 担保公司暂定无锡合众
		$end_time = strtotime('2016-12-31 00:00:00');

		$where['second_verify_time'] = array('between', array($begin_time, $end_time));


		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息
		$borrow_ids = array(); //记录已生成的标的


		//项目成立数据
		if (true) {
			$list = M('borrow_info bi')
				->field('bi.id,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
				->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
				->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
				->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
				->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
				->where($where)
				// ->limit(50)
				->select();
			//echo M()->getLastSql().'<br>';die;
			$detail = M('investor_detail');
			foreach ($list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = $detail->where('borrow_id=' . $value['id'])->select();
				//约定还款计划
				$arr = array();
				$investor_interest = 0; //实际包括加息券、投资红包的
				foreach ($result as $k => $v) {
					$investor_interest += $v['interest'];
					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}
				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}
				//实际还款计划
				$real_repaymentlist = date("Y-m-d", $value['second_verify_time']) . ':0:0:01';

				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				$repayment_status = '01';

				//剩余本金、利息
				$capital_last = $value['borrow_money'];
				$interest_last = $investor_interest; //$value['borrow_interest'];

				//还款方式
				if ($value['repayment_type'] == 4) {
					$repayment_type = '01';
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05';
				}

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['id'])->count();

				//担保方式
				if ($value['borrow_type'] == 1) {
					$guarantee_way = '02';
				} elseif ($value['borrow_type'] == 2) {
					$guarantee_way = '03';
				}


				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['deadline']) . "|" .//借款到期日期
					ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					$guarantee_way . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}
				//借款人信息
				$borrower .= "91320200323591589D1" . $value['id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['id'];
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['deadline']);
					$row[$i]['no12'] = ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = getFloatValue($present_capital, 4);
					$row[$i]['no24'] = getFloatValue($present_interest, 4);
					$row[$i]['no25'] = getFloatValue($capital_last, 4);
					$row[$i]['no26'] = getFloatValue($interest_last, 4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}
				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['id'];

			}
		}

		//还款中标的 的还款数据
		$where2['repayment_time'] = array('between', array($begin_time, $end_time));
		// $where2['borrow_id'] = '864';
		$repayment_list = M('investor_detail id')
			->group('borrow_id')
			->field('id.repayment_time, id.borrow_id, id.capital, id.interest, id.receive_interest, id.receive_capital, id.deadline,id.sort_order,id.total, bi.borrow_name,bi.second_verify_time,bi.borrow_money,bi.deadline as borrow_deadline,bi.borrow_interest_rate, bi.borrow_duration,bi.repayment_type,bi.borrow_interest,bi.borrow_uid,bi.borrow_status,bi.borrow_zhaiquan, bi.is_prepayment,bi.is_advanced,lz.zhaiquan_idcard,lz.type as lztype, lz.zhaiquan_bankinfo,lz.mortgage,lz.borrow_type')
			->join('lzh_borrow_info as bi on bi.id = id.borrow_id')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where2)
			// ->limit(50)
			->select();

		if (!empty($repayment_list)) {
			foreach ($repayment_list as $key => $value) {
				switch ($value['borrow_duration']) {
					case '1':
						$borrow_fee_rate = (11.76 + 1) / 100;
						break;
					case '3':
						$borrow_fee_rate = (10.56 + 1.6) / 100;
						break;
					case '6':
						$borrow_fee_rate = (9.96 + 2.2) / 100;
						break;
					case '12':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
						break;
					case '18':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
						break;
					case '24':
						$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
						break;
				}
				$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
				$motrgage = $value['mortgage'];

				//计算
				$result = M('investor_detail')->where('borrow_id=' . $value['borrow_id'])->select();
				//约定还款计划
				$arr = array();

				//实际累计本金、利息偿还额
				$total_present_capital = '0'; //累计还款本金
				$total_present_interest = 0; //项目总还款利息

				$repay_present_capital = '0'; //累计还款本金
				$repay_present_interest = 0; //累计还款利息

				$current_present_capital = '0'; //当期本金总和
				$current_present_interest = '0'; //当期利息总和

				$current_repayment_time = '';

				foreach ($result as $k => $v) {
					$total_present_interest += $v['receive_interest'];//累计偿还利息
					//所有小于当期的数据
					if ($v['sort_order'] <= $value['sort_order']) {
						$repay_present_interest += $v['receive_interest'];//累计偿还利息
					}
					if ($v['sort_order'] == $value['sort_order']) {
						$current_present_interest += $v['receive_interest'];//当期偿还利息
						$current_repayment_time = $v['repayment_time'];
					}

					// if($value['sort_order'] < $value['total'] ){
					// 	// $next_sort =
					// }

					// if(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] < $value['total']){
					// 	// $next_repayment_time[] = $result[$k+1]['repayment_time'];
					// 	$next_repayment_time =  $value['repayment_time'] + date('t') * 24 * 3600; //下期还款日期
					// }elseif(($v['sort_order'] == $value['sort_order']) && $value['sort_order'] == $value['total']){
					// 	//最后一期
					// }

					$arr[$v['deadline']]['capital'] += $v['capital'];
					$arr[$v['deadline']]['interest'] += $v['interest'];
					$arr[$v['deadline']]['deadline'] = $v['deadline'];
				}

				// echo 'debug<br><pre>'; echo $repay_present_interest; print_r($value); print_r($result); exit;

				foreach ($arr as $kk => $vv) {
					$data[] = date("Y-m-d", $vv['deadline']) . ":" . getFloatValue($vv['capital'], 4) . ":" . getFloatValue($vv['interest'], 4);
				}


				//项目状态 01项目新成立、02还款中、03正常还款已结清、04提前还款已结清
				//默认还款中
				$repayment_status = '02';

				//实际还款记录  当期实际的还款日期、本金、利息、还款来源
				if ($value['sort_order'] < $value['total']) {
					//不是最后一期
					$current_present_capital = 0;
					$next_sort = $value['sort_order'] + 1;
					$next_repayment_time_real = M('investor_detail')->where(array('borrow_id' => $value['borrow_id'], 'sort_order' => $next_sort))->select();

					if (date('Y-m-d', $next_repayment_time_real[0]['repayment_time']) == date('Y-m-d', $value['repayment_time'])) {
						$repayment_status = '04';
						//当下一期的还款时间等于当期还款时间，则标的为提前还款标的
						// 且还款本金=标的金额
						$current_present_capital = $value['borrow_money'];
					}

				} else {
					//最后一期
					$current_present_capital = $value['borrow_money'];
					// $total_present_capital = $value['borrow_money'];

					if (date('Y-m-d', $value['repayment_time']) < date('Y-m-d', $value['deadline'])) {
						$repayment_status = '04';
					} else {
						$repayment_status = '03';
					}
				}
				$real_repaymentlist = date("Y-m-d", $current_repayment_time) . ':' . getFloatValue($current_present_capital, 4) . ':' . getFloatValue($current_present_interest, 4) . ':01';


				if ($value['repayment_type'] == 4) {
					$repayment_type = '01'; //还款方式
				} elseif ($value['repayment_type'] == 5) {
					$repayment_type = '05'; //还款方式
				}

				//实际累计本金偿还额 ，实际累计利息偿还额 取标的的信息
				if ($repayment_status == 3) {
					//正常还款结束
					$total_present_capital = $value['borrow_money'];
					// $repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} elseif ($repayment_status == 4) {
					//提前还款结束
					$total_present_capital = $value['borrow_money'];
					$repay_present_interest = $total_present_interest;

					$capital_last = 0;
					$interest_last = 0;
				} else {
					//正常还款
					//剩余本金、利息
					$capital_last = $value['borrow_money'] - $current_present_capital;
					$interest_last = $total_present_interest - $repay_present_interest;
				}


				$capital_last .= ' =' . $value['borrow_money'] . ' - ' . $current_present_capital;
				$interest_last .= ' =' . $total_present_interest . ' - ' . $repay_present_interest;

				//出借人个数
				$investor_num = M('borrow_investor')->where('borrow_id=' . $value['borrow_id'])->count();

				//担保方式
				if ($value['borrow_type'] == 1) {
					$guarantee_way = '02';
				} elseif ($value['borrow_type'] == 2) {
					$guarantee_way = '03';
				}


				//项目信息
				$borrow_info .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					"91320200323591589D" . "|" .//社会信用代码
					"1" . "|" .//平台序号".
					$value['borrow_id'] . "|" .//项目编号
					"01" . "|" .//项目类型 //个体直接借贷
					$value['borrow_name'] . "|" .//项目名称
					date("Ymd", $value['second_verify_time']) . "|" .//项目成立日期
					getFloatValue($value['borrow_money'], 4) .//借款金额
					"|CNY|" .//借款币种
					date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
					date("Ymd", $value['borrow_deadline']) . "|" .//借款到期日期
					// round(($d2-$d1)/3600/24)
					// 1469079439    1500652799
					//timediff($value['borrow_deadline'], $value['second_verify_time'])."|".

					ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24)) . "|" .//借款期限  ？？
					getFloatValue(($value['borrow_interest_rate'] / 100), 8) . "|" .//出借利率
					getFloatValue($borrow_fee_rate, 8) . "|" .//项目费率 //待处理？
					getFloatValue($borrow_fee, 4) . "|" .//项目费用 //待处理？
					getFloatValue(0, 4) . "|" .//其他费用
					"02" . "|" .//还款保证措施
					$value['borrow_duration'] . "|" .//还款期数
					$guarantee_way . "|" .//担保方式
					$motrgage . "|" .//担保公司名称
					implode(";", $data) . "|" .//约定还款计划
					$real_repaymentlist . "|" .//实际还款记录
					getFloatValue($total_present_capital, 4) . "|" .//实际累计本金偿还额
					getFloatValue($repay_present_interest, 4) . "|" .//实际累计利息偿还额
					getFloatValue($capital_last, 4) . "|" .//借款剩余本金余额
					getFloatValue($interest_last, 4) . "|" .//借款剩余应付利息
					"1" . "|" .//是否支持转让
					$repayment_status . "|" .//项目状态
					"|" .//逾期原因
					"|" .//逾期次数
					$repayment_type . "|" .//还款方式
					"03" . "|" .//借款用途
					$investor_num//出借人个数
					. "\r\n";


				//====================借款人记录====================

				$count_borrow['borrow_uid'] = $value['borrow_uid'];
				$count_borrow['borrow_status'] = array('in', array('2', '4', '6', '7', '9'));
				$num = M('borrow_info')->where($count_borrow)->count();

				//借款角色
				$borrower_type = '01';//借款人类型 01:自然人 02:法人
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);

				if ($sexint % 2 == 0) {
					$sex = '2';
				} elseif ($sexint % 2 != 0) {
					$sex = '1';
				} else {
					$sex = '0';
				}
				//职业种类
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
				//所属地区
				$area = substr($idcode, 0, 6);
				//开户银行名称
				$bankname = $value['zhaiquan_bankinfo'];

				//企业借款人
				if ($value['lztype'] == 2) {
					$borrower_type = '02';
					$sex = '';
					$career = '';
					$area = substr($idcode, 1, 6);
				}


				//借款人信息
				$borrower .= "91320200323591589D1" . $value['borrow_id'] . "|" .//项目唯一编号
					$borrower_type . "|" .//借款人类型
					$value['borrow_uid'] . "|" .//借款人ID
					"01" . "|" .//证件类型
					$idcode . "|" .//证件号码////////////////////////////待添加
					$sex . "|" .//性别
					"|" .//借款人年平均收入
					"|" .//借款人主要收入来源
					$career . "|" .//职业类型80000不便分类的其他从业人员
					$area . "|" .//所属地区////////////////////////////待添加
					"|" .//实缴资本
					"|" .//注册资本
					"|" .//所属行业
					"|" .//机构成立时间
					$bankname . "|" .//开户银行名称////////////////////////////待添加
					"|" .//收款账户开户行所在地区
					"|" .//借款人信用评级
					$num .//借款人累计借款次数
					"\r\n";//\r\n


				if ($is_export) {
					//导出EXCEL
					$row[$i]['id'] = "91320200323591589D1" . $value['borrow_id'];
					$row[$i]['no2'] = '91320200323591589D';
					$row[$i]['no3'] = '1';
					$row[$i]['no4'] = $value['borrow_id'] . '-还款标';
					$row[$i]['no5'] = '01';
					$row[$i]['no6'] = $value['borrow_name'];
					$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
					$row[$i]['no9'] = 'CNY';
					$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
					$row[$i]['no11'] = date("Ymd", $value['borrow_deadline']);
					$row[$i]['no12'] = ceil(($value['borrow_deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
					$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
					$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
					$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
					$row[$i]['no16'] = getFloatValue(0, 4);
					$row[$i]['no17'] = '02';
					$row[$i]['no18'] = $value['borrow_duration'];
					$row[$i]['no19'] = '02';
					$row[$i]['no20'] = $motrgage;
					$row[$i]['no21'] = implode(";", $data);
					$row[$i]['no22'] = $real_repaymentlist;
					$row[$i]['no23'] = $total_present_capital; //getFloatValue($total_present_capital,4);
					$row[$i]['no24'] = $repay_present_interest; //getFloatValue($repay_present_interest,4);
					$row[$i]['no25'] = $capital_last; //getFloatValue($capital_last,4);
					$row[$i]['no26'] = $interest_last; //getFloatValue($interest_last,4);
					$row[$i]['no27'] = '1';
					$row[$i]['no28'] = $repayment_status;
					$row[$i]['no29'] = 'is_advanced=' . $value['is_advanced'];
					$row[$i]['no30'] = '';
					$row[$i]['no31'] = $repayment_type;
					$row[$i]['no32'] = '03';
					$row[$i]['no33'] = $investor_num;

					$i++;
				}

				unset($data);
				unset($real_repaymentlist);


				//存储所有标的ID
				$borrow_ids[] = $value['borrow_id'];
			}
		}


		if ($is_export) {
			$filename = 'load_borrow_userlist_' . date('Y-m-d', $begin_time) . '~' . date('Y-m-d', $end_time);
			import("ORG.Io.Excel");
			$xls = new Excel_XML('UTF-8', false, $filename);
			$xls->addArray($row);
			$xls->generateXML($filename);
			echo 'OK';
			exit;
		}


		//出借人记录
		$status2['id.borrow_id'] = array('in', $borrow_ids);
		$investor_arr = M('borrow_investor id')
			->field('borrow_id,investor_uid,investor_capital,idcard')
			->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
			->where($status2)
			//->limit(10)
			->select();

		foreach ($investor_arr as $invest => $inv) {
			//出借人身份证号码
			if (substr($inv['idcard'], 17, 1) == 'x') {
				$idcode_investor = substr($inv['idcard'], 0, 17) . 'X';
			} else {
				$idcode_investor = $inv['idcard'];
			}

			$investor .= "91320200323591589D1" . $inv['borrow_id'] . "|" .//项目唯一编号
				"01" . "|" .//出借人类型
				$inv['investor_uid'] . "|" .//出借人ID
				"01" . "|" .//证件类型
				$idcode_investor . "|" .//证件号码////////////////////////////待添加
				"|" .//职业类型
				"|" .//所属地区
				"|" .//所属行业
				getFloatValue($inv['investor_capital'], 4) . "|" .//出借金额
				"01"//出借状态
				. "\r\n";//\r\n
		}
		// echo $borrow_info."<hr>";
		// echo $borrower."<hr>";
		// echo $investor."<br><br>";

		if (true) {
			//生成txt文件
			$filedir = '91320200323591589D' . date('Ymd', ($begin_time + 1)) . '24001';

			if (empty($borrow_info) || empty($borrower) || empty($investor)) {

				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 2, //2为数据空
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				// $chk = M('nifa_tongji')->where(array('filename' =>$filedir))->find();
				// if(!empty($chk)){
				// 	$adddata['id'] = $chk['id'];
				// 	$adddata['status'] = $return['success'] == 'true' ? 1 : 0;
				// 	$adddata['update_time'] = NOW_TIME;
				// }
				M('nifa_tongji')->add($adddata);


				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = '当天数据为空';
				exit(json_encode($return));

			}

			$dataTxt = array(
				'borrow_info' => $borrow_info,
				'borrower' => $borrower,
				'investor' => $investor,
			);

			$create_res = $this->createTxt($dataTxt, $filedir);
			// $this->createTxt($this->fileName['binfobor'], $borrower, $filedir);
			// $this->createTxt($this->fileName['binfoinr'], $investor, $filedir);

			if ($create_res) {
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 1, //
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				//上报数据
				$sourcePath = 'D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/' . $filedir . '.zip';

				$nifa_url = 'http://localhost:8888/nifa/sftp/upload?systemid=1&stype=24&sourcePath=' . urlencode($sourcePath);

				//D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/91320200323591589D2017052524001.zip
				// header("Location:".$nifa_url);

				$result = $this->getUrl($nifa_url);
				$return = json_decode($result, true);
				if ($return['success'] == 'true') {
					$adddata['post_status'] = 1;
					$msg = 'zip文件生成成功，上报成功';
				} else {
					$adddata['post_status'] = 0;
					$msg = 'zip文件生成成功，上报失败';
				}

				M('nifa_tongji')->add($adddata);

				$return['status'] = 1;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . $msg;
				exit(json_encode($return));
			} else {
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . 'zip文件生成失败';
				exit(json_encode($return));
			}
		}

	}

	private function get_borrow_fee_by_borrow_duration($borrow_money, $borrow_duration)
	{


		// if(!empt($value['fee_option']) && false){
		// 	//不用此数据
		// 	$fee_option = implode('|', $value['fee_option']);
		// 	$borrow_fee = 0;
		// 	foreach ($fee_option as $key => $value) {
		// 		$borrow_fee += $value;
		// 	}
		// }else{
		switch ($borrow_duration) {
			case '1':
				$borrow_fee_rate = (11.76 + 1) / 100;
				break;
			case '3':
				$borrow_fee_rate = (10.56 + 1.6) / 100;
				break;
			case '6':
				$borrow_fee_rate = (9.96 + 2.2) / 100;
				break;
			case '12':
				$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
				break;
			case '18':
				$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
				break;
			case '24':
				$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
				break;
		}
		$borrow_fee = $borrow_money * $borrow_fee_rate;
		// }

		return $borrow_fee;


		if (false) {
			//导出EXCEL
			$row[$i]['id'] = "91320200323591589D1" . $value['id'];
			$row[$i]['no2'] = '91320200323591589D';
			$row[$i]['no3'] = '1';
			$row[$i]['no4'] = $value['id'];
			$row[$i]['no5'] = '01';
			$row[$i]['no6'] = $value['borrow_name'];
			$row[$i]['no7'] = date("Ymd", $value['second_verify_time']);
			$row[$i]['no8'] = getFloatValue($value['borrow_money'], 4);
			$row[$i]['no9'] = 'CNY';
			$row[$i]['no10'] = date("Ymd", $value['second_verify_time']);
			$row[$i]['no11'] = date("Ymd", $value['deadline']);
			$row[$i]['no12'] = ceil(($value['deadline'] - $value['second_verify_time']) / (60 * 60 * 24));
			$row[$i]['no13'] = getFloatValue(($value['borrow_interest_rate'] / 100), 8);
			$row[$i]['no14'] = getFloatValue($borrow_fee_rate, 8);
			$row[$i]['no15'] = getFloatValue($borrow_fee, 4);
			$row[$i]['no16'] = getFloatValue(0, 4);
			$row[$i]['no17'] = '02';
			$row[$i]['no18'] = $value['borrow_duration'];
			$row[$i]['no19'] = '02';
			$row[$i]['no20'] = $motrgage;
			$row[$i]['no21'] = implode(";", $data);
			$row[$i]['no22'] = implode(";", $data1);
			$row[$i]['no23'] = getFloatValue($present_capital, 4);
			$row[$i]['no24'] = getFloatValue($present_interest, 4);
			$row[$i]['no25'] = getFloatValue($capital_last, 4);
			$row[$i]['no26'] = getFloatValue($interest_last, 4);
			$row[$i]['no27'] = '1';
			$row[$i]['no28'] = $repayment_status;
			$row[$i]['no29'] = '';
			$row[$i]['no30'] = '';
			$row[$i]['no31'] = $repayment_type;
			$row[$i]['no32'] = '03';
			$row[$i]['no33'] = $investor_num;

			$i++;
		}
		unset($data);
		unset($data1);
	}
	
	
	/**
	 * 中互金数据上报接口调整   互联网金融统计系统数据报送（2019年10月8日）
	 * 批量上传
	 */
	public function zhj_create_new(){
		 $borrow_create_arr = FS("Webconfig/borrow_create_arr");
		 // $borrow_create_arr = array();
		 // FS("borrow_create_arr",$borrow_create_arr, "Webconfig/");
		 echo 'debug1<br><pre>'; print_r($borrow_create_arr);
		 echo count($borrow_create_arr);
		 exit;
		
		
		if (!IS_POST) {
			// $postdays = 10; //定义跑多少天
			// $this->assign('postdays', $postdays);
			$this->display();
			
			return;
		}
		
		
		$days = intval($_POST['day']);
		//结束时间
		//$stop_time = strtotime('2016-08-24 23:59:59');
		$stop_time = strtotime('2017-10-01 23:59:59');//截止到2017年10月1日的数据
		
		//已结清
		//$borrow_verify_time = strtotime('2016-08-24 23:59:59');
		$begin_time = strtotime('2016-09-04 23:59:59') + $days * 24 * 3600;  //9月5号开始
		$end_time = strtotime('2016-09-06 00:00:00') + $days * 24 * 3600;
		
		
		if ($begin_time >= $stop_time) {
			
			$return['status'] = 2;
			$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
			$return['info'] = '上报结束';
			exit(json_encode($return));
		}
		
		// $borrow_ids_arr = ''; //引入已上报标的ID
		$borrow_create_arr = FS("Webconfig/borrow_create_arr");
		//echo 'debug1<br><pre>'; print_r($borrow_create_arr); exit;
		
		
					// $start = date("Y-m-d", (time() - 86400));
					// $begin_time = strtotime($start) - 1;//前天23:59：59开始时间
					// $begin_time = strtotime('2018-10-09 23:59:59');
					// $end = date("Y-m-d", time());
					// $end_time = strtotime($end);//上报数据截止时间今天00:00:00;
					// $end_time = strtotime('2018-10-11 00:00:00');
		
		$where['second_verify_time'] = array('between', array($begin_time, $end_time));
		$where['bi.id'] = array('lt', 515);
		
		
		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息
		$appoint_repay_detail = '';//约定还款计划数据项
		$real_repay_detail = '';//项目变更信息(实际还款信息)
		$borrow_ids = array(); //记录已生成的标的
		
		$list = M('borrow_info bi')
			->field('bi.id,bi.borrow_uid,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,
			has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,
			zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where)
			// ->limit(50)
			->select();
		
		
		foreach($list as $key=>$value){
			
			//还款方式
			if($value['repayment_type'] == 4){
				$repayment_type = '04';//先息后本
			}elseif ($value['repayment_type'] == 5){
				$repayment_type = '05';//末期本息
			}
			
			//借款用途
			if($value['borrow_type'] == 1){//抵押
				$borrow_use = '01';//个人消费
			}elseif ($value['borrow_type'] == 2){//质押
				$borrow_use = '01';
			}elseif ($value['borrow_type'] == 3){//企业贷
				$borrow_use = '03';//企业经营周转
			}
			
			//借款项目平台服务费
			switch ($value['borrow_duration']) {
				case '1':
					$borrow_fee_rate = (11.76 + 1) / 100;
					break;
				case '3':
					$borrow_fee_rate = (10.56 + 1.6) / 100;
					break;
				case '6':
					$borrow_fee_rate = (9.96 + 2.2) / 100;
					break;
				case '12':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
					break;
				case '18':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
					break;
				case '24':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
					break;
			}
			$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
			
			//出借人个数
			$investor_num = M('borrow_investor')->distinct(true)->field('investor_uid')->where('borrow_id=' . $value['id'])->select();
			$investor_num = count($investor_num);
			
			
			
			//还款期数
			if($value['repayment_type'] == 4){
				$borrow_duration = $value['borrow_duration'];
			}elseif ($value['repayment_type'] == 5){
				$borrow_duration = 1;
			}
			
			
			// 1.项目信息数据项
			$borrow_info .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
				date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
				"91320200323591589D" . "|" .//社会信用代码
				$value['id'] . "|" .//项目编号
				getFloatValue($value['borrow_money'], 4) ."|" .//借款金额
				date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
				date("Ymd", $value['deadline']) . "|" .//借款到期日期
				getFloatValue(($value['borrow_interest_rate'] / 100), 4) . "|" .//出借利率
				getFloatValue($borrow_fee, 4) . "|" .//借款项目平台服务费
				getFloatValue(0, 4) . "|" .//第三方代偿保障费用
				$repayment_type . "|" .//还款方式
				$borrow_use . "|" .//借款用途
				$investor_num . "|" .//出借人个数
				$borrow_duration //约定还款总期数
				. "\r\n";
			
			
			//2.约定还款计划
			$appoint = M('investor_detail')
				->field("FROM_UNIXTIME(deadline, '%Y%m%d') as deadline,sum(interest) as interest,sum(capital) as capital,sort_order,total")
				->group('sort_order')
				->where('borrow_id='.$value['id'])
				->select();
			//echo 'debug<br><pre>'; print_r($appoint); exit;
			
			foreach($appoint as $k=>$v){
				$appoint_repay_detail .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
					date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
					"91320200323591589D" . "|" .//社会信用代码
					$value['id'] . "|" .//项目编号
					$borrow_duration . "|" .//约定还款总期数
					$v['sort_order'] . "|" .//约定还款期次
					$v['deadline'] . "|" .//约定还款日期
					getFloatValue($v['capital'],4) . "|" .//约定还款本金
					getFloatValue($v['interest'],4) //约定还款利息
					."\r\n";
				
			}
			
			
			//借款人类型
			if($value['lztype'] == 2){
				$borrower_type = '02';//法人
				$card_type = 'aa';//aa 社会信用代码、bb 组织机构代码、cc 营业执照编号
				$idcode = $value['zhaiquan_idcard'];
				$sex = '';
				$income = '02';//协会已将法人的年收入字段改为“01.一百万以内 02.一百万至五百万 03.五百万至一千万 04.一千万至五千万 05五千万以上（区间范围为左闭右开）”
				$career = '';
				
			}else{
				$borrower_type = '01';//自然人
				$card_type = '01'; //身份证
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);
				
				if ($sexint % 2 == 0) {
					$sex = '02';
				} elseif ($sexint % 2 != 0) {
					$sex = '01';
				}
				$income = '03';
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
			}
			
			
			
			
			//3.借款人信息
			$borrower .="91320200323591589D" . $value['id'] . "|" .//项目唯一编号
				date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
				"91320200323591589D" . "|" .//社会信用代码
				$value['id'] . "|" .//项目编号
				$borrower_type. "|" .//借款人类型
				$value['borrow_uid']. "|" .//借款人id
				$card_type. "|" .//证件类型
				$idcode. "|" .//证件号码
				$sex. "|" .//借款人性别
				$career. "|" .//职业类型
				$income//借款人年平均收入
				."\r\n";
			
			
			//4.出借人信息
			$borrow_investor = M('borrow_investor id')
				->field('investor_uid,sum(investor_capital) as capital,idcard')
				->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
				->where('borrow_id='.$value['id'])
				->group('investor_uid')
				->select();
			foreach ($borrow_investor as $kk=>$vv){
				if (substr($vv['idcard'], 17, 1) == 'x') {
					$idnum = substr($vv['idcard'], 0, 17) . 'X';
				} else {
					$idnum = $vv['idcard'];
				}
				
				$investor .="91320200323591589D" . $value['id'] . "|" .//项目唯一编号
					date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
					"91320200323591589D" . "|" .//社会信用代码
					$value['id'] . "|" .//项目编号
					'01'. "|" .//出借人类型 //01 自然人，02 法人
					$vv['investor_uid']. "|" .//出借人id
					'01'. "|" .//出借人证件类型
					$idnum. "|" .//证件号码
					'02'. "|" .//出借人年收入
					getFloatValue($vv['capital'],4)//出借金额
					."\r\n";
			}
			
			//存储所有标的ID
			if(!empty($borrow_create_arr)){
				if(!empty($value['id'])){
					if(!in_array($value['id'],$borrow_create_arr)){
						$borrow_create_arr[] = $value['id'];
					}
				}
			}else{
				if(!empty($value['id'])){
					$borrow_create_arr[] = $value['id'];
				}
			}
			
			
		}
		
		
		//写入配置文件中
		FS("borrow_create_arr",$borrow_create_arr, "Webconfig/");
		//echo 'debug<br><pre>'; print_r($borrow_create_arr); exit;
		
		
		//die;
		
		
		// echo '项目信息<br><pre>'; print_r($borrow_info); //exit;
		// echo '约定还款计划信息<br><pre>'; print_r($appoint_repay_detail); //exit;
		// echo '借款人信息<br><pre>'; print_r($borrower); //exit;
		// echo '出借人信息<br><pre>'; print_r($investor); exit;
		
		
		//5.项目变更信息(实际还款信息)，随借款项目状态变化报送
		
		//昨日还款标的
		$status['repayment_time'] = array('between',array($begin_time,$end_time));
		$status['borrow_id'] = array('in',$borrow_create_arr);
		$repay_id = M('investor_detail id')
			->distinct(true)
			->field('borrow_id')
			->where($status)
			->select();
		//echo $repay_id->getLastSql();die;
		foreach ($repay_id as $m=>$n){
			$repayId[] = $n['borrow_id'];
		}
		$repay_status['bi.id'] = array('in',$repayId);
		$repay_list = M('borrow_info bi')
			->field('bi.id,bi.borrow_uid,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,
			has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,
			zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($repay_status)
			// ->limit(50)
			->select();
		
		
		
		foreach ($repay_list as $key=>$value){
			//实际还款期次
			$detail = M('investor_detail');
			$result = $detail->where('borrow_id=' . $value['id'])->select();
			//以还款时间为下标将还款数据重组成新的二维数组
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
					$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
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
					$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
				}
			}
			unset($arr2['1970-01-01']);
			
			
			
			
			//拼接数据
			foreach ($arr2 as $m=>$n){
				
				
				
				
				if (($n['repayment_time'] > $begin_time) && ($n['repayment_time'] < $end_time)) {
					
					//借贷余额
					if (($value['repayment_type'] == 4 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 4 && $value['is_prepayment'] != 0)) {
						if($n['sort_order'] == $real_total){
							$residue_money = 0;//剩余本金
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '05';//当前期次状态
						}else{
							$residue_money = $value['borrow_money'];//剩余本金
							$residue_interest = $n['receive_interest']*$n['total']-$n['receive_interest']*$n['sort_order'];//剩余利息
							$borrow_status = '01';//借款项目状态
							$current_status = '01';//当前期次状态
						}
					}else{
						if($n['sort_order'] == $n['total']){
							$residue_money = 0;
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '01';//当前期次状态
						}else{
							$residue_money = $value['borrow_money'];
							$residue_interest = $n['receive_interest']*$n['total']-$n['receive_interest']*$n['sort_order'];//剩余利息
							$borrow_status = '01';//借款项目状态
							$current_status = '01';//当前期次状态
						}
						if(($value['repayment_type'] == 5 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 5 && $value['is_prepayment'] != 0)){
							$current_status = '05';//当前期次状态
						}
					}
					
					$real_repay_detail .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
						date('Ymd',$n['repayment_time']) . "|" .//业务日期(当前期次还款日期)
						"91320200323591589D" . "|" .//社会信用代码
						$value['id'] . "|" .//项目编号
						$n['sort_order'] . "|" .//实际还款期次
						date('Ymd',$n['repayment_time']) . "|" .//实际还款日期
						getFloatValue($n['receive_capital'],4) . "|" .//实际还款本金
						getFloatValue($n['receive_interest'],4) . "|" .//实际还款利息
						getFloatValue($residue_money,4) . "|" .//借贷余额（仅本金）
						getFloatValue($residue_interest,4) . "|" .//借款剩余利息
						$borrow_status . "|" .//借款项目状态
						$current_status . "|" .//当前期次状态
						getFloatValue(0, 4)//其他费用
						. "\r\n";
				}
			}
			
		}
		
		
		//echo 'debug<br><pre>'; print_r($repayId); exit;
		
		
		//echo 'debug<br><pre>'; print_r($real_repay_detail); exit;
		
		
		if (true) {
			//生成csv文件
			$filedir = '91320200323591589D' . date('Ymd', ($begin_time + 1)) . '24';
			
			if (empty($borrow_info) && empty($borrower) && empty($investor) && empty($appoint_repay_detail) && empty($real_repay_detail)) {
				
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 2, //2为数据空
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				// $chk = M('nifa_tongji')->where(array('filename' =>$filedir))->find();
				// if(!empty($chk)){
				// 	$adddata['id'] = $chk['id'];
				// 	$adddata['status'] = $return['success'] == 'true' ? 1 : 0;
				// 	$adddata['update_time'] = NOW_TIME;
				// }
				
				M('nifa_tongji')->add($adddata);
				
				
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = '当天数据为空';
				exit(json_encode($return));
				
			}
			if(!empty($borrow_info) && !empty($real_repay_detail)){
				$dataTxt = array(
					'borrow_info' => $borrow_info,
					'borrower' => $borrower,
					'investor' => $investor,
					'repay' => $appoint_repay_detail,
					'change' => $real_repay_detail,
				);
				$status = 1;
			}elseif (!empty($borrow_info) && empty($real_repay_detail)){
				$dataTxt = array(
					'borrow_info' => $borrow_info,
					'borrower' => $borrower,
					'investor' => $investor,
					'repay' => $appoint_repay_detail,
				);
				$status = 2;
			}elseif (empty($borrow_info) && !empty($real_repay_detail)){
				$dataTxt = array(
					'change' => $real_repay_detail,
				);
				$status = 3;
			}
			
			
			$create_res = $this->createTxt($dataTxt, $filedir,$status);
			// $this->createTxt($this->fileName['binfobor'], $borrower, $filedir);
			// $this->createTxt($this->fileName['binfoinr'], $investor, $filedir);
			
			
					// $return['info'] = '文件生成成功';
					// exit(json_encode($return));
					// die;
			
			// $return['status'] = 1;
			// $return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
			// $return['info'] = $filedir . '文件生成成功';
			// exit(json_encode($return));
			
			
			
			if ($create_res) {
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 1, //
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				//上报数据
				$sourcePath = 'D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/' . $filedir . '.zip';
				
				$nifa_url = 'http://localhost:8888/nifa/data/upload?id=1&sourcePath=' . urlencode($sourcePath);
				
				//D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/91320200323591589D2017052524001.zip
				// header("Location:".$nifa_url);
				
				$result = $this->getUrl($nifa_url);
				$return = json_decode($result, true);
				//echo 'debug<br><pre>'; print_r($return);
				if ($return['success'] == 'true') {
					$adddata['post_status'] = 1;
					$msg = 'zip文件生成成功，上报成功';
				} else {
					$adddata['post_status'] = 0;
					$msg = 'zip文件生成成功，上报失败';
				}
				
				M('nifa_tongji')->add($adddata);
				
				$return['status'] = 1;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . $msg;
				exit(json_encode($return));
			} else {
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . 'zip文件生成失败';
				exit(json_encode($return));
			}
		}
		
		
		
		
	}
	
	
	//每次执行上传一天的数据
	public function zhj_create_oneDay(){
		$borrow_create_arr = FS("Webconfig/borrow_create_arr");
		// //$borrow_create_arr = array();
		// FS("borrow_create_arr",$borrow_create_arr, "Webconfig/");
		// echo 'debug1<br><pre>'; print_r($borrow_create_arr);
		// echo count($borrow_create_arr);
		// exit;
		
		
		
		
		//$borrow_create_arr = FS("Webconfig/borrow_create_arr");
		
		
		$array_repaying = array(1522,1530,1547,1555,1566,1570,1578,1598,1606,1607,
								1625,1626,1627,1628,1634,1643,1647,1654,1657,1706,
								1709,1716,1720,1762,1768,1793,1867,1868);
		$start = date("Y-m-d", (time() - 86400));
		$begin_time = strtotime($start) - 1;//前天23:59：59开始时间
		$begin_time = strtotime('2018-1-2 23:59:59');
		$end = date("Y-m-d", time());
		$end_time = strtotime($end);//上报数据截止时间今天00:00:00;
		$end_time = strtotime('2018-1-4 00:00:00');
		
		$where['second_verify_time'] = array('between', array($begin_time, $end_time));
		$where['bi.id'] = array('in',$array_repaying);
		//$where['bi.id'] = array('lt', 515);
		
		
		$borrow_info = '';//项目信息
		$borrower = '';//借款人信息
		$investor = '';//出借人信息
		$appoint_repay_detail = '';//约定还款计划数据项
		$real_repay_detail = '';//项目变更信息(实际还款信息)
		$borrow_ids = array(); //记录已生成的标的
		
		$list = M('borrow_info bi')
			->field('bi.id,bi.borrow_uid,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,
			has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,
			zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($where)
			// ->limit(50)
			->select();
		
		
		foreach($list as $key=>$value){
			
			//还款方式
			if($value['repayment_type'] == 4){
				$repayment_type = '04';//先息后本
			}elseif ($value['repayment_type'] == 5){
				$repayment_type = '05';//末期本息
			}
			
			//借款用途
			if($value['borrow_type'] == 1){//抵押
				$borrow_use = '01';//个人消费
			}elseif ($value['borrow_type'] == 2){//质押
				$borrow_use = '01';
			}elseif ($value['borrow_type'] == 3){//企业贷
				$borrow_use = '03';//企业经营周转
			}
			
			//借款项目平台服务费
			switch ($value['borrow_duration']) {
				case '1':
					$borrow_fee_rate = (11.76 + 1) / 100;
					break;
				case '3':
					$borrow_fee_rate = (10.56 + 1.6) / 100;
					break;
				case '6':
					$borrow_fee_rate = (9.96 + 2.2) / 100;
					break;
				case '12':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 2.6) / 100 : (8.26 + 2.6) / 100;
					break;
				case '18':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 3.6) / 100 : (8.26 + 3.6) / 100;
					break;
				case '24':
					$borrow_fee_rate = $value['repayment_type'] == 4 ? (8.76 + 4.6) / 100 : (8.26 + 4.6) / 100;
					break;
			}
			$borrow_fee = $value['borrow_money'] * $borrow_fee_rate;
			
			//出借人个数
			$investor_num = M('borrow_investor')->distinct(true)->field('investor_uid')->where('borrow_id=' . $value['id'])->select();
			$investor_num = count($investor_num);
			
			
			
			//还款期数
			if($value['repayment_type'] == 4){
				$borrow_duration = $value['borrow_duration'];
			}elseif ($value['repayment_type'] == 5){
				$borrow_duration = 1;
			}
			
			
			// 1.项目信息数据项
			$borrow_info .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
				date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
				"91320200323591589D" . "|" .//社会信用代码
				$value['id'] . "|" .//项目编号
				getFloatValue($value['borrow_money'], 4) ."|" .//借款金额
				date("Ymd", $value['second_verify_time']) . "|" .//借款起息日
				date("Ymd", $value['deadline']) . "|" .//借款到期日期
				getFloatValue(($value['borrow_interest_rate'] / 100), 4) . "|" .//出借利率
				getFloatValue($borrow_fee, 4) . "|" .//借款项目平台服务费
				getFloatValue(0, 4) . "|" .//第三方代偿保障费用
				$repayment_type . "|" .//还款方式
				$borrow_use . "|" .//借款用途
				$investor_num . "|" .//出借人个数
				$borrow_duration //约定还款总期数
				. "\r\n";
			
			
			//2.约定还款计划
			$appoint = M('investor_detail')
				->field("FROM_UNIXTIME(deadline, '%Y%m%d') as deadline,sum(interest) as interest,sum(capital) as capital,sort_order,total")
				->group('sort_order')
				->where('borrow_id='.$value['id'])
				->select();
			//echo 'debug<br><pre>'; print_r($appoint); exit;
			
			foreach($appoint as $k=>$v){
				$appoint_repay_detail .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
					date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
					"91320200323591589D" . "|" .//社会信用代码
					$value['id'] . "|" .//项目编号
					$borrow_duration . "|" .//约定还款总期数
					$v['sort_order'] . "|" .//约定还款期次
					$v['deadline'] . "|" .//约定还款日期
					getFloatValue($v['capital'],4) . "|" .//约定还款本金
					getFloatValue($v['interest'],4) //约定还款利息
					."\r\n";
				
			}
			
			
			//借款人类型
			if($value['lztype'] == 2){
				$borrower_type = '02';//法人
				$card_type = 'aa';//aa 社会信用代码、bb 组织机构代码、cc 营业执照编号
				$idcode = $value['zhaiquan_idcard'];
				$sex = '';
				$income = '02';//协会已将法人的年收入字段改为“01.一百万以内 02.一百万至五百万 03.五百万至一千万 04.一千万至五千万 05五千万以上（区间范围为左闭右开）”
				$career = '';
				
			}else{
				$borrower_type = '01';//自然人
				$card_type = '01'; //身份证
				//证件号码
				if (substr($value['zhaiquan_idcard'], 17, 1) == 'x') {
					$idcode = substr($value['zhaiquan_idcard'], 0, 17) . 'X';
				} else {
					$idcode = $value['zhaiquan_idcard'];
				}
				//性别
				$sexint = substr($value['zhaiquan_idcard'], 16, 1);
				
				if ($sexint % 2 == 0) {
					$sex = '02';
				} elseif ($sexint % 2 != 0) {
					$sex = '01';
				}
				$income = '03';
				$career = '80000';//自然人时 职业类型80000不便分类的其他从业人员
			}
			
			
			
			
			//3.借款人信息
			$borrower .="91320200323591589D" . $value['id'] . "|" .//项目唯一编号
				date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
				"91320200323591589D" . "|" .//社会信用代码
				$value['id'] . "|" .//项目编号
				$borrower_type. "|" .//借款人类型
				$value['borrow_uid']. "|" .//借款人id
				$card_type. "|" .//证件类型
				$idcode. "|" .//证件号码
				$sex. "|" .//借款人性别
				$career. "|" .//职业类型
				$income//借款人年平均收入
				."\r\n";
			
			
			//4.出借人信息
			$borrow_investor = M('borrow_investor id')
				->field('investor_uid,sum(investor_capital) as capital,idcard')
				->join('lzh_member_info as lmi on lmi.uid = id.investor_uid')
				->where('borrow_id='.$value['id'])
				->group('investor_uid')
				->select();
			foreach ($borrow_investor as $kk=>$vv){
				if (substr($vv['idcard'], 17, 1) == 'x') {
					$idnum = substr($vv['idcard'], 0, 17) . 'X';
				} else {
					$idnum = $vv['idcard'];
				}
				
				$investor .="91320200323591589D" . $value['id'] . "|" .//项目唯一编号
					date("Ymd", $value['second_verify_time']) . "|" .//业务日期(项目成立日期)
					"91320200323591589D" . "|" .//社会信用代码
					$value['id'] . "|" .//项目编号
					'01'. "|" .//出借人类型 //01 自然人，02 法人
					$vv['investor_uid']. "|" .//出借人id
					'01'. "|" .//出借人证件类型
					$idnum. "|" .//证件号码
					'02'. "|" .//出借人年收入
					getFloatValue($vv['capital'],4)//出借金额
					."\r\n";
			}
			
			//存储所有标的ID
			// if(!empty($borrow_create_arr)){
			// 	if(!empty($value['id'])){
			// 		if(!in_array($value['id'],$borrow_create_arr)){
			// 			$borrow_create_arr[] = $value['id'];
			// 		}
			// 	}
			// }else{
			// 	if(!empty($value['id'])){
			// 		$borrow_create_arr[] = $value['id'];
			// 	}
			// }
			
			
		}
		
		
		//写入配置文件中
		//FS("borrow_create_arr",$borrow_create_arr, "Webconfig/");
		//echo 'debug<br><pre>'; print_r($borrow_create_arr); exit;
		
		
		//5.项目变更信息(实际还款信息)，随借款项目状态变化报送
		
		//昨日还款标的
		$status['repayment_time'] = array('between',array($begin_time,$end_time));
		$status['borrow_id'] = 1547;
		//$status['borrow_id'] = array('in',$borrow_create_arr);
		$repay_id = M('investor_detail id')
			->distinct(true)
			->field('borrow_id')
			->where($status)
			->select();
		//echo $repay_id->getLastSql();die;
		foreach ($repay_id as $m=>$n){
			$repayId[] = $n['borrow_id'];
		}
		$repay_status['bi.id'] = array('in',$repayId);
		$repay_list = M('borrow_info bi')
			->field('bi.id,bi.borrow_uid,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,
			has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,
			zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($repay_status)
			// ->limit(50)
			->select();
		
		
		
		foreach ($repay_list as $key=>$value){
			//实际还款期次
			$detail = M('investor_detail');
			$result = $detail->where('borrow_id=' . $value['id'])->select();
			$oriResult = $detail->field("sum(receive_capital) as capital,sum(receive_interest) as interest,
				total,sort_order,repayment_time")
				->where('borrow_id=' . $value['id'])->group('sort_order')->select();
			//以还款时间为下标将还款数据重组成新的二维数组
			$arr1 = array();
			$arr2 = array();
			foreach ($result as $m => $n) {
				$arr1[date('Y-m-d', $n['repayment_time'])]['total'] = $n['total'];
			}
			unset($arr1['1970-01-01']);//删除还未还款的期数
			$real_total = count($arr1);
			
			//echo 'debug<br><pre>'; print_r($arr1);echo $real_total; exit;
			
			
			//拼接数据
			foreach ($oriResult as $m=>$n){
				
				if (($n['repayment_time'] > $begin_time) && ($n['repayment_time'] < $end_time)) {
					
					//还款相关判断
					if (($value['repayment_type'] == 4 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 4 && $value['is_prepayment'] != 0)) {
						if($n['sort_order'] > $real_total){
							$residue_money = 0;//剩余本金
							$residue_interest = 0;//剩余利息
							$current_status = '05';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = 0;//当期还款利息
						}elseif($n['sort_order'] == $real_total){
							$residue_money = 0;//剩余本金
							$residue_interest = 0;//剩余利息
							$current_status = '05';//当前期次状态
							$repay_capital = $value['borrow_money'];//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}else{
							$residue_money = $value['borrow_money'];//当期剩余本金
							$residue_interest = $n['interest']*$n['total']-$n['interest']*$n['sort_order'];//剩余利息
							$current_status = '01';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}
						if($n['sort_order'] == $n['total']){
							$borrow_status = '02';//借款项目状态  只在最后一期项目状态为完结02
						}else{
							$borrow_status = '01';//借款项目状态
						}
					}else{
						if($n['sort_order'] == $n['total']){
							$residue_money = 0;
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '01';//当前期次状态
							$repay_capital = $value['borrow_money'];//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}else{
							$residue_money = $value['borrow_money'];
							$residue_interest = $n['interest']*$n['total']-$n['interest']*$n['sort_order'];//剩余利息
							$borrow_status = '01';//借款项目状态
							$current_status = '01';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}
						if(($value['repayment_type'] == 5 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 5 && $value['is_prepayment'] != 0)){
							$current_status = '05';//当前期次状态
						}
					}
					
					$real_repay_detail .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
						date('Ymd',$n['repayment_time']) . "|" .//业务日期(当前期次还款日期)
						"91320200323591589D" . "|" .//社会信用代码
						$value['id'] . "|" .//项目编号
						$n['sort_order'] . "|" .//实际还款期次
						date('Ymd',$n['repayment_time']) . "|" .//实际还款日期
						getFloatValue($repay_capital,4) . "|" .//实际还款本金
						getFloatValue($repay_interest,4) . "|" .//实际还款利息
						getFloatValue($residue_money,4) . "|" .//借贷余额（仅本金）
						getFloatValue($residue_interest,4) . "|" .//借款剩余利息
						$borrow_status . "|" .//借款项目状态
						$current_status . "|" .//当前期次状态
						getFloatValue(0, 4)//其他费用
						. "\r\n";
				}
			}
			
		}
		
		echo '项目信息<br><pre>'; print_r($borrow_info); //exit;
		echo '约定还款计划信息<br><pre>'; print_r($appoint_repay_detail); //exit;
		echo '借款人信息<br><pre>'; print_r($borrower); //exit;
		echo '出借人信息<br><pre>'; print_r($investor); //exit;
		echo '还款信息<br><pre>'; print_r($real_repay_detail); //exit;
		
		
		if (true) {
			//生成csv文件
			$filedir = '91320200323591589D' . date('Ymd', ($begin_time + 1)) . '24';
			
			if (empty($borrow_info) && empty($borrower) && empty($investor) && empty($appoint_repay_detail) && empty($real_repay_detail)) {
				
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 2, //2为数据空
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				// $chk = M('nifa_tongji')->where(array('filename' =>$filedir))->find();
				// if(!empty($chk)){
				// 	$adddata['id'] = $chk['id'];
				// 	$adddata['status'] = $return['success'] == 'true' ? 1 : 0;
				// 	$adddata['update_time'] = NOW_TIME;
				// }
				
				M('nifa_tongji')->add($adddata);
				
				
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = '当天数据为空';
				exit(json_encode($return));
				
			}
			if(!empty($borrow_info) && !empty($real_repay_detail)){
				$dataTxt = array(
					'borrow_info' => $borrow_info,
					'borrower' => $borrower,
					'investor' => $investor,
					'repay' => $appoint_repay_detail,
					'change' => $real_repay_detail,
				);
				$status = 1;
			}elseif (!empty($borrow_info) && empty($real_repay_detail)){
				$dataTxt = array(
					'borrow_info' => $borrow_info,
					'borrower' => $borrower,
					'investor' => $investor,
					'repay' => $appoint_repay_detail,
				);
				$status = 2;
			}elseif (empty($borrow_info) && !empty($real_repay_detail)){
				$dataTxt = array(
					'change' => $real_repay_detail,
				);
				$status = 3;
			}
			
			
			$create_res = $this->createTxt($dataTxt, $filedir,$status);
			// $this->createTxt($this->fileName['binfobor'], $borrower, $filedir);
			// $this->createTxt($this->fileName['binfoinr'], $investor, $filedir);
			
			
			// $return['info'] = '文件生成成功';
			// exit(json_encode($return));
			// die;
			
			// $return['status'] = 1;
			// $return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
			// $return['info'] = $filedir . '文件生成成功';
			// exit(json_encode($return));
			
			
			
			if ($create_res) {
				$adddata = array(
					'admin_uid' => 136,
					'systemId' => 1,
					'stype' => 24,
					'filename' => $filedir,
					'status' => 1, //
					'post_date' => date('Y-m-d', ($begin_time + 1)),
					'add_time' => NOW_TIME
				);
				//上报数据
				$sourcePath = 'D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/' . $filedir . '.zip';
				
				$nifa_url = 'http://localhost:8888/nifa/data/upload?id=1&sourcePath=' . urlencode($sourcePath);
				
				//D:/wwwroot/api.51daishu.com/UF/Uploads/Nifa/91320200323591589D2017052524001.zip
				// header("Location:".$nifa_url);
				
				$result = $this->getUrl($nifa_url);
				$return = json_decode($result, true);
				echo 'debug<br><pre>'; print_r($return);
				if ($return['success'] == 'true') {
					$adddata['post_status'] = 1;
					$msg = 'zip文件生成成功，上报成功';
				} else {
					$adddata['post_status'] = 0;
					$msg = 'zip文件生成成功，上报失败';
				}
				
				M('nifa_tongji')->add($adddata);
				
				$return['status'] = 1;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . $msg;
				exit(json_encode($return));
			} else {
				$return['status'] = 0;
				$return['date'] = date('Y-m-d H:i:s', $begin_time) . '~' . date('Y-m-d H:i:s', $end_time);
				$return['info'] = $filedir . 'zip文件生成失败';
				exit(json_encode($return));
			}
		}
		
		
		
		
	}
	//测试提前还款
	public function testrepay()
	{
		
		$begin_time = strtotime('2017-07-26 23:59:59');
		$end_time = strtotime('2017-07-28 00:00:00');
		$real_repay_detail = '';
		
		//5.项目变更信息(实际还款信息)，随借款项目状态变化报送
		
		//昨日还款标的
		$status['repayment_time'] = array('between', array($begin_time, $end_time));
		//$status['borrow_id'] = array('in', $borrow_create_arr);
		$status['borrow_id'] = 1208;
		$repay_id = M('investor_detail id')
			->distinct(true)
			->field('borrow_id')
			->where($status)
			->select();
		//echo $repay_id->getLastSql();die;
		foreach ($repay_id as $m => $n) {
			$repayId[] = $n['borrow_id'];
		}
		$repay_status['bi.id'] = array('in', $repayId);
		$repay_list = M('borrow_info bi')
			->field('bi.id,bi.borrow_uid,borrow_name,second_verify_time,borrow_money,deadline,borrow_interest_rate, borrow_duration,repayment_type,
			has_pay,borrow_interest,borrow_uid,borrow_status,is_advanced,is_prepayment,idcard,custrole_type,bankname,idno,cardid,bankid,
			zhaiquan_idcard,lz.type as lztype,zhaiquan_bankinfo,mortgage,lz.borrow_type')
			->join('lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			->join('lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			->join('lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->join('lzh_zhaiquan as lz on bi.borrow_zhaiquan = lz.id')
			->where($repay_status)
			// ->limit(50)
			->select();
		
		
		foreach ($repay_list as $key => $value) {
			//实际还款期次
			$detail = M('investor_detail');
			$result = $detail->where('borrow_id=' . $value['id'])->select();
			$oriResult = $detail->field("sum(receive_capital) as capital,sum(receive_interest) as interest,total,sort_order,repayment_time")
				->where('borrow_id=' . $value['id'])->group('sort_order')->select();
			
			//以还款时间为下标将还款数据重组成新的二维数组
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
					$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
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
					$arr2[date('Y-m-d', $vvv['repayment_time'])]['substitute_money'] += $vvv['substitute_money'];
				}
			}
			unset($arr2['1970-01-01']);
			//echo 'debug<br><pre>'; print_r($arr2); exit;
			
			//echo 'debug<br><pre>'; print_r($oriResult); exit;
			
			//拼接数据
			foreach ($oriResult as $m=>$n){
				
				if (($n['repayment_time'] > $begin_time) && ($n['repayment_time'] < $end_time)) {
					
					//还款相关判断
					if (($value['repayment_type'] == 4 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 4 && $value['is_prepayment'] != 0)) {
						if($n['sort_order'] > $real_total){
							$residue_money = 0;//剩余本金
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '05';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = 0;//当期还款利息
						}elseif($n['sort_order'] == $real_total){
							$residue_money = 0;//剩余本金
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '05';//当前期次状态
							$repay_capital = $value['borrow_money'];//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}else{
							$residue_money = $value['borrow_money'];//当期剩余本金
							$residue_interest = $n['interest']*$n['total']-$n['interest']*$n['sort_order'];//剩余利息
							$borrow_status = '01';//借款项目状态
							$current_status = '01';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}
					}else{
						if($n['sort_order'] == $n['total']){
							$residue_money = 0;
							$residue_interest = 0;//剩余利息
							$borrow_status = '02';//借款项目状态
							$current_status = '01';//当前期次状态
							$repay_capital = $value['borrow_money'];//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}else{
							$residue_money = $value['borrow_money'];
							$residue_interest = $n['interest']*$n['total']-$n['interest']*$n['sort_order'];//剩余利息
							$borrow_status = '01';//借款项目状态
							$current_status = '01';//当前期次状态
							$repay_capital = 0;//当期还款本金
							$repay_interest = $n['interest'];//当期还款利息
						}
						if(($value['repayment_type'] == 5 && $value['is_advanced'] != 0) || ($value['repayment_type'] == 5 && $value['is_prepayment'] != 0)){
							$current_status = '05';//当前期次状态
						}
					}
					
					$real_repay_detail .= "91320200323591589D" . $value['id'] . "|" .//项目唯一编号
						date('Ymd',$n['repayment_time']) . "|" .//业务日期(当前期次还款日期)
						"91320200323591589D" . "|" .//社会信用代码
						$value['id'] . "|" .//项目编号
						$n['sort_order'] . "|" .//实际还款期次
						date('Ymd',$n['repayment_time']) . "|" .//实际还款日期
						getFloatValue($repay_capital,4) . "|" .//实际还款本金
						getFloatValue($repay_interest,4) . "|" .//实际还款利息
						getFloatValue($residue_money,4) . "|" .//借贷余额（仅本金）
						getFloatValue($residue_interest,4) . "|" .//借款剩余利息
						$borrow_status . "|" .//借款项目状态
						$current_status . "|" .//当前期次状态
						getFloatValue(0, 4)//其他费用
						. "\r\n";
				}
			}
			
			
		}
		echo 'debug<br><pre>'; print_r($real_repay_detail); exit;
		
		
		
	}
	
	


	//生存本地文件
	private function createTxt($dataTxt, $filedir = '', $status)
	{
		header("Content-Type: text/html;charset=utf-8");

		if($status == 1){
			$config_fileName = array(
				'borrow_info' => '24EXPORTBUSINESSZHAIQ.csv',         // 互联网债权类融资项目信息
				'borrower' => '24EXPORTBUSINESSZHAIQ_BOR.csv',    // 互联网债权类融资借款人信息
				'investor' => '24EXPORTBUSINESSZHAIQ_INV.csv',     // 互联网债权类融资出借人信息
				'repay' => '24EXPORTBUSINESSZHAIQ_REPAY.csv',     // 互联网债权类融资约定还款计划
				'change' => '24EXPORTBUSINESSZHAIQ_CHANGE.csv',     // 互联网债权类融资项目变更信息
			);
		}elseif ($status == 2){
			$config_fileName = array(
				'borrow_info' => '24EXPORTBUSINESSZHAIQ.csv',         // 互联网债权类融资项目信息
				'borrower' => '24EXPORTBUSINESSZHAIQ_BOR.csv',    // 互联网债权类融资借款人信息
				'investor' => '24EXPORTBUSINESSZHAIQ_INV.csv',     // 互联网债权类融资出借人信息
				'repay' => '24EXPORTBUSINESSZHAIQ_REPAY.csv',     // 互联网债权类融资约定还款计划
			);
		}elseif($status == 3){
			$config_fileName = array(
				'change' => '24EXPORTBUSINESSZHAIQ_CHANGE.csv',     // 互联网债权类融资项目变更信息
			);
		}
		

		$dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/UF/Uploads/Nifa/' . $filedir;
		if (!is_dir($dir)) {
			mkdir($dir); //创建文件夹
		}
		$fileList = array();
		foreach ($dataTxt as $key => $value) {
			$filename = $config_fileName[$key];
			$local = $dir . '/' . $filename;
			$fileList[] = $local;
			//if ($key == 'investor') {
							// $txtcontent = iconv("GBK",'UTF-8',$value);
							// $txtcontent=iconv("GB2312",'UTF-8',$value);
				file_put_contents($local, chr(0xEF) . chr(0xBB) . chr(0xBF) . $value, true);
							// file_put_contents($local, $txtcontent, true);
			//} else {
				//$file = fopen($local, "w");
							# Now UTF-8 - Add byte order mark
							// fwrite($file, pack("CCC",0xef,0xbb,0xbf));

				//fwrite($file, $value);
				//fclose($file);

				unset($file, $local);
			//}

		}
		//生成压缩包
		$zip_name = $dir . ".zip";
		$zip = new ZipArchive();
		$zip->open($zip_name, ZipArchive::CREATE);   //打开压缩包
		foreach ($fileList as $file) {
			$zip->addFile($file, basename($file));   //向压缩包中添加文件
		}


		if ($zip->open($zip_name, \ZipArchive::CREATE) !== TRUE) {
			$zipstatus = 0;
		} else {
			$zipstatus = 1;
		}
		$zip->close();  //关闭压缩包

		return $zipstatus;
	}

	//百行文件上报方式：生成zip文件//加密生成cry文件
	private function createZip($filename, $txtcontent)
	{
		$dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/UF/Uploads/baihang';
		if (!is_dir($dir)) {
			mkdir($dir); //创建文件夹
		}
		$file_dir = $dir . '/' . $filename;
		file_put_contents($file_dir, $txtcontent, true);

		//生成压缩文件
		$zipname = $dir . '/' . str_replace(strrchr($filename, "."), "", $filename) . ".zip";
		$zip = new ZipArchive();
		$zip->open($zipname, ZipArchive::CREATE);//打开压缩包
		$zip->addFile($file_dir, basename($file_dir));//向压缩包中添加文件
		if ($zip->open($zipname, \ZipArchive::CREATE) !== TRUE) {
			$zipstatus = 0;
			return 'zip文件生成失败';
		} else {
			$zipstatus = 1;
		}
		$zip->close();  //关闭压缩包


		$config = array(
		    'AES_KEY' => 'WWW51DAISHUCOM', // AES密钥
		    'privateKeyFilePath' => $_SERVER['DOCUMENT_ROOT'].'/CORE/Extend/Library/ORG/BaihangCredit/cert/rsa_private_key.pem', //己方私钥签名生成sign
		    'publicKeyFilePath' =>$_SERVER['DOCUMENT_ROOT'].'/CORE/Extend/Library/ORG/BaihangCredit/cert/rsa_public_key.pem', //百行提供的 RSA 公钥
	    );
		//加密生成cry文件
		$cry_file = $dir . '/' . str_replace(strrchr($filename, "."), "", $filename) . ".cry";
		import("ORG.BaihangCredit.BaihangCredit");
		$rsa = new Rsa($config['publicKeyFilePath'], $config['privateKeyFilePath']);
		$cry_content = ''; //cry 的密文内容

		//将ASE密钥加密后放入加密文件的第一行 PHP_EOL
		$cry_content .= $rsa->encrypt($config['AES_KEY']) . PHP_EOL;

		$aes = new CryptAES();
		// $aes->set_iv($config['AES_KEY']);
		$aes->set_key($config['AES_KEY']);


		//$new_path = iconv('UTF-8','GB2312',$new_path);
		if (file_exists($zipname)) {
			$fp = fopen($zipname, "r");
			$buffer = 1024;//每次读取 1024 字节
			while (!feof($fp)) {//循环读取，直至读取完整个文件
				$str = fread($fp, $buffer);
				$cry_content .= $aes->encrypt($str) . PHP_EOL;
				// $cry_content .= $str . '$$';

				unset($str);
			}

			//生成cry密文文件
			//$new_file_cry = iconv('UTF-8','GB2312',$cry_file);
			$file_cry = fopen($cry_file, "w");
			fwrite($file_cry, $cry_content);
			fclose($file_cry);

			unset($file_cry);

			return $cry_content;
		} else {
			return $zipname . '不存在';
		}

	}

	//信息共享平台生成生成txt并压缩成zip文件
	private function creZip($filename, $txtcontent)
	{
		$dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/UF/Uploads/gongxiang';
		if (!is_dir($dir)) {
			mkdir($dir); //创建文件夹
		}
		$file_dir = $dir . '/' . $filename;
		$txtcontent = iconv('utf-8', "GBK", $txtcontent); //转gbk格式
		file_put_contents($file_dir, $txtcontent, true);

		//生成压缩文件
		$packagename = '323591589' . date('Ymd', time()) . '12' . '0001' . '.zip';
		$zipname = $dir . '/' . $packagename;
		$zip = new ZipArchive();
		$zip->open($zipname, ZipArchive::CREATE);//打开压缩包
		$zip->addFile($file_dir, basename($file_dir));//向压缩包中添加文件
		if ($zip->open($zipname, \ZipArchive::CREATE) !== TRUE) {
			$zipstatus = 0;
			return 'zip文件生成失败';
		} else {
			$zipstatus = 1;
		}
		$zip->close();  //关闭压缩包
	}

	/**
	 * @param $url
	 * @param $params
	 * @return mixed
	 * get请求
	 */
	private function getUrl($url, $params = array(), $timeout = 600)
	{
		$url = strstr($url, '?') ? $url . '&' . http_build_query($params) : $url . '?' . http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

	//生存本地文件
	private function getTxt($filename, $txtcontent)
	{
		$local = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/UF/' . $filename;

		// $txtcontent=iconv('utf-8',"GBK",$txtcontent); //转gbk格式

		file_put_contents($local, $txtcontent, true);
	}

	// public function convertEncoding($string){
	// 	//根据系统进行配置
	// 	 $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
	// 	 $string = iconv('UTF-8', $encode, $string);
	// 	 $string = mb_convert_encoding($string, $encode, 'UTF-8');
	// 	 return $string;
	// }


	//计算提前还款的实际利息
	public function calculate()
	{
		//未计利息天数
		//$unborrow_days = ceil((1544111999 - 1543294217)/86400);
		$unborrow_days = ceil((1530806399 - 1529990995) / 86400);
		echo '<br>未计算天数 = ' . $unborrow_days . '天<br>';

		$month_days = 30;
		$borrow_days = $unborrow_days < 30 ? ($month_days - $unborrow_days + 1) : 0; //实际计息天数
		echo '<br>实际计息天数 = ' . $borrow_days . '天<br>';
		//如果每月还息
		$interest = 375.00 / 30 * $borrow_days;
		$borrow_interest = 700.00 / 30 * $borrow_days;

		echo '<br>实际出借人收到利息=' . $interest;
		echo '<br>实际借款人还款利息=' . $borrow_interest;
		exit;
	}

	//查询repayment_time
	public function check_repayment_time()
	{
		//$where['second_verify_time&borrow_status&is_advanced&repayment_type'] = array(array('gt','1472054399'),7,2,4,'_multi'=>true);
		//$where['_logic'] = 'OR';
		$where['bi.id'] = 1834;//1835 1834 1614 1613 1604 1563
		$list = M('borrow_info bi')
			->field('bi.id')
			// ->join('left join lzh_member_jshbank as lmj on bi.borrow_uid = lmj.uid')
			// ->join('left join lzh_member_info as lmi on bi.borrow_uid = lmi.uid')
			// ->join('left join lzh_member_chinapnr as lmc on bi.borrow_uid = lmc.uid')
			->where($where)
			//->limit(10)
			->select();
		//print_r($list);die;
		echo M('lzh_borrow_info bi')->getLastSql();
		echo "<hr>";

		foreach ($list as $key => $value) {
			$result = M('investor_detail')->where('borrow_id=' . $value['id'])->select();
			//实际还款记录
			$arr1 = array();
			$arr2 = array();
			//只有一个还款时间
			foreach ($result as $kk => $vv) {
				if ($result[0]['repayment_time'] > $result[0]['deadline']) {
					$arr2[$value['id'] . "-" . (date('Y-m-d', $vv['repayment_time']))]['receive_capital'] += $vv['receive_capital'];
					$arr2[$value['id'] . "-" . (date('Y-m-d', $vv['repayment_time']))]['receive_interest'] += $vv['receive_interest'];
					$arr2[$value['id'] . "-" . (date('Y-m-d', $vv['repayment_time']))]['repayment_time'] = date('Y-m-d', $vv['repayment_time']);
				}
			}

			unset($arr1[$value['id'] . "-" . '1970-01-01']);
			if (count($arr2) == 1) {
				//print_r($arr1);
				$new1[] = $value['id'];
			}

			//还款时间大于一个
			foreach ($result as $kkk => $vvv) {
				if ($result[0]['repayment_time'] > $result[0]['deadline']) {
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['receive_capital'] += $vvv['receive_capital'];
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['receive_interest'] += $vvv['receive_interest'];
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['repayment_time'] = date('Y-m-d', $vvv['repayment_time']);

					$new[] = $value['id'];
				}
				$new = array_unique($new);
			}
			unset($arr1[$value['id'] . "-" . '1970-01-01']);
		}
		echo count($new);
		foreach ($new as $hhh => $mmm) {
			$newss[] = $mmm;
		}
		//print_r($newss);
		foreach ($newss as $uuu => $yyy) {
			if (!in_array($yyy, $new1)) {
				$last[] = $yyy;
			}
		}
		print_r($new1);
		echo "<hr>";
		//$new1 = array_slice($new1,0,10);
		print_r($new1);
		//修改提前还款时间；已修改完成，暂时注释
		$status5['borrow_id'] = array('in', $new1);
		$result_new = M('investor_detail')->field('repayment_time,deadline,id')->where($status5)->select();
		//print_r($result_new);die;
		foreach ($result_new as $way => $wa) {
			if ($wa['repayment_time'] > $wa['deadline']) {
				M('investor_detail')->where('id=' . $wa['id'])->setField('repayment_time', strtotime("-9 hour", $wa['deadline'] - 328));
			}
		}
		//print_r($last);echo count($last);

	}


	//批量修复提前还款更新所有期数还款时间
	//
	public function batchUpdateInvestDetail()
	{

		$where['second_verify_time'] = array('gt', strtotime("2016-08-24"));
		$where['is_advanced'] = array('in', array('1', '2'));
		$where['borrow_status'] = 7;
		$where['repayment_type'] = 4;
		// $list = M('borrow_info bi')
		//     ->field('bi.id, bi.borrow_name, bi.repayment_type, bi.is_advanced, bi.borrow_duration, id.borrow_id,id.repayment_time, id.deadline, id.real_repayment_time')
		//     ->join('lzh_investor_detail as id on id.borrow_id = bi.id')
		//     ->where($where)
		//     ->limit(20)
		//     ->select();
		$list = M('borrow_info bi')
			->field('bi.id, bi.borrow_name, bi.repayment_type, bi.is_advanced, bi.borrow_duration')
			->where($where)
			->limit(2)
			->select();

		$arr1 = array();
		foreach ($list as $key => $value) {
			$result = M('investor_detail')->where('borrow_id=' . $value['id'])->select();
			//实际还款记录
			foreach ($result as $kkk => $vvv) {
				if ($result[0]['repayment_time'] > $result[0]['deadline']) {
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['receive_capital'] += $vvv['receive_capital'];
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['receive_interest'] += $vvv['receive_interest'];
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['repayment_time'] = date('Y-m-d H:i:s', $vvv['repayment_time']);
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['deadline'] = date('Y-m-d H:i:s', $vvv['deadline']);
					$arr1[$value['id'] . "-" . (date('Y-m-d', $vvv['repayment_time']))]['sort_order'] = $vvv['sort_order'];

					//更新 repayment_time
					$real_repayment_time = $vvv['deadline'] - rand(43200, 50000);
					M('investor_detail')->where("id=" . $vvv['id'])->setField('repayment_time', $real_repayment_time);
					echo '<Br>update_' . $vvv['id'] . '=' . date("Y-m-d H:i:s", $real_repayment_time);
				}
				unset($real_repayment_time);
			}

			unset($arr1[$value['id'] . "-" . '1970-01-01']);
			if (count($arr1) == 1) {
				//print_r($arr1);
				$new[] = $value['id'];
			}

		}
		echo 'debug<br><pre>';
		print_r($arr1);
		print_r($new);
		exit;
		echo M()->getLastSql();
		echo 'debug<br><pre>';
		print_r($list);
		var_dump($list);
		exit;
	}


	//批量修复提前还款更新回收本金及利息
	//
	public function batchRepireInvestDetail()
	{

		exit;
		$borrow_ids_arr = FS("Webconfig/borrow_ids_arr");
		$i = 0;
		$update = array();
		foreach ($borrow_ids_arr as $key => $value) {

			// if($i == 20){
			// 	break;
			// }
			$result = M('investor_detail')->where('borrow_id=' . $value)->select();
			//实际还款记录

			foreach ($result as $kkk => $vvv) {
				if ($vvv['receive_interest'] != $vvv['interest'] || $vvv['receive_capital'] != $vvv['capital']) {
					// echo '<br>'.$i.'：borrow_id='.$value.'存在第'.$vvv['sort_order'].'期数据不一致';
					$errArr[] = $value;

					if ($vvv['receive_interest'] != $vvv['interest']) {
						$update['receive_interest'] = $vvv['interest'];
					}
					if ($vvv['receive_capital'] != $vvv['capital']) {
						$update['receive_capital'] = $vvv['capital'];
					}
					$upres = M('investor_detail')->where('id=' . $vvv['id'])->save($update);

					echo '<br>' . $i . '：borrow_id=' . $value . '存在第' . $vvv['sort_order'] . '期数据不一致=》' . (($upres !== false) ? '已修复' : '修复失败');
					unset($update);
				}
			}
			$i++;


		}
		echo '<br>debug<br><pre>';
		print_r($errArr);
		exit;
	}


	public function testSFTP()
	{

		// 1469079439    1500652799
		$timediff = timediff(1469079439, 1500652799);

		echo ceil((1500652799 - 1469079439) / (60 * 60 * 24));

		echo '<Br>starttime = ' . date('Y-m-d H:i:s', 1469079439);
		echo '<Br>endtime = ' . date('Y-m-d H:i:s', 1500652799);
		echo 'debug<br><pre>';
		print_r($timediff);
		var_dump($timediff);
		exit;
	}

	public function choose_name()
	{
		$array_name = array('一', '二', '更', '大', '锤', '小', '花', '四', '五', '六', '七', '娟', '罐', '轴', '车', '军', '山', '哈', '库', '是', '这', '谁', '水');
		$random = array_rand($array_name, 2);
		$name = '';
		foreach ($random as $v) {
			$name .= $array_name[$v];
		}
		return $name;
	}

	public function choose_names($bid){
		if(($bid < 500)&&($bid > 400)&&($bid % 2) == 1){
			$name = "哈哈";
		}elseif ((($bid == 500)||($bid < 500))&&($bid > 400)&&($bid % 2) == 0){
			$name = "测试";
		}elseif (($bid < 600)&&($bid > 500)&&($bid % 2) == 1){
			$name = "大大";
		}elseif ((($bid == 600)||($bid < 600))&&($bid > 500)&&($bid % 2) == 0){
			$name = "小小";
		}elseif (($bid < 700)&&($bid > 600)&&($bid % 2) == 1){
			$name = "高高";
		}elseif ((($bid == 700)||($bid < 700))&&($bid > 600)&&($bid % 2) == 0){
			$name = "胖胖";
		}elseif (($bid < 800)&&($bid > 700)&&($bid % 2) == 1){
			$name = "欢欢";
		}elseif ((($bid == 800)||($bid < 800))&&($bid > 700)&&($bid % 2) == 0){
			$name = "妞妞";
		}elseif (($bid < 900)&&($bid > 800)&&($bid % 2) == 1){
			$name = "瘦瘦";
		}elseif ((($bid == 900)||($bid < 900))&&($bid > 800)&&($bid % 2) == 0){
			$name = "圆圆";
		}elseif (($bid < 1000)&&($bid > 900)&&($bid % 2) == 1){
			$name = "果果";
		}elseif ((($bid == 1000)||($bid < 1000))&&($bid > 900)&&($bid % 2) == 0){
			$name = "春春";
		}elseif (($bid < 1100)&&($bid > 1000)&&($bid % 2) == 1){
			$name = "多多";
		}elseif ((($bid == 1100)||($bid < 1100))&&($bid > 1000)&&($bid % 2) == 0){
			$name = "少少";
		}elseif (($bid < 1200)&&($bid > 1100)&&($bid % 2) == 1){
			$name = "乖乖";
		}elseif ((($bid == 1200)||($bid < 1200))&&($bid > 1100)&&($bid % 2) == 0){
			$name = "巧巧";
		}elseif (($bid < 1300)&&($bid > 1200)&&($bid % 2) == 1){
			$name = "实在";
		}elseif ((($bid == 1300)||($bid < 1300))&&($bid > 1200)&&($bid % 2) == 0){
			$name = "大锤";
		}elseif (($bid < 1400)&&($bid > 1300)&&($bid % 2) == 1){
			$name = "大能";
		}elseif ((($bid == 1400)||($bid < 1400))&&($bid > 1300)&&($bid % 2) == 0){
			$name = "一一";
		}elseif (($bid < 1500)&&($bid > 1400)&&($bid % 2) == 1){
			$name = "可可";
		}elseif ((($bid == 1500)||($bid < 1500))&&($bid > 1400)&&($bid % 2) == 0){
			$name = "心心";
		}elseif (($bid < 1600)&&($bid > 1500)&&($bid % 2) == 1){
			$name = "工作";
		}elseif ((($bid == 1600)||($bid < 1600))&&($bid > 1500)&&($bid % 2) == 0){
			$name = "成功";
		}elseif (($bid < 1700)&&($bid > 1600)&&($bid % 2) == 1){
			$name = "结实";
		}elseif ((($bid == 1700)||($bid < 1700))&&($bid > 1600)&&($bid % 2) == 0){
			$name = "拿拿";
		}elseif (($bid < 1800)&&($bid > 1700)&&($bid % 2) == 1){
			$name = "花花";
		}elseif ((($bid == 1800)||($bid < 1800))&&($bid > 1700)&&($bid % 2) == 0){
			$name = "栓蛋";
		}elseif (($bid < 1900)&&($bid > 1800)&&($bid % 2) == 1){
			$name = "贝贝";
		}elseif ((($bid == 1900)||($bid < 1900))&&($bid > 1800)&&($bid % 2) == 0){
			$name = "京京";
		}
		return $name;
	}


}
