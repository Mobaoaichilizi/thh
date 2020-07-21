<?php
// +----------------------------------------------------------------------
// | 充值接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class RechargeController extends DoctorbaseController {
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
			// $notify_url = "http://gz.51dade.com/Doctor/Payreturn/return_pay";
		
			$notify_url = "http:/api.sxgzyl.com/Doctor/Payreturn/return_pay";
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
		$result = html_entity_decode($result);
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
	public function return_pay()
	{
		
		$id=1;
	    require_once VENDOR_PATH.'Pay/weixin/notify.php';
        $notify = new \PayNotifyCallBack();
        $notify->Handle(true);
        $xml=$GLOBALS['HTTP_RAW_POST_DATA'];
        $str=json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
		fwrite($myfile, $str);
		fclose($myfile);
		$ordernumber=$str['out_trade_no'];
		$pay_money=$str['total_fee'] / 100;
		$res=array(
			'user_id' => $id,
			'ordernumber' => $ordernumber,
			'pay_money' => $pay_money,
			'pay_way' => 1,
			'create_time' => time(),
		);
		$result=$this->recharge->add($res);
		$balance = $this->user->where('id='.$id)->getfield('balance');
		$bala = $balance+$pay_money;
		$ress =array(
			'id' => $id,
			'balance' => $bala,
		);
		if($result)
		{
			financial_log($id,$pay_money,1,$bala,'充值',6);
			$results = $this->user->save($ress);
			unset($data);
			$data['code']=1;
			$data['message']="充值成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="充值失败！";
			outJson($data);
		}
	}
	
	 /*支付宝异步通知*/
    public function return_zfbpay(){
        require_once VENDOR_PATH.'Pay/Alipay/alipay.config.php';
        import('Vendor.pay.alipay.lib.alipay_notify');
        
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
        
            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            //商户订单号        
            $ordernumber = $_POST['out_trade_no'];
        
            //支付宝交易号        
            $trade_no = $_POST['trade_no'];        
            //交易状态
            $trade_status = $_POST['trade_status'];            
            //交易金额
            $pay_money = $_POST['total_fee'];  

			$id=$_POST['body'];
        
            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                    //如果有做过处理，不执行商户的业务程序
                        
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
        
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {                               
                //更新订单状态
				echo "success";     //请不要修改或删除
				$res=array(
					'user_id' => $id,
					'ordernumber' => $ordernumber,
					'pay_money' => $pay_money,
					'pay_way' => 2,
					'create_time' => time(),
				);
				$result=$this->recharge->add($res);
				$balance = $this->user->where('id='.$id)->getfield('balance');
				$bala = $balance+$pay_money;
				$ress =array(
					'id' => $id,
					'balance' => $bala,
				);
				if($result)
				{
					financial_log($id,$pay_money,1,$bala,'充值',6);
					$results = $this->user->save($ress);
					
				}
     
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("订单编号:".$out_trade_no."付款成功[支付宝异步通知]");
            }
        }
        else {
            //验证失败
            echo "fail";
        
            //调试用，写文本函数记录程序运行情况是否正常
           // logResult("订单编号:".$out_trade_no."付款失败[支付宝异步通知]");
        }
    }
	
}
?>