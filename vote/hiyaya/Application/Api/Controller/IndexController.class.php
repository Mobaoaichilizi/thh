<?php
// +----------------------------------------------------------------------
// | 后台首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class IndexController extends ApibaseController {
    public function index(){
		$shopuser=M('ShopUser');
		$user_id=session('user_id');
		$user_list=$shopuser->where('id='.$user_id)->find();
		$this->assign('user_list',$user_list);
		$shop_lists=M('Shop')->where("id=".session('shop_id'))->find();
		$this->assign('shop_lists',$shop_lists);
        $this->display('index');
    }
	public function welcome()
	{		/*
		$this->member=M('Member');
		$this->shop=M('Shop');
		$this->order=M('Order');
		//今日新增会员数
		$today_member_count=$this->member->where("status=1 and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		//总会员数
		$member_count=$this->member->where("status=1")->count();
		//店铺数
		$shop_count=$this->shop->where("state=1")->count();
		//今日成交金额
		$tody_pay_count=$this->order->where("pay_status=1 and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->sum('order_price');
		$tody_pay_count=!empty($tody_pay_count) ? $tody_pay_count : 0;
		//未支付订单数
		$order_not_pay=$this->order->where("pay_status=0")->count();
		//已支付订单数
		$order_yes_pay=$this->order->where("pay_status=1")->count();
		//总订单数
		$order_count=$this->order->where("is_show=0")->count();
		//总成交金额
		$pay_count=$this->order->where("pay_status=1")->sum('order_price');
		
		
		//7天的订单数
		$datetime7=time()-24*3600*6;
		$redata=array();
		$numberdata=array();
		for($i=0;$i<7;$i++)
		{
			unset($res);
			$redata[$i]=date('Y-m-d',$datetime7+$i*3600*24);
			$res=$this->order->where('pay_status=1 and '.strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1))->count();
			$numberdata[$i]=$res;
		}
		$redata=json_encode($redata);
		$numberdata=json_encode($numberdata);

		
		//12个月的成交额
		$reyear=array();
		$numberyear=array();
		for($i=0;$i<12;$i++)
		{
			unset($resyear);
			$reyear[$i]=($i+1).'月';
			$resyear=$this->order->where('pay_status=1 and '.strtotime(date("Y-".($i+1)."-d")).' < createtime  and createtime <'.(strtotime(date("Y-".($i+2)."-d"))+24*3600-1))->sum('order_price');
			$numberyear[$i]=!empty($resyear) ? $resyear : 0;
		}
		$reyear=json_encode($reyear);
		$numberyear=json_encode($numberyear);

		$this->assign("today_member_count",$today_member_count);
		$this->assign("member_count",$member_count);
		$this->assign("shop_count",$shop_count);
		$this->assign("tody_pay_count",$tody_pay_count);
		
		$this->assign("order_not_pay",$order_not_pay);
		$this->assign("order_yes_pay",$order_yes_pay);
		$this->assign("order_count",$order_count);
		$this->assign("pay_count",$pay_count);
		
		$this->assign("redata",$redata);
		$this->assign("numberdata",$numberdata);
		
		$this->assign("reyear",$reyear);
		$this->assign("numberyear",$numberyear);
		*/
		$this->display('welcome');
	}

}
?>