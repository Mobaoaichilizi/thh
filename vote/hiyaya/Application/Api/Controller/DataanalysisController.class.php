<?php
// +----------------------------------------------------------------------
// | 数据分析
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class DataanalysisController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		//$this->shoplockcard = D("shopLockcard"); //锁牌
		//$this->itemcategory=M("ItemCategory"); //项目分类
		//$this->shopitem=M("ShopItem"); //项目列表
		//$this->shopproduct=M("ShopProduct"); //产品列表
		//$this->productcategory=M("ProductCategory"); //产品分类
		//$this->shopreward=M("ShopReward"); //推荐提成
		//$this->shopscheduling=M("ShopScheduling"); //排班表
		$this->shopmasseur=M("ShopMasseur"); //技师表
		$this->orders=M("Orders"); //订单表
		$this->ordersproject=M("OrdersProject"); //订单项目表
		$this->ordersproduct=M("OrdersProduct"); //订单产品表
		//$this->ordersreward=M("OrdersReward"); //订单提成表
		$this->shoproom=M("ShopRoom"); //房间表
		$this->roomcategory=M("RoomCategory"); //房间分类
		$this->shopbed=M("ShopBed"); //床位表
		$this->shopmember=M("ShopMember"); //会员表
		$this->shopnumcard=M("ShopNumcard"); //次卡
		$this->shopdeadlinecard=M("ShopDeadlinecard"); //期限卡
		$this->shopcoursecard=M("ShopCoursecard"); //疗程卡
		$this->shopcourseproject=M("ShopCourseproject"); //疗程卡对应的项目
		$this->setting=M("Setting"); //参数管理
		$this->shopfinancial=M("ShopFinancial"); //消费记录
		$this->masseurcategory=M("MasseurCategory"); //技师等级
		//$this->shoprole=M("ShopRole"); //商家权限
       // $this->shopuser=M("ShopUser"); //商家管理员
		//$this->smscode=M("Smscode"); //短信内容
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
		$this->userid=$this->user_id;
	}
	//业绩查看
	public function ResultsView()
	{
       $start_time=I('post.start_time');
	   $end_time=I('post.end_time');
	   if(empty($start_time) && empty($end_time))
	   {
		   $start_time=strtotime(date("Y-m-d",time()-(24*3600*6)));
		   $end_time=strtotime(date("Y-m-d 23:59:59",time()));
	   }else
	   {
		   $start_time=strtotime(date("Y-m-d",strtotime($end_time)));
           $end_time=strtotime(date("Y-m-d 23:59:59",$start_time));
	   }
	  	
		//业绩图标
		//$datetime7=time()-24*3600*6;
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		$numberdata=array();
		for($i=0;$i<$poor_day;$i++)
		{	
			$redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
			$numberdata[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
		}
		$time_results=$redata;
		$moeny_results=$numberdata;

		//print_r($redata_array);
		//$this->assign("redata_array",array_reverse($redata_array));
	   //今天营业收入
	   $today_income=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and createtime >".strtotime(date("Y-m-d",time()))." and createtime < ".strtotime(date("Y-m-d 23:59:59",time())))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date("Y-m-d",time()))." and createtime < ".strtotime(date("Y-m-d 23:59:59",time())))->sum("pay_amount"));
	   
	   //营业概况
	   
	   
	   
	   
	   $business_info=array(
			'operating_income' => ($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount")),
			'card_income' => $this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"),
			'order_count' => $this->orders->where("shop_id=".$this->shopid." and status >0 and createtime >".$start_time." and createtime < ".$end_time)->count(),
			'guest_count' => $this->ordersproject->where("shop_id=".$this->shopid." and is_del=0 and createtime >".$start_time." and createtime < ".$end_time." and (types=1 or types=2 or types=4) and up_time > 0")->count(),
	   );
	   //会员
	   $member_card_recharge=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=1 and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $member_card_recharge=!empty($member_card_recharge) ? $member_card_recharge : 0;
	   $time_card_recharge=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=2 and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $time_card_recharge=!empty($time_card_recharge) ? $time_card_recharge : 0;
	   $member_card_consumption=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=1 and type=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $member_card_consumption=!empty($member_card_consumption) ? $member_card_consumption : 0;
	   $time_card_consumption=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=2 and type=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $time_card_consumption=!empty($time_card_consumption) ? $time_card_consumption : 0;
	   $treatment_card_consumption=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=4 and type=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $treatment_card_consumption=!empty($treatment_card_consumption) ? $treatment_card_consumption : 0;
	   $member_info=array(
			'member_card_recharge' => $member_card_recharge,
			'time_card_recharge' => $time_card_recharge,
			'member_card_consumption' => $member_card_consumption,
			'time_card_consumption' => $time_card_consumption,
			'treatment_card_consumption' => $treatment_card_consumption,
	   );
	   //营收构成
	   $cash=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=5 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=5 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount"));
		
	   $cash=!empty($cash) ? $cash : 0;
	   $bank_card=$this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=18 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money")+$this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=18 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount");
	   $bank_card=!empty($bank_card) ? $bank_card : 0;
	   $alipay=$this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=3 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money")+$this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=3 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount");
	   $alipay=!empty($alipay) ? $alipay : 0;
	   $weixin=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=4 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=4	 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount"));
	   
	   $weixin=!empty($weixin) ? $weixin : 0;
	   $preferential=$this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("preferential_amount");
	   $preferential=!empty($preferential) ? $preferential : 0;
	   $revenue_form=array(
			'cash' => $cash,
			'bank_card' => $bank_card,
			'alipay' => $alipay,
			'weixin' => $weixin,
			'preferential' => $preferential,
	   );
	   
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['time_results']=$time_results;
		$data['moeny_results']=$moeny_results;
		$data['today_income']=$today_income;
		$data['business_info']=$business_info;
		$data['member_info']=$member_info;
		$data['revenue_form']=$revenue_form;
		outJson($data); 
	}
	//营业日报
	public function BusinessDaily()
	{
	   $start_time=I('post.start_time');
	   $end_time=I('post.end_time');
	   if(empty($start_time) && empty($end_time))
	   {
		   $start_time=strtotime(date("Y-m-d",time()-(24*3600*6)));
		   $end_time=strtotime(date("Y-m-d 23:59:59",time()));
	   }else
	   {
		   $start_time=strtotime(date("Y-m-d",strtotime($end_time)));
           $end_time=strtotime(date("Y-m-d 23:59:59",$start_time));
	   }
	  	
		//业绩图标
		//$datetime7=time()-24*3600*6;
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		$numberdata=array();
		$order_countmoney=array();
		$card_countmoney=array();
		$order_countnum=array();
		$card_countnum=array();
		$cash_countmoney=array();
		$bank_countmoney=array();
		$alipay_countmoney=array();
		$weixin_countmoney=array();
		$preferential_countmoney=array();
		$taxes_countmoney=array();
		for($i=0;$i<$poor_day;$i++)
		{	
			$redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
			$numberdata[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
			//$member_card_recharge=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=1 and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
			$numberdata[$i]=!empty($numberdata[$i]) ? $numberdata[$i] : 0;
			
			
			$order_countmoney[$i]=$this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount");
			$order_countmoney[$i]=!empty($order_countmoney[$i]) ? $order_countmoney[$i] : 0;
			$card_countmoney[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and (transaction_type=1 or transaction_type=2 or transaction_type=3 or transaction_type=4) and type=1 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money");
			$card_countmoney[$i]=!empty($card_countmoney[$i]) ? $card_countmoney[$i] : 0;
			$order_countnum[$i]=$this->orders->where("shop_id=".$this->shopid." and status >0 and createtime >".$start_time." and createtime < ".$end_time)->count();
			$order_countnum[$i]=!empty($order_countnum[$i]) ? $order_countnum[$i] : 0;
			$card_countnum[$i]=50;
			$card_countnum[$i]=!empty($card_countnum[$i]) ? $card_countnum[$i] : 0;
			$cash_countmoney[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=5 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=5 and  createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
			$cash_countmoney[$i]=!empty($cash_countmoney[$i]) ? $cash_countmoney[$i] : 0;
			$bank_countmoney[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=18 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=18 and  createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
			$bank_countmoney[$i]=!empty($bank_countmoney[$i]) ? $bank_countmoney[$i] : 0;
			$alipay_countmoney[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=3 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=3 and  createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
			$alipay_countmoney[$i]=!empty($alipay_countmoney[$i]) ? $alipay_countmoney[$i] : 0;
			$weixin_countmoney[$i]=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=4 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=4 and  createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("pay_amount"));
			$weixin_countmoney[$i]=!empty($weixin_countmoney[$i]) ? $weixin_countmoney[$i] : 0;
			$preferential_countmoney[$i]=$this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("preferential_amount");
			$preferential_countmoney[$i]=!empty($preferential_countmoney[$i]) ? $preferential_countmoney[$i] : 0;
			$taxes_countmoney[$i]=$this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".strtotime(date('Y-m-d',$start_time+$i*3600*24))." and createtime < ".strtotime(date('Y-m-d 23:59:59',$start_time+$i*3600*24)))->sum("invoice_money");
			$taxes_countmoney[$i]=!empty($taxes_countmoney[$i]) ? $taxes_countmoney[$i] : 0;
		}
		$time_results=$redata; //时间
		$moeny_results=$numberdata; //营业总收入
		$order_count_money=$order_countmoney; //订单总收入
		$card_count_money=$card_countmoney; //售卡总收入
		$order_count_num=$order_countnum; //订单总数
		$card_count_num=$card_countnum; //售卡总数
		$cash_count_money=$cash_countmoney; //现金
		$bank_count_money=$bank_countmoney; //银行卡
		$alipay_count_money=$alipay_countmoney; //支付宝
		$weixin_count_money=$weixin_countmoney; //微信
		$preferential_count_money=$preferential_countmoney; //优惠
		$taxes_count_money=$taxes_countmoney; //税费
		
		$pay_money_list=array(
			'time_results' => $time_results,
			'moeny_results' => $moeny_results,
			'order_count_money' => $order_count_money,
			'card_count_money' => $card_count_money,
			'order_count_num' => $order_count_num,
			'card_count_num' => $card_count_num,
			'cash_count_money' => $cash_count_money,
			'bank_count_money' => $bank_count_money,
			'alipay_count_money' => $alipay_count_money,
			'weixin_count_money' => $weixin_count_money,
			'preferential_count_money' => $preferential_count_money,
			'taxes_countmoney' => $taxes_countmoney,
		);
		
		
		
		
	   $number_card_money=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=2 and type=1 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $number_card_money=!empty($number_card_money) ? $number_card_money : 0;
	   $member_card_money=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_type=1 and type=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money");
	   $member_card_money=!empty($member_card_money) ? $member_card_money : 0;
	   $order_money=$this->orders->where("shop_id=".$this->shopid." and status=2 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount");
	   $order_money=!empty($order_money) ? $order_money : 0;
	  
		
		//收入构成
		$incomes=array(
			'number_card_money' => $number_card_money,
			'member_card_money' => $member_card_money,
			'order_money' => $order_money,
		);
		
		
	   $cash=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=5 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=5 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount"));
	   $cash=!empty($cash) ? $cash : 0;
	   $bank_card=$this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=18 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money")+$this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=18 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount");
	   $bank_card=!empty($bank_card) ? $bank_card : 0;
	   $alipay=$this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=3 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money")+$this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=3 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount");
	   $alipay=!empty($alipay) ? $alipay : 0;
	   $weixin=($this->shopfinancial->where("shop_id=".$this->shopid." and type=1 and pay_way=4 and createtime >".$start_time." and createtime < ".$end_time)->sum("transaction_money"))+($this->orders->where("shop_id=".$this->shopid." and status=2 and pay_type=4	 and createtime >".$start_time." and createtime < ".$end_time)->sum("pay_amount"));
	   $weixin=!empty($weixin) ? $weixin : 0;
		
		
		//支付构成
		$to_pay=array(
			'cash' => $cash,
			'bank_card' => $bank_card,
			'alipay' => $alipay,
			'weixin' => $weixin,
		);
		
		
		
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['pay_money_list']=$pay_money_list;
		$data['incomes']=$incomes;
		$data['to_pay']=$to_pay;
		outJson($data); 
		
	}
}
?>