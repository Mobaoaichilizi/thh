<?php
// +----------------------------------------------------------------------
// | 用户组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class SchedulingController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shopgroup = M("ShopGroup"); //分组表
		$this->shopmasseur = M("ShopMasseur"); //技师表
		$this->shopgroupmasseur = M("ShopGroupmasseur"); //分组技师表
		$this->shoptime = M("ShopTime"); //班次表
		$this->shopscheduling = D("ShopScheduling"); //排班表
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index()
	{
        $this->display('index');
    }
	public function get_index()
	{
		$nick_name = I('request.nick_name');
		$start_time = I('request.start_time');
		$end_time = I('request.end_time');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		//$where['shop_id']=$this->shopid;
		if($start_time!='' && $end_time!='')
		{
			$start_time=strtotime($start_time);
			$end_time=strtotime($end_time)+24*3600-1;
			$where.= " and start_time >=".$start_time." and start_time <= ".$end_time;
			//$where['start_time'] = array(array('egt',$start_time),array('elt',$end_time));
		}
		if($nick_name!='')
		{
			//$masseur_id=$this->shopmasseur->where("nick_name like '%".$nick_name."%'")->select();
			$where.= " and (masseur_id in (select id from dade_shop_masseur where nick_name = '".$nick_name."'))";
		}
		
		$count=$this->shopscheduling->where("shop_id=".$this->shopid.$where)->count();
		$rolelist = $this->shopscheduling
            ->where("shop_id=".$this->shopid.$where)
            ->order("id desc")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['is_day'] = $row['is_day']==1 ? '<button class="layui-btn layui-btn-xs">是</button>' : '<button class="layui-btn layui-btn-primary layui-btn-xs">否</button>';
			$row['start_time_str']=$row['start_time'];
			$row['now_time']=strtotime(date('Y-m-d',time()));
			$row['start_time']=date("Y-m-d H:i:s",$row['start_time']);
			$row['end_time']=date("Y-m-d H:i:s",$row['end_time']);
			$row['masseur_name']=$this->shopmasseur->where("id=".$row['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$row['nick_name']=$this->shopmasseur->where("id=".$row['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			if($row['status']==1)
			{
				$row['status_name']="早班";
			}else if($row['status']==2)
			{
				$row['status_name']="中班";
			}else if($row['status']==3)
			{
				$row['status_name']="晚班";
			}else if($row['status']==4)
			{
				$row['status_name']="休假";
			}else if($row['status']==5)
			{
				$row['status_name']="请假";
			}else if($row['status']==6)
			{
				$row['status_name']="工休";
			}else if($row['status']==7)
			{
				$row['status_name']="病假";
			}else if($row['status']==8)
			{
				$row['status_name']="事假";
			}else if($row['status']==9)
			{
				$row['status_name']="加班";
			}
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	public function addScheduling()
	{
		if(IS_POST){
			$chain_id=I('post.chain_id');
			$shop_id=I('post.shop_id');
			$start_time=I('post.start_time');
			$end_time=I('post.end_time');
			
			$last_number=$this->shopscheduling->where("chain_id=".$chain_id." and shop_id=".$shop_id)->order("end_time desc")->getField("number");
			if($last_number)
			{
				$last_number=$last_number+1;
			}else
			{
				$last_number=1;
			}
			if(strtotime($start_time) > strtotime($end_time))
			{
				$this->error('开始时间不能大于结束时间!');
			}
			//删除以后的排班
			$delete_result=$this->shopscheduling->where("chain_id=".$chain_id." and shop_id=".$shop_id." and start_time > ".strtotime($start_time))->delete();
			
			$remaining_days=(strtotime($end_time)-strtotime($start_time))/(24*3600);
			for($i=0;$i<=$remaining_days;$i++)
			{
				unset($shop_group_list);
				$shop_group_list=$this->shopgroup->where("shop_id=".$this->shopid)->order("sort asc")->select();
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
							'number' => $last_number,
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
			$shop_time_list=$this->shoptime->where("status=1 and shop_id=".$this->shopid)->order("sort asc")->select();
			$this->assign('shop_time_list',$shop_time_list);
			$shop_group_list=$this->shopgroup->where("shop_id=".$this->shopid)->order("sort asc")->select();
			foreach($shop_group_list as &$val)
			{
				$val['count']=$this->shopgroupmasseur->where("group_id=".$val['id']." and shop_id=".$this->shopid)->count();
			}
			$this->assign('shop_group_list',$shop_group_list);
			$shop_scheduling_info=$this->shopscheduling->where("shop_id=".$this->shopid)->order("id desc")->find();
			if($shop_scheduling_info)
			{
				//$day_time=date('Y-m-d',time());
				$day_time=date('Y-m-d',strtotime('+1 day'));
			}else
			{
				//$day_time=date('Y-m-d',time());
				$day_time=date('Y-m-d',strtotime('+1 day'));
			}
			$this->assign('day_time',$day_time);
			$this->display('addScheduling');
		}
	}
	
	//编辑排班
	public function editScheduling()
	{
		if(IS_POST){
			$id = I('post.id',0,'intval');
			$status = I('post.status',0,'intval');
			if(empty($id) || empty($status))
			{
				$this->error("请选择要换的班次");
			}
			$info=$this->shopscheduling->where("id=".$id)->find();
			$start_time=date("Y-m-d",$info['start_time']);
			if($status==1)
			{
				$shop_time_info=$this->shoptime->where("shop_id=".$this->shopid." and types=".$status." and status=1")->find();
				if($shop_time_info['is_day']==1)
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+(24*3600);
				}else
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']));
				}
				unset($data_array);
				$data_array=array(
					'time_id' => $shop_time_info['id'],
					'start_time' => $remaining_starttime,
					'end_time' => $remaining_endtime,
					'status' => $status,
					'is_day' => $shop_time_info['is_day'],
				);
			}else if($status==2)
			{
				$shop_time_info=$this->shoptime->where("shop_id=".$this->shopid." and types=".$status." and status=1")->find();
				if($shop_time_info['is_day']==1)
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+(24*3600);
				}else
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']));
				}
				unset($data_array);
				$data_array=array(
					'time_id' => $shop_time_info['id'],
					'start_time' => $remaining_starttime,
					'end_time' => $remaining_endtime,
					'status' => $status,
					'is_day' => $shop_time_info['is_day'],
				);
			}else if($status==3)
			{
				$shop_time_info=$this->shoptime->where("shop_id=".$this->shopid." and types=".$status." and status=1")->find();
				if($shop_time_info['is_day']==1)
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+(24*3600);
				}else
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']));
				}
				unset($data_array);
				$data_array=array(
					'time_id' => $shop_time_info['id'],
					'start_time' => $remaining_starttime,
					'end_time' => $remaining_endtime,
					'status' => $status,
					'is_day' => $shop_time_info['is_day'],
				);
			}else if($status==4)
			{
				unset($data_array);
				$data_array=array(
					'status' => $status
				);
			}else if($status==5)
			{
				unset($data_array);
				$data_array=array(
					'status' => $status
				);
			}else if($status==6)
			{
				unset($data_array);
				$data_array=array(
					'status' => $status
				);
			}else if($status==7)
			{
				unset($data_array);
				$data_array=array(
					'status' => $status
				);
			}else if($status==8)
			{
				unset($data_array);
				$data_array=array(
					'status' => $status
				);
			}else if($status==9)
			{
				$shop_time_info=$this->shoptime->where("shop_id=".$this->shopid." and types=".$status." and status=1")->find();
				if($shop_time_info['is_day']==1)
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']))+(24*3600);
				}else
				{
					$remaining_starttime=strtotime(date($start_time.$shop_time_info['start_time']));
					$remaining_endtime=strtotime(date($start_time.$shop_time_info['end_time']));
				}
				unset($data_array);
				$data_array=array(
					'time_id' => $shop_time_info['id'],
					'start_time' => $remaining_starttime,
					'end_time' => $remaining_endtime,
					'status' => $status,
					'is_day' => $shop_time_info['is_day'],
				);
			}
			$result=$this->shopscheduling->where("id=".$id)->save($data_array);
			if($result)
			{
				$this->success("编辑成功！");
			}else
			{
				$this->error("编辑失败！");
			}
			
		}else
		{
			$id = I('get.id',0,'intval');
			$info=$this->shopscheduling->where("id=".$id)->find();
			$info['masseur_name']=$this->shopmasseur->where("id=".$info['masseur_id']." and shop_id=".$this->shopid)->getField('masseur_name');
			$info['nick_name']=$this->shopmasseur->where("id=".$info['masseur_id']." and shop_id=".$this->shopid)->getField('nick_name');
			$this->assign('info',$info);
			$shop_time_list=$this->shoptime->where("status=1 and shop_id=".$this->shopid)->order("sort asc")->select();
			$this->assign('shop_time_list',$shop_time_list);
			$this->display('editScheduling');
		}
	}

	//删除排班
	public function delScheduling()
	{
		$id = I('post.id',0,'intval');
		if ($this->shopscheduling->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>