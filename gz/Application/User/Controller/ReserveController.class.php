<?php
// +----------------------------------------------------------------------
// | 预约就诊管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class ReserveController extends UserbaseController {
	public function _initialize() {
		//echo md5(md5(2));
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->reserve = D('Reserve');//预约咨询表
		$this->telephone = D('Telephone');//电话咨询表
		$this->videodia = D('Videodia');//视频咨询表
		$this->consultation = D('Consultation');//预约咨询排版表
		$this->financial =D("Financial"); //收支明细
		$this->sms =D("Sms");
		$this->systemsinfo =D("SystemsInfo");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');		
		
	}
	//预约咨询提交
	public function do_reserve()
	{
		
		$id = $this->uid;
		$member_id = I('post.member_id');
		$year = I('post.year');
		$res_date = I('post.res_date');
		$morn_after = I('post.morn_after');
		$money = I('post.money');
		$address = I('post.address');
		
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
		if(empty($year))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择预约年份！";
			outJson($data);
		}
		if(empty($res_date))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择预约日期！";
			outJson($data);
		}
		if(empty($money))
		{
			unset($data);
			$data['code']=0;
			$data['message']="费用不能为空！";
			outJson($data);
		}
		if(empty($address))
		{
			unset($data);
			$data['code']=0;
			$data['message']="预约地点不能为空！";
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
		$res_date=strtotime($year.'-'.$res_date);
		unset($user_info);
		$user_info=$this->user->where("id=".$id)->find();
		if($user_info['balance'] < $money)
		{
			unset($data);
			$data['code']=10003;
			$data['message']="卡里余额不足，请充值！";
			outJson($data);
		}
		
		
		$recount=$this->reserve->where("member_id=".$member_id." and patientmember_id=".$id." and status=1")->count();
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
				'content' => '预约咨询消费',
				'type' => 3,
				'createtime' => time()
			);
			$this->financial->add($res);
			unset($result);
			unset($res);
			$res=array(
				'ordernumber' => time().rand(1000,9999),
				'member_id' => $member_id,
				'patientmember_id' => $id,
				'res_date' => $res_date,
				'address' => $address,
				'morn_after' => $morn_after,
				'money' => $money,
				'status' => 1,
				'name' => $name,
				'age' => $age,
				'phone' => $phone,
				'create_time' => time(),
			);
			$result=$this->reserve->add($res);
			if($result)
			{
				$type_code='预约就诊';
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
					"type" => 5,
					"content" => $str,
					"code" => $type_code,
				);
				if($result_sms['Code']=='OK')
				{
					$this->sms->add($res_sms);
					if($doctor_phone['deviceid']!='')
					{
						$title="";
						$content = "亲,您有一个预约就诊！";
						$device[] = $doctor_phone['deviceid'];
						$extra = array("type" => "4", "type_id" => $result,'user_type' => 1,'status' => 1);
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
							"type" => 4,
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
	//预约咨询列表
	public function lists()
	{
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$ress=array();
		$result=$this->reserve->field("id,member_id,money,status,create_time,type")->where("patientmember_id=".$this->uid)->order("create_time desc")->limit($p.",".$limit)->select();
		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['member_id']);
			 	$list[$id]=isset($list[$id])?
			 			(strtotime($vo['create_time'])>strtotime($list[$id]['create_time']))? $vo:$list[$id] : $vo;
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["create_time"];'), $list);
		array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
		foreach ($list as $key => $value) {
			$res['id'] = $value['id'];
			$res['status'] = $value['status'];
			$res['type'] = $value['type'];
			$res['member_id'] = $value['member_id'];
			$res['name'] = $this->member->where("user_id='".$value['member_id']."'")->getfield("name");
			$res['img_thumb'] = $this->member->where("user_id='".$value['member_id']."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$res['create_time'] = time_tran($value['create_time']);
			$ress[] = $res;
		}
		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取预约咨询列表成功！";
			$data['info']=$ress;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="没有信息了！";
			$data['info']=array();
			outJson($data);	
		}
	}
	//对此医生的多次咨询
	public function doctor_consult(){
		$uid = $this->uid;
		$member_id = I('post.member_id');
		if(empty($member_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择医生！";
			outJson($data);
		}
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		$result = $this->reserve->field("id,create_time,type,money,status")->where("member_id=".$member_id." and patientmember_id=".$uid)->order('create_time desc')->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$res['id'] = $value['id'];
			$res['type'] = $value['type'];
			$res['status'] = $value['status'];
			$res['name'] = $this->member->where("user_id='".$member_id."'")->getfield("name");
			$res['img_thumb'] = $this->member->where("user_id='".$member_id."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$res['create_time'] = time_tran($value['create_time']);
			$ress[] = $res;
		}
		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $ress;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无数据";
			$data['info'] = array();
			outJson($data);
		}

	}
	//预约就诊时间
	public function week_list()
	{
		$id = I('post.id');
		// $id = 4;
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择医生！";
			outJson($data);
		}
		$data=$this->consultation->where("doctor_id=".$id)->find();
		$week = get_week_date();
		//print_r($week);
		foreach($week as $k=>$v)
		{
			//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
			$morning_count = $this->telephone->where("member_id=".$id." and tel_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
			
			//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
			$afternoon_count = $this->telephone->where("member_id=".$id." and tel_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
			
			//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
			$morningreserve_count = $this->reserve->where("member_id=".$id." and res_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
			
			//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
			$afternoonreserve_count = $this->reserve->where("member_id=".$id." and res_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
			
			//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
			$morningvideodia_count = $this->videodia->where("member_id=".$id." and video_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
			
			//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
			$afternoonvideodia_count = $this->videodia->where("member_id=".$id." and video_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
			
			if($v['week']=="星期一")
			{
				$week[$k]['morning_number']= $data['mon_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['mon_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期二")
			{
				$week[$k]['morning_number']= $data['tues_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['tues_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期三")
			{
				$week[$k]['morning_number']= $data['wed_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['wed_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期四")
			{
				$week[$k]['morning_number']= $data['thur_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['thur_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期五")
			{
				$week[$k]['morning_number']= $data['fri_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['fri_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期六")
			{
				$week[$k]['morning_number']= $data['sat_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['sat_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			else if($v['week']=="星期日")
			{
				$week[$k]['morning_number']= $data['sun_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
				$week[$k]['afternoon_number']= $data['sun_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
			}
			$week[$k]['date'] = $v['date'];
		}
		unset($data);
		$data['code']=1;
		$data['message']="获取列表成功！";
		$data['info']=array_values($week);
		outJson($data);	
	}
	
	//预约咨询详情
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
		$result=$this->reserve->field("id,member_id,name,age,phone,money,status,res_date,morn_after,address")->where("id=".$id)->find();
		if($result)
		{
			$res=$this->member->where("user_id=".$result['member_id'])->find();
			$result['phone']=str_pad_star($result['phone'],7,'****','left');
			$result['doctor_img']=$res['img_thumb'];
			$result['doctor_name']=$res['name'];
			$weekarray=array("日","一","二","三","四","五","六"); 
			$result['week']="星期".$weekarray[date("w",$result['res_date'])];
			$result['doctor_professional']=$res['professional'];
			$result['res_date']=date('Y年m月d日',$result['res_date']);
			
				
			unset($data);
			$data['code']=1;
			$data['message']="获取咨询详情成功！";
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