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
class GuestshowController extends ManagerbaseController {
	
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
		$this->roomcategory=M("RoomCategory"); //房间类型
		$this->shoproom=M("ShopRoom"); //房间表
		$this->shopbed=M("ShopBed"); //床位表
		$this->setting=M("Setting"); //参数管理
		$this->masseurcategory=M("MasseurCategory"); //技师等级
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
    public function index()
	{
        $this->display('index');
    }
	public function get_guest()
	{
		
		$masseur_cate=$this->masseurcategory->where("shop_id=".$this->shopid)->order("sort asc")->select();
		foreach($masseur_cate as &$rowct)
		{
			
		
			$rowct['round_clock']=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and b.state=1 and (a.status=1 or a.status=2 or a.status=3) and a.shop_id=".$this->shopid." and b.category_id=".$rowct['id'])->field("a.masseur_id as masseur_id,a.status as status,a.is_lock as is_lock,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id,b.sex as masseur_sex")->order("a.id asc")->select();

			foreach($rowct['round_clock'] as &$val)
			{
				unset($order_project_info);
				$order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shopid." and is_del=0 and (types=1 or types=4)")->order("down_time desc")->find();
				if($order_project_info)
				{
					$val['down_time']=$order_project_info['down_time'];
				}else
				{
					$val['down_time']=0;
				}
				$val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and (types=1 or types=4) and is_del=0")->count();
				$val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
				$val['project_title']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->getField('title');
				if($val['is_lock']==0)
				{
					unset($order_project);
					unset($order_info);
					$order_project=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->find();
					$order_info=$this->orders->where("id=".$order_project['order_id'])->find();
					$val['room_name']=$this->shoproom->where("id=".$order_info['room_id'])->getField("room_name");
				}
				
			}
			$rowct['arr1'] = array_map(create_function('$n', 'return $n["down_time"];'), $rowct['round_clock']);
			
			array_multisort($rowct['arr1'],SORT_ASC,SORT_NUMERIC,$rowct['round_clock']);
		
		}
		
		
		$cate_list=$this->roomcategory->where("shop_id=".$this->shopid)->order("sort asc")->select();
		foreach($cate_list as &$val)
		{
			$val['room_list']=$this->shoproom->where("shop_id=".$this->shopid." and category_id=".$val['id'])->order("id asc")->select();
			foreach($val['room_list'] as &$rowtt)
			{
				$rowtt['order_info']=$this->orders->where("room_id=".$rowtt['id'])->order("id desc")->find();
				$rowtt['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$rowtt['id'])->count();
				if($rowtt['state']==0)
				{
					$rowtt['project_info']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$rowtt['order_info']['id'])->group("masseur_id")->select();
					foreach($rowtt['project_info'] as &$rowt)
					{
						$rowt['nickname']=$this->shopmasseur->where("id=".$rowt['masseur_id'])->getField("nick_name");
					}
				}
			}
		}
		
		
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['data']=$masseur_cate;
		$data['room_info']=$cate_list;
		outJson($data);
	}
}
?>