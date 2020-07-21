<?php
// +----------------------------------------------------------------------
// | 我的订单接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class OrderController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->order =D("Order");
		$this->order_goods = D('OrderGoods');
		$this->goods = D('Goods');
		$this->setting = D('Setting');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//订单列表
	public	function order_list()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$status_type=!empty($_POST['status_type']) ? $_POST['status_type'] : 0;
		$p=($p-1)*$limit;
		if($status_type!==0)
		{
			$sqlwhere.=" and status_type=".$status_type;
		}
		$result = $this->order->field("id,order_sn,order_price,status_type")->where("user_id=".$uid." and is_show = 0 ".$sqlwhere)->order("createtime desc")->limit($p.",".$limit)->select();
		foreach ($result as &$row) {
			$row['goods_list']=$this->order_goods->field("goods_id,goods_name,goods_desc,goods_img,number,price")->where("order_id='".$row['id']."'")->select();
		}
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "列表加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无订单";
			$data['info'] = array();
			outJson($data);
		}
		
	}
	//删除订单
	public function del_order()
	{
		$id = I('post.id');//订单ID
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="订单不存在";
			outJson($data);	
		}
		$uid = $this->uid;
		$result = $this->order->where("id=".$id)->save(array('is_show' => 1));
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "删除成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "订单不存在";
			outJson($data);
		}
	}
	//确认收货
	public function order_confirm()
	{
		$id = I('post.id');//订单ID
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="订单不存在";
			outJson($data);	
		}
		$result=$this->order->where("id=".$id)->save(array('status_type' => 125));
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "确认完成";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "订单不存在";
			outJson($data);
		}
	}
	public function order_details()
	{
		$id = I('post.id');//订单ID
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="订单不存在";
			outJson($data);	
		}
		$result=$this->order->field("id,order_sn,order_price,distribution,status_type,name,phone,address,note")->where("id=".$id)->find();
		if($result){
			$result['goods_list']=$this->order_goods->field("goods_id,goods_name,goods_desc,goods_img,number,price")->where("order_id='".$result['id']."'")->select();
			unset($data);
			$data['code'] = 1;
			$data['message'] = "获取成功";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "订单不存在";
			outJson($data);
		}
		
	}
}
?>