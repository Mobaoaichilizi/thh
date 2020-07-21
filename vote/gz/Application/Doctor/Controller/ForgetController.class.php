<?php
// +----------------------------------------------------------------------
// | 找回密码接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class ForgetController extends DoctorbaseController {
	function _initialize() {
		parent::_initialize();
		$this->member = D("Member");
		$this->sms    = D("Sms");
		$this->user   = D('User');
	}
	//验证手机号码是否存在
public	function forget_phone()
	{	
		
		$username = I('post.username');
		$role = I('post.role');
		if(empty($username))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='请输入手机号码！';
			outJson($data);
		}
		$res=$this->user->where("username='".$username."' and role=".$role)->find();
		if(empty($res))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='手机号码不存在！';
			outJson($data);
		}
		$starttime = time()-3600;//一小时只能发5封邮件！
		$count = $this->sms->where("phone='".$username."' and type=".$role." and createtime > ".$starttime)->count();
		if($count > 5){
			unset($data);
    		$data['code']='0'; 
       		$data['message']='您已经超过次数限制，请一小时后再获取验证码！';
			outJson($data);
		}
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
			"type" => $role,
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
	
public	function do_password()
	{
		$username = I('post.username');
		$role = I('post.role');
		$code = I('post.code');
		$password = I('post.password');
		$passworded = I('post.passworded');
		if(empty($username))
		{
			unset($data);
			$data['code']=0;
			$data['message']="手机号不存在！";
			outJson($data);
		}
		if(empty($code))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入验证码！";
			outJson($data);
		}
		$nowtime=time()-3600;  //一个小时之内有效
		$count = $this->sms->where("phone='".$username."' and code='".$code."' and createtime > ".$nowtime." and type=".$role)->count();
		if($count <= 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入正确的验证码！";
			outJson($data);
		}
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
		$member_uid=$this->user->where("username='".$username."' and role=".$role)->find();
		$res=array(
			'id' => $member_uid['id'],
			'password' => sp_password($password),
		);
		$result=$this->user->save($res);
		if($result!==false)
		{
			unset($data);
			$data['code']=1;
			$data['message']="修改成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="修改失败！";
			outJson($data);
		}
		
	}
	

}
?>