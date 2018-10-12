<?php
// +----------------------------------------------------------------------
// | 预约就诊详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class ReservedetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->reserve =D("Reserve");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user =D("User");
		$this->sms = D("Sms");
		$this->systemsinfo =D("SystemsInfo");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield("id");
	}

public	function reserve_details()
	{

		$uid = $this->uid;//用户的ID
		$id = I('post.id');//预约诊疗的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->reserve->field("id,patientmember_id,res_date,money,morn_after,status")->where("id='".$id."'")->find();
		
			$patient = $this->patient->field("user_id,name,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
			$results['id'] = $id;
			$results['name'] = $patient['name'];
			$results['user_id'] = $patient['user_id'];
			$results['img_thumb'] = $patient['img_thumb'];
			$results['phone'] = $this->user->where("id=".$result['patientmember_id'])->getfield('username');
			$results['address'] = $this->doctor->where("user_id=".$uid)->getfield("address");
			$results['morn_after'] = $result['morn_after'];
			$results['status'] = $result['status'];
			
			$results['price'] = $result['money'];
			$results['res_date'] = date('Y年m月d日',$result['res_date']);
			$results['week'] = weekday($result['res_date']);

		
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
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择的咨询！";
			outJson($data);
		}
		$uid = $this->uid;
		$create_time = $this->reserve->where("id=".$id)->getfield("create_time");
		$status = $this->reserve->where("id=".$id)->getfield("status");
		$is_replay = $this->reserve->where("id=".$id)->getfield("is_replay");
		$ress = $this->reserve->where("id=".$id)->getfield("money");
		$patient = $this->reserve->where("id=".$id)->getfield("patientmember_id");
		$doctor_id = $this->reserve->where("id=".$id)->getfield("member_id");
		$doctor_phone = $this->user->where("id=".$doctor_id)->getField('username');
		$patient_phone = $this->user->where("id=".$patient)->getField('username');
		$name = $this->user->where("id=".$uid)->getfield("username");
		$code = "预约就诊";

	    if($status == 1 || $status == 2){
	    	if($uid == $patient){
	    		$rest = array(
					'status' => 5,
				);
				$str='您好，'.$name.'患者取消对您的'.$code.'，并已申请退款。';
				require_once VENDOR_PATH."SmsApi/SmsApi.php";
				$aliyunsms = new SmsApi(C('WEB_SMS_USER'),C('WEB_SMS_PASS'));
				$response = $aliyunsms->sendSms(
					"广正云医", // 短信签名
					"SMS_126971334", // 短信模板编号
					$doctor_phone, // 短信接收者
					Array (  // 短信模板中字段的值
						"name"=>$name,
						"code"=>$code,
					)
				);
				$result= json_decode(json_encode($response),true);

				$uid_phone = $this->user->where("id=".$doctor_id)->find();

				if($uid_phone['deviceid']!='')
				{
					$title="";
					$content = "亲,您有预约就诊被取消，请注意查看！";
					$device[] = $uid_phone['deviceid'];
					$extra = array("type" => "4", "type_id" => $id,'user_type' => 1,'status' => 5);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$uid_phone['os'];
					jpush_send($title,$content,$audience,$os,$extras);
					$res_sys_info=array(
					"title" => $title,
					"description" => $content,
					"send_uid" => $patient,
					"receive_uid" =>$doctor_id,
					"type_id" => $id,
					"type" => 4,
					"createtime" => time(),
				);
				$this->systemsinfo->add($res_sys_info);
			}
	    	}else{
		    	$rest = array(
					'status' => 4,
				);
				$str='您好，'.$name.'医生对您发起的'.$code.'进行了退款，可在个人中心中进行查看。';
				require_once VENDOR_PATH."SmsApi/SmsApi.php";
				$aliyunsms = new SmsApi(C('WEB_SMS_USER'),C('WEB_SMS_PASS'));
				$response = $aliyunsms->sendSms(
					"广正云医", // 短信签名
					"SMS_126781718", // 短信模板编号
					$patient_phone, // 短信接收者
					Array (  // 短信模板中字段的值
						"name"=>$name,
						"code"=>$code,
					)
				);
				$result= json_decode(json_encode($response),true);
				$uid_phone = $this->user->where("id=".$patient)->find();

				if($uid_phone['deviceid']!='')
				{
					$title="";
					$content = "亲,您有预约就诊被取消，请注意查看！";
					$device[] = $uid_phone['deviceid'];
					$extra = array("type" => "4", "type_id" => $id,'user_type' => 2,'status' => 4);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$uid_phone['os'];
					jpush_send($title,$content,$audience,$os,$extras);
					$res_sys_info=array(
					"title" => $title,
					"description" => $content,
					"send_uid" => $doctor_id,
					"receive_uid" =>$patient,
					"type_id" => $id,
					"type" => 4,
					"createtime" => time(),
				);
				$this->systemsinfo->add($res_sys_info);
		    }
		}
		    $res=array(
				"phone" => $name,
				"createtime" => time(),
				"type" => 5,
				"content" => $str,
				"code" => $code,
			);
			if($result['Code'] == 'OK'){
				$this->sms->add($res);
			}
			unset($result);
			$result = $this->reserve->where('id='.$id)->save($rest);
			
			if($result){
				$balance = $this->user->where('id='.$patient)->getfield('balance');
				$ba = $balance + $ress;
				$money = array(
					'balance' => $ba,
				);
				$this->user->where('id='.$patient)->save($money);
				financial_log($patient,$ress,3,$ba,'预约就诊退款收益',12);
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
	//医生确认
	public function visits(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
			'is_replay' => 1,
		);
		$result = $this->reserve->where('id='.$id)->save($res);
		$info = $this->reserve->where('id='.$id)->find();
		$patient_info = $this->user->where("id=".$info['patientmember_id'])->find();
		if($result){
			if($patient_info['deviceid']!='')
			{
				$title="";
				$content = "亲,您有预约咨询已经开始，请进行确认！";
				$device[] = $patient_info['deviceid'];
				$extra = array("type" => "4", "type_id" => $id,'user_type' => 2,'status' => 2);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$patient_info['os'];
				jpush_send($title,$content,$audience,$os,$extras);
				$res_sys_info=array(
				"title" => $title,
				"description" => $content,
				"send_uid" => $uid,
				"receive_uid" =>$info['patientmember_id'],
				"type_id" => $id,
				"type" => 4,
				"createtime" => time(),
			);
			$this->systemsinfo->add($res_sys_info);
			}
			unset($data);
			$data['code'] = 1;
			$data['message'] = "预约就诊开始！";
			outJson($data);
		}

	}
//结束咨询(用户确认)
public function exit_consult(){
		$id = I('post.id');
		$uid = $this->uid;
		$patient_id = $this->reserve->where('id='.$id)->getfield('patientmember_id');
		$doctor_id = $this->reserve->where('id='.$id)->getfield('member_id');
		$res = array(
			'status' => 6,
		);

		$money = $this->reserve->where("id=".$id)->getfield('money');
		$money = $money - $money*C('RES_NAME')/100;
		setpoints($doctor_id,1,$money,'元','166',0);
		$balance = $this->user->where('id='.$uid)->getField('balance');
		financial_log($doctor_id,$money,3,$balance,'预约就诊收益',3);

		$result = $this->reserve->where('id='.$id)->save($res);
		if($result){
			$doctor_phone=$this->user->where("id='".$patient_id."' and role=2")->find();
			if($doctor_phone['member_level'] == "0"){
				setpoints($patient_id,2,10,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,10,3,$score,'咨询赠送',3,2);
			}else if($doctor_phone['member_level'] == "1"){
				setpoints($patient_id,2,20,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,20,3,$score,'咨询赠送',3,2);
			}else if($doctor_phone['member_level'] == "2"){
				setpoints($patient_id,2,30,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,30,3,$score,'咨询赠送',3,2);
			}
			$patient_phone = $this->user->where("id=".$doctor_id." and role=1")->find();
			if($patient_phone['deviceid']!='')
			{
				$title="";
				$content = "亲,您有预约就诊结束！";
				$device[] = $patient_phone['deviceid'];
				$extra = array("type" => "4", "type_id" => $id,'user_type' => 1,'status' => 6);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$patient_phone['os'];
				jpush_send($title,$content,$audience,$os,$extras);
			}
			unset($data);
			$data['code'] = 1;
			$data['message'] = "开始就诊！";
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