<?php
// +----------------------------------------------------------------------
// | 动态管理
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: love_fat <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class DynamicController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->shoproom=M("ShopRoom"); //房间表
		$this->shopbed=M("ShopBed"); //床位表
		$this->roomcategory=M("RoomCategory"); //房间分类
		$this->shopfloor=M("ShopFloor"); //床位表
		$this->masseurcategory=M("MasseurCategory"); //技师等级
		$this->shopscheduling=M("ShopScheduling"); //排班表
		$this->shopmasseur=M("ShopMasseur"); //技师表
		$this->orders=M("Orders"); //订单表
		$this->ordersproject=M("OrdersProject"); //订单项目表
		$this->ordersproduct=M("OrdersProduct"); //订单产品表
		$this->ordersreward=M("OrdersReward"); //订单提成表
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
		$this->userid=$this->user_id;
    }
	//房间动态
    public function RoomDynamic()
    {
        $category_id = I('post.category_id');
		$floor_id = I('post.floor_id');
		$state = I('post.state');
		if(!empty($category_id))
		{
			$where['category_id']=$category_id;
		}
		if(!empty($floor_id))
		{
			$where['floor_id']=$floor_id;
		}
		if(!empty($state))
		{
			$where['state']=$state;
		}
		$where['shop_id']=$this->shopid;
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
			$val['floor_name']=$this->shopfloor->where("id=".$val['floor_id'])->getField('floor_name');
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			if($val['state']==0){
				$val['order_id'] = $this->orders->where("shop_id=".$this->shopid." and room_id=".$val['id'])->order("createtime desc,id desc")->limit(1)->getfield('id');
			}
			
		}
		$category_list=$this->roomcategory->field("id,category_name,sort")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		$floor_list=$this->shopfloor->field("id,floor,floor_name")->where("shop_id=".$this->shopid)->order("id asc")->select();
		$count=$this->shoproom->where($where)->count();
		
		//房间状态数量
		$count_lock=$this->shoproom->where("shop_id=".$this->shopid." and state=0")->count();
		$count_free=$this->shoproom->where("shop_id=".$this->shopid." and state=1")->count();
		$count_used=$this->shoproom->where("shop_id=".$this->shopid." and state=2")->count();
		$count_sweep=$this->shoproom->where("shop_id=".$this->shopid." and state=3")->count();
		$count_maintenance=$this->shoproom->where("shop_id=".$this->shopid." and state=4")->count();
		$count_leave=$this->shoproom->where("shop_id=".$this->shopid." and state=5")->count();
		$count_housing=$this->shoproom->where("shop_id=".$this->shopid." and state=6")->count();
		$count_rest=$this->shoproom->where("shop_id=".$this->shopid." and state=7")->count();
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=$result;
			$data['category_list']=$category_list;
			$data['floor_list']=$floor_list;
			$data['count_lock']=$count_lock;
			$data['count_free']=$count_free;
			$data['count_used']=$count_used;
			$data['count_sweep']=$count_sweep;
			$data['count_maintenance']=$count_maintenance;
			$data['count_leave']=$count_leave;
			$data['count_housing']=$count_housing;
			$data['count_rest']=$count_rest;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=array();
			$data['category_list']=$category_list;
			$data['floor_list']=$floor_list;
			$data['count_lock']=$count_lock;
			$data['count_free']=$count_free;
			$data['count_used']=$count_used;
			$data['count_sweep']=$count_sweep;
			$data['count_maintenance']=$count_maintenance;
			$data['count_leave']=$count_leave;
			$data['count_housing']=$count_housing;
			$data['count_rest']=$count_rest;
			outJson($data);
		}
		
    }
	//健康师动态
	public function MasseurDynamic()
	{
		$masseur_level=$this->masseurcategory->field("id,category_name")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		//$this->assign("masseur_level",$masseur_level);
		
		$result=$this->shopscheduling->field("masseur_id,is_lock,status")->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->order("id asc")->select();
		$count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid)->count();
		
		
		//计算各个数量
		$early_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=1")->count();
		$center_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=2")->count();
		$evening_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=3")->count();
		$repair_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=4")->count();
		$ask_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and status=5")->count();
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
			if($val['is_lock'] == '0'){
				$val['is_lock']='锁定';
			}else{
				$val['is_lock']='空闲';
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
			$data['level_list']=$masseur_level;
			$data['early_count']=$early_count;
			$data['center_count']=$center_count;
			$data['evening_count']=$evening_count;
			$data['repair_count']=$repair_count;
			$data['ask_count']=$ask_count;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=array();
			$data['level_list']=$masseur_level;
			$data['early_count']=$early_count;
			$data['center_count']=$center_count;
			$data['evening_count']=$evening_count;
			$data['repair_count']=$repair_count;
			$data['ask_count']=$ask_count;
			outJson($data);
		}
	}
	
	
	//可用房间列表
    public function FreeRoom()
    {
		$where['state']=1;
		$where['shop_id']=$this->shopid;
		$count=$this->shoproom->where($where)->count();
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
		}
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
	//占用房间列表
    public function LockRoom()
    {
		$where['state']=0;
		//$where['lock_user_id']=$this->userid;
		$where['shop_id']=$this->shopid;
		$count=$this->shoproom->where($where)->count();
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
		//$this->shoproom->table("dade_shop_room as a")->join("left join dade_orders as b on a.id=b.room_id")->where("a.shop_id=".$this->shopid." and a.state=0 ")->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id")->order("a.id asc")->select()
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			
			$val['order_id']=$this->orders->where("shop_id=".$this->shopid." and room_id=".$val['id'])->order("id desc")->getField("id");
		}
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
	//锁定列表
	public function MyLockRoom()
	{
		$where['state']=2;
		$where['shop_id']=$this->shopid;
		$count=$this->shoproom->where($where)->count();
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
			//总房间数
			$val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and room_id=".$val['id'])->count();
			//剩余房间数
			$val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shopid." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
			
			$val['order_id']=$this->orders->where("shop_id=".$this->shopid." and room_id=".$val['id'])->order("id desc")->getField("id");
		}
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
	//设为锁定
	public function UpdateLock()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请选择房间';
			outJson($data);
		}
		$result=$this->shoproom->where("shop_id=".$this->shopid." and id=".$id." and state=1")->find();
		if($result)
		{
			unset($data_array);
			$data_array=array(
				'state' => 2,
			);
			$res=$this->shoproom->where("id=".$id)->save($data_array);
			if($res)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='锁定成功！';
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
	
	//锁定设为空净
	public function UpdateFree()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请选择房间';
			outJson($data);
		}
		$result=$this->shoproom->where("shop_id=".$this->shopid." and id=".$id." and state=2")->find();
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
				$data['msg']='设置成功！';
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

}
?>