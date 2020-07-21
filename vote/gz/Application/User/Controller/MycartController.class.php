<?php
// +----------------------------------------------------------------------
// | 我的购物车管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MycartController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->cart =D("Cart");
		$this->goods =D("Goods");
		$this->personaddress=M("Personaddress");
		$this->order=M("Order");
		$this->ordergoods=M("OrderGoods");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//加入购物车
	public function cart_add()
	{
		$goods_id=I('post.goods_id');
		$user_id=$this->uid;
		$count=$this->cart->where("goods_id=".$goods_id." and user_id=".$user_id)->count();
		if($count > 0)
		{
			$res=$this->cart->where("goods_id=".$goods_id." and user_id=".$user_id)->setInc('num',1);
			if($res)
			{
				unset($data);
				$data['code']=1;
				$data['message']="加入购物车成功！";
				outJson($data);	
			}
		}else
		{
			unset($res);
			$res=array(
				'goods_id' => $goods_id,
				'num' => 1,
				'user_id' => $user_id,
				'createtime' => time(),
			);
			$result=$this->cart->add($res);
			if($result)
			{
				unset($data);
				$data['code']=1;
				$data['message']="加入购物车成功！";
				outJson($data);	
			}
		}
		
	}
	//个人信息展示
	public function cart_list()
	{
		$uid=$this->uid;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$total=0;
		$number=0;
		$list=$this->cart->field("id,goods_id,num")->where("user_id=".$uid)->order('createtime desc')->select();
		foreach($list as &$row)
		{
			$row['goods_name']=$this->goods->where("id=".$row['goods_id'])->getField('title');
			$row['goods_price']=$this->goods->where("id=".$row['goods_id'])->getField('price');
			$row['goods_desc']=$this->goods->where("id=".$row['goods_id'])->getField('description');
			$row['goods_img']=$this->goods->where("id=".$row['goods_id'])->getField('img_thumb');
		}
		if($list)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取信息成功！";
			$data['info']=$list;
			$data['total']=$total;
			$data['number']=$number;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无信息";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//增加数量
	public function num_save()
	{
		$uid=$this->uid;
		$id=I('post.id');
		$num=I('post.num');
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="此商品不存在您的购物车！";
			outJson($data);	
		}
		$result=$this->cart->where("user_id=".$uid." and goods_id=".$id)->save(array('num' => $num));
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="更新成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="更新失败！";
			outJson($data);	
		}
		
	}
	//删除购物车
	public function cart_del()
	{
		$uid=$this->uid;
		$id=$_POST['id'];
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="此商品不存在您的购物车！";
			outJson($data);	
		}
		$id=explode(',',$id);
		foreach($id as $row)
		{
			$result=$this->cart->where("user_id=".$uid." and goods_id=".$row)->delete();
		}
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="删除成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="删除失败！";
			outJson($data);	
		}
	}
	//结算
	public function cart_settlement()
	{
		$id=$_POST['id'];
		$uid=$this->uid;
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="此商品不存在您的购物车！";
			outJson($data);
		}
		$id=explode(',',$id);
		foreach($id as $row)
		{
			unset($result);
			unset($price);
			$result=$this->cart->where("user_id=".$uid." and goods_id=".$row)->find();
			$price=$this->goods->where("id=".$result['goods_id'])->getField('price');
			$total+=$price*$result['num'];
		}
		$res=array(
			'total' => $total,
			'number' => count($id),
		);
		unset($data);
		$data['code']=1;
		$data['message']="获取成功！";
		$data['info']=$res;
		outJson($data);	
		
	}
	
	public function shipping_address()
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
	
	//结算
	public function order_settlement()
	{
		$id=$_POST['id'];
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="此商品不存在您的购物车！";
			outJson($data);
		}
		$id=explode(',',$id);
		foreach($id as $key=>$row)
		{
			$result[$key]=$this->cart->field("id,goods_id,num")->where("goods_id=".$row)->find();
			$result[$key]['goods_name']=$this->goods->where("id=".$result[$key]['goods_id'])->getField('title');
			$result[$key]['goods_price']=$this->goods->where("id=".$result[$key]['goods_id'])->getField('price');
			$result[$key]['goods_desc']=$this->goods->where("id=".$result[$key]['goods_id'])->getField('description');
			$result[$key]['goods_img']=$this->goods->where("id=".$result[$key]['goods_id'])->getField('img_thumb');
			//$price=$this->goods->where("id=".$result['goods_id'])->getField('price');
			$total+=$result[$key]['goods_price']*$result[$key]['num'];
		}
		$res['cart_list']=$result;
		$res['distribution']='5.00';
		$res['coupons']='无可用';
		$res['integral']='0';
		$res['total']=$total+$res['distribution'];
		unset($data);
		$data['code']=1;
		$data['message']="信息获取成功";
		$data['info']=$res;
		outJson($data);		
		
	}
	
	
	//直接结算
	public function order_deal()
	{
		$id=$_POST['id'];
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="此商品不存在您的购物车！";
			outJson($data);
		}
		$res['goods_id']=$id;
		$res['goods_name']=$this->goods->where("id=".$id)->getField('title');
		$res['goods_price']=$this->goods->where("id=".$id)->getField('price');
		$res['goods_desc']=$this->goods->where("id=".$id)->getField('description');
		$res['goods_img']=$this->goods->where("id=".$id)->getField('img_thumb');
		$res['num']=1;
		$res['distribution']='5.00';
		$res['coupons']='无可用';
		$res['integral']='0';
		$res['total']=$res['goods_price']+$res['distribution'];
		unset($data);
		$data['code']=1;
		$data['message']="信息获取成功";
		$data['info']=$res;
		outJson($data);		
		
	}
	//
	
	
	//提交支付
	public function do_pay()
	{
		$id=I('post.id');
		$name=I('post.my_name');
		$phone=I('post.my_phone');
		$address=I('post.my_address');
		$order_price=I('post.order_price');
		$distribution=I('post.distribution');
		$note=I('post.note');
		$num=I('post.num');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="商品不存在";
			outJson($data);	
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="收货人姓名不能为空！";
			outJson($data);	
		}
		if(empty($phone))
		{
			unset($data);
			$data['code']=0;
			$data['message']="收货人电话不能为空！";
			outJson($data);	
		}
		if(empty($address))
		{
			unset($data);
			$data['code']=0;
			$data['message']="收货地址不能为空！";
			outJson($data);	
		}
		if(empty($order_price))
		{
			unset($data);
			$data['code']=0;
			$data['message']="总价不能为空！";
			outJson($data);	
		}
		$balance=$this->user->where("id=".$this->uid)->getField("balance");
		if($order_price > $balance)
		{
			unset($data);
			$data['code']=10003;
			$data['message']="余额不足，请充值！";
			outJson($data);	
		}
		unset($res);
		$res=array(
			'order_sn' => date("YmdHis").rand(10000,99999),
			'user_id' => $this->uid,
			'order_price' => $order_price,
			'name' => $name,
			'phone' => $phone,
			'address' => $address,
			'note' => $note ,
			'distribution' => $distribution,
			'status_type' => 124,
			'createtime' => time(),
		);
		$result=$this->order->add($res);
		if($result)
		{
			if($num==0 || $num=='')
			{
			$id=explode(',',$id);
			foreach($id as $key=>$row)
			{
				unset($rest);
				unset($goods_find);
				unset($insert_order);
				$rest=$this->cart->field("id,goods_id,num")->where("goods_id=".$row)->find();
				$goods_find=$this->goods->field("id,title,price,description,img_thumb")->where("id=".$row)->find();
				$insert_order=array(
					'user_id' => $this->uid,
					'order_id' => $result,
					'goods_id' => $goods_find['id'],
					'goods_name' => $goods_find['title'],
					'goods_desc' => $goods_find['description'],
					'price' => $goods_find['price'],
					'goods_img' => $goods_find['img_thumb'],
					'number' => $rest['num'],
				);
				$this->ordergoods->add($insert_order);
				$this->cart->where("goods_id=".$row)->delete();	
			}
			}else
			{
				$goods_find=$this->goods->field("id,title,price,description,img_thumb")->where("id=".$id)->find();
				$insert_order=array(
					'user_id' => $this->uid,
					'order_id' => $result,
					'goods_id' => $goods_find['id'],
					'goods_name' => $goods_find['title'],
					'goods_desc' => $goods_find['description'],
					'price' => $goods_find['price'],
					'goods_img' => $goods_find['img_thumb'],
					'number' => $num,
				);
				$this->ordergoods->add($insert_order);
			}
			$this->user->where("id=".$this->uid)->setDec('balance',$order_price);
			$balance = $this->user->where('id='.$this->uid)->getField('balance');
			financial_log($this->uid,$order_price,2,$balance,'商品支付',11);
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