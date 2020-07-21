<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class OrderController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shoplockcard = D("shopLockcard"); //锁牌
		$this->itemcategory=M("ItemCategory"); //项目分类
		$this->shopitem=M("ShopItem"); //项目列表
		$this->shopproduct=M("ShopProduct"); //产品列表
		$this->productcategory=M("ProductCategory"); //产品分类
		$this->shopreward=M("ShopReward"); //推荐提成
		$this->shopscheduling=M("ShopScheduling"); //排班表
		$this->shopmasseur=M("ShopMasseur"); //技师表
		$this->orders=M("Orders"); //订单表
		$this->ordersproject=M("OrdersProject"); //订单项目表
		$this->ordersproduct=M("OrdersProduct"); //订单产品表
		$this->ordersreward=M("OrdersReward"); //订单提成表
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
		$this->shoprole=M("ShopRole"); //商家权限
        $this->shopuser=M("ShopUser"); //商家管理员
		$this->smscode=M("Smscode"); //短信内容
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
		$this->userid=$this->user_id;
	}
	
    public function index()
	{
		
		//空闲锁牌
		$free_lockcard_list=$this->shoplockcard->where("is_lock=1 and shop_id=".$this->shopid." and status=1")->order("sort asc")->select(); //未锁定
		//$unfree_lockcard_list=$this->shoplockcard->where("is_lock=0 and shop_id=".$this->shopid)->order("sort asc")->select(); //锁定
		//$this->assign('free_lockcard_list',$free_lockcard_list);
		//订单列表
		$order_list=$this->orders->where("shop_id=".$this->shopid." and (status=1 or status=2)")->order("createtime desc")->select();
		//$this->assign('order_list',$order_list);
		//空闲房间
		$free_room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		foreach($free_room_list as &$val)
		{
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
		}
		//$this->assign('free_room_list',$free_room_list);
		//$this->assign('unfree_lockcard_list',$unfree_lockcard_list);
		//轮钟排序
		//$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.category_id as category_id")->order("a.id asc")->select();
		//echo $this->shopscheduling->getLastSql();
		//$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$round_clock=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		foreach($round_clock as &$val)
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
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $round_clock);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$round_clock);
		//$this->assign("round_clock",$round_clock);
		
		
		$free_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->count();
		$use_room_count=$this->shoproom->where("shop_id=".$this->shopid." and state=0")->count();
		$free_masseur_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
		$use_masseur_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=0")->count();
		
		
		
		unset($data);
		$data['code']=0;
		$data['msg']='信息获取成功！';
		$data['free_room_count']=$free_room_count;
		$data['use_room_count']=$use_room_count;
		$data['free_masseur_count']=$free_masseur_count;
		$data['use_masseur_count']=$use_masseur_count;
		outJson($data);
		
		
        //$this->display('index');
    }
	//获取床位列表
	public function bed_list()
	{
		$room_id=I('post.room_id');
		if(empty($room_id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="房间不存在！";
			outJson($data);
		}
		$bed_list=$this->shopbed->field("id,bed_no")->where("room_id=".$room_id." and shop_id=".$this->shopid." and state=1 and is_lock=1")->order("sort asc")->select();
		if($bed_list)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='信息获取成功！';
			$data['data']=$bed_list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='没有床位了！';
			$data['data']=array();
			outJson($data);
		}
		
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
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="房间不存在！";
			outJson($data);
		}
		//锁牌列表
		$shop_lockcard_info=$this->shoplockcard->field("id,card_number")->where("status=1 and shop_id=".$this->shopid." and is_lock=1")->order("sort asc")->select();
		//$this->assign("shop_lockcard_info",$shop_lockcard_info);
		//房间
		$room_list=$this->shoproom->where("shop_id=".$this->shopid." and state=1 and id=".$id)->order("id asc")->find();
		//$this->assign('room_list',$room_list);
		//床位列表
		$bed_list=$this->shopbed->field("id,bed_no")->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$room_list['id'])->select();
		//$this->assign('bed_list',$bed_list);
		
		if($bed_list)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='信息获取成功！';
			$data['data']=$bed_list;
			$data['shop_lockcard_info']=$shop_lockcard_info;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='没有床位了！';
			$data['data']=array();
			outJson($data);
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
		$result=$this->shopitem->field("id,item_sn,item_name,item_price,item_duration,cover")->where($where)->order("id desc")->select();
		foreach($result as &$val)
		{
			$val['cover']=$this->host_url.$val['cover'];
			$val['order_number']=10;
		}
		$count=$this->shopitem->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	
	//带分类项目列表
	public function cate_project()
	{
		$masseur_id=I('post.masseur_id');
		
		$cate_list=$this->itemcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		foreach($cate_list as &$val)
		{
			if(!empty($masseur_id))
			{
				$masseur_info=$this->shopmasseur->where("id=".$masseur_id)->find();
				$where=" and id in (".$masseur_info['master_item'].")";
			}
			$val['project_list']=$this->shopitem->field("id,item_sn,item_name,item_price,item_duration,cover")->where("shop_id=".$this->shopid." and category_id=".$val['id'].$where)->order("id desc")->select();
			foreach($val['project_list'] as $rowcc)
			{
				$rowcc['cover']=$this->host_url.$rowcc['cover'];
			}
		}
		if($cate_list)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=$cate_list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=array();
			outJson($data);
		}
		
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
		$result=$this->shopproduct->field("id,product_sn,product_name,product_price,cover")->where($where)->order("id desc")->select();
		foreach($result as &$val)
		{
			$val['cover']=$this->host_url.$val['cover'];
		}
		$count=$this->shopproduct->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	
	
	//带分类产品列表
	public function cate_product()
	{
		$cate_list=$this->productcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		foreach($cate_list as &$val)
		{
			$val['product_list']=$this->shopproduct->field("id,product_sn,product_name,product_price,cover")->where("shop_id=".$this->shopid." and category_id=".$val['id'])->order("id desc")->select();
			foreach($val['product_list'] as $rowcc)
			{
				$rowcc['cover']=$this->host_url.$rowcc['cover'];
			}
		}
		if($cate_list)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=$cate_list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=array();
			outJson($data);
		}
		
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
		$result=$this->shopreward->field("id,reward_name,reward")->where($where)->order("sort asc")->select();
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
			$order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shopid." and is_del=0 and (types=1 or types=4)")->order("down_time desc")->find();
			if($order_project_info)
			{
				$val['down_time']=$order_project_info['down_time'];
			}else
			{
				$val['down_time']=0;
			}
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			$val['level_proportion']=$this->masseurcategory->where("id=".$val['masseur_level'])->getField("level_proportion");
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		$this->assign("result",$result);
		
		$this->display();
	}
	public function get_order_addmasseur()
	{		

		$project_id=I('project_id');

		$shop_project_info=$this->shopitem->where("id=".$project_id." and shop_id=".$this->shopid)->find();
		
		
		$masseur_level=$this->masseurcategory->where("shop_id=".$this->shopid)->order("sort asc")->select();
		$masseur_level_info=$this->masseurcategory->where("shop_id=".$this->shopid)->order("sort asc")->find();
		
		$masseur_name = I('post.name');
		$is_level = I('post.is_level',$masseur_level_info['id']);
		$is_types = I('post.is_types',1);
		$is_sex = I('post.is_sex','女');
		if($masseur_name){
			$where.=" and b.masseur_name like '%$masseur_name%'";
		}
		if($is_level){
			$where.=" and b.category_id=".$is_level;
		}
		if($is_sex){
			$where.=" and b.sex='".$is_sex."'";
		}
		
		if($is_types==1)
		{
			$where.=" and (a.status=1 or a.status=2 or a.status=3)";
		}else if($is_types==2)
		{
			$where.=" and (a.status=1 or a.status=2 or a.status=3)";
		}else if($is_types==3)
		{
			$where.=" and (a.status=1 or a.status=2 or a.status=3)";
		}else if($is_types==4)
		{
			$where.=" and (a.status=1 or a.status=2 or a.status=3)";
		}else if($is_types==5)
		{
			$where.=" and (a.status=9)";
		}
		
		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and b.state=1 and a.shop_id=".$this->shopid." and a.is_lock=1 and FIND_IN_SET(".$shop_project_info['id'].",b.master_item) ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id")->order("a.id asc")->select();
		//echo $this->shopscheduling->getLastSql();
		//$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
		foreach($result as &$val)
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
			$val['masseur_level']=masseur_level($val['category_id']);
			$val['level_proportion']=$this->masseurcategory->where("id=".$val['category_id'])->getField("level_proportion");
			if($is_types==1)
			{
				$val['masseur_sn']="轮钟";
				$val['masseur_name']="轮钟";
				$val['nick_name']="轮钟";
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
		//$data['count']=$count;
		$data['data']=$result;
		$data['masseur_level']=$masseur_level;
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
		$result=$this->shopmasseur->field("id,masseur_sn,nick_name")->where("shop_id=".$this->shopid." and id in (".$masseur_id.")")->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$project_id;
			$val['masseur_id']=$val['id'];
			$val['number']=1;
			$val['name']=$project_name;
			$val['price']=$project_price;
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
			else if($types==4)
			{
				$val['types_name']='抽钟';
			}else if($types==5)
			{
				$val['types_name']='加班';
			}
			$val['p_types']=1;
			$total_price+=$project_price;
		}
		unset($data);
		$data['state']='success';
		$data['info']='添加成功！';
		$data['data']=$result;
		$data['total_number']=count($_POST['masseur_id']);
		$data['total_price']=$total_price;
		outJson($data);
	}
	
	//产品添加技师
	public function order_addproductmasseur()
	{
		//$product_id=I('post.product_id');
		//$shop_product_info=$this->shopproduct->where("id=".$product_id." and shop_id=".$this->shopid)->find();
		//$this->assign("shop_product_info",$shop_product_info);

		
		$result=$this->shopscheduling->field("masseur_id")->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
	

		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=masseur_level($this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id'));
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['sex']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('sex');
		}
		//$this->assign("result",$result);
		
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['data']=$result;
		outJson($data);
		
		//$this->display();
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
			$nick_name='-';
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
	
	//提成添加技师
	public function order_addrewardmasseur()
	{
		//$reward_id=I('post.reward_id');
		//$shop_reward_info=$this->shopreward->where("id=".$reward_id." and shop_id=".$this->shopid)->find();
		//$this->assign("shop_reward_info",$shop_reward_info);

		
		$result=$this->shopscheduling->field("masseur_id")->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
	

		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['sex']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('sex');
		}
		
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['data']=$result;
		outJson($data);
		
		//$this->assign("result",$result);
		
		//$this->display();
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
	//提交订单
	public function post_orders()
	{
		$chain_id=$this->chainid;
		$shop_id=$this->shopid;
		

		
		$lockcard_id=I('post.lockcard_id');
		$room_id=I('post.room_id');
		$bed_id=I('post.bed_id');
		$member_id=I('post.member_id',0);
		
		$p_orders=$_POST['p_orders'];
		
		$p_id=$_POST['p_id'];  //项目ID
		$masseur_id=$_POST['masseur_id']; //健康师ID
		$p_types=$_POST['p_types']; //类型 1：项目 2：产品 3：推荐提成
		$types=$_POST['types']; //轮钟类型  1：轮钟 2：点钟 3：加钟 4：抽钟
		$number=$_POST['number']; //数量
		if(empty($chain_id) || empty($shop_id) || empty($room_id) || empty($bed_id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='参数错误！';
			outJson($data);
		}
		
		$p_orders=json_decode($p_orders,true);
		
		if(count($p_orders)==0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='项目、产品、推荐提成不能同时为空！';
			outJson($data);
		}
		

		
		$is_shoproom=$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->find();
		if($is_shoproom['state']==0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此房间已经被使用，请选择其他房间！';
			outJson($data);
		}
		
		//file_put_contents('bbb.txt',var_export($_POST,TRUE));
		
		unset($data);
		$data=array(
			'chain_id' => $chain_id,
			'shop_id' => $shop_id,
			'order_sn' => 'YS-'.time().substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),
			'shopuser_id' => $this->userid,
			'room_id' => $room_id,
			'bed_id' => $bed_id,
			'member_id' => $member_id,
			'lockcard_id' => $lockcard_id,
			'status' => 1,
			'createtime' => time(),
		);
		$this->shoplockcard->where("id=".$lockcard_id." and shop_id=".$this->shopid)->save(array("is_lock" => 0));
		$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
		$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->save(array('state' => 0,'lock_user_id' => $this->userid));
		$result=$this->orders->add($data);
		$pay_total_money=0;
		if($result)
		{
			foreach($p_orders as $val)
			{
				if($val['p_types']==1)
				{
					$project_info=$this->shopitem->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					
					
					$is_up_lock=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time()." and is_lock=0")->find();
					if($is_up_lock)
					{
						$this->orders->where("id=".$result)->delete();
						$this->shoplockcard->where("id=".$lockcard_id." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
						$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
						$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->save(array('state' => 1));
						unset($data);
						$data['code']=1;
						$data['msg']='此技师已被占用，请重新选择！';
						outJson($data);
					}
					
					
					$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->find();
					
					
					$masseur_info_level=$this->shopmasseur->where("id=".$val['masseur_id'])->find();
					$level_proportion=$this->masseurcategory->where("id=".$masseur_info_level['category_id'])->getField("level_proportion");
					
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'scheduling_id' => $masseur_info['id'],
						'project_id' => $val['p_id'],
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price']+$level_proportion,
						'number' => $val['number'],
						'total_price' => ($val['number']*$project_info['item_price'])+($val['number']*$level_proportion),
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'is_discount' => $project_info['is_discount'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $val['types'],
						'createtime' => time(),
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
					$pay_total_money+=($val['number']*$project_info['item_price'])+($val['number']*$level_proportion);
					if($masseur_info_level['device']!='')
					{
						unset($title);
						unset($content);
						unset($device);
						$title="您有一个新的订单需要处理！";
						$content = "您有一个新的订单需要处理！";
						$device[] = $masseur_info_level['device'];
						$extra = array("push_type" => 1, "order_id" => $result,'is_confirm' => 0);
						$audience='{"alias":'.json_encode($device).'}';
						$extras=json_encode($extra);
						$os=$masseur_info_level['os'];
						$res=jpush_send($title,$content,$audience,$os,$extras);
					}
				}else if($val['p_types']==2)
				{
					$product_info=$this->shopproduct->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'product_id' => $val['p_id'],
						'title' => $product_info['product_name'],
						'unit_price' => $product_info['product_price'],
						'number' => $val['number'],
						'total_price' => $val['number']*$product_info['product_price'],
						'product_reward' => $product_info['rec_reward'],
						'is_discount' => $product_info['is_discount'],
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
					$pay_total_money+=$val['number']*$product_info['product_price'];
				}else if($val['p_types']==3)
				{
					$reward_info=$this->shopreward->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'reward_id' => $val['p_id'],
						'title' => $reward_info['reward_name'],
						'unit_reward' => $reward_info['reward'],
						'number' => $val['number'],
						'total_reward' => $val['number']*$reward_info['reward'],
						'createtime' => time(),
					);
					$this->ordersreward->add($reward_data);
				}
			}
			$this->orders->where("id=".$result)->setInc('total_amount',$pay_total_money);
			unset($data);
			$data['code']=0;
			$data['msg']='下单成功！';
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='下单失败！';
			outJson($data);
		}
		
	}
	//订单详情
	public function show_order()
	{
		$order_id=I('post.id');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		if(!$order_info)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
		
		$order_info['pay_time_data']=date("Y-m-d H:i:s",$order_info['pay_time']);
		$order_info['createtime_data']=date("Y-m-d H:i:s",$order_info['createtime']);
		
		//$shopuser_id = $this->orders->where('id='.$order_id)->getfield('shopuser_id');
		$role_id = $this->shopuser->where('id='.$order_info['shopuser_id'])->getField('role_id');
		if($role_id == '0'){
			$order_info['order_from'] = '管理员';
		}else{
			$order_info['order_from'] = $this->shoprole->where('id='.$role_id)->getField('name');
		}
		
		//$this->assign('order_info',$order_info);
		
		//会员信息
		$member_info=array();
		if($order_info['member_id']!=0)
		{
			$member_info=$this->shopmember->where("chain_id=".$this->chainid." and id=".$order_info['member_id'])->find();
			//$this->assign('member_info',$member_info);
		}
		//获取订单项目
		$finish_time=0;
		/*
		$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->select();
		foreach($result_project as &$val)
		{
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$finish_time+=$val['duration'];
		}
		*/
		
		$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("id asc")->group("masseur_id")->select();
		foreach($result_project as &$val)
		{
			$val['project_list']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->order("down_time asc")->select();
			
			$val['project_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->count();
			$val['project_up_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and up_time=0")->count();
			$val['project_down_count']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and down_time=0")->count();
			
			$val['project_is_one_uptime']=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and up_time > 0")->count();
			
			$project_total_money=0;
			foreach($val['project_list'] as &$rowtt)
			{
				$rowtt['up_time_data']=date("Y-m-d H:i:s",$rowtt['up_time']);
				$rowtt['down_time_data']=date("Y-m-d H:i:s",$rowtt['down_time']);
				$rowtt['createtime_data']=date("Y-m-d H:i:s",$rowtt['createtime']);
				if($rowtt['types']==1)
				{
					$rowtt['types_name']='轮钟';
				}else if($rowtt['types']==2)
				{
					$rowtt['types_name']='点钟';
				}else if($rowtt['types']==3)
				{
					$rowtt['types_name']='加钟';
				}
				else if($rowtt['types']==4)
				{
					$rowtt['types_name']='抽钟';
				}
				else if($rowtt['types']==5)
				{
					$rowtt['types_name']='加班';
				}
				
				$project_total_money+=$rowtt['total_price'];
				$finish_time+=$rowtt['duration'];
			}
			$finish_time+=$val['duration'];
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['up_time_data']=date("Y-m-d H:i:s",$val['up_time']);
			$val['down_time_data']=date("Y-m-d H:i:s",$val['down_time']);
			$val['createtime_data']=date("Y-m-d H:i:s",$val['createtime']);
			$val['total_price']=number_format($project_total_money, 2);
			
			
		}
		
		
		
		//$this->assign('result_project',$result_project);
		//预计结束时间
		//$this->assign('finish_time',$finish_time);
		//订单是否全部上钟
		$up_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time=0")->count();
		//$this->assign('up_count',$up_count);
		//全部下钟
		$down_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and down_time=0")->count();
		//$this->assign('down_count',$down_count);
		
		//是否有一个上钟
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time > 0")->count();
		//$this->assign('is_one_uptime',$is_one_uptime);
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
		//$this->assign('result_product',$result_product);
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
		
		//$this->assign('hykzk_sum',$hykzk_sum);
		//$this->assign('hykzk_pay_sum',$hykzk_pay_sum);
		//$this->assign('ckzk_sum',$ckzk_sum);
		//$this->assign('qxkzk_sum',$qxkzk_sum);
		//$this->assign('lckzk_sum',$lckzk_sum);
		//$this->assign('product_hykzk_sum',$product_hykzk_sum);
		//$this->assign('product_hykzk_pay_sum',$product_hykzk_pay_sum);
		
		
		//$this->assign('result_reward',$result_reward);
		
		
		unset($data);
		$data['code']=0;
		$data['order_info']=$order_info; //订单信息
		$data['member_info']=$member_info; //会员信息
		$data['result_project']=$result_project; //项目列表
		$data['up_count']=$up_count; //是否全部上钟
		$data['down_count']=$down_count; //是否全部下钟
		$data['is_one_uptime']=$is_one_uptime; //是否有一个上钟
		$data['result_product']=$result_product; //产品列表
		$data['result_reward']=$result_reward; //推荐提成列表
		//计算支付明细
		$data['hykzk_sum']=$hykzk_sum; //会员卡原价
		$data['hykzk_pay_sum']=$hykzk_pay_sum; //会员卡支付总数
		$data['ckzk_sum']=$ckzk_sum; //次卡抵扣价格
		$data['qxkzk_sum']=$qxkzk_sum; //期限卡抵扣价格
		$data['lckzk_sum']=$result_reward; //疗程卡抵扣价格
		$data['product_hykzk_sum']=$product_hykzk_sum; //产品总价
		$data['product_hykzk_pay_sum']=$result_reward; //产品支付价格
		outJson($data);
		
		//$this->display();
	}
	
	
	//删除推荐提成
	public function del_reward()
	{
		$id = I('post.id',0,'intval');
		if ($this->ordersreward->delete($id)!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']='删除成功！';
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']='删除失败！';
			outJson($data);
		}	
	}
	//删除产品
	public function del_product()
	{
		$id = I('post.id',0,'intval');
		$order_product_info=$this->ordersproduct->where("id=".$id)->find();
		$this->orders->where("id=".$order_product_info['order_id'])->setDec('total_amount',$order_product_info['total_price']);
		if ($this->ordersproduct->delete($id)!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']='删除成功！';
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']='删除失败！';
			outJson($data);
		}	
	}
	//删除项目
	public function del_project()
	{
		$id = I('post.id',0,'intval');
		$project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->find();
		
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id." and up_time > 0")->find();
		if($is_one_uptime)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此项目已经上钟！';
			outJson($data);
		}
		
		$count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$project_info['order_id'])->count();
		if($count < 2)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='最少要选择一个项目！';
			outJson($data);
		}
		if ($this->ordersproject->delete($id)!==false) {
			
			$is_download=$this->ordersproject->where("order_id=".$project_info['order_id']." and masseur_id=".$project_info['masseur_id']." and is_del=0 and down_time <=0")->count();
			if($is_download == 0)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$project_info['masseur_id']." and id=".$project_info['scheduling_id'])->save(array('is_lock' => 1));
			}
			
			$this->orders->where("id=".$project_info['order_id'])->setDec('total_amount',$project_info['total_price']);
			
			//$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$project_info['masseur_id']." and id=".$project_info['scheduling_id'])->save(array('is_lock' => 1));
			unset($data);
			$data['code']=0;
			$data['msg']='删除成功！';
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']='删除失败！';
			outJson($data);
		}	
	}
	//取消订单
	public function cancel_order()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if(!$order_info)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单不存在！';
			outJson($data);
		}
		//是否有一个上钟
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time > 0")->find();
		if($is_one_uptime)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已经上钟！';
			outJson($data);
		}
		
		$result=$this->orders->where("id=".$id." and shop_id=".$this->shopid)->save(array("status" => -1));
		if ($result!==false) {
			$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
			$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
			$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 1,'lock_user_id' => 0));
			$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$this->ordersproduct->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$this->ordersreward->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->save(array('is_del' => 1));
			$orders_project=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->select();
			foreach($orders_project as $val)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and id=".$val['scheduling_id'])->save(array('is_lock' => 1));
				$masseur_info_level=$this->shopmasseur->where("id=".$val['masseur_id'])->find();
				if($masseur_info_level['device']!='')
				{
					unset($title);
					unset($content);
					$title="您的订单已经取消！";
					$content = "您的订单已经取消！";
					$device[] = $masseur_info_level['device'];
					$extra = array("push_type" => 3, "order_id" => $order_info['id']);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$masseur_info_level['os'];
					$res=jpush_send($title,$content,$audience,$os,$extras);
				}
			}
			unset($data);
			$data['code']=0;
			$data['msg']='取消成功！';
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']='取消失败！';
			outJson($data);
		}		
	}
	//全部上钟
	public function all_up()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if($order_info['status'] <= 0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
		if($order_info)
		{
			$result=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid." and up_time=0")->save(array('up_time' => time()));
			if($result)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='全部上钟成功！';
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='上钟失败！';
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
	}
	//全部下钟
	public function all_down()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$id)->find();
		if($order_info['status'] <= 0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
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
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3,'lock_user_id' => 0));
			}
			if($result)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='全部下钟成功！';
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='下钟失败！';
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
	}
	//单个上钟
	public function one_up_clock()
	{
		$id = I('post.id',0,'intval');
		$order_product_info_one=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->find();
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_product_info_one['order_id'])->find();
		if($order_info['status'] <= 0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
		$order_project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->save(array('up_time' => time()));
		if($order_project_info)
		{	
			unset($data);
			$data['code']=0;
			$data['msg']='上钟成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
	}
	//单个下钟
	public function one_down_clock()
	{
		$id = I('post.id',0,'intval');
		$order_project_info=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->find();
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_project_info['order_id'])->find();
		if($order_info['status'] <= 0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
		$project_save=$this->ordersproject->where("shop_id=".$this->shopid." and id=".$id)->save(array('down_time' => time()));
		if($project_save)
		{

			$is_down_all=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_project_info['order_id']." and masseur_id=".$order_project_info['masseur_id']." and scheduling_id=".$order_project_info['scheduling_id']." and down_time=0")->count();
			if($is_down_all==0)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$order_project_info['masseur_id']." and id=".$order_project_info['scheduling_id'])->save(array('is_lock' => 1));
			}
			$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_project_info['order_id'])->find();
			$down_clock_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_project_info['order_id']." and down_time=0")->count();
			if($order_info['status']==2 && $down_clock_count==0)
			{
				$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
				$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3,'lock_user_id' => 0));
			}
			
			unset($data);
			$data['code']=0;
			$data['msg']='下钟成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
	}
	//更换包间
	public function replace_room()
	{

		$room_id=I('post.room_id');
		$bed_id=I('post.bed_id');
		$order_id=I('post.order_id');
		if($order_id=='')
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
		if($room_id=='')
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请选择房间';
			outJson($data);
		}
		if($bed_id=='')
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请选择床位';
			outJson($data);
		}
		unset($data);
		$data=array(
			'room_id' => $room_id,
			'bed_id' => $bed_id,
		);
		$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3,'lock_user_id' => 0));
		$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
		$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save($data);
		if($result)
		{
			$this->shoproom->where("id=".$room_id." and shop_id=".$this->shopid)->save(array('state' => 0,'lock_user_id' => $this->userid));
			$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
			unset($data);
			$data['code']=0;
			$data['msg']='更新成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='更新失败！';
			outJson($data);
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
		
		$this->display();
	}
	
	
	//给订单追加信息提交订单
	public function post_add_orders()
	{
		
		
		$order_id=I('post.order_id');
		$chain_id=$this->chainid;
		$shop_id=$this->shopid;
		
		$p_orders=$_POST['p_orders'];
		
		$p_id=$_POST['p_id'];
		$masseur_id=$_POST['masseur_id'];
		$p_types=$_POST['p_types'];
		$types=$_POST['types'];
		$number=$_POST['number'];
		if(empty($chain_id) || empty($shop_id) || empty($order_id))
		{
			$this->error("参数错误！");
		}
		$p_orders=json_decode($p_orders,true);
		
		if(count($p_orders)==0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='项目、产品、推荐提成不能同时为空！';
			outJson($data);
		}
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		if($order_info['status']==-1)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
		if($order_info['status']==2)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已结账';
			outJson($data);
		}
		$pay_total_money=0;
		if($order_info)
		{
			foreach($p_orders as $key=>$val)
			{
				if($val['p_types']==1)
				{
					$project_info=$this->shopitem->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->find();
					$orderproject_info = $this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_id." and masseur_id=".$val['masseur_id'])->find();
					
					$orderproject_info_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_id." and masseur_id=".$val['masseur_id'])->count();
					
					$orderproject_info['is_confirm']=!empty($orderproject_info['is_confirm']) ? $orderproject_info['is_confirm'] : 0;
					$orderproject_info['accept_time']=!empty($orderproject_info['accept_time']) ? $orderproject_info['accept_time'] : 0;
					
					if(!$masseur_info)
					{
						unset($data);
						$data['code']=1;
						$data['msg']='此技师没有上班！';
						outJson($data);
					}
					
					
					$is_up_lock=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time()." and is_lock=0")->find();
					if($is_up_lock)
					{
						unset($data);
						$data['code']=1;
						$data['msg']='此技师已被锁定，请重新选择！';
						outJson($data);
					}
					
					
					$masseur_info_level=$this->shopmasseur->where("id=".$val['masseur_id'])->find();
					$level_proportion=$this->masseurcategory->where("id=".$masseur_info_level['category_id'])->getField("level_proportion");
					
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'scheduling_id' => $masseur_info['id'],
						'project_id' => $val['p_id'],
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price']+$level_proportion,
						'number' => $val['number'],
						'total_price' => ($val['number']*$project_info['item_price'])+($val['number']*$level_proportion),
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'is_discount' => $project_info['is_discount'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $val['types'],
						'createtime' => time(),
						'is_confirm' => $orderproject_info['is_confirm'],
                        'accept_time' => $orderproject_info['accept_time'],
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
					$pay_total_money+=($val['number']*$project_info['item_price'])+($val['number']*$level_proportion);
					if($masseur_info_level['device']!='')
					{
						unset($title);
						unset($content);
						$title="您有一个新的订单需要处理！";
						$content = "您有一个新的订单需要处理！";
						$device[] = $masseur_info_level['device'];
						$extra = array("push_type" => 1, "order_id" => $order_info['id'],'is_confirm' => 0);
						$audience='{"alias":'.json_encode($device).'}';
						$extras=json_encode($extra);
						$os=$masseur_info_level['os'];
						$res=jpush_send($title,$content,$audience,$os,$extras);
					}
				}else if($val['p_types']==2)
				{
					$product_info=$this->shopproduct->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'product_id' => $val['p_id'],
						'title' => $product_info['product_name'],
						'unit_price' => $product_info['product_price'],
						'number' => $val['number'],
						'total_price' => $val['number']*$product_info['product_price'],
						'product_reward' => $product_info['rec_reward'],
						'is_discount' => $product_info['is_discount'],
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
					$pay_total_money+=$val['number']*$product_info['product_price'];
				}else if($val['p_types']==3)
				{
					$reward_info=$this->shopreward->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'reward_id' => $val['p_id'],
						'title' => $reward_info['reward_name'],
						'unit_reward' => $reward_info['reward'],
						'number' => $val['number'],
						'total_reward' => $val['number']*$reward_info['reward'],
						'createtime' => time(),
					);
					$this->ordersreward->add($reward_data);
				}
			}
			$this->orders->where("id=".$order_id)->setInc('total_amount',$pay_total_money);
			unset($data);
			$data['code']=0;
			$data['msg']='追加成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='下单失败！';
			outJson($data);
		}
		
	}
	
	
	//给技师追加信息提交订单
	public function post_additional_orders()
	{
		$order_id=I('post.order_id');
		$chain_id=$this->chainid;
		$shop_id=$this->shopid;
		
		$p_orders=$_POST['p_orders'];
		
		$p_id=$_POST['p_id'];
		$masseur_id=$_POST['masseur_id'];
		$p_types=$_POST['p_types'];
		$types=$_POST['types'];
		$number=$_POST['number'];
		if(empty($chain_id) || empty($shop_id) || empty($order_id))
		{
			$this->error("参数错误！");
		}
		$p_orders=json_decode($p_orders,true);
		
		if(count($p_orders)==0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='项目、产品、推荐提成不能同时为空！';
			outJson($data);
		}
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		if($order_info['status']==-1)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已取消';
			outJson($data);
		}
		if($order_info['status']==2)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单已结账';
			outJson($data);
		}
		$pay_total_money=0;
		if($order_info)
		{
			foreach($p_orders as $key=>$val)
			{
				if($val['p_types']==1)
				{
					$project_info=$this->shopitem->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					$masseur_info=$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->find();
					$orderproject_info = $this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_id." and masseur_id=".$val['masseur_id'])->find();
					
					$orderproject_info_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_id." and masseur_id=".$val['masseur_id'])->count();
					
					
					
					$orderproject_info['is_confirm']=!empty($orderproject_info['is_confirm']) ? $orderproject_info['is_confirm'] : 0;
					$orderproject_info['accept_time']=!empty($orderproject_info['accept_time']) ? $orderproject_info['accept_time'] : 0;
					
					if(!$masseur_info)
					{
						unset($data);
						$data['code']=1;
						$data['msg']='此技师没有上班！';
						outJson($data);
					}
					
					$masseur_info_level=$this->shopmasseur->where("id=".$val['masseur_id'])->find();
					$level_proportion=$this->masseurcategory->where("id=".$masseur_info_level['category_id'])->getField("level_proportion");
					
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'scheduling_id' => $masseur_info['id'],
						'project_id' => $val['p_id'],
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price']+$level_proportion,
						'number' => $val['number'],
						'total_price' => ($val['number']*$project_info['item_price'])+($val['number']*$level_proportion),
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'is_discount' => $project_info['is_discount'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $val['types'],
						'createtime' => time(),
						'is_confirm' => $orderproject_info['is_confirm'],
                        'accept_time' => $orderproject_info['accept_time'],
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
					$pay_total_money+=($val['number']*$project_info['item_price'])+($val['number']*$level_proportion);
					if($masseur_info_level['device']!='')
					{
						unset($title);
						unset($content);
						$title="您有一个新的订单需要处理！";
						$content = "您有一个新的订单需要处理！";
						$device[] = $masseur_info_level['device'];
						$extra = array("push_type" => 1, "order_id" => $order_info['id'],'is_confirm' => 0);
						$audience='{"alias":'.json_encode($device).'}';
						$extras=json_encode($extra);
						$os=$masseur_info_level['os'];
						$res=jpush_send($title,$content,$audience,$os,$extras);
					}
				}else if($val['p_types']==2)
				{
					$product_info=$this->shopproduct->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'product_id' => $val['p_id'],
						'title' => $product_info['product_name'],
						'unit_price' => $product_info['product_price'],
						'number' => $val['number'],
						'total_price' => $val['number']*$product_info['product_price'],
						'product_reward' => $product_info['rec_reward'],
						'is_discount' => $product_info['is_discount'],
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
					$pay_total_money+=$val['number']*$product_info['product_price'];
				}else if($val['p_types']==3)
				{
					$reward_info=$this->shopreward->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $val['masseur_id'],
						'reward_id' => $val['p_id'],
						'title' => $reward_info['reward_name'],
						'unit_reward' => $reward_info['reward'],
						'number' => $val['number'],
						'total_reward' => $val['number']*$reward_info['reward'],
						'createtime' => time(),
					);
					$this->ordersreward->add($reward_data);
				}
			}
			$this->orders->where("id=".$order_id)->setInc('total_amount',$pay_total_money);
			unset($data);
			$data['code']=0;
			$data['msg']='追加成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='下单失败！';
			outJson($data);
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
		$where['chain_id']=$this->chainid;
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
			unset($data);
			$data['code']=1;
			$data['msg']='请选择会员！';
			outJson($data);
		}
		$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save(array('member_id' => $member_id));
		if($result!==false)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='添加成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='添加失败！';
			outJson($data);
		}
	}
	//取消会员
	public function cancel_member()
	{
		$id = I('post.id',0,'intval');
		$order_info=$this->orders->where("id=".$id." and shop_id=".$this->shopid)->find();
		if($order_info['status']==-1 || $order_info['status']==2)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单不能操作！';
			outJson($data);
		}
		if ($this->orders->where("id=".$id." and shop_id=".$this->shopid)->save(array('member_id' => 0))!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']='取消成功！';
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']='取消失败！';
			outJson($data);
		}	
	}
	public function go_pay()
	{
		$order_id=I('post.orderid');
		if(empty($order_id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单ID不存在！';
			outJson($data);
		}
		$order_info=$this->orders->field("id,order_sn,room_id,member_id,total_amount,pay_amount,status,createtime,shopuser_id")->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		//$this->assign('order_info',$order_info);
		if(!$order_info)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);
		}
		$room_info=$this->shoproom->where("id=".$order_info['room_id'])->find();
		//$this->assign('room_info',$room_info);
		$order_info['room_name']=$room_info['room_name'];
		$order_info['createtime']=date("Y-m-d H:i:s",$order_info['createtime']);
		unset($role_id);
		$role_id = $this->shopuser->where('id='.$order_info['shopuser_id'])->getField('role_id');
		if($role_id == '0'){
			$order_info['order_from'] = '管理员';
		}else{
			$order_info['order_from'] = $this->shoprole->where('id='.$role_id)->getField('name');
		}
		
		
		//会员信息
		if($order_info['member_id']!=0)
		{
			$member_info=$this->shopmember->where("chain_id=".$this->chainid." and id=".$order_info['member_id'])->find();
			//$this->assign('member_info',$member_info);
		}else
		{
			$member_info=array();
		}
		//获取订单项目
		$finish_time=0;
		$result_project=$this->ordersproject->field("id,order_id,masseur_id,project_id,title,unit_price,duration,number,total_price,up_time,down_time,is_discount")->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("down_time asc")->select();
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
		$order_info['finish_time']=$room_info['finish_time'];
		//$this->assign('result_project',$result_project);
		//预计结束时间
		//$this->assign('finish_time',$finish_time);
		// 服务开始时间
		$up_start_time=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->order("up_time asc")->find();
		//$this->assign('up_start_time',$up_start_time);
		//订单是否全部上钟
		$up_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time=0")->count();
		//$this->assign('up_count',$up_count);
		//全部下钟
		$down_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and down_time=0")->count();
		//$this->assign('down_count',$down_count);
		
		//是否有一个上钟
		$is_one_uptime=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time > 0")->count();
		//$this->assign('is_one_uptime',$is_one_uptime);
		//获取订单产品
		$result_product=$this->ordersproduct->field("id,order_id,masseur_id,product_id,title,unit_price,number,total_price,is_discount")->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
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
		//$this->assign('result_product',$result_product);
		
		//支付列表
		$pay_typest=$this->setting->field("id,title,img_thumb,sort")->where("parentid=1 and status=1")->order("sort asc")->select();
		//$this->assign('pay_typest',$pay_typest);
		
		
		
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['data']=$order_info;
		$data['member_info']=$member_info;
		$data['result_project']=$result_project;
		$data['result_product']=$result_product;
		$data['pay_typest']=$pay_typest;
		outJson($data);
		
		
		
		//$this->display();
		
	}
	//去支付
	public function post_pay_order()
	{
		$order_id=$_POST['order_id'];  //订单ID
		
		
		
		$pincode=$_POST['pincode'];  //验证码
		
		$pro_id=$_POST['pro_id'];  //项目产品ID
		$pro_order_id=$_POST['pro_order_id'];  //项目产品订单里面的ID
		$pro_type=$_POST['pro_type']; //类型 1：项目 2：产品
		$types=$_POST['types']; //优惠类型 0：无优惠 1：会员卡折扣 2：次卡抵扣 3：期限卡抵扣 4：疗程卡
		$card_id=$_POST['card_id']; //卡的ID
		
		
		$p_orders=$_POST['p_orders'];
		
		$pay_money_deal=I('post.pay_money_deal',0); //整单优惠
		$pay_types=$_POST['pay_types']; //支付类型
		$pay_money_type=$_POST['pay_money_type'];
		$total_money=0;
		$pay_total=0;
		$order_info_find=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
		if($order_info_find['status']!=1)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此订单不能付款！';
			outJson($data);
		}
		
        $p_orders=json_decode($p_orders,true);

        foreach($p_orders as $key=>$row)
		{
            
			if($row['pro_type']==1)
			{
                
				$project_order_total=$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
				if($row['types']==0)
				{
					$pay_total+=$project_order_total['total_price'];
				}else if($row['types']==1)
				{
					$member_info=$this->shopmember->where("id=".$row['card_id'])->find();
					$pay_total+=$project_order_total['total_price']*$member_info['discount']*0.1;
				}else if($row['types']==2)
				{
					$pay_total+=0;
				}else if($row['types']==3)
				{
					$pay_total+=0;
				}else if($row['types']==4)
				{
					$pay_total+=0;
				}
				$total_money+=$project_order_total['total_price'];
			}else if($row['pro_type']==2)
			{
				$product_order_total=$this->ordersproduct->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
				if($row['types']==0)
				{
					$pay_total+=$product_order_total['total_price'];
				}else if($row['types']==1)
				{
					$member_info=$this->shopmember->where("id=".$row['card_id'])->find();
					$pay_total+=$product_order_total['total_price']*$member_info['discount']*0.1;
				}
				$total_money+=$product_order_total['total_price'];
			}
			
			
			if($row['types'] == '2'){
				//判断是否有相同产品，统计此产品的次卡抵扣次数
				if(!in_array($row['pro_id'],$numcard_project['project_id'])){
					$numcard_project['project_id'][] = $row['pro_id'];
					$numcard_project['count'][$row['pro_id']] = 1;
				}else{
					$numcard_project['count'][$row['pro_id']] = (int)$numcard_project['count'][$row['pro_id']] + 1;
					//查询此产品的剩余次卡抵扣次数
					$project_numcard_number = $this->shopnumcard->where('id='.$row['card_id'])->getField('card_num');
					if($numcard_project['count'][$row['pro_id']] > $project_numcard_number){
						//$this->error('您的次卡已经剩余'.$project_numcard_number.'次！');
						unset($data);
						$data['code']=1;
						$data['msg']='您的次卡已经剩余'.$project_numcard_number.'次！';
						outJson($data);
					}
				}
			}else if($row['types'] == '3'){
				if(!in_array($row['pro_id'],$qxkdk_project['project_id'])){
					$qxkdk_project['project_id'][] = $row['pro_id'];
					$qxkdk_project['count'][$row['pro_id']] = 1;
				}else{
					$qxkdk_project['count'][$row['pro_id']] = (int)$qxkdk_project['count'][$row['pro_id']] + 1;
				}
				$qxkxf_info=$this->shopfinancial->where("project_id=".$row['pro_id']." and shop_id=".$this->shopid." and card_id=".$row['card_id']." and createtime > ".strtotime(date("Y-m-d",time()))." and createtime < ".strtotime(date("Y-m-d 23:23:59",time())))->count();
				$project_qxkdk_number = $this->shopdeadlinecard->where('id='.$row['card_id'])->getField('day_ceiling');
				if($project_qxkdk_number <= $qxkxf_info){
					//$this->error('您今天的使用次数已经超限！');
					unset($data);
					$data['code']=1;
					$data['msg']='您今天的使用次数已经超限！';
					outJson($data);
				}
				if($qxkdk_project['count'][$row['pro_id']] > $project_qxkdk_number){
					//$this->error('期限卡抵扣已经超出单日使用上限！');
					unset($data);
					$data['code']=1;
					$data['msg']='期限卡抵扣已经超出单日使用上限！';
					outJson($data);
				}
			}else if($row['types'] == '4'){
				if(!in_array($row['pro_id'],$lckdk_project['project_id'])){
					$lckdk_project['project_id'][] = $row['pro_id'];
					$lckdk_project['count'][$row['pro_id']] = 1;
				}else{
					$lckdk_project['count'][$row['pro_id']] = (int)$lckdk_project['count'][$row['pro_id']] + 1;
					//查询此产品的剩余次卡抵扣次数
					$project_lckdk_number = $this->shopcourseproject->where('id='.$row['card_id'])->getField('card_num');
					if($lckdk_project['count'][$row['pro_id']] > $project_lckdk_number){
						//$this->error('您的疗程卡已经剩余'.$project_lckdk_number.'次！');
						unset($data);
						$data['code']=1;
						$data['msg']='您的疗程卡已经剩余'.$project_lckdk_number.'次！';
						outJson($data);
					}
				}
			}
		}
		//dump($numcard_project);
		//计算应收明细
		/*
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
		*/
		
		if($pay_types==2)
		{
			$member_balance=$this->shopmember->where("id=".$order_info_find['member_id'])->find();
			
			
			$is_reg=$this->smscode->where("phone='".$member_balance['member_tel']."' and code = '".$pincode."' and types=2")->order("id desc")->find();
			if($is_reg)
			{
				$is_time=$this->smscode->where("phone='".$member_balance['member_tel']."' and createtime > ".(time()-600)." and types=2")->order("id desc")->find();
				if(!$is_time)
				{
					//$this->error("您的验证码已经过期！");
					unset($data);
					$data['code']=1;
					$data['msg']='您的验证码已经过期！';
					outJson($data);
				}
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='验证码错误！';
				outJson($data);
				//$this->error("验证码错误！");
			}
			
			
			if($pay_money_type > $member_balance['balance'])
			{
				//$this->error("您会员卡余额不足！");
				unset($data);
				$data['code']=1;
				$data['msg']='您会员卡余额不足！';
				outJson($data);
			}
			$this->shopmember->where("id=".$order_info_find['member_id'])->setDec('balance',$pay_money_type);
		}
		
		//欠款
		//$arrears_moeny=$pay_total-($pay_type_deal+$pay_money_deal);
		$arrears_moeny=round($pay_total-($pay_money_type+$pay_money_deal),2);
        
		if($arrears_moeny!=0)
		{
			//$this->error("金额不对！");
			unset($data);
			$data['code']=1;
			$data['msg']='金额不对！';
			outJson($data);
		}
		
		foreach($p_orders as $key=>$row)
		{
			if($row['pro_type']==1)
			{
				$project_order_total=$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
				if($row['types']==0)
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
					$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($project_data);
				}else if($row['types']==1)
				{
					//会员卡打折
					$member_info=$this->shopmember->where("id=".$row['card_id'])->find();
					
					unset($project_data);
					$project_data=array(
						'card_type' => 1,
						'card_id' => $row['card_id'],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $project_order_total['total_price']*$member_info['discount']*0.1,
					);
					$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($project_data);
					
				}else if($row['types']==2)
				{
					//次卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 2,
						'card_id' => $row['card_id'],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					
					
					
					
					$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($project_data);
					unset($numcard_info);
					$numcard_info=$this->shopnumcard->where("id=".$row['card_id']." and member_id=".$order_info_find['member_id'])->find();
					
					unset($order_project_info);
					$order_project_info=$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
					
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $row['pro_order_id'],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $row['card_id'],
						'identity' => 1,
						'transaction_type' => 2,
						'transaction_money' => '-'.$numcard_info['average'],
						'transaction_num' =>1,
						'now_money' => $numcard_info['numcard_money']-$numcard_info['average'],
						'now_num' => $numcard_info['card_num']-1,
						'shopuser_id' => $this->userid,
						'type' => 2,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					if($numcard_info['numcard_money']-$numcard_info['average'] > 0)
					{
						$this->shopnumcard->where("id=".$row['card_id'])->save(array('card_num' => $numcard_info['card_num']-1,'numcard_money' => $numcard_info['numcard_money']-$numcard_info['average']));
					}else
					{
						$this->shopnumcard->where("id=".$row['card_id'])->save(array('card_num' => $numcard_info['card_num']-1,'numcard_money' => 0));
					}
				}else if($row['types']==3)
				{
					//期限卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 3,
						'card_id' => $row['card_id'],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($project_data);
					
					$order_project_info=$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
					
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $row['pro_order_id'],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $row['card_id'],
						'identity' => 1,
						'transaction_type' => 3,
						'transaction_money' => 0,
						'transaction_num' =>1,
						'now_money' => 0,
						'shopuser_id' => $this->userid,
						'type' => 2,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					
					
				}else if($row['types']==4)
				{
					//疗程卡消费
					unset($project_data);
					$project_data=array(
						'card_type' => 4,
						'card_id' => $row['card_id'],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => 0,
					);
					$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($project_data);
					
					unset($coursecard_info);
					$courseproject_info=$this->shopcourseproject->where("id=".$row['card_id']." and member_id=".$order_info_find['member_id'])->find();
					$coursecard_info=$this->shopcoursecard->where("id=".$courseproject_info['card_id']." and member_id=".$order_info_find['member_id'])->find();
					
					
					unset($order_project_info);
					$order_project_info=$this->ordersproject->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
					
					
					unset($financial_data);
					$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => $row['pro_order_id'],
						'project_id' => $order_project_info['project_id'],
						'card_id' => $courseproject_info['card_id'],
						'courseproject_id' => $row['card_id'],
						'identity' => 1,
						'transaction_type' => 4,
						'transaction_money' => '-'.$coursecard_info['average'],
						'transaction_num' =>1,
						'now_money' => $coursecard_info['card_money']-$coursecard_info['average'],
						'now_num' => $courseproject_info['card_num']-1,
						'shopuser_id' => $this->userid,
						'type' => 2,
						'createtime' => time(),
						'status' => 1
					);
					$this->shopfinancial->add($financial_data);
					
					$this->shopcourseproject->where("id=".$row['card_id'])->save(array('card_num' => $courseproject_info['card_num']-1));
					$this->shopcoursecard->where("id=".$coursecard_info['id'])->save(array('card_money' => $coursecard_info['card_money']-$coursecard_info['average']));
			
				}
			}else if($row['pro_type']==2)
			{
				$product_order_total=$this->ordersproduct->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->find();
				if($row['types']==0)
				{
					unset($product_data);
					$product_data=array(
						'card_type' => 0,
						'card_id' => 0,
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $product_order_total['total_price'],
					);
					$this->ordersproduct->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($product_data);
				}else if($row['types']==1)
				{
					$member_info=$this->shopmember->where("id=".$row['card_id'])->find();
					unset($product_data);
					$product_data=array(
						'card_type' => 1,
						'card_id' => $row['card_id'],
						'payuser_id' => $this->userid,
						'pay_time' => time(),
						'pay_money' => $product_order_total['total_price']*$member_info['discount']*0.1,
					);
					$this->ordersproduct->where("id=".$row['pro_order_id']." and shop_id=".$this->shopid)->save($product_data);
				}
			}
		}
		unset($data);
		$data=array(
			'pay_amount' => $pay_money_type,
			'preferential_amount' => $pay_money_deal,
			'status' => 2,
			'payuser_id' => $this->userid,
			'pay_type' => $pay_types,
			'note' => $note,
			'pay_time' => time(),
		);
		$result=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->save($data);
		if($result)
		{
			/*
			$member_balance_last=$this->shopmember->where("id=".$order_info_find['member_id'])->find();
			unset($financial_data);
			$financial_data=array(
						'chain_id' => $this->chainid,
						'shop_id' => $this->shopid,
						'order_id' => $order_id,
						'member_id' => $order_info_find['member_id'],
						'orderporject_id' => 0,
						'project_id' => 0,
						'card_id' => 0,
						'identity' => 1,
						'transaction_type' => 1,
						'transaction_money' => '-'.$pay_money_type,
						'transaction_num' =>0,
						'now_money' => $member_balance_last['balance'],
						'shopuser_id' => $this->userid,
						'createtime' => time(),
						'type' => 2,
						'status' => 1
			);
			$this->shopfinancial->add($financial_data);
			*/
			
			
			if($pay_types==2)
			{
				$member_balance_last=$this->shopmember->where("id=".$order_info_find['member_id'])->find();
				unset($financial_data);
				$financial_data=array(
							'chain_id' => $this->chainid,
							'shop_id' => $this->shopid,
							'order_id' => $order_id,
							'member_id' => $order_info_find['member_id'],
							'orderporject_id' => 0,
							'project_id' => 0,
							'card_id' => 0,
							'identity' => 1,
							'transaction_type' => 1,
							'transaction_money' => '-'.$pay_money_type,
							'transaction_num' =>0,
							'now_money' => $member_balance_last['balance'],
							'shopuser_id' => $this->userid,
							'createtime' => time(),
							'type' => 2,
							'status' => 1
				);
				$this->shopfinancial->add($financial_data);
			}
			
			
			$project_down_count=$this->ordersproject->where("order_id=".$order_id." and shop_id=".$this->shopid." and down_time=0")->count();
			if($project_down_count == 0)
			{
					
				$order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shopid)->find();
				$this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shopid)->save(array("is_lock" => 1));
				$this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shopid)->save(array('state' => 3,'lock_user_id' => 0));
				$this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shopid)->save(array('is_lock' => 1));
				
			}
			unset($data);
			$data['code']=0;
			$data['msg']='付款成功！';
			$data['data']=$order_id;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='付款失败！';
			outJson($data);
		}
	}

	//订单列表信息--------
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
			unset($role_id);
			$role_id = $this->shopuser->where('id='.$val['shopuser_id'])->getField('role_id');
			if($role_id == '0'){
				$val['order_from'] = '管理员';
			}else{
				$val['order_from'] = $this->shoprole->where('id='.$role_id)->getField('name');
			}
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
	
	
	//选择健康师数据
	public function get_update_master()
	{
		
		$masseur_name = I('request.name');
		if($masseur_name){
			$where.=" and b.masseur_name like '%$masseur_name%'";
		}
	
		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.category_id as category_id,b.sex as sex")->order("a.id asc")->select();
		//echo $this->shopscheduling->getLastSql();
		//$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
		foreach($result as &$val)
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
			//$val['level_proportion']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('level_proportion');
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
			unset($data);
			$data['code']=0;
			$data['msg']='更换成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='更换失败！';
			outJson($data);
		}
	}




	//项目/产品追加选择技师
	public function post_choose_masseur_orders()
	{
		$order_id=I('post.order_id');
		$chain_id=$this->chainid;
		$shop_id=$this->shopid;
		
		$p_orders=$_POST['p_orders'];
		
		if(empty($chain_id) || empty($shop_id) || empty($order_id))
		{
			$this->error("参数错误！");
		}
		$p_orders=json_decode($p_orders,true);
		
		if(count($p_orders)==0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='产品、推荐提成不能同时为空！';
			outJson($data);
		}
		$order_info=$this->orders->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		$pay_total_money=0;
		if($order_info)
		{
			foreach($p_orders as $key=>$val)
			{
				if($val['p_types']==2)
				{
					$product_info=$this->shopproduct->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($product_data);
					$product_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'id' => $val['p_id'],
						'masseur_id' => $val['masseur_id'],
						'product_id' => $val['p_id'],
					);
					$this->ordersproduct->save($product_data);
					
				}else if($val['p_types']==3)
				{
					$reward_info=$this->shopreward->where("id=".$val['p_id']." and shop_id=".$this->shopid)->find();
					unset($reward_data);
					$reward_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'id' => $val['p_id'],
						'masseur_id' => $val['masseur_id'],
						'reward_id' => $val['p_id'],
					);
					$this->ordersreward->save($reward_data);
				}
			}
			unset($data);
			$data['code']=0;
			$data['msg']='追加成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='追加失败！';
			outJson($data);
		}
		
	}
	
	//获取房间和对应的床位
	public function room_bed()
	{
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			$val['bed_list']=$this->shopbed->field("id,bed_no")->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->order("sort asc")->select();
		}
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=array();
			outJson($data);
		}
	}
	//获取技师等级
	public function masseur_level()
	{
		$masseur_level=$this->masseurcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		if($masseur_level)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=$masseur_level;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=array();
			outJson($data);
		}
	}
	//房态管理
	public function tosweep()
	{
		$sweep_list=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where("shop_id=".$this->shopid." and state=3")->order("id asc")->select();
		if($sweep_list)
		{
			//$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
			foreach($result as &$val)
			{
				
				
			}
			foreach($sweep_list as &$val)
			{
				//$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
				$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
				//总房间数
				$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
				//剩余房间数
				$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			}
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=$sweep_list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['data']=array();
			outJson($data);
		}
	}
	//操作房态为空闲
	public function update_free()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请选择房间';
			outJson($data);
		}
		$result=$this->shoproom->where("shop_id=".$this->shopid." and id=".$id." and state=3")->find();
		if($result)
		{
			unset($data_array);
			$data_array=array(
				'state' => 1,
			);
			$res=$this->shoproom->where("id=".$id)->save($data_array);
			if($res)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='更新成功！';
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='更新失败！';
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='此房间不能操作！';
			outJson($data);
		}
		
	}
	//订单列表
	public function order_lists()
	{
		$limit = I('post.limit',8);
		$page = I('post.page',1);
		$is_history = I('post.is_history',0);
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['shop_id']=$this->shopid;
		if($is_history==0)
		{
			$where['status']=array('eq',1);
		}else
		{
			$where['status']=array('neq',1);
		}
		$result=$this->orders->field("id,order_sn,shopuser_id,member_id,room_id,total_amount,pay_amount,invoice_money,preferential_amount,status,payuser_id,pay_time,is_invoice,createtime")->where($where)->order("createtime desc")->limit($page.','.$limit)->select();
		foreach($result as &$val)
		{
			$val['room_name']=$this->shoproom->where("id=".$val['room_id'])->getField("room_name");
			unset($role_id);
			$role_id = $this->shopuser->where('id='.$val['shopuser_id'])->getField('role_id');
			if($role_id == '0'){
				$val['order_from'] = '管理员';
			}else{
				$val['order_from'] = $this->shoprole->where('id='.$role_id)->getField('name');
			}
			$val['createtime']=date("Y-m-d H:i:s",$val['createtime']);
			if($val['member_id']!=0)
			{
				$val['member_info']=$this->shopmember->field("id,member_name,member_tel,birthday,sex,member_no,member_card")->where("id=".$val['member_id'])->find();
			}else
			{
				$val['member_info']=array();
			}
			$fish_time=0;
			$val['masseur_list']=$this->ordersproject->field("id,order_id,masseur_id,title,up_time,down_time,duration")->where("order_id=".$val['id'])->order("id desc")->select();
			foreach($val['masseur_list'] as &$rowt)
			{
				$rowt['masseur_name']=$this->shopmasseur->where("id=".$rowt['masseur_id'])->getField("nick_name");
				$fish_time+=$rowt['duration'];
			}
			unset($up_time_one);
			$up_time_one=$this->ordersproject->where("order_id=".$val['id']." and up_time > 0")->order("up_time asc")->find();
			if($up_time_one)
			{
				$val['countdown']=ceil((($up_time_one['up_time']+($fish_time*60))-time())/60);
			}else
			{
				$val['countdown']="";
			}
			
		}
		$count=$this->orders->where($where)->count();
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$result;
		outJson($data);
	}
	
	//空闲健康师列表
	public function MasseurFree()
	{
		//$masseur_level=$this->masseurcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		//$this->assign("masseur_level",$masseur_level);
		
		$result=$this->shopscheduling->field("masseur_id,is_lock,status")->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->count();
		
		//计算各个数量
		//$early_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=1")->count();
		//$center_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=2")->count();
		//$evening_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=3")->count();
		//$repair_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=4")->count();
		//$ask_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=5")->count();
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
			$val['masseur_sex']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('sex');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			$val['level_name']=$this->masseurcategory->where("id=".$val['masseur_level'])->getField("category_name");
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=1 and is_del=0")->count();
			$val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=array();
			outJson($data);
		}
	}
	
	//锁定健康师列表
	public function MasseurLock()
	{
		//$masseur_level=$this->masseurcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		//$this->assign("masseur_level",$masseur_level);
		
		$result=$this->shopscheduling->field("masseur_id,is_lock,status")->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=0")->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->count();
		
		//计算各个数量
		//$early_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=1")->count();
		//$center_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=2")->count();
		//$evening_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=3")->count();
		//$repair_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=4")->count();
		//$ask_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=5")->count();
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
			$val['masseur_sex']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('sex');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
			$val['level_name']=$this->masseurcategory->where("id=".$val['masseur_level'])->getField("category_name");
			$val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=1 and is_del=0")->count();
			$val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
		}
		$arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
		
		array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=array();
			outJson($data);
		}
	}
	
	//发送验证码
	public function send_code()
	{
		$phone=I('post.phone');
		$order_id=I('post.order_id');
		$pay_money=I('post.pay_money');
		if($phone=='')
		{
			//$this->error("请输入您的手机号！");
			unset($data);
			$data['code']=1;
			$data['msg']='请输入您的手机号！';
			outJson($data);
		}
		$order_info=$this->orders->field("id,order_sn,room_id")->where("shop_id=".$this->shopid." and id=".$order_id)->find();
		if(!$order_info)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='订单不存在！';
			outJson($data);	
		}
		
		$is_reg=$this->smscode->where("phone='".$phone."' and createtime > ".(time()-600)." and types=2")->order("id desc")->find();
		if($is_reg)
		{
			//$this->error("请等会再发！");
			unset($data);
			$data['code']=1;
			$data['msg']='请等会再发！';
			outJson($data);
		}
		$room_info=$this->shoproom->where("id=".$order_info['room_id'])->getField("room_name");
		$rand_code=rand(111111,999999);
		$message="【嗨丫丫】您的订单".$room_info."号包间正在确认收款，应收金额".$pay_money."元，验证码".$rand_code."，请勿泄露。";
		$result=SendSms($phone,$message,'2921');
		//print_r($result);
		if($result > 0)
		{
			unset($data);
			$data=array(
				'phone' => $phone,
				'code' => $rand_code,
				'types' => 2,
				'createtime' => time(),
			);
			$this->smscode->add($data);
			unset($data);
			$data['code']=0;
			$data['msg']='发送成功！';
			outJson($data);
			//$this->success("发送成功！");
		}else
		{
			//$this->error("发送失败！");
			unset($data);
			$data['code']=1;
			$data['msg']='发送失败！';
			outJson($data);
		}
	}
	//修改备注
	public function update_note()
	{
		$order_id=I('post.order_id');
		$note=I('post.note');
		if($order_id=='' || $note=='')
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请输入备注！';
			outJson($data);
		}
		$result=$this->orders->where("id=".$order_id)->save(array('note' => $note));
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='编辑成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='编辑失败！';
			outJson($data);
		}
	}
	
}
?>