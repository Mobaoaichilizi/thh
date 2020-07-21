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
$money=$_POST['money'];
if($act=="money_in")
{
	$order_sn=get_order_sn();
	$sql="insert into cool_order_money_in(regtime,money,crm_id,order_sn) values('".time()."','".$money."','".$uid."','".$order_sn."')";
	if($db->query($sql))
	{
		$arr['subject']="会员充值".$money."元";
		$arr['body']="money_in";
		$arr['money']=$money;
		$arr['order_sn']=$order_sn;
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
		
		$arr['subject']="订单支付";
		$arr['body']="order|".$total_fee."|".$bonus_sn;
		$arr['money']=$must_fee;
		//$arr['money']=0.01;
		$arr['order_sn']=$order_sn;
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