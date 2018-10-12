<?php
// +----------------------------------------------------------------------
// | 充值接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\BaseController;
class PayreturnController extends BaseController {
	function _initialize() {
		parent::_initialize();
		$this->recharge = D('Recharge');
		$this->user = D('User');
		$this->information = D('Information');
	}
	
	public function return_pay()
	{
		
	    require_once VENDOR_PATH.'Pay/weixin/notify.php';
        $notify = new \PayNotifyCallBack();
        $notify->Handle(true);
        $xml=$GLOBALS['HTTP_RAW_POST_DATA'];
        $str=json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		if($str['out_trade_no']=='')
		{
			unset($data);
			$data['code']=0;
			$data['message']="充值失败！";
			outJson($data);
		}
		$order_sn=explode('_',$str['out_trade_no']);
		$ordernumber=$order_sn[0];
		$id=$order_sn[1];
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
    public function return_zfbpay()
	{
	
		//ini_set('date.timezone','Asia/Shanghai');
		//error_reporting(E_ERROR);
        //require_once VENDOR_PATH.'Pay/alipay/alipay.config.php';
		//import('Vendor.Pay.alipay.lib.alipay_notify');
        
        //计算得出通知验证结果
        //$alipayNotify = new \AlipayNotify($alipay_config);
        //$verify_result = $alipayNotify->verifyNotify();
		//$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
			//fwrite($myfile,$verify_result);
			//fclose($myfile);
        	
       // if($verify_result) {//验证成功
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
					$this->user->save($ress);
					echo "success";     
				}
            }
       
       
    }
	
}
?>