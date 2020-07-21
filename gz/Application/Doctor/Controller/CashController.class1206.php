<?php
// +----------------------------------------------------------------------
// | 提现接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class CashController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->cash = D('Cash');
		$this->user = D('User');
		$this->setting = D('Setting');
		$this->bank = D('Bank');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public function getbalance()
	{
		$id = $this->uid;
		$balance = $this->user->where('id='.$id)->getfield('balance');
		unset($data);
		$data['code'] = 1;
		$data['message'] = "余额加载成功！";
		$data['balance'] = $balance;
		outJson($data);
		
	}	
public	function bank_info()
	{
		$id = $this->uid;
		$personname = I('post.personname');
		$banknumber = I('post.banknumber');
		$title = I('post.title');
		
		if(empty($personname))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入开户人姓名！";
			outJson($data);
		}
		if(empty($banknumber))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入银行卡号！";
			outJson($data);
		}
		if(empty($title))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入开户行！";
			outJson($data);
		}else{
			$setting_id = $this->setting->where("title = '".$title."'")->getfield('id');
			if(empty($setting_id)){
				$ress = array(
					'title' => $title,
					'parentid' => 29,
				);
				$setting_id = $this->setting->add($ress);
			}
		}
		
		$res=array(
			'user_id' => $id,
			'pay_money' => $pay_money,
			'personname' => $personname,
			'banknumber' => $banknumber,
			'setting_id' => $setting_id,
		);
		$result=$this->cash->add($res);
		
		if($result!==false)
		{
			
			unset($data);
			$data['code']=1;
			$data['message']="银行卡信息保存成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="银行卡信息保存失败！";
			outJson($data);
		}
		
	}
public function do_cash()
	{
		$id = $this->uid;
		$pay_money = I('post.pay_money');
		if(empty($pay_money))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入转出金额！";
			outJson($data);
		}
		$balance = $this->user->where('id='.$id)->getfield('balance');
		$bala = $balance - $pay_money;
		if($bala < 0){
			unset($data);
			$data['code']=0;
			$data['message']="余额不足！";
			outJson($data);
		}
		$ress=array(
			'balance' => $bala,
		);
		$results=$this->user->where('id='.$id)->save($ress);
		if($results!==false){
			// setpoints($results,1,$pay_money,'提现',0);
			unset($data);
			$data['code']=1;
			$data['message']="提现成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code']=0;
			$data['message']="提现失败！";
			outJson($data);
		}

	}
public function bank_list()
	{
		$id = $this->uid;
		$result = $this->bank->field("setting_id,banknumber")->where("user_id='".$id."'")->select();
		if(empty($result)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无绑定银行卡！";
			outJson($data);
		}
		foreach ($result as $k=>$vo) {
			$results[$k] = $this->setting->field("id,title,description,img_thumb")->where("id='".$vo['setting_id']."'")->find();
			$results[$k]['banknumber'] = $vo['banknumber'];
		}
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "列表显示成功！";
			$data['info'] = $results;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}
	}
public function add_bankcard()
	{
		$id = $this->uid;
		$personname = I('post.personname');
		$banktitle = I('post.banktitle');
		$banknumber = I('post.banknumber');
		if(empty($personname)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入开户人姓名！";
			outJson($data);
		}
		if(empty($banktitle)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入开户银行名称！";
			outJson($data);
		}else{
			$setting_id = $this->setting->where("title='".$banktitle."'")->getfield("id");
			if(empty($setting_id)){
				$ress = array(
					'title' => $banktitle,
					'parentid' => 29,
				);
				$setting_id = $this->setting->add($ress);
			}
		}
		if(empty($banknumber)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入银行卡号！";
			outJson($data);
		}
		$res = array(
			'personname' => $personname,
			'setting_id' => $setting_id,
			'banknumber' => $banknumber,
			'user_id'    => $id,
			'update_time' => time(),
		);
		$result = $this->bank->add($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "添加成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "添加失败！";
			outJson($data);
		}
	}
public function del_bank()
	{
		$uid = $this->uid;
		$id = I('post.id');
		$result = $this->bank->where("id='".$id."' and user_id='".$uid."'")->delete();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "解绑成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "解绑失败！";
			outJson($data);
		}

	}	
}
?>