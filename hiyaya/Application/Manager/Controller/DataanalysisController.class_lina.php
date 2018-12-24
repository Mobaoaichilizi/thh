<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class DataanalysisController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shopmember=M("ShopMember"); //会员表
		$this->shopnumcard=M("ShopNumcard"); //次卡
		$this->shopdeadlinecard=M("ShopDeadlinecard"); //期限卡
		$this->shopcoursecard=M("ShopCoursecard"); //疗程卡
		$this->shopfinancial=M("ShopFinancial"); //消费记录
		$this->shoporders=M("Orders"); //订单记录
       //------start Alina-----
        $this->OrdersProject=M("OrdersProject"); //项目消费统计
        $this->OrdersProduct=M("OrdersProduct"); //产品消费统计
        $this->ShopItem=M("ShopItem"); //项目
        $this->ShopProduct=M("ShopProduct"); //产品
        //------end Alina-----
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
	//营业日报
    public function business()
	{
		//折线图时间
		$start_time=time()-24*3600*6;
		$end_time=time();
		$this->assign("start_time",date('Y-m-d',$start_time));
		$this->assign("end_time",date('Y-m-d',$end_time));
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		for($i=0;$i<$poor_day+1;$i++)
		{	
			$redata[$i]=$start_time+$i*3600*24;
			$redata_time[$i]=date('m-d',$start_time+$i*3600*24);
			//新增订单收入
			unset($business);
			$business=$this->shoporders->where("shop_id=".$this->shopid." and ".$redata[$i].' < pay_time  and pay_time <'.($redata[$i]+24*3600-1))->sum('pay_amount');
			$business_res[$i]=!empty($business) ? $business : 0;
			$business_res_count+=$business_res[$i];
			//新增售卡收入
			unset($sell_card);
			$sell_card=$this->shopfinancial->where("transaction_money > 0 and shop_id=".$this->shopid." and ".$redata[$i].' < createtime  and createtime <'.($redata[$i]+24*3600-1))->sum('transaction_money');
			$sell_card_res[$i]=!empty($sell_card) ? $sell_card : 0;
			$sell_card_res_count+=$sell_card_res[$i];
		}
		$this->assign('redata_time',json_encode($redata_time));
		$this->assign('business_res',json_encode($business_res));
		$this->assign('business_res_count',$business_res_count);
		$this->assign('sell_card_res',json_encode($sell_card_res));
		$this->assign('sell_card_res_count',$sell_card_res_count);
        $this->display('business');
    }
	//今日日报
	public function todaydaily()
	{
		$start_time = I('request.start_time');
		if($start_time=='')
		{
			$start_time = strtotime(date("Y-m-d"),time());
			$this->assign('today',date("Y-m-d"),time());
		}else{
			$this->assign('today',$start_time);
			$start_time=strtotime($start_time);
			
		}
		//新增订单收入
		unset($business);
		$business=$this->shoporders->where("shop_id=".$this->shopid." and ".$start_time.' < pay_time  and pay_time <'.($start_time+24*3600-1))->sum('pay_amount');
		$business_res=!empty($business) ? $business : 0;
		
		//新增售卡收入
		unset($sell_card);
		$sell_card=$this->shopfinancial->where("transaction_money > 0 and shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->sum('transaction_money');
		$sell_card_res=!empty($sell_card) ? $sell_card : 0;
		$income = $business_res + $sell_card_res;
		//会员消费
		unset($consumption);
		$consumption = $this->shopfinancial->where("transaction_money < 0 and shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->sum('transaction_money');
		$consumption_res=!empty($consumption) ? $consumption : 0;
		//新增会员数
		unset($insert_member);
		$insert_member = $this->shopmember->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->count();
		$insert_member_res = !empty($insert_member) ? $insert_member : 0;
		$this->assign('business_res',$business_res);
		$this->assign('sell_card_res',$sell_card_res);
		$this->assign('income',$income);
		$this->assign('consumption_res',$consumption_res);
		$this->assign('insert_member_res',$insert_member_res);
		$this->display('todaydaily');
	}
	//会员分析
	public function member()
	{
		
		//会员数量
		$member_count=$this->shopmember->where("shop_id=".$this->shopid)->count();
		$member_count=!empty($member_count) ? $member_count : 0;
		$this->assign("member_count",$member_count);
		//会员卡余额
		$member_balance=$this->shopmember->where("shop_id=".$this->shopid)->sum("balance");
		$member_balance=!empty($member_balance) ? $member_balance : 0;
		$this->assign("member_balance",$member_balance);
		
		//次卡充值余额
		$numcard_balance=$this->shopnumcard->where("shop_id=".$this->shopid)->sum("numcard_money");
		$numcard_balance=!empty($numcard_balance) ? $numcard_balance : 0;
		$this->assign("numcard_balance",$numcard_balance);
		
		//疗程卡充值余额
		$coursecard_balance=$this->shopcoursecard->where("shop_id=".$this->shopid)->sum("card_money");
		$coursecard_balance=!empty($coursecard_balance) ? $coursecard_balance : 0;
		$this->assign("coursecard_balance",$coursecard_balance);
		
		//折线图新增会员和充值金额
		$start_time = I('request.start_time');
		$end_time = I('request.end_time');
		
		
		if($start_time=='' || $end_time=='')
		{
			$start_time=time()-24*3600*6;
			$end_time=time();
			$this->assign("start_time",date('Y-m-d',$start_time));
			$this->assign("end_time",date('Y-m-d',$end_time));
		}else
		{
			$this->assign("start_time",$start_time);
			$this->assign("end_time",$end_time);
			$start_time=strtotime($start_time);
			$end_time=strtotime($end_time);
		}
		
		//$datetime7=time()-24*3600*6;
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		$numberdata=array();
		for($i=0;$i<$poor_day+1;$i++)
		{	
			$redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
			$redata_time[$i]=date('m-d',$start_time+$i*3600*24);
			//新增会员数量
			unset($member_res);
			$member_res=$this->shopmember->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1))->count();
			$member_data[$i]=!empty($member_res) ? $member_res : 0;
			$member_data_count+=$member_data[$i];
			//充值金额
			unset($topup_res);
			$topup_res=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and (transaction_type=1 or transaction_type=2 or transaction_type=3 or transaction_type=4)")->sum("transaction_money");
			$topup_money[$i]=!empty($topup_res) ? $topup_res : 0;
			//会员卡充值
			$membercard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=1")->sum("transaction_money");
			$membercard_array_prepaid[$i]=!empty($membercard_array_prepaid[$i]) ? $membercard_array_prepaid[$i] : 0;
			$membercard_prepaid+=$membercard_array_prepaid[$i];	
			//次卡充值
			$numcard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=2")->sum("transaction_money");
			$numcard_array_prepaid[$i]=!empty($numcard_array_prepaid[$i]) ? $numcard_array_prepaid[$i] : 0;
			$numcard_prepaid+=$numcard_array_prepaid[$i];
			//期限卡充值	
			$deadlinecard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=3")->sum("transaction_money");
			$deadlinecard_array_prepaid[$i]=!empty($deadlinecard_array_prepaid[$i]) ? $deadlinecard_array_prepaid[$i] : 0;
			$deadlinecard_prepaid+=$deadlinecard_array_prepaid[$i];
			//疗程卡充值	
			$coursecard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=4")->sum("transaction_money");
			$coursecard_array_prepaid[$i]=!empty($coursecard_array_prepaid[$i]) ? $coursecard_array_prepaid[$i] : 0;
			$coursecard_prepaid+=$coursecard_array_prepaid[$i];
			//会员卡消费
			$membercard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=1")->sum("transaction_money");
			$membercard_array_consumption[$i]=!empty($membercard_array_consumption[$i]) ? $membercard_array_consumption[$i] : 0;
			$membercard_consumption+=$membercard_array_consumption[$i];
			//次卡消费
			$numcard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=2")->sum("transaction_money");
			$numcard_array_consumption[$i]=!empty($numcard_array_consumption[$i]) ? $numcard_array_consumption[$i] : 0;
			$numcard_consumption+=$numcard_array_consumption[$i];
			//疗程卡消费	
			$coursecard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=4")->sum("transaction_money");
			$coursecard_array_consumption[$i]=!empty($coursecard_array_consumption[$i]) ? $coursecard_array_consumption[$i] : 0;
			$coursecard_consumption+=$coursecard_array_consumption[$i];
		}
		$redata_array=$redata;
		$member_data_array=$member_data;
		$topup_money_array=$topup_money;
		
		
		
		$this->assign("redata_array",array_reverse($redata_array));
		$this->assign("member_data_array",array_reverse($member_data_array));
		$this->assign("topup_money_array",array_reverse($topup_money_array));
		
		
		$this->assign("membercard_array_prepaid",array_reverse($membercard_array_prepaid));
		$this->assign("numcard_array_prepaid",array_reverse($numcard_array_prepaid));
		$this->assign("deadlinecard_array_prepaid",array_reverse($deadlinecard_array_prepaid));
		$this->assign("coursecard_array_prepaid",array_reverse($coursecard_array_prepaid));
		$this->assign("membercard_array_consumption",array_reverse($membercard_array_consumption));
		$this->assign("numcard_array_consumption",array_reverse($numcard_array_consumption));
		$this->assign("coursecard_array_consumption",array_reverse($coursecard_array_consumption));
		
		
		$redata=json_encode($redata);
		$member_data=json_encode($member_data);
		$topup_money=json_encode($topup_money);
		$this->assign("redata",$redata);
		$this->assign("redata_time",json_encode($redata_time));
		$this->assign("member_data",$member_data);
		$this->assign("topup_money",$topup_money);
		
		
		$this->assign("member_data_count",$member_data_count);
		$this->assign("membercard_prepaid",$membercard_prepaid);
		$this->assign("numcard_prepaid",$numcard_prepaid);
		$this->assign("deadlinecard_prepaid",$deadlinecard_prepaid);
		$this->assign("coursecard_prepaid",$coursecard_prepaid);
		
		$this->assign("membercard_consumption",$membercard_consumption);
		$this->assign("numcard_consumption",$numcard_consumption);
		$this->assign("coursecard_consumption",$coursecard_consumption);
		
		
		
		
		$this->display('member');
	}
    //------start Alina-----
    ///项目统计
    public function project()
    {
        $this->display('project');
    }
    //项目明细统计列表
    public function projectdetailjson()
    {
        $data=$this->OrdersProject->where("shop_id=".$this->shopid)->select();
        foreach ($data as $k=>$v)
        {
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
            $data[$k]['item_name']=M('ShopItem')->where(" id=".$v['project_id'])->getField('item_name');

        }
        //统计总价格
        $sumtotal_price=$this->OrdersProject->where("shop_id=".$this->shopid)->sum('total_price');
        //统计总收款
        $sumpay_money=$this->OrdersProject->where("shop_id=".$this->shopid)->sum('pay_money');
        //统计总提成
        $sumproject_reward=$this->OrdersProject->where("shop_id=".$this->shopid)->sum('project_reward');
        $this->assign("sumtotal_price",$sumtotal_price);
        $this->assign("sumpay_money",$sumpay_money);
        $this->assign("sumproject_reward",$sumproject_reward);
        $this->assign("data",$data);
        $data=array("code"=>0,"msg"=>"","count"=>'',"data"=>$data);
        outJson($data);
    }
    public function projectjson()
    {
        //项目汇总统计列表
        $datalist=$this->ShopItem
                        ->alias('i')
                        ->field('i.id,i.category_id,i.item_name,o.pay_money,o.total_price')
                        ->join('left join dade_orders_project AS o ON i.id = o.project_id')
                        ->where("i.shop_id=".$this->shopid)->select();
        foreach ($datalist as $k=>$v)
        {
            $datalist[$k]['ordernum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND shop_id=".$this->shopid)->count();
            $datalist[$k]['category_name']      =M('ItemCategory')->where("id=".$v['category_id'])->getField('category_name');
            $datalist[$k]['loop_rewardnum']     =M('OrdersProject')->where(" types=1 AND project_id=".$v['id']." AND shop_id=".$this->shopid)->count();
            $datalist[$k]['point_rewardnum']    =M('OrdersProject')->where(" types=2 AND project_id=".$v['id']." AND shop_id=".$this->shopid)->count();
            $datalist[$k]['add_rewardnum']       =M('OrdersProject')->where(" types=3 AND project_id=".$v['id']." AND shop_id=".$this->shopid)->count();
            $datalist[$k]['ordersnum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND shop_id=".$this->shopid)->count();
        }
        //统计总价格
        $sumtotal_price=$this->ShopItem
            ->alias('i')
            ->field('o.total_price as total_price')
            ->join('left join dade_orders_project AS o ON i.id = o.project_id')
            ->where("i.shop_id=".$this->shopid)->sum('total_price');
        //统计总收款
        $sumpay_money=$this->ShopItem
            ->alias('i')
            ->field('o.pay_money as pay_money')
            ->join('left join dade_orders_project AS o ON i.id = o.project_id')
            ->where("i.shop_id=".$this->shopid)->sum('pay_money');
        $this->assign("datalist",$datalist);
        $this->assign("sumtotal_price",$sumtotal_price);
        $this->assign("sumpay_money",$sumpay_money);
        $data=array("code"=>0,"msg"=>"","count"=>'',"data"=>$datalist);
        outJson($data);
    }
    //产品统计
    public function product()
    {
        //商品汇总统计列表
        $datalist=$this->ShopProduct
                   ->alias('p')
                   ->field('p.id,p.category_id,p.product_name,o.pay_money,o.total_price')
                   ->join('left join dade_orders_product AS o ON p.id = o.product_id')
                   ->where("p.shop_id=".$this->shopid)->select();

        foreach ($datalist as $k=>$v)
        {
            $datalist[$k]['category_name']=M('ProductCategory')->where("id=".$v['category_id'])->getField('category_name');
            $datalist[$k]['ordernum']=M('OrdersProduct')->where(" product_id=".$v['id']." AND shop_id=".$this->shopid)->count();
        }
        //统计总价格
        $sumtotal_price=$this->ShopProduct
                            ->alias('p')
                            ->field('o.total_price as total_price')
                            ->join('left join dade_orders_product AS o ON p.id = o.product_id')
                            ->where("p.shop_id=".$this->shopid)->sum('total_price');
        //统计总收款
        $sumpay_money=$this->ShopProduct
                            ->alias('p')
                            ->field('o.pay_money as pay_money')
                            ->join('left join dade_orders_product AS o ON p.id = o.product_id')
                            ->where("p.shop_id=".$this->shopid)->sum('pay_money');
        $this->assign("datalist",$datalist);
        $this->assign("sumtotal_price",$sumtotal_price);
        $this->assign("sumpay_money",$sumpay_money);
        //商品明细统计列表
        $data=$this->OrdersProduct->where("shop_id=".$this->shopid)->select();
        foreach ($data as $k=>$v)
        {
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
            $data[$k]['product_name']=M('ShopProduct')->where(" id=".$v['product_id'])->getField('product_name');

        }
        //统计总价格
        $sumtotal_price=$this->OrdersProduct->where("shop_id=".$this->shopid)->sum('total_price');
        //统计总收款
        $sumpay_money=$this->OrdersProduct->where("shop_id=".$this->shopid)->sum('pay_money');
        //统计总提成
        $sumproduct_reward=$this->OrdersProduct->where("shop_id=".$this->shopid)->sum('product_reward');
        $this->assign("sumtotal_price",$sumtotal_price);
        $this->assign("sumpay_money",$sumpay_money);
        $this->assign("sumproduct_reward",$sumproduct_reward);
        $this->assign("data",$data);
        $this->display('product');
    }
    //------end Alina-----



    //散客分析
	public function individual(){
		//散客数量
		$member_count=$this->shopmember->where("shop_id=".$this->shopid.' and identity=2')->count();
		$member_count=!empty($member_count) ? $member_count : 0;
		$this->assign("member_count",$member_count);
		//散客消费金额
		$individuals = $this->shopmember->where("shop_id=".$this->shopid.' and identity=2')->getfield('id',true);
		$individuals = implode(',', $individuals);
		$member_balance=$this->shoporders->where("shop_id=".$this->shopid." and id in ('".$individuals."')")->sum("pay_amount");
		$member_balance=!empty($member_balance) ? $member_balance : 0;
		$this->assign("member_balance",$member_balance);
		
		//优惠金额
		$preferential=$this->shoporders->where("shop_id=".$this->shopid." and id in ('".$individuals."')")->sum("preferential_amount");
		$preferential=!empty($preferential) ? $preferential : 0;
		$this->assign("preferential",$preferential);
		//散客人均消费
		$average_consumption = number_format($individuals/$member_count, 2);
		$this->assign("average_consumption",$average_consumption);
		
		//折线图新增会员和充值金额
		$start_time = I('request.start_time');
		$end_time = I('request.end_time');
		
		
		if($start_time=='' || $end_time=='')
		{
			$start_time=time()-24*3600*6;
			$end_time=time();
			$this->assign("start_time",date('Y-m-d',$start_time));
			$this->assign("end_time",date('Y-m-d',$end_time));
		}else
		{
			$this->assign("start_time",$start_time);
			$this->assign("end_time",$end_time);
			$start_time=strtotime($start_time);
			$end_time=strtotime($end_time);
		}
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		$numberdata=array();
		for($i=0;$i<$poor_day+1;$i++)
		{	
			$redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
			$redata_time[$i]=date('m-d',$start_time+$i*3600*24);
			//新增散客数量
			unset($member_res);
			$member_res=$this->shopmember->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1).' and identity=2')->count();
			$member_data[$i]=!empty($member_res) ? $member_res : 0;
			$member_data_count+=$member_data[$i];
			
			//消费金额
			unset($consumption);
			$consumption=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and identity=2")->sum("transaction_money");
			$consumption_money[$i]=!empty($consumption) ? $consumption : 0;

			//优惠金额
			$preferential_res=$this->shoporders->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and id in ('".$individuals."')")->sum("preferential_amount");
			$preferential_money[$i]=!empty($preferential_res) ? $preferential_money : 0;
			
		}
		
		//表格统计数据
		$redata_array=$redata;
		$member_data_array=$member_data;
		$consumption_money_array=$consumption_money;
		$preferential_money_array=$preferential_money;
	
		$this->assign("redata_array",array_reverse($redata_array));
		$this->assign("member_data_array",array_reverse($member_data_array));
		$this->assign("consumption_money_array",array_reverse($consumption_money_array));
		$this->assign("preferential_money_array",array_reverse($preferential_money_array));
		//折线图
		$redata=json_encode($redata);
		$member_data=json_encode($member_data);
		$consumption_money=json_encode($consumption_money);
		$this->assign("redata",$redata);
		$this->assign("redata_time",json_encode($redata_time));
		$this->assign("member_data",$member_data);
		$this->assign("consumption_money",$consumption_money);
		$this->assign("member_data_count",$member_data_count);
		$this->display('individual');
	}
}
?>