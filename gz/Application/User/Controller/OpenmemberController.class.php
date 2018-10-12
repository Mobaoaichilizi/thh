<?php
// +----------------------------------------------------------------------
// | 开通会员接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class OpenmemberController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->patient =D("Patientmember");
		$this->user =D("User");
		$this->setting =D("Setting");
		$this->information = D('Information');
		$this->vip = D('Vip');
		$this->scorelist = D('ScoreList');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//会员信息
	public function user_info()
	{
		$result=$this->patient->field("name,img_thumb")->where("user_id=".$this->uid)->find();
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
		if(empty($member_level)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择会员类型！";
			outJson($data);
		}
		
		if(empty($time_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择会员期限！";
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
			if($member_level != $member){
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
			$time = $base_time + 365*24*3600;
			// $time = strtotime("+1 year",$base_time);
		}else if($time_type == 2){
			$is_continuous = 0;
			$time = $base_time + 120*24*3600;
			// $time = strtotime("+120 days",$base_time); 
		}else if($time_type == 3){
			$is_continuous = 0;
			$time = $base_time + 90*24*3600;
			// $time = strtotime("+90 days",$base_time); 
		}else if($time_type == 4){
			$is_continuous = 1;
			$time = $base_time + 30*24*3600;
			// $time = strtotime("+30 days",$base_time);
		}
		
		$balance = $this->user->where("id=".$id)->getfield("balance");
		if($os == 'android'){
			if($balance < $money)
			{
				unset($data);
				$data['code'] = 10003;
				$data['message'] = "钱包里余额不足，请充值！";
				outJson($data);
			
			}
			$balance = $balance - $money;
		}
		$res = array(
			'member_level' => $member_level,
			'due_time' => $time,
			'time_type'    => $time_type,
			'is_continuous' => $is_continuous,
			'balance' => $balance,
		);
		$result = $this->user->where('id='.$id)->save($res);
		financial_log($id,$money,2,$balance,'开通会员消费',5);
		$is_first = $this->scorelist->where("user_id=".$id." and setting_id=147")->count();
		if($is_first == 0){
			if($member_level == 1){
				if($time_type == 1){
					setpoints($id,2,20,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,20,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 2){
					setpoints($id,2,80,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,80,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 3){
					setpoints($id,2,50,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,50,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 4){
					setpoints($id,2,30,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,30,3,$score,'开通会员奖励',8,2);
				}
			}else if($member_level == 2){
				if($time_type == 1){
					setpoints($id,2,30,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,30,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 2){
					setpoints($id,2,100,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,100,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 3){
					setpoints($id,2,80,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,80,3,$score,'开通会员奖励',8,2);
				}else if($time_type == 4){
					setpoints($id,2,50,'分','147',0);
					$score = $this->user->where("id=".$id)->getField('score');
					financial_log($id,50,3,$score,'开通会员奖励',8,2);
				}
			}
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
	if($resu){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "信息加载成功！";
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "数据加载失败！";
		outJson($data);
	}
}	

}
?>