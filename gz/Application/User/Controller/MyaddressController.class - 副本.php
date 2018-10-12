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
		$token=$_REQUEST['token'];
		accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->user =D("User");
		$this->personaddress =D("Personaddress");
		
	}
	//地址列表
	public function addresslist()
	{
		$uid=I('post.uid');
		$list=$this->personaddress->where("patient_id=".$uid)->order("is_default desc,id desc")->select();
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
			$data['code']=0;
			$data['message']="暂无地址";
			outJson($data);	
		}
		
	}
	//添加地址
	public function addressadd()
	{
		$uid=I('post.uid');
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
			'name' => $name,
			'phone' => $phone,
			'address' => $address,
			'province' => $province,
			'city' => $city,
			'district' => $district,
		);
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
	public function addressedit()
	{
		$uid=I('post.uid');
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
			'name' => $name,
			'phone' => $phone,
			'address' => $address,
			'province' => $province,
			'city' => $city,
			'district' => $district,
		);
		$result=$this->personaddress->save($res);
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="编辑成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="编辑失败！";
			outJson($data);	
		}
	}
	

}
?>