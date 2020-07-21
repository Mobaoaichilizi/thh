<?php
// +----------------------------------------------------------------------
// | 排版设置
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class SchedulingController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shopgroup = M("ShopGroup"); //分组表
		$this->shopmasseur = M("ShopMasseur"); //技师表
		$this->shopgroupmasseur = M("ShopGroupmasseur"); //分组技师表
		$this->shoptime = M("ShopTime"); //班次表
		$this->shopscheduling = D("ShopScheduling"); //排班表
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
	}
	public function get_index()
	{
		$name = I('post.name');
		$limit = I('post.pagesize',10,'intval');
		$page = I('post.pagecur',1,'intval');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['shop_id']=$this->shopid;
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->shopscheduling->where($where)->count();
		$rolelist = $this->shopscheduling
            ->where($where)
            ->order("id asc")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['is_day'] = $row['is_day']==1 ? '<button class="layui-btn layui-btn-xs">是</button>' : '<button class="layui-btn layui-btn-primary layui-btn-xs">否</button>';
			$row['start_time']=date("Y-m-d H:i:s",$row['start_time']);
			$row['end_time']=date("Y-m-d H:i:s",$row['end_time']);
			$row['masseur_name']=$this->shopmasseur->where("id=".$row['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_sn');
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	//设置排班
	public function setScheduling()
	{
		$shop_time_list=$this->shoptime->where("status=1 and shop_id=".$this->shop_id)->order("sort asc")->select();
		//$this->assign('shop_time_list',$shop_time_list);
		$shop_group_list=$this->shopgroup->where("shop_id=".$this->shop_id)->order("sort asc")->select();
		foreach($shop_group_list as &$val)
		{
			$val['count']=$this->shopgroupmasseur->where("group_id=".$val['id']." and shop_id=".$this->shop_id)->count();
		}
		//$this->assign('shop_group_list',$shop_group_list);
		$shop_scheduling_info=$this->shopscheduling->where("shop_id=".$this->shopid)->order("id desc")->find();
		if($shop_scheduling_info)
		{
			$day_time=date("Y-m-d",$shop_scheduling_info['start_time']+24*3600);
		}else
		{
			$day_time=date('Y-m-d',strtotime('+1 day'));
		}
		
		unset($data);
		$data['code']=0;
		$data['msg']="获取成功！";
		$data['info']=$shop_time_list;
		$data['data']=$shop_group_list;
		$data['day_time']=$day_time;
		outJson($data);
	}	
	public function addScheduling()
	{
		if(IS_POST){
			$chain_id=I('post.chain_id');
			$shop_id=I('post.shop_id');
			$start_time=I('post.start_time');
			$end_time=I('post.end_time');
			$remaining_days=(strtotime($end_time)-strtotime($start_time))/(24*3600);
			for($i=0;$i<=$remaining_days;$i++)
			{
				unset($shop_group_list);
				$shop_group_list=$this->shopgroup->where("shop_id=".$this->shop_id)->order("sort asc")->select();
				foreach($shop_group_list as $val)
				{
					unset($shop_groupmasseur_list);
					unset($time_id);
					$time_id=$_POST['time_id_'.$val['id']];
					if($time_id=='')
					{
						$this->error('请选择班次!');
					}
					unset($shop_time_info);
					$shop_time_info=$this->shoptime->where("id=".$time_id." and shop_id=".$this->shopid)->find();
					unset($remaining_starttime);
					unset($remaining_endtime);
					if($shop_time_info['is_day']==1)
					{
						$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']))+($i*24*3600);
						$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+(($i+1)*24*3600);
					}else
					{
						$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']))+($i*24*3600);
						$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+($i*24*3600);
					}
					$shop_groupmasseur_list=$this->shopgroupmasseur->where("group_id=".$val['id']." and shop_id=".$this->shopid)->order("id asc")->select();
					foreach($shop_groupmasseur_list as $row)
					{
						 unset($data);
						 $data=array(
							'chain_id' => $chain_id,
							'shop_id' => $shop_id,
							'masseur_id' => $row['masseur_id'],
							'time_id' => $time_id,
							'start_time' => $remaining_starttime,
							'end_time' => $remaining_endtime,
							'status' => $shop_time_info['types'],
							'is_day' => $shop_time_info['is_day'],
						 );
						 $result=$this->shopscheduling->add($data);
					}
				}
			}
			if ($result!==false) {
				$this->success("添加成功！", U("Scheduling/index"));
			}else
			{
				$this->error('添加失败!');
			}
				
		}else
		{
			$shop_time_list=$this->shoptime->where("status=1 and shop_id=".$this->shop_id)->order("sort asc")->select();
			$this->assign('shop_time_list',$shop_time_list);
			$shop_group_list=$this->shopgroup->where("shop_id=".$this->shop_id)->order("sort asc")->select();
			foreach($shop_group_list as &$val)
			{
				$val['count']=$this->shopgroupmasseur->where("group_id=".$val['id']." and shop_id=".$this->shop_id)->count();
			}
			$this->assign('shop_group_list',$shop_group_list);
			$shop_scheduling_info=$this->shopscheduling->where("shop_id=".$this->shopid)->order("id desc")->find();
			if($shop_scheduling_info)
			{
				$day_time=date("Y-m-d",$shop_scheduling_info['start_time']+24*3600);
			}else
			{
				$day_time=date('Y-m-d',strtotime('+1 day'));
			}
			$this->assign('day_time',$day_time);
			$this->display('addScheduling');
		}
	}	
}
?>