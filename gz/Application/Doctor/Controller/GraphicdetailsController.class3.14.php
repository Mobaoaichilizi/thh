<?php
// +----------------------------------------------------------------------
// | 图文咨询详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class GraphicdetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->graphic =D("Graphic");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user = D("User");
		$this->sms = D("Sms");
		$this->systemsinfo = D("SystemsInfo");
		$this->uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield("id");
	}

public	function graphic_details()
	{
		$uid = $this->uid;//用户的ID
		$id = I('post.id');//图文咨询的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->graphic->field("patientmember_id,description,status,money,img_thumb as img_desc")->where("id='".$id."'")->find();
			$patient = $this->patient->field("user_id,name,img_thumb,phone")->where("user_id='".$result['patientmember_id']."'")->find();
			$results['doctor_phone'] = $this->user->where('id='.$uid)->getfield('username');
			$results['id'] = $uid;
			$results['name'] = $patient['name'];
			$results['user_id'] = $patient['user_id'];
			$results['img_thumb'] = $patient['img_thumb'];
			$results['phone'] = $this->user->where("id=".$patient['user_id'])->getfield("username");
			$results['patient_uid'] = md5(md5($patient['user_id']));
			// $results['phone'] = str_pad_star($patient['phone'],7,'****','left');
			$results['description'] = $result['description'];
			$results['img_desc'] = $result['img_desc'];
			$results['status'] = $result['status'];
			$results['price'] = $result['money'];
			
		
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
		$create_time = $this->graphic->where("id=".$id)->getfield("create_time");
		$status = $this->graphic->where("id=".$id)->getfield("status");
		$is_replay = $this->graphic->where("id=".$id)->getfield("is_replay");
		$ress = $this->graphic->where("id=".$id)->getfield("money");
		$patient = $this->graphic->where("id=".$id)->getfield("patientmember_id");
		$doctor_id = $this->graphic->where("id=".$id)->getfield("member_id");
		$doctor_phone = $this->user->where("id=".$doctor_id)->getField('username');
		$patient_phone = $this->user->where("id=".$patient)->getField('username');
		$name = $this->user->where("id=".$uid)->getfield("username");
		$code = "图文咨询";
	    if($status == 1 && $is_replay == 0){
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
		    }
		    $res=array(
				"phone" => $name,
				"createtime" => time(),
				"type" => 3,
				"content" => $str,
				"code" => $code,
			);
			if($result['Code'] == 'OK'){
				$this->sms->add($res);
			}
			unset($result);
			$result = $this->graphic->where('id='.$id)->save($rest);
			
			if($result){

				$balance = $this->user->where('id='.$patient)->getfield('balance');
				
				$ba = $balance + $ress;
				
	
				unset($money);
				$money = array(
					'balance' => $ba,
				);
				$this->user->where('id='.$patient)->save($money);
				financial_log($patient,$ress,3,$ba,'图文咨询退款收益',12);
				
				unset($data);
				$data['code'] = 1;
				$data['message'] = "退还成功！";
				$data['pay'] = $ress;
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
	public function exit_consult()
	{
		$uid = I('post.uid');
		// $uid = '28c8edde3d61a0411511d3b1866f0636';
		$toid = I('post.toid');
		// $toid = '4e44f1ac85cd60e3caa56bfd4afb675e';
		$role = I('post.role');
		// $role = '1';
		$uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield("id");
		$toid = $this->user->where("md5(md5(id))='".$toid."'")->getfield("id");
		if($role == 1){
			$patient_id = $toid;
			$doctor_id = $uid;
		}else if($role == 2){
			$patient_id = $uid;
			$doctor_id = $toid;
		}
		$res = array(
			'status' => 6,
		);
		$graphic_id = $this->graphic->where("member_id=".$doctor_id." and patientmember_id=".$patient_id." and status in (1,2)")->order('create_time desc')->limit(1)->getfield('id');
		// dump($this->graphic->getlastsql());
		if(empty($graphic_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "此咨询不符合结束条件！";
			outJson($data);
		}
		$money = $this->graphic->where("id=".$graphic_id)->getfield('money');
		$money = $money - $money*C('TW_NAME')/100;
		setpoints($doctor_id,1,$money,'元','166',0);
		$balance = $this->user->where('id='.$doctor_id)->getField('balance');
		financial_log($doctor_id,$money,3,$balance,'咨询收益',1);

		$result = $this->graphic->where('id='.$graphic_id)->save($res);
		// $patient_id=$this->graphic->where('id='.$graphic_id)->getField('patientmember_id');
		if($result){
			$doctor_phone=$this->user->where("id='".$patient_id."' and role=2")->find();
			$uid_phone=$this->user->where("id=".$toid)->find();
			if($doctor_phone['member_level'] == "0"){
				setpoints($patient_id,2,10,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,10,3,$score,'咨询赠送',1,2);
			}else if($doctor_phone['member_level'] == "1"){
				setpoints($patient_id,2,20,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,20,3,$score,'咨询赠送',1,2);
			}else if($doctor_phone['member_level'] == "2"){
				setpoints($patient_id,2,30,'分','150',0);
				$score = $this->user->where('id='.$patient_id)->getField('score');
				financial_log($patient_id,30,3,$score,'咨询赠送',1,2);
			}
			if($uid_phone['deviceid']!='')
			{
				$title="";
				$content = "亲,您有图文咨询结束！";
				$device[] = $uid_phone['deviceid'];
				$extra = array("type" => "2", "type_id" => $graphic_id,'user_type' => $role,'status' => 6);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$uid_phone['os'];
				jpush_send($title,$content,$audience,$os,$extras);
				$res_sys_info=array(
				"title" => $title,
				"description" => $content,
				"send_uid" => $uid,
				"receive_uid" =>$toid,
				"type_id" => $graphic_id,
				"type" => 2,
				"createtime" => time(),
			);
			$this->systemsinfo->add($res_sys_info);
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
	public function update_complate()
	{
		$id = I('post.id');
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择的咨询！";
			outJson($data);
		}
		$res = array(
			'status' => 2,
		);
		$result = $this->graphic->where('id='.$id)->save($res);
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

	public function visits(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
			'is_replay' => 1,
		);
		$result = $this->graphic->where('id='.$id)->save($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "咨询成功！";
			outJson($data);
		}

	}
}
?>