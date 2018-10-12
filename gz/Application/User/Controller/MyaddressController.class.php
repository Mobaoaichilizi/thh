<?php
// +----------------------------------------------------------------------
// | 我的地址管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MyaddressController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->personaddress =D("Personaddress");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//地址列表
	public function address_list()
	{
		$uid=$this->uid;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$list=$this->personaddress->field("id,province,city,district,phone,person,address,is_default")->where("patient_id=".$uid)->order("is_default desc,id desc")->select();
		if($list)
		{
			unset($data);
			$data['code']=1;
			$data['message']="地址列表获取成功！";
			$data['info']=$list;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无地址";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//添加地址
	public function address_add()
	{
		$uid=$this->uid;
		$name=I('post.name');
		$phone=I('post.phone');
		$address=I('post.address');
		$province=I('post.province');
		$city=I('post.city');
		$district=I('post.district');
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入收货人姓名！";
			outJson($data);	
		}
		if(empty($phone))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入收货人电话！";
			outJson($data);	
		}
		if(empty($address))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入详细地址！";
			outJson($data);	
		}
		$res=array(
			'patient_id' => $uid,
			'person' => $name,
			'phone' => $phone,
			'address' => $address,
			'province' => $province,
			'city' => $city,
			'district' => $district,
		);
		$count=$this->personaddress->where("patient_id=".$uid)->count();
		if($count > 5)
		{
			unset($data);
			$data['code']=0;
			$data['message']="最多5个收货地址！";
			outJson($data);	
		}
		$result=$this->personaddress->add($res);
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="添加成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="添加失败！";
			outJson($data);	
		}
		
	}
	//编辑地址
	public function address_edit()
	{
		$uid=$this->uid;
		$id=I('post.id');
		$name=I('post.name');
		$phone=I('post.phone');
		$address=I('post.address');
		$province=I('post.province');
		$city=I('post.city');
		$district=I('post.district');
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="您编辑的地址不存在！";
			outJson($data);	
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入收货人姓名！";
			outJson($data);	
		}
		if(empty($phone))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入收货人电话！";
			outJson($data);	
		}
		if(empty($address))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入详细地址！";
			outJson($data);	
		}
		$res=array(
			'id' => $id,
			'patient_id' => $uid,
			'person' => $name,
			'phone' => $phone,
			'address' => $address,
			'province' => $province,
			'city' => $city,
			'district' => $district,
		);
		$result=$this->personaddress->save($res);
		unset($data);
		$data['code']=1;
		$data['message']="编辑成功！";
		outJson($data);
		
	}
	//删除地址
	public function address_del()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="您删除的地址不存在！";
			outJson($data);	
		}
		$result=$this->personaddress->where("id=".$id)->delete();
		unset($data);
		$data['code']=1;
		$data['message']="删除成功！";
		outJson($data);
		
	}
	//设为默认
	public function address_default()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="您删除的地址不存在！";
			outJson($data);	
		}
		$res=array(
			'is_default' => 1,
		);
		$this->personaddress->where("patient_id=".$this->uid)->save(array('is_default' => 0));
		$result=$this->personaddress->where("id=".$id." and patient_id=".$this->uid)->save($res);
		unset($data);
		$data['code']=1;
		$data['message']="设置成功！";
		outJson($data);
		
	}
	

}
?>