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
class MyprescriptionController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");
		$this->pharmacy =D("Pharmacy");
		$this->singlehrebs =D("SingleHrebs");
		$this->setting =D("Setting");
		$this->pharmacyhrebs =D("PharmacyHrebs");
		$this->goods =D("Goods");
		$this->cart =D("Cart");
		$this->ordergoods =D("OrderGoods");
		$this->admin =D("Admin");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//我要抓药列表
	public function prescription_list()
	{
		$uid=$this->uid;
		$setting_id=I('post.setting_id');
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		if(empty($setting_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择中药炮制类型！";
			outJson($data);	
		}
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$list=$this->pharmacy->field("id,img_thumb,content,create_time,order_status")->where("user_id=".$uid." and setting_id=".$setting_id)->order("create_time desc")->limit($p.",".$limit)->select();
		foreach($list as &$row)
		{
			$row['create_time']=date("Y-m-d",$row['create_time']);
			if($row['img_thumb']!=''){
				$img = explode(",", $row['img_thumb']);
				$row['img_thumb'] = $img[0];
			}
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
	//我要抓药提交
	public function prescription_add()
	{
		$uid=$this->uid;
		$img_thumb=I('post.img_thumb');
		$content=I('post.content');
		$setting_id=I('post.setting_id');
		$yun_free = $this->admin->where("is_default=1")->getField("yun_free");
		if(empty($setting_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择中药炮制类型！";
			outJson($data);	
		}
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		if(empty($img_thumb))
		{
			unset($data);
			$data['code']=0;
			$data['message']="处方图片不能为空！";
			outJson($data);	
		}
		
		
		$res=array(
			'order_sn' => time().rand(1000,9999),
			'user_id' => $uid,
			'img_thumb' => $img_thumb,
			'content' => $content,
			'setting_id' => $setting_id,
			'create_time' => time(),
			'yun_free' => $yun_free
		);
		if($this->pharmacy->add($res))
		{
			unset($data);
			$data['code']=1;
			$data['message']="抓药成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="抓药失败！";
			outJson($data);	
		}
		
	}
	//抓药详情
	public function prescription_info()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="药房不存在！";
			outJson($data);	
		}
		$info=$this->pharmacy->field("id,order_sn,all_price,process_price,order_status")->where("id=".$id)->find();
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
	//自营商品
	public function goods_list()
	{
		$setting_id=I('post.setting_id');
		if(empty($setting_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择类型！";
			outJson($data);	
		}
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		
		$p=($p-1)*$limit;
		
		if($typesort==1)
		{
			$orderwhere="sales desc,";
		}else if($typesort==2)
		{
			$orderwhere="price asc,";
		}
		$result=$this->goods->field("id,title,img_thumb,price,sales")->where("status=0 and category_id=".$setting_id)->order($orderwhere."createtime desc")->limit($p.",".$limit)->select();
		
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="商品列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无商品！";
			$data['info']=array();
			outJson($data);	
		}
	}
	//商品详情
	public function goods_deal()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择商品";
			outJson($data);	
		}
		$result=$this->goods->field("id,title,img_thumb,price,sales,description,img_item,label,body")->where("status=0 and id=".$id)->find();
		if($result)
		{
			$result['rate']='96%';
			// $count['order'] = $this->ordergoods->where("1=1")->count();
			// $count['order_goods'] = $this->ordergoods->where("goods_id=".$id)->count();
			// $result['rate']=number_format($count['order_goods']/$count['order'],2)*100;
			// $result['rate'].="%";
			$result['img_item']=explode(',', $result['img_item']);
			$result['body']=explode(',', $result['body']);
			$result['cart_count']=$this->cart->where("user_id=".$this->uid)->count();
			$res=$this->setting->field("id,title")->where("parentid=134 and status=1")->order("sort asc")->select();
			foreach($res as &$row)
			{
				if(in_array($row['id'],explode(',',$result['label']))){
					$row['is_show']=1;
				}else
				{
					$row['is_show']=0;
				}
			}
			$result['label']=$res;
			unset($data);
			$data['code']=1;
			$data['message']="商品列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无商品！";
			$data['info']=array();
			outJson($data);	
		}
	}
	

}
?>