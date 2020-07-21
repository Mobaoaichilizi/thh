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
		$bank=array();
		$bank = $this->bank->field("id,setting_id,banknumber")->where("user_id=".$id." and is_acquiescence=1")->find();
		
		if(empty($bank)){
			$bank = $this->bank->field("id,setting_id,banknumber")->where("user_id=".$id."")->order("update_time desc")->find();
			if(!empty($bank)){
				$bank['title'] = $this->setting->where('id='.$bank['setting_id'])->getField('title');
				$bank['img_thumb'] = $this->setting->where('id='.$bank['setting_id'])->getField('img_thumb');
			}
			
		}else{
			$bank['title'] = $this->setting->where('id='.$bank['setting_id'])->getField('title');
			$bank['img_thumb'] = $this->setting->where('id='.$bank['setting_id'])->getField('img_thumb');
		}
		$balance = $this->user->where('id='.$id)->getfield('balance');
		$result['balance']=$balance;
		$result['card_info']=$bank;
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "余额加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无数据！";
			outJson($data);
		}
		
		
	}
//选择银行卡
public function choose_bank(){
	$lists = $this->setting->field('id,title')->where('parentid=29')->select();
	if($lists){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "列表加载成功！";
		$data['info'] = $lists;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 1;
		$data['message'] = "暂无分类！";
		$data['info'] = array();
		outJson($data);
	}
}
//输入银行卡信息		
public	function bank_info()
	{
		$id = $this->uid;
		$personname = I('post.personname');
		$banknumber = I('post.banknumber');
		$setting_id = I('post.setting_id');
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
		if(empty($setting_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择开户行！";
			outJson($data);
		}
		
		$res=array(
			'user_id' => $id,
			'personname' => $personname,
			'banknumber' => $banknumber,
			'setting_id' => $setting_id,
			'update_time'=> time(),
		);
		$result=$this->bank->add($res);
		
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
		$bank_id = I('post.bank_id');
		$pay_money = I('post.pay_money');
		$ordernumber = date("YmdHis").rand(10000,99999).'_'.$bank_id;
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
			$data['code']=10003;
			$data['message']="余额不足！";
			outJson($data);
		}
		$ress=array(
			'balance' => $bala,
		);

		$results=$this->user->where('id='.$id)->save($ress);
		$bank_list = $this->bank->where("id=".$bank_id)->find();
		
		$bankname = $this->setting->where('id='.$bank_list['setting_id'])->getfield('title');
		$resu = array(
			'ordernumber' => $ordernumber,
			'pay_money'=> $pay_money,
			'bank_id'  => $bank_id,
			'banknumber' => $bank_list['banknumber'],
			'bankname' => $bankname,
			'person' => $bank_list['personname'],
			'user_id'  => $id,
			'create_time'=>time(),
		);
		$rest = $this->cash->add($resu);
		if($rest!==false){
			$res['is_acquiescence']=0;
			$ress['is_acquiescence']=1;
			$banks = $this->bank->where("user_id=".$id)->save($res);
			$banks = $this->bank->where("user_id=".$id." and id=".$bank_id."")->save($ress);
			financial_log($id,$pay_money,2,$bala,'账户提现',7);
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
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->bank->field("id,setting_id,banknumber,is_acquiescence")->where("user_id=".$id." and status=1")->limit($p.",".$limit)->select();
		if(empty($result)){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无绑定银行卡！";
			$data['info'] = array();
			outJson($data);
		}
		foreach ($result as $k=>$vo) {
			$results[$k] = $this->setting->field("title,description,img_thumb")->where("id='".$vo['setting_id']."'")->find();
			$results[$k]['banknumber'] = $vo['banknumber'];
			$results[$k]['is_acquiescence'] = $vo['is_acquiescence'];
			$results[$k]['id'] = $vo['id'];
		}
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "列表显示成功！";
			$data['info'] = $results;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "数据加载失败！";
			$data['info'] = array();
			outJson($data);
		}
	}

public function del_bank()
	{
		$uid = $this->uid;
		$id = I('post.id');
		$res = array(
			'id' => $id,
			'status' => 0,
		);
		$result = $this->bank->save($res);
		if($result!==false){
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
public function bank()
	{
		$uid = $this->uid;
		$lists = $this->setting->field("id,title,description,img_thumb")->where("parentid=29")->select();
		if($lists){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "列表加载成功！";
			$data['info'] = $lists;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无列表！";
			$data['info'] = array();
			outJson($data);
		}
	}
}
?>