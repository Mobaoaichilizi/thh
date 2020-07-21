<?php
session_start();
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
define('IN_COOLFULL', true);
if (!defined('IN_COOLFULL'))
{
	die('Hacking attempt');
}
define('APP_ROOT', $_SERVER["DOCUMENT_ROOT"]);
include_once(APP_ROOT."/includes/init.php");
include_once(APP_ROOT."/api/sdk_jpush.php");
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
//file_put_contents("b",json_encode($_POST));
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if($verify_result)
{
	//请在这里加上商户的业务逻辑程序代
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	//商户订单号
	$out_trade_no=$_POST['out_trade_no'];
	//支付宝交易号
	$trade_no=$_POST['trade_no'];
	//交易状态
	$trade_status=$_POST['trade_status'];
	//购买人的支付宝帐号
	$buyer_email=$_POST['buyer_email'];
	//支付时间
	$gmt_create=$_POST['gmt_create'];
	//实际支付费用
	$act_fee=$_POST['total_fee'];
	$body=$_POST['body'];
    if($_POST['trade_status']=='TRADE_FINISHED'||$_POST['trade_status']=='TRADE_SUCCESS')
	{
		if($body=="order")
		{
			$sql="select * from cool_order where order_sn='".$out_trade_no."'";
			$res=mysql_query($sql);
			$row=mysql_fetch_array($res);
			$status=$row['status'];
			if($status=="待付款")
			{
				$sql="update cool_order set status='待发货',pay_type='支付宝',act_fee='".$act_fee."',pay_time='".date("Y-m-d H:i:s")."',order_sn_out='".$trade_no."' where id='".$row['id']."'";
				if(mysql_query($sql))
				{
					echo "success";
					$dep_id=$row['dep_id'];
					$sql_u="select deviceid,os from cool_user where deviceid!='' and role_id=5 and dep_id='".$dep_id."' order by uid desc limit 0,1";
					$res_u=mysql_query($sql_u);
					$row_u=mysql_fetch_array($res_u);
					if($row_u['deviceid'])
					{
						$device_arr=array();
						$device_arr[]=$row_u['deviceid'];
						$description="亲,您有新的订单,订单号为:".$row['order_sn'];
						$extra=array("push_type"=>"order_action","order_id"=>$row['id']);
						send_app_msg('dep',$description,$device_arr,$row_u['os'],$extra);
					}
				}
				else
				{
					echo "fail";
				}
			}
			else
			{
				echo "success";
			}
		}
		else if($body=="order_num_zx")
		{
			$sql="update cool_graphic_consulting set order_status='4030',pay_type='支付宝',act_fee=good_fee,pay_time='".date("Y-m-d H:i:s")."',pay_order_sn='".$trade_no."',is_pay='是' where order_sn='".$out_trade_no."'";
			file_put_contents("filename.text", $sql);
			if(mysql_query($sql))
			{
				echo "success";
				$sql="select * from cool_graphic_consulting where order_sn='".$out_trade_no."'";
				$res=mysql_query($sql);
				while($row=mysql_fetch_array($res))
				{

					$sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
					$res_u=mysql_query($sql_u);
					$row_u=mysql_fetch_array($res_u);
					if($row_u['deviceid'])
					{
						$device_arr=array();
						$device_arr[]=$row_u['deviceid'];
						$description="亲,您有新的订单,订单号为:".$row['order_sn'];
						$extra=array("push_type"=>"order_action","order_id"=>$row['id']);
						send_app_msg('doctor',$description,$device_arr,$row_u['os'],$extra);
					}
				}
			}
			else
			{
				echo "fail";
			}
		}
    }
	else
	{
		echo "fail";
	}
}
else
{
    echo "fail";
}
?>