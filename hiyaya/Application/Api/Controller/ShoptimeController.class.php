<?php
// +----------------------------------------------------------------------
// | 用户组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class ShoptimeController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shoptime = D("ShopTime");
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
		$count=$this->shoptime->where($where)->count();
		$rolelist = $this->shoptime->field("id,name,start_time,end_time,status,is_day,sort")
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['status'] = $row['status']==1 ? '启用' : '禁用';
			$row['is_day'] = $row['is_day']==1 ? '是' : '否';
		}
		unset($data);
		$data['code']=0;
		$data['msg']='获取成功！';
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	//添加班次
	public function addShoptime()
	{
		$name=I('post.name');
		$start_time=I('post.start_time');
		$end_time=I('post.end_time');
		$status=I("post.status",0,'intval');
		$is_day=I("post.is_day",0,'intval');
		$types=I('post.types');
		$sort=I('post.sort');
		if(empty($name))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入班次名称";
			outJson($data);
		}
		if(empty($start_time))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择开始时间";
			outJson($data);
		}
		if(empty($end_time))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择结束时间";
			outJson($data);
		}
		if(empty($status))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择状态";
			outJson($data);
		}
		if(empty($is_day))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择是否跨天";
			outJson($data);
		}
		if(empty($types))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择班次";
			outJson($data);
		}
		if(empty($sort))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入排序";
			outJson($data);
		}
		$count=$this->shoptime->where("shop_id=".$this->shopid." and types=".$types)->count();
		if($count > 0)
		{
			$this->error('每个班次只能添加一次!');
		}
		unset($data_array);
		$data_array=array(
			'name' => $name,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'status' => $status,
			'is_day' => $is_day,
			'types' => $types,
			'sort' => $sort,
			'chain_id' => $this->chainid,
			'shop_id' => $this->shopid,
		);
		$result=$this->shoptime->add($data_array);
		if ($result!==false) {	
			unset($data);
			$data['code']=0;
			$data['msg']="添加成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="添加失败";
			outJson($data);
		}
		
	}
	//编辑查看班次
	public function showShoptime()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的班次不存在";
			outJson($data);
		}
		$result=$this->shoptime->field("id,name,start_time,end_time,status,is_day,types,sort")->where("id=".$id)->find();
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']="获取成功！";
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的班次不存在";
			outJson($data);
		}
	}
	//提交班次编辑
	public function editShoptime()
	{
		$id=I('post.id');
		$name=I('post.name');
		$start_time=I('post.start_time');
		$end_time=I('post.end_time');
		$status=I("post.status",0,'intval');
		$is_day=I("post.is_day",0,'intval');
		$sort=I('post.sort');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的班次不存在";
			outJson($data);
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入班次名称";
			outJson($data);
		}
		if(empty($start_time))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择开始时间";
			outJson($data);
		}
		if(empty($end_time))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择结束时间";
			outJson($data);
		}
		if(empty($status))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择状态";
			outJson($data);
		}
		if(empty($is_day))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请选择是否跨天";
			outJson($data);
		}
		if(empty($sort))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入排序";
			outJson($data);
		}
		unset($data_array);
		$data_array=array(
			'name' => $name,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'status' => $status,
			'is_day' => $is_day,
			'sort' => $sort,
			'chain_id' => $this->chainid,
			'shop_id' => $this->shopid,
		);
		$result=$this->shoptime->where("id=".$id)->save($data_array);
		if ($result!==false) {	
			unset($data);
			$data['code']=0;
			$data['msg']="编辑成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="编辑失败";
			outJson($data);
		}
	}
	//删除班次
	public function delShoptime()
	{
		$id = I('post.id',0,'intval');
		if ($this->shoptime->delete($id)!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']="删除成功！";
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']="删除失败！";
			outJson($data);
		}
	}
	
}
?>