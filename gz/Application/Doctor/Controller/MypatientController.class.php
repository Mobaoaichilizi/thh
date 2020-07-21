<?php
// +----------------------------------------------------------------------
// | 我的患者接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MypatientController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user=D("User");
		$this->patient =D("Patientmember");
		$this->reserve =D("Reserve");
		$this->telephone =D("Telephone");
		$this->graphic =D("graphic");
		$this->video =D("videodia");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
public function patient_diaed(){
	$id = $this->uid;
	$p=!empty($_POST['p']) ? $_POST['p'] : 0;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
	$p=($p-1)*$limit;
	
		// $map0['lgq_graphic.status'] = array("eq",2);
		$map0['lgq_graphic.member_id'] = array("eq",$id);
		$ress['patient'] = $this->patient->join("lgq_graphic on lgq_graphic.patientmember_id = lgq_patientmember.user_id")->field("user_id,lgq_patientmember.img_thumb,lgq_patientmember.name,lgq_graphic.id,type,create_time")->where($map0)->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['phone'] = $this->user->where('id='.$ress['patient'][$key]['user_id'])->getfield('username');
				$ress['patient'][$key]['patient_id'] = md5(md5($ress['patient'][$key]['user_id']));
			}	
			
		}

		$res[] = $ress;
	


		// $map2['lgq_reserve.status'] = array("eq",2);
		$map2['lgq_reserve.member_id'] = array("eq",$id);
		$ress['patient'] = $this->patient->join("lgq_reserve on lgq_reserve.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_reserve.id,type,create_time")->where($map2)->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['phone'] = $this->user->where('id='.$ress['patient'][$key]['user_id'])->getfield('username');
				$ress['patient'][$key]['patient_id'] = md5(md5($ress['patient'][$key]['user_id']));
			}	
		}

		$res[] = $ress;

		// $map3['lgq_videodia.status'] = array("eq",2);
		// $map3['lgq_videodia.member_id'] = array("eq",$id);
		// $ress['patient'] = $this->patient->join("lgq_videodia on lgq_videodia.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_videodia.id,type,video_date")->where($map3)->select();
		// foreach($ress as $k=>$v){
		// 	foreach ($v as $key => $value) {
		// 		$ress['patient'][$key]['video_date'] = format_date($this->video->where($map3)->getfield('video_date'));
		// 		$ress['patient'][$key]['phone'] = $this->user->where('id='.$ress['patient'][$key]['user_id'])->getfield('username');

		// 		$ress['patient'][$key]['patient_id'] = md5(md5($ress['patient'][$key]['user_id']));
		// 	}	
		// }

		// $res[] = $ress;
		foreach ($res as $key => $value) {
			foreach ($value as $k => $v) {
				foreach ($v as $a => $r) {
					$result[] = $r;
				}
				
			}
		}
	$arr1 = array_map(create_function('$n', 'return $n["create_time"];'), $result);
	array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$result);
	$list = array();
	foreach ($result as $k=>$vo) {
		$id=intval($vo['user_id']);
		 	$list[$id]=isset($list[$id])?$list[$id] : $vo;
	}
	$list=array_values($list);
	// dump($list);


	$results['count'] = count($result);
	if($limit !== 0){
		$list = array_splice($list,$p,$limit);
	}
	
	$results['patient'] = $list;
	if(empty($results['patient'])){
		$results['patient'] = array();
	}
	
	foreach ($list as $ko => &$vo) {
		$vo['create_time'] = format_date($vo['create_time']);
	}
	if($results){
		
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $list;
		// $data['info'] = $list;
		outJson($data);
	
	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无数据！";
		outJson($data);
	}
	
}

// public function patient_dia(){
// 		$id = $this->uid;
// 		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
// 		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
// 		$p=($p-1)*$limit;
// 		$map0['lgq_graphic.status'] = array("eq",1);
// 		$map0['lgq_graphic.member_id'] = array("eq",$id);
// 		unset($ress);
// 		$ress['patient'] = $this->patient->join("lgq_graphic on lgq_graphic.patientmember_id = lgq_patientmember.user_id")->field("user_id,lgq_patientmember.img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_graphic.id,type,update_time")->where($map0)->select();
// 		foreach($ress as $k=>$v){
// 			foreach ($v as $key => $value) {
// 				$ress['patient'][$key]['update_time'] = format_date($this->graphic->where($map0)->getfield('update_time'));
// 			}	
			
// 		}
// 		$res[] = $ress;

	
// 		$map1['lgq_telephone.status'] = array("eq",1);
// 		$map1['lgq_telephone.member_id'] = array("eq",$id);
// 		unset($ress);
// 		$ress['patient'] = $this->patient->join("lgq_telephone on lgq_telephone.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_telephone.id,type,tel_date")->where($map1)->select();

// 		foreach($ress as $k=>$v){
// 			foreach ($v as $key => $value) {
// 				$ress['patient'][$key]['tel_date'] = format_date($this->telephone->where($map1)->getfield('tel_date'));
// 			}	
// 		}

// 		$res[] = $ress;


// 		$map2['lgq_reserve.status'] = array("eq",1);
// 		$map2['lgq_reserve.member_id'] = array("eq",$id);
// 		unset($ress);
// 		$ress['patient'] = $this->patient->join("lgq_reserve on lgq_reserve.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_reserve.id,type,res_date")->where($map2)->select();
// 		foreach($ress as $k=>$v){
// 			foreach ($v as $key => $value) {
// 				$ress['patient'][$key]['res_date'] = format_date($this->reserve->where($map2)->getfield('res_date'));
// 			}	
// 		}

// 		$res[] = $ress;

// 		$map3['lgq_videodia.status'] = array("eq",1);
// 		$map3['lgq_videodia.member_id'] = array("eq",$id);
// 		unset($ress);
// 		$ress['patient'] = $this->patient->join("lgq_videodia on lgq_videodia.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_videodia.id,type,video_date")->where($map3)->select();
// 		foreach($ress as $k=>$v){
// 			foreach ($v as $key => $value) {
// 				$ress['patient'][$key]['video_date'] = format_date($this->video->where($map3)->getfield('video_date'));
// 			}	
// 		}
// 		$res[] = $ress;
// 		foreach ($res as $key => $value) {
// 			foreach ($value as $k => $v) {
// 				foreach ($v as $a => $r) {
// 					$result[] = $r;
// 				}
				
// 			}
// 		}
// 	$results['count'] = count($result);
// 	if($limit !== 0){
// 		$result = array_splice($result,$p,$limit);
// 	}
	
// 	$results['patient'] = $result;
// 	if(empty($results['patient'])){
// 		$results['patient'] = array();
// 	}		
// 		if($results){
			
// 				unset($data);
// 				$data['code'] = 1;
// 				$data['message'] = "加载成功！";
// 				$data['info'] = $results;
// 				outJson($data);
			
// 		}else{
// 			unset($data);
// 			$data['code'] = 0;
// 			$data['message'] = "加载失败！";
// 			outJson($data);
// 		}
		
// 	}
public function num(){
	$id = $this->uid;
	$map['status'] = array("eq",1);
	$map['member_id'] = array("eq",$id);
	$dia_graphic = $this->graphic->where($map)->count();
	$dia_tel = $this->telephone->where($map)->count();
	$dia_reserve = $this->reserve->where($map)->count();
	$info['dia_count'] = $dia_graphic + $dia_tel + $dia_reserve;
	$map1['status'] = array("eq",2);
	$map1['member_id'] = array("eq",$id);
	$diaed_graphic = $this->graphic->where($map1)->count();
	$diaed_tel = $this->telephone->where($map1)->count();
	$diaed_reserve = $this->reserve->where($map1)->count();
	$info['diaed_count'] = $diaed_graphic + $diaed_tel + $diaed_reserve;
	if($info){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $info;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无数据！";
		outJson($data);
	}

}
public function patient_list(){
	$uid = $this->uid;
	$patient_id = I('post.patient_id');
	$p=!empty($_POST['p']) ? $_POST['p'] : 1;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
	$p=($p-1)*$limit;
	$where['member_id'] = array("eq",$uid);
	$where['patientmember_id'] = array("eq",$patient_id);
	$list[] = $this->graphic->field("id,type,create_time")->where($where)->select();
	$list[] = $this->telephone->field("id,type,create_time")->where($where)->select();
	$list[] = $this->reserve->field("id,type,create_time")->where($where)->select();

	foreach ($list as $key => $value) {

		if(!empty($value)){
			$result[] = array_filter($value, function($va) {
				return !empty($va['id']);
			});
		}
		
		
	}
	foreach($result as $value){
	    foreach($value as $v){  
	        $results[]=$v;


	    }  

	}
	$arr1 = array_map(create_function('$n', 'return $n["create_time"];'), $results);
	array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$results);
	foreach ($results as $ko => &$vo) {
		$vo['create_time'] = date('Y-m-d H:i:s',$vo['create_time']);
	}
	if($results){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $results;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 2;
		$data['message'] = "暂无数据！";
		outJson($data);
	}
}
public function consult_details(){
	$id = I('post.id');
	$type = I('post.type');
	$where['id'] = array('eq',$id);
	if($type == 1){
		$result = $this->graphic->where($where)->find();
		$patient = $this->patient->field("user_id,name,age,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
		$result['name'] = $patient['name'];
		$result['img_thumb'] = $patient['img_thumb'];
		$result['age'] = $patient['age'];
		$results['phone'] = $this->user->where("id=".$patient['user_id'])->getfield("username");
		$result['create_time'] = date('Y-m-d H:i:s',$result['create_time']);
	}else if($type == 2){
		$result = $this->telephone->where($where)->find();
		$patient = $this->patient->field("user_id,name,age,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
		$result['name'] = $patient['name'];
		$result['img_thumb'] = $patient['img_thumb'];
		$result['age'] = $patient['age'];
		$results['phone'] = $this->user->where("id=".$patient['user_id'])->getfield("username");
		$result['create_time'] = date('Y-m-d H:i:s',$result['create_time']);
	}else if($type == 3){
		$result = $this->reserve->where($where)->find();
		$patient = $this->patient->field("user_id,name,age,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
		$result['name'] = $patient['name'];
		$result['img_thumb'] = $patient['img_thumb'];
		$result['age'] = $patient['age'];
		$results['phone'] = $this->user->where("id=".$patient['user_id'])->getfield("username");
		$result['create_time'] = date('Y-m-d H:i:s',$result['create_time']);
	}
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $result;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "无此信息！";
		outJson($data);
	}
}
//个人信息展示
	public function patient_archives()
	{
		$patient_id = I('post.patient_id');
		// $patient_id = '538b45ef5c6f26b001e71b1c55b26a2c';
		$id = $this->user->where("md5(md5(id))='".$patient_id."'")->getField('id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="无此用户信息！";
			outJson($data);	
		}
		$list=$this->patient->field("id,name,img_thumb,sex,age,symptoms_desc,taboo_that,other_disease")->where("user_id=".$id)->find();
		if($list)
		{
			$list['img_thumb']=$this->host.$list['img_thumb'];
			$list['phone']=$this->user->where("id=".$id)->getField('username');
			$list['phone']=str_pad_star($list['phone'],7,'****','left');
			unset($data);
			$data['code']=1;
			$data['message']="获取信息成功！";
			$data['info']=$list;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无信息";
			outJson($data);	
		}
		
	}
}
?>