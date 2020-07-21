<?php
// +----------------------------------------------------------------------
// | 用户注册接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class RegisterController extends DoctorbaseController {
	function _initialize() {
		
		parent::_initialize();
		$this->user =D("User");
		$this->sms =D("Sms");
		$this->member = D('Member');
		$this->consultation = D('Consultation');
	}
public function do_register(){
		$username = I('post.username');
		$code = I('post.code');
		$os = I('post.os');
		$deviceid = I('deviceid');
		$verison = I('verison');
		if(empty($username))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入手机号！";
			outJson($data);
		}else{
			$user = $this->user->where("username='".$username."' and role = 1")->select();
			if($user){
				unset($data);
				$data['code']=0; 
				$data['message']='用户名已存在！';
				outJson($data);
			}
		}
		if(empty($code))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入验证码！";
			outJson($data);
		}
		$nowtime=time()-3600;  //一个小时之内有效
		$count = $this->sms->where("phone='".$username."' and type=1 and code='".$code."' and createtime > ".$nowtime)->count();
		if($count <= 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入正确的验证码！";
			outJson($data);
		}
		//验证密码
		$password = I('post.password');
		$passworded = I('post.passworded');
		
		if(empty($password))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入密码！";
			outJson($data);
		}
		if($password!=$passworded)
		{
			unset($data);
			$data['code']=0;
			$data['message']="两次密码不同！";
			outJson($data);
		}
		//邀请码
		$inviter_share_number = I('post.inviter_share_number','');
		$ress=$this->user->where("inviter_share_number='".$inviter_share_number."'")->find();
		if(!empty($inviter_share_number)){
			if(!$ress){
				unset($data);
				$data['code'] = 0;
				$data['message'] = "邀请码错误";
				outJson($data);
			}
		}
		$invite_num = get_rand_number(100000000,999999999,1);

		$res=array(
			'username' => $username,
			'password' => sp_password($password),
			'createtime' => time(),
			'role'     => 1,
			'inviter_share_number' => $inviter_share_number,
			'share_number' => $invite_num[0],
			'member_level ' => 0,
			'os' => $os,
		    'deviceid' => $deviceid,
		    'is_login' => 1,
		);
		$result=$this->user->add($res);
		

		$ress = array(
				'user_id' => $result,
				
			);
		$results = $this->member->add($ress);
		
		if($results)
		{
			if($result)
			{	
				if(!empty($inviter_share_number)){
					$invite_person = $this->user->field('id,role')->where('share_number='.$inviter_share_number)->find();
					$invite_num = $this->user->where('inviter_share_number='.$inviter_share_number)->count();

					if($inviter_person['role'] == 1){
						setpoints($result,1,5,'元','151',0);
						$balance = $this->user->where('id='.$results)->getField('balance');
						financial_log($result,5,3,$balance,'被邀请注册',8);
						setpoints($inviter_person['id'],1,5,'元','144',0);
						$ba = $this->user->where('id='.$inviter_person['id'])->getField('balance');
						financial_log($inviter_person['id'],5,3,$ba,'邀请注册',8);
						if($invite_num == 10){
							setpoints($inviter_person['id'],1,12,'元','153',0);
							$money = $this->user->where('id='.$inviter_person['id'])->getField('balance');
							financial_log($inviter_person['id'],12,3,$money,'邀请累计十人',8);
						}else if($invite_num == 5){
							setpoints($inviter_person['id'],1,5,'元','152',0);
							$money = $this->user->where('id='.$inviter_person['id'])->getField('balance');
							financial_log($inviter_person['id'],5,3,$money,'邀请累计五人',8);
						}
					}else if($inviter_person['role'] == 2){
						setpoints($result,1,100,'分','151',0);
						$score = $this->user->where('id='.$results)->getField('score');
						financial_log($result,100,3,$score,'被邀请注册',8,2);
						setpoints($inviter_person['id'],2,100,'分','144',0);
						$score1 = $this->user->where('id='.$inviter_person['id'])->getField('score');
						financial_log($inviter_person['id'],100,3,$score1,'邀请注册',8,2);
						if($invite_num == 10){
							setpoints($inviter_person['id'],2,110,'分','153',0);
							$score = $this->user->where('id='.$inviter_person['id'])->getField('score');
							financial_log($inviter_person['id'],110,3,$score,'邀请累计十人',8,2);
						}else if($invite_num == 5){
							setpoints($inviter_person['id'],2,50,'分','152',0);
							$score = $this->user->where('id='.$inviter_person['id'])->getField('score');
							financial_log($inviter_person['id'],5,3,$score,'邀请累计五人',8,2);
						}
					}
					
					
				}else{
					setpoints($result,1,3,'元','154',0);
					$balance = $this->user->where('id='.$result)->getField('balance');
					financial_log($result,3,3,$balance,'新用户注册',8);
				}
			$res = array(
				"doctor_id"=>$result,
			);
			$this->consultation->add($res);
			unset($data);
			$data['code']=1;
			$data['message']="注册成功！";
			$data['uid']=md5(md5($result));
			$data['role'] = 1;
			outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="注册失败！";
			outJson($data);
		}
	}



	//验证手机号码是否存在
public	function register_phone()
	{	
		//$verison = I('verison');
		$username = I('post.username');
		if(empty($username))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='请输入用户名！';
			outJson($data);
		}
		$res=$this->user->where("username='".$username."' and role=1")->find();
		if(!empty($res))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='用户名已存在！';
			outJson($data);
		}
		$starttime = time()-3600;//一小时只能发5封邮件！
		$count = $this->sms->where("phone='".$username."' and type=1 and createtime > ".$starttime)->count();
		if($count > 5){
			unset($data);
    		$data['code']='0'; 
       		$data['message']='您已经超过次数限制，请一小时后再获取验证码！';
			outJson($data);
		}
		//$rand=rand(1000,9999);
		//$str='';//短信内容
		//$result=sendMail($username,"系统","约呦-用户验证码通知",$str);
		
		$rand=rand(1000,9999);
		$str='尊敬的用户您好,您的验证码为'.$rand.'，请于5分钟内输入，请勿泄露。';
		
		require_once VENDOR_PATH."SmsApi/SmsApi.php";
		$aliyunsms = new SmsApi(C('WEB_SMS_USER'),C('WEB_SMS_PASS'));
		$response = $aliyunsms->sendSms(
			"广正云医", // 短信签名
			"SMS_115765080", // 短信模板编号
			$username, // 短信接收者
			Array (  // 短信模板中字段的值
				"code"=>$rand,
			)
		);
		$result= json_decode(json_encode($response),true);
		
		$res=array(
			"phone" => $username,
			"createtime" => time(),
			"type" => 1,
			"content" => $str,
			"code" => $rand,
		);
		if($result['Code']=='OK')
		{
			$this->sms->add($res);
			unset($data);
			$data['code']=1;
			$data['message']="短信发送成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="短信发送失败！";
			outJson($data);

		}
		
	}
}
?>