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
require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
require_once 'log.php';
include_once(APP_ROOT."/api/sdk_jpush.php");
//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify
{
    public function __construct()
	{

    }
    //查询订单
	public function Queryorder($transaction_id)
	{
        $input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)&& array_key_exists("result_code", $result)&& $result["return_code"] == "SUCCESS"&& $result["result_code"] == "SUCCESS")
		{
			$out_trade_no=$result['out_trade_no'];//私有订单号，你就用这个订单号来进行你自己订单的各种更新吧
			$mch_id=$result['mch_id'];//商户号
			$transaction_id=$result['transaction_id'];//微信内部的订单流水号
			$attach=$result['attach'];//附件数据
			$openid=$result['openid'];//微信加密的用户身份识别,app支付的话其实意义不大了
			$act_fee=$result['total_fee']/100;//支付金额，出来的金额要除以100
            if($attach=="order")
			{
                $sql="select * from cool_order where order_num='".$out_trade_no."'";
                $res = mysql_query($sql);
                file_put_contents('1212.txt',$sql);
                $row = mysql_fetch_array($res);
                file_put_contents('32333333333.txt',12346);

//
//                file_put_contents('aaaaaaaaaaaaaaaaaa.txt',$res);
//
//                file_put_conetns('222222222222.txt',$row);
                $status=$row['status'];
                file_put_conetns('33333.txt',$status);
//				if($status=="待付款")
//				{
//                    $time_s = date("Y-m-d H:i:s");
//                    file_put_conetns('xxxxxx.txt',$time_s);
//                    $sql_order ="select * from cool_order where order_num='".$row['id']."'";
////                    $sql="update cool_order set status='待发货',pay_type='微信' where id='".$row['id']."'";
////                    file_put_conetns('ccccccccc.txt',333323223222222);
////                   // $sql="update cool_order set status='待发货',pay_type='微信',act_fee='".$act_fee."',pay_time='".$time_s."',main_order_sn='".$transaction_id."' where id='".$row['id']."'";
////                    file_put_conetns('bbbbb.txt',$sql);
////					if(mysql_query($sql))
////					{
////						$dep_id=$row['dep_id'];
////						$sql_u="select deviceid,os from cool_user where deviceid!='' and role_id=5 and dep_id='".$dep_id."' order by uid desc limit 0,1";
////						$res_u=mysql_query($sql_u);
////						$row_u=mysql_fetch_array($res_u);
////						if($row_u['deviceid'])
////						{
////							$device_arr=array();
////							$device_arr[]=$row_u['deviceid'];
////							$description="亲,您有新的订单,订单号为:".$row['order_sn'];
////							$extra=array("push_type"=>"order_action","order_id"=>$row['id']);
////							send_app_msg($description,$device_arr,$row_u['os'],$extra);
////						}
////						return true;
////					}
////					else
////					{
////						return false;
////					}
//				}
//				else
//				{
//					return false;
//				}
			}
            else if($attach=="order_num_zx")
            {

                $sql="update cool_graphic_consulting set order_status='4030',pay_type='微信',pay_time='".date("Y-m-d H:i:s")."',pay_order_sn='".$transaction_id."',is_pay='是'  where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sql="select * from cool_graphic_consulting where order_sn='".$out_trade_no."' ";
                    $res=mysql_query($sql);
                    while($row=mysql_fetch_array($res))
                    {
                        $sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $crm_truename =get_vclassname('truename','cool_crm','id',$row['crm_id']);
                            $msg_title ="您有新的图文咨询";
                            $description ="您有新的图文咨询,咨询人：".$crm_truename;
                            $content ="您有新的图文咨询,咨询人：".$crm_truename;

                            $msg_sql ="insert into cool_msg(doctor_id,title,description,content,graphic_consulting_id,regtime,cat_id)values('".$row['doctor_id']."','".$msg_title."','".$description."','".$content."','".$row['id']."','".time()."',4078)";
                            mysql_query($msg_sql);

                            $device_arr=array();
                            $device_arr[]=$row_u['deviceid'];
                            $description="亲,您有新的订单,订单号为:".$row['order_sn'];
                            $extra=array("push_type"=>"tuwenzixun","order_id"=>$row['id'],"user"=>"doctor");
                            send_app_msg($description,$device_arr,$row_u['os'],$extra);
                        }
                    }
                    return true;
                }
            }
            else if($attach=="order_num_tzx")
            {
                $sql="update cool_tel_consultation set order_status='4041',pay_status='微信',pay_time='".date("Y-m-d H:i:s",time())."',pay_order_sn='".$transaction_id."',is_pay='是' where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sql="select * from cool_tel_consultation where order_sn='".$out_trade_no."'";
                    $res=mysql_query($sql);
                    while($row=mysql_fetch_array($res))
                    {
                        $sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $crm_truename =get_vclassname('truename','cool_crm','id',$row['crm_id']);
                            $msg_title ="您有新的电话咨询";
                            $description ="您有新的电话咨询,咨询人：".$crm_truename;
                            $content ="您有新的电话咨询,咨询人：".$crm_truename;

                            $msg_sql ="insert into cool_msg(doctor_id,title,description,content,tel_consultation_id,regtime,cat_id)values('".$row['doctor_id']."','".$msg_title."','".$description."','".$content."','".$row['id']."','".time()."',4077)";
                            mysql_query($msg_sql);

                            $device_arr=array();
                            $device_arr[]=$row_u['deviceid'];
                            $description="亲,您有新的订单,订单号为:".$row_u['order_sn'];
                            $extra=array("push_type"=>"dianhuazixun","order_id"=>$row_u['id'],"doctor_id"=>$row['doctor_id'],"user"=>"doctor");
                           send_app_msg($description,$device_arr,$row_u['os'],$extra);
                        }
                    }
                    return true;
                }
            }
            else if($attach=="order_num_yzx_g")
            {
                $sql ="update cool_doctor_visits set order_type='4045',gua_is_pay='是',gua_pay_status='微信',gua_pay_order_sn='".$transaction_id."',gua_pay_time='".date("Y-m-d H:i:s",time())."' where order_sn='".$out_trade_no."'";
                //$sql="update cool_tel_consultation set order_status='4041',pay_status='支付宝',pay_time='".date("Y-m-d H:i:s")."',pay_order_sn='".$trade_no."',is_pay='是' where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sql="select * from cool_doctor_visits where order_sn='".$out_trade_no."'";
                    $res=mysql_query($sql);
                    while($row=mysql_fetch_array($res))
                    {
                        $crm_truename =get_vclassname('truename','cool_crm','id',$row['crm_id']);
                        $msg_title ="您有新的预约就诊";
                        $description ="您有新的预约就诊,咨询人：".$crm_truename;
                        $content ="您有新的预约就诊,咨询人：".$crm_truename;
                        $msg_sql ="insert into cool_msg(doctor_id,title,description,content,doctor_visits_id,regtime,cat_id)values('".$row['doctor_id']."','".$msg_title."','".$description."','".$content."','".$row['id']."','".time()."',4076)";
                        mysql_query($msg_sql);

                        $sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $device_arr=array();
                            $device_arr[]=$row_u['deviceid'];
                            $description="亲,您有新的订单,订单号为:".$row['order_sn'];
                            $extra=array("push_type"=>"yuyuejiuzhen","order_id"=>$row['id'],"user"=>"doctor");
                            send_app_msg($description,$device_arr,$row_u['os'],$extra);
                        }
                    }

                    return true;
                }
            }
            else if($attach=="order_num_yzx")
            {
                $order_sn = explode('_',$out_trade_no);
                $sql ="update cool_doctor_visits set order_type='4050', is_pay='是',pay_status='微信',pay_order_sn='".$transaction_id."',pay_time='".date("Y-m-d H:i:s",time())."' where order_sn='".$order_sn[0]."'";
                //$sql="update cool_tel_consultation set order_status='4041',pay_status='支付宝',pay_time='".date("Y-m-d H:i:s")."',pay_order_sn='".$trade_no."',is_pay='是' where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sql="select * from cool_doctor_visits where order_sn='".$order_sn[0]."'";
                    $res=mysql_query($sql);
                    while($row=mysql_fetch_array($res))
                    {
                        $crm_truename =get_vclassname('truename','cool_crm','id',$row['crm_id']);
                        $msg_title ="订单有更新";
                        $description ="订单有更新,咨询人：".$crm_truename;
                        $content ="订单有更新,咨询人：".$crm_truename;

                        $msg_sql ="insert into cool_msg(doctor_id,title,description,content,doctor_visits_id,regtime,cat_id)values('".$row['doctor_id']."','".$msg_title."','".$description."','".$content."','".$row['id']."','".time()."',4076)";
                        mysql_query($msg_sql);

                        $sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $device_arr=array();
                            $device_arr[]=$row_u['deviceid'];
                            $description="亲,订单有更新,订单号为:".$row['order_sn'];
                            $extra=array("push_type"=>"yuyuejiuzhen","order_id"=>$row['id']);
                             send_app_msg($description,$device_arr,$row_u['os'],$extra);
                        }
                    }
                    return true;
                }
            }
            else if($attach=="a_reward")
            {
                $sql ="update cool_a_reward set pay_status='微信',is_pay='是',pay_time='".date("Y-m-d H:i:s",time())."',pay_order_sn='".$transaction_id."' where  order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sqls="select * from cool_a_reward where order_sn='".$out_trade_no."'";
                    $res=mysql_query($sqls);
                    while($row=mysql_fetch_array($res))
                    {

                        $sql_u="select deviceid,os from cool_doctor where deviceid!='' and  id='".$row['doctor_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $device_arr=array();
                            $device_arr[]=$row_u['deviceid'];
                            $description="亲,有用户打赏你￥".$act_fee."元";
                            $extra=array("push_type"=>"a_reward","order_id"=>$row['id'],"user"=>'doctor');
                            send_app_msg($description,$device_arr,$row_u['os'],$extra);
                        }
                        file_put_contents('11111111111.txt',11111111111);
                        $description="亲,有用户打赏你￥".$act_fee."元";
                        $cat_id = "打赏";
                        $sql_money_rocode ="insert into cool_money_record(doctor_id,money,description,a_reward_id,cat_id)values('".$row['doctor_id']."','".$act_fee."','".$description."','".$row['id']."','".$cat_id."')";

                        mysql_query($sql_money_rocode);
                    }
                    return true;
                }
            }
            else if($attach=="order_zhuayao")
            {

                $sql="update cool_pharmacy set pay_status='微信',is_pay='是',total_price='".$act_fee."',pay_time='".date('Y-m-d H:i:s',time())."',order_type='4059',pay_order_sn='".$transaction_id."',regtime='".time()."' where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
//                    $sql="select * from cool_pharmacy where order_sn='".$out_trade_no."'";
//                    $res=mysql_query($sql);
//                    while($row=mysql_fetch_array($res))
//                    {
//                        $sql_u="select deviceid,os from cool_crm where deviceid!='' and  id='".$row['crm_id']."' limit 0,1";
//                        $res_u=mysql_query($sql_u);
//                        $row_u=mysql_fetch_array($res_u);
//                        if($row_u['deviceid'])
//                        {
//                            $device_arr=array();
//                            $device_arr[]=$row_u['deviceid'];
//                            $description="亲,您的订单有更新,订单号为:".$row['order_sn'];
//                            $extra=array("push_type"=>"order_action","order_id"=>$row['id']);
//                            // send_app_msg('doctor',$description,$device_arr,$row_u['os'],$extra);
//                        }
//                    }
                    return true;
                }
            }
            else if($attach=="order_num_cz")
            {
                $sql ="update cool_recharge set pay_status='微信',pay_order_sn='".$transaction_id."',pay_time='".date("Y-m-d H:i:s",time())."',is_pay='是',total_price='".$act_fee."' where order_sn='".$out_trade_no."'";
                if(mysql_query($sql))
                {
                    $sql="select * from cool_recharge where order_sn='".$out_trade_no."'";
                    $res=mysql_query($sql);
                    while($row=mysql_fetch_array($res))
                    {

                        $sql_u="select deviceid,os from cool_crm where deviceid!='' and  id='".$row['crm_id']."' limit 0,1";
                        $res_u=mysql_query($sql_u);
                        $row_u=mysql_fetch_array($res_u);
                        if($row_u['deviceid'])
                        {
                            $sql_purse="insert into cool_money_record(regtime,money,crm_id,description) values('".time()."','".$act_fee."','".$row['crm_id']."','充值')";
                            mysql_query($sql_purse);
//                            $device_arr=array();
//                            $device_arr[]=$row_u['deviceid'];
//                            do_sy_money($row['crm_id']);
//                            $description="亲,您充值了:".$act_fee."元";
//                             $extra=array("push_type"=>"order_action","order_id"=>$row['id']);
                            // send_app_msg('doctor',$description,$device_arr,$row_u['os'],$extra);
                        }
                    }
                    return true;
                }
            }
            else if($attach=="all_order")
			{
				$sql="update cool_order set status='待发货',pay_type='微信',act_fee=good_fee,pay_time='".date("Y-m-d H:i:s")."',order_sn_out='".$transaction_id."' where main_order_sn='".$out_trade_no."'";
				if(mysql_query($sql))
				{
					$sql="select * from cool_order where main_order_sn='".$out_trade_no."' group by dep_id";
					$res=mysql_query($sql);
					while($row=mysql_fetch_array($res))
					{
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
					return true;
				}
			}
			/*以下两行用做调试，会自动生成in_test.txt文件而且后期内容会自动追加到这个文件*/
			//return true;//这个很重要，微信的异步请求，当你执行完了你的内部处理以后给他返回true，微信就认为你的内部处理完成了，就不会再次请求你了，否则他会一直请求你这个文件，知道超时。
		}
		return false;
	}
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		if(!array_key_exists("transaction_id", $data))
		{
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"]))
		{
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}
Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
?>