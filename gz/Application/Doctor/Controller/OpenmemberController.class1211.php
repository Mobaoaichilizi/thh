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
				$result['remaining_day']=($res['due_time']-time())/86400;
			}
		}else if($result['member_level']==2)
		{
			if(($res['due_time']-time()) < 0)
			{
				$result['remaining_day']=0;
			}else
			{
				$result['remaining_day']=($res['due_time']-time())/86400;
			}
		}
		$rest=$this->setting->field("id,title")->where("parentid=139 and status=1")->order('sort asc')->select();
		foreach($rest as &$row)
		{
			$row['time_type']=$this->vip->field("id,type_id,money,title")->where("setting_id=".$row['id'])->order('createtime asc')->select();
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
		$pay_way = I('post.pay_way');
		$order_sn = I('post.order_sn');
		
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
		if(empty($pay_way)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择支付方式！";
			outJson($data);
		}
		
		$due_time = $this->user->where("id=".$id)->getfield("due_time");
		if($due_time <= time() || $due_time==0){
			$base_time = time();
		}else{
			$base_time = $due_time;
		}
		if($time_type == 1){
			$is_continuous = 1;
			$time = strtotime("+1 month",$base_time);
		}else if($time_type == 2){
			$is_continuous = 0;
			$time = strtotime("+1 year",$base_time); 
		}else if($time_type == 3){
			$is_continuous = 0;
			$time = strtotime("+3 month",$base_time); 
		}else if($time_type == 4){
			$is_continuous = 0;
			$time = strtotime("+1 month",$base_time);
			}
		
		if($pay_way=='alipay')
		{
			
		}else if($pay_way=='weixin')
		{
			include_once(VENDOR_PATH . "Pay/weixin/lib/WxPay.Api.php");
			include_once(VENDOR_PATH . "Pay/weixin/source/WxPay.AppPay.php");
			$notify_url = "http://jlt.zv100.net/api/weixin/source/notify.php";
			$notify = new \AppPay();
			/*首先生成prepayid*/
			$input = new \WxPayUnifiedOrder();
			$input->SetBody("会员充值");//商品或支付单简要描述(必须填写)
			$input->SetAttach("order");//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据(不必填)
			$input->SetOut_trade_no($order_sn);//订单号(必须填写)
			$input->SetTotal_fee($money * 100);//订单金额(必须填写
			//$input->SetTotal_fee(0.01*100);//订单金额(必须填写))
			//$input->SetTime_start(date("YmdHis"));//交易起始时间(不必填)
			//$input->SetTime_expire(date("YmdHis",time()+600));//交易结束时间10分钟之内不必填)
			//$input->SetGoods_tag("money_in");//商品标记(不必填)
			$input->SetNotify_url($notify_url);//回调URL(必须填写)
			$input->SetTrade_type("APP");//交易类型(必须填写)
			//$input->SetProduct_id("123456789");//rade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
			$order = \WxPayApi::unifiedOrder($input);//获得订单的基本信息，包括prepayid
			$arr = $notify->GetAppApiParameters($order);//生成提交给app的一些参数
		}else if($pay_way=='balance_pay')
		{
			$balance = $this->user->where("id=".$id)->getfield("balance");
			if($balance < $money)
			{
				unset($data);
				$data['code'] = 10003;
				$data['message'] = "钱包里余额不足，请充值！";
				outJson($data);
			}
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "支付方式错误！";
			outJson($data);
		}
		$res = array(
			'member_level' => $member_level,
			'due_time' => $time,
			'pay_way'    => $pay_way,
			'time_type'    => $time_type,
			'is_continuous' => $is_continuous,
		);
		$result = $this->user->where('id='.$id)->save($res);
		
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
	
	// if($is_auto == 1){
	// 	$due_time = $this->user->where("id=".$uid)->getfield("due_time");
	// 	$time_type = $this->user->where("id=".$uid)->getfield("time_type");
	// 	if($due_time == time()){
	// 		if($time_type == 1 || $time_type == 2){
	// 			$time = strtotime("+1 year");
	// 		}else if($time_type == 3){
	// 			$time = strtotime("+3 month");
	// 		}else if($time_type == 4){
	// 			$time = strtotime("+1 month");
	// 		}
	// 		$res = array(
	// 			"id"       => $uid,
	// 			"due_time" => $time,
	// 		);
	// 		$result = $this->user->save($res);
	// 		if($result){
	// 			unset($data);
	// 			$data['code'] = 1;
	// 			$data['message'] = "自动续费成功！";
	// 			$data['info'] = $results;
	// 			outJson($data);
	// 		}else{
	// 			unset($data);
	// 			$data['code'] = 0;
	// 			$data['message'] = "自动续费失败！";
	// 			outJson($data);
	// 		}
	// 	}
	// }
	
	if($resu){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "信息加载成功！";
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "暂无信息！";
		outJson($data);
	}
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