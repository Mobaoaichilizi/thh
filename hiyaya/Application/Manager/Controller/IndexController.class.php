<?php
// +----------------------------------------------------------------------
// | 后台首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class IndexController extends ManagerbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->shoplockcard = D("shopLockcard"); //锁牌
		$this->shopitem=M("ShopItem"); //项目列表
		$this->shopproduct=M("ShopProduct"); //产品列表
		$this->shopreward=M("ShopReward"); //推荐提成
		$this->shopscheduling=M("ShopScheduling"); //排班表
		$this->shopmasseur=M("ShopMasseur"); //技师表
		$this->orders=M("Orders"); //订单表
		$this->ordersproject=M("OrdersProject"); //订单项目表
		$this->ordersproduct=M("OrdersProduct"); //订单产品表
		$this->ordersreward=M("OrdersReward"); //订单提成表
		$this->roomcategory=M("RoomCategory"); //房间类型
		$this->shoproom=M("ShopRoom"); //房间表
		$this->shopbed=M("ShopBed"); //床位表
		$this->shopmember=M("ShopMember"); //会员表
		$this->shopnumcard=M("ShopNumcard"); //次卡
		$this->shopdeadlinecard=M("ShopDeadlinecard"); //期限卡
		$this->shopcoursecard=M("ShopCoursecard"); //疗程卡
		$this->shopcourseproject=M("ShopCourseproject"); //疗程卡对应的项目
		$this->setting=M("Setting"); //参数管理
		$this->shopfinancial=M("ShopFinancial"); //消费记录
		$this->masseurcategory=M("MasseurCategory"); //技师等级
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
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
	{
		//空闲锁牌
		$free_lockcard_list=$this->shoplockcard->where("is_lock=1 and shop_id=".$this->shopid." and status=1")->order("sort asc")->select(); //未锁定
		//$unfree_lockcard_list=$this->shoplockcard->where("is_lock=0 and shop_id=".$this->shopid)->order("sort asc")->select(); //锁定
		$this->assign('free_lockcard_list',$free_lockcard_list);
		//订单列表
		$order_list=$this->orders->where("shop_id=".$this->shopid." and (status=1 or status=2)")->order("createtime desc")->select();
		$this->assign('order_list',$order_list);
		//空闲房间
		$free_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->count();
		$free_room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		foreach($free_room_list as &$val)
		{
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			$val['cate_name']=$this->roomcategory->where("id=".$val['category_id'])->getField("category_name");
		}
		$this->assign('free_room_count',$free_room_count);
		$this->assign('free_room_list',$free_room_list);
		
		
		//锁定房间
		$lock_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=0")->count();
		$lock_room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=0")->order("id asc")->select();
		foreach($lock_room_list as &$val)
		{
			unset($val['order_info']);
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			$val['cate_name']=$this->roomcategory->where("id=".$val['category_id'])->getField("category_name");
			
			$val['order_info']=$this->orders->where("room_id=".$val['id'])->order("id desc")->find();
			
			//项目列表
			$val['project_info']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id'])->select();
			foreach($val['project_info'] as &$rowt)
			{
				$rowt['nickname']=$this->shopmasseur->where("id=".$rowt['masseur_id'])->getField("nick_name");
			}
			//全部上钟
			$val['up_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id']." and up_time=0")->count();
			//全部下钟
			$val['down_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id']." and down_time=0")->count();
			
			$val['nopay_money']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id']." and is_del=0")->sum("total_price")+$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id']." and is_del=0")->sum("total_price");
			
		}
		$this->assign('lock_room_count',$lock_room_count);
		$this->assign('lock_room_list',$lock_room_list);
		
		
		//待扫房间
		$sweep_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=3")->count();
		$sweep_room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=3")->order("id asc")->select();
		foreach($sweep_room_list as &$val)
		{
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			$val['cate_name']=$this->roomcategory->where("id=".$val['category_id'])->getField("category_name");
		}
		$this->assign('sweep_room_count',$sweep_room_count);
		$this->assign('sweep_room_list',$sweep_room_list);
		
		
		//维修房间
		$maintenance_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=4")->count();
		$maintenance_room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=4")->order("id asc")->select();
		foreach($maintenance_room_list as &$val)
		{
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			$val['cate_name']=$this->roomcategory->where("id=".$val['category_id'])->getField("category_name");
		}
		$this->assign('maintenance_room_count',$maintenance_room_count);
		$this->assign('maintenance_room_list',$maintenance_room_list);
		
		
		$round_clock=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid)->field("a.masseur_id as masseur_id,a.status as status,a.is_lock as is_lock,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id,b.sex as masseur_sex")->order("a.id asc")->select();

		//$round_clock=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
		foreach($round_clock as &$val)
		{
			unset($order_project_info);
			$order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shopid." and is_del=0 and types=1")->order("down_time desc")->find();
			if($order_project_info)
			{
				$val['down_time']=$order_project_info['down_time'];
			}else
			{
				$val['down_time']=0;
			}
			//$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			//$val['masseur_sex']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('sex');
			//$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			//$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			//$val['level_name']=$this->masseurcategory->where("id=".$val['masseur_level'])->getField("category_name");
			//$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=1 and is_del=0")->count();
			$val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
			$val['project_title']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("down_time desc")->getField('title');
			
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $round_clock);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$round_clock);
		$this->assign("round_clock",$round_clock);
		
		//包间总数
		$room_count=$this->shoproom->where("shop_id=".$this->shopid)->count();
		$this->assign("room_count",$room_count);
		
		//待收银包间数
		$nopay_count=$this->orders->where("shop_id=".$this->shopid." and status=1")->count();
		$this->assign("nopay_count",$nopay_count);
		
		
		//空闲技师位
		$sex_men_count=0;
		$sex_women_count=0;
		$free_masseur_result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 and b.state=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.sex as sex")->order("a.id asc")->select();
		foreach($free_masseur_result as $rowc)
		{
			if($rowc['sex']=='男')
			{
				$sex_men_count+=1;
			}else if($rowc['sex']=='女')
			{
				$sex_women_count+=1;
			}	
		}
		$this->assign("sex_men_count",$sex_men_count);
		$this->assign("sex_women_count",$sex_women_count);
		
		
		$this->display('welcome');
	}

}
?>