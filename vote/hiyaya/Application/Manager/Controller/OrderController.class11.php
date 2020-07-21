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
		$this->shoproom=M("ShopRoom"); //房间表
		$this->shopbed=M("ShopBed"); //床位表
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
	
    public function index()
	{
		$free_lockcard_list=$this->shoplockcard->where("is_lock=1 and shop_id=".$this->shopid)->order("sort asc")->select(); //未锁定
		//$unfree_lockcard_list=$this->shoplockcard->where("is_lock=0 and shop_id=".$this->shopid)->order("sort asc")->select(); //锁定
		$this->assign('free_lockcard_list',$free_lockcard_list);
		//订单列表
		$order_list=$this->orders->where("shop_id=".$this->shopid." and (status=1 or status=2)")->order("createtime desc")->select();
		$this->assign('order_list',$order_list);
		//$this->assign('unfree_lockcard_list',$unfree_lockcard_list);
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

		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_orders_project as b on a.masseur_id=b.masseur_id")->where("a.start_time < 1533812222 and a.end_time > 1533812222 and a.shop_id=".$this->shopid." and a.is_lock=1")->field("a.masseur_id as masseur_id,a.status as status,b.id as cid")->order("b.down_time asc,a.id asc")->select();
		
		print_r($result);
		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['masseur_id']);
			$list[$id]=isset($list[$id])?($vo['cid']>$list[$id]['cid'])? $vo:$list[$id] : $vo;
		}
		$list=array_values($list);
		print_r($list);
		

		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('category_id');
		}
		$this->assign("result",$result);
		
		$this->display();
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
		$result=$this->shopmasseur->field("id,masseur_sn")->where("shop_id=".$this->shopid." and id in (".$masseur_id.")")->select();
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
	
	//项产品添加技师
	public function order_addproductmasseur()
	{
		$product_id=I('product_id');
		$shop_product_info=$this->shopproduct->where("id=".$product_id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_product_info",$shop_product_info);

		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_orders_project as b on a.masseur_id=b.masseur_id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid)->field("a.masseur_id as masseur_id,a.status as status")->order("b.down_time asc,a.id asc")->select();
		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
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
		$reward_id=I('reward_id');
		$shop_reward_info=$this->shopreward->where("id=".$reward_id." and shop_id=".$this->shopid)->find();
		$this->assign("shop_reward_info",$shop_reward_info);

		
		$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_orders_project as b on a.masseur_id=b.masseur_id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid)->field("a.masseur_id as masseur_id,a.status as status")->order("b.down_time asc,a.id asc")->select();
		foreach($result as &$val)
		{
			$val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
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
		$result=$this->shopreward->field("id")->where("shop_id=".$this->shopid." and id=".$reward_id)->select();
		$total_price=0;
		foreach($result as &$val)
		{
			$val['p_id']=$reward_id;
			$val['masseur_id']=$masseur_id;
			$val['number']=$number;
			$val['name']=$reward_name;
			$val['masseur_sn']=$masseur_sn;
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
		if(empty($chain_id) || empty($shop_id) || empty($lockcard_id) || empty($room_id) || empty($bed_id))
		{
			$this->error("参数错误！");
		}
		if(count($p_id)==0)
		{
			$this->error("项目、产品、推荐提成不能同时为空");
		}
		if(!in_array('1',$p_types))
		{
			$this->error("最少选择一个项目！");
		}
		unset($data);
		$data=array(
			'chain_id' => $chain_id,
			'shop_id' => $shop_id,
			'order_sn' => 'YS-'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),
			'shopuser_id' => $this->userid,
			'room_id' => $room_id,
			'bed_id' => $bed_id,
			'lockcard_id' => $lockcard_id,
			'status' => 1,
			'createtime' => time(),
		);
		$this->shoplockcard->where("id=".$lockcard_id." and shop_id=".$this->shopid)->save(array("is_lock" => 0));
		$this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
		$result=$this->orders->add($data);
		if($result)
		{
			foreach($p_id as $key=>$val)
			{
				if($p_types[$key]==1)
				{
					$project_info=$this->shopitem->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $result,
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'project_id' => $val,
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price'],
						'number' => $number[$key],
						'total_price' => $number[$key]*$project_info['item_price'],
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $types[$key],
						'createtime' => time(),
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
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
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
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
		//获取订单项目
		$finish_time=0;
		$result_project=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
		foreach($result_project as &$val)
		{
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			$finish_time+=$val['duration'];
		}
		$this->assign('result_project',$result_project);
		//预计结束时间
		$this->assign('finish_time',$finish_time);
		//订单是否全部上钟
		$up_count=$this->ordersproject->where("shop_id=".$this->shopid." and order_id=".$order_info['id']." and up_time=0")->count();
		$this->assign('up_count',$up_count);
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
		//获取订单提成
		$result_reward=$this->ordersreward->where("shop_id=".$this->shopid." and order_id=".$order_info['id'])->select();
		foreach($result_reward as &$val)
		{
			
			$val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
			
		}
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
		if ($this->ordersproject->delete($id)!==false) {
			$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$project_info['masseur_id']." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 1));
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
			$orders_project=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shopid)->select();
			foreach($orders_project as $val)
			{
				$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$val['masseur_id']." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 1));
			}
			$this->success("取消成功！");
		} else {
			$this->error("取消失败！");
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
		if($order_info)
		{
			foreach($p_id as $key=>$val)
			{
				if($p_types[$key]==1)
				{
					$project_info=$this->shopitem->where("id=".$val." and shop_id=".$this->shopid)->find();
					unset($project_data);
					$project_data=array(
						'chain_id' => $chain_id,
						'shop_id' => $shop_id,
						'order_id' => $order_info['id'],
						'shopuser_id' => $this->userid,
						'masseur_id' => $masseur_id[$key],
						'project_id' => $val,
						'title' => $project_info['item_name'],
						'duration' => $project_info['item_duration'],
						'unit_price' => $project_info['item_price'],
						'number' => $number[$key],
						'total_price' => $number[$key]*$project_info['item_price'],
						'loop_reward' => $project_info['turn_reward'],
						'point_reward' => $project_info['point_reward'],
						'add_reward' => $project_info['add_reward'],
						'project_reward' => $project_info['rec_reward'],
						'up_time' => 0,
						'down_time' => 0,
						'status' => 1,
						'types' => $types[$key],
						'createtime' => time(),
					);
					$this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id[$key]." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
					$this->ordersproject->add($project_data);
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
						'createtime' => time(),
					);
					$this->ordersproduct->add($product_data);
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
			$this->success("下单成功！");
		}else
		{
			$this->error("下单失败！");
		}
		
	}
	
	
}
?>