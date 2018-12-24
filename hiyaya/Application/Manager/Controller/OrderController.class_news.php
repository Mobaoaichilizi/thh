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
class OrderController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shop=M("Shop"); //店铺信息
		$this->shopuser=M("ShopUser"); //店铺管理员信息
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
		$this->orderspaytype=M("OrdersPaytype"); //支付方式记录
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
	
    public function index()
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
			$val['project_info']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['order_info']['id'])->group("masseur_id")->limit("0,3")->select();
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
		$no_count=0;
		$nopay_count=$this->orders->where("shop_id=".$this->shopid." and status=1")->select();
		foreach($nopay_count as &$val)
		{
			$val['down_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$val['id']." and down_time=0")->count();
			if($val['down_count']==0)
			{
				$no_count=$no_count+1;
			}
		}
		$this->assign("nopay_count",$no_count);
		
		
		//空闲技师位
		$sex_men_count=0;
		$sex_women_count=0;
		$free_masseur_result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.sex as sex")->order("a.id asc")->select();
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
		
        $this->display('index');
    }
	//获取床位列表
	public function bed_list()
	{
		$room_id=I('post.room_id');
		$bed_list=$this->shopbed->where("room_id=".$room_id." and shop_id=".$this->shopid." and state=1 and is_lock=1")->order("sort asc")->select();
		unset($data);
		$data['state']='success';
		$data['info']='信息获取成功！';
		$data['data']=$bed_list;
		outJson($data);
	}
	//开单
	public function add_order()
	{
		$id=I('get.id');
		$shop_lockcard_info=$this->shoplockcard->where("id=".$id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_lockcard_info",$shop_lockcard_info);
		$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		$this->assign('room_list',$room_list);
		$this->display();
	}
	
	//开单
	public function add_room_order()
	{
		$id=I('get.id');
		//锁牌列表
		$shop_lockcard_info=$this->shoplockcard->where("status=1 and shop_id=".$this->shopid." and is_lock=1")->order("sort asc")->select();
		$this->assign("shop_lockcard_info",$shop_lockcard_info);
		//房间
		$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1 and id=".$id)->order("id asc")->find();
		$this->assign('room_list',$room_list);
		//床位列表
		$bed_list=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$room_list['id'])->select();
		$this->assign('bed_list',$bed_list);
		$this->display();
	}
	
	//更改为空净包间
	public function update_free()
	{
		$room_id=I('post.id');
		$result=$this->shoproom->where("id=".$room_id." and state=3")->find();
		if($result)
		{
			$res=$this->shoproom->where("id=".$room_id)->save(array('state' => 1));
			if($res)
			{
				$this->success("更改成功！");
			}else
			{
				$this->error("更改失败！");
			}
		}else
		{
			$this->error("当前房间不在待扫状态！");
		}
	}
	
	//项目列表
	public function get_project()
	{
		$name = I('request.name');
		$where['shop_id']=$this->shopid;
		$where['state']=1;
		if($name){
			$where['item_name'] = array('like',"%$name%");
		}
		$result=$this->shopitem->where($where)->order("id desc")->select();
		$count=$this->shopitem->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//产品列表
	public function get_product()
	{
		$name = I('request.name');
		$where['shop_id']=$this->shopid;
		$where['state']=1;
		if($name){
			$where['product_name'] = array('like',"%$name%");
		}
		$result=$this->shopproduct->where($where)->order("id desc")->select();
		$count=$this->shopproduct->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//推荐提成列表
	public function get_reward()
	{
		$name = I('request.name');
		$where['shop_id']=$this->shopid;
		$where['state']=1;
		if($name){
			$where['reward_name'] = array('like',"%$name%");
		}
		$result=$this->shopreward->where($where)->order("sort asc")->select();
		$count=$this->shopreward->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//项目添加技师
	public function order_addmasseur()
	{
		$project_id=I('project_id');

		$shop_project_info=$this->shopitem->where("id=".$project_id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_project_info",$shop_project_info);
		
		
		//技师等级
		$masseur_level=$this->masseurcategory->where("shop_id=".$this->shopid)->order("sort asc")->select();
		$this->assign("masseur_level",$masseur_level);
		/*
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_orders_project as b on a.masseur_id=b.masseur_id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1")->field("a.masseur_id as masseur_id,a.status as status,b.id as cid,b.down_time,b.is_del,b.types")->order("b.down_time asc,a.id asc")->select();
		// dump($this->shopscheduling->getLastSql());
		// print_r($result);
		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['masseur_id']);
			if($vo['is_del']==0 || $vo['is_del']=='' || $vo['types']==1)
			{
				$list[$id]=isset($list[$id])?($vo['down_time']==$list[$id]['down_time'])?($vo['cid']>$list[$id]['cid'])? $vo:$list[$id] : ($vo['down_time']<$list[$id]['down_time'])?$list[$id] : $vo : $vo;			
			}
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $list);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$list);
		*/
		$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();

		foreach($result as &$val)
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
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		
		$this->assign("result",$result);
		
		$this->display();
	}
	public function get_order_addmasseur()
	{
		

		/*
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_orders_project as b on a.masseur_id=b.masseur_id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1")->field("a.masseur_id as masseur_id,a.status as status,b.id as cid,b.down_time,b.is_del,b.types")->order("b.down_time asc,a.id asc")->select();
		// dump($this->shopscheduling->getLastSql());
		// print_r($result);
		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['masseur_id']);
			if($vo['is_del']==0 || $vo['is_del']=='' || $vo['types']==1)
			{
				$list[$id]=isset($list[$id])?($vo['down_time']==$list[$id]['down_time'])?($vo['cid']>$list[$id]['cid'])? $vo:$list[$id] : ($vo['down_time']<$list[$id]['down_time'])?$list[$id] : $vo : $vo;			
			}
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $list);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$list);
		*/
		
		$project_id=I('project_id');

		$shop_project_info=$this->shopitem->where("id=".$project_id." and shop_id=".$this->shopid)->find();
		$masseur_level_info=$this->masseurcategory->where("shop_id=".$this->shopid)->order("sort asc")->find();
		
		$masseur_name = I('request.name');
		$is_level = I('request.is_level',$masseur_level_info['id'],'intval');
		$is_types = I('request.is_types',1,'intval');
		if($masseur_name){
			$where.=" and b.masseur_name like '%$masseur_name%'";
		}
		if($is_level){
			$where.=" and b.category_id=".$is_level;
		}
		
		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id")->order("a.id asc")->select();
		//echo $this->shopscheduling->getLastSql();
		//echo $this->shopscheduling->getLastSql();
		//$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
		foreach($result as &$val)
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
			//echo $val['category_id'];
			
			$val['masseur_level']=masseur_level($val['category_id']);
			if($is_types==1)
			{
				$val['masseur_sn']="轮钟";
				$val['masseur_name']="轮钟";
				$val['nick_name']="轮钟";
				$val['is_types']=1;
			}
			
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		if($is_types==1)
		{
			$result=array_slice($result,0,$shop_project_info['masseur_num']);
		}
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//项目技师提交
	public function post_shopproject()
	{
		$types=I('post.types');
		$project_id=I('post.project_id');
		$masseur_id=$_POST['masseur_id'];
		$project_name=I('post.project_name');
		$project_price=I('post.project_price');
		$project_duration=I('post.project_duration');
		if($types=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择类型';
			outJson($data);
		}
		if($project_id=='' || $project_name=='' || $project_price=='' || $project_duration=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择产品';
			outJson($data);
		}
		if(count($masseur_id)==0)
		{
			unset($data);
			$data['state']='error';
			$data['info']='最少选择一个技师！';
			outJson($data);
		}
		$masseur_id=implode(',',$masseur_id);
		$result=$this->shopmasseur->field("id,masseur_sn,nick_name,category_id")->where("shop_id=".$this->shopid." and id in (".$masseur_id.")")->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$level_proportion=$this->masseurcategory->where("id=".$val['category_id'])->getField("level_proportion");
			$val['p_id']=$project_id;
			$val['masseur_id']=$val['id'];
			$val['number']=1;
			$val['name']=$project_name;
			$val['price']=$project_price+($project_price*$level_proportion*0.01);
			$val['duration']=$project_duration;
			$val['types']=$types;
			if($types==1)
			{
				$val['types_name']='轮钟';
			}else if($types==2)
			{
				$val['types_name']='点钟';
			}else if($types==3)
			{
				$val['types_name']='加钟';
			}
			$val['p_types']=1;
			$total_price+=$project_price+($project_price*$level_proportion*0.01);
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=count($_POST['masseur_id']);
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	
	
	//给技师追加项目技师提交
	public function post_zadd_shopproject()
	{

		$project_id=I('post.project_id');
		$masseur_id=I('post.masseur_id');
		if($project_id=='' || $masseur_id=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择产品';
			outJson($data);
		}
		//$masseur_id=implode(',',$masseur_id);
		$project_info=$this->shopitem->where("shop_id=".$this->shopid." and id=".$project_id)->find();
		$result=$this->shopmasseur->field("id,masseur_sn,nick_name,category_id")->where("shop_id=".$this->shopid." and id=".$masseur_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$level_proportion=$this->masseurcategory->where("id=".$val['category_id'])->getField("level_proportion");
			$val['p_id']=$project_id;
			$val['masseur_id']=$val['id'];
			$val['number']=1;
			$val['name']=$project_info['item_name'];
			$val['price']=$project_info['item_price']+($project_info['item_price']*$level_proportion*0.01);
			$val['duration']=$project_info['item_duration'];
			$val['p_types']=1;
			$total_price+=$project_info['item_price']+($project_info['item_price']*$level_proportion*0.01);
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=1;
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	
	
	
	//项产品添加技师
	public function order_addproductmasseur()
	{
		$product_id=I('product_id');
		$shop_product_info=$this->shopproduct->where("id=".$product_id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_product_info",$shop_product_info);

		
		$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
	

		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			
		}
		$this->assign("result",$result);
		
		$this->display();
	}
	
	//产品技师提交
	public function post_shopproduct()
	{
		$product_id=I('post.product_id'); //产品ID
		$masseur_id=I('post.masseur_id'); //技师ID
		$number=I('post.number'); //产品数量
		$is_masseur=I("post.is_masseur",0,'intval'); //是否选中技师
		$product_name=I('post.product_name'); //产品名称
		$product_price=I('post.product_price'); //产品价格
		if($number==0)
		{
			unset($data);
			$data['state']='error';
			$data['info']='数量不能为0！';
			outJson($data);
		}
		if($product_id=='' || $product_name=='' || $product_price=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择产品';
			outJson($data);
		}
		if($is_masseur==1)
		{
			if($masseur_id=='')
			{
				unset($data);
				$data['state']='error';
				$data['info']='最少选择一个技师！';
				outJson($data);
			}
			$masseur_sn=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('masseur_sn');
			$nick_name=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('nick_name');
		}else
		{
			$masseur_sn='-';
			$masseur_id=0;
		}
		$result=$this->shopproduct->field("id")->where("shop_id=".$this->shopid." and id=".$product_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$product_id;
			$val['masseur_id']=$masseur_id;
			$val['number']=$number;
			$val['name']=$product_name;
			$val['masseur_sn']=$masseur_sn;
			$val['nick_name']=$nick_name;
			$val['duration']='';
			$val['price']=$product_price*$number;
			$val['p_types']=2;
			$total_price+=$product_price*$number;
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=1;
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	
	//追加产品技师提交
	public function post_zadd_shopproduct()
	{
		$product_id=I('post.product_id'); //产品ID
		$masseur_id=I('post.masseur_id'); //技师ID

		if($product_id=='' || $masseur_id=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择产品';
			outJson($data);
		}
		
		//$product_info=$this->shopproduct->where("shop_id=".$this->shopid." and id=".$product_id)->find();
			
		$masseur_sn=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('masseur_sn');
		$nick_name=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('nick_name');
		
		$result=$this->shopproduct->field("id,product_name,product_price")->where("shop_id=".$this->shopid." and id=".$product_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$product_id;
			$val['masseur_id']=$masseur_id;
			$val['number']=1;
			$val['name']=$val['product_name'];
			$val['masseur_sn']=$masseur_sn;
			$val['nick_name']=$nick_name;
			$val['duration']='';
			$val['price']=$val['product_price'];
			$val['p_types']=2;
			$total_price+=$val['product_price'];
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=1;
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	//提成添加技师
	public function order_addrewardmasseur()
	{
		$reward_id=I('reward_id');
		$shop_reward_info=$this->shopreward->where("id=".$reward_id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_reward_info",$shop_reward_info);

		
		$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
	

		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
		}
		$this->assign("result",$result);
		
		$this->display();
	}
	
	
	//提成技师提交
	public function post_shopreward()
	{
		$reward_id=I('post.reward_id'); //产品ID
		$masseur_id=I('post.masseur_id'); //技师ID
		$number=I('post.number'); //产品数量
		$reward_name=I('post.reward_name'); //产品名称
		$reward_price=I('post.reward_price'); //产品价格
		if($number==0)
		{
			unset($data);
			$data['state']='error';
			$data['info']='数量不能为0！';
			outJson($data);
		}
		if($reward_id=='' || $reward_name=='' || $reward_price=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择产品';
			outJson($data);
		}
		if($masseur_id=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='最少选择一个技师！';
			outJson($data);
		}
		
			
		$masseur_sn=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('masseur_sn');
		$nick_name=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('nick_name');
		$result=$this->shopreward->field("id")->where("shop_id=".$this->shopid." and id=".$reward_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$reward_id;
			$val['masseur_id']=$masseur_id;
			$val['number']=$number;
			$val['name']=$reward_name;
			$val['masseur_sn']=$masseur_sn;
			$val['nick_name']=$nick_name;
			$val['price']=$reward_price*$number;
			$val['p_types']=3;
			$total_price+=$reward_price*$number;
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=1;
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	
	//提成技师提交
	public function post_zadd_shopreward()
	{
		$reward_id=I('post.reward_id'); //产品ID
		$masseur_id=I('post.masseur_id'); //技师ID

		if($reward_id=='' || $masseur_id=='')
		{
			unset($data);
			$data['state']='error';
			$data['info']='请选择推荐提成';
			outJson($data);
		}
			
		$masseur_sn=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('masseur_sn');
		$nick_name=$this->shopmasseur->where("shop_id=".$this->shopid." and id=".$masseur_id)->getField('nick_name');
		$result=$this->shopreward->field("id,reward_name,reward")->where("shop_id=".$this->shopid." and id=".$reward_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$reward_id;
			$val['masseur_id']=$masseur_id;
			$val['number']=1;
			$val['name']=$val['reward_name'];
			$val['masseur_sn']=$masseur_sn;
			$val['nick_name']=$nick_name;
			$val['price']=$val['reward'];
			$val['p_types']=3;
			$total_price+=$val['reward'];
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=1;
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	
	//提交订单
	public function post_orders()
	{
		$chain_id=I('post.chain_id');
		$shop_id=I('post.shop_id');
		$lockcard_id=I('post.lockcard_id');
		$room_id=I('post.room_id');
		$bed_id=I('post.bed_id');
		
		$p_id=$_POST['p_id'];
		$masseur_id=$_POST['masseur_id'];
		$p_types=$_POST['p_types'];
		$types=$_POST['types'];
		$number=$_POST['number'];
		if(empty($chain_id) || empty($shop_id) || empty($room_id) || empty($bed_id))
		{
			$this->error("参数错误！");
		}
		if(count($p_id)==0)
		{
			$this->error("项目、产品、推荐提成不能同时为空");
		}
		unset($data);
		$data=array(
			'chain_id' => $chain_id,
			'shop_id' => $shop_id,
			'order_sn' => 'YS-'.time().substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),
			'shopuser_id' => $this->userid,
			'room_id' => $room_id,
			'bed_id' => $bed_id,
			'lockcard_id' => $lockcard_id,
			'status' => 1,
			'createtime' => time(),
		);
		$this->shoplockcard->where("id=".$lockcard_id." and shop_id=".$this->shopid)->save(array("is_lock" => 0));
		$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
		$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->save(array('state' => 0));
		$result=$this->orders->add($data);
		$pay_total_money=0;
		if($result)
		{
			foreach($p_id as $key=>$val)
			{
				if($p_types[$key]==1)
				{
					$project_info=$this->shopitem->where("id=".$val." and shop_id=".$this->shopid)->find();
					$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->find();
					if(!$masseur_info)
					{
						$this->orders->where("id=".$result)->delete();
						$this->error("此技师已被锁定，请重新选择！");
					}
					
					$masseur_info_level=$this->shopmasseur->where("id=".$masseur_id[$key])->getField("category_id");
					$level_proportion=$this->masseurcategory->where("id=".$masseur_info_level)->getField("level_proportion");
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'scheduling_id' => $masseur_info['id'],
						'project_id' => $val,
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price']+($project_info['item_price']*$level_proportion*0.01),
						'number' => $number[$key],
						'total_price' => ($number[$key]*$project_info['item_price'])+($number[$key]*$project_info['item_price']*$level_proportion*0.01),
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'is_discount' => $project_info['is_discount'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $types[$key],
						'createtime' => time(),
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
					$pay_total_money+=($number[$key]*$project_info['item_price'])+($number[$key]*$project_info['item_price']*$level_proportion*0.01);
				}else if($p_types[$key]==2)
				{
					$product_info=$this->shopproduct->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'product_id' => $val,
						'title' => $product_info['product_name'],
						'unit_price' => $product_info['product_price'],
						'number' => $number[$key],
						'total_price' => $number[$key]*$product_info['product_price'],
						'product_reward' => $product_info['rec_reward'],
						'is_discount' => $product_info['is_discount'],
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
					$pay_total_money+=$number[$key]*$product_info['product_price'];
				}else if($p_types[$key]==3)
				{
					$reward_info=$this->shopreward->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'reward_id' => $val,
						'title' => $reward_info['reward_name'],
						'unit_reward' => $reward_info['reward'],
						'number' => $number[$key],
						'total_reward' => $number[$key]*$reward_info['reward'],
						'createtime' => time(),
					);
					$this->ordersreward->add($reward_data);
				}
			}
			$this->orders->where("id=".$result)->setInc('total_amount',$pay_total_money);
			$this->success("下单成功！");
		}else
		{
			$this->error("下单失败！");
		}
		
	}
	//订单详情
	public function show_order()
	{
		$order_id=I('id');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		$this->assign('order_info',$order_info);
		$room_info=$this->shoproom->where("id=".$order_info['room_id'])->find();
		$this->assign('room_info',$room_info);
		//会员信息
		if($order_info['member_id']!=0)
		{
			$member_info=$this->shopmember->where("shop_id=".$this->shopid." and id=".$order_info['member_id'])->find();
			$this->assign('member_info',$member_info);
		}
		//获取订单项目
		$finish_time=0;
		
		$result_project_payment=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->select();
		foreach($result_project_payment as &$val)
		{
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			
		}
		$this->assign('result_project_payment',$result_project_payment);
		
		$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->group("masseur_id")->select();
		foreach($result_project as &$val)
		{
			$val['project_list']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and id!=".$val['id'])->order("down_time asc")->select();
			$val['project_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->count();
			$val['project_up_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and up_time=0")->count();
			
			
			foreach($val['project_list'] as $rowtt)
			{
				$finish_time+=$rowtt['duration'];
			}
			$finish_time+=$val['duration'];
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');	
		}
		
		
		$this->assign('result_project',$result_project);
		//预计结束时间
		$this->assign('finish_time',$finish_time);
		// 服务开始时间
		$up_start_time=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("up_time asc")->find();
		$this->assign('up_start_time',$up_start_time);
		//订单是否全部上钟
		$up_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time=0")->count();
		$this->assign('up_count',$up_count);
		//全部下钟
		$down_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and down_time=0")->count();
		$this->assign('down_count',$down_count);
		
		//是否有一个上钟
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time > 0")->count();
		$this->assign('is_one_uptime',$is_one_uptime);
		//获取订单产品
		$result_product=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
		foreach($result_product as &$val)
		{
			if($val['masseur_id']!=0)
			{
				$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
				$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			}else
			{
				$val['masseur_name']='-';
				$val['nick_name']='-';
			}
		}
		$this->assign('result_product',$result_product);
		//获取订单提成
		$result_reward=$this->ordersreward->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
		foreach($result_reward as &$val)
		{
			
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			
		}
		//计算支付明细
		$hykzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
		
		$hykzk_pay_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
		$ckzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=2")->sum("total_price");
		$qxkzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=3")->sum("total_price");
		$lckzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=4")->sum("total_price");
		
		$product_hykzk_sum=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
		$product_hykzk_pay_sum=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
		
		$this->assign('hykzk_sum',$hykzk_sum);
		$this->assign('hykzk_pay_sum',$hykzk_pay_sum);
		$this->assign('ckzk_sum',$ckzk_sum);
		$this->assign('qxkzk_sum',$qxkzk_sum);
		$this->assign('lckzk_sum',$lckzk_sum);
		$this->assign('product_hykzk_sum',$product_hykzk_sum);
		$this->assign('product_hykzk_pay_sum',$product_hykzk_pay_sum);
		
		
		$this->assign('result_reward',$result_reward);
		$this->display();
	}
	
	
	//删除推荐提成
	public function del_reward()
	{
		$id = I('post.id',0,'intval');
		if ($this->ordersreward->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}	
	}
	//删除产品
	public function del_product()
	{
		$id = I('post.id',0,'intval');
		$order_product_info=$this->ordersproduct->where("id=".$id)->find();
		$this->orders->where("id=".$order_product_info['order_id'])->setDec('total_amount',$order_product_info['total_price']);
		if ($this->ordersproduct->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}	
	}
	//删除项目
	public function del_project()
	{
		$id = I('post.id',0,'intval');
		$project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->find();
		$count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$project_info['order_id'])->count();
		if($count < 2)
		{
			$this->error("最少要选择一个项目！");
		}
		
		
		$this->orders->where("id=".$project_info['order_id'])->setDec('total_amount',$project_info['total_price']);
		
		if ($this->ordersproject->delete($id)!==false) {
			$is_download=$this->ordersproject->where("order_id=".$project_info['order_id']." and masseur_id=".$project_info['masseur_id']." and down_time <= 0")->count();
			if($is_download == 0)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$project_info['masseur_id']." and id=".$project_info['scheduling_id'])->save(array('is_lock' => 1));
			}
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}	
	}
	//取消订单
	public function cancel_order()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		$result=$this->orders->where("id=".$id." and shop_id=".$this->shopid)->save(array("status" => -1));
		if ($result!==false) {
			$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
			$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
			$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 1));
			$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$this->ordersproduct->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$this->ordersreward->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$orders_project=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->select();
			foreach($orders_project as $val)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and id=".$val['scheduling_id'])->save(array('is_lock' => 1));
			}
			$this->success("取消成功！");
		} else {
			$this->error("取消失败！");
		}	
	}
	
	//开发票
	public function print_invoice()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if($order_info['status']!=2)
		{
			$this->success("您的订单未支付，还不能开发票！");
		}
		$shop_info=$this->shop->where("id=".$this->shopid)->find();
		unset($data_array);
		$data_array=array(
			'is_invoice' => 1,
			'invoice_money' => $order_info['pay_amount']*$shop_info['rate']*0.01,
		);
		$result=$this->orders->where("id=".$id)->save($data_array);
		if($result)
		{
			$this->success("开票成功！");
		}else
		{
			$this->error("开票失败！");
		}
		
		
	}
	
	//全部上钟
	public function all_up()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if($order_info)
		{
			$result=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid." and up_time=0")->save(array('up_time' => time()));
			if($result)
			{
				$this->success("全部上钟成功！");
			}else
			{
				$this->error("上钟失败！");
			}
		}else
		{
			$this->error("订单不存在！");
		}
	}
	//全部下钟
	public function all_down()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if($order_info)
		{
			$orders_project=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid." and down_time=0")->select();
			foreach($orders_project as $val)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and id=".$val['scheduling_id'])->save(array('is_lock' => 1));
			}
			$result=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid." and down_time=0")->save(array('down_time' => time()));
			if($order_info['status']==2)
			{
				$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
				$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3));
			}
			if($result)
			{
				$this->success("全部下钟成功！");
			}else
			{
				$this->error("下钟失败！");
			}
		}else
		{
			$this->error("订单不存在！");
		}
	}
	//单个上钟
	public function one_up_clock()
	{
		$id = I('post.id',0,'intval');
		$order_project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->save(array('up_time' => time()));
		if($order_project_info)
		{	
			$this->success("上钟成功！");		
		}else
		{
			$this->error("订单不存在！");
		}
	}
	//单个下钟
	public function one_down_clock()
	{
		$id = I('post.id',0,'intval');
		$order_project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->find();
		$project_save=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->save(array('down_time' => time()));
		if($order_project_info)
		{
			$is_down_all=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_project_info['order_id']." and masseur_id=".$order_project_info['masseur_id']." and scheduling_id=".$order_project_info['scheduling_id']." and down_time=0")->count();
			if($is_down_all==0)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$order_project_info['masseur_id']." and id=".$order_project_info['scheduling_id'])->save(array('is_lock' => 1));
			}
			$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_project_info['order_id'])->find();
			$down_clock_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_project_info['order_id']." and down_time > 0")->count();
			if($order_info['status']==2 && $down_clock_count==0)
			{
				$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
				$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3));
			}
			$this->success("下钟成功！");		
		}else
		{
			$this->error("订单不存在！");
		}
	}
	//更换包间
	public function replace_room()
	{
		if(IS_POST){
			$room_id=I('post.room_id');
			$bed_id=I('post.bed_id');
			$order_id=I('post.order_id');
			if($order_id=='')
			{
				$this->error("订单不存在！");
			}
			if($bed_id=='')
			{
				$this->error("请选择床位");
			}
			unset($data);
			$data=array(
				'room_id' => $room_id,
				'bed_id' => $bed_id,
			);
			$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
			$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3));
			$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
			$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save($data);
			if($result)
			{
				$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->save(array('state' => 0));
				$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
				$this->success("更新成功！");
			}else
			{
				$this->error("更新失败！");
			}
		}else
		{
			$order_id=I('get.orderid');
			$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
			$this->assign('room_list',$room_list);
			$this->assign('order_id',$order_id);
			$this->display();
		}
	}
	//追加信息
	public function additional_project()
	{
		$orderid=I('get.orderid');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$orderid)->find();
		$this->assign('order_info',$order_info);
		$shop_lockcard_info=$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->find();
		$this->assign("shop_lockcard_info",$shop_lockcard_info);
		$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		$this->assign('room_list',$room_list);
		
		$room_info=$this->shoproom->where("shop_id=".$this->shopid." and id=".$order_info['room_id'])->find();
		$this->assign('room_info',$room_info);
		
		$this->display();
	}
	
	//新追加信息
	public function news_add_project()
	{
		$orderid=I('get.orderid');
		
		$project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$orderid)->find();
		$this->assign('project_info',$project_info);
		
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$project_info['order_id'])->find();
		$this->assign('order_info',$order_info);
		$shop_lockcard_info=$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->find();
		$this->assign("shop_lockcard_info",$shop_lockcard_info);
		$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		$this->assign('room_list',$room_list);
		
		$room_info=$this->shoproom->where("shop_id=".$this->shopid." and id=".$order_info['room_id'])->find();
		$this->assign('room_info',$room_info);
		
		$this->display();
	}
	
	//追加信息提交订单
	public function post_additional_orders()
	{
		$order_id=I('post.order_id');
		$chain_id=I('post.chain_id');
		$shop_id=I('post.shop_id');
		
		$p_id=$_POST['p_id'];
		$masseur_id=$_POST['masseur_id'];
		$p_types=$_POST['p_types'];
		$types=$_POST['types'];
		$number=$_POST['number'];
		if(empty($chain_id) || empty($shop_id) || empty($order_id))
		{
			$this->error("参数错误！");
		}
		if(count($p_id)==0)
		{
			$this->error("项目、产品、推荐提成不能同时为空");
		}
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		$pay_total_money=0;
		if($order_info)
		{
			foreach($p_id as $key=>$val)
			{
				if($p_types[$key]==1)
				{
					$project_info=$this->shopitem->where("id=".$val." and shop_id=".$this->shopid)->find();
					$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->find();
					if(!$masseur_info)
					{
						$this->error("此技师已被锁定，请重新选择！");
					}
					
					unset($project_data);
					
					$masseur_info_level=$this->shopmasseur->where("id=".$masseur_id[$key])->getField("category_id");
					$level_proportion=$this->masseurcategory->where("id=".$masseur_info_level)->getField("level_proportion");
					
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'scheduling_id' => $masseur_info['id'],
						'project_id' => $val,
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price']+($project_info['item_price']*$level_proportion*0.01),
						'number' => $number[$key],
						'total_price' => ($number[$key]*$project_info['item_price'])+($number[$key]*$project_info['item_price']*$level_proportion*0.01),
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'is_discount' => $project_info['is_discount'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $types[$key],
						'createtime' => time(),
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
					$pay_total_money+=($number[$key]*$project_info['item_price'])+($number[$key]*$project_info['item_price']*$level_proportion*0.01);
				}else if($p_types[$key]==2)
				{
					$product_info=$this->shopproduct->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'product_id' => $val,
						'title' => $product_info['product_name'],
						'unit_price' => $product_info['product_price'],
						'number' => $number[$key],
						'total_price' => $number[$key]*$product_info['product_price'],
						'product_reward' => $product_info['rec_reward'],
						'is_discount' => $product_info['is_discount'],
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
					$pay_total_money+=$number[$key]*$product_info['product_price'];
				}else if($p_types[$key]==3)
				{
					$reward_info=$this->shopreward->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'reward_id' => $val,
						'title' => $reward_info['reward_name'],
						'unit_reward' => $reward_info['reward'],
						'number' => $number[$key],
						'total_reward' => $number[$key]*$reward_info['reward'],
						'createtime' => time(),
					);
					$this->ordersreward->add($reward_data);
				}
			}
			$this->orders->where("id=".$order_id)->setInc('total_amount',$pay_total_money);
			$this->success("下单成功！");
		}else
		{
			$this->error("下单失败！");
		}
		
	}
	//选择会员
	public function select_member()
	{
		$order_id=I('orderid');
		$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		if($order_info['status']==-1 || $order_info['status']==2)
		{
			$this->error("此订单不能操作！");
		}
		$this->assign("order_id",$order_id);
		$this->display();
	}
	
	//支付页选择会员
	public function pay_select_member()
	{
		$order_id=I('orderid');
		$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		if($order_info['status']==-1 || $order_info['status']==2)
		{
			$this->error("此订单不能操作！");
		}
		$this->assign("order_id",$order_id);
		$this->display();
	}
	
	//选择会员数据
	public function get_member_list()
	{
		
		$name = I('request.name');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		// $where['shop_id']=$this->shopid;
		$where['chain_id']=$this->chainid;
		$where['identity']=1;
		$where['state']=1;
		if($name){
			$where['member_name'] = array('like',"%$name%");
		}
		$result=$this->shopmember->where($where)->order("id desc")->limit($page.','.$limit)->select();
		$count=$this->shopmember->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//提交信息
	public function post_select_member()
	{
		$order_id=I('post.order_id');
		$member_id=I('post.member_id');
		if(empty($order_id) || empty($member_id))
		{
			$this->error("请选择会员！");
		}
		$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save(array('member_id' => $member_id));
		if($result)
		{
			$this->success("添加成功！");
		}else
		{
			$this->error("添加失败！");
		}
	}
	//取消会员
	public function cancel_member()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("id=".$id." and shop_id=".$this->shopid)->find();
		if($order_info['status']==-1 || $order_info['status']==2)
		{
			$this->error("此订单不能操作！");
		}
		if ($this->orders->where("id=".$id." and shop_id=".$this->shopid)->save(array('member_id' => 0))!==false) {
			$this->success("取消成功！");
		} else {
			$this->error("取消失败！");
		}	
	}
	//去付款
	public function go_pay()
	{
		$order_id=I('get.orderid');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		$this->assign('order_info',$order_info);
		$room_info=$this->shoproom->where("id=".$order_info['room_id'])->find();
		$this->assign('room_info',$room_info);
		
		//会员信息
		if($order_info['member_id']!=0)
		{
			$member_info=$this->shopmember->where("shop_id=".$this->shopid." and id=".$order_info['member_id'])->find();
			$this->assign('member_info',$member_info);
		}
		//获取订单项目
		$finish_time=0;
		$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->select();
		foreach($result_project as &$val)
		{
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			if($order_info['member_id']!=0)
			{
				//计算是否有次卡
				$val['numcard_info']=$this->shopnumcard->where("shop_id=".$this->shopid." and member_id=".$member_info['id']." and project_id=".$val['project_id']." and card_num > 0")->find();
				//计算是否有期限卡
				$val['deadlinecard_info']=$this->shopdeadlinecard->where("shop_id=".$this->shopid." and member_id=".$member_info['id']." and project_id=".$val['project_id']." and day_ceiling > 0 and start_time < ".time()." and end_time > ".time())->find();
				//计算是否有疗程卡
				$val['coursecard_info']=$this->shopcourseproject->where("shop_id=".$this->shopid." and member_id=".$member_info['id']." and project_id=".$val['project_id']." and card_num > 0")->order("id asc")->find();
				
			}
			$finish_time+=$val['duration'];
		}
		$this->assign('result_project',$result_project);
		//预计结束时间
		$this->assign('finish_time',$finish_time);
		// 服务开始时间
		$up_start_time=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("up_time asc")->find();
		$this->assign('up_start_time',$up_start_time);
		//订单是否全部上钟
		$up_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time=0")->count();
		$this->assign('up_count',$up_count);
		//全部下钟
		$down_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and down_time=0")->count();
		$this->assign('down_count',$down_count);
		
		//是否有一个上钟
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time > 0")->count();
		$this->assign('is_one_uptime',$is_one_uptime);
		//获取订单产品
		$result_product=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
		foreach($result_product as &$val)
		{
			if($val['masseur_id']!=0)
			{
				$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			}else
			{
				$val['masseur_name']='-';
			}
		}
		$this->assign('result_product',$result_product);
		
		//支付列表
		$pay_typest=$this->setting->where("parentid=1 and status=1")->order("sort asc")->select();
		$this->assign('pay_typest',$pay_typest);
		
		$this->display();
		
	}
	//去支付
	public function post_pay_order()
	{
		$order_id=$_POST['order_id'];  //订单ID
		$pro_id=$_POST['pro_id'];  //项目产品ID
		$pro_order_id=$_POST['pro_order_id'];  //项目产品订单里面的ID
		$pro_type=$_POST['pro_type']; //类型 1：项目 2：产品
		$types=$_POST['types']; //优惠类型 0：无优惠 1：会员卡折扣 2：次卡抵扣 3：期限卡抵扣 4：疗程卡
		$card_id=$_POST['card_id']; //卡的ID
		$pay_money_deal=I('post.pay_money_deal'); //整单优惠
		$pay_types=$_POST['pay_types']; //支付类型
		$note=I('post.note'); //备注
		$total_money=0;
		$pay_total=0;
		$order_info_find=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		foreach($pro_id as $key=>$row)
		{
			if($pro_type[$key]==1)
			{
				$project_order_total=$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
				if($types[$key]==0)
				{
					$pay_total+=$project_order_total['total_price'];
				}else if($types[$key]==1)
				{
					$member_info=$this->shopmember->where("id=".$card_id[$key])->find();
					$pay_total+=$project_order_total['total_price']*$member_info['discount']*0.1;
				}else if($types[$key]==2)
				{
					$pay_total+=0;
				}else if($types[$key]==3)
				{
					$pay_total+=0;
				}else if($types[$key]==4)
				{
					$pay_total+=0;
				}
				$total_money+=$project_order_total['total_price'];
			}else if($pro_type[$key]==2)
			{
				$product_order_total=$this->ordersproduct->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
				if($types[$key]==0)
				{
					$pay_total+=$product_order_total['total_price'];
				}else if($types[$key]==1)
				{
					$member_info=$this->shopmember->where("id=".$card_id[$key])->find();
					$pay_total+=$product_order_total['total_price']*$member_info['discount']*0.1;
				}
				$total_money+=$product_order_total['total_price'];
			}
			
			
			if($types[$key] == '2'){
				//判断是否有相同产品，统计此产品的次卡抵扣次数
				if(!in_array($row,$numcard_project['project_id'])){
					$numcard_project['project_id'][] = $row;
					$numcard_project['count'][$row] = 1;
				}else{
					$numcard_project['count'][$row] = (int)$numcard_project['count'][$row] + 1;
					//查询此产品的剩余次卡抵扣次数
					$project_numcard_number = $this->shopnumcard->where('id='.$card_id[$key])->getField('card_num');
					if($numcard_project['count'][$row] > $project_numcard_number){
						$this->error('您的次卡已经剩余'.$project_numcard_number.'次！');
					}
				}
			}else if($types[$key] == '3'){
				if(!in_array($row,$qxkdk_project['project_id'])){
					$qxkdk_project['project_id'][] = $row;
					$qxkdk_project['count'][$row] = 1;
				}else{
					$qxkdk_project['count'][$row] = (int)$qxkdk_project['count'][$row] + 1;
				}
				$qxkxf_info=$this->shopfinancial->where("project_id=".$row." and shop_id=".$this->shopid." and card_id=".$card_id[$key]." and createtime > ".strtotime(date("Y-m-d",time()))." and createtime < ".strtotime(date("Y-m-d 23:23:59",time())))->count();
				$project_qxkdk_number = $this->shopdeadlinecard->where('id='.$card_id[$key])->getField('day_ceiling');
				if($project_qxkdk_number <= $qxkxf_info){
					$this->error('您今天的使用次数已经超限！');
				}
				if($qxkdk_project['count'][$row] > $project_qxkdk_number){
					$this->error('期限卡抵扣已经超出单日使用上限！');
				}
			}else if($types[$key] == '4'){
				if(!in_array($row,$lckdk_project['project_id'])){
					$lckdk_project['project_id'][] = $row;
					$lckdk_project['count'][$row] = 1;
				}else{
					$lckdk_project['count'][$row] = (int)$lckdk_project['count'][$row] + 1;
					//查询此产品的剩余次卡抵扣次数
					$project_lckdk_number = $this->shopcourseproject->where('id='.$card_id[$key])->getField('card_num');
					if($lckdk_project['count'][$row] > $project_lckdk_number){
						$this->error('您的疗程卡已经剩余'.$project_lckdk_number.'次！');
					}
				}
			}
		}
		//dump($numcard_project);
		//计算应收明细
		$pay_type_deal=0;
		foreach($pay_types as $key=>$val)
		{
			if($val==2)
			{
				$member_balance=$this->shopmember->where("id=".$order_info_find['member_id'])->find();
				if($_POST['pay_money_deal_'.$val] > $member_balance['balance'])
				{
					$this->error("您会员卡余额不足！");
				}
				$this->shopmember->where("id=".$order_info_find['member_id'])->setDec('balance',$_POST['pay_money_deal_'.$val]);
			}
			$pay_type_deal+=$_POST['pay_money_deal_'.$val];
		}
		//欠款
		$arrears_moeny=round($pay_total-($pay_type_deal+$pay_money_deal),2);
	
		if($arrears_moeny!=0)
		{
			$this->error("金额不对！");
		}
		
		foreach($pro_id as $key=>$row)
		{
			if($pro_type[$key]==1)
			{
				$project_order_total=$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
				if($types[$key]==0)
				{
					//无任何折扣
					unset($project_data);
					$project_data=array(
						'card_type' => 0,
						'card_id' => 0,
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $project_order_total['total_price'],
					);
					$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($project_data);
				}else if($types[$key]==1)
				{
					//会员卡打折
					$member_info=$this->shopmember->where("id=".$card_id[$key])->find();
					
					unset($project_data);
					$project_data=array(
						'card_type' => 1,
						'card_id' => $card_id[$key],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $project_order_total['total_price']*$member_info['discount']*0.1,
					);
					$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($project_data);
					
				}else if($types[$key]==2)
				{
					//次卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 2,
						'card_id' => $card_id[$key],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($project_data);
					unset($numcard_info);
					$numcard_info=$this->shopnumcard->where("id=".$card_id[$key]." and member_id=".$order_info_find['member_id'])->find();
					unset($order_project_info);
					$order_project_info=$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $pro_order_id[$key],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $card_id[$key],
						'identity' => 1,
						'transaction_type' => 2,
						'transaction_money' => '-'.$numcard_info['average'],
						'transaction_num' =>1,
						'now_money' => $numcard_info['numcard_money']-$numcard_info['average'],
						'shopuser_id' => $this->userid,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					if($numcard_info['numcard_money']-$numcard_info['average'] > 0)
					{
						$this->shopnumcard->where("id=".$card_id[$key])->save(array('card_num' => $numcard_info['card_num']-1,'numcard_money' => $numcard_info['numcard_money']-$numcard_info['average']));
					}else
					{
						$this->shopnumcard->where("id=".$card_id[$key])->save(array('card_num' => $numcard_info['card_num']-1,'numcard_money' => 0));
					}
				}else if($types[$key]==3)
				{
					//期限卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 3,
						'card_id' => $card_id[$key],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($project_data);
					unset($order_project_info);
					$order_project_info=$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $pro_order_id[$key],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $card_id[$key],
						'identity' => 1,
						'transaction_type' => 3,
						'transaction_money' => 0,
						'transaction_num' =>1,
						'now_money' => 0,
						'shopuser_id' => $this->userid,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					
					
				}else if($types[$key]==4)
				{
					//疗程卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 4,
						'card_id' => $card_id[$key],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($project_data);
					
					unset($coursecard_info);
					$courseproject_info=$this->shopcourseproject->where("id=".$card_id[$key]." and member_id=".$order_info_find['member_id'])->find();
					$coursecard_info=$this->shopcoursecard->where("id=".$courseproject_info['card_id']." and member_id=".$order_info_find['member_id'])->find();
					
					unset($order_project_info);
					$order_project_info=$this->ordersproject->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
					
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $pro_order_id[$key],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $courseproject_info['card_id'],
						'courseproject_id' => $card_id[$key],
						'identity' => 1,
						'transaction_type' => 4,
						'transaction_money' => '-'.$coursecard_info['average'],
						'transaction_num' =>1,
						'now_money' => $coursecard_info['card_money']-$coursecard_info['average'],
						'shopuser_id' => $this->userid,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					
					$this->shopcourseproject->where("id=".$card_id[$key])->save(array('card_num' => $courseproject_info['card_num']-1));
					$this->shopcoursecard->where("id=".$coursecard_info['id'])->save(array('card_money' => $coursecard_info['card_money']-$coursecard_info['average']));
			
				}
			}else if($pro_type[$key]==2)
			{
				$product_order_total=$this->ordersproduct->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->find();
				if($types[$key]==0)
				{
					unset($product_data);
					$product_data=array(
						'card_type' => 0,
						'card_id' => 0,
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $product_order_total['total_price'],
					);
					$this->ordersproduct->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($product_data);
				}else if($types[$key]==1)
				{
					$member_info=$this->shopmember->where("id=".$card_id[$key])->find();
					unset($product_data);
					$product_data=array(
						'card_type' => 1,
						'card_id' => $card_id[$key],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $product_order_total['total_price']*$member_info['discount']*0.1,
					);
					$this->ordersproduct->where("id=".$pro_order_id[$key]." and shop_id=".$this->shopid)->save($product_data);
				}
			}
		}
		unset($data);
		$data=array(
			'pay_amount' => $pay_total,
			'preferential_amount' => $pay_money_deal,
			'status' => 2,
			'payuser_id' => $this->userid,
			'note' => $note,
			'pay_time' => time(),
		);
		$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save($data);
		if($result)
		{
			
			foreach($pay_types as $key=>$val)
			{
				
				unset($pay_type_money);
				$pay_type_money=array(
					'chain_id' => $this->chainid,
					'shop_id' => $this->shopid,
					'order_id' => $order_id,
					'payuser_id' => $this->userid,
					'pay_typeid' => $val,
					'pay_money' => $_POST['pay_money_deal_'.$val],
					'pay_time' => time(),
				);
				$this->orderspaytype->add($pay_type_money);
			}
			
			$project_down_count=$this->ordersproject->where("order_id=".$order_id." and shop_id=".$this->shopid." and down_time=0")->count();
			if($project_down_count == 0)
			{
				$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
				$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3));
				$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
			}
			$this->success("付款成功！");
		}else
		{
			$this->error("付款失败！");
		}
	}
	//订单列表
	public function order_lists()
	{
		$this->display();
	}
	//订单列表信息
	public function get_order_lists()
	{
		$order_sn = I('request.order_sn');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['shop_id']=$this->shopid;
		$where['status']=array('NEQ',-2);
		if($order_sn){
			$where['order_sn'] = array('like',"%$order_sn%");
		}
		$result=$this->orders->where($where)->order("createtime desc")->limit($page.','.$limit)->select();
		//echo $this->orders->getLastSql();
		foreach($result as &$val)
		{
			$val['createtime']=date("Y-m-d H:i:s",$val['createtime']);
			if($val['member_id']!=0)
			{
				$val['member_name']=$this->shopmember->where("id=".$val['member_id'])->getField("member_name");
			}else
			{
				$val['member_name']="散客";
			}
			$val['room_name']=$this->shoproom->where("id=".$val['room_id'])->getField("room_name");
		}
		$count=$this->orders->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//删除订单
	public function order_list_del()
	{
		$id = I('post.id',0,'intval');
		$result=$this->orders->where("id=".$id." and shop_id=".$this->shopid)->save(array("status" => -2));
		if ($result!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	
	//更换技师
	public function update_master()
	{
		$project_id=I('projectid');
		$this->assign("project_id",$project_id);
		$this->display();
	}
	
	
	//选择会员数据
	public function get_update_master()
	{
		
		$masseur_name = I('request.name');
		if($masseur_name){
			$where.=" and b.masseur_name like '%$masseur_name%'";
		}
	
		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id")->order("a.id asc")->select();
		//echo $this->shopscheduling->getLastSql();
		//$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
		foreach($result as &$val)
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
			$val['masseur_level']=masseur_level($val['category_id']);
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	//提交信息
	public function post_update_master()
	{
		$project_id=I('post.project_id');
		$masseur_id=I('post.masseur_id');
		if(empty($project_id) || empty($masseur_id))
		{
			$this->error("请选择技师！");
		}
		$order_project_info=$this->ordersproject->where("id=".$project_id." and shop_id=".$this->shopid)->find();
		
		
		
		$this->shopscheduling->where("shop_id=".$this->shopid." and id=".$order_project_info['scheduling_id'])->save(array('is_lock' => 1));
		$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id." and start_time < ".time()." and end_time > ".time())->find();
		
		$result=$this->ordersproject->where("order_id=".$order_project_info['order_id']." and masseur_id=".$order_project_info['masseur_id']." and shop_id=".$this->shopid)->save(array('masseur_id' => $masseur_id,'scheduling_id' => $masseur_info['id']));
		if($result)
		{
			$this->shopscheduling->where("shop_id=".$this->shopid." and id=".$masseur_info['id'])->save(array('is_lock' => 0));
			$this->success("更换成功！");
		}else
		{
			$this->error("更换失败！");
		}
	}
	//打印清单
	public function print_listing()
	{
		$order_id=I('id');
		$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		if($order_info)
		{
			$shop_info=$this->shop->where("id=".$this->shopid)->find();
			$this->assign('shop_info',$shop_info);
			$this->assign("order_info",$order_info);
			
			$room_info=$this->shoproom->where("id=".$order_info['room_id'])->find();
			$this->assign('room_info',$room_info);
			
			
			if($order_info['member_id']!=0)
			{
				$member_info=$this->shopmember->where("shop_id=".$this->shopid." and id=".$order_info['member_id'])->find();
				
				$member_info['numcard']=$this->shopnumcard->where("member_id=".$member_info['id']." and shop_id=".$this->shopid." and card_num > 0")->select();
				//$member_info['coursecard']=$this->shopcoursecard->where("member_id=".$member_info['id']." and shop_id=".$this->shopid)->select();
				
				$member_info['coursecardproject']=$this->shopcourseproject->where("member_id=".$member_info['id']." and shop_id=".$this->shopid." and card_num > 0")->select();
				
				$member_info['deadlinecard']=$this->shopdeadlinecard->where("member_id=".$member_info['id']." and shop_id=".$this->shopid." and start_time < ".time()." and end_time > ".time())->select();
				
				

				$this->assign('member_info',$member_info);
			}
			//print_r($member_info);
			//获取订单项目
			
			$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->select();
			foreach($result_project as &$val)
			{
				$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
				$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
				
			}
			$this->assign('result_project',$result_project);
			
			
			//获取订单产品
			$result_product=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
			foreach($result_product as &$val)
			{
				if($val['masseur_id']!=0)
				{
					$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
					$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
				}else
				{
					$val['masseur_name']='-';
					$val['nick_name']='-';
				}
			}
			$this->assign('result_product',$result_product);
			//获取订单提成
			$result_reward=$this->ordersreward->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
			foreach($result_reward as &$val)
			{
				
				$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
				$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
				
			}
			//计算支付明细
			$hykzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
			
			$hykzk_pay_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
			$ckzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=2")->sum("total_price");
			$qxkzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=3")->sum("total_price");
			$lckzk_sum=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=4")->sum("total_price");
			
			$product_hykzk_sum=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
			$product_hykzk_pay_sum=$this->ordersproduct->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
			
			$this->assign('hykzk_sum',$hykzk_sum);
			$this->assign('hykzk_pay_sum',$hykzk_pay_sum);
			$this->assign('ckzk_sum',$ckzk_sum);
			$this->assign('qxkzk_sum',$qxkzk_sum);
			$this->assign('lckzk_sum',$lckzk_sum);
			$this->assign('product_hykzk_sum',$product_hykzk_sum);
			$this->assign('product_hykzk_pay_sum',$product_hykzk_pay_sum);
			
			$order_pay_list=$this->orderspaytype->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->select();
			$this->assign('order_pay_list',$order_pay_list);
			
			$user_info_login=$this->shopuser->where("id=".$this->userid." and shop_id=".$this->shopid)->find();
			$this->assign('user_info_login',$user_info_login);
			
			
			$this->display();
		}else
		{
			$this->error("订单不存在！");
		}
	}
	
}
?>