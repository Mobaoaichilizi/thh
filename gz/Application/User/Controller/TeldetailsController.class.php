<?php
// +----------------------------------------------------------------------
// | 电话咨询详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class TeldetailsController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->telephone =D("Telephone");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user =D("User");
		$this->uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield("id");
	}

public	function tel_details()
	{
		$uid = $this->uid;//用户的ID
		$id = I('post.id');//咨询的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->telephone->field("id,member_id,patientmember_id,name,age,phone,tel_date,money,morn_after,status")->where("id='".$id."'")->find();
			$doctor = $this->doctor->field("name,img_thumb,professional,telephone_price")->where("user_id='".$result['member_id']."'")->find();
			
			$results['id'] = $result['id'];
			$results['pname'] = $result['name'];
			$results['patient_id'] = $uid;
			$results['age'] = $result['age'];
			$results['phone'] = str_pad_star($result['phone'],7,'****','left');
			$results['dname'] = $doctor['name'];
			$results['doctor_img'] = $doctor['img_thumb'];
			$results['professional'] = $doctor['professional'];
			$results['status'] = $result['status'];
			$results['morn_after'] = $result['morn_after'];
			$results['price'] = $result['money'];
			$results['tel_date'] = time_tran($result['tel_date']);
			$results['week'] = weekday($result['tel_date']);
		
		if($results)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $results;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息加载失败！";
			outJson($data);
		}
		
	}
//申请退款
public function apply_refund(){
		$id = I('post.id');
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择的咨询！";
			outJson($data);
		}
		$uid = $this->uid;
		
	    $status = $this->telephone->where("id=".$id)->getfield("status");
		$is_replay = $this->telephone->where("id=".$id)->getfield("is_replay");
	    if($status == 1 && $is_replay == 0){
	    	$res = array(
			'status' => 5,
			);
			$result = $this->telephone->where('id='.$id)->save($res);
			$ress = $this->telephone->where("id=".$id)->getfield("money");
			if($result){
				$balance = $this->user->where('id='.$uid)->getField('balance');
				
				$ba = $balance + $ress;
				$money = array(
					'balance' => $ba,
				);
				$this->user->where('id='.$uid)->save($money);
			
	     		financial_log($uid,$ress,3,$ba,'咨询退款',12);
				unset($data);
				$data['code'] = 1;
				$data['message'] = "申请退款成功！";
				$data['pay'] = $ress;
				outJson($data);
			}else{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "申请退款失败！";
				outJson($data);
			}
	    }else{
	    	unset($data);
			$data['code'] = 0;
			$data['message'] = "不支持退款！";
			outJson($data);
	    }
		
	}

//结束咨询
	public function exit_pay(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
		);
		$result = $this->telephone->where('id='.$id)->save($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "结束成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "结束失败！";
			outJson($data);
		}
	}
}
?>