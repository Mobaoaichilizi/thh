<?php
// +----------------------------------------------------------------------
// | 个人信息
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: TH2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class PersonController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->user = D("ShopUser");
		$this->shop = D("Shop");
		$this->chain = D("Chain");
		$this->order = D("Orders");
		$this->ordersproject = D("OrdersProject");
		$this->where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
	}
	public function index()
	{
		$user = $this->user->where($this->where.' and id='.$this->user_id)->find();
		$info['user_name'] = $user['name'];
		$info['head_img'] = $user['head_img'];
		if($info['head_img']){
				$info['head_img'] = $this->host_url.$info['head_img'];
		}
		$info['shop_name'] = $this->shop->where('id='.$this->shop_id)->getfield('shop_name');
		$info['business_time'] = $this->shop->where('id='.$this->shop_id)->getfield('business_time');
		$info['success_order'] = $this->order->where($this->where.' and shopuser_id='.$this->user_id.' and status!=-1')->count();
		$info['today_order'] = $this->order->where($this->where.' and shopuser_id='.$this->user_id.' and status!=-1 and createtime>'.strtotime(date('Y-m-d')).' and createtime<'.time())->count();
		$info['today_guest'] = $this->ordersproject->where($this->where.' and shopuser_id='.$this->user_id.' and is_del=0 and createtime>'.strtotime(date('Y-m-d')).' and createtime<'.time())->count();
		$info['phone'] = $this->chain->where('id='.$this->chain_id)->getfield('phone');
		unset($data);
		$data['code'] = 0;
		$data['msg'] = 'success';
		$data['info'] = $info;
		outJson($data);
	}
	//个人信息
	public function person_info(){
		$info = $this->user->where('id='.$this->user_id)->find();
		unset($data);
		$data['code'] = 0;
		$data['msg'] = 'success';
		$data['info'] = $info;
		outJson($data);
	}
	//门店信息
	public function shop_info(){
		$info = $this->shop->where('id='.$this->shop_id)->find();
		unset($data);
		$data['code'] = 0;
		$data['msg'] = 'success';
		$data['info'] = $info;
		outJson($data);  
	}
	//修改密码
	//修改密码
	public function edit_password()
	{
		$old_password=I('post.old_password');
		$password=I('post.password');
		$new_password=I('post.new_password');
		if(empty($old_password))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请输入旧密码';
			outJson($data);
		}
		if(empty($password))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请输入新密码';
			outJson($data);
		}
		if($password!=$new_password)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='两次密码不同！';
			outJson($data);
		}
		$shopmasseur_info = $this->user->where('id='.$this->user_id." and password='".sp_password($old_password)."'")->find();
		if($shopmasseur_info)
		{
			unset($data_array);
			$data_array=array(
				'password' => sp_password($new_password),
			);
			$res=$this->user->where('id='.$this->user_id)->save($data_array);
			if($res)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='修改成功！';
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='修改失败！';
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='原始密码错误！';
			outJson($data);
		}
	}
}
?>