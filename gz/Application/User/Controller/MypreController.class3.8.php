<?php
// +----------------------------------------------------------------------
// | 我的处方接口
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MypreController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->pre =D("Pre");
		$this->patient =D("Patientmember");
		$this->member =D("Member");
		$this->hrebs =D("Hrebs");
		$this->pre_hrebs =D("PreHrebs");
		$this->single_hrebs =D("SingleHrebs");
		$this->setting =D("setting");
		$this->user =D("User");
		$this->admin =D("Admin");
		$this->personaddress=M("Personaddress");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//我的处方列表
	public function mypre_list()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$result=$this->pre->field("id,create_time,doctor_id,status")->where("patient_id=".$this->uid)->order("create_time desc")->limit($p.",".$limit)->select();
		
		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['doctor_id']);
			 	$list[$id]=isset($list[$id])?
			 			(strtotime($vo['create_time'])>strtotime($list[$id]['create_time']))? $vo:$list[$id] : $vo;
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["create_time"];'), $list);
		array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
		foreach($list as &$row)
		{
			$row['img_thumb']=$this->member->where("user_id=".$row['doctor_id'])->getField('img_thumb');
			$row['name']=$this->member->where("user_id=".$row['doctor_id'])->getField('name');
			$row['create_time']=time_tran($row['create_time']);
		}

		if($list)
		{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['info'] = $list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['info'] = array();
			outJson($data);
		}
		
	}
	//此医生的所有处方
	public function doctor_pre(){
		$uid = $this->uid;
		$doctor_id = I('post.doctor_id');
		if(empty($doctor_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择医生！";
			outJson($data);
		}
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		$result = $result=$this->pre->field("id,create_time,status")->where("patient_id=".$uid." and doctor_id =".$doctor_id)->order("create_time desc")->limit($p.",".$limit)->select();
		foreach ($result as $key => &$value) {
			$value['img_thumb']=$this->member->where("user_id=".$doctor_id)->getField('img_thumb');
			$value['name']=$this->member->where("user_id=".$doctor_id)->getField('name');
			$value['create_time']=time_tran($value['create_time']);
		}
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $result;
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
	//电子处方详情
	public function mypre_deal()
	{
		$id=I('post.id');
		$uid = $this->uid;
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方详情不存在";
			outJson($data);	
		}
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,doctor_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz,dosage_name,admin_pre")->where("id = '".$id."'")->find();
		if($results){
			$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
			$result['pname'] = $patient['name'];
			$result['sex'] = $patient['sex'];
			$result['age'] = $patient['age'];
			$result['phone'] =  $this->user->where("id=".$results['patient_id'])->getfield('username');
			$result['doctor'] = $this->member->where("user_id = '".$results['doctor_id']."'")->getfield('name');
			if($results['setting_id_class'] == 44){
				$result['img_thumb_prime'] = $results['img_thumb_prime'];
				$result['img_thumb_common'] = $results['img_thumb_common'];
				$result['pre_bz'] = $results['pre_bz'];
			}else{
				if($result['dosage_name'] == 131){
					$result['dosage_name'] = "原药";
				}else if($result['dosage_name'] == 132){
					$result['dosage_name'] = '汤药代煎';
				}else{
					$result['dosage_name'] = '膏方制作';
				}
			}
			$result['setting_id_class'] = $this->setting->where("id = '".$results['setting_id_class']."'")->getfield("title");
			$result['admin_pre'] = $this->admin->where('id='.$results['admin_pre'])->getfield('admin_name');
			
			if((!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)) && $results['setting_id_class'] == '43'){
				$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number,hrebs_total')->where("id = '".$results['hrebs_id']."'")->find();

				$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
				if(!empty($hrebs['pre_hrebs_id'])){
					$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.setting_id_model as setting_id_type')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
					foreach ($res as $key => $value) {
						$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
						$ress[]=$value;

					}
					if(empty($ress)){
						$result['pre_hrebs_id'] = array();
					}else{
						$result['pre_hrebs_id'] = $ress;
					}
				}else{
					$result['pre_hrebs_id'] = array();
				}

				$result['hrebs_number'] = $hrebs['hrebs_number'];
				$result['hrebs_total'] = $hrebs['hrebs_total'];
				$result['pre_instructions'] = $this->hrebs->where('id='.$results['hrebs_id'])->getField('hrebs_bz');
				$result['pre_description'] = $results['pre_description'];
					
				
			}else{
				$result['pre_hrebs_id'] = array();
			}
			
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['info'] = $result;
			outJson($data);

		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}







		// $results = $this->pre->field("id,setting_id_class,setting_id_type,hrebs_id,patient_id,doctor_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz")->where("id = '".$id."'")->find();
		// if($results){
		// 	$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
		// 	$result['pname'] = $patient['name'];
		// 	$result['sex'] = $patient['sex'];
		// 	$result['age'] = $patient['age'];
		// 	$result['phone'] =  $this->user->where("id=".$results['patient_id'])->getfield('username');
		// 	$result['doctor'] = $this->member->where("user_id = '".$results['doctor_id']."'")->getfield('name');
		// 	$result['setting_id_class'] = $this->setting->where("id = '".$results['setting_id_class']."'")->getfield("title");
		// 	$result['img_thumb_prime'] = $results['img_thumb_prime'];
		// 	$result['img_thumb_common'] = $results['img_thumb_common'];
		// 	$result['pre_description'] = $results['pre_description'];
			
		// 	if(!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)){
		// 		$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number,hrebs_total')->where("id = '".$results['hrebs_id']."'")->find();
		// 		$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
		// 			$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.setting_id_model as setting_id_type')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
		// 		$result['hrebs_number'] = $hrebs['hrebs_number'];
		// 		$result['hrebs_total'] = $hrebs['hrebs_total'];
		// 		$result['pre_bz'] = $this->hrebs->where('id='.$results['hrebs_id'])->getField('hrebs_bz');
		// 			foreach ($res as $key => $value) {
		// 				$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
		// 				$ress[]=$value;

		// 			}
		// 			if(empty($ress)){
		// 				$result['pre_hrebs_id'] = array();
		// 			}else{
		// 				$result['pre_hrebs_id'] = $ress;
		// 			}
				
		// 	}else{
		// 		$result['pre_hrebs_id'] = array();
		// 	}
			
		// 	unset($data);
		// 	$data['code'] = 1;
		// 	$data['message'] = "处方加载成功！";
		// 	$data['info'] = $result;
		// 	outJson($data);

		// }else{
		// 	unset($data);
		// 	$data['code'] = 0;
		// 	$data['message'] = "数据加载失败！";
		// 	outJson($data);
		// }
		
	}
	//取消付款
	public function pre_del()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
		$info=$this->pre->where("id=".$id)->find();
		if($info)
		{	if($info['setting_id_class']==43)
			{
				$res=$this->hrebs->where("id=".$info['hrebs_id'])->find();
				$this->pre_hrebs->where("id in (".$res['pre_hrebs_id'].")")->delete();
				$this->hrebs->where("id=".$info['hrebs_id'])->delete();
			}
			$this->pre->where("id=".$id)->delete();
			unset($data);
			$data['code']=1;
			$data['message']="删除成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
	}
	public function pre_address()
	{
		$result=$this->personaddress->field("id,province,city,district,phone,person,address")->where("patient_id=".$this->uid." and is_default=1")->find();
		if($result)
		{
			$result=$result;
		}else
		{
			unset($result);
			$result=$this->personaddress->field("id,province,city,district,phone,person,address")->where("patient_id=".$this->uid)->order("id desc")->find();
			if($result)
			{
				$result=$result;
			}else
			{
				$result=array();
			}
		}
		unset($data);
		$data['code']=1;
		$data['message']="信息获取成功";
		$data['info']=$result;
		outJson($data);		
	}
	//处方付款
	public function pre_pay()
	{
		$id=I('post.id');
		
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
		$info=$this->pre->where("id=".$id)->find();
		$info_res=$this->hrebs->where("id=".$info['hrebs_id'])->find();
		$res['id']=$info['id'];
		
		if($info['setting_id_class']==43)
		{
			unset($ret);
			$ret['img_thumb']='oss/2017-11-27/15117523983053.jpg';
			$ret['description']=$info['pre_description'];
			$ret['price']=$info_res['hrebs_total'];
			$res['pre']=$ret;
			$res['total']=$info['total_price'];
		}else if($info['setting_id_class']==44)
		{
			unset($ret);
			$ret['img_thumb']=$info['img_thumb_common'];
			$ret['description']=$info['pre_bz'];
			$ret['price']=$info['total'];
			$res['pre']=$ret;
			$res['total']=$info['total_price'];
		}
		if(!empty($info['dosage_name'])){
			$res['dosage']=$this->setting->where("id=".$info['dosage_name'])->order("sort asc")->getfield('title');
		}else{
			$res['dosage'] = '原药';
		}
		// $res['dosage']=$info['dosage_name'];
		$res['process_price']=$info['process_price'];
		$res['distribution']=$info['yun_free'];
		$res['coupons']='无可用';
		$res['integral']='0';
		unset($data);
		$data['code']=1;
		$data['message']="信息获取成功";
		$data['info']=$res;
		outJson($data);				
	}
	//提交支付
	public function do_pay()
	{
		$id=I('post.id');
		$address_id=I('post.address_id');
		$total_price=I('post.total_price');
		$hrebs_bz=I('post.hrebs_bz');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
		if(empty($address_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择地址";
			outJson($data);	
		}
		
		if(empty($total_price))
		{
			unset($data);
			$data['code']=0;
			$data['message']="总价不能为空！";
			outJson($data);	
		}
		$balance=$this->user->where("id=".$this->uid)->getField("balance");
		if($total_price > $balance)
		{
			unset($data);
			$data['code']=10003;
			$data['message']="余额不足，请充值！";
			outJson($data);	
		}
		unset($res);
		$res=array(
			'status' => 75,
			'address_id' => $address_id,
			'total_price' => $total_price,
			'hrebs_bz' => $hrebs_bz,
			'pay_time' => time(),
		);
		$result=$this->pre->where("id=".$id)->save($res);
		if($result)
		{
			$this->user->where("id=".$this->uid)->setDec('balance',$total_price);
			$balance = $this->user->where('id='.$this->uid)->getField('balance');
			financial_log($this->uid,$total_price,2,$balance,'处方支付',9);
			unset($data);
			$data['code']=1;
			$data['message']="支付成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="支付失败！";
			outJson($data);	
		}	
	}
}
?>