<?php
session_start();
ini_set('date.timezone','Asia/Shanghai');
define('IN_COOLFULL', true);
if (!defined('IN_COOLFULL'))
{
	die('Hacking attempt');
}
define('APP_ROOT', $_SERVER["DOCUMENT_ROOT"]);
include_once(APP_ROOT."/includes/init.php");
include_once (APP_ROOT . "/api/api.php");
$uid=get_user_info_by_id("id",$_POST['uid'],"crm");
$arr = array("error" => 0, "error_msg" => "");
$act=$_POST['act'];
if($act=="money_in")
{
	$money=$_POST['money'];
	$order_sn=get_order_sn();
	$sql="insert into cool_order_money_in(regtime,money,crm_id,order_sn) values('".time()."','".$money."','".$uid."','".$order_sn."')";
	if($db->query($sql))
	{
		require_once "../lib/WxPay.Api.php";
		require_once "WxPay.AppPay.php";
		$notify = new AppPay();
		/*首先生成prepayid*/
		$input = new WxPayUnifiedOrder();
		$input->SetBody("会员充值");//商品或支付单简要描述(必须填写)
		$input->SetAttach("money_in");//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据(不必填)
		$input->SetOut_trade_no($order_sn);//订单号(必须填写)
		$input->SetTotal_fee($money*100);//订单金额(必须填写
		//$input->SetTotal_fee(0.01*100);//订单金额(必须填写))
		//$input->SetTime_start(date("YmdHis"));//交易起始时间(不必填)
		//$input->SetTime_expire(date("YmdHis",time()+600));//交易结束时间10分钟之内不必填)
		//$input->SetGoods_tag("money_in");//商品标记(不必填)
		$input->SetNotify_url("http://manage.zbkuaisong.com/api/weixin/source/notify.php");//回调URL(必须填写)
		$input->SetTrade_type("APP");//交易类型(必须填写)
		//$input->SetProduct_id("123456789");//rade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
		$order=WxPayApi::unifiedOrder($input);//获得订单的基本信息，包括prepayid
		$arr=$notify->GetAppApiParameters($order);//生成提交给app的一些参数
		$arr['error']=0;
	}
	else
	{
		$arr['error']=1;
		$arr['error_msg']="服务器繁忙,请重试";
	}
}
else if($act=="order_pay")
{
	$order_id=$_POST['order_id'];
	$bonus_sn=$_POST['bonus_sn'];
	$total_fee=$_POST['money'];
	$sql="select status,order_sn from cool_order where crm_id='".$uid."' and id='".$order_id."'";
	$res=$db->query($sql);
	$row=$db->getarray($res);
	$order_sn=$row['order_sn'];
	$status=$row['status'];
	if($status==2754)
	{
		if($bonus_sn)
		{
			$sql_b="select a.money,a.discount from cool_bonus as a,cool_bonus_record as b where a.id=b.bonus_id and b.bonus_sn='".$bonus_sn."'";
			$res_b=$db->query($sql_b);
			$row_b=$db->getarray($res_b);
			if($row_b['money']>0)
			{
				$bonus_fee=$row_b['money'];
			}
			else if($row_b['discount']>0)
			{
				$bonus_fee=sprintf("%.2f",$total_fee*(1-$row_b['discount']));
			}
		}
		$must_fee=$total_fee-$bonus_fee;
		require_once "../lib/WxPay.Api.php";
		require_once "WxPay.AppPay.php";
		$notify = new AppPay();
		/*首先生成prepayid*/
		$input = new WxPayUnifiedOrder();
		$input->SetBody("订单支付");//商品或支付单简要描述(必须填写)
		$input->SetAttach("order|".$total_fee."|".$bonus_sn);//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据(不必填)
		$input->SetOut_trade_no($order_sn);//订单号(必须填写)
		$input->SetTotal_fee($must_fee*100);//订单金额(必须填写
		//$input->SetTotal_fee(0.01*100);//订单金额(必须填写))
		//$input->SetTime_start(date("YmdHis"));//交易起始时间(不必填)
		//$input->SetTime_expire(date("YmdHis",time()+600));//交易结束时间10分钟之内不必填)
		//$input->SetGoods_tag("money_in");//商品标记(不必填)
		$input->SetNotify_url("http://manage.zbkuaisong.com/api/weixin/source/notify.php");//回调URL(必须填写)
		$input->SetTrade_type("APP");//交易类型(必须填写)
		//$input->SetProduct_id("123456789");//rade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
		$order=WxPayApi::unifiedOrder($input);//获得订单的基本信息，包括prepayid
		$arr=$notify->GetAppApiParameters($order);//生成提交给app的一些参数
		$arr['error']=0;
	}
	else
	{
		$arr['error']=1;
		$arr['error_msg']="状态异常,无法支付";
	}
}

die(json_encode($arr));
?>