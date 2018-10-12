<?php
// +----------------------------------------------------------------------
// | 图文咨询管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class ConsultingController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->graphic = D('Graphic');//图文咨询表
		$this->financial =D("Financial"); //收支明细
		$this->sms =D("Sms");
		$this->systemsinfo =D("SystemsInfo");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');		
		
	}
	//图文咨询提交
	public function do_consulting()
	{
		
		$id = $this->uid;
		$member_id = I('post.member_id');
		$img_thumb = I('post.img_thumb');
		$description = I('post.description');
		$money = I('post.money');
		
		$name = I('post.name');
		$age = I('post.age');
		$phone = I('post.phone');
		if(empty($member_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择医生！";
			outJson($data);
		}
		// if(empty($img_thumb))
		// {
		// 	unset($data);
		// 	$data['code']=0;
		// 	$data['message']="请上传图片！";
		// 	outJson($data);
		// }
		// if(empty($description))
		// {
		// 	unset($data);
		// 	$data['code']=0;
		// 	$data['message']="请输入描述！";
		// 	outJson($data);
		// }
		if(empty($money))
		{
			unset($data);
			$data['code']=0;
			$data['message']="费用不能为空！";
			outJson($data);
		}
		
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入问诊人！";
			outJson($data);
		}
		if(empty($age))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入问诊人年龄";
			outJson($data);
		}
		if(empty($phone))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入问诊人电话";
			outJson($data);
		}
		unset($user_info);
		$user_info=$this->user->where("id=".$id)->find();
		if($user_info['balance'] < $money)
		{
			unset($data);
			$data['code']=10003;
			$data['message']="卡里余额不足，请充值！";
			outJson($data);
		}
		
		
		$recount=$this->graphic->where("member_id=".$member_id." and patientmember_id=".$id." and status=1")->count();
		if($recount > 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="您还有未完成的服务，请在我的——我的咨询中查看！";
			outJson($data);
		}
		
		unset($result);
		$result=$this->user->where("id=".$id)->setDec('balance',$money);
		
			unset($user_info);
			$user_info=$this->user->where("id=".$id)->find();
			unset($res);
			$res=array(
				'user_id' => $id,
				'money' => $money,
				'operation' => 2,
				'balance' => $user_info['balance'],
				'content' => '图文咨询消费',
				'type' => 1,
				'createtime' => time()
			);
			$this->financial->add($res);
			unset($result);
			unset($res);
			$res=array(
				'ordernumber' => time().rand(1000,9999),
				'member_id' => $member_id,
				'patientmember_id' => $id,
				'img_thumb' => $img_thumb,
				'description' => $description,
				'money' => $money,
				'status' => 1,
				'name' => $name,
				'age' => $age,
				'phone' => $phone,
				'create_time' => time(),
			);
			$result=$this->graphic->add($res);
			if($result)
			{
				$type_code='图文咨询';
				$str='您好，'.$phone.'，患者预约了您的'.$type_code.'，请您尽快登陆APP，进行查看。';
				require_once VENDOR_PATH."SmsApi/SmsApi.php";
				$aliyunsms = new SmsApi(C('WEB_SMS_USER'),C('WEB_SMS_PASS'));
				$doctor_phone=$this->user->where("id=".$member_id." and role=1")->find();
				$response = $aliyunsms->sendSms(
					"广正云医", // 短信签名
					"SMS_126876454", // 短信模板编号
					$doctor_phone['username'], // 短信接收者
					Array (  // 短信模板中字段的值
						"name"=>$phone,
						"code"=>$type_code,
					)
				);
				$result_sms= json_decode(json_encode($response),true);
				$res_sms=array(
					"phone" => $doctor_phone['username'],
					"createtime" => time(),
					"type" => 3,
					"content" => $str,
					"code" => $type_code,
				);
				if($result_sms['Code']=='OK')
				{
					$this->sms->add($res_sms);
					if($doctor_phone['deviceid']!='')
					{
						$title="";
						$content = "亲,您有一个图文咨询！";
						$device[] = $doctor_phone['deviceid'];
						$extra = array("type" => "2", "type_id" => $result,'user_type' => 1,'status' => 1);
						$audience='{"alias":'.json_encode($device).'}';
						$extras=json_encode($extra);
						$os=$doctor_phone['os'];
						jpush_send($title,$content,$audience,$os,$extras);
						$res_sys_info=array(
							"title" => $title,
							"description" => $content,
							"send_uid" => $id,
							"receive_uid" =>$member_id ,
							"type_id" => $result,
							"type" => 2,
							"createtime" => time(),
						);
						$this->systemsinfo->add($res_sys_info);
					}
					unset($ret);
					$ret['id']=$result;
					$ret['status']=1;
					unset($data);
					$data['code']=1;
					$data['message']="保存成功！";
					$data['info']=$ret;
					outJson($data);
				}else
				{
					unset($data);
					$data['code']=0;
					$data['message']="保存失败！";
					outJson($data);

				}
			}else
			{
				unset($data);
				$data['code']=0;
				$data['message']="保存失败！";
				outJson($data);
			}
		
	}
	//图文咨询列表
	public function lists()
	{
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;

		$result=$this->graphic->field("id,member_id,money,status,create_time")->where("patientmember_id=".$this->uid)->order("create_time desc")->limit($p.",".$limit)->select();
		foreach($result as &$row)
		{
			unset($res);
			$res=$this->member->where("user_id=".$row['member_id'])->find();
			$row['create_time']=time_tran($row['create_time']);
			$row['doctor_img']=$res['img_thumb'];
			$row['doctor_name']=$res['name'];
		}
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取图文咨询列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无医生！";
			$data['info']=array();
			outJson($data);	
		}
	}
	//图文咨询详情
	public function deal()
	{
		$id = I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择咨询！";
			outJson($data);
		}
		$result=$this->graphic->field("id,member_id,img_thumb,name,age,phone,description,money,status")->where("id=".$id)->find();
		if($result)
		{
			$res=$this->member->where("user_id=".$result['member_id'])->find();
			$result['phone']=str_pad_star($result['phone'],7,'****','left');
			$result['doctor_img']=$res['img_thumb'];
			$result['doctor_name']=$res['name'];
			$result['doctor_professional']=$res['professional'];
			$result['img_thumb']=explode(',',$result['img_thumb']);
			unset($data);
			$data['code']=1;
			$data['message']="获取图文咨询列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="您的咨询不存在";
			outJson($data);	
		}
		
	}
	

}
?>