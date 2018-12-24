<?php
namespace Home\Controller;
use Common\Controller\BaseController;
class BindingController extends BaseController {
	public function _initialize() {
		parent::_initialize();
		$this->member=M('ShopMember');
		$this->shop=M('Shop');
		$this->smscode=M('Smscode');
		$this->openid=$_SESSION['wx_user'];
		$this->wx_i=I('get.i');
	}
	//绑定账号
	public function index()
	{
		
		$result=$this->member->where("openid='".$this->openid."' and chain_id=".$this->wx_i)->find();
		if($result['member_tel']!='')
		{
			$this->error("您已经绑定过会员了！");
		}
		
		$shop_list=$this->shop->where("chain_id=".$this->wx_i)->order("id desc")->select();
		$this->assign('shop_list',$shop_list);
		$this->assign('i',$this->wx_i);
		
		$shop_data   = M('Chain')->where("id=".$this->wx_i)->field('wx_appid,wx_appsecret')->find();
		Vendor('jssdk');
		$wx_jssdk = new \JSSDK($shop_data['wx_appid'],$shop_data['wx_appsecret']);
		$signPackage = $wx_jssdk->GetSignPackage();
		$this->assign('signPackage',$signPackage);
		
		
		$this->display();
	}
	//提交绑定账号
	public function binding()
	{
		$member_tel = I('post.member_tel'); //手机号
		$pincode=I('post.pincode'); //验证码
		$member_name=I('post.member_name'); //姓名
		$sex=I('post.sex'); //性别
		$birthday=I('post.birthday'); //生日
		$member_card=I('post.member_card'); //实体卡
		$i=I('post.i'); //连锁店ID
		$shop_id=I('post.shop_id'); //店ID
		if($i=='')
		{
			$this->error("您的连锁店铺不存在！");
		}
		if($shop_id=='')
		{
			$this->error("您的店铺不存在！");
		}
		if($member_tel=='')
		{
			$this->error("请输入您的手机号！");
		}
		if($pincode=='')
		{
			$this->error("请输入手机验证码！");
		}
		if($member_name=='')
		{
			$this->error("请输入您的姓名！");
		}
		if($birthday=='')
		{
			$this->error("请输入您的生日！");
		}
		
		
		$is_reg=$this->smscode->where("phone='".$member_tel."' and code = '".$pincode."' and types=1")->order("id desc")->find();
		if($is_reg)
		{
			$is_time=$this->smscode->where("phone='".$member_tel."' and createtime > ".(time()-600)." and types=1")->order("id desc")->find();
			if(!$is_time)
			{
				$this->error("您的验证码已经过期！");
			}
		}else
		{
			$this->error("验证码错误！");
		}
		

		$result=$this->member->where("member_tel='".$member_tel."' and shop_id=".$shop_id)->find();
		
		if($result)
		{	
			unset($data);
			$data=array(
				'id' => $result['id'],
				'openid' => $this->openid,
			);
			$rest=$this->member->save($data);
			if($rest!==false)
			{
				$_SESSION['user_id']=$res['id'];
				$this->success("绑定会员成功！",U("Index/index",array('i' => $i)));
			}else
			{
				$this->error("绑定会员失败！");
			}
			
		}else
		{
			$shop_info=$this->shop->where("id=".$shop_id)->find();
			if($shop_info['start_id']=='')
			{
				$this->error("请设置门头！");
			}
			unset($data);
			$data=array(
				'chain_id' => $shop_info['chain_id'],
				'shop_id' => $shop_info['id'],
				'openid' => $this->openid,
				'member_no' => get_member_sn($shop_info['start_id'],$shop_id),
				'member_name' => $member_name,
				'sex' => $sex,
				'member_tel' => $member_tel,
				'member_card' => $member_card,
				'birthday' => $birthday,
				'createtime' => time(),
			);
			$res_info=$this->member->add($data);
			if($res_info)
			{
				$_SESSION['user_id']=$res_info;
				$this->success("生成会员成功！",U("Index/index",array('i' => $i)));
			}else
			{
				$this->error("生成会员失败！");
			}
		}
	}
	//发送验证码
	public function send_code()
	{
		$phone=I('post.phone');
		if($phone=='')
		{
			$this->error("请输入您的手机号！");
		}
		$is_reg=$this->smscode->where("phone='".$phone."' and createtime > ".(time()-600)." and types=1")->order("id desc")->find();
		if($is_reg)
		{
			$this->error("请等会再发！");
		}
		$rand_code=rand(111111,999999);
		$message="【嗨丫丫】尊敬的用户：您的验证码是".$rand_code."，10分钟内有效，请勿泄露。";
		$result=SendSms($phone,$message,'2921');
		if($result > 0)
		{
			unset($data);
			$data=array(
				'phone' => $phone,
				'code' => $rand_code,
				'types' => 1,
				'createtime' => time(),
			);
			$this->smscode->add($data);
			$this->success("发送成功！");
		}else
		{
			$this->error("发送失败！");
		}
	}	
		
}