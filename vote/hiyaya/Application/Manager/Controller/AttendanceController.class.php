<?php
// +----------------------------------------------------------------------
// | 考勤统计
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class AttendanceController extends ManagerbaseController {
	
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
		$start_time = I('request.start_time');
		$end_time = I('request.end_time');
		$status=I('request.status');
		$where="shop_id=".$this->shopid;
		
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
            $where.=" and start_time>=".$start_time." and end_time<=".$end_time;
		}
		if($status!='')
        {
            $where.=" and status=".$status;
            $this->assign("status",$status);
        }
		
		$result=$this->shopmasseur->where("shop_id=".$this->shopid." and state=1")->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['early_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=1")->count();
			$val['center_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=2")->count();
			$val['evening_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=3")->count();
			$val['rest_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=4")->count();
			$val['ask_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=5")->count();
			$val['holiday_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=6")->count();
			$val['sick_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=7")->count();
			$val['private_days']=$this->shopscheduling->where($where." and masseur_id=".$val['id']." and status=8")->count();
		}
		
		$this->assign("result",$result);
		
        $this->display('index');
    }
}
?>