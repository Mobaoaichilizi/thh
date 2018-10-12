<?php
// +----------------------------------------------------------------------
// | 处方管理接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
use Aliyun\DySDKLite\Sms\SmsApi;
class PreController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->pre =D("Pre");
		$this->member =D("Member");
		$this->patientmember =D("Patientmember");
		$this->hrebs =D("Hrebs");
		$this->single_hrebs =D("SingleHrebs");
		$this->pre_hrebs =D("PreHrebs");//所需单个药材
		$this->user =D("User");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		$this->admin = D('Admin');
		$this->setting = D('Setting');
		$this->personaddress = D('Personaddress');
		$this->sms = D('Sms');
	}
//上传电子处方
public function upload_elepre(){
	$id = $this->uid;

	$patient_id = I('post.user_id');
	// $patient_id = 5;
	
	$setting_id_type = I('post.setting_id_type');

	$pre_description = I('post.pre_description');
	// $pre_description = "按时吃";
		
	$pre_hrebs_id = $_POST['pre_hrebs_id'];
	// $pre_hrebs_id = "[{\"setting_id_model\":\"50\",\"hrebs_name\":\"北柴胡\",\"id\":\"6\",\"drug_num\":\"10\"},{\"setting_id_model\":\"50\",\"hrebs_name\":\"北柴胡\",\"id\":\"6\",\"drug_num\":\"10\"}]";

	$setting_id_usage = I('post.setting_id_usage');
	// $setting_id_usage = 70;
	
	$hrebs_number = I('post.hrebs_number');
	// $hrebs_number = 1;
	
	$total = I('post.hrebs_total');
	// $hrebs_total = 20;

	$hrebs_bz = I('post.hrebs_bz','');

	$dosage_name = I('post.dosage_name');
 
 	$admin_pre = I('post.admin_pre');
	
	if(empty($setting_id_type)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择处方种类！";
		outJson($data);
	}
	// if(empty($pre_description)){
	// 	unset($data);
	// 	$data['code'] = 0;
	// 	$data['message'] = "请填写病例诊断！";
	// 	outJson($data);
	// }
	if(empty($setting_id_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材规格！";
			outJson($data);
	}
	if(empty($pre_hrebs_id)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请添加药材！";
		outJson($data);
	}
	if(empty($setting_id_usage)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择药材用法！";
		outJson($data);
	}
	if(empty($hrebs_number)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择药材副数！";
		outJson($data);
	}
	// if(empty($dosage_name)){
	// 	unset($data);
	// 	$data['code'] = 0;
	// 	$data['message'] = "请选择剂型！";
	// 	outJson($data);
	// }
	$de_json = json_decode($pre_hrebs_id,TRUE);
    $count_json = count($de_json);
    for ($i = 0; $i < $count_json; $i++)
       {
            $data_id = $de_json[$i]['id'];
            $data_num = $de_json[$i]['drug_num'];
            if(!empty($data_id)){
            	$ress = array(
							"hrebs_name_id" => $data_id,
							"hrebs_dosage"  => $data_num,
						);
				$hrebs = $this->pre_hrebs->add($ress);
				$arr[] = $hrebs;
            }
			
        }
    

	
	$hrebs_id = implode(",", $arr);
	$res = array(
			'setting_id_type'  => $setting_id_type,
			'pre_hrebs_id'  => $hrebs_id,
			'setting_id_usage' => $setting_id_usage,
			'hrebs_number'     => $hrebs_number,
			'hrebs_bz'         => $hrebs_bz,
			'hrebs_total'      => $total,
		);
	$result = $this->hrebs->add($res);
	
	unset($res);
	$res = array(
		'setting_id_class' => 43,
		'patient_id' => $patient_id,
		'doctor_id' => $id,
		'pre_description' => $pre_description,
		'setting_id_type' => $setting_id_type,
		'hrebs_id' => $result,
		'create_time' => time(),
		'dosage_name' => $dosage_name,
		'admin_pre'  => $admin_pre,
	);
	$results = $this->pre->add($res);
	unset($result);
	$result1 = $this->member->field('name')->where("user_id='".$id."'")->find();
	$patient = $this->patientmember->field('name,sex,age,phone')->where("user_id='".$patient_id."'")->find();
	$re['doctor'] = $result1;
	$re['name'] = $patient['name'];
	$re['sex'] = $patient['sex'];
	$re['age'] = $patient['age'];
	$re['phone'] = $patient['phone'];
	$re['pre_id'] = $results;
	if($re){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方上传成功！";
		$data['info'] = $re;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "处方上传失败！";
		outJson($data);
	}

}
//上传传统处方
public function upload_trapre(){
	$id = $this->uid;
	$patient_id = I('post.patient_id');
	$img_thumb_common = I('post.img_thumb_common','');
	$img_thumb_prime = I('post.img_thumb_prime','');
	$pre_bz = I('post.pre_bz','');
	$admin_pre = I('post.admin_pre');
	// if(empty($patient_id)){
	// 	unset($data);
	// 	$data['code'] = 0;
	// 	$data['message'] = "请选择患者！";
	// 	outJson($data);
	// }
	if(empty($img_thumb_common) && empty($img_thumb_prime)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请上传处方！";
		outJson($data);
	}
	if(!empty($img_thumb_common)){
		$setting_id_type = 155;
	}else if(!empty($img_thumb_prime)){
		$setting_id_type = 156;
	}
	$res = array(
		'patient_id' => $patient_id,
		'doctor_id' => $id,
		'setting_id_class' => 44,
		'img_thumb_common' => $img_thumb_common,
		'img_thumb_prime' => $img_thumb_prime,
		'pre_bz' => $pre_bz,
		'create_time' => time(),
		'admin_pre' => $admin_pre,
		'setting_id_type' => $setting_id_type,
	);
	$results = $this->pre->add($res);
	$result['doctor'] = $this->member->field('name')->where("user_id='".$id."'")->find();
	$result['patient'] = $this->patientmember->field('name,sex,age,phone')->where("user_id='".$patient_id."'")->find();
	$result['pre_id'] = $results;
	if($results){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方上传成功！";
		$data['info'] = $results;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "处方上传失败！";
		outJson($data);
	}

}
//处方展示
public function pre_list(){
	$id = $this->uid;
	$result = $this->pre->field('id,patient_id,create_time')->where("doctor_id = '".$id."' and setting_id_class = 43")->order('create_time desc')->select();

	$results = $this->pre->field('id,patient_id,create_time')->where("doctor_id = '".$id."' and setting_id_class = 44")->order('create_time desc')->select();
	$electron = array();
	foreach ($result as $k=>$vo){
		 	$id=intval($vo['patient_id']);
		 	$electron[$id]=isset($electron[$id])?
		 			(strtotime($vo['create_time'])>strtotime($electron[$id]['create_time']))? $vo:$electron[$id] : $vo;
		
		 
	}
	// dump($electron);
	$electron=array_values($electron);
	
	foreach ($electron as $key => &$value) {
		$value['img_thumb'] = $this->patientmember->where("user_id='".$value['patient_id']."'")->getfield('img_thumb');
		$value['name'] = $this->patientmember->where("user_id='".$value['patient_id']."'")->getfield('name');
		$value['create_time'] = date("Y-m-d",$value['create_time']);
	}
	$tradition = array();
	foreach ($results as $k=>$vo) {
		$id=intval($vo['patient_id']);
		 	$tradition[$id]=isset($tradition[$id])?
		 			(strtotime($vo['create_time'])>strtotime($tradition[$id]['create_time']))? $vo:$tradition[$id] : $vo;
	}
	$tradition=array_values($tradition);
	foreach ($tradition as $m => &$n) {
		$n['img_thumb'] = $this->patientmember->where("user_id='".$n['patient_id']."'")->getfield('img_thumb');
		$n['name'] = $this->patientmember->where("user_id='".$n['patient_id']."'")->getfield('name');
		$n['create_time'] = date("Y-m-d",$n['create_time']);
	}
	$electron = array_slice($electron, 0,3);
	$tradition = array_slice($tradition, 0,3);
	if($result){
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['electron'] = $electron;
			$data['tradition'] = $tradition;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['electron'] = $result;
			$data['tradition'] = array();
			outJson($data);
		}
		
	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无处方！";
		$data['info'] = array();
		outJson($data);
	}
}
//电子处方/传统处方列表
public function type_list(){
	$id = $this->uid;
	$p=!empty($_POST['p']) ? $_POST['p'] : 1;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		
	$p=($p-1)*$limit;
	$setting_id_class = I('post.setting_id_class');
	if(empty($setting_id_class)){
		unset($data);
		$data['code'] = 0;
		$data['message'] ="请选择处方类型";
		outJson($data);
	}
	$result = $this->pre->field('id,patient_id,create_time')->where("doctor_id = '".$id."' and setting_id_class = ".$setting_id_class)->order('create_time desc')->select();
	$pre = array();
	foreach ($result as $k=>$vo){
		 	$id=intval($vo['patient_id']);
		 	$pre[$id]=isset($b[$id])?
		 			(strtotime($vo['create_time'])>strtotime($b[$id]['create_time']))? $vo:$pre[$id] : $vo;
		
		 
	}
	$pre=array_values($pre);
	foreach ($pre as $key => &$value) {
		$value['img_thumb'] = $this->patientmember->where("user_id='".$value['patient_id']."'")->getfield('img_thumb');
		$value['name'] = $this->patientmember->where("user_id='".$value['patient_id']."'")->getfield('name');
		$value['create_time'] = date("Y-m-d",$value['create_time']);
	}
	$pre  = array_slice($pre,$p,$limit);
	if($pre){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方加载成功！";
		$data['info'] = $pre;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无处方！";
		$data['info'] = array();
		outJson($data);
	}

}


//根据此患者的所有处方
public function patient_pre(){
	$uid = $this->uid;
	$patient_id = I('post.patient_id');
	$p=!empty($_POST['p']) ? $_POST['p'] : 1;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		
	$p=($p-1)*$limit;
	$setting_id_class = I('post.setting_id_class');
	if(empty($setting_id_class)){
		unset($data);
		$data['code'] = 0;
		$data['message'] ="请选择处方类型";
		outJson($data);
	}
	if(empty($patient_id)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择患者";
		outJson($data);
	}
	$result = $this->pre->field('id,create_time')->where("doctor_id = '".$uid."' and patient_id = ".$patient_id." and setting_id_class = ".$setting_id_class)->order('create_time desc')->limit($p.",".$limit)->select();
	foreach ($result as $key => &$value) {
	 	$value['img_thumb'] = $this->patientmember->where("user_id='".$patient_id."'")->getfield('img_thumb');
		$value['name'] = $this->patientmember->where("user_id='".$patient_id."'")->getfield('name');
		$value['create_time'] = date("Y-m-d",$value['create_time']);
	 } 
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方加载成功！";
		$data['info'] = $result;
		outJson($data);

	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无处方！";
		$data['info'] = array();
		outJson($data);
	}
}
public function hrebs_list()
	{
		$uid = $this->uid;
		$firstletter = I('post.firstletter');
		$setting_id_type = I('post.setting_id_type');
		$admin_id = I('post.admin_id');
		if(empty($firstletter)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "首字母为空！";
			outJson($data);
		}
		if(empty($setting_id_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材规格！";
			outJson($data);
		}
		if(empty($admin_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择所属中医馆！";
			outJson($data);
		}

		$result = $this->single_hrebs->field("hrebs_name,unit_price,id,setting_id_model")->where("firstletter='".$firstletter."' and setting_id_model=".$setting_id_type." and admin_id=".$admin_id)->select();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "药材加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无数据！";
			outJson($data);
		}
	}
	public function yaopu(){
		$setting_id_model = I('post.setting_id_model');
		if(empty($setting_id_model)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材品质！";
			outJson($data);
		}
		$pha = $this->admin->field('id,admin_name,is_default,rate')->where('is_pharmacy=1')->select();//查询出所有的药堂
		// $result['standard'] = array();
		// $result['high-quality'] = array();
		foreach ($pha as $key => $value) {
			$count = $this->single_hrebs->where("setting_id_model = ".$setting_id_model." and admin_id=".$value['id'])->count();
			$value['rate'] = $value['rate']."%";
			if($count > 0){
				$result[] = $value;
			}
			// $count1 = $this->single_hrebs->where('setting_id_model = 156 and admin_id='.$value['id'])->count();
			// if($count1 > 0){
			// 	$result['high-quality'][] = $value;
			// }
			
		}
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "success";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无数据";
			$data['info'] = array();
			outJson($data);
		}
	}
	//药房的剂型价格
	public function hrebs_model(){
		$admin_id = I('post.admin_id');//药房id
		if(empty($admin_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择中医馆";
			outJson($data);
		}
		$list['price'] = $this->admin->where('id='.$admin_id)->getfield('technical_price');
		$list['name'] = "原药";
		$list['id'] = "131";
		$result[] = $list;
		$list1['price'] = $this->admin->where('id='.$admin_id)->getfield('frying_price');
		$list1['name'] = "汤剂代煎";
		$list1['id'] = "132";
		$result[] = $list1;
		$list2['price'] = $this->admin->where('id='.$admin_id)->getfield('paste_price');
		$list2['name'] = "膏方制作";
		$list2['id'] = "133";
		$result[] = $list2;
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "success";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无数据";
			$data['info'] = array();
			outJson($data);
		}
	}
	//用法
	public function usage(){
		$result = $this->setting->field('id,title')->where('parentid=56')->order('sort asc')->select();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "success";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无数据";
			$data['info'] = array();
			outJson($data);
		}
	}
	public	function do_patientinfo(){
		$id = $this->uid;
		$name = I('post.name');
		$sex = I('post.sex');
		$age = I('post.age');
		$phone = I('post.phone');
		$card = I('post.card');
		$address = I('post.address');
		if($sex == "男"){
			$sex = 2;
		}else if($sex == "女"){
			$sex = 1;
		}else{
			$sex = 0;
		}
		if(empty($phone)){
			unset($data);
			$data['code']=0; 
			$data['message']='手机号码不能为空！';
			outJson($data);
		}
		$rest=$this->user->where("username='".$phone."' and role=2")->find();
		if(!empty($rest))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='手机号码已存在！';
			outJson($data);
		}
		$rand=get_rand_number(100000000,999999999,1);
		$inviter_share_number = $this->user->where("id=".$id)->getField("share_number");
		$res=array(
			'username' => $phone,
			'password' => sp_password('123456'),
			'share_number' => $rand[0],
			'inviter_share_number' => $inviter_share_number,
			'role' => 2,
			'createtime' => time(),
		);
		$patient_id=$this->user->add($res);

		unset($res);
		$res=array(
				'user_id' => $patient_id,
				'name' => $name,
				'sex' => $sex,
				'age' => $age,
				'card' => $card,
			);
		if($this->patientmember->add($res)){
			unset($res);
			$res = array(
				'person' => $name,
				'age'    => $age,
				'phone'  => $phone,
				'address'=> $address,
				'patient_id' => $patient_id,
			);
			$results = $this->personaddress->add($res);
			if($results){
				$str='您已注册成功，登陆账号为您的手机号码，密码为123456。';
				require_once VENDOR_PATH."SmsApi/SmsApi.php";
				$aliyunsms = new SmsApi(C('WEB_SMS_USER'),C('WEB_SMS_PASS'));
				$response = $aliyunsms->sendSms(
					"广正云医", // 短信签名
					"SMS_126350106", // 短信模板编号
					$phone, // 短信接收者
					Array (  // 短信模板中字段的值
						"code"=>'123456',
					)
				);
				unset($result);
				$result= json_decode(json_encode($response),true);
				//$result=sendsms($username,$str,'1839');
				unset($res);
				$res=array(
					"phone" => $phone,
					"createtime" => time(),
					"type" => 2,
					"content" => $str,
					"code" => '123456',
				);
				if($result['Code']=='OK')
				{
					$this->sms->add($res);
					$data=array(
				           'user_id'    => $patient_id, //会员ID，就你要给那个会员操作积分就传入那个会员的ID
				           'role'   => 2,
				           'score'  => 5,//操作的积分数量,正数为加分，负数为减分；
				           'content' => '元',
				           'setting_id'  => "151",   //操作理由，简单的积分操作理由；
				           'optime' => time(), //操作时间
				           'opid'   => 0  //操作员ID,如果为0表示系统操作；
			     	);
 					M('ScoreList')->add($data); 
 					M('User')->where("id=".$patient_id)->setInc('balance',"5");
					// setpoints($patient_id,2,5,'元','151',0); 
					$balance = $this->user->where('id='.$patient_id)->getField('balance');
					financial_log($patient_id,5,3,$balance,'被邀请注册',8);
					setpoints($id,1,5,'元','144',0);
					$ba = $this->user->where('id='.$id)->getField('balance');
					financial_log($id,5,3,$ba,'邀请注册',8);
					$patient_information = $this->patientmember->field('user_id as patient_id,name,img_thumb,sex,age')->where("user_id=".$patient_id)->find();
					$patient_information['phone'] = $phone;
					unset($data);
					$data['code'] = 1;
					$data['message'] = "信息保存成功！";
					$data['info'] = $patient_information;
					outJson($data);
				}else
				{
					unset($data);
					$data['code']=0;
					$data['message']="短信发送失败！";
					outJson($data);

				}



				
			}else{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "信息保存失败！";
				outJson($data);
			}
		}

	}
}
?>