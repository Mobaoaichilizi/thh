<?php
// +----------------------------------------------------------------------
// | 开通会员接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class OpenmemberController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->doctor =D("Member");
		$this->setting =D("Setting");
		$this->information = D('Information');
		$this->vip = D('Vip');
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//会员信息
	public function doctor_info()
	{
		$result=$this->doctor->field("name,img_thumb")->where("user_id=".$this->uid)->find();
		$res=$this->user->where("id=".$this->uid)->find();
		$result['phone']=$res['username'];
		$result['member_level']=$res['member_level'];
		$result['is_auto']=$res['is_auto'];
		// if($res['os'] == 'android'){
		// 	$os = 1;
		// }else if($res['os'] == 'ios'){
		// 	$os = 2;
		// }
		if($result['member_level']==0)
		{
			$result['remaining_day']=0;
		}else if($result['member_level']==1)
		{
			if(($res['due_time']-time()) < 0)
			{
				$result['remaining_day']=0;
			}else
			{
				$result['remaining_day']=ceil(($res['due_time']-time())/86400);
			}
		}else if($result['member_level']==2)
		{
			if(($res['due_time']-time()) < 0)
			{
				$result['remaining_day']=0;
			}else
			{
				$result['remaining_day']=ceil(($res['due_time']-time())/86400);
			}
		}
		$rest=$this->setting->field("id,title")->where("parentid=139 and status=1")->order('sort asc')->select();
		foreach($rest as &$row)
		{
			$row['time_type']=$this->vip->field("id,type_id,money,title")->where("setting_id=".$row['id']." and os=1")->order('type_id desc')->select();
		}
		$result['vip_type']=$rest;
		$result['order_sn']=date("YmdHis").rand(10000,99999);
		unset($data);
		$data['code'] = 1;
		$data['message'] = "会员信息";
		$data['info'] = $result;
		outJson($data);
	}
	//开通会员
	public	function do_member(){
		$id = $this->uid;
		$member_level = I('post.member_level');
		$time_type = I('post.time_type');
		$money = I('post.money');
		$order_sn = I('post.order_sn');
		$os = $this->user->where("id=".$id)->getField('os');
		if($os == 'ios'){
			//苹果内购的验证收据
	        $receipt_data = I('post.apple_receipt'); 
	        // 验证支付状态
	        $pay_status=validate_apple_pay($receipt_data);
	    }
		if(empty($member_level)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择会员类型！";
			outJson($data);
		}
		if(empty($order_sn)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "订单号不存在！";
			outJson($data);
		}
		if(empty($time_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择会员期限！";
			outJson($data);
		}
		if(empty($money)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "钱数不能为空！";
			outJson($data);
		}
		$member  = $this->user->where("id=".$id)->getField('member_level');

		$due_time = $this->user->where("id=".$id)->getfield("due_time");
		if($member_level == 140){
			$member_level = 1;
		}else if($member_level == 141){
			$member_level = 2;
		}
		if($member > 0){
			if($member_level !== $member){
				$due_time = 0;
			}
		}
		
		if($due_time <= time() || $due_time==0){
			$base_time = time();
		}else{
			$base_time = $due_time;
		}
		if($time_type == 1){
			$is_continuous = 1;
			$time = strtotime("+1 year",$base_time);
		}else if($time_type == 2){
			$is_continuous = 0;
			$time = strtotime("+120 days",$base_time); 
		}else if($time_type == 3){
			$is_continuous = 0;
			$time = strtotime("+90 days",$base_time); 
		}else if($time_type == 4){
			$is_continuous = 0;
			$time = strtotime("+30 days",$base_time);
		}
		
		$balance = $this->user->where("id=".$id)->getfield("balance");
		if($os != 'ios'){
			if($balance < $money)
			{
				unset($data);
				$data['code'] = 10003;
				$data['message'] = "钱包里余额不足，请充值！";
				outJson($data);
			
			}
			$balance = $balance - $money;
		}
		$balance = $balance - $money;
		
		$res = array(
			'member_level' => $member_level,
			'due_time' => $time,
			'time_type'    => $time_type,
			'is_continuous' => $is_continuous,
			'balance' => $balance,
		);
		if($pay_status == '' || $pay_status['status']){
			$result = $this->user->where('id='.$id)->save($res);
			financial_log($id,$money,2,$balance,'开通会员消费',5);
		}
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "开通成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "开通失败！";
			outJson($data);
		}

	}
public function is_auto(){
	$uid = $this->uid;
	$is_auto = I('post.is_auto');
	$ress = array(
		"id"       => $uid,
		"is_auto" => $is_auto
	);
	$resu = $this->user->save($ress);
	

		unset($data);
		$data['code'] = 1;
		$data['message'] = "信息加载成功！";
		$data['info'] = $is_auto;
		outJson($data);
	
}
public function member_info()
	{
		$uid = $this->uid;
		$results = $this->doctor->join("lgq_user on lgq_user.id = lgq_member.user_id")->field("user_id,name,member_level,img_thumb,username,due_time,is_auto")->where("user_id=".$uid)->find();
		$results['privilege_img'] = $this->information->where("setting_id=85")->getfield("img_thumb");
		$results['privilege_content'] = $this->information->where("setting_id=85")->getfield("content");
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $results;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无信息！";
			outJson($data);
		}
	}
}
?>