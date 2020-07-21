<?php

// +----------------------------------------------------------------------
// | 充值接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class RechargeController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->recharge = D('Recharge');
		$this->user = D('User');
		$this->information = D('Information');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	
public	function do_recharge()
	{
		$id = $this->uid;
		$pay_money = I('post.pay_money');
		$pay_way = I('post.pay_way');
		//$ordernumber = I('post.ordernumber');
		// if(empty($ordernumber)){
		// 	unset($data);
		// 	$data['code'] = 0;
		// 	$data['message'] = "订单号不存在！";
		// 	outJson($data);
		// }
		
		if(empty($pay_money))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入充值金额！";
			outJson($data);
		}
		if(empty($pay_way))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择支付方式！";
			outJson($data);
		}
		if($pay_way=='2')
		{
			$order_sn=date("YmdHis").rand(10000,99999);
			$arr['order_sn']=$order_sn;
			$arr['subject']="用户充值";
			$arr['money']=$pay_money;
			$arr['body']=$id;
		}else if($pay_way=='1')
		{
			$order_sn=date("YmdHis").rand(10000,99999).'_'.$id;
			include_once(VENDOR_PATH . "Pay/weixin/lib/WxPay.Api.php");
			include_once(VENDOR_PATH . "Pay/weixin/source/WxPay.AppPay.php");
			$notify_url = $this->host_url."Doctor/Payreturn/return_pay";
			$notify = new \AppPay();
			/*首先生成prepayid*/
			$input = new \WxPayUnifiedOrder();
			$input->SetBody("会员充值");//商品或支付单简要描述(必须填写)
			$input->SetAttach("order");//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据(不必填)
			$input->SetOut_trade_no($order_sn);//订单号(必须填写)
			$input->SetTotal_fee($pay_money * 100);//订单金额(必须填写
			//$input->SetTotal_fee(0.01*100);//订单金额(必须填写))
			//$input->SetTime_start(date("YmdHis"));//交易起始时间(不必填)
			//$input->SetTime_expire(date("YmdHis",time()+600));//交易结束时间10分钟之内不必填)
			//$input->SetGoods_tag("money_in");//商品标记(不必填)
			$input->SetNotify_url($notify_url);//回调URL(必须填写)
			$input->SetTrade_type("APP");//交易类型(必须填写)
			//$input->SetProduct_id("123456789");//rade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
			$order = \WxPayApi::unifiedOrder($input);//获得订单的基本信息，包括prepayid
			$arr = $notify->GetAppApiParameters($order);//生成提交给app的一些参数
			$arr['order_sn']=$order_sn;
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "支付方式错误！";
			outJson($data);
		}
		unset($data);
		$data['code']=1;
		$data['message']="支付成功！";
		$data['info']=$arr;
		outJson($data);
		
	}
	
public function account_explain()
	{
		$id = $this->uid;
		$result = $this->information->where("setting_id=81")->getfield("content");
		if($result){
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code']=0;
			$data['message']="信息加载失败！";
			outJson($data);
		}
	}
}
?>