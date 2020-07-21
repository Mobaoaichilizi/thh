<?php
// +----------------------------------------------------------------------
// | 电话咨询详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class TeldetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->telephone =D("Telephone");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user =D("User");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield("id");
	}

public	function tel_details()
	{
		$uid = $this->uid;//用户的ID
		$id = I('post.id');//电话咨询的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->telephone->field("id,patientmember_id,tel_date,money,morn_after,status")->where("id='".$id."'")->find();
		
			$patient = $this->patient->field("user_id,name,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
			$results['id'] = $id;
			$results['name'] = $patient['name'];
			$results['user_id'] = $patient['user_id'];
			$results['img_thumb'] = $patient['img_thumb'];
			$results['phone'] = $this->user->where("id=".$result['patientmember_id'])->getfield('username');
			$results['morn_after'] = $result['morn_after'];
			$results['status'] = $result['status'];
			$results['price'] = $result['money'];
			$results['tel_date'] = date('Y年m月d日',$result['tel_date']);
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
//退还诊费
public function exit_pay(){
		$id = I('post.id');
		$id = 40;
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择的咨询！";
			outJson($data);
		}
		$uid = $this->uid;
		$create_time = $this->telephone->where("id=".$id)->getfield("create_time");
		$status = $this->telephone->where("id=".$id)->getfield("status");
		$is_replay = $this->telephone->where("id=".$id)->getfield("is_replay");
		$ress = $this->telephone->where("id=".$id)->getfield("money");
		$patient = $this->telephone->where("id=".$id)->getfield("patientmember_id");
	
	    if($is_replay == 0){
	    	if($uid == $patient){
	    		$res = array(
					'status' => 5,
				);
	    	}else{
		    	$res = array(
					'status' => 4,
				);
		    }
			$result = $this->telephone->where('id='.$id)->save($res);
			
			if($result){
				$balance = $this->user->where('id='.$patient)->getField('balance');
				// dump($this->user->getlastsql());
				$ba = $balance + $ress;
				// dump($ba);
				$money = array(
					'balance' => $ba,
				);
				$this->user->where('id='.$patient)->save($money);
				// dump($this->user->getlastsql());
	     		financial_log($patient,$ress,3,$ba,'咨询退款',12);
				unset($data);
				$data['code'] = 1;
				$data['message'] = "退还成功！";
				$data['info'] = $ress;
				outJson($data);
			}else{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "退还失败！";
				outJson($data);
			}
	    }else{
	    	unset($data);
			$data['code'] = 0;
			$data['message'] = "不支持退还！";
			outJson($data);
	    }
		
	}
//结束咨询
public function exit_consult(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 6,
		);

		$money = $this->telephone->where("id=".$id)->getfield('money');
		$money = $money - $money*C('TEL_NAME')/100;
		setpoints($uid,1,$money,'元','166',0);
		$balance = $this->user->where('id='.$uid)->getField('balance');
		financial_log($uid,$money,3,$balance,'咨询收益',2);

		$result = $this->telephone->where('id='.$id)->save($res);
		$patient_id=$this->telephone->where('id='.$id)->getField('patientmember_id');
		if($result){
			$doctor_phone=$this->user->wh.ere("id='".$patient_id."' and role=2")->find();
			if($doctor_phone['member_level'] == "0"){
				setpoints($patient_id,2,10,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,10,3,$score,'咨询赠送',2,2);
			}else if($doctor_phone['member_level'] == "1"){
				setpoints($patient_id,2,20,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,20,3,$score,'咨询赠送',2,2);
			}else if($doctor_phone['member_level'] == "2"){
				setpoints($patient_id,2,30,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,30,3,$score,'咨询赠送',2,2);
			}
			if($doctor_phone['deviceid']!='')
			{
				$title="";
				$content = "亲,您有电话咨询结束！";
				$device[] = $doctor_phone['deviceid'];
				$extra = array("type" => "3", "type_id" => $id,'user_type' => 2,'status' => 2);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$doctor_phone['os'];
				jpush_send($title,$content,$audience,$os,$extras);
			}
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
	//一键问诊
	public function visits(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
			'is_replay' => 1,
		);
		$result = $this->telephone->where('id='.$id)->save($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "问诊成功！";
			outJson($data);
		}

	}

}
?>