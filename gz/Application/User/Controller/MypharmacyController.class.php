<?php
// +----------------------------------------------------------------------
// | 我要抓药管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MypharmacyController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");
		$this->pharmacy =D("Pharmacy");
		$this->personaddress =D("Personaddress");
		$this->singlehrebs =D("SingleHrebs");
		$this->setting =D("Setting");
		$this->pharmacyhrebs =D("PharmacyHrebs");
		$this->goods =D("Goods");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//我要药房列表
	public function lists()
	{
		$uid=$this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$list=$this->pharmacy->field("id,img_thumb,content,create_time,order_status")->where("user_id=".$uid)->order("create_time desc")->limit($p.",".$limit)->select();
		foreach($list as &$row)
		{
			$row['create_time']=date("Y-m-d",$row['create_time']);
		}
		if($list)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$list;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="没有信息了";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	
	//抓药详情
	public function info()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="药房不存在！";
			outJson($data);	
		}
		$info=$this->pharmacy->field("id,order_sn,price as all_price,process_price,hrebs_number,order_status")->where("id=".$id)->find();
		$info['all_price'] = $info['all_price']/$info['hrebs_number'];
		$res=$this->patientmember->field("name,age")->where("user_id=".$this->uid)->find();
		$info['user_name']=$res['name'];
		$info['user_age']=$res['age'];
		$info['user_phone']=$this->user->where("id=".$this->uid)->getField("username");
		$info['user_phone']=str_pad_star($info['user_phone'],5,'*****','left');
		$result_list=array();
		$materials_list=$this->pharmacyhrebs->where("pharmacy_id=".$info['id'])->select();
		foreach($materials_list as $row)
		{
			unset($rest);
			$rest=$this->singlehrebs->where("id=".$row['hrebs_name_id'])->find();
			$result['name']=$rest['hrebs_name'];
			$result['num']=$row['hrebs_dosage'];
			$result['unit_price']=$rest['unit_price'];
			$result['unit']=$this->setting->where("id=".$rest['setting_id_model'])->getField('description');
			$result['count_price']=$result['unit_price']*$result['num'];
			$result_list[]=$result;
		}
		$info['materials_list']=$result_list;
		if($info)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取成功";
			$data['info']=$info;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="获取失败";
			outJson($data);	
		}
	}
	//取消付款
	public function mypharmacy_del()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
		$info=$this->pharmacy->where("id=".$id)->find();
		if($info)
		{	
			$this->pharmacyhrebs->where("pharmacy_id=".$info['id'])->delete();
			$this->pharmacy->where("id=".$id)->delete();
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
	//处方付款
	public function Mypharmacy_pay()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方不存在";
			outJson($data);	
		}
		$info=$this->pharmacy->field("id,img_thumb,content,yun_free,all_price,process_price,price,hrebs_number")->where("id=".$id)->find();
		$info['price'] = $info['price']/$info['hrebs_number'];
		$info['dosage']=$this->setting->field("id,title")->where("parentid=130")->order("sort asc")->select();
		unset($data);
		$data['code']=1;
		$data['message']="信息获取成功";
		$data['info']=$info;
		outJson($data);				
	}
	//提交支付
	public function do_pay()
	{
		$id=I('post.id');
		$address_id=I('post.address_id');
		$dosage_name=I('post.dosage_name');
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
		if(empty($dosage_name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择剂型";
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
		$patient_address = $this->personaddress->where('id='.$address_id)->find();
		unset($res);
		$res=array(
			'order_status' => 75,
			'address_id' => $address_id,
			'consignee' => $patient_address['person'],
			'phone' => $patient_address['phone'],
			'address' => $patient_address['address'],
			'setting_id_dostype' => $dosage_name,
			'hrebs_bz' => $hrebs_bz,
			'is_pay' => 1,
			'pay_time' => time(),
		);
		$result=$this->pharmacy->where("id=".$id)->save($res);
		if($result)
		{
			$this->user->where("id=".$this->uid)->setDec('balance',$total_price);
			$balance = $this->user->where('id='.$this->uid)->getField('balance');
			financial_log($this->uid,$total_price,2,$balance,'我的药房处方支付',10);
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