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

        $this->shopmasseur = M('ShopMasseur');//技师表
        $this->ordersreward = M('OrdersReward');//推荐提成表
        $this->orderspaytype = M('OrdersPaytype');//订单付款方式表
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
        $this->shop_name=M("shop")->where('id='.$this->shopid)->getField('shop_name');
	}
	//营业日报
    public function business()
	{
		//折线图
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
		// $start_time=time()-24*3600*6;
		// $end_time=time();
		$this->assign("start_time",date('Y-m-d',$start_time));
		$this->assign("end_time",date('Y-m-d',$end_time));
		$poor_day=round(($end_time-$start_time)/(24*3600));
		$redata=array();
		for($i=0;$i<$poor_day+1;$i++)
		{	
			$redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
			$redata_time[$i]=date('m-d',$start_time+$i*3600*24);
			//新增订单收入
			unset($business);
			$business=$this->shoporders->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < pay_time  and pay_time <'.(strtotime($redata[$i])+24*3600-1))->sum('pay_amount');
			$business_res[$i]=!empty($business) ? $business : 0;
			$business_res_count+=$business_res[$i];
			//新增售卡收入
			unset($sell_card);
			$sell_card=$this->shopfinancial->where("transaction_money > 0 and shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_type!=4")->sum('transaction_money');
			$sell_card_res[$i]=!empty($sell_card) ? $sell_card : 0;

			$financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i])." < createtime  and createtime <".(strtotime($redata[$i])+24*3600-1)." and type=1 and transaction_type=4")->select();

			//排序只保留相同card_id的一条数据
			$list = array();
			if(!empty($financials)){
				foreach ($financials as $k=>$vo) {
					$id=intval($vo['card_id']);
				 	$list[$id]=isset($list[$id])?
				 			(strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
				}
			}
			$list=array_values($list);
			$arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
			array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
			if(!empty($list)){
				foreach ($list as $key => $value) {
					$card_cash[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_cash_count[$i]+=$card_cash[$key];
				}
				$card_cash_count_res[$i]=!empty($card_cash_count[$i]) ? $card_cash_count[$i] : 0;
			}
			$sell_card_res[$i]+=$card_cash_count_res[$i];
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
		// unset($sell_card);
		// $sell_card=$this->shopfinancial->where("transaction_money > 0 and shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->sum('transaction_money');
		// $sell_card_res=!empty($sell_card) ? $sell_card : 0;
		// $income = $business_res + $sell_card_res;
		//会员消费
		unset($consumption);
		$consumption = $this->shopfinancial->where("transaction_money < 0 and shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->sum('transaction_money');
		$consumption_res=!empty($consumption) ? (-1)*$consumption : 0;
		//新增会员数
		unset($insert_member);
		$insert_member = $this->shopmember->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($start_time+24*3600-1))->count();
		$insert_member_res = !empty($insert_member) ? $insert_member : 0;

		//订单收入
		$business_cash=$this->orderspaytype->where("shop_id=".$this->shopid." and ".$start_time." < pay_time  and pay_time <".($start_time+24*3600-1)." and pay_typeid=5")->sum('pay_money');
		$business_cash_count = !empty($business_cash) ? $business_cash : 0;
		$business_wechat=$this->orderspaytype->where("shop_id=".$this->shopid." and ".$start_time." < pay_time  and pay_time <".($start_time+24*3600-1)." and pay_typeid=4")->sum('pay_money');
		$business_wechat_count = !empty($business_wechat) ? $business_wechat : 0;
		$business_alipay=$this->orderspaytype->where("shop_id=".$this->shopid." and ".$start_time." < pay_time  and pay_time <".($start_time+24*3600-1)." and pay_typeid=3")->sum('pay_money');
		$business_alipay_count = !empty($business_alipay) ? $business_alipay : 0;
		$business_bank=$this->orderspaytype->where("shop_id=".$this->shopid." and ".$start_time." < pay_time  and pay_time <".($start_time+24*3600-1)." and pay_typeid=2")->sum('pay_money');
		$business_bank_count = !empty($business_bank) ? $business_bank : 0;
		$business_preferential = $this->shoporders->where("shop_id=".$this->shopid." and ".$start_time." < pay_time  and pay_time <".($start_time+24*3600-1))->sum('preferential_amount');
		$business_preferential_count = !empty($business_preferential) ? $business_preferential : 0;
		
		//售卡收入
		$financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time." < createtime  and createtime <".($start_time+24*3600-1)." and type=1")->select();
		//排序只保留相同card_id的一条数据
		$list = array();
		if(!empty($financials)){
			foreach ($financials as $k=>$vo) {
				if($vo['transaction_type'] == '4'){
					$id=intval($vo['card_id']);
				 	$list[$id]=isset($list[$id])?
				 			(strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
		 		}else{
					$list[$vo['createtime']] = $vo;
		 		}
				
			}
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
		array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
		$card_cash_count = 0;
		$card_bank_count = 0;
		$card_alipay_count = 0;
		$card_wechat_count = 0;
		if(!empty($list)){
			foreach ($list as $key => $value) {
				if($value['pay_way'] == 1){
					$card_cash[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_cash_count+=$card_cash[$key];
				}else if($value['pay_way'] == 2){
					$card_bank[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_bank_count+=$card_bank[$key];
				}else if($value['pay_way'] == 3){
					$card_alipay[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_alipay_count+=$card_alipay[$key];
				}else if($value['pay_way'] == 4){
					$card_wechat[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_wechat_count+=$card_wechat[$key];
				}
			}
		}
		$sell_card_res = $card_cash_count+$card_bank_count+$card_alipay_count+$card_wechat_count;
		$income = $business_res+$sell_card_res;
		$this->assign('business_cash',$business_cash_count);
		$this->assign('business_wechat',$business_wechat_count);
		$this->assign('business_alipay',$business_alipay_count);
		$this->assign('business_bank',$business_bank_count);
		$this->assign('business_preferential',$business_preferential_count);
		$this->assign('card_cash',$card_cash_count);
		$this->assign('card_bank',$card_bank_count);
		$this->assign('card_alipay',$card_alipay_count);
		$this->assign('card_wechat',$card_wechat_count);

		//总统计数据
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
		$member_count=$this->shopmember->where("shop_id=".$this->shopid.' and identity=1')->count();
		$member_count=!empty($member_count) ? $member_count : 0;
		$this->assign("member_count",$member_count);
		//会员卡余额
		$member_balance=$this->shopmember->where("shop_id=".$this->shopid.' and identity=1')->sum("balance");
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
			$member_res=$this->shopmember->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and identity=1")->count();
			$member_data[$i]=!empty($member_res) ? $member_res : 0;
			$member_data_count+=$member_data[$i];
			
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
			$financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i])." < createtime  and createtime <".(strtotime($redata[$i])+24*3600-1)." and type=1 and transaction_type=4")->select();
			//排序只保留相同card_id的一条数据
			$list = array();
			if(!empty($financials)){
				foreach ($financials as $k=>$vo) {
					$id=intval($vo['card_id']);
				 	$list[$id]=isset($list[$id])?
				 			(strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
				}
			}
			$list=array_values($list);
			$arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
			array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
			if(!empty($list)){
				foreach ($list as $key => $value) {
					$card_cash[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
					$card_cash_count[$i]+=$card_cash[$key];
				}
				
			}
			$coursecard_array_prepaid[$i]=!empty($card_cash_count[$i]) ? $card_cash_count[$i] : 0;
			$coursecard_prepaid+=$coursecard_array_prepaid[$i];



			// $coursecard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=4")->sum("transaction_money");
			// $coursecard_array_prepaid[$i]=!empty($coursecard_array_prepaid[$i]) ? $coursecard_array_prepaid[$i] : 0;
			// $coursecard_prepaid+=$coursecard_array_prepaid[$i];

			//充值金额
			unset($topup_res);
			// $topup_res=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and (transaction_type=1 or transaction_type=2 or transaction_type=3 or transaction_type=4)")->sum("transaction_money");
			$topup_res = $membercard_array_prepaid[$i]+$numcard_array_prepaid[$i]+$deadlinecard_array_prepaid[$i]+$coursecard_array_prepaid[$i];
			$topup_money[$i]=!empty($topup_res) ? $topup_res : 0;
			//会员卡消费
			$membercard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=1")->sum("transaction_money");
			$membercard_array_consumption[$i]=!empty($membercard_array_consumption[$i]) ? (-1)*$membercard_array_consumption[$i] : 0;
			$membercard_consumption+=$membercard_array_consumption[$i];
			//次卡消费
			$numcard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=2")->sum("transaction_money");
			$numcard_array_consumption[$i]=!empty($numcard_array_consumption[$i]) ? (-1)*$numcard_array_consumption[$i] : 0;
			$numcard_consumption+=$numcard_array_consumption[$i];
			//疗程卡消费	
			$coursecard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=4")->sum("transaction_money");
			$coursecard_array_consumption[$i]=!empty($coursecard_array_consumption[$i]) ? (-1)*$coursecard_array_consumption[$i] : 0;
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
    //项目汇总统计列表
    public function projectjson()
    {
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $pagenum =!empty($limit) ? $limit :10;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $where=" shop_id=".$this->shopid;
        $sqlwhere=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $sqlwhere.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $sqlwhere.=" and createtime<='".strtotime($enddate)."'";
        }
        $datalist=$this->ShopItem->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->ShopItem->where($where)->count();
        $total_price=0;
        $pay_money=0;
        $ordernum=0;
        $loop_rewardnum=0;
        $point_rewardnum=0;
        $add_rewardnum=0;
        $ordersnum=0;
        $fee=0;
        foreach ($datalist as $k=>$v)
        {
            $datalist[$k]['pay_money']          =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+=$datalist[$k]['pay_money'];

            //税费计算
            $order_ids = $this->OrdersProject->where("  project_id=".$v['id']." AND ".$sqlwhere)->getfield('order_id',true);
          
            if($order_ids){
             $orders_ids = implode(',', array_unique($order_ids));

            	$datalist[$k]['fee']                 =$this->shoporders->where("id in (".$orders_ids.")")->sum('invoice_money');
            }else{
            	$datalist[$k]['fee']="0";
            }
            $fee+=$datalist[$k]['fee'];

            $datalist[$k]['total_price']        =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$datalist[$k]['total_price'];
            $datalist[$k]['ordernum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+= $datalist[$k]['ordernum'];
            $datalist[$k]['category_name']      =M('ItemCategory')->where("id=".$v['category_id'])->getField('category_name');
            $datalist[$k]['loop_rewardnum']     =M('OrdersProject')->where(" types=1 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $loop_rewardnum+=$datalist[$k]['loop_rewardnum'];
            $datalist[$k]['point_rewardnum']    =M('OrdersProject')->where(" types=2 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $point_rewardnum+=$datalist[$k]['point_rewardnum'];
            $datalist[$k]['add_rewardnum']      =M('OrdersProject')->where(" types=3 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $add_rewardnum+=$datalist[$k]['add_rewardnum'];
            $datalist[$k]['ordersnum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordersnum+=$datalist[$k]['ordersnum'];
        }
        $total=array('id'=>'',"category_name"=>'',
            'item_name'=>'合计',
            'total_price'=>$total_price,'pay_money'=>$pay_money,'fee'=>$fee,'ordernum'=>$ordernum,'loop_rewardnum'=>$loop_rewardnum,
            'point_rewardnum'=>$point_rewardnum,'add_rewardnum'=>$add_rewardnum,'ordersnum'=>$ordersnum);

        array_push($datalist,$total);
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$datalist);

        outJson($data);
    }
    //项目统计列表导出
    public function exportproject()
    {
//        $limit   =I('get.limit');
//        $pagecur=I('get.page');
//        $pagenum =!empty($limit) ? $limit :10;
//        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" shop_id=".$this->shopid;
        $sqlwhere=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");

        if(!empty($startdate))
        {
            $sqlwhere.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $sqlwhere.=" and createtime<='".strtotime($enddate)."'";
        }
        //$datalist=$this->ShopItem->where($where)->page($pagecur.','.$pagenum)->select();
        $datalist=$this->ShopItem->where($where)->select();
        $ordernum=0;
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $ordersnum=0;
        $loop_rewardnum=0;
        $point_rewardnum=0;
        $add_rewardnum=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['category_name']      =M('ItemCategory')->where("id=".$v['category_id'])->getField('category_name');
            $data[$k]['item_name']          =$v['item_name'];
            $data[$k]['ordernum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+=$data[$k]['ordernum'];
            $data[$k]['total_price']        =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money']          =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+=$data[$k]['pay_money'];
            //税费计算
            $order_ids = $this->OrdersProject->where("  project_id=".$v['id']." AND ".$sqlwhere)->getfield('order_id',true);
          
            if($order_ids){
             $orders_ids = implode(',', array_unique($order_ids));

            	$datalist[$k]['fee']                 =$this->shoporders->where("id in (".$orders_ids.")")->sum('invoice_money');
            }else{
            	$datalist[$k]['fee']="0";
            }
            $fee+=$datalist[$k]['fee'];
            // $data[$k]['fee']                 ='';
            // $fee+=$data[$k]['fee'];
            $data[$k]['ordersnum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordersnum+=$data[$k]['ordersnum'];
            $data[$k]['loop_rewardnum']     =M('OrdersProject')->where(" types=1 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $data[$k]['point_rewardnum']    =M('OrdersProject')->where(" types=2 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $data[$k]['add_rewardnum']      =M('OrdersProject')->where(" types=3 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $loop_rewardnum+=$data[$k]['loop_rewardnum'];
            $point_rewardnum+=$data[$k]['point_rewardnum'];
            $add_rewardnum+=$data[$k]['add_rewardnum'];

        }
        $total=array('id'=>'','category_name'=>'','item_name'=>'合计','ordernum'=>$ordernum,'total_price'=>$total_price,'pay_money'=>$pay_money,
                        'fee'=>$fee,'ordersnum'=>$ordersnum,'loop_rewardnum'=>$loop_rewardnum,'point_rewardnum'=>$point_rewardnum,'add_rewardnum'=>$add_rewardnum
        );
        array_push($data,$total);
        $Title= $this->shop_name."项目统计列表";
        $Fieldsname=array( array('id','ID'),
                            array('category_name',"项目类型"),
                            array('item_name',"项目名称"),
                            array('ordernum',"消费次数"),
                            array('total_price',"金额（原价）"),
                            array('pay_money',"优惠"),
                            array('fee',"税费"),
                            array('ordersnum',"总钟数"),
                            array('loop_rewardnum',"轮钟数"),
                            array('point_rewardnum',"点钟数"),
                            array('add_rewardnum',"加钟数"));

        exportExcel($Title,$Fieldsname,$data);
    }
    //项目明细统计列表
    public function projectdetailjson()
    {
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $pagenum =!empty($limit) ? $limit :10;
        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $where.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $where.=" and createtime<='".strtotime($enddate)."'";
        }
        $data=$this->OrdersProject->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->OrdersProject->where($where)->count();
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $project_reward=0;
        foreach ($data as $k=>$v)
        {
            $total_price+=$data[$k]['total_price'];
            $pay_money+=$data[$k]['pay_money'];
            $project_reward+= $data[$k]['project_reward'];
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
            $data[$k]['item_name']=M('ShopItem')->where(" id=".$v['project_id'])->getField('item_name');

            //税费计算
           	$data[$k]['fee']=$this->shoporders->where("id=".$v['order_id'])->getField('invoice_money');
           	$fee+=$data[$k]['fee'];
            
           

        }
        $total=array('id'=>'',"createtime"=>'','pay_time'=>'',
            'item_name'=>'合计',
            'total_price'=>$total_price,'pay_money'=>$pay_money,'fee'=>$fee,'masseur_name'=>'','project_reward'=>$project_reward,'payuser'=>'');

        array_push($data,$total);
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$data);
        outJson($data);
    }

    //项目明细列表导出
    public function exportprojectdetail()
    {
        //$limit = I('get.limit');
        //$pagecur = I('get.page');
        //$pagenum = !empty($limit) ? $limit : 10;
        //$pagecur = !empty($pagecur) ? $pagecur : 1;

        $where = " is_del=0 AND shop_id=" . $this->shopid;
        $startdate = I("startdate");
        $enddate = I("enddate");
        if (!empty($startdate)) {
            $where .= " and createtime>='" . strtotime($startdate) . "'";
        }
        if (!empty($enddate)) {
            $where .= " and createtime<='" . strtotime($enddate) . "'";
        }
        //$datalist = $this->OrdersProject->where($where)->page($pagecur . ',' . $pagenum)->select();
        $datalist = $this->OrdersProject->where($where)->select();
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $project_reward=0;
        foreach ($datalist as $k => $v) {
            $data[$k]['id'] =$v['id'];
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['item_name'] = M('ShopItem')->where(" id=" . $v['project_id'])->getField('item_name');
            $data[$k]['total_price'] =$v['total_price'];
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money'] =$v['pay_money'];
            $pay_money+=$data[$k]['pay_money'];
            //税费计算
           	$data[$k]['fee']=$this->shoporders->where("id=".$v['order_id'])->getField('invoice_money');
           	$fee+=$data[$k]['fee'];
            // $data[$k]['fee'] ='';
            // $fee+=$data[$k]['fee'];
            $data[$k]['masseur_name'] = M('ShopMasseur')->where(" id=" . $v['masseur_id'])->getField('masseur_name');
            $data[$k]['project_reward'] =$v['project_reward'];
            $project_reward+= $data[$k]['project_reward'];
            $data[$k]['payuser'] = M('ShopUser')->where(" id=" . $v['payuser_id'])->getField('username');
        }
        $total=array('id'=>'',"createtime"=>'','pay_time'=>'',
            'item_name'=>'合计',
            'total_price'=>$total_price,'pay_money'=>$pay_money,'fee'=>$fee,'masseur_name'=>'','project_reward'=>$project_reward,'payuser'=>'');

        array_push($data,$total);
        $Title= $this->shop_name."项目明细列表";
        $Fieldsname=array( array('id','ID'),
                    array('createtime',"开单时间"),
                    array('pay_time',"记账日期"),
                    array('item_name',"项目名称"),
                    array('total_price',"项目金额（原价）"),
                    array('pay_money',"优惠"),
                    array('fee',"税费"),
                    array('masseur_name',"服务健康师"),
                    array('project_reward',"服务提成"),
                    array('payuser',"收银人员"));

        exportExcel($Title,$Fieldsname,$data);
    }


    //产品统计
    //商品汇总统计列表
    public function productjson()
    {
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $pagenum =!empty($limit) ? $limit :10;
        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" shop_id=".$this->shopid;
        $sqlwhere=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $sqlwhere.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $sqlwhere.=" and createtime<='".strtotime($enddate)."'";
        }
        $datalist=$this->ShopProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->ShopProduct->where($where)->count();
        $ordernum=0;
        $total_price=0;
        $pay_money=0;
        $fee=0;
        foreach ($datalist as $k=>$v)
        {
            $datalist[$k]['category_name']=M('ProductCategory')->where("id=".$v['category_id'])->getField('category_name');
            $datalist[$k]['ordernum']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+=$datalist[$k]['ordernum'];
            $datalist[$k]['pay_money']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+=$datalist[$k]['pay_money'];
            $datalist[$k]['total_price']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$datalist[$k]['total_price'];
            //税费计算
            $order_ids = $this->OrdersProduct->where("  product_id=".$v['id']." AND ".$sqlwhere)->getfield('order_id',true);
          
            if($order_ids){
             $orders_ids = implode(',', array_unique($order_ids));

            	$datalist[$k]['fee'] = $this->shoporders->where("id in (".$orders_ids.")")->sum('invoice_money');
            }else{
            	$datalist[$k]['fee']="0";
            }
            $fee+=$datalist[$k]['fee'];

            $datalist[$k]['fee']=0;
            $fee+=$datalist[$k]['fee'];
        }
        $total=array("id"=>'',"category_name"=>'',"product_name"=>'合计',"ordernum"=>$ordernum,"total_price"=>$total_price,"pay_money"=>$pay_money,"fee"=>$fee);
        array_push($datalist,$total);
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$datalist);
        outJson($data);
    }
    //导出
    public function exportproduct()
    {
//        $limit   =I('get.limit');
//        $pagecur=I('get.page');
//        $pagenum =!empty($limit) ? $limit :10;
//        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" shop_id=".$this->shopid;
        $sqlwhere=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $sqlwhere.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $sqlwhere.=" and createtime<='".strtotime($enddate)."'";
        }
        //$datalist=$this->ShopProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $datalist=$this->ShopProduct->where($where)->select();
        $ordernum=0;
        $total_price=0;
        $pay_money=0;
        $fee=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['category_name']=M('ProductCategory')->where("id=".$v['category_id'])->getField('category_name');
            $data[$k]['product_name']   =$v['product_name'];
            $data[$k]['ordernum']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+=$data[$k]['ordernum'];
            $data[$k]['total_price']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+= $data[$k]['pay_money'];
            //税费计算
            $order_ids = $this->OrdersProduct->where("product_id=".$v['id']." AND ".$sqlwhere)->getfield('order_id',true);
          
            if($order_ids){
             $orders_ids = implode(',', array_unique($order_ids));

            	$data[$k]['fee'] = $this->shoporders->where("id in (".$orders_ids.")")->sum('invoice_money');
            }else{
            	$datalist[$k]['fee']="0";
            }
            $fee+=$datalist[$k]['fee'];
            // $data[$k]['fee']='';
            // $fee+=$data[$k]['fee'];

        }
        $total=array("id"=>'',"category_name"=>'',"product_name"=>'合计',"ordernum"=>$ordernum,"total_price"=>$total_price,"pay_money"=>$pay_money,"fee"=>$fee);
        array_push($data,$total);
        $Title= $this->shop_name."商品汇总列表";
        $Fieldsname=array(array('id','ID'),
            array('category_name',"商品类型"),
            array('product_name',"商品名称"),
            array('ordernum',"消费次数"),
            array('total_price',"金额（原价)"),
            array('pay_money',"优惠"),
            array('fee',"税费"));

        exportExcel($Title,$Fieldsname,$data);

    }
    //商品明细统计列表
    public function productdetailjson()
    {
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $pagenum =!empty($limit) ? $limit :10;
        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $where.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $where.=" and createtime<='".strtotime($enddate)."'";
        }
        $data=$this->OrdersProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->OrdersProduct->where($where)->count();
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $product_reward=0;
        foreach ($data as $k=>$v)
        {
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
            $data[$k]['product_name']=M('ShopProduct')->where(" id=".$v['product_id'])->getField('product_name');
            $total_price+=$v['total_price'];
            $pay_money+=$v['pay_money'];
            //税费计算
           	$data[$k]['fee']=$this->shoporders->where("id=".$v['order_id'])->getField('invoice_money');
           	$fee+=$data[$k]['fee'];
            // $data[$k]['fee']='';
            // $fee+=$data[$k]['fee'];
            $product_reward+=$v['product_reward'];
        }
        $total=array('id'=>'',"createtime"=>'','pay_time'=>'',
            'product_name'=>'合计',
            'total_price'=>$total_price,'pay_money'=>$pay_money,'fee'=>$fee,'masseur_name'=>'','product_reward'=>$product_reward,'payuser'=>'');

        array_push($data,$total);
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$data);
        outJson($data);
    }
    //导出
    public function exportproductdetail()
    {
//        $limit   =I('get.limit');
//        $pagecur=I('get.page');
//        $pagenum =!empty($limit) ? $limit :10;
//        $pagecur = !empty($pagecur)? $pagecur:1;

        $where=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $where.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $where.=" and createtime<='".strtotime($enddate)."'";
        }
       // $datalist=$this->OrdersProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $datalist=$this->OrdersProduct->where($where)->select();
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $product_reward=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['product_name']=M('ShopProduct')->where(" id=".$v['product_id'])->getField('product_name');
            $data[$k]['total_price']=$v['total_price'];
            $total_price+=$v['total_price'];
            $data[$k]['pay_money']=$v['pay_money'];
            $pay_money+=$v['pay_money'];
            //税费计算
           	$data[$k]['fee']=$this->shoporders->where("id=".$v['order_id'])->getField('invoice_money');
           	$fee+=$data[$k]['fee'];
            // $data[$k]['fee']='';
            // $fee+=$data[$k]['fee'];
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['product_reward']=$v['product_reward'];
            $product_reward+=$v['product_reward'];
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
        }
        $total=array('id'=>'',"createtime"=>'','pay_time'=>'',
            'product_name'=>'合计',
            'total_price'=>$total_price,'pay_money'=>$pay_money,'fee'=>$fee,'masseur_name'=>'','product_reward'=>$product_reward,'payuser'=>'');

        array_push($data,$total);
        $Title= $this->shop_name."商品明细列表";
        $Fieldsname=array( array('id','ID'),
            array('createtime',"开单时间"),
            array('pay_time',"记账日期"),
            array('product_name',"商品名称"),
            array('total_price',"商品金额（原价）"),
            array('pay_money',"优惠"),
            array('fee',"税费"),
            array('masseur_name',"服务健康师"),
            array('product_reward',"服务提成"),
            array('payuser',"收银人员"));

        exportExcel($Title,$Fieldsname,$data);
    }
    public function product()
    {
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
		// $individuals = implode(',', $individuals);
		// $member_balance=$this->shoporders->where("shop_id=".$this->shopid." and id in ('".$individuals."')")->sum("pay_amount");
		$member_balance=$this->shopfinancial->where("shop_id=".$this->shopid." and transaction_money < 0 and identity=2")->sum("transaction_money");
		$member_balance=!empty($member_balance) ? (-1)*$member_balance : 0;
		$this->assign("member_balance",$member_balance);
		
		//优惠金额
		$preferential=$this->shoporders->where("shop_id=".$this->shopid." and id in ('".$individuals."')")->sum("preferential_amount");
		$preferential=!empty($preferential) ? $preferential : 0;
		$this->assign("preferential",$preferential);
		//散客人均消费
		$average_consumption = round((-1)*$member_balance/$member_count, 2);
		// $average_consumption =0;
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
			$consumption=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and identity=2")->sum("transaction_money");
			$consumption_money[$i]=!empty($consumption) ? (-1)*$consumption : 0;

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
	//健康师统计
	public function masseur(){
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
		//健康师列表
		$masseur_list = $this->shopmasseur->where("shop_id=".$this->shopid.' and state=1')->select();
		foreach ($masseur_list as $key => $value) {
			//项目原价
			unset($project_price);
			$project_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
			$project_price_data[$key]=!empty($project_price) ? $project_price : 0;
			$project_price_data_count+=$project_price_data[$key];
			//产品原价
			unset($product_price);
			$product_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
			$product_price_data[$key]=!empty($product_price) ? $product_price : 0;
			$product_price_data_count+=$product_price_data[$key];
			//实收金额
			unset($real_price);
			$orderproject_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
			$orderproduct_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
			$real_price = $orderproject_price+$orderproduct_price;
			$real_price_data[$key]=!empty($real_price) ? $real_price : 0;
			$real_price_data_count+=$real_price_data[$key];
			//会员卡售卡/充值金额
			unset($card);
			$card = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$card_data[$key]=!empty($card) ? $card : 0;
			$card_data_count+=$card_data[$key];
			//次卡售卡/充值金额
			unset($numcard);
			$numcard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$numcard_data[$key]=!empty($numcard) ? $numcard : 0;
			$numcard_data_count+=$numcard_data[$key];
			//期限卡售卡/充值金额
			unset($deadlinecard);
			$deadlinecard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$deadlinecard_data[$key]=!empty($deadlinecard) ? $deadlinecard : 0;
			$deadlinecard_data_count+=$deadlinecard_data[$key];
			//疗程卡售卡/充值金额
			$financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time." < createtime  and createtime <".($end_time+24*3600-1)." and type=1 and transaction_type=4 and status=1 and sellcard_masseur=".$value['id'])->select();
			//排序只保留相同card_id的一条数据
			$list = array();
			if(!empty($financials)){
				foreach ($financials as $k=>$vo) {
					$id=intval($vo['card_id']);
				 	$list[$id]=isset($list[$id])?
				 			(strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
				}
			}
			$list=array_values($list);
			$arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
			array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
			if(!empty($list)){
				foreach ($list as $k1 => $v1) {
					$card_cash[$k1] = !empty($v1['transaction_money'])?$v1['transaction_money']:0;
					$card_cash_count[$key]+=$card_cash[$k1];
				}
				
			}
			$coursecard_data[$key]=!empty($card_cash_count[$key]) ? $card_cash_count[$key] : 0;
			$coursecard_data_count+=$coursecard_data[$key];



			// unset($coursecard);
			// $coursecard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			// $coursecard_data[$key]=!empty($coursecard) ? $coursecard : 0;
			// $coursecard_data_count+=$coursecard_data[$key];
			//项目提成
			unset($project_reward);
			$project_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('project_reward');
			$project_reward_data[$key]=!empty($project_reward) ? $project_reward : 0;
			$project_reward_data_count+=$project_reward_data[$key];
			//商品提成
			unset($product_reward);
			$product_reward =$this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('product_reward');
			$product_reward_data[$key]=!empty($product_reward) ? $product_reward : 0;
			$product_reward_data_count+=$product_reward_data[$key];
			//会员卡提成
			unset($card_reward);
			$card_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$card_reward_data[$key]=!empty($card_reward) ? $card_reward : 0;
			$card_reward_data_count+=$card_reward_data[$key];
			//次卡提成
			unset($numcard_reward);
			$numcard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$numcard_reward_data[$key]=!empty($numcard_reward) ? $numcard_reward : 0;
			$numcard_reward_data_count+=$numcard_reward_data[$key];
			//期限卡提成
			unset($deadlinecard_reward);
			$deadlinecard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$deadlinecard_reward_data[$key]=!empty($deadlinecard_reward) ? $deadlinecard_reward : 0;
			$deadlinecard_reward_data_count+=$deadlinecard_reward_data[$key];
			



			//疗程卡提成
			$financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->select();
			// dump($financials);
			//排序只保留相同card_id的一条数据
			$list = array();
			if(!empty($financials)){
				foreach ($financials as $k=>$vo) {
					$id=intval($vo['card_id']);
				 	$list[$id]=isset($list[$id])?
				 			(strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
				}
			}
			$list=array_values($list);
			$arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
			array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
			if(!empty($list)){
				foreach ($list as $k1 => $v1) {
					$coursecard_cash[$k1] = !empty($v1['sellcard_reward'])?$v1['sellcard_reward']:0;
					$coursecard_cash_count[$key]+=$coursecard_cash[$k1];
				}
				
			}
			$coursecard_reward_data[$key]=!empty($coursecard_cash_count[$key]) ? $coursecard_cash_count[$key] : 0;
			$coursecard_reward_data_count+=$coursecard_reward_data[$key];



			// unset($coursecard_reward);
			// $coursecard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			// $coursecard_reward_data[$key]=!empty($coursecard_reward) ? $coursecard_reward : 0;
			// $coursecard_reward_data_count+=$coursecard_reward_data[$key];
			



			//推荐提成
			unset($recommend_reward);
			$recommend_reward = $this->ordersreward->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'])->sum('total_reward');
			$recommend_reward_data[$key]=!empty($recommend_reward) ? $recommend_reward : 0;
			$recommend_reward_data_count+=$recommend_reward_data[$key];

			//奖励提成
			unset($prize_reward);
			$loop_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=1')->sum('loop_reward');
			$point_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=2')->sum('point_reward');
			$add_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=3')->sum('add_reward');
			$prize_reward_data[$key]=$loop_reward+$point_reward+$add_reward;
			$prize_reward_data_count+=$prize_reward_data[$key];
			//总提成
			unset($all_reward_data1);
			$all_reward_data1 = $project_reward_data[$key]+$product_reward_data[$key]+$card_reward_data[$key]+$numcard_reward_data[$key]+$deadlinecard_reward_data[$key]+$coursecard_reward_data[$key]+$recommend_reward_data[$key]+$prize_reward_data[$key];
			$all_reward_data[$key]=!empty($all_reward_data1) ? $all_reward_data1 : 0;
			$all_reward_data_count+=$all_reward_data[$key];

			//轮钟数
			unset($wheel);
			$wheel=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=1 and is_del=0')->count();
			$wheel_data[$key]=!empty($wheel) ? $wheel : 0;
			$wheel_data_count+=$wheel_data[$key];
			//点钟数
			unset($point);
			$point=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=2 and is_del=0')->count();
			$point_data[$key]=!empty($point) ? $point : 0;
			$point_data_count+=$point_data[$key];
			//加钟数
			unset($add);
			$add=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=3 and is_del=0')->count();
			$add_data[$key]=!empty($add) ? $add : 0;
			$add_data_count+=$add_data[$key];
			//总钟数
			unset($all_data1);
			$all_data1 = $wheel_data[$key]+$point_data[$key]+$add_data[$key];
			$all_data[$key]=!empty($all_data1) ? $all_data1 : 0;
			$all_data_count+=$all_data[$key];

		}
		$this->assign("project_price_data",$project_price_data);
		$this->assign("product_price_data",$product_price_data);
		$this->assign("real_price_data",$real_price_data);
		$this->assign("card_data",$card_data);
		$this->assign("numcard_data",$numcard_data);
		$this->assign("deadlinecard_data",$deadlinecard_data);
		$this->assign("coursecard_data",$coursecard_data);
		$this->assign("project_reward_data",$project_reward_data);
		$this->assign("product_reward_data",$product_reward_data);
		$this->assign("card_reward_data",$card_reward_data);
		$this->assign("numcard_reward_data",$numcard_reward_data);
		$this->assign("deadlinecard_reward_data",$deadlinecard_reward_data);
		$this->assign("coursecard_reward_data",$coursecard_reward_data);
		$this->assign("recommend_reward_data",$recommend_reward_data);
		$this->assign("prize_reward_data",$prize_reward_data);
		$this->assign("all_reward_data",$all_reward_data);
		$this->assign("wheel_data",$wheel_data);
		$this->assign("point_data",$point_data);
		$this->assign("add_data",$add_data);
		$this->assign("all_data",$all_data);
		$this->assign("masseur_list",$masseur_list);
		//合计
		$this->assign("project_price_data_count",$project_price_data_count);
		$this->assign("product_price_data_count",$product_price_data_count);
		$this->assign("real_price_data_count",$real_price_data_count);
		$this->assign("card_data_count",$card_data_count);
		$this->assign("numcard_data_count",$numcard_data_count);
		$this->assign("deadlinecard_data_count",$deadlinecard_data_count);
		$this->assign("coursecard_data_count",$coursecard_data_count);
		$this->assign("project_reward_data_count",$project_reward_data_count);
		$this->assign("product_reward_data_count",$product_reward_data_count);
		$this->assign("card_reward_data_count",$card_reward_data_count);
		$this->assign("numcard_reward_data_count",$numcard_reward_data_count);
		$this->assign("deadlinecard_reward_data_count",$deadlinecard_reward_data_count);
		$this->assign("coursecard_reward_data_count",$coursecard_reward_data_count);
		$this->assign("recommend_reward_data_count",$recommend_reward_data_count);
		$this->assign("prize_reward_data_count",$prize_reward_data_count);
		$this->assign("all_reward_data_count",$all_reward_data_count);
		$this->assign("wheel_data_count",$wheel_data_count);
		$this->assign("point_data_count",$point_data_count);
		$this->assign("add_data_count",$add_data_count);
		$this->assign("all_data_count",$all_data_count);
		$this->display('masseur');
	}
	//散客导出
	public function exportguest(){
        $start_time = I('request.start_time');
		$end_time = I('request.end_time');
		
		if($start_time=='' || $end_time=='')
		{
			$start_time=time()-24*3600*6;
			$end_time=time();
		}else
		{
			$start_time=strtotime($start_time);
			$end_time=strtotime($end_time);
		}
        $poor_day=round(($end_time-$start_time)/(24*3600));
        $data = array();
		for($i=0;$i<$poor_day+1;$i++)
		{	
			$data_res=date('Y-m-d',$start_time+$i*3600*24);
			$data[$i]['data'] = $data_res;
			$add_guest_res = $this->shopmember->where("shop_id=".$this->shopid." and ".strtotime($data[$i]['data']).' < createtime  and createtime <'.(strtotime($data[$i]['data'])+24*3600-1).' and identity=2')->count();
			$data[$i]['add_guest'] = !empty($add_guest_res) ? $add_guest_res : 0;
			$add_guest += $data[$i]['add_guest'];
			$consumption_res = $this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($data[$i]['data']).' < createtime  and createtime <'.(strtotime($data[$i]['data'])+24*3600-1)." and transaction_money < 0 and identity=2")->sum("transaction_money");
			$data[$i]['consumption'] = !empty($consumption_res) ? $consumption_res : 0;
			$consumption += $data[$i]['consumption'];
			$preferential_res = $this->shoporders->where("shop_id=".$this->shopid." and ".strtotime($data[$i]['data']).' < createtime  and createtime <'.(strtotime($data[$i]['data'])+24*3600-1)." and id in ('".$individuals."')")->sum("preferential_amount");
			$data[$i]['preferential'] = !empty($preferential_res) ? $preferential_money : 0;
			$preferential += $data[$i]['preferential'];
			
		}
        $total=array('data'=>'合计','add_guest'=>$add_guest,'consumption'=>$consumption,'preferential'=>$preferential);

        array_push($data,$total);
        $Title= $this->shop_name."散客统计列表";
        $Fieldsname=array( array('data','时间'),
            array('add_guest',"新增散客数量"),
            array('consumption',"散客消费金额"),
            array('preferential',"优惠金额"),);

        exportExcel($Title,$Fieldsname,$data);
   
	}

	//健康师导出
	public function exportmasseur(){
        $start_time = I('request.start_time');
		$end_time = I('request.end_time');
		
		if($start_time=='' || $end_time=='')
		{
			$start_time=time()-24*3600*6;
			$end_time=time();
		}else
		{
			$start_time=strtotime($start_time);
			$end_time=strtotime($end_time);
		}
		//健康师列表
		$masseur_list = $this->shopmasseur->where("shop_id=".$this->shopid.' and state=1')->select();
		foreach ($masseur_list as $key => $value) {
			$data[$key]['masseur_name'] = $value['masseur_name'];
			//项目原价
			unset($project_price);
			$project_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
			$data[$key]['project_price_data']=!empty($project_price) ? $project_price : 0;
			$project_price_data_count+=$data[$key]['project_price_data'];
			//产品原价
			unset($product_price);
			$product_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
			$data[$key]['product_price_data']=!empty($product_price) ? $product_price : 0;
			$product_price_data_count+=$data[$key]['product_price_data'];
			//实收金额
			unset($real_price);
			$orderproject_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
			$orderproduct_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
			$real_price = $orderproject_price+$orderproduct_price;
			$data[$key]['real_price_data']=!empty($real_price) ? $real_price : 0;
			$real_price_data_count+=$data[$key]['real_price_data'];
			//会员卡售卡/充值金额
			unset($card);
			$card = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$data[$key]['card_data']=!empty($card) ? $card : 0;
			$card_data_count+=$data[$key]['card_data'];
			//次卡售卡/充值金额
			unset($numcard);
			$numcard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$data[$key]['numcard_data']=!empty($numcard) ? $numcard : 0;
			$numcard_data_count+=$data[$key]['numcard_data'];
			//期限卡售卡/充值金额
			unset($deadlinecard);
			$deadlinecard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$data[$key]['deadlinecard_data']=!empty($deadlinecard) ? $deadlinecard : 0;
			$deadlinecard_data_count+=$data[$key]['deadlinecard_data'];
			//疗程卡售卡/充值金额
			unset($coursecard);
			$coursecard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
			$data[$key]['coursecard_data']=!empty($coursecard) ? $coursecard : 0;
			$coursecard_data_count+=$data[$key]['coursecard_data'];
			//项目提成
			unset($project_reward);
			$project_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('project_reward');
			$data[$key]['project_reward_data']=!empty($project_reward) ? $project_reward : 0;
			$project_reward_data_count+=$data[$key]['project_reward_data'];
			//商品提成
			unset($product_reward);
			$product_reward =$this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('product_reward');
			$data[$key]['product_reward_data']=!empty($product_reward) ? $product_reward : 0;
			$product_reward_data_count+=$data[$key]['product_reward_data'];
			//会员卡提成
			unset($card_reward);
			$card_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$data[$key]['card_reward_data']=!empty($card_reward) ? $card_reward : 0;
			$card_reward_data_count+=$data[$key]['card_reward_data'];
			//次卡提成
			unset($numcard_reward);
			$numcard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$data[$key]['numcard_reward_data']=!empty($numcard_reward) ? $numcard_reward : 0;
			$numcard_reward_data_count+=$data[$key]['numcard_reward_data'];
			//期限卡提成
			unset($deadlinecard_reward);
			$deadlinecard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$data[$key]['deadlinecard_reward_data']=!empty($deadlinecard_reward) ? $deadlinecard_reward : 0;
			$deadlinecard_reward_data_count+=$data[$key]['deadlinecard_reward_data'];
			//疗程卡提成
			unset($coursecard_reward);
			$coursecard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
			$data[$key]['coursecard_reward_data']=!empty($coursecard_reward) ? $coursecard_reward : 0;
			$coursecard_reward_data_count+=$data[$key]['coursecard_reward_data'];
			//推荐提成
			unset($recommend_reward);
			$recommend_reward = $this->ordersreward->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'])->sum('total_reward');
			$data[$key]['recommend_reward_data']=!empty($recommend_reward) ? $recommend_reward : 0;
			$recommend_reward_data_count+=$data[$key]['recommend_reward_data'];

			//奖励提成
			unset($prize_reward);
			$loop_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=1')->sum('loop_reward');
			$point_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=2')->sum('point_reward');
			$add_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=3')->sum('add_reward');
			$data[$key]['prize_reward_data']=$loop_reward+$point_reward+$add_reward;
			$prize_reward_data_count+=$data[$key]['prize_reward_data'];
			//总提成
			unset($all_reward_data1);
			$all_reward_data1 = $project_reward_data[$key]+$product_reward_data[$key]+$card_reward_data[$key]+$numcard_reward_data[$key]+$deadlinecard_reward_data[$key]+$coursecard_reward_data[$key]+$recommend_reward_data[$key]+$prize_reward_data[$key];
			$data[$key]['all_reward_data']=!empty($all_reward_data1) ? $all_reward_data1 : 0;
			$all_reward_data_count+=$data[$key]['all_reward_data'];

			//轮钟数
			unset($wheel);
			$wheel=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=1 and is_del=0')->count();
			$data[$key]['wheel_data']=!empty($wheel) ? $wheel : 0;
			$wheel_data_count+=$data[$key]['wheel_data'];
			//点钟数
			unset($point);
			$point=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=2 and is_del=0')->count();
			$data[$key]['point_data']=!empty($point) ? $point : 0;
			$point_data_count+=$data[$key]['point_data'];
			//加钟数
			unset($add);
			$add=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=3 and is_del=0')->count();
			$data[$key]['add_data']=!empty($add) ? $add : 0;
			$add_data_count+=$data[$key]['add_data'];
			//总钟数
			unset($all_data1);
			$all_data1 = $data[$key]['wheel_data']+$data[$key]['point_data']+$data[$key]['add_data'];
			$data[$key]['all_data']=!empty($all_data1) ? $all_data1 : 0;
			$all_data_count+=$data[$key]['all_data'];

		}





        $total=array('masseur_name'=>'合计','all_data'=>$all_data_count,'project_price_data'=>$project_price_data_count,'product_price_data'=>$product_price_data_count,'real_price_data'=>$real_price_data_count,'card_data'=>$card_data_count,'numcard_data'=>$numcard_data_count,'deadlinecard_data'=>$deadlinecard_data_count,'coursecard_data'=>$coursecard_data_count,'project_reward_data'=>$project_reward_data_count,'product_reward_data'=>$product_reward_data_count,'card_reward_data'=>$card_reward_data_count,'numcard_reward_data'=>$numcard_reward_data_count,'deadlinecard_reward_data'=>$deadlinecard_reward_data_count,'coursecard_reward_data'=>$coursecard_reward_data_count,'recommend_reward_data'=>$recommend_reward_data_count,'prize_reward_data'=>$prize_reward_data_count,'all_reward_data'=>$all_reward_data_count,'wheel_data'=>$wheel_data_count,'point_data'=>$point_data_count,'add_data'=>$add_data_count);

        array_push($data,$total);
        $Title= $this->shop_name."健康师统计列表";
        $Fieldsname=array( array('masseur_name','健康师'),
        	array('all_data',"总钟数"),
            array('project_price_data',"项目原价"),
            array('product_price_data',"产品原价"),
            array('real_price_data',"实收金额"),
            array('card_data',"会员卡售卡"),
            array('numcard_data',"次卡售卡"),
            array('deadlinecard_data',"期限卡售卡"),
            array('coursecard_data',"疗程卡售卡"),
            array('project_reward_data',"项目提成"),
            array('product_reward_data',"商品提成"),
            array('card_reward_data',"会员卡提成"),
            array('numcard_reward_data',"次卡提成"),
            array('deadlinecard_reward_data',"疗程卡提成"),
            array('coursecard_reward_data',"期限卡提成"),
            array('recommend_reward_data',"推荐提成"),
            array('prize_reward_data',"奖励提成"),
            array('all_reward_data',"总提成"),
            array('wheel_data',"轮钟数"),
            array('point_data',"点钟数"),
            array('add_data',"加钟数"),);

        exportExcel($Title,$Fieldsname,$data);
   
	}


}
?>