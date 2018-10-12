<?php
session_start();
ini_set('date.timezone','Asia/Shanghai');


define('IN_COOLFULL', true);

if (!defined('IN_COOLFULL')){

	die('Hacking attempt');

}

define('APP_ROOT', $_SERVER["DOCUMENT_ROOT"]);

include_once(APP_ROOT."/includes/init.php");

require_once "../lib/WxPay.Api.php";

require_once "WxPay.AppPay.php";

		$notify = new AppPay();

		/*首先生成prepayid*/

		$input = new WxPayUnifiedOrder();

		$input->SetBody("快递员充值");//商品或支付单简要描述(必须填写)

		//$input->SetAttach("test2");//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据(不必填)

		//$input->SetDetail("Ipad mini  16G  白色,黑色");//商品名称明细列表(不必填)

		$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));//订单号(必须填写)

		$input->SetTotal_fee($_POST['money']*100);//订单金额(必须填写)

		//$input->SetTime_start(date("YmdHis"));//交易起始时间(不必填)

		//$input->SetTime_expire(date("YmdHis",time()+600));//交易结束时间10分钟之内不必填)

		$input->SetGoods_tag("test");//商品标记(不必填)

		$input->SetNotify_url("http://tc.weixinxa.com/app/payment/weixin/source/notify.php");//回调URL(必须填写)

		$input->SetTrade_type("APP");//交易类型(必须填写)

		//$input->SetProduct_id("123456789");//rade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。

		$order = WxPayApi::unifiedOrder($input);//获得订单的基本信息，包括prepayid
		if($_POST['tel']){
			$mailmanID=get_vclassname("id","ex_mailmaninfo","TEL",$_POST['tel']);
			$sql_money="select * from ex_mailmanmoney where mailmanID='".$mailmanID."'";
			$row_money=$db->getarray($db->query($sql_money));

			//总金额
			$money=$row_money['money']+$_POST['money'];
			$ableMoney=$row_money['ableMoney']+$_POST['money'];
			$mailman_sql="update ex_mailmanmoney set money='".$money."',ableMoney='".$ableMoney."' where mailmanID='".$mailmanID."'";
			//添加账户详情
			$moneydetail_sql="INSERT INTO ex_mailmanmoneydetails(mailmanID,orderCode,money,shouZhi,reason,regtime) VALUES('".$mailmanID."','".WxPayConfig::MCHID.date("YmdHis")."','".$_POST['money']."','收入','充值','".time()."')";
			if($db->query($mailman_sql)&&$db->query($moneydetail_sql)){
				$appApiParameters = $notify->GetAppApiParameters($order);//生成提交给app的一些参数
			}else{
				$appApiParameters['status']=1;
				$appApiParameters['msg']="充值失败";
			}

		}
		//$appApiParameters = $notify->GetAppApiParameters($order);//生成提交给app的一些参数
die($appApiParameters);

?>